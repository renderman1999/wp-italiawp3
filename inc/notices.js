(function ($) {
	'use strict';

	var cfg = window.italiawp3SiteNotices || {};
	var storageKey = cfg.storageKey || 'italiawp3_site_notices';
	var now = typeof cfg.now === 'number' ? cfg.now : Math.floor(Date.now() / 1000);
	var cookieMaxAge = 31536000;

	function readStoreFromCookie() {
		var pattern = '(?:^|; )' + encodeURIComponent(storageKey).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)';
		var match = document.cookie.match(new RegExp(pattern));
		if (!match || !match[1]) {
			return null;
		}
		try {
			return JSON.parse(decodeURIComponent(match[1]));
		} catch (e) {
			return null;
		}
	}

	function readStore() {
		var store = null;

		try {
			var raw = window.localStorage.getItem(storageKey);
			if (raw) {
				store = JSON.parse(raw);
			}
		} catch (e) {
			store = null;
		}

		if (!store || typeof store !== 'object') {
			store = readStoreFromCookie();
		}

		return store && typeof store === 'object' ? store : {};
	}

	function writeStore(store) {
		var json = JSON.stringify(store);

		try {
			window.localStorage.setItem(storageKey, json);
		} catch (e) {
			// localStorage non disponibile.
		}

		document.cookie = storageKey + '=' + encodeURIComponent(json) + '; path=/; max-age=' + cookieMaxAge + '; SameSite=Lax';
	}

	function noticeKey(id, version) {
		return String(id) + ':' + String(version);
	}

	function findNoticeData(id) {
		var notices = cfg.notices || [];
		var i;

		for (i = 0; i < notices.length; i++) {
			if (String(notices[i].id) === String(id)) {
				return notices[i];
			}
		}

		return null;
	}

	function persistDismiss($notice) {
		if (!$notice || !$notice.length) {
			return;
		}

		var id = $notice.attr('data-notice-id');
		var version = $notice.attr('data-notice-version') || '';
		var key = noticeKey(id, version);
		var store = readStore();
		var entry = store[key] || {};

		entry.dismissed = true;
		entry.dismissedAt = now;
		if (!entry.firstSeen) {
			entry.firstSeen = now;
		}
		store[key] = entry;
		writeStore(store);
	}

	function trackFirstSeen($el, store) {
		var id = $el.attr('data-notice-id');
		var version = $el.attr('data-notice-version') || '';
		var key = noticeKey(id, version);

		if (store[key] && store[key].dismissed) {
			return store;
		}

		if (!store[key]) {
			store[key] = { firstSeen: now, dismissed: false };
			writeStore(store);
			return store;
		}

		if (!store[key].firstSeen) {
			store[key].firstSeen = now;
			writeStore(store);
		}

		return store;
	}

	function removeNoticeFromDom($notice) {
		$notice.remove();
		if (!$('.italiawp-site-notice').length) {
			$('#italiawp-site-notices').remove();
		}
	}

	function dismissAndRemoveNotice($notice) {
		if (!$notice.length) {
			return;
		}

		persistDismiss($notice);

		if (typeof $notice.alert === 'function') {
			$notice.alert('close');
			return;
		}

		removeNoticeFromDom($notice);
	}

	function openNoticeModal(noticeId) {
		var data = findNoticeData(noticeId);
		var $modal = $('#italiawp3-notice-modal');

		if (!data || !$modal.length) {
			return;
		}

		$modal.find('#italiawp3-notice-modal-title').text(data.title || '');
		$modal.find('.italiawp3-notice-modal__body').html(data.content || '');
		$modal.attr('data-active-notice-id', noticeId);
		$modal.modal('show');
	}

	$(function () {
		var $wrap = $('#italiawp-site-notices');
		var $modal = $('#italiawp3-notice-modal');
		var store;

		if (!$wrap.length) {
			return;
		}

		store = readStore();
		$('.italiawp-site-notice').each(function () {
			store = trackFirstSeen($(this), store);
		});

		$(document).on('click', '.italiawp-site-notice__close', function () {
			persistDismiss($(this).closest('.italiawp-site-notice'));
		});

		$(document).on('close.bs.alert', '.italiawp-site-notice', function () {
			persistDismiss($(this));
		});

		$(document).on('click', '.italiawp-site-notice__read', function (event) {
			event.preventDefault();
			var noticeId = $(this).closest('.italiawp-site-notice').attr('data-notice-id');
			openNoticeModal(noticeId);
		});

		$(document).on('click', '.italiawp3-notice-modal__done', function (event) {
			event.preventDefault();
			var noticeId = $modal.attr('data-active-notice-id');
			var $notice = $('.italiawp-site-notice[data-notice-id="' + noticeId + '"]');

			$modal.modal('hide');
			dismissAndRemoveNotice($notice);
		});
	});
}(jQuery));
