<?php

if (!class_exists('WPEM_Cron')) {

    /**
     * Handles cron jobs and intervals
     *
     * Note: Because WP-Cron only fires hooks when HTTP requests are made, make sure that an external monitoring service pings the site regularly to ensure hooks are fired frequently
     */
    class WPEM_Cron extends WPEM_Module {

        protected static $readable_properties = array();
        protected static $writeable_properties = array();

        /*
         * Magic methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct() {
            $this->register_hook_callbacks();
        }

        /*
         * Static methods
         */

        /**
         * Adds custom intervals to the cron schedule.
         *
         * @mvc Model
         *
         * @param array $schedules
         * @return array
         */
        public static function add_custom_cron_intervals($schedules) {
            $schedules['wpem_debug'] = array(
                'interval' => 5,
                'display' => 'Every 5 seconds'
            );

            $schedules['wpem_ten_minutes'] = array(
                'interval' => 60 * 10,
                'display' => 'Every 10 minutes'
            );

            $schedules['wpem_example_interval'] = array(
                'interval' => 60 * 60 * 5,
                'display' => 'Every 5 hours'
            );

            return $schedules;
        }

        /**
         * Fires a cron job at a specific time of day, rather than on an interval
         *
         */
        public static function fire_schedule_jobs() {
            $send_time = WPEM()->modules['settings']->settings['schedule']['send_time'];
            $now = current_time('timestamp');
            $trigger_time = strtotime(date('Y-m-d')) + ($send_time['hh'] * 3600) + ($send_time['mn'] * 60);
            $time_passed = $now - $trigger_time;
            if ($time_passed > 0 && $time_passed < 3600) {
                if (!get_transient('wpem_schedules_cron_job_complete')) {
                    WPEM_Schedules::process_schedules();
                    set_transient('wpem_schedules_cron_job_complete', true, 60 * 70); //1 hour and 10 minutes transient
                }
            }
        }

        /*
         * Instance methods
         */

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            add_action('wpem_cron_schedule_jobs', __CLASS__ . '::fire_schedule_jobs');

            add_action('init', array($this, 'init'));

            add_filter('cron_schedules', __CLASS__ . '::add_custom_cron_intervals');
        }

        /**
         * Prepares site to use the plugin during activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide) {
            if (wp_next_scheduled('wpem_cron_schedule_jobs') === false) {
                wp_schedule_event(
                       microtime( true ), 'wpem_debug', 'wpem_cron_schedule_jobs'
                );
            }
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate() {
            wp_clear_scheduled_hook('wpem_cron_schedule_jobs');
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

    }

    // end WPEM_Cron
}
