<?php
/**
 * Initialize and display admin panel output.
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Devio\Pipedrive\{Exceptions\PipedriveException, Pipedrive as Pipedrive};
use Procoders\Cf7pd\Loader as Loader;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Handles and updates settings submitted from the admin panel.
	 * Renders the settings template with updated values.
	 */
	public function settings_callback(): void {
		$template = new Loader();
		$settings = array();

		$notification_subject_default = esc_html__( 'API Error Notification', 'connect-cf7-to-pipedrive' );

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7pd_submit_form' );
		}

		// Check for 'connect' submission.
		if ( isset( $_POST['connect'] ) ) {
			$settings['message'] = $this->getConnectionStatusMessage();
		}

		// Check for 'submit' submission.
		if ( isset( $_POST['submit'] ) ) {
			$this->updateOptionFields();
		}

		// Get saved options.
		$settings['access_token'] = get_option( 'cf7pd_access_token' );

		// Get notification_subject, set default if not exists.
		$settings['notification_subject'] = get_option( 'cf7pd_notification_subject', $notification_subject_default );
		$settings['notification_send_to'] = get_option( 'cf7pd_notification_send_to' );
		$settings['uninstall']            = get_option( 'cf7pd_uninstall' );

		$template->set_template_data(
			array(
				'template' => $template,
				'settings' => $settings,
			)
		)->get_template_part( 'admin/settings' );
	}

	/**
	 * Returns an array containing a status message and a success flag for the connection status
	 *
	 * @return array The status message and a success flag
	 */
	private function getConnectionStatusMessage(): array {
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7pd_submit_form' );
		}
		if ( ! isset( $_POST['cf7pd_access_token'] ) ) {
			return array();
		}
		$connectionStatus = $this->setToken( sanitize_text_field( wp_unslash( $_POST['cf7pd_access_token'] ) ) );

		return array(
			'text'    => $connectionStatus
				? esc_html__( 'Connection Successful.', 'connect-cf7-to-pipedrive' )
				: esc_html__( 'Connection Error.', 'connect-cf7-to-pipedrive' ),
			'success' => $connectionStatus,
		);
	}

	/**
	 * Iterates over defined option fields and updates each with the submitted value
	 * Casts to int if the option is 'cf7pd_uninstall'
	 */
	private function updateOptionFields(): void {
		$option_fields = array(
			'cf7pd_notification_subject',
			'cf7pd_notification_send_to',
			'cf7pd_uninstall',
		); // define the option fields.

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7pd_submit_form' );
		}

		// perform update_option for each option field.
		foreach ( $option_fields as $option_field ) {
			$field_value = isset( $_POST[ $option_field ] )
				? sanitize_text_field( wp_unslash( $_POST[ $option_field ] ) )
				: null;

			// update_option only if $field_value is not null; casting to int if it's 'cf7pd_uninstall'.
			update_option(
				$option_field,
				'cf7pd_uninstall' === $option_field
					? (int) $field_value
					: $field_value
			);
		}
	}

	/**
	 * Sets the access token for Pipedrive API and updates the option value in the database.
	 *
	 * @param string $token The access token to be set.
	 *
	 * @return bool Returns true if the token is valid and set successfully, false otherwise.
	 */
	public function setToken( string $token ): bool {
		try {
			$pipedrive = new Pipedrive( $token );
			$response  = $pipedrive->organizations->all();
		} catch ( PipedriveException $e ) {
			return false;
		}
		$this->reset_forms();
		update_option( 'cf7pd_access_token', $token );
		return true;
	}
	private function reset_forms(): void {
		$all_forms_ids = get_posts( array(
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_type'      => 'cf7pd_contact_form'
		) );
		foreach ( $all_forms_ids as $id ) {
			delete_post_meta( $id, 'cf7pd_active' );
		}
	}

}
