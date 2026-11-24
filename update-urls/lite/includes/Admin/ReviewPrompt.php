<?php
/**
 * A friendly "would you leave us a review?" popup on Update URLS screens.
 *
 * Kept deliberately polite: it only appears to administrators who have
 * actually run a search & replace and used the plugin for a while, from
 * START_DATE on, at most once a day, and every way of closing it is honoured.
 *
 * @package KaizenCoders\UpdateURLS\Admin
 */

namespace KaizenCoders\UpdateURLS\Admin;

use KaizenCoders\UpdateURLS\Admin\Promotions\PromoBanner;
use KaizenCoders\UpdateURLS\Helper;
use KaizenCoders\UpdateURLS\Option;

class ReviewPrompt {

	const AJAX_ACTION  = 'kc_uu_review_prompt';
	const NONCE_ACTION = 'kc_uu_review_prompt';

	const REVIEW_URL = 'https://wordpress.org/support/plugin/update-urls/reviews/#new-post';

	// Linked from the popup and the Thanksgiving email.
	const KAIZENCODERS_URL = 'https://kaizencoders.com';
	const PLUGIN_URL       = 'https://wordpress.org/plugins/update-urls';
	const CONTACT_URL      = 'https://kaizencoders.com/contact';

	// User meta recording the answer, shared with the older Feedback notice so
	// an answer given in either place counts. "_time" is appended for when.
	const META_REVIEWED    = 'kc_uu_feedback_review_done';
	const META_ALREADY_DID = 'kc_uu_feedback_already_did';
	const META_NEVER       = 'kc_uu_feedback_do_not_ask_again';
	const META_LATER       = 'kc_uu_feedback_maybe_later';

	// When the popup was last displayed to this user.
	const META_LAST_SHOWN = 'kc_uu_review_prompt_last_shown';

	// First day the popup may appear, in the site's timezone.
	const START_DATE = '2026-11-25';

	const MIN_INSTALL_DAYS = 14;
	const SNOOZE_DAYS      = 30;

	/**
	 * Register hooks.
	 *
	 * Renders in admin_footer rather than admin_notices, so
	 * Admin::remove_admin_notices() does not strip it.
	 *
	 * @since 1.5.4
	 */
	public function init() {
		add_action( 'admin_footer', [ $this, 'maybe_render' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'handle_response' ] );
	}

	/**
	 * Responses the popup can send, mapped to the user meta they set.
	 *
	 * @return array
	 *
	 * @since 1.5.4
	 */
	public static function get_responses() {
		return [
			'review'      => self::META_REVIEWED,
			'already_did' => self::META_ALREADY_DID,
			'never'       => self::META_NEVER,
			'later'       => self::META_LATER,
		];
	}

	/* ---------------------------------------------------------------------
	 * Show logic
	 * ------------------------------------------------------------------ */

