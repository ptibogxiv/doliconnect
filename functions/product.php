<?php

function doliproduct($object, $value) {

if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
return !empty($object->multilangs->$lang->$value) ? $object->multilangs->$lang->$value : $object->$value;
} else {
return $object->$value;
}

}

function doliprice($object, $mode = "ttc", $currency = null) {
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

//$$objet->multicurrency_code
if ( is_null($currency) ) { $currency = strtoupper(doliconst("MAIN_MONNAIE", dolidelay('constante'))); }
if ( function_exists('pll_the_languages') ) { 
$locale = pll_current_language('locale');
} else { if ( $current_user->locale == null ) { $locale = get_locale(); } else { $locale = $current_user->locale; } }
$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
return numfmt_format_currency($fmt, $montant, $currency);//.$decimal
}

function doliproductstock($product, $refresh = false) {

$stock = "<script>";
$stock .= "(function ($) {
$(document).ready(function(){
$('#popover-".$product->id."').popover({
placement : 'auto',
delay: { 'show': 150, 'hide': 150 },
trigger : 'focus',
html : true
})
});
})(jQuery);";
$stock .= "</script>";

if ( ! is_object($product) || empty(doliconst('MAIN_MODULE_STOCK')) || ($product->type != '0' && empty(doliconst('STOCK_SUPPORTS_SERVICES')) )) {
$stock .= "<a tabindex='0' id='popover-".$product->id."' class='badge badge-pill badge-success text-white' data-container='body' data-toggle='popover' data-trigger='focus' title='".__( 'Available immediately', 'doliconnect')."' data-content='".sprintf( __( 'This item is in stock and can be send immediately. %s', 'doliconnect'), '')."'><i class='fas fa-warehouse'></i> ".__( 'Available immediately', 'doliconnect').'</a>';
} else {

$minstock = min(array($product->stock_theorique,$product->stock_reel));
$maxstock = max(array($product->stock_theorique,$product->stock_reel));

if (!empty(doliconnectid('dolishipping'))) {
$shipping = '<a href="'.doliconnecturl('dolishipping').'" class="btn btn-link btn-block btn-sm">'.__( 'Shipping', 'doliconnect').'</a>';
} else {
$shipping = null;
}

if ( $maxstock <= 0 || (isset($product->array_options->options_packaging) && $maxstock < $product->array_options->options_packaging ) ) { $stock .= "<a tabindex='0' id='popover-".$product->id."' class='badge badge-pill badge-dark text-white' data-container='body' data-toggle='popover' data-trigger='focus' title='".__( 'Not available', 'doliconnect')."' data-content='".sprintf( __( 'This item is out of stock and can not be ordered or shipped. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Not available', 'doliconnect')."</a>"; }  
elseif ( ($minstock <= 0 || (isset($product->array_options->options_packaging) && $product->stock_reel < $product->array_options->options_packaging)) && $maxstock >= 0 && $product->stock_theorique > $product->stock_reel ) { 
$delay =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product', $refresh));
if (empty($delay[0]->delivery_time_days)) { $delay = esc_html__( 'few', 'doliconnect'); } else { $delay = $delay[0]->delivery_time_days;}
if (doliversion('12.0.0')) {
$datelivraison =  callDoliApi("GET", "/supplierorders?sortfield=t.date_livraison&sortorder=ASC&limit=1&product_ids=".$product->id."&sqlfilters=(t.fk_statut%3A%3D%3A'2')", null, dolidelay('order', $refresh));
if (isset($datelivraison[0]->date_livraison) && !empty($datelivraison[0]->date_livraison)) {
$next = sprintf( "<br>".esc_html__( 'Reception scheduled on %s.', 'doliconnect'), wp_date('d/m/Y', $datelivraison[0]->date_livraison));
} else {
$next = null;
}
} else {
$next = null;
}
$stock .= "<a tabindex='0' id='popover-".$product->id."' class='badge badge-pill badge-danger text-white' title='".__( 'Available soon', 'doliconnect')."' data-container='body' data-toggle='popover' data-trigger='focus' data-content='".sprintf( __( 'This item is not in stock but should be available soon within %s days. %s %s', 'doliconnect'), $delay, $next, $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available soon', 'doliconnect')."</a>"; 
} elseif ( $minstock >= 0 && $maxstock <= $product->seuil_stock_alerte ) { $stock .= "<a tabindex='0' id='popover-".$product->id."' class='badge badge-pill badge-warning text-white' data-container='body' data-toggle='popover' data-trigger='focus' title='".__( 'Limited availability', 'doliconnect')."' data-content='".sprintf( __( 'This item is in stock and can be shipped immediately but only in limited quantities. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Limited availability', 'doliconnect')."</a>";
} else {
$stock .= "<a tabindex='0' id='popover-".$product->id."' class='badge badge-pill badge-success text-white' data-container='body' data-toggle='popover' data-trigger='focus' title='".__( 'Available immediately', 'doliconnect')."' data-content='".sprintf( __( 'This item is in stock and can be shipped immediately. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available immediately', 'doliconnect').'</a>';
}
} 

return $stock;
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

function doliaddtocart($productid, $quantity = null, $price = null, $remise_percent = null, $timestart = null, $timeend = null, $url = null) {
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
    'date' => mktime(),
    'demand_reason_id' => 1,
    'cond_reglement_id' => $thirdparty->cond_reglement_id,
    'module_source' => 'doliconnect',
    'pos_source' => get_current_blog_id(),
	];                  
$order = callDoliApi("POST", "/orders", $rdr, 0);
}

$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));

if ( $order->lines != null ) {
foreach ( $order->lines as $ln ) {
if ( $ln->fk_product == $productid ) {
//$deleteline = callDoliApi("DELETE", "/orders/".$orderid."/lines/".$ln[id], null, 0);
//$qty=$ln[qty];
$line=$ln->id;
}
}}
if (!$line > 0) { $line=null; }

$prdt = callDoliApi("GET", "/products/".$productid."?includestockdata=1&includesubproducts=true", null, dolidelay('product', true));

if ( doliconnector($current_user, 'fk_order') > 0 && $quantity > 0 && (empty(doliconst('MAIN_MODULE_STOCK')) || $prdt->stock_reel >= $quantity || ($line->type != '0' && empty(doliconst('STOCK_SUPPORTS_SERVICES')) )) && is_null($line) ) {
                                                                                     
$adln = [
    'fk_product' => $prdt->id,
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price
	];                 
$addline = callDoliApi("POST", "/orders/".doliconnector($current_user, 'fk_order')."/lines", $adln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
set_transient( 'doliconnect_cartlinelink_'.$addline, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
}
return doliconnect_countitems($order);

} elseif ( doliconnector($current_user, 'fk_order') > 0 && ($prdt->stock_reel >= $quantity || ($line->type != '0' && empty(doliconst('STOCK_SUPPORTS_SERVICES')) )) && $line > 0 ) {

if ( $quantity < 1 ) {

$deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
delete_transient( 'doliconnect_cartlinelink_'.$line );

return doliconnect_countitems($order);
 
} else {

$uln = [
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price
	];                  
$updateline = callDoliApi("PUT", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, $uln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
set_transient( 'doliconnect_cartlinelink_'.$line, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
} else {
delete_transient( 'doliconnect_cartlinelink_'.$line );

}
return doliconnect_countitems($order);

}
} elseif ( doliconnector($current_user, 'fk_order') > 0 && is_null($line) ) {

return doliconnect_countitems($order);

} else {

return -1;//doliconnect_countitems($order);

}
}

function dolisummarycart($object) {
global $current_user;

$remise=0;
$subprice=0;
$qty=0;

if ( isset($object->lines) && $object->lines != null ) {
$list = null;
foreach ($object->lines as $line) {
//$product = callDoliApi("GET", "/products/".$post->product_id."?includestockdata=1&includesubproducts=true", null, 0);
$list .= "<li class='list-group-item list-group-item-light d-flex justify-content-between lh-condensed'><div><small class='text-muted'>".$line->libelle."</small><br><small class='text-muted'>".__( 'Quantity', 'doliconnect').": ".$line->qty."</small></div>";
$remise+=$line->subprice-$line->total_ht;
$subprice+=$line->subprice;
$qty+=$line->qty;
$list .= "<span class='text-muted'>".doliprice($line, 'total_ttc',isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</span></li>";
}
}

$cart = "<div class='card'><div class='card-header'>".__( 'Cart', 'doliconnect')." - ".sprintf( _n( '%s item', '%s items', $qty, 'doliconnect'), $qty)."</div>";
$cart .= "<ul class='list-group list-group-flush'>";
$cart .= $list;

if ( doliconnector($current_user, 'remise_percent') > 0 && $remise > 0 ) { 
$remise_percent = (0*doliconnector($current_user, 'remise_percent'))/100;
$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>
<div class='text-success'><small class='my-0'>".__( 'including Discount', 'doliconnect')."</small>";
//$cart .= "<br><small>-".number_format(100*$remise/$subprice, 0)." %</small>";
$cart .= "</div><small class='text-success'>-".doliprice($remise, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";
}

$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>";
$cart .= "<small>".__( 'including VAT', 'doliconnect')."</small>";
$cart .= "<small>".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";

//$total=$subtotal-$remise_percent;            
$cart .= "<li class='list-group-item list-group-item-primary d-flex justify-content-between'>";
if ( isset($object->resteapayer) ) { 
$cart .= "<span>".__( 'Already paid', 'doliconnect')."</span>";
$cart .= "<strong>".doliprice($object->total_ttc-$object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
$cart .= "<li class='list-group-item list-group-item-primary d-flex justify-content-between'>";
$cart .= "<span>".__( 'Remains to be paid', 'doliconnect')."</span>";
$cart .= "<strong>".doliprice($object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
} else {
$cart .= "<span>".__( 'Total to pay', 'doliconnect')."</span>";
$cart .= "<strong>".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
}
$cart .= "</ul></div><br>";
return $cart;
}

function doliconnect_addtocart($product, $category = 0, $quantity = 0, $add = 0, $time = 0, $refresh = null) {
global $current_user;

$button = "<form id='form-product-".$product->id."' class='form-product-".$product->id."' method='post' action='".admin_url('admin-ajax.php')."'>";
$button .= "<input type='hidden' name='action' value='doliaddproduct_request'>";
$button .= "<input type='hidden' name='product-add-nonce' value='".wp_create_nonce( 'product-add-nonce-'.$product->id)."'>";
$button .= "<input type='hidden' name='product-add-id' value='".$product->id."'>";

$button .= "<script>";
$button .= 'jQuery(document).ready(function($) {
//jQuery(".dolisavewish'.$product->id.'").click(function(){
//alert("test");
//}
	
	jQuery(".form-product-'.$product->id.'").on("submit", function(e){
  jQuery("#DoliconnectLoadingModal").modal("show");
	e.preventDefault();
	var $form = $(this);
    
jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function(e){ 
		$.post($form.attr("action"), $form.serialize(), function(response){
      if (response.success) {
      if (document.getElementById("DoliHeaderCarItems")) {
      document.getElementById("DoliHeaderCarItems").innerHTML = response.data;
      }
      if (document.getElementById("DoliFooterCarItems")) {  
      document.getElementById("DoliFooterCarItems").innerHTML = response.data;
      }
      if (document.getElementById("DoliWidgetCarItems")) {
      document.getElementById("DoliWidgetCarItems").innerHTML = response.data;      
      }
      document.getElementById("error-product-'.$product->id.'").innerHTML = "";
      } else {
      document.getElementById("error-product-'.$product->id.'").innerHTML = response.data.message;
      }
jQuery("#DoliconnectLoadingModal").modal("hide");
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

$currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:'eur';

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {
if ( $product->duration_unit == 'i' ) {
$altdurvalue=60/$product->duration_value; 
}
}

if ( !empty(doliconst("PRODUIT_MULTIPRICES")) && !empty($product->multiprices_ttc) ) {
$lvl=doliconnector($current_user, 'price_level');
//$button .=$lvl;

if (!empty(doliconnector($current_user, 'price_level'))) {
$level=doliconnector($current_user, 'price_level');
} else {
$level=1;
}
 
$price_min_ttc=$product->multiprices_min->$level; 
$price_ttc=$product->multiprices_ttc->$level;
$price_ht=$product->multiprices->$level;
$vat=$product->tva_tx;

if (isset($add) && $add < 0) {
$button .= '<table class="table table-bordered table-sm"><tbody>'; 
$button .= '<tr><td class="text-right">'.doliprice( (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht), null, $currency)."</td></tr>";
} else {
$button .= '<table class="table table-sm table-striped table-bordered"><tbody>';
foreach ( $product->multiprices_ttc as $level => $price ) {
$button .= '<tr';
if ( (empty(doliconnector($current_user, 'price_level')) && $level == 1 ) || doliconnector($current_user, 'price_level') == $level ) {
$button .= ' class="table-primary"';  
}
$button .= '>';   
$button .= '<td><small>'.__( 'Price', 'doliconnect').' '.$level.' - '.doliconst('PRODUIT_MULTIPRICES_LABEL'.$level).'</small></td>';
$button .= '<td class="text-right"><small>'.doliprice( (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht), null, $currency);
if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); }
$button .= '</small></td>';
if ( !empty($altdurvalue) ) { $button .= "<td class='text-right'>soit ".doliprice( $altdurvalue*(empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht), null, $currency)." par ".__( 'hour', 'doliconnect')."</td>"; } 
//$button .= '<small class="float-right">'.__( 'You benefit from the rate', 'doliconnect').' '.doliconst('PRODUIT_MULTIPRICES_LABEL'.$level).'</small>';
$button .= '</tr>'; 
}
}

$button .= '<tr><td colspan="2"><small><div class="float-left">'.(empty(get_option('dolibarr_b2bmode'))?__( 'Our prices are incl. VAT', 'doliconnect'):__( 'Our prices are excl. VAT', 'doliconnect'));
if (!empty($product->net_measure)) { $button .= '</div><div class="float-right">'.doliprice( (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht)/$product->net_measure, null, $currency);
$unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
if (!empty($unit)) $button .= "/".$unit[0]->short_label; }
$button .= "</div></small></td></tr>";

$button .= '</tbody></table>';
} else {
$button .= '<table class="table table-bordered table-sm table-striped"><tbody>';
$button .= '<tr>'; 
$button .= '<td>'.__( 'Selling Price', 'doliconnect').'</td>';
$button .= '<td class="text-right">'.doliprice( empty(get_option('dolibarr_b2bmode'))?$product->price_ttc:$product->price, null, $currency);
if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); } 
if ( !empty($altdurvalue) ) { $button .= "<td class='text-right'>soit ".doliprice( $altdurvalue*$product->price_ttc, null, $currency)." par ".__( 'hour', 'doliconnect')."</td>"; } 
$button .= '</td>';
$button .= '</tr>'; 

if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES")) && doliconnector($current_user, 'fk_soc') > 0 ) {
$product2 = callDoliApi("GET", "/products/".$product->id."/selling_multiprices/per_customer?thirdparty_id=".doliconnector($current_user, 'fk_soc'), null, dolidelay('product'));
}
if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES")) && isset($product2) && !isset($product2->error) ) {
foreach ( $product2 as $pdt2 ) {
$price_min_ttc=$pdt2->price_min;
$price_ttc=$pdt2->price_ttc;
$price_ht=$pdt2->price;
$vat = $pdt2->tva_tx;
$button .= '<tr class="table-primary">'; 
$button .= '<td>'.__( 'Your price', 'doliconnect').'</td>';
$button .= '<td class="text-right">'.doliprice( empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht, $currency);
if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); } 
if ( !empty($altdurvalue) ) { $button .= "<td class='text-right'>soit ".doliprice( $altdurvalue*(empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht), null, $currency)." par ".__( 'hour', 'doliconnect')."</td>"; } 
$button .= '</td>';
}
} else {
$price_min_ttc=$product->price_min;
$price_ttc=$product->price_ttc;
$price_ht=$product->price;
$vat=$product->tva_tx;
}

$button .= '<tr><td colspan="'.(!empty($altdurvalue)?'3':'2').'"><small><div class="float-left">'.(empty(get_option('dolibarr_b2bmode'))?__( 'Our prices are incl. VAT', 'doliconnect'):__( 'Our prices are excl. VAT', 'doliconnect'));
if (!empty($product->net_measure)) { $button .= '</div><div class="float-right">'.doliprice( (empty(get_option('dolibarr_b2bmode'))?$price_ttc:$price_ht)/$product->net_measure, null, $currency);
$unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
if (!empty($unit)) $button .= "/".$unit[0]->short_label; }
$button .= "</div></small></td></tr>";
$button .= '</tbody></table>';
}

if ( is_user_logged_in() && $add <= 0 && !empty(doliconst('MAIN_MODULE_COMMANDE')) && doliconnectid('dolicart') > 0 ) {
if ( $product->stock_reel-$qty > 0 && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES')) ) ) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m0 = 1*$product->array_options->options_packaging;
$m1 = get_option('dolicartlist')*$product->array_options->options_packaging;
} else {
$m0 = 1;
$m1 = get_option('dolicartlist');
}
if ( $product->stock_reel-$qty >= $m1 || empty(doliconst('MAIN_MODULE_STOCK')) ) {
$m2 = $m1;
} elseif ( $product->stock_reel > $qty ) {
$m2 = $product->stock_reel;
} else { $m2 = $qty; }
} else {
if ( isset($line) && $line->qty > 1 ) { $m2 = $qty; }
else { $m2 = 1; }
} 
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$step = $product->array_options->options_packaging;
} else {
$step = 1;
}
$button .= "<div class='input-group mb-3'><select class='form-control form-control-sm' id='select' name='product-add-qty' ";
if ( ( empty($product->stock_reel) || $m2 < $step) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) ) { $button .= " disabled"; }
$button .= ">";
if ((empty($product->stock_reel) && !empty(doliconst('MAIN_MODULE_STOCK')) && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES')) )) || $m2 < $step)  { $button .= "<OPTION value='0' selected>".__( 'Unavailable', 'doliconnect')."</OPTION>"; 
} elseif (!empty($m2) && $m2 >= $step) {
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
$button .= "</select><div class='input-group-append'>";
if ( !empty(doliconst('MAIN_MODULE_WISHLIST')) && !empty(get_option('doliconnectbeta')) ) {
$button .= "<button class='btn btn-info btn-sm' type='submit' name='cartaction' value='addtowish' title='".esc_html__( 'Save my wish', 'doliconnect')."'><i class='fas fa-save fa-inverse fa-fw'></i></button>";
}
$button .= "<button class='btn btn-warning btn-sm' type='submit' name='cartaction' value='addtocart' title='".esc_html__( 'Add to cart', 'doliconnect')."' ";
if ( ( empty($product->stock_reel) || $m2 < $step) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) ) { $button .= " disabled"; }
$button .= "><i class='fas fa-cart-plus fa-inverse fa-fw'></i></button></div></div>";

//if ( $qty > 0 ) {
//$button .= "<br /><div class='input-group'><a class='btn btn-block btn-warning' href='".doliconnecturl('dolicart')."' role='button' title='".__( 'Go to cart', 'doliconnect')."'>".__( 'Go to cart', 'doliconnect')."</a></div>";
//}
} elseif ( empty($add) && doliconnectid('dolicart') > 0 ) {
$arr_params = array( 'redirect_to' => doliconnecturl('dolishop'));
$loginurl = esc_url( add_query_arg( $arr_params, wp_login_url( )) );

if ( get_option('doliloginmodal') == '1' ) {       
$button .= '<div class="input-group"><a href="#" data-toggle="modal" class="btn btn-block btn-outline-secondary" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect').'</a></div>';
} else {
$button .= "<div class='input-group'><a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-block btn-outline-secondary' >".__( 'log in', 'doliconnect').'</a></div>';
}

//$button .= "<div class='input-group'><a class='btn btn-block btn-outline-secondary' href='".$loginurl."' role='button' title='".__( 'Login', 'doliconnect')."'>".__( 'Login', 'doliconnect')."</a></div>";
} else {
$button .= "<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect')."'>".__( 'Contact us', 'doliconnect')."</a></div>";
}

if ( !empty(doliconnector($current_user, 'remise_percent')) ) { $button .= "<small>".sprintf( esc_html__( 'you get %u %% discount', 'doliconnect'), doliconnector($current_user, 'remise_percent'))."</small>"; }
$button .= "<input type='hidden' name='product-add-vat' value='".$product->tva_tx."'><input type='hidden' name='product-add-remise_percent' value='".doliconnector($current_user, 'remise_percent')."'><input type='hidden' name='product-add-price' value='".$price_ht."'>";
//$button .= '<div id="product-add-loading-'.$product->id.'" style="display:none">'.doliprice($price_ttc).'<button class="btn btn-secondary btn-block" disabled><i class="fas fa-spinner fa-pulse fa-1x fa-fw"></i> '.__( 'Loading', 'doliconnect').'</button></div>';

$button .= "</form>";
$button .= "<div id='success-product-".$product->id."' class='text-success font-weight-bolder'></div>";
$button .= "<div id='error-product-".$product->id."' class='text-danger font-weight-bolder'></div>";

return $button;
}

