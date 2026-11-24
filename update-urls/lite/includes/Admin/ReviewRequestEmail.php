<?php
/**
 * A one-time Thanksgiving thank-you email that asks for a review.
 *
 * Sent from the site itself to its admin email address around US Thanksgiving
 * (the fourth Thursday of November), starting the day before it. It rides on
 * the email digest's recurring Action Scheduler hook, which is scheduled on
 * every request whether or not the digest is switched on and fires every four
 * hours, and it sends at most once a year.
 *
 * It is skipped on sites that have barely used the plugin and on sites where
 * an administrator has already reviewed or asked not to be asked.
 *
 * @package KaizenCoders\UpdateURLS\Admin
 */

namespace KaizenCoders\UpdateURLS\Admin;

use KaizenCoders\UpdateURLS\Admin\Promotions\PromoBanner;
use KaizenCoders\UpdateURLS\EmailReports\Init as EmailDigest;
use KaizenCoders\UpdateURLS\Option;

class ReviewRequestEmail {

	const PREVIEW_ACTION = 'kc_uu_review_email_preview';

	// Option (prefixed kc_uu_ by Option::) recording the year it was sent.
	const SENT_OPTION = 'review_request_email_sent_year';

	// Start this many days before Thanksgiving (1 = the Wednesday)...
	const DAYS_BEFORE = 1;

	// ...from this local time...
	const SEND_FROM_HOUR = 9;

	// ...until the end of this many days after it, in case the scheduler does
	// not run on the day itself on a quiet site.
	const GRACE_DAYS = 2;

	/**
	 * Register hooks.
	 *
	 * @since 1.5.4
	 */
	public function init() {
		add_action( EmailDigest::HOOK, [ $this, 'maybe_send' ], 20 );
		add_action( 'admin_post_' . self::PREVIEW_ACTION, [ $this, 'handle_preview' ] );
	}

	/**
	 * US Thanksgiving for a year, at midnight in the site's timezone.
	 *
	 * @param int $year Year.
	 *
	 * @return \DateTimeImmutable
	 *
	 * @since 1.5.4
	 */
	public static function get_thanksgiving( $year ) {
		$first = new \DateTimeImmutable( sprintf( '%04d-11-01 00:00:00', (int) $year ), wp_timezone() );

		// Days from 1 November to its first Thursday (ISO day 4), then three weeks on.
		$offset = ( 4 - (int) $first->format( 'N' ) + 7 ) % 7;

		return $first->modify( '+' . ( $offset + 21 ) . ' days' );
	}

