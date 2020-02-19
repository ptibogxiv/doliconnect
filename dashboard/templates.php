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

print "<div class='row'><div class='col-xs-12 col-sm-12 col-md-3'><div class='row'><div class='col-3 col-xs-4 col-sm-4 col-md-12 col-xl-12'><div class='card shadow-sm' style='width: 100%'>";
print get_avatar($ID);

if ( is_user_logged_in() && !defined("DOLIBUG") ) {
print "<a href='".esc_url( add_query_arg( 'module', 'avatars', doliconnecturl('doliaccount')) )."' class='card-img-overlay'><div class='d-block d-sm-block d-xs-block d-md-none'></div><div class='d-none d-md-block'><i class='fas fa-camera fa-2x'></i> ".__( 'Edit', 'doliconnect')."</div></a>";
}
print "<ul class='list-group list-group-flush'><a href='".esc_url( doliconnecturl('doliaccount') )."' class='list-group-item list-group-item-light list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-home'></i></div><div class='d-none d-md-block'><i class='fas fa-home'></i> ".__( 'Home', 'doliconnect')."</div></center></a>";
print "</ul>";

print "</div><br></div><div class='col-9 col-xs-8 col-sm-8 col-md-12 col-xl-12'>";

if ( is_user_logged_in() ) {

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
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
print "<div class='card shadow-sm'><div class='card-body'>";
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

$role = 'subscriber';

if ( is_multisite() ) {
$entity = dolibarr_entity(); 
add_user_to_blog($entity,$ID,$role);
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
if (isset($_POST["case"]) && $_POST["case"] == 'updatepwd'){
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
$arr_params = array( 'action' => 'lostpassword', 'success' => true);  
wp_redirect(esc_url( add_query_arg( $arr_params, wp_login_url( get_permalink() )) ));
exit;
}
elseif ( $pwd != $_POST["pwd2"] ) {
print dolialert('warning', __( 'The new passwords entered are different', 'doliconnect'));
}
elseif (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd)){
print dolialert('danger', __( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect'));
}
}

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ($dolibarr->fk_user > '0'){  
$request = "/users/".$dolibarr->fk_user;
$doliuser = callDoliApi("GET", $request , null, dolidelay('thirdparty'));
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect')."</b></li>";
} 
print "<li class='list-group-item'><h5 class='card-title'>".__( 'Change your password', 'doliconnect')."</h5>
<form class='was-validated' id='doliconnect-rpwform' action='' method='post'><input type='hidden' name='submitted' id='submitted' value='true' />";

print doliloaderscript('doliconnect-rpwform'); 

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
  
if( isset($_POST['user_email']) ) {

    if( sanitize_email($_POST['user_email']) === '' )  {
        $emailError = __( 'A valid email is need to reset your password', 'doliconnect');
        $hasError = true;
    } elseif ( !email_exists(sanitize_email($_POST['user_email'])) ) {
        $emailError = __( 'Reset password is not permitted', 'doliconnect');
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

$arr_params = array( 'action' => 'rpw', 'key' => $key, 'login' => $user->user_login);  
$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
      $emailError = __( 'Reset password is not permitted', 'doliconnect');
      $emailSent = false;	
      
 } elseif ( !empty($key) ) { 
			$sitename = get_option('blogname');
      $siteurl = get_option('siteurl');
      $subject = "[$sitename] ".__( 'Reset Password', 'doliconnect');
      $body = __( 'A request to change your password has been made. You can change it via the single-use link below:', 'doliconnect')."<br><br><a href='".$url."'>".$url."</a><br><br>".__( 'If you have not made this request, please ignore this email.', 'doliconnect')."<br><br>".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br>$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($email, $subject, $body, $headers);

				if( $mail ) { $emailSent = true; } else { $emailSent = false; }		
}
       
}
}

if ( isset($emailSent) && $emailSent == true ) { 
print dolialert('success', __( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect'));
} elseif ( isset($hasError) || isset($emailError) ) { 
print dolialert('danger', $emailError);
} elseif ( isset($emailSent) && $emailSent != true ) {
print dolialert('warning', __( 'A problem occurred. Please retry later!', 'doliconnect'));
}

print "<form id='doliconnect-fpwform' action='".doliconnecturl('doliaccount')."?action=fpw' method='post' class='was-validated'><input type='hidden' name='submitted' id='submitted' value='true' />";

print doliloaderscript('doliconnect-fpwform'); 
 
print "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".__( 'Forgot password?', 'doliconnect')."</h5></div>";
print "<ul class='list-group list-group-flush'><li class='list-group-item'>";
print "<div class='form-group'><label for='inputemail'><small>".__( 'Please enter the email address by which you registered your account.', 'doliconnect')."</small></label>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_email' type='email' placeholder='".__( 'Email', 'doliconnect')."' name='user_email' value ='' required>";
print "</div></div>";

print "</li></lu><div class='card-body'>";
print "<button class='btn btn-danger btn-block' type='submit'><b>".__( 'Submit', 'doliconnect')."</b></button>";
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-left'>";

print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div></form>";

} else {

if ( isset($_GET["login"]) && $_GET["login"] == 'failed' ) { 
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

print "<div class='card shadow-sm'>";

if ( function_exists('doliconnect_modal') && get_option('doliloginmodal') == '1' ) {

print "<ul class='list-group list-group-flush'><li class='list-group-item'><center><i class='fas fa-user-lock fa-fw fa-10x'></i><br><br>";
//print "<h2>".__( 'Restricted area', 'doliconnect')."</h2></center>";
print "</li></lu><div class='card-body'>";
print '<a href="#" id="login-'.current_time('timestamp').'" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'doliconnect').'" class="btn btn-block btn-primary my-2 my-sm-0" role="button">'.__('You have already an account', 'doliconnect').'</a>';
if (((!is_multisite() && get_option( 'users_can_register' )) or (get_option('users_can_register')=='1' && (get_site_option( 'registration' ) == 'user' or get_site_option( 'registration' ) == 'all')))) 
{
print '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect').'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
print '<a href="'.wp_registration_url( get_permalink() ).'" id="login-'.current_time('timestamp').'" class="btn btn-block btn-primary my-2 my-sm-0" role="button">'.__("You don't have an account", 'doliconnect').'</a>';
}

} else {

do_action( 'login_head');

print "<div class='card-body'><h5 class='card-title'>".__( 'Welcome', 'doliconnect')."</h5>";
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
if ( ((!is_multisite() && get_option( 'users_can_register' )) || (get_option('users_can_register') == '1' && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all'))) ) {
print "<a href='".wp_registration_url( get_permalink() )."' role='button' title='".__( 'Create an account', 'doliconnect')."'>".__( 'Create an account', 'doliconnect')."</a>";
}
//<input type='checkbox' class='custom-control-input' value='forever' id='remembermemodal' name='rememberme'>";
//print "<label class='custom-control-label' for='remembermemodal'> ".__( 'Remember me', 'doliconnect')."</label>";
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
print "</div></div>";
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
        $headers = array("Content-Type: text/html; charset=UTF-8'","From: $name <$email>"); 
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

if ( isset($_GET['supplier']) && $_GET['supplier'] > 0 ) { 
 
$request = "/thirdparties/".esc_attr($_GET['supplier']);

$thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $thirdparty;
}

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ( !isset($thirdparty->error) && isset($_GET['supplier']) && isset($thirdparty->id) && ($_GET['supplier'] == $thirdparty->id) && $thirdparty->status == 1 && $thirdparty->fournisseur == 1 ) {

print "<li class='list-group-item'>".$thirdparty->name."</li>"; 

$request = "/products/purchase_prices?sortfield=t.ref&sortorder=ASC&limit=100&supplier=".esc_attr($_GET["supplier"]);

$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc as $product) {

$arr_params = array( 'product' => $product->id);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );

print "<li class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div class='d-none d-md-block col-md-2 col-lg-1'><center><i class='fa fa-cube fa-fw fa-2x'></i></center></div><div class='col-12 col-sm-10 col-md-10 col-lg-11'><h6 class='my-0'>".doliproduct($product, 'label')." ".doliproductstock($product)."</h6><small class='text-muted'>".doliproduct($product, 'description')."</small></div></li>"; 

}} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No product', 'doliconnect')."</center></li>";
}

} else {

$request = "/thirdparties?sortfield=t.rowid&sortorder=ASC&limit=100&mode=4&sqlfilters=(t.status%3A%3D%3A'1')";

$resultatsc = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc as $supplier) {

print "<a href='".esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) )."' class='list-group-item list-group-item-action'>".$supplier->name."</a>";

}}

} 

