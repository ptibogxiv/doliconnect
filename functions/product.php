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
if ($montant == 0) {
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

function doliProductStock($product, $refresh = false, $nohtml = false) {
global $current_user;

$mstock = array();
$warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE', $refresh);

if (!empty($product->type) && empty(doliconst('STOCK_SUPPORTS_SERVICES', $refresh))) {
  $mstock['stock'] = 1;
} elseif (isset($product->stock_warehouse) && !empty($product->stock_warehouse) && !empty($warehouse) && $warehouse > 0) {
  if (isset($product->stock_warehouse->$warehouse)) {
    $mstock['stock'] = min(array($product->stock_reel,$product->stock_warehouse->$warehouse->real,$product->stock_theorique));
  } else {
    $mstock['stock'] = 0;
  }
} else {
  $mstock['stock'] = min(array($product->stock_theorique,$product->stock_reel));
}

if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
  $mstock['step'] = $product->array_options->options_packaging;
} else {
  $mstock['step'] = 1;
}

if (!empty(doliconnectid('dolishipping'))) {
  $shipping = '<a href="'.doliconnecturl('dolishipping').'" class="btn btn-link btn-block btn-sm">'.__( 'Shipping', 'doliconnect').'</a>';
} else {
  $shipping = null;
}

if (doliconnector($current_user, 'fk_order') > 0) {
  $orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order'), null, $refresh);
}

if ( isset($orderfo->lines) && $orderfo->lines != null ) {
  foreach ($orderfo->lines as $line) {
  if  ($line->fk_product == $product->id) {
  //$button = var_dump($line);
  $mstock['qty'] = $line->qty;
  $mstock['line'] = $line->id;
  }}
}
if (!isset($mstock['qty']) ) {
  $mstock['qty'] = 0;
  $mstock['line'] = null;
}
if (isset($mstock['line']) && !$mstock['line'] > 0) { $mstock['line'] = null; }
if (! isset($mstock['line'])) { $mstock['line'] = null; }

if (doliconst('CUSTOMER_ORDER_DRAFT_FOR_VIRTUAL_STOCK', $refresh)) $mstock['stock']=$mstock['stock']+$mstock['qty'];

if ( $mstock['stock']-$mstock['qty'] > 0 && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) ) ) {
  $mstock['m0'] = 1*$mstock['step'];
  $mstock['m1'] = get_option('dolicartlist')*$mstock['step'];
  if ( $mstock['stock']-$mstock['qty'] >= $mstock['m1'] || empty(doliconst('MAIN_MODULE_STOCK')) ) {
    $mstock['m2'] = $mstock['m1'];
  } elseif ( $mstock['stock'] > $mstock['qty'] ) {
    $mstock['m2'] = $mstock['stock'];
  } else { $mstock['m2'] = $mstock['qty']; }
} else {
  $mstock['m0'] = 1;
  if ( isset($line) && $line->qty > 1 ) { $mstock['m2'] = $mstock['qty']; }
  else { $mstock['m2'] = 1; }
} 

if (!$nohtml) { 
$stock = '<script>';
$stock .= 'jQuery(document).ready(function($) {
$("#popover-stock-'.$product->id.'").popover({
placement : "auto",
delay: { "show": 150, "hide": 150 },
trigger : "focus",
html : true
})
});';
$stock .= '</script>';
}

if ( ! is_object($product) || empty(doliconst('MAIN_MODULE_STOCK', $refresh)) || (!empty($product->type) && empty(doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) ) || (empty($product->type) && !empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) && empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER', $refresh)) )) {
  if (!$nohtml) $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-success text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available', 'doliconnect')."' data-bs-content='".__( 'This item is available and can be order', 'doliconnect')."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
  $mstock['m0'] = 1*$mstock['step'];
  $mstock['m1'] = get_option('dolicartlist')*$mstock['step'];
  $mstock['m2'] = $mstock['m1'];
} elseif (empty($product->type) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) && empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER', $refresh)) && isset($product->array_options->options_unlimitedsale) && !empty($product->array_options->options_unlimitedsale)) {
  if (!$nohtml) $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-info text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available', 'doliconnect')."' data-bs-content='".__( 'This item is available and can be order but it can sometimes be briefly unavailable', 'doliconnect')."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
  $mstock['m0'] = 1*$mstock['step'];
  $mstock['m1'] = get_option('dolicartlist')*$mstock['step'];
  $mstock['m2'] = $mstock['m1'];
  $mstock['stock'] = $mstock['m2'];
} elseif (!$nohtml) {
if ( $mstock['stock'] <= 0 || (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging) && $mstock['stock'] < $product->array_options->options_packaging) ) { 
  $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-dark text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Not available', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is out of stock and can not be ordered or shipped. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Not available', 'doliconnect')."</a>"; }  
