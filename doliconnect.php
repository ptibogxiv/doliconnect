<?php
/**
 * Plugin Name: Doliconnect
 * Plugin URI: https://ptibogxiv.eu
 * Description: Connect your Dolibarr (ERP/CRM) to Wordpress. 
 * Version: 10.7.2
 * Author: ptibogxiv
 * Author URI: https://ptibogxiv.eu
 * Network: true
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: doliconnect
 * Domain Path: /languages
 * Donate link: https://ptibogxiv.eu
 *   
 * @author ptibogxiv.eu <support@ptibogxiv.eu>
 * @copyright Copyright (c) 2017-2026, ptibogxiv.eu
**/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
  
add_action( 'plugins_loaded', 'doliconnect_textdomain' ); 
function doliconnect_textdomain() {
    load_plugin_textdomain( 'doliconnect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
// ********************************************************

/*
 * Dolibarr minimum and legal version
 * 
 * @since 9.2.3
 * @version 9.2.3
 */

define('DOLIBARR_MINIMUM_VERSION', '18.0.0');
define('DOLIBARR_LEGAL_VERSION', '24.0.0');

// ********************************************************

require_once plugin_dir_path(__FILE__).'/functions/enqueues.php';
require_once plugin_dir_path(__FILE__).'/functions/data-request.php';
require_once plugin_dir_path(__FILE__).'/functions/tools.php';
require_once plugin_dir_path(__FILE__).'/functions/widgets.php';
require_once plugin_dir_path(__FILE__).'/functions/cron.php';
require_once plugin_dir_path(__FILE__).'/functions/api.php';
require_once plugin_dir_path(__FILE__).'/dashboard/templates.php';
require_once plugin_dir_path(__FILE__).'/dashboard/dashboard.php';
if ( doliCheckModules('product') || doliCheckModules('service') ) {
    require_once plugin_dir_path(__FILE__).'/functions/product.php';
}
if ( doliCheckModules('adherent') ) {
    require_once plugin_dir_path(__FILE__).'/functions/member.php';
}
require_once plugin_dir_path(__FILE__).'/admin/admin.php'; 
require_once plugin_dir_path(__FILE__).'/blocks/index.php';

//add_action( 'plugins_loaded', 'doliconnect_run66', 10, 0 );
/*
function doliconnect_run66() {
	if ( file_exists( WP_CONTENT_DIR . '/maintenance.php' ) ) {
		require_once WP_CONTENT_DIR . '/maintenance.php';
		die();
	}

	require_once ABSPATH . WPINC . '/functions.php';
	wp_load_translations_early();

	header( 'Retry-After: 600' );

	wp_die(
		__( 'Briefly unavailable for scheduled maintenance. Check back in a minute.' ),
		__( 'Maintenance' ),
		503
	);
}
*/

// ********************************************************
/*
 * Prepare futur with API connector
 * 
 * @since 10.0.4
 */
/*
add_action( 'wp_connectors_init', function ( WP_Connector_Registry $registry ) {
    ///if ( $registry->is_registered( 'dolibarr' ) ) {
        //$connector = $registry->unregister( 'dolibarr' );
        $connector['name'] = __( 'Dolibarr', 'doliconnect' );
        $connector['description'] = __( 'Connect Wordpress with Dolibarr', 'doliconnect' );
        $connector['type'] = 'erp_crm';
        $connector['authentication'] = array(
				'method'          => 'api_key',
				'credentials_url' => '',
				'setting_name'    => '',
				'constant_name'   => '',
			);
        $connector['plugin'] = array(
				'file'      => 'doliconnect/doliconnect.php',
                'is_active' => function () {
						return 5;
					},
			);
         $connector['settings'] = array(
                'api_key' => array(
                    'label'       => __( 'API Key', 'doliconnect' ),
                    'type'        => 'text',
                    'description' => __( 'Enter your API key from My Custom API.', 'doliconnect' ),
                    'required'    => true,
                ),
                'api_url' => array(
                    'label'       => __( 'API URL', 'doliconnect' ),
                    'type'        => 'url',
                    'description' => __( 'Base URL of the API endpoint.', 'doliconnect' ),
                    'required'    => true,
                ),
            );
        $registry->register( 'dolibarr', $connector );
    //}
} );
*/
// ********************************************************

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'doliconnect_settings_action_links', 10, 2 );
function doliconnect_settings_action_links( $links, $file ) {
  // lien vers les widgets
  //$mylink = '<a href="' . admin_url( 'widgets.php' ) . '">' . __( 'Widgets' ) . '</a>'; 
  //array_push( $links, $mylink );

    // liens vers les articles
  //$links[] = '<a href="' . admin_url( 'edit.php' ) . '">' . __( 'Posts' ) . '</a>';

  // lien vers la page de config de ce plugin
  array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=ptibogxiv_management_page' ) . '">' . __( 'Settings' ) . '</a>' );

  return $links;
}

add_filter( 'plugin_row_meta', 'doliconnect_plugin_row_meta', 10, 2 );
function doliconnect_plugin_row_meta( $links, $file ) {    
    if ( plugin_basename( __FILE__ ) == $file ) {
        $row_meta = array(
            'docs'    => '<a href="' . esc_url( 'https://ptibogxiv.eu' ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'doliconnect' ) . '" style="color:green;">' . esc_html__( 'Docs', 'doliconnect' ) . '</a>',
            'github'    => '<a href="' . esc_url( 'https://github.com/ptibogxiv/doliconnect' ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'doliconnect' ) . '" style="color:green;">' . esc_html__( 'GitHub', 'doliconnect' ) . '</a>',
        );
        return array_merge( $links, $row_meta );
    }
    return (array) $links;
}

// ********************************************************

function doliconnecturl($page) {
    if (empty($page)) {
        return null;
    } elseif ( function_exists('pll_get_post') ) { 
        return esc_url(get_permalink(pll_get_post(get_option($page))));
    } elseif ( function_exists('wpml_object_id') ) {
        return esc_url(get_permalink(apply_filters( 'wpml_object_id', get_option($page), 'page', true)));
    } else {
        return esc_url(get_permalink(get_option($page)));
    }  
}

function doliconnectid($page) {
    if (empty($page)) {
        return null;
    } elseif (function_exists('pll_get_post')) { 
        return pll_get_post(get_option($page));
    } elseif ( function_exists('wpml_object_id') ) {
        return apply_filters( 'wpml_object_id', get_option($page), 'page', true);
    } else {
        return get_option($page);
    }  
}
// ********************************************************
add_action('init', 'app_output_buffer');
function app_output_buffer() {
global $current_user;
//ob_start();
if ( is_user_logged_in() && !is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !empty(get_option('doliconnectrestrict_role')) ) {
if ( is_multisite() ) {
add_user_to_blog(get_current_blog_id(), $current_user->ID, get_option('doliconnectrestrict_role'));
} else {
$current_user->set_role(get_option('doliconnectrestrict_role'));
}
}
} 
// ********************************************************
add_action( 'admin_init', 'dolibarr_entity', 5);
function dolibarr_entity( $entity = null ) {

if ( !empty($entity) ) {
return $entity;
} elseif ( get_site_option('dolibarr_entity') && get_option('dolibarr_entity') ) {
return get_option('dolibarr_entity');
} else {
return get_current_blog_id();
}
//return get_current_network_id();
}
// ********************************************************
function doliconst( $constante, $refresh = null ) {
    $const = callDoliApi("GET", "/setup/conf/".$constante, null, dolidelay('constante', $refresh));
    if (!isset($const->error) && $const != null) {
        return $const;
    } else {
        return null; 
    }
}
// ********************************************************
add_action( 'wp_head', 'doliconnect_run', 10, 0 );
function doliconnect_run() {
$array=array();
if ( !empty(doliconnectid('doliaccount')) ) { $array[]=doliconnectid('doliaccount'); }
if ( !empty(doliconnectid('dolicart')) ) { $array[]=doliconnectid('dolicart'); }
if ( !empty(doliconnectid('dolicontact')) ) { $array[]=doliconnectid('dolicontact'); }
if ( !empty($array) && is_page( $array ) ) {
if ( !defined ('DONOTCACHEPAGE') ) {
define( 'DONOTCACHEPAGE', 1);
}
} elseif (!is_user_logged_in() && !empty(get_option('doliconnectrestrict')) ) { 
define( 'DONOTCACHEPAGE', 1);
}
}
// ********************************************************
// Add the Dolibarr API call function
// This function is used to call the Dolibarr API with the specified method, link,
// body, delay, and entity. It handles caching the response using transients.
// It also logs the request and response in debug mode.
// It returns the response as a JSON object, or an error if the API call fails.
// It is used to interact with the Dolibarr API from WordPress.
// It is called by various functions in the plugin to retrieve or update data in Dolibarr
// such as products, services, contacts, etc.
//// @param string $method The HTTP method to use for the API call (GET, POST, PUT, DELETE).
//// @param string $link The API endpoint to call.
//// @param mixed $body The body of the request, if applicable (for POST or PUT requests).
//// @param int $delay The delay in seconds for caching the response. Default is 1 hour (HOUR_IN_SECONDS).
//// @param int $entity The Dolibarr entity ID to use for the API call. Default is null, which uses the current blog ID.
//// @return object The response from the Dolibarr API as a JSON object, or an error if the API call fails.
// @since 9.2.3
// @version 9.2.3
// ********************************************************

function callDoliApi($method = null, $link = null, $body = null, $delay = HOUR_IN_SECONDS, $entity = null) {
    if ( empty($method) || empty($link) || empty(get_site_option('dolibarr_public_url')) || empty(get_site_option('dolibarr_private_key')) ) {
        if ( ! defined( 'DOLIBUG' ) ) {
            define( 'DOLIBUG', 1 );
        }
        return null;
    }

    $method = strtoupper( $method );
    $url = rtrim( get_site_option( 'dolibarr_public_url' ), '/' ) . '/api/index.php' . $link;
    $cache_key = 'doliconnect_api_' . md5( $method . '|' . $url . '|' . dolibarr_entity( $entity ) );

    if ( 'GET' === $method && $delay > 0 ) {
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            if ( is_object( $cached ) ) {
                $cached->request = $link;
            }
            return $cached;
        }
    }

    $args = array(
        'timeout'     => 10,
        'redirection' => 5,
        'method'      => $method,
        'sslverify'   => true,
        'headers'     => array(
            'DOLAPIKEY'    => get_site_option( 'dolibarr_private_key' ),
            'DOLAPIENTITY' => dolibarr_entity( $entity ),
        ),
    );

    if ( in_array( $method, array( 'POST', 'PUT', 'DELETE', 'PATCH' ), true ) && null !== $body ) {
        $args['body'] = $body;
    }

    $request = wp_remote_request( esc_url_raw( $url ), $args );
    if ( is_wp_error( $request ) ) {
        if ( ! defined( 'DOLIBUG' ) ) {
            define( 'DOLIBUG', 1 );
        }
        return null;
    }

    $http_code = wp_remote_retrieve_response_code( $request );
    $response_body = wp_remote_retrieve_body( $request );
    $response = null;

    if ( '' !== trim( $response_body ) ) {
        $response = json_decode( $response_body );
        if ( JSON_ERROR_NONE !== json_last_error() && WP_DEBUG ) {
            error_log( sprintf( 'Doliconnect API JSON error: %s | request: %s', json_last_error_msg(), $url ) );
        }
    }

    if ( 'DELETE' === $method || $delay <= 0 || ! in_array( $http_code, array( 200, 404 ), true ) ) {
        delete_transient( $cache_key );
        if ( ! in_array( $http_code, array( 200, 400, 404, 600 ), true ) && ! defined( 'DOLIBUG' ) ) {
            define( 'DOLIBUG', $http_code );
        }
    } elseif ( 'GET' === $method ) {
        set_transient( $cache_key, $response, absint( $delay ) );
    }

    if ( is_object( $response ) ) {
        $response->request = $link;
    }

    return $response;
}