print '</ul><div class="card-body">';
print '</div><div class="card-footer text-muted">';
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print '</div></div>';

} else {

return $content;

}

}

add_filter( 'the_content', 'dolisupplier_display');

// ********************************************************

function dolishop_display($content) {

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolishop')) && !empty(doliconnectid('dolishop')) ) {

doliconnect_enqueues();

$shop = callDoliApi("GET", "/doliconnector/constante/DOLICONNECT_CATSHOP", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $shop;

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ( defined("DOLIBUG")) {
$request = null;
print "<li class='list-group-item list-group-item-white'>";
print dolibug(isset($shop->error->message)?$shop->error->message:$shop);
print "</li>";

} else { 
if ( !isset($_GET['category']) ) {
if ( $shop->value != null ) {

$request = "/categories?sortfield=t.label&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent='".esc_attr($shop->value)."')";

$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc as $categorie) {

print "<a href='".esc_url( add_query_arg( 'category', $categorie->id, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action'>".doliproduct($categorie, 'label')."</a>"; //."<br>".doliproduct($categorie, 'description')

}}
}

$catoption = callDoliApi("GET", "/doliconnector/constante/ADHERENT_MEMBER_CATEGORY", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !empty($catoption->value) && is_user_logged_in() ) {
print "<a href='".esc_url( add_query_arg( 'category', $catoption->value, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action' >Produits/Services lies a l'adhesion</a>";
}

} else {

if ( isset($_GET['product']) ) {
doliaddtocart(esc_attr($_GET['product']), esc_attr($_POST['product_update'][$_GET['product']]['qty']), esc_attr($_POST['product_update'][$_GET['product']]['price']));
//print $_POST['product_update'][$_GET['product']][product];
wp_redirect( esc_url( add_query_arg( 'category', $_GET['category'], doliconnecturl('dolishop')) ) );
exit;
}

$category = callDoliApi("GET", "/categories/".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"]), null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print "<li class='list-group-item'>".doliproduct($category, 'label')."<br><small>".doliproduct($category, 'description')."</small></li>"; 

$request = "/categories?sortfield=t.label&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent='".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"])."')";

$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc as $categorie) {

$arr_params = array( 'category' => $_GET['category'], 'subcategory' => $categorie->id);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );

print "<a href='".$return."' class='list-group-item list-group-item-action'>".doliproduct($categorie, 'label')."<br><small>".doliproduct($categorie, 'description')."</small></a>"; 

}}

$request = "/products?sortfield=t.label&sortorder=ASC&category=".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"])."&sqlfilters=(t.tosell=1)";

$resultatso = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultatso->error) && $resultatso != null ) {
foreach ($resultatso as $product) {
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=".$includestock, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print "<li class='list-group-item' id='prod-li-".$product->id."'><table width='100%' style='border:0px'><tr><td width='20%' style='border:0px'><center>";
print doliconnect_image('product', $product->id, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</center></td>";

print "<td width='80%' style='border:0px'><b>".doliproduct($product, 'label')."</b>";
if ( ! empty(doliconnectid('dolicart')) ) { 
print " ".doliproductstock($product);
}
print "<div class='row'><div class='col'><p><small>".__( 'Reference', 'doliconnect').": ".$product->ref."</small>";
if ( !empty($product->barcode) ) { print "<br><small>".__( 'Barcode', 'doliconnect').": ".$product->barcode."</small>"; }
//print "<p>".doliproduct($product, 'description')."</p>";
 
$arr_params = array( 'category' => $_GET['category'], 'subcategory' => isset($_GET['subcategory'])?$_GET['subcategory']:null, 'product' => $product->id);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );
print "<a href='".$return."' class='btn btn-link btn-block'>En savoir plus</a>";
 
print "</p></div>";

if ( ! empty(doliconnectid('dolicart')) ) { 
print "<div class='col-12 col-md-6'><center>";
print doliconnect_addtocart($product, esc_attr($_GET['category']), 0, 2, 0);
print "</center></div>";
}
print "</div></td></tr></table></li>"; 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No product', 'doliconnect')."</center></li>";
}

}
}
print '</ul><div class="card-body">';
print '</div><div class="card-footer text-muted">';
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print '</div></div>';

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
$module=esc_attr($_GET['module']);
} elseif (doliconnector($current_user, 'fk_order') > 0) {
$request = "/orders/".doliconnector($current_user, 'fk_order')."?contact_list=0";
$module='orders';
} else {
$request = "/orders/-1";
$module='orders';
}

