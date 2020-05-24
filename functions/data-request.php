<?php
/**
 * Data Request Handler.
 */

function doli_gdrf_data_request() {
	$gdrf_error     = array();
	$gdrf_type      = esc_html( filter_input( INPUT_POST, 'gdrf_data_type', FILTER_SANITIZE_STRING ) );
	$gdrf_email     = sanitize_email( $_POST['gdrf_data_email'] );
	$gdrf_human     = absint( filter_input( INPUT_POST, 'gdrf_data_human', FILTER_SANITIZE_NUMBER_INT ) );
	$gdrf_human_key = esc_html( filter_input( INPUT_POST, 'gdrf_data_human_key', FILTER_SANITIZE_STRING ) );
	$gdrf_numbers   = explode( '000', $gdrf_human_key );
	$gdrf_answer    = absint( $gdrf_numbers[0] ) + absint( $gdrf_numbers[1] );
	$gdrf_nonce     = esc_html( filter_input( INPUT_POST, 'gdrf_data_nonce', FILTER_SANITIZE_STRING ) );

	if ( ! function_exists( 'wp_create_user_request' ) ) {
		wp_send_json_success( esc_html__( 'The request can’t be processed on this website. This feature requires WordPress 4.9.6 at least.', 'doliconnect') );
		die();
	}

	if ( ! empty( $gdrf_email ) && ! empty( $gdrf_human ) ) {
		if ( ! wp_verify_nonce( $gdrf_nonce, 'gdrf_nonce' ) ) {
			$gdrf_error[] = esc_html__( 'Security check failed, please refresh this page and try to submit the form again.', 'doliconnect');
		} else {
			if ( ! is_email( $gdrf_email ) ) {
				$gdrf_error[] = esc_html__( 'This is not a valid email address.', 'doliconnect');
			}
			if ( intval( $gdrf_answer ) !== intval( $gdrf_human ) ) {
				$gdrf_error[] = esc_html__( 'Security check failed, invalid human verification field.', 'doliconnect');
			}
			if ( ! in_array( $gdrf_type, array( 'export_personal_data', 'remove_personal_data' ) ) ) {
				$gdrf_error[] = esc_html__( 'Request type invalid, please refresh this page and try to submit the form again.', 'doliconnect');
			}
		}
	} else {
		$gdrf_error[] = esc_html__( 'All fields are required.', 'doliconnect');
	}
	if ( empty( $gdrf_error ) ) {
		$request_id = wp_create_user_request( $gdrf_email, $gdrf_type );
		if ( is_wp_error( $request_id ) ) {
			wp_send_json_success( $request_id->get_error_message() );
		} elseif ( ! $request_id ) {
			wp_send_json_success( esc_html__( 'Unable to initiate confirmation request. Please contact the administrator.', 'doliconnect') );
		} else {
			$send_request = wp_send_user_request( $request_id );
			wp_send_json_success( 'success' );
		}
	} else {
		wp_send_json_success( join( '<br />', $gdrf_error ) );
	}
	die();
}

add_action( 'wp_ajax_doli_gdrf_data_request', 'doli_gdrf_data_request' );
add_action( 'wp_ajax_nopriv_doli_gdrf_data_request', 'doli_gdrf_data_request' );



add_action('wp_ajax_doliaddproduct_request', 'doliaddproduct_request');
add_action('wp_ajax_nopriv_doliaddproduct_request', 'doliaddproduct_request');

