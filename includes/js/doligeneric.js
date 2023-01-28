( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

		var form = document.getElementById('loginmodal-form');
		form.addEventListener('submit', function(event) { 
		jQuery("#DoliconnectLoadingModal").modal("show");
		jQuery(window).scrollTop(0);
		form.submit();
		});

	});
})( jQuery );