//if ( doliconnector($current_user, 'fk_order') > 0 ) {
$object = callDoliApi("GET", $request, null, dolidelay('cart', true));
//print var_dump($object);
//}

if ( defined("DOLIBUG") ) {

print dolibug($object->error->message);

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

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw ";

if ( $payinfo->status == 'succeeded' ) {
print "text-success";
}
elseif ( $payinfo->status == 'processing' ) {
print "text-warning";
}
elseif ( $payinfo->status == 'requires_confirmation' ) {
print "text-danger";
}
else {
print "text-warning";
}

print "' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>"; 

//if ( ( !isset($object->id) ) || (doliconnector($current_user, 'fk_soc') != $object->socid) ) {
//$order = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, 0);
//$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, 0);
//wp_safe_redirect(esc_url(doliconnecturl('dolicart')));
//exit;
//}

print "<div class='card shadow-sm' id='cart-form'><div class='card-body'><center><h2>".__( 'Your order has been registered', 'doliconnect')."</h2>".__( 'Reference', 'doliconnect').": ".$object->ref."<br>".__( 'Payment method', 'doliconnect').": ".$object->mode_reglement_code."<br><br>";
$TTC = doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null);

if ( $object->statut == '1' && !isset($_GET['error']) ) {
if (!empty($object->billed) || !empty($object->paid)) {

print "<div class='alert alert-success' role='alert'><p>".__( 'Your payment has been registered', 'doliconnect');
if (isset($_GET['charge'])) "<br>".__( 'Reference', 'doliconnect').": ".$_GET['charge'];
print "</p>";

} elseif ( $object->mode_reglement_code == 'CHQ') {

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), $TTC, $listpaymentmethods->CHQ->proprio, $object->ref ).":</p><p><b>".$listpaymentmethods->CHQ->owner_address."</b></p>";

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

