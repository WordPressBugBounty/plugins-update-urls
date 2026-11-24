<?php
/**
 * Thanksgiving review request email.
 *
 * Available variables (from ReviewRequestEmail::generate_html):
 *   $kc_uu_review_data['stats']       - [ 'runs' => int, 'last_rows' => int ]
 *   $kc_uu_review_data['site_name']   - Site name
 *   $kc_uu_review_data['site_url']    - Site URL
 *   $kc_uu_review_data['review_url']  - WordPress.org review form
 *   $kc_uu_review_data['support_url'] - KaizenCoders contact page
 *   $kc_uu_review_data['company_url'] - KaizenCoders website
 *   $kc_uu_review_data['plugin_url']  - Update URLS on WordPress.org
 *
 * @package KaizenCoders\UpdateURLS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$kc_uu_runs         = isset( $kc_uu_review_data['stats']['runs'] ) ? (int) $kc_uu_review_data['stats']['runs'] : 0;
$kc_uu_rows         = isset( $kc_uu_review_data['stats']['last_rows'] ) ? (int) $kc_uu_review_data['stats']['last_rows'] : 0;
$kc_uu_site_name    = ! empty( $kc_uu_review_data['site_name'] ) ? $kc_uu_review_data['site_name'] : get_bloginfo( 'name' );
$kc_uu_site_url     = ! empty( $kc_uu_review_data['site_url'] ) ? $kc_uu_review_data['site_url'] : home_url();
$kc_uu_link_style   = 'color:#c2410c;font-weight:600;text-decoration:none;';
$kc_uu_allowed      = [
	'a' => [
		'href'  => [],
		'style' => [],
	],
];
$kc_uu_plugin_link  = '<a href="' . esc_url( $kc_uu_review_data['plugin_url'] ) . '" style="' . esc_attr( $kc_uu_link_style ) . '">' . esc_html__( 'Update URLS', 'update-urls' ) . '</a>';
$kc_uu_company_link = '<a href="' . esc_url( $kc_uu_review_data['company_url'] ) . '" style="' . esc_attr( $kc_uu_link_style ) . '">' . esc_html__( 'KaizenCoders', 'update-urls' ) . '</a>';
$kc_uu_show_runs    = $kc_uu_runs > 1;
$kc_uu_show_rows    = $kc_uu_rows > 0;
// phpcs:enable
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php esc_html_e( 'Happy Thanksgiving from the Update URLS team', 'update-urls' ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background-color:#f3f4f6;">
<tr><td align="center" style="padding:32px 16px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="max-width:560px;background-color:#ffffff;border-radius:10px;overflow:hidden;">

	<tr>
		<td style="background-color:#9a3412;background:linear-gradient(135deg,#9a3412 0%,#c2410c 55%,#d97706 100%);padding:34px 32px;text-align:center;">
			<div style="font-size:40px;line-height:1;">🦃🍂</div>
			<p style="margin:14px 0 0;font-size:24px;font-weight:700;color:#ffffff;line-height:1.3;">
				<?php esc_html_e( 'Happy Thanksgiving!', 'update-urls' ); ?>
			</p>
			<p style="margin:6px 0 0;font-size:14px;color:rgba(255,255,255,0.9);">
				<?php esc_html_e( 'A thank-you note from the Update URLS team', 'update-urls' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<td style="padding:30px 32px 8px;font-size:15px;line-height:1.7;color:#374151;">
			<p style="margin:0 0 16px;"><?php esc_html_e( 'Hi there,', 'update-urls' ); ?></p>

			<p style="margin:0 0 16px;">
				<?php
				/* translators: %s: site name */
				printf( esc_html__( 'Thanksgiving is a time for gratitude, and we are grateful that %s trusts Update URLS with its database when links need to change.', 'update-urls' ), '<strong>' . esc_html( $kc_uu_site_name ) . '</strong>' );
				?>
			</p>

			<?php if ( $kc_uu_show_runs || $kc_uu_show_rows ) : ?>
			<?php $kc_uu_both = $kc_uu_show_runs && $kc_uu_show_rows; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 18px;">
				<tr>
					<?php if ( $kc_uu_show_runs ) : ?>
					<td width="<?php echo $kc_uu_both ? '50%' : '100%'; ?>" style="background-color:#fff7ed;border-radius:<?php echo $kc_uu_both ? '8px 0 0 8px' : '8px'; ?>;padding:16px;text-align:center;">
						<div style="font-size:26px;font-weight:700;color:#c2410c;"><?php echo esc_html( number_format_i18n( $kc_uu_runs ) ); ?></div>
						<div style="font-size:12px;color:#9a3412;"><?php esc_html_e( 'search & replace runs', 'update-urls' ); ?></div>
					</td>
					<?php endif; ?>
					<?php if ( $kc_uu_show_rows ) : ?>
					<td width="<?php echo $kc_uu_both ? '50%' : '100%'; ?>" style="background-color:#fffbeb;border-radius:<?php echo $kc_uu_both ? '0 8px 8px 0' : '8px'; ?>;padding:16px;text-align:center;">
						<div style="font-size:26px;font-weight:700;color:#b45309;"><?php echo esc_html( number_format_i18n( $kc_uu_rows ) ); ?></div>
						<div style="font-size:12px;color:#92400e;"><?php esc_html_e( 'rows updated in your last run', 'update-urls' ); ?></div>
					</td>
					<?php endif; ?>
				</tr>
			</table>
			<?php endif; ?>

			<p style="margin:0 0 16px;">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: "KaizenCoders" linked to its website, 2: "Update URLS" linked to its plugin page */
						__( 'We are a small team at %1$s. Every feature, fix and support reply in %2$s comes from a handful of people who care a lot about getting it right, and hearing from the people who use it is the best part of our work.', 'update-urls' ),
						$kc_uu_company_link,
						$kc_uu_plugin_link
					),
					$kc_uu_allowed
				);
				?>
			</p>

			<p style="margin:0 0 22px;">
				<?php esc_html_e( 'If Update URLS has made your work a little easier this year, would you share that in a review on WordPress.org? It takes about two minutes, it helps other site owners find us, and a 5-star review from you would truly make our Thanksgiving.', 'update-urls' ); ?>
			</p>

			<div style="text-align:center;margin:0 0 8px;">
				<div style="font-size:26px;letter-spacing:4px;color:#f59e0b;margin-bottom:12px;">★★★★★</div>
				<a href="<?php echo esc_url( $kc_uu_review_data['review_url'] ); ?>" style="display:inline-block;background-color:#c2410c;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:13px 28px;border-radius:8px;">
					<?php esc_html_e( 'Write a review', 'update-urls' ); ?> &rarr;
				</a>
			</div>

			<p style="margin:22px 0 0;font-size:14px;color:#6b7280;">
				<?php esc_html_e( 'And if something is not working the way you hoped, please tell us first. We would much rather fix it for you:', 'update-urls' ); ?>
				<a href="<?php echo esc_url( $kc_uu_review_data['support_url'] ); ?>" style="color:#c2410c;"><?php esc_html_e( 'tell us and we will fix it', 'update-urls' ); ?></a>.
			</p>

			<p style="margin:24px 0 0;">
				<?php esc_html_e( 'Wishing you and your family a warm and happy Thanksgiving,', 'update-urls' ); ?><br>
				<strong>
				<?php
				echo wp_kses(
					/* translators: 1: "Update URLS" linked to its plugin page, 2: "KaizenCoders" linked to its website */
					sprintf( __( 'The %1$s team at %2$s', 'update-urls' ), $kc_uu_plugin_link, $kc_uu_company_link ),
					$kc_uu_allowed
				);
				?>
				</strong>
			</p>
		</td>
	</tr>

	<tr>
		<td style="padding:22px 32px 26px;font-size:12px;line-height:1.6;color:#9ca3af;text-align:center;border-top:1px solid #f3f4f6;">
			<?php
			/* translators: %s: site name */
			printf( esc_html__( 'A once-a-year note from Update URLS on %s. It stops once you leave a review or choose "Don\'t ask again".', 'update-urls' ), '<a href="' . esc_url( $kc_uu_site_url ) . '" style="color:#6b7280;">' . esc_html( $kc_uu_site_name ) . '</a>' );
			?>
		</td>
	</tr>

</table>
</td></tr>
</table>
</body>
</html>
