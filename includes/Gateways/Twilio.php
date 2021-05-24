<?php
/**
 * Twilio class
 *
 * Manage  Twilio related functionality on FormSms
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Gateways;

use WP_Error;
use Twilio\Rest\Client;


/**
 *  Twilio Class.
 *
 * @package  ChiliDevs\FormSMS\Gateways
 */
class Twilio implements GatewayInterface {
	/**
	 * Send sms via Twilio
	 *
	 * @param array $form_data hold form number and body.
	 * @param array $options Keep gateway settings data.
	 *
	 * @return array
	 */
	public function send( $form_data, $options, $form_entry ) {
		$twilio_account_sid   = ! empty( $options['twilio_account_sid'] ) ? $options['twilio_account_sid'] : '';
		$twilio_auth_token    = ! empty( $options['twilio_auth_token'] ) ? $options['twilio_auth_token'] : '';
		$twilio_source_number = ! empty( $options['twilio_source_number'] ) ? $options['twilio_source_number'] : '';


		if ( '' === $twilio_account_sid || '' === $twilio_source_number) {
			return new WP_Error( 'no-gateway-settings', __( 'No API key or Token found', 'form-sms' ), [ 'status' => 401 ] );
		}

		if ( empty( $form_data['number'] ) ) {
			return new WP_Error( 'no-number-found', __( 'No number found for sending SMS', 'form-sms' ), [ 'status' => 401 ] );
		}


		$account_sid = $twilio_account_sid;
		$auth_token  = $twilio_auth_token;


		$twilio_number = $twilio_source_number;

		$client = new Client( $account_sid, $auth_token );

		$message = $client->messages->create(
			$form_data['number'],
			array(
				'from' => $twilio_number,
				'body' => $form_data['body'],
			)
		);
		if ( 'queued' === $message->status ) {
			$response = [
				'message'  => __( 'SMS sent successfully', 'form-sms' ),
				'response' => $message,
			];
			do_action( 'sms_sent_successfully', $response, $form_data['form_name'], $form_entry );
			return $response;
		} else {
			$response = [
				'message'  => __( 'The message failed with status:', 'form-sms' ),
				'response' => $message,
			];
			do_action( 'sms_sent_fail', $response, $form_data['form_name'], $form_entry );
			return $response;
		}
	}
}
