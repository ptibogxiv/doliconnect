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

$minstock = min(array($product->stock_theorique,$product->stock_reel));
$maxstock = max(array($product->stock_theorique,$product->stock_reel));

if ( $maxstock <= 0 || (isset($product->array_options->options_packaging) && $maxstock <= $product->array_options->options_packaging ) ) { $stock = "<span class='badge badge-pill badge-dark'>".__( 'Out of stock', 'doliconnect')."</SPAN>"; }  
elseif ( ($minstock <= 0 || (isset($product->array_options->options_packaging) && $product->stock_reel < $product->array_options->options_packaging)) && $maxstock >= 0 && $product->stock_theorique > $product->stock_reel ) { $stock = "<span class='badge badge-pill badge-secondary'>".__( 'Replenishment', 'doliconnect')."</span>"; }
elseif ( $minstock >= 0 && $maxstock <= $product->seuil_stock_alerte ) { $stock = "<span class='badge badge-pill badge-danger'>".__( 'Limited stock', 'doliconnect')."</span>"; } 
else { $stock = "<span class='badge badge-pill badge-success'>".__( 'In stock', 'doliconnect')."</span>"; }
}
//$stock .= $product->stock_theorique;
return $stock;
}

function doliproducttocart($product, $category=0, $add=0, $time=0) {
global $current_user;

$order = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, dolidelay('constante'));
$enablestock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));
$stockservices = callDoliApi("GET", "/doliconnector/constante/STOCK_SUPPORTS_SERVICES", null, dolidelay('constante'));

$button = "";

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

$arr_params = array( 'category' => $category, 'product' => $product->id.'#prod-'.$product->id);  
$return = add_query_arg( $arr_params, doliconnecturl('dolishop'));
$button .= "<form id='product-add-form-".$product->id."' role='form' action='".$return."'  method='post'>";

$button .= doliloaderscript('product-add-form-'.$product->id.'', false);

$button .= "<input type='hidden' name='product_update' value='$product->id'><input type='hidden' name='product_update[".$product->id."][product]' value='$product->id'>";
$button .= "<script>";

$button .= "</script>";


$currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:'eur';

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {

if ( $product->duration_unit == 'i' ) {
$altdurvalue=60/$product->duration_value; 
}

}

if ( callDoliApi("GET", "/doliconnector/constante/PRODUIT_MULTIPRICES", null, dolidelay('constante')) && !empty($product->multiprices_ttc) ) {
$button .= '<table class="table table-sm table-striped"><tbody>';
$lvl=doliconnector($current_user, 'price_level');
//$count=1;
//$button .=$lvl;
foreach ( $product->multiprices_ttc as $level => $price ) {
$button .= '<tr>';   
//if ( (empty(doliconnector($current_user, 'price_level')) && $level == 1 ) || doliconnector($current_user, 'price_level') == $level ) {
$button .= '<td>'.__( 'Price', 'doliconnect').' '.$level.' - '.doliconst('PRODUIT_MULTIPRICES_LABEL'.$level).'</td>';
$button .= '<td class="text-right">'.doliprice( $price, $currency);
if ( empty($time) && !empty($product->duration_value) ) { $button .='/'.doliduration($product); }
$button .= '</td>';
if ( !empty($altdurvalue) ) { $button .= "<td class='text-right'>soit ".doliprice( $altdurvalue*$price, $currency)." par ".__( 'hour', 'doliconnect')."</td>"; } 
//$button .= '<small class="float-right">'.__( 'You benefit from the rate', 'doliconnect').' '.doliconst('PRODUIT_MULTIPRICES_LABEL'.$level).'</small>';
//}
//$count++;
$button .= '</tr>'; 
}
if (!empty(doliconnector($current_user, 'price_level'))) {
$level=doliconnector($current_user, 'price_level');
$price_min_ttc=$product->multiprices_min->$level;
$price_ttc=$product->multiprices->$level;
} else {
$level=1;
$price_ttc=$product->multiprices->$level;
}
$button .= '</tbody></table>';
} else {
if ( !empty(callDoliApi("GET", "/doliconnector/constante/PRODUIT_CUSTOMER_PRICES", null, dolidelay('constante'))->value) && doliconnector($current_user, 'fk_soc') > 0 ) {
$product2 = callDoliApi("GET", "/products/".$product->id."/selling_multiprices/per_customer?thirdparty_id=".doliconnector($current_user, 'fk_soc'), null, dolidelay('product'));
}
if ( !empty(callDoliApi("GET", "/doliconnector/constante/PRODUIT_CUSTOMER_PRICES", null, dolidelay('constante'))->value) && !isset($product2->error) && $product2 != null ) {
foreach ( $product2 as $pdt2 ) {
$price_min_ttc=$pdt2->price_min;
$price_ttc=$pdt2->price;
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect').': <s>'.doliprice( $product->price_ttc, $currency).'</s> '.doliprice( $pdt2->price_ttc, $currency);
}
} else {
$price_min_ttc=$product->price_min;
$price_ttc=$product->price;
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect').': '.doliprice( $product->price_ttc, $currency);
}
if ( empty($time) && isset($product->duration) ) { $button .=' '.doliduration($product); } 
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$product->price_ttc, $currency)." par ".__( 'hour', 'doliconnect')."</h6>"; } 
}

