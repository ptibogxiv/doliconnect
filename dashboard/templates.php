<?php

function doliaccount_display($content, $controle = false) {
global $wpdb, $current_user;

if ( (in_the_loop() && is_main_query() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) || ( (!is_user_logged_in() && !empty(get_option('doliconnectrestrict')) && !is_page(doliconnectid('doliaccount')) && !empty($controle) ) || (!is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !empty(get_option('doliconnectrestrict')) && !is_page(doliconnectid('doliaccount')) && !empty($controle) ) )) {

doliconnect_enqueues();

if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
print '<div class="card shadow-sm">';
print '<div class="card-body">';
print dolicheckie($_SERVER['HTTP_USER_AGENT']);
print "</div></div>";
} else {

$ID = $current_user->ID;
$time = current_time( 'timestamp', 1);

print "<div class='row'>";
if ( empty(get_option('doliconnectrestrict')) || is_user_logged_in() ) {
print "<div class='col-xs-12 col-sm-12 col-md-3'><div class='row'><div class='col-3 col-xs-4 col-sm-4 col-md-12 col-xl-12'><div class='card shadow-sm' style='width: 100%'>";
print get_avatar($ID);

if ( !defined("DOLIBUG") && is_user_logged_in() && is_user_member_of_blog( $current_user->ID, get_current_blog_id())) {
print "<a href='".esc_url( add_query_arg( 'module', 'avatars', doliconnecturl('doliaccount')) )."' title='".__( 'Edit my avatar', 'doliconnect')."' class='card-img-overlay'><div class='d-block d-sm-block d-xs-block d-md-none'></div><div class='d-none d-md-block'><i class='fas fa-camera fa-2x'></i></div></a>";
} 
print '<ul class="list-group list-group-flush">';
if ( isset($_GET['module']) && !empty($_GET['module'])) {
print "<a href='".esc_url( doliconnecturl('doliaccount') )."' class='list-group-item list-group-item-light list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-arrow-circle-left fa-fw'></i></div><div class='d-none d-md-block'><i class='fas fa-arrow-circle-left fa-fw'></i> ".__( 'Return', 'doliconnect')."</div></center></a>";
} else {
print "<a href='".esc_url(home_url())."' class='list-group-item list-group-item-light list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-home'></i></div><div class='d-none d-md-block'><i class='fas fa-home fa-fw'></i> ".__( 'Home', 'doliconnect')."</div></center></a>";
}
print '</ul>';

print '</div><br></div>';
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

} elseif ( isset($thirdparty->status) && $thirdparty->status != '1' ) {

print "</div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'><div class='card shadow-sm'><div class='card-body'>";
print '<br><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>'.__( 'This account is closed. Please contact us for reopen it.', 'doliconnect').'</h4></div></center><br><br><br><br><br>';
print "</div></div></div></div>";

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', true));

} else { 

if ( isset($_GET['module']) && !empty($_GET['module'])) {
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
} elseif ( has_action('grh_doliconnect_'.esc_attr($_GET['module'])) ) {
    if ( has_action('grh_doliconnect_menu') ) {
    print "<div class='list-group shadow-sm'>";
    do_action('grh_doliconnect_menu', esc_attr($_GET['module']));
    print "</div><br>";
    }
    print "</div></div></div>";
    print "<div class='col-xs-12 col-sm-12 col-md-9'>";
    do_action('grh_doliconnect_'.esc_attr($_GET['module']), esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
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
print "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5><small class='text-muted'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</small></p></div></div></div>";
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

print "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5><small class='text-muted'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</small></p></div></div></div>";
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
if ( has_action('user_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('user_doliconnect_menu');
print "</div><br>";
}  

if ( has_action('customer_doliconnect_menu') && isset($thirdparty->client) && $thirdparty->client == '1' ) {
print "<div class='list-group shadow-sm'>";
do_action('customer_doliconnect_menu');
print "</div><br>";
}

if ( has_action('options_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('options_doliconnect_menu');
print "</div><br>";
}

if ( has_action('supplier_doliconnect_menu') && isset($thirdparty->fournisseur) && $thirdparty->fournisseur == '1' ) {
print "<div class='list-group shadow-sm'>";
do_action('supplier_doliconnect_menu');
print "</div><br>";
}

if ( has_action('grh_doliconnect_menu') ) {
print "<div class='list-group shadow-sm'>";
do_action('grh_doliconnect_menu');
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
//print "<p class='font-weight-light' align='justify'><small class='text-muted'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</p>";
print "</div></div></div>";

if ( empty(get_option('doliconnectrestrict')) ) {
print "<div class='col-xs-12 col-sm-12 col-md-9'>";
} else {
print "<div class='col-md-6 offset-md-3'>";
}

if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
print '<div class="card shadow-sm">';
print '<div class="card-body">';
print dolicheckie($_SERVER['HTTP_USER_AGENT']);
print "</div></div>";
} elseif ( isset($_GET["action"]) && $_GET["action"] == 'confirmaction' ) {

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
} elseif ( isset($_GET["action"]) && $_GET["action"] == 'signup' && !is_user_logged_in() ) {

if ( is_multisite() && !get_option( 'users_can_register' ) && (get_site_option( 'registration' ) != 'user' or get_site_option( 'registration' ) != 'all') ) {
//wp_redirect(esc_url(doliconnecturl('doliaccount')));
//exit;
} elseif ( !get_option( 'users_can_register' ) ) {
//wp_redirect(esc_url(doliconnecturl('doliaccount')));
//exit;
}

if (isset($_GET["morphy"]) && (($_GET["morphy"] == 'mor' && (empty(get_option('doliconnect_disablepro')) || get_option('doliconnect_disablepro') == 'mor')) || ($_GET["morphy"] == 'phy' && (empty(get_option('doliconnect_disablepro')) || get_option('doliconnect_disablepro') == 'phy')))) {
print "<div id='doliuserinfos-alert'></div><form action='".admin_url('admin-ajax.php')."' id='doliuserinfos-form' method='post' class='was-validated' enctype='multipart/form-data'>";

print doliajax('doliuserinfos', null, 'create');

print '<div class="card shadow-sm"><div class="card-header">';
if ($_GET["morphy"] == 'phy') {
print __( 'Create a personnal account', 'doliconnect');   
} elseif ($_GET["morphy"] == 'mor') {
print __( 'Create an enterprise account', 'doliconnect');    
}
print '<a class="float-end text-decoration-none" href="'.wp_registration_url(get_permalink()).'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a>';  
print '</div>';

print doliuserform( null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'thirdparty', doliCheckRights('societe', 'creer'));

print "<div class='card-body'><div class='d-grid gap-2'><input type='hidden' name='submitted' id='submitted' value='true'><button class='btn btn-secondary' type='submit'";
if ( get_option('users_can_register')=='1' && ( get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all' ) || ( !is_multisite() && get_option( 'users_can_register' )) ) {
print "";
} else { print " aria-disabled='true'  disabled"; }
print ">".__( 'Create an account', 'doliconnect')."</button></form>";

print '</div></div></div></form>';

do_action( 'login_footer');

} else {

print '<div class="card shadow-sm"><div class="card-header">'.__( 'Create an account', 'doliconnect');
print '<a class="float-end text-decoration-none" href="'.esc_url( doliconnecturl('doliaccount') ).'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a>';  
print '</div>';

print '<div class="card-body"><div class="card-group">
  <div class="card">
    
    <div class="card-body">
      <h5 class="card-title">'.__( 'Create a personnal account', 'doliconnect').'</h5>
      <p class="card-text"><small class="text-muted">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</small></p>
      <div class="d-grid gap-2">';
  if (get_option('doliconnect_disablepro') == 'mor') {
    print '<a class="btn btn-primary disabled" href="'.wp_registration_url(get_permalink()).'&morphy=phy" role="button" title="'.__( 'Create a personnal account', 'doliconnect').'" aria-disabled="true">'.__( 'Create a personnal account', 'doliconnect').'</a>';
  } else {
    print '<a class="btn btn-primary" href="'.wp_registration_url(get_permalink()).'&morphy=phy" role="button" title="'.__( 'Create a personnal account', 'doliconnect').'">'.__( 'Create a personnal account', 'doliconnect').'</a>';    
  }
      print '</div>
    </div>
  </div>
  <div class="card">
    
    <div class="card-body">
      <h5 class="card-title">'.__( 'Create an enterprise account', 'doliconnect').'</h5>
      <p class="card-text"><small class="text-muted">his is a wider card with supporting text below as a natural lead-in to additional content. This card has even longer content than the first to show that equal height action.</small></p>
      <div class="d-grid gap-2">';
  if (get_option('doliconnect_disablepro') == 'phy') {
    print '<a class="btn btn-primary disabled" href="'.wp_registration_url(get_permalink()).'&morphy=mor" role="button" title="'.__( 'Create an enterprise account', 'doliconnect').'" aria-disabled="true">'.__( 'Create a personnal account', 'doliconnect').'</a>';
  } else {
    print '<a class="btn btn-primary" href="'.wp_registration_url(get_permalink()).'&morphy=mor" role="button" title="'.__( 'Create an enterprise account', 'doliconnect').'">'.__( 'Create an enterprise account', 'doliconnect').'</a>';    
  }
      print '</div>
  </div>
</div>';
print '</div></div>';
}

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

print dolipasswordform($user, doliconnecturl('doliaccount'));

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

print "<div id='dolifpw-alert'></div><form id='dolifpw-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
print doliajax('dolifpw');
 
print '<div class="card shadow-sm"><div class="card-header">'.__( 'Forgot password?', 'doliconnect');
print '<a class="float-end text-decoration-none" href="'.esc_url( doliconnecturl('doliaccount') ).'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a>';  
print '</div>';
print "<ul class='list-group list-group-flush'><li class='list-group-item list-group-item-light list-group-item-action'>";
print "<p class='text-justify'>".__( 'Please enter the email address by which you registered your account.', 'doliconnect')."</p>";

print '<div class="form-floating mb-2">
<input type="email" class="form-control" id="user_email" placeholder="name@example.com" name="user_email" value="" required>
<label for="user_email"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label>
</div>';

print dolicaptcha();

print "</li></lu><div class='card-body'>";
print '<div class="d-grid gap-2"><button class="btn btn-secondary" type="submit" value="submit">'.__( 'Submit', 'doliconnect').'</button></div>';

print "</form></div></div>";

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
print "<div class='card shadow-sm'><div class='card-header'>";
if ( empty(get_option('doliconnectrestrict')) ) {
print "<h5 class='card-title'>".__( 'Welcome', 'doliconnect')."</h5>";
} else {
print "<h5 class='card-title'>".__( 'Access restricted to users', 'doliconnect')."</h5>";
}
print "</div>";
}
if ( function_exists('doliconnect_modalform') && get_option('doliloginmodal') == '1' ) {

print '<ul class="list-group list-group-flush"><li class="list-group-item"><center><i class="fas fa-user-lock fa-fw fa-10x"></i>';
//print "<h2>".__( 'Restricted area', 'doliconnect')."</h2></center>";
print "</center></li></lu><div class='card-body'>";

print '<div class="btn-group w-100" role="group" aria-label="actions buttons">';
if ((!is_multisite() && get_option( 'users_can_register' )) || ((!is_multisite() && get_option( 'dolicustsupp_can_register' )) || ((get_option( 'dolicustsupp_can_register' ) || get_option('users_can_register') == '1') && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
print '<a href="'.wp_registration_url( get_permalink() ).'" id="login-'.current_time('timestamp').'" title="'.__('Signup', 'doliconnect').'" class="btn btn-secondary" role="button">'.__("You don't have an account", 'doliconnect').'</a>';
}
print '<a href="#" id="login-'.current_time('timestamp').'" data-bs-target="#DoliconnectLogin"  data-bs-toggle="modal" data-bs-dismiss="modal" title="'.__('Sign in', 'doliconnect').'" class="btn btn-secondary" role="button">'.__('You have already an account', 'doliconnect').'</a>';
print '</div>';

} else {

do_action( 'login_head');

if (!empty(get_option('doliaccountinfo'))) {
print "<div class='card-body'><b>".get_option('doliaccountinfo')."</b></div>";
}

if ( function_exists('socialconnect') ) {
print socialconnect(get_permalink());
}

if ( function_exists('secupress_get_module_option') && !empty(get_site_option('secupress_active_submodule_move-login')) && secupress_get_module_option('move-login_slug-login', '', 'users-login' ) ) {
$login_url = site_url()."/".secupress_get_module_option('move-login_slug-login', '', 'users-login'); 
} elseif (get_site_option('doliconnect_login')) {
$login_url = site_url()."/".get_site_option('doliconnect_login');
} else {
$login_url = site_url()."/wp-login.php"; }
if ( isset($_GET["redirect_to"])) { $redirect_to=$_GET["redirect_to"]; } else {
$redirect_to=$_SERVER['HTTP_REFERER'];}
 
print "<form class='was-validated' id='doliconnect-loginform' action='".$login_url."' method='post'>";
print "<ul class='list-group list-group-flush'><li class='list-group-item'>";

print doliloaderscript('doliconnect-loginform'); 
if  ( defined("DOLICONNECT_DEMO") ) {
print "<p><i class='fas fa-info-circle fa-fw'></i> <b>".__( 'Demo mode is activated', 'doliconnect')."</b></p>";
} 
print '<div class="form-floating mb-3"><input type="email" class="form-control" id="user_login" name="log" placeholder="name@example.com" value="';
if ( defined("DOLICONNECT_DEMO") && defined("DOLICONNECT_DEMO_EMAIL") && !empty(constant("DOLICONNECT_DEMO_EMAIL")) ) {
print constant("DOLICONNECT_DEMO_EMAIL");
}
print '" required autofocus><label for="user_login"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect-pro').'</label></div>';

print '<div class="form-floating mb-3"><input type="password" class="form-control" id="user_pass" name="pwd" placeholder="Password" value="';
if ( defined("DOLICONNECT_DEMO") && defined("DOLICONNECT_DEMO_PASSWORD") && !empty(constant("DOLICONNECT_DEMO_PASSWORD")) ) {
print constant("DOLICONNECT_DEMO_PASSWORD");
}
print '" required><label for="user_pass"><i class="fas fa-key fa-fw"></i> '.__( 'Password', 'doliconnect-pro').'</label></div>';

do_action( 'login_form' );

print '<div class="form-check float-start">
  <input class="form-check-input" type="checkbox" name="rememberme" value="forever" id="rememberme" checked>
  <label class="form-check-label" for="rememberme">'.__( 'Remember me', 'doliconnect').'</label>
</div>';

print "<a class='float-end' href='".wp_lostpassword_url(get_permalink())."' role='button' title='".__( 'Forgot password?', 'doliconnect')."'><small>".__( 'Forgot password?', 'doliconnect')."</small></a>"; 

print "</li></lu><div class='card-body'>";
if ( get_site_option('doliconnect_mode') == 'one' && function_exists('switch_to_blog') ) {
switch_to_blog(1);
} 
if ((!is_multisite() && get_option( 'users_can_register' )) || ((!is_multisite() && get_option( 'dolicustsupp_can_register' )) || ((get_option( 'dolicustsupp_can_register' ) || get_option('users_can_register') == '1') && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
print "<a class='btn btn-secondary' href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect')."'>".__( 'Create an account', 'doliconnect')."</a> ";
}
if (get_site_option('doliconnect_mode')=='one') {
restore_current_blog();
}
print "<input type='hidden' value='$redirect_to' name='redirect_to'><button id='submit' class='btn btn-secondary' type='submit' name='submit' value='Submit'>".__( 'Sign in', 'doliconnect')."</button>";

do_action( 'login_footer');

}

print "</div></div></form>";

}

}

} 
} else {
return $content;
}

}

add_filter( 'the_content', 'doliaccount_display', 10, 2);

// ********************************************************

function dolifaq_display($content) {
    global $current_user;
    
    if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolifaq')) && !empty(doliconnectid('dolifaq')) ) {
    
    doliconnect_enqueues();
    
    if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
    print '<div class="card shadow-sm">';
    print '<div class="card-body">';
    print dolicheckie($_SERVER['HTTP_USER_AGENT']);
    print "</div></div>";
    } else {

        $limit=8;
        if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
        $request = "/knowledgemanagement/knowledgerecords?sortfield=t.rowid&sortorder=ASC&limit=".$limit."&page=".$page; 
        $listfaq = callDoliApi("GET", $request, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        $url = doliconnecturl('dolifaq');

    //print var_dump($faq);
    
    print '<div class="card"><div class="card-header">'.__( 'Knowledge base', 'doliconnect').'</div>';
    print '<div class="accordion accordion-flush" id="accordionDolifaq">';
    if ( !isset( $listfaq->error ) && $listfaq != null ) {
        foreach ( $listfaq as $postfaq ) { 
    print '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingDolifaq'.$postfaq->id.'">';
    print '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseDolifaq'.$postfaq->id.'" aria-expanded="false" aria-controls="flush-collapseDolifaq'.$postfaq->id.'">';
    print $postfaq->question;
    print '</button></h2>
    <div id="flush-collapseDolifaq'.$postfaq->id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingDolifaq'.$postfaq->id.'" data-bs-parent="#accordionDolifaq">
    <div class="accordion-body">'.$postfaq->answer.'</div></div></div>';
    }}
  print '</div>';

  print '<div class="card-body">';
  print dolipage($listfaq, $url, $page, $limit);
  print '</div><div class="card-footer text-muted">';
  print "<small><div class='float-start'>";
  if ( isset($request) ) print dolirefresh($request, $url, dolidelay('constante'));
  print "</div><div class='float-end'>";
  print dolihelp('ISSUE');
  print "</div></small>";
  print '</div></div>';

    }
    } else {
    return $content;
    }
    
    }
    
    add_filter( 'the_content', 'dolifaq_display');
    
    // ********************************************************

function dolicontact_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicontact')) && !empty(doliconnectid('dolicontact')) ) {

doliconnect_enqueues();

if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
print '<div class="card shadow-sm">';
print '<div class="card-body">';
print dolicheckie($_SERVER['HTTP_USER_AGENT']);
print "</div></div>";
} else {

print "<div class='row w-100'><div class='col-md-4'><h4>".__( 'Address', 'doliconnect')."</h4>";
$company = callDoliApi("GET", "/setup/company", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print $company->name.'<br>';
if (isset($company->address) && !empty($company->address)) print $company->address.'<br>';
if (isset($company->town) && !empty($company->town)) print $company->zip.' '.$company->town.'<br>';
if ( isset($company->state_id) && !empty($company->state_id) ) {  
  if ( function_exists('pll_the_languages') ) { 
  $lang = pll_current_language('locale');
  } else {
  $lang = $current_user->locale;
  }
  $state = callDoliApi("GET", "/setup/dictionary/states/".$company->state_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
  print $state->name;
}
print ' - ';
if ( isset($company->country_id) && !empty($company->country_id) ) {  
  if ( function_exists('pll_the_languages') ) { 
  $lang = pll_current_language('locale');
  } else {
  $lang = $current_user->locale;
  }
  $country = callDoliApi("GET", "/setup/dictionary/countries/".$company->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print $country->label;
}
print '<br>';
if (isset($company->phone) && !empty($company->phone)) print $company->phone;
print "<br><h4>".__( 'Opening hours', 'doliconnect')."</h4>";


print "</div><div class='col-md-8'><div id='content'>";

print "<div id='dolicontact-alert'></div><form id='dolicontact-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
print doliajax('dolicontact');

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>
<li class='list-group-item'>";
if (is_user_logged_in()) {
$fullname = $current_user->user_firstname." ".$current_user->user_lastname;
} else {
$fullname = '';
}
print '<div class="form-floating mb-2">
<input type="text" class="form-control" name="contactName" autocomplete="off" id="contactName" placeholder="Name" value="'.$fullname.'"';
if ( is_user_logged_in() ) { print " readonly"; } else { print " required"; }
print '>
<label for="contactName"><i class="fas fa-user fa-fw"></i> '.__( 'Complete name', 'doliconnect').'</label>
</div>';

print '<div class="form-floating mb-2">
<input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="'.$current_user->user_email.'" autocomplete="off" ';
if ( is_user_logged_in() ) { print " readonly"; } else { print " required"; }
print '>
<label for="email"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label>
</div>';

$type = callDoliApi("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if ( isset($type) ) { 
print '<div class="form-floating mb-2"><select class="form-select" id="ticket_type"  name="ticket_type" aria-label="'.__( 'Type', 'doliconnect').'" required>';
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
print '</select><label for="ticket_type">'.__( 'Type', 'doliconnect').'</label></div>';
}

print '<div class="form-floating mb-2">
<textarea class="form-control" placeholder="Leave a comment here" name="comments" id="commentsText" style="height: 200px" required></textarea>
<label for="commentsText">'.__( 'Message', 'doliconnect').'</label>
</div>';

print dolicaptcha();

if ( !is_user_logged_in() ) {
print '</li><li class="list-group-item"><div class="form-check"><input id="rgpdinfo" class="form-check-input form-check-sm" type="checkbox" name="rgpdinfo" value="ok"><label class="form-check-label w-100" for="rgpdinfo"><small class="form-text text-muted"> '.__( 'I agree to save my personnal informations in order to contact me', 'doliconnect').'</small></label></div>';  
}
print "</li></ul>";
print "<div class='card-body'><div class='d-grid gap-2'><button class='btn btn-secondary' type='submit'>".__( 'Send', 'doliconnect')."</button><input type='hidden' name='submitted' id='submitted' value='true' /></div></div></div></div></div></form>";

print "</div>";

}
} else {
return $content;
}

}

add_filter( 'the_content', 'dolicontact_display');

// ********************************************************

function dolisupplier_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolisupplier')) && !empty(doliconnectid('dolisupplier')) ) {

doliconnect_enqueues();

$shopsupplier = doliconst("DOLICONNECT_CATSHOP_SUPPLIER", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$category = "";

if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
print '<div class="card shadow-sm">';
print '<div class="card-body">';
print dolicheckie($_SERVER['HTTP_USER_AGENT']);
print "</div></div>";
} else {

if ( isset($_GET['supplier']) && is_numeric(esc_attr($_GET['supplier'])) && esc_attr($_GET['supplier']) > 0 ) { 
 
$request = "/thirdparties/".esc_attr($_GET['supplier']);
$module = 'thirdparty';
$thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $thirdparty;
}

print "<div class='card shadow-sm'>";

if ( !isset($thirdparty->error) && isset($_GET['supplier']) && isset($thirdparty->id) && ($_GET['supplier'] == $thirdparty->id) && $thirdparty->status == 1 && $thirdparty->fournisseur == 1 ) {

print "<ul class='list-group list-group-flush'><li class='list-group-item'>";

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

if (!empty(doliconnect_categories('supplier', $thirdparty))) print doliconnect_categories('supplier', $thirdparty, doliconnecturl('dolisupplier'))."<br><br>";

print doliconnect_image('thirdparty', $thirdparty->id, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $thirdparty->entity);

print "</li>"; 

$module = 'product';
$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = "0"; }
$request = "/products/purchase_prices?sortfield=t.ref&sortorder=ASC&limit=".$limit."&page=".$page."&supplier=".esc_attr($_GET["supplier"])."&sqlfilters=(t.tosell%3A%3D%3A1)";
$resultats2 = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$resultats = array();
if ( !isset($resultats2->error) && $resultats2 != null ) {
$count = count($resultats);
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
foreach ($resultats2 as $product) {

$resultats[$product[0]->id] = 1; 
$product = callDoliApi("GET", "/products/".$product[0]->id."?includestockdata=".$includestock."&includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print apply_filters( 'doliproductlist', $product);

}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No item currently on sale', 'doliconnect')."</center></li>";
}

print "</ul><div class='card-body'>";

} else {

if (isset($shopsupplier) && !empty($shopsupplier)) $category = "&category=".$shopsupplier;
$module = 'thirdparty';
$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$request = "/thirdparties?sortfield=t.nom&sortorder=ASC&limit=".$limit."&page=".$page."&mode=4".$category."&sqlfilters=(t.status%3A%3D%3A'1')";
$resultats = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if (!empty(get_option('dolicartsuppliergrid'))) { 
print "<div class='card-body'><div class='row' data-masonry='{'percentPosition': true }'>";
} else {
print "<ul class='list-group list-group-flush'>";
}

if ( !isset($resultats->error) && $resultats != null ) {
foreach ($resultats as $supplier) {

if (!empty(get_option('dolicartsuppliergrid'))) { 
print '<div class="col-sm-6 col-lg-4 mb-4"><div class="card">';
if (!empty($supplier->logo)) { 
print '<a href="'.esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) ).'">'.doliconnect_image('thirdparty', $supplier->id.'/logos/'.$supplier->logo, array('entity'=>$supplier->entity, 'class'=>'card-img'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)).'</a>';
} else {
print '<div class="card-body"><a href="'.esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) ).'"><center>'.(!empty($supplier->name_alias)?$supplier->name_alias:$supplier->name).'</center></a></div>';
}

print "</div></div>";

} else {
print "<a href='".esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) )."' class='list-group-item list-group-item-action'>".(!empty($supplier->name_alias)?$supplier->name_alias:$supplier->name)."</a>";
}

}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No supplier', 'doliconnect')."</center></li>";
}

if (!empty(get_option('dolicartsuppliergrid'))) { 
print "</div></div><div class='card-body'>";
} else {
print "</ul><div class='card-body'>";
} 

} 

