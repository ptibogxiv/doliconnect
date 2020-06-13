<?php

function doliaccount_display($content, $controle = false) {
global $wpdb, $current_user;

if ( (in_the_loop() && is_main_query() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) || ( (!is_user_logged_in() && !empty(get_option('doliconnectrestrict')) && !is_page(doliconnectid('doliaccount')) && !empty($controle) ) || (!is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !empty(get_option('doliconnectrestrict')) && !is_page(doliconnectid('doliaccount')) && !empty($controle) ) )) {

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

print "<div class='row'>";
if ( empty(get_option('doliconnectrestrict')) || is_user_logged_in() ) {
print "<div class='col-xs-12 col-sm-12 col-md-3'><div class='row'><div class='col-3 col-xs-4 col-sm-4 col-md-12 col-xl-12'><div class='card shadow-sm' style='width: 100%'>";
print get_avatar($ID);

if ( !defined("DOLIBUG") && is_user_logged_in() && is_user_member_of_blog( $current_user->ID, get_current_blog_id())) {
print "<a href='".esc_url( add_query_arg( 'module', 'avatars', doliconnecturl('doliaccount')) )."' title='".__( 'Edit avatar', 'doliconnect')."' class='card-img-overlay'><div class='d-block d-sm-block d-xs-block d-md-none'></div><div class='d-none d-md-block'><i class='fas fa-camera fa-2x'></i> ".__( 'Edit', 'doliconnect')."</div></a>";
}
print "<ul class='list-group list-group-flush'><a href='".esc_url( doliconnecturl('doliaccount') )."' class='list-group-item list-group-item-light list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-home'></i></div><div class='d-none d-md-block'><i class='fas fa-home'></i> ".__( 'Home', 'doliconnect')."</div></center></a>";
print "</ul>";

print "</div><br></div>";
print "<div class='col-9 col-xs-8 col-sm-8 col-md-12 col-xl-12'>";
} else {
print "<div class='col-md-6 offset-md-3'>";
}
if ( is_user_logged_in() && is_user_member_of_blog( $current_user->ID, get_current_blog_id())) {

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( defined("DOLIBUG") ) {

print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'><div class='card shadow-sm'><div class='card-body'>";
print dolibug(isset($thirdparty->error->message)?$thirdparty->error->message:$thirdparty);
print "</div></div></div></div>";

} elseif ( $thirdparty->status != '1' ) {

print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'><div class='card shadow-sm'><div class='card-body'>";
print '<br><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>'.__( 'This account is closed. Please contact us for reopen it.', 'doliconnect').'</h4></div></center><br><br><br><br><br>';
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

} elseif ( isset($_GET["action"]) && $_GET["action"] == 'confirmaction' ) {
print "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
print "<div class='card shadow-sm'><div class='card-body'>";
		if ( ! isset( $_GET['request_id'] ) ) {
			print __( 'Missing request ID.');
		}

		if ( ! isset( $_GET['confirm_key'] ) ) {
			print __( 'Missing confirm key.');
		}   

if ( isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
		$request_id = (int) $_GET['request_id'];
		$key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
		$result     = wp_validate_user_request_key( $request_id, $key );

//if ( !is_wp_error( $result ) ) {
do_action( 'user_request_action_confirmed', $request_id );
$message = _wp_privacy_account_request_confirmed_message( $request_id );
print $message;
//}
}

print "</div></div></div>";

} else {

print "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</p></div></div></div>";
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

if ( has_action('supplier_doliconnect_menu') && $thirdparty->fournisseur == '1' ) {
print "<div class='list-group shadow-sm'>";
do_action('supplier_doliconnect_menu');
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
} else { 
//print "<p class='font-weight-light' align='justify'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</p>";
print "</div></div></div>";

if ( empty(get_option('doliconnectrestrict')) ) {
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
} else {
print "<div class='col-md-6 offset-md-3'>";
}

if ( isset($_GET["action"]) && $_GET["action"] == 'confirmaction' ) {

		if ( ! isset( $_GET['request_id'] ) ) {
			print __( 'Missing request ID.');
		}

		if ( ! isset( $_GET['confirm_key'] ) ) {
			print __( 'Missing confirm key.');
		}   

if ( isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
		$request_id = (int) $_GET['request_id'];
		$key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
		$result     = wp_validate_user_request_key( $request_id, $key );

//if ( !is_wp_error( $result ) ) {
do_action( 'user_request_action_confirmed', $request_id );
$message = _wp_privacy_account_request_confirmed_message( $request_id );
print $message;
//}
}
print "</div></div>";
} elseif ( isset($_GET["action"]) && $_GET["action"] == 'signup' ) {

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
        $emailError = "".__( 'This email address is already linked to an account. You can reactivate your account through this <a href=\'".wp_lostpassword_url( get_permalink() )."\' title=\'lost password\'>form</a>.', 'doliconnect')."";
        $hasError = true;
        } else {
        $email = sanitize_email($thirdparty['email']);
        }

if ( $thirdparty['firstname'] == $_POST['user_nicename'] && $thirdparty['firstname'] == $thirdparty['lastname']) {
        $emailError = "".__( 'Create this account is not permitted', 'doliconnect')."";       
        $hasError = true;
        }

    if(!isset($hasError)) {
        $emailTo = get_option('tz_email');
        if (!isset($emailTo) || ($emailTo == '') ) {
        $emailTo = get_option('admin_email');
        }

$sitename = get_option('blogname');
$subject = "[".$sitename."] ".__( 'Registration confirmation', 'doliconnect')."";
if ( !empty($_POST['pwd1']) && $_POST['pwd1'] == $_POST['pwd2'] ) {
$password=sanitize_text_field($_POST['pwd1']);
} else {
$password = wp_generate_password( 12, false ); 
}
      
$ID = wp_create_user(uniqid(), $password, $email );

$role = get_option( 'default_role' );

if ( is_multisite() ) {
$entity = dolibarr_entity(); 
add_user_to_blog($entity,$ID,$role);
} else {
$user = get_user_by( 'ID', $ID);
$user->set_role($role);
}

if ( $thirdparty['morphy'] == 'mor' ) {
$thirdparty['tva_intra'] =strtoupper(sanitize_user($thirdparty['tva_intra']));
} else { $thirdparty['tva_intra'] = ''; }

if ( $thirdparty['morphy'] != 'mor' && get_option('doliconnect_disablepro') != 'mor' ) {
$thirdparty['name'] = ucfirst(strtolower($thirdparty['firstname']))." ".strtoupper($thirdparty['lastname']);
} 
 
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($thirdparty['email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
if ( isset($thirdparty['name'])) wp_update_user( array( 'ID' => $ID, 'display_name' => sanitize_user($thirdparty['name'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($thirdparty['firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($thirdparty['lastname']))));
if ( isset($_POST['description'])) wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
if ( isset($thirdparty['url'])) wp_update_user( array( 'ID' => $ID, 'user_url' => sanitize_textarea_field($thirdparty['url'])));
update_user_meta( $ID, 'civility_id', sanitize_text_field($thirdparty['civility_id']));
update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
if ( $thirdparty['morphy'] == 'mor' ) { update_user_meta( $ID, 'billing_company', sanitize_text_field($thirdparty['name'])); }
update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);
if ( isset($_POST['optin1']) ) { update_user_meta( $ID, 'optin1', $_POST['optin1'] ); }

$body = sprintf(__('Thank you for your registration on %s.', 'doliconnect'), $sitename);

$user = get_user_by( 'ID', $ID);
 
if ( function_exists('dolikiosk') && ! empty(dolikiosk()) && $user ) {  

print $dolibarrid = doliconnector($user, 'fk_soc', true, $thirdparty);
do_action('wp_dolibarr_sync', $thirdparty);

//wp_set_current_user( $ID, $user->user_login );
//wp_set_auth_cookie( $ID, false);
//do_action( 'wp_login', $user->user_login, $user);

//wp_redirect(esc_url(home_url()));
//exit;   
} else { 
$key = get_password_reset_key($user);

$arr_params = array( 'action' => 'rpw', 'key' => $key, 'login' => $user->user_login);  
$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

$body .= "<br><br>".__('To activate your account on and choose your password, please click on the following link', 'doliconnect').":<br><br><a href='".$url."'>".$url."</a>";
}

$body .= "<br><br>".sprintf(__("Your %s's team", 'doliconnect'), $sitename)."<br>".get_option('siteurl');
$headers = array('Content-Type: text/html; charset=UTF-8'); 
wp_mail($email, $subject, $body, $headers);
$emailSent = true;
               
}
}

if ( function_exists('dolikiosk') && ! empty(dolikiosk()) && isset($user) && isset($emailSent) && $emailSent == true ) { 
print dolialert('success', __( 'Your account was created. Now, you are connected', 'doliconnect'));
} elseif ( isset($emailSent) && $emailSent == true ) { 
print dolialert('success', __( 'Your account was created and an account activation link was sent by email. Don\'t forget to look at your unwanted emails if you can\'t find our message.', 'doliconnect'));
} else {
if ( isset($hasError) || isset($captchaError) ) {
print dolialert('danger', $emailError);
}
}

print "<form id='doliconnect-signinform' action='".doliconnecturl('doliaccount')."?action=signup' role='form' method='post' class='was-validated'>";

print doliloaderscript('doliconnect-signinform'); 

print "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".__( 'Create an account', 'doliconnect')."</h5></div>";

print doliuserform( null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'thirdparty');

print "<div class='card-body'><input type='hidden' name='submitted' id='submitted' value='true'><button class='btn btn-primary btn-block' type='submit'";
if ( get_option('users_can_register')=='1' && ( get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all' ) || ( !is_multisite() && get_option( 'users_can_register' )) ) {
print "";
} else { print " aria-disabled='true'  disabled"; }
print "><b>".__( 'Create an account', 'doliconnect')."</b></button></form>";

print "</div>";
print '<div class="card-footer text-muted">';
print "<small><div class='float-left'>";

print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print '</div></div></form>';

do_action( 'login_footer');

} elseif ( isset($_GET["action"]) && $_GET["action"] == 'rpw' ) {

if (!$_GET["login"] || !$_GET["key"]) {
wp_redirect(wp_login_url( get_permalink() ));
exit;
} else {   
$user = check_password_reset_key( esc_attr($_GET["key"]), esc_attr($_GET["login"]) );
if ( ! $user || is_wp_error( $user ) ) {
if ( $user && $user->get_error_code() === 'expired_key' ){
$arr_params = array( 'action' => 'lostpassword', 'error' => 'expiredkey');  
wp_redirect(esc_url( add_query_arg( $arr_params, wp_login_url( get_permalink() )) ));
exit;
}else{
$arr_params = array( 'action' => 'lostpassword', 'error' => 'invalidkey');  
wp_redirect(esc_url( add_query_arg( $arr_params, wp_login_url( get_permalink() )) ));
exit;
}
exit;
} else {
$dolibarr = callDoliApi("GET", "/doliconnector/".$user->ID, null, 0);

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( isset($dolibarr->fk_user) && $dolibarr->fk_user > 0){  
$request = "/users/".$dolibarr->fk_user;
$doliuser = callDoliApi("GET", $request , null, dolidelay('thirdparty'));
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect')."</b></li>";
} 
print "<li class='list-group-item'><h5 class='card-title'>".__( 'Change your password', 'doliconnect')."</h5>";

print "<div id='DoliRpwAlert' class='text-danger font-weight-bolder'></div><form id='dolirpw-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
print "<input type='hidden' name='action' value='dolirpw_request'>";
print "<input type='hidden' name='dolirpw-nonce' value='".wp_create_nonce( 'dolirpw-nonce')."'>";
if (isset($_GET["key"]) && isset($_GET["login"])) {
print "<input type='hidden' name='key' value='".esc_attr($_GET["key"])."'><input type='hidden' name='login' value='".esc_attr($_GET["login"])."'>";
}

print "<script>";
print 'jQuery(document).ready(function($) {
	
	jQuery("#dolirpw-form").on("submit", function(e) {
  jQuery("#DoliconnectLoadingModal").modal("show");
	e.preventDefault();
    
	var $form = $(this);
  var url = "'.doliconnecturl('doliaccount').'";  
jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) { 
		$.post($form.attr("action"), $form.serialize(), function(response) {
      if (response.success) {
      document.location = url;
      } else {
      if (document.getElementById("DoliRpwAlert")) {
      document.getElementById("DoliRpwAlert").innerHTML = response.data;      
      }
      }
jQuery("#DoliconnectLoadingModal").modal("hide");

		}, "json");  
  });
});
});';
print "</script>";

print "<div class='form-group'><label for='pwd1'><small>".__( 'New password', 'doliconnect')."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Enter your new password', 'doliconnect')."' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
print ' readonly';
} else {
print ' required';
}
print "></div>
<small id='pwd1' class='form-text text-justify text-muted'>
".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect')."
</small>
<div class='form-group'><label for='pwd2'></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your new password', 'doliconnect')."' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
print ' readonly';
} else {
print ' required';
}
print "></div>
</div></div></li></ul><div class='card-body'><input type='hidden' name='case' value ='updatepwd'><button class='btn btn-danger btn-block' type='submit' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
print ' disabled';
}
print "><b>".__( 'Update', 'doliconnect')."</b></button></form></div>";
print "<div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, null, dolidelay('thirdparty'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";

}}

} elseif ( isset($_GET["provider"]) && $_GET["provider"] != null ) {
include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/config.php');
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
$emailError = __( 'No account seems to be linked to this email address', 'doliconnect');
        $hasError = true;   
    } else {
$user=get_user_by( 'email', $userProfile->email);    
wp_set_current_user($user->ID); 
if (wp_validate_auth_cookie()==FALSE)
{
    wp_set_auth_cookie($user->ID, true, true);
}   
do_action('wp_login', $user->user_login, $user); 

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
    print "<br><br><b>Original error message:</b> " . $e->getMessage();
//print "<hr /><h3>Trace</h3> <pre>" . $e->getTraceAsString() . "</pre>";  
}
} elseif ( isset($_GET["action"]) && $_GET["action"] == 'fpw' ) { 

print "<div id='DoliFpwAlert' class='text-danger font-weight-bolder'></div><form id='dolifpw-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
print "<input type='hidden' name='action' value='dolifpw_request'>";
print "<input type='hidden' name='dolifpw-nonce' value='".wp_create_nonce( 'dolifpw-nonce')."'>";

print "<script>";
print 'jQuery(document).ready(function($) {
	
	jQuery("#dolifpw-form").on("submit", function(e) {
  jQuery("#DoliconnectLoadingModal").modal("show");
	e.preventDefault();
    
	var $form = $(this);
    
jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) { 
		$.post($form.attr("action"), $form.serialize(), function(response) {

      if (document.getElementById("DoliFpwAlert")) {
      document.getElementById("DoliFpwAlert").innerHTML = response.data;      
      }

jQuery("#DoliconnectLoadingModal").modal("hide");

		}, "json");  
  });
});
});';
print "</script>";
 
