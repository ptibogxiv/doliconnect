( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {
	console.log( "success js" + " - case: " + "dolicontactinfos" );
	jQuery("#dolicontactinfos-form").on("submit", function(e) { 
		e.preventDefault();
		e.stopPropagation(); 
		jQuery("#DoliconnectLoadingModal").modal("show");
		var $form = $(this);
		var modalId = $form.find('input[name="modalid"]').val();
		console.log("dolicontactinfos form id: " + modalId);
		if (modalId) {
			jQuery("#doliModal" + modalId).modal("hide");
		}
		jQuery("#DoliconnectLoadingModal").one("shown.bs.modal", function (e) { 
			jQuery.post($form.attr("action"), $form.serialize(), function(response) {
				if (response.success) {
					if (document.getElementById("dolicontactinfos-alert") && response.data.hasOwnProperty("message")) {
						document.getElementById("dolicontactinfos-alert").innerHTML = response.data.message;      
					}
					if (document.getElementById("dolicontact-list") && response.data.hasOwnProperty("list")) {
						document.getElementById("dolicontact-list").innerHTML = response.data.list;  
					}
				} else {
					if (document.getElementById("dolicontactinfos-alert") && response.data.hasOwnProperty("message")) {
						document.getElementById("dolicontactinfos-alert").innerHTML = response.data.message;      
					}
				}
				jQuery("#DoliconnectLoadingModal").modal("hide");
			}, "json");  
		});
	  });

	});
})( jQuery );
