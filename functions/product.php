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

function doliproductstock($product) {

$enablestock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));
$stockservices = callDoliApi("GET", "/doliconnector/constante/STOCK_SUPPORTS_SERVICES", null, dolidelay('constante'));

if ( ! is_object($product) || empty($enablestock->value) || ($product->type != '0' && ! is_object($stockservices->value)) ) {
$stock = "<span class='badge badge-pill badge-success'>".__( 'Available', 'doliconnect' )."</span>"; 
} else {

$minstock = min(array($product->stock_reel, $product->stock_theorique));
$maxstock = max(array($product->stock_reel, $product->stock_theorique));

if ( $maxstock <= 0 ) { $stock = "<span class='badge badge-pill badge-dark'>".__( 'Out of stock', 'doliconnect' )."</SPAN>"; }
elseif ( $minstock < 0 && $maxstock > 0 ) { $stock = "<span class='badge badge-pill badge-secondary'>".__( 'Replenishment', 'doliconnect' )."</span>"; }
elseif ( $minstock >= 0 && $maxstock <= $product->seuil_stock_alerte ) { $stock = "<span class='badge badge-pill badge-danger'>".__( 'Limited stock', 'doliconnect' )."</span>"; }
else { $stock = "<span class='badge badge-pill badge-success'>".__( 'In stock', 'doliconnect' )."</span>"; }
}

return $stock;
}

function doliproducttocart($product, $category=0, $add=0, $time=0) {
global $current_user;

$order = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, dolidelay('constante'));
$enablestock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));
$stockservices = callDoliApi("GET", "/doliconnector/constante/STOCK_SUPPORTS_SERVICES", null, dolidelay('constante'));

$button = "<div class='jumbotron'>";

if (doliconnector($current_user, 'fk_order') > 0) {
$orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order'), null, 0);
//$button .=$orderfo;
}

if ( isset($orderfo) && $orderfo->lines != null ) {
foreach ($orderfo->lines as $line) {
if  ($line->fk_product == $product->id) {
//$button = var_dump($line);
$qty=$line->qty;
$ln=$line->id;
}
}}

if (!isset($qty) ) {
$qty=null;
$ln=null;
}

$button .="<form id='product-add-form-$product->id' role='form' action='".doliconnecturl('dolishop')."?category=".$category."&product=".$product->id."'  method='post'>";

$button .= doliloaderscript('product-add-form-'.$product->id.'');

$button .="<input type='hidden' name='product_update' value='$product->id'><input type='hidden' name='product_update[".$product->id."][product]' value='$product->id'>";
$button .="<script type='text/javascript' language='javascript'>";

$button .="</script>";


$currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:'eur';

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {

if ( $product->duration_unit == 'i' ) {
$altdurvalue=60/$product->duration_value; 
}

}

if ( !empty($product->multiprices_ttc) ) {
$lvl=doliconnector($current_user, 'price_level');
$count=1;
//$button .=$lvl;
foreach ( $product->multiprices_ttc as $level => $price ) {
if ( (doliconnector($current_user, 'price_level') == 0 && $level == 1 ) || doliconnector($current_user, 'price_level') == $level ) {
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect-pro' ).': '.doliprice( $price, $currency);
if ( empty($time) ) { $button .=' '.doliduration($product); }
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$price, $currency)." par ".__( 'hour', 'doliconnect-pro' )."</h6>"; } 
$button .= '<small class="float-right">'.__( 'You benefit from the rate', 'doliconnect-pro' ).' '.doliconst(PRODUIT_MULTIPRICES_LABEL.$level).'</small>';
}
$count++; 
}
} else {
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect-pro' ).': '.doliprice( $product->price_ttc, $currency);
if ( empty($time) && isset($product->duration) ) { $button .=' '.doliduration($product); } 
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$product->price_ttc, $currency)." par ".__( 'hour', 'doliconnect-pro' )."</h6>"; } 

}

if (doliconnector($current_user, 'price_level') > 0){
$level=doliconnector($current_user, 'price_level');
$price_min_ttc=$product->multiprices_min_ttc->$level;
$price_ttc=$product->multiprices_ttc->$level;
}
else {
$price_min_ttc=$product->price_min_ttc;
$price_ttc=$product->price_ttc;
}
//$button .=doliprice($price_ttc);

