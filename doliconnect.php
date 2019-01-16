<?php
/**
 * Plugin Name: Doliconnect
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Connect your Dolibarr (free ERP/CRM) to Wordpress. 
 * Version: 3.0.12
 * Author: ptibogxiv
 * Author URI: https://www.ptibogxiv.net/en
 * Network: true
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: doliconnect
 * Domain Path: /languages
 *
 * @author ptibogxiv.net <support@ptibogxiv.net>
 * @copyright Copyright (c) 2017-2019, ptibogxiv.net
**/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}   

require plugin_dir_path(__FILE__).'/update/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/ptibogxiv/doliconnect/',
	__FILE__,
	'doliconnect'
);
$myUpdateChecker->getVcsApi()->enableReleaseAssets();

require_once plugin_dir_path(__FILE__).'/functions/enqueues.php';
require_once plugin_dir_path(__FILE__).'/functions/data-request.php';
require_once plugin_dir_path(__FILE__).'/functions/tools.php';
require_once plugin_dir_path(__FILE__).'/functions/dashboard.php';
require_once plugin_dir_path(__FILE__).'/functions/product.php';
require_once plugin_dir_path(__FILE__).'/functions/admin.php'; 
require_once plugin_dir_path(__FILE__).'/blocks/index.php';

//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// ********************************************************
function doliconnecturl ($page) {
global $wpdb;
if (function_exists('pll_get_post')) { 
return esc_url(get_permalink(pll_get_post(get_option($page))));
} else { 
return esc_url(get_permalink(get_option($page)));
}  
}

