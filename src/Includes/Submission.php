<?php
/**
 * Submisttion class for CF7
 *
 * @package Cf7pd
 */

namespace Procoders\Cf7pd\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Devio\Pipedrive\Exceptions\PipedriveException as PipedriveException;
use Devio\Pipedrive\Pipedrive as Pipedrive;
use Procoders\Cf7pd\Admin\Logs as Logs;
use Procoders\Cf7pd\Functions as Functions;


/**
 * Create or update a person in Pipedrive.
 *
 * @param Pipedrive $pipedrive The Pipedrive instance.
 * @param string $name The name of the person.
 * @param string $email The email address of the person.
 * @param string $phone The phone number of the person.
 * @param int|null $org_id The ID of the associated organization (optional).
 *
 * @return int The ID of the created or updated person.
 */
class Submission {

	/**
	 * Initialize the submission process.
	 *
	 * @param mixed $form The form being submitted.
	 * @param boolean $abort Flag to indicate whether the submission should be aborted.
	 * @param mixed $object The object being submitted.
	 *
	 * @return void
	 */
	public function init( $form, &$abort, $object ): void {
		$access_token = get_option( 'cf7pd_access_token' );

		// PCF7_Submission i`ts from contact form 7.
		$submission = \WPCF7_Submission::get_instance();

		if ( ! $submission ) {
			Functions::return_error( 'Contact Form 7 plugin is required.' );
		}
		//$post_id = $submission->get_meta( 'container_post_id' );
		$request = $submission->get_posted_data();
		$form_id = $submission->get_contact_form()->id();

		if ( $form_id && '0' == ! get_post_meta( $form_id, 'cf7pd_active', true ) ) {
			$cf7pd_fields = get_post_meta( $form_id, 'cf7pd_fields', true );

			if ( null !== $cf7pd_fields ) {
				$data = $this->prepare_data( $request, $cf7pd_fields );
				$data = $this->process_data( $data );

				$cf7pd_update_person = get_post_meta( $form_id, 'cf7pd_update_person', true );
				$cf7pd_update_org    = get_post_meta( $form_id, 'cf7pd_update_org', true );

				try {
					$pipedrive                = new Pipedrive( $access_token );
					$personFirstName          = $data['persons']['first_name'] ?? '';
					$personName               = $data['persons']['name'] ?? $personFirstName;
					$personLastName           = $data['persons']['last_name'] ?? '';
					$personPhone              = $data['persons']['phone'] ?? '';
					$personLabel              = $data['persons']['label'] ?? 1;
					$personFile               = $data['file']['file'] ?? '';
					$marketingStatus          = isset( $data['marketing']['status_subscription'] ) && '1' === $data['marketing']['status_subscription']
						? 'subscribed'
						: 'unsubscribed';
					$personEmail              = $data['persons']['email'] ?? '';
					$notesContent             = $data['notes']['content'] ?? '';
					$leadTitle                = $data['lead']['title'] ?? $personName;
					$leadLabel                = $data['lead']['label'] ?? '';
					$organizationAddress      = $data['organizations']['address'] ?? '';
					$organizationName         = $data['organizations']['name'] ?? '';
					$organizationLabel        = $data['organizations']['label'] ?? '';
					$organizationPeople       = $data['organizations']['people'] ?? '';
					$personsConfigurable      = $data['persons']['configurable'] ?? [];
					$organizationConfigurable = $data['organizations']['configurable'] ?? [];
					$organizationId           = null;

					// Check and create/update organization only when address is not empty
					if ( ! empty( $organizationAddress ) || ! empty( $organizationName ) ) {
						$organizationId = $this->createOrUpdateOrganization(
							$pipedrive,
							$personName,
							$organizationAddress,
							$organizationName,
							$organizationLabel,
							$organizationPeople,
							$organizationConfigurable,
							$cf7pd_update_org
						);
					}

					// Check and create/update person
					$personId = $this->createOrUpdatePerson(
						$pipedrive,
						$personName,
						$personFirstName,
						$personLastName,
						$personEmail,
						$personPhone,
						$personLabel,
						$marketingStatus,
						$personsConfigurable,
						$cf7pd_update_person,
						$organizationId
					);

					// Create lead
					if ( $leadTitle && isset( $personId ) ) {
						$leadId = $this->createLead(
							$pipedrive,
							$leadTitle,
							$personId,
							$leadLabel,
							$organizationId
						);
					}

					// Create Notes
					if ( $notesContent && isset( $leadId ) ) {
						$this->createNotes(
							$pipedrive,
							$notesContent,
							$leadId,
							$personId,
							$organizationId
						);
					}
					//Create File
					if ( ! empty( $personFile ) && isset( $leadId ) ) {
						$this->createFile(
							$submission,
							$pipedrive,
							$cf7pd_fields,
							$personId,
							$organizationId,
							$leadId
						);
					}
				} catch ( PipedriveException $e ) {
					// echo 'Error: ' . $e->getMessage();
					Logs::handleErrorResponse( $e->getMessage(), $form_id );
					$submission->set_status( 'validation_failed' );
					$abort = true;
					$submission->set_response( 'API submission errors: ' . $this->error_special_cases( $e->getMessage() ) );
				}
			}
		}
	}

