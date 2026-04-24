<?php

namespace KaizenCoders\UpdateURLS\EmailReports;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class ReportGenerator {

	/**
	 * Assemble all data needed by the email template.
	 *
	 * @param string $frequency  'daily'|'weekly'|'monthly'
	 * @param bool   $preview    When true, always use the last 7 days.
	 *
	 * @return array
	 */
	public static function generate( $frequency = 'weekly', $preview = false ) {
		list( $period_start, $period_end ) = self::get_period_dates( $frequency, $preview );

		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );

		$data = array(
			'frequency'       => $frequency,
			'period_label'    => self::period_label( $frequency, $preview ),
			'period_start'    => $period_start,
			'period_end'      => $period_end,
			'site_name'       => get_bloginfo( 'name' ),
			'site_url'        => $site_url,
			'home_url'        => $home_url,
			'urls_match'      => ( $site_url === $home_url ),
			'is_https'        => ( strpos( $site_url, 'https://' ) === 0 ),
			'http_post_count' => self::count_http_in_posts( $site_url ),
			'last_run'        => self::get_last_run(),
			'news_items'      => self::get_news_items(),
			'is_pro'          => \UU()->is_pro(),
			'is_preview'      => $preview,
		);

		// Allow PRO (and third parties) to append additional sections.
		$data = apply_filters( 'kc_uu_email_digest_data', $data );

