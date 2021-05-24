<?php
/**
 * MessageMedia class
 *
 * Manage  MessageMedia related functionality on FormSms
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Gateways;

use WP_Error;
use MessageMediaMessagesLib\Models;
use MessageMediaMessagesLib\Exceptions;


/**
 *  MessageMedia Class.
 *
 * @package ChiliDevs\FormSMS\Gateways
 */
class MessageMedia implements GatewayInterface {
	/**
	 * Send sms via MessageMedia
	 *
	 * @param array $form_data hold form number and body.
	 * @param array $options Keep gateway settings data.
	 *
	 * @return array
	 */
	public function send( $form_data, $options, $form_entry ) {
		$messagemedia_api_key    = ! empty( $options['messagemedia_api_key'] ) ? $options['messagemedia_api_key'] : '';
		$messagemedia_api_secret = ! empty( $options['messagemedia_api_secret'] ) ? $options['messagemedia_api_secret'] : '';
		$useHmacAuthentication   = false;

		if ( '' === $messagemedia_api_key || '' === $messagemedia_api_secret ) {
			return new WP_Error( 'no-gateway-settings', __( 'No API key or secret', 'form-sms' ), [ 'status' => 401 ] );
		}

		if ( empty( $form_data['number'] ) ) {
			return new WP_Error( 'no-number-found', __( 'No number found for sending SMS', 'form-sms' ), [ 'status' => 401 ] );
		}

		$client = new \MessageMediaMessagesLib\MessageMediaMessagesClient( $messagemedia_api_key, $messagemedia_api_secret, $useHmacAuthentication );

		$messages_controller = $client->getMessages();

		$body           = new Models\SendMessagesRequest();
		$body->messages = array();

		$body->messages[0]                    = new Models\Message();
		$body->messages[0]->content           = $form_data['body'];
		$body->messages[0]->destinationNumber = $form_data['number'];

		try {
			$result = $messages_controller->sendMessages( $body );

			$response = [
				'message'  => __( 'SMS sent successfully', 'form-sms' ),
				'response' => $result,
			];
			do_action( 'sms_sent_successfully', $response, $form_data['form_name'], $form_entry );
			return $response;

		} catch ( Exceptions\SendMessages400Response $e ) {
			echo 'Caught SendMessages400Response: ',  $e->getMessage(), "\n";
			do_action( 'sms_sent_fail', $response, $form_data['form_name'], $form_entry );
		} catch ( \MessageMediaMessagesLib\APIException $e ) {
			echo 'Caught APIException: ',  $e->getMessage(), "\n";
		}

	}
}