elseif ( ($mstock['stock'] <= 0 || (isset($product->array_options->options_packaging) && $mstock['stock'] < $product->array_options->options_packaging)) && $product->stock_theorique > $mstock['stock'] ) { 
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
} elseif ( $mstock['stock'] >= 0 && $mstock['stock'] <= $product->seuil_stock_alerte ) { 
  $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-warning text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Limited availability', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is in stock and can be shipped immediately but only in limited quantities. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect')."</a>";
} else {
  $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-success text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available immediately', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is in stock and can be shipped immediately. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
}
} 

if ($nohtml) { 
  return $mstock;
} else {
  return $stock;
}

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

function doliconnect_CartItemsList($order = null) {
global $current_user;
if (empty($order)) $order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order')."?contact_list=0", null, dolidelay('order'));

if ( isset($order->lines) && $order->lines != null ) {
$ln = '<table class="table table-hover table-sm"><thead><tr>
<th scope="col" width="40px">'.__( 'Qty', 'doliconnect').'</th><th scope="col">'.__( 'Item', 'doliconnect').'</th></tr></thead><tbody>';
foreach ( $order->lines as $line ) { 
$ln .= '<tr><td scope="row">'.$line->qty.'</td><td><small>'.doliproduct($line, 'product_label');
if ( !empty(get_option('doliconnectbeta')) ) $ln .= '<div class="float-end"><a type="button" onclick="doliDeleteLine('.$line->id.')"><i class="fa-solid fa-trash-can"></i></a></div>';
$ln .= '</small></td></tr>';
}
$ln .= '</tbody><tfoot><tr><th colspan="2" class="table-active">'.__( 'Total to be paid', 'doliconnect').' '.doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null).'</th></tr></tfoot></table><div class="dropdown mt-3">
<div class="d-grid gap-2">';
$ln .= '<a class="btn btn-primary" role="button" href="'.esc_url(doliconnecturl('dolicart')).'" >'.__( 'Finalize the order', 'doliconnect').'</a>';
if ( !empty(get_option('doliconnectbeta')) ) $ln .= '<button type="button" class="btn btn-outline-secondary" onclick="doliPurgeCart('.$order->id.')">'.__( 'Empty the basket', 'doliconnect').'</button>';
$ln .= '</div></div>';
return $ln;
} else {
return '<center class="p-3 text-muted">'.__( 'Your basket is empty', 'doliconnect').'</center>';
}
}

