<?php
if (!class_exists('WPEM_Schedules')) {

    /**
     * Handles email schedules 
     */
    class WPEM_Schedules extends WPEM_Module {

        protected $schedules;
        protected $submit_results;
        protected static $default_settings;
        protected static $readable_properties = array('schedules');
        protected static $writeable_properties = array('schedules');

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
            add_action('admin_init', array($this, 'admin_int'));
        }

        public function plugins_loaded() {
            $this->schedules = self::get_schedules();
        }

        public function admin_int() {
		    if (isset($_POST['schedule']['save'])){
                 $this->save_schedule();
			}
            $this->process_bulk_action();
        }

        public static function get_schedules() {
            $schedules = get_option('wpem-schedules', array());
            //update_option('wpem-schedules', array());
            return apply_filters('wpem_schedules', $schedules);
        }
       
        public function save_schedule() {
            $this->submit_results = null;

            if (wp_verify_nonce($_POST['wpem_save_schedule'], 'wpem_save_schedule_nonce')) {
                do_action('wpem_schedule_pre_save', $_POST);
                $updated = false;
                $inputs = $this->validate_inputs($_POST['schedule']);

                $errors = $inputs['errors'];

                if (!$errors->get_error_code()) {
                    $id = $inputs['id'];
                    unset($inputs['errors']);
                    $schedules = $this->schedules;
                    $schedules[$id] = $inputs;
                    do_action('wpem_schedule_post_save', $inputs, $schedules);
                    $schedules = apply_filters('wpem_save_notifications', $schedules);
                    update_option('wpem-schedules', $schedules);
                    $updated = true;
                    $location = add_query_arg('message', 1, admin_url('admin.php?page=wpem_mail&mail_scope=schedules'));
                    wp_redirect($location);
                }

                $this->submit_results = $inputs;
            }
        }

        public static function process_schedules() {
            $schedules = get_option('wpem-schedules');
            $day_now = strtotime(date('Y-m-d'));
            $mail_sent = $count = 0;
            foreach ((array) $schedules as $schedule) {
                $next_send = self::next_send(strtotime($schedule['start_date']), $schedule['frequency'], strtotime($schedule['end_date']));
                if ($day_now == $next_send) {
                    $mail_sent += EM_Mailer::send_mail($schedule);
                    $count++;
                }
            }
            if ($mail_sent)
                add_notice(sprintf(__('%s scheduled emails were sent to %s email addresses', 'wpem'), $count, $mail_sent));
        }

        public static function next_send($start_date, $frequency, $end_date, $time_parameter=0) {

            $day_now = $dayotime_now = strtotime(date('Y-m-d'));

            if ($time_parameter) {
                $dayotime_now = time();
            }

            $next_send = $start_date;

            if ($end_date < $day_now) {
                return false;
            }

            while ($next_send + $time_parameter < $dayotime_now) {

                switch ($frequency) {
                    case 'Daily':
                        if (($day_now + $time_parameter) < $dayotime_now) {
                            return strtotime('+1 day', $day_now);
                        } else {
                            return $day_now;
                        }
                        break;
                    case 'Weekly':
                        $next_send = strtotime('+1 week', $next_send);
                        break;
                    case 'Monthly':
                        $next_send = strtotime('+1 month', $next_send);
                        break;
                    case 'Yearly':
                        $next_send = strtotime('+1 year', $next_send);
                        break;
                    default:
                        return false;
                        break;
                }
            }

            if ($next_send > $end_date) {
                return false;
            } else {
                return $next_send;
            }
        }

        public function validate_inputs($post) {
            $post = apply_filters('wpem_pre_validate_schedule', $post);
            $errors = new WP_Error();

            if (!absint($post['id'])) {
                $errors->add('schedule_id', __('invalid schedule.', 'wpem'));
            }

            $data_sources = EM_Mailer::get_data_sources();

            if (!isset($post['source']['id']) || !array_key_exists($post['source']['id'], $data_sources)) {
                $errors->add('email_source', __('Invalid value for Emaill source given', 'wpem'));
            }
            /* if($post['source']['id']=='wp'){

              }else{

              } */

            if (!in_array($post['mail_format'], array('html', 'plaintext'))) {
                $errors->add('mail_format', __('Unsupported mail format', 'wpem'));
            }

            if (empty($post['from_name'])) {
                $errors->add('from_name', __('From Name should not be empty', 'wpem'));
            } else {
                $post['from_name'] = sanitize_text_field($post['from_name']);
            }

            if (!is_email($post['from_email'])) {
                $errors->add('from_email', __('invalid email', 'wpem'));
            } else {
                $post['from_email'] = sanitize_email($post['from_email']);
            }
            $start_date = $post['start_date'];
            if (!preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2})#', $start_date, $matches)) {
                $errors->add('start_date', __('Invalid Start Date', 'wpem'));
            }

            if (!in_array($post['frequency'], array('Once', 'Daily', 'Weekly', 'Monthly', 'Yearly'))) {
                $errors->add('frequency', __('Invalid Frequency', 'wpem'));
            }

            $end_date = $post['end_date'];
            if (!preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2})#', $end_date, $matches)) {
                $errors->add('end_date', __('Invalid End Date', 'wpem'));
            }


            if (!intval($post['template'])) {
                $errors->add('template', __('Invalid template', 'wpem'));
            }
            if (empty($post['title'])) {
                $errors->add('title', __('Title cannot be empty.', 'wpem'));
            } else {
                $post['title'] = sanitize_text_field($post['title']);
            }
            if (empty($post['body'])) {
                $errors->add('body', __('Notification body cannot be empty.', 'wpem'));
            } else {
                $post['body'] = wp_unslash($post['body']);
            }

            if (!empty($post['attachments'])) {
                $post['attachments'] = wpem_sanitize_attachments($post['attachments']);
            }

            foreach ($post as $key => $value) {
                if (!in_array($key, apply_filters('wpem_allowed_mail_keys', array('id', 'source', 'mail_format', 'from_name', 'from_email', 'start_date', 'frequency', 'end_date', 'template', 'title', 'body', 'attachments'))))
                    unset($post[$key]);
            }
            $post['errors'] = $errors;

            return apply_filters('wpem_validate_schedule', $post);
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

        public function current_action() {
            if (isset($_REQUEST['filter_action']) && !empty($_REQUEST['filter_action']))
                return false;

            if (isset($_REQUEST['action']) && -1 != $_REQUEST['action'])
                return $_REQUEST['action'];

            if (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2'])
                return $_REQUEST['action2'];

            return false;
        }

        function process_bulk_action() {

            //Detect when a bulk action is being triggered...
            if ('delete' === $this->current_action()) {
                foreach ((array) $_REQUEST['schedule'] as $schedule) {
                    unset($this->schedules[$schedule]);
                }

                update_option('wpem-schedules', $this->schedules);
                $location = add_query_arg('message', 2, admin_url('admin.php?page=wpem_mail&mail_scope=schedules'));
                wp_redirect($location);
            }
        }

        public function render_schedules() {

            $templates = get_posts(array('post_type' => 'wpem-temps'));

            if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('edit', 'add_new'))) {

                switch ($_REQUEST['action']) {
                    case 'edit':
                        if (isset($_REQUEST['schedule']) && array_key_exists($_REQUEST['schedule'], $this->schedules)) {
                            $id = $_REQUEST['schedule'];
                            $schedule = $this->schedules[$id];
                        } else {
                            $schedule = null;
                        }
                        break;
                    case 'add_new':
                        if (!isset($this->submit_results)) {
                            $id = $this->get_new_id();
                            $schedule = null;
                        } else {
                            $schedule = $this->submit_results;
                            $id = $schedule['id'];
                        }

                        break;
                    default:
                        break;
                }
                echo self::render_template('wpem-pages/schedule.php', array('templates' => $templates, 'id' => $id, 'schedule' => $schedule));
                return;
            }

            $Schedule_list = new WPEM_Schedules_Class;
            $Schedule_list->prepare_items();
            $messages = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __('Schedule Saved.'),
                2 => __('Schedule deleted.')
            );
            $messages = apply_filters('wpem_shedule_messages', $messages);
            $message = false;
            if (isset($_REQUEST['message']) && ( $msg = (int) $_REQUEST['message'] )) {
                if (isset($messages[$msg]))
                    $message = $messages[$msg];
            }
            ?>
            <div class="wrap">
            <?php wpem_mail_tabs(); ?>
            <?php if ($message) : ?>
                    <div id="message" class="updated"><p><?php echo $message; ?></p></div>
                    <?php $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
                endif; ?>
                <br/>
                <h2>Schedules <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=schedules&action=add_new') ?>">Add New</a></h2>

                <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                <form id="schedules-filter" method="get" >
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <input type="hidden" name="mail_scope" value="schedules" />
                    <!-- Now we can render the completed list table -->
            <?php $Schedule_list->display() ?>
                </form>

            </div>
            <?php
        }

        protected function get_new_id() {

            if (empty($this->schedules)) {
                $id = 1;
            } else {
                $id = count($this->schedules);
            }

            while (array_key_exists($id, $this->schedules)) {
                $id++;
            }
            return $id;
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

        // end WPEM_Schedules
    }

}