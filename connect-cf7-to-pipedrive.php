<?php
/**
 * Plugin Name:       Connect CF7 to PipeDrive
 * Plugin URI:        #
 * Description:       CF7 To PipeDrive plugin allows you to send Contact Form 7 data to PipeDrive.
 * Version:           1.0.9
 * Requires at least: 5.3
 * Requires PHP:      8.0
 * Author:            ProCoders
 * Author URI:        https://procoders.tech/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       connect-cf7-to-pipedrive
 * Domain Path:       /languages
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd;

use \Procoders\Cf7pd\Admin\RegisterMenu as Menu;
use \Procoders\Cf7pd\Admin\SettingsLinks as Links;
use \Procoders\Cf7pd\Admin\ScriptsManager as Scripts;

define( 'CF7PD_VERSION', '1.0.9' );
define( 'CF7PD_FILE', __FILE__ );
define( 'CF7PD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * cf7pd class.
 */
class cf7pd {

	/**
	 * Holds the class instance.
	 *
	 * @var cf7pd $instance
	 */
	private static ?cf7pd $instance = null;

	/**
	 * Return an instance of the class
	 *
	 * @return cf7pd class instance.
	 * @since 1.0.0
	 */
	public static function get_instance(): cf7pd {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class initializer.
	 */
	public function plugins_loaded(): void {
		load_plugin_textdomain(
			'connect-cf7-to-pipedrive',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		// Register the admin menu.
		Menu::run();
		Links::run();
		// Register Script.
		Scripts::run();
		$submission = new Includes\Submission();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wpcf7_before_send_mail', array( $submission, 'init' ), 10, 3 );
	}

	/**
	 * Init plugin.
	 */
	public function init(): void {
		// Silent.
	}
}

add_action(
	'plugins_loaded',
	function () {
		$cf7pd = cf7pd::get_instance();
		$cf7pd->plugins_loaded();
	}
);