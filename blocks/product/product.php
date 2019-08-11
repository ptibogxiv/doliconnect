<?php
/**
 * BLOCK: Product
 *
 * Gutenberg Custom Product Block assets.
 *
 * @since   1.0.0
 * @package OPB
 */

// Exit if accessed directly.
// if ( ! defined( 'ABSPATH' ) ) {
// 	exit;
// }

/**
 * Enqueue the block's assets for the editor.
 *
 * `wp-blocks`: Includes block type registration and related functions.
 * `wp-element`: Includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 1.0.0
 */

function doliconnect_product_block() {

	// Scripts.
	wp_register_script(
		'doliconnect-product-block-script', // Handle.
		plugins_url( 'block.js', __FILE__ ), // Block.js: We register the block here.
		array( 'wp-blocks', 'wp-element', 'wp-i18n' ), // Dependencies, defined above.
    'beta'
	);

	// Styles.
	wp_register_style(
		'doliconnect-product-block-editor-style', // Handle.
		plugins_url( 'editor.css', __FILE__ ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependencies, defined above.
    'beta'
	);
  
//	wp_register_style(
//		'doliconnect-product-block-frontend-style', // Handle.
//		plugins_url( 'style.css', __FILE__ ), // Block editor CSS.
//		array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
//	); 

function doliconnect_product_render_block( $attributes ) {

doliconnect_enqueues();

$html = '<DIV class="card shadow-sm"><DIV class="card-body">';
if ($attributes['productID']>0) {
$product = callDoliApi("GET", "/products/".$attributes['productID']."?includestockdata=1", null, dolidelay('product'));
//$html .= $product;

if (defined("DOLIBUG")) {
$html .=dolibug();
} else    if ($product->id>0 && $product->status==1) {
$html .='<div class="row">';
$html .='<div class="col-12 d-block d-sm-block d-xs-block d-md-none">';
$html .=wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
$html .='</div>';
$html .='<div class="col-md-4 d-none d-md-block">';
$html .=wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
$html .='</div>';

$html .='<div class="col-12 col-md-8"><h5 class="card-title">'.doliproduct($product, 'label').'<br>'.doliproductstock($product).'</h5><p>'.doliproduct($product, 'description').'</p>';

if ( function_exists('dolibuttontocart') ) {
$html .= dolibuttontocart($product, null, $attributes['showButtonToCart'], isset($attributes['hideDuration']) ? $attributes['hideDuration'] : null);
}

$html .='</div></div>';
} else {
$html .='<center>'.__( 'Product/Service not in sale', 'doliconnect' ).'</center>';
} 
}
$html .='</div></div><BR/>';
return $html;
}

	// Here we actually register the block with WP, again using our namespacing
	// We also specify the editor script to be used in the Gutenberg interface
	register_block_type( 'doliconnect/product-block', array(
    'render_callback' => 'doliconnect_product_render_block',
		'editor_script' => 'doliconnect-product-block-script',
		'editor_style' => 'doliconnect-product-block-editor-style',
		'style' => 'doliconnect-product-block-frontend-style',
	) );

} // End function organic_profile_block().

// Hook: Editor assets.
add_action( 'init', 'doliconnect_product_block' );
