<?php

add_filter( 'wpsf_register_settings_kc_uu', 'kc_uu_tabbed_settings' );

function kc_uu_tabbed_settings( $wpsf_settings ) {
	$tabs = [
		[
			'id'    => 'general',
			'title' => __( 'General', 'update-urls' ),
		],
		[
			'id'    => 'email_digest',
			'title' => __( 'Email Digest', 'update-urls' ),
		],
	];

	$general_fields = [
		[
			'id'         => 'delete_data_on_uninstall',
			'title'      => __( 'Delete Data on Uninstall', 'update-urls' ),
			'desc'       => __( 'Remove all plugin data when the plugin is deleted.', 'update-urls' ),
			'type'       => 'checkbox',
			'default'    => 0,
			'order'      => 1,
		],
	];

	if ( UU()->is_pro() ) {
		$general_fields[] = [
			'id'         => 'page_size',
			'title'      => __( 'Max Page Size', 'update-urls' ),
			'title_desc' => __( 'The number of rows to process per batch. Decrease if you experience timeouts.', 'update-urls' ),
			'desc'       => __( 'The number of rows to process per batch. Decrease if you experience timeouts.', 'update-urls' ),
			'type'       => 'custom',
			'output'     => kc_uu_render_page_size_slider(),
			'order'      => 2,
		];
		$general_fields[] = [
			'id'         => 'max_results',
			'title'      => __( 'Max Results', 'update-urls' ),
			'title_desc' => __( 'Maximum number of change details to track per table. Higher values use more memory.', 'update-urls' ),
			'desc'       => __( 'Maximum number of change details to track per table. Higher values use more memory.', 'update-urls' ),
			'type'       => 'custom',
			'output'     => kc_uu_render_max_results_slider(),
			'order'      => 3,
		];
		$general_fields[] = [
			'id'         => 'enable_gzip',
			'title'      => __( 'Backup Compression', 'update-urls' ),
			'title_desc' => __( 'Compress backup files using gzip to reduce file size.', 'update-urls' ),
			'desc'       => __( 'Compress backup files using gzip to reduce file size.', 'update-urls' ),
			'type'       => 'checkbox',
			'default'    => 0,
			'order'      => 4,
		];
	}

	$wpsf_settings['tabs'] = apply_filters( 'kc_uu_filter_settings_tabs', $tabs );

	$email_digest_fields = [
		[
			'id'         => 'email_digest_enabled',
			'title'      => __( 'Enable Email Digest', 'update-urls' ),
			'title_desc' => __( 'Automatically send a summary of your site\'s URL health via email.', 'update-urls' ),
			'type'       => 'switch',
			'default'    => 0,
			'order'      => 1,
		],
		[
			'id'      => 'email_digest_frequency',
			'title'   => __( 'Digest Frequency', 'update-urls' ),
			'type'    => 'radio',
			'default' => 'weekly',
			'choices' => [
				'daily'   => __( 'Daily', 'update-urls' ),
				'weekly'  => __( 'Weekly', 'update-urls' ),
				'monthly' => __( 'Monthly', 'update-urls' ),
			],
			'order'   => 2,
		],
		[
			'id'     => 'email_digest_day',
			'title'  => __( 'Digest Day', 'update-urls' ),
			'desc'   => __( 'Day of the week for weekly digests, or day of the month for monthly digests. Not used for daily digests.', 'update-urls' ),
			'type'   => 'custom',
			'output' => kc_uu_render_email_digest_day_field(),
			'order'  => 3,
		],
		[
			'id'      => 'email_digest_time',
			'title'   => __( 'Send Time', 'update-urls' ),
			'desc'    => __( 'Time of day to send the digest in your site timezone.', 'update-urls' ),
			'type'    => 'select',
			'default' => '09:00',
			'choices' => kc_uu_get_email_digest_time_choices(),
			'order'   => 4,
		],
		[
			'id'          => 'email_digest_recipients',
			'title'       => __( 'Recipients', 'update-urls' ),
			'desc'        => sprintf( __( 'Enter one email address per line. If left blank, the digest is sent to the admin email: <b>%s</b>', 'update-urls' ), get_option( 'admin_email' ) ),
			'type'        => 'textarea',
			'default'     => '',
			'placeholder' => "user@example.com\nanother@example.com",
			'order'       => 5,
		],
		[
			'id'     => 'email_digest_status',
			'title'  => __( 'Digest Status', 'update-urls' ),
			'type'   => 'custom',
			'output' => kc_uu_render_email_digest_status_field(),
			'order'  => 6,
		],
	];

	$general_fields      = apply_filters( 'kc_uu_filter_general_settings', $general_fields );
	$email_digest_fields = apply_filters( 'kc_uu_filter_email_digest_settings', $email_digest_fields );

	$sections = [
		[
			'tab_id'        => 'general',
			'section_id'    => 'settings',
			'section_title' => __( 'General Settings', 'update-urls' ),
			'section_order' => 1,
			'fields'        => $general_fields,
		],
		[
			'tab_id'        => 'email_digest',
			'section_id'    => 'settings',
			'section_title' => __( 'Email Digest', 'update-urls' ),
			'section_order' => 1,
			'fields'        => $email_digest_fields,
		],
	];

	$wpsf_settings['sections'] = apply_filters( 'kc_uu_filter_settings_sections', $sections );

	return $wpsf_settings;
}