	/**
	 * Should the popup render?
	 *
	 * Free of WordPress lookups (apart from the site timezone) so it can be
	 * reasoned about from its inputs.
	 *
	 * @param array $context {
	 *     @type bool $can_manage         Current user is an administrator.
	 *     @type bool $on_plugin_screen   Current screen belongs to Update URLS.
	 *     @type bool $answered           Reviewed, already did, or never ask again.
	 *     @type int  $later_at           When "maybe later" was chosen, 0 if never.
	 *     @type int  $last_shown         When the popup was last displayed, 0 if never.
	 *     @type int  $days_since_install Whole days since install.
	 *     @type bool $has_run            A search & replace has been run on this site.
	 *     @type int  $now                Unix timestamp.
	 * }
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public static function should_show( $context ) {
		$c = wp_parse_args(
			(array) $context,
			[
				'can_manage'         => false,
				'on_plugin_screen'   => false,
				'answered'           => false,
				'later_at'           => 0,
				'last_shown'         => 0,
				'days_since_install' => 0,
				'has_run'            => false,
				'now'                => time(),
			]
		);

		if ( ! $c['can_manage'] || ! $c['on_plugin_screen'] || $c['answered'] ) {
			return false;
		}

		if ( (int) $c['now'] < self::get_start_timestamp() ) {
			return false;
		}

		// Only ask people who have had time to form an opinion.
		if ( (int) $c['days_since_install'] < self::MIN_INSTALL_DAYS || ! $c['has_run'] ) {
			return false;
		}

		$now = (int) $c['now'];

		if ( (int) $c['later_at'] > 0 && ( $now - (int) $c['later_at'] ) < self::SNOOZE_DAYS * DAY_IN_SECONDS ) {
			return false;
		}

		// Ignored without an answer (navigated away): wait a day before asking again.
		if ( (int) $c['last_shown'] > 0 && ( $now - (int) $c['last_shown'] ) < DAY_IN_SECONDS ) {
			return false;
		}

		return true;
	}

	/**
	 * Midnight on START_DATE in the site's timezone.
	 *
	 * @return int
	 *
	 * @since 1.5.4
	 */
	public static function get_start_timestamp() {
		return ( new \DateTimeImmutable( self::START_DATE . ' 00:00:00', wp_timezone() ) )->getTimestamp();
	}

	/**
	 * Has this user already answered for good?
	 *
	 * @param int $user_id User id.
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public static function has_answered( $user_id ) {
		foreach ( [ self::META_REVIEWED, self::META_ALREADY_DID, self::META_NEVER ] as $key ) {
			if ( get_user_meta( $user_id, $key, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gather everything should_show() needs for the current request.
	 *
	 * Cheap checks run first so the common case costs no extra lookups.
	 *
	 * @return array
	 *
	 * @since 1.5.4
	 */
	public static function build_context() {
		$user_id = get_current_user_id();

		$context = [
			'can_manage'       => current_user_can( 'manage_options' ),
			'on_plugin_screen' => Helper::is_plugin_admin_screen(),
			'answered'         => $user_id > 0 ? self::has_answered( $user_id ) : true,
			'now'              => time(),
		];

		if ( ! $context['can_manage'] || ! $context['on_plugin_screen'] || $context['answered'] ) {
			return $context;
		}

		if ( $context['now'] < self::get_start_timestamp() ) {
			return $context;
		}

		$later = get_user_meta( $user_id, self::META_LATER, true );

		$context['later_at']           = $later ? (int) get_user_meta( $user_id, self::META_LATER . '_time', true ) : 0;
		$context['last_shown']         = (int) get_user_meta( $user_id, self::META_LAST_SHOWN, true );
		$context['days_since_install'] = PromoBanner::get_days_since_install();
		$context['has_run']            = PromoBanner::has_run_search_replace();

		return $context;
	}

	/**
	 * Search & replace runs recorded in History (PRO) and the rows the most
	 * recent run updated, for a personal touch.
	 *
	 * @return array { @type int $runs, @type int $last_rows }
	 *
	 * @since 1.5.4
	 */
	public static function get_usage_stats() {
		global $wpdb;

		$runs  = 0;
		$table = $wpdb->prefix . 'kc_uu_history';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$runs = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		}

		$last_run = Option::get( 'last_run', [] );

