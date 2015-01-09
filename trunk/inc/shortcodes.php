<?php

/**
 * Sign Up form shortcode.
 * 
 * @return string Sign Up form HTML.
 */
function vsu_signup_form( $atts ) {
    $data = array();
    if( isset( $atts['align'] ) ) {
        $data['align'] = $atts['align'];
    }

    // prepare signup form data
    $ref = filter_input( INPUT_GET, 'ref' );
    if( (string) $ref !== '' ) {
        $data['ref'] = $ref;
    }
    $data['email_value'] = filter_input( INPUT_POST, 'vsu_email' );
    
    // check for signup errors
    $signup_error = vsu_check_signup_errors();
    if( $signup_error !== false ) {
        $data['error'] = $signup_error;
        $out = vsu_form_html( 'front', $data );
        return $out;
    } // error occurred while signing up, show the form to try again
    
    $signup_action = vsu_check_signup_action();
    
    if( isset( $signup_action[ 'display_popup' ] ) ) {
        $out = vsu_popup_html( $signup_action[ 'display_popup' ] );
        return $out;
    } // show popup
    
    if( isset( $signup_action['display_ref_error'] ) ) {
        $data['error'] = $signup_action['display_ref_error'];
        $out = vsu_form_html( 'front', $data );
        return $out;
    } // reference key was specified, but not found. Let user to signup for a new account.
    
    if( isset( $signup_action['display_error'] ) ) {
        $data['error'] = $signup_action['display_error'];
        $out = vsu_form_html( 'front', $data );
        return $out;
    } // error signing up
    
    $out = vsu_form_html( 'front', $data );
    return $out;
}
add_shortcode( 'viralsignups_form', 'vsu_signup_form' );