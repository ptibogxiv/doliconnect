<?php

function doliconnect_discountproduct_block_render( $attributes, $content) {

doliconnect_enqueues();

$content = '<div class="card shadow-sm"><div class="card-body">';

if (isset($attributes['productID']) && $attributes['productID']>0) {
$product = callDoliApi("GET", "/products/".$attributes['productID']."?includestockdata=1", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$content .= doliproductcard($product, $attributes);

} else {
$content .= "<center>".__( 'No product', 'doliconnect' )."</center>";
}
$content .= "</div>";
$content .= "<div class='card-footer text-muted'>";
$content .= "<small><div class='float-left'>";
//$content .= dolirefresh("/products/".$attributes['productID']."?includestockdata=1&includesubproducts=true", null, dolidelay('thirdparty'));
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
