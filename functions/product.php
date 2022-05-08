<?php

function doliproduct($object, $value) {
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
return !empty($object->multilangs->$lang->$value) ? $object->multilangs->$lang->$value : $object->$value;
} else {
if (isset($object->$value)) return $object->$value;
}
}

function doliprice($object = null, $mode = "ttc", $currency = null) {
global $current_user;

if ( is_object($object) ) {
$total='multicurrency_total_'.$mode;
if ( isset($object->$mode) ) { $montant=$object->$mode;
} else {
$total = 'total_'.$mode;
$montant = $object->$total;
} } elseif (!empty($object)) {
$montant = $object;
} else {
$montant = 0;
}
if (empty((int)$montant)) {
return __( 'Free', 'doliconnect');
} else {
//$objet->multicurrency_code
if ( is_null($currency) ) { $currency = strtoupper(doliconst("MAIN_MONNAIE")); }
if ( function_exists('pll_the_languages') ) { 
$locale = pll_current_language('locale');
} else { if ( $current_user->locale == null ) { $locale = get_locale(); } else { $locale = $current_user->locale; } }
$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
return numfmt_format_currency($fmt, $montant, $currency);//.$decimal
}
}

function doliproductstock($product, $refresh = false) {

$stock = "<script>";
$stock .= "(function ($) {
$(document).ready(function(){
$('#popover-stock-".$product->id."').popover({
placement : 'auto',
delay: { 'show': 150, 'hide': 150 },
trigger : 'focus',
html : true
})
});
})(jQuery);";
$stock .= "</script>";

if ( ! is_object($product) || empty(doliconst('MAIN_MODULE_STOCK', $refresh)) || (!empty($product->type) && empty(doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) ) || (empty($product->type) && !empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) && !empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER', $refresh)) )) {
$stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-success text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available', 'doliconnect')."' data-bs-content='".__( 'This item is available and can be order', 'doliconnect')."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
} else {
$warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE', $refresh);
if (isset($product->stock_warehouse) && !empty($product->stock_warehouse) && !empty($warehouse) && $warehouse > 0) {
if (isset($product->stock_warehouse->$warehouse)) {
$realstock = min(array($product->stock_reel,$product->stock_warehouse->$warehouse->real,$product->stock_theorique));
} else {
$realstock = 0;
}
} else {
$realstock = min(array($product->stock_theorique,$product->stock_reel));
}

if (!empty(doliconnectid('dolishipping'))) {
$shipping = '<a href="'.doliconnecturl('dolishipping').'" class="btn btn-link btn-block btn-sm">'.__( 'Shipping', 'doliconnect').'</a>';
} else {
$shipping = null;
}

if ( $realstock <= 0 || (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging) && $realstock < $product->array_options->options_packaging) ) { $stock .= "<a tabindex='0' id='popover-".$product->id."' class='badge rounded-pill bg-dark text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Not available', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is out of stock and can not be ordered or shipped. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Not available', 'doliconnect')."</a>"; }  
elseif ( ($realstock <= 0 || (isset($product->array_options->options_packaging) && $realstock < $product->array_options->options_packaging)) && $product->stock_theorique > $realstock ) { 
$delay =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product', $refresh));
if (empty($delay[0]->delivery_time_days)) { $delay = esc_html__( 'few', 'doliconnect'); } else { $delay = $delay[0]->delivery_time_days;}
if (doliversion('12.0.0')) {
$datelivraison =  callDoliApi("GET", "/supplierorders?sortfield=t.date_livraison&sortorder=ASC&limit=1&product_ids=".$product->id."&sqlfilters=(t.fk_statut%3A%3D%3A'2')", null, dolidelay('order', $refresh));
if (!empty($datelivraison) && is_array($datelivraison) && isset($datelivraison[0]->date_livraison) && !empty($datelivraison[0]->date_livraison)) {
$next = sprintf( "<br>".esc_html__( 'Reception scheduled on %s.', 'doliconnect'), wp_date('d/m/Y', $datelivraison[0]->date_livraison));
} else {
$next = null;
}
} else {
$next = null;
}
$stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-danger text-white text-decoration-none' title='".__( 'Available soon', 'doliconnect')."' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' data-bs-content='".sprintf( __( 'This item is not in stock but should be available soon within %s days. %s %s', 'doliconnect'), $delay, $next, $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available soon', 'doliconnect')."</a>"; 
} elseif ( $realstock >= 0 && $realstock <= $product->seuil_stock_alerte ) { $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-warning text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Limited availability', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is in stock and can be shipped immediately but only in limited quantities. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect')."</a>";
} else {
$stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-success text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available immediately', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is in stock and can be shipped immediately. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
}
} 

