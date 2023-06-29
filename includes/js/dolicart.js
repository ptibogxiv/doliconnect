function doliJavaCartAction(form, id, qty, acase) {
	(function ($) {
	  $(document).ready(function () {
		$("#DoliconnectLoadingModal").modal("show");
		$.ajax({
		  url:"'.admin_url('admin-ajax.php').'",
		  type:"POST",
		  cache:false,
		  data: {
			"action": "dolicart_request",
			"dolicart-nonce": "'.wp_create_nonce( 'dolicart-nonce').'",
			"case": form,
			"id" : id,
			"qty" : qty,
			"modify" : acase
		  },
		}).done(function(response) {
		  if (response.success) { 
			if (document.getElementById("qty-prod-" + id)) {
			  document.getElementById("qty-prod-" + id).value = response.data.newqty;
			}
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
			//$("#offcanvasDolicart").offcanvas("show");  
		  } else {
		  }
		  $("#DoliconnectLoadingModal").modal("hide");
		});
	  })
	})(jQuery);
  }