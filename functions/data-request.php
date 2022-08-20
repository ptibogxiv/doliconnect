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
			wp_send_json_error( $request_id->get_error_message() );
		} elseif ( ! $request_id ) {
			wp_send_json_error( esc_html__( 'Unable to initiate confirmation request. Please contact the administrator.', 'doliconnect') );
		} else {
			$send_request = wp_send_user_request( $request_id );
			wp_send_json_success( 'success' );
		}
	} else {
		wp_send_json_error( join( '<br />', $gdrf_error ) );
	}
	die();
}

add_action( 'wp_ajax_doli_gdrf_data_request', 'doli_gdrf_data_request' );
add_action( 'wp_ajax_nopriv_doli_gdrf_data_request', 'doli_gdrf_data_request' );

add_action('wp_ajax_doliproduct_request', 'doliproduct_request');
add_action('wp_ajax_nopriv_doliproduct_request', 'doliproduct_request');

function doliproduct_request(){
global $current_user;
		
if ( wp_verify_nonce( trim($_POST['product-add-nonce']), 'product-add-nonce-'.trim($_POST['product-add-id']) ) ) {
$result = doliaddtocart(trim($_POST['product-add-id']), trim($_POST['product-add-qty']), trim($_POST['product-add-price']), trim($_POST['product-add-remise_percent']), isset($_POST['product-add-timestamp_start'])?trim($_POST['product-add-timestamp_start']):null, isset($_POST['product-add-timestamp_end'])?trim($_POST['product-add-timestamp_end']):null);
if ($result >= 0) {
$response = [
    'message' => '<div class="alert alert-success alert-dismissible d-flex align-items-center" role="alert">'.__( 'As our inventory is updated in real time, your items have been put in the basket for 1 hour', 'doliconnect').'</div>',
    'items' => $result,
    'list' => doliconnect_CartItemsList()
        ];
wp_send_json_success( $response ); 
} else {
$response = [
    'message' => '<div class="alert alert-danger alert-dismissible d-flex align-items-center" role="alert">'.__( 'We no longer have this item in this quantity', 'doliconnect').'</div>',
        ];
wp_send_json_error( $response ); 
}
}	elseif ( wp_verify_nonce( trim($_POST['product-wish-nonce']), 'product-wish-nonce-'.trim($_POST['product-wish-id']) ) ) {
$response = [
    'message' => '<i class="fas fa-heart-broken" style="color:Fuchsia"></i>',
    'items' => $result,
    'list' => doliconnect_CartItemsList()
        ];
wp_send_json_success( $response ); 
} else {
$response = [
    'message' => '<div class="alert alert-danger alert-dismissible d-flex align-items-center" role="alert">'.__( 'A security error occured', 'doliconnect').'</div>',
        ];
wp_send_json_error( $response ); 
}
}

add_action('wp_ajax_doliselectform_request', 'doliselectform_request');
add_action('wp_ajax_nopriv_doliselectform_request', 'doliselectform_request');

function doliselectform_request(){

if (isset($_POST['case']) && $_POST['case'] == "update" ) {	
	$response = array();
	if (isset($_POST['legalformId'])) $response['state_id'] = doliSelectForm("state_id", "/setup/dictionary/states?sortfield=code_departement&sortorder=ASC&limit=500&country=".$_POST['countryId'], __( '- Select your state -', 'doliconnect'), __( 'State', 'doliconnect'), $_POST['stateId'], $_POST['objectId'], $_POST['rights'], $_POST['delay']);
	if (isset($_POST['legalformId'])) $response['forme_juridique_code'] = doliSelectForm("forme_juridique_code", "/setup/dictionary/legal_form?sortfield=libelle&sortorder=ASC&active=1&limit=500&country=".$_POST['countryId'], __( '- Select your legal form -', 'doliconnect'), __( 'Legal form', 'doliconnect'), $_POST['legalformId'], $_POST['objectId'], $_POST['rights'], $_POST['delay'], 'code');
	$response['ziptown'] = doliSelectForm("ziptown", "/setup/dictionary/towns?sortfield=town&sortorder=ASC&active=1&limit=1000&sqlfilters=(t.fk_pays%3A%3D%3A'".$_POST['countryId']."')%20AND%20(t.fk_county%3A%3D%3A'".$_POST['stateId']."')", __( '- Select your town -', 'doliconnect'), __( 'Town', 'doliconnect'), $_POST['ziptownId'], $_POST['objectId'], $_POST['rights'], $_POST['delay']);
	$response['profids'] = doliProfId($_POST['idprof1'], $_POST['idprof2'], $_POST['idprof3'], $_POST['idprof4'], $_POST['country_code'], $_POST['objectId'], $_POST['rights']);
	wp_send_json_success( $response );
} else {
	wp_send_json_error( dolialert('danger', __( 'A security error occured', 'doliconnect'))); 
}

}
add_action('wp_ajax_doliuserinfos_request', 'doliuserinfos_request');
add_action('wp_ajax_nopriv_doliuserinfos_request', 'doliuserinfos_request');

