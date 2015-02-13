<?php
if( ! class_exists( 'VSU_Ajax' ) ) {
    
    /**
     * Registers and responds to AJAX requests.
     * 
     * @package ViralSignUps
     * @subpackage Ajax
     */
    class VSU_Ajax{
        
        /**
         * Fired in WP 'init' hook.
         */
        public static function init() {
            if( current_user_can( 'manage_options' ) ) {
                // save license for admin "Settings" section
                add_action( 'wp_ajax_vsu_save_license', array( 'VSU_Ajax', 'save_license' ) );
                // save account info for admin "Settings" section
                add_action( 'wp_ajax_vsu_save_account', array( 'VSU_Ajax', 'save_account' ) );
                // save settings for admin "Popup Content" section
                add_action( 'wp_ajax_vsu_save_popup_content', array( 'VSU_Ajax', 'save_popup_content' ) );
                // save all settings
                add_action( 'wp_ajax_vsu_save_general', array( 'VSU_Ajax', 'save_general' ) );
                // retrieve signups data
                add_action( 'wp_ajax_vsu_get_signups', array( 'VSU_Ajax', 'get_signups' ) );
            }

            // get retrieve popup content html for both logged in and out users
            add_action( 'wp_ajax_vsu_popup_content', array( 'VSU_Ajax', 'popup_content' ) );
            add_action( 'wp_ajax_nopriv_vsu_popup_content', array( 'VSU_Ajax', 'popup_content' ) );
        }
        
        /**
         * Responds to AJAX requests.
         * 
         * @param string $state Generally 'success' or 'error'.
         * @param string $message Message to display.
         * @param mixed $data Any data attached to the repond.
         */
        public static function ajax_response( $state, $message, $data = '' ) {
            echo json_encode( array(
                'state' => $state,
                'message' => $message,
                'data' => $data
            ) );
            die();
        }
        
        /**
         * Error respond.
         * 
         * @param string $message Message to display.
         * @param mixed $data Any data attached to the repond.
         */
        public static function ajax_error( $message, $data = '' ) {
            VSU_Ajax::ajax_response( 'error', $message, $data );
        }
        
        /**
         * Success respond.
         * 
         * @param string $message Message to display.
         * @param mixed $data Any data attached to the repond.
         */
        public static function ajax_success( $message = '', $data = '' ) {
            if( $message === '' ) {
                $message = __( 'Saved!', 'viralsignups' );
            }
            VSU_Ajax::ajax_response( 'success', $message, $data );
        }

        /**
         * Returns data received from admin settings screen for the current section.
         * 
         * @param string $section_name Section of the admin settings screen.
         * @return array Section data to be saved.
         */
        public static function receive_data( $section_name ) {
            $error_message = __( 'No data received.', 'viralsignups' );

            $_nav_position = filter_input( INPUT_POST, 'position' );
            if( $_nav_position !== null && $_nav_position !== false ) {
                update_option( 'vsu_nav_pos', $_nav_position );
            }

            $data_serialized = filter_input( INPUT_POST, 'data' );
            if( $data_serialized === null || $data_serialized === false ) {
                VSU_Ajax::ajax_error( $error_message );
            } // not set or failed to retrieve

            $data = array();
            parse_str( $data_serialized, $data );
            $data = (array) $data;
            if( ! isset( $data['vsu_admin'][ $section_name ] ) ) {
                VSU_Ajax::ajax_error( $error_message );
            } // data for the section not set
            
            // sanitize magic quotes
            if( get_magic_quotes_gpc() ) {
                $data['vsu_admin'][ $section_name ] = stripslashes_deep( $data['vsu_admin'][ $section_name ] ); 
            }
            return $data['vsu_admin'][ $section_name ];
        }

        /**
         * Retrieves stored settings data. 
         * 
         * @return array Stored data or empty array.
         */
        public static function get_stored_data() {
            return get_option( 'vsu_data', array() );
        }

        /**
         * Replaces saved section data with new data. 
         * 
         * @param string $section_ID Section name.
         * @param array $data Section data.
         */
        public static function save_data( $section_ID, $data ) {
            $saved_data = VSU_Ajax::get_stored_data();
            $saved_data[$section_ID] = $data;
            update_option( 'vsu_data', $saved_data );
        }

# Front-End Action Handlers

        /**
         * Returns popup HTML after signup action.
         * 
         * @global type $vsu_manager
         */
        public static function popup_content() {
            global $vsu_manager;
            $vsu_manager->signup_handler();

            // check for signup errors
            $signup_error = vsu_check_signup_errors();
            if( $signup_error !== false ) {
                VSU_Ajax::ajax_error( $signup_error );
            } // error occurred while signing up, show the form to try again

            // check signup action
            $signup_action = vsu_check_signup_action();
            if( isset( $signup_action[ 'display_popup' ] ) ) {
                $out = vsu_popup_html( $signup_action[ 'display_popup' ] );
                VSU_Ajax::ajax_success( __( 'Success!', 'viralsignups'), array( 'popup_html' => $out ) );
            } // show popup

            if( isset( $signup_action['display_ref_error'] ) ) {
                VSU_Ajax::ajax_error( $signup_action['display_ref_error'], array( 'remove_ref_field' => true ) );
            } // reference key was specified, but not found. Let user to signup for a new account.

            if( isset( $signup_action['display_error'] ) ) {
                VSU_Ajax::ajax_error( $signup_action['display_error'] );
            } // error signing up
            
            VSU_Ajax::ajax_error( __( 'Could not sign up. Please try again later.', 'viralsignups' ) );
        }

# Admin Action Handlers   

        /**
         * General save handler for most admin sections.
         */
        public static function save_general() {
            $section_ID = filter_input( INPUT_POST, 'section_ID' );
            if( ! $section_ID ){
                VSU_Ajax::ajax_error( __( 'Section not found.', 'viralsignups' ) );
            }
            $data = VSU_Ajax::receive_data( $section_ID );
            VSU_Ajax::save_data( $section_ID, $data );
            VSU_Ajax::ajax_success('',$data);
        }
        
        /**
         * Save handler for account details in "Settings" section.
         */
        public static function save_account() {
            $data = VSU_Ajax::receive_data( 'settings' );
            $license_key = vsu_get_setting( 'license_key', 'settings' );
            $api = new VSU_API( $license_key );
            $saved = $api->save_account_details( $data );
            if( isset( $saved['account_details'] ) ) {
                global $vsu_settings;
                $settings = $vsu_settings['settings'];
                $settings = wp_parse_args( $saved['account_details'], $settings );
                $settings['license_key'] = $saved['api_key'];
                $settings['license_key_verified'] = $saved['api_key_status'];
                VSU_Ajax::save_data( 'settings', $settings );
            }
            VSU_Ajax::ajax_success( 'Account Details Saved!', $saved );
        }

        /**
         * Save handler for license field in "Settings" section.
         */
        public static function save_license() {
            $data = VSU_Ajax::receive_data( 'settings' );
            $api_verified = '';

            // license authentication
            $license_key = trim( $data['license_key'] );
            $account_details = false;
            if( (string) $license_key !== '' ) {
                $api = new VSU_API( $license_key );
                $api_verified = $api->authenticate();
                if( $api_verified !== false ) {
                    $account_details = $api->get_account_details();
                    $data = wp_parse_args( $account_details, $data );
                }
            }
            $data['license_key_verified'] = $api_verified;

            VSU_Ajax::save_data( 'settings', $data );
            VSU_Ajax::ajax_success( '', array(
                'api_verified' => $api_verified,
                'account_details' => $account_details
            ) );
        }
        
        /**
         * Save handler for "Popup Content" section.
         */
        public static function save_popup_content() {
            $data = VSU_Ajax::receive_data( 'popup_content' );
            if( isset( $data['promo_text'] ) ) {
                $data['promo_text'] = vsu_short_text( $data['promo_text'] );
            }
            
            // switch free plans
            $credits_were_on = (boolean) vsu_get_setting( 'credit_line_on', 'popup_content' );
            if( ! isset( $data['credit_line_on'] ) ) {
                $data['credit_line_on'] = false;
            }
            $credits_are_on = (boolean) $data['credit_line_on'];
            if( $credits_were_on !== $credits_are_on ) {
                $api = new VSU_API( vsu_get_setting( 'license_key', 'settings' ) );
                $switched = $api->switch_free_plans( $credits_are_on );
                if( ! $switched ) {
                    VSU_Ajax::ajax_error( __( 'Could not switch your free plan.', 'viralsignups' ) );
                }
            }
            
            VSU_Ajax::save_data( 'popup_content', $data );
            vsu_settings_init();
            
            VSU_Ajax::ajax_success( '', array(
                'ref_number' => vsu_get_setting( 'ref_number', 'popup_content' ),
                'promo_text' => vsu_get_setting( 'promo_text', 'popup_content' ),
                'promo_page' => vsu_get_ref_url( 'siK7jl' )
            ) );
        }

        /**
         * Retrieves list of signups for current client.
         * 
         * @global object $vsu_api API manager.
         */
        public static function get_signups() {
            $get_total = (boolean) filter_input( INPUT_POST, 'get_total' );
            $filter = array(
                'action' => filter_input( INPUT_POST, 'filter_action' ),
                'number' => filter_input( INPUT_POST, 'filter_number' )
            );
            $page = filter_input( INPUT_POST, 'page' );
            
            global $vsu_api;
            if( (string) $vsu_api->get_api_key() === '' ) {
                VSU_Ajax::ajax_error( __( 'License key not found. Please specify your license key in the Settings section.', 'viralsignups' ) );
            }
            $retrieved = $vsu_api->get_signups_data( $page, $filter, $get_total );

            if( $retrieved === false ) {
                VSU_Ajax::ajax_error( __( 'Could not retrieve the data. Please try again later.', 'viralsignups' ) );
            }
            VSU_Ajax::ajax_success( __( 'Singups retrieved!', 'viralsignups' ), $retrieved );
        }
    }
}