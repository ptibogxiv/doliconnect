<?php

add_action( 'doliconnect_cron_hook', 'doliconnect_cron_process' );

function doliconnect_cron_process($refresh = false) {

$products = array();
$categories = array();

if (get_option('dolicartnewlist') != 'none') {
$date = new DateTime(); 
$date->modify('NOW');
$duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
$date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
$lastdate = $date->format('Y-m-d');
$requestp = "/products?sortfield=t.datec&sortorder=DESC&sqlfilters=(t.datec%3A%3E%3A'".$lastdate."')%20AND%20(t.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
if ( !isset($listproduct->error) && $listproduct != null ) {
foreach ($listproduct as $product) {
$products[$product->id]['id'] = $product->id;
$products[$product->id]['entity'] = $product->entity;
}}
}

if ( !empty(doliconst('MAIN_MODULE_DISCOUNTPRICE')) ) {
$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$requestp = "/discountprice?sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')%20AND%20(d.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
if ( !isset($listproduct->error) && $listproduct != null ) {
foreach ($listproduct as $product) {
$products[$product->fk_product]['id'] = $product->fk_product;
$products[$product->fk_product]['entity'] = $product->entity;
}}
}

$shop = doliconst("DOLICONNECT_CATSHOP");
$request = "/categories/".esc_attr($shop)."?include_childs=true";
$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', $refresh));
if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc->childs as $category) {
$categories[$category->id] = $category->id;
doliconnect_image('category', $category->id, 1, $refresh, $category->entity);
}}

foreach ($categories as $id => $categorie) {
$request = "/categories/".$id."?include_childs=true";
$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', $refresh));
if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc->childs as $category) {
$categories[$category->id] = $category->id;
doliconnect_image('category', $category->id, 1, $refresh, $category->entity);
}}
}


foreach ($categories as $id => $categorie) {
$requestp = "/products?sortfield=t.label&sortorder=ASC&category=".$id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
if ( !isset($listproduct->error) && $listproduct != null ) {
foreach ($listproduct as $product) {
$products[$product->id]['id'] = $product->id;
$products[$product->id]['entity'] = $product->entity;
}}
}

$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}  
foreach ($products as $id => $product) {
$product1 = callDoliApi("GET", "/products/".$product['id']."?includestockdata=".$includestock."&includesubproducts=true", null, dolidelay('product', $refresh));
doliconnect_image('product', $product['id'], array('limit'=>1, 'entity'=>$product['entity'], 'size'=>'200x200'), $refresh);
if ( !empty(doliconst('MAIN_MODULE_DISCOUNTPRICE')) ) {
$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$product2 = callDoliApi("GET", "/discountprice?productid=".$product['id']."&sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')", null, dolidelay('product', $refresh));
}
if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES"))) {
$product3 = callDoliApi("GET", "/products/".$product['id']."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
}
}

}
?>