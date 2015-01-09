<?php

/**
 * Contains default settings for admin section area to be used when no saved 
 * settings data are found.
 */
global $current_user;
get_currentuserinfo();

return array(
    'settings' => array(
        'license_key' => '',
        'first_name' => $current_user->user_firstname,
        'last_name' => $current_user->user_lastname,
        'domain' => get_site_url(),
        'email' => $current_user->user_email
    ),
    'email_form' => array(
        'button_text' => __( 'SUBMIT', 'viralsignups' ),
        'button_color' => '#31ba8b',
        'email_text' => __( 'Enter Your Email', 'viralsignups' )
    ),
    'popup_content' => array(
        'ref_number' => 5,
        'promo_text' => __( 'Want to cut the line and get Viral Sign Ups free for 12 months?', 'viralsignups' )
    ),
    'social_sharing' => array(
        'text' => __( 'Viral Sign Ups - Viral marketing tool for email sign ups', 'viralsignups' )
    ),
    'shortcode' => array(
        'signup_form' => '[viralsignups_form align="left"/]'
    ),
    'autoresponders' => array(
        'from_name' => get_bloginfo( 'name' ),
        'from_address' => get_option( 'admin_email' ),
        'signup_full_text' => __( "You've earned Viral Sign Ups free for 12 months! You will receive your invite within the next business day. Keep sharing — more rewards coming soon :)", 'viralsignups' )
    )
);