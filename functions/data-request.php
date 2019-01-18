<?php

function gdrf_data_request() {
	$gdrf_error      = array();
	$gdrf_type       = sanitize_key( $_POST['gdrf_data_type'] );
	$gdrf_email      = sanitize_email( $_POST['gdrf_data_email'] );
	$gdrf_nonce      = esc_html( filter_input( INPUT_POST, 'gdrf_data_nonce', FILTER_SANITIZE_STRING ) );

	if ( ! function_exists( 'wp_create_user_request' ) ) {
		wp_send_json_success( esc_html__( 'The request can’t be processed on this website. This feature requires WordPress 4.9.6 at least.', 'doliconnect' ) );
		die();
	}

	if ( ! empty( $gdrf_email ) && ! empty( $gdrf_type ) ) {
		if ( ! wp_verify_nonce( $gdrf_nonce, 'gdrf_nonce' ) ) {
			$gdrf_error[] = esc_html__( 'Security check failed, please refresh this page and try to submit the form again.', 'doliconnect' );
		} else {
			if ( ! is_email( $gdrf_email ) ) {
				$gdrf_error[] = esc_html__( 'This is not a valid email address.', 'gdpr-data-request-form' );
			}
			if ( ! in_array( $gdrf_type, array( 'export_personal_data', 'remove_personal_data' ), true ) ) {
				$gdrf_error[] = esc_html__( 'Request type invalid, please refresh this page and try to submit the form again.', 'doliconnect' );
			}
		}
	} else {
		$gdrf_error[] = esc_html__( 'All fields are required.', 'gdpr-data-request-form' );
	}
	if ( empty( $gdrf_error ) ) {
		$request_id = wp_create_user_request( $gdrf_email, $gdrf_type );
		if ( is_wp_error( $request_id ) ) {
			wp_send_json_success( $request_id->get_error_message() );
		} elseif ( ! $request_id ) {
			wp_send_json_success( esc_html__( 'Unable to initiate confirmation request. Please contact the administrator.', 'doliconnect' ) );
		} else {
			$send_request = wp_send_user_request( $request_id );
			wp_send_json_success( 'success' );
		}
	} else {
		wp_send_json_success( join( '<br />', $gdrf_error ) );
	}
	die();
}