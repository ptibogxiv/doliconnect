function doliCartButton(form, id, lineid, qty, productarray, acase) {
          (function ($) {
            $(document).ready(function () {
              var DisplayCart = 0;
              if (document.getElementById("doliModalCartInfos")) {
                document.getElementById("doliModalDiv").innerHTML = ""; 
                $("#doliModalCartInfos").modal("handleUpdate"); 
                DisplayCart = 1;  
              }
              console.log( form + acase );
              $.ajax({
                url: dolicart_localize.dolicart_ajax_url,
                type: "POST",
                cache: false,
                data: {
                  "action": "dolicart_request",
                  "dolicart-nonce": dolicart_localize.dolicart_nonce,
                  "case": form,
                  "id" : id,
                  "lineid" : lineid,
                  "qty" : qty,
                  "productarray" : productarray,
                  "modify" : acase,
                  "DisplayCart" : DisplayCart
                },
              }).done(function(response) {
                if (response.success) { 
                  if (document.getElementById("doliform-product-" + id + "-" + lineid) && response.data.hasOwnProperty("newwish")) {
                     document.getElementById("doliform-product-" + id + "-" + lineid).outerHTML = response.data.newwish;
                  }
                  if (document.getElementById("DoliHeaderCartItems") && response.data.hasOwnProperty("items")) {
                    document.getElementById("DoliHeaderCartItems").innerHTML = response.data.items;
                  }
                  if (document.getElementById("DoliFooterCartItems") && response.data.hasOwnProperty("items")) {  
                    document.getElementById("DoliFooterCartItems").innerHTML = response.data.items;
                  }
                  if (document.getElementById("DoliWidgetCartItems") && response.data.hasOwnProperty("items")) {
                    document.getElementById("DoliWidgetCartItems").innerHTML = response.data.items;      
                  }
                  if (document.getElementById("offcanvasDoliCartLabel") && response.data.hasOwnProperty("dolicart")) {
                    document.getElementById("offcanvasDoliCartLabel").innerHTML = response.data.dolicart;      
                  }
                  if (document.getElementById("doliModalDiv") && response.data.hasOwnProperty("modal")) {
                    document.getElementById("doliModalDiv").innerHTML = response.data.modal; 
                    $("#doliModalCartInfos").modal("toggle");     
                  } 
                } else {
                  if (document.getElementById("doliModalDiv") && response.data.hasOwnProperty("modal")) {
                    document.getElementById("doliModalDiv").innerHTML = response.data.modal; 
                    $("#doliModalCartInfos").modal("toggle");     
                  }
                }
                $("#doliModalCartInfos").on("hidden.bs.modal", function () {
                  $("#doliModalCartInfos").modal("dispose");
                  document.getElementById("doliModalDiv").innerHTML = "";
                });
              });
            })
          })(jQuery);
        }