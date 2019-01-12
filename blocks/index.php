<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//if (version_compare(get_bloginfo('version'), '5.0', '>=' ) or is_plugin_active( 'gutenberg/gutenberg.php' )) {
 
function load_doliconnect_bootstrap_admin_style($hook) {
    if ( 'post.php' != $hook ) {
        return;
    }
    
doliconnect_enqueues();

}
add_action( 'admin_enqueue_scripts', 'load_doliconnect_bootstrap_admin_style' );

require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/admin/admin.php' );
require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/membership/membership.php' );
require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/product/product.php' );
//}
