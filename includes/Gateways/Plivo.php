<?php
/**
 * Plivo class
 *
 * Manage  Plivo related functionality on FormSms
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Gateways;

use WP_Error;
use Plivo\RestClient;
use Exception;


/**
 *  Plivo Class.
 *
 * @package ChiliDevs\FormSMS\Gateways
 */
class Plivo implements GatewayInterface {
	/**
	 * Send sms via Plivo
	 *
	 * @param array $form_data hold form number and body.
	 * @param array $options Keep gateway settings data.
	 *
	 * @return array
	 */
	public function send( $form_data, $options, $form_entry ) {
		$plivo_auth_id       = ! empty( $options['plivo_auth_id'] ) ? $options['plivo_auth_id'] : '';
		$plivo_auth_token    = ! empty( $options['plivo_auth_token'] ) ? $options['plivo_auth_token'] : '';
		$plivo_source_number = ! empty( $options['plivo_source_number'] ) ? $options['plivo_source_number'] : 'plivo';

		if ( '' === $plivo_auth_id || '' === $plivo_auth_token ) {
			return new WP_Error( 'no-gateway-settings', __( 'No auth id or auth token found for sending SMS', 'form-sms' ), [ 'status' => 401 ] );
		}

		if ( empty( $form_data['number'] ) ) {
			return new WP_Error( 'no-number-found', __( 'No number found for sending SMS', 'form-sms' ), [ 'status' => 401 ] );
		}

		$client = new RestClient( $plivo_auth_id, $plivo_auth_token );

		try {
			$response = $client->messages->create(
				$plivo_source_number,
				[ $form_data['number'] ],
				$form_data['body']
			);
			
			do_action( 'sms_sent_successfully', $response, $form_data['form_name'], $form_entry );
		} catch ( Exception $e ) {
			do_action( 'sms_sent_fail', $response, $form_data['form_name'], $form_entry );
		}
		// phpcs:ignore.

	}
}
