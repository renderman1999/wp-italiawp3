/**
 * Catalogo dataset home: filtri AJAX, skeleton, badge filtri attivi.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
(function () {
	'use strict';

	var form = document.getElementById('opendata-catalog-form');
	var results = document.getElementById('opendata-catalog-results');
	var inner = results ? results.querySelector('.opendata-catalog-results-inner') : null;
	var activeFiltersEl = document.getElementById('opendata-catalog-active-filters');

	if (!form || !results || !inner || !activeFiltersEl || typeof italiawp2Catalog === 'undefined') {
		return;
	}

	var searchInput = document.getElementById('opendata_s');
	var groupSel = document.getElementById('opendata_group');
	var orgSel = document.getElementById('opendata_org');
	var orderSel = document.getElementById('opendata_order');
	var skeletonHtml = italiawp2Catalog.skeletonHtml || '';
	var labels = italiawp2Catalog.labels || {};
	var orderLabels = italiawp2Catalog.orderLabels || {};
	var lastHtml = inner.innerHTML;

	var debounceMs =
		typeof italiawp2Catalog.searchDebounce === 'number'
			? italiawp2Catalog.searchDebounce
			: 450;
	var searchTimer = null;

	function stripCountSuffix(text) {
		return String(text || '').replace(/\s*\(\d+\)\s*$/, '').trim();
	}

	function getSelectLabel(select) {
		if (!select || !select.value) {
			return '';
		}
		var option = select.options[select.selectedIndex];
		return option ? stripCountSuffix(option.text) : select.value;
	}

	function getCheckboxLabel(checkbox) {
		if (!checkbox || !checkbox.id) {
			return checkbox ? checkbox.value : '';
		}
		var label = form.querySelector('label[for="' + checkbox.id + '"]');
		return label ? stripCountSuffix(label.textContent) : checkbox.value;
	}

	function buildActiveFilterBadges() {
		var badges = [];

		if (searchInput && searchInput.value.trim() !== '') {
			badges.push({
				type: 'search',
				value: '',
				text: (labels.search || 'Cerca') + ': ' + searchInput.value.trim(),
			});
		}

		if (orderSel && orderSel.value !== '') {
			badges.push({
				type: 'order',
				value: '',
				text: (labels.order || 'Ordina') + ': ' + (orderLabels[orderSel.value] || getSelectLabel(orderSel)),
			});
		}

		if (orgSel && orgSel.value !== '') {
			badges.push({
				type: 'org',
				value: '',
				text: (labels.org || 'Organizzazione') + ': ' + getSelectLabel(orgSel),
			});
		}

		if (groupSel && groupSel.value !== '') {
			badges.push({
				type: 'group',
				value: '',
				text: (labels.group || 'Tema') + ': ' + getSelectLabel(groupSel),
			});
		}

		form.querySelectorAll('.opendata-catalog-format-filter:checked').forEach(function (checkbox) {
			badges.push({
				type: 'format',
				value: checkbox.value,
				text: (labels.format || 'Formato') + ': ' + String(checkbox.value).toUpperCase(),
			});
		});

		form.querySelectorAll('.opendata-catalog-tag-filter:checked').forEach(function (checkbox) {
			badges.push({
				type: 'tag',
				value: checkbox.value,
				text: (labels.tag || 'Tag') + ': ' + getCheckboxLabel(checkbox),
			});
		});

		return badges;
	}

	function renderActiveFilters() {
		var badges = buildActiveFilterBadges();
		activeFiltersEl.innerHTML = '';

		if (!badges.length) {
			activeFiltersEl.hidden = true;
			return;
		}

		activeFiltersEl.hidden = false;

		badges.forEach(function (badge) {
			var item = document.createElement('span');
			item.className = 'opendata-catalog-filter-badge';
			item.dataset.filterType = badge.type;
			item.dataset.filterValue = badge.value;

			var text = document.createElement('span');
			text.className = 'opendata-catalog-filter-badge__label';
			text.textContent = badge.text;

			var removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'opendata-catalog-filter-badge__remove';
			removeBtn.setAttribute('aria-label', (labels.removeFilter || 'Rimuovi filtro') + ': ' + badge.text);
			removeBtn.innerHTML = '&times;';

			item.appendChild(text);
			item.appendChild(removeBtn);
			activeFiltersEl.appendChild(item);
		});
	}

	function clearFilter(type, value) {
		switch (type) {
			case 'search':
				if (searchInput) {
					searchInput.value = '';
				}
				break;
			case 'order':
				if (orderSel) {
					orderSel.value = '';
				}
				break;
			case 'org':
				if (orgSel) {
					orgSel.value = '';
				}
				break;
			case 'group':
				if (groupSel) {
					groupSel.value = '';
				}
				break;
			case 'format':
				form.querySelectorAll('.opendata-catalog-format-filter').forEach(function (checkbox) {
					if (checkbox.value === value) {
						checkbox.checked = false;
					}
				});
				break;
			case 'tag':
				form.querySelectorAll('.opendata-catalog-tag-filter').forEach(function (checkbox) {
					if (checkbox.value === value) {
						checkbox.checked = false;
					}
				});
				break;
			default:
				break;
		}
	}

	function debounceSearch() {
		renderActiveFilters();
		if (searchTimer) {
			clearTimeout(searchTimer);
		}
		searchTimer = window.setTimeout(function () {
			searchTimer = null;
			fetchCatalog(false);
		}, debounceMs);
	}

	function setLoading(on) {
		if (on) {
			lastHtml = inner.innerHTML;
			if (skeletonHtml) {
				inner.innerHTML = skeletonHtml;
			}
			results.setAttribute('aria-busy', 'true');
			results.classList.add('is-loading');
		} else {
			results.removeAttribute('aria-busy');
			results.classList.remove('is-loading');
		}
	}

	function fetchCatalog(updateBadges) {
		if (updateBadges !== false) {
			renderActiveFilters();
		}
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
					lastHtml = data.data.html;
				} else {
					inner.innerHTML = lastHtml;
				}
				renderActiveFilters();
			})
			.catch(function () {
				setLoading(false);
				inner.innerHTML = lastHtml;
				renderActiveFilters();
			});
	}

	activeFiltersEl.addEventListener('click', function (event) {
		var removeBtn = event.target.closest('.opendata-catalog-filter-badge__remove');
		if (!removeBtn) {
			return;
		}
		event.preventDefault();

		var badge = removeBtn.closest('.opendata-catalog-filter-badge');
		if (!badge) {
			return;
		}

		if (searchTimer) {
			clearTimeout(searchTimer);
			searchTimer = null;
		}

		clearFilter(badge.dataset.filterType, badge.dataset.filterValue || '');
		fetchCatalog();
	});

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
		groupSel.addEventListener('change', function () {
			fetchCatalog();
		});
	}
	if (orgSel) {
		orgSel.addEventListener('change', function () {
			fetchCatalog();
		});
	}
	if (orderSel) {
		orderSel.addEventListener('change', function () {
			fetchCatalog();
		});
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

	renderActiveFilters();
})();
