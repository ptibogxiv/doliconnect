function doliModalButton(acase, id, value1, value2, redirect_to) {
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
						const modalContainer = document.getElementById("doliModalDiv");
						modalContainer.innerHTML = response.data.modal;
						const dialog = document.getElementById("doliModal" + id);
						if (dialog && typeof dialog.showModal === "function") {
							dialog.addEventListener("close", function () {
								modalContainer.innerHTML = "";
							}, { once: true });
							dialog.showModal();
							console.log( "ok loading dialog" + " - case: " + acase );
						} else {
							console.error( "error opening dialog" + " - case: " + acase );
						}
					}
				} else {
					console.log( "error loading modal" + " - case: " + acase + " - error: " + response.data);
					const modalContainer = document.getElementById("doliModalDiv");
					if (modalContainer) modalContainer.innerHTML = "";
				}
			});
        })
    })(jQuery);
}