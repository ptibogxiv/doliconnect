<?php

function doliproduct($object, $value) {
if ( function_exists('pll_the_languages') ) { 
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
$stock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));

$minstock = min($product->stock_reel, product->stock_theorique);
$maxstock = max($product->stock_reel, product->stock_theorique);

if ( $minstock > $product->seuil_stock_alerte && $product->stock_reel > '0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) { $stock = "<span class='badge badge-pill badge-success'>".__( 'In stock', 'doliconnect' )."</span>"; }
elseif ( $minstock <= $product->seuil_stock_alerte && $minstock > '0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) { $stock = "<span class='badge badge-pill badge-danger'>".__( 'Limited stock', 'doliconnect' )."</span>"; }
elseif ( $product->stock_reel <= '0' && $product->stock_theorique > '0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) { $stock = "<span class='badge badge-pill badge-secondary'>".__( 'Replenishment', 'doliconnect' )."</span>"; }
elseif ( $maxstock <='0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) { $stock = "<span class='badge badge-pill badge-dark'>".__( 'Out of stock', 'doliconnect' )."</SPAN>"; }
else { $stock = "<span class='badge badge-pill badge-light'>".__( 'Available', 'doliconnect' )."</span>"; }
//$stock=$product[stock_reel];
return $stock;
}
?>