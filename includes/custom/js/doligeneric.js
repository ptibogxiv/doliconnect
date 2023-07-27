( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

		var form = document.getElementById('loginmodal-form');
		form.addEventListener('submit', function(e) { 
			e.preventDefault();
			e.stopPropagation();  
			jQuery("#doliModalTest").modal("hide");
			jQuery("#DoliconnectLoadingModal").modal("show");
		jQuery(window).scrollTop(0);
		form.submit();
		});

	});
})( jQuery );
