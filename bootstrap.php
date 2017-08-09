<?php
/*
Plugin Name: eMail Manager
Plugin URI:  zanto.org
Description: Send and schedule beautiful professional email and WordPress notifications.
Version:     0.2
Author:      Mucunguzi Ayebare Brooks
Author URI:  http://ayebare@zanto.org
*/


if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'WPEM_NAME',  'eMail Manager' );
define( 'WPEM_REQUIRED_PHP_VERSION', '5.3' );                          
define( 'WPEM_REQUIRED_WP_VERSION',  '3.1' );                          
define( 'WPEM_VERSION',  '0.1' ); 
define( 'WPEM_PLUGIN_PATH', dirname(__FILE__));
define( 'WPEM_PLUGIN_FOLDER', basename(WPEM_PLUGIN_PATH));
define( 'WPEM_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'DATE_FORMAT', get_option('date_format'));
define( 'TIME_FORMAT',get_option('time_format'));
define( 'DATE_TIME_FORMAT',_x('Y-m-d G:i:s', 'timezone date format'));

/**
 * Loads plugin translations
 */
function wpem_load_lang_files()
{
    $lang_dir = WPEM_PLUGIN_FOLDER . '/languages/';
    load_plugin_textdomain('wpem', false, $lang_dir);
}
add_filter('wp_loaded', 'wpem_load_lang_files');


/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function wpem_requirements_met() {
	global $wp_version;
	//require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		// to get is_plugin_active() early

	if ( version_compare( PHP_VERSION, WPEM_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, WPEM_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	/*
	if ( ! is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
		return false;
	}
	*/

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function wpem_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( wpem_requirements_met() ) {
    require_once( __DIR__ . '/includes/functions.php' );
	require_once( __DIR__ . '/modules/single-mail.php' );
	require_once( __DIR__ . '/classes/wpem-module.php' );
	require_once( __DIR__ . '/classes/email-manager.php' );
	require_once( __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php' );
	require_once( __DIR__ . '/classes/wpem-custom-post-type.php' );
	require_once( __DIR__ . '/classes/wpem-template-class.php' );
	require_once( __DIR__ . '/classes/wpem-settings.php' );
	require_once( __DIR__ . '/classes/wpem-cron.php' );
	require_once( __DIR__ . '/classes/wpem-instance-class.php' );
	require_once( __DIR__ . '/classes/wpem-schedules-class.php' );
	require_once( __DIR__ . '/classes/wpem-schedules-table-class.php' );
	require_once( __DIR__ . '/classes/wpem-notifications-class.php' );	
	require_once( __DIR__ . '/classes/wpem-send-mail-class.php' );	

	if ( class_exists( 'Email_Manager' ) ) {
		$GLOBALS['wpem'] = Email_Manager::get_instance();
		$EM_Mailer = EM_Mailer::get_instance();
		register_activation_hook(   __FILE__, array( $GLOBALS['wpem'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['wpem'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'wpem_requirements_error' );
}
function WPEM(){
return $GLOBALS['wpem'];
}
