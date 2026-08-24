<?php
/**
 * What sits at the bottom of an Update URLS screen.
 *
 * @link       https://kaizencoders.com
 * @since      1.5.2
 *
 * @package    UpdateURLS
 * @subpackage UpdateURLS/includes/Admin
 */

namespace KaizenCoders\UpdateURLS\Admin;

use KaizenCoders\UpdateURLS\Helper;

/**
 * Update URLS' own footer, on Update URLS' own screens.
 *
 * One row, and WordPress's own two footer lines emptied to make room for it:
 * the "Thank you for creating with WordPress" credit on the left and the
 * "Get Version 7.1" nag on the right. Everything they said that is worth
 * saying — the plugin, its version, who made it — this row says better, and
 * the previous arrangement said it twice: `admin_footer_text` was rewritten to
 * name the plugin and version, and repeated the WordPress credit alongside it.
 *
 * **`in_admin_footer` fires inside `#wpfooter`, which WordPress positions
 * absolutely against the bottom of the page.** A one-line paragraph is fine in
 * there; a row of content is not — it overlaps whatever the page ends with. So
 * a body class goes on Update URLS' screens and the footer bar is returned to
 * the normal flow, which is the whole layout fix.
 *
 * All of it is confined to Update URLS' screens. A plugin that brands the
 * footer of somebody else's page, or empties WordPress's credit there, has
 * overstepped.
 *
 * @package    UpdateURLS
 * @subpackage UpdateURLS/includes/Admin
 * @author     KaizenCoders <hello@kaizencoders.com>
 *
 * @since 1.5.2
 */
class Footer {

	/**
	 * Where the reviews are left.
	 *
	 * @since 1.5.2
	 * @var   string
	 */
	const REVIEW_URL = 'https://wordpress.org/support/plugin/update-urls/reviews/#new-post';

	/**
	 * Where questions are asked.
	 *
	 * @since 1.5.2
	 * @var   string
	 */
	const SUPPORT_URL = 'https://wordpress.org/support/plugin/update-urls/';

	/**
	 * Where the documentation lives.
	 *
	 * @since 1.5.2
	 * @var   string
	 */
	const DOCS_URL = 'https://kaizencoders.com/docs/update-urls/';

	/**
	 * Initialise the class.
	 *
	 * @return void
	 *
	 * @since 1.5.2
	 */
	public function init() {
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
		add_filter( 'admin_footer_text', [ $this, 'credit' ], 999999999 );
		add_filter( 'update_footer', [ $this, 'version_nag' ], 999999999 );
		add_action( 'in_admin_footer', [ $this, 'render' ] );
	}

	/**
	 * Mark Update URLS' screens on the body, so the stylesheet can reach the
	 * admin furniture around the page.
	 *
	 * @param string $classes The classes WordPress and everybody else set.
	 *
	 * @return string
	 *
	 * @since 1.5.2
	 */
	public function body_class( $classes ) {
		if ( ! Helper::is_plugin_admin_screen() ) {
			return $classes;
		}

		return trim( $classes . ' kc-uu-screen' );
	}

	/**
	 * Empty WordPress's footer credit on Update URLS' screens.
	 *
	 * The row below says the same things with room to say them. Everywhere else
	 * in wp-admin the credit is left exactly as it was.
	 *
	 * @param string $text What WordPress or another plugin would have said.
	 *
	 * @return string
	 *
	 * @since 1.5.2
	 */
	public function credit( $text ) {
		return Helper::is_plugin_admin_screen() ? '' : $text;
	}

	/**
	 * Empty the version nag on the right of the footer.
	 *
	 * "Get Version 7.1" belongs on a WordPress screen, not under Update URLS'
	 * own footer, where it competes with the version that is actually being
	 * asked about. Core adds it through `core_update_footer()` at priority 10;
	 * this runs after.
	 *
	 * @param string $text What core or another plugin would have said.
	 *
	 * @return string
	 *
	 * @since 1.5.2
	 */
	public function version_nag( $text ) {
		return Helper::is_plugin_admin_screen() ? '' : $text;
	}

	/**
	 * The plugin's name, as this build should be called.
	 *
	 * @return string
	 *
	 * @since 1.5.2
	 */
	protected function name() {
		return UU()->is_pro() ? __( 'Update URLS PRO', 'update-urls' ) : __( 'Update URLS', 'update-urls' );
	}

	/**
	 * Tag an outbound footer link so the click can be attributed.
	 *
	 * @param string $url     Target url.
	 * @param string $content UTM content slot.
	 *
	 * @return string
	 *
	 * @since 1.5.2
	 */
	protected function link( $url, $content ) {
		return Helper::get_utm_url(
			$url,
			[
				'medium'   => 'admin-footer',
				'campaign' => 'branding',
				'content'  => $content,
			]
		);
	}

	/**
	 * The links an Update URLS screen ends with.
	 *
	 * @return void
	 *
	 * @since 1.5.2
	 */
	public function render() {
		if ( ! Helper::is_plugin_admin_screen() ) {
			return;
		}

		$links = [
			[
				'label' => __( 'About', 'update-urls' ),
				'url'   => UU()->get_website_url( '', [ 'medium' => 'admin-footer', 'campaign' => 'branding', 'content' => 'about' ] ),
			],
			[
				'label' => __( 'Documentation', 'update-urls' ),
				'url'   => $this->link( self::DOCS_URL, 'documentation' ),
			],
			[
				'label' => __( 'Support', 'update-urls' ),
				'url'   => $this->link( self::SUPPORT_URL, 'support' ),
			],
		];

		/*
		 * On a free build this is the one thing in the row worth acting on, so
		 * it carries a class and is styled as a filled chip rather than sitting
		 * among the signposts as a fourth identical link. It is last because the
		 * end of the row is where the eye stops.
		 *
		 * can_show_premium_promotion() rather than a bare is_pro() check, so a
		 * site that has switched promotions off with `kc_uu_disable_promotion`
		 * does not get one here after all the others have gone.
		 */
		if ( UU()->can_show_premium_promotion() ) {
			$links[] = [
				'label' => __( 'Go PRO', 'update-urls' ),
				'url'   => UU()->get_pricing_url( 'annual' ),
				'class' => 'kc-uu-footer__pro',

				// An admin screen, not somebody else's site.
				'local' => true,
			];
		}

		$kc_uu_footer = [
			'name'    => $this->name(),
			'version' => KC_UU_PLUGIN_VERSION,
			'links'   => $links,
			'maker'   => $this->link( 'https://kaizencoders.com', 'kaizencoders' ),
			'review'  => $this->link( self::REVIEW_URL, 'rating' ),

			// The plugin's own mark. The 64px copy is served to retina screens,
			// where the 32px one would be resampled at the size it is drawn.
			'icon'    => KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/icon-32.png',
			'icon_2x' => KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/icon-64.png',
		];

		require KC_UU_ADMIN_TEMPLATES_DIR . '/footer.php';
	}
}
