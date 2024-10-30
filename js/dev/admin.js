(function ( $ ) {
	"use strict";
	
	$(function () {
		
		var $phone, $p, $message, $profile, $date, $completed, $emailSubject, $addNew;
		$phone = $('#phone-number');
		$date = $('#datepicker');
		$completed = $('#project_is_completed');
		$emailSubject = $('#email_subject');
		$addNew = $('.add-new-h2');
		
		// First, setup a place holder so that user's are guided on how phone numbers should look
		if( 0 < $phone.length ) {
		
			$phone.attr( 'placeholder', '404-123-1234' );	
			$profile = $('#your-profile');
		
			// Next, validate that they can save 
			$('#submit, #createusersub').click(function(evt) {
							
				if( 0 < $phone.val().length && ! /^(\d|,|-)+$/.test( $phone.val() ) ) {
					
						
					evt.preventDefault();
					
					// Create the message
					$p = $('<p />')
						.attr('id', 'notice')
						.append("You've entered an invalid phone number.");
					
					// Create a notice to display at the top of the page
					$message = $( '<div />' )
						.attr('id', 'message')
						.attr('class', 'error below-h2')
						.append( $p );
						
					// Check to see if the message already exists.
					if( 'message' !== $profile.prev() ) {
						
						// First, check to see if we're on the 'your Profile' page or the 'Add New User'
						$profile = 0 < $('#createuser').length ? $('#createuser') : $('#your-profile');
						
						// Now add the message
						$message.insertBefore( $profile );
						
					} // end if
					
					// Scroll the window to the top for the user to see.
					window.scrollTo(0, 0);
					
				} // end if/else
				
			});
		
		} // end if
		
		// Setup notifications for the checkbox
		$completed.click(function() {

			var $desc = $(this)
						.parent()
						.next();
			
			if( $(this).is( ':checked' ) ) {
				$desc.show();
			} else {
				$desc.hide();
			} // end if/else
			
		});
		
		// Setup the date picker
		if( 0 < $date.length ) {
		
			$date
				.datepicker({
					minDate:	1
				})
				.keydown(function(evt) {
					evt.preventDefault();
				});
			
		} // end if
		
		// Move the email subject line directory below the title
		if( 0 < $emailSubject.length ) {
			
			// Move the email subject
			$emailSubject
				.insertAfter( $('#titlewrap') )
				.css( 'margin-top', '20px' );
				
			// Disable the title field
			$('#title')
				.css( 'background', 'rgb(250, 250, 250)' )
				.attr( 'disabled', 'disabled' );
			
			// Place the focus in the subject field
			$('input#email_subject').focus();
			
		} // end if

		// Remove the 'Add New' buttons from the Project and the Email Template dashboard pages		
		if( 0 < $addNew.length ) {
		
			// Let's hide the 'Add New' anchor at the top of the page
			if( 'post-new.php?post_type=cj_project' === $addNew.attr('href') || 'post-new.php?post_type=cj_email' === $addNew.attr('href') ) {
				$addNew.hide();
			} // end if
		
		} // end if 
		
	});
}(jQuery));