<?php

if (!class_exists('WPEM_Settings')) {

    /**
     * Handles plugin settings and user profile meta fields
     */
    class WPEM_Settings extends WPEM_Module {

        protected $settings;
        protected static $default_settings;
        protected static $readable_properties = array('settings');
        protected static $writeable_properties = array('settings');

        const REQUIRED_CAPABILITY = 'administrator';


        /*
         * General methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct() {
            $this->register_hook_callbacks();
        }

        /**
         * Public setter for protected variables
         *
         * Updates settings outside of the Settings API or other subsystems
         *
         * @mvc Controller
         *
         * @param string $variable
         * @param array  $value This will be merged with WPEM_Settings->settings, so it should mimic the structure of the WPEM_Settings::$default_settings. It only needs the contain the values that will change, though. See Email_Manager->upgrade() for an example.
         */
        public function __set($variable, $value) {
            // Note: WPEM_Module::__set() is automatically called before this

            if ($variable != 'settings') {
                return;
            }

            $this->settings = self::validate_settings($value);
            update_option('wpem_settings', $this->settings);
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            global $pagenow;

            add_action('admin_menu', __CLASS__ . '::register_settings_pages');
            add_action('init', array($this, 'init'));
            add_action('show_user_profile', array($this, 'add_user_fields'));
            add_action('edit_user_profile', array($this, 'add_user_fields'));
            add_action('personal_options_update', array($this, 'save_user_fields'));
            add_action('edit_user_profile_update', array($this, '::save_user_fields'));

            add_action('admin_init', array($this, 'register_settings'));

            add_filter(
                    'plugin_action_links_' . plugin_basename(dirname(__DIR__)) . '/bootstrap.php', __CLASS__ . '::add_plugin_action_links'
            );

            /* if ('admin.php' == $pagenow && isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpem_mail') {
              add_filter('media_buttons_context', array($this, 'tinymce_buttons'));
              } */
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init() {
            self::$default_settings = self::get_default_settings();
            $this->settings = self::get_settings();
        }

        /**
         * Executes the logic of upgrading from specific older versions of the plugin to the current version
         *
         * @mvc Model
         *
         * @param string $db_version
         */
        public function upgrade($db_version = 0) {
            /*
              if( version_compare( $db_version, 'x.y.z', '<' ) )
              {
              // Do stuff
              }
             */
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected function is_valid($property = 'all') {
            // Note: __set() calls validate_settings(), so settings are never invalid

            return true;
        }

        /*
         * Plugin Settings
         */

        /**
         * Establishes initial values for all settings
         *
         * @mvc Model
         *
         * @return array
         */
        protected static function get_default_settings() {

            $mail_settings = array(
                'default_mail_format' => 'html', // Mail User - Default mail format (html or plain text)
                'max_bcc_recipients' => 0, // Mail User - Maximum number of recipients in the BCC field
                'omit_display_names' => 0, // Mail User - Default setting Omit Display Names in Email Addresses
                'copy_sender' => 0, // Mail User - Default setting for Copy Sender
                'from_sender_exclude' => 1, // Mail User - Default setting for From Sender Exclude
                'from_sender_email' => get_option('admin_email'), // Mail User - Default setting for From Sender Address Override
                'from_sender_name' => get_bloginfo('name'), // Mail User - Default setting for From Sender Name Override
                'bounces_address' => get_option('admin_email'), // Mail User - Default setting for Send Bounces To Address Override
				'inline_style'=>1,
				'show_in_browser_text'=>__('View it in your browser.','wpem')
            );

            $schedule_settings = array(
                'send_time' => array('hh' => 12, 'mn' => 0)// Default template for user notices
            );

            $notices_settings = array(
                'default_template' => -1, // Default template for user notices
                'default_admin_template' => -1, // Default template for admin notices
            );

            $user_settings = array(
                'unsubscribe_enable' => 1, // Allow users to subscribe or unsubscribe to emails
                'unsubscribe_page' => null,
                'unsubscribe_text' => __('unsubscribe from this list', 'wpem'), // Mail User - Default setting for Mass Email		
            );

            $header_settings = array(
                'x_mailer' => 0, // Mail User - Default setting for Add X-Mailer header
                'mime' => 1, // Mail User - Add MIME-Version mail header record.
            );

            return array(
                'db-version' => '0',
                'mail' => $mail_settings,
                'schedule' => $schedule_settings,
                'notices' => $notices_settings,
                'user' => $user_settings,
                'headers' => $header_settings
            );
        }

        /**
         * Output our tinyMCE field buttons
         * 
         * @access public
         * @since 1.0
         * @return void
         */
        public function tinymce_buttons($context) {

            /**
             * Allow plugins to hijack tinymce and insert their own short codes next to the add media button
             *
             *
             * Passing a truthy value to the filter will short-circuit the operation
             * returning the plugin value instead depending on the 'data_source' parameter in the url
             *
             * @since 1.0
             *
             * @param bool|mixed $pre  Value to return instead of the option value.
             *                               Default false to skip it.
             */
            if (isset($_REQUEST['data_source'])) {
                $pre = apply_filters('tinymce_sc_' . $_REQUEST['data_source'], false);
                if (false !== $pre) {
                    return $pre;
                } else {
                    return $context;
                }
            } else {

                $user_fields = apply_filters('wpem_wp-user-fields', array(
                    'ID' => __('ID', 'wpem'), // - An integer that will be used for updating an existing user.
                    'user_login' => __('User Login', 'wpem'), // string that contains the user's username for logging in.
                    'user_nicename' => __('User Nice Name', 'wpem'), // A string that contains a URL-friendly name for the user.
                    'user_url' => __('User Url', 'wpem'), // string containing the user's URL for the user's web site.
                    'user_email' => __('User Email', 'wpem'), // A string containing the user's email address.
                    'display_name' => __('Display Name', 'wpem'), // A string that will be shown on the site. Defaults to user's
                    'username' => __('Username', 'wpem'), //It is likely that you will want to change this, for appearance.
                    'nickname' => __('Nickname', 'wpem'), // The user's nickname, defaults to the user's username.
                    'first_name' => __('First Name', 'wpem'), // The user's first name.
                    'last_name' => __('Last Name', 'wpem'), // The user's last name.
                    'description' => __('Description', 'wpem'), // A string containing content about the user.
                    'user_registered' => __('Registration Date', 'wpem'), // The date the user registered. Format is 'Y-m-d H:i:s'.
                    'role' => __('Role', 'wpem') //A string used to set the user's role.
                        ));


                $html = '<select id="wpem-fields-select">';
                foreach ($user_fields as $field_id => $field) {
                    $html .= '<option value="' . $field_id . '">' . $field . '</option>';
                }
                $html .= '</select>';
                $html .= ' <a href="#" id="wpem-shortcode" class="button-secondary">Insert Field</a> <a href="#" id="wpem-insert-all" class="button-secondary nf-insert-all-fields">Insert All Fields</a>';

                return $html;
            }
        }

        /**
         * Retrieves all of the settings from the database
         *
         * @mvc Model
         *
         * @return array
         */
        protected static function get_settings() {
            $settings = wpem_merge_atts(
                    self::$default_settings, get_option('wpem_settings', array())
            );

            return $settings;
        }

        /**
         * Adds links to the plugin's action link section on the Plugins page
         *
         * @mvc Model
         *
         * @param array $links The links currently mapped to the plugin
         * @return array
         */
        public static function add_plugin_action_links($links) {
            array_unshift($links, '<a href="http://wordpress.org/extend/plugins/email-manager/faq/">Help</a>');
            array_unshift($links, '<a href="admin.php?page=wpem_mail&mail_scope=settings">Settings</a>');

            return $links;
        }

        /**
         * Adds pages to the Admin Panel menu
         *
         * @mvc Controller
         */
        public static function register_settings_pages() {

            add_menu_page(
                    'Dashboard', WPEM_NAME, self::REQUIRED_CAPABILITY, 'wpem_dashboard', __CLASS__ . '::markup_dashboard_page', 'dashicons-email'
            );
			
			add_submenu_page(
                        'wpem_dashboard',  __('Email Manager Dashboard', 'wpem'), __('Dashboard', 'wpem'), self::REQUIRED_CAPABILITY, 'wpem_dashboard', __CLASS__ . '::markup_dashboard_page'
                );				
			

			 add_submenu_page(
                        'wpem_dashboard',   __('Email Manager', 'wpem'), __('Email Manager', 'wpem'), self::REQUIRED_CAPABILITY, 'wpem_mail', __CLASS__ . '::markup_settings_page'
                );			
        }
		
		public static function markup_dashboard_page(){
		    if (current_user_can(self::REQUIRED_CAPABILITY)) {
                if (isset($_GET['dash_scope']) && $_GET['dash_scope']=='short-codes') {
                    echo self::render_template('wpem-pages/shortcode-docs.php');
				}else{
				    echo self::render_template('wpem-pages/dashboard.php');
				}
			}
		}

        /**
         * Creates the markup for the Settings page
         *
         * @mvc Controller
         */
        public static function markup_settings_page() {
            global $EM_Mailer;
            if (current_user_can(self::REQUIRED_CAPABILITY)) {
                if (isset($_GET['mail_scope'])) {

                    switch ($_GET['mail_scope']) {
                        case 'settings':
                            echo self::render_template('wpem-pages/settings.php');
                            break;
                        case 'schedules':
                            WPEM()->modules['schedules']->render_schedules();
                            break;
                        case 'notifications':
                            WPEM()->modules['notifications']->render_notifications();
                            break;
                        case 'edit-schedule':
                            break;
                        default:
                            $templates = get_posts(array('post_type' => 'wpem-temps'));
                            echo self::render_template('wpem-pages/send-mail.php', array('templates' => $templates, 'mail' => $EM_Mailer->submit_results));
                            break;
                    }
                } else {
                    $templates = get_posts(array('post_type' => 'wpem-temps'));
                    echo self::render_template('wpem-pages/send-mail.php', array('templates' => $templates, 'mail' => $EM_Mailer->get_submit_results()));
                }
            } else {
                wp_die('Access denied.');
            }
        }

        /**
         * Registers settings sections, fields and settings
         *
         * @mvc Controller
         */
        public function register_settings() {

            /*
             * Mail Section
             */
            add_settings_section(
                    'wpem_section-mail', 'Mail Settings', __CLASS__ . '::markup_section_headers', 'wpem_settings'
            );

            add_settings_field(
                    'wpem_default-mail-format', __('Mail format', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_default-mail-format')
            );
            add_settings_field(
                    'wpem_max-bcc-recipients', __('Maximum BCC Recipients', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_max-bcc-recipients')
            );

            add_settings_field(
                    'wpem_omit-display-names', __('Display Names', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_omit-display-names')
            );
            add_settings_field(
                    'wpem_copy-sender', __('Copy Sender', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_copy-sender')
            );
            add_settings_field(
                    'wpem_from-sender-exclude', __('Exclude Sender', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_from-sender-exclude')
            );
			add_settings_field(
                    'wpem_from-inline-style', __('Add CSS Inline', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_inline-style')
            );
            add_settings_field(
                    'wpem_from-sender-name', __('Sender Name', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_from-sender-name')
            );
            add_settings_field(
                    'wpem_bounces-address', __('Email For Bounces', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_bounces-address')
            );
            add_settings_field(
                    'wpem_from-sender-email', __('Sender Email', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_from-sender-email')
            );
            add_settings_field(
                    'wpem_show-in-browser-text', __('Show in browser text', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-mail', array('label_for' => 'wpem_show-in-browser-text')
            );			
			

            /*
             * Schedules Section
             */
            add_settings_section(
                    'wpem_section-scheduler', __('Schedular Settings', 'wpem'), __CLASS__ . '::markup_section_headers', 'wpem_settings'
            );

            add_settings_field(
                    'wpem_send-time', __('Emails Send Time', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-scheduler', array('label_for' => 'wpem_send-time')
            );
            /*
             * Notices Section
             */
            add_settings_section(
                    'wpem_section-notices', 'Notices Settings', __CLASS__ . '::markup_section_headers', 'wpem_settings'
            );

            add_settings_field(
                    'wpem_default-template', __('Default Notification Template', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-notices', array('label_for' => 'wpem_default-template')
            );

            add_settings_field(
                    'wpem_default-admin-template', __('Default Admin Notification Template', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-notices', array('label_for' => 'wpem_default-admin-template')
            );

            /*
             *  User section
             */
            add_settings_section(
                    'wpem_section-user', __('User Settings', 'wpem'), __CLASS__ . '::markup_section_headers', 'wpem_settings'
            );

            add_settings_field(
                    'wpem_user-stgs', __('User Settings', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-user', array('label_for' => 'wpem_user-stgs')
            );



            /*
             *  Header section
             */
            add_settings_section(
                    'wpem_section-header', __('Header Settings', 'wpem'), __CLASS__ . '::markup_section_headers', 'wpem_settings'
            );

            add_settings_field(
                    'wpem_header-stgs', __('Additional Mail Headers', 'wpem'), array($this, 'markup_fields'), 'wpem_settings', 'wpem_section-header', array('label_for' => 'wpem_header-stgs')
            );

            // The settings container
            register_setting(
                    'wpem_settings', 'wpem_settings', array($this, 'validate_settings')
            );
        }

        /**
         * Adds the section introduction text to the Settings page
         *
         * @mvc Controller
         *
         * @param array $section
         */
        public static function markup_section_headers($section) {
            echo self::render_template('wpem-settings/page-settings-section-headers.php', array('section' => $section), 'always');
        }

        /**
         * Delivers the markup for settings fields
         *
         * @mvc Controller
         *
         * @param array $field
         */
        public function markup_fields($field) {
            /* switch ($field['label_for']) {
              case 'wpem_field-example1':
              // Do any extra processing here
              break;
              } */
            $field = apply_filters('wpem_markup_fields', $field);

            $templates = get_posts(array('post_type' => 'wpem-temps'));
            echo self::render_template('wpem-settings/page-settings-fields.php', array('settings' => $this->settings, 'field' => $field, 'templates' => $templates), 'always');
        }

        /**
         * Validates submitted setting values before they get saved to the database. Invalid data will be overwritten with defaults.
         *
         * @mvc Model
         *
         * @param array $new_settings
         * @return array
         */
        public function validate_settings($new_settings) {


            if (isset($new_settings['db-version']) && !is_string($new_settings['db-version'])) {
                $new_settings['db-version'] = Email_Manager::VERSION;
            }

            /*
             * Mail Settings
             */

            if (isset($new_settings['mail']['default_mail_format']) && !in_array($new_settings['mail']['default_mail_format'], array('html', 'plain-text'))) {
                add_notice(__('unknown mail format', 'wpem'), 'error');
                $new_settings['mail']['default_mail_format'] = self::$default_settings['mail']['default_mail_format'];
            }

            if (isset($new_settings['mail']['max_bcc_recipients'])) {
                $new_settings['mail']['max_bcc_recipients'] = absint($new_settings['mail']['max_bcc_recipients']);
            }

            $new_settings['mail']['omit_display_names']=(isset($new_settings['mail']['omit_display_names']))?1:0;
            $new_settings['mail']['copy_sender'] = (isset($new_settings['mail']['copy_sender']))?1:0;
            $new_settings['mail']['from_sender_exclude'] = (isset($new_settings['mail']['from_sender_exclude']))?1:0;
            $new_settings['mail']['inline_style'] = (isset($new_settings['mail']['inline_style']))?1:0;

            if (isset($new_settings['mail']['from_sender_email'])) {
                if (!is_email($new_settings['mail']['from_sender_email'])) {
                    add_notice(__('Invalid email address provided for "sender mail"', 'wpem'), 'error');
                    $new_settings['mail']['from_sender_email'] = self::$default_settings['mail']['from_sender_email'];
                }
            }

            if (isset($new_settings['mail']['from_sender_name'])) {
                $new_settings['mail']['from_sender_name'] = sanitize_text_field($new_settings['mail']['from_sender_name']);
            }

            if (isset($new_settings['mail']['bounces_address'])) {
                if (!is_email($new_settings['mail']['bounces_address'])) {
                    add_notice(__('Invalid email address provided for "bounces mail"', 'wpem'), 'error');
                    $new_settings['mail']['bounces_address'] = self::$default_settings['mail']['bounces_address'];
                }
            }
			
			if (isset($new_settings['mail']['show_in_browser_text'])) {
                $new_settings['mail']['show_in_browser_text'] = sanitize_text_field($new_settings['mail']['show_in_browser_text']);

                if (empty($new_settings['mail']['show_in_browser_text'])) {
                    $new_settings['mail']['show_in_browser_text'] = self::$default_settings['mail']['show_in_browser_text'];
                }
            }
			

            /*
             * Schedule Settings
             */
            if (isset($new_settings['schedule']['send_time'])) {
                $new_settings['schedule']['send_time']['hh'] = absint($new_settings['schedule']['send_time']['hh']);
                $new_settings['schedule']['send_time']['hh'] = ($new_settings['schedule']['send_time']['hh'] > 23)?0:$new_settings['schedule']['send_time']['hh'];
                $new_settings['schedule']['send_time']['mn'] = absint($new_settings['schedule']['send_time']['mn']);
                $new_settings['schedule']['send_time']['mn'] = ($new_settings['schedule']['send_time']['mn'] > 59)?0:$new_settings['schedule']['send_time']['mn'];
            }

            /*
             * Notices Settings
             */
            if (isset($new_settings['notices']['default_template']) && absint($new_settings['notices']['default_template'])) {
                $new_settings['notices']['default_template'] = (int) $new_settings['notices']['default_template'];
            }

            if (isset($new_settings['notices']['default_admin_template']) && absint($new_settings['notices']['default_admin_template'])) {
                $new_settings['notices']['default_admin_template'] = (int) $new_settings['notices']['default_admin_template'];
            }

            /*
             * User Default Settings
             */

            $new_settings['user']['unsubscribe_enable'] = (isset($new_settings['user']['unsubscribe_enable']))?1:0;

            if (isset($new_settings['user']['unsubscribe_text'])) {
                $new_settings['user']['unsubscribe_text'] = sanitize_text_field($new_settings['user']['unsubscribe_text']);

                if (empty($new_settings['user']['unsubscribe_text'])) {
                    $new_settings['user']['unsubscribe_text'] = self::$default_settings['user']['unsubscribe_text'];
                }
            }

            if (isset($new_settings['user']['unsubscribe_page']) && absint($new_settings['user']['unsubscribe_page'])) {
                $new_settings['user']['unsubscribe_page'] = (int) $new_settings['user']['unsubscribe_page'];
            }
            /*
             * Extra Header Settings
             */

            $new_settings['headers']['x_mailer'] = isset($new_settings['headers']['x_mailer'])?1:0;
            $new_settings['headers']['mime'] = isset($new_settings['headers']['mime'])?1:0;

            $new_settings = shortcode_atts($this->settings, $new_settings);

            return $new_settings;
        }

        /*
         * User Settings
         */

        /**
         * Adds extra option fields to a user's profile
         *
         * @mvc Controller
         *
         * @param object
         */
        public function add_user_fields($user) {
            if (!$this->settings['user']['unsubscribe_enable']) {
                return;
            }
            $unsubscribe_emails = get_option('wpem-unsubscribe_list', array());
            $checked = true;
            if (in_array($user->user_email, $unsubscribe_emails)) {
                $checked = false;
            }
            echo self::render_template('wpem-settings/user-fields.php', array('user' => $user, 'checked' => $checked));
        }

        /**
         * Validates and saves the values of extra user fields to the database
         *
         * @mvc Controller
         *
         * @param int $user_id
         */
        public  function save_user_fields($user_id) {
		    if (!$this->settings['user']['unsubscribe_enable']) {
                return;
            }
            $user = get_user_by('id', $user_id);
            $user_fields = self::validate_user_fields($user_id, $_POST);

            if ($user_fields['wpem_user-subscribe']) {
                self::unsubscribe($user->user_email, false);
            } else {
                self::unsubscribe($user->user_email);
            }
        }

        public static function unsubscribe($user_email, $unsubscribe=true) {
            $unsubscribe_emails = get_option('wpem-unsubscribe_list', array());
            $action_taken = false;
            if ($unsubscribe) {

                if (!in_array($user_email, $unsubscribe_emails)) {
                    $unsubscribe_emails[] = $user_email;
                    $action_taken = true;
                }
            } else {
                foreach ($unsubscribe_emails as $index => $email) {

                    if ($user_email == $email) {
                        unset($unsubscribe_emails[$index]);
                        $action_taken = true;
                    }
                }
            }
            update_option('wpem-unsubscribe_list', $unsubscribe_emails);
            return $action_taken;
        }

        /**
         * Validates submitted user field values before they get saved to the database
         *
         * @mvc Model
         *
         * @param int   $user_id
         * @param array $user_fields
         * @return array
         */
        public static function validate_user_fields($user_id, $user_fields) {
            if (isset($user_fields['wpem_user-subscribe'])) {
                $user_fields['wpem_user-subscribe'] = true;
            } else {
                $user_fields['wpem_user-subscribe'] = false;
            }
            return $user_fields;
        }

        /**
         * Prepares site to use the plugin during activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide) {
            $settings = get_option('wpem_settings', array());
            if (!isset($settings['user']['unsubscribe_page'])) {
                // unsubscribe page
                $unsubscribe_page = wp_insert_post(
                        array(
                            'post_title' => __('Unsubscribe', 'wpem'),
                            'post_content' => sprintf(__('To Subscribe or Unsubscribe to our email list, Login in to your profile  <br/> and edit the receive mail options', 'wpem'), admin_url()),
                            'post_status' => 'publish',
                            'post_author' => 1,
                            'post_type' => 'page',
                            'comment_status' => 'closed'
                        )
                );


                // Store page IDs
                $settings['user']['unsubscribe_page'] = $unsubscribe_page;
                update_option('wpem_settings', $settings);
            }
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate() {
            
        }

    }

    // end WPEM_Settings
}
