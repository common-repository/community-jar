(function ($) {
	"use strict";
	$(function () {

		// Check to see if the Ajax Notification is visible
		if ($('#dismiss-ajax-notification').length > 0) {

			// If so, we need to setup an event handler to trigger it's dismissal
			$('#dismiss-ajax-notification').click(function (evt) {

				evt.preventDefault();

				// Initiate a request to the server-side
				$.post(ajaxurl, {

					// The name of the function to fire on the server
					action: 'hide_admin_notification',

					// The nonce value to send for the security check
					nonce: $.trim($('#ajax-notification-nonce').text())

				}, function (response) {

					// If the response was successful (that is, 1 was returned), hide the notification;
					// Otherwise, we'll change the class name of the notification
					if ('1' === response) {
						$('#ajax-notification').fadeOut('slow');
					} else {

						$('#ajax-notification')
							.removeClass('updated')
							.addClass('error');

					} // end if

				});

			});

		} // end if

	});
}(jQuery));