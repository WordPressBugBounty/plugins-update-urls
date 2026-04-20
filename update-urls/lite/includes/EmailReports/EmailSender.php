<?php

namespace KaizenCoders\UpdateURLS\EmailReports;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class EmailSender {

	/**
	 * Send the digest email.
	 *
	 * @param string $subject
	 * @param string $html
	 * @param string $recipients_raw  Newline- or comma-separated addresses. Falls back to admin email.
	 *
	 * @return bool
	 */
	public static function send( $subject, $html, $recipients_raw = '' ) {
		$to = self::parse_recipients( $recipients_raw );

		if ( empty( $to ) ) {
			return false;
		}

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $to, $subject, $html, $headers );
	}

	/**
	 * Build the email subject line.
	 *
	 * @param array $data  Report data array from ReportGenerator.
	 *
	 * @return string
	 */
	public static function subject( $data ) {
		$site_name = isset( $data['site_name'] ) ? $data['site_name'] : get_bloginfo( 'name' );
		$label     = isset( $data['period_label'] ) ? $data['period_label'] : '';
		$prefix    = ! empty( $data['is_preview'] ) ? '[Preview] ' : '';

		return sprintf(
			/* translators: 1: preview prefix (may be empty), 2: period label, 3: site name */
			__( '%1$sUpdate URLS %2$s Digest — %3$s', 'update-urls' ),
			$prefix,
			$label,
			$site_name
		);
	}

	/**
	 * Parse a raw recipients string into a clean array of validated addresses.
	 *
	 * @param string $raw
	 *
	 * @return array
	 */
	private static function parse_recipients( $raw ) {
		$raw = trim( (string) $raw );

		if ( empty( $raw ) ) {
			return array( get_option( 'admin_email' ) );
		}

		$emails = preg_split( '/[\r\n,]+/', $raw );
		$valid  = array();

		foreach ( $emails as $email ) {
			$email = sanitize_email( trim( $email ) );
			if ( is_email( $email ) ) {
				$valid[] = $email;
			}
		}

		$valid = array_unique( $valid );

		return ! empty( $valid ) ? $valid : array( get_option( 'admin_email' ) );
	}
}