print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay($module));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

}
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

if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
print '<div class="card shadow-sm">';
print '<div class="card-body">';
print dolicheckie($_SERVER['HTTP_USER_AGENT']);
print "</div></div>";
} elseif ( isset($_GET['search']) && !isset($_GET['product']) && empty($_GET['search'])) {

print "<ul class='list-group list-group-flush'>";
print "<div class='card-body'>";

print '<form role="search" method="get" id="shopform" action="' . doliconnecturl('dolishop') . '" ><div class="input-group">
<input type="text" class="form-control" name="search" id="search" placeholder="' . esc_attr__('Name, Ref., Description or Barcode', 'doliconnect') . '" aria-label="Search for..." aria-describedby="searchproduct">
<button class="btn btn-primary" type="submit" id="searchproduct" ><i class="fas fa-search"></i></button></div></form>';

print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} elseif (get_option('dolicartnewlist') != 'none' && isset($_GET['category']) && $_GET['category'] == 'new' && !isset($_GET['product'])) {

print "<ul class='list-group list-group-flush'>";

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$date = new DateTime(); 
$date->modify('NOW');
$duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
$date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
$lastdate = $date->format('Y-m-d');
$request = "/products?sortfield=t.datec&sortorder=DESC&limit=".$limit."&page=".$page."&sqlfilters=(t.datec%3A%3E%3A'".$lastdate."')%20AND%20(t.tosell%3A%3D%3A1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
print "<li class='list-group-item list-group-item-light'><center>".__(  'Here are our new items', 'doliconnect')."</center></li>";
foreach ($resultats as $product) {

$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=".$includestock."&includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print apply_filters( 'doliproductlist', $product);
 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__(  'No new item', 'doliconnect')."</center></li>";
}
print "</ul><div class='card-body'>";
print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} elseif (!empty(doliconst('MAIN_MODULE_DISCOUNTPRICE')) && isset($_GET['category']) && $_GET['category'] == 'discount' && !isset($_GET['product'])) {

print "<ul class='list-group list-group-flush'>";

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$request = "/discountprice?sortfield=t.rowid&sortorder=DESC&limit=".$limit."&page=".$page."&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')%20AND%20(d.tosell%3A%3D%3A1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $resultatso;

$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}

