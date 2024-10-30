(function ( $ ) {
	"use strict";
	
	$(function () {
		
		var $tr, $th, $label, $td, $input;
		
		// First, create the table row that will contain the new input field
		$tr = $('<tr />').addClass('form-field');
			
		// Next, create the header for the label
		$th = $('<th />').attr('scope', 'row');
				
		// Create the label for the table header
		$label = $('<label />')
					.attr('for', 'role')
					.text('Phone Number');
					
		// Append the label to the table header
		$th.append( $label );
		
		// Now create the tabel cell for the input
		$td = $('<td />');
		
		// Create the input field for the phone number
		$input = $('<input />')
					.attr('type', 'text')
					.attr('name', 'phone-number')
					.attr('id', 'phone-number');
					
		// Add the input to the table cell
		$td.append( $input );
		
		// Now append the ahole thing to the table row
		$tr
			.append( $th )
			.append( $td );
			
		// Finally, insert this after the Website
		$tr.insertAfter( $('#url' ).parents('.form-field') );
		
	});
}(jQuery));