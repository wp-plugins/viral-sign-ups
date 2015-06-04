<?php

if( ! class_exists( 'VSU_Admin_Manager' ) ) {

    /**
     * ViralSignUps plugin back-end manager.
     * 
     * @package ViralSignUps
     * @subpackage Admin
     */
    class VSU_Admin_Manager{

        /**
         * @var array Assoc array with setting sections ( section_id => section_name ).
         */
        public $sections = array();
        
        /**
         * @var array Merge of default and saved settings.
         */
        public $data = array();
        
        /**
         * @var int Indicates which section is currently active.
         */
        public $nav_postion = 0;

        /**
         * Initializes sections.
         */
        public function __construct() {
            $this->sections = array(
                'settings' => __( 'Settings', 'viralsignups' ),
                'email_form' => __( 'Email Form', 'viralsignups' ),
                'popup_content' => __( 'Popup Content', 'viralsignups' ),
                'social_sharing' => __( 'Social Sharing', 'viralsignups' ),
                'autoresponders' => __( 'Email Auto Replies', 'viralsignups' ),
                'shortcode' => __( 'Short Code', 'viralsignups' ),
                'signups' => __( 'Sign Ups', 'viralsignups' ),
                'support' => __( 'Feedback & Support', 'viralsignups' )
            );
        }
        
        /**
         * Fired on WP 'init' hook when on admin side.
         */
        public function init() {
            add_action( 'admin_menu', array( $this, 'menu' ) );
        }

# Admin Menus

        /**
         * Registers admin menus.
         */
        public function menu() {
            $settings_page = add_menu_page( __( 'Viral Sign Ups', 'viralsignups' ),
                    __( 'Viral Sign Ups', 'viralsignups' ), 
                    'manage_options',
                    'vsu_settings',
                    array( 'VSU_Admin_Manager', 'settings_page' ),
                    VSU_ASSETS_ADMIN_URI . 'img/menu_icon.png' );

            add_action('admin_print_scripts-' . $settings_page, array( $this, 'scripts' ) );
        }

# Settings Page

        /**
         * Sets up data to be used in admin Settings page.
         * 
         * @global array $vsu_settings
         */
        public function init_data() {
            global $vsu_settings;
            $this->data = $vsu_settings;
            $this->nav_postion = (int) get_option( 'vsu_nav_pos', 0 );
        }

        /**
         * Enqueues all styles and scripts to be used in admin Settings page.
         */
        public function scripts() {
            wp_enqueue_style( 'vsu-admin-gfonts', 'http://fonts.googleapis.com/css?family=Lato:400,300' );
            wp_enqueue_style( 'wp-color-picker' ); 
            wp_enqueue_style( 'vsu-admin-style', VSU_ASSETS_ADMIN_URI . 'css/style.css' );
            wp_enqueue_style( 'vsu-signup-form-style', VSU_ASSETS_URI . 'css/signup_form.css' );
            wp_enqueue_script( 'wp-color-picker');
            wp_enqueue_script( 'vsu-admin-script', VSU_ASSETS_ADMIN_URI . 'js/custom.js', array( 'jquery' ), '0.1', true );
            wp_localize_script( 'vsu-admin-script', 'VSU_Admin', array(
                'signup_page_email_label' => __( 'Email', 'viralsignups' ),
                'signup_page_refer_label' => __( 'Referring site', 'viralsignups' ),
                'signup_page_empty_referrer_site' => __( 'None.', 'viralsignups' ),
                'loading_text' => __( 'Loading...', 'viralsignups' ),
                'antispam' => __( 'Are you human?', 'viralsignups'),
                'no_results_text' => __( 'No data found.', 'viralsignups'),
            ) );
        }

# Outputters

        /**
         * Prepare and output admin Settings page.
         * 
         * @global object $vsu_admin_manager
         */
        public static function settings_page() {
            global $vsu_admin_manager;
            $vsu_admin_manager->init_data();

            // header
            $header = $vsu_admin_manager->settings_page_header();
            // left side navigation
            $navigation = $vsu_admin_manager->settings_page_navigation();
            // main content area
            $main_content = $vsu_admin_manager->settings_page_sections();
            $main_content = "<div class='vsu-admin-main-content'>$main_content</div>";
            // wrap content and navigation
            $inner = "<div class='vsu-admin-wrap-inner'>$navigation $main_content</div>";
            $inner .= "<input type='hidden' class='vsu-admin-position' value='$vsu_admin_manager->nav_postion'/>";
            // wrap all
            $out = "<div class='vsu-admin-wrap cleanslate'>$header $inner</div>";

            echo $out;
        }

        /**
         * @return string Output of header area of admin Settings page.
         */
        public function settings_page_header() {
            $img_url = VSU_ASSETS_ADMIN_URI . 'img/logo_white.png';
            $url = VSU_WEBSITE;

            $img = "<img class='vsu-admin-logo' src='$img_url' />";
            $logo = "<a class='vsu-admin-logo-link' href='$url' target='_blank'>$img</a>";
            $out = "<div class='vsu-admin-header'>$logo</div>";

            return $out;
        }

        /**
         * @return string Output of left side navigation area of admin Settings
         * page.
         */
        public function settings_page_navigation() {
            $sections = '';
            foreach( $this->sections as $item_ID => $item_name ) {
                $sections .= "<li><a href='#vsu-admin-section-$item_ID'>$item_name</a></li>";
            }
            
            $buy_now_text = __( 'Buy Pro Version', 'viralsignups' );
            $pricings_url = esc_attr( VSU_UPGRADE_MEMBERSHIP_URL );
            $sections .= "<li><a href='$pricings_url' class='vsu-admin-button' target='_blank'>$buy_now_text</a></li>";
            
            $out = "<ul class='vsu-admin-navigation'>$sections</ul>";
            $out = "<div class='vsu-admin-navigation-wrap'>$out</div>";

            return $out;
        }

        /**
         * @return string Output of main content area of admin Settings page.
         */
        public function settings_page_sections() {
            $out = '';
            foreach( $this->sections as $section_ID => $section_title ) {
                $section_callback = array( $this, 'section_' . $section_ID . '_html' );
                if( is_callable( $section_callback ) ) {
                    $section_html = call_user_func( $section_callback, $section_title, $section_ID );
                    $out .= "<div id='vsu-admin-section-$section_ID' class='vsu-admin-section'>$section_html</div>";
                }
            }

            return $out;
        }

# Field Outputters

        /**
         * StepInfo Field. Outputs a number with some info regarding the current
         * step.
         * 
         * @param int $step_number Step number.
         * @param string $text Info about the step.
         * @return string HTML output.
         */
        public function step_info_field( $step_number, $text ) {
            $step_number = (int) $step_number;

            $out =  "<div class='vsu-admin-stepinfo-number-wrap'>"
                        . "<span class='vsu-admin-stepinfo-number'>$step_number</span>"
                    . "</div>"
                    . "<p class='vsu-admin-stepinfo-text'>$text</p>";
            $out = $this->field_wrap( $out, 'vsu-admin-stepinfo' );

            return $out;
        }

        /**
         * License field. A text field with a license verification ticker and 
         * links to retrieve the license key. 
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function license_field( $section_id, $field_id, $label = '' ) {
            $attrs = ' class="vsu-admin-field-verify"';
            $verified = $this->get_value( $section_id, 'license_key_verified' );
            if( $verified !== '' && $verified !== null ){
                if( $verified === 0 ){
                    $verified = 'limit_reached';
                }
                else{
                    $verified = $verified ? 'verified' : 'unverified';
                }
                $attrs .= " data-vsu-admin-state='$verified'";
            }
            
            $out = $this->text_field( $section_id, $field_id, $label, $attrs );
            
            // limit reached text
            $upgrade_url = esc_attr( VSU_UPGRADE_MEMBERSHIP_URL );
            $limit_reached = sprintf( 
                                __( 'Want to %supgrade%s?', 'viralsignups' ),
                                "<a href='$upgrade_url' target='_blank'>", "</a>" );
            $out .= "<span class='vsu-admin-upgrade-text vsu-admin-field-desc'>$limit_reached</span>";
            
            // find license key text
            $my_account_url = esc_attr( VSU_MY_ACCOUNT_URL );
            $help_text = sprintf( 
                            __( 'Manage your account or find your License key %shere%s.', 'viralsignups' ), 
                            "<a href='$my_account_url' target='_blank'>", "</a>" );
            $out .= "<p class='vsu-admin-field-desc'>$help_text</p>";
            return $out;
        }

        /**
         * Colorpicker field.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function color_field( $section_id, $field_id, $label = '' ) {
            $attrs = ' class="vsu-admin-color-picker"';
            $out = $this->text_field( $section_id, $field_id, $label, $attrs );

            return $out;
        }

        /**
         * Textarea with 4 rows. 
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @param string $attrs Extra textarea attributes.
         * @return string Field output.
         */
        public function textarea( $section_id, $field_id, $label = '', $attrs = '' ) {
            $id = $this->get_id( $section_id, $field_id );
            $name = $this->get_name( $section_id, $field_id );
            $value = esc_textarea( $this->get_value( $section_id, $field_id ) );
            if( $label !== '' ) {
                $label = "<label for='$id'>$label</label>"; 
            }

            $out = "$label<textarea rows='4' cols='50' id='$id' name='$name'$attrs>$value</textarea>";

            return $out;
        }

        /**
         * General text field.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @param string $attrs Extra text field attributes.
         * @return string Field output.
         */
        public function text_field( $section_id, $field_id, $label = '', $attrs = '' ) {
            $id = $this->get_id( $section_id, $field_id );
            $name = $this->get_name( $section_id, $field_id );
            $value = esc_attr( $this->get_value( $section_id, $field_id ) );
            if( $label !== '' ) {
                $label = "<label for='$id'>$label</label>"; 
            }

            $out = "$label<input type='text' id='$id' name='$name' value='$value'$attrs/>";

            return $out;
        }
        
        /**
         * General password field.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function password_field( $section_id, $field_id, $label = '' ) {
            $id = $this->get_id( $section_id, $field_id );
            $name = $this->get_name( $section_id, $field_id );
            $value = esc_attr( $this->get_value( $section_id, $field_id ) );
            if( $label !== '' ) {
                $label = "<label for='$id'>$label</label>"; 
            }

            $out = "$label<input type='password' id='$id' name='$name' value='$value'/>";

            return $out;
        }

        /**
         * Custom checkbox field with a ticker.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function tickbox( $section_id, $field_id, $label = '' ) {
            $id = $this->get_id( $section_id, $field_id );
            $name = $this->get_name( $section_id, $field_id );
            $checked = checked( 'yes', $this->get_value( $section_id, $field_id ), false );

            $out = "<input id='$id' class='vsu-admin-custom-checkbox' type='checkbox' name='$name' value='yes' $checked/>"
                    . "<div class='vsu-admin-tickbox'></div>";
             if( $label !== '' ) {
                $out = "<label class='vsu-admin-label-small' for='$id'>$label $out</label>"; 
            }

            return $out;
        }

        /**
         * Text field that selects all of its contents on click.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function copy_text_field( $section_id, $field_id, $label = '' ) {
            $id = $this->get_id( $section_id, $field_id );
            $value = esc_attr( $this->get_value( $section_id, $field_id ) );
            if( $label !== '' ) {
                $label = "<label for='$id'>$label</label>"; 
            }
            $out = "$label<input type='text' id='$id' class='vsu-admin-input-copy' value='$value' readonly='readonly' />";

            return $out;
        }

        /**
         * Small text field.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function small_text_field( $section_id, $field_id, $label = '' ) {
            return $this->text_field( $section_id, $field_id, $label, " class='vsu-admin-input-small'" );
        }

        /**
         * Text field that stretches to its parent.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @param string $desc Description text.
         * @param string $attrs Extra text field attributes.
         * @return string Field output.
         */
        public function long_text_field( $section_id, $field_id, $label = '', $desc = '', $attrs = '' ) {
            $out = $this->text_field( $section_id, $field_id, $label, " class='vsu-admin-input-long'" . $attrs );
            if( $desc !== '' ){
                $out .= "<p class='vsu-admin-field-desc'>$desc</p>";
            }

            return $out;
        }
        
        /**
         * Checkbox field.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function checkbox( $section_id, $field_id, $label ) {
            $id = $this->get_id( $section_id, $field_id );
            $name = $this->get_name( $section_id, $field_id );
            $checked = checked( 'yes', $this->get_value( $section_id, $field_id ), false );
            $checkbox = "<input type='checkbox' id='$id' name='$name' value='yes' $checked />";
            $out = "<label for='$id'>$checkbox $label</label>"; 
            
            return $out;
        }

        /**
         * A dropdown select box to pick a WP Page.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @param string $label Label to output before the field.
         * @return string Field output.
         */
        public function page_dropdown( $section_id, $field_id, $label = '' ) {
            $id = $this->get_id( $section_id, $field_id );
            $name = $this->get_name( $section_id, $field_id );
            $value = esc_attr( $this->get_value( $section_id, $field_id ) );
            if( $label !== '' ) {
                $label = "<label for='$id'>$label</label>"; 
            }

            $pages = wp_dropdown_pages( array(
                'id' => $id,
                'name' => $name,
                'selected' => $value,
                'echo' => false,
                'show_option_none' => __( 'None', 'viralsignups')
            ) );
            
            $out = $label . $pages;
            return $out;
        }

        /**
         * Generates an escaped name to be used in field inputs.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @return string Field name.
         */
        public function get_name( $section_id, $field_id ) {
            return esc_attr( "vsu_admin[$section_id][$field_id]" );
        }
        /**
         * Generates an escaped ID to be used in field inputs.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @return string Field ID.
         */
        public function get_id( $section_id, $field_id, $sep = '-' ) {
            return esc_attr( "vsu_admin$sep$section_id$sep$field_id" );
        }
        /**
         * Retrives the value of the field from data. Not escaped.
         * 
         * @param string $section_id Current section ID.
         * @param string $field_id Current field ID.
         * @return string Field value.
         */
        public function get_value( $section_id, $field_id ) {
            if( ! isset( $this->data[$section_id][$field_id] ) ) {
                return;
            }
            return $this->data[$section_id][$field_id];
        }

        /**
         * Puts a field into a wrapper.
         * 
         * @param string $out Field HTML.
         * @param string $class Any extra classes to apply to field wrapper.
         * @return string Wrapped field output.
         */
        public function field_wrap( $out, $class = '' ) {
            if( $class !== '' ) {
                $class = esc_attr( ' ' . $class );
            }
            $out = "<div class='vsu-admin-field$class'>$out</div>";
            return $out;
        }

        /**
         * Submit button.
         * 
         * @param string $text Text of the button. Default: 'Save & Continue'.
         * @return string Button output.
         */
        public function submit_button( $text = '', $classes = '', $wrap = true ) {
            if( $text === '' ) {
                $text = esc_attr__( 'Save & Continue', 'viralsignups' );
            }
            else{
                $text = esc_attr( $text );
            }

            $out = "<input type='submit' class='vsu-admin-button $classes' value='$text'/>";
            if( ! $wrap ) {
                return $out;
            }
            return "<div class='vsu-admin-submit'>$out</div>" ;
        }

        /**
         * General button.
         * 
         * @param string $text Button text.
         * @param string $href Link for the button. Set to false to display an
         * input button.
         * @param string $classes Extra classes to apply to the button.
         * @return string Button output.
         */
        public function button( $text, $href = '#', $classes = '' ) {
            if( $classes !== '' ){
                $classes = esc_attr( ' ' . (string) $classes );
            }
            if( $href === false ) {
                $text = esc_attr($text);
                return "<input type='button' class='vsu-admin-button$classes' value='$text'/>";
            }

            $href = esc_attr( $href );
            return "<a href='$href' class='vsu-admin-button$classes'>$text</a>" ;
        }

# Sections

        /**
         * Section heading. Generally include a title and optionally a description.
         * 
         * @param string $title Section title.
         * @param string $desc Section description.
         * @return string Heading output.
         */
        public function section_heading( $title, $desc = '' ) {
            $title = "<h2 class='vsu-admin-section-title'>$title</h2>";
            if( $desc !== '' ) {
                $desc = "<p class='vsu-admin-section-desc'>$desc</p>";
            }
            $heading = "<div class='vsu-admin-section-heading'>$title $desc</div>";

            return $heading;
        }

        /**
         * 'Settings' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_settings_html( $title, $section_id ) {               
            // heading
            $heading = $this->section_heading( $title, 
                    __( "To register your plugin please enter your details below"
                            . " and press the 'Save Account' button. Your License"
                            . " key will then be created.",
                            'viralsignups' ) );

            // create new account
            $create_account_title = __( 'Create New Account', 'viralsignups' );
            $create_account_title = "<h3 class='vsu-admin-label'>$create_account_title</h3><hr/>";
            $create_new = $create_account_title;
            
            $create_new .= $this->field_wrap( 
                        $this->text_field( $section_id, 'first_name',
                            __( 'First Name', 'viralsignups' ) 
                        )
                    );
            $create_new .= $this->field_wrap( 
                        $this->text_field( $section_id, 'last_name',
                            __( 'Last Name', 'viralsignups' ) 
                        )
                    );
            $create_new .= $this->field_wrap( 
                        $this->text_field( $section_id, 'domain',
                            __( 'Domain', 'viralsignups' ) 
                        )
                    );
            // @todo email field
            $create_new .= $this->field_wrap( 
                        $this->text_field( $section_id, 'email',
                            __( 'Email', 'viralsignups' ) 
                        )
                    );
            // @todo password field
            $create_new .= $this->field_wrap( 
                        $this->password_field( $section_id, 'password',
                            __( 'Password', 'viralsignups' ) 
                        )
                    );

            // license key
            $license = $this->field_wrap( 
                        $this->license_field( $section_id, 'license_key',
                            __( 'Your license key', 'viralsignups' ) 
                        )
                    );
            
            // license form
            $license_submit = $this->submit_button( __( 'Authenticate', 'viralsignups' ) );
            $action = esc_attr( add_query_arg( 'action', 'save_license' ) );
            $license =  "<form id='vsu-admin-form-$section_id-license' action='$action' method='post'>"
                                . " $license $license_submit"
                            . "</form>";
            // create new account form
            $create_new_submit = $this->submit_button( __( 'Save Account', 'viralsignups' ) );
            $action = esc_attr( add_query_arg( 'action', 'save_account' ) );
            $create_new =  "<form id='vsu-admin-form-$section_id-account' action='$action' method='post'>"
                                . " $create_new $create_new_submit"
                            . "</form>";
            
            // put together
            $out =  "$heading<div class='vsu-admin-row'>"
                                . "<div class='vsu-admin-col-half'>$create_new</div>"
                                . "<div class='vsu-admin-col-half'>$license</div>"
                            . "</div>";
            
            

            return $out;
        }

        /**
         * 'Email Form' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_email_form_html( $title, $section_id ) {
            // heading
            $heading = $this->section_heading( $title,
                    __( 'This will be the email form you place onto your page with a ‘short code’ for users to sign up.',
                            'viralsignups' ) );

            // Button Text
            $button_text_field = $this->field_wrap( $this->text_field( $section_id, 'button_text', 
                    __( 'Enter the text for your sign up button: ', 'viralsignups' ) ) );

            // Antispam Enable
            $antispam_enable_field = $this->field_wrap( $this->tickbox( $section_id, 'antispam_enabled', 
                    __( 'Turn on Anti Spam feature? ', 'viralsignups' ) ) );

            // Button Color
            $button_color_field = $this->field_wrap( $this->color_field( $section_id, 'button_color', 
                    __( 'Choose a colour for your button:', 'viralsignups' ) ) );

            $form_fields =  "<div class='vsu-admin-row'>"
                                . "<div class='vsu-admin-col-half'>$button_text_field $antispam_enable_field</div>"
                                . "<div class='vsu-admin-col-half'>$button_color_field</div>"
                            . "</div>";

            // Preview
            $preview_data = array(
                'email_name' => $this->get_name( $section_id, 'email_text' )
            );

            $preview =  "<div id='vsu-admin-signup-form-preview'>"
                            . '<h3 class="vsu-admin-preview-title">' . __( 'Preview', 'viralsignups' ) . '</h3>'
                            . vsu_form_html( 'preview', $preview_data )
                        . "</div>";

            // submit
            $submit = $this->submit_button();

            // form
            $action = esc_attr( add_query_arg( 'action', 'save' ) );
            $out = "<form id='vsu-admin-form-$section_id' action='$action' method='post' class='vsu-admin-form' data-vsu-section-id='$section_id'>"
                    . "$heading $form_fields $preview $submit"
                    . "</form>";

            return $out;
        }

        /**
         * 'Popup Content' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_popup_content_html( $title, $section_id ) {
            // heading
            $heading = $this->section_heading( $title );

            // number
            $number_field_text = sprintf(
                    __( "If someone gets at least %s friends to sign up, I'll give them:", 'viralsignups' ),
                    $this->small_text_field( 'popup_content', 'ref_number' ) );
            $number_field = "<p class='vsu-admin-label'>$number_field_text</p>";

            // promo text
            $promo_text = $this->field_wrap( $this->long_text_field( 'popup_content', 'promo_text', '', 
                    sprintf( __( '(max characters: %s)', 'viralsignups' ), '164' ), ' maxlength="164"' ) );

            // promo page
            $promo_page = $this->field_wrap( $this->page_dropdown( 'popup_content', 'promo_page', 
                    __( 'My sign up form is on this web page:', 'viralsignups') ) );
            
            // enable credits
            $enable_credits = $this->field_wrap( $this->checkbox( 'popup_content', 'credit_line_on', 
                    __( "Enable 'Powered by' link", 'viralsignups') ) );
            $extend_url = VSU_EXTEND_FREE_MEMBERSHIP_URL;
            $learn_more = __( "(You can collect a maximum of 10 emails. To increase this to 10,000 emails per month enable the 'Powered by' link above)", 'viralsignups' );
            $enable_credits .= "<p class='vsu-admin-field-desc'>$learn_more</p>";
            
            $field_row =  "<div class='vsu-admin-row'>"
                            . "<div class='vsu-admin-col-half'>$promo_page</div>"
                            . "<div class='vsu-admin-col-half'>$enable_credits</div>"
                        . "</div>";

            // submit
            $submit = $this->submit_button();

            // form
            $action = esc_attr( add_query_arg( 'action', 'save' ) );
            $out =  "<form id='vsu-admin-form-$section_id' class='vsu-admin-form' action='$action' method='post'>"
                        . "$heading $number_field $promo_text $field_row $submit"
                    . "</form>";
            
            // preview
            $preview =  "<div id='vsu-admin-popup-content-preview'>"
                            . '<h3 class="vsu-admin-preview-title">' . __( 'Preview', 'viralsignups' ) . '</h3>'
                            . vsu_popup_html( 'thank_you', 'preview' )
                        . "</div>";

            return $out . $preview;
        }

        /**
         * 'Social Sharing' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_social_sharing_html( $title, $section_id ) {
            // heading
            $heading = $this->section_heading( $title,
                    __( "This will be the text that will be posted onto the users Facebook wall for example if they choose to share their unique link via one of the social share buttons in the popup.",
                            'viralsignups' ) );

            // social sharing text
            $social_share_text = $this->field_wrap( $this->long_text_field( $section_id, 'text' ) );

            // submit
            $submit = $this->submit_button();

            // form
            $action = esc_attr( add_query_arg( 'action', 'save' ) );
            $out =  "<form id='vsu-admin-form-$section_id' class='vsu-admin-form' action='$action' method='post' data-vsu-section-id='$section_id'>"
                        . "$heading $social_share_text $submit"
                    . "</form>";

            return $out;
        }

        /**
         * 'Shortcode' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_shortcode_html( $title, $section_id ) {
            // heading
            $heading = $this->section_heading( $title );

            // shortcode
            $shortcode_field = $this->field_wrap( $this->copy_text_field( $section_id, 'signup_form', 
                    __( 'Copy and paste this short code into your page where you would like the sign up', 'viralsignups' ) ) );

            $out = $heading . $shortcode_field;
            return $out;
        }

        /**
         * 'Support' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_support_html( $title, $section_id ) {
            // heading
            $heading = $this->section_heading( $title );

            // support embed
            $support_embed = '<!-- UserVoice JavaScript SDK (only needed once on a page) -->'
                    . '<script>'
                        . '(function(){'
                            . 'var uv=document.createElement("script");'
                            . 'uv.type="text/javascript";'
                            . 'uv.async=true;'
                            . 'uv.src="//widget.uservoice.com/0JERJjPMw1nlVJGAXuOzGw.js";'
                            . 'var s=document.getElementsByTagName("script")[0];'
                            . 's.parentNode.insertBefore(uv,s)'
                        . '})()'
                    . '</script>'
                    . '<!-- The Classic Widget will be embeded wherever this div is placed -->'
                    . '<div data-uv-inline="classic_widget" data-uv-mode="full"'
                    . ' data-uv-primary-color="#0575bf" data-uv-link-color="#31ba8a"'
                    . ' data-uv-default-mode="support" data-uv-forum-id="266844"'
                    . ' data-uv-width="100%" data-uv-height="550px"></div>';
            
            $out = $heading . $support_embed;
            return $out;
        }

        /**
         * 'Autoresponders' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_autoresponders_html( $title, $section_id ) {
            // heading
            $heading = $this->section_heading( $title,
                    __( 'These will be the auto reply emails your customer will get when they sign up. If they choose to share your link, then they will receive further mails showing how many people have signed up through their referral link.', 'viralsignups' ) );

            // from name
            $from_name_field = $this->field_wrap( 
                        $this->text_field( $section_id, 'from_name', 
                            __( 'Auto reply ‘from’ name', 'viralsignups' )
                        ) 
                    );
            // from address
            $from_address_field = $this->field_wrap( 
                        $this->text_field( $section_id, 'from_address', 
                            __( 'Auto reply ‘from’ email address', 'viralsignups' )
                        ) 
                    );
            $from_fields =  "<div class='vsu-admin-row'>"
                        . "<div class='vsu-admin-col-half'>$from_name_field</div>"
                        . "<div class='vsu-admin-col-half'>$from_address_field</div>"
                    . "</div>";

            // successfull signup
            $info = $this->step_info_field( '1', __( 'When your customer signs up they will receive this email reply from you.', 'viralsignups' ) );
            $preview = VSU_Autoreponder::template_thank_you( 'siK7jl' );
            $preview_1 =  "<div class='vsu-admin-row'>"
                            . "<div class='vsu-admin-col-half'>$info</div>"
                            . "<div class='vsu-admin-col-half'>$preview</div>"
                        . "</div>";

            // new signup
            $info = $this->step_info_field( '2', __( 'If your customer has shared their unique link and another person signs up through it, then your customer will receive a series of emails showing their progress. For example: 1/5, 2/5, 3/5 etc.', 'viralsignups' ) );
            $preview = VSU_Autoreponder::template_signup_progress( 1, 'siK7jl' );
            $preview_2 =  "<div class='vsu-admin-row'>"
                            . "<div class='vsu-admin-col-half'>$info</div>"
                            . "<div class='vsu-admin-col-half'>$preview</div>"
                        . "</div>";

            // all signups
            $info = $this->step_info_field( '3', __( 'This will be the final email they will receive once they have signed up the correct amount of people. Edit the body text in this email to let them know what will happen next.', 'viralsignups' ) );
            $preview = VSU_Autoreponder::template_signup_full( 'siK7jl' );
            $signup_full_field = $this->field_wrap(
                                    $this->textarea( $section_id, 'signup_full_text',
                                            __( 'Edit email body text above.', 'viralsignups'),
                                            ' class="vsu-admin-input-long" data-preview-container="vsu-preview-signup-full-text"'
                                        )
                                    );
            $preview_3 =  "<div class='vsu-admin-row'>"
                            . "<div class='vsu-admin-col-half'>$info</div>"
                            . "<div class='vsu-admin-col-half'>"
                                . "<div id='vsu-admin-email-3-preview'>$preview</div>$signup_full_field</div>"
                        . "</div>";

             // submit
            $submit = $this->submit_button();

            // form
            $action = esc_attr( add_query_arg( 'action', 'save' ) );
            $out =  "<form id='vsu-admin-form-$section_id' class='vsu-admin-form' action='$action' method='post' data-vsu-section-id='$section_id'>"
                        . "$heading $from_fields $preview_1 $preview_2 $preview_3 $submit"
                    . "</form>";

            return $out;
        }

        /**
         * 'Sign Ups' section output.
         * 
         * @param string $title Section title
         * @param string $section_id Section ID.
         * @return string Section output.
         */
        public function section_signups_html( $title, $section_id ) {        
            // heading
            $heading = $this->section_heading( $title,
                        sprintf( 
                            __( 'Total: %s  with %s referred.', 'viralsignups' ),
                            "<span class='vsu-admin-total-signups-number'>0</span>",
                            "<span class='vsu-admin-total-shared-number'>0</span>"
                        ) 
                    );

            // subtitle
            $subtitle = __( 'Congratulations, these people have already signed up:', 'viralsignups' );
            $subtitle = "<p class='vsu-admin-subtitle'>$subtitle</p>";

            // signup filter
            $filter_label = __( 'Filter:', 'viralsignups' );
            $filter_actions = array(
                'all' => __( 'All', 'viralsignups' ),
                'more_than' => __( 'More Than', 'viralsignups' ),
                'less_than' => __( 'Less Than', 'viralsignups' ),
                'exactly' => __( 'Exactly', 'viralsignups' )
            );
            $filter_dropdown = '';
            foreach( $filter_actions as $_filter_action => $_filter_label ) {
                $filter_dropdown .= "<option value='$_filter_action'>$_filter_label</option>";
            }
            $filter_dropdown = "<select name='vsu-filter[action]'>$filter_dropdown</select>";
            $filter_number = '<input type="text" name="vsu-filter[number]" value="" class="vsu-admin-input-small">';
            $number_label = __( 'number of referrers.', 'viralsignups' );
            $filter_button = $this->submit_button( __( 'Filter', 'mvp' ), 'vsu-admin-filter', false );

            $filter =   "<div class='vsu-admin-filters'>"
                            . "<form id='vsu-admin-signup-filter-form' action='#' method='post'>"
                                . "<span class='vsu-admin-filter-label'>$filter_label</span>"
                                . $filter_dropdown . $filter_number
                                . "<span class='vsu-admin-filter-label'>$number_label</span>"
                                . $filter_button
                            . "</form>"
                        . "</div>";

            // export button
            global $vsu_api;
            $url = esc_attr( $vsu_api->export_url() );
            $base = esc_attr( add_query_arg( array( 
                'q' => 'export',
                'cd' => md5( get_bloginfo( 'url' ) )
                ), VSU_API::request_url ) );
            $button_text = __( 'Export CSV', 'viralsignups' );
            $export = "<a href='$url' data-export-base='$base' target='_blank' class='vsu-admin-button'>$button_text</a>";

            $table_top = "<div class='vsu-admin-signups-header'>$filter</div>";

            // Contents
            // location, ip address, http referrer, time

            // load more button
            $load_more_button = $this->button( __( 'Load More', 'viralsignups' ), false, 'vsu-admin-button-secondary vsu-admin-load-more-button' );
            $footer = "<div class='vsu-signups-footer'>$load_more_button</div>";

            $out = $heading . $subtitle . $table_top . $footer;
            return $out;
        }
    }
}