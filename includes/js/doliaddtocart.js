( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

		$( '#gdrf-form' ).on( 'submit', function( event ) {

			event.preventDefault();

			var data = $( this ).serialize();

			$( '.gdrf-errors' ).remove();
			$( '.gdrf-success' ).remove();

			$.ajax({
				url: gdrf_localize.gdrf_ajax_url,
				type: 'post',
				data: data,
				success: function( response ) {
					if ( 'success' !== response.data ) {
						$( '#gdrf-form' ).append( '<div class="gdrf-errors" style="display:none;">' + gdrf_localize.gdrf_errors + '<br />' + response.data + '</div>' );
						$( '.gdrf-errors' ).slideDown();
					} else {
						$( '#gdrf-form' ).append( '<div class="gdrf-success" style="display:none;">' + gdrf_localize.gdrf_success + '</div>' );
						$( '.gdrf-success' ).slideDown();
					}
				}
			});
		});
	});
})( jQuery );