return $stock;//.$realstock
}

function doliconnect_countitems($object){
$qty=0;
if ( is_object($object) && isset($object->lines) && $object->lines != null ) {
foreach ($object->lines as $line) {
$qty+=$line->qty;
}
}
return $qty;
}

function doliconnect_CartItemsList() {
global $current_user;
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order'));
if ( isset($order->lines) && $order->lines != null ) {
$ln = '<table class="table table-hover table-sm"><thead><tr>
<th scope="col" width="40px">'.__( 'Qty', 'doliconnect').'</th><th scope="col">'.__( 'Item', 'doliconnect').'</th></tr></thead><tbody>';
foreach ( $order->lines as $line ) { 
$ln .= '<tr><td scope="row">'.$line->qty.'</td><td>'.doliproduct($line, 'product_label');
if ( !empty(get_option('doliconnectbeta')) ) $ln .= '<div class="float-end"><i class="fa-solid fa-trash-can"></i></div>';
$ln .= '</td></tr>';
}
$ln .= '</tbody><tfoot><tr><th colspan="2" class="table-active">'.__( 'Total to be paid', 'doliconnect').' '.doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null).'</th></tr></tfoot></table><div class="dropdown mt-3">
<div class="d-grid gap-2">';
$ln .= '<a class="btn btn-primary" role="button" href="'.esc_url(doliconnecturl('dolicart')).'" >'.__( 'Finalize the order', 'doliconnect').'</a>';
if ( !empty(get_option('doliconnectbeta')) ) $ln .= '<button type="button" class="btn btn-outline-secondary">'.__( 'Empty the basket', 'doliconnect').'</button>';
$ln .= '</div></div>';
return $ln;
} else {
return '<center class="p-3 text-muted">'.__( 'Your basket is empty', 'doliconnect').'</center>';
}
}

function doliaddtocart($productid, $quantity = null, $price = null, $remise_percent = null, $timestart = null, $timeend = null, $url = null, $array_options = array()) {
global $current_user;

if (!is_null($timestart) && $timestart > 0 ) {
$date_start=strftime('%Y-%m-%d 00:00:00', $timestart);
} else {
$date_start=null;
}

if ( !is_null($timeend) && $timeend > 0 ) {
$date_end=strftime('%Y-%m-%d 00:00:00', $timeend);
} else {
$date_end=null;
} 

if ( empty(doliconnector($current_user, 'fk_order', true)) ) {
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty'));
$rdr = [
    'socid' => doliconnector($current_user, 'fk_soc'),
    'date' => time(),
    'demand_reason_id' => 1,
    'cond_reglement_id' => $thirdparty->cond_reglement_id,
    'shipping_method_id' => $thirdparty->shipping_method_id,
    'module_source' => 'doliconnect',
    'modelpdf' =>  doliconst("COMMANDE_ADDON_PDF"),
    'pos_source' => get_current_blog_id(),
	];                  
$order = callDoliApi("POST", "/orders", $rdr, 0);
}

$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));

if ( isset($order->lines) && $order->lines != null ) {
foreach ( $order->lines as $ln ) {
if ( $ln->fk_product == $productid ) {
//$deleteline = callDoliApi("DELETE", "/orders/".$orderid."/lines/".$ln[id], null, 0);
//$qty=$ln[qty];
$line=$ln->id;
}
}}

if (isset($line) && !$line > 0) { $line = null; }
if (! isset($line)) { $line = null; }

$prdt = callDoliApi("GET", "/products/".$productid."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', true));

$warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE');
if (isset($prdt->stock_warehouse) && !empty($prdt->stock_warehouse) && is_numeric($warehouse)) {
if (isset($prdt->stock_warehouse->$warehouse)) {
$realstock = min(array($prdt->stock_reel,$prdt->stock_warehouse->$warehouse->real,$prdt->stock_theorique));
} else {
$realstock = 0;
}
} else {
$realstock = min(array($prdt->stock_theorique,$prdt->stock_reel));
}

if (empty($prdt->status)) {

if (!empty($line)) $deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
//delete_transient( 'doliconnect_cartlinelink_'.$line );

return -1;

} elseif ( doliconnector($current_user, 'fk_order') > 0 && $quantity > 0 && empty($line) && (empty(doliconst('MAIN_MODULE_STOCK')) || $realstock >= $quantity || (is_null($line) && empty(doliconst('STOCK_SUPPORTS_SERVICES')) ))) {
                                                                                     
$adln = [
    'fk_product' => $prdt->id,
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price,
    'array_options' => $array_options
	];                 
$addline = callDoliApi("POST", "/orders/".doliconnector($current_user, 'fk_order')."/lines", $adln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
//set_transient( 'doliconnect_cartlinelink_'.$addline, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
}
return doliconnect_countitems($order);

} elseif ( doliconnector($current_user, 'fk_order') > 0 && ($realstock >= $quantity || empty($quantity) || (is_object($line) && $line->type != '0' && empty(doliconst('STOCK_SUPPORTS_SERVICES')) )) && $line > 0 ) {

if ( $quantity < 1 ) {

$deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
//delete_transient( 'doliconnect_cartlinelink_'.$line );

return doliconnect_countitems($order);
 
} else {

$uln = [
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price,
    'array_options' => $array_options
	];                  
$updateline = callDoliApi("PUT", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, $uln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
//set_transient( 'doliconnect_cartlinelink_'.$line, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
} else {
//delete_transient( 'doliconnect_cartlinelink_'.$line );

}
return doliconnect_countitems($order);

}
} elseif ( doliconnector($current_user, 'fk_order') > 0 && is_null($line) ) {

return doliconnect_countitems($order);

} else {

return -$realstock;//doliconnect_countitems($order);

}
}