if ( is_user_logged_in() && $add==1 && is_object($order) && $order->value == 1 && doliconnectid('dolicart') > 0 ) {
if ( ($product->stock_reel-$qty > 0 && $product->type == '0') ) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m1 = 10*$product->array_options->options_packaging;
} else {
$m1 = 10;
}
if ( $product->stock_reel-$qty >= $m1 || (is_object($enablestock) && $enablestock->value != 1) ) {
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
$button .= "<div class='input-group'><select class='form-control' name='product_update[".$product->id."][qty]' ";
if ( ( empty($product->stock_reel) || $m2 < $step) && $product->type == '0' && (is_object($enablestock) && $enablestock->value == 1)) { $button .= " disabled"; }
$button .= ">";
if ($m2 < $step)  { $button .= "<OPTION value='0' >x 0</OPTION>"; }
foreach (range(0, $m2, $step) as $number) {
		if ( $number == $qty ) {
$button .= "<OPTION value='$number' selected='selected'>x ".$number."</OPTION>";
		} else {
$button .= "<OPTION value='$number' >x ".$number."</OPTION>";
		}
	}
$button .= "</SELECT><DIV class='input-group-append'><BUTTON class='btn btn-outline-secondary' type='submit' ";
if ( ( empty($product->stock_reel) || $m2 < $step) && $product->type == '0' && (is_object($enablestock) && $enablestock->value == 1)) { $button .= " disabled"; }
$button .= ">";
if ( $qty > 0 ) {
$button .= __( 'Update', 'doliconnect')."";
} else {
$button .= __( 'Add', 'doliconnect')."";
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

//$button .= "<div class='input-group'><a class='btn btn-block btn-outline-secondary' href='".$loginurl."' role='button' title='".__( 'Login', 'doliconnect')."'>".__( 'Login', 'doliconnect')."</a></div>";
} else {
$button .= "<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect')."'>".__( 'Contact us', 'doliconnect')."</a></div>";
}

if ( !empty(doliconnector($current_user, 'remise_percent')) ) { $button .= "<small>".sprintf( esc_html__( 'you get %u %% discount', 'doliconnect'), doliconnector($current_user, 'remise_percent'))."</small>"; }
$button .= "<input type='hidden' name='product_update[".$product->id."][price]' value='".$price_ttc."'></form>";
$button .= '<div id="product-add-loading-'.$product->id.'" style="display:none">'.doliprice($price_ttc).'<button class="btn btn-secondary btn-block" disabled><i class="fas fa-spinner fa-pulse fa-1x fa-fw"></i> '.__( 'Loading', 'doliconnect').'</button></div>';
$button .= "";
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