function doliuserinfos_request(){
	global $current_user;
	$ID = $current_user->ID;
	
	if ( isset($_POST['doliuserinfos-nonce']) && wp_verify_nonce( trim($_POST['doliuserinfos-nonce']), 'doliuserinfos') && isset($_POST['case']) && $_POST['case'] == "update" ) {

		$thirdparty=$_POST['thirdparty'][''.doliconnector($current_user, 'fk_soc').''];
		$thirdparty = dolisanitize($thirdparty);
		//$thirdparty['no_email'] = !$thirdparty['no_email'];
		
		wp_update_user( array( 'ID' => $ID,
		'user_email' => $thirdparty['email'],
		'nickname' => sanitize_user($_POST['user_nicename']),
		'first_name' => $thirdparty['firstname'],
		'last_name' => $thirdparty['lastname'],
		'description' => $thirdparty['note_public'],
		'user_url' => $thirdparty['url'],
		'display_name' => $thirdparty['name']));
		update_user_meta( $ID, 'civility_code', sanitize_text_field($thirdparty['civility_code']));
		update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
		if ( $thirdparty['morphy'] == 'mor' ) { update_user_meta( $ID, 'billing_company', $thirdparty['name']); }
		update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);
		if ( isset($thirdparty['locale']) ) { update_user_meta( $ID, 'locale', sanitize_text_field($thirdparty['locale']) ); }  
		
		do_action('wp_dolibarr_sync', $thirdparty);
		$response = [
			'message' => dolialert('success', __( 'Your informations have been updated.', 'doliconnect')),
			'captcha' => dolicaptcha('doliuserinfos'),
				];
		wp_send_json_success( $response );
	} elseif ( isset($_POST['doliuserinfos-nonce']) && wp_verify_nonce( trim($_POST['doliuserinfos-nonce']), 'doliuserinfos') && isset($_POST['case']) && $_POST['case'] == "create" ) {

		$thirdparty=$_POST['thirdparty'];
		$thirdparty = dolisanitize($thirdparty);
		//$thirdparty['no_email'] = !$thirdparty['no_email'];
		$UserError = array();

		if (email_exists($thirdparty['email'])) {
			$UserError[] = __( 'This email address is already linked to an account. You can reactivate your account through this <a href=\'".wp_lostpassword_url( get_permalink() )."\' title=\'lost password\'>form</a>.', 'doliconnect');
		} else {
			$email = sanitize_email($thirdparty['email']);
			$domainemail = explode("@", $email)[1];
		}
		
		if (defined("DOLICONNECT_SELECTEDEMAIL") && is_array(constant("DOLICONNECT_SELECTEDEMAIL")) && !in_array($domainemail, constant("DOLICONNECT_SELECTEDEMAIL"))) {
			$UserError[] = esc_html__( 'Only emails from selected domains are allowed', 'doliconnect'); 
		}
	
		if ($thirdparty['firstname'] == $_POST['user_nicename'] && $thirdparty['firstname'] == $thirdparty['lastname']) {
			$UserError[] = esc_html__( 'Create this account is not permitted', 'doliconnect');       
		}
		
		if ( !isset($_POST['btndolicaptcha']) || empty(wp_verify_nonce( $_POST['ctrldolicaptcha'], 'ctrldolicaptcha-'.$_POST['btndolicaptcha'])) ) {
			$UserError[] = esc_html__( 'Security check failed, invalid human verification field.', 'doliconnect');
		}
			  
		if ( defined("DOLICONNECT_DEMO") ) {
			$UserError[] = esc_html__( 'Create account is not permitted because the demo mode is active', 'doliconnect');       
		}
	
		if ( empty($UserError) ) {
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
		
		if ( is_wp_error( $ID )) {
			$response = [
				'message' => dolialert('error', sprintf(__('Creating your account fails for the following reason: %s. Please contact us for help.', 'doliconnect'), $ID->get_error_message()) ),
				'captcha' => dolicaptcha('doliuserinfos'),
					];
			wp_send_json_error( $response );	
			die();		   
		} 

		$role = get_option( 'default_role' );
		
		if ( is_multisite() ) {
		$entity = dolibarr_entity(); 
		add_user_to_blog($entity,$ID,$role);
		} else {
		$user = get_user_by( 'ID', $ID);
		$user->set_role($role);
		}
		
		wp_update_user( array( 'ID' => $ID,
		'user_email' => $thirdparty['email'],
		'nickname' => sanitize_user($_POST['user_nicename']),
		'first_name' => $thirdparty['firstname'],
		'last_name' => $thirdparty['lastname'],
		'description' => $thirdparty['note_public'],
		'user_url' => $thirdparty['url'],
		'display_name' => $thirdparty['name']));
		update_user_meta( $ID, 'civility_code', sanitize_text_field($thirdparty['civility_code']));
		update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
		if ( $thirdparty['morphy'] == 'mor' ) { update_user_meta( $ID, 'billing_company', $thirdparty['name']); }
		update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);
		if ( isset($_POST['optin1']) ) { update_user_meta( $ID, 'optin1', $_POST['optin1'] ); }
		
		$body = sprintf(__('Thank you for your registration on %s.', 'doliconnect'), $sitename);
		$user = get_user_by( 'ID', $ID);
		$key = get_password_reset_key($user);
		$arr_params = array( 'action' => 'rpw', 'key' => $key, 'login' => $user->user_login);  
		$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );
		
		$body .= "<br><br>".__('To activate your account on and choose your password, please click on the following link', 'doliconnect').":<br><br><a href='".$url."'>".$url."</a>";
		$body .= "<br><br>".sprintf(__("Your %s's team", 'doliconnect'), $sitename)."<br>".get_option('siteurl');
		
		if ( has_filter( 'doliconnect_templatesignupemail') ) {
		if (!empty(apply_filters( 'doliconnect_templatesignupemail', $sitename, $url))){
		$body = apply_filters( 'doliconnect_templatesignupemail', $sitename, $url);
		}
		}
		
		$headers = array('Content-Type: text/html; charset=UTF-8'); 
		$emailSent = wp_mail($email, $subject, $body, $headers);

		if ( !is_wp_error( $emailSent ) && ($thirdparty['morphy'] == 'mor' && $user) || (function_exists('dolikiosk') && ! empty(dolikiosk()) && $user) ) {  
		
			$dolibarrid = doliconnector($user, 'fk_soc', true, $thirdparty);
			do_action('wp_dolibarr_sync', $thirdparty, $user);
			
			wp_set_current_user( $ID, $user->user_login );
			wp_set_auth_cookie( $ID, false);
			do_action( 'wp_login', $user->user_login, $user);
			$response = [
				'message' => dolialert('success', __( "Your account has been created. Now, you are connected", 'doliconnect')),
				'captcha' => dolicaptcha('doliuserinfos'),
					];
			wp_send_json_success( $response );	
			die();		   	 
		} elseif ( !is_wp_error( $emailSent )) {
			$response = [
				'message' => dolialert('success', __( "Your account has been created and an account activation link has been sent by email. Don't forget to look at your unwanted emails if you can't find our message.", 'doliconnect')),
				'captcha' => dolicaptcha('doliuserinfos'),
					];
			wp_send_json_success( $response );
		} else {
			$response = [
				'message' => dolialert('danger', __( 'Your account has been created but sending an activation link by email fails. Please contact us.', 'doliconnect')),
				'captcha' => dolicaptcha('doliuserinfos'),
					];
			wp_send_json_error( $response ); 
			die();
		}

		} else {
			$response = [
				'message' => dolialert('danger', join( '<br />', $UserError )),
				'captcha' => dolicaptcha('doliuserinfos'),
					];
			wp_send_json_error( $response );
			die();
		}
	
	} else {
		$response = [
			'message' => dolialert('danger', __( 'A security error occured', 'doliconnect')),
			'captcha' => dolicaptcha('doliuserinfos'),
				];
		wp_send_json_error( $response ); 
	}
}