function doliProductCart($product) {
if (current_user_can('administrator') && !empty(get_option('doliconnectbeta')) ) { 

  print '<form id="doliform-product-'.$product->id.'" method="post">';
  
  print "<script>";
  print '$(function() {
      $("#doliform-product-'.$product->id.' button[type=submit]").on("click", function(e) {
          e.preventDefault();
          var acase = $(this).val();
          //jQuery("#DoliconnectLoadingModal").modal("show");
          console.log("changed " + '.$product->id.' + " to " + acase);
          $.ajax({
              url :"'.admin_url('admin-ajax.php').'",
              type:"POST",
              cache:false,
              data: {
                "action": "dolicart_request",
                "case": acase,
              },
          }).done(function(response) {
              //jQuery("#DoliconnectLoadingModal").modal("hide");
              console.log("updated qty " + response.data);
          });
  
      });
  });';
  print "</script>";
  
  print '<div class="input-group">';
  print '<button class="btn btn-sm btn-warning" name="minus" value="minus" type="submit"><i class="fa-solid fa-minus"></i></button>
  <input type="text" class="form-control" placeholder="" aria-label="Quantity" value="0" style="text-align:center;" readonly>
  <button class="btn btn-sm btn-warning" name="plus" value="plus" type="submit"><i class="fa-solid fa-plus"></i></button>';
  if ( !empty(doliconst('MAIN_MODULE_WISHLIST')) && !empty(get_option('doliconnectbeta')) ) {
  print '<button class="btn btn-sm btn-light" name="wish" value="wish" type="submit"><i class="fas fa-heart" style="color:Fuchsia"></i></button>';
  }
  print '</div>';
  print '</form>';
}}

function doliconnect_addtocart($product, $category = 0, $quantity = 0, $add = 0, $time = 0, $refresh = null) {
global $current_user;

$button = "<form id='form-product-cart-".$product->id."' class='form-product-cart-".$product->id."' method='post' action='".admin_url('admin-ajax.php')."'>";
$button .= "<input type='hidden' name='action' value='doliproduct_request'>";
$button .= "<input type='hidden' name='product-add-nonce' value='".wp_create_nonce( 'product-add-nonce-'.$product->id)."'>";
$button .= "<input type='hidden' name='product-add-id' value='".$product->id."'>";

$button .= "<script>";
$button .= 'jQuery(document).ready(function($) {
	jQuery(".form-product-cart-'.$product->id.'").on("submit", function(e){
  jQuery("#DoliconnectLoadingModal").modal("show");
	e.preventDefault();
	var $form = $(this);
    
jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function(e){ 
		$.post($form.attr("action"), $form.serialize(), function(response){
      jQuery("#offcanvasDolicart").offcanvas("show");
      document.getElementById("message-dolicart").innerHTML = "";
      jQuery("#DoliconnectLoadingModal").modal("hide");  
      if (response.success) {
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
      } else {
      if (document.getElementById("message-dolicart")) {
      document.getElementById("message-dolicart").innerHTML = response.data.message;      
      }      
      }
		}, "json");  
  });
});
});';
$button .= "</script>";

if (doliconnector($current_user, 'fk_order') > 0) {
$orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order'), null, $refresh);
//$button .=$orderfo;
}

if ( isset($orderfo->lines) && $orderfo->lines != null ) {
foreach ($orderfo->lines as $line) {
if  ($line->fk_product == $product->id) {
//$button = var_dump($line);
$qty = $line->qty;
$ln = $line->id;
}
}}
if (!isset($qty) ) {
$qty = null;
$ln = null;
}

$currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:strtoupper(doliconst("MAIN_MONNAIE", $refresh));

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {
if ( $product->duration_unit == 'i' ) {
$altdurvalue=60/$product->duration_value; 
}
}

$discount = !empty(doliconnector($current_user, 'remise_percent'))?doliconnector($current_user, 'remise_percent'):'0';
$customer_discount = $discount;

if ( !empty(doliconst("PRODUIT_MULTIPRICES", $refresh)) && !empty($product->multiprices_ttc) ) {
$lvl=doliconnector($current_user, 'price_level');
//$button .=$lvl;

if (!empty(doliconnector($current_user, 'price_level'))) {
$level=doliconnector($current_user, 'price_level');
} else {
$level=1;
}
 
$price_min_ttc = $product->multiprices_min->$level; 
$price_ttc = $product->multiprices_ttc->$level;
$price_ht = $product->multiprices->$level; 
$vat = $product->tva_tx;
$refprice=(empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht);

if (isset($add) && $add < 0) {
$button .= '<table class="table table-bordered table-sm"><tbody>'; 
$button .= '<tr><td class="text-end"><div class="float-start">'.__( 'Selling Price', 'doliconnect').'</div>';
$button .= '<div class="float-end">'.doliprice( (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht), null, $currency)."</div></td></tr>";
} else {
$button .= '<table class="table table-sm table-striped table-bordered"><tbody>';
$multiprix = (empty(get_option('dolibarr_b2bmode'))?$product->multiprices_ttc:$product->multiprices);
foreach ( $multiprix as $level => $price ) {
$button .= '<tr';
if ( (empty(doliconnector($current_user, 'price_level')) && $level == 1 ) || doliconnector($current_user, 'price_level') == $level ) {
$button .= ' class="table-primary"';  
}
$button .= '>';   
$button .= '<td><small>'.(!empty(doliconst('PRODUIT_MULTIPRICES_LABEL'.$level, $refresh))?doliconst('PRODUIT_MULTIPRICES_LABEL'.$level, $refresh):__( 'Price', 'doliconnect').' '.$level).'</small></td>';
$button .= '<td class="text-end"><small>'.doliprice( (empty(get_option('dolibarr_b2bmode'))?$price:$price_ht), null, $currency);
if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); }
$button .= '</small></td>';
if ( !empty($altdurvalue) ) { $button .= "<td class='text-end'>soit ".doliprice( $altdurvalue*(empty(get_option('dolibarr_b2bmode'))?$price:$price_ht), null, $currency)." par ".__( 'hour', 'doliconnect')."</td>"; } 
//$button .= '<small class="float-end">'.__( 'You benefit from the rate', 'doliconnect').' '.doliconst('PRODUIT_MULTIPRICES_LABEL'.$level).'</small>';
$button .= '</tr>'; 
}
}

$button .= '<tr><td colspan="';
if (!empty($product->net_measure)) { $button .= '2'; } else { $button .= '3'; };
$button .= '"><small class="fw-lighter">';
if (!empty($product->net_measure)) { $button .= '<div class="float-end">'.doliprice( $refprice/$product->net_measure, null, $currency).'</div>';
$unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
if (!empty($unit)) $button .= "/".$unit[0]->short_label; }
$button .= "</small></td></tr>";

