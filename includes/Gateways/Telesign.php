<?php
/**
 * Telesign class
 *
 * Manage  Telesign related functionality on FormSms
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Gateways;

use WP_Error;
use Telesign\Client;
use Telesign\Objects\Message;


/**
 *  Telesign Class.
 *
 * @package ChiliDevs\FormSMS\Gateways
 */
class Telesign implements GatewayInterface {
	/**
	 * Send sms via Telesign
	 *
	 * @param array $form_data hold form number and body.
	 * @param array $options Keep gateway settings data.
	 *
	 * @return array
	 */
	public function send( $form_data, $options, $form_entry ) {
		$customer_id = ! empty( $options['telesign_customer_id'] ) ? $options['telesign_customer_id'] : '';
		$api_key     = ! empty( $options['telesign_api_key'] ) ? $options['telesign_api_key'] : '';

		if ( '' === $customer_id || '' === $api_key ) {
			return new WP_Error( 'no-gateway-settings', __( 'No Customer Id Or Api key  found', 'form-sms' ), [ 'status' => 401 ] );
		}

		if ( empty( $form_data['number'] ) ) {
			return new WP_Error( 'no-number-found', __( 'No number found for sending SMS', 'form-sms' ), [ 'status' => 401 ] );
		}

		$messaging = new MessagingClient( $customer_id, $api_key );
		$response  = $messaging->message( $form_data['number'], $form_data['body'], 'ARN' );

		if ( $response->ok ) {
			$response = [
				'message'  => __( 'SMS sent successfully', 'form-sms' ),
				'response' => $response,
			];

			do_action( 'sms_sent_successfully', $response, $form_data['form_name'], $form_entry );
			return $response;
		} else {
			$response = [
				'message'  => __( 'The message failed with status:', 'form-sms' ),
				'response' => $response,
			];
			
			do_action( 'sms_sent_fail', $response, $form_data['form_name'], $form_entry );
			return $response;
		}
	}
}
