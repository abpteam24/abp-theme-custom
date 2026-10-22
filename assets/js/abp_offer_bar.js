(function ($) {
	"use strict";
	$(function () {
		var cfg = window.abpOfferBar || {};
		var $modal = $('#abp-offer-modal');
		var $form = $('#abp-offer-form');
		var $response = $('#abp-offer-response');

		$('[data-abp-offer-open]').on('click', function () {
			$modal.addClass('abp-offer-open').attr('aria-hidden', 'false');
			$response.hide();
		});

		$modal.find('[data-abp-offer-close]').on('click', function () {
			$modal.removeClass('abp-offer-open').attr('aria-hidden', 'true');
		});

		$(document).on('keyup.abpOffer', function (e) {
			if (e.key === 'Escape') {
				$modal.removeClass('abp-offer-open').attr('aria-hidden', 'true');
			}
		});

		$('[data-abp-offer-copy]').on('click', function () {
			var code = $(this).attr('data-abp-offer-copy');
			var $btn = $(this);
			var $label = $btn.find('span').length ? $btn.find('span') : $btn;
			var original = $label.text();
			var done = function () {
				$label.text(cfg.copied || 'Copied!');
				setTimeout(function () { $label.text(original); }, 1600);
			};
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText(code).then(done, done);
			} else {
				var $tmp = $('<input type="text" />').val(code).appendTo('body').select();
				try { document.execCommand('copy'); } catch (err) {}
				$tmp.remove();
				done();
			}
		});

		$form.on('submit', function (e) {
			e.preventDefault();
			var form = this;
			var fd = new FormData(form);
			fd.append('action', 'abp_free_offer_submit');

			var $btn = $form.find('.abp-offer-submit');
			$response.hide();
			$btn.prop('disabled', true).text(cfg.sending || 'Sending...');

			$.ajax({
				url: cfg.ajaxUrl,
				type: 'POST',
				data: fd,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(res) {
					var message = (res && res.data && res.data.message) ? res.data.message : (cfg.unexpected || 'Unexpected response from server. Please try again.');
					$response.show().text(message);
					if (res && res.success) {
						$response.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' });
						form.reset();
					} else {
						$response.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fca5a5' });
					}
					$btn.prop('disabled', false).text(cfg.sendLabel || 'Send Me My Discount Code');
				},
				error: function() {
					$response.show().text(cfg.networkError || 'We could not reach the server. Please try again in a moment.').css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fca5a5' });
					$btn.prop('disabled', false).text(cfg.sendLabel || 'Send Me My Discount Code');
				}
			});
		});
	});
}(jQuery));