function kc_uu_get_email_digest_time_choices() {
	$choices = [];

	for ( $hour = 0; $hour < 24; $hour++ ) {
		foreach ( [ 0, 30 ] as $minute ) {
			$key             = sprintf( '%02d:%02d', $hour, $minute );
			$choices[ $key ] = $key;
		}
	}

	return $choices;
}

function kc_uu_render_email_digest_day_field() {
	$settings  = get_option( 'kc_uu_settings', [] );
	$frequency = isset( $settings['email_digest']['settings']['email_digest_frequency'] )
		? $settings['email_digest']['settings']['email_digest_frequency']
		: 'weekly';
	$day       = isset( $settings['email_digest']['settings']['email_digest_day'] )
		? (int) $settings['email_digest']['settings']['email_digest_day']
		: 1;

	$weekly_options = [
		1 => __( 'Monday', 'update-urls' ),
		2 => __( 'Tuesday', 'update-urls' ),
		3 => __( 'Wednesday', 'update-urls' ),
		4 => __( 'Thursday', 'update-urls' ),
		5 => __( 'Friday', 'update-urls' ),
		6 => __( 'Saturday', 'update-urls' ),
		7 => __( 'Sunday', 'update-urls' ),
	];

	$output  = '<div id="kc-uu-digest-day-wrapper"' . ( 'daily' === $frequency ? ' style="display:none;"' : '' ) . '>';
	$output .= '<select id="kc-uu-digest-day-select" name="kc_uu_settings[email_digest][settings][email_digest_day]">';

	if ( 'monthly' === $frequency ) {
		for ( $i = 1; $i <= 28; $i++ ) {
			$output .= '<option value="' . esc_attr( $i ) . '"' . selected( $day, $i, false ) . '>' . esc_html( $i ) . '</option>';
		}
	} else {
		foreach ( $weekly_options as $value => $label ) {
			$output .= '<option value="' . esc_attr( $value ) . '"' . selected( $day, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
	}

	$output .= '</select>';
	$output .= '</div>';

	return $output;
}

function kc_uu_render_email_digest_status_field() {
	$settings       = get_option( 'kc_uu_settings', [] );
	$enabled        = ! empty( $settings['email_digest']['settings']['email_digest_enabled'] ) ? 1 : 0;
	$frequency      = isset( $settings['email_digest']['settings']['email_digest_frequency'] )
		? $settings['email_digest']['settings']['email_digest_frequency']
		: 'weekly';
	$last_sent      = (int) \KaizenCoders\UpdateURLS\Option::get( 'email_digest_last_sent', 0 );
	$timezone       = wp_timezone_string();
	$preview_url    = add_query_arg(
		'_wpnonce', wp_create_nonce( 'kc_uu_email_digest_preview' ),
		admin_url( 'admin-post.php?action=kc_uu_email_digest_preview' )
	);
	$test_nonce     = wp_create_nonce( 'kc_uu_email_digest_nonce' );
	$status_markup  = $enabled
		? '<span style="color:#047857;font-weight:600;">&#10003; ' . esc_html__( 'Active', 'update-urls' ) . '</span>'
		: '<span style="color:#6b7280;">' . esc_html__( 'Inactive', 'update-urls' ) . '</span>';
	$last_sent_text = $last_sent > 0
		? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sent )
		: __( 'Never', 'update-urls' );
	$admin_email    = get_option( 'admin_email' );
	$weekly_options = [
		1 => __( 'Monday', 'update-urls' ),
		2 => __( 'Tuesday', 'update-urls' ),
		3 => __( 'Wednesday', 'update-urls' ),
		4 => __( 'Thursday', 'update-urls' ),
		5 => __( 'Friday', 'update-urls' ),
		6 => __( 'Saturday', 'update-urls' ),
		7 => __( 'Sunday', 'update-urls' ),
	];

	ob_start();
	?>
	<div class="kc-uu-email-digest-panel">
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
			<div style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;">
				<div style="font-size:12px;color:#6b7280;margin-bottom:4px;"><?php esc_html_e( 'Status', 'update-urls' ); ?></div>
				<div><?php echo wp_kses( $status_markup, [ 'span' => [ 'style' => true ] ] ); ?></div>
			</div>
			<div style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;">
				<div style="font-size:12px;color:#6b7280;margin-bottom:4px;"><?php esc_html_e( 'Frequency', 'update-urls' ); ?></div>
				<div><?php echo esc_html( ucfirst( $frequency ) ); ?></div>
			</div>
			<div style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;">
				<div style="font-size:12px;color:#6b7280;margin-bottom:4px;"><?php esc_html_e( 'Last Sent', 'update-urls' ); ?></div>
				<div><?php echo esc_html( $last_sent_text ); ?></div>
			</div>
			<div style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;">
				<div style="font-size:12px;color:#6b7280;margin-bottom:4px;"><?php esc_html_e( 'Timezone', 'update-urls' ); ?></div>
				<div><?php echo esc_html( $timezone ); ?></div>
			</div>
		</div>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
			<div style="padding:14px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
				<strong><?php esc_html_e( 'Preview Email', 'update-urls' ); ?></strong>
				<p style="margin:8px 0 12px;color:#6b7280;font-size:13px;"><?php esc_html_e( 'Open a preview of the digest in a new tab using sample data from the last 7 days.', 'update-urls' ); ?></p>
				<button type="button" class="button button-secondary" id="kc-uu-digest-preview-btn"><?php esc_html_e( 'Preview Email', 'update-urls' ); ?></button>
			</div>
			<div style="padding:14px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
				<strong><?php esc_html_e( 'Send Test Email', 'update-urls' ); ?></strong>
				<p style="margin:8px 0 12px;color:#6b7280;font-size:13px;"><?php esc_html_e( 'Send a test digest to verify formatting and delivery.', 'update-urls' ); ?></p>
				<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
					<input type="email" id="kc-uu-digest-test-email" class="regular-text" placeholder="<?php echo esc_attr( $admin_email ); ?>">
					<button type="button" class="button button-secondary" id="kc-uu-digest-test-btn"><?php esc_html_e( 'Send Test', 'update-urls' ); ?></button>
				</div>
				<div id="kc-uu-digest-test-status" style="display:none;margin-top:10px;font-size:13px;"></div>
			</div>
		</div>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var previewUrl  = <?php echo wp_json_encode( $preview_url ); ?>;
			var ajaxUrl     = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var ajaxNonce   = <?php echo wp_json_encode( $test_nonce ); ?>;
			var weeklyOptions = <?php echo wp_json_encode( $weekly_options ); ?>;

			function renderMonthlyOptions(select) {
				var html = '';
				for (var i = 1; i <= 28; i++) {
					html += '<option value="' + i + '">' + i + '</option>';
				}
				select.innerHTML = html;
			}

			function renderWeeklyOptions(select) {
				var html = '';
				Object.keys(weeklyOptions).forEach(function (key) {
					html += '<option value="' + key + '">' + weeklyOptions[key] + '</option>';
				});
				select.innerHTML = html;
			}

			function syncDigestDayField() {
				var selectedFrequency = document.querySelector('input[name="kc_uu_settings[email_digest][settings][email_digest_frequency]"]:checked');
				var wrapper = document.getElementById('kc-uu-digest-day-wrapper');
				var select  = document.getElementById('kc-uu-digest-day-select');

				if (!selectedFrequency || !wrapper || !select) {
					return;
				}

				// Hide/show the entire settings row (tr) so the label disappears too.
				var row = wrapper.closest('tr');

				var previousValue = select.value;
				if (selectedFrequency.value === 'daily') {
					if (row) row.style.display = 'none';
					return;
				}

				if (row) row.style.display = '';

				if (selectedFrequency.value === 'monthly') {
					renderMonthlyOptions(select);
					if (parseInt(previousValue, 10) >= 1 && parseInt(previousValue, 10) <= 28) {
						select.value = previousValue;
					}
				} else {
					renderWeeklyOptions(select);
					if (weeklyOptions[previousValue]) {
						select.value = previousValue;
					}
				}
			}

			document.querySelectorAll('input[name="kc_uu_settings[email_digest][settings][email_digest_frequency]"]').forEach(function (input) {
				input.addEventListener('change', syncDigestDayField);
			});
			syncDigestDayField();

			var previewBtn = document.getElementById('kc-uu-digest-preview-btn');
			if (previewBtn) {
				previewBtn.addEventListener('click', function () {
					window.open(previewUrl, '_blank');
				});
			}

			var testBtn   = document.getElementById('kc-uu-digest-test-btn');
			var testEmail = document.getElementById('kc-uu-digest-test-email');
			var status    = document.getElementById('kc-uu-digest-test-status');

			if (testBtn && testEmail && status) {
				testBtn.addEventListener('click', function () {
					var email = testEmail.value.trim();
					status.style.display = 'none';
					testBtn.disabled = true;

					var formData = new window.FormData();
					formData.append('action', 'kc_uu_email_digest_test');
					formData.append('nonce', ajaxNonce);
					formData.append('email', email);

					window.fetch(ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						body: formData
					}).then(function (response) {
						return response.json();
					}).then(function (payload) {
						status.style.display = 'block';
						status.style.color = payload.success ? '#047857' : '#b91c1c';
						status.textContent = payload.data && payload.data.message ? payload.data.message : 'Request failed.';
					}).catch(function () {
						status.style.display = 'block';
						status.style.color = '#b91c1c';
						status.textContent = 'Request failed.';
					}).finally(function () {
						testBtn.disabled = false;
					});
				});
			}
		});
	</script>
	<?php

	return ob_get_clean();
}

