function doliModalAction(form, action, url) {
    ( function( $ ) {
		'use strict';
        $(document).ready(function () {			
			$("#DoliconnectLoadingModal").modal("show");
			var $form = $(form);
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