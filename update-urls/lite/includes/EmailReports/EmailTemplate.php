<?php

namespace KaizenCoders\UpdateURLS\EmailReports;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class EmailTemplate {

	/**
	 * Build the full HTML email string.
	 *
	 * @param array $data      From ReportGenerator::generate().
	 * @param bool  $is_preview
	 *
	 * @return string
	 */
	public static function build( $data, $is_preview = false ) {
		ob_start();
		self::render( $data, $is_preview );
		return ob_get_clean();
	}

	private static function render( $data, $is_preview ) {
		$site_name    = esc_html( $data['site_name'] );
		$site_url     = esc_url( $data['site_url'] );
		$period_label = esc_html( $data['period_label'] );
		$period_from  = esc_html( date_i18n( get_option( 'date_format' ), $data['period_start'] ) );
		$period_to    = esc_html( date_i18n( get_option( 'date_format' ), $data['period_end'] ) );
		$is_pro       = ! empty( $data['is_pro'] );
		?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $period_label; ?> — <?php echo $site_name; ?></title>
<style>
  body { margin: 0; padding: 0; background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
  .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
  .header { background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%); padding: 32px 40px; text-align: center; }
  .header .plugin-name { color: #a5b4fc; font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; margin: 0 0 8px; }
  .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0 0 6px; }
  .header .site { color: #c7d2fe; font-size: 13px; margin: 0; }
  .preview-badge { display: inline-block; background: #f59e0b; color: #fff; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; margin-bottom: 12px; }
  .period-bar { background: #eef2ff; border-left: 3px solid #6366f1; margin: 0 40px 0; padding: 10px 16px; font-size: 13px; color: #4338ca; }
  .section { padding: 28px 40px; border-bottom: 1px solid #f3f4f6; }
  .section:last-child { border-bottom: none; }
  .section-title { font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7280; margin: 0 0 16px; }
  .health-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
  .health-icon { font-size: 16px; line-height: 1.4; flex-shrink: 0; }
  .health-label { font-size: 14px; color: #374151; line-height: 1.5; }
  .health-label strong { color: #111827; }
  .health-label .sub { font-size: 12px; color: #6b7280; }
  .badge-ok { background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
  .badge-warn { background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
  .badge-error { background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
  .last-run-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; }
  .last-run-box .meta { font-size: 12px; color: #6b7280; margin: 0 0 8px; }
  .last-run-box .from-to { font-size: 14px; color: #111827; font-weight: 500; }
  .last-run-box .from-to span { font-family: 'Courier New', monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
  .last-run-box .changes { font-size: 12px; color: #6b7280; margin-top: 8px; }
  .no-run { font-size: 14px; color: #9ca3af; font-style: italic; }
  .pro-sections {}
  .cta-box { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); padding: 28px 40px; text-align: center; }
  .cta-box h2 { color: #ffffff; font-size: 18px; font-weight: 700; margin: 0 0 8px; }
  .cta-box p { color: #fed7aa; font-size: 13px; margin: 0 0 20px; line-height: 1.6; }
  .cta-features { list-style: none; padding: 0; margin: 0 0 20px; text-align: left; display: inline-block; }
  .cta-features li { color: #fff7ed; font-size: 13px; padding: 3px 0; }
  .cta-features li::before { content: "✓ "; color: #fdba74; font-weight: 700; }
  .btn { display: inline-block; background: #ffffff; color: #ea580c !important; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 6px; text-decoration: none; }
  .coupon { color: #fff7ed; font-size: 12px; margin-top: 12px; }
  .coupon code { background: rgba(255,255,255,.2); padding: 2px 8px; border-radius: 3px; font-weight: 700; letter-spacing: 1px; }
  .footer { padding: 20px 40px; text-align: center; background: #f9fafb; }
  .footer p { font-size: 11px; color: #9ca3af; margin: 4px 0; line-height: 1.6; }
  .footer a { color: #6b7280; text-decoration: underline; }
  @media (max-width: 600px) {
    .section, .header, .cta-box, .footer { padding-left: 20px; padding-right: 20px; }
    .period-bar { margin: 0 20px; }
  }
</style>
</head>
<body>
<div class="wrapper">

  <!-- Header -->
  <div class="header">
    <?php if ( $is_preview ) : ?><div class="preview-badge">Preview</div><?php endif; ?>
    <p class="plugin-name">Update URLS</p>
    <h1><?php echo $period_label; ?> <?php esc_html_e( 'Digest', 'update-urls' ); ?></h1>
    <p class="site"><?php echo $site_name; ?> &mdash; <a href="<?php echo $site_url; ?>" style="color:#c7d2fe;"><?php echo $site_url; ?></a></p>
  </div>

  <!-- Period bar -->
  <div class="period-bar">
    <?php printf(
      /* translators: 1: start date, 2: end date */
      esc_html__( 'Reporting period: %1$s – %2$s', 'update-urls' ),
      $period_from,
      $period_to
    ); ?>
  </div>

  <!-- Site Health -->
  <div class="section">
    <p class="section-title"><?php esc_html_e( 'Site Health', 'update-urls' ); ?></p>

    <?php self::render_health_row( $data ); ?>
  </div>

  <!-- URL Consistency -->
  <div class="section">
    <p class="section-title"><?php esc_html_e( 'URL Consistency Check', 'update-urls' ); ?></p>

    <?php self::render_consistency( $data ); ?>
  </div>

  <!-- Last Search & Replace Activity -->
  <div class="section">
    <p class="section-title"><?php esc_html_e( 'Last Search & Replace Activity', 'update-urls' ); ?></p>

    <?php self::render_last_run( $data ); ?>
  </div>

  <?php
  // PRO: operations summary, backup status etc. injected via filter.
  $pro_html = apply_filters( 'kc_uu_email_digest_sections_html', '', $data );
  if ( ! empty( $pro_html ) ) {
    echo '<div class="pro-sections">' . $pro_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
  }
  ?>

  <!-- News, Tips & Announcements -->
  <?php self::render_news_items( $data ); ?>

  <!-- Upgrade CTA (free only) -->
  <?php if ( ! $is_pro ) : ?>
  <div class="cta-box">
    <?php self::render_cta(); ?>
  </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="footer">
    <?php self::render_footer( $data ); ?>
  </div>

</div>
</body>
</html>
		<?php
	}

	// -------------------------------------------------------------------------
	// Section renderers
	// -------------------------------------------------------------------------

	private static function render_health_row( $data ) {
		$urls_match = ! empty( $data['urls_match'] );
		$is_https   = ! empty( $data['is_https'] );

		// siteurl == home check.
		if ( $urls_match ) {
			echo '<div class="health-row"><span class="health-icon">✅</span><div class="health-label">';
			echo '<strong>' . esc_html__( 'Site URL and Home URL match', 'update-urls' ) . '</strong> <span class="badge-ok">' . esc_html__( 'OK', 'update-urls' ) . '</span>';
			echo '<div class="sub">' . esc_html( $data['site_url'] ) . '</div>';
			echo '</div></div>';
		} else {
			echo '<div class="health-row"><span class="health-icon">⚠️</span><div class="health-label">';
			echo '<strong>' . esc_html__( 'Site URL and Home URL do not match', 'update-urls' ) . '</strong> <span class="badge-warn">' . esc_html__( 'Warning', 'update-urls' ) . '</span>';
			echo '<div class="sub">' . esc_html__( 'siteurl:', 'update-urls' ) . ' ' . esc_html( $data['site_url'] ) . '</div>';
			echo '<div class="sub">' . esc_html__( 'home:', 'update-urls' ) . ' ' . esc_html( $data['home_url'] ) . '</div>';
			echo '</div></div>';
		}

		// HTTPS check.
		if ( $is_https ) {
			echo '<div class="health-row"><span class="health-icon">🔒</span><div class="health-label">';
			echo '<strong>' . esc_html__( 'Site is served over HTTPS', 'update-urls' ) . '</strong> <span class="badge-ok">' . esc_html__( 'Secure', 'update-urls' ) . '</span>';
			echo '</div></div>';
		} else {
			echo '<div class="health-row"><span class="health-icon">🔓</span><div class="health-label">';
			echo '<strong>' . esc_html__( 'Site is not using HTTPS', 'update-urls' ) . '</strong> <span class="badge-warn">' . esc_html__( 'Consider upgrading', 'update-urls' ) . '</span>';
			echo '<div class="sub">' . esc_html__( 'Migrate to HTTPS and use Update URLS to replace all http:// references.', 'update-urls' ) . '</div>';
			echo '</div></div>';
		}
	}

	private static function render_consistency( $data ) {
		$count    = isset( $data['http_post_count'] ) ? (int) $data['http_post_count'] : 0;
		$is_https = ! empty( $data['is_https'] );

		if ( ! $is_https ) {
			echo '<p style="font-size:13px;color:#6b7280;margin:0;">';
			esc_html_e( 'This check applies to HTTPS sites. Migrate to HTTPS to detect mixed-content issues.', 'update-urls' );
			echo '</p>';
			return;
		}

		if ( 0 === $count ) {
			echo '<div class="health-row"><span class="health-icon">✅</span><div class="health-label">';
			echo '<strong>' . esc_html__( 'No HTTP references found in published posts', 'update-urls' ) . '</strong> <span class="badge-ok">' . esc_html__( 'Clean', 'update-urls' ) . '</span>';
			echo '<div class="sub">' . esc_html__( 'No published posts contain an http:// version of your domain.', 'update-urls' ) . '</div>';
			echo '</div></div>';
		} else {
			$http_domain = str_replace( 'https://', 'http://', $data['site_url'] );

			echo '<div class="health-row"><span class="health-icon">⚠️</span><div class="health-label">';
			echo '<strong>' . sprintf(
				/* translators: %d: number of posts */
				esc_html( _n( '%d published post contains HTTP references', '%d published posts contain HTTP references', $count, 'update-urls' ) ),
				$count
			) . '</strong> <span class="badge-warn">' . esc_html__( 'Action needed', 'update-urls' ) . '</span>';
			echo '<div class="sub">' . sprintf(
				/* translators: %s: old http URL */
				esc_html__( 'Search for "%s" and replace with the https:// version to fix mixed-content warnings.', 'update-urls' ),
				esc_html( $http_domain )
			) . '</div>';
			echo '<div class="sub" style="margin-top:8px;"><a href="' . esc_url( admin_url( 'admin.php?page=update-urls' ) ) . '" style="color:#4f46e5;">' . esc_html__( 'Fix now with Update URLS →', 'update-urls' ) . '</a></div>';
			echo '</div></div>';
		}
	}

	private static function render_last_run( $data ) {
		$last_run = isset( $data['last_run'] ) ? $data['last_run'] : null;

		if ( empty( $last_run ) ) {
			echo '<p class="no-run">' . esc_html__( 'No Search & Replace has been run yet.', 'update-urls' ) . '</p>';
			return;
		}

		$ts           = isset( $last_run['timestamp'] ) ? (int) $last_run['timestamp'] : 0;
		$search_for   = isset( $last_run['search_for'] ) ? $last_run['search_for'] : '';
		$replace_with = isset( $last_run['replace_with'] ) ? $last_run['replace_with'] : '';
		$total        = isset( $last_run['total_changes'] ) ? (int) $last_run['total_changes'] : 0;
		$human_time   = $ts ? human_time_diff( $ts, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'update-urls' ) : '';

		echo '<div class="last-run-box">';
		if ( $human_time ) {
			echo '<p class="meta">' . esc_html( date_i18n( 'F j, Y \a\t g:i a', $ts ) ) . ' &mdash; ' . esc_html( $human_time ) . '</p>';
		}
		if ( $search_for || $replace_with ) {
			echo '<p class="from-to">';
			echo esc_html__( 'From', 'update-urls' ) . ' <span>' . esc_html( wp_trim_words( $search_for, 8, '…' ) ) . '</span> ';
			echo esc_html__( 'to', 'update-urls' ) . ' <span>' . esc_html( wp_trim_words( $replace_with, 8, '…' ) ) . '</span>';
			echo '</p>';
		}
		echo '<p class="changes">' . sprintf(
			/* translators: %d: number of changes */
			esc_html( _n( '%d row updated', '%d rows updated', $total, 'update-urls' ) ),
			$total
		) . '</p>';
		echo '</div>';
	}

	private static function render_news_items( $data ) {
		$news_items = isset( $data['news_items'] ) ? $data['news_items'] : array();
		if ( empty( $news_items ) ) {
			return;
		}

		$badge_colors = array(
			'tip'          => 'background-color:#ecfdf5;color:#065f46;',
			'news'         => 'background-color:#eff6ff;color:#1e40af;',
			'announcement' => 'background-color:#fef3c7;color:#92400e;',
		);

		echo '<div class="section">';
		echo '<p class="section-title">' . esc_html__( 'News, Tips & Announcements', 'update-urls' ) . '</p>';

		foreach ( $news_items as $item ) {
			$badge       = ! empty( $item['badge'] ) ? strtolower( $item['badge'] ) : 'tip';
			$badge_label = ! empty( $item['badge'] ) ? $item['badge'] : 'Tip';
			$title       = ! empty( $item['title'] ) ? $item['title'] : '';
			$description = ! empty( $item['description'] ) ? $item['description'] : '';
			$item_url    = ! empty( $item['url'] ) ? $item['url'] : '';
			$url_label   = ! empty( $item['url_label'] ) ? $item['url_label'] : __( 'Learn more →', 'update-urls' );

			if ( empty( $title ) ) {
				continue;
			}

			$badge_style = isset( $badge_colors[ $badge ] ) ? $badge_colors[ $badge ] : $badge_colors['tip'];

			echo '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:10px;">';
			echo '<tr><td style="background-color:#fafafa;border:1px solid #e5e7eb;border-radius:6px;padding:14px 16px;">';
			echo '<span style="display:inline-block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;padding:2px 8px;border-radius:10px;margin-bottom:7px;' . esc_attr( $badge_style ) . '">' . esc_html( $badge_label ) . '</span><br>';
			echo '<span style="font-size:14px;font-weight:600;color:#111827;">' . esc_html( $title ) . '</span>';
			if ( ! empty( $description ) ) {
				echo '<br><span style="font-size:13px;color:#6b7280;line-height:1.5;display:block;margin-top:4px;">' . esc_html( $description ) . '</span>';
			}
			if ( ! empty( $item_url ) ) {
				echo '<br><a href="' . esc_url( $item_url ) . '" style="font-size:12px;color:#2563eb;text-decoration:none;display:inline-block;margin-top:6px;">' . esc_html( $url_label ) . '</a>';
			}
			echo '</td></tr>';
			echo '</table>';
		}

		echo '</div>';
	}

	private static function render_cta() {
		$upgrade_url = 'https://kaizencoders.com/update-urls?utm_campaign=email-digest&utm_medium=email&utm_source=digest-cta';
		?>
		<h2><?php esc_html_e( 'Unlock the Full Power of Update URLS', 'update-urls' ); ?></h2>
		<p><?php esc_html_e( 'Upgrade to PRO for complete database safety tools, operation history, one-click undo, and more.', 'update-urls' ); ?></p>
		<ul class="cta-features">
			<li><?php esc_html_e( 'One-Click Database Backup & Import', 'update-urls' ); ?></li>
			<li><?php esc_html_e( 'Full Operation History with Undo', 'update-urls' ); ?></li>
			<li><?php esc_html_e( 'Dry Run — Preview Changes Before Applying', 'update-urls' ); ?></li>
			<li><?php esc_html_e( 'Table Selection & Search Profiles', 'update-urls' ); ?></li>
			<li><?php esc_html_e( 'Weekly Digest with Operations Summary', 'update-urls' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( $upgrade_url ); ?>" class="btn"><?php esc_html_e( 'Upgrade to PRO →', 'update-urls' ); ?></a>
		<p class="coupon"><?php esc_html_e( 'Limited time — flat 50% off. Use coupon', 'update-urls' ); ?> <code>SPECIAL50</code></p>
		<?php
	}

	private static function render_footer( $data ) {
		$manage_url = admin_url( 'admin.php?page=kc-uu-settings&tab=email_digest' );
		$site_url   = esc_url( $data['site_url'] );
		?>
		<p><?php
			printf(
				/* translators: %s: site name */
				esc_html__( 'You are receiving this digest because you enabled it for %s.', 'update-urls' ),
				'<a href="' . $site_url . '">' . esc_html( $data['site_name'] ) . '</a>'
			);
		?></p>
		<p><a href="<?php echo esc_url( $manage_url ); ?>"><?php esc_html_e( 'Manage digest preferences', 'update-urls' ); ?></a></p>
		<p><?php
			printf(
				/* translators: %s: plugin version */
				esc_html__( 'Update URLS v%s', 'update-urls' ),
				esc_html( KC_UU_PLUGIN_VERSION )
			);
		?> &mdash; <a href="https://kaizencoders.com">KaizenCoders</a></p>
		<?php
	}
}