	/**
	 * Creates a file in the Pipedrive system using the uploaded files from the form submission.
	 *
	 * @param \WPCF7_Submission $submission The form submission containing the uploaded files.
	 * @param Pipedrive $pipedrive The Pipedrive instance to interact with the Pipedrive API.
	 * @param array $attachment_fields The fields that contain attachments.
	 * @param int $personId The ID of the person associated with the file.
	 * @param int|null $organizationId The ID of the organization associated with the file, or null.
	 * @param string $leadId The ID of the lead associated with the file.
	 *
	 * @return int The ID of the created file in the Pipedrive system.
	 */
	private function createFile(
		\WPCF7_Submission $submission,
		Pipedrive $pipedrive,
		array $attachment_fields,
		int $personId,
		?int $organizationId,
		string $leadId
	): int {
		$files = $submission->uploaded_files();
		if ( ! $files ) {
			return false;
		}
		$res = new \stdClass;

		foreach ( $attachment_fields as $attachment_field_key => $attachment_field ) {
			if ( ! isset( $files[ $attachment_field_key ] ) || ! $files[ $attachment_field_key ] ) {
				continue;
			}

			$file = is_array( $files[ $attachment_field_key ] )
				? $files[ $attachment_field_key ][0]
				: $files[ $attachment_field_key ];

			$file_res = new \SplFileInfo( $file );
			$res      = $pipedrive->files->add( [
				'file'      => $file_res,
				'person_id' => $personId,
				'lead_id'   => $leadId,
				'org_id'    => $organizationId,
			] )->getData();
		}

		return $res->id;
	}

	/**
	 * Creates a note in Pipedrive with the provided content and associated IDs.
	 *
	 * @param Pipedrive $pipedrive Pipedrive instance to interact with the Pipedrive API.
	 * @param string $content The content of the note to be created.
	 * @param string $lead_id The ID of the lead to associate the note with.
	 * @param int $person_id The ID of the person to associate the note with.
	 * @param int|null $organizationId The ID of the organization to associate the note with, null if not applicable.
	 *
	 * @return int The ID of the created note.
	 */
	private function createNotes(
		Pipedrive $pipedrive,
		string $content,
		string $lead_id,
		int $person_id,
		?int $organizationId
	): int {
		$notesData = [
			"content"   => $content,
			"lead_id"   => $lead_id,
			"person_id" => $person_id,
			"add_time"  => current_time( 'mysql' ),
		];
		if ( $organizationId ) {
			$notesData['org_id'] = $organizationId;
		}
		$note = $pipedrive->notes->add( $notesData )->getData();

		return $note->id;
	}

	/**
	 * Creates a new lead using the provided parameters and returns the created lead's ID.
	 *
	 * @param Pipedrive $pipedrive An instance of the Pipedrive client.
	 * @param string $title The title of the lead.
	 * @param int $person_id The ID of the person associated with the lead.
	 * @param string $label The label for the lead.
	 * @param int|null $organizationId The ID of the organization associated with the lead, if any.
	 *
	 * @return string The ID of the created lead.
	 */
	private function createLead(
		Pipedrive $pipedrive,
		string $title,
		int $person_id,
		string $label,
		?int $organizationId
	): string {
		$leadData =
			[
				'title'     => $title,
				'person_id' => $person_id,
			];

		if ( ! empty( $label ) ) {
			$leadData['label_ids'] = [ $label ];
		}
		if ( $organizationId ) {
			$leadData['organization_id'] = $organizationId;
		}
		$lead = $pipedrive->leads->add( $leadData )->getData();

		return $lead->id;
	}

	/**
	 * Creates or updates an organization in Pipedrive based on the provided data.
	 *
	 * @param Pipedrive $pipedrive An instance of the Pipedrive client.
	 * @param string $person_name The name of the person associated with the organization.
	 * @param string $address The address associated with the organization.
	 * @param string $org_name The name of the organization.
	 * @param string $label A label for categorization.
	 * @param string $people The number of people associated with the organization.
	 * @param array $configurable Additional configurable data for the organization.
	 * @param string $update A flag indicating whether to update (if '1') or create (if '0') the organization.
	 *
	 * @return int The ID of the created or updated organization.
	 */
	private function createOrUpdateOrganization(
		Pipedrive $pipedrive,
		string $person_name,
		string $address,
		string $org_name,
		string $label,
		string $people,
		array $configurable,
		string $update
	): int {
		$name          = ! empty( $org_name ) ? $org_name : $person_name;
		$organizations = $pipedrive->organizations->search( $name, [ 'name' ] )->getData();

		$organizationData = array_merge(
			[
				'name'         => $name,
				'address'      => $address,
				'label'        => $label,
				'people_count' => $people,
			],
			$configurable
		);

		if ( count( $organizations->items ) > 0 && $update === '1' ) {
			$organization = $pipedrive->organizations->update( $organizations->items[0]->item->id, $organizationData )->getData();
		} else {
			$organization = $pipedrive->organizations->add( $organizationData )->getData();
		}

		return $organization->id;
	}

