<?php
/**
 * Plugin Name: Doliconnect
 * Plugin URI: https://www.ptibogxiv.eu
 * Description: Connect your Dolibarr (free ERP/CRM) to Wordpress. 
 * Version: 8.6.0
 * Author: ptibogxiv
 * Author URI: https://www.ptibogxiv.eu
 * Network: true
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: doliconnect
 * Domain Path: /languages
 * Donate link: https://www.paypal.me/ptibogxiv
 *   
 * @author ptibogxiv.net <support@ptibogxiv.net>
 * @copyright Copyright (c) 2017-2023, ptibogxiv.eu
**/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
  
add_action( 'plugins_loaded', 'doliconnect_textdomain' ); 
function doliconnect_textdomain() {
    load_plugin_textdomain( 'doliconnect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

require_once plugin_dir_path(__FILE__).'/functions/enqueues.php';
require_once plugin_dir_path(__FILE__).'/functions/data-request.php';
require_once plugin_dir_path(__FILE__).'/functions/tools.php';
require_once plugin_dir_path(__FILE__).'/functions/widgets.php';
require_once plugin_dir_path(__FILE__).'/functions/cron.php';
require_once plugin_dir_path(__FILE__).'/functions/member.php';
require_once plugin_dir_path(__FILE__).'/dashboard/templates.php';
require_once plugin_dir_path(__FILE__).'/dashboard/dashboard.php';
require_once plugin_dir_path(__FILE__).'/functions/product.php';
require_once plugin_dir_path(__FILE__).'/admin/admin.php'; 
require_once plugin_dir_path(__FILE__).'/blocks/index.php';
//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('DOLIBARR_MINIMUM_VERSION', '14.0.0');
define('DOLIBARR_LEGAL_VERSION', '19.0.3');

// ********************************************************
function doliconnecturl($page) {
global $wpdb;
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
global $wpdb;
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
    if (doliversion('13.0.0')){ 
        $const = callDoliApi("GET", "/setup/conf/".$constante, null, dolidelay('constante', $refresh));
        if (!isset($const->error) && $const != null) {
            return $const;
        } else {
            return null; 
        }
    } else {
        $const = callDoliApi("GET", "/doliconnector/constante/".$constante, null, dolidelay('constante', $refresh));
        return isset($const->value)?$const->value:null;
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
function json_basic_auth_handler( $user ) {
	global $wp_json_basic_auth_error;
	$wp_json_basic_auth_error = null;

	if ( ! empty( $user ) ) {
		return $user;
	}

	if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
		return $user;
	}
	$username = $_SERVER['PHP_AUTH_USER'];
	$password = $_SERVER['PHP_AUTH_PW'];

	remove_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
	$user = wp_authenticate( $username, $password );
	add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
	if ( is_wp_error( $user ) ) {
		$wp_json_basic_auth_error = $user;
		return null;
	}
	$wp_json_basic_auth_error = true;
	return $user->ID;
}
add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
function json_basic_auth_error( $error ) {
	// Passthrough other errors
	if ( ! empty( $error ) ) {
		return $error;
	}
	global $wp_json_basic_auth_error;
	return $wp_json_basic_auth_error;
}
add_filter( 'rest_authentication_errors', 'json_basic_auth_error' );
// ********************************************************
add_action( 'admin_init', 'callDoliApi', 5, 5); 
function callDoliApi($method = null, $link = null, $body = null, $delay = HOUR_IN_SECONDS, $entity = null) {
//echo $link;
$headers = array(
        'DOLAPIENTITY' => dolibarr_entity($entity),
        'DOLAPIKEY' => get_site_option('dolibarr_private_key')
    );

$url=get_site_option('dolibarr_public_url').'/api/index.php'.$link;

if ( !empty(get_site_option('dolibarr_public_url')) && !empty(get_site_option('dolibarr_private_key')) ) {
if ( !empty( $link ) && ( false ===  get_transient( $link ) || $method!='GET' || $delay <= 0 ) ) {

$args = array(
    'timeout' => '10',
    'redirection' => '5',
    'method' => $method,
    'sslverify' => true,
    'headers' => $headers
); 

if ( $method == 'POST' ) {
$args['body'] = $body;
delete_transient( $link );  
$request = wp_remote_post( esc_url_raw($url), $args );
} elseif ( $method == 'PUT' ) {
$args['body'] = $body;
delete_transient( $link ); 
$request = wp_remote_request( esc_url_raw($url), $args );
} elseif ( $method == 'DELETE' ) { 
$request = wp_remote_request( esc_url_raw($url), $args );
} else {
$request = wp_remote_get( esc_url_raw($url), $args );
}

$http_code = wp_remote_retrieve_response_code( $request );

if (true === WP_DEBUG) {
    if (is_array($request) || is_object($request)) {
        error_log(print_r(json_decode( wp_remote_retrieve_body($request)), true));
    } else {
        error_log(json_decode( wp_remote_retrieve_body($request)));
    }
}

if ( $method == 'DELETE' ) {
delete_transient( $link ); 
} elseif ( $delay <= 0 || ! in_array( $http_code,array('200', '404') ) ) {
delete_transient( $link );

if (! in_array($http_code,array('200', '400', '404', '600')) ) {

    if ( !defined("DOLIBUG") ) {
        define('DOLIBUG', $http_code);
    }

} elseif ( $delay != 0 ) {
$delay = abs( intval($delay) );
set_transient( $link, wp_remote_retrieve_body( $request ), $delay);
}
            } else {
                set_transient( $link, wp_remote_retrieve_body( $request ), $delay );
            }
            return json_decode( wp_remote_retrieve_body( $request ) );
        } else {
            return json_decode( get_transient( $link ) );   
        }
    } else {
        if ( !defined("DOLIBUG") ) {
            define('DOLIBUG', 1);
        }
    }
}
// ********************************************************
add_action( 'init', 'dolibarr', 10);
function dolibarr() {
global $current_user;  

if ( is_user_logged_in() ) { 
    $user=get_current_user_id(); 
    $dolibarr = callDoliApi("GET", "/doliconnector/".$user, null, dolidelay('doliconnector', false));

    if ( defined("DOLIBUG") || !is_object($dolibarr) ) {
        define('DOLIBARR', null);
        define('PRICE_LEVEL', 0);
        define('REMISE_PERCENT', 0);
        define('DOLIBARR_MEMBER', null);
        define('DOLIBARR_TRAINEE', null);
        define('DOLIBARR_USER', null);
        define('DOLICONNECT_CART', 0);
        define('DOLICONNECT_CART_ITEM', 0); 
    } else {  
        if ( empty($dolibarr->fk_soc) ) {
            if ( $current_user->billing_type == 'mor' ) { 
            if (!empty($current_user->billing_company)) { $name = $current_user->billing_company; }
            else { $name = $current_user->user_login; }
            } else {
            if (!empty($current_user->user_firstname) && !empty($current_user->user_lastname)) { $name = $current_user->user_firstname." ".$current_user->user_lastname; }
            else { $name = $current_user->user_login; }
            } 

            $rdr = [
                'name'  => $name,
                'email' => $current_user->user_email,
                'client' => 1,
                'status' => 1,
                ];
            $dolibarr = callDoliApi("POST", "/doliconnector/".$user, $rdr, dolidelay('doliconnector'));
            define('DOLIBARR', $dolibarr->fk_soc);
        } else {   
            define('DOLIBARR', $dolibarr->fk_soc);
        }
        if (isset($dolibarr->price_level)) define('PRICE_LEVEL', $dolibarr->price_level);
        if (isset($dolibarr->remise_percent)) define('REMISE_PERCENT', $dolibarr->remise_percent);
        if (isset($dolibarr->fk_member)) define('DOLIBARR_MEMBER', $dolibarr->fk_member);
        if (isset($dolibarr->fk_trainee)) define('DOLIBARR_TRAINEE', $dolibarr->fk_trainee);
        if (isset($dolibarr->fk_user)) define('DOLIBARR_USER', $dolibarr->fk_user); 
        define('DOLICONNECT_CART', $dolibarr->fk_order);
        define('DOLICONNECT_CART_ITEM', $dolibarr->fk_order_nb_item);
    } 
} else {     
    define('DOLIBARR', null);
    define('PRICE_LEVEL', 0);
    define('REMISE_PERCENT', 0);
    define('DOLIBARR_MEMBER', null);
    define('DOLIBARR_TRAINEE', null);
    define('DOLIBARR_USER', null);
    define('DOLICONNECT_CART', 0);
    define('DOLICONNECT_CART_ITEM', 0);
} 
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
/*add_filter( 'pll_custom_flag', 'doliconnect_pll_custom_flag', 10, 2 );
function doliconnect_pll_custom_flag( $flag, $code ) {
    $flag['url']    = esc_url( plugins_url( '/includes/flag-icon-css/flags/4x3/'.$code.'.svg', dirname(__FILE__) ) );
    $flag['width']  = 24;
    $flag['height'] = 18;
    return $flag;
}*/
// ********************************************************
add_filter( 'get_avatar' , 'doliconnect_custom_avatar' , 1 , 5 );
function doliconnect_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
global $wpdb;    
    $user = false;
if (get_site_option('doliconnect_mode')=='one' && is_multisite() ) {
switch_to_blog(1);
}      
    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );	
    }

    if ( $user && is_object( $user ) ) {

$avatar = 'YOUR_NEW_IMAGE_URL';

if ($size=='96') {
$taille=" class='card-img-top' ";
} else {
$taille=" class='rounded-circle border border-white' height='{$size}' width='{$size}' ";   
}
$entity = get_current_blog_id();
$table_prefix = $wpdb->get_blog_prefix( $entity ); 
$nam=$table_prefix."member_photo";
if (isset($user->$nam) && NULL != $user->$nam) {
$upload_dir = wp_upload_dir(); 
$filename=$upload_dir['baseurl']."/doliconnect/".$user->data->ID."/".$user->$nam;
$avatar = "<img src='$filename' ".$taille." alt='avatar-".$user->data->ID."'>";
} else { 
$avatar = "<img src='" . plugins_url( 'images/default.jpg', __FILE__ ) . "' ".$taille."  alt='avatar-default'>";
}               
} elseif ( !is_user_logged_in() && !empty(get_option('doliconnectrestrict')) ) {
$taille=" class='card-img' ";
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
        $avatar = "<img src='" . plugins_url( 'images/default.jpg', __FILE__ ) . "' ".$taille."  alt='avatar-default'>";
    }
} else {
$taille=" class='card-img' ";
$avatar = "<img src='" . plugins_url( 'images/default.jpg', __FILE__ ) . "' ".$taille."  alt='avatar-default'>";
}
if ( get_site_option('doliconnect_mode')=='one' && is_multisite() ) {
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

if ( doliconnector($current_user, 'fk_soc') > 0 ) {
$thirparty = callDoliApi("PUT", "/thirdparties/".doliconnector($current_user, 'fk_soc'), $object, 0);
}

if (NULL != doliconnector($current_user, 'fk_member')) { 
$adherent = callDoliApi("PUT", "/members/".doliconnector($current_user, 'fk_member'), $object, 0);
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
    $template = plugin_dir_path( __FILE__ ) . 'templates/restricted.php';
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
            if( ! wp_next_scheduled( 'doliconnect_cron_hook' ) ) {
            wp_schedule_event( current_time( 'timestamp', 1), 'fifteen_minutes', 'doliconnect_cron_hook' );
            }
            restore_current_blog();
        }
        return;
    }
    if( ! wp_next_scheduled( 'doliconnect_cron_hook' ) ) {
        wp_schedule_event( current_time( 'timestamp', 1), 'fifteen_minutes', 'doliconnect_cron_hook' );
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
    }
    wp_clear_scheduled_hook( 'doliconnect_cron_hook' );
}
// ********************************************************
// outils de personnalisation et utilisation du module
function doliconnect_login_logo_url() {
return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'doliconnect_login_logo_url' );