if ( is_user_logged_in() && $add==1 && is_object($order) && $order->value == 1 && doliconnectid('dolicart') > 0 ) {
$button .= "<div class='input-group'><select class='form-control' name='product_update[".$product->id."][qty]' ";
if ( empty($product->stock_reel) && $product->type == '0' && (is_object($enablestock) && $enablestock->value == 1)) { $button .= " disabled"; }
$button .= ">";
if ( ($product->stock_reel-$qty > '0' && $product->type == '0') ) {
if ( $product->stock_reel-$qty >= '10' || (is_object($enablestock) && $enablestock->value != 1) ) {
$m2 = 10;
} elseif ( $product->stock_reel > $line->qty ) {
$m2 = $product->stock_reel;
} else { $m2 = $qty; }
} else {
if ( isset($line) && $line->qty > 1 ) { $m2 = $qty; }
else { $m2 = 1; }
}
for ( $i=0;$i<=$m2;$i++ ) {
		if ( $i == $qty ) {
$button .= "<OPTION value='$i' selected='selected'>$i</OPTION>";
		} else {
$button .= "<OPTION value='$i' >$i</OPTION>";
		}
	}
$button .= "</SELECT><DIV class='input-group-append'><BUTTON class='btn btn-outline-secondary' type='submit' ";
if ( empty($product->stock_reel) && $product->type == '0' && (is_object($enablestock) && $enablestock->value == 1)) { $button .= " disabled"; }
$button .= ">";
if ( $qty > 0 ) {
$button .= __( 'Update', 'doliconnect-pro' )."";
} else {
$button .= __( 'Add', 'doliconnect-pro' )."";
}
$button .= "</button></div></div>";
if ( $qty > 0 ) {
$button .= "<br /><div class='input-group'><a class='btn btn-block btn-warning' href='".doliconnecturl('dolicart')."' role='button' title='".__( 'Go to cart', 'doliconnect-pro')."'>".__( 'Go to cart', 'doliconnect-pro')."</a></div>";
}
} elseif ( $add == 1 && doliconnectid('dolicart') > 0 ) {
$arr_params = array( 'redirect_to' => doliconnecturl('dolishop'));
$loginurl = esc_url( add_query_arg( $arr_params, wp_login_url( )) );

if ( get_option('doliloginmodal') == '1' ) {       
$button .= '<div class="input-group"><a href="#" data-toggle="modal" class="btn btn-block btn-outline-secondary" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect-pro').'</a></div>';
} else {
$button .= "<div class='input-group'><a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-block btn-outline-secondary' >".__( 'log in', 'doliconnect-pro').'</a></div>';
}

//$button .="<div class='input-group'><a class='btn btn-block btn-outline-secondary' href='".$loginurl."' role='button' title='".__( 'Login', 'doliconnect-pro' )."'>".__( 'Login', 'doliconnect-pro')."</a></div>";
} else {
$button .= "<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect-pro')."'>".__( 'Contact us', 'doliconnect-pro')."</a></div>";
}

if ( !empty(doliconnector($current_user, 'remise_percent')) ) { $button .= "<small>".sprintf( esc_html__( 'you get %u %% discount', 'doliconnect-pro'), doliconnector($current_user, 'remise_percent'))."</small>"; }
$button .= "<input type='hidden' name='product_update[".$product->id."][price]' value='$price_ttc'></form>";
$button .= '<div id="product-add-loading-'.$product->id.'" style="display:none">'.doliprice($price_ttc).'<button class="btn btn-secondary btn-block" disabled><i class="fas fa-spinner fa-pulse fa-1x fa-fw"></i> '.__( 'Loading', 'doliconnect-pro').'</button></div>';
$button .= "</div>";
return $button;
}

function dolisummarycart($object) {
global $current_user;

$remise=0;
$subprice=0;
$qty=0;

if ( $object->lines != null ) {
$list = null;
foreach ($object->lines as $line) {
//$product = callDoliApi("GET", "/products/".$post->product_id."?includestockdata=1", null, 0);
$list .= "<li class='list-group-item d-flex justify-content-between lh-condensed'><div><h6 class='my-0'>".$line->libelle."</h6><small class='text-muted'>".__( 'Quantity', 'doliconnect-pro' ).": ".$line->qty."</small></div>";
$remise+=$line->subprice-$line->total_ht;
$subprice+=$line->subprice;
$qty+=$line->qty;
$list .= "<span class='text-muted'>".doliprice($line, 'total_ttc',isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</span></li>";
}
}

$cart = "<div class='card'><div class='card-header'>".__( 'Cart', 'doliconnect-pro' )." - ".sprintf( _n( '%s item', '%s items', $qty, 'doliconnect-pro' ), $qty);
if ( !isset($object->resteapayer) && $object->statut == 0 ) { $cart .= " <small>(<a href='".doliconnecturl('dolicart')."' >".__( 'update', 'doliconnect-pro' )."</a>)</small>"; }
$cart .= "</div><ul class='list-group list-group-flush'>";
$cart .= $list;

if ( doliconnector($current_user, 'remise_percent') > 0 && $remise > 0 ) { 
$remise_percent = (0*doliconnector($current_user, 'remise_percent'))/100;
$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>
<div class='text-success'><small class='my-0'>".__( 'Discount', 'doliconnect-pro' )."</small>";
//$cart .= "<br><small>-".number_format(100*$remise/$subprice, 0)." %</small>";
$cart .= "</div><small class='text-success'>-".doliprice($remise, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";
}

$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>";
$cart .= "<small>".__( 'VAT', 'doliconnect-pro' )."</small>";
$cart .= "<small>".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";

//$total=$subtotal-$remise_percent;            
$cart .= "<li class='list-group-item d-flex justify-content-between'>";
if ( isset($object->resteapayer) ) { 
$cart .= "<span>".__( 'Already paid', 'doliconnect-pro' )."</span>";
$cart .= "<strong>".doliprice($object->total_ttc-$object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
$cart .= "<li class='list-group-item d-flex justify-content-between'>";
$cart .= "<span>".__( 'Remains to be paid', 'doliconnect-pro' )."</span>";
$cart .= "<strong>".doliprice($object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
} else {
$cart .= "<span>".__( 'Total to pay', 'doliconnect-pro' )."</span>";
$cart .= "<strong>".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
}
$cart .= "</ul></div><br>";
return $cart;
}
?>