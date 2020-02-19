<?php

function doliproduct($object, $value) {

if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
return isset($object->multilangs->$lang->$value) ? $object->multilangs->$lang->$value : $object->$value;
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
if ( is_null($currency) ) { $currency = strtoupper(callDoliApi("GET", "/doliconnector/constante/MAIN_MONNAIE", null, dolidelay('constante'))->value); }
if ( function_exists('pll_the_languages') ) { 
$locale = pll_current_language('locale');
} else { if ( $current_user->locale == null ) { $locale = get_locale(); } else { $locale = $current_user->locale; } }
$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
return numfmt_format_currency($fmt, $montant, $currency);//.$decimal
}

function doliconnect_image($module, $object, $mode = null, $refresh = null) {
$img =  callDoliApi("GET", "/documents?modulepart=".$module."&id=".$object->id, null, dolidelay('document', $refresh));
//print var_dump($img);
if ( !isset($img->error) && $img != null ) {
$imgj =  callDoliApi("GET", "/documents/download?modulepart=product&original_file=".$img[0]->level1name."/".$img[0]->relativename, null, 0);
//print var_dump($imgj);
$imgj = (array) $imgj; 
if (is_array($imgj) && $imgj['content-type'] == 'image/jpeg') {
$data = "data:".$imgj['content-type'].";".$imgj['encoding'].",".$imgj['content'];
$image = "<img src='".$data ."' class='img-fluid img-thumbnail'  alt='".$imgj['filename']."'>";
} else {
$image = "<i class='fa fa-cube fa-fw fa-2x'></i>";
}
} else {
$image = "<i class='fa fa-cube fa-fw fa-2x'></i>";
}
return $image;
}

function doliproductstock($product) {

if ( ! is_object($product) || empty(doliconst('MAIN_MODULE_STOCK')) || ($product->type != '0' && empty(doliconst('STOCK_SUPPORTS_SERVICES')) )) {
$stock = "<span class='badge badge-pill badge-success'>".__( 'Available', 'doliconnect')."</span>"; 
} else {

$minstock = min(array($product->stock_theorique,$product->stock_reel));
$maxstock = max(array($product->stock_theorique,$product->stock_reel));

if ( $maxstock <= 0 || (isset($product->array_options->options_packaging) && $maxstock <= $product->array_options->options_packaging ) ) { $stock = "<span class='badge badge-pill badge-dark'><i class='fas fa-warehouse'></i> ".__( 'Out of stock', 'doliconnect')."</span>"; }  
elseif ( ($minstock <= 0 || (isset($product->array_options->options_packaging) && $product->stock_reel < $product->array_options->options_packaging)) && $maxstock >= 0 && $product->stock_theorique > $product->stock_reel ) { $stock = "<span class='badge badge-pill badge-danger'><i class='fas fa-warehouse'></i> ".__( 'Replenishment', 'doliconnect')."</span>"; }
elseif ( $minstock >= 0 && $maxstock <= $product->seuil_stock_alerte ) { $stock = "<span class='badge badge-pill badge-warning'><i class='fas fa-warehouse'></i> ".__( 'Limited stock', 'doliconnect')."</span>"; } 
else { $stock = "<span class='badge badge-pill badge-success'><i class='fas fa-warehouse'></i> ".__( 'In stock', 'doliconnect')."</span>"; }
}
//$stock .= $product->stock_theorique;
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

function doliaddtocart($product, $quantity = null, $price = null, $remise_percent = null, $timestart = null, $timeend = null, $url = null) {
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
    'date_commande' => mktime(),
    'demand_reason_id' => 1,
    'cond_reglement_id' => $thirdparty->cond_reglement_id,
    'module_source' => 'doliconnect',
    'pos_source' => get_current_blog_id(),
	];                  
$order = callDoliApi("POST", "/orders", $rdr, 0);
}

$orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));

if ( $orderfo->lines != null ) {
foreach ( $orderfo->lines as $ln ) {
if ( $ln->fk_product == $product ) {
//$deleteline = callDoliApi("DELETE", "/orders/".$orderid."/lines/".$ln[id], null, 0);
//$qty=$ln[qty];
$line=$ln->id;
}
}}

