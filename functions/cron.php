<?php


add_action( 'doliconnect_cron_hook', 'doliconnect_cron_process' );

function doliconnect_cron_process($refresh = false) {

$product = array();

if (get_option('dolicartnewlist') != 'none') {
$date = new DateTime(); 
$date->modify('NOW');
$duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
$date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
$lastdate = $date->format('Y-m-d');
$requestp = "/products?sortfield=t.datec&sortorder=DESC&sqlfilters=(t.datec%3A%3E%3A'".$lastdate."')%20AND%20(t.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
}

if ( !empty(doliconst('MAIN_MODULE_DISCOUNTPRICE')) ) {
$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$requestp = "/discountprice?sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')%20AND%20(d.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
}

$shop = doliconst("DOLICONNECT_CATSHOP", $refresh);

$request = "/categories/".esc_attr($shop)."?include_childs=true";
$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', $refresh));

foreach ($resultatsc->childs as $categorie) {

$requestp = "/products?sortfield=t.label&sortorder=ASC&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));

if ( isset($_GET['category']) && $categorie->id == $_GET['category'] ) {

$request = "/categories/".esc_attr(isset($_GET["category"]) ? $_GET["category"] : $_GET["subcategory"])."?include_childs=true";
$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', $refresh));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc->childs as $categorie) {
$requestp = "/products?sortfield=t.label&sortorder=ASC&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));

//if ( !isset($listproduct->error) && $listproduct != null ) {
//foreach ($resultats as $product) {

//}}

}}

}

}

    //error_log('Mi evento se ejecutó: '.Date("h:i:sa"));
    $recepients="support@ptibogxiv.net";
    $subject="Hello from your Cron Job";
    $message="This is a test mail sent by WordPress automatically as per Your schedule.";
    //let’s send it
    //wp_mail($recepients,$subject,$message);
}



?>