function kc_uu_render_page_size_slider() {
	$uu_settings = get_option( 'kc_uu_settings', [] );
	$value       = isset( $uu_settings['general']['settings']['page_size'] )
		? (int) $uu_settings['general']['settings']['page_size']
		: 20000;

	ob_start();
	?>
	<div class="flex items-center gap-4">
		<input type="range"
		       id="kc_uu_page_size"
		       name="kc_uu_settings[general][settings][page_size]"
		       min="1000" max="50000" step="1000"
		       value="<?php echo esc_attr( $value ); ?>"
		       class="w-full kc-uu-range-slider"
		       oninput="document.getElementById('kc_uu_page_size_value').textContent = Number(this.value).toLocaleString()" />
		<span id="kc_uu_page_size_value" class="text-sm font-medium text-gray-900 min-w-[60px]">
			<?php echo esc_html( number_format( $value ) ); ?>
		</span>
	</div>
	<?php
	return ob_get_clean();
}

function kc_uu_render_max_results_slider() {
	$uu_settings = get_option( 'kc_uu_settings', [] );
	$value       = isset( $uu_settings['general']['settings']['max_results'] )
		? (int) $uu_settings['general']['settings']['max_results']
		: 60;

	ob_start();
	?>
	<div class="flex items-center gap-4">
		<input type="range"
		       id="kc_uu_max_results"
		       name="kc_uu_settings[general][settings][max_results]"
		       min="20" max="1000" step="20"
		       value="<?php echo esc_attr( $value ); ?>"
		       class="w-full kc-uu-range-slider"
		       oninput="document.getElementById('kc_uu_max_results_value').textContent = this.value" />
		<span id="kc_uu_max_results_value" class="text-sm font-medium text-gray-900 min-w-[40px]">
			<?php echo esc_html( $value ); ?>
		</span>
	</div>
	<?php
	return ob_get_clean();
}
