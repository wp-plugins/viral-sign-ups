<?php

/**
 * Initializes $vsu_settings global variable. Merges saved settings with default 
 * ones. 
 * 
 * @global array $vsu_settings
 */
function vsu_settings_init() {
    global $vsu_settings;
    $stored = get_option( 'vsu_data', array() );
    $default = include_once VSU_INC_DIR . 'default_settings.php';
    $vsu_settings = wp_parse_args( $stored, $default );
}

/**
 * Retrieves a setting in the specified section.
 * 
 * @global array $vsu_settings
 * @param string $setting_name Setting name.
 * @param string $section_name Section name.
 * @return mixed The setting if found, null if not.
 */
function vsu_get_setting( $setting_name, $section_name ) {
    global $vsu_settings;
    if( ! isset( $vsu_settings[ $section_name ][ $setting_name ] ) ) {
        return;
    }
    return $vsu_settings[ $section_name ][ $setting_name ];
}

/**
 * Caches a temporary setting.
 * 
 * @global array $vsu_settings
 * @param string $setting_name Setting name.
 * @param string $setting_value Setting value.
 */
function vsu_temp_setting( $setting_name, $setting_value ){
    global $vsu_settings;
    $vsu_settings['_temp'][$setting_name] = $setting_value;
}

/**
 * Checks for signup errors and returns the error if found. 
 * 
 * @return boolean|string False if no errors occurred, the error text if an error
 * was found.
 */
function vsu_check_signup_errors(){
    $signup_error = vsu_get_setting( 'signup_error', '_temp' );
    if( ! $signup_error ) {
        return false;
    }
    $signup_errors = array(
        'antispam_fail' => __( 'Failed to pass the spam check.', 'viralsignups' ),
        'empty_email_address' => __( 'Please fill in the email field.', 'viralsignups' ),
        'invalid_email_address' => __( 'Please insert a valid email address.', 'viralsignups' )
    );
    if( ! isset( $signup_errors[ $signup_error ] ) ) {
        return false;
    }
    
    return $signup_errors[ $signup_error ];
}

/**
 * Checks what action should be taken after a signup attempt. 
 * 
 * @return boolean|array False if no action is to be taken, assoc array of
 * action_id => action_label of the action to proceed with.
 */
function vsu_check_signup_action() {
    $signup_action = vsu_get_setting( 'signup_action', '_temp' );
    if( ! $signup_action ) {
        return false;
    }
    
    $popup_screens = array(
        'success' => 'thank_you',
        'pull_user_data' => 'log_in'
    );
    
    if( isset( $popup_screens[ $signup_action ] ) ) {
        return array( 'display_popup' => $popup_screens[ $signup_action ] );
    }
    if( $signup_action === 'wrong_reference_key' ) {
        $signup_url = esc_attr( vsu_get_ref_url() );
        $new_user = __( 'new user', 'viralsignups' );
        return array( 'display_ref_error' => sprintf(
                __( 'Your reference key was not found. You can sign up as a %s.', 'viralsignups' ), 
                "<a href='$signup_url'>$new_user</a>") );
    }
    if( $signup_action === 'error' ) {
        return array( 'display_error' => __( 'Error occurred while signing up. Please try again.', 'viralsignups' ) );
    }
    if( $signup_action === 'limit_reached' ) {
        return array( 'display_error' => __( 'Oops! Seems like we\'ve reached our monthly quota. Please get in touch through our Contact page.' ) );
    }
    return false;
}

/**
 * Returns the signup form output.
 * 
 * @param string $screen 'front' to prepare the form for front-end display, 'preview'
 * to prepare the admin preview.
 * @param array $data
 * For Front-End optionally needs to receive:
 *      'ref' : Reference key of the form.
 *      'email_value': Value for email field.
 *      'error': Any errors the form should display.
 * For Preview:
 *      'email_name': Name attribute for email field.
 * @return string Form HTML.
 */