if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
print "<li class='list-group-item list-group-item-light'><center>".__(  'Here are our discounted items', 'doliconnect')."</center></li>";
foreach ($resultats as $product) {

$product = callDoliApi("GET", "/products/".$product->fk_product."?includestockdata=".$includestock."&includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print apply_filters( 'doliproductlist', $product);
 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__(  'No discounted item', 'doliconnect')."</center></li>";
}
print "</ul><div class='card-body'>";
print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} elseif ( isset($_GET['product']) && is_numeric(esc_attr($_GET['product'])) ) {

$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
$request = "/products/".esc_attr($_GET['product'])."?includestockdata=".$includestock."&includesubproducts=true&includetrans=true";
$product = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

print "<div class='card-body'>";
print apply_filters( 'doliproductcard', $product, null);
print "</div>";

print "</ul><div class='card-body'>";

print "</div><div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

} elseif ( (isset($_GET['category']) && !empty($_GET['category'])) || (isset($_GET['search']) && !empty($_GET['search'])) ) {

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); } else { $page = 0; }
if ( isset($_GET['field']) ) { $field = esc_attr($_GET['field']); } else { $field = 'label'; }
if ( isset($_GET['order']) ) { $order = esc_attr($_GET['order']); } else { $order = 'ASC'; }