	/**
	 * Is $now inside this year's sending window?
	 *
	 * @param int $now Unix timestamp.
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public static function is_in_send_window( $now ) {
		$local = ( new \DateTimeImmutable( '@' . (int) $now ) )->setTimezone( wp_timezone() );
		$day   = self::get_thanksgiving( (int) $local->format( 'Y' ) );

		$start = $day->modify( '-' . self::DAYS_BEFORE . ' days' )->setTime( self::SEND_FROM_HOUR, 0 )->getTimestamp();
		$end   = $day->modify( '+' . ( self::GRACE_DAYS + 1 ) . ' days' )->getTimestamp() - 1;

		return $now >= $start && $now <= $end;
	}

	/**
	 * Should the email go out now?
	 *
	 * Free of WordPress lookups (apart from the site timezone) so it can be
	 * reasoned about from its inputs.
	 *
	 * @param array $context {
	 *     @type int  $now                Unix timestamp.
	 *     @type int  $sent_year          Year it was last sent, 0 if never.
	 *     @type bool $answered           An administrator reviewed or declined.
	 *     @type int  $days_since_install Whole days since install.
	 *     @type bool $has_run            A search & replace has been run on this site.
	 * }
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public static function should_send( $context ) {
		$c = wp_parse_args(
			(array) $context,
			[
				'now'                => time(),
				'sent_year'          => 0,
				'answered'           => false,
				'days_since_install' => 0,
				'has_run'            => false,
			]
		);

		if ( ! self::is_in_send_window( (int) $c['now'] ) ) {
			return false;
		}

		$year = (int) wp_date( 'Y', (int) $c['now'] );

		if ( (int) $c['sent_year'] >= $year || $c['answered'] ) {
			return false;
		}

		return (int) $c['days_since_install'] >= ReviewPrompt::MIN_INSTALL_DAYS && ! empty( $c['has_run'] );
	}

	/**
	 * Has any administrator already reviewed, or asked not to be asked?
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public static function has_any_admin_answered() {
		$admins = get_users(
			[
				'role'   => 'administrator',
				'fields' => 'ID',
				'number' => 50,
			]
		);

		foreach ( $admins as $admin_id ) {
			if ( ReviewPrompt::has_answered( (int) $admin_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Scheduler callback: send if everything lines up.
	 *
	 * @return void
	 *
	 * @since 1.5.4
	 */
	public function maybe_send() {
		/**
		 * Turn the Thanksgiving review email off.
		 *
		 * @param bool $send Whether the email may be sent.
		 *
		 * @since 1.5.4
		 */
		if ( ! apply_filters( 'kc_uu_send_review_request_email', true ) ) {
			return;
		}

		$now = time();

		// Cheap date check first; everything else only matters on the day.
		if ( ! self::is_in_send_window( $now ) ) {
			return;
		}

		$context = [
			'now'                => $now,
			'sent_year'          => (int) Option::get( self::SENT_OPTION, 0 ),
			'answered'           => self::has_any_admin_answered(),
			'days_since_install' => PromoBanner::get_days_since_install(),
			'has_run'            => PromoBanner::has_run_search_replace(),
		];

		if ( ! self::should_send( $context ) ) {
			return;
		}

		// Mark first, so an overlapping run cannot send it twice.
		Option::set( self::SENT_OPTION, (int) wp_date( 'Y', $now ) );

		$this->send( get_option( 'admin_email' ) );
	}

	/**
	 * Send the email.
	 *
	 * @param string $to Recipient.
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public function send( $to ) {
		if ( ! is_email( $to ) ) {
			return false;
		}

		return wp_mail(
			$to,
			$this->get_subject(),
			$this->generate_html(),
			[ 'Content-Type: text/html; charset=UTF-8' ]
		);
	}

	/**
	 * Subject line.
	 *
	 * @return string
	 *
	 * @since 1.5.4
	 */
	public function get_subject() {
		return __( 'Happy Thanksgiving from the Update URLS team 🦃', 'update-urls' );
	}

	/**
	 * Email body.
	 *
	 * @return string
	 *
	 * @since 1.5.4
	 */
	public function generate_html() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$kc_uu_review_data = [
			'stats'       => ReviewPrompt::get_usage_stats(),
			'site_name'   => get_bloginfo( 'name' ),
			'site_url'    => home_url(),
			'review_url'  => ReviewPrompt::REVIEW_URL,
			'support_url' => ReviewPrompt::CONTACT_URL,
			'company_url' => ReviewPrompt::KAIZENCODERS_URL,
			'plugin_url'  => ReviewPrompt::PLUGIN_URL,
		];

		ob_start();
		include KC_UU_ADMIN_TEMPLATES_DIR . '/Email/review-request-template.php';

		return ob_get_clean();
	}

	/**
	 * Link that shows the email in the browser.
	 *
	 * @return string
	 *
	 * @since 1.5.4
	 */
	public static function get_preview_url() {
		return admin_url( 'admin-post.php?action=' . self::PREVIEW_ACTION );
	}

	/**
	 * Show the email in the browser, for administrators.
	 *
	 * /wp-admin/admin-post.php?action=kc_uu_review_email_preview
	 *
	 * Read-only, so a capability check is enough and the link can be shared
	 * with the team without a nonce that expires.
	 *
	 * @return void
	 *
	 * @since 1.5.4
	 */
	public function handle_preview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'update-urls' ) );
		}

		echo $this->generate_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}
