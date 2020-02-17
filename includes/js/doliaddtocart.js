( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

		$( '#product-form' ).on( 'submit', function( event ) {
    

			event.preventDefault();

			var data = $(this).serialize();

			$( '.product-errors' ).remove();
			$( '.product-success' ).remove();

			$.ajax({
				url: gdrf_localize.gdrf_ajax_url,
				type: 'post',
				data: data,
				success: function( response ) {
					if ( 'success' !== response.data ) {
						$( '#product-form' ).append( '<div class="product-errors" style="display:none;">test:<br />' + response.data + '</div>' );
						$( '.product-errors' ).slideDown();
					} else {
						$( '#product-form' ).append( '<div class="product-success" style="display:none;">test:<br />' + response.data + '</div>' );
						$( '.product-success' ).slideDown();
					}             
				}      
			});
		});
	});
})( jQuery );