print "<ul class='list-group list-group-flush'>";

$cat = esc_attr(isset($_GET["subsubcategory"]) ? $_GET["subsubcategory"] : (isset($_GET["subcategory"]) ? $_GET["subcategory"] : (isset($_GET["category"]) ? $_GET["category"] : null)));
$subcat = esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $cat);
$subsubcat = esc_attr(isset($_GET["subsubcategory"]) ? $_GET["subsubcategory"] : $cat);
$category = callDoliApi("GET", "/categories/".$cat."?include_childs=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ((is_numeric($cat) && isset($category->id) && $category->id > 0) || (isset($_GET["category"]) && $_GET["category"] == 'all') || (isset($_GET['search'])&& !empty($_GET['search']))) {

if (isset($_GET['search'])&& !empty($_GET['search']))  {
$request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&sqlfilters=((t.label%3Alike%3A'%25".esc_attr($_GET['search'])."%25')%20OR%20(t.description%3Alike%3A'%25".esc_attr($_GET['search'])."%25')%20OR%20(t.ref%3Alike%3A'%25".esc_attr($_GET['search'])."%25')%20OR%20(t.barcode%3Alike%3A'%25".esc_attr($_GET['search'])."%25'))%20AND%20(t.tosell%3A%3D%3A1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('search', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
} elseif (isset($_GET["category"]) && $_GET["category"] == 'all') {
$request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".esc_attr($shop)."&sqlfilters=(t.tosell%3A%3D%3A1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}else {
$request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".$cat."&sqlfilters=(t.tosell=1)";
$resultats = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}
//print $resultats;

print "<li class='list-group-item'>";
print "<div class='row'><div class='col-4 col-md-2'><center>";
if (isset($_GET['search'])&& !empty($_GET['search'])) {
if ( !isset($resultats->error) && $resultats != null ) {
$count = count($resultats);
} else $count = 0;
//print doliconnect_image('category', $category->id, 1, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $category->entity);
print '</center></div><div class="col-4 col-md-7">';
printf( _n( 'We have found %s item with this search', 'We have found %s items with this search', $count, 'doliconnect' ), number_format_i18n( $count ) );
print " '".esc_attr($_GET['search'])."'";
} elseif (isset($_GET["category"]) && $_GET["category"] == 'all') {
//print doliconnect_image('category', $category->id, 1, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $category->entity);
print "</center></div><div class='col-4 col-md-7'>".__( 'All items', 'doliconnect');
} else {
print doliconnect_image('category', $category->id, 1, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $category->entity);
print "</center></div><div class='col-4 col-md-7'>".doliproduct($category, 'label')."<br><small>".doliproduct($category, 'description').'</small>';
}
print '</div><div class="col-4 col-md-3"><div class="input-group">
  <span class="input-group-text" id="basic-addon1"><i class="fas fa-filter"></i></span><select id="selectbox" class="form-select form-select-sm" aria-label=".form-select-sm example" name="" onchange="javascript:location.href = this.value;">
    <option value="" disabled selected>'.__( '- Select -', 'doliconnect').'</option>
    <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'category' => !empty($cat)?$cat:null, 'subcategory' => !empty($subcat)?$subcat:null, 'pg' => $page+1, 'field' => 'label', 'order' => 'ASC'), doliconnecturl('dolishop')) ).'"';
    if ($field == 'label' && $order == 'ASC') { print 'selected'; }
    print '>'.__( 'Name A->Z', 'doliconnect').'</option>
    <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'category' => !empty($cat)?$cat:null, 'subcategory' => !empty($subcat)?$subcat:null, 'pg' => $page+1, 'field' => 'label', 'order' => 'DESC'), doliconnecturl('dolishop')) ).'"';
    if ($field == 'label' && $order == 'DESC') { print 'selected'; }
    print '>'.__( 'Name Z->A', 'doliconnect').'</option>
    <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'category' => !empty($cat)?$cat:null, 'subcategory' => !empty($subcat)?$subcat:null, 'pg' => $page+1, 'field' => 'rowid', 'order' => 'DESC'), doliconnecturl('dolishop')) ).'"';
    if ($field == 'rowid' && $order == 'DESC') { print 'selected'; }
    print '>'.__( 'Novelties', 'doliconnect').'</option>
    <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null,'category' => !empty($cat)?$cat:null, 'subcategory' => !empty($subcat)?$subcat:null, 'pg' => $page+1, 'field' => 'price', 'order' => 'ASC'), doliconnecturl('dolishop')) ).'"';
    if ($field == 'price' && $order == 'ASC') { print 'selected'; }
    print '>'.__( 'Lowest prices', 'doliconnect').'</option>
    <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'category' => !empty($cat)?$cat:null, 'subcategory' => !empty($subcat)?$subcat:null, 'pg' => $page+1, 'field' => 'price', 'order' => 'DESC'), doliconnecturl('dolishop')) ).'"';
    if ($field == 'price' && $order == 'DESC') { print 'selected'; }
    print '>'.__( 'Highest prices', 'doliconnect').'</option>
