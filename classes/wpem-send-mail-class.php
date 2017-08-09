<?php

if (!class_exists('EM_Mailer')) {

    /**
     * Handles send mail operations 
     */
    class EM_Mailer {
        const REQUIRED_CAPABILITY = 'administrator';
        protected $submit_results;
        private static $instances = array();

        /**
         * Constructor
         *
         * @mvc Controller
         */
        function __construct() {
            $this->register_hook_callbacks();
        }

        /**
         * Provides access to a single instance of a module using the singleton pattern
         *
         * @mvc Controller
         *
         * @return object
         */
        public static function get_instance() {
            $module = get_called_class();

            if (!isset(self::$instances[$module])) {
                self::$instances[$module] = new $module();
            }

            return self::$instances[$module];
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            add_action('init', array($this, 'init'));
            add_action('admin_init', array($this, 'admin_init'));
            add_filter('wp_mail', __CLASS__ . '::add_default_template');
            add_filter('wpem_get_recepients', array($this, 'process_subscriptions'));
        }

        public function admin_init() {
            $this->submit_results = null;

            if (isset($_REQUEST['wpem_send_mail'])) {
                $this->proces_subissions();
            }
        }

        public function get_submit_results() {
            return $this->submit_results;
        }

        function proces_subissions() {

            if (wp_verify_nonce($_POST['wpem_send_mail_nonce'], 'wpem_send_mail')) {
                $mail = EM_Mailer::send_mail($_REQUEST['mail']);
                if (is_integer($mail)) {
                    $location = add_query_arg('message', 1, admin_url('admin.php?page=wpem_mail&recepients=' . absint($mail)));
                    wp_redirect($location);
                }
                $this->submit_results = $mail;
            }
        }

        public static function get_data_sources() {
            $nf_active = $gf_active = 0;
            return apply_filters('wpem_data_sources', array('wp' => array('name' => __('WordPress Users', 'wpem'), 'active' => 1),
                        'sm' => array('name' => __('Single Mail', 'wpem'), 'active' => 1),
                        'nf' => array('name' => 'Ninja Forms', 'active' => $nf_active),
                        'gf' => array('name' => 'Gravity Forms', 'active' => $gf_active)
                    ));
        }

        public static function add_default_template($mail) {

            if ($mail['to'] == get_option('admin_email')) {
                $template = WPEM()->modules['settings']->settings['notices']['default_admin_template'];
            } else {
                $template = WPEM()->modules['settings']->settings['notices']['default_template'];
            }
            if ($template && $template !== -1) {
                $template_post = get_post($template);
                $content = do_shortcode($template_post->post_content);
                $style = get_post_meta($template_post->ID, 'wpem_css-box-field', true);
                $body = str_replace("[mail_body]", $mail['message'], $content);
                $mail['headers'][] = sprintf('Content-Type: %s; charset="%s"', get_bloginfo('html_type'), get_bloginfo('charset'));
                $mailtext = '<html><head><style>' . $style . '</style><meta name="viewport" content="width=device-width"/><title>' . $mail['subject'] . '</title></head><body>' . $body . '</body></html>';
                $mail['message'] = $mailtext;
            }
            return $mail;
        }

        public static function add_html_headers($mail) {

            $mail['headers'][] = sprintf('Content-Type: %s; charset="%s"', get_bloginfo('html_type'), get_bloginfo('charset'));
            //remove_filter( 'wp_mail', __CLASS__. '::add_html_headers'); //Filter should only run for mail where it was added.
            return $mail;
        }

        public static function format_recepients($recipients, $bcc_limit, $omit_name) {

            $bcc = $to = array();

            if (empty($recipients)) {
                return false;
            } else {

                $count = $i = 0;

                foreach ($recipients as $recipient) {
                    $f_recipient = ($omit_name) ? $recipient['email'] : sprintf('%s <%s>', $recipient['name'], $recipient['email']);

                    if ($bcc_limit == -1) {
                        $to[] = $f_recipient;
                    } else {
                        $bcc[$i][] = sprintf('Bcc: %s', $f_recipient);
                        $count++;

                        if ($bcc_limit != 0 && ($bcc_limit == $count)) {
                            $i++;
                            $count = 0;
                        }
                    }
                }
            }
            return (array('to' => $to, 'bcc' => $bcc));
        }

        public static function send_mail($mail) {
            $mail_stgs = WPEM()->modules['settings']->settings;
            $mail['recepients'] = self::get_recepients($mail);
            $mail = self::validate_mail($mail);
            $errors = $mail['errors'];

            if ($errors->get_error_code()) {
                return $mail;
            }
            $omit_name = $mail_stgs['mail']['omit_display_names'];
            //  Default the To: and Cc: values to the send email address
            $to = ($omit_name) ? $mail['from_email'] : sprintf('%s <%s>', $mail['from_name'], $mail['from_email']);
            $cc = sprintf('Cc: %s', $to);
            $headers = array();
            //  Cc: Sender?
            $ccsender = $mail_stgs['mail']['copy_sender'];

            if ($ccsender) {
                $cc = sprintf('Cc: %s', $to);
                $headers[] = $cc;
            }

            $num_sent = 0; // return value

            if ((empty($mail['recepients']))) {
                return $num_sent;
            }

            //  Return path defaults to sender email if not specified
            $return_path = $mail_stgs['mail']['bounces_address'];

            if ($return_path == '') {
                $return_path = $mail['from_email'];
            }

            //  Build headers
            $headers[] = ($omit_name) ? 'From: ' . $mail['from_email'] : sprintf('From: "%s" <%s>', $mail['from_name'], $mail['from_email']);
            $headers[] = sprintf('Return-Path: <%s>', $return_path);
            $headers[] = ($omit_name) ? 'Reply-To: ' . $mail['from_email'] : sprintf('Reply-To: "%s" <%s>', $mail['from_name'], $mail['from_email']);

            if ($mail_stgs['headers']['x_mailer']) {
                $headers[] = sprintf('X-Mailer: PHP %s', phpversion());
            }

            $subject = apply_filters('wpem_mail_title', stripslashes($mail['title']));
            $message = apply_filters('wpem_mail_body', do_shortcode(stripslashes($mail['body'])));
            $attachments = array();

            if (isset($mail['attachments']) && !empty($mail['attachments'])) {
                foreach ($mail['attachments'] as $files) {
                    $attachments[] = $files['attachment'];
                }
            }


            if ('html' == $mail['mail_format']) {
			
                if ($mail_stgs['headers']['mime']) { //add mime version_header
                    $headers[] = 'MIME-Version: 1.0';
                }
                $headers[] = sprintf('Content-Type: %s; charset="%s"', get_bloginfo('html_type'), get_bloginfo('charset'));

                $style = '';

                if (isset($mail['template']) && absint($mail['template']) && intval($mail['template']) != -1) {
                    $css = get_post_meta(intval($mail['template']), 'wpem_css-box-field', true);
					
					if(!empty($css)){
                        $style = '<style>' . $css . '</style>';
					}
                }


                if (isset($css) && !empty($css) && $mail_stgs['mail']['inline_style']) {
                    require_once( WPEM_PLUGIN_PATH . '/classes/Emogrifier.php' );
                    $html = '<html><head><title>' . $subject . '</title></head><body>' . $message . '</body></html>';
                    $emogrifier = new \Pelago\Emogrifier($html, $css);
                    $mailtext = $emogrifier->emogrify();
                } else {
                    $mailtext = '<html><head><title>' . $subject . '</title>' . $style . '</head><body>' . $message . '</body></html>';
                }
            } else {

                if ($mail_stgs['headers']['mime']) {
                    $headers[] = 'MIME-Version: 1.0';
                }
                $headers[] = sprintf('Content-Type: text/plain; charset="%s"', get_bloginfo('charset'));
                $message = preg_replace('|&[^a][^m][^p].{0,3};|', '', $message);
                $message = preg_replace('|&amp;|', '&', $message);
                $mailtext = wordwrap(strip_tags($message . "\n" . $footer), 80, "\n");
            }

            $recepients = self::format_recepients($mail['recepients'], $mail_stgs['mail']['max_bcc_recipients'], $omit_name);

            /* if (WPEM_DEBUG) {
              wpem_preprint(array_merge($headers, $bcc));
              wpem_debug_wp_mail($to, $subject, $mailtext, array_merge($headers, $bcc));
              } */
            remove_filter('wp_mail', __CLASS__ . '::add_default_template');
            if ($mail_stgs['mail']['max_bcc_recipients'] == -1) {
                $to = array_unique($to);
                $num_sent = count($recepients['to']);
                foreach ($recepients['to'] as $to) {
                    @wp_mail($to, $subject, $mailtext, $headers, $attachments);
                }
            } else {

                foreach ($recepients['bcc'] as $bcc) {
                    $bcc = array_unique($bcc);
                    $num_sent = $num_sent + count($bcc);
                    $to = ltrim(array_shift($bcc), "Bcc: ");
                    @wp_mail($to, $subject, $mailtext, array_merge($headers, $bcc), $attachments);
                }
            }
            add_filter('wp_mail', __CLASS__ . '::add_default_template');

            return $num_sent;
        }

        public static function validate_mail($mail) {
            $mail = apply_filters('wpem_pre_validate_mail', $mail);
            $errors = new WP_Error();

            if (!in_array($mail['mail_format'], array('html', 'plaintext'))) {
                $errors->add('mail_format', __('Unsupported mail format', 'wpem'));
            }

            if (empty($mail['from_name'])) {
                $errors->add('from_name', __('From Name should not be empty', 'wpem'));
            } else {
                $mail['from_name'] = sanitize_text_field($mail['from_name']);
            }

            if (!is_email($mail['from_email'])) {
                $errors->add('from_email', __('invalid email', 'wpem'));
            }

            if (!intval($mail['template'])) {
                $errors->add('template', __('Invalid template', 'wpem'));
            }

            foreach ((array) $mail['recepients'] as $index => $recepient) {
                $invalid_recepients = array();
                if (!is_email($recepient['email'])) {
                    unset($mail['recepients'][$index]);
                }
                if (empty($mail['recepients'])) {
                    $errors->add('from_email', __('No valid recepient emails', 'wpem'));
                }
                do_action('wpem-invalid-recepients', $invalid_recepients, $mail);
            }

            if (empty($mail['title'])) {
                $errors->add('title', __('Title cannot be empty.', 'wpem'));
            } else {
                $mail['title'] = sanitize_text_field($mail['title']);
            }
            if (empty($mail['body'])) {
                $errors->add('body', __('Body cannot be empty.', 'wpem'));
            } else {
                $mail['body'] = wp_unslash($mail['body']);
            }

            if (isset($mail['attachments'])) {
                $mail['attachments'] = wpem_sanitize_attachments($mail['attachments']);
            }

            $mail['errors'] = $errors;

            return apply_filters('wpem_validate_mail', $mail);
        }

        public static function get_recepients($mail) {

            $source = $mail['source'];
            $recepients = array();

            if (isset($source['id'])) {
                if ($source['id'] == 'wp') {
                    if (isset($source['wp_group'])) {
                        $role = $source['wp_group'];
                    } else {
                        return null;
                    }
                    $users = get_users();
                    $recepients = array();
                    foreach ($users as $user) {
                        $common_elts = array_intersect($user->roles, $source['wp_group']);
                        if (!empty($common_elts))
                            $recepients[] = array('email' => $user->user_email, 'name' => $user->display_name);
                    }
                }

                $recepients = apply_filters('wpem-send_to_' . $source['id'], $recepients, $mail);
            }

            return apply_filters('wpem_get_recepients', $recepients);
        }

        function process_subscriptions($recepients) {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                return $recepients;
            }

            $unsubscribed_emails = get_option('wpem-unsubscribe_list', array());
            foreach ((array) $recepients as $index => $recepient) {
                if (in_array($recepient['email'], $unsubscribed_emails)) {
                    unset($recepients[$index]);
                }
            }
            return $recepients;
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init() {
            
        }

        // end Send_Mail
    }

}