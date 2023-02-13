( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

	jQuery("#linkedmember-form").on("submit", function(e) { 
		e.preventDefault();
		e.stopPropagation();  
		jQuery("#doliModalTest").modal("hide");
		jQuery("#DoliconnectLoadingModal").modal("show");
		var $form = $(this);
		var url = "'.esc_url(doliconnecturl('dolicart')).'";
		jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) {
			document.getElementById("message-dolicart").innerHTML = "";  
			  $.post($form.attr("action"), $form.serialize(), function(response) {
			if (response.success) { 
			  if (document.getElementById("message-dolicart")) {
				document.getElementById("message-dolicart").innerHTML = response.data.message;      
			  }
			  $("#offcanvasDolicart").offcanvas("show");  
			} else {
			  //console.log("error updating qty " + response.data.message);
			}
			$("#DoliconnectLoadingModal").modal("hide");
			  }, "json");  
		});
	  });

	});
})( jQuery );