function doliaddtocart($product, $mstock, $quantity, $price, $timestart = null, $timeend = null, $url = null, $array_options = array()) {
global $current_user;

$response = array();

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

if (empty($product->status)) {

if (!empty($mstock['line'])) $deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$mstock['line'], null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
//$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
//delete_transient( 'doliconnect_cartlinelink_'.$mstock['line'] );

$response['message'] = __( 'This item has been deleted to basket', 'doliconnect');
$response['items'] = doliconnect_countitems($order);
$response['lines'] = doliline($order);
$response['list'] = doliconnect_CartItemsList($order);
$response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
return $response;

} elseif ( doliconnector($current_user, 'fk_order') > 0 && $quantity > 0 && empty($mstock['line'])) {
                                                                                     
$adln = [
    'fk_product' => $product->id,
    'desc' => $product->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $product->tva_tx, 
    'remise_percent' => $price['discount'],
    'subprice' => $price['subprice'],
    'array_options' => $array_options
	];                 
$addline = callDoliApi("POST", "/orders/".doliconnector($current_user, 'fk_order')."/lines", $adln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
//$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', true));
if ( !empty($url) ) {
//set_transient( 'doliconnect_cartlinelink_'.$addline, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
}
$response['message'] = __( 'This item has been added to basket', 'doliconnect');
$response['items'] = doliconnect_countitems($order);
$response['lines'] = doliline($order);
$response['list'] = doliconnect_CartItemsList($order);
$response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
return $response;

} elseif ( doliconnector($current_user, 'fk_order') > 0 && $mstock['line'] > 0 ) {

if ( $quantity < 1 ) {

$deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$mstock['line'], null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
//$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', true));
//delete_transient( 'doliconnect_cartlinelink_'.$mstock['line'] );

$response['message'] = __( 'This item has been deleted to basket', 'doliconnect');
$response['items'] = doliconnect_countitems($order);
$response['lines'] = doliline($order);
$response['list'] = doliconnect_CartItemsList($order);
$response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
return $response;
 
} else {

$uln = [
    'desc' => $product->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $product->tva_tx, 
    'remise_percent' => $price['discount'],
    'subprice' => $price['subprice'],
    'array_options' => $array_options
	];                  
$updateline = callDoliApi("PUT", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$mstock['line'], $uln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
//$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', true));
if ( !empty($url) ) {
//set_transient( 'doliconnect_cartlinelink_'.$mstock['line'], esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
} else {
//delete_transient( 'doliconnect_cartlinelink_'.$mstock['line'] );

}
$response['message'] = __( 'Quantities have been changed', 'doliconnect');
$response['items'] = doliconnect_countitems($order);
$response['lines'] = doliline($order);
$response['list'] = doliconnect_CartItemsList($order);
$response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
return $response;

}
} elseif ( doliconnector($current_user, 'fk_order') > 0 && is_null($mstock['line']) ) {

return doliconnect_countitems($order);

} else {

return $mstock['stock'];

}
}

function doliProductCart($product, $refresh = null, $line = null) {

$button = '<form id="doliform-product-'.$product->id.'" method="post">';

$button .= "<script>";
$button .= 'jQuery(document).ready(function($) {
      $("#doliform-product-'.$product->id.' button[type=submit]").on("click", function(e) {
          e.preventDefault();
          var acase = $(this).val();
          $("#DoliconnectLoadingModal").modal("show");
          $.ajax({
              url :"'.admin_url('admin-ajax.php').'",
              type:"POST",
              cache:false,
              data: {
                "action": "dolicart_request",
                "dolicart-nonce": "'.wp_create_nonce( 'dolicart-nonce').'",
                "case": "updateline",
                "productId" : "'.$product->id.'",
                "qty" : document.getElementById("qty-prod-'.$product->id.'").value,
                "modify" : acase
              },
          }).done(function(response) {
              if (response.success) { 
                console.log(response.data.message);
                if (document.getElementById("qty-prod-'.$product->id.'")) {
                  document.getElementById("qty-prod-'.$product->id.'").value = response.data.newqty;
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
                $("#offcanvasDolicart").offcanvas("show");  
              } else {
                //console.log("error updating qty " + response.data.message);
              }
              $("#DoliconnectLoadingModal").modal("hide");
          });
  
      });
  });';
  $button .= "</script>";

  $mstock = doliProductStock($product, $refresh, true);

  $button .= '<div class="input-group">';
if ($mstock['stock'] <= 0 || $mstock['m2'] < $mstock['step'])  { 
  $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Unavailable', 'doliconnect').'" aria-label="'.__( 'Unavailable', 'doliconnect').'" style="text-align:center;" disabled readonly>';
} else {
  $button .= '<button class="btn btn-sm btn-warning" name="minus" value="minus" type="submit"><i class="fa-solid fa-minus" ></i></button>
  <input id="qty-prod-'.$product->id.'" type="number" class="form-control form-control-sm" placeholder="" aria-label="Quantity" value="'.$mstock['qty'].'" style="text-align:center;" readonly>
  <button class="btn btn-sm btn-warning" name="plus" value="plus" type="submit"><i class="fa-solid fa-plus"></i></button>';
  if ( !empty(doliconst('MAIN_MODULE_WISHLIST')) && !empty(get_option('doliconnectbeta')) ) {
    $button .= '<button class="btn btn-sm btn-light" name="wish" value="wish" type="submit"><i class="fas fa-heart" style="color:Fuchsia"></i></button>';
  }
}
  $button .= '</div>';
  $button .= '</form>';
  return $button;
}

function doliProductPrice($product, $quantity = null, $refresh = false, $nohtml = false) {
global $current_user;

$button = null;
$price = array();

if (doliconnector($current_user, 'fk_order') > 0) {
  $orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order'), null, $refresh);
}
$currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:strtoupper(doliconst("MAIN_MONNAIE", $refresh));

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {
if ( $product->duration_unit == 'i' ) {
$altdurvalue=60/$product->duration_value; 
}
}

$price['discount'] = !empty(doliconnector($current_user, 'remise_percent'))?doliconnector($current_user, 'remise_percent'):'0';
$customer_discount = $price['discount'];

if ( !empty(doliconst("PRODUIT_MULTIPRICES", $refresh)) && !empty($product->multiprices_ttc) ) {
$lvl=doliconnector($current_user, 'price_level');
//$button .=$lvl;

if (!empty(doliconnector($current_user, 'price_level'))) {
$level=doliconnector($current_user, 'price_level');
} else {
$level=1;
}
 
$price_min_ttc = $product->multiprices_min_ttc->$level; 
$price_min_ht = $product->multiprices_min->$level;  
$price_ttc = $product->multiprices_ttc->$level;
$price_ht = $product->multiprices->$level; 
$price_min_ttc3 = $product->multiprices_min_ttc->$level;
$price_min_ht3 = $product->multiprices_min->$level;  
$price_ttc3 = $product->multiprices_ttc->$level;
$price_ht3 = $product->multiprices->$level;
$vat = $product->tva_tx;
$refprice=(empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht);

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
$button .= '</tbody></table>';
} else {

if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES", $refresh)) && doliconnector($current_user, 'fk_soc', $refresh) > 0 ) {
$product2 = callDoliApi("GET", "/products/".$product->id."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
if ( !isset($product2->error) && $product2 != null ) {
$new_product2 = array_filter($product2, function($obj){

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
$price['discount'] = $product3[0]->discount;
} elseif (!empty($product3[0]->price)) {
$price_ht3=$product3[0]->price; 
$price_ht=$product->price; 
$price['discount'] = 100-(100*$price_ht3/$price_ht);
$price_ttc3=$product->price_ttc-($product->price_ttc*$price['discount']/100);
$price_ttc=$product->price_ttc;
$vat = $product->tva_tx;
} elseif (!empty($product3[0]->price_ttc)) {
$price_ttc3=$product3[0]->price_ttc; 
$price_ttc=$product->price_ttc; 
$price['discount'] = 100-(100*$price_ttc3/$price_ttc);
$price_ht3=$product->price-($product->price*$price['discount']/100);
$price_ht=$product->price;
$vat = $product->tva_tx;
}
$price_min_ttc=$product->price_min_ttc;
$refprice=(empty(get_option('dolibarr_b2bmode'))?$price_ttc3:$price_ht3);

if (!empty($product3[0]->label)) {
$discountlabel = $product3[0]->label;
}

} elseif ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES", $refresh)) && isset($product2) && !empty($product2) && !isset($product2->error) ) {
  $price_min_ttc3=$product->price_min_ttc-($product2->price_min_ttc*$price['discount']/100);
  $price_ttc3=$product->price_ttc-($product2->price_ttc*$price['discount']/100);
  $price_ht3=$product->price-($product2->price*$price['discount']/100);
  $price_min_ttc=$product2->price_min_ttc;
  $price_min_ht=$product2->price_min;
  $price_ttc=$product2->price_ttc;
  $price_ht=$product2->price;
  $vat = $product2->tva_tx;
  $refprice = (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht);
} else {
  $price_min_ttc3=$product->price_min_ttc-($product->price_min_ttc*$price['discount']/100);
  $price_ttc3=$product->price_ttc-($product->price_ttc*$price['discount']/100);
  $price_ht3=$product->price-($product->price*$price['discount']/100);
  $price_min_ttc=$product->price_min_ttc;
  $price_min_ht=$product->price_min;
  $price_ttc=$product->price_ttc;
  $price_ht=$product->price;
  $vat=$product->tva_tx;
  $refprice = (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht);
}

if ($price_min_ttc == $price_ttc) {
$price['discount'] = 0;
$price_ttc3 = $price_min_ttc;
$price_ht3 = $price_min_ht;
} elseif ($price_min_ttc > ($price_ttc-($price_ttc*$price['discount']/100))) {
$price['discount'] = 100-(100*$price_min_ttc/$price_ttc);
$price_ttc3 = $price_ttc-($price_ttc*$price['discount']/100);
$price_ht3 = $price_ht-($price_ht*$price['discount']/100);
}

}

$price['subprice'] = $price_ht3;

if ($nohtml) { 
  return $price;
} else {
$button = '<script>';
$button .= 'jQuery(document).ready(function($) {
$("#popover-price-'.$product->id.'").popover({
placement : "auto",
delay: { "show": 150, "hide": 150 },
trigger : "focus",
html : true
})
});';
$button .= '</script>';

$explication = (empty(get_option('dolibarr_b2bmode'))?__( 'Displayed price is included VAT', 'doliconnect'):__( 'Displayed price is excluded VAT', 'doliconnect'));
$explication .= sprintf(__( 'VAT rate of %s', 'doliconnect'), $vat);
//$explication .= "<ul>";
$explication .= sprintf(__( 'Initial sale price: %s', 'doliconnect'), doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht, $currency));
if (isset($customer_discount) && !empty($customer_discount) && !empty($price['discount'])) $explication .= sprintf(__( 'Your customer discount is %s percent', 'doliconnect'), $customer_discount);
if (isset($discountlabel) && !empty($discountlabel)) $explication .= $discountlabel;
if ($price_ttc != $price_ttc3) $explication .= sprintf(__( 'Discounted price: %s', 'doliconnect'), doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc3:$price_ht3, $currency));
//$explication .= "</ul>";
$button .= "<a tabindex='0' id='popover-price-".$product->id."' class='btn btn-light position-relative top-0 end-0";
if (!empty($price['discount'])) $button .= " text-danger";
$button .= "' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'About price', 'doliconnect')."' data-bs-content='".$explication."'>";
$button .= doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc3:$price_ht3, $currency);