$button .= '</tbody></table>';
} else {

if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES", $refresh)) && doliconnector($current_user, 'fk_soc', $refresh) > 0 ) {
$product2 = callDoliApi("GET", "/products/".$product->id."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
if ( !isset($product2->error) && $product2 != null ) {
$new_product2 = array_filter($product2, function($obj){
global $current_user;
$thirdparty_id = doliconnector($current_user, 'fk_soc');
    if (isset($obj->fk_soc)) {
            if ($obj->fk_soc != $thirdparty_id)  { return false; }
    }
    return true;
});
$product2 = null;
foreach ($new_product2 as $array) {
$product2 = $array;
}
}
}

if ( !empty(doliconst('MAIN_MODULE_DISCOUNTPRICE', $refresh)) ) {
$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$requestp = "/discountprice?productid=".$product->id."&sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')";
$product3 = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
}
//$button .= var_dump($product3);

if ( !empty(doliconst('MAIN_MODULE_DISCOUNTPRICE', $refresh)) && isset($product3) && !isset($product3->error) ) {
if (!empty($product3[0]->discount)) {
$price_ttc3=$product->price_ttc-($product->price_ttc*$product3[0]->discount/100);
$price_ht3=$product->price-($product->price*$product3[0]->discount/100);
$price_ttc=$product->price_ttc;
$price_ht=$product->price;
$vat = $product->tva_tx;
$discount = $product3[0]->discount;
} elseif (!empty($product3[0]->price)) {
$price_ht3=$product3[0]->price; 
$price_ht=$product->price; 
$discount = 100-(100*$price_ht3/$price_ht);
$price_ttc3=$product->price_ttc-($product->price_ttc*$discount/100);
$price_ttc=$product->price_ttc;
$vat = $product->tva_tx;
} elseif (!empty($product3[0]->price_ttc)) {
$price_ttc3=$product3[0]->price_ttc; 
$price_ttc=$product->price_ttc; 
$discount = 100-(100*$price_ttc3/$price_ttc);
$price_ht3=$product->price-($product->price*$discount/100);
$price_ht=$product->price;
$vat = $product->tva_tx;
}
$price_min_ttc=$product->price_min_ttc;
$refprice=(empty(get_option('dolibarr_b2bmode'))?$price_ttc3:$price_ht3);

if (!empty($product3[0]->label)) {
$discountlabel = $product3[0]->label;
}

} elseif ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES", $refresh)) && isset($product2) && !empty($product2) && !isset($product2->error) ) {
  $price_min_ttc3=$product->price_min_ttc-($product2->price_min_ttc*$discount/100);
  $price_ttc3=$product->price_ttc-($product2->price_ttc*$discount/100);
  $price_ht3=$product->price-($product2->price*$discount/100);
  $price_min_ttc=$product2->price_min_ttc;
  $price_ttc=$product2->price_ttc;
  $price_ht=$product2->price;
  $vat = $product2->tva_tx;
  $refprice = (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht);
} else {
  $price_min_ttc3=$product->price_min_ttc-($product->price_min_ttc*$discount/100);
  $price_ttc3=$product->price_ttc-($product->price_ttc*$discount/100);
  $price_ht3=$product->price-($product->price*$discount/100);
  $price_min_ttc=$product->price_min_ttc;
  $price_ttc=$product->price_ttc;
  $price_ht=$product->price;
  $vat=$product->tva_tx;
  $refprice = (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht);
}

if ($price_min_ttc == $price_ttc) {
$discount = 0;
$price_ttc3 = $price_min_ttc;
//$price_ht3 = $price_min_ht;
} elseif ($price_min_ttc > ($price_ttc-($price_ttc*$discount/100))) {
$discount = 100-(100*$price_min_ttc/$price_ttc);
$price_ttc3 = $price_ttc-($price_ttc*$discount/100);
$price_ht3 = $price_ht-($price_ht*$discount/100);
}

}

$button .= "<script>";
$button .= "(function ($) {
$(document).ready(function(){
$('#popover-price-".$product->id."').popover({
placement : 'auto',
delay: { 'show': 150, 'hide': 150 },
trigger : 'focus',
html : true
})
});
})(jQuery);";
$button .= "</script>";
$explication = (empty(get_option('dolibarr_b2bmode'))?__( 'Displayed price is included VAT', 'doliconnect'):__( 'Displayed price is excluded VAT', 'doliconnect'));
$explication .= sprintf(__( 'VAT rate of %s', 'doliconnect'), $vat);
//$explication .= "<ul>";
$explication .= sprintf(__( 'Initial sale price: %s', 'doliconnect'), doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht, $currency));
if (isset($customer_discount) && !empty($customer_discount) && !empty($discount)) $explication .= sprintf(__( 'Your customer discount is %s percent', 'doliconnect'), $customer_discount);
if (isset($discountlabel) && !empty($discountlabel)) $explication .= $discountlabel;
if ($price_ttc != $price_ttc3) $explication .= sprintf(__( 'Discounted price: %s', 'doliconnect'), doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc3:$price_ht3, $currency));
//$explication .= "</ul>";
$button .= "<a tabindex='0' id='popover-price-".$product->id."' class='btn btn-light position-relative top-0 end-0";
if (!empty($discount)) $button .= " text-danger";
$button .= "' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'About price', 'doliconnect')."' data-bs-content='".$explication."'>";
$button .= doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc3:$price_ht3, $currency);
if (!empty($discount)) $button .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">-'.$discount.'%<span class="visually-hidden">discount</span></span>';
if (!empty($product->net_measure) && !empty($product->net_measure_units)) { 
  $unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
  $button .= '<span class="position-absolute top-100 start-0 translate-middle badge rounded-pill bg-info"><small>'.doliprice( $refprice/$product->net_measure, null, $currency).'/'.$unit[0]->short_label.'<span class="visually-hidden">net measure price</span></small></span>';
}
if (!empty($discount)) $button .= '<span class="position-absolute top-100 start-100 translate-middle badge bg-light text-dark"><small><s>'.doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht, $currency).'</s><span class="visually-hidden">initial price</span></small></span>';
$button .= '</a><br><br>';

