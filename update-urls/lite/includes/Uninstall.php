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
	}

}