</select></div></div>';
print '</div></li>'; 

if (!isset($_GET['search'])) {
$request2 = "/categories/".$cat."?include_childs=true";
$resultats2 = callDoliApi("GET", $request2, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultats2->error) && $resultats2 != null ) {
foreach ($resultats2->childs as $categorie) {

$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}

$arg['category'] = esc_attr($_GET['category']);
if (isset($_GET["subcategory"]) && isset($_GET["category"])) {
$arg['subcategory'] = esc_attr($_GET['subcategory']);
$arg['subsubcategory'] = $categorie->id;
} else {
$arg['subcategory'] = $categorie->id;
}
print "<a href='".esc_url( add_query_arg( $arg, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action'>".doliproduct($categorie, 'label')." (".$count.")</a>";

}}
}

if ( !isset($resultats->error) && $resultats != null ) {
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
foreach ($resultats as $product) {

$product = callDoliApi("GET", "/products/".$product->id."?includestockdata=".$includestock."&includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
print apply_filters( 'doliproductlist', $product);
 
}
} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No item currently on sale', 'doliconnect')."</center></li>";
}
} else {
print "<li class='list-group-item list-group-item-white'><center><br><br><br><br><div class='align-middle'><i class='fas fa-bomb fa-7x fa-fw'></i><h4>".__( 'Oops! This category does not appear to exist', 'doliconnect' )."</h4></div><br>";
print '<button type="button" class="btn btn-link" onclick="window.history.back()">'.__( 'Return', 'doliconnect').'</button>';
print "<br><br><br></center></li>";
}