$date = new DateTime(); 
$date->modify('NOW');
if (!empty(get_option('dolicartnewlist')) && get_option('dolicartnewlist') != 'none') { 
  $date->modify('FIRST DAY OF LAST '.get_option('dolicartnewlist').' MIDNIGHT');
  $lastdate = $date->format('Y-m-d');
} elseif (empty(get_option('dolicartnewlist'))) {
  $date->modify('FIRST DAY OF LAST MONTH MIDNIGHT');
  $lastdate = $date->format('Y-m-d');
} else {
  $lastdate = $date->format('Y-m-d');
}
if ($product->date_creation >= $lastdate) $button .= '<span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-warning">'.__( 'Novelty', 'doliconnect').'<span class="visually-hidden">Novelty</span></span>';
if (!empty($price['discount'])) $button .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">-'.$price['discount'].'%<span class="visually-hidden">discount</span></span>';
if (!empty($product->net_measure) && !empty($product->net_measure_units)) { 
  $unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
  $button .= '<span class="position-absolute top-100 start-0 translate-middle badge rounded-pill bg-info"><small>'.doliprice( $refprice/$product->net_measure, null, $currency).'/'.$unit[0]->short_label.'<span class="visually-hidden">net measure price</span></small></span>';
}
if (!empty($price['discount'])) $button .= '<span class="position-absolute top-100 start-100 translate-middle badge bg-light text-dark"><small><s>'.doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht, $currency).'</s><span class="visually-hidden">initial price</span></small></span>';
$button .= '</a><br><br>';

