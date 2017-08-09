<?php
if (!class_exists('WPEM_Notifications')) {

    /**
     * Handles email notifications 
     */
    class WPEM_Notifications extends WPEM_Module {

        protected $wpem_notifications;
        protected static $default_settings;
        protected static $readable_properties = array('notifications');
        protected static $writeable_properties = array('notifications');

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
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            global $pagenow;
            add_action('plugins_loaded', array($this, 'plugins_loaded'));
            add_action('init', array($this, 'init'));
            add_action('admin_init', array($this, 'admin_int'), 5);
            if ('admin.php' == $pagenow && isset($_REQUEST['mail_scope']) && $_REQUEST['mail_scope'] == 'notifications') {
                add_filter('media_buttons_context', array($this, 'tinymce_buttons'));
            }
            add_filter('wpem_new_user_notification_body', array($this, 'filter_notifications_body'));
            add_filter('retrieve_password_message', array($this, 'filter_notifications_body'));
            add_filter('retrieve_password_message', array($this, 'wpmu_signup_user_notification_email'));
            add_filter('retrieve_password_message', array($this, 'wpmu_signup_blog_notification_email'));
            add_filter('retrieve_password_message', array($this, 'update_welcome_email'));
        }

        public function plugins_loaded() {
            $notifications = get_option('wpem-notifications', array());
            $this->wpem_notifications = apply_filters('wpem_notifications', $notifications);
        }

        public function filter_notifications_body($message) {
            
            if (doing_action('wpem_new_user_notification_body')) {
                $id = 'wp_new_user_notification';
            } elseif (doing_action('retrieve_password_message')) {
                $id = 'password_reset';
            } elseif (doing_action('wpmu_signup_user_notification_email')) {
                $id = 'wpmu_signup_user_notification';
            } elseif (doing_action('wpmu_signup_blog_notification_email')) {
                $id = 'wpmu_signup_blog_notification';
            } elseif (doing_action('update_welcome_email')) {
                $id = 'wpmu_welcome_notification';
            }

            $notification = $this->get_notification($id);

            if (isset($notification['use_custom'])) {
                remove_filter('wp_mail',  'EM_Mailer::add_default_template');

                $message = str_replace('[' . __('system-message', 'wpem') . ']', $message, $notification['body']);

                if ($notification['mail_format'] == 'html') {
                    $style = get_post_meta($notification['template'], 'wpem_css-box-field', true);
                    add_filter('wp_mail',  'EM_Mailer::add_html_headers');
                    $message = '<html><head><style>' . $style . '</style><meta name="viewport" content="width=device-width"/><title>' . get_bloginfo('name') . '</title></head><body>' . $message . '</body></html>';
                }
            }
            return $message;
        }

        public function register_notifications() {
            $this->add_default_notification(
                    'password_reset', //id
                    'Password Reset', //name
                    __('Mail sent while resetting password', 'wpem') //description
            );

            $this->add_default_notification(
                    'wp_new_user_notification', //id,
                    'wp new user notification', //name
                    __(' Email login credentials to a newly-registered user.', 'wpem') //description
            );

            $this->add_default_notification(
                    'wpmu_signup_user_notification', //id,
                    'wpmu signup user notification', //name
                    __('Notify user of signup success (when no new site has been requested).', 'wpem') //description
            );

            $this->add_default_notification(
                    'wpmu_signup_blog_notification', //id,
                    'wpmu signup blog notification', //name
                    __('Notify user of signup success', 'wpem') //description
            );

            $this->add_default_notification(
                    'wpmu_welcome_notification', //id,
                    'wpmu welcome notification', //name
                    __('Notify a user that their blog activation has been successful', 'wpem') //description
            );

            do_action('wpem_register_notifications');
        }

        public function add_default_notification($id, $name, $description) {
            global $wpem_default_notifications;
            $wpem_default_notifications[$id] = array('id' => $id, 'name' => $name, 'description' => $description);
        }

        public function get_notification($id) {

            global $wpem_default_notifications;

            if (isset($wpem_default_notifications[$id])) {
                $notification = $wpem_default_notifications[$id];
                if (isset($this->wpem_notifications[$id])) {
                    $notification = array_merge($wpem_default_notifications[$id], $this->wpem_notifications[$id]);
                }
            } else {
                return false;
            }
            return $notification;
        }

        /**
         * Prepares site to use the plugin during activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide) {
            
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate() {
            
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init() {
            
        }

        public function admin_int() {
            $this->register_notifications();
            $this->save_notification();
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

        public function render_notifications() {
            global $wpem_default_notifications;
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
                if (isset($_REQUEST['id']) && $notification = $this->get_notification($_REQUEST['id'])) {
                    $templates = get_posts(array('post_type' => 'wpem-temps'));
                    echo self::render_template('wpem-pages/edit-notification.php', array('id' => $_REQUEST['id'], 'notification' => $notification, 'templates' => $templates));
                    return;
                }
            }
            ?>
            <div class="wrap">
            <?php wpem_mail_tabs(); ?>
                <br/>
                <h2><?php _e('Notifications', 'wpem') ?></h2>
                <p><?php _e('Click on the notification link and start editing or use your custom template on it:', 'wpem') ?></p>

                <table class="widefat importers">


                    <tbody>
            <?php foreach ($wpem_default_notifications as $id => $notification): ?>

                            <tr class="alternate">
                                <td class="row-title">
                                    <a title="<?php echo $notification['name'] ?>" class="thickbox" href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=notifications&action=edit&id=' . $id) ?>"><?php echo $notification['name'] ?></a></td>
                                <td class="desc"><?php echo $notification['description'] ?></td>
                            </tr>

            <?php endforeach; ?>

                    </tbody></table>
            </div>
                        <?php
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

                            $html = ' <a href="#" id="wpem-shortcode" class="button-secondary">' . __('Insert System Message', 'wpem') . '</a> ';

                            return $html;
                        }
                    }

                    public function save_notification() {

                        if (isset($_POST['notification']['save']) && wp_verify_nonce($_POST['wpem_edit_notification'], 'wpem_edit_notification_nonce')) {

                            do_action('wpem_notificans_pre_save', $_POST);

                            if (!$inputs = $this->validate_inputs($_POST['notification']))
                                return;
                            $id = $inputs['id'];
                            $notifications = $this->wpem_notifications;
                            $notifications[$id] = $inputs;
                            do_action('wpem_notificans_post_save', $inputs, $notifications);
                            $notifications = apply_filters('wpem_save_notifications', $notifications);
                            update_option('wpem-notifications', $notifications);
                            $this->wpem_notifications = $notifications;
                        }
                    }

                    public function validate_inputs($post) {
                        global $wpem_default_notifications;

                        $post = apply_filters('wpem_pre_validate_notification', $post, $wpem_default_notifications);

                        if (!isset($post['id']) || !isset($wpem_default_notifications[$post['id']])) {
                            add_notice(__('invalid id', 'wpem'), 'error');
                            return false;
                        }

                        if (isset($post['use_custom'])) {
                            $post['use_custom'] = absint($post['use_custom']);
                        }

                        if (!in_array($post['mail_format'], array('html', 'plaintext'))) {
                            add_notice(__('Unknown mail format', 'wpem'), 'error');
                            unset($post['mail_format']);
                        }

                        if (!absint($post['template'])) {
                            if ($post['template'])
                                add_notice(__('invalid template', 'wpem'), 'error');
                            unset($post['template']);
                        }

                        if (empty($post['body'])) {
                            add_notice(__('Notification body cannot be empty', 'wpem'), 'error');
                            unset($post['body']);
                        } else {
                            $post['body'] = wp_unslash($post['body']);
                        }
                        if (!in_array($post['mail_format'], array('html', 'plaintext'))) {
                            add_notice(__('Unsurported Mail Format', 'wpem'), 'error');
                            unset($post['mail_format']);
                        }
                        foreach ($post as $key => $value) {
                            if (!in_array($key, apply_filters('wpem_notification_fields', array('id', 'template', 'title', 'body', 'mail_format', 'use_custom'))))
                                unset($post[$key]);
                        }
                        return apply_filters('wpem_validate_notification', $post);
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
                        return true;
                    }

                    // end WPEM_Notifications
                }

            }