// ********************************************************

function doliconnector($current_user = null, $value = null, $refresh = false, $thirdparty = null) {
    if ( empty($current_user) ) {
        $current_user = wp_get_current_user();
    }
    if ( $current_user ) { 
        $dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', $refresh));
        if ( defined("DOLIBUG") || (is_object($dolibarr) && ! empty($dolibarr->fk_soc) ) )  {
            if ( ! empty($value) && isset($dolibarr->$value) ) {
                return $dolibarr->$value;
            } elseif ( ! empty($value) && !isset($dolibarr->$value) ) {
                return null;
            } else {
                return $dolibarr;
            }
        } else {
            $dolibarr = callDoliApi("POST", "/doliconnector/".$current_user->ID, $thirdparty, dolidelay('doliconnector', true));

            if ( ! empty($value) ) {
                return (isset($dolibarr->$value) ? $dolibarr->$value : null );
            } else {
                return $dolibarr;
            }
        }
    }
}

// ********************************************************
/* Bloquer acces aux non-admins */
add_action('init', 'doliconnect_block_dashboard');
function doliconnect_block_dashboard() {
	$file = basename($_SERVER['PHP_SELF']);
	if (is_user_logged_in() && is_admin() && !current_user_can('edit_posts') && $file != 'admin-ajax.php') {
		wp_redirect( doliconnecturl('doliaccount') );
		exit();
	}
}
// ********************************************************
add_filter( 'pll_custom_flag', 'doliconnect_pll_custom_flag', 10, 2 );
function doliconnect_pll_custom_flag( $flag, $code ) {
    $flag['url']    = esc_url( plugins_url( '/includes/flag-icon-css/flags/4x3/'.$code.'.svg', dirname(__FILE__) ) );
    $flag['width']  = 24;
    $flag['height'] = 18;
    return $flag;
}
// ********************************************************
add_filter( 'get_avatar' , 'doliconnect_custom_avatar' , 1 , 5 );
function doliconnect_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
    global $wpdb;
    $user = false;
    $switched_blog = false;

    if ( get_site_option( 'doliconnect_mode' ) === 'one' && is_multisite() ) {
        switch_to_blog( 1 );
        $switched_blog = true;
    }

    if ( is_numeric( $id_or_email ) ) {
        $id = (int) $id_or_email;
        $user = get_user_by( 'id', $id );
    } elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id', $id );
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( $user && is_object( $user ) ) {
        if ( $size == 96 || $size === '96' ) {
            $taille = " class='card-img-top' ";
        } else {
            $taille = sprintf( ' class="%s" height="%d" width="%d" ', esc_attr( 'rounded-circle border border-white' ), absint( $size ), absint( $size ) );
        }

        $entity = get_current_blog_id();
        $table_prefix = $wpdb->get_blog_prefix( $entity );
        $nam = $table_prefix . 'member_photo';

        if ( isset( $user->$nam ) && null !== $user->$nam ) {
            $upload_dir = wp_upload_dir();
            $filename = trailingslashit( $upload_dir['baseurl'] ) . 'doliconnect/' . $user->data->ID . '/' . $user->$nam;
            $avatar   = sprintf(
                '<img src="%s"%s alt="%s">',
                esc_url( $filename ),
                $taille,
                esc_attr( 'avatar-' . $user->data->ID )
            );
        } else {
            $avatar = sprintf(
                '<img src="%s"%s alt="%s">',
                esc_url( plugins_url( 'images/default.jpg', __FILE__ ) ),
                $taille,
                esc_attr( 'avatar-default' )
            );
        }
    } elseif ( ! is_user_logged_in() && ! empty( get_option( 'doliconnectrestrict' ) ) ) {
        $taille = " class='card-img' ";
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $custom_logo_attr = array(
                'class' => 'card-img',
            );

            $image_alt = get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true );
            if ( empty( $image_alt ) ) {
                $custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
            }

            $avatar = wp_get_attachment_image( $custom_logo_id, 'medium_large', false, $custom_logo_attr );
        } elseif ( is_customize_preview() ) {
            $avatar = sprintf(
                '<img src="%s"%s alt="%s">',
                esc_url( plugins_url( 'images/default.jpg', __FILE__ ) ),
                $taille,
                esc_attr( 'avatar-default' )
            );
        }
    } else {
        $taille = " class='card-img' ";
        $avatar = sprintf(
            '<img src="%s"%s alt="%s">',
            esc_url( plugins_url( 'images/default.jpg', __FILE__ ) ),
            $taille,
            esc_attr( 'avatar-default' )
        );
    }

    if ( $switched_blog ) {
        restore_current_blog();
    }

    return $avatar;
}

