<?php

function product_block_render( $attributes, $content ) {
	$args = array();
	if ( isset( $attributes['request_type'] ) ) {
		if ( 'export' === $attributes['request_type'] ) {
			$args['request_type'] = 'export';
		} elseif ( 'remove' === $attributes['request_type'] ) {
			$args['request_type'] = 'remove';
		}
	}
	$content = '<div class="gdpr-data-request-block">' . gdrf_data_request_form( $args ) . '</div>';
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