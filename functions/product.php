<?php

function doliproduct($object, $value) {
if ( function_exists('pll_the_languages') && is_object($object->multilangs) ) { 
$lang = pll_current_language('locale');
return $object->multilangs->$lang->$value ? $object->multilangs->$lang->$value : $object->$value;
} else {
return $object->$value;
}
}

function doliprice($object, $mode = "ttc", $currency = "EUR") {
global $current_user; 

if ( is_object($object) ) {
$total='multicurrency_total_'.$mode;
if ( isset($object->$mode) ) { $montant=$object->$mode;
} else {
$total='total_'.$mode;
$montant=$object->$total;
} } elseif (!empty($object)) {
$montant=$object;
} else {
$montant=0;
}

//$$objet->multicurrency_code
if ( is_null($currency) ) { $currency="EUR"; }
if ( function_exists('pll_the_languages') ) { 
$locale=pll_current_language('locale');
} else { if ( $current_user->locale == null ) { $locale=get_locale(); } else { $locale=$current_user->locale; } }
$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
return numfmt_format_currency($fmt, $montant, $currency);//.$decimal
}

function doliproductstock($product) {

$enablestock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));
$stockservices = callDoliApi("GET", "/doliconnector/constante/STOCK_SUPPORTS_SERVICES", null, dolidelay('constante'));

$minstock = min(array($product->stock_reel, $product->stock_theorique));
$maxstock = max(array($product->stock_reel, $product->stock_theorique));

if ( ! is_object($product) || empty($enablestock->value) || ($product->type != '0' && ! is_object($stockservices->value)) ) {
$stock = "<span class='badge badge-pill badge-light'>".__( 'Available', 'doliconnect' )."</span>"; 
} else {
if ( $maxstock <='0' ) { $stock = "<span class='badge badge-pill badge-dark'>".__( 'Out of stock', 'doliconnect' )."</SPAN>"; }
elseif ( $minstock < '0' && $maxstock > '0' ) { $stock = "<span class='badge badge-pill badge-secondary'>".__( 'Replenishment', 'doliconnect' )."</span>"; }
elseif ( $minstock >= '0' && $maxstock <= $product->seuil_stock_alerte ) { $stock = "<span class='badge badge-pill badge-danger'>".__( 'Limited stock', 'doliconnect' )."</span>"; }
else { $stock = "<span class='badge badge-pill badge-success'>".__( 'In stock', 'doliconnect' )."</span>"; }
}

return $stock;
}
?>