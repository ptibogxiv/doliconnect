<?php
/**
 * Plugin Name: Doliconnect
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Connect your Dolibarr (free ERP/CRM) to Wordpress. 
 * Version: 3.9.1
 * Author: ptibogxiv
 * Author URI: https://www.ptibogxiv.net/en
 * Network: true
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: doliconnect
 * Domain Path: /languages
 * Donate link: https://www.paypal.me/ptibogxiv
 *   
 * @author ptibogxiv.net <support@ptibogxiv.net>
 * @copyright Copyright (c) 2017-2019, ptibogxiv.net
**/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  
 
function doliconnect_textdomain() {
    load_plugin_textdomain( 'doliconnect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'doliconnect_textdomain' );

require_once plugin_dir_path(__FILE__).'/functions/enqueues.php';
require_once plugin_dir_path(__FILE__).'/functions/data-request.php';
require_once plugin_dir_path(__FILE__).'/functions/tools.php';
require_once plugin_dir_path(__FILE__).'/dashboard/dashboard.php';
require_once plugin_dir_path(__FILE__).'/functions/product.php';
require_once plugin_dir_path(__FILE__).'/admin/admin.php'; 
require_once plugin_dir_path(__FILE__).'/blocks/index.php';
//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// ********************************************************
function doliconnecturl($page) {
global $wpdb;
if ( function_exists('pll_get_post') ) { 
return esc_url(get_permalink(pll_get_post(get_option($page))));
} elseif ( function_exists('wpml_object_id') ) {
return esc_url(get_permalink(apply_filters( 'wpml_object_id', get_option($page), 'page', true)));
} else {
return esc_url(get_permalink(get_option($page)));
}  
}

function doliconnectid($page) {
global $wpdb;
if (function_exists('pll_get_post')) { 
return pll_get_post(get_option($page));
} elseif ( function_exists('wpml_object_id') ) {
return apply_filters( 'wpml_object_id', get_option($page), 'page', true);
} else {
return get_option($page);
}  
}
// ********************************************************
function app_output_buffer() {
ob_start();
} 
add_action('init', 'app_output_buffer');
// ********************************************************
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
function doliconst( $constante ) {
global $wpdb;

$const = callDoliApi("GET", "/doliconnector/constante/".$constante, null, dolidelay('constante'));

return $const->value;
}
// ********************************************************
function doliconnect_run() {
$array=array();
if ( !empty(doliconnectid('doliaccount')) ) { $array[]=doliconnectid('doliaccount'); }
if ( !empty(doliconnectid('dolicart')) ) { $array[]=doliconnectid('dolicart'); }
if ( !empty($array) && is_page( $array ) ) {
if ( !defined ('DONOTCACHEPAGE') ) {
define( 'DONOTCACHEPAGE', 1);
}
}
}
add_action( 'wp_head', 'doliconnect_run', 10, 0 );
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
function callDoliApi($method = null, $link = null, $body = null, $delay = HOUR_IN_SECONDS, $entity = null) {
global $wpdb;

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

if ( $method == 'DELETE' ) {
delete_transient( $link ); 
} elseif ( $delay <= 0 || ! in_array( $http_code,array('200','404') ) ) {
delete_transient( $link );

if (! in_array($http_code,array('200', '400', '404')) ) {

if ( !defined("DOLIBUG") ) {
define('DOLIBUG', $http_code);
}

} elseif ( $delay != 0 ) {
$delay = abs( $delay );
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
add_action( 'admin_init', 'callDoliApi', 5, 5); 
// ********************************************************
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
if ( $dolibarr->fk_soc == 0 ) {

if ( $current_user->billing_type == 'mor' ) { 
if (!empty($current_user->billing_company)) { $name = $current_user->billing_company; }
else { $name = $current_user->user_login; }
} else {
if (!empty($current_user->user_firstname) && !empty($current_user->user_lastname)) { $name = $current_user->user_firstname." ".$current_user->user_lastname; }
else { $name = $current_user->user_login; }
} 

$rdr = [
    'name'  => $name,
    'email' => $current_user->user_email
	];
$dolibarr = callDoliApi("POST", "/doliconnector/".$user, $rdr, dolidelay('doliconnector'));
define('DOLIBARR', $dolibarr->fk_soc);
} else {   
define('DOLIBARR', $dolibarr->fk_soc);}
define('PRICE_LEVEL', $dolibarr->price_level);
define('REMISE_PERCENT', $dolibarr->remise_percent);
define('DOLIBARR_MEMBER', $dolibarr->fk_member);
define('DOLIBARR_TRAINEE', $dolibarr->fk_trainee);
define('DOLIBARR_USER', $dolibarr->fk_user); 
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
add_action( 'init', 'dolibarr', 10);
// ********************************************************
function doliconnector($current_user = null, $value = null, $refresh = false, $thirdparty = null) {
if (empty($current_user)) {
$current_user=get_current_user_id();
} else {
$current_user=$current_user->ID;
}

$user = get_user_by('ID', $current_user);

if ( $user ) { 
//$user=get_current_user_id(); 

$dolibarr = callDoliApi("GET", "/doliconnector/".$user->ID, null, dolidelay('doliconnector', $refresh));

if ( defined("DOLIBUG") || (is_object($dolibarr) && $dolibarr->fk_soc > 0 ) )  {

if (!empty($value)) {
return $dolibarr->$value;
} else {
return $dolibarr;
}
 
} else {

if ( isset($current_user->billing_type) && $current_user->billing_type == 'mor' ) { 
if (!empty($current_user->billing_company)) { $name = $current_user->billing_company; }
else { $name = $current_user->user_login; }
} else {
if (!empty($current_user->user_firstname) && !empty($current_user->user_lastname)) { $name = $current_user->user_firstname." ".$current_user->user_lastname; }
else { $name = $current_user->user_login; }
} 

$rdr = [
    'name'  => $name,
    'email' => $current_user->user_email
	];

$dolibarr = callDoliApi("POST", "/doliconnector/".$user, $thirdparty, dolidelay('doliconnector', true));

if (!empty($value)) {
return $dolibarr->$value;
} else {
return $dolibarr;
}

}

}

}
// ********************************************************
add_filter( 'get_avatar' , 'my_custom_avatar' , 1 , 5 );

function my_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
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

//if ( $user->data->ID == '1' ) {

$avatar = 'YOUR_NEW_IMAGE_URL';
//$avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";

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

function doliaccount_display($content) {
global $wpdb, $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) {

doliconnect_enqueues();

$current_offset = get_option('gmt_offset');
$tzstring = get_option('timezone_string');
$check_zone_info = true;
// Remove old Etc mappings. Fallback to gmt_offset.
if ( false !== strpos($tzstring,'Etc/GMT') )
	$tzstring = '';

if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if ( 0 == $current_offset )
		$tzstring = 'UTC+0';
	elseif ($current_offset < 0)
		$tzstring = 'UTC' . $current_offset;
	else
		$tzstring = 'UTC+' . $current_offset;
}
//define( 'MY_TIMEZONE', (get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : date_default_timezone_get() ) );
//date_default_timezone_set( MY_TIMEZONE );
date_default_timezone_set($tzstring);

$ID = $current_user->ID;
$time = current_time( 'timestamp', 1);

print "<div class='row'><div class='col-xs-12 col-sm-12 col-md-3'><div class='row'><div class='col-3 col-xs-4 col-sm-4 col-md-12 col-xl-12'><div class='card shadow-sm' style='width: 100%'>";
print get_avatar($ID);

if ( is_user_logged_in() && !defined("DOLIBUG") ) {
print "<a href='".esc_url( add_query_arg( 'module', 'avatars', doliconnecturl('doliaccount')) )."' class='card-img-overlay'><div class='d-block d-sm-block d-xs-block d-md-none text-center'><i class='fas fa-camera'></i></div><div class='d-none d-md-block'><i class='fas fa-camera fa-2x'></i> ".__( 'Edit', 'doliconnect' )."</div></a>";
}
print "<ul class='list-group list-group-flush'><a href='".esc_url( doliconnecturl('doliaccount') )."' class='list-group-item list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-home'></i></div><div class='d-none d-md-block'><i class='fas fa-home'></i> ".__( 'Home', 'doliconnect' )."</div></center></a>";
print "</ul>";

print "</div><br></div><div class='col-9 col-xs-8 col-sm-8 col-md-12 col-xl-12'>";

if ( is_user_logged_in() ) {

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( defined("DOLIBUG") ) {

print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'><div class='card shadow-sm'><div class='card-body'>";
print dolibug($thirdparty->error->message);
print "</div></div></div></div>";

} elseif ( $thirdparty->status != '1' ) {

print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'><div class='card shadow-sm'><div class='card-body'>";
print '<br><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>'.__( 'This account is closed. Please contact us for reopen it.', 'doliconnect' ).'</h4></div></center><br><br><br><br><br>';
print "</div></div></div></div>";

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', true));

} else { 

if ( isset($_GET['module']) ) {
//****
if ( has_action('user_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('user_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('user_doliconnect_menu', esc_attr($_GET['module']));
print "</div><br>";
}
print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'user_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('customer_doliconnect_'.esc_attr($_GET['module'])) && $thirdparty->client == '1' ) {
if ( has_action('customer_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('customer_doliconnect_menu', esc_attr($_GET['module']));
print "</div><br>";
}
print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'customer_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('options_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('options_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('options_doliconnect_menu', esc_attr($_GET['module']));
print "</div><br>";
}
print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'options_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('supplier_doliconnect_'.esc_attr($_GET['module'])) && $thirdparty->fournisseur == '1' ) {
if ( has_action('supplier_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('supplier_doliconnect_menu', esc_attr($_GET['module']));
print "</div><br>";
}
print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action('supplier_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('my_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('my_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('my_doliconnect_menu', esc_attr($_GET['module']));
print "</div><br>";
}
print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'my_doliconnect_'.esc_attr($_GET['module']),esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('settings_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('settings_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('settings_doliconnect_menu', esc_attr($_GET['module']));
print "</div><br>";
}
print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'settings_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} else {
wp_redirect( esc_url(doliconnecturl('doliaccount')) );
exit;
}
//****
print "</div>";

} else {

print "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
if ( has_action('user_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('user_doliconnect_menu');
print "</div><br>";
}  

if ( has_action('customer_doliconnect_menu') && $thirdparty->client == '1' ) {
print "<div class='list-group shadow-sm'>";
do_action('customer_doliconnect_menu');
print "</div><br>";
}

if ( has_action('options_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('options_doliconnect_menu');
print "</div><br>";
}

if ( has_action('supplier_doliconnect_menu') && $thirdparty->fournisseur == '1' && get_option('doliconnectbeta')=='1' ) {
print "<div class='list-group shadow-sm'>";
do_action('supplier_doliconnect_menu');
print "</div><br>";
}

if ( has_action('my_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('my_doliconnect_menu');
print "</div><br>";
}

if ( has_action('settings_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('settings_doliconnect_menu');
print "</div><br>";
}

print "</div>";
}
// fin de sous page
print "</div>";
}
} elseif ( !is_user_logged_in() && isset($_GET["signup"]) ) {
print "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";

if ( is_user_logged_in() ) {
wp_redirect(site_url());
exit;
}

if ( is_multisite() && !get_option( 'users_can_register' ) && (get_site_option( 'registration' ) != 'user' or get_site_option( 'registration' ) != 'all') ) {
wp_redirect(esc_url(doliconnecturl('doliaccount')));
exit;
} elseif ( !get_option( 'users_can_register' ) ) {
wp_redirect(esc_url(doliconnecturl('doliaccount')));
exit;
}

if ( isset($_POST['submitted']) ) {

$thirdparty=$_POST['thirdparty'];

if ( email_exists($thirdparty['email']) ) {
        $emailError = "".__( 'This email address is already linked to an account. You can reactivate your account through this <a href=\'".wp_lostpassword_url( get_permalink() )."\' title=\'lost password\'>form</a>.', 'doliconnect' )."";
        $hasError = true;
        } else {
        $email = sanitize_email($thirdparty['email']);
        }

if ( $thirdparty['firstname'] == $_POST['user_nicename'] && $thirdparty['firstname'] == $thirdparty['lastname']) {
        $emailError = "".__( 'Create this account is not permitted', 'doliconnect' )."";       
        $hasError = true;
        }

    if(!isset($hasError)) {
        $emailTo = get_option('tz_email');
        if (!isset($emailTo) || ($emailTo == '') ) {
        $emailTo = get_option('admin_email');
        }

$sitename = get_option('blogname');
$subject = "[".$sitename."] ".__( 'Registration confirmation', 'doliconnect' )."";
if ( !empty($_POST['pwd1']) && $_POST['pwd1'] == $_POST['pwd2'] ) {
$password=sanitize_text_field($_POST['pwd1']);
} else {
$password = wp_generate_password( 12, false ); 
}
      
$ID = wp_create_user(uniqid(), $password, $email );

$role = 'subscriber';

if ( is_multisite() ) {
$entity = dolibarr_entity(); 
add_user_to_blog($entity,$ID,$role);
}
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($thirdparty['email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
wp_update_user( array( 'ID' => $ID, 'display_name' => ucfirst(strtolower($thirdparty['firstname']))." ".strtoupper($thirdparty['lastname'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($thirdparty['firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($thirdparty['lastname']))));
update_user_meta( $ID, 'civility_id', sanitize_text_field($thirdparty['civility_id']));
update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
if ( isset($thirdparty['name']) ) { update_user_meta( $ID, 'billing_company', sanitize_text_field($thirdparty['name'])); }
update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);
if ( isset($_POST['optin1']) ) { update_user_meta( $ID, 'optin1', $_POST['optin1'] ); }

$body = sprintf(__('Thank you for your registration on %s.', 'doliconnect'), $sitename);

$user = get_user_by( 'ID', $ID);
 
if ( function_exists('dolikiosk') && ! empty(dolikiosk()) && $user ) {  

//$dolibarr = doliconnector($user, 'fk_soc', true, $thirdparty);
//do_action('wp_dolibarr_sync', $thirdparty);

//wp_set_current_user( $ID, $user->user_login );
//wp_set_auth_cookie( $ID, false);
//do_action( 'wp_login', $user->user_login );

//wp_redirect(esc_url(home_url()));
//exit;   
} else { 
//$user=get_user_by( 'ID', $ID );     
//$user = get_user_by( 'email', $email);   
$key = get_password_reset_key($user);

$arr_params = array( 'rpw' => true, 'key' => $key, 'login' => $user->user_login);  
$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

$body .= "<br /><br />".__('To activate your account on and choose your password, please click on the following link', 'doliconnect').":<br /><br /><a href='".$url."'>".$url."</a>";
}

$body .= "<br /><br />".sprintf(__("Your %s's team", 'doliconnect'), $sitename)."<br />".get_option('siteurl');
$headers = array('Content-Type: text/html; charset=UTF-8'); 
wp_mail($email, $subject, $body, $headers);
$emailSent = true;
               
}
}

if ( isset($emailSent) && $emailSent == true ) { 
print "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'Your account was created and an account activation link was sent by email. Don\'t forget to look at your unwanted emails if you can\'t find our message.', 'doliconnect' )."</p></div>"; 
} else {
if ( isset($hasError) || isset($captchaError) ) {
print "<div class='alert alert-danger'><a class='close' data-dismiss='alert'>x</a><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p class='error'>$emailError<p></div>";
}
}
print "<form id='doliconnect-signinform' action='".doliconnecturl('doliaccount')."?signup' role='form' method='post' class='was-validated'>";

if ( isset($msg) ) { print $msg; }

print doliloaderscript('doliconnect-signinform'); 

print "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".__( 'Create an account', 'doliconnect' )."</h5></div>";

print doliconnectuserform( null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'thirdparty');

print "<div class='card-body'><input type='hidden' name='submitted' id='submitted' value='true'><button class='btn btn-primary btn-block' type='submit'";
if ( get_option('users_can_register')=='1' && ( get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all' ) || ( !is_multisite() && get_option( 'users_can_register' )) ) {
print "";
} else { print " aria-disabled='true'  disabled"; }
print "><b>".__( 'Create an account', 'doliconnect' )."</b></button></form>";

print "</div></div>";

do_action( 'login_footer' );

print "<p class='text-right'><small>";
print dolihelp('ISSUE');
print "</small></p>";

print "</div></div>";

} elseif ( !is_user_logged_in() && isset($_GET["rpw"]) ) {

print "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
if (!$_GET["login"] || !$_GET["key"]) {
wp_redirect(wp_login_url( get_permalink() ));
exit;
} else {   
$user = check_password_reset_key( esc_attr($_GET["key"]), esc_attr($_GET["login"]) );
if ( ! $user || is_wp_error( $user ) ) {
if ( $user && $user->get_error_code() === 'expired_key' ){
wp_redirect(wp_login_url( get_permalink() )."?action=lostpassword&error=expiredkey");
exit;
}else{
wp_redirect(wp_login_url( get_permalink() )."?action=lostpassword&error=invalidkey");
exit;
}
exit;
} else {
$dolibarr = callDoliApi("GET", "/doliconnector/".$user->ID, null, 0);
if ($_POST["case"] == 'updatepwd'){
$pwd = sanitize_text_field($_POST["pwd1"]);                                   
if ( ($_POST["pwd1"] == $_POST["pwd2"]) && (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,20}/', $pwd))) {  //"#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#"

wp_set_password($pwd, $user->ID);

if ( $dolibarr->fk_user > '0' ) {
$data = [
    'pass' => $pwd
	];
$doliuser = callDoliApi("PUT", "/users/".$dolibarr->fk_user, $data, 0);
}

$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $user->user_login ) );
wp_redirect(wp_login_url( get_permalink() )."?action=lostpassword&success");
exit;
}
elseif ( $pwd != $_POST["pwd2"] ) {
$msg = "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><span class='fa fa-times-circle'></span> ".__( 'The new passwords entered are different', 'doliconnect' )."</div>";
}
elseif (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd)){
$msg = "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><span class='fa fa-times-circle'></span> ".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' )."</div>";
}
}
 
print $msg."<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( $dolibarr->fk_user > '0') {
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect' )."</b></li>";
} 
print "<li class='list-group-item'><h5 class='card-title'>".__( 'Change your password', 'doliconnect' )."</h5>
<form class='was-validated' id='doliconnect-rpwform' action='' method='post'><input type='hidden' name='submitted' id='submitted' value='true' />";

print doliloaderscript('doliconnect-rpwform'); 

print "<div class='form-group'><label for='pwd1'><small>".__( 'New password', 'doliconnect' )."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Enter your new password', 'doliconnect' )."' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
print ' readonly';
} else {
print ' required';
}
print "></div>
<small id='pwd1' class='form-text text-justify text-muted'>
".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' )."
</small>
<div class='form-group'><label for='pwd2'></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your new password', 'doliconnect' )."' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
print ' readonly';
} else {
print ' required';
}
print "></div>
</div></div></li><li class='list-group-item'><input type='hidden' name='case' value ='updatepwd'><button class='btn btn-danger btn-block' type='submit' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
print ' disabled';
}
print "><b>".__( 'Update', 'doliconnect' )."</b></button></form></li></ul>";
print "</div>";

}
}
} elseif ( isset($_GET["provider"]) && $_GET["provider"] != null ) { 
include( plugin_dir_path( __DIR__ ) . 'doliconnect-pro/lib/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'doliconnect-pro/lib/hybridauth/src/config.php');
try {
    //Feed configuration array to Hybridauth
    $hybridauth = new Hybridauth\Hybridauth($config);

    //Attempt to authenticate users with a provider by name
    $adapter = $hybridauth->authenticate($_GET["provider"]); 

    //Returns a boolean of whether the user is connected with Twitter
    $isConnected = $adapter->isConnected();
 
    //Retrieve the user's profile
    $userProfile = $adapter->getUserProfile();
if ( !email_exists($userProfile->email) ) {
$emailError = __( 'No account seems to be linked to this email address', 'doliconnect' );
        $hasError = true;   
    } else {
$user=get_user_by( 'email', $userProfile->email);    
wp_set_current_user($user->ID); 
if (wp_validate_auth_cookie()==FALSE)
{
    wp_set_auth_cookie($user->ID, true, true);
}   
do_action( 'wp_login', '<USERNAME>' ); 

$adapter->disconnect();     
wp_redirect(esc_url(home_url()));
exit;   
    }
    //Inspect profile's public attributes
//var_dump($userProfile);
//var_dump($adapter->getAccessToken());
    //Disconnect the adapter 
    $adapter->disconnect();
}
catch(\Exception $e) {
    // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
    // let hybridauth forget all about the user so we can try to authenticate again.
    // Display the recived error, 
    // to know more please refer to Exceptions handling section on the userguide
    switch( $e->getCode() ){ 
        case 0 : print "Unspecified error."; break;
        case 1 : print "Hybriauth configuration error."; break;
        case 2 : print "Provider not properly configured."; break;
        case 3 : print "Unknown or disabled provider."; break;
        case 4 : print "Missing provider application credentials."; break;
        case 5 : print "Authentication failed. " 
                  . "The user has canceled the authentication or the provider refused the connection."; 
        case 6 : print "User profile request failed. Most likely the user is not connected "
                  . "to the provider and he should to authenticate again."; 
               $adapter->logout(); 
               break;
        case 7 : print "User not connected to the provider."; 
               $adapter->logout(); 
               break;
    } 
    print "<br /><br /><b>Original error message:</b> " . $e->getMessage();
//print "<hr /><h3>Trace</h3> <pre>" . $e->getTraceAsString() . "</pre>";  
}
} elseif ( !is_user_logged_in() && isset($_GET["fpw"]) ) { 
print "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
  
if( isset($_POST['user_email']) ) {

    if( sanitize_email($_POST['user_email']) === '' )  {
        $emailError = __( 'A valid email is need to reset your password', 'doliconnect' );
        $hasError = true;
    } elseif ( !email_exists(sanitize_email($_POST['user_email'])) ) {
        $emailError = __( 'Reset password is not permitted', 'doliconnect' );
        $hasError = true;   
    }
    else {
        $email = sanitize_email($_POST['user_email']);

        $emailTo = get_option('tz_email');
        if (!isset($emailTo) || ($emailTo == '') ){
            $emailTo = get_option('admin_email');
        }
        
$user = get_user_by( 'email', $email);   
$key = get_password_reset_key($user);

$arr_params = array( 'rpw' => true, 'key' => $key, 'login' => $user->user_login);  
$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
      $emailError = __( 'Reset password is not permitted', 'doliconnect' );
      $emailSent = false;	
      
 } elseif ( !empty($key) ) { 
			$sitename = get_option('blogname');
      $siteurl = get_option('siteurl');
      $subject = "[$sitename] ".__( 'Reset Password', 'doliconnect' );
      $body = __( 'A request to change your password has been made. You can change it via the single-use link below:', 'doliconnect' )."<br /><br /><a href='".$url."'>".$url."</a><br /><br />".__( 'If you have not made this request, please ignore this email.', 'doliconnect' )."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($email, $subject, $body, $headers);

				if( $mail ) { $emailSent = true; } else { $emailSent = false; }		
}
       
}
}

if ( isset($emailSent) && $emailSent == true ) { 
print "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect' )."</p></div>";
} elseif ( isset($hasError) || isset($emailError) ) { 
print "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p>$emailError</p></div>";
} elseif ( isset($emailSent) && $emailSent != true ) {
print "<div class='alert alert-warning'><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p>".__( 'A problem occurred. Please retry later!', 'doliconnect' )."</p></div>";
}

print "<form id='doliconnect-fpwform' action='".doliconnecturl('doliaccount')."?fpw' method='post' class='was-validated'><input type='hidden' name='submitted' id='submitted' value='true' />";

if ( isset($msg) ) { print $msg; }

print doliloaderscript('doliconnect-fpwform'); 
 
print "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".__( 'Forgot password?', 'doliconnect' )."</h5>";

print "<div class='form-group'><label for='inputemail'><small>".__( 'Please enter the email address by which you registered your account.', 'doliconnect' )."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_email' type='email' placeholder='".__( 'Email', 'doliconnect' )."' name='user_email' value ='' required>";
print "</div></div></div>";
print "<ul class='list-group list-group-flush'><li class='list-group-item'>";
print "<button class='btn btn-danger btn-block' type='submit'><b>".__( 'Submit', 'doliconnect' )."</b></button>";
print "</li></ul>";
print "</div></form>";

print "<p class='text-right'><small>";
print dolihelp('ISSUE');
print "</small></p>"; 

print "</div></div>";

} else {

print "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";

if ( isset($emailSent) && $emailSent == true ) { 
print "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect' )."</p></div>";
}

if( isset($_GET["login"]) && $_GET["login"] == 'failed' ) { 
print "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p>".__( 'There is no account for these login data or the email and/or the password are not correct.', 'doliconnect' )."</p></div>";
}
print "<div class='card shadow-sm'><ul class='list-group list-group-flush'><li class='list-group-item'>";

if ( function_exists('doliconnect_modal') && get_option('doliloginmodal') == '1' ) {

print "<center><i class='fas fa-user-lock fa-fw fa-10x'></i><br><br>";
//print "<h2>".__( 'Restricted area', 'doliconnect' )."</h2></center>";
print '<a href="#" id="login-'.current_time('timestamp').'" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'doliconnect').'" class="btn btn-block btn-primary my-2 my-sm-0" role="button">'.__('You have already an account', 'doliconnect').'</a>';
if (((!is_multisite() && get_option( 'users_can_register' )) or (get_option('users_can_register')=='1' && (get_site_option( 'registration' ) == 'user' or get_site_option( 'registration' ) == 'all')))) 
{
print '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect' ).'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
print '<a href="'.wp_registration_url( get_permalink() ).'" id="login-'.current_time('timestamp').'" class="btn btn-block btn-primary my-2 my-sm-0" role="button">'.__("You don't have an account", 'doliconnect').'</a>';
}

} else {

do_action( 'login_head' );

print "<div id='loginmodal-form'><h5 class='card-title'>".__( 'Welcome', 'doliconnect' )."</h5>";
print "<b>".get_option('doliaccountinfo')."</b>";

if ( function_exists('socialconnect') ) {
print socialconnect(get_permalink());
}

if ( function_exists('secupress_get_module_option') && secupress_get_module_option('move-login_slug-login', $slug, 'users-login' ) ) {
$login_url=site_url()."/".secupress_get_module_option('move-login_slug-login', $slug, 'users-login' ); 
} else {
$login_url=site_url()."/wp-login.php"; }
if ( isset($_GET["redirect_to"])) { $redirect_to=$_GET["redirect_to"]; } else {
$redirect_to=$_SERVER['HTTP_REFERER'];}
 
print "<form class='was-validated' id='doliconnect-loginform' action='$login_url' method='post'>";

print doliloaderscript('doliconnect-loginform'); 
 
print "<div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_login' type='email' placeholder='".__( 'Email', 'doliconnect' )."' name='log' value='' required autofocus>";
print "</div></div><div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='user_pass' type='password' placeholder='".__( 'Password', 'doliconnect' )."' name='pwd' value ='' required>";
print "</div></div>";

do_action( 'login_form' );

print "<div><div class='float-left'><small>";
if ( ((!is_multisite() && get_option( 'users_can_register' )) || (get_option('users_can_register') == '1' && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all'))) ) {
print "<a href='".wp_registration_url( get_permalink() )."' role='button' title='".__( 'Create an account', 'doliconnect' )."'>".__( 'Create an account', 'doliconnect' )."</a>";
}
//<input type='checkbox' class='custom-control-input' value='forever' id='remembermemodal' name='rememberme'>";
//print "<label class='custom-control-label' for='remembermemodal'> ".__( 'Remember me', 'doliconnect' )."</label>";
print "</div><div class='float-right'><a href='".wp_lostpassword_url( get_permalink() )."' role='button' title='".__( 'Forgot password?', 'doliconnect' )."'>".__( 'Forgot password?', 'doliconnect' )."</a></small></div></div>"; 

print "</div></li><li class='list-group-item'><input type='hidden' value='$redirect_to' name='redirect_to'><button id='submit' class='btn btn-block btn-primary' type='submit' name='submit' value='Submit'";
print "><b>".__( 'Sign in', 'doliconnect' )."</b></button></form>";

do_action( 'login_footer' );

}

print "</li></lu></div>";



print "<p class='text-right'><small>";
print dolihelp('ISSUE');
print "</small></p>";

print "</div></div>";

}

} else {

return $content;

}

}

add_filter( 'the_content', 'doliaccount_display');

// ********************************************************

function dolicontact_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicontact')) && !empty(doliconnectid('dolicontact')) ) {

doliconnect_enqueues();

if( ! empty($_POST['email-control']) )   //! $is_valid  || ! 
{
$emailError = __( 'Your request is unsuccessful', 'doliconnect' );
}
elseif ( isset($_POST['submitted']) ) {
    if ( sanitize_text_field($_POST['contactName']) === '' ) {
        $nameError = 'Please enter your name.';
        $hasError = true;
    } else {
        $name = sanitize_text_field($_POST['contactName']);
    }

    if ( sanitize_email($_POST['email']) === '' )  {
        $emailError = 'Please enter your email address.';
        $hasError = true;
    } else if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", sanitize_email($_POST['email']))) {
        $emailError = 'You entered an invalid email address.';
        $hasError = true;
    } else {
        $email = sanitize_email($_POST['email']);
    }

    if( sanitize_textarea_field($_POST['comments']) === '') {
        $commentError = 'Please enter a message.';
        $hasError = true;
    } else {

        $comments = sanitize_textarea_field($_POST['comments']);

    }

    if ( !isset($hasError) ) {
        $emailTo = get_option('tz_email');
        $user=get_userdata( $_GET['user'] ); 
        
        if ( isset($_GET['user']) && $user == true && $_GET['type'] == 'EMAIL' ) {
        $emailTo = $user->user_email;}
        elseif (!isset($emailTo) || ($emailTo == '') ) {
            $emailTo = get_option('admin_email');
        }
        $subject = "[".get_bloginfo( 'name' )."] ".$_POST['ticket_type'];
        $body = "Nom: $name <br />Email: $email <br />Message: $comments";
        $headers = array("Content-Type: text/html; charset=UTF-8'","From: $name <$email>"); 
        wp_mail($emailTo, $subject, $body, $headers);
        $emailSent = true;
    }

}

print "<div class='row'><div class='col-md-4'><div class='form-group'><h4>".__( 'Address', 'doliconnect')."</h4>";
print doliconst('MAIN_INFO_SOCIETE_ADDRESS');
print "<br />";
print doliconst('MAIN_INFO_SOCIETE_ZIP');
print " ";
print doliconst('MAIN_INFO_SOCIETE_TOWN'); 
print "</div></div><div class='col-md-8'><div id='content'>";
if ( isset($emailSent) && $emailSent == true ) { 
$msg = "<div class='alert alert-success'>
<p>".__( 'Your message is successful send!', 'doliconnect')."</p>
</div>";
} elseif ( isset($hasError) || isset($captchaError) ) { 
$msg = "<div class='alert alert-warning'>
<a class='close' data-dismiss='alert'>x</a>
<h4 class='alert-heading'>".__( 'Oops', 'doliconnect')."</h4>
<p class='error'>Please try again!<p></div>";
}

print "<form action='' id='doliconnect-contactform' method='post' class='was-validated'>";

if ( isset($msg) ) { print $msg; }

print doliloaderscript('doliconnect-contactform');

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>
<li class='list-group-item'><div class='form-group'>
<label class='control-label' for='contactName'><small>".__( 'Complete name', 'doliconnect')."</small></label>
<input class='form-control' type='text' name='contactName' autocomplete='off' id='contactName' value='";
if ( is_user_logged_in() ) { print $current_user->user_lastname." ".$current_user->user_firstname; } else { print ""; }
print "'";
if ( is_user_logged_in() ) { print " readonly";} else { print " required"; }
print ">";
print "</div><div class='form-group'>
<label class='control-label' for='email'><small>".__( 'Email', 'doliconnect')."</small></label>
<input class='form-control' type='email' name='email' autocomplete='off' id='email' value='$current_user->user_email'";
if ( is_user_logged_in() ) { print " readonly"; } else { print " required"; }
print ">";
print "</div><div class='form-group d-none'>
<label class='control-label' for='email-control'><small>".__( 'Email', 'doliconnect')."</small></label>
<input class='form-control' type='email' name='email-control' autocomplete='off' id='email-control' ";
print "/>";
print "</div>";

print "<div class='form-group'><label class='control-label' for='type'><small>".__( 'Type of request', 'doliconnect')."</small></label>";
$type = callDoliApi("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100", null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( isset($type) ) { 
$tp= __( 'Issue or problem', 'doliconnect').__( 'Commercial question', 'doliconnect').__( 'Change or enhancement request', 'doliconnect').__( 'Project', 'doliconnect').__( 'Other', 'doliconnect');
print "<select class='custom-select' id='ticket_type'  name='ticket_type'>";
foreach ( $type as $postv ) {
print "<option value='".$postv->code."' ";
if ( $_GET['type'] == $postv->code ) {
print "selected ";
} elseif ( $postv->use_default == 1 ) {
print "selected ";}
print ">".__($postv->label, 'doliconnect')."</option>";
}
print "</select>";
}
print "</div>";

print "<div class='form-group'><label class='control-label' for='commentsText'><small>".__( 'Message', 'doliconnect')."</small></label>
<textarea class='form-control' name='comments' id='commentsText' rows='7' cols='20' required></textarea>";

if ( !is_user_logged_in() ) {
print '</li><li class="list-group-item"><div class="custom-control custom-checkbox"><input id="rgpdinfo" class="custom-control-input form-control-sm" type="checkbox" name="rgpdinfo" value="ok" required><label class="custom-control-label w-100" for="rgpdinfo"><small class="form-text text-muted"> '.__( 'I agree to save my personnal informations in order to contact me', 'doliconnect').'</small></label></div>';  
}
print "</li></ul>";
print "<div class='card-body'><button class='btn btn-primary btn-block' type='submit'><b>".__( 'Send', 'doliconnect')."</b></button><input type='hidden' name='submitted' id='submitted' value='true' /></div></div></div></div></form>";

print "</div>";

} else {

return $content;

}

}

add_filter( 'the_content', 'dolicontact_display');

// ********************************************************

function dolishop_display($content) {

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolishop')) && !empty(doliconnectid('dolishop')) ) {

doliconnect_enqueues();

$shop = callDoliApi("GET", "/doliconnector/constante/DOLICONNECT_CATSHOP", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $shop;

if ( defined("DOLIBUG") ) {

print dolibug();

} else { 
print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( !isset($_GET['category']) ) {
if ( $shop->value != null ) {

$request = "/categories?sortfield=t.rowid&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent='".$shop->value."')";

$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc as $categorie) {

print "<a href='".esc_url( add_query_arg( 'category', $categorie->id, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action'>".doliproduct($categorie, 'label')."<br />".doliproduct($categorie, 'description')."</a>"; 

}}
}

$catoption = callDoliApi("GET", "/doliconnector/constante/ADHERENT_MEMBER_CATEGORY", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !empty($catoption->value) && is_user_logged_in() ) {
print "<a href='".esc_url( add_query_arg( 'category', $catoption->value, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action' >Produits/Services lies a l'adhesion</a>";
}

} else {

if ( isset($_GET['product']) ) {
addtodolibasket(esc_attr($_GET['product']), esc_attr($_POST['product_update'][$_GET['product']]['qty']), esc_attr($_POST['product_update'][$_GET['product']]['price']));
//print $_POST['product_update'][$_GET['product']][product];
wp_redirect( esc_url( add_query_arg( 'category', $_GET['category'], doliconnecturl('dolishop')) ) );
exit;
}
print "<table class='table' width='100%'>";

$request = "/products?sortfield=t.label&sortorder=ASC&category=".$_GET['category']."&sqlfilters=(t.tosell=1)";

$resultatso = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultatso->error) && $resultatso != null ) {
foreach ($resultatso as $product) {
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=1", null, 0);
print "<tr class='table-light'><td><center><i class='fa fa-plus-circle fa-2x fa-fw'></i></center></td>";

print "<td><b>".doliproduct($product, 'label')."</b> ";
print doliproductstock($product);
print "<br />".doliproduct($product, 'description')."</td>";

print "<td width='300px'><center>";
if (function_exists('dolibuttontocart')) {
print dolibuttontocart($product, esc_attr($_GET['category']), 1);
}
print "</center></td></tr>"; 
}
} else {
wp_redirect(esc_url(get_permalink()));
exit;
}
print "</tbody></table>";
}
}
print "</ul></div>";

print "<small><div class='float-left'>";
print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('COM');
print "</div></small>";

} else {

return $content;

}

}

add_filter( 'the_content', 'dolishop_display');

// ********************************************************

function dolidonation_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolidonation')) && !empty(doliconnectid('dolidonation')) ) {

doliconnect_enqueues();

$donation = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_DON", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art200 = callDoliApi("GET", "/doliconnector/constante/DONATION_ART200", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art238 = callDoliApi("GET", "/doliconnector/constante/DONATION_ART238", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art835 = callDoliApi("GET", "/doliconnector/constante/DONATION_ART835", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $shop;

if ( defined("DOLIBUG") ) {

print dolibug();

} elseif (empty($donation->value)) {
print "<div class='card shadow-sm'><div class='card-body'>";
print dolibug(__( 'Inactive module on Dolibarr', 'doliconnect'));
print "</div></div>";
} elseif (is_user_logged_in())  {

if ( doliconnector($current_user, 'fk_soc') > '0') {
$request = "/thirdparties/".doliconnector($current_user, 'fk_soc');
$thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
}

print "<form action='".doliconnecturl('dolidonation')."' id='doliconnect-donationform' method='post' class='was-validated' enctype='multipart/form-data'>";

if ( isset($msg) ) { print $msg; }

print doliloaderscript('doliconnect-donationform');

print "<div class='card shadow-sm'>";

if (isset($_GET["create"])) {
print doliconnectuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'donation');

print "<div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect')."</b></button></div>";

} else {
print "<div class='card-body'>"; 

print "<h5><i class='fas fa-donate fa-fw'></i> Don hors ligne</h5>";

//if ( $object->mode_reglement_code == 'CHQ') {

$chq = callDoliApi("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay('constante'));

$bank = callDoliApi("GET", "/bankaccounts/".$chq->value, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect-pro' ), 'votre choix', $bank->proprio, $object->ref ).":</p><p><b>$bank->owner_address</b></p></div>";

//} 
//if ($object->mode_reglement_code == 'VIR') {

$vir = callDoliApi("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay('constante'));

$bank = callDoliApi("GET", "/bankaccounts/".$vir->value, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect-pro' ), 'votre choix', $object->ref ).":";
print "<br><b>".__( 'Bank', 'doliconnect-pro' ).": $bank->bank</b>";
print "<br><b>IBAN: $bank->iban</b>";
if ( ! empty($bank->bic) ) { print "<br><b>BIC/SWIFT: $bank->bic</b>";}
print "</p></div>";

//}
print "<h5><i class='fas fa-donate fa-fw'></i> ".__( 'Tax exemptions', 'doliconnect' )."</h5>";
if (! empty($art200->value) || ! empty($art238->value) || ! empty($art835->value)) {
if (! empty($art200->value)) {
print __( 'DonationArt200', 'doliconnect');
}

if (! empty($art238->value)) {
print __( 'DonationArt238', 'doliconnect');
}

if (! empty($art835->value)) {
print __( 'DonationArt835', 'doliconnect');
}
} else {
print __( "You should't have tax exemptions", 'doliconnect');
}
print "</div>";
}

print "</div></form>";

print "<small><div class='float-left'>";
print dolirefresh($request, doliconnecturl('dolidonation'), dolidelay('constante'));
print "</div><div class='float-right'>";
print dolihelp('COM');
print "</div></small>";
}


} else {

return $content;

}

}

add_filter( 'the_content', 'dolidonation_display');

// ********************************************************

function update_synctodolibarr($element) {
global $current_user,$wpdb;
$entity = get_current_blog_id();
wp_get_current_user();

//$resultatsa = $wpdb->get_results(
//$wpdb->prepare(
//        "SELECT * FROM ".$wpdb->base_prefix."usermeta where user_id = %d and meta_key like '".$wpdb->prefix."doliextra_%' ",
//        $current_user->ID
//    )
//);
//foreach ($resultatsa as $posta) {
//$subject = $posta->meta_key ;
//$search = $wpdb->prefix."doliextra_";
//$key = str_replace($search, '', $subject) ;
//$value = $posta->meta_value;
//$extrafields[$key] = $value;
//}

if ($current_user->billing_type == 'phy'){
$name = $current_user->user_firstname." ".$current_user->user_lastname;}
else {$name = $current_user->billing_company;}
if (NULL != doliconnector($current_user, 'fk_member')) {
//$infomember = [
//   'login'  => $current_user->user_login,
//   'morphy'  => $current_user->billing_type,
//    'civility_id'  => $current_user->billing_civility,            
//    'firstname'  => $current_user->user_firstname,
//    'lastname'  => $current_user->user_lastname,
//    'address' => $current_user->billing_address,
//    'zip' => $current_user->billing_zipcode,
//    'town' => $current_user->billing_city,
//    'country_id' => $current_user->billing_country,
//    'email' => $current_user->user_email,
//    'phone' => $current_user->billing_phone,
//    'birth' => $current_user->billing_birth,
//    'array_options' => isset($extrafields) ? $extrafields : null
//	]; 
$adherent = callDoliApi("PUT", "/adherentsplus/".doliconnector($current_user, 'fk_member'), $element, 0);
//update_user_meta( $current_user->ID, 'billing_birth', $current_user->billing_birth);
}
if ( doliconnector($current_user, 'fk_soc') > 0 ) {
//$info = [
//    'status' => 1,
//    'name'  => $name,
//    'address' => $current_user->billing_address,
//    'zip' => $current_user->billing_zipcode,
//    'town' => $current_user->billing_city,
//    'country_id' => $current_user->billing_country,
//    'email' => $current_user->user_email,
//    'phone' => $current_user->billing_phone,
//    'url' => $current_user->user_url,
//    'array_options' => isset($extrafields) ? $extrafields : null
//	];
$thirparty = callDoliApi("PUT", "/thirdparties/".doliconnector($current_user, 'fk_soc'), $element, 0);
}

}
add_action('wp_dolibarr_sync','update_synctodolibarr', 1, 1);

// outils de personnalisation et utilisation du module
function my_login_logo_url() {
return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
return 'nom du site';
}
add_filter( 'login_headertext', 'my_login_logo_url_title' );

// Hide Author EVERYWHERE
add_filter( 'generate_post_author','generate_modify_author_display' );
function generate_modify_author_display()
{
    //if ( is_single() )
    //    return true;
return false;
}

function my_register_page( $register_url ) {
return doliconnecturl('doliaccount') . '?signup';
}
add_filter( 'register_url', 'my_register_page' );

function my_lost_password_page( $lostpassword_url ) {
return doliconnecturl('doliaccount') . '?fpw';
}
if (get_option('doliaccount')) {
add_filter( 'lostpassword_url', 'my_lost_password_page', 10, 2 );}

function login_link_url( $url ) {
if (get_option('doliaccount')) {
return doliconnecturl('doliaccount'); }
}
if (get_option('doliaccount')) {
add_filter( 'login_url', 'login_link_url', 10, 2 ); }

add_filter('asgarosforum_filter_profile_link', 'my_profile_url', 10, 2);
function my_profile_url($profile_url, $user_object) {
return doliconnecturl('doliaccount');
}

function account_login_fail( $username ) {
$referrer = $_SERVER['HTTP_REFERER'];  
if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
wp_redirect( esc_url( add_query_arg( 'login', 'failed', doliconnecturl('doliaccount')) ) );  // let's append some information (login=failed) to the URL for the theme to use
exit;}
}
add_action( 'wp_login_failed', 'account_login_fail' );  // hook failed login

function passresetmodif_login ($url, $redirect) { 
if (get_site_option('doliconnect_login')) {
$login_url=site_url()."/" . get_site_option('doliconnect_login');
}else {
$login_url=site_url()."/wp-login.php"; 
}
    $args = array( 'action' => 'lostpassword' );

    if ( !empty($redirect) )
        $args['redirect_to'] = $redirect;
    return add_query_arg( $args, $login_url );
}

// ******************WIDGET********************************

class My_doliconnect extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'my_doliconnect',                               
			'description' => 'Soumission de bug',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'my_doliconnect', 'SOS Bug', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {
global $wpdb;
		
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
  print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

$time=current_time('timestamp');

if (is_user_logged_in()){ 
  print "<a class='btn btn-block btn-warning' href='".doliconnecturl('doliaccount') . "?module=ticket&type=ISSUE&create' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect' )."</a>";
} else {
  print "<a class='btn btn-block btn-warning' href='".esc_url( add_query_arg( 'type', 'issue', doliconnecturl('dolicontact')) ) . "' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect' )."</a>";
} 
  print $args['after_widget'];  
}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Offre d\'emploi', 'text_domain' );
		?>
		<p>
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php print esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

add_action( 'widgets_init', function(){
	register_widget( 'My_doliconnect' );
});

class My_doliconnect_Membership extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'my_doliconnect_membership',                               
			'description' => 'lightbox adhesion',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'my_doliconnect_membership', 'Adhesion', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {
global $current_user, $wpdb;
		// outputs the content of the widget
    
  		print $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

if (doliconnector($current_user, 'fk_member') > 0) {
$adherent = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member'), null);
}
 
if ($adherent->statut == '1' && $adherent->datefin < current_time('timestamp')) {
print "<A class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Pay my subscription', 'doliconnect' )."</a>"; 
}
elseif ($adherent->statut == '0') {
print "<a class='btn btn-block btn-info' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect' )."</a>"; 
}
elseif ($adherent->statut == '-1') {
print "<a class='btn btn-block btn-warning disabled' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Membership', 'doliconnect' )."</a>";//requested 
}
elseif (!$adherent->id > 0) {
print "<a class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect' )."</a>"; 
}


print $args['after_widget'];  
    
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = '';
		?>
		<p>
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php print esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

add_action( 'widgets_init', function(){
	register_widget( 'My_doliconnect_Membership' );
});

?>
