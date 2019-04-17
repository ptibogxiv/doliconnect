<?php

function doliprice($object, $mode = "ttc", $currency = "EUR") {
global $current_user; 

if ( is_object($object) ) {
$total='multicurrency_total_'.$mode;
if ( isset($object->$mode) ) { $montant=$object->$mode;
} else {
$total='total_'.$mode;
$montant=$object->$total;
} } else {
$montant=$object;
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

if ( $product->stock_reel > $product->seuil_stock_alerte && $product->stock_reel > '10' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) {$stock = "<span class='badge badge-pill badge-success'>".__( 'In stock', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= $product->seuil_stock_alerte && $product->seuil_stock_alerte > '10' && is_object($stock) && $product->stock_reel > '10' && $product->type == '0' && $stock->value == 1 ) {$stock = "<span class='badge badge-pill badge-warning'>".__( 'In stock', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= '10' && $product->stock_reel > '0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) { $stock = "<span class='badge badge-pill badge-danger'>".__( 'Limited stock', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= '0' && $product->stock_reel > '0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) { $stock = "<span class='badge badge-pill badge-secondary'>".__( 'Replenishment', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= '0' && $product->type == '0' && is_object($stock) && $stock->value == 1 ) {$stock = "<span class='badge badge-pill badge-dark'>".__( 'Out of stock', 'doliconnect' )."</SPAN>";}
else { $stock = "<span class='badge badge-pill badge-light'>".__( 'Available', 'doliconnect' )."</span>";}
//$stock=$product[stock_reel];
return $stock;
}
?>