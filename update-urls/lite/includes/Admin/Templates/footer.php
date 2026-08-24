<?php
/**
 * The Update URLS footer.
 *
 * One row, three zones, separated from the page by a hairline and nothing else.
 * A footer is the end of the page, not another panel on it — so no card, no
 * tint, no border box. What identifies this as Update URLS' is the mark and the
 * link colour; everything else gets out of the way.
 *
 * Left is identity: what this is and which version. The centre is the
 * signature. The right is where anybody would go next, and the review ask ends
 * it — the last thing the eye lands on, and the one item here that asks for
 * something rather than pointing somewhere.
 *
 * Written in visual order — left, centre, right — so the focus ring travels the
 * way the row reads. Placing the nav before the signature in the markup and
 * moving it with `grid-column` would tab right, then back to the middle.
 *
 * The mark is the plugin's own icon — the one on the wordpress.org listing and
 * in the plugins table — so the row is signed with the thing being named.
 * Decorative, and the name is right beside it, so the alt is empty rather than
 * a repetition for anybody listening to the page.
 *
 * @link       https://kaizencoders.com
 * @since      1.5.2
 *
 * @package    UpdateURLS
 * @subpackage UpdateURLS/includes/Admin/Templates
 *
 * @var array<string, mixed> $kc_uu_footer What Admin\Footer gathered.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?>
<div class="kc-uu-footer">
	<div class="kc-uu-footer__identity">
		<img
			class="kc-uu-footer__mark"
			src="<?php echo esc_url( $kc_uu_footer['icon'] ); ?>"
			srcset="<?php echo esc_url( $kc_uu_footer['icon'] ); ?> 1x, <?php echo esc_url( $kc_uu_footer['icon_2x'] ); ?> 2x"
			width="22"
			height="22"
			alt=""
		/>

		<span class="kc-uu-footer__name"><?php echo esc_html( $kc_uu_footer['name'] ); ?></span>

		<span class="kc-uu-footer__version"><?php echo esc_html( $kc_uu_footer['version'] ); ?></span>
	</div>

	<p class="kc-uu-footer__maker">
		<?php
		printf(
			/* translators: 1: a heart, 2: link opening tag, 3: link closing tag */
			esc_html__( 'Made with %1$s by %2$sKaizenCoders%3$s', 'update-urls' ),
			'<span class="kc-uu-footer__heart" aria-hidden="true">❤️</span>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a literal.
			'<a href="' . esc_url( $kc_uu_footer['maker'] ) . '" target="_blank" rel="noreferrer noopener">',
			'</a>'
		);
		?>
	</p>

	<nav class="kc-uu-footer__links" aria-label="<?php esc_attr_e( 'Update URLS links', 'update-urls' ); ?>">
		<?php foreach ( $kc_uu_footer['links'] as $kc_uu_link ) : ?>
			<a
				<?php if ( ! empty( $kc_uu_link['class'] ) ) : ?>
					class="<?php echo esc_attr( $kc_uu_link['class'] ); ?>"
				<?php endif; ?>
				href="<?php echo esc_url( $kc_uu_link['url'] ); ?>"
				<?php
				/*
				 * A link that stays inside wp-admin keeps the tab it is in. Only
				 * the ones that leave the site open a new one.
				 */
				if ( empty( $kc_uu_link['local'] ) ) :
					?>
					target="_blank"
					rel="noreferrer noopener"
				<?php endif; ?>
			>
				<?php echo esc_html( $kc_uu_link['label'] ); ?>
			</a>
		<?php endforeach; ?>

		<a
			class="kc-uu-footer__rating"
			href="<?php echo esc_url( $kc_uu_footer['review'] ); ?>"
			target="_blank"
			rel="noreferrer noopener"
		>
			<span class="kc-uu-footer__stars" aria-hidden="true">★★★★★</span>

			<?php
			/*
			 * Named, because in the footer of a page full of somebody else's
			 * plugins "Leave a review" does not say what is being reviewed.
			 *
			 * "Update URLS" and not the build's own name: the listing being
			 * reviewed is the one on wordpress.org, which is Update URLS
			 * whichever build is installed, and "Rate Update URLS PRO" would
			 * send somebody to a page named for something else.
			 */
			esc_html_e( 'Rate Update URLS', 'update-urls' );
			?>

			<span class="screen-reader-text">
				<?php esc_html_e( 'on WordPress.org (opens in a new tab)', 'update-urls' ); ?>
			</span>
		</a>
	</nav>
</div>
