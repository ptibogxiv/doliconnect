<?php
/**
 * Doliconnect REST API routes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', 'doliconnect_register_rest_routes' );

function doliconnect_register_rest_routes() {
    $namespace = 'wp/v2/doliconnect';

    register_rest_route(
        $namespace,
        '/status',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'doliconnect_rest_status',
            'permission_callback' => 'doliconnect_rest_permissions_check',
        )
    );

    register_rest_route(
        $namespace,
        '/products',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'doliconnect_rest_get_products',
            'permission_callback' => 'doliconnect_rest_permissions_check',
        )
    );

    register_rest_route(
        $namespace,
        '/products/(?P<id>\d+)',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'doliconnect_rest_get_product',
            'permission_callback' => 'doliconnect_rest_permissions_check',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
            ),
        )
    );

    register_rest_route(
        $namespace,
        '/products/(?P<id>\d+)',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'doliconnect_rest_update_product',
            'permission_callback' => 'doliconnect_rest_update_permissions_check',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
            ),
        )
    );
}

function doliconnect_rest_permissions_check( $request ) {
    if ( is_user_logged_in() ) {
        return true;
    }

    return new WP_Error(
        'rest_forbidden',
        __( 'Sorry, you are not allowed to view this resource.', 'doliconnect' ),
        array( 'status' => 401 )
    );
}

function doliconnect_rest_status( $request ) {
    $public_url = get_site_option( 'dolibarr_public_url' );
    $active = ! empty( $public_url ) && ! empty( get_site_option( 'dolibarr_private_key' ) );

    return rest_ensure_response(
        array(
            'active'         => (bool) $active,
            'public_url'     => esc_url_raw( $public_url ),
            'site'           => array(
                'name' => get_bloginfo( 'name' ),
                'url'  => get_bloginfo( 'url' ),
                'version' => get_bloginfo( 'version' ),
            ),
            'plugin_version' => get_plugin_data( __DIR__ . '/../doliconnect.php' )['Version'],
        )
    );
}

function doliconnect_rest_get_products( $request ) {
    $user = wp_get_current_user();

    /*if ( ! $user || 0 === $user->ID ) {
        return new WP_Error(
            'rest_no_user',
            __( 'Unable to retrieve the current user.', 'doliconnect' ),
            array( 'status' => 401 )
        );
    }*/

    $args = array(
        'post_type'  => 'doliproduct',
        'meta_query' => array(
            array(
            'key'     => 'doliproduct_productid',
            'value'   => sanitize_text_field($product_id),
            'compare' => '='
            )
        ),
        'posts_per_page' => 1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {   
        return rest_ensure_response( $query->posts[0] );
    } else {
        return new WP_Error(
            'rest_product_not_found',
            __( 'Dolibarr product not found.', 'doliconnect' ),
            array( 'status' => 404 )
        );
    }
}

function doliconnect_rest_get_product( $request ) {
    $user = wp_get_current_user();

    if ( ! $user || 0 === $user->ID ) {
        return new WP_Error(
            'rest_no_user',
            __( 'Unable to retrieve the current user.', 'doliconnect' ),
            array( 'status' => 401 )
        );
    }

    $product_id = absint( $request->get_param( 'id' ) );
    if ( ! $product_id ) {
        return new WP_Error(
            'rest_invalid_id',
            __( 'Invalid product id.', 'doliconnect' ),
            array( 'status' => 400 )
        );
    }

    $args = array(
        'post_type'  => 'doliproduct',
        'meta_query' => array(
            array(
            'key'     => 'doliproduct_productid',
            'value'   => sanitize_text_field($product_id),
            'compare' => '='
            )
        ),
        'posts_per_page' => 1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {   
        return rest_ensure_response( $query->posts[0] );
    } else {
        return new WP_Error(
            'rest_product_not_found',
            __( 'Dolibarr product not found.', 'doliconnect' ),
            array( 'status' => 404 )
        );
    }
}

function doliconnect_rest_update_permissions_check( $request ) {
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_forbidden',
            __( 'Sorry, you are not allowed to update this resource.', 'doliconnect' ),
            array( 'status' => 401 )
        );
    }

    if ( ! current_user_can( 'edit_posts' ) ) {
        return new WP_Error(
            'rest_forbidden',
            __( 'Sorry, you do not have permission to edit products.', 'doliconnect' ),
            array( 'status' => 403 )
        );
    }

    return true;
}

function doliconnect_rest_update_product( $request ) {
    $product_id = absint( $request->get_param( 'id' ) );
    
    if ( ! $product_id ) {
        return new WP_Error(
            'rest_invalid_id',
            __( 'Invalid product id.', 'doliconnect' ),
            array( 'status' => 400 )
        );
    }

    // Get the custom post doliproduct
    $args = array(
        'post_type'  => 'doliproduct',
        'meta_query' => array(
            array(
                'key'     => 'doliproduct_productid',
                'value'   => $product_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    );

    $query = new WP_Query($args);

    if ( ! $query->have_posts() ) {
        return new WP_Error(
            'rest_product_not_found',
            __( 'Dolibarr product not found.', 'doliconnect' ),
            array( 'status' => 404 )
        );
    }

    $post = $query->posts[0];
    $post_id = $post->ID;

    // Get request body
    $body = $request->get_json_params();

    if ( empty( $body ) ) {
        return new WP_Error(
            'rest_invalid_body',
            __( 'Request body is empty.', 'doliconnect' ),
            array( 'status' => 400 )
        );
    }

    // Update post content
    $post_data = array(
        'ID' => $post_id,
    );

    if ( isset( $body['label'] ) ) {
        $post_data['post_title'] = sanitize_text_field( $body['label'] );
    }

    if ( isset( $body['description'] ) ) {
        $post_data['post_content'] = wp_kses_post( $body['description'] );
    }

    if ( isset( $body['status'] ) ) {
        $post_data['post_status'] = sanitize_text_field( $body['status'] );
    }

    // Update the post
    $updated = wp_update_post( $post_data );

    if ( is_wp_error( $updated ) ) {
        return new WP_Error(
            'rest_post_update_failed',
            __( 'Failed to update product.', 'doliconnect' ),
            array( 'status' => 500 )
        );
    }

    // Update custom meta fields TO DO 
    /*
    if ( isset( $body['meta'] ) && is_array( $body['meta'] ) ) {
        foreach ( $body['meta'] as $meta_key => $meta_value ) {
            $safe_key = sanitize_text_field( $meta_key );
            $safe_value = sanitize_text_field( $meta_value );
            update_post_meta( $post_id, $safe_key, $safe_value );
        }
    }
    */

    // Return updated post
    $updated_post = get_post( $post_id );
    return rest_ensure_response( $updated_post );
}
