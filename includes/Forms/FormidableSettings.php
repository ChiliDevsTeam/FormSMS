<?php
/**
 * FormSettings  class
 *
 * Manage  FormSettings related functionality
 *
 * @package ChiliDevs\TextyForms
 */

declare(strict_types=1);

namespace ChiliDevs\TextyForms\Forms;

use WP_Error;

/**
 * Formidable FormSettings Class.
 *
 * @package ChiliDevs\TextyForms\Forms
 */
class FormidableSettings {

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'frm_add_form_settings_section', [ $this, 'add_settings_panel' ], 10, 2 );
		add_filter( 'frm_form_options_before_update', [ $this, 'save_sms_data' ], 40, 2 );
		add_action( 'frm_after_create_entry', array( $this, 'send_sms' ), 30, 2);
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
	public function add_settings_panel( $panels, $values) {
		$panels[] = array(
			'name'     => __( 'SMS Settings', 'texty-forms' ),
			'anchor'   => 'sms-settings',
			'function' => [ $this ,'editor_sms_settings' ],
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
	public function editor_sms_settings( $values ) {
		$my_form_opts = maybe_unserialize( get_option('frm_mysettings_' . $values['id'] ) );

		?>
			<h3 class="frm_first_h3"><?php _e( 'SMS Settings', 'texty-forms' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title= "<?php esc_html_e('This is SMS Settings for this form.','texty-forms') ?>"</span>
        	</h3>
			<p class="frm6 frm_form_field">
				<label for="frm_form_sms_admin_phone">
					<?php esc_html_e( 'Admin Phone Number:', 'texty-forms' ); ?>
				</label>
				<input type="text" id="frm_form_sms" name="frm_sms[phone]" value="<?php echo ! empty( $values['sms_settings']['phone'] ) ? esc_attr( $values['sms_settings']['phone'] ) : ''; ?>" />
				<p><i><?php echo wp_kses_post( sprintf( __( 'Insert your phone number (e.g.: <code>%s</code>)', 'texty-forms' ), '+8801673322116'  ) ) ?></i></p>
			</p>
			<p class="frm_has_shortcodes">
				<label for="frm_message_body">
					<?php esc_html_e( 'Enter SMS Body:', 'formidable' ); ?>
				</label>
				<textarea id="frm_form_sms_body" name="frm_sms[message]" cols="50" rows="4"><?php echo ! empty( $values['sms_settings']['message'] ) ? esc_attr( $values['sms_settings']['message'] ) : ''; ?></textarea>
				<p><i><?php esc_html_e( 'Enter your custom SMS text. Just follow the Mail -> Message Body section convention', 'texty-forms' ); ?></i></p>
			</p>

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
	public function save_sms_data( $options, $values ) {
		if ( empty( $values['id'] ) ) {
			return;
		}

		$post_val = wp_unslash( $_POST );

		if( ! empty( $post_val['frm_sms'] ) ) {
			$options['sms_settings'] = $values['frm_sms'];
		}

		return $options;
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
	function send_sms( $entry_id, $form_id ) {
		$options = get_option( 'textyforms_sms_settings' );

		$entry = \FrmEntry::getOne( $entry_id );
		$form = \FrmForm::getOne( $form_id );

		$admin_phone = $form->options['sms_settings']['phone'];
		$body        = $form->options['sms_settings']['message'];

		$message = \FrmFieldsHelper::basic_replace_shortcodes( $body, $form, $entry );
		$prev_mail_body = $message;
		$pass_entry     = clone $entry; // make a copy to prevent changes by reference
		$message_body   = \FrmEntriesHelper::replace_default_message(
			$prev_mail_body,
			array(
				'id'        => $entry_id,
				'entry'     => $pass_entry,
			)
			);
			$final_message_body = strip_tags( $message_body );
			$form_data 			= [
						'number' => ! empty( $admin_phone ) ? $admin_phone : '',
						'body'   => $final_message_body,
			];
		$sms_gateway   = $options['sms_gateway'];
		$classname     = textyforms_class_mapping( $sms_gateway );
		$gateway_class = new $classname();
		$gateway       = $gateway_class->send( $form_data, $options );

		if ( is_wp_error( $gateway ) ) {
			return $gateway->get_error_message();
		}
	}
}