$nonce = wp_create_nonce( 'doli-'.$module.'-'. $object->id.'-'.$object->ref);
$arr_params = array('module' => $module, 'id' => $object->id, 'ref' => $object->ref, 'security' => $nonce);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );
print "<br><a href='".$return."' class='btn btn-primary'>".__( 'View my receipt', 'doliconnect')."</a>";

} else {

$nonce = wp_create_nonce( 'doli-'.$module.'-'. $object->id.'-'.$object->ref);
$arr_params = array('module' => $module, 'id' => $object->id, 'ref' => $object->ref, 'security' => $nonce);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

print "<div class='alert alert-danger' role='alert'><p>".__( 'An error is occurred', 'doliconnect')."</p>";
print "<br><a href='".$return."' class='btn btn-primary'>".__( 'Return', 'doliconnect')."</a>";
}

print "</div></div></div>";

} elseif ( isset($_GET['step']) && $_GET['step'] == 'payment' && isset($_GET['cart']) && wp_verify_nonce( $_GET['cart'], 'valid_dolicart-'.$object->id) && ( (doliconnector($current_user, 'fk_order_nb_item') > 0 && $object->statut == 0 && !isset($_GET['module']) && !isset($_GET['id'])) || ( ($_GET['module'] == 'orders' && $object->billed != 1 ) || ($_GET['module'] == 'invoices' && $object->paye != 1) )) && $object->socid == doliconnector($current_user, 'fk_soc') ) {

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

print "<div class='row'><div class='col-12 col-md-4  d-none d-sm-none d-md-block'>";
print dolisummarycart($object);
print "<div class='card'><div class='card-header'>".__( 'Contacts', 'doliconnect')."  <small>(";
if ( !isset($object->resteapayer) && $object->statut == 0 ) { print "<a href='".doliconnecturl('dolicart')."?cart=".$_GET['cart']."&step=info' >".__( 'update', 'doliconnect')."</a>"; }
else { print __( 'unchangeable, please contact us', 'doliconnect'); }
print ")</small></div><ul class='list-group list-group-flush'>";

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {

print "<li class='list-group-item list-group-item-light'><h6>".__( "Customer's Address", "doliconnect")."</h6><small class='text-muted'>";
print doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";

foreach ($object->contacts_ids as $contact) {
if ('BILLING' == $contact->code) {
print "<li class='list-group-item list-group-item-light'><h6>".__( "Billing address", "doliconnect")."</h6><small class='text-muted'>";
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
} elseif ('SHIPPING' == $contact->code) {
print "<li class='list-group-item list-group-item-light'><h6>".__( "Shipping address", "doliconnect")."</h6><small class='text-muted'>";
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
} 
}

} else {
print "<li class='list-group-item list-group-item-light'><h6>".__( 'Billing and shipping address', 'doliconnect')."</h6><small class='text-muted'>";
print doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
}

if ( ! empty($object->note_public) ) {
print "<li class='list-group-item list-group-item-light'><h6>".__( 'Message', 'doliconnect')."</h6><small class='text-muted'>";
print $object->note_public;
print "</small></li>";
}

print "</ul></div></div><div class='col-12 col-md-8'>";

if ( doliversion('11.0.0') ) {
$nonce = wp_create_nonce( 'valid_dolicart-'. $object->id);
$arr_params = array('step' => 'validation', 'module' => $module, 'id' => $object->id, 'ref' => $object->ref, 'cart' => $nonce);  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
print dolipaymentmethods($object, substr($module, 0, -1), $return, true);
} else {
print __( "It seems that your version of Dolibarr and/or its plugins are not up to date!", "doliconnect");
}

print "</div></div>";

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

if ($_POST['contact_shipping']) {
$order_shipping= callDoliApi("POST", "/".$module."/".$object->id."/contact/".$_POST['contact_shipping']."/SHIPPING", null, dolidelay('order', true));
}

if ( isset($_POST['note_public']) && $_POST['note_public'] != $object->note_public) {
$data = [
    'note_public' => isset($_POST['AddMessage']) ? $_POST['note_public'] : '',
	];
$object = callDoliApi("PUT", "/".$module."/".$object->id, $data, dolidelay('order', true));
}

wp_safe_redirect(doliconnecturl('dolicart').'?cart='.$_GET['cart'].'&step=payment');
exit;
                                   
} elseif ( !$object->id > 0 && $object->lines == null ) {

wp_safe_redirect(doliconnecturl('dolicart'));
exit;

}
//header('Refresh: 300; URL='.esc_url(get_permalink()).'');
$ID = $current_user->ID;

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

