<?php
/**
 * Helper functions for the plugin.
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd;

use Procoders\Cf7pd\Loader as Loader;

/**
 * Class Functions
 */
class Functions {


	/**
	 * Return the plugin slug.
	 *
	 * @return string plugin slug.6
	 */
	public static function get_plugin_slug(): string {
		return dirname( plugin_basename( CF7PD_FILE ) );
	}

	/**
	 * Return the basefile for the plugin.
	 *
	 * @return string base file for the plugin.
	 */
	public static function get_plugin_file(): string {
		return plugin_basename( CF7PD_FILE );
	}

	/**
	 * Return the plugin path.
	 *
	 * @return string path to plugin
	 */
	public static function get_plugin_path(): string {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Return the version for the plugin.
	 *
	 * @return float version for the plugin.
	 */
	public static function get_plugin_version(): float {
		return CF7PD_VERSION;
	}

	/**
	 * Return the license server
	 *
	 * @return string lisense server url
	 */
	public static function get_license_server(): string {
		return CF7PD_LSERVER;
	}

	public static function return_error( string $message ): void {
		$template = new loader();
		$template->set_template_data(
			array(
				'template' => $template,
				'message'  => array(
					'success' => false,
					'text'    => $message,
				)
			)
		)->get_template_part( 'admin/message' );
		die();
	}
}
