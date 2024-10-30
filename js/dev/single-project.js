/*------------------------------------------------------------*
 * Individual Validation Functions
 *------------------------------------------------------------*/
 
function verifyName( $, $name ) {
	"use strict";
	
	var bIsValid = false;
	
	// Check the validity based on the length of the name
	bIsValid = 0 < $.trim( $name.val() ).length;
	
	// Show an error if isn't visible
	if( ! bIsValid && $('#name-error').is(':not(:visible)') ) {
		$('#name-error').toggle();
	} else if( bIsValid && $('#name-error').is(':visible') ) {
		$('#name-error').toggle();
	} // end if
	
	return bIsValid;	
	
} // end verifyName 
 
function verifyEmailAddress( $, $owner ) {
	"use strict";
	
	var bIsValid, oPattern;
	bIsValid = false;
	
	// First, test the email address
	oPattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    bIsValid = oPattern.test( $owner.val() );
	
	// Next, show error messages as needed
	if( ! bIsValid && $('#email-error').is(':not(:visible)') ) {
		$('#email-error').toggle();
	} else if( bIsValid && $('#email-error').is(':visible') ) {
		$('#email-error').toggle();
	} // end if
		
	return bIsValid;
	
} // end verifyEmailAddress

function verifyPhoneNumber( $, $number ) {
	"use strict";
	
	var bIsValid, oPattern;
	bIsValid = false;
	
	oPattern = new RegExp(/(\d{1})?(-|(\.)|\s)*(-|(\.)|\s)*(\()?\d{3}(\))?(-|(\.)|\s)*\d{3}(-|(\.)|\s)*\d{4}/);
	bIsValid = oPattern.test( $number.val() );
	
	// Show an error if isn't visible
	if( ! bIsValid && $('#number-error').is(':not(:visible)') ) {
		$('#number-error').toggle();
	} else if( bIsValid && $('#number-error').is(':visible') ) {
		$('#number-error').toggle();
	} // end if
	
	return bIsValid;	
	
} // end verifyPhoneNumber

function validateVolunteer( $ ) {
	"use strict";
	
	return	verifyName( $, $('#volunteer-name' ) ) &&
			verifyEmailAddress( $, $('#volunteer-email' ) ) &&
			verifyPhoneNumber( $, $('#volunteer-phone') );
	
} // end validateInput

/*------------------------------------------------------------*
 * DOM Ready
 *------------------------------------------------------------*/

(function ( $ ) {
	"use strict";
	
	$(function () {
		
		var iCommentLength;
		
		// Countdown the number of characters on the comment form
		$('#volunteer-comments').keyup(function() {
			
			iCommentLength = $(this).val().length;
			$('#count').text( 300 - parseInt( iCommentLength, 10 ) );
			
		});
		
		// Validate and submit the form
		$('#submit-volunteer').click(function(evt) {
			
			evt.preventDefault();
			
			if( validateVolunteer( $ ) ) {
				$('#volunteer-sign-up')[0].submit();
			} // end if
			
		});
		
		// Reset the form when 'Cancel' is clicked
		$('#cancel-volunteer').click(function(evt) {
			$('#volunteer-sign-up')[0].reset();
		});
		
		// If the 'success' message exists, then scroll to the bottom of the page
		if( 2 === document.location.href.split('?').length ) {
		
			$("html, body").animate({ 
				scrollTop: $(document).height() - $('#sign-up').height() - $('#sidebar1').height() 
			});
			
		} // end if
		
	});
	
}(jQuery));