<?php
/**
 * Cf7Settings class
 *
 * Manage Cf7Settings related functionality
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Forms;

use WP_Error;

/**
 * Cf7Settings Class.
 *
 * @package ChiliDevs\FormSMS\Forms
 */
class Cf7Settings {

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'wpcf7_editor_panels', [ $this, 'add_settings_panel' ], 10 );
		add_action( 'wpcf7_after_save', [ $this, 'save_sms_data' ] );
		add_action( 'wpcf7_mail_sent', array( $this, 'send_sms' ) );
	}

	/**
	 * Add settings tab in form editor
	 *
	 * @since 1.0.0
	 *
	 * @param array $panels Panels Array.
	 *
	 * @return array
	 */
	public function add_settings_panel( $panels ) {
		$panels['sms-settings'] = array(
			'title'    => __( 'SMS Settings', 'form-sms' ),
			'callback' => [ $this, 'editor_sms_settings' ],
		);

		return $panels;
	}

	/**
	 * Render form sms settings html
	 *
	 * @since 1.0.0
	 *
	 * @param Object $form Form Array.
	 *
	 * @return html|void
	 */
	public function editor_sms_settings( $form ) {
		$options = get_post_meta( $form->id(), '_sms_settings', true );
		?>
		<div id="sms-sortables" class="meta-box-sortables ui-sortable">
			<div id="maildiv" class="postbox ">
				<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'form-sms' ) ?>"><br></div>
				<h3 class="hndle" style="padding:12px;"><span><?php esc_html_e( 'Admin SMS Settings', 'form-sms' ); ?></span></h3>
				<div class="inside">
					<div class="mail-fields">
						<div class="half-left">
							<div class="mail-field">
								<label for="wpcf7-sms-recipient"><?php esc_html_e( 'Admin Phone Number:', 'form-sms' ); ?></label><br>
								<input type="text" id="wpcf7-sms-recipient" name="cf7_sms[phone]" class="large-text" size="70" value="<?php echo ! empty( $options['phone'] ) ? esc_attr( $options['phone'] ) : ''; ?>">
								<p><i><?php echo wp_kses_post( sprintf( __( 'Insert your phone number (e.g.: <code>%s</code>)', 'form-sms' ), '+8801673322116'  ) ) ?></i></p>
							</div>
						</div>
						<br>
						<div class="half-right">
							<div class="mail-field">
								<label for="wpcf7-mail-body"><?php esc_html_e( 'Enter SMS body:', 'form-sms' ) ?></label><br>
								<p>
									<?php echo esc_html( __( "In the following fields, you can use these mail-tags:", 'form-sms' ) ); ?><br />
									<?php $form->suggest_mail_tags( 'sms-settings' ); ?></legend>
								</p>
								<textarea id="wpcf7-mail-body" name="cf7_sms[message]" class="large-text" rows="8"><?php echo ! empty( $options['message'] ) ? esc_attr( $options['message'] ) : ''; ?></textarea>
								<p><i><?php esc_html_e( 'Enter your custom SMS text. Just follow the Mail -> Message Body section convention', 'form-sms' ); ?></i></p>
							</div>
						</div>

						<br class="clear">
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save sms form data
	 *
	 * @since 1.0.0
	 *
	 * @param Object $form Form Object.
	 *
	 * @return void
	 */
	public function save_sms_data( $form ) {
		if ( empty( $form->id() ) ) {
			return;
		}

		if ( ! wpcf7_admin_has_edit_cap() ) {
			return;
		}

		$postdata = wp_unslash( $_POST['cf7_sms'] );

		$data = array(
			'phone'   => ! empty( $postdata['phone'] ) ? sanitize_text_field( $postdata['phone'] ) : '',
			'message' => ! empty( $postdata['message'] ) ? sanitize_textarea_field( $postdata['message'] ) : '',
		);

		update_post_meta( $form->id(), '_sms_settings', $data );
	}

	/**
	 * Send SMS when form submitted
	 *
	 * @since 1.0.0
	 *
	 * @param Object $form Obj.
	 *
	 * @return WP_Error | void
	 */
	public function send_sms( $form ) {
		$options = get_option( 'form_sms_settings' );

		if ( empty( $options['sms_gateway'] ) ) {
			return new WP_Error( 'no-options', __( 'Please set your settings first', 'form-sms' ), [ 'status' => 401 ] );
		}

		$replace       = array();
		$form_settings = get_post_meta( $form->id(), '_sms_settings', true );

		preg_match_all("/\[(.*?)\]/", $form_settings['message'], $matches );

		$find     = $matches[0];
		$postdata = wp_unslash( $_POST );

		foreach ( $matches[1] as $value ) {
			$replace[] = ! empty( $postdata[$value] ) ? sanitize_text_field( $postdata[$value] ) : '';
		}

		$body      = str_replace( $find, $replace, $form_settings['message'] );
		$form_name = 'ContactForm-7';

		$form_data = [
			'number'    => ! empty( $form_settings['phone'] ) ? $form_settings['phone'] : '',
			'body'      => $body,
			'form_name' => $form_name,
		];

		$sms_gateway = $options['sms_gateway'];

		$classname = form_sms_class_mapping( $sms_gateway );

		$gateway_class = new $classname();
		$gateway       = $gateway_class->send( $form_data, $options );

		if ( is_wp_error( $gateway ) ) {
			return $gateway->get_error_message();
		}

		do_action( 'cf7_sms_sent', $gateway, $form );
	}

}
