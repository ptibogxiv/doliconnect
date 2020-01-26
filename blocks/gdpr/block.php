<?php

function doliconnect_gdpr_block_render( $attributes, $content ) {
	$args = array();
	if ( isset( $attributes['request_type'] ) ) {
		if ( 'export' === $attributes['request_type'] ) {
			$args['request_type'] = 'export';
		} elseif ( 'remove' === $attributes['request_type'] ) {
			$args['request_type'] = 'remove';
		}
	}
	$content = '<div class="gdpr-data-request-block">' . doli_gdrf_data_request_form( $args ) . '</div>';
	return $content;
}
function doliconnect_gdpr_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'data-request-form',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' )
		);
		register_block_type(
			'doliconnect/data-request-form',
			array(
				'editor_script'   => 'data-request-form',
				'render_callback' => 'doliconnect_gdpr_block_render',
				'attributes'      => array(
					'request_type' => array(
						'type' => 'string',
					),
				),
			)
		);
	}
}
add_action( 'init', 'doliconnect_gdpr_block_init' );