//if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); } 
//if ( !empty($altdurvalue) ) { $button .= "<tr><td class='text-end'>soit ".doliprice( $altdurvalue*$product->price_ttc, null, $currency)." par ".__( 'hour', 'doliconnect')."</td></tr>"; } 
if (!empty($product->net_measure) && !empty($product->net_measure_units)) { 
  $unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
  $button .= '<span class="badge rounded-pill bg-light text-dark">'.$product->net_measure;
  if (!empty($unit)) $button .= " ".$unit[0]->short_label;
  $button .= '</span> ';
}
  

if ( empty(doliconnectid('dolicart')) ) {

$button .= "<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect')."'>".__( 'Contact us', 'doliconnect')."</a></div>";

} elseif ( is_user_logged_in() && !empty($add) && !empty(doliconst('MAIN_MODULE_COMMANDE', $refresh)) && doliconnectid('dolicart') > 0 ) {
$warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE', $refresh);
if (isset($product->stock_warehouse) && !empty($product->stock_warehouse) && !empty($warehouse) && $warehouse > 0) {
if (isset($product->stock_warehouse->$warehouse)) {
$realstock = min(array($product->stock_reel,$product->stock_warehouse->$warehouse->real,$product->stock_theorique));
} else {
$realstock = 0;
}
} else {
$realstock = min(array($product->stock_theorique,$product->stock_reel));
}
if (empty($product->type) && !empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) && empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER', $refresh))) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m0 = 1*$product->array_options->options_packaging;
$m1 = get_option('dolicartlist')*$product->array_options->options_packaging;
} else {
$m0 = 1;
$m1 = get_option('dolicartlist');
}
$m2 = $m1; 
} elseif ( $realstock-$qty > 0 && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) ) ) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m0 = 1*$product->array_options->options_packaging;
$m1 = get_option('dolicartlist')*$product->array_options->options_packaging;
} else {
$m0 = 1;
$m1 = get_option('dolicartlist');
}
if ( $realstock-$qty >= $m1 || empty(doliconst('MAIN_MODULE_STOCK')) ) {
$m2 = $m1;
} elseif ( $realstock > $qty ) {
$m2 = $realstock;
} else { $m2 = $qty; }
} else {
$m0 = 1;
if ( isset($line) && $line->qty > 1 ) { $m2 = $qty; }
else { $m2 = 1; }
} 
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$step = $product->array_options->options_packaging;
} else {
$step = 1;
}

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', $refresh));

$button .= "<div class='input-group input-group-sm mb-3'><select class='form-control btn-light btn-outline-secondary' id='product-".$product->id."-add-qty' name='product-add-qty' ";
if (( isset($thirdparty->status) && $thirdparty->status != '1' ) || (( $realstock <= 0 || $m2 < $step) && empty($product->type) && !empty(doliconst('MAIN_MODULE_STOCK')) )) { $button .= " disabled"; }
$button .= ">";
if (isset($thirdparty->status) && $thirdparty->status != '1' )  { $button .= "<OPTION value='0' selected>".__( 'Account closed', 'doliconnect')."</OPTION>"; }
elseif (($realstock <= 0 && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) )) || $m2 < $step)  { $button .= "<OPTION value='0' selected>".__( 'Unavailable', 'doliconnect')."</OPTION>"; }
elseif (!empty($m2) && $m2 >= $step) {
if ($step >1 && !empty($quantity)) $quantity = round($quantity/$step)*$step; 
if (empty($qty) && $quantity > $m2) $quantity = $m2; 
if ($m2 < $step)  { $button .= "<OPTION value='0' >".__( 'Delete', 'doliconnect')."</OPTION>"; } else {
foreach (range(0, $m2, $step) as $number) {
if ($number == 0) { $button .= "<OPTION value='0' >".__( 'Delete', 'doliconnect')."</OPTION>";
} elseif ( ($number == $step && empty($qty) && empty($quantity)) || $number == $qty || ($number == $quantity && empty($qty)) || ($number == $m0 && empty($qty) && empty($quantity))) {
$button .= "<option value='$number' selected='selected'>x ".$number."</option>";
} else {
$button .= "<option value='$number' >x ".$number."</option>";
}
	}
}}
$button .= "</select>";
$button .= "<input type='hidden' name='product-add-vat' value='".$product->tva_tx."'><input type='hidden' name='product-add-remise_percent' value='".$discount."'><input type='hidden' name='product-add-price' value='".$price_ht."'>";