add_action('wp_ajax_dolicontactinfos_request', 'dolicontactinfos_request');
//add_action('wp_ajax_nopriv_dolicontactinfos_request', 'dolicontactinfos_request');

function dolicontactinfos_request(){
	global $current_user;
	$ID = $current_user->ID;
	
	if ( isset($_POST['dolicontactinfos-nonce']) && wp_verify_nonce( trim($_POST['dolicontactinfos-nonce']), 'dolicontactinfos') && isset($_POST['case']) && $_POST['case'] == "update" ) {

		$contact = $_POST['contact'][''.$_POST['contactid'].''];
		$contact = dolisanitize($contact);
		$contact['no_email'] = !$contact['no_email'];
		$object = callDoliApi("PUT", "/contacts/".$_POST["contactid"]."?includecount=1&includeroles=1", $contact, 0);
		
		if (!isset($object->error)) { 
			$response = [
				'message' => dolialert('success', __( 'Your informations have been updated.', 'doliconnect')),
				'captcha' => dolicaptcha('dolicontactinfos'),
					];
			wp_send_json_success( $response );
		} else {
			$response = [
				'message' => __( 'An error occured:', 'doliconnect').' '.$object->error->message,
				'captcha' => dolicaptcha('dolicontactinfos'),
					];
			wp_send_json_error( $response ); 
		}

	} elseif ( isset($_POST['dolicontactinfos-nonce']) && wp_verify_nonce( trim($_POST['dolicontactinfos-nonce']), 'dolicontactinfos') && isset($_POST['case']) && $_POST['case'] == "create" ) {

		$contact = $_POST['contact'][''.doliconnector($current_user, 'fk_soc').''];
		$contact = dolisanitize($contact);
		$contact['socid'] = doliconnector($current_user, 'fk_soc');
		$contact['no_email'] = !$contact['no_email'];
		$object = callDoliApi("POST", "/contacts", $contact, 0);
		
		if (!isset($object->error)) { 
			$response = [
				'message' => dolialert('success', __( 'Your informations have been added.', 'doliconnect')),
				'captcha' => dolicaptcha('dolicontactinfos'),
					];
			wp_send_json_success( $response );
		} else {
			$response = [
				'message' => __( 'An error occured:', 'doliconnect').' '.$object->error->message,
				'captcha' => dolicaptcha('dolicontactinfos'),
					];
			wp_send_json_error( $response ); 
		}
	
	} else {
		$response = [
			'message' => dolialert('danger', __( 'A security error occured', 'doliconnect')),
			'captcha' => dolicaptcha('doliuserinfos'),
				];
		wp_send_json_error( $response ); 
	}
}

