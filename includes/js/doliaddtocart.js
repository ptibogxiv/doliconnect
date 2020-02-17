( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

		$( '#product-form' ).on( 'submit', function( event ) {
    

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
						$( '#product-form' ).append( '<div class="gdrf-errors" style="display:none;">test:<br />' + response.data + '</div>' );
						$( '.gdrf-errors' ).slideDown();
					} else {
						$( '#product-form' ).append( '<div class="gdrf-success" style="display:none;">test:<br />' + response.data + '</div>' );
						$( '.gdrf-success' ).slideDown();
					}             
				}      
			});
		});
	});
})( jQuery );