function doliconnectid ($page) {
global $wpdb;
if (function_exists('pll_get_post')) { 
return pll_get_post(get_option($page));
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

if ( !get_site_option('dolibarr_entity') && get_site_option('doliconnect_mode') == 'one' ) {
return 1;
} elseif ( get_site_option('dolibarr_entity') && get_option('dolibarr_entity') ) {
return get_option('dolibarr_entity');
} else {
return get_current_blog_id();
}

}
// ********************************************************
function doliconst( $constante ) {
global $wpdb;

$const = CallAPI("GET", "/doliconnector/constante/".$constante, null, MONTH_IN_SECONDS);

return $const->value;
}
// ********************************************************
if ( is_page(array(doliconnectid ('doliaccount'),doliconnectid ('dolicart'))) ) {
if ( !defined ('DONOTCACHEPAGE') ) {
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
function CallAPI($method = null, $link = null, $body = null, $delay = HOUR_IN_SECONDS, $entity = null) {
global $wpdb;

$headers = array(
        'DOLAPIENTITY' => dolibarr_entity($entity),
        'DOLAPIKEY' => get_site_option('dolibarr_private_key')
    );

$url=get_site_option('dolibarr_public_url').'/api/index.php'.$link;

if ( !empty(get_site_option('dolibarr_public_url')) && !empty(get_site_option('dolibarr_private_key')) ) {
if ( !empty( $link ) && ( false === ( $response = get_transient( $link ) ) || $method!='GET' || $delay <= 0 ) ) {

$args = array(
    'timeout' => '10',
    'redirection' => '5',
    'method' => $method,
    'headers' => $headers
); 

if ( $method=='POST' ) {
$args['body'] = $body;
delete_transient( $link );  
$request = wp_remote_post( esc_url_raw($url), $args );
} elseif ( $method=='PUT' ) {
$args['body'] = $body;
delete_transient( $link ); 
$request = wp_remote_request( esc_url_raw($url), $args );
} elseif ( $method=='DELETE' ) { 
$request = wp_remote_request( esc_url_raw($url), $args );
} else {
$request = wp_remote_get( esc_url_raw($url), $args );
}

$http_code = wp_remote_retrieve_response_code( $request );

if ( $method == 'DELETE' ) {
delete_transient( $link ); 
} elseif ( $delay <= 0 || ! in_array($http_code,array('200','404')) ) {
delete_transient( $link );

if (! in_array($http_code,array('200','404')) ) {

if ( !defined("DOLIBUG") ) {
define('DOLIBUG', 1);
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
return json_decode( $response );   
}
} else {

if ( !defined("DOLIBUG") ) {
define('DOLIBUG', 1);
}
}
}
add_action( 'admin_init', 'CallAPI', 5, 5); 
// ********************************************************
function dolibarr(){
global $current_user;  

if ( is_user_logged_in() ) { 
$user=get_current_user_id(); 

$dolibarr = CallAPI("GET", "/doliconnector/".$user, null, dolidelay( HOUR_IN_SECONDS, false));

if ( defined("DOLIBUG") || !is_object($dolibarr) ) {
define('DOLIBARR', null);
define('PRICE_LEVEL', 0);
define('REMISE_PERCENT', 0);
define('DOLIBARR_MEMBER', null);
define('DOLIBARR_USER', null);
define('DOLICONNECT_CART', 0);
define('DOLICONNECT_CART_ITEM', 0); 
} else {  
if ( $dolibarr->fk_soc == 0 ) {
if ( $current_user->billing_type == 'phy' ) {
$name = $current_user->user_firstname." ".$current_user->user_lastname; }
elseif ( $current_user->billing_type == 'mor' ) {$name = $current_user->billing_company;}
$rdr = [
    'name'  => $name,
    'address' => $current_user->billing_address,    
    'zip' => $current_user->billing_zipcode,
    'town' => $current_user->billing_city,
    'country_id' => $current_user->billing_country,
    'email' => $current_user->user_email,
    'phone' => $current_user->billing_phone,
	];
$dolibarr = CallAPI("POST", "/doliconnector/".$user, $rdr, HOUR_IN_SECONDS);
define('DOLIBARR', $dolibarr->fk_soc);
} else {   
define('DOLIBARR', $dolibarr->fk_soc);}
define('PRICE_LEVEL', $dolibarr->price_level);
define('REMISE_PERCENT', $dolibarr->remise_percent);
define('DOLIBARR_MEMBER', $dolibarr->fk_member);
define('DOLIBARR_USER', $dolibarr->fk_user); 
define('DOLICONNECT_CART', $dolibarr->fk_order);
define('DOLICONNECT_CART_ITEM', $dolibarr->fk_order_nb_item);
} 

} else {     
define('DOLIBARR', null);
define('PRICE_LEVEL', 0);
define('REMISE_PERCENT', 0);
define('DOLIBARR_MEMBER', null);
define('DOLIBARR_USER', null);
define('DOLICONNECT_CART', 0);
define('DOLICONNECT_CART_ITEM', 0);
} 

}
add_action( 'init', 'dolibarr', 10);
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
function doliaccount_shortcode() {                                                                                                               
global $wp_hasher,$current_user,$wpdb;
require_once ABSPATH . WPINC . '/class-phpass.php';

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

echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-3'><div class='row'><div class='col-3 col-xs-4 col-sm-4 col-md-12 col-xl-12'><div class='card shadow-sm' style='width: 100%'>";
echo get_avatar($ID);
if ( is_user_logged_in() && !defined("DOLIBUG") ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'avatars', doliconnecturl('doliaccount')) )."' class='card-img-overlay'><div class='d-block d-sm-block d-xs-block d-md-none text-center'><i class='fas fa-camera'></i></div><div class='d-none d-md-block'><i class='fas fa-camera fa-2x'></i> ".__( 'Edit', 'doliconnect' )."</div></a>";
}
echo "<ul class='list-group list-group-flush'><a href='".esc_url( doliconnecturl('doliaccount') )."' class='list-group-item list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-home'></i></div><div class='d-none d-md-block'><i class='fas fa-home'></i> ".__( 'Home', 'doliconnect' )."</div></center></a>";
echo "</ul>";

echo "</div><br></div><div class='col-9 col-xs-8 col-sm-8 col-md-12 col-xl-12'>";

if ( is_user_logged_in() ) {

if ( defined("DOLIBUG") ) {

echo "</div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
echo dolibug();
echo "</div></div>";

} else { 

if ( isset($_GET['module']) ) {
//****
if ( has_action('user_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('user_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('user_doliconnect_menu', esc_attr($_GET['module']));
echo "</div><br>";
}
echo "</div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'user_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('compta_doliconnect_'.esc_attr($_GET['module'])) ) {
if( has_action('compta_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('compta_doliconnect_menu', esc_attr($_GET['module']));
echo "</div><br>";
}
echo "</div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'compta_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('options_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('options_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('options_doliconnect_menu', esc_attr($_GET['module']));
echo "</div><br>";
}
echo "</div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'options_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('my_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('my_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('my_doliconnect_menu', esc_attr($_GET['module']));
echo "</div><br>";
}
echo "</div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'my_doliconnect_'.esc_attr($_GET['module']),esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} elseif ( has_action('settings_doliconnect_'.esc_attr($_GET['module'])) ) {
if ( has_action('settings_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('settings_doliconnect_menu', esc_attr($_GET['module']));
echo "</div><br>";
}
echo "</div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
do_action( 'settings_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
} else {
wp_redirect( esc_url(doliconnecturl('doliaccount')) );
exit;
}
//****
echo "</div>";

} else {

echo "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
if ( has_action('user_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('user_doliconnect_menu');
echo "</div><br>";
}  

if ( has_action('compta_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('compta_doliconnect_menu');
echo "</div><br>";
}

if ( has_action('options_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('options_doliconnect_menu');
echo "</div><br>";
}

if ( has_action('my_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('my_doliconnect_menu');
echo "</div><br>";
}

if ( has_action('settings_doliconnect_menu') ) {
echo "<div class='list-group shadow-sm'>";
do_action('settings_doliconnect_menu');
echo "</div><br>";
}

echo "</div>";
}
// fin de sous page
echo "</div>";
}
} elseif ( !is_user_logged_in() && isset($_GET["signup"]) ) {
echo "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";

global $wp_hasher,$wpdb;
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

$is_valid = apply_filters('invisible_recaptcha', true);
if( ! $is_valid )
{
    // handle error here
} elseif ( isset($_POST['submitted']) ) {
if ( email_exists($_POST['user_email']) ) {
        $emailError = "".__( 'This email address is already linked to an account. You can reactivate your account through this <a href=\'".wp_lostpassword_url( get_permalink() )."\' title=\'lost password\'>form</a>.', 'doliconnect' )."";
        $hasError = true;
    } else {
        $email = trim($_POST['user_email']);
    }

    if(!isset($hasError)) {
        $emailTo = get_option('tz_email');
        if (!isset($emailTo) || ($emailTo == '') ){
            $emailTo = get_option('admin_email');
        }

$sitename = get_option('blogname');
$subject = "[".$sitename."] ".__( 'Registration confirmation', 'doliconnect' )."";
if ( !empty($_POST['pwd1']) && $_POST['pwd1']==$_POST['pwd2'] ) {
$password=$_POST['pwd1'];
} else {
$password = wp_generate_password( 12, false ); 
}


$first = ucfirst(sanitize_user($_POST['user_firstname']));
$last = strtoupper(sanitize_user($_POST['user_lastname']));
//$login = substr($_POST['user_firstname']).strtolower($_POST['user_lastname']);       
$ID = wp_create_user(uniqid(), $password, sanitize_email($_POST['user_email']) );
$entity = get_current_blog_id();
$role = 'subscriber';

if ( is_multisite() ) { 
add_user_to_blog($entity,$ID,$role); }
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($_POST['user_email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
wp_update_user( array( 'ID' => $ID, 'display_name' => ucfirst(strtolower($_POST['user_firstname']))." ".strtoupper($_POST['user_lastname'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($_POST['user_firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($_POST['user_lastname']))));
wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
update_usermeta( $ID, 'billing_civility', $_POST['billing_civility']);
update_usermeta( $ID, 'billing_type', $_POST['billing_type']);
update_usermeta( $ID, 'billing_company', sanitize_text_field($_POST['billing_company']));
update_usermeta( $ID, 'billing_address', sanitize_textarea_field($_POST['billing_address']));
update_usermeta( $ID, 'billing_zipcode', sanitize_text_field($_POST['billing_zipcode']));
update_usermeta( $ID, 'billing_city', sanitize_text_field($_POST['billing_city']));
update_usermeta( $ID, 'billing_country', $_POST['billing_country'] );
update_usermeta( $ID, 'billing_phone', sanitize_text_field($_POST['billing_phone'])); 
update_usermeta( $ID, 'billing_birth',$_POST['billing_birth']);
update_usermeta( $ID, 'optin1', $_POST['optin1'] );

$body = sprintf(__('Thank you for your registration on %s.', 'doliconnect'), $sitename);

if ( function_exists('dolikiosk') && ! empty(dolikiosk()) ) {
$user = get_user_by( 'id', $ID);   
if( $user ) {
    wp_set_current_user( $ID, $user->user_login );
    wp_set_auth_cookie( $ID, false);
    do_action( 'wp_login', $user->user_login );
} 
wp_redirect(esc_url(home_url()));
exit;   
} else {
$user=get_user_by( 'ID', $ID );     
$key=get_password_reset_key($ID);
$hashed = current_time('timestamp') . ':' . $wp_hasher->HashPassword($key);
$key_saved = $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
$url=doliconnecturl('doliaccount')."?rpw&key=$key&login=$user->user_login"; 

$body .= "<br /><br />".__('To activate your account on and choose your password, please click on the following link', 'doliconnect').":<br /><br /><a href='".$url."'>".$url."</a>";
}

$body .= "<br /><br />".sprintf(__("Your %s's team", 'doliconnect'), $sitename)."<br />".get_option('siteurl');
$headers = array('Content-Type: text/html; charset=UTF-8'); 
wp_mail($email, $subject, $body, $headers);
$emailSent = true;

                
}
}

if ( isset($emailSent) && $emailSent == true ) { 
echo "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'Your account was created and an account activation link was sent by email. Don\'t forget to look at your unwanted emails if you can\'t find our message.', 'doliconnect' )."</p></div>"; 
} else {
if ( isset($hasError) || isset($captchaError) ) {
echo "<div class='alert alert-danger'><a class='close' data-dismiss='alert'>x</a><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p class='error'>$emailError<p></div>";
}
echo "<form  action='' method='post' class='needs-validation' novalidate><div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".__( 'Create an account', 'doliconnect' )."</h5>";
echo "<script>";
?>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
<?php
echo "</script>";
if ( isset($_GET["pro"]) && !get_option('doliconnect_disablepro') ) {
echo "<a  href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create a personnal account', 'doliconnect' )."'><small>(".__( 'Personnal account', 'doliconnect' )."?)</small></a>";                                                                                                                                                                                                                                                                                                                                     
}
elseif (!get_option('doliconnect_disablepro')) {
echo "<a  href='".wp_registration_url(get_permalink())."&pro' role='button' title='".__( 'Create a pro/supplier account', 'doliconnect' )."'><small>(".__( 'Pro account', 'doliconnect' )."?)</small></a>";
}

echo "</div><ul class='list-group list-group-flush'>";

if ( function_exists('dolikiosk') && ! empty(dolikiosk()) ) {
echo doliconnectuserform(null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"]), true), 'full');
} else {
echo doliconnectuserform(null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"]), true), 'small');
}

echo "<li class='list-group-item'><input type='hidden' name='submitted' id='submitted' value='true'>";

if ( function_exists('dolikiosk') && ! empty(dolikiosk()) ) {
echo "<div class='form-group'><label for='pwd1'><small>".__( 'Password', 'doliconnect' )."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Choose your password', 'doliconnect' )."' required='required'></div>
<small id='pwd1' class='form-text text-justify text-muted'>
".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' )."
</small>
<div class='form-group'><label for='pwd2'></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your password', 'doliconnect' )."' required='required'></div>";
echo "</li><li class='list-group-item'>";
}

echo "<div class='custom-control custom-checkbox my-1 mr-sm-2'>
<input type='checkbox' class='custom-control-input' value='1' id='optin1' name='optin1'>
<label class='custom-control-label' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect' )."</label></div>";
echo "<div class='custom-control custom-checkbox my-1 mr-sm-2'>
<input type='checkbox' class='custom-control-input' value='forever' id='validation' name='validation' required>
<label class='custom-control-label' for='validation'> ".__( 'I read and accept the <a href="#" data-toggle="modal" data-target="#cgvumention">Terms & Conditions</a>.', 'doliconnect')."</label>
<div class='invalid-feedback'>".__( 'This field is required.', 'doliconnect' )."</div></div></li>";
echo "</ul><div class='card-body'><button class='btn btn-primary btn-block' type='submit'";
if (get_option('users_can_register')=='1' && (get_site_option( 'registration' ) == 'user' or get_site_option( 'registration' ) == 'all') or (!is_multisite() && get_option( 'users_can_register' ))){
echo "";
}
else {echo " aria-disabled='true'  disabled";}
echo "><b>".__( 'Create an account', 'doliconnect' )."</b></button></form>";
}
echo"</div></div>";
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
echo "</div></div>";
if (get_option( 'wp_page_for_privacy_policy' )) {
echo "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='cgvumention' aria-hidden='true'><div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header'><h5 class='modal-title' id='cgvumentionLabel'>".__( 'Terms & Conditions', 'doliconnect')."</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
<div class='modal-body'>";
echo apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' ))); 
echo "</div></div></div>";}

echo "</div></div>";
} elseif (!is_user_logged_in() && isset($_GET["rpw"])) {
echo "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
if (!$_GET["login"] or !$_GET["key"]) {
wp_redirect(wp_login_url( get_permalink() ));
exit;
} else {   
$user = check_password_reset_key($_GET["key"], $_GET["login"] );
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
$dolibarr = CallAPI("GET", "/doliconnector/".$user->ID, null, 0);
if ($_POST["case"] == 'updatepwd'){
$pwd = sanitize_text_field($_POST["pwd1"]);                                   
if ( ($_POST["pwd1"] == $_POST["pwd2"]) && (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,20}/', $pwd))) {  //"#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#"
$hash = md5($pwd);
//wp_set_password($pwd, $ID);
$update_user = wp_update_user( array (
					'ID' => $user->ID, 
					'user_pass' => $pwd
				)
			);
if ( $dolibarr->fk_user > '0' ) {
$data = [
    'pass' => $pwd
	];
$doliuser = CallAPI("PUT", "/users/".$dolibarr->fk_user, $data, 0);
}

//$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p>Votre mot de passe a été changé avec succès !</p></div>";
$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $user->user_login ) );
wp_redirect(wp_login_url( get_permalink() )."?action=lostpassword&success");
exit;
}
elseif ( $pwd != $_POST["pwd2"] ) {
$msg = "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><span class='fa fa-times-circle'></span> Les 2 nouveaux mots de passe saisis sont différents!</div>";
}
elseif (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd)){
$msg = "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><span class='fa fa-times-circle'></span> Votre nouveau mot de passe doit comporter entre 8 et 20 caractères dont au moins 1 chiffre, 1 lettre, 1 majuscule et 1 symbole.</div>";
}
}
 
echo $msg."<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( $dolibarr->fk_user > '0') {
echo "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect' )."</b></li>";
} 
echo "<li class='list-group-item'><h5 class='card-title'>".__( 'Change your password', 'doliconnect' )."</h5><form class='was-validated' id='fpwForm' action='' method='post'><input type='hidden' name='submitted' id='submitted' value='true' />
<div class='form-group'><label for='pwd1'><small>".__( 'New password', 'doliconnect' )."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Enter your new password', 'doliconnect' )."' ";
if ( DOLICONNECT_DEMO == $user->ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo "></div>
<small id='pwd1' class='form-text text-justify text-muted'>
".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' )."
</small>
<div class='form-group'><label for='pwd2'></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your new password', 'doliconnect' )."' ";
if ( DOLICONNECT_DEMO == $user->ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo "></div>
</div></div></li><li class='list-group-item'><input type='hidden' name='case' value ='updatepwd'><button class='btn btn-danger btn-block' type='submit' ";
if ( DOLICONNECT_DEMO == $user->ID ) {
echo ' disabled';
}
echo "><b>".__( 'Update', 'doliconnect' )."</b></button></form></li></ul>";
echo "</div>";

}
}
} elseif ( isset($_GET["provider"]) ) { 
include( plugin_dir_path( __DIR__ ) . 'doliconnect-pro/includes/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'doliconnect-pro/includes/hybridauth/src/config.php');
try{
    //Feed configuration array to Hybridauth
    $hybridauth = new Hybridauth\Hybridauth($config);

    //Then we can proceed and sign in with Twitter as an example. If you want to use a diffirent provider, 
    //simply replace 'Twitter' with 'Google' or 'Facebook'.

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
catch(\Exception $e){
        // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
    // let hybridauth forget all about the user so we can try to authenticate again.
    // Display the recived error, 
    // to know more please refer to Exceptions handling section on the userguide
    switch( $e->getCode() ){ 
        case 0 : echo "Unspecified error."; break;
        case 1 : echo "Hybriauth configuration error."; break;
        case 2 : echo "Provider not properly configured."; break;
        case 3 : echo "Unknown or disabled provider."; break;
        case 4 : echo "Missing provider application credentials."; break;
        case 5 : echo "Authentication failed. " 
                  . "The user has canceled the authentication or the provider refused the connection."; 
        case 6 : echo "User profile request failed. Most likely the user is not connected "
                  . "to the provider and he should to authenticate again."; 
               $adapter->logout(); 
               break;
        case 7 : echo "User not connected to the provider."; 
               $adapter->logout(); 
               break;
    } 
    echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
//echo "<hr /><h3>Trace</h3> <pre>" . $e->getTraceAsString() . "</pre>";  
}
} elseif ( !is_user_logged_in() && isset($_GET["fpw"]) ) { 
echo "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";
  
if( isset($_POST['submitted']) ) {

    if( sanitize_email($_POST['user_email']) === '' )  {
        $emailError = __( 'A valid email is need to reset your password', 'doliconnect' );
        $hasError = true;
    } elseif ( !email_exists(sanitize_email($_POST['user_email'])) ) {
        $emailError = __( 'No account seems to be linked to this email address', 'doliconnect' );
        $hasError = true;   
    }
    else {
        $email = sanitize_email($_POST['user_email']);

        $emailTo = get_option('tz_email');
        if (!isset($emailTo) || ($emailTo == '') ){
            $emailTo = get_option('admin_email');
        }
        
$user=get_user_by( 'email', $email);     
$key=get_password_reset_key($user->ID);
$hashed = current_time('timestamp') . ':' . $wp_hasher->HashPassword($key);
$key_saved = $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

$arr_params = array( 'rpw' => true, 'key' => $key, 'login' => $user->user_login);  
$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

if ( true == $key_saved && DOLICONNECT_DEMO != $user->ID ) {
			$sitename = get_option('blogname');
      $siteurl = get_option('siteurl');
      $subject = "[$sitename] ".__( 'Reset Password', 'doliconnect' );
      $body = __( 'A request to change your password has been made. You can change it via the single-use link below:', 'doliconnect' )."<br /><br /><a href='".$url."'>".$url."</a><br /><br />".__( 'If you have not made this request, please ignore this email.', 'doliconnect' )."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($email, $subject, $body, $headers);

				if( $mail )
					$emailSent = true;		
			} else {
      $emailError = __( 'Reset password of this account is not permitted', 'doliconnect' );
      $emailSent = false;	
      }
       
 }
}

if ( isset($emailSent) && $emailSent == true ) { 
echo "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect' )."</p></div>";
} elseif ( isset($hasError) || isset($captchaError) ) { 
echo "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p>$emailError</p></div>";
}

echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

echo "<script>";
?>
window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);
var form2 = document.getElementById('fpw-form');
form2.addEventListener('submit', function(event) {
 $(document).ready(function(){
    $(window).scrollTop(0);
});
jQuery('#fpw-form').hide(); 
jQuery('#doliloading-fpw').show();
console.log("submit");
form2.submit();
});
<?php
echo "</SCRIPT>";
echo "<form id='fpw-form' action='' method='post' class='needs-validation' novalidate><input type='hidden' name='submitted' id='submitted' value='true' />";
echo $msg;
echo'<script>';
?>
window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);
var form2 = document.getElementById('fpw-form');
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
<?php            
echo '</script>'; 
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".__( 'Forgot password?', 'doliconnect' )."</h5>";

echo "<div class='form-group'><label for='inputemail'><small>".__( 'Please enter the email address by which you registered your account.', 'doliconnect' )."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_email' type='email' placeholder='".__( 'Email', 'doliconnect' )."' name='user_email' value ='' required><DIV class='invalid-tooltip'>".__( 'This field is required.', 'doliconnect' )."</DIV>";
echo "</div></div></div>";
echo "<ul class='list-group list-group-flush'><li class='list-group-item'>";
echo "<button data-sitekey='".get_option('doliconnect_captcha_sitekey')."' data-callback='onSubmit' class='g-recaptcha btn btn-primary btn-block' type='submit'><b>".__( 'Submit', 'doliconnect' )."</b></button>";
echo "</li></ul>";
echo "</div>";

echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p></form>"; 
echo doliloading('fpw');

echo "</div></div>";

} else {

echo "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations and much more via this secure client area.', 'doliconnect' )."</p></div></div></div>";
echo "<div class='col-xs-12 col-sm-12 col-md-9'>";

if( isset($emailSent) && $emailSent == true ) { 
echo "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect' )."</p></div>";
}

if($_GET["login"]=='failed') { 
echo "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops', 'doliconnect' )."</h4><p>".__( 'There is no account for these login data or the email and/or the password are not correct.', 'doliconnect' )."</p></div>";
}
echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'><li class='list-group-item'>";

if ( function_exists('doliconnect_modal') && get_option('doliloginmodal') == '1' ) {

echo "<center><i class='fas fa-user-lock fa-fw fa-10x'></i><br><br>";
//echo "<h2>".__( 'Restricted area', 'doliconnect' )."</h2></center>";
echo '<a href="#" id="login-'.current_time('timestamp').'" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'doliconnect').'" class="btn btn-block btn-primary my-2 my-sm-0" role="button">'.__('You have already an account', 'doliconnect').'</a>';
if (((!is_multisite() && get_option( 'users_can_register' )) or (get_option('users_can_register')=='1' && (get_site_option( 'registration' ) == 'user' or get_site_option( 'registration' ) == 'all')))) 
{
echo '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect' ).'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
echo '<a href="'.wp_registration_url( get_permalink() ).'" id="login-'.current_time('timestamp').'" class="btn btn-block btn-primary my-2 my-sm-0" role="button">'.__("You don't have an account", 'doliconnect').'</a>';
}

} else {

echo "<div id='loginmodal-form'><h5 class='card-title'>".__( 'Welcome', 'doliconnect' )."</h5>";
echo "<b>".get_option('doliaccountinfo')."</b>";

if (function_exists('socialconnect')) {
socialconnect(get_permalink());
}

if (function_exists('secupress_get_module_option') && secupress_get_module_option('move-login_slug-login', $slug, 'users-login' )) {
$login_url=site_url()."/".secupress_get_module_option('move-login_slug-login', $slug, 'users-login' ); 
}else{
$login_url=site_url()."/wp-login.php"; }
if (isset($_GET["redirect_to"])) { $redirect_to=$_GET["redirect_to"]; } else {
$redirect_to="//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];} 
echo "<form class='was-validated' id='LoginForm' action='$login_url' method='post'>";
echo "<script>";
?>
var form = document.getElementById('loginmodal-form');
form.addEventListener('submit', function(event) {
 $(document).ready(function(){
    $(window).scrollTop(0);
});
loadingLoginModal();
console.log("submit");
form.submit();
});
function loadingLoginModal() {
jQuery('#LoginForm').hide(); 
jQuery('#doliloading-login').show(); 
}
<?php
echo "</script>";
echo "<div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_login' type='email' placeholder='".__( 'Email', 'doliconnect' )."' name='log' value='' required autofocus>";
echo "</div></div><div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='user_pass' type='password' placeholder='".__( 'Password', 'doliconnect' )."' name='pwd' value ='' required>";
echo "</div></div>";
echo "<div><div class='float-left'><small>";
if ( ((!is_multisite() && get_option( 'users_can_register' )) or (get_option('users_can_register') == '1' && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all'))) ) {
echo "<a href='".wp_registration_url( get_permalink() )."' role='button' title='".__( 'Create an account', 'doliconnect' )."'>".__( 'Create an account', 'doliconnect' )."</a>";
}
//<input type='checkbox' class='custom-control-input' value='forever' id='remembermemodal' name='rememberme'>";
//echo "<label class='custom-control-label' for='remembermemodal'> ".__( 'Remember me', 'doliconnect' )."</label>";
echo "</div><div class='float-right'><a href='".wp_lostpassword_url( get_permalink() )."' role='button' title='".__( 'Forgot password?', 'doliconnect' )."'>".__( 'Forgot password?', 'doliconnect' )."</a></small></div></div>"; 

echo "</div></li><li class='list-group-item'><input type='hidden' value='$redirect_to' name='redirect_to'><button id='submit' class='btn btn-block btn-primary' type='submit' name='submit' value='Submit'";
echo "><b>".__( 'Sign in', 'doliconnect' )."</b></button></form>";
echo doliloading('login');

}

echo "</li></lu></div>";

echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";

echo "</div></div>";
}
}
add_shortcode('doliaccount', 'doliaccount_shortcode');
// ********************************************************
function dolicontact_shortcode() { 
global $wpdb,$current_user;

doliconnect_enqueues();

if( ! empty($_POST['email-control']) )   //! $is_valid  || ! 
{
$emailError = "Votre demande n'est pas autorisée";
}
elseif(isset($_POST['submitted'])) {
    if( sanitize_text_field($_POST['contactName']) === '' ) {
        $nameError = 'Please enter your name.';
        $hasError = true;
    } else {
        $name = sanitize_text_field($_POST['contactName']);
    }

    if( sanitize_email($_POST['email']) === '' )  {
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

    if(!isset($hasError)) {
        $emailTo = get_option('tz_email');
        $user=get_userdata( $_GET['user'] ); 
        
        if (isset($_GET['user']) && $user == true && $_GET['type']==EMAIL){
        $emailTo = $user->user_email;}
        elseif (!isset($emailTo) || ($emailTo == '') ){
            $emailTo = get_option('admin_email');
        }
        $subject = $_POST['type'];
        $body = "Nom: $name <br />Email: $email <br />Message: $comments";
        $headers = array("Content-Type: text/html; charset=UTF-8'","From: $name <$email>"); 
        wp_mail($emailTo, $subject, $body, $headers);
        $emailSent = true;
    }

}

echo "<div class='row'><div class='col-md-4'><div class='form-group'><h4>Siège social</h4>";
echo doliconst(MAIN_INFO_SOCIETE_ADDRESS);
echo "<br />";
echo doliconst(MAIN_INFO_SOCIETE_ZIP);
echo " ";
echo doliconst(MAIN_INFO_SOCIETE_TOWN); 
echo "</div></div><div class='col-md-8'><div id='content'>";
if(isset($emailSent) && $emailSent == true) { 
echo "<div class='alert alert-success'>
<p>Merci! Votre message a été envoyé avec succès.</p>
</div>";
} else { 
if(isset($hasError) || isset($captchaError)) { 
echo "<div class='alert alert-warning'>
<a class='close' data-dismiss='alert'>x</a>
<h4 class='alert-heading'>Sorry, an error occured.</h4>
<p class='error'>Please try again!<p></div>";
}

echo "<form action='' id='doliconnect-contactform' method='post' class='was-validated'>";

echo "<script>";
?>

window.setTimeout(function () {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);

var form = document.getElementById('doliconnect-contactform');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0); 
console.log("submit");
form.submit();

});

<?php
echo "</script>";

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>
<li class='list-group-item'><div class='form-group'>
<label class='control-label' for='contactName'><small>".__( 'Complete name', 'doliconnect' )."</small></label>
<input class='form-control' type='text' name='contactName' autocomplete='off' id='contactName' value=";
if (is_user_logged_in()){ echo "'$current_user->user_lastname $current_user->user_firstname'"; } else { echo "''";}
if (is_user_logged_in()){ echo " readonly";} else {echo " required"; }
echo "/>";
if($nameError != '') { 
echo "<p><span class='error'>$nameError</span></p>";
} 
echo "</div>
<div class='form-group'>
<label class='control-label' for='email'><small>".__( 'Email', 'doliconnect' )."</small></label>
<input class='form-control' type='email' name='email' autocomplete='off' id='email' value='$current_user->user_email'";
if (is_user_logged_in()){echo " readonly";} else {echo " required";}
echo "/>";
if($emailError != '') {
echo "<p><span class='error'>$emailError</span></p>";
}
echo "</div>
<div class='form-group d-none'>
<label class='control-label' for='email-control'><small>".__( 'Email', 'doliconnect' )."</small></label>
<input class='form-control' type='email' name='email-control' autocomplete='off' id='email-control' ";
echo "/>";
echo "</div>";

echo "<div class='form-group'><label class='control-label' for='type'><small>".__( 'Type of request', 'doliconnect' )."</small></label>";
$type = CallAPI("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100", null, MONTH_IN_SECONDS);

if (isset($type)) { 
$tp= __( 'Issue or problem', 'doliconnect' ).__( 'Commercial question', 'doliconnect' ).__( 'Change or enhancement request', 'doliconnect' ).__( 'Project', 'doliconnect' ).__( 'Other', 'doliconnect' );
echo "<select class='custom-select' id='ticket_type'  name='ticket_type'>";
foreach ($type as $postv) {
echo "<option value='".$postv->code."' ";
if ( $_GET['type']==$postv->code ) {
echo "selected ";
} elseif ( $postv->use_default==1 ) {
echo "selected ";}
echo ">".__($postv->label, 'doliconnect' )."</option>";
}
echo "</select>";
}
echo "</div>";

echo "<div class='form-group'>
<label class='control-label' for='commentsText'><small>".__( 'Message', 'doliconnect' )."</small></label>
<textarea class='form-control' name='comments' id='commentsText' rows='7' cols='20' required></textarea>";
if ($commentError != '') { 
echo "<p><span class='error'>$commentError</span></p>";
}

if (!is_user_logged_in()){
echo '</li><li class="list-group-item"><div class="custom-control custom-checkbox"><input id="rgpdinfo" class="custom-control-input form-control-sm" type="checkbox" name="rgpdinfo" value="ok" required><label class="custom-control-label w-100" for="rgpdinfo"><small class="form-text text-muted"> '.__( 'I agree to save my personnal informations in order to contact me', 'doliconnect' ).'</small></label></div>';  
}
echo "</li></ul>";
echo "<div class='card-body'><button class='btn btn-primary btn-block' type='submit'><small>".__( 'Send', 'doliconnect' )."</small></button><input type='hidden' name='submitted' id='submitted' value='true' /></div></div></div></div></form>";
} 
echo "</div>";
                   
}
add_shortcode('dolicontact', 'dolicontact_shortcode');
// ********************************************************
function update_synctodolibarr($dolibarr) {
global $current_user,$wpdb;
$entity = get_current_blog_id();
get_currentuserinfo();

$resultatsa = $wpdb->get_results(
$wpdb->prepare(
        "SELECT * FROM ".$wpdb->base_prefix."usermeta where user_id = %d and meta_key like '".$wpdb->prefix."doliextra_%' ",
        $current_user->ID
    )
);
foreach ($resultatsa as $posta) {
$subject = $posta->meta_key ;
$search = $wpdb->prefix."doliextra_";
$key = str_replace($search, '', $subject) ;
$value = $posta->meta_value;
$extrafields[$key] = $value;
}

if ($current_user->billing_type == 'phy'){
$name = $current_user->user_firstname." ".$current_user->user_lastname;}
else {$name = $current_user->billing_company;}
if (NULL!=constant("DOLIBARR_MEMBER")) {
$infomember = [
    'login'  => $current_user->user_login,
    'morphy'  => $current_user->billing_type,
    'civility_id'  => $current_user->billing_civility,            
    'firstname'  => $current_user->user_firstname,
    'lastname'  => $current_user->user_lastname,
    'address' => $current_user->billing_address,
    'zip' => $current_user->billing_zipcode,
    'town' => $current_user->billing_city,
    'country_id' => $current_user->billing_country,
    'email' => $current_user->user_email,
    'phone' => $current_user->billing_phone,
    'birth' => $current_user->billing_birth,
    'array_options' => $extrafields
	]; 

$adherent = CallAPI("PUT", "/adherentsplus/".constant("DOLIBARR_MEMBER"), $infomember, 0);
update_usermeta( $current_user->ID, 'billing_birth', $current_user->billing_birth);
}
if ( constant("DOLIBARR") > 0 ) {
$info = [
    'status' => 1,
    'name'  => $name,
    'address' => $current_user->billing_address,
    'zip' => $current_user->billing_zipcode,
    'town' => $current_user->billing_city,
    'country_id' => $current_user->billing_country,
    'email' => $current_user->user_email,
    'phone' => $current_user->billing_phone,
    'url' => $current_user->user_url,
    'array_options' => $extrafields
	];
$thirparty = CallAPI("PUT", "/thirdparties/".constant("DOLIBARR"), $info, 0);
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
add_filter( 'login_headertitle', 'my_login_logo_url_title' );

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
		
echo $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
  echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

$time=current_time('timestamp');
$entity=get_current_blog_id();

if (is_user_logged_in()){ 
  echo "<a class='btn btn-block btn-warning' href='".doliconnecturl('doliaccount') . "?module=ticket&type=ISSUE&create' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect' )."</a>";
} else {
  echo "<a class='btn btn-block btn-warning' href='".esc_url( add_query_arg( 'type', 'issue', doliconnecturl('dolicontact')) ) . "' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect' )."</a>";
} 
  echo $args['after_widget'];  
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
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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
			'description' => 'lightbox adhésion',
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
global $current_user,$wpdb;
		// outputs the content of the widget
    
  		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

$entity=get_current_blog_id();

if (constant("DOLIBARR_MEMBER") > 0) {
$adherent = CallAPI("GET", "/adherentsplus/".constant("DOLIBARR_MEMBER"), null);
}
 
if ($adherent->statut == '1' && $adherent->datefin < current_time('timestamp')) {
echo "<A class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' >".__( 'Pay my subscription', 'doliconnect' )."</a>"; 
}
elseif ($adherent->statut == '0') {
echo "<a class='btn btn-block btn-info' href='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' >Adhérer</a>"; 
}
elseif ($adherent->statut == '-1') {
echo "<a class='btn btn-block btn-warning disabled' href='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' >Adhésion demandée</a>"; 
}
elseif (!$adherent->id > 0) {
echo "<a class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' >Adhérer à l'association</a>"; 
}


echo $args['after_widget'];  
    
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
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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