print "<div class='card shadow-sm'><div class='card-header'><h5 class='card-title'>".__( 'Forgot password?', 'doliconnect')."</h5></div>";
print "<ul class='list-group list-group-flush'><li class='list-group-item'>";
print "<div class='form-group'><label for='inputemail'><small class='text-justify'><i class='fas fa-at fa-fw'></i> ".__( 'Please enter the email address by which you registered your account.', 'doliconnect')."</small></label>
<div class='input-group mb-2 mr-sm-2'>
<input class='form-control' id='user_email' type='email' placeholder='".__( 'Email', 'doliconnect')."' name='user_email' value ='' required>";
print "</div></div>";

print "</li></lu><div class='card-body'>";
print "<button class='btn btn-danger btn-block' type='submit' value='submit'><b>".__( 'Submit', 'doliconnect')."</b></button></form>";
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";

print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} else {

if (is_user_logged_in() && !is_user_member_of_blog( $current_user->ID, get_current_blog_id()) ) {
print dolialert('danger', __( 'This account is not allowed to connect this website.', 'doliconnect'));
// TODO logout script
} elseif ( isset($_GET["login"]) && $_GET["login"] == 'failed' ) { 
print dolialert('danger', __( 'There is no account for these login data or the email and/or the password are not correct.', 'doliconnect'));
}

if ( isset($_GET["action"]) && $_GET["action"] == 'lostpassword' ) {

if( isset($_GET["success"]) ) { 
print dolialert('success', __( 'Your password have been updated.', 'doliconnect'));
} elseif ( isset($_GET["error"]) && $_GET["error"] == 'expiredkey' ) { 
print dolialert('danger', __( 'The security key is expired!', 'doliconnect'));
} elseif ( isset($_GET["error"]) && $_GET["error"] == 'invalidkey' ) { 
print dolialert('danger', __( 'The security key is invalid!', 'doliconnect'));
}

}


$image_attributes = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full', false); 
if ( $image_attributes && !empty(get_option('doliconnectrestrict'))) {
print '<div class="card shadow-lg border-0" style="-webkit-backdrop-filter: blur(6px);backdrop-filter: blur(6px);background-color: rgba(255, 255, 255, 0.6);">';
print '<img src="'.$image_attributes[0].'" class="card-img-top" alt="..."/>';
} else { 
print "<div class='card shadow-sm' >";
print "<div class='card-header'>";
if ( empty(get_option('doliconnectrestrict')) ) {
print "<h5 class='card-title'>".__( 'Welcome', 'doliconnect')."</h5>";
} else {
print "<h5 class='card-title'>".__( 'Access restricted to users', 'doliconnect')."</h5>";
}
print "</div>";
}
if ( function_exists('doliconnect_modal') && get_option('doliloginmodal') == '1' ) {

print "<ul class='list-group list-group-flush'><li class='list-group-item'><center><i class='fas fa-user-lock fa-fw fa-10x'></i><br><br>";
//print "<h2>".__( 'Restricted area', 'doliconnect')."</h2></center>";
print "</li></lu><div class='card-body'>";

print '<a href="#" id="login-'.current_time('timestamp').'" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'doliconnect').'" class="btn btn-block btn-outline-secondary bg-light text-body" role="button">'.__('You have already an account', 'doliconnect').'</a>';

if ((!is_multisite() && get_option( 'users_can_register' )) || ((!is_multisite() && get_option( 'dolicustsupp_can_register' )) || ((get_option( 'dolicustsupp_can_register' ) || get_option('users_can_register') == '1') && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
print '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect').'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
print '<a href="'.wp_registration_url( get_permalink() ).'" id="login-'.current_time('timestamp').'" class="btn btn-block btn-outline-secondary bg-light text-body" role="button">'.__("You don't have an account", 'doliconnect').'</a>';
}

} else {

do_action( 'login_head');

print "<div class='card-body'>";
print "<b>".get_option('doliaccountinfo')."</b></div>";

if ( function_exists('socialconnect') ) {
print socialconnect(get_permalink());
}

if ( function_exists('secupress_get_module_option') && secupress_get_module_option('move-login_slug-login', $slug, 'users-login' ) ) {
$login_url=site_url()."/".secupress_get_module_option('move-login_slug-login', $slug, 'users-login'); 
} else {
$login_url=site_url()."/wp-login.php"; }
if ( isset($_GET["redirect_to"])) { $redirect_to=$_GET["redirect_to"]; } else {
$redirect_to=$_SERVER['HTTP_REFERER'];}
 
print "<form class='was-validated' id='doliconnect-loginform' action='$login_url' method='post'>";
print "<ul class='list-group list-group-flush'><li class='list-group-item'>";
print doliloaderscript('doliconnect-loginform'); 
 
print "<div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_login' type='email' placeholder='".__( 'Email', 'doliconnect')."' name='log' value='' required autofocus>";
print "</div></div><div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='user_pass' type='password' placeholder='".__( 'Password', 'doliconnect')."' name='pwd' value ='' required>";
print "</div></div>";

do_action( 'login_form');

print "</li><li class='list-group-item'><div><small><div class='float-left'>";
if ((!is_multisite() && get_option( 'users_can_register' )) || ((!is_multisite() && get_option( 'dolicustsupp_can_register' )) || ((get_option( 'dolicustsupp_can_register' ) || get_option('users_can_register') == '1') && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
print "<a href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect-pro')."'>".__( 'Create an account', 'doliconnect')."</a>";
}

print "<div class='form-group'><div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input' value='forever' id='remembermelogin' name='rememberme'>";
print "<label class='custom-control-label' for='remembermelogin'> ".__( 'Remember me', 'doliconnect')."</label></div></div>";

print "</div><div class='float-right'><a href='".wp_lostpassword_url( get_permalink() )."' role='button' title='".__( 'Forgot password?', 'doliconnect')."'>".__( 'Forgot password?', 'doliconnect')."</a></div></small></div>"; 
print "</li></lu><div class='card-body'>";

print "<input type='hidden' value='$redirect_to' name='redirect_to'><button id='submit' class='btn btn-block btn-primary' type='submit' name='submit' value='Submit'";
print "><b>".__( 'Sign in', 'doliconnect')."</b></button></form>";

do_action( 'login_footer');

}

print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";

print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div></form>";

}

}

} else {

return $content;

}

}

