<?php

function doliconnect_discountproduct_block_render( $attributes, $content) {

doliconnect_enqueues();

$content = '<div class="card shadow-sm"><ul class="list-group list-group-flush">';

$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$request = "/discountprice?sortfield=t.rowid&sortorder=DESC&limit=5&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3C%3D%3A'".$lastdate."')";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
//$content .= "<li class='list-group-item list-group-item-light'><center>".__(  'Here are our discounted items', 'doliconnect')."</center></li>";
foreach ($resultats as $product) {
$request2 = "/products/".$product->fk_product."?includestockdata=1";
$product = callDoliApi("GET", $request2, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$content .= apply_filters( 'doliproductlist', $product);
 
}
} else {
$content .= "<center>".__( 'No discounted item', 'doliconnect' )."</center>";
}
$content .= '</ul><div class="card-body"></div>';
$content .= "<div class='card-footer text-muted'>";
$content .= "<small><div class='float-left'>";
$content .= dolirefresh($request, null, dolidelay('product'));
$content .= "</div><div class='float-right'>";
$content .= dolihelp('ISSUE');
$content .= "</div></small>";
$content .= "</div></div>";
return $content;
}
function doliconnect_discountproduct_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'doliconnect-discountproduct-block-script',
			plugins_url( 'blockdiscount.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' )
		);
		register_block_type(
			'doliconnect/discountproduct-block',
			array(
				'editor_script'   => 'doliconnect-discountproduct-block-script',
				'render_callback' => 'doliconnect_discountproduct_block_render',
	) );
}
}
add_action( 'init', 'doliconnect_discountproduct_block_init' );
