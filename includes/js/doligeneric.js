( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

		var form = document.getElementById('loginmodal-form');
		form.addEventListener('submit', function(event) { 
		jQuery(window).scrollTop(0);
		console.log("submit");
		form.submit();
		});

	});
})( jQuery );
