/*------------------------------------------------------------*
 * Individual Validation Functions
 *------------------------------------------------------------*/
 
function verifyTitle( $, $title ) {
	"use strict";
	
	var bIsValid = false;
	
	// Check the validity based on the length of the title
	bIsValid = 0 < $.trim( $title.val() ).length;
	
	// Show an error if isn't visible
	if( ! bIsValid && $('#title-error').is(':not(:visible)') ) {
		$('#title-error').toggle();
	} else if( bIsValid && $('#title-error').is(':visible') ) {
		$('#title-error').toggle();
	} // end if
	
	return bIsValid;
	
} // end verifyTitle

function verifyDescription( $, $desc ) {
	"use strict";
	
	var bIsValid = false;
	
	// Check the validity based on the length of the description
	bIsValid = 0 < $desc.val().length;
	
	// Show an error if isn't visible
	if( ! bIsValid && $('#desc-error').is(':not(:visible)') ) {
		$('#desc-error').toggle();
	} else if( bIsValid && $('#desc-error').is(':visible') ) {
		$('#desc-error').toggle();
	} // end if
	
	return bIsValid;	
	
} // end verifyDescription

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

function verifyDate( $, $date ) {
	"use strict";
	
	var bIsValid = false;
	
	// Check the validity based on the length of the description
	bIsValid = 0 < $.trim( $date.val() ).length;
	
	// Show an error if isn't visible
	if( ! bIsValid && $('#date-error').is(':not(:visible)') ) {
		$('#date-error').toggle();
	} else if( bIsValid && $('#date-error').is(':visible') ) {
		$('#date-error').toggle();
	} // end if
	
	return bIsValid;
		
} // end verifyDate

/*------------------------------------------------------------*
 * Project Validation
 *------------------------------------------------------------*/
 
function verifyProject( $ ) {
	"use strict";
	
	return	verifyTitle( $, $('#project-title' ) ) &&
			verifyDescription( $, $('#project-description') ) &&
			verifyName( $, $('#project-owner-name' ) ) &&
			verifyEmailAddress( $, $('#project-owner' ) ) &&
			verifyPhoneNumber( $, $('#project-owner-phone' ) ) &&
			verifyDate( $, $('#project-date' ) );
	
} // end verifyProject

/*------------------------------------------------------------*
 * DOM Ready
 *------------------------------------------------------------*/

(function ( $ ) {
	"use strict";
	$(function () {

		// Enable Redactor on the textarea
		$('#project-description').redactor({
			buttons:	[ 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link' ]
		});
		
		// Enable the datepicker on the datepicker field
		$('#project-date').datepicker({ 
			minDate: 1,
			altField  : '.alt-datepicker',
			altFormat : 'yy-mm-dd'
		});
		
		// If the 'Submit' button is clicked...
		$('#submit-project').click(function(evt) {
			
			evt.preventDefault();
			
			// Verify the project
			if( verifyProject($) ) {
			
				// ..hide all of the error alerts that are visible.
				$('.alert').each(function() {
					if( $(this).is(':visible') ) {
						$(this).hide();
					} // end if
				});
				
				// ...submit the project
				$("#community-jar-submit-form").submit();
				
			} else {
			
				// Scroll the window back to the top
				$(window).scrollTop( 0, 0 );
				
			} // end if
						
		});
		
		// Clear the form if the user opts to reset it
		$('#cancel-project').click(function(evt) {
			$("#community-jar-submit-form").reset();
		});
		
	});
}(jQuery));