<?php

namespace KaizenCoders\UpdateURLS;

/**
 * Class Promo
 *
 * Handle Promotional Campaign
 *
 * @since   1.8.0
 * @package KaizenCoders\URL_Shortify
 *
 */
class Promo {
	/**
	 * Initialize Promotions
	 *
	 * @since 1.8.0
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'dismiss_promotions' ] );
		add_action( 'admin_notices', [ $this, 'handle_promotions' ] );
	}

	/**
	 * Get Valid Promotions.
	 *
	 * @since 1.5.12.2
	 * @return string[]
	 *
	 */
    public function get_valid_promotions() {
        return [
                'price_increase_notification',
                'magic_link_launch_offer',
                'pre_launch_offer',
        ];
    }

	/**
	 * Dismiss Promotions.
	 *
	 * @since 1.5.12.2
	 */
	public function dismiss_promotions() {
		if ( isset( $_GET['kc_uu_dismiss_admin_notice'] ) && $_GET['kc_uu_dismiss_admin_notice'] == '1' && isset( $_GET['option_name'] ) ) {
			$option_name = sanitize_text_field( $_GET['option_name'] );

			$valid_options = $this->get_valid_promotions();

			if ( in_array( $option_name, $valid_options ) ) {

				update_option( 'kc_uu_' . $option_name . '_dismissed', 'yes', false );

                if ( in_array( $option_name, $valid_options ) ) {
                    if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) ) {
                        $redirect_to = esc_url_raw( $_GET['redirect_to'] );
                        wp_redirect( $redirect_to );
                    } else {
                        $referer = wp_get_referer();
                        wp_safe_redirect( $referer );
                    }
                }

				exit();
			}
		}
	}

	/**
	 * Handle promotions activity.
	 *
	 * @since 1.5.12.2
	 */
	public function handle_promotions() {
        $magic_link_launch_offer = [
                'title'                         => "<b class='text-red-600 text-xl'>" . __( 'WordPress Plugin Launch Offer - Magic Link PRO', 'update-urls' ) . "</b>",
                'start_date'                    => '2025-09-10',
                'end_date'                      => '2025-09-30',
                'total_links'                   => 1,
                'start_after_installation_days' => 0,
                'pricing_url'                   => UU()->get_pricing_url( 'yearly' ),
                'promotion'                     => 'magic_link_launch_offer',
                'message'                       => __( '<p class="text-xl">Magic Link PRO launch offer <b class="text-2xl">flat 50%</b> discount until <b class="text-red-600 text-xl">September 30, 2025</b></p>', 'update-urls' ),
                'coupon_message'                => __( 'Use Coupon Code - <b>LAUNCH50</b>', 'update-urls' ),
                'show_upgrade'                  => true,
                'show_plan'                     => 'free',
                'dismiss_url'                   => add_query_arg( 'pricing', 'true', 'https://kaizencoders.com/magic-link' ),
                'banner'                        => false,
                'redirect_to'                  => 'https://kaizencoders.com/magic-link',
        ];

		$pre_launch_offer = [
			'title'                         => "<b class='text-red-600 text-xl'>" . __( '🚀 Pre-launch Offer', 'update-urls' ) . "</b>",
			'start_date'                    => '2024-09-20',
			'end_date'                      => '2024-10-03',
			'start_after_installation_days' => 0,
			'pricing_url'                   => 'https://kaizencoders.com/social-linkz/',
			'promotion'                     => 'pre_launch_offer',
			'message'                       => __( '<p class="text-xl">Get <b>Update URLs</b> & <b>Social Linkz</b> (newly launched) PRO for Lifetime at Just $49 until <b class="text-red-600 text-xl">September 30, 2024</b></p>', 'update-urls' ),
			'coupon_message'                => '',
			'check_plan'                    => 'free',
		];

        // Promotion.
        if ( Helper::can_show_promotion( $magic_link_launch_offer )) {
            $this->show_promotion( 'magic_link_launch_offer', $magic_link_launch_offer );
        } elseif ( Helper::can_show_promotion( $pre_launch_offer ) ) {
            $this->show_promotion( 'pre_launch_offer', $pre_launch_offer );
        }
	}

	/**
	 * Show Promotion.
	 *
	 * @since 1.5.12.2
	 *
	 * @param $data      array
	 * @param $promotion string
	 *
	 */
	public function show_promotion( $promotion, $data ) {

		$current_screen_id = Helper::get_current_screen_id();

		if ( in_array( $promotion, [
				'initial_upgrade',
				'regular_upgrade_banner',
			] ) && Helper::is_plugin_admin_screen( $current_screen_id ) ) {
			$action = Helper::get_data( $_GET, 'action' );
			if ( 'statistics' === $action ) {
				?>
                <div class="wrap">
					<?php Helper::get_upgrade_banner( null, null, $data ); ?>
                </div>
				<?php
			}
		} else {
			$query_strings = [
				'kc_uu_dismiss_admin_notice' => 1,
				'option_name'                => $promotion,
			];

            if ( isset( $data['redirect_to'] ) && ! empty( $data['redirect_to'] ) ) {
                $query_strings['redirect_to'] = esc_url( $data['redirect_to'] );
            }
			?>

            <div class="wrap">
				<?php Helper::get_upgrade_banner( $query_strings, true, $data ); ?>
            </div>
			<?php
		}
	}

	/**
	 * Is Promo displayed and dismissed by user?
	 *
	 * @since 1.5.12.2
	 *
	 * @param $promo
	 *
	 * @return bool
	 *
	 */
	public function is_promotion_dismissed( $promotion ) {
		if ( empty( $promotion ) ) {
			return false;
		}

		$promotion_dismissed_option = 'kc_us_' . trim( $promotion ) . '_dismissed';

		return 'yes' === get_option( $promotion_dismissed_option );
	}
}
