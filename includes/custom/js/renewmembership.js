( function( $ ) {
	'use strict';

	jQuery(document).ready(function() {

	jQuery("#subscribe-form").on("submit", function(e) { 
		e.preventDefault();
		e.stopPropagation();  
		jQuery("#doliModalTest").modal("hide");
		jQuery("#DoliconnectLoadingModal").modal("show");
		var $form = $(this);
		var url = "'.esc_url(doliconnecturl('dolicart')).'";
		jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) {  
			  $.post($form.attr("action"), $form.serialize(), function(response) {
			if (response.success) { 
			  //console.log(response.data.message);
			  if (document.getElementById("DoliHeaderCartItems") && response.data.hasOwnProperty("items")) {
				document.getElementById("DoliHeaderCartItems").innerHTML = response.data.items;
			  }
			  if (document.getElementById("DoliFooterCartItems") && response.data.hasOwnProperty("items")) {  
				document.getElementById("DoliFooterCartItems").innerHTML = response.data.items;
			  }
			  if (document.getElementById("DoliWidgetCartItems") && response.data.hasOwnProperty("items")) {
				document.getElementById("DoliWidgetCartItems").innerHTML = response.data.items;      
			  }
			} else {
			  //console.log("error updating qty " + response.data.message);
			}
			$("#DoliconnectLoadingModal").modal("hide");
			  }, "json");  
		});
	  });

	});
})( jQuery );