// ********************************************************
add_action('wp_dolibarr_sync','update_synctodolibarr', 1, 2);
function update_synctodolibarr($object, $user = null) {
global $current_user;

    if (!empty($user)) {
        $current_user = $user;
    }

    $thirdparty = doliConnect('thirdparty', $current_user);
    if ( isset($thirdparty->id) && $thirdparty->id > 0 ) {
        $thirparty = callDoliApi("PUT", "/thirdparties/".$thirdparty->id, $object, 0);
    }

    $member = doliConnect('thirdparty', $current_user);
    if ( isset($member->id) && $member->id > 0 ) {
        $member = callDoliApi("PUT", "/members/".$member->id, $object, 0);
        //update_user_meta( $current_user->ID, 'billing_birth', $current_user->billing_birth);
    }
}
// ********************************************************
add_filter( 'template_include', 'doliconnect_accessrestricted' );

function doliconnect_accessrestricted( $template )
{
    global $current_user;
    if (!empty(get_option('doliconnectrestrict')) && defined("DOLICONNECT_EVICTIONRESTRICTEDPAGEID") && is_array(constant("DOLICONNECT_EVICTIONRESTRICTEDPAGEID"))) {
    $eviction = constant("DOLICONNECT_EVICTIONRESTRICTEDPAGEID");
    } else {
    $eviction = array();
    }
    if ( (!empty(get_option('doliconnectrestrict')) && !is_user_logged_in() && !in_array(get_the_ID(), $eviction)) || (!empty(get_option('doliconnectrestrict')) && !is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !in_array(get_the_ID(), $eviction)) ) {
        $file_name = 'restricted.php';
        if ( locate_template( $file_name ) ) {
            $template = locate_template( $file_name );
        } else {
            $template = plugin_dir_path( __FILE__ ) . 'templates/'. $file_name;
        }
    }
    return $template;
}
// ********************************************************
add_filter( 'cron_schedules', 'doliconnect_add_cron_interval' );
function doliconnect_add_cron_interval( $schedules ) { 
    $schedules['fifteen_minutes'] = array(
        'interval' => 900,
        'display'  => esc_html__( 'Every 15 minutes' ), );
    return $schedules;
}
// ********************************************************
register_activation_hook( __FILE__, 'doliconnect_plugin_activation' );
function doliconnect_plugin_activation($network_wide){
    if($network_wide){ //Plugin is network activated
        $site_ids = get_sites(array('fields' => 'ids'));
        foreach($site_ids as $site_id){
            //Perform something on all sites within the network
            switch_to_blog($site_id);
            //flush_rewrite_rules();
            if( ! wp_next_scheduled( 'doliconnect_cron_hook' ) ) {
            wp_schedule_event( current_time( 'timestamp', 1), 'fifteen_minutes', 'doliconnect_cron_hook' );
            }
            restore_current_blog();
        }
        return;
    } else {
        //flush_rewrite_rules();
        if( ! wp_next_scheduled( 'doliconnect_cron_hook' ) ) {
            wp_schedule_event( current_time( 'timestamp', 1), 'fifteen_minutes', 'doliconnect_cron_hook' );
        }
    }
}

