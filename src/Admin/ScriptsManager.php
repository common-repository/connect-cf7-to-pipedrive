<?php
/**
 * License managing class
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * Create the admin menu.
 */
class ScriptsManager {

	/**
	 * Main class runner.
	 */
	public static function run(): void {
		add_action( 'admin_enqueue_scripts', array( static::class, 'admin_assets' ) );
	}

	/**
	 * Enqueues assets for the admin area.
	 *
	 * @return void
	 */
	public static function admin_assets(): void {
		global $hook_suffix;
		// Check if the current page is a plugin page.
		if ( str_contains( $hook_suffix, 'cf7-' ) ) {
			wp_enqueue_style( 'cf7pd-style', plugins_url( 'Assets/css/admin.css', __FILE__ ), array(), CF7PD_VERSION, 'all' );
			wp_enqueue_script( 'cf7pd-script', plugins_url( 'Assets/js/admin.js', __FILE__ ), array( 'jquery' ), CF7PD_VERSION, true );
		}
	}
}
