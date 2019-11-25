<?php

function product_block_render( $attributes, $content ) {
doliconnect_enqueues();
$content = '<DIV class="card shadow-sm"><DIV class="card-body">';
if ($attributes['productID']>0) {
$product = callDoliApi("GET", "/products/".$attributes['productID']."?includestockdata=1", null, dolidelay('product'));
//$content .= $product;
$documents = callDoliApi("GET", "/documents?modulepart=product&id=".$product->id, null, dolidelay('product'));
//$content .= $documents;
if (defined("DOLIBUG")) {
$content .= dolibug();
} else    if ( $product->id>0 && $product->status == 1 ) {
$content .= '<div class="row">';
$content .= '<div class="col-12 d-block d-sm-block d-xs-block d-md-none"><center><i class="fa fa-cube fa-fw fa-5x"></i></center>';
//$content .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
$content .= '</div>';
$content .= '<div class="col-md-4 d-none d-md-block"><center><i class="fa fa-cube fa-fw fa-5x"></i></center>';
//$content .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
$content .= '</div>';
$content .= '<div class="col-12 col-md-8"><h5 class="card-title"><b>'.doliproduct($product, 'label')."</b>";
if ( ! empty(doliconnectid('dolicart')) ) { 
$content .= " ".doliproductstock($product);
}
$content .= "<br><small>".__( 'Reference', 'doliconnect').": ".$product->ref;
if ( !empty($product->barcode) ) { $content .= " / ".__( 'Barcode', 'doliconnect').": ".$product->barcode; }
$content .= "</small><p>".doliproduct($product, 'description')."</p>";
if ( ! empty(doliconnectid('dolicart')) ) { 
$content .= doliproducttocart($product, null, isset($attributes['showButtonToCart']) ? $attributes['showButtonToCart'] : null, isset($attributes['hideDuration']) ? $attributes['hideDuration'] : null);
}
$content .= '</div></div>';
} else {
$content .= '<center>'.__( 'Product/Service not in sale', 'doliconnect' ).'</center>';
} 
} else {
$content .= '<center>'.__( 'No product', 'doliconnect' ).'</center>';
}
$content .= '</div></div>';
return $content;
}
function product_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'product-block',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ),
      'beta4'
		);
		register_block_type(
			'doliconnect/product-block',
			array(
				'editor_script'   => 'product-block',
				'render_callback' => 'product_block_render',
				'attributes'      => array(
					'request_type' => array(
						'type' => 'string',
					),
				),
			)
		);
	}
}
add_action( 'init', 'product_block_init' );