<?php
/**
 * MessageBird class
 *
 * Manage  MessageBird related functionality on FormSms
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Gateways;

use WP_Error;
use MessageBird\Client;
use MessageBird\Objects\Message;


/**
 *  MessageBird Class.
 *
 * @package ChiliDevs\FormSMS\Gateways
 */
class MessageBird implements GatewayInterface {
	/**
	 * Send sms via MessageBird
	 *
	 * @param array $form_data hold form number and body.
	 * @param array $options Keep gateway settings data.
	 *
	 * @return array
	 */
	public function send( $form_data, $options, $form_entry ) {
		$live_key = ! empty( $options['messagebird_live_key'] ) ? $options['messagebird_live_key'] : '';
		$test_key = ! empty( $options['messagebird_test_key'] ) ? $options['messagebird_test_key'] : '';
		$mode     = ! empty( $options['messagebird_is_test_mode'] ) ? $options['messagebird_is_test_mode'] : 'no';

		if ( '' === $live_key || '' === $test_key ) {
			return new WP_Error( 'no-gateway-settings', __( 'No live or test key found', 'form-sms' ), [ 'status' => 401 ] );
		}

		if ( empty( $form_data['number'] ) ) {
			return new WP_Error( 'no-number-found', __( 'No number found for sending SMS', 'form-sms' ), [ 'status' => 401 ] );
		}

		$access_key = ( 'yes' === $mode ) ? $test_key : $live_key;

		$message_bird = new Client( $access_key );
		$message      = new Message();

		$message->originator = 'MessageBird';
		$message->recipients = array( $form_data['number'] );
		$message->body       = $form_data['body'];

		$result = $message_bird->messages->create( $message );

		if ( ! empty( $result ) ) {
			$response = [
				'message'  => __( 'SMS sent successfully', 'form-sms' ),
				'response' => $message,
			];
			do_action( 'sms_sent_successfully', $response, $form_data['form_name'], $form_entry );
			return $response;
		} else {
			$response = [
				'message'  => __( 'The message failed with status:', 'form-sms' ) . $message->getStatus(),
				'response' => $message,
			];
			do_action( 'sms_sent_fail', $response, $form_data['form_name'], $form_entry );
			return $response;
		}
	}
}
