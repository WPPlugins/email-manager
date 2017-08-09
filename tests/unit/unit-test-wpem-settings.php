<?php

require_once( WP_PLUGIN_DIR . '/simpletest-for-wordpress/WpSimpleTest.php' );
require_once( dirname( dirname( __DIR__ ) ) . '/classes/wpem-settings.php' );

/**
 * Unit tests for the WPEM_Settings class
 *
 * Uses the SimpleTest For WordPress plugin
 *
 * @link http://wordpress.org/extend/plugins/simpletest-for-wordpress/
 */
if ( ! class_exists( 'UnitTestWPEM_Settings' ) ) {
	class UnitTestWPEM_Settings extends UnitTestCase {
		public function __construct() {
			$this->WPEM_Settings = WPEM_Settings::get_instance();
		}

		/*
		 * validate_settings()
		 */
		public function test_validate_settings() {
			// Valid settings
			$this->WPEM_Settings->init();
			$valid_settings = array(
				'basic'    => array(
					'field-example1' => 'valid data'
				),

				'advanced' => array(
					'field-example2' => 5
				)
			);

			$clean_settings = $this->WPEM_Settings->validate_settings( $valid_settings );

			$this->assertEqual( $valid_settings['basic']['field-example1'], $clean_settings['basic']['field-example1'] );
			$this->assertEqual( $valid_settings['advanced']['field-example2'], $clean_settings['advanced']['field-example2'] );


			// Invalid settings
			$this->WPEM_Settings->init();
			$invalid_settings = array(
				'basic'    => array(
					'field-example1' => 'invalid data'
				),

				'advanced' => array(
					'field-example2' => - 5
				)
			);

			$clean_settings = $this->WPEM_Settings->validate_settings( $invalid_settings );
			$this->assertNotEqual( $invalid_settings['basic']['field-example1'], $clean_settings['basic']['field-example1'] );
			$this->assertNotEqual( $invalid_settings['advanced']['field-example2'], $clean_settings['advanced']['field-example2'] );
		}

		/*
		 * __set()
		 */
		public function test_magic_set() {
			// Test that fields are validated
			$this->WPEM_Settings->init();
			$this->WPEM_Settings->settings = array( 'db-version' => array() );
			$this->assertEqual( $this->WPEM_Settings->settings['db-version'], Email_Manager::VERSION );

			// Test that values gets written to database
			$this->WPEM_Settings->settings = array( 'db-version' => '5' );
			$this->WPEM_Settings->init();
			$this->assertEqual( $this->WPEM_Settings->settings['db-version'], '5' );
			$this->WPEM_Settings->settings = array( 'db-version' => Email_Manager::VERSION );

			// Test that setting deep values triggers error
			$this->expectError( new PatternExpectation( '/Indirect modification of overloaded property/i' ) );
			$this->WPEM_Settings->settings['db-version'] = Email_Manager::VERSION;
		}
	} // end UnitTestWPEM_Settings
}

?>