$button .= "<button class='btn btn-sm btn-warning' type='submit' name='cartaction' value='addtocart' title='".esc_html__( 'Add to cart', 'doliconnect')."' ";
if (( isset($thirdparty->status) && $thirdparty->status != '1' ) || (( $realstock <= 0 || $m2 < $step) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) )) { $button .= " disabled"; }
$button .= "><i class='fas fa-cart-plus fa-fw'></i></button></form>";
$button .= "</div>";

//if ( $qty > 0 ) {
//$button .= "<br /><div class='input-group'><a class='btn btn-block btn-warning' href='".doliconnecturl('dolicart')."' role='button' title='".__( 'Go to cart', 'doliconnect')."'>".__( 'Go to cart', 'doliconnect')."</a></div>";
//}
} else {
$button .= '<div class="d-grid gap-2">';
$arr_params = array( 'redirect_to' => doliconnecturl('dolishop'));
$loginurl = esc_url( add_query_arg( $arr_params, wp_login_url( )) );

if ( get_option('doliloginmodal') == '1' ) {       
$button .= '<a href="#" data-bs-toggle="modal" class="btn btn-sm btn-outline-secondary" data-bs-target="#DoliconnectLogin" data-bs-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect').'</a>';
} else {
$button .= "<a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-sm btn-outline-secondary' >".__( 'log in', 'doliconnect').'</a>';
}
$button .= '</div>';
}

$button .= "<div id='message-doliproduct-".$product->id."'></div>";

return $button;
}

function doliconnect_supplier($product, $refresh = false){

$brands =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product', $refresh));

$supplier = "";

if ( !isset($brands->error) && $brands != null ) {
$supplier .= "<small><i class='fas fa-industry fa-fw'></i> ";
$supplier .= _n( 'Supplier:', 'Supplier:', count($brands), 'doliconnect' );
$i = 0;
foreach ($brands as $brand) {
if ($i > 0) $supplier .= ",";
$thirdparty =  callDoliApi("GET", "/thirdparties/".$brand->fourn_id, null, dolidelay('product', $refresh));
 $supplier .= " ";
if (!empty(doliconnectid('dolisupplier'))) {
$supplier .= "<a href='".doliconnecturl('dolisupplier')."?supplier=".$thirdparty->id."'>";
}
$supplier .= (!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name);
if (!empty(doliconnectid('dolisupplier'))) {
$supplier .= "</a>";
}
$i++;
}
$supplier .= "</small>";
}

return $supplier;
}

// list of products filter
function doliproductlist($product) {
global $current_user;

$wish = 0;
if (!empty($product->qty)) {
$wish = $product->qty;
$product = callDoliApi("GET", "/products/".$product->fk_product."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
} else {
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}

$arr_params = array( 'search' => isset($_GET['search'])?$_GET['search']:null, 'category' => isset($_GET['category'])?$_GET['category']:null, 'subcategory' => isset($_GET['subcategory'])?$_GET['subcategory']:null, 'product' => $product->id);  
$producturl = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );

$list = "<li class='list-group-item list-group-item-light list-group-item-action' id='prod-li-".$product->id."'><table width='100%' style='border:0px'><tr><td width='20%' style='border:0px'><center>";
$list .= '<a href="'.$producturl.'" class="text-decoration-none">'.doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'150x150'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)).'</a>';
$list .= "</center></td>";
//$list .= var_dump($product);
$list .= '<td width="80%" style="border:0px"><a href="'.$producturl.'" class="text-decoration-none"><b>'.doliproduct($product, 'label').'</b></a>';
$list .= "<div class='row'><div class='col'><p><small>";
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $list .= "<i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-'); }
if ( !empty($product->barcode) ) { 
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $list .= " | "; }
$list .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
$list .= "</small>";
if ( ! empty(doliconnectid('dolicart')) ) { 
$list .= "<br>".doliproductstock($product);
}
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$list .= "<br><small><span class='flag-icon flag-icon-".strtolower($product->country_code)."'></span> ".$country->label;
if ( isset($product->state_id) && !empty($product->state_id) ) { 
$state = callDoliApi("GET", "/setup/dictionary/states/".$product->state_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
$list .= " - ".$state->name; } 
$list .= "</small>"; }
if( has_filter('mydoliconnectproductdesc') ) {
$list .= apply_filters('mydoliconnectproductdesc', $product, 'list');
}

$list .= '<div class="d-grid gap-2"><a href="'.$producturl.'" class="btn btn-link">'.__( 'Read more...', 'doliconnect').'</a></div>';
$list .= '</p></div>';

