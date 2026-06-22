/**
 * Footer credits modal (Bootstrap Italia 4 / jQuery).
 */
(function ($) {
	'use strict';

	$(function () {
		var $modal = $('#italiawp3-footer-credits-modal');
		if (!$modal.length) {
			return;
		}

		$(document).on('click', '.footer-credits-trigger', function (event) {
			event.preventDefault();
			$modal.modal('show');
		});
	});
})(jQuery);
