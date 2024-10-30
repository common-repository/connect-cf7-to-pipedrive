<?php

namespace Procoders\Cf7pd\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

class PipeDriveFields {
	public static array $objects = array(
		'persons',
		'organizations',
		'notes',
	);

	public static array $lead = array(
		[
			'id'             => 999,
			'key'            => 'title',
			'name'           => 'Title',
			'field_type'     => 'String',
			'mandatory_flag' => false
		]
	);

	public static array $file = array(
		[
			'id'             => 998,
			'key'            => 'file',
			'name'           => 'File',
			'field_type'     => 'File',
			'mandatory_flag' => false
		],
	);

	public static array $marketing_status = array(
		[
			'id'             => 997,
			'key'            => 'subscription',
			'name'           => 'Subscription',
			'field_type'     => 'boolean',
			'mandatory_flag' => false
		],
	);
	public static array $breakFields = array(
		'persons'          => array(
			'doi_status',
			'add_time',
			'update_time',
			'open_deals_count',
			'next_activity_date',
			'last_activity_date',
			'id',
			'org_id',
			'won_deals_count',
			'lost_deals_count',
			'closed_deals_count',
			'activities_count',
			'done_activities_count',
			'undone_activities_count',
			'email_messages_count',
			'picture_id',
			'last_incoming_mail_time',
			'last_outgoing_mail_time',
			'owner_id',
			'visible_to',

		),
		'organizations'    => array(
			'open_deals_count',
			'add_time',
			'update_time',
			'next_activity_date',
			'last_activity_date',
			'id',
			'won_deals_count',
			'lost_deals_count',
			'closed_deals_count',
			'activities_count',
			'done_activities_count',
			'undone_activities_count',
			'email_messages_count',
			'picture_id',
			'address_subpremise',
			'address_street_number',
			'address_route',
			'address_sublocality',
			'address_locality',
			'address_admin_area_level_1',
			'address_admin_area_level_2',
			'address_country',
			'address_postal_code',
			'address_formatted_address',
			'owner_id',
			'visible_to',
		),
		'notes'            => array(
			'id',
			'org_id',
			'person_id',
			'deal_id',
			'add_time',
			'update_time',
			'user_id',
			'lead_id',
			'pinned_to_deal_flag',
			'pinned_to_organization_flag',
			'pinned_to_person_flag',
			'pinned_to_lead_flag',
		),
		'lead'             => array(),
		'file'             => array(),
		'marketing_status' => array()
	);
}
