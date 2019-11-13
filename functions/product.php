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
$stock = "<span class='badge badge-pill badge-success'>".__( 'Available', 'doliconnect')."</span>"; 
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
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect' ).': '.doliprice( $price, $currency);
if ( empty($time) ) { $button .=' '.doliduration($product); }
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$price, $currency)." par ".__( 'hour', 'doliconnect' )."</h6>"; } 
$button .= '<small class="float-right">'.__( 'You benefit from the rate', 'doliconnect' ).' '.doliconst(PRODUIT_MULTIPRICES_LABEL.$level).'</small>';
}
$count++; 
}
} else {
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect' ).': '.doliprice( $product->price_ttc, $currency);
if ( empty($time) && isset($product->duration) ) { $button .=' '.doliduration($product); } 
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$product->price_ttc, $currency)." par ".__( 'hour', 'doliconnect' )."</h6>"; } 

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
} elseif ( $product->stock_reel > $qty ) {
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
$button .= __( 'Update', 'doliconnect' )."";
} else {
$button .= __( 'Add', 'doliconnect' )."";
}
$button .= "</button></div></div>";
if ( $qty > 0 ) {
$button .= "<br /><div class='input-group'><a class='btn btn-block btn-warning' href='".doliconnecturl('dolicart')."' role='button' title='".__( 'Go to cart', 'doliconnect')."'>".__( 'Go to cart', 'doliconnect')."</a></div>";
}
} elseif ( $add == 1 && doliconnectid('dolicart') > 0 ) {
$arr_params = array( 'redirect_to' => doliconnecturl('dolishop'));
$loginurl = esc_url( add_query_arg( $arr_params, wp_login_url( )) );

if ( get_option('doliloginmodal') == '1' ) {       
$button .= '<div class="input-group"><a href="#" data-toggle="modal" class="btn btn-block btn-outline-secondary" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect').'</a></div>';
} else {
$button .= "<div class='input-group'><a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-block btn-outline-secondary' >".__( 'log in', 'doliconnect').'</a></div>';
}

//$button .="<div class='input-group'><a class='btn btn-block btn-outline-secondary' href='".$loginurl."' role='button' title='".__( 'Login', 'doliconnect' )."'>".__( 'Login', 'doliconnect')."</a></div>";
} else {
$button .= "<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect')."'>".__( 'Contact us', 'doliconnect')."</a></div>";
}

if ( !empty(doliconnector($current_user, 'remise_percent')) ) { $button .= "<small>".sprintf( esc_html__( 'you get %u %% discount', 'doliconnect'), doliconnector($current_user, 'remise_percent'))."</small>"; }
$button .= "<input type='hidden' name='product_update[".$product->id."][price]' value='$price_ttc'></form>";
$button .= '<div id="product-add-loading-'.$product->id.'" style="display:none">'.doliprice($price_ttc).'<button class="btn btn-secondary btn-block" disabled><i class="fas fa-spinner fa-pulse fa-1x fa-fw"></i> '.__( 'Loading', 'doliconnect').'</button></div>';
$button .= "</div>";
return $button;
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
return $addline;

} elseif ( doliconnector($current_user, 'fk_order') > 0 && $line > 0 ) {

if ( $quantity < 1 ) {

$deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
delete_transient( 'doliconnect_cartlinelink_'.$line );

return $deleteline;
 
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
return $updateline;

}
}
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
$list .= "<li class='list-group-item d-flex justify-content-between lh-condensed'><div><h6 class='my-0'>".$line->libelle."</h6><small class='text-muted'>".__( 'Quantity', 'doliconnect' ).": ".$line->qty."</small></div>";
$remise+=$line->subprice-$line->total_ht;
$subprice+=$line->subprice;
$qty+=$line->qty;
$list .= "<span class='text-muted'>".doliprice($line, 'total_ttc',isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</span></li>";
}
}

$cart = "<div class='card'><div class='card-header'>".__( 'Cart', 'doliconnect' )." - ".sprintf( _n( '%s item', '%s items', $qty, 'doliconnect' ), $qty);
if ( !isset($object->resteapayer) && $object->statut == 0 ) { $cart .= " <small>(<a href='".doliconnecturl('dolicart')."' >".__( 'update', 'doliconnect' )."</a>)</small>"; }
$cart .= "</div><ul class='list-group list-group-flush'>";
$cart .= $list;

