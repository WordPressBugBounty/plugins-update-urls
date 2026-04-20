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
     * @return string[]
     *
     * @since 1.5.12.2
     */
    public function get_valid_promotions() {
        return [
                'price_increase_notification',
                'magic_link_launch_offer',
                'pre_launch_offer',
                'major_upgrade_pro',
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

                if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) ) {
                    $redirect_to = esc_url_raw( $_GET['redirect_to'] );
                    wp_redirect( $redirect_to );
                } else {
                    $referer = wp_get_referer();
                    wp_safe_redirect( $referer );
                }

                exit();
            }
        }
    }

    /**
     * Handle promotions activity.
     *
     * @since 1.4.2
     */
    public function handle_promotions() {
        $major_upgrade = [
                'title'                         => "<b class='text-red-600 text-xl'>" . __( 'Major Upgrade', 'update-urls' ) . "</b>",
                'start_date'                    => '2026-02-20',
                'end_date'                      => '2026-03-05',
                'start_after_installation_days' => 0,
                'pricing_url'                   => 'https://kaizencoders.com/update-urls/',
                'promotion'                     => 'major_upgrade_pro',
                'message'                       => __( '<p class="text-xl">Get Dry-Run, One Click Export/Import Database, History and much more at flat 50% OFF until <b class="text-red-600 text-xl">April 30, 2026</b></p>', 'update-urls' ),
                'coupon_message'                => '<p class="text-xl">Use Coupon Code - <b class="text-red-600">SPECIAL50</b></p>',
                'check_plan'                    => 'free',
        ];

        // Promotion.
        if ( Helper::can_show_promotion( $major_upgrade ) ) {
            $this->show_promotion( 'major_upgrade_pro', $major_upgrade );
        }
    }

    /**
     * Show Promotion.
     *
     * @param $data      array
     * @param $promotion string
     *
     * @since 1.5.12.2
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
                    <?php
                    Helper::get_upgrade_banner( null, null, $data ); ?>
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
                <?php
                Helper::get_upgrade_banner( $query_strings, true, $data ); ?>
            </div>
            <?php
        }
    }

    /**
     * Is Promo displayed and dismissed by user?
     *
     * @param $promo
     *
     * @return bool
     *
     * @since 1.5.12.2
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
