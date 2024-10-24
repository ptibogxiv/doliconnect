<?php

function doliconnect_product_block_render( $attributes, $content) {
	doliconnect_enqueues();
	$content = '<div class="card shadow-sm">';
	if (isset($attributes['productID']) && $attributes['productID'] > 0) {
		$request = "/products/".$attributes['productID']."?includestockdata=1&includesubproducts=true&includetrans=true";
		$product = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
		$content .= doliproductcard($product, $attributes);
	} else {
		$content .= '<div class="card-header">'.__( 'Item', 'doliconnect').'</div><div class="card-body"><center>'.__( 'No item', 'doliconnect' ).'</center></div>';
	}
	$content .= doliCardFooter($request, 'product');
	$content .= "</div>";
	return $content;
}

function doliconnect_product_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'doliconnect-product-block-script',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ), 
			'1.0.0',
			array(
			'strategy'  => 'defer',
			'in_footer' => true
			)
		);
		register_block_type(
			'doliconnect/product-block',
			array(
				'editor_script'   => 'doliconnect-product-block-script',
				'render_callback' => 'doliconnect_product_block_render',
				'attributes'      => array(
          'productID' => array( 'type' => 'string' ),
          'showButtonToCart' => array( 'type' => 'boolean' ),
          'hideDuration' => array( 'type' => 'boolean' ),
          'hideStock' => array( 'type' => 'boolean' ),
		),
	) );
}
}
add_action( 'init', 'doliconnect_product_block_init' );
