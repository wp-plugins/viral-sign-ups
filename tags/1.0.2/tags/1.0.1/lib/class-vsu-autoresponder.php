<?php
if( ! class_exists( 'VSU_Autoreponder' ) ) {
    
    /**
     * Autoresponder manager.
     * 
     * @package ViralSignUps
     * @subpackage Autoresponder
     */
    class VSU_Autoreponder{

# Template Helpers

        /**
         * Generates footer of email templates: Unsub link and credit lines.
         * 
         * @return string Footer output.
         */
        public static function footer() {
//            $unsub_text = __( 'To stop receiving emails about sign ups, click here.', 'viralsignups' );
            $website_url = esc_attr( VSU_WEBSITE );
            $logo_url =  esc_attr( VSU_ASSETS_URI . 'img/logo_small.png' );
            $logo = "<img src='$logo_url' alt='ViralSignUps logo' style='vertical-align: middle; height: 12px; width: auto;border:0;' />";
            $powered_by = sprintf( 
                    __( 'Powered by: %s', 'viralsignups' ), 
                    "<a href='$website_url' target='_blank' style='font:inherit; text-decoration: none; color:#299cd1;'>$logo</a>"
                );
            $out = '<tr class="vsu-powered-by">
                        <td>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr>'.
//                                    <td width="65%" style="padding: 10px 6px 6px 16px;"><p style="margin: 0; text-align: left; font: 300 10px/20px Lato, Arial, sans-serif; color: #39455b;">' . $unsub_text . '</p></td>
                                    '<td style="padding: 0 16px 6px 6px;"><p style="margin: 0; text-align: right; font: 300 12px/20px Lato, Arial, sans-serif; color: #39455b;">' . $powered_by . '</p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
            
            return $out;
        }

        /**
         * Appends the content with a referral URL and wraps all in a table.
         * 
         * @param string $content Table rows.
         * @param string $ref_key Referral key.
         * @return string Table output.
         */
        public static function table( $content, $ref_key ) {
            $content .= VSU_Autoreponder::ref_link( $ref_key );
            $out =  '<table align="center" border="0" cellpadding="0" cellspacing="0" width="500" style="border-collapse: collapse; border: 1px solid #f0eff2;margin: 0 auto; background-color: #FFF;">'
                        . $content
                        . VSU_Autoreponder::footer()
                    . '</table>';
            return $out;
        }

        /**
         * Outputs a row with highlighted text.
         * 
         * @param string $text Text to highlight.
         * @return Row output.
         */
        public static function text_highlighted( $text ){
            $out = "<p style='margin: 0; text-align: center; font: 300 18px/1.4 Lato, Arial, sans-serif; color: #31ba8b;'>$text</p>";
            $out = "<tr><td style='padding: 27px 20px 0;'>$out</td></tr>";
            return $out;
        }
        
        /**
         * Outputs a row with big text.
         * 
         * @param string $text Row text.
         * @param string $id ID attribute to 'p' tag to wrap the text in.
         * @return Row output.
         */
        public static function text_big( $text, $id = '', $styles = '' ){
            if( $id ) {
                $id = " id='$id'";
            }
            $out = "<p$id style='margin: 0; text-align: center; font: 300 24px/30px Lato, Arial, sans-serif; color: #39455b;$styles'>$text</p>";
            $out = "<tr><td style='padding: 0 20px;'>$out</td></tr>";
            return $out;
        }
        
        /**
         * Outputs the promo text.
         * 
         * @return Row output.
         */
        public static function promo_text() {
            $text = esc_html( vsu_get_setting( 'promo_text', 'popup_content' ) );
            $out = "<p data-vsu-live='promo_text' style='margin: 0; text-align: center; font: 300 24px/30px Lato, Arial, sans-serif; color: #39455b;'>$text</p>";
            $out = "<tr><td style='padding: 0 20px;'>$out</td></tr>";
            return $out;
        }
        
        /**
         * Outputs a row with big and highlighted text.
         * 
         * @param string $text Row text.
         * @return Row output.
         */
        public static function text_big_highlighted( $text ) {
            $out = "<p style='margin: 0; text-align: center; font: bold 36px/20px Lato, Arial, sans-serif; color: #31ba8b;'>$text</p>";
            $out = "<tr><td style='padding: 36px 20px 9px;'>$out</td></tr>";
            return $out;
        }
        
        /**
         * Outputs a simple row.
         * 
         * @param string $text Row text.
         * @return Row output.
         */
        public static function text( $text ){
            $out = "<p style='margin: 0; text-align: center; font: 300 18px/30px Lato, Arial, sans-serif; color: #5B6983;'>$text</p>";
            $out = "<tr><td style='padding: 12px 20px 0;'>$out</td></tr>";
            return $out;
        }
        
        /**
         * Outputs a row with referral link.
         * 
         * @param string $text Row text.
         * @return Row output.
         */
        public static function ref_link( $ref_key ){
            $ref = vsu_get_ref_url( $ref_key );
            $ref_attr = esc_attr( $ref );
            $ref = esc_html( $ref );
            $out = "<p style='margin: 0; text-align: center;'><a href='$ref_attr' data-vsu-live='promo_page' style='font: bold 18px/30px Lato, Arial, sans-serif; text-decoration: none; color: #39455b;'>$ref</a></p>";
            $out = "<tr><td style='padding: 10px 20px;'>$out</td></tr>";
            return $out;
        }
        
        /**
         * Outputs a row with a text inviting new signups.
         * 
         * @param string $text Row text.
         * @return Row output.
         */
        public static function text_signup_friends() {
            $ref_number = vsu_get_setting( 'ref_number', 'popup_content' );
            $out = VSU_Autoreponder::text( 
                        sprintf( 
                            esc_html__( 'Get %s of your friends to sign up.', 'vialsignups' ),
                            "<span data-vsu-live='ref_number'>$ref_number</span>"
                        )
                    );
            return $out;
        }

# Email Templates

        /**
         * Template sent to newly signed up users.
         * 
         * @param string $ref_key User referral key.
         * @return string The email template.
         */
        public static function template_thank_you( $ref_key ) {
            $out = VSU_Autoreponder::text_highlighted( esc_html__( 'Thank you!', 'viralsignups' ) );
            $out .= VSU_Autoreponder::promo_text();
            $out .= VSU_Autoreponder::text_signup_friends();

            $out = VSU_Autoreponder::table( $out, $ref_key );
            return $out;
        }

        /**
         * Template sent to users who got a new referred signup.
         * 
         * @param string $number How many signups the user has referred.
         * @param string $ref_key User referral key.
         * @return string The email template.
         */
        public static function template_signup_progress( $number, $ref_key ) {
            $ref_number = vsu_get_setting( 'ref_number', 'popup_content' ) ;
            $out = VSU_Autoreponder::text_big_highlighted( 
                        sprintf( 
                            esc_html__( '%s/%s Sign-ups', 'viralsignups' ), 
                            $number, "<span data-vsu-live='ref_number'>$ref_number</span>"
                        ) 
                    );
            $out .= VSU_Autoreponder::promo_text();
            $out .= VSU_Autoreponder::text_signup_friends();

            $out = VSU_Autoreponder::table( $out, $ref_key );
            return $out;
        }

        /**
         * Template sent to users who referred enough signups to complete the 
         * promo. 
         * 
         * @param string $ref_key User referral key.
         * @return string The email template.
         */
        public static function template_signup_full( $ref_key ) {
            $ref_number = vsu_get_setting( 'ref_number', 'popup_content' ) ;
            $out = VSU_Autoreponder::text_big_highlighted( 
                        sprintf( 
                            esc_html__( 'You got %s sign ups!', 'viralsignups' ), 
                            "<span data-vsu-live='ref_number'>$ref_number</span>"
                        ) 
                    );
            $out .= VSU_Autoreponder::text_big( esc_html( vsu_get_setting( 'signup_full_text', 'autoresponders' ) ), 'vsu-preview-signup-full-text', 'white-space:pre-line;' );

            $out = VSU_Autoreponder::table( $out, $ref_key );
            return $out;
        }
    }
}