add_action('wp_ajax_dolicontact_request', 'dolicontact_request');
add_action('wp_ajax_nopriv_dolicontact_request', 'dolicontact_request');

function dolicontact_request(){
	global $current_user;
	$ID = $current_user->ID;
	
	if ( wp_verify_nonce( trim($_POST['dolicontact-nonce']), 'dolicontact') ) {
			$ContactError = array();
		if ( sanitize_text_field($_POST['contactName']) === '' ) {
			$ContactError[] = esc_html__( 'Please enter your name.', 'doliconnect');
		} else {
			$name = sanitize_text_field($_POST['contactName']);
		}
	
		if ( sanitize_email($_POST['email']) === '' )  {
			$ContactError[] = esc_html__( 'Please enter you email.', 'doliconnect');
		} elseif (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", sanitize_email($_POST['email']))) {
			$ContactError = 'You entered an invalid email address.';
			$hasError = true;
		} else {
			$email = sanitize_email($_POST['email']);
		}
	
		if( sanitize_textarea_field($_POST['comments']) === '') {
			$ContactError[] = esc_html__( 'A message is needed.', 'doliconnect');
		} else {
			$comments = sanitize_textarea_field($_POST['comments']);
		}
		
		if ( !isset($_POST['btndolicaptcha']) || empty(wp_verify_nonce( $_POST['ctrldolicaptcha'], 'ctrldolicaptcha-'.$_POST['btndolicaptcha'])) ) {
			$ContactError[] = esc_html__( 'Security check failed, invalid human verification field.', 'doliconnect');
		}
	
		if ( defined("DOLICONNECT_DEMO") ) {
			$ContactError[] = esc_html__( 'Sending message is not permitted because the demo mode is active', 'doliconnect');       
		}
	
		if ( empty($ContactError) ) {
			$emailTo = get_option('tz_email');
			
			if (!isset($emailTo) || ($emailTo == '') ) {
				$emailTo = get_option('admin_email');
			}
			$subject = "[".get_bloginfo( 'name' )."] ".$_POST['ticket_type'];
			$body = "Nom: $name <br>Email: $email <br>Message: $comments";
			$headers = array("Content-Type: text/html; charset=UTF-8","From: ".$name." <".$email.">","Cc: ".$name." <".$email.">"); 
			$emailSent = wp_mail($emailTo, $subject, $body, $headers);

		if ( !is_wp_error( $emailSent )) {
			$response = [
				'message' => dolialert('success', __( 'Your message is successful send!', 'doliconnect')),
				'captcha' => dolicaptcha('dolicontact'),
					];
			wp_send_json_success( $response );
			die();	
		} else {
			$response = [
				'message' => dolialert('error', sprintf(__('Sending message fails for the following reason: %s. Please contact us for help.', 'doliconnect'), $emailSent->get_error_message()) ),
				'captcha' => dolicaptcha('dolicontact'),
					];
			wp_send_json_error( $response );
			die();	
		}
			
		} else {
			$response = [
				'message' => dolialert('danger', join( '<br />', $ContactError )),
				'captcha' => dolicaptcha('dolicontact'),
					];
			wp_send_json_error( $response );
			die();	
		}

	} else {
		$response = [
			'message' => dolialert('danger', __( 'A security error occured', 'doliconnect')),
			'captcha' => dolicaptcha('dolicontact'),
				];
		wp_send_json_error( $response ); 
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
		
wp_send_json_success('success');
} else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolifpw_request', 'dolifpw_request');
add_action('wp_ajax_nopriv_dolifpw_request', 'dolifpw_request');

function dolifpw_request(){
	$gdrf_error     = array();
	$fpw_email     = sanitize_email( $_POST['user_email'] );

	if ( wp_verify_nonce( trim($_POST['dolifpw-nonce']), 'dolifpw') ) {
			if ( !isset($_POST['btndolicaptcha']) || empty(wp_verify_nonce( $_POST['ctrldolicaptcha'], 'ctrldolicaptcha-'.$_POST['btndolicaptcha'])) ) {
				$gdrf_error[] = esc_html__( 'Security check failed, invalid human verification field.', 'doliconnect');
			}
			if ( ! is_email( $fpw_email ) ) {
				$gdrf_error[] = esc_html__( 'This is not a valid email address.', 'doliconnect');
			}
			if ( ! email_exists( $fpw_email ) ) {
				$gdrf_error[] = esc_html__( 'No account seems to be linked to this email address', 'doliconnect');
			}

	if ( empty( $gdrf_error ) ) {

$emailTo = get_option('tz_email');

if (!isset($emailTo) || ($emailTo == '') ){
$emailTo = get_option('admin_email');
}

$user = get_user_by( 'email', $fpw_email);   
$key = get_password_reset_key($user);

$arr_params = array( 'action' => 'rpw', 'key' => $key, 'login' => $user->user_login);  
$url = esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) );

if ( defined("DOLICONNECT_DEMO_EMAIL") && ''.constant("DOLICONNECT_DEMO_EMAIL").'' == $fpw_email ) {
	$response = [
		'message' => dolialert('danger', __( 'Reset password is not permitted for this account!', 'doliconnect')),
		'captcha' => dolicaptcha('dolifpw'),
			];
    wp_send_json_error( $response ); 
	die();
} elseif ( !empty($key) ) { 
		$sitename = get_option('blogname');
    $siteurl = get_option('siteurl');
    $subject = "[$sitename] ".__( 'Reset Password', 'doliconnect');
    $body = __( 'A request to change your password has been made. You can change it via the single-use link below:', 'doliconnect')."<br><br><a href='".$url."'>".$url."</a><br><br>".__( 'If you have not made this request, please ignore this email.', 'doliconnect')."<br><br>".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br>$siteurl";				
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $emailSent =  wp_mail($fpw_email, $subject, $body, $headers);

	if ( !is_wp_error( $emailSent )) {
		$response = [
			'message' => dolialert('success', __( 'A password reset link was sent to you by email. Please check your spam folder if you don\'t find it.', 'doliconnect')),
			'captcha' => dolicaptcha('dolifpw'),
				];
		wp_send_json_success( $response );
	} else { 
		$response = [
			'message' => dolialert('error', sprintf(__('Sending message fails for the following reason: %s. Please contact us for help.', 'doliconnect'), $emailSent->get_error_message()) ),
			'captcha' => dolicaptcha('dolifpw'),
				];
		wp_send_json_error( $response );	
		die(); 
	}	
}
	} else {
		$response = [
			'message' => dolialert('warning', join( '<br />', $gdrf_error ) ),
			'captcha' => dolicaptcha('dolifpw'),
				];
		wp_send_json_error( $response );
	}
	die();

} else {
	$response = [
		'message' => dolialert('danger', __( 'A security error occured', 'doliconnect')),
		'captcha' => dolicaptcha('dolifpw'),
			];
	wp_send_json_error( $response ); 
}

}

