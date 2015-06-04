<?php

if( ! class_exists( 'VSU_API' ) ) {
    
    /**
     * Handles API calls.
     * 
     * @package ViralSIgnUps
     * @subpackage API
     */
    class VSU_API{
        
        /**
         * string Request URL for API calls.
         */
        const request_url = 'http://viralsignups.com/api/';
        
        /**
         * Current API key.
         *
         * @var string 
         */
        protected $api_key = '';

        /**
         * General contructor.
         * 
         * @param string $api_key API key.
         */
        public function __construct( $api_key = '' ) {
            $this->api_key = $api_key;
        }
        
        /**
         * Getter for API key.
         * @return string API key.
         */
        public function get_api_key() {
            return $this->api_key;
        }

        /**
         * Checks authorization for requests.
         * 
         * @return int|boolean True, if authenticated. False if user does not exist
         * or errors occurred, 0 if user exists but is not valid / active, 1 if
         * user exists but has reached the request limit.
         */
        public function authenticate() {            
            $res = $this->request( array(
                'q' => 'verify'
            ) );
            
            if( isset( $res['verified'] ) ) {
                return true;
            }
            
            if( isset( $res['state'] ) && $res['state'] === 'error' ) {
                if( isset( $res['data']['invalid'] ) ) {
                    return 0; // user not active
                }
                if( isset( $res['data']['limit_reached'] ) ) {
                    return 1; // limit reached
                }
            }
            
            return false; // request error
        }
        
        public function get_account_details() {
            $res = $this->request( array(
                'q' => 'get_account_details'
            ) );

            if( ! $res ) {
                return false;
            }
            
            if( isset( $res['state'] ) && $res['state'] === 'error' ) {
                return false;
            }
            
            return $res;
        }
        
        /**
         * Returns URL to export all signup data. 
         * 
         * @return type
         */
        public function export_url() {
            $url = add_query_arg( array(
                'key' => $this->api_key,
                'q' => 'export',
                'cd' => md5( get_bloginfo( 'url' ) )
            ), VSU_API::request_url );
            return $url;
        }
        
        /**
         * Prepares and fires an API call.
         * 
         * @param array $args Request arguments. Has to have a "key", and "action" or "q".
         * @param string $type Request type - get | post .
         * @return boolean|array False when the call is an error, received json data
         * on success.
         */
        public function request( $args = array(), $type = 'get' ) {
            $args = wp_parse_args( $args, array(
                'key' => $this->api_key,
                'nocache' => wp_generate_password(4) . time()
            ) );
            $url = add_query_arg( $args, VSU_API::request_url );
            $headers = array(
                'user-agent' => get_site_url()
            );
            $res = ( $type === 'get' ) 
                    ? wp_remote_get( $url,
                        array(
                            'method' => 'GET'
                        ) + $headers ) 
                    : wp_remote_post( VSU_API::request_url,
                        array(
                            'method' => 'POST',
                            'body' => $args,
                        ) + $headers );

            if( is_wp_error( $res ) ){
                return false;
            }
            else{
                return json_decode( wp_remote_retrieve_body( $res ), true );
            }
        }

        /**
         * Requests signups data from the API.
         * 
         * @param int $page Pagination number for the data received.
         * @param array $filter Assoc array with filter options:
         * 'action' - filter action, e.g. more_than, less_than, equal etc
         * 'number' - how many items to retrieve.
         * @param boolean $get_total Whether to return total number of signups in
         * the result.
         * @return boolean|array False on error, data on success.
         */
        public function get_signups_data( $page, $filter, $get_total = false ) {
            $res = $this->request( array(
                'q' => 'get_signups_data',
                'page_n' => $page,
                'filter_action' => $filter['action'],
                'filter_number' => $filter['number'],
                'get_total' => $get_total
            ) );

            if( ! $res ) {
                return false;
            }
            
            if( isset( $res['state'] ) && $res['state'] === 'error' ) {
                return false;
            }
            
            return $res;
        }

        /**
         * 
         * Returns:
         * false if error occurred
         * -1 if reference key was passed but not valid
         * data array on success
         * 
         * @param string $email_address Email address.
         * @param mixed $ref_key Non empty string would be passed as a reference key.
         */
        /**
         * Request to signup a user.
         * 
         * @param string $email_address User email address.
         * @param string $ref_key User referral key.
         * @return boolean|int|array False if error occurred. When the passed 
         * referral key is not valid, the result returned is -1, when the limit
         * is reached, result is -2. On success, 
         * the following signup data are returned:
         * 'total_signups' => Number of signups the user already has referred. For
         * newly signed up users this is set to false.
         * 'ref_key' => The referance key for this user.
         */
        public function signup( $email_address, $ref_key ) {
            $http_ref = (string) filter_input( INPUT_POST, 'http_ref' );
            $ip = (string) filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
            $res = $this->request( array(
                'action' => 'signup',
                'email' => $email_address,
                'ref_key' => $ref_key,
                'http_ref' => $http_ref,
                'ip' => $ip,
                'autoresponder_settings' => array(
                    'from_address' => vsu_get_setting( 'from_address', 'autoresponders' ),
                    'from_name' => vsu_get_setting( 'from_name', 'autoresponders' ),
                    'ref_number' => vsu_get_setting( 'ref_number', 'popup_content' ),
                    'promo_text' => vsu_get_setting( 'promo_text', 'popup_content' ),
                    'promo_page_url' => vsu_get_ref_url(),
                    'signup_full_text' => vsu_get_setting( 'signup_full_text', 'autoresponders' ),
                    'credit_line_on' => vsu_get_setting( 'credit_line_on', 'popup_content' )
                )
            ), 'post' );

            if( ! $res ) {
                return false;
            }
            
            if( isset( $res['state'] ) && $res['state'] === 'error' ) {
                if( isset( $res['data']['invalid_ref_key'] ) ) {
                    return -1;
                }
                if( isset( $res['data']['limit_reached'] ) ) {
                    return -2;
                }
                return false;
            }
            
            return $res;
        }
        
        /**
         * Saves user account details. The account details has to have at least 
         * 'email' and 'domain' provided. Checks to see if the account with 
         * provided API key, email or domain exists. If found, performs an account
         * update and returns updated account data. If the account is not registered,
         * creates a new account and returns new account data ( including the new 
         * API key ).
         * 
         * @param array $account_data Assoc array of account data ( name, domain etc )
         * to be saved.
         * @return Assoc array. Saved account data on success, '_error' set to an error
         * message on failure.
         */
        public function save_account_details( $account_data ) {
            $res = $this->request( array(
                'action' => 'save_account',
                'data' => $account_data
                ), 'post'
            );
            return $res;
        }
        
        /**
         * Switcher Free Membership plans
         * @param boolean $credits_enabled Whether credits are enabled.
         */
        public function switch_free_plans( $credits_enabled ) {
            $res = $this->request( array(
                'action' => 'switch_free_plans',
                'credits_enabled' => $credits_enabled
            ), 'post' );
            
            if( ! $res ) {
                return false;
            }
            
            if( isset( $res['state'] ) && $res['state'] === 'error' ) {
                return false;
            }
            return true;
        }
    }
}