function doliconnect_login_logo_url_title() {
    return 'nom du site';
}
add_filter( 'login_headertext', 'doliconnect_login_logo_url_title' );

// Hide Author EVERYWHERE
add_filter( 'generate_post_author','generate_modify_author_display' );
function generate_modify_author_display()
{
    //if ( is_single() )
    //    return true;
return false;
}
// ********************************************************
if (get_option('doliaccount')) {
    add_filter( 'register_url', 'doliconnect_register_page', 80, 1);
}
function doliconnect_register_page( $register_url ) {
    return esc_url( add_query_arg( 'action', 'signup', doliconnecturl('doliaccount')) ); 
}
// ********************************************************
if (get_option('doliaccount')) {
    add_filter( 'lostpassword_url', 'doliconnect_lost_password_page', 80, 1);
}
function doliconnect_lost_password_page( $lostpassword_url ) {
    return esc_url( add_query_arg( 'action', 'fpw', doliconnecturl('doliaccount')) ); 
}
// ********************************************************
if (get_option('doliaccount')) {
    add_filter( 'login_url', 'doliconnect_login_link_url', 80, 3 );
}
function doliconnect_login_link_url( $login_url, $redirect, $force_reauth ) {
    if (get_option('doliaccount') && !preg_match('/action=confirm_admin_email/i', $redirect)) {
        $login_url = doliconnecturl('doliaccount');
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
    } elseif (preg_match('/action=confirm_admin_email/i', $redirect)) {
        if ( function_exists('secupress_get_module_option') && !empty(get_site_option('secupress_active_submodule_move-login')) && secupress_get_module_option('move-login_slug-login', null, 'users-login' )) {
            $login_url = site_url()."/".secupress_get_module_option('move-login_slug-login', null, 'users-login' ); 
        } elseif (get_site_option('doliconnect_login')) {
            $login_url = site_url()."/".get_site_option('doliconnect_login');
        } else {
            $login_url = site_url()."/wp-login.php"; 
        }
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
    }
    if ( ! empty( $redirect ) ) {
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
    }
    if ( $force_reauth ) {
        $login_url = add_query_arg( 'reauth', '1', $login_url );
    }
    return $login_url;
    }