/*
//if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); } 
//if ( !empty($altdurvalue) ) { $button .= "<tr><td class='text-end'>soit ".doliprice( $altdurvalue*$product->price_ttc, null, $currency)." par ".__( 'hour', 'doliconnect')."</td></tr>"; } 
//if (!empty($product->net_measure) && !empty($product->net_measure_units)) { 
//  $unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
//  $button .= '<span class="badge rounded-pill bg-light text-dark">'.$product->net_measure;
//  if (!empty($unit)) $button .= " ".$unit[0]->short_label;
//  $button .= '</span> ';
//}
  
$button .= '<div class="d-grid gap-2">';
$arr_params = array( 'redirect_to' => doliconnecturl('dolishop'));
$loginurl = esc_url( add_query_arg( $arr_params, wp_login_url( )) );

if ( get_option('doliloginmodal') == '1' ) {       
$button .= '<a href="#" data-bs-toggle="modal" class="btn btn-sm btn-outline-secondary" data-bs-target="#DoliconnectLogin" data-bs-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect').'</a>';
} else {
$button .= "<a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-sm btn-outline-secondary' >".__( 'log in', 'doliconnect').'</a>';
}

$button .= "<div id='message-doliproduct-".$product->id."'></div>";
*/

  return $button;
}}

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
$list .= "<br>".doliProductStock($product);
}
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$list .= "<br><small><span class='fi fi-".strtolower($product->country_code)."'></span> ".$country->label;
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
$list .= doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$list .= doliProductCart($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
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
$card .= '<br>'.doliProductStock($product);
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
$card .= " <span class='fi fi-".strtolower($product->country_code)."'></span></small>"; }
if( has_filter('mydoliconnectproductdesc') ) {
$card .= apply_filters('mydoliconnectproductdesc', $product, 'card');
}
if ( ! empty(doliconnectid('dolicart')) ) { 
$card .= '<br><br><div class="jumbotron">';
$card .= doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$card .= doliProductCart($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
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
