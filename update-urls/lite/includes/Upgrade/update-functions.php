<?php
/**
 * Functions for updating data, used by the background updater.
 */

defined( 'ABSPATH' ) || exit;

use KaizenCoders\UpdateURLS\Option;

/* --------------------- 1.0.0 (Start)--------------------------- */

/**
* Update DB version
 *
 * @since 1.0.0
 */
function kc_uu_update_123_add_installed_on_option() {
	Option::add( 'installed_on', time(), true );
}

/* --------------------- 1.0.0 (End)--------------------------- */

/* --------------------- 1.5.0 (Start)--------------------------- */

use KaizenCoders\UpdateURLS\Install;

/**
 * Create custom tables for history and profiles.
 *
 * @since 1.5.0
 */
function kc_uu_update_150_create_custom_tables() {
	Install::create_tables();
}

/**
 * Migrate history and profiles data from wp_options to custom tables.
 *
 * @since 1.5.0
 */
function kc_uu_update_150_migrate_data() {
	global $wpdb;

	// Migrate history.
	$history = get_option( 'kc_uu_history', [] );

	if ( is_array( $history ) && ! empty( $history ) ) {
		$table = $wpdb->prefix . 'kc_uu_history';

		foreach ( $history as $entry ) {
			$wpdb->insert(
				$table,
				[
					'entry_id'         => isset( $entry['id'] ) ? $entry['id'] : '',
					'date'             => isset( $entry['date'] ) ? $entry['date'] : current_time( 'mysql' ),
					'search_for'       => isset( $entry['search_for'] ) ? $entry['search_for'] : '',
					'replace_with'     => isset( $entry['replace_with'] ) ? $entry['replace_with'] : '',
					'tables'           => isset( $entry['tables'] ) ? wp_json_encode( $entry['tables'] ) : '[]',
					'case_insensitive' => ! empty( $entry['case_insensitive'] ) ? 1 : 0,
					'replace_guids'    => ! empty( $entry['replace_guids'] ) ? 1 : 0,
					'total_changes'    => isset( $entry['total_changes'] ) ? absint( $entry['total_changes'] ) : 0,
					'total_updates'    => isset( $entry['total_updates'] ) ? absint( $entry['total_updates'] ) : 0,
					'undone'           => ! empty( $entry['undone'] ) ? 1 : 0,
					'details'          => isset( $entry['details'] ) ? wp_json_encode( $entry['details'] ) : null,
				],
				[ '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s' ]
			);
		}

		delete_option( 'kc_uu_history' );
	}

	// Migrate profiles.
	$profiles = get_option( 'kc_uu_profiles', [] );

	if ( is_array( $profiles ) && ! empty( $profiles ) ) {
		$table = $wpdb->prefix . 'kc_uu_profiles';

		foreach ( $profiles as $name => $profile ) {
			$wpdb->insert(
				$table,
				[
					'name'             => $name,
					'search_for'       => isset( $profile['search_for'] ) ? $profile['search_for'] : '',
					'replace_with'     => isset( $profile['replace_with'] ) ? $profile['replace_with'] : '',
					'select_tables'    => isset( $profile['select_tables'] ) ? wp_json_encode( $profile['select_tables'] ) : '[]',
					'case_insensitive' => isset( $profile['case_insensitive'] ) ? $profile['case_insensitive'] : 'off',
					'replace_guids'    => isset( $profile['replace_guids'] ) ? $profile['replace_guids'] : 'off',
				],
				[ '%s', '%s', '%s', '%s', '%s', '%s' ]
			);
		}

		delete_option( 'kc_uu_profiles' );
	}
}

/* --------------------- 1.5.0 (End)--------------------------- */