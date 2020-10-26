<?php

function doliconnect_newproduct_block_render( $attributes, $content) {

doliconnect_enqueues();

$content = '<div class="card shadow-sm"> <div class="card-header">'.__( 'New items', 'doliconnect' ).'</div><ul class="list-group list-group-flush">';

$date = new DateTime(); 
$date->modify('NOW');
$duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
$date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
$lastdate = $date->format('Y-m-d');
$request = "/products?sortfield=t.datec&sortorder=DESC&limit=5&sqlfilters=(t.datec%3A%3E%3A'".$lastdate."')%20AND%20(t.tosell%3A%3D%3A1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
//$content .= "<li class='list-group-item list-group-item-light'><center>".__(  'Here are our discounted items', 'doliconnect')."</center></li>";
foreach ($resultats as $product) {

$content .= apply_filters( 'doliproductlist', $product);
 
}
} else {
$content .= "<li class='list-group-item'><center><center>".__( 'No new item', 'doliconnect' )."</center></li>";
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
function doliconnect_newproduct_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'doliconnect-newproduct-block-script',
			plugins_url( 'blocknew.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' )
		);
		register_block_type(
			'doliconnect/newproduct-block',
			array(
				'editor_script'   => 'doliconnect-newproduct-block-script',
				'render_callback' => 'doliconnect_newproduct_block_render',
				'attributes'      => array(
          'productID' => array( 'type' => 'string' ),
          'showButtonToCart' => array( 'type' => 'boolean' ),
          'hideDuration' => array( 'type' => 'boolean' ),
          'hideStock' => array( 'type' => 'boolean' ),
		),
	) );
}
}
add_action( 'init', 'doliconnect_newproduct_block_init' );
