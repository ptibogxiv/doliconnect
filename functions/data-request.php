<?php
/**
 * Data Request Handler.
 *
 * @link       http://jeanbaptisteaudras.com
 * @since      1.0
 *
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

$result = doliaddtocart(trim($_POST['product-add-id']), trim($_POST['product-add-qty']), trim($_POST['product-add-price']), trim($_POST['product-add-remise_percent']), isset($_POST['product-add-timestamp_start'])?trim($_POST['product-add-timestamp_start']):null, isset($_POST['product-add-timestamp_end'])?trim($_POST['product-add-timestamp_end']):null);
wp_send_json_success( $result ); 

} else {
wp_send_json_error('security error'); 
}
}  


add_action('wp_ajax_dolisettings_request', 'dolisettings_request');
add_action('wp_ajax_nopriv_dolisettings_request', 'dolisettings_request');

function dolisettings_request(){
global $current_user;
$ID = $current_user->ID;

if ( wp_verify_nonce( trim($_POST['dolisettings-nonce']), 'dolisettings-nonce') ) {
if ( isset($_POST['loginmailalert'])) { update_user_meta( $ID, 'loginmailalert', sanitize_text_field($_POST['loginmailalert']) ); } else { delete_user_meta($ID, 'loginmailalert'); }
if ( isset($_POST['optin1'])) { update_user_meta( $ID, 'optin1', sanitize_text_field($_POST['optin1']) ); } else { delete_user_meta($ID, 'optin1'); }
if ( isset($_POST['optin2'])) { update_user_meta( $ID, 'optin2', sanitize_text_field($_POST['optin2']) ); } else { delete_user_meta($ID, 'optin2'); }
if ( isset($_POST['locale']) ) { update_user_meta( $ID, 'locale', sanitize_text_field($_POST['locale']) ); }  
//if (isset($_POST['multicurrency_code'])) {vupdate_user_meta( $ID, 'multicurrency_code', sanitize_text_field($_POST['multicurrency_code']) );v}

if ( doliconnector($current_user, 'fk_soc') > 0 ) {

$info = array(
      'default_lang' => isset($_POST['locale'])?sanitize_text_field($_POST['locale']):null,
      'multicurrency_code' => isset($_POST['multicurrency_code'])?sanitize_text_field($_POST['multicurrency_code']):$monnaie,
            );
$thirparty = callDoliApi("PUT", "/thirdparties/".doliconnector($current_user, 'fk_soc'), $info, dolidelay('thirdparty', true));
}
		
wp_send_json_success('success');
} else wp_send_json_error('security error'); 
 
}

add_action('wp_ajax_dolifpw_request', 'dolifpw_request');
add_action('wp_ajax_nopriv_dolifpw_request', 'dolifpw_request');

function dolifpw_request(){
if ( wp_verify_nonce( trim($_POST['dolifpw-nonce']), 'dolifpw-nonce') && isset($_POST['user_email']) && email_exists(sanitize_email($_POST['user_email'])) ) {
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
      wp_send_json_success('error'); 
} elseif ( !empty($key) ) { 
			$sitename = get_option('blogname');
      $siteurl = get_option('siteurl');
      $subject = "[$sitename] ".__( 'Reset Password', 'doliconnect');
      $body = __( 'A request to change your password has been made. You can change it via the single-use link below:', 'doliconnect')."<br><br><a href='".$url."'>".$url."</a><br><br>".__( 'If you have not made this request, please ignore this email.', 'doliconnect')."<br><br>".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br>$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($email, $subject, $body, $headers);

if( $mail ) { wp_send_json_success('success');  } else { wp_send_json_error('error');  }		
}

}	 else {
wp_send_json_error('security error'); 
}
}

add_action('wp_ajax_dolirpw_request', 'dolirpw_request');
add_action('wp_ajax_nopriv_dolirpw_request', 'dolirpw_request');

function dolirpw_request(){
global $current_user;

if ( wp_verify_nonce( trim($_POST['dolirpw-nonce']), 'dolirpw-nonce')) {
$pwd0 = sanitize_text_field($_POST["pwd0"]);
$pwd1 = sanitize_text_field($_POST["pwd1"]);
$pwd2 = sanitize_text_field($_POST["pwd2"]);

if ( (isset($pwd0) && !empty($pwd0) && wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ) ) && ($pwd1 == $pwd2) && (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,20}/', $pwd1)) ) {
wp_set_password($pwd1, $current_user->ID);

if (doliconnector($current_user, 'fk_user') > '0'){
$data = [
    'pass' => $pwd1
	];
$doliuser = callDoliApi("PUT", "/users/".doliconnector($current_user, 'fk_user'), $data, 0);
}

wp_send_json_success('success'); 
} elseif (isset( $current_user->ID ) && ! wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ) ) {
wp_send_json_error(__( 'Your actual password is incorrect', 'doliconnect'));
} elseif ( $pwd1 != $_POST["pwd2"] ) {
wp_send_json_error(__( 'The new passwords entered are different', 'doliconnect'));
} elseif ( !preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd1) ) {
wp_send_json_error(__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect'));
}

}	 else {
wp_send_json_error('security error', 'doliconnect'); 
}
}
