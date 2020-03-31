<?php

function doliconnect_product_block_render( $attributes, $content ) {

doliconnect_enqueues();

$content = '<div class="card shadow-sm"><div class="card-body">';
if (isset($attributes['productID']) && $attributes['productID']>0) {
$product = callDoliApi("GET", "/products/".$attributes['productID']."?includestockdata=1", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= $product;
$documents = callDoliApi("GET", "/documents?modulepart=product&id=".$product->id, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= $documents;
if (defined("DOLIBUG")) {
$content .= dolibug();
} elseif ( $product->id>0 && $product->status == 1 ) {
$content .= "<div class='row'>";
$content .= '<div class="col-12 d-block d-sm-block d-xs-block d-md-none"><center>';
$content .= doliconnect_image('product', $product->id, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$content .= '</center>';
//$content .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
$content .= "</div>";
$content .= '<div class="col-md-4 d-none d-md-block"><center>';
$content .= doliconnect_image('product', $product->id, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$content .= '</center>';
//$content .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
$content .= "</div>";
$content .= "<div class='col-12 col-md-8'><h6 class='card-title'><b>".doliproduct($product, 'label')."</b>";
if ( ! empty(doliconnectid('dolicart')) && !isset($attributes['hideStock']) ) { 
$content .= ' '.doliproductstock($product);
}
$content .= "</h5><small>".__( 'Reference:', 'doliconnect')." ".$product->ref."</small>"; 
if ( !empty($product->barcode) ) { $content .= "<br><small>".__( 'Barcode:', 'doliconnect')." ".$product->barcode."</small>"; }
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$content .= "<br><small>".__( 'Origin:', 'doliconnect')." ".$country->label."</small>"; }
$content .= "<br><br><p>".doliproduct($product, 'description')."</p>";
$content .= "<div class='jumbotron'>";
if ( ! empty(doliconnectid('dolicart')) ) { 
$content .= doliconnect_addtocart($product, 0, 0, isset($attributes['showButtonToCart']) ? $attributes['showButtonToCart'] : 0, isset($attributes['hideDuration']) ? $attributes['hideDuration'] : 0);
}
$content .= "</div></div>";
} else {
$content .= "<center>".__( 'Product/Service not in sale', 'doliconnect' )."</center>";
} 
$content .= "</div>";
} else {
$content .= "<center>".__( 'No product', 'doliconnect' )."</center>";
}
$content .= "</div>";
$content .= "<div class='card-footer text-muted'>";
$content .= "<small><div class='float-left'>";
$content .= dolirefresh("/products/".$attributes['productID']."?includestockdata=1&includesubproducts=true", null, dolidelay('thirdparty'));
$content .= "</div><div class='float-right'>";
$content .= dolihelp('ISSUE');
$content .= "</div></small>";
$content .= "</div></div>";
return $content;
}
function doliconnect_product_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'doliconnect-product-block-script',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ),
      'rc1'
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