	/**
	 * Creates or updates a person in Pipedrive.
	 *
	 * @param Pipedrive $pipedrive The Pipedrive instance.
	 * @param string $name The full name of the person.
	 * @param string $firstName The first name of the person.
	 * @param string $lastName The last name of the person.
	 * @param string $email The email address of the person.
	 * @param string $phone The phone number of the person.
	 * @param string $label A label associated with the person.
	 * @param string $marketing_status The marketing status of the person.
	 * @param array $configurable Additional configurable data for the person.
	 * @param string $update A flag to indicate whether to update the person if they already exist.
	 * @param int|null $org_id Optional organization ID associated with the person.
	 *
	 * @return int The ID of the created or updated person.
	 */
	function createOrUpdatePerson(
		Pipedrive $pipedrive,
		string $name,
		string $firstName,
		string $lastName,
		string $email,
		string $phone,
		string $label,
		string $marketing_status,
		array $configurable,
		string $update,
		int $org_id = null
	): int {

		$personData = array_merge( [
			'name'             => $name,
			'first_name'       => $firstName,
			'last_name'        => $lastName,
			'label'            => (int) $label,
			'marketing_status' => $marketing_status,
			'email'            => [
				'value'   => $email,
				'primary' => true,
				'label'   => 'Work'
			],
		], $configurable );

		if ( ! empty( $phone ) ) {
			$personData['phone'] = [
				'value'   => $phone,
				'primary' => true,
				'label'   => 'Mobile',
			];
		}
		if ( $org_id ) {
			$personData['org_id'] = $org_id;
		}

		//

		$response = $pipedrive->persons->search( $email, [ 'email' ] );
		$persons  = $response->getData();

		if ( count( $persons->items ) > 0 && $update === '1' ) {
			$person = $pipedrive->persons->update( $persons->items[0]->item->id, $personData )->getData();
		} else {
			$person = $pipedrive->persons->add( $personData )->getData();
		}

		return $person->id;
	}

	/**
	 * Prepare data for submission.
	 *
	 * @param array $request The form submission data.
	 * @param array $cf7pd_fields Fields mapping configuration.
	 *
	 * @return array Prepared data for submission.
	 */
	private function prepare_data( array $request, array $cf7pd_fields ): array {
		$data = array();
		foreach ( $cf7pd_fields as $cf7pd_field_key => $cf7pd_field ) {
			if ( isset( $cf7pd_field['key'] ) && $cf7pd_field['key'] ) {

				$value = $request[ $cf7pd_field_key ] ?? null;
				$value = $this->format_value( $value, $cf7pd_field );

				if ( null !== $value ) {
					$data[ $cf7pd_field['key'] ] = wp_strip_all_tags( $value );
				}
			}
		}

		return $data;
	}

	/**
	 * Format the value based on its type.
	 *
	 * @param mixed $value The value to be formatted.
	 * @param array $cf7pd_field Field configuration.
	 *
	 * @return mixed The formatted value.
	 */
	private function format_value( $value, array $cf7pd_field ): mixed {
		if ( is_array( $value ) ) {
			$value = implode( ';', $value );
		}

		if ( ( 'datetime' === $cf7pd_field['type'] || 'date' === $cf7pd_field['type'] ) && $value ) {
			$value = strtotime( $value ) . '000';
		}

		return $value;
	}

	/**
	 * Processes the given data and organizes it into a nested array.
	 *
	 * @param array $data The data to be processed.
	 *
	 * @return array The processed data in the form of a nested array.
	 */
	private function process_data( array $data ): array {
		$request = [];
		foreach ( $data as $key => $value ) {
			$parts = explode( '_', $key );
			$group = $parts[0];
			array_shift( $parts );

			$field_name = implode( '_', $parts );
			if ( $this->is_hash( $field_name ) ) {
				$request[ $group ]['configurable'][ $field_name ] = $value;
			} else {
				$request[ $group ][ $field_name ] = $value;
			}
		}

		return $request;
	}

	/**
	 * Validates if the given string is a valid hash.
	 *
	 * @param string $string The string to be checked.
	 *
	 * @return bool Returns true if the string is a valid hash, false otherwise.
	 */
	private function is_hash( string $string ): bool {
		$pattern = '/^[a-f0-9]{40}$/i';

		return preg_match( $pattern, $string );
	}


	/**
	 * Handle special error cases.
	 *
	 * @param string $error The error message.
	 *
	 * @return string The modified error message if it matches a special case, otherwise the original error message.
	 */
	private function error_special_cases( string $error ): string {
		switch ( trim( $error ) ) {
			case "provided dataset is not valid" :
			{
				return $error . __( ', the data sent may not be in the required structure or could be of the wrong data type. Check yours fields mapping.', 'connect-cf7-to-pipedrive' );
			}
			default:
			{
				return $error;
			}
		}
	}

}
