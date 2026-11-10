/**
 * ABP product shortcode copy buttons.
 *
 * Each shortcode card exposes a copy button so visitors can grab the
 * shortcode without selecting the text manually. The button is hidden in
 * the markup and only revealed once this script is ready.
 */
(function () {
	'use strict';

	var RESET_DELAY = 1800;

	function fallbackCopy(text) {
		return new Promise(function (resolve, reject) {
			var field = document.createElement('textarea');
			field.value = text;
			field.setAttribute('readonly', '');
			field.style.position = 'fixed';
			field.style.top = '-9999px';
			document.body.appendChild(field);
			field.select();

			try {
				document.execCommand('copy');
				resolve();
			} catch (error) {
				reject(error);
			} finally {
				document.body.removeChild(field);
			}
		});
	}

	function copyText(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}
		return fallbackCopy(text);
	}

	function setState(button, label, icon, copied) {
		var text = button.querySelector('.abp_sc_copy_text');
		var glyph = button.querySelector('i');

		if (text) {
			text.textContent = label;
		}
		if (glyph) {
			glyph.className = icon;
		}
		button.classList.toggle('is-copied', copied);
	}

	function initButton(button) {
		button.hidden = false;

		var defaultLabel = button.querySelector('.abp_sc_copy_text');
		defaultLabel = defaultLabel ? defaultLabel.textContent : 'Copy';
		var defaultAria = button.getAttribute('aria-label') || '';
		var timer = null;

		button.addEventListener('click', function () {
			var value = button.getAttribute('data-abp-copy') || '';

			copyText(value).then(function () {
				setState(button, 'Copied', 'fas fa-check', true);
				button.setAttribute('aria-label', 'Shortcode copied');
			}).catch(function () {
				setState(button, 'Copy failed', 'fas fa-exclamation-triangle', false);
				button.setAttribute('aria-label', 'Copy failed, please copy the shortcode manually');
			}).then(function () {
				window.clearTimeout(timer);
				timer = window.setTimeout(function () {
					setState(button, defaultLabel, 'fas fa-copy', false);
					button.setAttribute('aria-label', defaultAria);
				}, RESET_DELAY);
			});
		});
	}

	function init() {
		var buttons = document.querySelectorAll('.abp_sc_copy[data-abp-copy]');
		Array.prototype.forEach.call(buttons, initButton);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
