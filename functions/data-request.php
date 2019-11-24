<?php
/**
 * Data Request Handler.
 *
 * @link       http://jeanbaptisteaudras.com
 * @since      1.0
 *
 * @package    gdpr-data-request-form
 * @subpackage gdpr-data-request-form/includes
 * @prefix     gdrf_
 */

function gdrf_data_request() {
	$gdrf_error     = array();
	$gdrf_type      = esc_html( filter_input( INPUT_POST, 'gdrf_data_type', FILTER_SANITIZE_STRING ) );
	$gdrf_email     = sanitize_email( $_POST['gdrf_data_email'] );
	$gdrf_human     = absint( filter_input( INPUT_POST, 'gdrf_data_human', FILTER_SANITIZE_NUMBER_INT ) );
	$gdrf_human_key = esc_html( filter_input( INPUT_POST, 'gdrf_data_human_key', FILTER_SANITIZE_STRING ) );
	$gdrf_numbers   = explode( '000', $gdrf_human_key );
	$gdrf_answer    = absint( $gdrf_numbers[0] ) + absint( $gdrf_numbers[1] );
	$gdrf_nonce     = esc_html( filter_input( INPUT_POST, 'gdrf_data_nonce', FILTER_SANITIZE_STRING ) );

	if ( ! function_exists( 'wp_create_user_request' ) ) {
		wp_send_json_success( esc_html__( 'The request can’t be processed on this website. This feature requires WordPress 4.9.6 at least.', 'gdpr-data-request-form' ) );
		die();
	}

	if ( ! empty( $gdrf_email ) && ! empty( $gdrf_human ) ) {
		if ( ! wp_verify_nonce( $gdrf_nonce, 'gdrf_nonce' ) ) {
			$gdrf_error[] = esc_html__( 'Security check failed, please refresh this page and try to submit the form again.', 'gdpr-data-request-form' );
		} else {
			if ( ! is_email( $gdrf_email ) ) {
				$gdrf_error[] = esc_html__( 'This is not a valid email address.', 'gdpr-data-request-form' );
			}
			if ( intval( $gdrf_answer ) !== intval( $gdrf_human ) ) {
				$gdrf_error[] = esc_html__( 'Security check failed, invalid human verification field.', 'gdpr-data-request-form' );
			}
			if ( ! in_array( $gdrf_type, array( 'export_personal_data', 'remove_personal_data' ) ) ) {
				$gdrf_error[] = esc_html__( 'Request type invalid, please refresh this page and try to submit the form again.', 'gdpr-data-request-form' );
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
			wp_send_json_success( esc_html__( 'Unable to initiate confirmation request. Please contact the administrator.', 'gdpr-data-request-form' ) );
		} else {
			$send_request = wp_send_user_request( $request_id );
			wp_send_json_success( 'success' );
		}
	} else {
		wp_send_json_success( join( '<br />', $gdrf_error ) );
	}
	die();
}

add_action( 'wp_ajax_gdrf_data_request', 'gdrf_data_request' );
add_action( 'wp_ajax_nopriv_gdrf_data_request', 'gdrf_data_request' );