		return [
			'runs'      => $runs,
			'last_rows' => is_array( $last_run ) && isset( $last_run['total_changes'] ) ? (int) $last_run['total_changes'] : 0,
		];
	}

	/* ---------------------------------------------------------------------
	 * Render
	 * ------------------------------------------------------------------ */

	/**
	 * Print the popup when it applies.
	 *
	 * @return void
	 *
	 * @since 1.5.4
	 */
	public function maybe_render() {
		/**
		 * Turn the review popup off entirely.
		 *
		 * @param bool $show Whether the popup may be shown.
		 *
		 * @since 1.5.4
		 */
		if ( ! apply_filters( 'kc_uu_show_review_prompt', true ) ) {
			return;
		}

		// Preview: show it regardless of the rules, and remember nothing.
		if ( self::is_preview() ) {
			$this->render( self::get_usage_stats(), true );

			return;
		}

		if ( ! self::should_show( self::build_context() ) ) {
			return;
		}

		update_user_meta( get_current_user_id(), self::META_LAST_SHOWN, time() );

		$this->render( self::get_usage_stats() );
	}

	/**
	 * Is an administrator asking to preview the popup?
	 *
	 * Add `&kc_uu_preview=review-popup` to any Update URLS screen. Nothing is
	 * recorded in preview: not that it was shown, and not the answer.
	 *
	 * @return bool
	 *
	 * @since 1.5.4
	 */
	public static function is_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preview.
		$preview = isset( $_GET['kc_uu_preview'] ) ? sanitize_key( wp_unslash( $_GET['kc_uu_preview'] ) ) : '';

		return 'review-popup' === $preview
			&& current_user_can( 'manage_options' )
			&& Helper::is_plugin_admin_screen();
	}

	/**
	 * Link that previews the popup on the Update URLS screen.
	 *
	 * @return string
	 *
	 * @since 1.5.4
	 */
	public static function get_preview_url() {
		return admin_url( 'admin.php?page=update-urls&kc_uu_preview=review-popup' );
	}

	/**
	 * The personal line at the top of the popup, or '' when there is nothing
	 * worth quoting.
	 *
	 * @param array $stats See get_usage_stats().
	 *
	 * @return string HTML with <strong> only.
	 *
	 * @since 1.5.4
	 */
	public static function get_stat_line( $stats ) {
		$runs = (int) Helper::get_data( $stats, 'runs', 0 );
		$rows = (int) Helper::get_data( $stats, 'last_rows', 0 );

		if ( $runs > 1 ) {
			return sprintf(
				/* translators: %s: number of search & replace runs */
				__( 'Update URLS has run %s search & replace operations on your site.', 'update-urls' ),
				'<strong>' . esc_html( number_format_i18n( $runs ) ) . '</strong>'
			);
		}

		if ( $rows > 0 ) {
			return sprintf(
				/* translators: %s: number of database rows */
				_n(
					'Your last search & replace updated %s row in one go, no SQL needed.',
					'Your last search & replace updated %s rows in one go, no SQL needed.',
					$rows,
					'update-urls'
				),
				'<strong>' . esc_html( number_format_i18n( $rows ) ) . '</strong>'
			);
		}

		return '';
	}

	/**
	 * Popup markup.
	 *
	 * @param array $stats   See get_usage_stats().
	 * @param bool  $preview Do not save the answer.
	 *
	 * @return void
	 *
	 * @since 1.5.4
	 */
	public function render( $stats, $preview = false ) {
		$current_user = wp_get_current_user();
		$first_name   = ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
		$nonce        = wp_create_nonce( self::NONCE_ACTION );
		$stat_line    = self::get_stat_line( $stats );
		?>
		<div id="kc-uu-review-prompt" class="kc-uu-rp" role="dialog" aria-modal="true" aria-labelledby="kc-uu-rp-title"
			data-nonce="<?php echo esc_attr( $nonce ); ?>" data-preview="<?php echo $preview ? '1' : '0'; ?>">
			<div class="kc-uu-rp__backdrop" data-kc-uu-rp="later"></div>
			<div class="kc-uu-rp__card">
				<button type="button" class="kc-uu-rp__close" data-kc-uu-rp="later" aria-label="<?php esc_attr_e( 'Close', 'update-urls' ); ?>">&times;</button>

				<div class="kc-uu-rp__heart" aria-hidden="true">💜</div>

				<h2 id="kc-uu-rp-title" class="kc-uu-rp__title">
					<?php
					/* translators: %s: user's first name */
					printf( esc_html__( 'Hi %s, can we ask a small favour?', 'update-urls' ), esc_html( $first_name ) );
					?>
				</h2>

				<?php if ( '' !== $stat_line ) : ?>
					<p class="kc-uu-rp__stat"><?php echo wp_kses( $stat_line, [ 'strong' => [] ] ); ?></p>
				<?php endif; ?>

				<p class="kc-uu-rp__body">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: 1: "Update URLS" linked to its plugin page, 2: "KaizenCoders" linked to its website */
							__( '%1$s is built and supported by a small team at %2$s. We read every review, and each one helps another site owner decide to give us a try. On a long day, a kind word from someone who uses what we make honestly keeps us going.', 'update-urls' ),
							'<a href="' . esc_url( self::PLUGIN_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Update URLS', 'update-urls' ) . '</a>',
							'<a href="' . esc_url( self::KAIZENCODERS_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'KaizenCoders', 'update-urls' ) . '</a>'
						),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					);
					?>
				</p>

				<p class="kc-uu-rp__body">
					<?php esc_html_e( 'If Update URLS has earned it, would you take two minutes to leave a 5-star review on WordPress.org? It means more to us than you might think.', 'update-urls' ); ?>
				</p>

				<div class="kc-uu-rp__stars" aria-hidden="true">★★★★★</div>

				<div class="kc-uu-rp__actions">
					<a class="kc-uu-rp__primary" href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener" data-kc-uu-rp="review">
						<?php esc_html_e( 'Sure, happy to help!', 'update-urls' ); ?>
					</a>
					<button type="button" class="kc-uu-rp__secondary" data-kc-uu-rp="later"><?php esc_html_e( 'Maybe later', 'update-urls' ); ?></button>
					<button type="button" class="kc-uu-rp__secondary" data-kc-uu-rp="already_did"><?php esc_html_e( 'I already did 🙌', 'update-urls' ); ?></button>
				</div>

				<p class="kc-uu-rp__footer">
					<?php esc_html_e( 'Something not working for you?', 'update-urls' ); ?>
					<a href="<?php echo esc_url( self::CONTACT_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Tell us and we will fix it.', 'update-urls' ); ?></a>
					&nbsp;&middot;&nbsp;
					<button type="button" class="kc-uu-rp__link" data-kc-uu-rp="never"><?php esc_html_e( "Don't ask again", 'update-urls' ); ?></button>
				</p>
			</div>
		</div>

		<style>
			.kc-uu-rp { position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 16px; }
			.kc-uu-rp__backdrop { position: absolute; inset: 0; background: rgba(17, 24, 39, .55); animation: kc-uu-rp-fade .2s ease; }
			.kc-uu-rp__card {
				position: relative; box-sizing: border-box; width: 100%; max-width: 480px; max-height: calc(100vh - 32px); overflow: auto;
				background: #fff; border-radius: 14px; padding: 32px 30px 22px; text-align: center;
				box-shadow: 0 24px 60px rgba(17, 24, 39, .3); animation: kc-uu-rp-pop .25s ease;
			}
			.kc-uu-rp__close { position: absolute; top: 10px; right: 12px; background: none; border: 0; font-size: 24px; line-height: 1; color: #9ca3af; cursor: pointer; padding: 4px 8px; border-radius: 6px; }
			.kc-uu-rp__close:hover { background: #f3f4f6; color: #374151; }
			.kc-uu-rp__heart { font-size: 40px; line-height: 1; margin-bottom: 10px; }
			.kc-uu-rp__title { font-size: 20px; line-height: 1.35; margin: 0 0 10px; color: #111827; }
			.kc-uu-rp__stat { font-size: 14px; color: #4338ca; background: #eef2ff; border-radius: 8px; padding: 10px 12px; margin: 0 0 14px; }
			.kc-uu-rp__body { font-size: 14px; line-height: 1.6; color: #4b5563; margin: 0 0 10px; }
			.kc-uu-rp__body a { color: #4f46e5; font-weight: 600; text-decoration: none; }
			.kc-uu-rp__body a:hover { text-decoration: underline; }
			.kc-uu-rp__stars { font-size: 28px; letter-spacing: 4px; color: #f59e0b; margin: 8px 0 16px; }
			.kc-uu-rp__actions { display: flex; flex-direction: column; gap: 8px; }
			.kc-uu-rp__primary {
				display: block; background: #4f46e5; color: #fff !important; font-size: 15px; font-weight: 600; text-decoration: none;
				padding: 12px 18px; border-radius: 8px; transition: background .15s ease;
			}
			.kc-uu-rp__primary:hover, .kc-uu-rp__primary:focus { background: #4338ca; }
			.kc-uu-rp__secondary { background: #fff; border: 1px solid #e5e7eb; color: #374151; font-size: 14px; padding: 10px 18px; border-radius: 8px; cursor: pointer; }
			.kc-uu-rp__secondary:hover { background: #f9fafb; }
			.kc-uu-rp__footer { font-size: 12px; color: #9ca3af; margin: 16px 0 0; }
			.kc-uu-rp__footer a { color: #6b7280; }
			.kc-uu-rp__link { background: none; border: 0; padding: 0; color: #9ca3af; font-size: 12px; text-decoration: underline; cursor: pointer; }
			.kc-uu-rp__link:hover { color: #6b7280; }
			@keyframes kc-uu-rp-fade { from { opacity: 0; } to { opacity: 1; } }
			@keyframes kc-uu-rp-pop { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
			@media (prefers-reduced-motion: reduce) { .kc-uu-rp__backdrop, .kc-uu-rp__card { animation: none; } }
		</style>

		<script>
			( function () {
				var root = document.getElementById( 'kc-uu-review-prompt' );

				if ( ! root ) {
					return;
				}

				var answered = false;

				function respond( answer ) {
					if ( answered ) {
						return;
					}
					answered = true;

					root.style.display = 'none';
					document.removeEventListener( 'keydown', onKey );

					if ( '1' === root.getAttribute( 'data-preview' ) ) {
						return;
					}

					var body = new URLSearchParams();
					body.append( 'action', <?php echo wp_json_encode( self::AJAX_ACTION ); ?> );
					body.append( 'nonce', root.getAttribute( 'data-nonce' ) || '' );
					body.append( 'response', answer );

					fetch( ajaxurl, {
						method:      'POST',
						credentials: 'same-origin',
						headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
						body:        body.toString()
					} ).catch( function () { /* best effort, the popup is already closed */ } );
				}

				function onKey( e ) {
					if ( 'Escape' === e.key ) {
						respond( 'later' );
					}
				}

				root.addEventListener( 'click', function ( e ) {
					var target = e.target.closest( '[data-kc-uu-rp]' );

					if ( target ) {
						// The review link still opens in its new tab.
						respond( target.getAttribute( 'data-kc-uu-rp' ) );
					}
				} );

				document.addEventListener( 'keydown', onKey );

				var primary = root.querySelector( '.kc-uu-rp__primary' );
				if ( primary ) {
					primary.focus();
				}
			} )();
		</script>
		<?php
	}

	/* ---------------------------------------------------------------------
	 * Response
	 * ------------------------------------------------------------------ */

	/**
	 * Save the user's answer.
	 *
	 * @return void
	 *
	 * @since 1.5.4
	 */
	public function handle_response() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'forbidden' ], 403 );
		}

		$response  = isset( $_POST['response'] ) ? sanitize_key( wp_unslash( $_POST['response'] ) ) : '';
		$responses = self::get_responses();

		if ( ! isset( $responses[ $response ] ) ) {
			wp_send_json_error( [ 'message' => 'unknown_response' ], 400 );
		}

		$user_id = get_current_user_id();
		$key     = $responses[ $response ];

		update_user_meta( $user_id, $key, 1 );
		update_user_meta( $user_id, $key . '_time', time() );

		wp_send_json_success();
	}
}
