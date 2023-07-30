<?php
/**
 * Data Request Handler.
 */

add_action( 'wp_ajax_doli_gdrf_data_request', 'doli_gdrf_data_request' );
add_action( 'wp_ajax_nopriv_doli_gdrf_data_request', 'doli_gdrf_data_request' );

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

//*****************************************************************************************

add_action('wp_ajax_doliselectform_request', 'doliselectform_request');
add_action('wp_ajax_nopriv_doliselectform_request', 'doliselectform_request');

function doliselectform_request(){

if (isset($_POST['case']) && $_POST['case'] == "update" ) {	
	$response = array();
	if (isset($_POST['legalformId'])) $response['state_id'] = doliSelectForm("state_id", "/setup/dictionary/states?sortfield=code_departement&sortorder=ASC&limit=500&country=".$_POST['countryId'], __( '- Select your state -', 'doliconnect'), __( 'State', 'doliconnect'), $_POST['stateId'], $_POST['objectId'], $_POST['rights'], $_POST['delay']);
	if (isset($_POST['legalformId'])) $response['forme_juridique_code'] = doliSelectForm("forme_juridique_code", "/setup/dictionary/legal_form?sortfield=libelle&sortorder=ASC&active=1&limit=500&country=".$_POST['countryId'], __( '- Select your legal form -', 'doliconnect'), __( 'Legal form', 'doliconnect'), $_POST['legalformId'], $_POST['objectId'], $_POST['rights'], $_POST['delay'], 'code');
	$response['ziptown'] = doliSelectForm("ziptown", "/setup/dictionary/towns?sortfield=town&sortorder=ASC&active=1&limit=1000&sqlfilters=(t.fk_pays%3A%3D%3A'".$_POST['countryId']."')%20AND%20(t.fk_county%3A%3D%3A'".$_POST['stateId']."')", __( '- Select your town -', 'doliconnect'), __( 'Town', 'doliconnect'), $_POST['ziptownId'], $_POST['objectId'], $_POST['rights'], $_POST['delay']);
	$response['profids'] = doliProfId((isset($_POST['idprof1'])?$_POST['idprof1']:null), (isset($_POST['idprof2'])?$_POST['idprof2']:null), (isset($_POST['idprof3'])?$_POST['idprof3']:null), (isset($_POST['idprof4'])?$_POST['idprof4']:null), (isset($_POST['country_code'])?$_POST['country_code']:null), (isset($_POST['objectId'])?$_POST['objectId']:null), (isset($_POST['rights'])?$_POST['rights']:null));
	wp_send_json_success( $response );
} else {
	wp_send_json_error( dolialert('danger', __( 'A security error occured', 'doliconnect'))); 
}

}

//*****************************************************************************************

add_action('wp_ajax_doliuserinfos_request', 'doliuserinfos_request');
add_action('wp_ajax_nopriv_doliuserinfos_request', 'doliuserinfos_request');

