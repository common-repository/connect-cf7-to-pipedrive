<?php
/**
 * Initialize and display main admin panel output.
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Devio\Pipedrive\Exceptions\PipedriveException as PipedriveException;
use Devio\Pipedrive\Pipedrive as Pipedrive;
use Procoders\Cf7pd\Functions as Functions;
use Procoders\Cf7pd\Includes\PipeDriveFields as PipeDriveFields;
use Procoders\Cf7pd\Loader as Loader;

/**
 * Class Functions
 */
class Init {

	/**
	 * Initializes the callback function for the given request
	 *
	 * @return void
	 */
	public function init_callback(): void {
		$template = new Loader();
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7pd_submit_form' );
		}

		if ( ! isset( $_REQUEST['id'] ) ) {
			// Lets Get all forms.
			$this->getFormList();

			return;
		}

		$id = ctype_digit( sanitize_text_field( $_REQUEST['id'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 1;

		if ( isset( $_POST['submit'] ) ) {
			$this->updateMetaFields( $id );
			$message = $this->getSubmitStatusMessage();
		}

		$form            = $this->getFormData( $id );
		$form['message'] = $message ?? false;
		$template->set_template_data(
			array(
				'template' => $template,
				'form'     => $form,
			)
		)->get_template_part( 'admin/form' );
	}

	/**
	 * Returns an array containing form data for the given ID
	 *
	 * @param int $id The ID of the form.
	 *
	 * @return array The form data including various fields and metadata
	 */
	private function getFormData( int $id ): array {
		$_form     = get_post_meta( $id, '_form', true );
		$pd_fields = $this->getFields( array_keys( PipeDriveFields::$breakFields ) );

		return array(
			'cf7pd_active'        => get_post_meta( $id, 'cf7pd_active', true ),
			'cf7pd_update_person' => get_post_meta( $id, 'cf7pd_update_person', true ),
			'cf7pd_update_org'    => get_post_meta( $id, 'cf7pd_update_org', true ),
			'cf7pd_fields'        => get_post_meta( $id, 'cf7pd_fields', true ),
			'title'               => get_the_title( $id ),
			'_form'               => $_form,
			'_labels_list'        => $this->labelsList(),
			'pd_fields'           => $pd_fields,
			'cf7_fields'          => $this->get_cf7_fields( $_form ),
		);
	}

	/**
	 * Returns an array containing labels related to Pipedrive integration
	 *
	 * @return array The labels for different entities in Pipedrive
	 */
	private function labelsList(): array {
		$groups       = array_keys( PipeDriveFields::$objects );
		$labels       = [];
		$access_token = esc_attr( get_option( 'cf7pd_access_token' ) );
		$pipedrive    = new Pipedrive( $access_token );

		$labels['persons']       = get_option( 'cf7pd_persons' );
		$labels['organizations'] = get_option( 'cf7pd_organizations' );
		foreach ( $labels as $key => $label ) {
			foreach ( $label as $label_ ) {
				if ( $label_->key === 'label' ) {
					$labels[ $key ] = $label_->options;
				}
			}
		}
		$labels['lead'] = $pipedrive->leads()->labels()->getData();

		return $labels;
	}

	/**
	 * Returns an array containing the fields information for each group
	 *
	 * @param array $groups The array of groups
	 *
	 * @return array|null The array containing the fields information or null if no fields found
	 */
	private function getFields( array $groups ): ?array {
		$fields = [];
		foreach ( $groups as $group ) {
			$fields_ = get_option( 'cf7pd_' . $group );
			if ( $fields_ ) {
				foreach ( $fields_ as $key => $field_ ) {
					if ( is_object( $field_ ) ) {
						if ( in_array( $field_->key, PipeDriveFields::$breakFields[ $group ] ) ) {
							continue;
						}
						$fields[ $group ][ $key ]['id']       = $field_->id;
						$fields[ $group ][ $key ]['name']     = $field_->key;
						$fields[ $group ][ $key ]['label']    = $field_->name;
						$fields[ $group ][ $key ]['type']     = $field_->field_type;
						$fields[ $group ][ $key ]['required'] = $field_->mandatory_flag;
					} else {
						if ( in_array( $field_['key'], PipeDriveFields::$breakFields[ $group ] ) ) {
							continue;
						}
						$fields[ $group ][ $key ]['id']       = $field_['id'];
						$fields[ $group ][ $key ]['name']     = $field_['key'];
						$fields[ $group ][ $key ]['label']    = $field_['name'];
						$fields[ $group ][ $key ]['type']     = $field_['field_type'];
						$fields[ $group ][ $key ]['required'] = $field_['mandatory_flag'];
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Retrieves a list of contact forms and their details
	 *
	 * @return void
	 */
	public function getFormList(): void {
		$template = new loader();
		if ( $this->syncCF7withPipeDrive() ) {
			$forms = new \WP_Query(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'order'          => 'ASC',
					'posts_per_page' => - 1,
				)
			);

			$forms_array = array();
			while ( $forms->have_posts() ) {
				$forms->the_post();
				$id                           = get_the_ID();
				$forms_array[ $id ]['title']  = get_the_title();
				$forms_array[ $id ]['status'] = get_post_meta( get_the_ID(), 'cf7pd_active', true );
				$forms_array[ $id ]['link']   = menu_page_url( functions::get_plugin_slug(), 0 ) . '&id=' . $id;
			}
			wp_reset_postdata();

			$template->set_template_data(
				array(
					'template' => $template,
					'forms'    => $forms_array,
				)
			)->get_template_part( 'admin/formList' );
		} else {
			// TODO: Lets make the error template.
			$template->set_template_data(
				array(
					'template' => $template,
					'forms'    => false,
				)
			)->get_template_part( 'admin/formList' );
		}
	}

	/**
	 * Syncs Contact Form 7 with PipeDrive by updating the options with required data from PipeDrive
	 *
	 * @return bool True if the sync is successful, otherwise False
	 */
	private function syncCF7withPipeDrive(): bool {
		$access_token = get_option( 'cf7pd_access_token' );
		try {
			$pipedrive = new Pipedrive( $access_token );
			update_option( 'cf7pd_persons', $pipedrive->personFields->all()->getData() ?? [] );
			update_option( 'cf7pd_organizations', $pipedrive->organizationFields()->all()->getData() ?? [] );
			update_option( 'cf7pd_notes', $pipedrive->noteFields()->all()->getData() ?? [] );
			update_option( 'cf7pd_lead', PipeDriveFields::$lead );
			update_option( 'cf7pd_file', PipeDriveFields::$file );
			update_option( 'cf7pd_marketing_status', PipeDriveFields::$marketing_status );
		} catch ( PipedriveException $e ) {
			Functions::return_error( $e->getMessage() );
		}

		return true;
	}

	/**
	 * Returns an array containing a status message and a success flag for the submission status
	 *
	 * @return array The status message and a success flag
	 */
	private function getSubmitStatusMessage(): array {
		return array(
			'text'    => esc_html__( 'Integration settings saved.', 'connect-cf7-to-pipedrive' ),
			'success' => true,
		);
	}

	/**
	 * Updates meta fields for a specifieds ID
	 *
	 * @param int $id The ID of the post to update meta fields for.
	 *
	 * @return void
	 */
	private function updateMetaFields( int $id ): void {
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'cf7pd_submit_form' );
		}
		$meta_fields = array(
			'cf7pd_fields',
			'cf7pd_active',
			'cf7pd_update_person',
			'cf7pd_update_org',
		); // define the meta fields.

		// perform update_post_meta for each option field.
		foreach ( $meta_fields as $meta_field ) {
			$field_value = isset( $_POST[ $meta_field ] ) ? sanitize_post( wp_unslash( $_POST[ $meta_field ] ) ) : null;
			if ( 'cf7pd_active' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			if ( 'cf7pd_update_person' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			if ( 'cf7pd_update_org' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			// update_post_meta if $field_value is not null.
			if ( null !== $field_value ) {
				update_post_meta(
					$id,
					$meta_field,
					$field_value
				);
			}
		}
	}

	/**
	 * Returns an array containing CF7 fields extracted from the given form
	 *
	 * @param string $_form The form content from which to extract CF7 fields.
	 *
	 * @return array|null The CF7 fields extracted from the form content, or null if no fields found.
	 */
	private function get_cf7_fields( string $_form ): bool|array {
		preg_match_all( '#\[([^\]]*)\]#', $_form, $matches );
		if ( null === $matches ) {
			return false;
		}

		$cf7_fields = array();
		foreach ( $matches[1] as $match ) {
			$match_explode = explode( ' ', $match );
			$field_type    = str_replace( '*', '', $match_explode[0] );
			// Continue in iteration if the field type is 'submit'.
			if ( 'submit' === $field_type ) {
				continue;
			}
			if ( isset( $match_explode[1] ) ) {
				$cf7_fields[ $match_explode[1] ] = array(
					'key'  => $match_explode[1],
					'type' => $field_type,
				);
			}
		}

		return $cf7_fields;
	}

}