function vsu_form_html( $screen = 'front', $data = array() ) {
    // Init
    $out = ''; $ref_field = ''; $error_field = ''; $ref_url_field = '';
    $email_placeholder = esc_attr( vsu_get_setting( 'email_text', 'email_form' ) );
    $button_text = esc_attr( vsu_get_setting( 'button_text', 'email_form' ) );
    $button_color = esc_attr( vsu_get_setting( 'button_color', 'email_form' ) );
    $button_type = 'submit';
    
    // Front-End settings
    if( $screen === 'front' ) {
        $ref = '';
        if( isset( $data['ref'] ) ) {
            $ref = esc_attr( $data['ref'] );
            $ref_field = "<input type='hidden' name='vsu_ref' value='$ref' />";
        }
        $http_ref = filter_input( INPUT_SERVER, 'HTTP_REFERER' );
        if( (string) $http_ref !== '' ) {
            $http_ref = esc_attr( $http_ref );
            $ref_url_field = "<input type='hidden' name='vsu_http_ref' value='$http_ref' />";
        }
        $current_page_url = esc_attr( vsu_get_ref_url( $ref ) );
        $email_value = esc_attr( $data['email_value'] );
        $email_attrs = " required='required' name='vsu_email' placeholder='$email_placeholder'";
        $email_type = 'email';
        
        if( isset( $data['error'] ) ) {
            $error = $data['error'];
            $error_field = "<div class='vsu-error-wrap'><p>$error</p></div>";
        }
    }
    
    // Preview settings
    if( $screen === 'preview' ) {
        $email_value = $email_placeholder;
        $email_type = 'text';
        $email_name = esc_attr( $data['email_name'] );
        $email_attrs = " name='$email_name'";
        $button_type = 'button';
    }
    
    $button_attrs = " style='background-color:$button_color;'";
    
    $out .= $error_field . $ref_field . $ref_url_field;
    $out .= "<p class='vsu-email-field-wrap'>" . 
                "<input class='vsu-email-field' type='$email_type' value='$email_value' $email_attrs />" .
            '</p>';
    $out .= '<p class="vsu-signup-wrap">' . 
                "<input class='vsu-signup-button vsu-button' type='$button_type' value='$button_text' $button_attrs />" .
            '</p>';
    
    // align
    $styles = '';
    if( isset( $data['align'] ) && in_array( $data['align'], array( 'left', 'center', 'right' ) ) ) {
        $align = $data['align'];
        $styles = " style='text-align: $align;'";
    }
    
    if( $screen === 'front' ) {
        $class = '';
        if( vsu_get_setting( 'antispam_enabled', 'email_form' ) ) {
            $class = ' vsu-antispam';
        }
        $out =  "<form class='vsu-form$class' method='post' action='$current_page_url'$styles>$out</form>";
    }
    
    $out =  "<div class='vsu-signup-form vsu-wrap'>$out</div><!-- / Viral Sign Up Form -->";
    return $out;
}

/**
 * @return string  General credits output.
 */
function vsu_credit_line( $force_credits = false ) {
    if( ! vsu_get_setting( 'credit_line_on', 'popup_content' ) && ! $force_credits ) {
        return;
    }
    $website_url = VSU_WEBSITE;
    $logo_url =  esc_attr( VSU_ASSETS_URI . 'img/logo_small.png' );
    $logo = "<img src='$logo_url' alt='ViralSignUps logo' />";
    $powered_by = sprintf( 
                __( 'Powered by: %s', 'viralsignups' ), 
                "<a href='$website_url' target='_blank' title='ViralSignUps.com'>$logo</a>"
            );
    
    $out =  "<div class='vsu-credits'>"
                . "<p class='vsu-powered-by'>$powered_by</p>"
            . "</div>";
    
    return $out;
}

/**
 * Generates output for Popup Content.
 * 
 * @param string $screen Which popup to display. Can be:
 * 'thank_you': Displayed after a new successfull signup.
 * 'log_in': Displayed after an attempt to resignup.
 * @return string Popup output.
 */
