<?php

function doliprice($montant,$currency = "EUR"){
global $current_user; 
if ( is_null($currency) ) { $currency="EUR"; }
if ( function_exists('pll_the_languages') ) { 
$locale=pll_current_language('locale');
} else { if ( $current_user->locale == null ) { $locale=get_locale(); } else { $locale=$current_user->locale; } }
$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
return numfmt_format_currency($fmt, $montant, $currency);//.$decimal
}

function doliproductstock($product){
if ( $product->stock_reel > $product->seuil_stock_alerte && $product->stock_reel > '10' && $product->type == '0' ) {$stock = "<span class='badge badge-pill badge-success'>".__( 'In stock', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= $billet->seuil_stock_alerte && $product->seuil_stock_alerte > '10' && $product->stock_reel > '10' && $product->type == '0' ) {$stock = "<span class='badge badge-pill badge-warning'>".__( 'In stock', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= '10' && $product->stock_reel > '0' && $product->type == '0' ) { $stock = "<span class='badge badge-pill badge-danger'>".__( 'Limited stock', 'doliconnect' )."</span>";}
elseif ( $product->tock_reel <= '0' && $product->stock_reel > '0' && $product->type == '0' ) { $stock = "<span class='badge badge-pill badge-secondary'>".__( 'Replenishment', 'doliconnect' )."</span>";}
elseif ( $product->stock_reel <= '0' && $product->type == '0' ) {$stock = "<span class='badge badge-pill badge-dark'>".__( 'Out of stock', 'doliconnect' )."</SPAN>";}
else { $stock = "<span class='badge badge-pill badge-light'>".__( 'Available', 'doliconnect' )."</span>";}
//$stock=$product[stock_reel];
return $stock;
}

function doliminicart($orderfo){
$item = constant("DOLICONNECT_CART_ITEM");
echo "<div class='card'><div class='card-header'>".__( 'Cart', 'doliconnect' )." - ".sprintf( _n( '%s item', '%s items', $item, 'doliconnect' ), $item)." <small>(<a href='".doliconnecturl('dolicart')."' >".__( 'update', 'doliconnect' )."</a>)</small></div><ul class='list-group list-group-flush'>";
if ( $orderfo->lines != null ) {
foreach ($orderfo->lines as $line) {

//$product = CallAPI("GET", "/products/".$post->product_id, null, 0);

echo "<li class='list-group-item d-flex justify-content-between lh-condensed'><div><h6 class='my-0'>".$line->libelle."</h6><small class='text-muted'>".__( 'Quantity', 'doliconnect' ).": ".$line->qty."</small></div>";

echo "<span class='text-muted'>".doliprice($line->multicurrency_total_ttc?$line->multicurrency_total_ttc:$line->total_ttc,$orderfo->multicurrency_code)."</span></li>";
}
}

if ( constant("REMISE_PERCENT") > 0 ) { 
$remise_percent = ($total->prx*constant("REMISE_PERCENT"))/100;
echo "<li class='list-group-item d-flex justify-content-between bg-light'>
              <div class='text-success'>
                <h6 class='my-0'>".__( 'Customer discount', 'doliconnect' )."</h6>
                <small>-".constant("REMISE_PERCENT")."%</small>
              </div>
              <span class='text-success'>-".doliprice($remise_percent,$orderfo->multicurrency_code)."</span></li>";
} 
$total=$subtotal-$remise_percent;            
echo "<li class='list-group-item d-flex justify-content-between'>
              <span>Total </span>
              <strong>".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</strong></li>";
echo "</ul></div><br>";
}

function addtodolibasket($product, $quantity, $price, $timestart = null, $timeend = null) {
global $wpdb,$current_user;
$delay=HOUR_IN_SECONDS;

if ( !is_null($timestart) || !is_null($timeend) )
{
$date_start=strftime('%Y-%m-%d 00:00:00',$timestart);
$date_end=strftime('%Y-%m-%d 00:00:00',$timeend);
} else {
$date_start=null;
$date_end=null;
}

if ( constant("DOLICONNECT_CART") > 0 ) {
$orderid=constant("DOLICONNECT_CART");
} else {
$rdr = [
    'socid' => constant("DOLIBARR"),
    'date_commande'  => mktime(),
    'demand_reason_id' => 1,
	];                  
$order = CallAPI("POST", "/orders", $rdr, 0);
$orderid=$order;
define('DOLICONNECT_CART', $orderid);
}

$orderfo = CallAPI("GET", "/orders/".$orderid, null, dolidelay($delay, true));

if ( $orderfo->lines != null ) {
foreach ( $orderfo->lines as $ln ) {
if ( $ln->fk_product == $product ) {
//$deleteline = CallAPI("DELETE", "/orders/".$orderid."/lines/".$ln[id], null, 0);
//$qty=$ln[qty];
$line=$ln->id;
}
}}

if (!$line > 0) {$line=null;}

if ( $orderid > 0 && $quantity > 0 && is_null($line) ) {
$prdt = CallAPI("GET", "/products/".$product, null, dolidelay($delay, true));
$adln = [
    'fk_product' => $product,
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'remise_percent' => constant("REMISE_PERCENT"),
    'subprice' => $price
	];                 
$addline = CallAPI("POST", "/orders/".$orderid."/lines", $adln, 0);
$order = CallAPI("GET", "/orders/".$orderid, null, dolidelay($delay, true));
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, dolidelay($delay, true));
return $addline;

} elseif ( $orderid > 0 && $line > 0 ) {

if ( $quantity < 1 ) {

$deleteline = CallAPI("DELETE", "/orders/".$orderid."/lines/".$line, null, 0);
$order = CallAPI("GET", "/orders/".$orderid, null, dolidelay($delay, true));
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, dolidelay($delay, true));
return $deleteline;
 
} else {
$prdt = CallAPI("GET", "/products/".$product, null, 0);
 $ln = [
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'remise_percent' => constant("REMISE_PERCENT"),
    'subprice' => $price
	];                  
$updateline = CallAPI("PUT", "/orders/".$orderid."/lines/".$line, $ln, 0);
$order = CallAPI("GET", "/orders/".$orderid, null, dolidelay($delay, true));
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, dolidelay($delay, true));
return $updateline;

}
}
}

?>