function doliconnect_supplier($product){

$brands =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product'));

$supplier = "";

if ( !isset($brands->error) && $brands != null ) {
$supplier .= "<small><i class='fas fa-building fa-fw'></i> ".__( 'Brand:', 'doliconnect' )." ";
foreach ($brands as $brand) {
$thirdparty =  callDoliApi("GET", "/thirdparties/".$brand->fourn_id, null, dolidelay('product'));
if (!empty(doliconnectid('dolisupplier'))) {
$supplier .= "<a href='".doliconnecturl('dolisupplier')."?supplier=".$thirdparty->id."'>";
}
$supplier .= (!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name);
if (!empty(doliconnectid('dolisupplier'))) {
$supplier .= "</a>";
}

}
$supplier .= "</small>";
}

return $supplier;
}

// list of products filter
function doliproductlist($product) {

$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}

$wish = 0;
if (!empty($product->quantity)) $wish = $product->quantity;
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=".$includestock."&includesubproducts=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$list = "<li class='list-group-item' id='prod-li-".$product->id."'><table width='100%' style='border:0px'><tr><td width='20%' style='border:0px'><center>";
$list .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'150x150'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$list .= "</center></td>";

$list .= "<td width='80%' style='border:0px'><b>".doliproduct($product, 'label')."</b>";
$list .= "<div class='row'><div class='col'><p><small><i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-');
if ( !empty($product->barcode) ) { $list .= " | <i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
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
$list .= "<br><small><span class='flag-icon flag-icon-".strtolower($product->country_code)."'></span> ".$country->label."</small>"; }