print "<div class='row' id='informations-form'><div class='col-12 col-md-4 d-none d-sm-none d-md-block'>";
print dolisummarycart($object);
print "</div><div class='col-12 col-md-8'>";
  
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 

print "<div class='modal fade' id='updatethirdparty' tabindex='-1' role='dialog' aria-labelledby='updatethirdpartyTitle' aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>
<h5 class='modal-title' id='updatethirdpartyTitle'>".__( 'Billing address', 'doliconnect')."</h5><button id='Closeupdatethirdparty-form' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
</div><div id='updatethirdparty-form'>";

print "<form class='was-validated' role='form' action='".doliconnecturl('dolicart')."?cart=".$_GET['cart']."&step=info' name='updatethirdparty-form' method='post'>"; 

print dolimodalloaderscript('updatethirdparty-form');

print doliuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'contact');

print "</div>".doliloading('updatethirdparty-form');
print wp_nonce_field( 'valid_dolicart-'.$object->id, 'dolichecknonce');  
print "<div id='Footerupdatethirdparty-form' class='modal-footer'><button name='update_thirdparty' value='validation' class='btn btn-warning btn-block' type='submit'><b>".__( 'Update', 'doliconnect')."</b></button></form></div>
</div></div></div>";

print "<form role='form' action='".doliconnecturl('dolicart')."?cart=".$_GET['cart']."&step=info' id ='doliconnect-infoscartform' method='post'>"; //class='was-validated'