add_filter( 'the_content', 'doliaccount_display', 10, 2);

// ********************************************************

function dolicontact_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicontact')) && !empty(doliconnectid('dolicontact')) ) {

doliconnect_enqueues();

if( ! empty($_POST['email-control']) )   //! $is_valid  || ! 
{
$emailError = __( 'Your request is unsuccessful', 'doliconnect');
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
        $body = "Nom: $name <br>Email: $email <br>Message: $comments";
        $headers = array("Content-Type: text/html; charset=UTF-8","From: ".$name." <".$email.">","Cc: ".$name." <".$email.">"); 
        wp_mail($emailTo, $subject, $body, $headers);
        $emailSent = true;
    }

}

print "<div class='row'><div class='col-md-4'><div class='form-group'><h4>".__( 'Address', 'doliconnect')."</h4>";
$company = callDoliApi("GET", "/setup/company", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print  $company->name.'<br>';
print  $company->address.'<br>';
print  $company->zip.' '.$company->town.'<br>';
print  $company->country;
print "</div></div><div class='col-md-8'><div id='content'>";
if ( isset($emailSent) && $emailSent == true ) {
print dolialert('success', __( 'Your message is successful send!', 'doliconnect')); 
} elseif ( isset($hasError) || isset($captchaError) ) { 
print dolialert('success', __( 'Please try again!', 'doliconnect')); 
}

print "<form action='' id='doliconnect-contactform' method='post' class='was-validated'>";

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
$type = callDoliApi("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $type;

if ( isset($type) ) { 
print "<select class='custom-select' id='ticket_type'  name='ticket_type'>";
if ( count($type) > 1 ) {
print "<option value='' disabled selected >".__( '- Select -', 'doliconnect')."</option>";
}
foreach ($type as $postv) {
print "<option value='".$postv->code."' ";
if ( isset($_GET['type']) && $_GET['type'] == $postv->code ) {
print "selected ";
} elseif ( $postv->use_default == 1 ) {
print "selected ";}
print ">".$postv->label."</option>";
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
print "<div class='card-body'><button class='btn btn-primary btn-block' type='submit'>".__( 'Send', 'doliconnect')."</button><input type='hidden' name='submitted' id='submitted' value='true' /></div></div></div></div></form>";

print "</div>";

} else {

return $content;

}

}

add_filter( 'the_content', 'dolicontact_display');

// ********************************************************

function dolisupplier_display($content) {

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolisupplier')) && !empty(doliconnectid('dolisupplier')) ) {

doliconnect_enqueues();

$shopsupplier = doliconst("DOLICONNECT_CATSHOP_SUPPLIER", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$category = "";

if ( isset($_GET['supplier']) && $_GET['supplier'] > 0 ) { 
 
$request = "/thirdparties/".esc_attr($_GET['supplier']);
$module = 'thirdparty';
$thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $thirdparty;
}

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ( !isset($thirdparty->error) && isset($_GET['supplier']) && isset($thirdparty->id) && ($_GET['supplier'] == $thirdparty->id) && $thirdparty->status == 1 && $thirdparty->fournisseur == 1 ) {

print "<li class='list-group-item'>";

print "<div class='row'><div class='col-4 col-md-2'><center>";
print doliconnect_image('thirdparty', $thirdparty->id.'/logos/'.$thirdparty->logo, array('entity'=> $thirdparty->entity), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</center></div><div class='col-8 col-md-10'>".(!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name);
if ( !empty($thirdparty->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$thirdparty->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print "<br><span class='flag-icon flag-icon-".strtolower($thirdparty->country_code)."'></span> ".$country->label.""; }

print "</div></div>";

print "<p class='text-justify'>".$thirdparty->note_private."</p>";

$photos = callDoliApi("GET", "/documents?modulepart=thirdparty&id=".$thirdparty->id, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if (!empty(doliconnect_categories('supplier', $thirdparty))) print doliconnect_categories('supplier', $thirdparty)."<br><br>";

print doliconnect_image('thirdparty', $thirdparty->id, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $thirdparty->entity);

print "</li>"; 

$module = 'product';
$limit=20;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = "0"; }
$request = "/products/purchase_prices?sortfield=t.ref&sortorder=ASC&limit=".$limit."&page=".$page."&supplier=".esc_attr($_GET["supplier"])."&sqlfilters=(t.tosell%3A%3D%3A1)";
$resultats2 = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$resultats = array();
if ( !isset($resultats2->error) && $resultats2 != null ) {
foreach ($resultats2 as $product) {
$resultats[$product[0]->id] = 1;
print apply_filters( 'doliproductlist', $product[0]);

}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No product / service currently on sale', 'doliconnect')."</center></li>";
}

} else {

 if (!empty($shopsupplier)) $category = "&category=".$shopsupplier;
$module = 'thirdparty';
$limit=20;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$request = "/thirdparties?sortfield=t.nom&sortorder=ASC&limit=".$limit."&page=".$page."&mode=4".$category."&sqlfilters=(t.status%3A%3D%3A'1')";
$resultats = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultats->error) && $resultats != null ) {
foreach ($resultats as $supplier) {

print "<a href='".esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) )."' class='list-group-item list-group-item-action'>".(!empty($supplier->name_alias)?$supplier->name_alias:$supplier->name)."</a>";

}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No supplier', 'doliconnect')."</center></li>";
}

} 

print "</ul><div class='card-body'>";
print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay($module));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} else {

return $content;

}

}

