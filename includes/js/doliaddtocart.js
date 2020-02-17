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
						$( '#gdrf-form' ).append( '<div class="product-errors" style="display:none;">' + gdrf_errors + '<br />' + response.data + '</div>' );
						$( '.gdrf-errors' ).slideDown();
					} else {
						$( '#gdrf-form' ).append( '<div class="product-success" style="display:none;">' + gdrf_success + '<br />' + response.data + '</div>' );
						$( '.gdrf-success' ).slideDown();
					}                  
				}
			});
		});
	});
})( jQuery );