function doliuserinfos_request(){
	global $current_user;
	$ID = $current_user->ID;
	
	if ( isset($_POST['doliuserinfos-nonce']) && wp_verify_nonce( trim($_POST['doliuserinfos-nonce']), 'doliuserinfos') && isset($_POST['case']) && $_POST['case'] == "update" ) {

		$thirdparty=$_POST['thirdparty'][''.doliconnector($current_user, 'fk_soc').''];
		$thirdparty = dolisanitize($thirdparty);
		if (empty($thirdparty['no_email'])) {
			$thirdparty['no_email'] = true;
		} else {
			$thirdparty['no_email'] = false;
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
		if (empty($thirdparty['no_email'])) {
			$thirdparty['no_email'] = true;
		} else {
			$thirdparty['no_email'] = false;
		}
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
		'description' => (isset($thirdparty['note_public'])?$thirdparty['note_public']:null),
		'user_url' => (isset($thirdparty['url'])?$thirdparty['url']:null),
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

//*****************************************************************************************

add_action('wp_ajax_dolicontactinfos_request', 'dolicontactinfos_request');
//add_action('wp_ajax_nopriv_dolicontactinfos_request', 'dolicontactinfos_request');

function dolicontactinfos_request(){
	global $current_user;
	$ID = $current_user->ID;
	
	if ( isset($_POST['dolicontactinfos-nonce']) && wp_verify_nonce( trim($_POST['dolicontactinfos-nonce']), 'dolicontactinfos') && isset($_POST['case']) && $_POST['case'] == "update" ) {

		$contact = $_POST['contact'][''.$_POST['contactid'].''];
		$contact = dolisanitize($contact);
		if (empty($contact['no_email'])) {
			$contact['no_email'] = true;
		} else {
			$contact['no_email'] = false;
		}
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
		if (empty($contact['no_email'])) {
			$contact['no_email'] = true;
		} else {
			$contact['no_email'] = false;
		}
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

//*****************************************************************************************

add_action('wp_ajax_doliticket_request', 'doliticket_request');
add_action('wp_ajax_nopriv_doliticket_request', 'doliticket_request');

function doliticket_request(){
	global $current_user;

	if ( isset($_POST['doliticket-nonce']) && wp_verify_nonce( trim($_POST['doliticket-nonce']), 'doliticket')) {
		if (isset($_POST['case']) && $_POST['case'] == "create") {
			$rdr = [        
				'fk_soc' => doliconnector($current_user, 'fk_soc'),
				'type_code' => $_POST['ticket_type'],
				'category_code' => $_POST['ticket_category'],
				'severity_code' => $_POST['ticket_severity'],
				'subject' => sanitize_text_field($_POST['ticket_subject']),
				'message' => sanitize_textarea_field($_POST['ticket_message']),
			];
			if (isset($_POST['fk_user_assign']) && !empty($_POST['fk_user_assign'])) $rdr['fk_user_assign'] = $_POST['fk_user_assign'];                    
			$result = callDoliApi("POST", "/tickets", $rdr, dolidelay('ticket', true));
			if (!isset($result->error)) { 
				$ticketfo = callDoliApi("GET", "/tickets/".esc_attr($_POST['id']), null, dolidelay('ticket', true));
				$response = [
					'message' => dolialert('success', __( 'Your ticket has been submitted', 'doliconnect')),
					'captcha' => dolicaptcha('doliticket'),
				];
				wp_send_json_success( $response );
				die();
			} else {
				$response = [
					'message' => __( 'An error occured:', 'doliconnect').' '.$result->error->message,
					'captcha' => dolicaptcha('doliticket'),
				];
				wp_send_json_error( $response );
				die();
			}
		} elseif (isset($_POST['case']) && $_POST['case'] == "newMessage") {
			$rdr = [
				'track_id' => $_POST['track_id'],
				'message' => sanitize_textarea_field($_POST['ticket_newmessage']),
			];                  
			$result = callDoliApi("POST", "/tickets/newmessage", $rdr, dolidelay('ticket', true));
			if (!isset($result->error)) { 
				$ticketfo = callDoliApi("GET", "/tickets/".esc_attr($_POST['id']), null, dolidelay('ticket', true));
				$response = [
					'message' => dolialert('success', __( 'Your message has been send', 'doliconnect')),
					'captcha' => dolicaptcha('doliticket'),
				];
				wp_send_json_success( $response );
				die();
			} else {
				$response = [
					'message' => __( 'An error occured:', 'doliconnect').' '.$result->error->message,
					'captcha' => dolicaptcha('doliticket'),
				];
				wp_send_json_error( $response );
				die();
			}
		} else {
			$response = [
				'message' => dolialert('warning', __( 'This action is not authorized', 'doliconnect')),
				'captcha' => dolicaptcha('doliticket'),
			];
			wp_send_json_error( $response );
			die();
		}
	} else {
		$response = [
			'message' => dolialert('danger', __( 'A security error occured', 'doliconnect')),
			'captcha' => dolicaptcha('doliticket'),
		];
		wp_send_json_error( $response );
		die();
	}
}

//*****************************************************************************************

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

		if( str_word_count(sanitize_textarea_field($_POST['comments']), 0) < 10 ) {
			$ContactError[] = esc_html__( 'Your message is too short!', 'doliconnect');
		} else {
			$comments = sanitize_textarea_field($_POST['comments']);
		}
		
		if ( !isset($_POST['btndolicaptcha']) || empty(wp_verify_nonce(  trim($_POST['ctrldolicaptcha']), 'ctrldolicaptcha-'. trim($_POST['btndolicaptcha']))) ) {
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
		die();
	}
}

//*****************************************************************************************
	
add_action('wp_ajax_dolisettings_request', 'dolisettings_request');
//add_action('wp_ajax_nopriv_dolisettings_request', 'dolisettings_request');

function dolisettings_request(){
	global $current_user;
	$ID = $current_user->ID;

	if ( isset($_POST['dolisettings-nonce']) && wp_verify_nonce( trim($_POST['dolisettings-nonce']), 'dolisettings')) {
		if ( isset($_POST['loginmailalert'])) { update_user_meta( $ID, 'loginmailalert', sanitize_text_field($_POST['loginmailalert']) ); } else { delete_user_meta($ID, 'loginmailalert'); }
		//if ( isset($_POST['optin1'])) { update_user_meta( $ID, 'optin1', sanitize_text_field($_POST['optin1']) ); } else { delete_user_meta($ID, 'optin1'); }
		$response = [
			'message' => dolialert('success', __( 'Yours settings are successul save', 'doliconnect')),
			'captcha' => dolicaptcha('dolicontact'),
		];
		wp_send_json_success( $response );
		die();	
	} else {
		$response = [
		'message' => dolialert('danger', __( 'A security error occured', 'doliconnect')),
		'captcha' => dolicaptcha('dolicontact'),
		];
		wp_send_json_error( $response );
		die();
	}
}

//*****************************************************************************************

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
		die();
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
	die();
}

}

//*****************************************************************************************

add_action('wp_ajax_dolirpw_request', 'dolirpw_request');
add_action('wp_ajax_nopriv_dolirpw_request', 'dolirpw_request');

function dolirpw_request(){
global $wpdb,$current_user; 

if ( wp_verify_nonce( trim($_POST['dolirpw-nonce']), 'dolirpw')) {
	if (isset($_POST["pwd0"])) $pwd0 = sanitize_text_field($_POST["pwd0"]);
	$pwd1 = sanitize_text_field($_POST["pwd1"]);
	$pwd2 = sanitize_text_field($_POST["pwd2"]);

	if (isset($_POST["key"]) && isset($_POST["login"])) {
		$current_user = check_password_reset_key( esc_attr($_POST["key"]), esc_attr($_POST["login"]) );
	}

	$dolipwd = doliconst("USER_PASSWORD_GENERATED", dolidelay('constante'));
	if ( $dolipwd == 'Perso' ) { 
		$pwdpattern = explode(";", doliconst("USER_PASSWORD_PATTERN", dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))));
		$password_a = preg_split('//u', $pwd1, null, PREG_SPLIT_NO_EMPTY);
		$maj = preg_split('//u', "ABCDEFGHIJKLMNOPQRSTUVWXYZ", null, PREG_SPLIT_NO_EMPTY);
		$num = preg_split('//u',  "0123456789", null, PREG_SPLIT_NO_EMPTY);
		$spe = preg_split('//u', "!@#$%&*()_-+={}[]\\|:;'/", null, PREG_SPLIT_NO_EMPTY);
		$doliValidatePassword = (strlen($pwd1) >= $pwdpattern[0]) && ( count(array_intersect($password_a, $maj)) >= $pwdpattern[1]) && (count(array_intersect($password_a, $num)) >= $pwdpattern[2]) && (count(array_intersect($password_a, $spe)) >= $pwdpattern[3]) && consecutiveDoliIterationSameCharacter($pwd1, $pwdpattern[4]);
	} elseif ( $dolipwd == 'standard' ) { $doliValidatePassword = preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{12,40}/', $pwd1); } else {
		$doliValidatePassword = true;
	}

	if ( (!isset($_POST["key"]) && !isset($_POST["login"]) && isset($pwd0) && !empty($pwd0) && wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ) && $doliValidatePassword ) || (isset($_POST["key"]) && isset($_POST["login"]) && ($pwd1 == $pwd2) && $doliValidatePassword ) ) {

	if ( doliconnector($current_user, 'fk_user') > '0' ) {
		$data = [
    	'pass' => $pwd1
		];
		$object = callDoliApi("PUT", "/users/".doliconnector($current_user, 'fk_user'), $data, dolidelay('thirdparty'));
	}

	if ( !isset($object) || ( isset($object) && !isset($object->error) ) ) { 
		wp_set_password($pwd1, $current_user->ID);

		if (isset($_POST["key"]) && isset($_POST["login"])) {
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $current_user->user_login ) );
		}
		
		$response = [
		'message' => dolialert('success', __( "Your informations have been updated. If connected, you will be log out and need to log in again.", 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
		];	
		wp_send_json_success( $response );
	} else {
		$response = [
			'message' => dolialert('danger', __( 'An error occured:', 'doliconnect').' '.$object->error->message),
			'captcha' => dolicaptcha('dolirpw'),
				];	
		wp_send_json_error( $response ); 
	}

	die();
	
	} elseif (!isset($_POST["key"]) && !isset($_POST["login"]) && isset( $current_user->ID ) && (!isset($pwd0) || (isset($pwd0) && ! wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ))) ) {
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
		die();
	} elseif ( !$doliValidatePassword ) {
		$response = [
		'message' => dolialert('danger',  __( 'Your password must strictly comply with the rules of composition', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
		];		
		wp_send_json_error( $response );
		die();
	} else {
		$response = [
		'message' => dolialert('danger',  __( 'A security error occured', 'doliconnect')),
		'captcha' => dolicaptcha('dolirpw'),
		];	
		wp_send_json_error( $response );
		die();
	}
} else {
	$response = [
	'message' => dolialert('danger',  __( 'A security error occured', 'doliconnect')),
	'captcha' => dolicaptcha('dolirpw'),
	];
	wp_send_json_error( $response ); 
	die();
}
}

