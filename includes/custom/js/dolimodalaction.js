function doliModalAction(form, action, url) {
    ( function( $ ) {
		'use strict';
		$(document).ready(function () {
			var $form = $(form);
			// Bloquer l'AJAX si le formulaire n'est pas valide
			if (!form.checkValidity()) {
				try { form.reportValidity(); } catch (e) { }
				var $firstInvalid = $form.find(':invalid').first();
				if ($firstInvalid.length) { $firstInvalid.focus(); }
				return;
			}
			$("#DoliconnectLoadingModal").modal("show");
			$form.append('<input type="hidden" name="case" value="' + action + '">');
			var modalId = $form.find('input[name="modalid"]').val();
			if (modalId) {
				$("#doliModal" + modalId).modal("hide");
			}
			$("#DoliconnectLoadingModal").one("shown.bs.modal", function (e) { 
				$.ajax({
					url: url,
					type: "POST",
					cache: false,
					data: $form.serialize(),
				}).done(function(response) {
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
				});
			});	
		})
    })(jQuery);
}