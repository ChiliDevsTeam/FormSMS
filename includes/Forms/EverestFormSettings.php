<?php
/**
 * EverestFormSettings  class
 *
 * Manage  EverestFormSettings related functionality
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Forms;

use WP_Error;

/**
 * EverestFormSettings Class.
 *
 * @package ChiliDevs\FormSMS\Forms
 */
class EverestFormSettings {

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'everest_forms_builder_settings_section', [ $this, 'add_settings_tab' ], 1, 20 );
		add_action( 'everest_forms_builder_content_settings', [ $this, 'sms_settings_content' ] );
		add_action( 'everest_forms_email_send_after', [ $this, 'send_message' ] );
	}

	/**
	 * Add a custom tab on form settings.
	 *
	 * @param array $tabs Setting Tab.
	 *
	 * @return  $tabs
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['sms_settings'] = 'Sms Settings';
		return $tabs;
	}

	/**
	 * Outputs the Sms Settings builder content.
	 */
	public function sms_settings_content() {
		$form_id = ! empty( $_GET['form_id'] ) ? $_GET['form_id'] : '';
		if ( ! $form_id ) {
			return;
		}

		$form_obj     = evf()->form->get( $form_id );
		$post_content = $form_obj->post_content;
		$form_data    = json_decode( $post_content );
		$admin_phone  = $form_data->settings->admin_phone;
		$message_body = $form_data->settings->email->sms_settings->message_body;

		echo '<div class="evf-content-section evf-content-sms_settings-settings">';
		echo '<div class="evf-content-section-title">';
		esc_html_e( 'Sms Settings', 'form-sms' );
		echo '</div>';

		everest_forms_panel_field(
			'text',
			'settings',
			'admin_phone',
			$form_id,
			esc_html__( 'Admin Phone', 'form-sms' ),
			array(
				'default' => isset( $admin_phone ) ? $admin_phone : '',
				'tooltip' => esc_html__( 'Enter Admin Phone Number', 'form-sms' ),
			)
		);

		everest_forms_panel_field(
			'tinymce',
			'email',
			'message_body',
			'sms_settings',
			esc_html__( 'Message Body', 'form-sms' ),
			array(
				'default'    => isset( $message_body ) ? $message_body : '',
				/* translators: %1$s - general settings docs url */
				'tooltip'    => sprintf( esc_html__( 'Enter the message tag fields. ', 'form-sms' ) ),
				'smarttags'  => array(
					'type'        => 'all',
					'form_fields' => 'all',
				),
				'parent'     => 'settings',
				'subsection' => 'sms_settings',
				/* translators: %s - all fields smart tag. */
				'after'      => '<p class="desc">' . sprintf( esc_html__( 'To display all form fields, use the %s Smart Tag.', 'form-sms' ), '<code>{all_fields}</code>' ) . '</p>',
			)
		);

		echo '</div>';
	}

	/**
	 * Outputs the Sms Settings builder content.
	 *
	 * @param obeject $form FormObject.
	 */
	public function send_message( $form ) {
		$options = get_option( 'form_sms_settings' );

		if ( empty( $options['sms_gateway'] ) ) {
			return new WP_Error( 'no-options', __( 'Please set your settings first', 'form-sms' ), [ 'status' => 401 ] );
		}

		$admin_phone = $form->form_data['settings']['admin_phone'];
		$body        = $form->form_data['settings']['email']['sms_settings']['message_body'];
		$body        = apply_filters( 'everest_forms_process_smart_tags', $body, $form->form_data, $form->fields, $form->entry_id );
		$form_name   = 'EverestForm';

		$form_data = [
			'number'    => ! empty( $admin_phone ) ? $admin_phone : '',
			'body'      => $body,
			'form_name' => $form_name,
		];

		$sms_gateway   = $options['sms_gateway'];
		$classname     = form_sms_class_mapping( $sms_gateway );
		$gateway_class = new $classname();
		$gateway       = $gateway_class->send( $form_data, $options );

		if ( is_wp_error( $gateway ) ) {
			return $gateway->get_error_message();
		}

	}

}