if ( ! empty(doliconnectid('dolicart')) ) { 
$list .= "<div class='col-12 col-md-4'><center>";
$list .= doliconnect_addtocart($product, esc_attr(isset($_GET['category'])?$_GET['category']:null), $wish, -1, 0, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$list .= doliProductCart($product);
$list .= "</center></div>";
}
$list .= "</div></td></tr></table></li>";
return $list;
}
add_filter( 'doliproductlist', 'doliproductlist', 10, 1);

// list of products filter
function doliproductcard($product, $attributes) {
global $current_user;

if (isset($product->id) && $product->id > 0) {

$card = "<div class='row'>";
if (defined("DOLIBUG")) {
$card = dolibug();
} elseif ($product->id > 0 && $product->status == 1) {
$card .= "<div class='col-12 d-block d-sm-block d-xs-block d-md-none'><center>";
$card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$card .= "</center>";
//$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
$card .= '</div>';
$card .= '<div class="col-md-4 d-none d-md-block"><center>';
$card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$card .= '</center>';
//$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
$card .= '</div>';
$card .= "<div class='col-12 col-md-8'><h6 itemprop='name'><b>".doliproduct($product, 'label')."</b></h6><small>";
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $card .= "<i class='fas fa-toolbox fa-fw'></i> <span itemprop='sku'>".(!empty($product->ref)?$product->ref:'-').'</span>'; }
if ( !empty($product->barcode) ) { 
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $card .= " | "; }
$card .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
$card .= "</small>";
if ( ! empty(doliconnectid('dolicart')) && !isset($attributes['hideStock']) ) { 
$card .= '<br>'.doliproductstock($product);
}
if (!empty(doliconnect_supplier($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)))) {
$card .= '<br>'.doliconnect_supplier($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
}
if (!empty(doliconnect_categories('product', $product, doliconnecturl('dolishop')))) $card .= '<br>'.doliconnect_categories('product', $product, doliconnecturl('dolishop'));
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$card .= "<br><small><i class='fas fa-globe-europe fa-fw'></i> ".__( 'Origin:', 'doliconnect')." ".$country->label;
if ( isset($product->state_id) && !empty($product->state_id) ) { 
$state = callDoliApi("GET", "/setup/dictionary/states/".$product->state_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
$card .= " - ".$state->name; } 
$card .= " <span class='flag-icon flag-icon-".strtolower($product->country_code)."'></span></small>"; }
if( has_filter('mydoliconnectproductdesc') ) {
$card .= apply_filters('mydoliconnectproductdesc', $product, 'card');
}
if ( ! empty(doliconnectid('dolicart')) ) { 
$card .= '<br><br><div class="jumbotron">';
$card .= doliconnect_addtocart($product, 0, 0, !empty($attributes['hideButtonToCart']) ? $attributes['hideButtonToCart'] : 1, isset($attributes['hideDuration']) ? $attributes['hideDuration'] : 0);
$card .= doliProductCart($product);
$card .= '</div>';
}
$card .= '</div><div class="col-12"><h6>'.__( 'Description', 'doliconnect' ).'</h6><p>'.doliproduct($product, 'description').'</p>';

if (!empty(doliconnect_supplier($product))) {
$brands =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if ( !isset($brands->error) && $brands != null ) {
$i = 0;
foreach ($brands as $brand) {
if (!empty($brand->desc_supplier)) {
$thirdparty =  callDoliApi("GET", "/thirdparties/".$brand->fourn_id, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$card .= '<p>';
$card .= (!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name).'<br>';
$card .= $brand->desc_supplier;
$card .= '</p>';
}
$i++;
}
}}

if (!empty($product->sousprods)) {
$card .= '</div><div class="col-12"><h6>'.__( 'This item contains:', 'doliconnect' ).'</h6>';
foreach ($product->sousprods as $subprod) {
  $card .= '<li>'.$subprod->qty.'x '.$subprod->label.'</li>';
}

}

$card .= '</div>';
} else {
$card .= '<div class="col-12"><p><center>'.__( 'Item not in sale', 'doliconnect' ).'</center></p></div>';
} 

if( has_filter('mydoliconnectproductcard') ) {
$card .= apply_filters('mydoliconnectproductcard', $product, 'card');
}

$card .= '</div>';
} else {
$card = "<center><br><br><br><br><div class='align-middle'><i class='fas fa-bomb fa-7x fa-fw'></i><h4>".__( 'Oops! This item does not appear to exist', 'doliconnect' )."</h4></div><br>";
$card .= '<button type="button" class="btn btn-link" onclick="window.history.back()">'.__( 'Return', 'doliconnect').'</button>';
$card .= "<br><br><br></center>";
}

return $card;
}
add_filter( 'doliproductcard', 'doliproductcard', 10, 2);

?>