print doliloaderscript('doliconnect-infoscartform');

print "<div class='card'><ul class='list-group list-group-flush'>";

if ( doliversion('10.0.0') ) {
print "<li class='list-group-item'><h6>".__( 'Billing address', 'doliconnect')."</h6><small class='text-muted'>";
} else {
print "<li class='list-group-item'><h6>".__( 'Billing and shipping address', 'doliconnect')."</h6><small class='text-muted'>";
}
print '<div class="custom-control custom-radio">
<input type="radio" id="billing0" name="contact_billing" class="custom-control-input" value="" checked>
<label class="custom-control-label" for="billing0">'.doliaddress($thirdparty).'</label>
</div>';

print '<div class="float-right"><button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#updatethirdparty"><center>'.__( 'Update', 'doliconnect').'</center></button></div>';
print "</small></li>";

if ( doliversion('10.0.0') ) {

print "<li class='list-group-item'><h6>".__( 'Shipping address', 'doliconnect')."</h6><small class='text-muted'>";

print '<div class="custom-control custom-radio">
<input type="radio" id="shipping-0" name="contact_shipping" class="custom-control-input" value="" checked>
<label class="custom-control-label" for="shipping-0">'.__( "Same address that billing", "doliconnect").'</label>
</div>';

$listcontact = callDoliApi("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&includecount=1&sqlfilters=t.statut=1", null, dolidelay('contact', true));

if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {

foreach ($object->contacts_ids as $contact) {
if ('SHIPPING' == $contact->code) {
$contactshipping = $contact->id;
}
}

}
if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) {
print '<div class="custom-control custom-radio"><input type="radio" id="shipping-'.$contact->id.'" name="contact_shipping" class="custom-control-input" value="'.$contact->id.'" ';
if ( !empty($contact->default) || $contactshipping == $contact->id ) { print "checked"; }
print '><label class="custom-control-label" for="shipping-'.$contact->id.'">';
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print '</label></div>';
}
}
print "</small></li>";

} elseif ( current_user_can( 'administrator' ) ) {
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".sprintf( esc_html__( "Add shipping contact needs Dolibarr %s but your version is %s", 'doliconnect'), '10.0.0', doliversion('10.0.0'))."</b></li>";
}

print "<li class='list-group-item'><h6>".__( 'Message', 'doliconnect')."</h6>";
print '<div class="custom-control custom-checkbox">
<input type="checkbox" class="custom-control-input" id="AddMessage" name="AddMessage" data-toggle="collapse" data-target="#collapseMessage" aria-expanded="false" aria-controls="collapseMessage">
<label class="custom-control-label" for="AddMessage">'.__( 'Add a message', 'doliconnect').'</label></div>';
print "<div class='collapse' id='collapseMessage'>";
print "<textarea class='form-control' id='note_public' name='note_public' rows='3' placeholder='".__( 'Enter a message here that you want to send us about your order', 'doliconnect')."'>".$object->note_public."</textarea>";
print "</div>";
print "</li></ul>";

