/**
 * Slider Dataset in evidenza (home): caricamento AJAX + Swiper.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
(function () {
	'use strict';

	var cfg = window.italiawp2DatasetSlider;
	var section = document.getElementById('dataset-slider');
	var wrapper = document.getElementById('dataset-swiper-wrapper');
	var swiperEl = document.querySelector('.dataset-swiper');
	var swiperInstance = null;
	var SLIDES_DESKTOP = 6;

	if (!cfg || !section || !wrapper || !swiperEl) {
		return;
	}

	function hideSection() {
		if (swiperInstance) {
			swiperInstance.destroy(true, true);
			swiperInstance = null;
		}
		section.style.display = 'none';
		section.setAttribute('aria-hidden', 'true');
	}

	function initSwiper(count) {
		if (typeof Swiper === 'undefined') {
			return;
		}
		if (swiperInstance) {
			swiperInstance.destroy(true, true);
			swiperInstance = null;
		}
		swiperInstance = new Swiper('.dataset-swiper', {
			slidesPerView: 2,
			spaceBetween: 12,
			loop: count > SLIDES_DESKTOP,
			watchOverflow: true,
			pagination: {
				el: '.dataset-swiper .swiper-pagination',
				clickable: true,
			},
			navigation: {
				nextEl: '.dataset-swiper .swiper-button-next',
				prevEl: '.dataset-swiper .swiper-button-prev',
			},
			breakpoints: {
				576: { slidesPerView: 3, spaceBetween: 12 },
				768: { slidesPerView: 4, spaceBetween: 14 },
				992: { slidesPerView: 5, spaceBetween: 14 },
				1200: { slidesPerView: SLIDES_DESKTOP, spaceBetween: 16 },
			},
		});
	}

	function markLoaded() {
		section.classList.remove('is-loading');
		section.removeAttribute('data-loading');
		swiperEl.setAttribute('aria-busy', 'false');
	}

	function loadSlides() {
		initSwiper(SLIDES_DESKTOP);

		var body = new FormData();
		body.append('action', 'italiawp2_featured_datasets');
		body.append('nonce', cfg.nonce);

		fetch(cfg.ajaxurl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin',
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload || !payload.success || !payload.data) {
					hideSection();
					return;
				}

				var count = parseInt(payload.data.count, 10) || 0;
				var html = payload.data.html || '';

				if (count < 1 || html === '') {
					hideSection();
					return;
				}

				wrapper.innerHTML = html;
				markLoaded();
				initSwiper(count);
			})
			.catch(function () {
				hideSection();
			});
	}

	document.addEventListener('DOMContentLoaded', loadSlides);
})();