//*****************************************************************************************

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

//*****************************************************************************************

add_action('wp_ajax_dolicart_request', 'dolicart_request');
//add_action('wp_ajax_nopriv_dolicart_request', 'dolicart_request');

function dolicart_request() {
global $current_user;
	if ( wp_verify_nonce( trim($_POST['dolicart-nonce']), 'dolicart-nonce')) {
		if (isset($_POST['case']) && $_POST['case'] == "updateLine") {
			$product = callDoliApi("GET", "/products/".trim($_POST['id'])."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', true));
			$mstock = doliProductStock($product, false, true);
			if (isset($_POST['modify']) && $_POST['modify'] == "delete") { 
				$price = doliProductPrice($product, 0, false, true);
				$result = doliaddtocart($product, $mstock, 0, $price, null, null);
				$response = [
					'message' => dolialert('success', $result['message']),
					'newqty' => $result['newqty'],
					'items' => $result['items'],	
					'list' => $result['list'],
					'lines' => $result['lines'],
					'total' => $result['total']
				];	
				wp_send_json_success($response);	
				die(); 
			} elseif (isset($_POST['modify']) && ($_POST['modify'] == "plus" || $_POST['modify'] == "minus" || $_POST['modify'] == "modify")) { 
				if (!is_numeric(trim($_POST['qty']))) $_POST['qty'] = $mstock['qty'];
				if ($_POST['modify'] == "plus") {
					$qty = trim($_POST['qty'])+$mstock['step'];
				} elseif ($_POST['modify'] == "minus") {
					$qty = trim($_POST['qty'])-$mstock['step'];	
				} else {
					$qty = trim($_POST['qty'])/$mstock['step'];
					$qty = ceil($qty)*$mstock['step'];
				}
				$price = doliProductPrice($product, $qty, false, true);
				$result = doliaddtocart($product, $mstock, $qty, $price, isset($_POST['product-add-timestamp_start'])?trim($_POST['product-add-timestamp_start']):null, isset($_POST['product-add-timestamp_end'])?trim($_POST['product-add-timestamp_end']):null);
				$response = [
					'message' => dolialert('success', $result['message']),
					'newqty' => $result['newqty'],
					'items' => $result['items'],	
					'list' => $result['list'],
					'lines' => $result['lines'],
					'total' => $result['total']
				];
				if ($qty != $result['newqty']) $response['modal'] = doliModalTemplate('CartInfos', __( 'Cart', 'doliconnect'), __( 'This item is not available in this quantity!', 'doliconnect'), '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.__( "Continue shopping", "doliconnect").'</button> <a class="btn btn-primary" role="button" href="'.esc_url(doliconnecturl('dolicart')).'" >'.__( 'Finalize the order', 'doliconnect').'</a>', 'modal-lg');
				wp_send_json_success($response);
				die();
			} elseif (isset($_POST['modify']) && ($_POST['modify'] == "wish" || $_POST['modify'] == "unwish")) {
				if (!is_numeric(trim($_POST['qty']))) $_POST['qty'] = $mstock['qty'];
				$qty = trim($_POST['qty'])/$mstock['step'];
				$qty = ceil($qty)*$mstock['step'];
				$price = doliProductPrice($product, $qty, false, true);
				$result = doliaddtocart($product, $mstock, $qty, $price, isset($_POST['product-add-timestamp_start'])?trim($_POST['product-add-timestamp_start']):null, isset($_POST['product-add-timestamp_end'])?trim($_POST['product-add-timestamp_end']):null);
				$response = [
					'message' => dolialert('success', $result['message']),
					'newqty' => $result['newqty'],
					'items' => $result['items'],	
					'list' => $result['list'],
					'lines' => $result['lines'],
					'total' => $result['total'],	
					'modal' => doliModalTemplate('CartInfos', __( 'Wishlist', 'doliconnect'), 'Soon...', '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.__( "Close", "doliconnect").'</button>'),
				];
				wp_send_json_success($response);			
				die(); 
			} else {
				$response['modal'] = doliModalTemplate('CartInfos', __( 'Error', 'doliconnect'), __( "This action is not authorized", "doliconnect"), '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.__( "Close", "doliconnect").'</button>');
				wp_send_json_error($response);			
				die(); 
			}
		} elseif (isset($_POST['case']) && $_POST['case'] == "update") {	
			if (isset($_POST['modify']) && $_POST['modify'] == "delete") {
				$object = callDoliApi("GET", "/orders/".trim($_POST['id']), null, dolidelay('order', true));
				if (!isset($object->error) && empty($object->statut)) {
					$object = callDoliApi("DELETE", "/orders/".trim($_POST['id']), null);
					if (!isset($object->error)) { 
						$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
						$response = [
							'items' => 0,
							'list' => doliconnect_CartItemsList(),
							'lines' => doliline(0),
							'total' => doliprice(0),
							'message' => __( 'Your cart has been emptied', 'doliconnect'),
						];
						wp_send_json_success($response);
						die();
					} else {
						wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
					}	
				} else {
					wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
				}	
			}
		} elseif (isset($_POST['case']) && $_POST['case'] == "purge_cart" && isset($_POST['module']) && isset($_POST['id'])) {
			$object = callDoliApi("GET", "/".trim($_POST['module'])."/".trim($_POST['id']), null, dolidelay('order', true));
			if (!isset($object->error) && empty($object->statut)) {
				$object = callDoliApi("DELETE", "/".trim($_POST['module'])."/".trim($_POST['id']), null);
				if (!isset($object->error)) { 
					$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
					$response = [
    					'items' => 0,
    					'list' => doliconnect_CartItemsList(),
    					'lines' => doliline(0),
						'total' => doliprice(0),
    					'message' => __( 'Your cart has been emptied', 'doliconnect'),
        			];
					wp_send_json_success($response);
					die();
				} else {
					wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
				}	
			} else {
				wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message); 
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
				die();
			} else {
				wp_send_json_error( __( 'An error occured:', 'doliconnect').' '.$object->error->message);
				die(); 
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
$payinfo = callDoliApi("POST", "/doliconnector/pay/".trim($_POST['module'])."/".trim($_POST['id']), $data, dolidelay('order'));
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
	} else {
		wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
	}
}

//*****************************************************************************************

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
			 	die(); 
			} else {
				wp_send_json_error( __( 'Customer not found', 'doliconnect')); 
				die(); 
			}
		}
	} else {
		wp_send_json_error( __( 'A security error occured', 'doliconnect')); 
		die(); 
	}
}

//*****************************************************************************************

add_action('wp_ajax_dolimember_request', 'dolimember_request');
//add_action('wp_ajax_nopriv_dolimember_request', 'dolimember_request');

function dolimember_request(){
global $current_user;

	if ( isset($_POST['dolimember-nonce']) && wp_verify_nonce( trim($_POST['dolimember-nonce']), 'dolimember-nonce') && isset($_POST['case']) && $_POST['case'] == "subscription" ) {

	$product = callDoliApi("GET", "/products/".doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", dolidelay('constante'))."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', true));
	$mstock = doliProductStock($product, false, true);

	$requesta = "/members/".doliconnector($current_user, 'fk_member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
	if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 ) {
		$adherent = callDoliApi("GET", $requesta, null, dolidelay('member'));
	}
	$requestb= "/adherentsplus/type/".$adherent->typeid;
	$adherenttype = callDoliApi("GET", $requestb, null, dolidelay('member'));

	$price = array();
	$price['discount'] = 0;
	$price['subprice'] = $adherenttype->price_prorata;

	$result = doliaddtocart($product, $mstock, 1, $price, $adherenttype->date_begin, $adherenttype->date_end, null, array('options_member_beneficiary' => $adherent->id));
	if ($result >= 0) {
		$response = [
			'message' => $result['message'],
			'newqty' => 1,
			'items' => $result['items'],	
			'list' => $result['list'],
			'lines' => $result['lines'],
			'total' => $result['total']
		];	
		wp_send_json_success($response);	
		die(); 
	} else {
		$response = [
    		'message' => __( 'We no longer have this item in this quantity', 'doliconnect').$result,
     	];
		wp_send_json_error( $response ); 
		die(); 
	}
	} elseif ( isset($_POST['dolimember-nonce']) && wp_verify_nonce( trim($_POST['dolimember-nonce']), 'dolimember-nonce') && isset($_POST['case']) && $_POST['case'] == "update" ) {
		$member=$_POST['member'][''.trim($_POST['memberid']).''];
		$member = dolisanitize($member);
		if (empty($member['no_email'])) {
			$member['no_email'] = true;
		} else {
			$member['no_email'] = false;
		}
		
		if (isset($_POST['memberid']) && trim($_POST['memberid']) > 0) { 
			$object = callDoliApi("PUT", "/members/".trim($_POST['memberid']), $member, 0);
		}
		$response = [
			'message' => 'success',
		];	
		wp_send_json_success($response);	
		die(); 
	} else {
		wp_send_json_error( __( 'A security error occured', 'doliconnect'));	
		die(); 
	}
}

//*****************************************************************************************

add_action('wp_ajax_dolimodal_request', 'dolimodal_request');
add_action('wp_ajax_nopriv_dolimodal_request', 'dolimodal_request');

function dolimodal_request(){
global $current_user;
	$response = array();
	$modal = array();	
	if ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "legacy" ) {
		$modal['header'] = __('Legal notice', 'doliconnect');
		$company = callDoliApi("GET", "/setup/company", null, dolidelay('constante'));
		$modal['body'] = '<p><strong>'.__('Editor', 'doliconnect').'</strong><br>';
		$modal['body'] .= doliCompanyCard($company);
		if (!empty($company->note_private)) { $modal['body'] .= '<br>'.$company->note_private; }
		if (!empty($company->managers)) $modal['body'] .= '</p><p><strong>'.__('Responsible for publishing', 'doliconnect').'</strong><br>'.$company->managers;
		if ( defined('PTIBOGXIV_NET') ) {
			$modal['body'] .= '</p><p><strong>'.__('Design & conception', 'doliconnect').'</strong><br>Thibault FOUCART - ptibogxiv.eu<br>
			1 rue de la grande brasserie<br>
			FR - 59000 LILLE - France<br>
			SIRET: 83802482600011 - APE6201Z<br>
			Site Internet: <a href="https://www.ptibogxiv.eu">ptibogxiv.eu</a></p>
			<p><strong>'.__('Hosting', 'doliconnect').'</strong><br>Infomaniak Network SA<br>
			Rue Eugène-Marziano, 25<br>
			CH - 1227 GENEVE - Suisse<br>
			N° TVA: CHE - 103.167.648<br>
			N° de société: CH - 660 - 0059996 - 1<br>
			Site Internet: <a href="https://www.infomaniak.com/goto/fr/home?utm_term=5de6793fdf41b">Infomaniak</a>';
		}
		$modal['body'] .= '</p>';
		$modal['footer'] = null;
		$response['js'] = null;
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer']);
		wp_send_json_success($response);	
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "login" ) {
		$modal['header'] = __( 'Welcome', 'doliconnect');
		$modal['body'] = null;
		if (!empty(get_option('doliaccountinfo'))) $modal['body'] .= '<b>'.get_option('doliaccountinfo').'</b>';
		if ( ! function_exists('dolikiosk') || ( function_exists('dolikiosk') && empty(dolikiosk())) ) {
			$modal['body'] .= socialconnect ( get_permalink() );
		}
		if ( function_exists('secupress_get_module_option') && !empty(get_site_option('secupress_active_submodule_move-login')) && secupress_get_module_option('move-login_slug-login', null, 'users-login' )) {
		  $login_url = site_url()."/".secupress_get_module_option('move-login_slug-login', null, 'users-login' ); 
		} elseif (get_site_option('doliconnect_login')) {
		  $login_url = site_url()."/".get_site_option('doliconnect_login');
		} else {
		  $login_url = site_url()."/wp-login.php"; }
		if  ( defined("DOLICONNECT_DEMO") ) {
			$modal['body'] .= "<p><i class='fas fa-info-circle fa-beat'></i> <b>".__( 'Demo mode is activated', 'doliconnect')."</b></p>";
		} 
		$modal['body'] .= '<div class="form-floating mb-3"><input type="email" class="form-control" id="user_login" name="log" placeholder="name@example.com" value="';
		if ( defined("DOLICONNECT_DEMO") && defined("DOLICONNECT_DEMO_EMAIL") && !empty(constant("DOLICONNECT_DEMO_EMAIL")) ) {
			$modal['body'] .= constant("DOLICONNECT_DEMO_EMAIL");
		}
		$modal['body'] .= '" required autofocus><label for="user_login"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label></div>';
		
		$modal['body'] .= '<div class="form-floating mb-3"><input type="password" class="form-control" id="user_pass" name="pwd" placeholder="Password" value="';
		if ( defined("DOLICONNECT_DEMO") && defined("DOLICONNECT_DEMO_PASSWORD") && !empty(constant("DOLICONNECT_DEMO_PASSWORD")) ) {
			$modal['body'] .= constant("DOLICONNECT_DEMO_PASSWORD");
		}
		$modal['body'] .= '" required><label for="user_pass"><i class="fas fa-key fa-fw"></i> '.__( 'Password', 'doliconnect').'</label></div>';
		
		do_action( 'login_form' );
		
		$modal['body'] .= '<div class="form-check float-start">
		  <input class="form-check-input" type="checkbox" name="rememberme" value="forever" id="rememberme" checked>
		  <label class="form-check-label" for="rememberme">'.__( 'Remember me', 'doliconnect').'</label>
		</div>';
		
		$modal['body'] .= "<a class='float-end' href='".wp_lostpassword_url(get_permalink())."' role='button' title='".__( 'Forgot password?', 'doliconnect')."'><small>".__( 'Forgot password?', 'doliconnect')."</small></a>"; 
		
		$modal['body'] .= "<input type='hidden' value='".$_POST['redirect_to']."' name='redirect_to'>";
		if ( get_site_option('doliconnect_mode') == 'one' && function_exists('switch_to_blog') ) {
			switch_to_blog(1);
		}
		$modal['footer'] = null;
		if ((!is_multisite() && get_option( 'users_can_register' )) || ((!is_multisite() && get_option( 'dolicustsupp_can_register' )) || ((get_option( 'dolicustsupp_can_register' ) || get_option('users_can_register') == '1') && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
			$modal['footer'] .=  "<a class='btn btn-lg btn-link fs-6 text-primary text-decoration-none col-6 m-0 rounded-0 border-end' href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect')."'><small>".__( 'Create an account', 'doliconnect')."</small></a>";
		}
		if (get_site_option('doliconnect_mode')=='one') {
			restore_current_blog();
		}
		$modal['footer'] .= '<button class="btn btn-lg btn-link fs-6 text-primary text-decoration-none col-6 m-0 rounded-0" type="submit" value="submit"><strong>'.__( 'Sign in', 'doliconnect').'</strong></button>';
		$response['js'] = plugins_url( 'doliconnect/includes/js/doligeneric.js');
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer'], null, null, null, 'flex-nowrap p-0', $login_url);
		wp_send_json_success($response);	
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "editmembership" ) {
		if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 && doliconnector($current_user, 'fk_soc') > 0 ) {
		  	$request = "/members/".doliconnector($current_user, 'fk_member');
		  	$adherent = callDoliApi("GET", $request, null, dolidelay('member'));
		} else {
			$adherent = (object) 0;
			$adherent->typeid = 0;
		}
		$member_id = '';
		if (isset($adherent) && isset($adherent->id) && $adherent->id > 0) $member_id = "member_id=".$adherent->id;
		$morphy = '';
		if (!empty($current_user->billing_type)) $morphy = "&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$current_user->billing_type."')";
		$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&".$member_id.$morphy, null, dolidelay('member'));
		$modal['header'] = __( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season;
		$modal['body'] = dolimembertypelist($typeadhesion, $adherent);	
		$modal['footer'] = __( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect');
		$response['js'] = null;
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer'], 'modal-lg', null, 'p-0');
		wp_send_json_success($response);
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "resiliatemembership" ) {
		$modal['header'] = __( 'Resiliate', 'doliconnect');
		$modal['body'] = null;	
		$modal['footer'] = "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='update_membership' value='2'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Resiliate', 'doliconnect')."</button></div></form>";
		$response['js'] = null;
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer']);
		wp_send_json_success($response);
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "renewmembership" ) {
		if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 && doliconnector($current_user, 'fk_soc') > 0 ) {
		  	$request = "/members/".doliconnector($current_user, 'fk_member');
		  	$adherent = callDoliApi("GET", $request, null, dolidelay('member'));
		} else {
			$adherent = (object) 0;
			$adherent->typeid = 0;
		}
		$member_id = '';
		$request= "/adherentsplus/type/".$adherent->typeid;
		$adherenttype = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
		if ( !doliversion('14.0.0') || !isset($adherenttype->amount)) {
			$adherenttype->amount = $adherenttype->price;
		}
		$modal['header'] = __( 'Pay my subscription', 'doliconnect');
		$modal['body'] = '<h6>'.__( 'This subscription', 'doliconnect').'</h6>
		'.__( 'Price:', 'doliconnect').' '.doliprice($adherenttype->price_prorata).'<br>
		'.__( 'From', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_begin).' '.__( 'until', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_end).'
		<hr>
		<h6>'.__( 'Next subscription', 'doliconnect').'</h6>
		'.__( 'Price:', 'doliconnect').' '.doliprice($adherenttype->amount).'<br>
		'.__( 'From', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_nextbegin).' '.__( 'until', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_nextend);	
		$modal['footer'] = '<form id="subscribe-form" action="'.admin_url('admin-ajax.php').'" method="post">';
		$modal['footer'] .= '<input type="hidden" name="action" value="dolimember_request"><input type="hidden" name="case" value="subscription"><input type="hidden" name="dolimember-nonce" value="'.wp_create_nonce( 'dolimember-nonce').'">';
		$modal['footer'] .= '<input type="hidden" name="update_membership" value="renew">';
		$modal['footer'] .= '<button class="btn btn-danger" type="submit">'.__( 'Add to basket', 'doliconnect').'</button></form>';
		$response['js'] = plugins_url( 'doliconnect/includes/js/renewmembership.js');
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer']);
		wp_send_json_success($response);
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "linkedmember" ) {
		if (isset($_POST['value1']) && !empty($_POST['value1'])) {
			$modal['header'] = __( 'Edit member', 'doliconnect');
			$object = callDoliApi("GET", "/members/".trim($_POST['value1']), null, dolidelay('member'));
		} else {
			$modal['header'] = __( 'New linked member', 'doliconnect');
			$object = null;
		}
		$modal['body'] = '<form id="linkedmember-form" action="'.admin_url('admin-ajax.php').'" method="post" class="was-validated">';
		$modal['body'] .= '<input type="hidden" name="action" value="dolimember_request"><input type="hidden" name="case" value="update">';
		$modal['body'] .= '<input type="hidden" name="memberid" value="'.trim($_POST['value1']).'"><input type="hidden" name="dolimember-nonce" value="'.wp_create_nonce( 'dolimember-nonce').'">';
		$modal['body'] .= doliuserform( $object, dolidelay('constante'), 'member', doliCheckRights('adherent', 'creer'));	
		$modal['body'] .= '<ul class="list-group list-group-flush"><li class="list-group-item"><button class="btn btn-danger" type="submit">'.__( 'Submit', 'doliconnect').'</button></form></li></ul';
		$modal['footer'] = null;
		$response['js'] = plugins_url( 'doliconnect/includes/js/editlinkedmember.js');
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer'], 'modal-lg', null, 'p-0');
		wp_send_json_success($response);
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "doliDownload" ) {
		$data = "data:application/pdf;base64,".$_POST['value2'];
		$modal['header'] = __( 'Download', 'doliconnect');
		$modal['body'] = null;
		$modal['footer'] = $_POST['value1'];
		$response['js'] = null;
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer'], 'modal-lg', null, 'p-0');
		wp_send_json_success($response);
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "doliCart" ) {
		$object = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order'));
		$modal['header'] = __( 'Cart', 'doliconnect');
		$modal['body'] = doliline($object, false, false);
		$modal['footer'] = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.__( "Continue shopping", "doliconnect").'</button> <a class="btn btn-primary" role="button" href="'.esc_url(doliconnecturl('dolicart')).'" >'.__( 'Finalize the order', 'doliconnect').'</a>';
		$response['js'] = null;
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer'], 'modal-lg', null, 'p-0');
		wp_send_json_success($response);
		die();
	} elseif ( wp_verify_nonce( trim($_POST['dolimodal-nonce']), 'dolimodal-nonce' ) && isset($_POST['case']) && $_POST['case'] == "doliSelectlang" ) {
		$modal['header'] = __('Choose your language', 'doliconnect');
		$modal['body'] = '<div class="card" id="doliSelectlang-form"><ul class="list-group list-group-flush">';
		$translations = pll_the_languages( array( 'post_id' => $_POST['value1'],'raw' => 1 ) );
		foreach ($translations as $key => $value) {
			$modal['body'] .= "<a href='".$value['url']."?".$_POST['value2']."' onclick='loadingDoliSelectlangModal()' class='list-group-item list-group-item-light list-group-item-action";
			if ( $value['current_lang'] == true ) { $modal['body'] .= ' active'; }
			$modal['body'] .= "'><span class='fi fi-".strtolower(substr($value['slug'], -2))."'></span> ".$value['name'];
			if ( $value['current_lang'] == true ) { $modal['body'] .= ' <i class="fas fa-language fa-fw"></i>'; }
			$modal['body'] .= '</a>';
		}      
		$modal['body'] .= '</ul></div><div id="loadingSelectLang" style="display:none"><br><br><br><center><div class="align-middle"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><h4>'.__('Loading', 'doliconnect').'</h4></div></center><br><br><br></div>';	
		$modal['footer'] = null;
		$response['js'] = plugins_url( 'doliconnect/includes/js/doliselectlang.js');
		$response['modal'] = doliModalTemplate($_POST['id'], $modal['header'], $modal['body'], $modal['footer']);
		wp_send_json_success($response);
		die();
	} else {
		wp_send_json_error( __( 'A security error occured', 'doliconnect'));	
		die(); 
	}
}