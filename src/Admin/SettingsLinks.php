<?php
/**
 * Add settings links to the plugin screen.
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7pd\Functions as Functions;

/**
 * Add settings links to the plugin screen.
 */
class SettingsLinks {

	/**
	 * Main class runner.
	 */
	public static function run() {
		add_filter(
			'plugin_action_links_' . Functions::get_plugin_path(),
			array( static::class, 'add_settings_link' )
		);
	}

	/**
	 * Add a settings link to the plugin's options.
	 *
	 * Add a settings link on the WordPress plugin's page.
	 *
	 * @param array $links Array of plugin options.
	 *
	 * @return array $links Array of plugin options
	 * @since 1.0.0
	 * @access public
	 *
	 * @see run
	 */
	public static function add_settings_link( array $links ): array {
		$settings_url = admin_url( 'admin.php?page=cfhs_settings' );
		$site_url     = '#';
		if ( current_user_can( 'manage_options' ) ) {
			$options_link = sprintf( '<a href="%s">%s</a>', esc_url( $settings_url ), _x( 'Settings', 'Options link', 'connect-cf7-to-pipedrive' ) );
			array_unshift( $links, $options_link );
			$site_link = sprintf( '<a href="%s">%s</a>', esc_url( $site_url ), _x( 'ProCoders', 'Plugin site', 'connect-cf7-to-pipedrive' ) );
			$links[]   = $site_link;
		}

		return $links;
	}
}
