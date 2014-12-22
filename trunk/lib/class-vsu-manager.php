<?php

if( ! class_exists( 'VSU_Manager' ) ) {

    /**
     * ViralSignUps plugin manager.
     * 
     * @package ViralSignUps
     */
    class VSU_Manager{
        function __construct() {
            // general contructor
        }

        /**
         * Call this before WP 'init' action is fired.
         */
        public function run() {
            add_action( 'wp_enqueue_scripts', array( $this, 'signup_form_scripts' ) );
            add_action( 'wp_head', array( $this, 'form_action_handler' ) );
        }

        /**
         * Fired on WP 'init' hook.
         * 
         * @global array $vsu_settings Holds general plugin settings.
         * @global VSU_API $vsu_api API Manager.
         */
        public function init() {
            VSU_Ajax::init(); // init AJAX
            vsu_settings_init(); // init settings
            global $vsu_api;
            $vsu_api = new VSU_API( vsu_get_setting( 'license_key', 'settings' ) ); // init API manager
        }   

        /**
         * Loads the plugin files.
         * 
         * @param string $screen Set to 'front' to skip loading admin side files.
         */
        public function library( $screen = 'admin' ) {
            require_once VSU_INC_DIR . 'functions.php'; // common functions
            require_once VSU_INC_DIR . 'shortcodes.php'; // load shortcodes
            require_once VSU_LIB_DIR . 'class-vsu-ajax.php'; // AJAX actions
            require_once VSU_LIB_DIR . 'class-vsu-api.php'; // API
            require_once VSU_LIB_DIR . 'class-vsu-autoresponder.php'; // Autoresponder

            if( $screen === 'admin' ) {
                require_once VSU_LIB_DIR . 'class-vsu-admin-manager.php'; // admin manager
            }
        }

        /**
         * Enqueues styles and scripts for front-end signup form. Should be called
         * from WP 'wp_enqueue_scripts' hook.
         */
        public function signup_form_scripts() {
            wp_enqueue_style( 'vsu-gfonts', 'http://fonts.googleapis.com/css?family=Lato:400,300' );
            wp_enqueue_style( 'vsu-signup-form-style', VSU_ASSETS_URI . 'css/signup_form.css' );
            wp_enqueue_script( 'vsu-script', VSU_ASSETS_URI . 'js/custom.js', array( 'jquery' ) );
            if ( get_option('permalink_structure') ) { 
                $linkedIn = true;
            }
            wp_localize_script( 'vsu-script', 'VSU_Data', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'texts' => array(
                    'share' => __( 'Share', 'viralsignups' ),
                    'tweet' => __( 'Tweet', 'viralsignups' ),
                    'email' => __( 'Email', 'viralsignups' ),
                    'close' => __( 'Close', 'viralsignups' ),
                    'antispam' => __( 'Are you human?', 'viralsignups'),
                    'antispam_alert' => __( 'Please check the box to confirm that you are NOT a spammer.', 'viralsignups')
                ),
                'social_url' => vsu_get_setting( 'ref_url', '_temp' ),
                'social_title' => vsu_get_setting( 'text', 'social_sharing' ),
                'social_text' => vsu_get_setting( 'promo_text', 'popup_content' ),
                'linkedin_enabled' => ( $linkedIn ? '1' : '0' )
            ) );
        }

        /**
         * Adds open graph metatags on the Signup Form page to set custom text
         * for Facebook sharer. 
         * 
         * @return string HTML output.
         */
        public function open_graph() {
            $ref_key = $this->get_submitted_key( 'ref' );
            if( $ref_key === false ) {
                return;
            }
            $title = esc_attr( esc_html( vsu_get_setting( 'text', 'social_sharing' ) ) );
            $desc = esc_attr( esc_html( vsu_get_setting( 'promo_text', 'popup_content' ) ) );
            $url = esc_attr( vsu_get_ref_url( $ref_key ) );

            $out = "<meta property='og:type' content='article' />";
            $out .= "<meta property='og:title' content='$title' />";
            $out .= "<meta property='og:description' content='$desc' />";
            $out .= "<meta property='og:url' content='$url' />";

            echo $out;
        }

        /**
         * Checks the Signup Form page for any sign up submissions occured. This
         * would generally be fallback submission when JavaScript is disabled
         * on client side.
         * 
         * Should run before WP 'init' hook.
         * 
         * @global VSU_API $vsu_api
         * @return type
         */
        public function form_action_handler() {
            if( is_admin() ){
                return;
            } // run only for front-end

            $promo_page_id = (int) vsu_get_setting( 'promo_page', 'popup_content' );
            if( ! $promo_page_id ){
                return;
            } // page not set

            $page_id = (int) get_queried_object_id();
            if( ! is_page($page_id) || $page_id !== $promo_page_id ){
                return;
            } // wrong page

            $this->open_graph();

            if( $this->form_submission_occurred() ) {
                $this->signup_handler();
            }
        }

        /**
         * Gets keys from GET request.
         * 
         * @param string $name Which key to get - 'ref' or in future 'confirm'.
         * @return boolean|string The key found, or false if the key was not set. 
         */
        public function get_submitted_key( $name ) {
            $key = filter_input( INPUT_GET, $name );
            if( $key === false || $key === null ) {
                return false;
            }
            return $key;
        }

        /**
         * Checks if signup submission has occured in the page. Should be called
         * before WP 'init' hook.
         * 
         * @return boolean Indicates whether a submission took place.
         */
        public function form_submission_occurred() {
            $email = filter_input( INPUT_POST, 'vsu_email' );
            if( NULL === $email || false === $email ) {
                return false;
            } // no submission

            return true; // submission occured
        }

        /**
         * Handles a request to signup a new user from signup form submission.
         * Should be called before WP 'init' hook.
         * 
         * @global VSU_API $vsu_api API manager.
         * @return void
         */
        public function signup_handler() {
            // Anitspam check
            if( vsu_get_setting( 'antispam_enabled', 'email_form') ) {
                $antispam_check = filter_input( INPUT_POST, 'vsu_antispam' );
                if( ! $antispam_check ) {
                    vsu_temp_setting( 'signup_error', 'antispam_fail' );
                    return;
                }
            }
            $email_address = filter_input( INPUT_POST, 'vsu_email' );
            if( $email_address === '' ){
                vsu_temp_setting( 'signup_error', 'empty_email_address' );
                return;
            } // empty email address

            if( ! is_email( $email_address ) ) {
                vsu_temp_setting( 'signup_error', 'invalid_email_address' );
                return;
            } // invalid email address

            $ref_key = filter_input( INPUT_POST, 'vsu_ref' );

            global $vsu_api;
            $signed_up = $vsu_api->signup( $email_address, $ref_key );        

            if( is_array( $signed_up ) ){
                $signup_action = ( false !== $signed_up['total_signups'] )
                        ? 'pull_user_data' // old user signed up
                        : 'success'; // new user signed up
                vsu_temp_setting( 'signup_action', $signup_action );
                vsu_temp_setting( 'user_data', $signed_up );
                return;
            } // user already signed up

            $signup_actions = array(
                false => 'error',
                -1 => 'wrong_reference_key',
                -2 => 'limit_reached'
            );
            if( isset( $signup_actions[ $signed_up ] ) ) {
                vsu_temp_setting( 'signup_action', $signup_actions[ $signed_up ] );
                return;
            }
        }
    }
}