if (!$line > 0) { $line=null; }

if ( doliconnector($current_user, 'fk_order') > 0 && $quantity > 0 && is_null($line) ) {
$prdt = callDoliApi("GET", "/products/".$product."?includestockdata=1", null, dolidelay('product', true));
$adln = [
    'fk_product' => $product,
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

} elseif ( doliconnector($current_user, 'fk_order') > 0 && $line > 0 ) {

if ( $quantity < 1 ) {

$deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
delete_transient( 'doliconnect_cartlinelink_'.$line );

return doliconnect_countitems($order);
 
} else {

$prdt = callDoliApi("GET", "/products/".$product."?includestockdata=1", null, dolidelay('product', true));
 $ln = [
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price
	];                  
$updateline = callDoliApi("PUT", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, $ln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
set_transient( 'doliconnect_cartlinelink_'.$line, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
} else {
delete_transient( 'doliconnect_cartlinelink_'.$line );
}
return doliconnect_countitems($order);

}
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
//$product = callDoliApi("GET", "/products/".$post->product_id."?includestockdata=1", null, 0);
$list .= "<li class='list-group-item list-group-item-light d-flex justify-content-between lh-condensed'><div><h6 class='my-0'>".$line->libelle."</h6><small class='text-muted'>".__( 'Quantity', 'doliconnect').": ".$line->qty."</small></div>";
$remise+=$line->subprice-$line->total_ht;
$subprice+=$line->subprice;
$qty+=$line->qty;
$list .= "<span class='text-muted'>".doliprice($line, 'total_ttc',isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</span></li>";
}
}

$cart = "<div class='card'><div class='card-header'>".__( 'Cart', 'doliconnect')." - ".sprintf( _n( '%s item', '%s items', $qty, 'doliconnect'), $qty)." <small>(";
if ( !isset($object->resteapayer) && $object->statut == 0 ) { $cart .= "<a href='".doliconnecturl('dolicart')."' >".__( 'update', 'doliconnect')."</a>"; }
else { $cart .= __( 'unchangeable', 'doliconnect'); }
$cart .= ")</small></div><ul class='list-group list-group-flush'>";
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

function dolilistpaymentmodes($paymentintent, $listpaymentmethods, $object, $redirect, $url) {

//PAYMENT REQUEST API
if ( ! empty($object) && get_option('doliconnectbeta')=='1' && !empty($listpaymentmethods->payment_request_api) ) {  
$paymentmethod .= "<li id='PraForm' class='list-group-item list-group-item-action flex-column align-items-start' style='display: none'><div class='custom-control custom-radio'>
<input id='src_pra' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='PRA' ";
//if ($listsource["sources"] == null) { $paymentmethod .= " checked";}
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_pra'>";
//$paymentmethod .= "<div class='row' id='googlepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
//$paymentmethod .= '<center><i class="fab fa-google fa-3x fa-fw" style="color:Black"></i></center>';
//$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Google Pay', 'doliconnect')."</h6>";
//$paymentmethod .= "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect')."</small></div></div>";
$paymentmethod .= "<div class='row' id='applepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fab fa-apple-pay fa-3x fa-fw" style="color:Black"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Apple Pay', 'doliconnect')."</h6>";
$paymentmethod .= "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect')."</small></div></div>";
$paymentmethod .= '</label></div></li>';
}

//alternative payment modes & offline
if ( ! empty($object) ) {

if ( isset($listpaymentmethods->PAYPAL) && $listpaymentmethods->PAYPAL != null && get_option('doliconnectbeta') == '1' && current_user_can( 'administrator' ) ) {
$paymentmethod .= "<li id='PaypalForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_paypal' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='PAYPAL' ";
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_paypal'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fab fa-paypal fa-3x fa-fw" style="color:#2997D8"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>PayPal</h6><small class='text-muted'>".__( 'Redirect to Paypal', 'doliconnect')."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

}
}
?>
