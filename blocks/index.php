<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
 
function load_doliconnect_bootstrap_admin_style($hook) {
    if ( 'post.php' != $hook ) {
        return;
    }
    
doliconnect_enqueues();

}
add_action( 'admin_enqueue_scripts', 'load_doliconnect_bootstrap_admin_style' );

require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/admin/block.php' );
require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/membership/membership.php' );
require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/gdpr/block.php' );  
require_once ( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __DIR__ ) ) . '/blocks/product/block.php' );