<?php
/**
 * GravityFormSettings class
 *
 * Manage  GravityFormSettings related functionality 
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Forms;

use WP_Error;

/**
 * GravityFormSettings Class.
 *
 * @package ChiliDevs\FormSMS\Forms
 */
class GravityFormSettings {

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'gform_pre_form_settings_save', [ $this, 'save_gravity_custom_form_setting' ] );
		add_filter( 'gform_form_settings', [ $this, 'gravity_forms_custom_form_setting' ], 10, 2 );
		add_action( 'gform_after_submission', [ $this, 'after_form_submission' ], 10, 2 );
		add_filter( 'gform_tooltips', [ $this, 'add_tooltips' ] );
	}

	/**
	 * Send SMS After form submission
	 *
	 * @param array $entry Entry Array.
	 * @param array $form From Array.
	 *
	 * @return $gateway
	 */
	public function after_form_submission( $entry, $form ) {
		$options = get_option( 'form_sms_settings' );

		if ( empty( $options['sms_gateway'] ) ) {
			return new WP_Error( 'no-options', __( 'Please set your settings first', 'form-sms' ), [ 'status' => 401 ] );
		}
		if ( isset( $form['gravity_forms_sms_enable'] ) && '' == $form['gravity_forms_sms_enable'] ) {
			return;
		}

		if ( isset( $form['gravity_forms_sms_phone_number'] ) && '' == $form['gravity_forms_sms_phone_number'] ) {
			return;
		}

		// Don't send spam over.
		if ( 'spam' == $entry['status'] ) {
			return;
		}
		$admin_phone = $form['gravity_forms_sms_phone_number'];
		$body        = $form['gravity_forms_sms_body'];
		$body        = \GFCommon::replace_variables( $body, $form, $entry );
		$form_name   = 'GravityForm';

		$first_name = isset( $entry['1.3'] ) ? $entry['1.3'] : '';
		$last_name  = isset( $entry['1.6'] ) ? $entry['1.6'] : '';
		$name       = 'Name: ' . $first_name . ' ' . $last_name;
		$email      = isset( $entry[2] ) ? 'Email: ' . $entry[2] : '';
		$message    = isset( $entry[5] ) ? 'Message: ' . $entry[5] : '';
		$phone      = isset( $entry[6] ) ? 'Phone: ' . $entry[6] : '';
		$date       = isset( $entry[7] ) ? 'Date: ' . $entry[7] : '';
		$time       = isset( $entry[8] ) ? 'Time: ' . $entry[8] : '';
		$number     = isset( $entry[10] ) ? 'Number: ' . $entry[10] : '';

		$form_entry = [
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'date'    => $date,
			'time'    => $time,
			'number'  => $number,
		];

		$form_entry = array_filter( $form_entry ); 

		$form_data = [
			'number'    => ! empty( $admin_phone ) ? $admin_phone : '',
			'body'      => $body,
			'form_name' => $form_name,
		];

		$sms_gateway   = $options['sms_gateway'];
		$classname     = form_sms_class_mapping( $sms_gateway );
		$gateway_class = new $classname();
		$gateway       = $gateway_class->send( $form_data, $options, $form_entry );

		if ( is_wp_error( $gateway ) ) {
			return $gateway->get_error_message();
		}

		$form_name = 'GravityForms';
		do_action( 'get_form_name', $form_name );
	}

	/**
	 * Add SMS body and number settings
	 *
	 * @param array $settings Settings Array.
	 * @param  array $form Form Array.
	 *
	 * @return $settings
	 */
	public function gravity_forms_custom_form_setting( $settings, $form ) {

		$enable_sms_checked = '';
		if ( rgar( $form, 'gravity_forms_sms_enable' ) ){
			$enable_sms_checked = 'checked="checked"';
		}

		$settings['SMS Settings']['gravity_forms_sms_enable'] = '
			<tr>
				<th><label for="gravity_forms_sms_enable">Enable SMS Notification' . gform_tooltip( 'gravity_forms_sms_enable', '', true ) . '</label></th>
				<td>
					<input id="gravity_forms_enable_sms" type="checkbox" value="1" ' . $enable_sms_checked . ' name="gravity_forms_sms_enable">
					<label for="gravity_forms_enable_sms">' . __( 'If checked then SMS notification system is enable for this form', 'form-sms' ) . '</label>
				</td>
			</tr>';

		$settings['SMS Settings']['gravity_forms_sms_phone_number'] = '
			<tr>
				<th><label for="gravity_forms_sms_phone_number">Admin Phone Number ' . gform_tooltip( 'gravity_forms_sms_phone_number', '', true ) . '</label></th>
				<td><input type="text" value="' . rgar( $form, 'gravity_forms_sms_phone_number' ) . '" class="fieldwidth-3" name="gravity_forms_sms_phone_number"></td>
			</tr>';

		ob_start();
		?>

		<tr>
			<th><label for="gravity_forms_sms_body">SMS Body <?php echo gform_tooltip('gravity_forms_sms_body', '', true ); ?></label></th>
			<td>
				<select id="gravity_forms_sms_body_variable_select" onchange="InsertVariable('gravity_forms_sms_body');">
					<?php
						$form_meta = \RGFormsModel::get_form_meta( $form['id'] );
						echo self::get_form_fields( $form_meta );
					?>
				</select><br/>
				<textarea id="gravity_forms_sms_body" name="gravity_forms_sms_body" style="height: 150px; width:410px;"><?php echo rgar( $form, 'gravity_forms_sms_body' ); ?></textarea>
			</td>
		</tr>

			<script>
				function InsertVariable(element_id, callback, variable){
					if(!variable)
						variable = jQuery('#' + element_id + '_variable_select').val();

					var messageElement = jQuery("#" + element_id);

					if(document.selection) {
						// Go the IE way
						messageElement[0].focus();
						document.selection.createRange().text=variable;
					}
					else if(messageElement[0].selectionStart) {
						// Go the Gecko way
						obj = messageElement[0]
						obj.value = obj.value.substr(0, obj.selectionStart) + variable + obj.value.substr(obj.selectionEnd, obj.value.length);
					}
					else {
						messageElement.val(variable + messageElement.val());
					}

					jQuery('#' + element_id + '_variable_select')[0].selectedIndex = 0;

					if(callback && window[callback])
						window[callback].call();
				}

			</script>
		<?php
		$settings['SMS Settings']['gravity_forms_sms_body'] = ob_get_clean();
		return $settings;
	}

	/**
	 * Save form-sms custom settings
	 *
	 * @param  array $form Form Form array.
	 *
	 * @return array $form
	 */
	public function save_gravity_custom_form_setting( $form ) {
		$form['gravity_forms_sms_enable']       = rgpost( 'gravity_forms_sms_enable' );
		$form['gravity_forms_sms_phone_number'] = rgpost( 'gravity_forms_sms_phone_number' );
		$form['gravity_forms_sms_body']         = rgpost( 'gravity_forms_sms_body' );
		return $form;
	}

	/**
	 * Get all form fields according to form
	 *
	 * @param array $form Form Array.
	 *
	 * @return string
	 */
	public static function get_form_fields( $form ) {

		$str = "<option value=''>" . __( 'Insert merge code', 'form-sms' ) . "</option>";

		$required_fields = array();
		$optional_fields = array();
		$pricing_fields  = array();

		foreach ( $form['fields'] as $field ) {
			if ( rgar( $field, 'displayOnly' ) )
				continue;

			$input_type = \RGFormsModel::get_input_type( $field );

			if ( $field['isRequired'] ) {

				switch ( $input_type ) {
					case 'name':
						if ( $field['nameFormat'] == 'extended' ){
							$prefix = \GFCommon::get_input( $field, $field['id'] + 0.2 );
							$suffix = \GFCommon::get_input( $field, $field['id'] + 0.8 );
							$optional_field = $field;
							$optional_field['inputs'] = array( $prefix, $suffix );

							// Add optional name fields to the optional list.
							$optional_fields[] = $optional_field;

							// Remove optional name field from required list.
							unset( $field["inputs"][0] );
							unset( $field["inputs"][3] );
						}

						$required_fields[] = $field;
						break;


					default:
						$required_fields[] = $field;
				}
			} else {
				$optional_fields[] = $field;
			}

			if ( \GFCommon::is_pricing_field( $field['type'] ) ) {
				$pricing_fields[] = $field;
			}
		}

		if ( ! empty( $required_fields ) ) {
			$str .= "<optgroup label='" . __( 'Required form fields', 'form-sms' ) . "'>";

			foreach ( $required_fields as $field ) {
				$str .= self::get_field_variable( $field );
			}

			$str .= "</optgroup>";
		}

		if ( ! empty( $optional_fields ) ) {
			$str .= "<optgroup label='" . __( 'Optional form fields', 'form-sms' ) . "'>";
			foreach ( $optional_fields as $field ) {
				$str .= self::get_field_variable( $field );
			}
			$str .= "</optgroup>";
		}

		if ( ! empty( $pricing_fields ) ) {
			$str .= "<optgroup label='" . __( 'Pricing form fields', 'form-sms' ) . "'>";

			foreach ( $pricing_fields as $field ) {
				$str .= self::get_field_variable( $field );
			}
			$str .= "</optgroup>";
		}

		$str .= "<optgroup label='" . __( 'Other', 'form-sms' ) . "'>
				<option value='{ip}'>" . __( 'Client IP Address', 'form-sms' ) ."</option>
				<option value='{date_mdy}'>" . __( 'Date', 'form-sms' ) . " (mm/dd/yyyy)</option>
				<option value='{date_dmy}'>" . __( 'Date', 'form-sms' ) . " (dd/mm/yyyy)</option>
				<option value='{embed_post:ID}'>" . __( 'Embed Post/Page Id', 'form-sms' ) . "</option>
				<option value='{embed_post:post_title}'>" . __( 'Embed Post/Page Title', 'form-sms' ) . "</option>
				<option value='{embed_url}'>" . __( 'Embed URL', 'form-sms' ) . "</option>
				<option value='{entry_id}'>" . __( 'Entry Id', 'form-sms' ) . "</option>
				<option value='{entry_url}'>" . __( 'Entry URL', 'form-sms' ) . "</option>
				<option value='{form_id}'>" . __( 'Form Id', 'form-sms' ) . "</option>
				<option value='{form_title}'>" . __( 'Form Title', 'form-sms' ) . "</option>
				<option value='{user_agent}'>" . __( 'HTTP User Agent', 'form-sms' ) . "</option>";

		if ( \GFCommon::has_post_field( $form['fields'] ) ) {
			$str .= "<option value='{post_id}'>" . __( 'Post Id', 'form-sms' ) . "</option>
					<option value='{post_edit_url}'>" . __( 'Post Edit URL', 'form-sms' ) . "</option>";
		}

		$str .= "<option value='{user:display_name}'>" . __( 'User Display Name', 'form-sms' ) . "</option>
				<option value='{user:user_email}'>" . __( 'User Email', 'form-sms' ) . "</option>
				<option value='{user:user_login}'>" . __( 'User Login', 'form-sms' ) ."</option>
		</optgroup>";

		return $str;
	}

	/**
	 * Get specific field variables
	 *
	 * @param  array   $field Field Array.
	 * @param integer $max_label_size Integer.
	 *
	 * @return sring
	 */
	public static function get_field_variable( $field, $max_label_size = 40 ) {
		$str = '';
		if ( is_array( $field['inputs'] ) ) {
			foreach ( $field['inputs'] as $input ) {
				$str .= "<option value = '{" . esc_attr( \GFCommon::get_label( $field, $input['id'] ) ) . ':' . $input['id'] . "}'>" . esc_html( \GFCommon::truncate_middle( \GFCommon::get_label( $field, $input['id'] ), $max_label_size ) ) . "</option>";
			}
		} else {
			$str .= "<option value='{" . esc_html( \GFCommon::get_label( $field ) ) . ':' . $field['id'] . "}'>" . esc_html( \GFCommon::truncate_middle( \GFCommon::get_label( $field ), $max_label_size ) ) . "</option>";
		}

		return $str;
	}

	/**
	 * Add Tooltips with individual settings
	 *
	 * @param array $tooltips Tooltip.
	 */
	public function add_tooltips( $tooltips ) {
		$tooltips['gravity_forms_sms_enable']       = 'If checked then sms notification option is enable for this form submission';
		$tooltips['gravity_forms_sms_phone_number'] = 'Enter your phone number where message will be sent';
		$tooltips['gravity_forms_sms_body']         = 'Enter your SMS body here. Using side select box you can chose what field input you want to get with message';
		return $tooltips;
	}

}
