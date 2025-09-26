<?php

/**
 * The Ajax functionality of the plugin.
 *
 * @link       https://kaizencoders.com
 * @since      1.0.0
 *
 * @package    KaizenCoders\URL_Shortify
 * @subpackage Ajax
 */

namespace KaizenCoders\UpdateURLS;

/**
 * Class Ajax
 *
 * Handle Ajax request
 *
 * @since   1.1.3
 * @package KaizenCoders\URL_Shortify
 *
 */
class Ajax {
	/**
	 * Init
	 *
	 * @since 1.1.3
	 */
	public function init() {
		add_action( 'wp_ajax_update_urls_manage_plugin', [ $this, 'handle_plugin_management' ] );
	}

	public function handle_plugin_management() {
		check_ajax_referer( 'update-urls-plugin-management', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ] );
		}

		$action = sanitize_text_field( $_POST['plugin_action'] );
		$plugin = sanitize_text_field( $_POST['plugin'] );
		$slug   = sanitize_text_field( $_POST['slug'] );

		switch ( $action ) {
			case 'install':
				include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

				$api = plugins_api( 'plugin_information', [ 'slug' => $slug ] );
				if ( is_wp_error( $api ) ) {
					wp_send_json_error( [ 'message' => $api->get_error_message() ] );
				}

				$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
				$result   = $upgrader->install( $api->download_link );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				}
				break;

			case 'activate':
				$result = activate_plugin( $plugin );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				}
				break;

			case 'deactivate':
				deactivate_plugins( [ $plugin ] );
				break;

			default:
				wp_send_json_error( [ 'message' => 'Invalid action' ] );
		}

		wp_send_json_success();
	}
}
