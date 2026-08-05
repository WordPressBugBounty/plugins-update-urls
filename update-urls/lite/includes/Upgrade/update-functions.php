<?php
/**
 * Functions for updating data, used by the background updater.
 */

defined( 'ABSPATH' ) || exit;

use KaizenCoders\UpdateURLS\Install;
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

/* --------------------- 1.4.2 (Start)--------------------------- */

/**
 * Set email digest defaults: enabled = true, frequency = daily.
 *
 * @since 1.4.2
 */
function kc_uu_update_142_set_email_digest_defaults() {
	Install::set_email_digest_defaults();
}

/* --------------------- 1.4.2 (End)--------------------------- */

/* --------------------- 1.5.0 (Start)--------------------------- */

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

/* --------------------- 1.5.0.1 (Start)--------------------------- */

/**
 * Add the undo journal and give existing history entries an undo status.
 *
 * History that predates the journal has nothing recorded against it, so
 * undo_recorded stays 0 and the UI reports those entries as not undoable rather
 * than offering a button that would restore a handful of rows and call it done.
 *
 * @since 1.5.0
 */
function kc_uu_update_1501_create_undo_journal() {
	global $wpdb;

	// dbDelta is idempotent: this adds the journal table and the undo columns
	// without disturbing tables that are already in shape.
	Install::create_tables();

	$table = $wpdb->prefix . 'kc_uu_history';

	$wpdb->query( "UPDATE {$table} SET undo_status = 'complete' WHERE undone = 1 AND undo_status = ''" );
}

/* --------------------- 1.5.0.1 (End)--------------------------- */

/* --------------------- 1.5.0.2 (Start)--------------------------- */

/**
 * Record per-row outcomes on the undo journal.
 *
 * Rows processed before these columns existed are left blank rather than
 * guessed at: a completed rollback flagged restored and skipped rows the same
 * way, so which was which cannot be recovered. The UI says so for those
 * entries instead of presenting an empty list as "nothing was skipped".
 *
 * @since 1.5.0
 */
function kc_uu_update_1502_record_undo_outcomes() {
	Install::create_tables();
}

/* --------------------- 1.5.0.2 (End)--------------------------- */