function doliaddproduct_request(){
global $current_user;
		
if ( wp_verify_nonce( trim($_POST['product-add-nonce']), 'product-add-nonce-'.trim($_POST['product-add-id']) ) ) {
//if ( $_POST['cartaction'] == 'addtocart') {
$result = doliaddtocart(trim($_POST['product-add-id']), trim($_POST['product-add-qty']), trim($_POST['product-add-price']), trim($_POST['product-add-remise_percent']), isset($_POST['product-add-timestamp_start'])?trim($_POST['product-add-timestamp_start']):null, isset($_POST['product-add-timestamp_end'])?trim($_POST['product-add-timestamp_end']):null);
if ($result >= 0) {
wp_send_json_success( $result ); 
} else {
$response = [
    'message' => __( 'We no longer have this item in this quantity', 'doliconnect'),
        ];
wp_send_json_error( $response ); 
}
//}	else {
//$response = [
//    'message' => __( 'Wish disabled', 'doliconnect').$_POST['cartaction'],
//        ];
//wp_send_json_error( $response ); 
//}
}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolisettings_request', 'dolisettings_request');
//add_action('wp_ajax_nopriv_dolisettings_request', 'dolisettings_request');

function dolisettings_request(){
global $current_user;
$ID = $current_user->ID;

if ( wp_verify_nonce( trim($_POST['dolisettings-nonce']), 'dolisettings-nonce') ) {
if ( isset($_POST['loginmailalert'])) { update_user_meta( $ID, 'loginmailalert', sanitize_text_field($_POST['loginmailalert']) ); } else { delete_user_meta($ID, 'loginmailalert'); }
if ( isset($_POST['optin1'])) { update_user_meta( $ID, 'optin1', sanitize_text_field($_POST['optin1']) ); } else { delete_user_meta($ID, 'optin1'); }
if ( isset($_POST['optin2'])) { update_user_meta( $ID, 'optin2', sanitize_text_field($_POST['optin2']) ); } else { delete_user_meta($ID, 'optin2'); }
if ( isset($_POST['locale']) ) { update_user_meta( $ID, 'locale', sanitize_text_field($_POST['locale']) ); }  
//if (isset($_POST['multicurrency_code'])) { update_user_meta( $ID, 'multicurrency_code', sanitize_text_field($_POST['multicurrency_code']) ); }

if ( doliconnector($current_user, 'fk_soc') > 0 ) {

$info = array(
      'default_lang' => isset($_POST['locale'])?sanitize_text_field($_POST['locale']):null,
      'multicurrency_code' => isset($_POST['multicurrency_code'])?sanitize_text_field($_POST['multicurrency_code']):$monnaie,
            );
$thirparty = callDoliApi("PUT", "/thirdparties/".doliconnector($current_user, 'fk_soc'), $info, dolidelay('thirdparty', true));
}
		
wp_send_json_success('success');
}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolifpw_request', 'dolifpw_request');
add_action('wp_ajax_nopriv_dolifpw_request', 'dolifpw_request');

function dolifpw_request(){
if ( wp_verify_nonce( trim($_POST['dolifpw-nonce']), 'dolifpw-nonce') ) { 
if (isset($_POST['user_email']) && email_exists(sanitize_email($_POST['user_email'])) ) {
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
    wp_send_json_error( __( 'Reset password is not permitted for this account!', 'doliconnect')); 
} elseif ( !empty($key) ) { 
		$sitename = get_option('blogname');
    $siteurl = get_option('siteurl');
    $subject = "[$sitename] ".__( 'Reset Password', 'doliconnect');
    $body = __( 'A request to change your password has been made. You can change it via the single-use link below:', 'doliconnect')."<br><br><a href='".$url."'>".$url."</a><br><br>".__( 'If you have not made this request, please ignore this email.', 'doliconnect')."<br><br>".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br>$siteurl";				
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $mail =  wp_mail($email, $subject, $body, $headers);

if( $mail ) {
wp_send_json_success( __( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect'));
} else { 
wp_send_json_error( __( 'A problem occurred. Please retry later!', 'doliconnect'));  
}		
} else { 
wp_send_json_error( __( 'A problem occurred. Please retry later!', 'doliconnect'));		
}
} else {
wp_send_json_error( __( 'No account seems to be linked to this email address', 'doliconnect'));
}
}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolirpw_request', 'dolirpw_request');
add_action('wp_ajax_nopriv_dolirpw_request', 'dolirpw_request');

function dolirpw_request(){
global $wpdb; 

if ( wp_verify_nonce( trim($_POST['dolirpw-nonce']), 'dolirpw-nonce')) {
$pwd0 = sanitize_text_field($_POST["pwd0"]);
$pwd1 = sanitize_text_field($_POST["pwd1"]);
$pwd2 = sanitize_text_field($_POST["pwd2"]);

if (!is_user_logged_in()) {
$current_user = check_password_reset_key( esc_attr($_POST["key"]), esc_attr($_POST["login"]) );
} else {
global $current_user;
}

if ( ((isset($pwd0) && !empty($pwd0) && is_user_logged_in() && wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID )) || (!is_user_logged_in()) ) && ($pwd1 == $pwd2) && (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,20}/', $pwd1)) ) {
wp_set_password($pwd1, $current_user->ID);

if (doliconnector($current_user, 'fk_user') > 0){
$data = [
    'pass' => $pwd1
	];
$doliuser = callDoliApi("PUT", "/users/".doliconnector($current_user, 'fk_user'), $data, 0);
}

if (!is_user_logged_in()) {
$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $user->user_login ) );
}

wp_send_json_success('success'); 
} elseif (is_user_logged_in() && isset( $current_user->ID ) && ! wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ) ) {
wp_send_json_error( __( 'Your actual password is incorrect', 'doliconnect'));
} elseif ( $pwd1 != $_POST["pwd2"] ) {
wp_send_json_error( __( 'The new passwords entered are different', 'doliconnect'));
} elseif ( !preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd1) ) {
wp_send_json_error( __( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect'));
}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolipaymentmethod_request', 'dolipaymentmethod_request');
//add_action('wp_ajax_nopriv_dolipaymentmethod_request', 'dolipaymentmethod_request');

function dolipaymentmethod_request(){
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods"; 

if ( wp_verify_nonce( trim($_POST['dolipaymentmethod-nonce']), 'dolipaymentmethod-nonce')) {

if ( isset($_POST['action_payment_method']) && $_POST['action_payment_method'] == "default_payment_method") {

$data = [
'default' => 1
];
$object = callDoliApi("PUT", $request."/".sanitize_text_field($_POST['payment_method']), $data, dolidelay( 0, true));

if (!isset($object->error)) {  
$gateway = callDoliApi("GET", $request, null, dolidelay('paymentmethods', true));
wp_send_json_success(__( 'You changed your default payment method', 'doliconnect'));
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} elseif ( isset($_POST['action_payment_method']) && $_POST['action_payment_method'] == "delete_payment_method") {

$object = callDoliApi("DELETE", $request."/".sanitize_text_field($_POST['payment_method']), null, dolidelay( 0, true));

if (!isset($object->error)) {
$gateway = callDoliApi("GET", $request, null, dolidelay('paymentmethods', true));
wp_send_json_success(__( 'You deleted a payment method', 'doliconnect'));
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} elseif ( isset($_POST['action_payment_method']) && $_POST['action_payment_method'] == "add_payment_method") {

$data = [
'default' => isset($_POST['default'])?$_POST['default']:0,
];

$object = callDoliApi("POST", $request."/".sanitize_text_field($_POST['payment_method']), $data, dolidelay( 0, true));

if (!isset($object->error)) { 
$gateway = callDoliApi("GET", $request, null, dolidelay('paymentmethods', true));
wp_send_json_success(__( 'You added a new payment method', 'doliconnect'));
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} else {
wp_send_json_error( __( 'An error occured', 'doliconnect')); 
}

}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolicart_request', 'dolicart_request');
//add_action('wp_ajax_nopriv_dolicart_request', 'dolicart_request');

function dolicart_request(){
global $current_user;

if ( wp_verify_nonce( trim($_POST['dolicart-nonce']), 'dolicart-nonce')) {

if ( isset($_POST['action_cart']) && $_POST['action_cart'] == "purge_cart") {
$object = callDoliApi("DELETE", "/".trim($_POST['module'])."/".trim($_POST['id']), null);

if (!isset($object->error)) { 
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
$response = [
    'items' => '0',
    'lines' => doliline(null, null),
    'message' => __( 'Your cart has been emptied', 'doliconnect'),
        ];
wp_send_json_success($response);
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} elseif ( isset($_POST['action_cart']) && $_POST['action_cart'] == "update_cart") {

//foreach ( $_POST['updateorderproduct'] as $productupdate ) {
$update = doliaddtocart($_POST['productid'], $_POST['qty'], $_POST['price'], $_POST['remise_percent'], $_POST['date_start'], $_POST['date_end']);
//print var_dump($_POST['updateorderproduct']);
//}
//doliconnector($current_user, 'fk_order', true);
$object = callDoliApi("GET", "/".trim($_POST['module'])."/".trim($_POST['id']), null, dolidelay('order', true));

//if (!isset($object->error)) {
$response = [
    'items' => '0',
    'lines' => doliline($object, true),
    'total' => 'test',
    'message' => __( 'Quantities have been changed', 'doliconnect'),
        ];
//wp_send_json_success($response);
//} else {
//wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
//}

} elseif ( isset($_POST['action_cart']) && $_POST['action_cart'] == "validate_cart") {

$data = [
    'date_modification' => mktime(),
    'demand_reason_id' => 1,
    'module_source' => 'doliconnect',
    'pos_source' => get_current_blog_id(),
	];                 
$object = callDoliApi("PUT", "/".trim($_POST['module'])."/".trim($_POST['id']), $data, dolidelay('order', true));

if (!isset($object->error)) {
$response = [
    'message' => __( 'Your cart has been validated', 'doliconnect'),
        ];
wp_send_json_success($response);
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}
} elseif ( isset($_POST['action_cart']) && $_POST['action_cart'] == "info_cart") {

$data = [
    'date_modif' => mktime(),
    'demand_reason_id' => 1,
    'module_source' => 'doliconnect',
    'pos_source' => get_current_blog_id(),
    'note_public' => $_POST['note_public'],
	];                 
$object = callDoliApi("PUT", "/".trim($_POST['module'])."/".trim($_POST['id']), $data, dolidelay('order', true));

if ($_POST['contact_shipping']) {
$shipping= callDoliApi("POST", "/".trim($_POST['module'])."/".trim($_POST['id'])."/contact/".$_POST['contact_shipping']."/SHIPPING", null, dolidelay('order', true));
}

//if ( doliversion('11.0.0') ) {
//if ( current_user_can('administrator') && !empty(get_option('doliconnectbeta')) ) {
//$content = doliconnect_paymentmethods($object, substr(trim($_POST['module']), 0, -1), null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
//} else {
//$content = dolipaymentmethods($object, substr(trim($_POST['module']), 0, -1), null, true);
//}
//} else {
//$content = __( "It seems that your version of Dolibarr and/or its plugins are not up to date!", "doliconnect");
//}
if (!isset($object->error)) {
$response = [
    //'content' => '".$content."',
    'message' => 'ok',
        ];
wp_send_json_success($response);
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}
} elseif ( isset($_POST['action_cart']) && $_POST['action_cart'] == "pay_cart") {

$data = [
  'paymentintent' => isset($_POST['paymentintent']) ? $_POST['paymentintent'] : null,
  'paymentmethod' => isset($_POST['paymentmethod']) ? $_POST['paymentmethod'] : null,
  'save' => isset($_POST['default']) ? $_POST['default'] : 0 ,
	];
$payinfo = callDoliApi("POST", "/doliconnector/pay/".trim($_POST['module'])."/".trim($_POST['id']), $data, 0);
//print var_dump($payinfo);

if (!isset($payinfo->error)) { 
doliconnector($current_user, 'fk_order', true);
$object = callDoliApi("GET", "/".trim($_POST['module'])."/".trim($_POST['id'])."?contact_list=0", null, dolidelay('cart', true));
$mode_reglement = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'".$payinfo->mode_reglement_code."')", null, dolidelay('constante'));
$message = '<div class="card"><div class="card-body"><center><i class="fas fa-check-circle fa-9x fa-fw text-success"></i>
<p class="card-text"><h2>'.__( 'Your order has been registered', 'doliconnect').'</h2>'.__( 'Reference', 'doliconnect').': '.$payinfo->ref.'<br>'.__( 'Payment method', 'doliconnect').': '.$mode_reglement[0]->label.'</p>';
$nonce = wp_create_nonce( 'doli-'.trim($_POST['module']).'-'. trim($_POST['id']).'-'.$payinfo->ref);
$arr_params = array('module' => trim($_POST['module']), 'id' => trim($_POST['id']), 'ref' => $payinfo->ref, 'security' => $nonce);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );
$message .= "<br><a href='".$return."' class='btn btn-primary'>".__( 'View my receipt', 'doliconnect')."</a>";
$message .= '</center></div></div>';
wp_send_json_success( $message ); 
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$payinfo->error->message); 
}

} else {
wp_send_json_error( __( 'An error occured', 'doliconnect')); 
}

}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolisignup_request', 'dolisignup_request');
add_action('wp_ajax_nopriv_dolisignup_request', 'dolisignup_request');

function dolisignup_request(){
global $current_user;
		
if ( wp_verify_nonce( trim($_POST['dolisignup-nonce']), 'dolisignup-nonce') ) {
$doliuser = callDoliApi("GET", "/thirdparties?sortfield=t.code_client&sortorder=ASC&limit=1&sqlfilters=(t.code_client%3A%3D%3A'".trim($_POST['code_client'])."')", null, 0);

if (isset($doliuser->error->message)) {
wp_send_json_error( __( 'Customer not found', 'doliconnect')); 
} else {
$order = callDoliApi("GET", "/orders/ref/".trim($_POST['reference'])."?contact_list=1", null, 0);
$invoice = callDoliApi("GET", "/invoices?sortfield=t.ref&sortorder=ASC&limit=1&thirdparty_ids=".$doliuser[0]->id."&sqlfilters=(t.ref%3Alike%3A'".trim($_POST['reference'])."')%20and%20(t.multicurrency_total_ttc%3Alike%3A'".number_format(trim($_POST['amount']), 0, '.', '')."%25')", null, 0);
if (!isset($invoice->error->message) || (!isset($order->error->message) && $order->socid == $doliuser[0]->id && preg_match('/'.number_format(trim($_POST['amount']).'/i', 0, '.', '').'/', $order->multicurrency_total_ttc))) {
 wp_send_json_success( 'success'); 
} else {
wp_send_json_error( __( 'Customer not found', 'doliconnect')); 
}
}
}	else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}
