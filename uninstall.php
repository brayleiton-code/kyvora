<?php
/**
 * Uninstall. Removes local options, transients, and logs. Does not call any remote service.
 *
 * @package WPPYSHardening
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wppys_settings' );

global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_wppys_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_wppys_' ) . '%'
	)
);

$upload_dir = wp_upload_dir();
$log_dir    = $upload_dir['basedir'] . '/wppys-logs';
if ( is_dir( $log_dir ) ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;
	if ( $wp_filesystem ) {
		$wp_filesystem->delete( $log_dir, true );
	}
}
