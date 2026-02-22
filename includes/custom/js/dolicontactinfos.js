( function( $ ) {
	'use strict';

	$(document).ready(function() {
		$("#dolicontactinfos-form").on("submit", function(event) { 
			event.preventDefault();
			event.stopPropagation(); 
			$("#DoliconnectLoadingModal").modal("show");
			var $form = $(this);
			var modalId = $form.find('input[name="modalid"]').val();
			console.log("dolicontactinfos form id: " + modalId);
			var modalId2 = $form.find('button[name="case"]').val();
			console.log("dolicontactinfos form id: " + modalId2);
			if (modalId) {
				$("#doliModal" + modalId).modal("hide");
			}
			$("#DoliconnectLoadingModal").one("shown.bs.modal", function (e) { 
				$.post($form.attr("action"), $form.serialize(), function(response) {
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
					$("#DoliconnectLoadingModal").modal("hide");
				}, "json");  
			});
		});
	});
})( jQuery );
