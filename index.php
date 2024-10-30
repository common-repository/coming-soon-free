<?php
/*
Plugin Name: Coming Soon - Free
Plugin URI: https://www.tourvista.com/plugins/coming-soon/
Description: One-click setup for a great coming soon page with smart defaults. Up & running in 10 seconds. It's free and always will be.
Version: 1.3
Author: TourVista
Author URI: https://www.tourvista.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! class_exists( 'TourVistaComingSoon' ) ) {
	class TourVistaComingSoon {
        public function __construct() {
            // de/activation
            register_activation_hook( __FILE__, array( $this, 'activate' ) );
            register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

            // backend
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_styles' ) );
            add_action( 'wp_before_admin_bar_render', array( $this, 'render_admin_bar' ), 100 );
            add_action( 'admin_notices', array( $this, 'add_activation_notice' ) );
            add_filter( 'login_redirect', array( $this, 'redirect_after_login' ) );
            add_action( 'wp_ajax_reset_settings', array( $this, 'reset_settings' ) );

            // frontend
            add_action( 'template_include', array( $this, 'show_coming_soon' ) );
            add_action( 'wp_ajax_nopriv_submit_email', array( $this, 'submit_email' ) );
        }

        public function activate() {
            // set default options if first activation
            $options = $this->get_default_options();
            add_option( 'tv_coming_soon', $options );

            // create table for storing emails
            global $wpdb;
        	$table_name = $wpdb->prefix . 'tv_cs_emails';

            $sql = "SHOW TABLES LIKE '$table_name';";
            if ( ! $wpdb->get_row( $sql ) ) {
            	$charset_collate = $wpdb->get_charset_collate();
            	$sql = "CREATE TABLE $table_name (
            		id int( 10 ) NOT NULL AUTO_INCREMENT,
            		email varchar( 255 ) NOT NULL,
            		added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            		ip varchar( 40 ) NOT NULL,
            		sent tinyint( 1 ) DEFAULT '0' NOT NULL,
            		PRIMARY KEY  ( id )
            	 ) $charset_collate;";
            	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            	dbDelta( $sql );
            }

            // flush cache
            if ( function_exists( 'w3tc_flush_all' ) ) {
                w3tc_flush_all();
            }

            // add admin notice
            if ( class_exists( 'TourVistaAdminPanel' ) ) {
                set_transient( 'show_coming_soon_notice', true, 30 );
            }
        }

        public function deactivate() {
            global $wpdb;

            // send emails?
            $options = get_option( 'tv_coming_soon' );
            if ( ! empty( $options['email_leads'] ) ) {
                $headers = array(
                    'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>',
                    'Content-Type: text/html; charset=UTF-8'
                );
                $body = apply_filters( 'the_content', $options['email_body'] );
                $subject = $options['email_subject'];

                $sql = "SELECT email FROM {$wpdb->prefix}tv_cs_emails WHERE sent = 0";
                $result = $wpdb->get_col( $sql );
                if ( $wpdb->num_rows ) {
                    foreach ( $result as $email ) {
                        wp_mail( $email, $subject, $body, $headers );
                    }
                    $sql = "UPDATE {$wpdb->prefix}tv_cs_emails SET sent = 1 WHERE sent = 0";
                    $wpdb->query( $sql );

                    // cc admin
                    if ( ! empty( $options['form_to'] ) ) {
                        $admin_body = '<p>Hello,</p>';
                        $admin_body .= '<p>An email has been sent to all users who requested to be notified when your site goes live. Below is a copy of the email that was sent to each user.</p>';
                        $admin_body .= '<p>* * * * * * * * * *</p>';
                        $admin_body .= $body;
                        $admin_body .= '<p>* * * * * * * * * *</p>';
                        $admin_body .= '<p>Here is a list of all the users who received this email.</p>';
                        $admin_body .= '<ul><li>' . implode( '</li><li>', $result ) . '</li></ul>';
                        $admin_body .= '<p>Don\'t forget to add these emails to your marketing list!</p>';
                        $admin_body .= '<p>Have a great day!</p>';
                        $admin_body .= $this->get_tv_footer();
                        $subject = 'Auto-email to coming soon subscribers for ' . $this->get_url() . ' sent!';
                        wp_mail( $options['form_to'], $subject, $admin_body, $headers );
                    }
                }
            }

            // flush cache
            if ( function_exists( 'w3tc_flush_all' ) ) {
                w3tc_flush_all();
            }
        }

        public function register_settings() {
            register_setting( 'tv_coming_soon', 'tv_coming_soon', array( $this, 'sanitize_settings' ) );
        }

        public function sanitize_settings() {
            $output = array();
            $defaults = $this->get_default_options();

            // text fields
            $texts = array(
                'h1', 'h2', 'h3',
                'social_facebook', 'social_twitter', 'social_google',
                'form_email', 'form_button', 'form_to',
                'email_subject', 'map_key', 'map_address',
                'login_text', 'login_url', 'credit_text', 'credit_url'
            );
            foreach ( $texts as $key ) {
                $output[$key] = sanitize_text_field( stripslashes( $_POST[$key] ) );
            }

            // positive integers
            $ints = array(
                'after_login', 'bg_img', 'form_show', 'form_notify', 'email_leads',
                'map_show', 'login_show', 'credit_show', 'credit_img'
            );
            foreach ( $ints as $key ) {
                $output[$key] = absint( $_POST[$key] );
            }

            // WYSIWYG editor
            $texts = array(
                'description', 'email_body'
            );
            foreach ( $texts as $key ) {
                $output[$key] = wp_kses_post( stripslashes( $_POST[$key] ) );
            }

            // if form is hidden, hide other form stuffs
            if ( ! $output['form_show'] ) {
                $output['form_notify'] = 0;
                $output['email_leads'] = 0;
            }

            // opacity has to be between 0 and 1
            if ( $_POST['bg_opacity'] >= 0 && $_POST['bg_opacity'] <= 1 ) {
                $output['bg_opacity'] = $_POST['bg_opacity'];
            } else {
                $output['bg_opacity'] = $defaults['bg_opacity'];
            }

            // if any defaults were cleared, we use the default
            $output = $this->supply_required_options($output);

            return $output;
        }

        public function create_admin_menu() {
            add_options_page( 'Coming Soon', 'Coming Soon', 'manage_options', 'tv_coming_soon', array( $this, 'load_settings_page' ) );
        }

        public function load_settings_page() {
            include 'settings.php';
        }

        public function add_admin_styles( $hook ) {
            if ( $hook !== 'settings_page_tv_coming_soon' ) return;

            add_thickbox();
            wp_enqueue_media();
            wp_enqueue_style( 'tv_coming_soon_admin_styles', plugins_url( 'css/admin.css', __FILE__ ) );
            wp_enqueue_script( 'clipboardjs', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js' );
        }

        public function render_admin_bar() {
            global $wp_admin_bar;
        	if ( current_user_can( 'manage_options' ) ) {
        		$wp_admin_bar->add_node( array(
        			'id' => 'tv_coming_soon',
        			'title' => 'Coming Soon Enabled',
        			'href' => admin_url( 'options-general.php?page=tv_coming_soon' ),
                    'meta' => array( 'class' => 'tv_coming_soon' ),
        		) );
                ?>
                <style>#wpadminbar .tv_coming_soon > .ab-item { background-color: #f00; }</style>
                <?php
            }
        }

        public function show_coming_soon( $template ) {
            // show coming soon if is not the feed page, is not logged in, or logged in but not editor or admin
    		if ( ! is_feed() && ( ! is_user_logged_in() || ! current_user_can( 'edit_pages' ) ) ) {
                return plugin_dir_path( __FILE__ ) . 'coming-soon.php';
    		}
            return $template;
        }

        public function submit_email() {
            check_ajax_referer( 'tourvistababy', 'tv' );

            $is_valid = false;

            parse_str( $_POST['data'], $input );
            if ( empty( $input['name'] ) ) {
                // possibily legit, validate email
                if ( ! empty( $input['email'] ) && is_email( $input['email'] ) ) {
                    $domain = substr( strstr( $input['email'], '@' ), 1 );
                    $is_valid = ! empty( $domain ) && checkdnsrr( $domain, 'MX' );
                    if ( $is_valid ) {
                        global $wpdb;

                        // does this email already exist in the db?
                        $sql = "SELECT id FROM {$wpdb->prefix}tv_cs_emails WHERE email = %s LIMIT 1";
                        if ( ! $wpdb->get_var( $wpdb->prepare( $sql, $input['email'] ) ) ) {
                            // add to db
                            $sql = "INSERT INTO {$wpdb->prefix}tv_cs_emails (email, added, ip) VALUES (%s, %s, %s)";
                            $wpdb->query( $wpdb->prepare( $sql, $input['email'], current_time( 'mysql' ), $_SERVER['REMOTE_ADDR'] ) );

                            // notify admin
                            $options = get_option( 'tv_coming_soon' );
                            if ( $options['form_notify'] && ! empty( $options['form_to'] ) ) {
                                $headers = array(
                                    'Content-Type: text/html; charset=UTF-8'
                                );
                                $body = '<p>Hello,</p>';
                                $body .= '<p>' . $input['email'] . ' has requested to be notified once ' . $this->get_url() . ' is live. View the current list of subscribers on the <a href="' . admin_url( 'options-general.php?page=tv_coming_soon' ) . '" target="_blank">Coming Soon settings page</a>.</p>';
                                if ( $options['email_leads'] ) {
                                    $body .= '<p>Based on your current settings, we will automatically send an email notification to all subscribers when you deactivate the plugin.</p>';
                                } else {
                                    $body .= '<p>Based on your current settings, you\'ll need to manually email all subscribers when your website is live. Note: there\'s a setting where we\'ll do this automatically when you deactivate the plugin.</p>';
                                }
                                $body .= '<p>Have a great day!</p>';
                                $body .= $this->get_tv_footer();
                                wp_mail( $options['form_to'], 'New subscriber to coming soon page for ' . $this->get_url(), $body, $headers );
                            }
                        }
                    }
                }
            } else {
                // spam, we pretend it was successful
                $is_valid = true;
            }

            echo $is_valid;
            wp_die();
        }

        public function get_tv_footer() {
            return '<p>The TourVista Team<br>
            Creators of the Coming Soon plugin for WordPress<br>
            <a href="https://www.tourvista.com/" target="_blank">www.tourvista.com</a></p>';
        }

        public function get_url() {
            return str_replace( 'www.', '', defined( 'TV_URL' ) && TV_URL ? TV_URL : $_SERVER['HTTP_HOST'] );
        }

        public function get_default_options() {
            $h1 = defined( 'TV_NAME' ) && TV_NAME ? TV_NAME : get_bloginfo( 'name' );
            $address = defined( 'TV_ADDRESS' ) && TV_ADDRESS ? TV_ADDRESS . ', ' . TV_CITY . ', ' . TV_STATE . ' ' . TV_ZIP : '';
            $phone = defined( 'TV_PHONE' ) && TV_PHONE ? ' &bull; Phone: ' . TV_PHONE : '';
            $body = '<p>Hello,</p>';
            $body .= '<p>You requested to know when our new website has gone live. We are pleased to inform you that it just went live. Check it out!</p>';
            $body .= '<p><a href="' . home_url( '/' ) . '" style="background: #0085ba; border: 1px solid #006799; border-radius: 3px; color: #fff; display: inline-block; margin: 0 auto; padding: 6px 12px; font-size: 14px; text-decoration: none;">Visit ' . $this->get_url() . '</a></p>';

            return array(
                'after_login' => 0,
                'bg_img' => 0,
                'bg_opacity' => '0.75',
                'h1' => $h1,
                'h2' => $address ? $address . $phone : get_bloginfo( 'description' ),
                'h3' => 'Coming Soon...',
                'description' => 'We are working on our new website. Enter your email to be notified when it\'s ready:',
                'social_facebook' => '',
                'social_twitter' => '',
                'social_google' => '',
                'form_show' => 1,
                'form_email' => 'Email address',
                'form_button' => 'Submit',
                'form_notify' => 1,
                'form_to' => defined( 'TV_EMAIL_LEADS' ) && TV_EMAIL_LEADS ? TV_EMAIL_LEADS : get_bloginfo( 'admin_email' ),
                'email_leads' => 1,
                'email_subject' => $this->get_url() . ' is live!',
                'email_body' => $body,
                'map_key' => defined( 'TV_GMAP_KEY' ) ? TV_GMAP_KEY : '',
                'map_show' => $address ? 1 : 0,
                'map_address' => $address,
                'login_show' => 1,
                'login_text' => 'Login',
                'login_url' => '/wp-admin/',
                'credit_show' => 0,
                'credit_text' => class_exists( 'TourVistaAdminPanel' ) ? 'Apartment Website Design by' : 'Credits',
                'credit_url' => class_exists( 'TourVistaAdminPanel' ) ? 'https://www.apartmentsites.com/' : 'https://www.tourvista.com/plugins/coming-soon/',
                'credit_img' => 0
            );
        }

        public function get_required_options() {
            return array(
                'after_login',
                'h1',
                'form_email',
                'form_button',
                'form_to',
                'email_subject',
                'email_body',
                'map_key',
                'map_address',
                'login_text',
                'login_url',
                'credit_text',
                'credit_url'
            );
        }

        public function supply_required_options( $options ) {
            $defaults = $this->get_default_options();
            $required = $this->get_required_options();
            foreach ( $required as $key ) {
                if ( empty( $options[$key] ) ) {
                    $options[$key] = $defaults[$key];
                }
            }
            return $options;
        }

        public function add_activation_notice() {
            if ( get_transient( 'show_coming_soon_notice' ) ) {
                echo '<div class="notice notice-warning"><p>Be sure to add this site to Google Search Console so the Big G indexes it!</p></div>';
                delete_transient( 'show_coming_soon_notice' );
            }
        }

        public function redirect_after_login( $redirect_to ) {
            $options = get_option( 'tv_coming_soon' );
            if ( $redirect_to == admin_url() && empty( $options['after_login'] ) ) {
                $redirect_to = site_url();
            }
            return $redirect_to;
        }

        public function reset_settings() {
            $options = $this->get_default_options();
            $_POST = $options;
            update_option( 'tv_coming_soon', 255 );
            wp_die();
        }

        public function datetime_to_html( $date ) {
            return date( 'm/d/Y \a\t g:i a', strtotime( $date ) );
        }
    }
    $tv_coming_soon = new TourVistaComingSoon();
}
?>