// ********************************************************
add_filter( 'logout_url', 'doliconnect_logout_url', 10, 2 );
function doliconnect_logout_url( $logout_url, $redirect ) {
    if (get_site_option('doliconnect_login')) {
        $logout_url = site_url()."/".get_site_option('doliconnect_login');
    } else {
        $logout_url = site_url()."/wp-login.php";
    };
    $logout_url = add_query_arg( 'action', 'logout', $logout_url );
    if ( ! empty( $redirect ) ) {
        $logout_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
    }
    $logout_url = wp_nonce_url( $logout_url, 'log-out' );
    return $logout_url;
}
// ********************************************************
add_filter('asgarosforum_filter_profile_link', 'doliconnect_profile_url', 10, 2);
function doliconnect_profile_url($profile_url, $user_object) {
    return doliconnecturl('doliaccount');
}
// ********************************************************
add_action( 'wp_login_failed', 'doliconnect_account_login_fail' );
function doliconnect_account_login_fail( $username ) { 
    if ( isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'],'wp-login') && !strstr($_SERVER['HTTP_REFERER'],'wp-admin') ) {
        wp_redirect( esc_url( add_query_arg( 'login', 'failed', doliconnecturl('doliaccount')) ) );
        exit;
    }
}
// ********************************************************
function passresetmodif_login ($url, $redirect) { 
    if (get_site_option('doliconnect_login')) {
        $login_url=site_url()."/" . get_site_option('doliconnect_login');
    } else {
        $login_url=site_url()."/wp-login.php"; 
    }
    $args = array( 'action' => 'lostpassword' );
    if ( !empty($redirect) ) $args['redirect_to'] = $redirect;
    return add_query_arg( $args, $login_url );
}

?>