add_action('wp_ajax_dolirpw_request', 'dolirpw_request');
add_action('wp_ajax_nopriv_dolirpw_request', 'dolirpw_request');

function dolirpw_request(){
global $wpdb; 

if ( wp_verify_nonce( trim($_POST['dolirpw-nonce']), 'dolirpw')) {
if (isset($pwd0)) $pwd0 = sanitize_text_field($_POST["pwd0"]);
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
$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $current_user->user_login ) );
}
	$response = [
		'message' => dolialert('success', __( 'Your informations have been updated. Now, you will be log out and need to log in again.', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
			];	
	wp_send_json_success( $response );
} elseif (is_user_logged_in() && isset( $current_user->ID ) && (!isset($pwd0) || (isset($pwd0) && ! wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ))) ) {
	$response = [
		'message' => dolialert('danger', __( 'Your actual password is incorrect', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
			];		
	wp_send_json_error( $response );
} elseif ( $pwd1 != $_POST["pwd2"] ) {
	$response = [
		'message' => dolialert('danger',  __( 'The new passwords entered are different', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
			];	
	wp_send_json_error( $response );
} elseif ( !preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd1) ) {
	$response = [
		'message' => dolialert('danger',  __( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
			];		
	wp_send_json_error( $response );
} else {
	$response = [
		'message' => dolialert('danger',  __( 'A security error occured', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
			];	
	wp_send_json_error( $response ); 
}
} else {
	$response = [
		'message' => dolialert('danger',  __( 'A security error occured', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
			];
	wp_send_json_error( $response ); 
}
}

add_action('wp_ajax_dolipaymentmethod_request', 'dolipaymentmethod_request');
//add_action('wp_ajax_nopriv_dolipaymentmethod_request', 'dolipaymentmethod_request');

function dolipaymentmethod_request(){
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods"; 

if ( wp_verify_nonce( trim($_POST['dolipaymentmethod-nonce']), 'dolipaymentmethod-nonce') && isset($_POST['case']) && $_POST['case'] == "default" ) {

$data = [
'default' => 1
];
$object = callDoliApi("PUT", $request."/".sanitize_text_field($_POST['payment_method']), $data, dolidelay( 0, true));

if (!isset($object->error)) {  
$gateway = callDoliApi("GET", $request, null, dolidelay('paymentmethods', true));
wp_send_json_success( dolialert('success', __( 'You changed your default payment method', 'doliconnect')));
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} elseif ( wp_verify_nonce( trim($_POST['dolipaymentmethod-nonce']), 'dolipaymentmethod-nonce') && isset($_POST['case']) && $_POST['case'] == "delete" ) {

$object = callDoliApi("DELETE", $request."/".sanitize_text_field($_POST['payment_method']), null, dolidelay( 0, true));

if (!isset($object->error)) {
$gateway = callDoliApi("GET", $request, null, dolidelay('paymentmethods', true));
wp_send_json_success( dolialert('success', __( 'You deleted a payment method', 'doliconnect')));
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} elseif ( wp_verify_nonce( trim($_POST['dolipaymentmethod-nonce']), 'dolipaymentmethod-nonce') && isset($_POST['case']) && $_POST['case'] == "create" ) {

if ($_POST['default'] == 2) { 
$default = 1;
} else {
$default = 0;
}

$data = [
'default' => $default,
];

$object = callDoliApi("POST", $request."/".sanitize_text_field($_POST['payment_method']), $data, dolidelay( 0, true));

if (!isset($object->error)) { 
$gateway = callDoliApi("GET", $request, null, dolidelay('paymentmethods', true));
wp_send_json_success( dolialert('success', __( 'You added a new payment method', 'doliconnect')));
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}

} else {
wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
}
}

add_action('wp_ajax_dolicart_request', 'dolicart_request');
//add_action('wp_ajax_nopriv_dolicart_request', 'dolicart_request');

function dolicart_request(){
global $current_user;

if ( wp_verify_nonce( trim($_POST['dolicart-nonce']), 'dolicart-nonce')) {

if ( isset($_POST['case']) && $_POST['case'] == "purge_cart" && isset($_POST['module']) && isset($_POST['id'])) {
$object = callDoliApi("GET", "/".trim($_POST['module'])."/".trim($_POST['id']), null, dolidelay('order', true));
if (!isset($object->error) && empty($object->statut)) {
$object = callDoliApi("DELETE", "/".trim($_POST['module'])."/".trim($_POST['id']), null);
if (!isset($object->error)) { 
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
$response = [
    'items' => '0',
    'lines' => doliline(null, null),
	'total' => doliprice(),
    'message' => __( 'Your cart has been emptied', 'doliconnect'),
        ];
wp_send_json_success($response);
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}
} else{
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}
} elseif ( isset($_POST['case']) && $_POST['case'] == "update_cart") {

$result = doliaddtocart($_POST['productid'], $_POST['qty'], $_POST['price'], (isset($_POST['remise_percent'])?$_POST['remise_percent']:null), (isset($_POST['date_start'])?$_POST['date_start']:null), (isset($_POST['date_end'])?$_POST['date_end']:null));

if (isset($_POST['module']) && isset($_POST['id']) ) $object = callDoliApi("GET", "/".trim($_POST['module'])."/".trim($_POST['id']), null, dolidelay('order', true));

if ($result >= 0) {
		$response = [
		'items' => $result,
    	'message' => __( 'Quantities have been changed', 'doliconnect')
    	];
		if (isset($object)) {
		$response .= [
		'lines' => doliline($object, true),
		'total' => doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)
		];	
		}	
		wp_send_json_success( $response ); 
	} else {
	$response = [
		'message' => '<div class="alert alert-danger alert-dismissible d-flex align-items-center" role="alert">'.__( 'We no longer have this item in this quantity', 'doliconnect').'</div>',
			];
	wp_send_json_error( $response ); 
	}

} elseif ( isset($_POST['case']) && $_POST['case'] == "validate_cart" && isset($_POST['module']) && isset($_POST['id'])) {

$data = [
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
} elseif ( isset($_POST['case']) && $_POST['case'] == "info_cart" && isset($_POST['module']) && isset($_POST['id'])) {

$data = [
    'demand_reason_id' => 1,
    'module_source' => 'doliconnect',
    'pos_source' => get_current_blog_id(),
    'note_public' => $_POST['note_public'],
	]; 
if (isset($_POST['shipping_method_id'])) $data['shipping_method_id'] = $_POST['shipping_method_id'];  
                  
$object = callDoliApi("PUT", "/".trim($_POST['module'])."/".trim($_POST['id']), $data, 0);
$object = callDoliApi("GET", "/".trim($_POST['module'])."/".trim($_POST['id'])."?contact_list=0", $data, dolidelay('order', true));
if (isset($_POST['contact_shipping'])) {
//$shipping= callDoliApi("POST", "/".trim($_POST['module'])."/".trim($_POST['id'])."/contact/".$_POST['contact_shipping']."/SHIPPING", null, dolidelay('order', true));
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
    'message' => __( 'Your cart has been validated', 'doliconnect'),
        ];
wp_send_json_success($response);
} else {
wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
}
} elseif ( isset($_POST['case']) && $_POST['case'] == "pay_cart" && isset($_POST['module']) && isset($_POST['id'])) {

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
$message .= '<p class="card-text">'.__( 'Payment status', 'doliconnect').': '.$payinfo->charge_status.'<br>'.__( 'Payment ID', 'doliconnect').': '.$payinfo->charge.'</p>';
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

add_action('wp_ajax_dolimember_request', 'dolimember_request');
//add_action('wp_ajax_nopriv_dolimember_request', 'dolimember_request');

function dolimember_request(){
global $current_user;
		
if ( wp_verify_nonce( trim($_POST['dolimember-nonce']), 'dolimember-nonce' ) ) {
//if ( $_POST['cartaction'] == 'addtocart') {
$productadhesion = doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", dolidelay('constante'));
$requesta = "/adherentsplus/".doliconnector($current_user, 'fk_member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 ) {
$adherent = callDoliApi("GET", $requesta, null, dolidelay('member'));
}
$requestb= "/adherentsplus/type/".$adherent->typeid;
$adherenttype = callDoliApi("GET", $requestb, null, dolidelay('member'));
$result = doliaddtocart($productadhesion, 1, $adherenttype->price_prorata, null, $adherenttype->date_begin, $adherenttype->date_end, null, array('options_member_beneficiary' => $adherent->id));
//$result = doliaddtocart(trim($_POST['product-add-id']), trim($_POST['product-add-qty']), trim($_POST['product-add-price']), trim($_POST['product-add-remise_percent']), isset($_POST['product-add-timestamp_start'])?trim($_POST['product-add-timestamp_start']):null, isset($_POST['product-add-timestamp_end'])?trim($_POST['product-add-timestamp_end']):null);
if ($result >= 0) {
$response = [
    'message' => '<div class="alert alert-success alert-dismissible d-flex align-items-center" role="alert">'.__( 'Your subscription have been put in the basket for 1 hour', 'doliconnect').'</div>',
    'items' => $result,
    'list' => doliconnect_CartItemsList()
        ];
wp_send_json_success( $response ); 
} else {
$response = [
    'message' => __( 'We no longer have this item in this quantity', 'doliconnect').$result,
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