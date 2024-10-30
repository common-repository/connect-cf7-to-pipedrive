<?php
/**
 * Initialize and display admin logs page
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\Cf7pd\Functions as Functions;
use Procoders\Cf7pd\Loader as Loader;

/**
 * Class Functions
 */
class Logs {

	/**
	 * This function handles logging of errors. It uses a file located by life_path as a storage medium for errors.
	 * The function opens the file, reads its content and then closes it.
	 *
	 * @return void
	 */
	public static function error_logs_callback(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$template  = new Loader();
		$file_path = Functions::get_plugin_path() . 'Logs/debug.log';

		$file_data = $wp_filesystem->get_contents( $file_path );
		if ( false === $file_data ) {
			$file_data = esc_html__( 'No Error Logs found.', 'connect-cf7-to-pipedrive' );
		}
		$template->set_template_data(
			array(
				'template'  => $template,
				'file_data' => $file_data,
			)
		)->get_template_part( 'admin/logs' );
	}

	/**
	 * Method to handle error response.
	 *
	 * @param object $response The response object.
	 * @param int|null $form_id The ID of the form (optional).
	 *
	 * @return void
	 */
	public static function handleErrorResponse( string $message, ?int $form_id = null ): bool {
		global $wp_filesystem;

		// Include the WP_Filesystem class.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// Initialize the WP_Filesystem.
		WP_Filesystem();

		// Check if directory and file exist, if not create them
		$log_path = Functions::get_plugin_path() . 'Logs';
		$log_file = $log_path . '/debug.log';

		if ( ! $wp_filesystem->is_dir( $log_path ) ) {
			$wp_filesystem->mkdir( $log_path );
		}

		if ( ! $wp_filesystem->exists( $log_file ) ) {
			$wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE ); // empty file
		}

		$log     = $wp_filesystem->get_contents( $log_file );
		$log     .= 'Message: ' . $message . "\n";
		$send_to = get_option( 'cfhs_notification_send_to' );
		$log     .= 'Date: ' . gmdate( 'Y-m-d H:i:s' ) . "\n\n";
		$log     .= 'Form id: ' . $form_id ?? 'Not set' . "\n\n";

		if ( $send_to ) {
			$to      = $send_to;
			$subject = get_option( 'cfhs_notification_subject' );
			$body    = '<ul style="list-style:none;padding-left:0; margin-left:0;" >';
			$body    .= '<li><strong>Form ID:</strong> ' . $form_id ?? 'Not set' . '</li>';
			$body    .= '<li><strong>Message:</strong> ' . $message . '</li>';
			$body    .= '<li><strong>Date:</strong> ' . gmdate( 'Y-m-d H:i:s' ) . '</li>';
			$body    .= '</ul>';

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
			);

			wp_mail( $to, $subject, $body, $headers );
		}
		$wp_filesystem->put_contents( Functions::get_plugin_path() . 'Logs/debug.log', $log, FS_CHMOD_FILE );

		// pre-defining permissions to 0644 i.e. FS_CHMOD_FILE.
		return true;

	}
}
