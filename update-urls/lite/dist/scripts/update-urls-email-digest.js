/* global kcUuEmailDigest */
(function ($) {
	'use strict';

	var CONDITIONAL_CLASS = '.kc-uu-digest-conditional';
	var ENABLED_INPUT     = '#email_digest_settings_enabled';
	var FREQUENCY_INPUT   = 'input[name="kc_uu_settings[email_digest][settings][frequency]"]';
	var DAY_SELECT        = '#kc-uu-digest-day';
	var PREVIEW_BTN       = '#kc-uu-digest-preview-btn';
	var TEST_BTN          = '#kc-uu-digest-test-btn';
	var TEST_EMAIL        = '#kc-uu-digest-test-email';
	var TEST_STATUS       = '#kc-uu-digest-test-status';

	/**
	 * Show or hide digest conditional fields based on enabled toggle.
	 *
	 * @param {boolean} enabled
	 */
	function toggleConditionals(enabled) {
		if (enabled) {
			$(CONDITIONAL_CLASS).show();
		} else {
			$(CONDITIONAL_CLASS).hide();
		}
	}

	/**
	 * Get ordinal suffix for a number.
	 *
	 * @param {number} n
	 * @returns {string}
	 */
	function getOrdinal(n) {
		var s = ['th', 'st', 'nd', 'rd'];
		var v = n % 100;
		return n + (s[(v - 20) % 10] || s[v] || s[0]);
	}

	/**
	 * Rebuild the day selector options based on selected frequency.
	 *
	 * @param {string} frequency 'daily', 'weekly', or 'monthly'
	 */
	function updateDaySelector(frequency) {
		var $wrapper = $('#kc-uu-digest-day-wrapper');
		var $select  = $(DAY_SELECT);
		var current  = $select.val();
		var options  = '';

		// Daily frequency does not use a day selector.
		if (frequency === 'daily') {
			$wrapper.hide();
			return;
		}

		$wrapper.show();

		if (frequency === 'monthly') {
			for (var d = 1; d <= 28; d++) {
				var selected = (parseInt(current, 10) === d) ? ' selected' : '';
				options += '<option value="' + d + '"' + selected + '>' + getOrdinal(d) + '</option>';
			}
		} else {
			var days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
			for (var i = 0; i < days.length; i++) {
				var val      = i + 1;
				var selAttr  = (parseInt(current, 10) === val) ? ' selected' : '';
				options += '<option value="' + val + '"' + selAttr + '>' + days[i] + '</option>';
			}
		}

		$select.html(options);
	}

	/**
	 * Send a test email via AJAX.
	 *
	 * @param {string}  email    Recipient email address.
	 * @param {Element} btnEl    The button element (to show spinner state).
	 * @param {Element} statusEl The status span element.
	 */
	function sendTestEmail(email, btnEl, statusEl) {
		var $btn    = $(btnEl);
		var $status = $(statusEl);
		var label   = $btn.text();

		$btn.prop('disabled', true).addClass('kc-uu-btn-loading').text('Sending\u2026');
		$status.hide().removeClass('kc-uu-status-success kc-uu-status-error');

		$.ajax({
			url: kcUuEmailDigest.ajaxUrl,
			method: 'POST',
			data: {
				action: 'kc_uu_email_digest_test',
				nonce:  kcUuEmailDigest.ajaxNonce,
				email:  email
			},
			success: function (response) {
				if (response.success) {
					$status
						.text(response.data.message || 'Test email sent!')
						.addClass('kc-uu-status-success')
						.show();
				} else {
					$status
						.text((response.data && response.data.message) || 'Failed to send test email.')
						.addClass('kc-uu-status-error')
						.show();
				}
			},
			error: function () {
				$status
					.text('Failed to send test email.')
					.addClass('kc-uu-status-error')
					.show();
			},
			complete: function () {
				$btn.prop('disabled', false).removeClass('kc-uu-btn-loading').text(label);
				setTimeout(function () {
					$status.fadeOut(400);
				}, 5000);
			}
		});
	}

	$(function () {
		// Initial toggle state.
		var $enabledSwitch = $(ENABLED_INPUT);
		var isEnabled = $enabledSwitch.is(':checked') || $enabledSwitch.val() == '1';
		toggleConditionals(isEnabled);

		// Toggle on change.
		$(document).on('change', ENABLED_INPUT, function () {
			toggleConditionals($(this).is(':checked'));
		});

		// Frequency change → rebuild day selector.
		$(document).on('change', FREQUENCY_INPUT, function () {
			updateDaySelector($(this).val());
		});

		// Preview button → open popup.
		$(document).on('click', PREVIEW_BTN, function (e) {
			e.preventDefault();
			window.open(
				kcUuEmailDigest.previewUrl,
				'kc_uu_email_preview',
				'width=800,height=600,scrollbars=yes,resizable=yes'
			);
		});

		// Test email button.
		$(document).on('click', TEST_BTN, function (e) {
			e.preventDefault();
			var email = $(TEST_EMAIL).val().trim();
			if (!email) {
				email = $(TEST_EMAIL).attr('placeholder') || '';
			}
			if (!email || !/\S+@\S+\.\S+/.test(email)) {
				$(TEST_STATUS)
					.text('Please enter a valid email address.')
					.removeClass('kc-uu-status-success')
					.addClass('kc-uu-status-error')
					.show();
				return;
			}
			sendTestEmail(email, this, TEST_STATUS);
		});
	});

})(jQuery);