print '</ul>';
if ((is_numeric($cat) && isset($category->id) && $category->id > 0) || (isset($_GET["category"]) && $_GET["category"] == 'all') || (isset($_GET['search'])&& !empty($_GET['search']))) {
print '<div class="card-body">';
if ( isset($resultats) ) print dolipage($resultats, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print '</div>';
}
print '<div class="card-footer text-muted">';
print '<small><div class="float-start">';
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print '</div><div class="float-end">';
print dolihelp('ISSUE');
print '</div></small>';
print '</div></div>';

} else {
print "<ul class='list-group list-group-flush'>";

$limit=25;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }

if ( $shop != null && $shop > 0 ) {
$request = "/categories/".esc_attr($shop)."?include_childs=true";
} else{
$request = "/categories";
}

$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {

if (doliconst("CATEGORIE_RECURSIV_ADD", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))) { 
print "<a href='".esc_url( add_query_arg( 'category', 'all', doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
if (isset($_GET['category']) && $_GET['category'] == 'all') { print " active"; }
$requestp = "/products?sortfield=t.rowid&sortorder=DESC&category=".esc_attr($shop)."&sqlfilters=(t.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}
print "'>".__(  'All items', 'doliconnect')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
}

if (get_option('dolicartnewlist') != 'none') {
print "<a href='".esc_url( add_query_arg( 'category', 'new', doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
if (isset($_GET['category']) && $_GET['category'] == 'new') { print " active"; }
$date = new DateTime(); 
$date->modify('NOW');
$duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
$date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
$lastdate = $date->format('Y-m-d');
$requestp = "/products?sortfield=t.datec&sortorder=DESC&sqlfilters=(t.datec%3A%3E%3A'".$lastdate."')%20AND%20(t.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}
print "'>".__(  'Novelties', 'doliconnect')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
}

if ( !empty(doliconst('MAIN_MODULE_DISCOUNTPRICE', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))) ) {
print "<a href='".esc_url( add_query_arg( 'category', 'discount', doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
if (isset($_GET['category']) && $_GET['category'] == 'discount') { print " active"; }
$date = new DateTime(); 
$date->modify('NOW');
$lastdate = $date->format('Y-m-d');
$requestp = "/discountprice?sortfield=t.rowid&sortorder=DESC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')%20AND%20(d.tosell%3A%3D%3A1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}
print "'>".__(  'Discounted items', 'doliconnect')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
}

if ( $shop != null && $shop > 0 ) {
$resultatsc = $resultatsc->childs;
} 

foreach ($resultatsc as $categorie) {

$requestp = "/products?sortfield=t.rowid&sortorder=DESC&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}

print "<a href='".esc_url( add_query_arg( 'category', $categorie->id, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
if ( isset($_GET['category']) && !isset($_GET['subcategory']) && $categorie->id == $_GET['category']) { print " active"; }
print "'>".doliproduct($categorie, 'label')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";

if ( isset($_GET['category']) && $categorie->id == $_GET['category'] ) {

$request = "/categories/".esc_attr(isset($_GET["category"]) ? $_GET["category"] : $_GET["subcategory"])."?include_childs=true";
$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc->childs as $categorie) {

$requestp = "/products?sortfield=t.rowid&sortorder=DESC&category=".$categorie->id."&sqlfilters=(t.tosell=1)";
$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (empty($listproduct) || isset($listproduct->error)) {
$count = 0;
} else {
$count = count($listproduct);
}

print "<a href='".esc_url( add_query_arg( array( 'category' => $_GET['category'], 'subcategory' => $categorie->id), doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
if ( isset($_GET['subcategory']) && $categorie->id == $_GET['subcategory'] ) { print " active"; }
print "'>>".doliproduct($categorie, 'label')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
}

}}

}

}

$catoption = doliconst("ADHERENT_MEMBER_CATEGORY", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
if ( !empty(doliconst('MAIN_MODULE_ADHERENTSPLUS')) && !empty($catoption) && is_user_logged_in() ) {
print "<a href='".esc_url( add_query_arg( 'category', $catoption, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action' >Produits/Services lies a l'adhesion</a>";
}

print '</ul>';
if (isset($resultats->childs)) {
print "<div class='card-body'>";
print dolipage($resultats->childs, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
print "</div>";
} 
print "<div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('product'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

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

$art200 = doliconst("DONATION_ART200", dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art238 = doliconst("DONATION_ART238", dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$art835 = doliconst("DONATION_ART835", dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $shop;

if ( defined("DOLIBUG") ) {

print dolibug();

} elseif ( !!empty(doliconst('MAIN_MODULE_COMMANDE')) ) {
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
print doliuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'donation', doliCheckRights('societe', 'creer'));

print "<div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect')."</b></button></div>";

} else {
print "<div class='card-body'>"; 

print "<h5><i class='fas fa-donate fa-fw'></i> Don hors ligne</h5>";

//if ( $object->mode_reglement_code == 'CHQ') {

$chq = doliconst("FACTURE_CHQ_NUMBER", dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$bank = callDoliApi("GET", "/bankaccounts/".$chq, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), 'votre choix', __( 'donation', 'doliconnect'), $bank->proprio ).":</p><p><b>$bank->owner_address</b></p></div>";

//} 
//if ($object->mode_reglement_code == 'VIR') {

$vir = doliconst("FACTURE_RIB_NUMBER", dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$bank = callDoliApi("GET", "/bankaccounts/".$vir, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), 'votre choix', __( 'donation', 'doliconnect') ).":";
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

print '<div class="card-footer text-muted">';

print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, doliconnecturl('dolidonation'), dolidelay('constante'));
print "</div><div class='float-end'>";
print dolihelp('COM');
print "</div></small></div>";

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

$object = callDoliApi("GET", $request, null, dolidelay('cart', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
print '<div class="card shadow-sm">';
print '<div class="card-body">';
print dolicheckie($_SERVER['HTTP_USER_AGENT']);
print "</div></div>";
} elseif ( defined("DOLIBUG") ) {

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

print '<li id="li-tab-cart" class="nav-item"><a id="a-tab-cart" class="nav-link';
if (!isset($_GET['stage']) || !isset($object) || !is_object($object) || !isset($object->lines)) { print ' active'; }
print '" data-bs-toggle="pill" role="tab" href="#nav-tab-cart" aria-controls="nav-tab-cart" aria-selected="';
if (!isset($_GET['stage']) || !isset($object) || !is_object($object) || !isset($object->lines)) { print 'true'; } else { print 'false'; }
print '"><i class="fas fa-shopping-bag fa-fw"></i> '.__( 'Cart', 'doliconnect').'</a></li>';

print '<li id="li-tab-info" class="nav-item"><a id="a-tab-info" class="nav-link';
if (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print ' active'; }
elseif (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print ''; } else { print ' disabled'; }
print '" data-bs-toggle="pill" role="tab" href="#nav-tab-info" aria-controls="nav-tab-info" aria-selected="';
if (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print 'true'; } else { print 'false'; }
print '"><i class="fas fa-user-check fa-fw"></i> '.__( 'Coordinates', 'doliconnect').'</a></li>';

print '<li id="li-tab-pay" class="nav-item"><a id="a-tab-pay" class="nav-link';
if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print ' active'; } else { print ' disabled'; }
print '" data-bs-toggle="pill" role="tab" href="#nav-tab-pay" aria-controls="nav-tab-pay" aria-selected="';
if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print 'true'; } else { print 'false'; }
print '"><i class="fas fa-money-bill-wave fa-fw"></i> '.__( 'Payment', 'doliconnect').'</a></li>';
 
print "</ul><br><div id='tab-cart-content' class='tab-content'>";

print '<div class="tab-pane fade';
if (!isset($_GET['stage']) || !isset($object) || !is_object($object) || !isset($object->lines)) { print ' show active'; }
print '" role="tabpanel" id="nav-tab-cart">';
 
if ( isset($object) && is_object($object) && isset($object->date_modification) && !empty($object->date_modification)) {
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

print doliline($object, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), false);

if ( isset($object) && is_object($object) && isset($object->socid) &&(doliconnector($current_user, 'fk_soc') == $object->socid) ) {
print "</ul><ul id='dolitotal' class='list-group list-group-flush'>";
print dolitotal($object);  
}

if ( has_filter('mydoliconnectcartfilter') ) {
print "<li class='list-group-item bg-light'>";
print apply_filters('mydoliconnectcartfilter', $object);
print "</li>";
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
$nonce = wp_create_nonce( 'dolicart-nonce');
$arr_params = array( 'stage' => 'informations', 'security' => $nonce);  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
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
            'dolicart-nonce': '".$nonce."',
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
if (document.getElementById('DoliHeaderCartItems')) {
document.getElementById('DoliHeaderCartItems').innerHTML = response.data.items;
}
if (document.getElementById('DoliFooterCarItems')) {  
document.getElementById('DoliFooterCarItems').innerHTML = response.data.items;
}
if (document.getElementById('DoliWidgetCarItems')) {
document.getElementById('DoliWidgetCartItems').innerHTML = response.data.items;
} 

} else if (actionvalue == 'validate_cart') {
//$('#a-tab-cart').removeClass('active');
//$('#a-tab-info').removeClass('disabled');
//$('#a-tab-info').addClass('active');    
//$('#nav-tab-cart').removeClass('show active');
//$('#nav-tab-info').addClass('show active');
//$('#nav-tab-cart').tab('dispose');
//$('#nav-tab-info').tab('show');
document.location = '".$return."';
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
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, doliconnecturl('dolicart'), dolidelay('cart'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

print "</div>";

if ( is_user_logged_in() ) { 
print '<div class="tab-pane fade';
if (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print ' show active'; }
print '" role="tabpanel" id="nav-tab-info">';
  
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 

print "<div class='card'><ul class='list-group list-group-flush'>";

if ( doliversion('10.0.0') ) {

print "<li class='list-group-item list-group-item-action'><div class='row'><div class='col-12 col-md-6'><h6>".__( 'Billing address', 'doliconnect')."</h6><small class='text-muted'>";

$listcontact = callDoliApi("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&includecount=1&sqlfilters=t.statut=1", null, dolidelay('contact', true));

$contactbilling = array(); 
if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) { 
foreach ($object->contacts_ids as $contact) {
if ('BILLING' == $contact->code) {
$contactbilling[] = $contact->id;
}}
}

print '<div class="form-check"><input type="checkbox" id="billing-0" name="contact_billing" class="form-check-input" value="0" ';
if (empty($contactbilling)) print ' checked ';
print 'disabled><label class="form-check-label" for="billing-0">'.doliaddress($thirdparty).'</label></div>';

if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) {
print '<div class="form-check"><input type="checkbox" id="billing-'.$contact->id.'" name="contact_billing" class="form-check-input" value="'.$contact->id.'" ';
if ( (isset($contact->default) && !empty($contact->default)) || in_array($contact->id, $contactbilling) ) { print "checked"; }
print ' disabled><label class="form-check-label" for="billing-'.$contact->id.'">';
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print '</label></div>';
}
}
print "</small></div>";

print "<div class='col-12 col-md-6'><h6>".__( 'Shipping address', 'doliconnect')."</h6><small class='text-muted'>";

$contactshipping = array(); 
if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {
foreach ($object->contacts_ids as $contact) {
if ('SHIPPING' == $contact->code) {
$contactshipping[] = $contact->id;
}}
}

print '<div class="form-check"><input type="checkbox" id="shipping-0" name="contact_shipping" class="form-check-input" value="0" ';
if (empty($contactshipping)) print ' checked ';
print 'disabled><label class="form-check-label" for="shipping-0">'.doliaddress($thirdparty).'</label></div>';

if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) {
print '<div class="form-check"><input type="checkbox" id="shipping-'.$contact->id.'" name="contact_shipping" class="form-check-input" value="'.$contact->id.'" ';
if ( (isset($contact->default) && !empty($contact->default)) || in_array($contact->id, $contactshipping) ) { print "checked"; }
print ' disabled><label class="form-check-label" for="shipping-'.$contact->id.'">';
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print '</label></div>';
}
}
print "</small></div></div></li>";

} else {
print "<li class='list-group-item list-group-item-info'><h6>".__( 'Billing address', 'doliconnect')."</h6>".doliaddress($thirdparty)."</li>";
}

if ( !empty(doliconst('MAIN_MODULE_FRAISDEPORT')) ) {
$listshipment = callDoliApi("GET", "/fraisdeport?modulepart=".$module."&id=1", null, dolidelay('order', true));
$shipping_method_id = $thirdparty->shipping_method_id;
if (!empty($object->shipping_method_id)) { $shipping_method_id = $object->shipping_method_id; }
if ( !isset($listshipment->error) && $listshipment != null ) {
print "<li class='list-group-item list-group-item-action'><h6>".__( 'Shipping method', 'doliconnect')."</h6>";
$i=0;
foreach ( $listshipment as $shipment ) {
if (isset($object->total_ht) && $object->total_ht >= $shipment->palier && !isset($controlefdp[$shipment->fk_shipment_mode])) {
print '<div class="form-check"><input type="radio" id="shipment-'.$shipment->id.'" name="shipping_method_id" class="form-check-input" value="'.$shipment->fk_shipment_mode.'" ';
if ( empty($i) || $shipping_method_id == $shipment->fk_shipment_mode ) { print " checked"; }
print '><label class="form-check-label" for="shipment-'.$shipment->id.'">'.dolishipmentmethods($shipment->fk_shipment_mode).' - '.doliprice($shipment, (empty(get_option('dolibarr_b2bmode'))?'price_ttc':'price_ht'));
if (!empty($shipment->description)) print ' <small>('.$shipment->description.')</small>';
print '</label></div>';
$controlefdp[$shipment->fk_shipment_mode] = true;
$i++;
}
}
print "</li>";
}
} else {
$listshipment = callDoliApi("GET", "/setup/dictionary/shipping_methods?limit=100&active=1", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$shipping_method_id = $thirdparty->shipping_method_id;
if (!empty($object->shipping_method_id)) { $shipping_method_id = $object->shipping_method_id; }
if ( !isset($listshipment->error) && $listshipment != null ) {
print "<li class='list-group-item list-group-item-action'><h6>".__( 'Shipping method', 'doliconnect')."</h6>";
foreach ( $listshipment as $shipment ) {
print '<div class="form-check"><input type="radio" id="shipment-'.$shipment->id.'" name="shipping_method_id" class="form-check-input" value="'.$shipment->id.'" ';
if ( $shipping_method_id == $shipment->id ) { print " checked"; }
print '><label class="form-check-label" for="shipment-'.$shipment->id.'">'.$shipment->label;
if (!empty($shipment->description)) print ' <small>('.$shipment->description.')</small>';
print '</label></div>';
$controlefdp[$shipment->id] = true;
}
print "</li>";
}
}

$note_public = isset($_POST['note_public']) ? $_POST['note_public'] : (isset($object->note_public) ? $object->note_public: null);

if ( empty(doliconst('MAIN_DISABLE_NOTES_TAB')) ) {
print "<li class='list-group-item list-group-item-action'>";
print '<div class="form-floating"><textarea class="form-control" placeholder="'.__( 'Message', 'doliconnect').'" id="note_public" name="note_public" style="height: 100px">'.$note_public.'</textarea>
<label for="floatingTextarea"><i class="fas fa-comment fa-fw"></i> '.__( 'If you want to send us a message about your order, you can leave one here', 'doliconnect').'</label></div>';
print "</li>";
} else {
print '<input type="hidden" id="note_public" name="note_public" value="'.$note_public.'">';
}

print "</ul>";

$nonce = wp_create_nonce( 'dolicart-nonce');
$arr_params = array( 'stage' => 'payment', 'security' => $nonce);  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
print "<script>";
print "(function ($) {
$(document).ready(function(){
$('#infobtn_cart').on('click',function(event){
event.preventDefault();
//$('#DoliconnectLoadingModal').modal('show');
var actionvalue = $(this).val();
var note_public = $('#note_public').val();
var shipping_method_id = $('input:radio[name=shipping_method_id]:checked').val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': actionvalue,
            'module': '".$module."',
            'id': '".$id."',
            'shipping_method_id': shipping_method_id,
            'note_public': note_public
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'info_cart') {
//$('#a-tab-info').removeClass('active');
//$('#a-tab-pay').removeClass('disabled');
//$('#a-tab-pay').addClass('active');    
//$('#nav-tab-info').removeClass('show active');
//$('#nav-tab-pay').addClass('show active');
//$('#nav-tab-info').tab('dispose');
//$('#nav-tab-pay').tab('show'); 
document.location = '".$return."';                                                                            
}

console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
print "</script>";

print "<div class='card-body'><div class='d-grid gap-2'><button type='button' id='infobtn_cart' name='info_cart' value='info_cart'  class='btn btn-secondary'>".__( 'Validate', 'doliconnect')."</button></div></div>";
print "<div class='card-footer text-muted'>";
print "<small><div class='float-start'>";
if ( isset($request) ) print dolirefresh($request, get_permalink(), dolidelay('cart'));
print "</div><div class='float-end'>";
print dolihelp('ISSUE');
print "</div></small>";
print "</div></div>";

print "</div>";

print '<div class="tab-pane fade';
if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { print ' show active'; }
print '" role="tabpanel" id="nav-tab-pay">';

if ( doliversion('11.0.0') ) {
$array = array();
if (isset($_GET["payment_intent"])) $array["payment_intent"] = $_GET["payment_intent"];
if (isset($_GET["payment_intent_client_secret"])) $array["payment_intent_client_secret"] = $_GET["payment_intent_client_secret"];
if (isset($_GET["redirect_status"])) $array["redirect_status"] = $_GET["redirect_status"];
print doliconnect_paymentmethods($object, esc_attr($module), $return, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $array);
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