		return $data;
	}

	// -------------------------------------------------------------------------
	// Period helpers
	// -------------------------------------------------------------------------

	private static function get_period_dates( $frequency, $preview ) {
		if ( $preview ) {
			$start = strtotime( '-7 days midnight' );
			$end   = strtotime( 'yesterday 23:59:59' );
			return array( $start, $end );
		}

		$now = current_time( 'timestamp' );

		if ( 'daily' === $frequency ) {
			$start = strtotime( 'yesterday midnight', $now );
			$end   = strtotime( 'yesterday 23:59:59', $now );
		} elseif ( 'monthly' === $frequency ) {
			$first_of_last_month = mktime( 0, 0, 0, (int) date( 'n', $now ) - 1, 1, (int) date( 'Y', $now ) );
			$start               = $first_of_last_month;
			$end                 = mktime( 23, 59, 59, (int) date( 'n', $now ), 0, (int) date( 'Y', $now ) ); // Last day of last month.
		} else {
			// Weekly: last full Mon–Sun week.
			$dow   = (int) date( 'N', $now ); // 1=Mon … 7=Sun
			$start = strtotime( '-' . ( $dow + 6 ) . ' days midnight', $now );
			$end   = strtotime( '-' . $dow . ' days 23:59:59', $now );
		}

		return array( $start, $end );
	}

	private static function period_label( $frequency, $preview ) {
		if ( $preview ) {
			return __( 'Last 7 Days (Preview)', 'update-urls' );
		}
		$map = array(
			'daily'   => __( 'Daily', 'update-urls' ),
			'weekly'  => __( 'Weekly', 'update-urls' ),
			'monthly' => __( 'Monthly', 'update-urls' ),
		);
		return isset( $map[ $frequency ] ) ? $map[ $frequency ] : __( 'Weekly', 'update-urls' );
	}

	// -------------------------------------------------------------------------
	// Site health checks
	// -------------------------------------------------------------------------

	/**
	 * Count published posts whose post_content contains the HTTP version
	 * of the site URL (only meaningful when site is HTTPS).
	 */
	private static function count_http_in_posts( $site_url ) {
		if ( strpos( $site_url, 'https://' ) !== 0 ) {
			return 0;
		}

		global $wpdb;

		$http_url = str_replace( 'https://', 'http://', $site_url );

		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish'
			   AND post_content LIKE %s",
			'%' . $wpdb->esc_like( $http_url ) . '%'
		) );

		return (int) $count;
	}

	// -------------------------------------------------------------------------
	// Last S&R run
	// -------------------------------------------------------------------------

	private static function get_last_run() {
		$raw = \KaizenCoders\UpdateURLS\Option::get( 'last_run', null );
		if ( empty( $raw ) || ! is_array( $raw ) ) {
			return null;
		}
		return $raw;
	}

	// -------------------------------------------------------------------------
	// News items (tips, announcements)
	// -------------------------------------------------------------------------

	/**
	 * Get news, tips, and announcement items for the digest email.
	 *
	 * Filterable via 'kc_uu_email_digest_news_items'. Each item is an array with:
	 *   'badge'       => string  — 'Tip', 'News', or 'Announcement'
	 *   'title'       => string  — Short headline
	 *   'description' => string  — Optional body text
	 *   'url'         => string  — Optional learn-more URL
	 *   'url_label'   => string  — Optional link label (defaults to 'Learn more →')
	 *
	 * @return array
	 */
	private static function get_news_items() {
		$items = array(
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Always backup before Search & Replace', 'update-urls' ),
				'description' => __( 'A database backup takes seconds and gives you a safety net. Run a backup before any Search & Replace operation, especially on production sites.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/how-to-do-one-click-database-import-and-export',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Watch out for serialized data', 'update-urls' ),
				'description' => __( 'WordPress serializes some option values and post meta. Update URLS handles this automatically — never manually edit serialized strings or they will break.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/how-to-update-serialized-data-effectively',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'HTTP → HTTPS is the most common migration', 'update-urls' ),
				'description' => __( 'After installing an SSL certificate, search for your old http:// address and replace it with https://. This fixes mixed-content warnings site-wide.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/http-to-https',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Staging to production? Update every URL', 'update-urls' ),
				'description' => __( 'When pushing from staging to live, replace the staging domain with your production domain. Don\'t forget wp-content paths if they differ between environments.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/staging-to-production',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'The siteurl and home options matter', 'update-urls' ),
				'description' => __( 'WordPress uses the siteurl and home options to build every URL on the site. Make sure they both point to your current domain after any migration.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/siteurl-home',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Use Dry Run to preview changes safely', 'update-urls' ),
				'description' => __( 'Not sure what will be affected? PRO\'s Dry Run mode shows you exactly which rows would change — without touching the database.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/how-to-do-a-dry-run-before-search-and-replace',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'CDN URL changes are easy with Update URLS', 'update-urls' ),
				'description' => __( 'Switching CDN providers? Update URLS can replace old CDN domains in post content, meta values, and option rows in one pass.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/how-to-change-cdn-providers',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Replace works across all tables', 'update-urls' ),
				'description' => __( 'Update URLS touches posts, post meta, custom fields, and links in one go — so you don\'t miss any stray references to old URLs.', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/update-urls/how-to-run-search-and-replace-on-selected-tables',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Need passwordless login for WordPress?', 'update-urls' ),
				'description' => __( 'Say goodbye to forgotten passwords. Enable a secure, passwordless login experience for your WordPress site using Magic Link.', 'update-urls' ),
				'url'         => 'https://wordpress.org/plugins/magic-link/',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Shorten & track links with URL Shortify', 'update-urls' ),
				'description' => __( 'Create branded short links, track clicks, and analyse your audience — all from inside WordPress using URL Shortify.', 'update-urls' ),
				'url'         => 'https://wordpress.org/plugins/url-shortify/',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Tip',
				'title'       => __( 'Monitor WordPress Activity using Logify', 'update-urls' ),
				'description' => __( 'Gain clear insights into events happening on your site, track changes effortlessly, and ensure accountability.', 'update-urls' ),
				'url'         => 'https://kaizencoders.com/logify',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
			array(
				'badge'       => 'Announcement',
				'title'       => __( 'Operation History & One-Click Undo in PRO', 'update-urls' ),
				'description' => __( 'Every Search & Replace is logged in PRO. Browse your full history and undo any operation with a single click — no manual restore needed.', 'update-urls' ),
				'url'         => 'https://kaizencoders.com/update-urls',
				'url_label'   => __( 'Upgrade to PRO →', 'update-urls' ),
			),
			array(
				'badge'       => 'News',
				'title'       => __( 'What\'s new in Update URLS PRO', 'update-urls' ),
				'description' => __( 'Update URLS PRO — The Complete Search & Replace Plugin for WordPress', 'update-urls' ),
				'url'         => 'https://docs.kaizencoders.com/blog/update-urls-features',
				'url_label'   => __( 'Learn more →', 'update-urls' ),
			),
		);

		$items = array( $items[ array_rand( $items ) ] );

		return (array) apply_filters( 'kc_uu_email_digest_news_items', $items );
	}
}
