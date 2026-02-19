( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {
	console.log( "success js" + " - case: " + "dolicontactinfos" );
	jQuery("#dolicontactinfos-form").on("submit", function(e) { 
		e.preventDefault();
		e.stopPropagation(); 
		jQuery("#doliModalTest").modal("hide");
		jQuery("#DoliconnectLoadingModal").modal("show");
		var $form = $(this);
		var url = "'.esc_url(doliconnecturl('dolicart')).'";
		jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) { 
			  $.post($form.attr("action"), $form.serialize(), function(response) {
			if (response.success) {
				//console.log( "success update" + " - case: " + "dolicontactinfos" ); 
				if (document.getElementById("dolicontactinfos-alert") && response.data.hasOwnProperty("message")) {
                	document.getElementById("dolicontactinfos-alert").innerHTML = response.data.message;      
              	}
			} else {
				//console.log( "error update" + " - case: " + "dolicontactinfos" + " - error: " + response.data.message );
				if (document.getElementById("dolicontactinfos-alert") && response.data.hasOwnProperty("message")) {
                	document.getElementById("dolicontactinfos-alert").innerHTML = response.data.message;      
              	}
			}
			$("#DoliconnectLoadingModal").modal("hide");
			  }, "json");  
		});
	  });

	});
})( jQuery );