// ********************************************************
register_deactivation_hook( __FILE__, 'doliconnect_plugin_desactivation' );
function doliconnect_plugin_desactivation($network_wide){
    if($network_wide){ //Plugin is network activated
        $site_ids = get_sites(array('fields' => 'ids'));
        foreach($site_ids as $site_id){
            //Perform something on all sites within the network
            switch_to_blog($site_id);
            wp_clear_scheduled_hook( 'doliconnect_cron_hook' );
            restore_current_blog();
        }
        return;
    } else {
        wp_clear_scheduled_hook( 'doliconnect_cron_hook' );
    }
}
// ********************************************************
if (get_option('doliaccount')) {
    add_filter( 'login_url', 'doliconnect_login_link_url', 80, 3 );
}
function doliconnect_login_link_url( $login_url, $redirect, $force_reauth ) {
    if ( ! empty( $redirect ) ) {
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), wp_login_url( ) );
    }
    if ( $force_reauth ) {
        $login_url = add_query_arg( 'reauth', '1', $login_url );
    }
    return $login_url;
    }
// ********************************************************    
/*
add_filter( 'login_redirect', function( $url, $query, $user ) {
	return home_url();
}, 90, 3 );
*/
// ********************************************************
/*
add_action( 'wp_login_failed', 'doliconnect_account_login_fail' );
function doliconnect_account_login_fail( $username ) { 
    if ( isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'],'wp-login') && !strstr($_SERVER['HTTP_REFERER'],'wp-admin') ) {
        wp_redirect( esc_url( add_query_arg( 'login', 'failed', doliconnecturl('doliaccount')) ) );
        exit;
    }
}
*/
// ********************************************************
add_filter( 'logout_url', 'doliconnect_logout_url', 10, 2 );
function doliconnect_logout_url( $logout_url, $redirect ) {
    $logout_url = add_query_arg( 'action', 'logout', wp_login_url( ) );
    if ( ! empty( $redirect ) ) {
        $logout_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
    }
    $logout_url = wp_nonce_url( $logout_url, 'log-out' );
    return $logout_url;
}
// ********************************************************
if (get_option('doliaccount')) {
    add_filter( 'lostpassword_url', 'doliconnect_lost_password_page', 80, 1);
}
function doliconnect_lost_password_page( $lostpassword_url ) {
    return esc_url( add_query_arg( 'action', 'fpw', doliconnecturl('doliaccount')) ); 
}
// ********************************************************
function passresetmodif_login ($url, $redirect) { 
    $args = array( 'action' => 'lostpassword' );
    if ( !empty($redirect) ) $args['redirect_to'] = $redirect;
    return add_query_arg( $args, wp_login_url( ) );
}
// ********************************************************
if (get_option('doliaccount')) {
    add_filter( 'register_url', 'doliconnect_register_page', 80, 1);
}
function doliconnect_register_page( $register_url ) {
    return esc_url( add_query_arg( 'action', 'register', doliconnecturl('doliaccount')) ); 
}
// ********************************************************
// Redirect wp-signup.php to a custom page
add_filter( 'before_signup_header', function( $url ) {
	wp_redirect( wp_registration_url() );
    die();
}, 10, 1 );
// ********************************************************
add_filter('asgarosforum_filter_profile_link', 'doliconnect_profile_url', 10, 2);
function doliconnect_profile_url($profile_url, $user_object) {
    return doliconnecturl('doliaccount');
}

?>