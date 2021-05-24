<?php
/**
 * GatewayInterface Interface
 *
 * Manage  Getway related functionality on Wp Form
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Gateways;

use WP_Error;

/**
 * GatewayInterface Interface.
 *
 * @package ChiliDevs\FormSMS\Gateways
 */
interface GatewayInterface {
	/**
	 * Send SMS via gateways
	 *
	 * @param array $form_data Hold form data.
	 * @param array $options Keep all gateway settings.
	 *
	 * @return array
	 */
	public function send( $form_data, $options, $form_entry );
}