function vsu_popup_html( $context, $screen = 'front'  ){
    $out = '';
    $user_data = vsu_get_setting( 'user_data', '_temp');
    $force_credits = false;
    
    $social_buttons = ''; $promo_attrs = ''; $promo_url_attrs = '';
    if( $screen === 'preview' ){
        $buttons = array(
            'facebook' => __( 'Share', 'viralsignups' ),
            'twitter' => __( 'Tweet', 'viralsignups' ),
            'googlePlus' => __( 'Share', 'viralsignups' ),
            'linkedIn' => __( 'Share', 'viralsignups' ),
            'email' => __( 'Email', 'viralsignups' ) );
        foreach ( $buttons as $button_id => $button_name ) {
            $social_buttons .= "<a class='vsu-button vsu-social-button vsu-social-button-$button_id'>$button_name</a>";
        }
        $user_data['ref_key'] = 'xkdIOendhIpsNM';
        $promo_attrs = ' data-vsu-live="promo_text"';
        $promo_url_attrs = ' data-vsu-live="promo_page"';
        $force_credits = true;
    }
    
    if( $context === 'thank_you' ) {
        $message = __( 'Thank you!', 'viralsignups' );
        $promo_text = esc_html( vsu_get_setting( 'promo_text', 'popup_content' ) );
        $out .= "<h1 class='vsu-popup-message'>$message</h1>";
        $out .= "<h2 class='vsu-popup-promo-text'$promo_attrs>$promo_text</h2>";
    }
    if( $context === 'log_in' ) {
        $message = __( 'Oops, seems like you have already signed up', 'viralsignups' );
        $out .= "<h2 class='vsu-popup-main-text'>$message</h2>";
        $message = sprintf( __( 'So far, you have %d referrals ', 'viralsignups' ), 
                (int) $user_data['total_signups'] );
        $out .= "<h2 class='vsu-popup-main-text vsu-popup-highlighted-text'>$message</h2>";
    }    
    
    $ref_number = (int) vsu_get_setting( 'ref_number', 'popup_content' );
    $content_text1 = sprintf( 
                        __( 'Get %s of your friends to sign up.', 'viralsignups' ),
                        "<span data-vsu-live='ref_number'>$ref_number</span>"
                    );
    $content_text2 = __( 'Or share your unique link:', 'viralsignups' );
    $ref_url = vsu_get_ref_url( $user_data['ref_key'] );
    $ref_url_attr = esc_attr( $ref_url );
    
    $out .= "<p class='vsu-popup-text'>$content_text1</p>";
    $out .= "<div class='vsu-popup-social-share' data-url='$ref_url_attr'>$social_buttons</div>";
    $out .= "<p class='vsu-popup-text'>$content_text2</p>";
    $out .= "<p class='vsu-popup-ref'>"
                . "<a href='$ref_url_attr' class='vsu-popup-ref-link'$promo_url_attrs>$ref_url</a>"
            . "</p>";
    $out = "<div class='vsu-popup-content-inner'>$out</div>";
    $out .= vsu_credit_line( $force_credits );
    $out = "<div class='vsu-popup-content vsu-wrap'>$out</div><!-- / Viral Sign Ups Popup Content -->";
    return $out;
}

/**
 * Builds a referral URL given the referral key.
 * 
 * @param string $ref_key Referral key.
 * @return boolean|string URL to signup with the specified referral key. False if 
 * the signup page was not set. 
 */
function vsu_get_ref_url( $ref_key = '' ) {
    $page_ID = vsu_get_setting( 'promo_page', 'popup_content' );
    $page_url = get_permalink( $page_ID );
    if( ! $page_url ) {
        return false;
    }
    if( (string) $ref_key === '' ){
        return $page_url;
    }
    
    $page_url = add_query_arg( 'ref', $ref_key, $page_url );
    
    return $page_url;
}

/**
 * Shortens the text.
 * 
 * @param string $string Given text.
 * @param int $length Max length to shorten the text to.
 * @return string Shortened text.
 */
function vsu_short_text( $string, $length = 164 ) {
    return substr( $string, 0, $length );
}