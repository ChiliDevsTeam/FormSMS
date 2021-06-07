<?php
/**
 * FormSettings  functions
 *
 * Manage  FormSettings related functionality
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

/**
 * Get option value for settings
 *
 * @since 1.0.0
 *
 * @param string $option Option String.
 * @param string $section Section String.
 * @param mixed  $default Default.
 *
 * @return mixed
 */
function form_sms_get_option( $option, $section, $default = '' ) {
	$options = get_option( $section );
	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}
	return $default;
}

	/**
	 * Get sms class name
	 *
	 * @param string $class_name SMS Class name.
	 *
	 * @return array
	 */
function form_sms_class_mapping( $class_name = '' ) {
	$classes = apply_filters( 'form_sms_class_map', [
		'nexmo'        => ChiliDevs\FormSMS\Gateways\Vonage::class,
		'clicksend'    => ChiliDevs\FormSMS\Gateways\ClickSend::class,
		'twilio'       => ChiliDevs\FormSMS\Gateways\Twilio::class,
		'telesign'     => ChiliDevs\FormSMS\Gateways\Telesign::class,
		'messagemedia' => ChiliDevs\FormSMS\Gateways\MessageMedia::class,
		'messagebird'  => ChiliDevs\FormSMS\Gateways\MessageBird::class,
		'plivo'        => ChiliDevs\FormSMS\Gateways\Plivo::class,
	] );
	return isset( $classes[ $class_name ] ) ? $classes[ $class_name ] : '';
}
