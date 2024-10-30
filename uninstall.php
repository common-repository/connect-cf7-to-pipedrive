<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$uninstall = get_option( 'cf7pd_uninstall' );
if ( $uninstall ) {
	delete_option( 'cf7pd_access_token' );
	delete_option( 'cf7pd_persons' );
	delete_option( 'cf7pd_organizations' );
	delete_option( 'cf7pd_notes' );
	delete_option( 'cf7pd_lead' );
	delete_option( 'cf7pd_file' );
	delete_option( 'cf7pd_marketing_status' );
}