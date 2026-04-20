<?php

namespace KaizenCoders\UpdateURLS\Admin;

class Settings {

	/**
	 * @var string
	 */
	private $plugin_path;

	/**
	 * @var \KaizenCoders\UpdateURLS\Settings
	 */
	private $wpsf;

	public function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );

		$this->wpsf = new \KaizenCoders\UpdateURLS\Settings( $this->plugin_path . '/admin-settings.php', 'kc_uu' );

		add_action( 'admin_menu', [ $this, 'add_settings_page' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( $this->wpsf->get_option_group() . '_settings_validate', [ $this, 'validate_settings' ] );
	}

	public function enqueue_styles() {
		if ( ! \KaizenCoders\UpdateURLS\Helper::is_plugin_admin_screen() ) {
			return;
		}

		wp_enqueue_style(
			'update-urls-main',
			plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'dist/styles/app.css',
			[],
			KC_UU_PLUGIN_VERSION,
			'all'
		);

		wp_enqueue_style(
			'update-urls-admin',
			plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'dist/styles/update-urls-admin.css',
			[],
			KC_UU_PLUGIN_VERSION,
			'all'
		);
	}

	public function enqueue_scripts() {
		if ( ! \KaizenCoders\UpdateURLS\Helper::is_plugin_admin_screen() ) {
			return;
		}

		wp_enqueue_script(
			'uu-app',
			plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'dist/scripts/app.js',
			[ 'jquery' ],
			KC_UU_PLUGIN_VERSION,
			true
		);

		wp_enqueue_script(
			'update-urls-admin',
			plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'dist/scripts/update-urls-admin.js',
			[ 'jquery' ],
			KC_UU_PLUGIN_VERSION,
			true
		);

	}

	public function add_settings_page() {
		$this->wpsf->add_settings_page( [
			'parent_slug' => 'update-urls',
			'page_title'  => __( 'Settings', 'update-urls' ),
			'menu_title'  => __( 'Settings', 'update-urls' ),
			'capability'  => 'manage_options',
		] );
	}

	public function validate_settings( $input ) {
		$general = isset( $input['general']['settings'] ) ? $input['general']['settings'] : [];

		$input['general']['settings']['delete_data_on_uninstall'] = ! empty( $general['delete_data_on_uninstall'] ) ? 1 : 0;
		$input['general']['settings']['enable_gzip']              = ! empty( $general['enable_gzip'] ) ? 1 : 0;

		$page_size = isset( $general['page_size'] ) ? (int) $general['page_size'] : 20000;
		$input['general']['settings']['page_size'] = max( 1000, min( 50000, $page_size ) );

		$max_results = isset( $general['max_results'] ) ? (int) $general['max_results'] : 60;
		$input['general']['settings']['max_results'] = max( 20, min( 1000, $max_results ) );

		// Email digest validation.
		$digest = isset( $input['email_digest']['settings'] ) ? $input['email_digest']['settings'] : [];

		$input['email_digest']['settings']['email_digest_enabled'] = ! empty( $digest['email_digest_enabled'] ) ? 1 : 0;

		$allowed_frequencies = [ 'daily', 'weekly', 'monthly' ];
		$frequency           = isset( $digest['email_digest_frequency'] ) ? $digest['email_digest_frequency'] : 'weekly';
		if ( ! in_array( $frequency, $allowed_frequencies, true ) ) {
			$frequency = 'weekly';
		}
		$input['email_digest']['settings']['email_digest_frequency'] = $frequency;

		$day = isset( $digest['email_digest_day'] ) ? (int) $digest['email_digest_day'] : 1;
		if ( 'monthly' === $frequency ) {
			$day = min( 28, max( 1, $day ) );
		} else {
			$day = min( 7, max( 1, $day ) );
		}
		$input['email_digest']['settings']['email_digest_day'] = $day;

		$time = isset( $digest['email_digest_time'] ) ? $digest['email_digest_time'] : '09:00';
		if ( ! preg_match( '/^\d{2}:\d{2}$/', (string) $time ) ) {
			$time = '09:00';
		}
		$input['email_digest']['settings']['email_digest_time'] = $time;

		$recipients_raw   = isset( $digest['email_digest_recipients'] ) ? (string) $digest['email_digest_recipients'] : '';
		$recipient_lines  = preg_split( '/[\r\n,]+/', $recipients_raw );
		$valid_recipients = [];
		foreach ( $recipient_lines as $line ) {
			$email = sanitize_email( trim( $line ) );
			if ( is_email( $email ) ) {
				$valid_recipients[] = $email;
			}
		}
		$input['email_digest']['settings']['email_digest_recipients'] = implode( "\n", array_unique( $valid_recipients ) );

		return $input;
	}
}