$arr_params = array( 'category' => isset($_GET['category'])?$_GET['category']:null, 'subcategory' => isset($_GET['subcategory'])?$_GET['subcategory']:null, 'product' => $product->id);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );
$list .= "<a href='".$return."' class='btn btn-link btn-block'>En savoir plus</a>";
 
$list .= "</p></div>";

if ( ! empty(doliconnectid('dolicart')) ) { 
$list .= "<div class='col-12 col-md-6'><center>";
$list .= doliconnect_addtocart($product, esc_attr(isset($_GET['category'])?$_GET['category']:null), $wish, -1, 0);
$list .= "</center></div>";
}
$list .= "</div></td></tr></table></li>";
return $list;
}
add_filter( 'doliproductlist', 'doliproductlist', 10, 1);

// list of products filter
function doliproductcard($product, $attributes) {

if ($product->id > 0) {

$documents = callDoliApi("GET", "/documents?modulepart=product&id=".$product->id, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$card .= $documents;
$card = "<div class='row'>";
if (defined("DOLIBUG")) {
$card = dolibug();
} elseif ( $product->id>0 && $product->status == 1 ) {
$card .= "<div class='col-12 d-block d-sm-block d-xs-block d-md-none'><center>";
$card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$card .= "</center>";
//$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
$card .= "</div>";
$card .= '<div class="col-md-4 d-none d-md-block"><center>';
$card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$card .= '</center>';
//$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
$card .= "</div>";
$card .= "<div class='col-12 col-md-8'><h6><b>".doliproduct($product, 'label')."</b></h6>";
$card .= "<small><i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-'); 
if ( !empty($product->barcode) ) { $card .= " | <i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
$card .= "</small>";
if ( ! empty(doliconnectid('dolicart')) && !isset($attributes['hideStock']) ) { 
$card .= '<br>'.doliproductstock($product);
}
if (!empty(doliconnect_supplier($product))) $card .= '<br>'.doliconnect_supplier($product);
if (!empty(doliconnect_categories('product', $product, doliconnecturl('dolishop')))) $card .= '<br>'.doliconnect_categories('product', $product, doliconnecturl('dolishop'));
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$card .= "<br><small><i class='fas fa-globe fa-fw'></i> ".__( 'Origin:', 'doliconnect')." <span class='flag-icon flag-icon-".strtolower($product->country_code)."'></span> ".$country->label."</small>"; }
if ( ! empty(doliconnectid('dolicart')) ) { 
$card .= "<br><br><div class='jumbotron'>";
$card .= doliconnect_addtocart($product, 0, 0, isset($attributes['hideButtonToCart']) ? $attributes['hideButtonToCart'] : 0, isset($attributes['hideDuration']) ? $attributes['hideDuration'] : 0);
$card .= "</div>";
}
$card .= "</div><div class='col-12'><h6>".__( 'Description', 'doliconnect' )."</h6><p>".doliproduct($product, 'description')."</p></div>";
} else {
$card .= "<div class='col-12'><p><center>".__( 'Product/Service not in sale', 'doliconnect' )."</center></p></div>";
} 

if( has_filter('mydoliconnectproductcard') ) {
$card .= apply_filters('mydoliconnectproductcard', $product);
}

$card .= "</div>";
} else {
$card .= "<center><br><br><br><br><center><div class='align-middle'><i class='fas fa-bomb fa-7x fa-fw'></i><h4>".__( 'Oops! This product does not appear to exist', 'doliconnect' )."</h4></div></center><br><br><br><br>";
}

return $card;
}
add_filter( 'doliproductcard', 'doliproductcard', 10, 2);

?>