add_filter( 'the_content', 'dolisupplier_display');

// ********************************************************

function dolishop_display($content) {

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolishop')) && !empty(doliconnectid('dolishop')) ) {

doliconnect_enqueues();

$shop = doliconst("DOLICONNECT_CATSHOP", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
//print $shop;

print "<div class='card shadow-sm'>";

if ( defined("DOLIBUG")) {
$request = null;
print "<ul class='list-group list-group-flush'><li class='list-group-item list-group-item-white'>";
print dolibug($shop);
print "</li>";
print "</ul>";
} else {
if ( isset($_GET['search']) ) {

print "<ul class='list-group list-group-flush'>";


if (empty($_GET['search'])) {

print "<div class='card-body'>";

print '<form role="search" method="get" id="shopform" action="' . doliconnecturl('dolishop') . '" ><div class="input-group"><input type="text" class="form-control" name="search" id="search" placeholder="' . esc_attr__('Name, Ref. or barcode', 'doliconnect') . '" aria-label="Search for..." aria-describedby="search-widget">
<div class="input-group-append"><button class="btn btn-primary" type="submit" id="searchproduct" ><i class="fas fa-search"></i></button></div>
</div></form>';

print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} else {
$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$request = "/products?sortfield=t.label&sortorder=ASC&limit=".$limit."&page=".$page."&sqlfilters=((t.label%3Alike%3A'%25".esc_attr($_GET['search'])."%25')%20OR%20(t.ref%3Alike%3A'%25".esc_attr($_GET['search'])."%25')%20OR%20(t.barcode%3Alike%3A'%25".esc_attr($_GET['search'])."%25'))%20AND%20(t.tosell%3A%3D%3A1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
print "<li class='list-group-item list-group-item-light'><center>";
printf( _n( 'We have found %s product with this search', 'We have found %s products with this search', $count, 'doliconnect' ), number_format_i18n( $count ) );
print " '".esc_attr($_GET['search'])."'";
print "<a href='".esc_url( add_query_arg( 'search', '', doliconnecturl('dolishop')) )."' class='btn btn-link btn-block'>".__(  'New search', 'doliconnect')."</a>";
print "</center></li>";
foreach ($resultats as $product) {

print apply_filters( 'doliproductlist', $product);
 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".sprintf( esc_html__( 'No product with this search: "%s"', 'doliconnect'), esc_attr($_GET['search']))."</center></li>";
}
print "</ul><div class='card-body'>";
print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], 0, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";
}

} elseif ( isset($_GET['new']) ) {

print "<ul class='list-group list-group-flush'>";

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$request = "/products?sortfield=t.datec&sortorder=DESC&limit=".$limit."&page=".$page."&sqlfilters=(t.tosell%3A%3D%3A1)"; //%20AND%20
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
print "<li class='list-group-item list-group-item-light'><center>".__(  'Here are our new products', 'doliconnect')."</center></li>";
foreach ($resultats as $product) {

print apply_filters( 'doliproductlist', $product);
 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__(  'No new product', 'doliconnect')."</center></li>";
}
print "</ul><div class='card-body'>";
print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} elseif ( isset($_GET['product']) ) {

$request = "/products/".esc_attr($_GET['product'])."?includestockdata=1";
$product = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

print "<div class='card-body'>";
print apply_filters( 'doliproductcard', $product, null);
print "</div>";

print "</ul><div class='card-body'>";

print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} elseif ( !isset($_GET['category']) ) {
print "<ul class='list-group list-group-flush'>";
if ( $shop != null ) {

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$request = "/categories/".$shop."?include_childs=true";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultats->error) && $resultats != null ) {
foreach ($resultats->childs as $categorie) {

$requestp = "/products?sortfield=t.label&sortorder=ASC&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}

print "<a href='".esc_url( add_query_arg( 'category', $categorie->id, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action'>".doliproduct($categorie, 'label')." (".$count.")</a>"; //."<br>".doliproduct($categorie, 'description')

}}
}

$catoption = doliconst("ADHERENT_MEMBER_CATEGORY", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( !empty($catoption) && is_user_logged_in() ) {
print "<a href='".esc_url( add_query_arg( 'category', $catoption, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action' >Produits/Services lies a l'adhesion</a>";
}

print "</ul><div class='card-body'>";
print dolipage($resultats->childs, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} else {

print "<ul class='list-group list-group-flush'>";
$category = callDoliApi("GET", "/categories/".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"])."?include_childs=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print "<li class='list-group-item'>";

print "<div class='row'><div class='col-4 col-md-2'><center>";
print doliconnect_image('category', $category->id, 1, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $category->entity);
print "</center></div><div class='col-8 col-md-10'>".doliproduct($category, 'label')."<br><small>".doliproduct($category, 'description');
print "</small></div></div></li>"; 

$request = "/categories/".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"])."?include_childs=true";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultats->error) && $resultats != null ) {
foreach ($resultats->childs as $categorie) {

$requestp = "/products?sortfield=t.label&sortorder=ASC&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}

print "<a href='".esc_url( add_query_arg( array( 'category' => $_GET['category'], 'subcategory' => $categorie->id), doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action'>".doliproduct($categorie, 'label')." (".$count.")</a>";

}}

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$request = "/products?sortfield=t.label&sortorder=ASC&limit=".$limit."&page=".$page."&category=".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"])."&sqlfilters=(t.tosell=1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultats->error) && $resultats != null ) {
foreach ($resultats as $product) {

print apply_filters( 'doliproductlist', $product);
 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No product / service currently on sale', 'doliconnect')."</center></li>";
}

print "</ul><div class='card-body'>";
print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

}
}

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

$art200 = callDoliApi("GET", "/doliconnector/constante/DONATION_ART200", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art238 = callDoliApi("GET", "/doliconnector/constante/DONATION_ART238", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art835 = callDoliApi("GET", "/doliconnector/constante/DONATION_ART835", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $shop;

if ( defined("DOLIBUG") ) {

print dolibug();

} elseif ( empty(doliconst('MAIN_MODULE_COMMANDE')) ) {
print "<div class='card shadow-sm'><div class='card-body'>";
print dolibug(__( 'Inactive module on Dolibarr', 'doliconnect'));
print "</div></div>";
} elseif (is_user_logged_in())  {

if ( doliconnector($current_user, 'fk_soc') > '0') {
$request = "/thirdparties/".doliconnector($current_user, 'fk_soc');
$thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
}

print "<form action='".doliconnecturl('dolidonation')."' id='doliconnect-donationform' method='post' class='was-validated' enctype='multipart/form-data'>";

print doliloaderscript('doliconnect-donationform');

print "<div class='card shadow-sm'>";

if (isset($_GET["create"])) {
print doliuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'donation');

print "<div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect')."</b></button></div>";

} else {
print "<div class='card-body'>"; 

print "<h5><i class='fas fa-donate fa-fw'></i> Don hors ligne</h5>";

//if ( $object->mode_reglement_code == 'CHQ') {

$chq = callDoliApi("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay('constante'));

$bank = callDoliApi("GET", "/bankaccounts/".$chq->value, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), 'votre choix', $bank->proprio, $object->ref ).":</p><p><b>$bank->owner_address</b></p></div>";

//} 
//if ($object->mode_reglement_code == 'VIR') {

$vir = callDoliApi("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay('constante'));

$bank = callDoliApi("GET", "/bankaccounts/".$vir->value, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), 'votre choix', $object->ref ).":";
print "<br><b>".__( 'Bank', 'doliconnect').": $bank->bank</b>";
print "<br><b>IBAN: $bank->iban</b>";
if ( ! empty($bank->bic) ) { print "<br><b>BIC/SWIFT: $bank->bic</b>";}
print "</p></div>";

//}
print "<h5><i class='fas fa-donate fa-fw'></i> ".__( 'Tax exemptions', 'doliconnect')."</h5>";
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
if ( isset($request) ) print dolirefresh($request, doliconnecturl('dolidonation'), dolidelay('constante'));
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
 
function dolicart_display($content) {
global $wpdb, $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicart')) && !empty(doliconnectid('dolicart')) )  {

doliconnect_enqueues();

$time = current_time( 'timestamp', 1);

if ( isset($_GET['module']) && ($_GET['module'] == 'orders' || $_GET['module'] == 'invoices') && isset($_GET['id']) && isset($_GET['ref']) ) {
$request = "/".esc_attr($_GET['module'])."/".esc_attr($_GET['id'])."?contact_list=0";
$module = esc_attr($_GET['module']);
$id = $_GET['id']; 
} elseif (doliconnector($current_user, 'fk_order') > 0) {
$request = "/orders/".doliconnector($current_user, 'fk_order')."?contact_list=0";
$module = 'orders';
$id = doliconnector($current_user, 'fk_order'); 
} else {
$request = "/orders/-1";
$module = 'orders';
$id = null;
}

$object = callDoliApi("GET", $request, null, dolidelay('cart', true));

if ( defined("DOLIBUG") ) {

print dolibug((isset($object->error)?$object->error->message:null));

} elseif ( empty(doliconst('MAIN_MODULE_COMMANDE', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))) ) {

print "<div class='card shadow-sm'><div class='card-body'>";
print dolibug(__( "Oops, Order's module is not available", "doliconnect"));
print "</div></div>";

} else {

if ( isset($_GET['step']) && $_GET['step'] == 'validation' && isset($_GET['cart']) && wp_verify_nonce( $_GET['cart'], 'valid_dolicart-'.$object->id) && ((doliconnector($current_user, 'fk_order_nb_item') > 0 && $object->statut == 0 && !isset($_GET['module']) ) || ( ($_GET['module'] == 'orders' && $object->billed != 1 ) || ($_GET['module'] == 'invoices' && $object->paye != 1) )) && $object->socid == doliconnector($current_user, 'fk_soc') ) {

$data = [
  'paymentintent' => isset($_POST['paymentintent']) ? $_POST['paymentintent'] : null,
  'paymentmethod' => isset($_POST['paymentmethod']) ? $_POST['paymentmethod'] : null,
  'save' => isset($_POST['default']) ? $_POST['default'] : 0 ,
	];
$payinfo = callDoliApi("POST", "/doliconnector/pay/".$module."/".$object->id, $data, 0);
//print var_dump($payinfo);
  
doliconnector($current_user, 'fk_order', true);
$object = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, dolidelay('cart', true));

print "<div class='card shadow-sm' id='cart-form'><div class='card-body'><center><h2>".__( 'Your order has been registered', 'doliconnect')."</h2>".__( 'Reference', 'doliconnect').": ".$object->ref."<br>".__( 'Payment method', 'doliconnect').": ".$object->mode_reglement_code."<br><br>";
$TTC = doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null);

if ( $object->statut == '1' && !isset($_GET['error']) ) {
if (!empty($object->billed) || !empty($object->paid)) {

print "<div class='alert alert-success' role='alert'><p>".__( 'Your payment has been registered', 'doliconnect');
if (isset($_GET['charge'])) "<br>".__( 'Reference', 'doliconnect').": ".$_GET['charge'];
print "</p>";

} elseif ( $object->mode_reglement_code == 'CHQ') {

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), $TTC, $object->ref, $listpaymentmethods->CHQ->proprio).":</p><p><b>".$listpaymentmethods->CHQ->owner_address."</b></p>";

} elseif ($object->mode_reglement_code == 'VIR') {

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), $TTC, $object->ref ).":";
print "<br><b>".__( 'Bank', 'doliconnect').": ".$listpaymentmethods->VIR->bank."</b>";
print "<br><b>IBAN: ".$listpaymentmethods->VIR->iban."</b>";
if ( ! empty($listpaymentmethods->VIR->bic) ) { print "<br><b>BIC/SWIFT : ".$listpaymentmethods->VIR->bic."</b>";}
print "</p>";

}

if ( (! empty(dolikiosk()) && empty($object->billed) && empty($object->paid) ) || $object->mode_reglement_code == 'LIQ') {
print "<br><p><b>".__( 'or go to reception desk', 'doliconnect')."</b></p>";
}


} else {

}

print "</div></div></div>";

} elseif ( isset($_GET['step']) && $_GET['step'] == 'info' && isset($_GET['cart']) && wp_verify_nonce( $_GET['cart'], 'valid_dolicart-'.$object->id) && isset($_POST['dolichecknonce']) && $_GET['cart'] == $_POST['dolichecknonce'] && doliconnector($current_user, 'fk_order_nb_item') > 0 && $object->socid == doliconnector($current_user, 'fk_soc') && !isset($object->resteapayer) && $object->statut == 0 && !isset($_GET['module']) && !isset($_GET['id']) ) {

if ( isset($_POST['update_thirdparty']) && $_POST['update_thirdparty'] == 'validation' ) {

$thirdparty=$_POST['contact'][''.doliconnector($current_user, 'fk_soc').''];
$ID = $current_user->ID;
if ( isset($thirdparty['morphy']) && $thirdparty['morphy'] == 'phy' ) {
$thirdparty['name'] = ucfirst(strtolower($thirdparty['firstname']))." ".strtoupper($thirdparty['lastname']);
} 
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($thirdparty['email'])));
if (isset($_POST['user_nicename'])) wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
if (isset($_POST['name'])) wp_update_user( array( 'ID' => $ID, 'display_name' => sanitize_user($thirdparty['name'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($thirdparty['firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($thirdparty['lastname']))));
if (isset($_POST['description'])) wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
if (isset($_POST['url'])) wp_update_user( array( 'ID' => $ID, 'user_url' => sanitize_textarea_field($thirdparty['url'])));
update_user_meta( $ID, 'civility_id', sanitize_text_field($thirdparty['civility_id']));
if (isset($_POST['morphy'])) update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
if ( isset($_POST['morphy']) && $thirdparty['morphy'] == 'mor' ) { update_user_meta( $ID, 'billing_company', sanitize_text_field($thirdparty['name'])); }
update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);

do_action('wp_dolibarr_sync', $thirdparty);
                                   
} elseif ( isset($_POST['info']) && $_POST['info'] == 'validation' ) {


                                   
} elseif ( !$object->id > 0 && $object->lines == null ) {

wp_redirect(doliconnecturl('dolicart'));
exit;

}

} else {

if ( isset($_GET['step']) || isset($_GET['cart']) || isset($_GET['id']) || isset($_GET['module']) ) {
wp_safe_redirect(doliconnecturl('dolicart'));
exit;
} 

print "<ul class='nav bg-white nav-pills rounded nav-justified flex-column flex-sm-row' role='tablist'>";

print '<li id="li-tab-cart" class="nav-item"><a id="a-tab-cart" class="nav-link active" data-toggle="pill" href="#nav-tab-cart">
<i class="fas fa-shopping-bag fa-fw"></i> '.__( 'Cart', 'doliconnect').'</a></li>';

print '<li id="li-tab-info" class="nav-item"><a id="a-tab-info" class="nav-link disabled" data-toggle="pill" href="#nav-tab-info">
<i class="fas fa-user-check fa-fw"></i> '.__( 'Coordinates', 'doliconnect').'</a></li>';

print '<li id="li-tab-pay" class="nav-item"><a id="a-tab-pay" class="nav-link disabled" data-toggle="pill" href="#nav-tab-pay">
<i class="fas fa-money-bill-wave fa-fw"></i> '.__( 'Payment', 'doliconnect').'</a></li>';
 
print "</ul><br><div id='tab-cart-content' class='tab-content'>";

print "<div class='tab-pane fade show active' id='nav-tab-cart'>";
 
if ( isset($object) && is_object($object) && isset($object->date_modification)) {
$timeout=$object->date_modification-current_time('timestamp',1)+1200;
//print "<script>";
//var tmp=<?php print ($timeout)*10;
// 
//var chrono=setInterval(function (){
//     min=Math.floor(tmp/600);
//     sec=Math.floor((tmp-min*600)/10);
//     dse=tmp-((min*60)+sec)*10;
//     tmp--;
//     jQuery('#duration').text(min+'mn '+sec+'sec');
//},100);
//print "</script>";
//header('Refresh: 120; URL='.esc_url(get_permalink()).'');
//header('Refresh: '.$timeout.'; URL='.esc_url(get_permalink()).'');
//print wp_date('d/m/Y H:i', $object[date_modification]);
}

if ( doliconnector($current_user, 'fk_order')>0 && isset($object->lines) && $object->lines != null ) {  //&& $timeout>'0'                                                                                         
//print "<div id='timer' class='text-center'><small>".sprintf( esc_html__('Your basket #%s is reserved for', 'doliconnect'), doliconnector($current_user, 'fk_order'))." <span class='duration'></span></small></div>";
}

print "<div class='card shadow-sm' id='cart-form'><ul id='doliline' class='list-group list-group-flush'>";

print doliline($object, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( isset($object) && is_object($object) && isset($object->socid) &&(doliconnector($current_user, 'fk_soc') == $object->socid) ) {
print "</ul><ul id='dolitotal' class='list-group list-group-flush'>";
print dolitotal($object);  
}

if (doliconnector($current_user, 'fk_soc')>0) {
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
$outstandingamount = 0;
if ($thirdparty->outstanding_limit) {
$outstandinginvoice = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc')."/outstandinginvoices?mode=customer", null, dolidelay('cart', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
print "<li class='list-group-item bg-light'><b>".__( 'Amount outstanding', 'doliconnect').": ".doliprice($outstandinginvoice->opened, null, null)." ".__( 'out of', 'doliconnect')." ".doliprice($thirdparty->outstanding_limit, null, null)." ".__( 'allowed', 'doliconnect');
$outstandingamount = $outstandinginvoice->opened-$thirdparty->outstanding_limit;
if ($outstandingamount > 0) print " - ".__( "Your account is blocked, this order can't be processed. Please, contact us to pay overdue unpaid invoices.", 'doliconnect');
print "</b></li>";
}}

print "</ul>"; 
 
if ( get_option('dolishop') || (!get_option('dolishop') && isset($object) && $object->lines != null) ) {
print "<div class='card-body'><ul class='list-group list-group-horizontal-sm'>";
if ( get_option('dolishop') ) {
print "<a href='".doliconnecturl('dolishop')."' class='list-group-item list-group-item-action flex-fill'><center><b>".__( 'Continue shopping', 'doliconnect')."</b></center></a>";
} 
if ( isset($object) && is_object($object) && isset($object->lines) && $object->lines != null && (doliconnector($current_user, 'fk_soc') == $object->socid) ) { 
if ( $object->lines != null && $object->statut == 0 ) {
print "<button button type='button' id='purgebtn_cart' name='purge_cart' value='purge_cart' class='list-group-item list-group-item-action flex-fill'><center><b>".__( 'Empty the basket', 'doliconnect')."</b></center></button>";
}
if ( $object->lines != null ) {
print "<button type='button' id='validatebtn_cart' name='validate_cart' value='validate_cart' class='list-group-item list-group-item-action list-group-item-warning flex-fill ' ";
if ($outstandingamount > 0 || (defined('dolilockcart') && !empty(constant('dolilockcart')))) print " disabled";
print "><center><b>".__( 'Process', 'doliconnect')."</b></center></button>";
}
}
print "</ul></div>";
}

print "<script>";
print "(function ($) {
$(document).ready(function(){
$('#purgebtn_cart, #validatebtn_cart').on('click',function(event){
event.preventDefault();
//$('#DoliconnectLoadingModal').modal('show');
var actionvalue = $(this).val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': actionvalue,
            'module': '".$module."',
            'id': '".$id."'
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'purge_cart')  {
document.getElementById('doliline').innerHTML = response.data.lines;
document.getElementById('dolitotal').remove();
document.getElementById('purgebtn_cart').remove();
document.getElementById('validatebtn_cart').remove();
$('#a-tab-info').addClass('disabled');
if (document.getElementById('DoliHeaderCarItems')) {
document.getElementById('DoliHeaderCarItems').innerHTML = response.data.items;
}
if (document.getElementById('DoliFooterCarItems')) {  
document.getElementById('DoliFooterCarItems').innerHTML = response.data.items;
}
if (document.getElementById('DoliWidgetCarItems')) {
document.getElementById('DoliWidgetCarItems').innerHTML = response.data.items;
} 

} else if (actionvalue == 'validate_cart') {
$('#a-tab-cart').removeClass('active');
$('#a-tab-info').removeClass('disabled');
$('#a-tab-info').addClass('active');    
$('#nav-tab-cart').removeClass('show active');
$('#nav-tab-info').addClass('show active');
$('#nav-tab-cart').tab('dispose');
$('#nav-tab-info').tab('show');   
}

console.log(response.data.message);
//$('#DoliconnectLoadingModal').modal('hide');
} 
        });
});
});
})(jQuery);";
print "</script>";

print '<div class="card-footer text-muted">';
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, doliconnecturl('dolicart'), dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

print "</div>";

if ( is_user_logged_in() ) { 
print "<div class='tab-pane fade' id='nav-tab-info'>";
  
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 

print "<div class='card'><ul class='list-group list-group-flush'>";

print "<li class='list-group-item'><h6>".__( 'Customer', 'doliconnect')."</h6><small class='text-muted'>";

print doliaddress($thirdparty);

print "</small></li>";

if ( doliversion('10.0.0') ) {

print "<li class='list-group-item'><div class='row'><div class='col-12 col-md-6'><h6>".__( 'Billing address', 'doliconnect')."</h6><small class='text-muted'>";

print '<div class="custom-control custom-radio">
<input type="radio" id="billing-0" name="contact_billing" class="custom-control-input" value="0" checked disabled>
<label class="custom-control-label" for="billing-0">'.__( "Same address as the customer", "doliconnect").'</label>
</div>';

$listcontact = callDoliApi("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&includecount=1&sqlfilters=t.statut=1", null, dolidelay('contact', true));

if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {
$contactshipping = null;
foreach ($object->contacts_ids as $contact) {
if ('BILLING' == $contact->code) {
$contactshipping = $contact->id;
}
}
}

if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) {
print '<div class="custom-control custom-radio"><input type="radio" id="billing-'.$contact->id.'" name="contact_billing" class="custom-control-input" value="'.$contact->id.'" ';
if ( (isset($contact->default) && !empty($contact->default)) || $contactshipping == $contact->id ) { print "checked"; }
print ' disabled><label class="custom-control-label" for="billing-'.$contact->id.'">';
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print '</label></div>';
}
}
print "</small></div>";

print "<div class='col-12 col-md-6'><h6>".__( 'Shipping address', 'doliconnect')."</h6><small class='text-muted'>";

print '<div class="custom-control custom-radio">
<input type="radio" id="shipping-0" name="contact_shipping" class="custom-control-input" value="0" checked disabled>
<label class="custom-control-label" for="shipping-0">'.__( "Same address as the customer", "doliconnect").'</label>
</div>';

$listcontact = callDoliApi("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&includecount=1&sqlfilters=t.statut=1", null, dolidelay('contact', true));

if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {
$contactshipping = null;
foreach ($object->contacts_ids as $contact) {
if ('SHIPPING' == $contact->code) {
$contactshipping = $contact->id;
}
}
}

if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) {
print '<div class="custom-control custom-radio"><input type="radio" id="shipping-'.$contact->id.'" name="contact_shipping" class="custom-control-input" value="'.$contact->id.'" ';
if ( (isset($contact->default) && !empty($contact->default)) || $contactshipping == $contact->id ) { print "checked"; }
print ' disabled><label class="custom-control-label" for="shipping-'.$contact->id.'">';
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print '</label></div>';
}
}
print "</small></div></div></li>";

} elseif ( current_user_can( 'administrator' ) ) {
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".sprintf( esc_html__( "Adding billing or shipping contacts requires Dolibarr %s but your version is %s", 'doliconnect'), '10.0.0', doliversion('10.0.0'))."</b></li>";
}

print "<li class='list-group-item'><h6>".__( 'Message', 'doliconnect')."</h6>";
print "<textarea class='form-control' id='note_public' name='note_public' rows='3' placeholder='".__( 'If you want to send us a message about your order, you can leave one here', 'doliconnect')."'>".$object->note_public."</textarea>";
print "</li></ul>";

$note_public = isset($_POST['note_public']) ? $_POST['note_public'] : '';

print "<script>";
print "(function ($) {
$(document).ready(function(){
$('#infobtn_cart').on('click',function(event){
event.preventDefault();
//$('#DoliconnectLoadingModal').modal('show');
var actionvalue = $(this).val();
var note_public = $('#note_public').val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': actionvalue,
            'module': '".$module."',
            'id': '".$id."',
            'note_public': note_public
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'info_cart') {
$('#a-tab-info').removeClass('active');
$('#a-tab-pay').removeClass('disabled');
$('#a-tab-pay').addClass('active');    
$('#nav-tab-info').removeClass('show active');
$('#nav-tab-info').tab('dispose');
if (document.getElementById('nav-tab-pay')) {
//document.getElementById('nav-tab-pay').remove();    
}
//var new_tab = $('<div>').addClass( 'tab-pane fade show active').attr('id', 'nav-tab-pay').append( response.data.content );
//$('#tab-cart-content').append( new_tab );
$('#nav-tab-pay').tab('show');                                                                             
}

console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
print "</script>";

print "<div class='card-body'><button type='button' id='infobtn_cart' name='info_cart' value='info_cart'  class='btn btn-light btn-outline-secondary btn-block'>".__( 'Validate', 'doliconnect')."</button></div>";
print "<div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

print "</div>";

print "<div class='tab-pane fade' id='nav-tab-pay'>";

if ( doliversion('11.0.0') ) {
print doliconnect_paymentmethods($object, esc_attr($module), null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
} else {
print __( "It seems that your version of Dolibarr and/or its plugins are not up to date!", "doliconnect");
}

print "</div>";
}

print "</div>";
}
}

} else {

return $content;

}

}

add_filter( 'the_content', 'dolicart_display');
?>
