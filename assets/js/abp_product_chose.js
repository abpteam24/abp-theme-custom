/**
 * ABP product "Why choose" capability cards.
 *
 * Capability lists can get long (some products ship 20+). Keep the first 6
 * cards visible and tuck the rest behind a disclosure so the section stays
 * scannable, matching the feature comparison disclosure pattern.
 */
(function () {
	'use strict';

	var VISIBLE_LIMIT = 6;

	function initGrid(grid, index) {
		var cards = Array.prototype.slice.call(grid.querySelectorAll('.abp-chose-card'));
		if (cards.length <= VISIBLE_LIMIT + 4) {
			return;
		}

		var footer = grid.parentElement ? grid.parentElement.querySelector('[data-abp-chose-footer]') : null;
		var button = footer ? footer.querySelector('[data-abp-chose-more]') : null;
		if (!footer || !button) {
			return;
		}

		var label = button.querySelector('.abp-chose-more-text');
		var moreText = button.getAttribute('data-more-text') || '';
		var lessText = button.getAttribute('data-less-text') || '';
		var total = cards.length;

		if (!grid.id) {
			grid.id = 'abp-chose-grid-' + index;
		}
		button.setAttribute('aria-controls', grid.id);

		grid.classList.add('is-collapsed');
		footer.hidden = false;

		button.addEventListener('click', function () {
			var expanded = button.getAttribute('aria-expanded') === 'true';

			if (expanded) {
				grid.classList.add('is-collapsed');
				button.setAttribute('aria-expanded', 'false');
				if (label) {
					label.textContent = moreText;
				}
			} else {
				grid.classList.remove('is-collapsed');
				button.setAttribute('aria-expanded', 'true');
				if (label) {
					label.textContent = lessText;
				}
			}
		});
	}

	function init() {
		var grids = document.querySelectorAll('[data-abp-chose-grid]');
		Array.prototype.forEach.call(grids, function (grid, index) {
			initGrid(grid, index);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