print "<div class='card-body'>".wp_nonce_field( 'valid_dolicart-'.$object->id, 'dolichecknonce' )."<input type='hidden' name='info' value='validation'><input type='hidden' name='dolicart' value='validation'><center><button class='btn btn-warning btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></center></div></form>";
print "<div class='card-footer text-muted'>";
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";
print '</div></div>';

} else {

if ( isset($_GET['step']) || isset($_GET['cart']) || isset($_GET['id']) || isset($_GET['module']) ) {
wp_safe_redirect(doliconnecturl('dolicart'));
exit;
} 

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

if ( isset($object) && is_object($object) && isset($object->id) && $object->id > 0 ) {
$nonce = wp_create_nonce( 'valid_dolicart-'.$object->id );
$arr_params = array( 'cart' => $nonce, 'step' => 'info');  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
}

if ( isset($_POST['dolicart']) && $_POST['dolicart'] == 'purge' && wp_verify_nonce( $_POST['dolichecknonce'], 'valid_dolicart-'.$object->id) ) {
$orderdelete = callDoliApi("DELETE", "/".$module."/".doliconnector($current_user, 'fk_order'), null);
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if (1==1) {
doliconnector($current_user, 'fk_order', true);
wp_safe_redirect(doliconnecturl('dolicart'));
exit;
} else {
print dolialert('danger', __( 'An error is occured. Please contact us!', 'doliconnect'));
}
}
 
if ( isset($_POST['updateorderproduct']) && wp_verify_nonce( $_POST['dolichecknonce'], 'valid_dolicart-'.$object->id)) {
foreach ( $_POST['updateorderproduct'] as $productupdate ) {
$result = doliaddtocart($productupdate['product'], $productupdate['qty'], $productupdate['price'], $productupdate['remise_percent'], $productupdate['date_start'], $productupdate['date_end']);
//print var_dump($_POST['updateorderproduct']);
}
doliconnector($current_user, 'fk_order', true);
$object = callDoliApi("GET", $request, null, dolidelay('cart', true));
wp_safe_redirect(doliconnecturl('dolicart'));
exit;
}

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

if ( doliconnector($current_user, 'fk_order')>0 && $object->lines != null ) {  //&& $timeout>'0'                                                                                         
//print "<div id='timer' class='text-center'><small>".sprintf( esc_html__('Your basket #%s is reserved for', 'doliconnect'), doliconnector($current_user, 'fk_order'))." <span class='duration'></span></small></div>";
}

print "<form role='form' action='".doliconnecturl('dolicart')."' id='doliconnect-basecartform' method='post'>";

print doliloaderscript('doliconnect-basecartform');

print "<div class='card shadow-sm' id='cart-form'><ul class='list-group list-group-flush'>";

print doliline($object, 'cart');

if ( isset($object) && is_object($object) && isset($object->socid) &&(doliconnector($current_user, 'fk_soc') == $object->socid) ) {
print dolitotal($object);  
print wp_nonce_field( 'valid_dolicart-'.$object->id, 'dolichecknonce');  
}

print "</ul>";
print "</form>";  
if ( get_option('dolishop') || (!get_option('dolishop') && isset($object) && $object->lines != null) ) {
print "<div class='card-body'><ul class='list-group list-group-horizontal-sm'>";
if ( get_option('dolishop') ) {
print "<a href='".doliconnecturl('dolishop')."' class='list-group-item list-group-item-action flex-fill'><center><b>".__( 'Continue shopping', 'doliconnect')."</b></center></a>";
} 
if ( isset($object) && is_object($object) && isset($object->lines) && $object->lines != null && (doliconnector($current_user, 'fk_soc') == $object->socid) ) { 
if ( $object->lines != null && $object->statut == 0 ) {
print "<button type='button' onclick='DeleteDoliCart(\"".$nonce."\")' class='list-group-item list-group-item-action flex-fill' role='button' aria-pressed='true'><center><b>".__( 'Empty the basket', 'doliconnect')."</b></center></button>";
}
if ( $object->lines != null ) {
print "<button type='button' onclick='ValidDoliCart(\"".$nonce."\")' class='list-group-item list-group-item-action list-group-item-warning flex-fill ' role='button' aria-pressed='true'><center><b>".__( 'Process', 'doliconnect')."</b></center></button>";
}
}
print "</ul></div>";
}

print "<script>";
print "function ChangeDoliCart() {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.getElementById('doliconnect-basecartform');
form.submit();
        }"; 
print "function DeleteDoliCart(nonce) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".doliconnecturl('dolicart')."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-cartform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'dolicart');
inputvar.setAttribute('value', 'purge');
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'dolichecknonce');
inputvar.setAttribute('value', nonce);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }"; 
print "function ValidDoliCart(nonce) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$return."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-cartform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'dolichecknonce');
inputvar.setAttribute('value', nonce);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }";                  
print "</script>";
  
print '<div class="card-footer text-muted">';
print "<small><div class='float-left'>";
if ( isset($request) ) print dolirefresh($request, doliconnecturl('dolicart'), dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";

}
}

} else {

return $content;

}

}

add_filter( 'the_content', 'dolicart_display');
?>
