/**
 * ABP home product cards.
 *
 * The homepage shows the first six PRO features of each product and keeps
 * the rest hidden behind a "more" disclosure so the cards stay compact.
 */
(function () {
	'use strict';

	function initCard(card) {
		var toggle = card.querySelector('[data-abp-home-more]');
		var list = card.querySelector('[data-abp-home-features]');

		if (!toggle || !list) {
			return;
		}

		var extras = Array.prototype.slice.call(list.querySelectorAll('.abp-home-card-extra'));
		if (!extras.length) {
			return;
		}

		var label = toggle.querySelector('.abp-home-card-more-text');
		var moreText = label ? label.textContent : '';
		var lessText = toggle.getAttribute('data-less-text') || 'Show fewer features';

		toggle.addEventListener('click', function () {
			var expanded = toggle.getAttribute('aria-expanded') === 'true';

			extras.forEach(function (item) {
				item.hidden = expanded;
			});

			toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
			toggle.classList.toggle('abp-active', !expanded);

			if (label) {
				label.textContent = expanded ? moreText : lessText;
			}
		});
	}

	function init() {
		var cards = document.querySelectorAll('.abp_home_product .abp-home-card');
		Array.prototype.forEach.call(cards, initCard);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
