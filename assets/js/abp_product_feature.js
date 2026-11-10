/**
 * ABP product feature comparison lists.
 *
 * Free / PRO comparison lists can get very long (some products ship 40+
 * features). Keep the first VISIBLE_LIMIT rows in view and tuck the rest
 * behind a disclosure so the two cards stay easy to scan side by side.
 */
(function () {
	'use strict';

	var VISIBLE_LIMIT = 12;

	function formatCount(count, more) {
		var template = more ? 'Show all %s features' : 'Show fewer features';
		return more ? template.replace('%s', count) : template;
	}

	function initList(card, index) {
		var list = card.querySelector('ul.abp_list');
		if (!list) {
			return;
		}

		var items = Array.prototype.slice.call(list.children);
		if (items.length <= VISIBLE_LIMIT + 1) {
			return;
		}

		if (!list.id) {
			list.id = 'abp-feature-list-' + index;
		}

		var hiddenItems = items.slice(VISIBLE_LIMIT);
		var total = items.length;

		var toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'abp-feature-more';
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-controls', list.id);

		var label = document.createElement('span');
		label.className = 'abp-feature-more-text';
		label.textContent = formatCount(total, true);

		var icon = document.createElement('span');
		icon.className = 'abp-feature-more-icon';
		icon.setAttribute('aria-hidden', 'true');

		toggle.appendChild(label);
		toggle.appendChild(icon);
		card.appendChild(toggle);

		hiddenItems.forEach(function (item) {
			item.hidden = true;
		});

		toggle.addEventListener('click', function () {
			var expanded = toggle.getAttribute('aria-expanded') === 'true';

			hiddenItems.forEach(function (item) {
				item.hidden = expanded;
			});

			toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
			toggle.classList.toggle('is-open', !expanded);
			label.textContent = formatCount(total, expanded);
		});
	}

	function init() {
		var cards = document.querySelectorAll('div.abp_product_feature .free_list, div.abp_product_feature .pro_list');
		Array.prototype.forEach.call(cards, function (card, index) {
			initList(card, index);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
