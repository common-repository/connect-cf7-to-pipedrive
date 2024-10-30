<?php
/**
 * Initialize the admin menu.
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7pd\Admin\Init as Init;
use Procoders\Cf7pd\Admin\Logs as Logs;
use Procoders\Cf7pd\Admin\Settings as Settings;
use Procoders\Cf7pd\Functions as Functions;

/**
 * Create the admin menu.
 */
class RegisterMenu {

	/**
	 * Main class runner.
	 */
	public static function run(): void {
		add_action( 'admin_menu', array( static::class, 'init_menu' ) );
	}

	/**
	 * Register the plugin menu.
	 */
	public static function init_menu(): void {

		$init     = new Init();
		$settings = new Settings();
		$logs     = new Logs();

		$slug = functions::get_plugin_slug();

		add_menu_page(
			esc_html__( 'Contact Form 7 - PipeDrive Integration', 'connect-cf7-to-pipedrive' ),
			esc_html__( 'CF7 - PipeDrive', 'connect-cf7-to-pipedrive' ),
			'manage_options',
			$slug,
			array( $init, 'init_callback' ),
			'dashicons-forms'
		);

		add_submenu_page(
			$slug,
			esc_html__( 'CF7 - PipeDrive: Settings', 'connect-cf7-to-pipedrive' ),
			esc_html__( 'Settings', 'connect-cf7-to-pipedrive' ),
			'manage_options',
			'cfpd_settings',
			array( $settings, 'settings_callback' ),
		);

		add_submenu_page(
			$slug,
			esc_html__( 'CF7 - PipeDrive: Error Logs', 'connect-cf7-to-pipedrive' ),
			esc_html__( 'Error Logs', 'connect-cf7-to-pipedrive' ),
			'manage_options',
			'cfpd_api_error_logs',
			array( $logs, 'error_logs_callback' ),
		);

	}
}
