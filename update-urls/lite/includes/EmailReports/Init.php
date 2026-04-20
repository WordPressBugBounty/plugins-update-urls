<?php

namespace KaizenCoders\UpdateURLS\EmailReports;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Init {

	const HOOK = 'kc_uu_email_digest';

	/**
	 * Called by the Loader — registers all hooks.
	 */
	public function init() {
		add_action( 'init', array( $this, 'schedule' ) );
		add_action( self::HOOK, array( $this, 'process_report' ) );
		add_action( 'wp_ajax_kc_uu_email_digest_test', array( $this, 'handle_test_ajax' ) );
		add_action( 'admin_post_kc_uu_email_digest_preview', array( $this, 'handle_preview' ) );
	}

	/**
	 * Register the recurring Action Scheduler job (runs every 4 hours).
	 */
	public function schedule() {
		if ( function_exists( 'as_has_scheduled_action' ) && ! as_has_scheduled_action( self::HOOK ) ) {
			as_schedule_recurring_action( time(), 4 * HOUR_IN_SECONDS, self::HOOK, array(), 'kc-update-urls' );
		}
	}

	/**
	 * Unschedule the job — call on plugin deactivation.
	 */
	public static function unschedule() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::HOOK, array(), 'kc-update-urls' );
		}
	}

	/**
	 * Entry point fired by Action Scheduler every 4 hours.
	 */
	public function process_report() {
		if ( ! $this->can_send_now() ) {
			return;
		}
		$this->send_report();
	}

	/**
	 * Determine whether it is time to send the digest right now.
	 */
	private function can_send_now() {
		if ( ! $this->get_setting( 'enabled', 0 ) ) {
			return false;
		}

		$frequency  = $this->get_setting( 'frequency', 'weekly' );
		$last_sent  = (int) \KaizenCoders\UpdateURLS\Option::get( 'email_digest_last_sent', 0 );
		$send_time  = $this->get_setting( 'time', '09:00' );

		// Parse configured time.
		$parts = explode( ':', $send_time );
		$hour  = isset( $parts[0] ) ? (int) $parts[0] : 9;
		$min   = isset( $parts[1] ) ? (int) $parts[1] : 0;
		$hour  = max( 0, min( 23, $hour ) );
		$min   = max( 0, min( 59, $min ) );

		$now = current_time( 'timestamp' );

		// Must be past the configured send time today.
		$today_send = mktime( $hour, $min, 0, (int) date( 'n', $now ), (int) date( 'j', $now ), (int) date( 'Y', $now ) );
		if ( $now < $today_send ) {
			return false;
		}

		if ( 'daily' === $frequency ) {
			// Send once per calendar day.
			return date( 'Y-m-d', $last_sent ) !== date( 'Y-m-d', $now );
		}

		if ( 'weekly' === $frequency ) {
			$send_day = (int) $this->get_setting( 'day', 1 ); // 1 = Monday … 7 = Sunday
			$send_day = max( 1, min( 7, $send_day ) );
			if ( (int) date( 'N', $now ) !== $send_day ) {
				return false;
			}
			// Different ISO week from last send?
			return date( 'o-W', $last_sent ) !== date( 'o-W', $now );
		}

		if ( 'monthly' === $frequency ) {
			$send_dom = (int) $this->get_setting( 'day', 1 ); // 1 – 28
			$send_dom = max( 1, min( 28, $send_dom ) );
			if ( (int) date( 'j', $now ) !== $send_dom ) {
				return false;
			}
			// Different month from last send?
			return date( 'Y-m', $last_sent ) !== date( 'Y-m', $now );
		}

		return false;
	}

	/**
	 * Generate and dispatch the digest email.
	 *
	 * @param bool   $is_preview    Output HTML to browser instead of emailing.
	 * @param string $test_email    Override recipient for a test send.
	 */
	private function send_report( $is_preview = false, $test_email = '' ) {
		$frequency = $this->get_setting( 'frequency', 'weekly' );
		$data      = ReportGenerator::generate( $frequency, $is_preview );
		$html      = EmailTemplate::build( $data, $is_preview );

		if ( $is_preview ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
			exit;
		}

		$recipients = ! empty( $test_email ) ? $test_email : $this->get_setting( 'recipients', '' );

		$sent = EmailSender::send(
			EmailSender::subject( $data ),
			$html,
			$recipients
		);

		if ( $sent && empty( $test_email ) ) {
			\KaizenCoders\UpdateURLS\Option::set( 'email_digest_last_sent', time() );
		}

		return $sent;
	}

	/**
	 * AJAX: Send a test email to a given address.
	 */
	public function handle_test_ajax() {
		check_ajax_referer( 'kc_uu_email_digest_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'update-urls' ) ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'update-urls' ) ) );
		}

		$sent = $this->send_report( false, $email );

		if ( $sent ) {
			wp_send_json_success( array( 'message' => sprintf(
				/* translators: %s: email address */
				__( 'Test email sent to %s.', 'update-urls' ),
				esc_html( $email )
			) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send. Check your WordPress mail configuration.', 'update-urls' ) ) );
		}
	}

	/**
	 * Admin-post: render a preview of the email in the browser.
	 */
	public function handle_preview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'update-urls' ) );
		}

		check_admin_referer( 'kc_uu_email_digest_preview' );
		$this->send_report( true );
	}

	/**
	 * Read a single email digest setting from the WPSF options structure.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	private function get_setting( $key, $default = '' ) {
		$settings = get_option( 'kc_uu_settings', array() );
		$prefixed = 'email_digest_' . $key;
		if ( isset( $settings['email_digest']['settings'][ $prefixed ] ) ) {
			return $settings['email_digest']['settings'][ $prefixed ];
		}
		return $default;
	}
}
