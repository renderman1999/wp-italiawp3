/**
 * Catalogo dataset home: tutti i filtri via AJAX, loader sui risultati.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
(function () {
	'use strict';

	var form = document.getElementById('opendata-catalog-form');
	var results = document.getElementById('opendata-catalog-results');
	var inner = results ? results.querySelector('.opendata-catalog-results-inner') : null;

	if (!form || !results || !inner || typeof italiawp2Catalog === 'undefined') {
		return;
	}

	var searchInput = document.getElementById('opendata_s');
	var groupSel = document.getElementById('opendata_group');
	var orgSel = document.getElementById('opendata_org');
	var orderSel = document.getElementById('opendata_order');

	var debounceMs =
		typeof italiawp2Catalog.searchDebounce === 'number'
			? italiawp2Catalog.searchDebounce
			: 450;
	var searchTimer = null;

	function debounceSearch() {
		if (searchTimer) {
			clearTimeout(searchTimer);
		}
		searchTimer = window.setTimeout(function () {
			searchTimer = null;
			fetchCatalog();
		}, debounceMs);
	}

	function setLoading(on) {
		if (on) {
			results.setAttribute('aria-busy', 'true');
			results.classList.add('is-loading');
		} else {
			results.removeAttribute('aria-busy');
			results.classList.remove('is-loading');
		}
	}

	function fetchCatalog() {
		setLoading(true);
		var fd = new FormData(form);
		fd.set('opendata_paged', '1');
		fd.append('action', 'italiawp2_catalog_datasets');
		fd.append('nonce', italiawp2Catalog.nonce);

		fetch(italiawp2Catalog.ajaxurl, {
			method: 'POST',
			body: fd,
			credentials: 'same-origin',
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				setLoading(false);
				if (data.success && data.data && data.data.html !== undefined) {
					inner.innerHTML = data.data.html;
				}
			})
			.catch(function () {
				setLoading(false);
			});
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		if (searchTimer) {
			clearTimeout(searchTimer);
			searchTimer = null;
		}
		fetchCatalog();
	});

	if (searchInput) {
		searchInput.addEventListener('input', debounceSearch);
	}
	if (groupSel) {
		groupSel.addEventListener('change', fetchCatalog);
	}
	if (orgSel) {
		orgSel.addEventListener('change', fetchCatalog);
	}
	if (orderSel) {
		orderSel.addEventListener('change', fetchCatalog);
	}

	form.addEventListener('change', function (e) {
		var t = e.target;
		if (!t || !t.classList) {
			return;
		}
		if (t.classList.contains('opendata-catalog-format-filter') || t.classList.contains('opendata-catalog-tag-filter')) {
			fetchCatalog();
		}
	});
})();
