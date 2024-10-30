function disableButton( $button ) {
	"use strict";
	
	$button.css({
		visibility: 'hidden'
	});
	
} // end disableButton

function enableButton( $button ) {
	"use strict";
	
	$button.css({
		visibility: 'visible'
	});
	
} // end enableButton

function updateButtons( $ ) {
	"use strict";
	
	if( 0 === $('#pending_volunteers').children().length ) {
		disableButton( $('#add-volunteers') );
	} else {
		enableButton( $('#add-volunteers') );
	} // end if
	
	if( 0 === $('#active_volunteers').children().length ) {
		disableButton( $('#remove-volunteers') );
	} else {
		enableButton( $('#remove-volunteers') );
	} // end if
	
} // end updateButtons

function allVolunteers( $ ) {
	"use strict";
	
	var $volunteers, $addButton;
	$addButton = $('#add-volunteers');
	
	// If there are no volunteers, disable the option
	if( 0 === $('#pending_volunteers').children().length ) {
		disableButton( $addButton );
	} // end if
	
	/* When the user clicks on the 'All Volunteers' button,
	 * the selected volunteer(s) should be removed from the
	 * 'All Volunteers' select box into the 'Project Volunteers'
	 * select box.
	 */
	$addButton.click(function(evt) {
		
		evt.preventDefault();
		
		// Grab the selected option elements and remove them from their current select element
		$volunteers = $('#pending_volunteers').children(':selected');
		
		// Then move them to the list of active volunteers
		$('#active_volunteers').append( $volunteers );
	
		// Display the save post reminder
		$('.approved-notification').show();
	
		updateButtons( $ );	
	
	});
	
} // end allVolunteers

function activeVolunteers( $ ) {
	"use strict";
	
	var $volunteers, $removeButton;
	$removeButton = $('#remove-volunteers');
	
	// If there are no volunteers, disable the option
	if( 0 === $('#active_volunteers').children().length ) {
		disableButton( $removeButton );
	} // end if
	
	/* When the user clicks on the 'All Volunteers' button,
	 * the selected volunteer(s) should be removed from the
	 * 'All Volunteers' select box into the 'Project Volunteers'
	 * select box.
	 */
	$removeButton.click(function(evt) {
		
		evt.preventDefault();
		
		// Grab the selected option elements and remove them from their current select element
		$volunteers = $('#active_volunteers').children(':selected');
		
		// Then move them to the list of all volunteers
		$('#pending_volunteers').append( $volunteers );
		
		updateButtons( $ );
		
	});
	
} // end activeVolunteers

(function ( $ ) {
	"use strict";
	
	$(function() {

		allVolunteers( $ );
		activeVolunteers( $ );

	});
}(jQuery));