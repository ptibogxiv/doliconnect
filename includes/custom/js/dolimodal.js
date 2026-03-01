function doliJavaButtonAction(acase, id, value1, value2, redirect_to) {
    (function ($) {
        $(document).ready(function () {
			console.log("dolimodal " + acase + " - " + id );
			$.ajax({
				url: dolimodal_localize.dolimodal_ajax_url,
				type: "POST",
				cache: false,
				data: {
					"action": "dolimodal_request",
					"dolimodal-nonce": dolimodal_localize.dolimodal_nonce,
					"case": acase,
					"id": id,
					"value1": value1,
					"value2": value2,
					"redirect_to": redirect_to,
				},
			}).done(function(response) {
				if (response.success) {
					if (response.data.js) {
						$.getScript( response.data.js ).done(function( script, textStatus ) {
							console.log( "success loading js" + " - case: " + acase );
						})
						.fail(function( jqxhr, settings, exception ) {
							console.log( "error loading js" + " - case: " + acase );
						});
					}
					if (document.getElementById("doliModalDiv") && response.data.hasOwnProperty("modal")) {
						console.log( "success loading modal" + " - case: " + acase );
						document.getElementById("doliModalDiv").innerHTML = response.data.modal; 
						$("#doliModal"+id).modal("show");     
					}
				} else {
					console.log( "error loading modal" + " - case: " + acase + " - error: " + response.data);
					document.getElementById("doliModalDiv").innerHTML = "";
				}
				$("#doliModal" + id).on("hidden.bs.modal", function () {
					$("#doliModal" + id).modal("dispose");
					document.getElementById("doliModalDiv").innerHTML = "";
				});
			});
        })
    })(jQuery);
}