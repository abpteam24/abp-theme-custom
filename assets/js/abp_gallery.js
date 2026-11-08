/**
 * ABP screenshot gallery.
 *
 * Batched "Load More" disclosure for the masonry grid plus a keyboard
 * accessible lightbox with previous / next navigation.
 */
(function () {
	'use strict';

	var BODY_CLASS = 'abp-lightbox-open';

	function toArray(list) {
		return Array.prototype.slice.call(list);
	}

	function preload(url) {
		if (!url) {
			return;
		}
		var img = new Image();
		img.src = url;
	}

	function initGallery(root) {
		var grid = root.querySelector('[data-abp-gallery-grid]');
		var lightbox = root.querySelector('[data-abp-lightbox]');

		if (!grid || !lightbox) {
			return;
		}

		var items = toArray(grid.querySelectorAll('[data-abp-gallery-item]'));
		if (!items.length) {
			return;
		}

		var batch = parseInt(root.getAttribute('data-batch'), 10);
		if (!batch || batch < 1) {
			batch = 12;
		}

		var total = items.length;
		var visible = Math.min(batch, total);

		var moreBtn = root.querySelector('[data-abp-gallery-more]');
		var status = root.querySelector('[data-abp-gallery-status]');

		/* ------------------------------------------------------------------
		 * Load more
		 * ------------------------------------------------------------------ */

		items.forEach(function (item, index) {
			if (index >= visible) {
				item.hidden = true;
			}
		});

		function syncMore() {
			var remaining = total - visible;

			if (moreBtn) {
				moreBtn.hidden = remaining <= 0;
				if (remaining > 0) {
					moreBtn.textContent = 'Load More (' + Math.min(batch, remaining) + ')';
				}
			}

			if (status) {
				if (remaining > 0) {
					status.textContent = 'Showing ' + visible + ' of ' + total + ' screenshots';
				} else {
					status.textContent = 'All ' + total + ' screenshots loaded';
				}
			}
		}

		function revealNext() {
			var next = Math.min(visible + batch, total);
			var firstNew = null;

			for (var i = visible; i < next; i++) {
				items[i].hidden = false;
				items[i].classList.add('is-revealed');
				if (!firstNew) {
					firstNew = items[i];
				}
			}

			visible = next;
			syncMore();

			if (firstNew) {
				var rect = firstNew.getBoundingClientRect();
				if (rect.top < 0 || rect.top > window.innerHeight * 0.7) {
					firstNew.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
				// Keep focus on the button while more batches remain; move it to the
				// first new screenshot once the list is fully expanded.
				if (moreBtn && moreBtn.hidden) {
					firstNew.focus({ preventScroll: true });
				}
			}
		}

		if (moreBtn) {
			moreBtn.addEventListener('click', revealNext);
		}

		syncMore();

		/* ------------------------------------------------------------------
		 * Lightbox
		 * ------------------------------------------------------------------ */

		var image = lightbox.querySelector('[data-abp-lightbox-image]');
		var caption = lightbox.querySelector('[data-abp-lightbox-caption]');
		var counter = lightbox.querySelector('[data-abp-lightbox-counter]');
		var closeBtn = lightbox.querySelector('.abp-lightbox-close');
		var prevBtn = lightbox.querySelector('[data-abp-lightbox-prev]');
		var nextBtn = lightbox.querySelector('[data-abp-lightbox-next]');
		var panel = lightbox.querySelector('.abp-lightbox-panel');

		var current = 0;
		var lastFocused = null;
		var touchStartX = 0;
		var touchStartY = 0;
		var touchTracking = false;

		function itemData(index) {
			var item = items[index];
			return {
				src: item.getAttribute('data-src'),
				caption: item.getAttribute('data-caption') || '',
				alt: (item.querySelector('img') || {}).alt || ''
			};
		}

		function show(index) {
			current = (index + total) % total;

			var data = itemData(current);
			var isSingle = total < 2;

			image.classList.add('is-loading');
			image.onload = function () {
				image.classList.remove('is-loading');
			};
			image.src = data.src;
			image.alt = data.alt;

			caption.textContent = data.caption;
			counter.textContent = (current + 1) + ' / ' + total;

			prevBtn.hidden = isSingle;
			nextBtn.hidden = isSingle;

			preload(itemData((current + 1) % total).src);
			preload(itemData((current - 1 + total) % total).src);
		}

		function open(index) {
			lastFocused = document.activeElement;
			show(index);
			lightbox.hidden = false;
			lightbox.setAttribute('aria-hidden', 'false');
			document.body.classList.add(BODY_CLASS);
			closeBtn.focus();
		}

		function close() {
			lightbox.hidden = true;
			lightbox.setAttribute('aria-hidden', 'true');
			image.removeAttribute('src');
			document.body.classList.remove(BODY_CLASS);
			if (lastFocused && typeof lastFocused.focus === 'function') {
				lastFocused.focus();
			}
		}

		items.forEach(function (item, index) {
			item.addEventListener('click', function () {
				open(index);
			});
		});

		lightbox.querySelectorAll('[data-abp-lightbox-close]').forEach(function (el) {
			el.addEventListener('click', close);
		});

		prevBtn.addEventListener('click', function () {
			show(current - 1);
		});

		nextBtn.addEventListener('click', function () {
			show(current + 1);
		});

		document.addEventListener('keydown', function (event) {
			if (lightbox.hidden) {
				return;
			}

			if (event.key === 'Escape') {
				event.preventDefault();
				close();
				return;
			}

			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				show(current - 1);
				return;
			}

			if (event.key === 'ArrowRight') {
				event.preventDefault();
				show(current + 1);
				return;
			}

			if (event.key === 'Tab') {
				var focusable = toArray(
					lightbox.querySelectorAll('button:not([hidden])')
				).filter(function (el) {
					return el.offsetParent !== null;
				});
				if (!focusable.length) {
					return;
				}
				var first = focusable[0];
				var last = focusable[focusable.length - 1];
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});

		panel.addEventListener('touchstart', function (event) {
			if (event.touches.length !== 1) {
				return;
			}
			touchTracking = true;
			touchStartX = event.touches[0].clientX;
			touchStartY = event.touches[0].clientY;
		}, { passive: true });

		panel.addEventListener('touchend', function (event) {
			if (!touchTracking || event.changedTouches.length !== 1) {
				touchTracking = false;
				return;
			}
			touchTracking = false;

			var deltaX = event.changedTouches[0].clientX - touchStartX;
			var deltaY = event.changedTouches[0].clientY - touchStartY;

			if (Math.abs(deltaX) < 45 || Math.abs(deltaY) > 70) {
				return;
			}

			show(current + (deltaX < 0 ? 1 : -1));
		}, { passive: true });
	}

	document.querySelectorAll('[data-abp-gallery]').forEach(initGallery);
})();
