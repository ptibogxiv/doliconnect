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
			document.getElementById("message-dolicart").innerHTML = "";  
			  $.post($form.attr("action"), $form.serialize(), function(response) {
			if (response.success) { 
			  //console.log(response.data.message);
			  if (document.getElementById("DoliHeaderCartItems")) {
				document.getElementById("DoliHeaderCartItems").innerHTML = response.data.items;
			  }
			  if (document.getElementById("DoliFooterCartItems")) {  
				document.getElementById("DoliFooterCartItems").innerHTML = response.data.items;
			  }
			  if (document.getElementById("DoliCartItemsList")) {  
				document.getElementById("DoliCartItemsList").innerHTML = response.data.list;
			  }
			  if (document.getElementById("DoliWidgetCartItems")) {
				document.getElementById("DoliWidgetCartItems").innerHTML = response.data.items;      
			  }
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
