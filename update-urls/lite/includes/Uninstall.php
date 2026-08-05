<?php


namespace KaizenCoders\UpdateURLS;


class Uninstall {
	/**
	 * Init Uninstall
	 *
	 * @since 1.4.9
	 */
	public function init() {
		kc_uu_fs()->add_action( 'after_uninstall', [ $this, 'uninstall_cleanup' ] );
	}

	/**
	 * Delete plugin data
	 *
	 * @since 1.4.9
	 */
	public function uninstall_cleanup() {
		global $wpdb;

		// Drop custom tables.
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}kc_uu_history" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}kc_uu_profiles" );

		// Background search/replace state.
		delete_option( 'kc_uu_sr_job' );
		delete_option( 'kc_uu_sr_job_report' );
		delete_option( 'kc_uu_sr_job_lock' );
		delete_option( 'kc_uu_data' );
		delete_option( 'kc_uu_update_site_url' );
		delete_transient( 'kc_uu_results' );
	}

}