if ( doliconnector($current_user, 'remise_percent') > 0 && $remise > 0 ) { 
$remise_percent = (0*doliconnector($current_user, 'remise_percent'))/100;
$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>
<div class='text-success'><small class='my-0'>".__( 'Discount', 'doliconnect' )."</small>";
//$cart .= "<br><small>-".number_format(100*$remise/$subprice, 0)." %</small>";
$cart .= "</div><small class='text-success'>-".doliprice($remise, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";
}

$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>";
$cart .= "<small>".__( 'VAT', 'doliconnect' )."</small>";
$cart .= "<small>".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";

//$total=$subtotal-$remise_percent;            
$cart .= "<li class='list-group-item d-flex justify-content-between'>";
if ( isset($object->resteapayer) ) { 
$cart .= "<span>".__( 'Already paid', 'doliconnect' )."</span>";
$cart .= "<strong>".doliprice($object->total_ttc-$object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
$cart .= "<li class='list-group-item d-flex justify-content-between'>";
$cart .= "<span>".__( 'Remains to be paid', 'doliconnect' )."</span>";
$cart .= "<strong>".doliprice($object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
} else {
$cart .= "<span>".__( 'Total to pay', 'doliconnect' )."</span>";
$cart .= "<strong>".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
}
$cart .= "</ul></div><br>";
return $cart;
}

function dolilistpaymentmodes($paymentintent, $listpaymentmethods, $object, $redirect, $url) {
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
doliconnect_enqueues();

if ( isset($object) ) { 
$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');  
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;
} else {
$currency=strtolower('eur');
$stripeAmount=0;
}

//$lock = dolipaymentmodes_lock();

$paymentmethod = "<script src='https://js.stripe.com/v3/'></script>";

$paymentmethod .= "<div id='payment-errors' class='alert alert-danger' role='alert' style='display: none'></div>";

$paymentmethod .= "<div id='payment-form'><div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if (empty($listpaymentmethods->stripe)) {
$paymentmethod .= "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b></li>";
}

if ( empty($object) ) { //$  &&  ( listsource->discount != 0 || $listsource->discount_product != null )
$paymentmethod .= "<li id='DiscountForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='discount' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='discount' ";
if ( !empty($object) && !current_user_can( 'administrator' ) ) { $paymentmethod .= " disabled "; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='discount'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= "<center><i class='fas fa-piggy-bank fa-3x fa-fw' style='color:HotPink'></i></center>";
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>";
if ( $listpaymentmethods->discount >= 0 ) {
$paymentmethod .= __( 'Credit of', 'doliconnect' );
} else {
$paymentmethod .= __( 'Debit of', 'doliconnect' );
}
$paymentmethod .= " ".doliprice($listpaymentmethods->discount)."</h6><small class='text-muted'>".__( 'Soon available', 'doliconnect' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
//if ( empty($object) && get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' )){
//print '<li class="list-group-item list-group-item-secondary" id="Recharge" style="display: none">';
//print 'Prochainement, vous pourrez recharger votre compte!';
//print '<div class="input-group mb-3">
//  <div class="input-group-prepend">
//    <span class="input-group-text">$</span>
//  </div>
//  <input type="num" class="form-control" aria-label="Amount (to the nearest dollar)">
//  <div class="input-group-append">
//    <span class="input-group-text">.00</span>
//  </div>
//</div>';
//print '</li>';
//}
}

//SAVED SOURCES
if ( $listpaymentmethods->paymentmethods != null ) {
$i=0;    
foreach ( $listpaymentmethods->paymentmethods as $method ) {
$i++;                                                                                                                         
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='$method->id' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='$method->id' ";
if ( date('Y/n') >= $method->expiration && !empty($object) && !empty($method->expiration) ) { $paymentmethod .= " disabled "; }
elseif ( $i == 1 || !empty($method->default_source) ) { $paymentmethod .= " checked "; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='$method->id'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i ';
if ( $method->type == 'sepa_debit' ) {
$paymentmethod .= 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $method->brand == 'visa' ) { $paymentmethod .= 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { $paymentmethod .= 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { $paymentmethod .= 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else { $paymentmethod .= 'class="fab fa-cc-amex fa-3x fa-fw"';}
}
$paymentmethod .= '></i></center>';
$paymentmethod .= '</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $method->type == 'sepa_debit' ) {
$paymentmethod .= __( 'Account', 'doliconnect' ).' '.$method->reference.'<small> <a href="'.$method->mandate_url.'" title="'.__( 'Mandate', 'doliconnect' ).' '.$method->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
$paymentmethod .= __( 'Card', 'doliconnect' ).' '.$method->reference;
}
if ( !empty($method->expiration) ) { $paymentmethod .= " - ".date("m/Y", strtotime($method->expiration.'/1')); }
$paymentmethod .= "</h6><small class='text-muted'>".$method->holder."</small></div>";
$paymentmethod .= "<div class='d-none d-sm-block col-2 align-middle text-right'>";
$paymentmethod .= "<img src='".plugins_url('doliconnect/images/flag/'.strtolower($method->country).'.png')."' class='img-fluid' alt='$method->country'>";
//print "<div class='btn-group-vertical' role='group'><a class='btn btn-light text-primary' href='#' role='button'><i class='fas fa-edit fa-fw'></i></a>
//<button name='delete_source' value='".$method->id."' class='btn btn-light text-danger' type='submit'><i class='fas fa-trash fa-fw'></i></button></div>";
$paymentmethod .= "</div></div></label></div></li>";
} }

//NEW CARD
if ( $i < 5 && $listpaymentmethods->code_client != null && !empty($listpaymentmethods->card) ) {      
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='CdDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newcard' ";
if ( empty($i) && empty($listpaymentmethods->paymentmethods) ) { $paymentmethod .= " checked"; }
$paymentmethod .= "><label class='custom-control-label w-100' for='CdDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= "<center><i class='fas fa-credit-card fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Credit card', 'doliconnect' )."</h6><small class='text-muted'>Visa, MasterCard, Amex...</small></div></div>";
$paymentmethod .= "</label></div></li>";

$paymentmethod .= '<li class="list-group-item list-group-item-secondary" id="CardForm" style="display: none"><form action="'.$url.'" >'; //onchange="ShowHideDiv()"
$paymentmethod .= '<input id="cardholder-name" name="cardholder-name" value="" type="text" class="form-control" placeholder="'.__( 'Owner', 'doliconnect' ).'" autocomplete="off" required>
<label for="card-element"></label>
<div class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>
<div id="card-errors" role="alert"></div>';
$paymentmethod .= '</form></li>';
}

//NEW SEPA DIRECT DEBIT
if ( $i < 5 && $listpaymentmethods->code_client != null && !empty($listpaymentmethods->sepa_direct_debit) ) {    
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='BkDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newbank' ";
//if ($listsource["sources"]==null) {print " checked";}
$paymentmethod .= " ><label class='custom-control-label w-100' for='BkDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= "<center><i class='fas fa-university fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank transfer', 'doliconnect' )."</h6><small class='text-muted'>".__( 'Via SEPA Direct Debit', 'doliconnect' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
$paymentmethod .= '<li class="list-group-item list-group-item-secondary" id="BankForm" style="display: none">';
$paymentmethod .= "<p class='text-justify'>";
$blogname=get_bloginfo('name');
$paymentmethod .= '<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect' ), $blogname).'</small>';
$paymentmethod .= "</p>";
$paymentmethod .= '<input id="ibanholder-name" name="ibanholder-name" value="" type="text" class="form-control" placeholder="'.__( 'Owner', 'doliconnect' ).'" autocomplete="off">
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- A Stripe Element will be inserted here. --></div>';
$paymentmethod .= '<div id="bank-name"></div>';
$paymentmethod .= '<div id="iban-errors" role="alert"></div>';
$paymentmethod .= '</li>';
}

//PAYMENT REQUEST API
if ( ! empty($object) && get_option('doliconnectbeta')=='1' && !empty($listpaymentmethods->payment_request_api) ) {  
$paymentmethod .= "<li id='PraForm' class='list-group-item list-group-item-action flex-column align-items-start' style='display: none'><div class='custom-control custom-radio'>
<input id='src_pra' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='PRA' ";
//if ($listsource["sources"] == null) { $paymentmethod .= " checked";}
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_pra'>";
//$paymentmethod .= "<div class='row' id='googlepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
//$paymentmethod .= '<center><i class="fab fa-google fa-3x fa-fw" style="color:Black"></i></center>';
//$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Google Pay', 'doliconnect' )."</h6>";
//$paymentmethod .= "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect' )."</small></div></div>";
$paymentmethod .= "<div class='row' id='applepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fab fa-apple-pay fa-3x fa-fw" style="color:Black"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Apple Pay', 'doliconnect' )."</h6>";
$paymentmethod .= "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect' )."</small></div></div>";
$paymentmethod .= '</label></div></li>';
}

//alternative payment modes & offline
if ( ! empty($object) ) {

if ( isset($listpaymentmethods->PAYPAL) && $listpaymentmethods->PAYPAL != null && get_option('doliconnectbeta') == '1' && current_user_can( 'administrator' ) ) {
$paymentmethod .= "<li id='PaypalForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_paypal' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='PAYPAL' ";
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_paypal'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fab fa-paypal fa-3x fa-fw" style="color:#2997D8"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>PayPal</h6><small class='text-muted'>".__( 'Redirect to Paypal', 'doliconnect' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

if ( isset($listpaymentmethods->RIB) && $listpaymentmethods->RIB != null ) {
$paymentmethod .= "<li id='VirForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_vir' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='VIR' ";
if ( $listpaymentmethods->paymentmethods == null && empty($listpaymentmethods->card) ) { $paymentmethod .= " checked"; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Transfer', 'doliconnect' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

if ( isset($listpaymentmethods->CHQ) && $listpaymentmethods->CHQ != null ) {
$paymentmethod .= "<li id='ChqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_chq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='CHQ' ";
if ( $listpaymentmethods->paymentmethods == null && $listpaymentmethods->card != 1 && $listpaymentmethods->RIB == null ) { $paymentmethod .= " checked"; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
} 

if ( ! empty(dolikiosk()) ) {
$paymentmethod .= "<li id='LiqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_liq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='LIQ' ";
if ( $listpaymentmethods->paymentmethods == null && empty($listpaymentmethods->card) && $listpaymentmethods->CHQ == null && $listpaymentmethods->RIB == null ) { $paymentmethod .= " checked"; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect' )."</h6><small class='text-muted'>".__( 'Go to reception desk', 'doliconnect' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

}

// save new source button
$paymentmethod .= "<li id='SaveFormButton' class='list-group-item list-group-item-action flex-column align-items-start'  style='display: none'>";
if ( ! empty($object) ) { $paymentmethod .= '<div class="custom-control custom-checkbox"><input id="savethesource" class="custom-control-input form-control-sm" type="checkbox" name="savethesource" value="1" ><label class="custom-control-label w-100" for="savethesource"><small class="form-text text-muted"> '.__( 'Save this payment method', 'doliconnect' ).'</small></label></div>';}
else { $paymentmethod .= '<div class="custom-control custom-checkbox"><input id="savethesource" type="hidden" name="savethesource" value="1"><input id="setasdefault" class="custom-control-input form-control-sm" type="checkbox" name="setasdefault" value="1" checked><label class="custom-control-label w-100" for="setasdefault"><small class="form-text text-muted"> '.__( 'Set as default mode', 'doliconnect' ).'</small></label></div>';}
$paymentmethod .= "</li>";

$paymentmethod .= "</ul><div class='card-body'>";

if ( $listpaymentmethods->paymentmethods == null ) { $paymentmethod .= "<input type='hidden' name='defaultsource' value='nosavedsource'>"; }  

$paymentmethod .= "<input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'>";
$paymentmethod .= "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>";
$paymentmethod .= "<button id='pay-Button' class='btn btn-danger btn-block' type='submit'><b>".__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc',$currency)."</b></button>";

$paymentmethod .= "</div></div>";

if ( empty($object) ) {
$paymentmethod .= "<small><div class='float-left'>";
$paymentmethod .= dolirefresh($request, $url, dolidelay('paymentmethods'));
$paymentmethod .= "</div><div class='float-right'>";
$paymentmethod .= dolihelp('ISSUE');
$paymentmethod .= "</div></small>";
}

$paymentmethod .= "</div>";

$paymentmethod .= '<div id="payment-success" class="card text-white bg-success" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Success Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';
$paymentmethod .= '<div id="payment-waiting" class="card text-white bg-warning" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Waiting Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';
$paymentmethod .= '<div id="payment-error" class="card text-white bg-danger" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Error Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';

$paymentmethod .= doliloading('payment');  

$paymentmethod .= "<script>";
if ( $listpaymentmethods->code_account != null ) {
$paymentmethod .= "var stripe = Stripe('".$listpaymentmethods->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->code_account."'
});";
} else {
$paymentmethod .= "var stripe = Stripe('".$listpaymentmethods->publishable_key."');";
}
$paymentmethod .= 'var style = {
  base: {
    color: "#32325d",
    lineHeight: "18px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};';

//VARIABLES
$paymentmethod .= '//VARIABLES
var CdDbt = document.getElementById("CdDbt");
var BkDbt = document.getElementById("BkDbt");  
var discount = document.getElementById("discount");

var src_chq = document.getElementById("src_chq");
var src_vir = document.getElementById("src_vir");
var src_liq = document.getElementById("src_liq");
var src_pra = document.getElementById("src_pra");

var montant = '.($object->total_ttc*100).';
var currency = "'.strtolower(isset($object->multicurrency_code) ? $object->multicurrency_code : 'EUR').'";
';

$paymentmethod .= 'function ShowHideDiv() {
//CARD
if ( CdDbt && CdDbt.checked ) {
// Create an instance of Elements
var elements = stripe.elements();
var cardElement = elements.create("card", {style: style});
cardElement.mount("#card-element");';

// Handle real-time validation errors from the card Element.
$paymentmethod .= 'var cardholderName = document.getElementById("cardholder-name");
cardholderName.value = "";
var displayError = document.getElementById("card-errors");
displayError.textContent = "";
cardElement.addEventListener("change", function(event) {
document.getElementById("pay-Button").disabled = false;
  if (event.error) {
    console.log("Show event error");
    displayError.textContent = event.error.message;
  } else {
    console.log("Reset error message");
    displayError.textContent = "";
  }
});


}';

$paymentmethod .= '
if (CdDbt) {
document.getElementById("CardForm").style.display = CdDbt.checked ? "block" : "none";
}

if (src_pra && src_pra.checked) {
  document.getElementById("pay-Button").style.display = "none";
  document.getElementById("payment-request-button").style.display = "block";
} else {
  document.getElementById("pay-Button").style.display = "block";
  document.getElementById("payment-request-button").style.display = "none";
}

var payButton = document.getElementById("pay-Button");
var clientSecret = "'.$paymentintent->stripe->client_secret.'";

payButton.addEventListener("click", function(ev) {
console.log("We click on buttontoaddcard");
event.preventDefault();
document.getElementById("pay-Button").disabled = true; 
        if (cardholderName.value == "")
        	{        
				console.log("Field Card holder is empty");
				var displayError = document.getElementById("card-errors");
				displayError.textContent = "'.__( "We need an owner as on your card.", "doliconnect").'";
        document.getElementById("pay-Button").disabled = false;    
        	}
        else
        	{
  stripe.handleCardPayment(
    clientSecret, cardElement, {
      payment_method_data: {
        billing_details: {name: cardholderName}
      }
    }
  ).then(function(result) {
    if (result.error) {
    // Show error in payment form
jQuery("#DoliconnectLoadingModal").modal("hide");
console.log("Error occured when adding card");
var displayError = document.getElementById("card-errors");
displayError.textContent = "'.__( "Your card number seems to be wrong.", "doliconnect").'";    
    } else {
      // The payment has succeeded. Display a success message.
    }
  }); 
}
});

}
window.onload=ShowHideDiv;
';

//PAYMENT REQUEST API
$paymentmethod .= '
var paymentRequest = stripe.paymentRequest({
  country: "FR",
  currency: currency,
  total: {
    label: "Demo total",
    amount: montant,
  },
});
//requestPayerName: true,
//requestPayerEmail: true,

var elements = stripe.elements();
var prButton = elements.create("paymentRequestButton", {
  paymentRequest: paymentRequest,
});

// Check the availability of the Payment Request API first.
paymentRequest.canMakePayment().then(function(result) {
  if (result) {
    prButton.mount("#payment-request-button");
    document.getElementById("payment-request-button").style.display = "none";
    document.getElementById("PraForm").style.display = "block";
  } else {
    document.getElementById("payment-request-button").style.display = "none";
    document.getElementById("PraForm").style.display = "none";
  }
});

paymentRequest.on("paymentmethod", function(ev) {
  stripe.confirmPaymentIntent(clientSecret, {
    payment_method: ev.paymentMethod.id,
  }).then(function(confirmResult) {
    if (confirmResult.error) {
      // Report to the browser that the payment failed, prompting it to
      // re-show the payment interface, or show an error message and close
      // the payment interface.
      ev.complete("fail");
    } else {
      // Report to the browser that the confirmation was successful, prompting
      // it to close the browser payment method collection interface.
      ev.complete("success");
      // Let Stripe.js handle the rest of the payment flow.
      stripe.handleCardPayment(clientSecret).then(function(result) {
        if (result.error) {
          // The payment failed -- ask your customer for a new payment method.
        } else {
          // The payment has succeeded.
        }
      });
    }
  });
});
';

$paymentmethod .= "</script>";

return $paymentmethod;
}
?>