<?php
/**
 * Admin class
 *
 * Manage all admin related functionality
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Admin;

use  ChiliDevs\FormSMS\Admin\SettingsAPI;
use function ChiliDevs\FormSMS\plugin;

/**
 * Admin class.
 *
 * @package ChiliDevs\FormSMS\Admin
 */
class Admin {
	/**
	 * Holde Settings API class
	 *
	 * @var $settings_api
	 *
	 * @since 1.0.0
	 */
	private $settings_api;

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		$this->settings_api = new SettingsAPI();
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'add_form_settings_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'chili_settings_form_bottom_form_sms_settings', [ $this, 'settings_gateway_fields' ] );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'admin-form-sms-scripts', plugin()->assets_dir . '/build/js/admin.build.js', array( 'jquery' ), false, true );
		wp_localize_script( 'admin-form-sms-scripts', 'wcmessagemedia', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	/**
	 * Initialize the settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Set the settings.
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		// Initialize settings.
		$this->settings_api->admin_init();
	}

	/**
	 * Added a Custom Menu
	 *
	 * @return void
	 */
	public function add_form_settings_menu() {
		add_menu_page(
			__( 'FormSMS', 'form-sms' ),
			__( 'FormSMS', 'form-sms' ),
			'manage_options',
			'form-sms',
			[ $this, 'form_sms_settings_page' ],
			'dashicons-admin-generic',
			10
		);
	}

	/**
	 * Plugin settings sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_settings_sections() {
		$sections = [
			[
				'id'    => 'form_sms_settings',
				'title' => '',
				'name'  => __( 'SMS Settings', 'form-sms' ),
				'icon'  => 'dashicons-admin-tools',
			],
		];

		return apply_filters( 'form_sms_get_settings_sections', $sections );
	}

	/**
	 * Returns all the settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array settings fields
	 */
	public function get_settings_fields() {
		$settings_fields = [
			'form_sms_settings' => [
				[
					'name'    => 'sms_gateway',
					'label'   => __( 'Select Gateway', 'form-sms' ),
					'desc'    => __( 'Select your sms gateway', 'form-sms' ),
					'type'    => 'select',
					'default' => '-1',
					'options' => $this->get_sms_gateway(),
				],
			],
		];

		return apply_filters( 'form_sms_get_settings_fields', $settings_fields );
	}

	/**
	 * Render setting content page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function form_sms_settings_page() {
		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'SMS Settings', 'form-sms' ); ?> </h1>
				<hr>
				<?php
					$this->settings_api->show_navigation();
					$this->settings_api->show_forms();
				?>
			</div>
		<?php
	}

	/**
	 * Get sms Gateway settings
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sms_gateway() {
		$gateway = array(
			''             => __( '--select--', 'form-sms' ),
			'nexmo'        => __( 'Vonage(Nexmo)', 'form-sms' ),
			'clicksend'    => __( 'ClickSend', 'form-sms' ),
			'telesign'     => __( 'Telesign', 'form-sms' ),
			'messagemedia' => __( 'MessageMedia', 'form-sms' ),
			'messagebird'  => __( 'MessageBird', 'form-sms' ),
			'twilio'       => __( 'Twilio', 'form-sms' ),
			'plivo'        => __( 'Plivo', 'form-sms' ),
		);

		return apply_filters( 'form_sms_gateway', $gateway );
	}

	/**
	 * Render settings gateway extra fields
	 *
	 * @since 1.0.0
	 *
	 * @return void|HTML
	 */
	public function settings_gateway_fields() {
		// Nexomo Properties.
		$nexmo_api        = form_sms_get_option( 'nexmo_api', 'form_sms_settings', '' );
		$nexmo_api_secret = form_sms_get_option( 'nexmo_api_secret', 'form_sms_settings', '' );
		$nexmo_from_name  = form_sms_get_option( 'nexmo_from_name', 'form_sms_settings', '' );
		$nexmo_helper     = sprintf( __( 'Enter your Vonage(Nexmo) details. Please visit <a href="%s" target="_blank">%s</a> and get your api keys and options', 'form-sms' ), 'https://dashboard.nexmo.com/login', 'Nexmo' );

		// Clicksend properties.
		$clicksend_username = form_sms_get_option( 'clicksend_username', 'form_sms_settings', '' );
		$clicksend_api      = form_sms_get_option( 'clicksend_api', 'form_sms_settings', '' );
		$clicksend_helper   = sprintf( __( 'Enter ClickSend details. Please visit <a href="%s" target="_blank">%s</a> and get your username and api keys', 'form-sms' ), 'https://dashboard.clicksend.com/signup', 'Clicksend' );
		
		// Telesign properties.
		$telesign_customer_id = form_sms_get_option( 'telesign_customer_id', 'form_sms_settings', '' );
		$telesign_api_key     = form_sms_get_option( 'telesign_api_key', 'form_sms_settings', '' );
		$telesign_helper      = sprintf( __( 'Enter your telesign Details. Please visit <a href="%s" target="_blank">%s</a> and get your api keys and options', 'form-sms' ), 'https://portal.telesign.com/login', 'telesign' );

		// Messagemedia properties.
		$messagemedia_api_key    = form_sms_get_option( 'messagemedia_api_key', 'form_sms_settings', '' );
		$messagemedia_api_secret = form_sms_get_option( 'messagemedia_api_secret', 'form_sms_settings', '' );
		$messagemedia_helper     = sprintf( __( 'Enter MessageMedia details. Please visit <a href="%s" target="_blank">%s</a> and get your api key and api secret', 'form-sms' ), 'https://hub.messagemedia.com/register', 'MessageMedia' );

		// Messagebird properties.
		$messagebird_is_test_mode = form_sms_get_option( 'messagebird_is_test_mode', 'form_sms_settings', '' );
		$messagebird_live_key     = form_sms_get_option( 'messagebird_live_key', 'form_sms_settings', '' );
		$messagebird_test_key     = form_sms_get_option( 'messagebird_test_key', 'form_sms_settings', '' );
		$messagebird_helper       = sprintf( __( 'Enter your MessageBird API details. Please visit <a href = "%s" target="_blank">%s</a> and get your api keys and options', 'form-sms' ), 'https://www.messagebird.com/en/pricing/', 'MessageBird' );

		// Twlio properties.
		$twilio_account_sid   = form_sms_get_option( 'twilio_account_sid', 'form_sms_settings', '' );
		$twilio_auth_token    = form_sms_get_option( 'twilio_auth_token', 'form_sms_settings', '' );
		$twilio_source_number = form_sms_get_option( 'twilio_source_number', 'form_sms_settings', '' );
		$twilio_helper        = sprintf( __( 'Enter your Twilio details. Please visit <a href="%s" target="_blank">%s</a> and get your api keys and options', 'form-sms' ), 'https://www.twilio.com/login', 'twilio' );

		// Plivo properties.
		$plivo_auth_id       = form_sms_get_option( 'plivo_auth_id', 'form_sms_settings', '' );
		$plivo_auth_token    = form_sms_get_option( 'plivo_auth_token', 'form_sms_settings', '' );
		$plivo_source_number = form_sms_get_option( 'plivo_source_number', 'form_sms_settings', '' );
		$plivo_helper        = sprintf( __( 'Enter your Plivo Details. Please visit <a href="%s" target="_blank">%s</a> and get your api keys and options', 'form-sms' ), 'https://console.plivo.com/', 'plivo' );

		?>

		<!-- start nexomo block -->
		<div class="nexmo_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
			<strong><?php echo wp_kses_post( $nexmo_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
				<th scrope="row"><?php esc_html_e( 'Vonage(Nexmo) API', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[nexmo_api]" id="form_sms_settings[nexmo_api]" value="<?php echo esc_attr( $nexmo_api ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Vonage(Nexmo) API key', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Vonage(Nexmo) API Secret', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[nexmo_api_secret]" id="form_sms_settings[nexmo_api_secret]" value="<?php echo esc_attr( $nexmo_api_secret ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Vonage(Nexmo) API secret', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Vonage(Nexmo) From Name', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[nexmo_from_name]" id="form_sms_settings[nexmo_from_name]" value="<?php echo esc_attr( $nexmo_from_name ); ?>">
						<p class="description"><?php esc_html_e( 'From which name the message will be sent to the users ( Default : VONAGE )', 'form-sms' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<!-- End nexomo block -->

		<!-- Start clicksend Block -->
		<div class="clicksend_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
				<strong><?php echo wp_kses_post( $clicksend_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'ClickSend Username', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[clicksend_username]" id="form_sms_settings[clicksend_username]" value="<?php echo esc_attr( $clicksend_username ); ?>">
						<p class="description"><?php esc_html_e( 'Enter ClickSend Username', 'form-sms' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'ClickSend API key', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[clicksend_api]" id="form_sms_settings[clicksend_api]" value="<?php echo esc_attr( $clicksend_api ); ?>">
						<p class="description"><?php esc_html_e( 'Enter ClickSend API', 'form-sms' ); ?></p>
					</td>
				</tr>

			</table>

		</div>
		<!-- End Clicksend Block -->

		<!-- Start Telesign Sms Api Block -->

		<div class="telesign_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
				<strong><?php echo wp_kses_post( $telesign_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Telesign customer Id', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[telesign_customer_id]" id="form_sms_settings[telesign_customer_id" value="<?php echo esc_attr( $telesign_customer_id ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Telesign Customer Id', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Telesign API Key', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[telesign_api_key]" id="form_sms_settings[telesign_api_key]" value="<?php echo esc_attr( $telesign_api_key ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Telesign Api Key', 'form-sms' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<!-- End Telesign Sms Api Block -->

		<!-- Start Messagemedia  Sms Api Block -->
		<div class="messagemedia_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
				<strong><?php echo wp_kses_post( $messagemedia_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'MessageMedia API key', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[messagemedia_api_key]" id="form_sms_settings[messagemedia_api_key]" value="<?php echo esc_attr( $messagemedia_api_key ); ?>">
						<p class="description"><?php esc_html_e( 'Enter MessageMedia API key', 'form-sms' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'MessageMedia API secret', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[messagemedia_api_secret]" id="form_sms_settings[messagemedia_api_secret]" value="<?php echo esc_attr( $messagemedia_api_secret ); ?>">
						<p class="description"><?php esc_html_e( 'Enter MessageMedia API secret', 'form-sms' ); ?></p>
					</td>
				</tr>

			</table>

		</div>
		<!-- End Messagemedia Sms Api Block -->

		<!-- Start Messagebird Sms Api Block  -->
		<div class="messagebird_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
				<strong><?php echo wp_kses_post( $messagebird_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Enable Test mode', 'form-sms' ) ?></th>
					<td>
						<input type="hidden" name="form_sms_settings[messagebird_is_test_mode]" value="no">
						<label for="form_sms_settings[messagebird_is_test_mode]">
							<input type="checkbox" class="checkbox" name="form_sms_settings[messagebird_is_test_mode]" id="form_sms_settings[messagebird_is_test_mode]" value="yes" <?php checked( $messagebird_is_test_mode, 'yes' ); ?>>
							<?php esc_html_e( 'Enable test mode', 'form-sms' ); ?>
						</label>

						<p class="description"><?php esc_html_e( 'Selet the mode Test or live', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Live Key', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[messagebird_live_key]" id="form_sms_settings[messagebird_live_key]" value="<?php echo esc_attr( $messagebird_live_key ); ?>">
						<p class="description"><?php esc_html_e( 'Enter your live API key', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Test Key', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[messagebird_test_key]" id="form_sms_settings[messagebird_test_key]" value="<?php echo esc_attr( $messagebird_test_key ); ?>">
						<p class="description"><?php esc_html_e( 'Enter your Test API key', 'form-sms' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<!-- End  Messagebird  Sms Api Block -->

		<!-- Start Twilio  Sms Api Block -->
		<div class="twilio_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
				<strong><?php echo wp_kses_post( $twilio_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Twilio Sid', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[twilio_account_sid]" id="form_sms_settings[twilio_account_sid]" value="<?php echo esc_attr( $twilio_account_sid ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Twilio Account Sid', 'form-sms' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Twilio Auth Token', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[twilio_auth_token]" id="form_sms_settings[twilio_auth_token]" value="<?php echo esc_attr( $twilio_auth_token ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Twilio Auth Token', 'form-sms' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Twilio Source Number', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[twilio_source_number]" id="form_sms_settings[twilio_source_number]" value="<?php echo esc_attr( $twilio_source_number ); ?>">
						<p class="description"><?php esc_html_e( 'Enter Twilio Source Number', 'form-sms' ); ?></p>
					</td>
				</tr>

			</table>
		</div>
		<!-- End Twilio Sms Api Block -->

		<!-- Start Plivo SMS block -->
		<div class="plivo_wrapper hide_class">
			<hr>
			<p style="margin-top:15px; margin-bottom:0px; font-style: italic; font-size: 14px;">
				<strong><?php echo wp_kses_post( $plivo_helper ); ?></strong>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Plivo Auth Id', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[plivo_auth_id]" id="form_sms_settings[plivo_auth_id]" value="<?php echo esc_attr( $plivo_auth_id ); ?>">
						<p class="description"><?php esc_html_e( 'Please Enter Plivo Auth Id', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Plivo Auth Token', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[plivo_auth_token]" id="form_sms_settings[plivo_auth_token]" value="<?php echo esc_attr( $plivo_auth_token ); ?>">
						<p class="description"><?php esc_html_e( 'Please Enter Plivo Auth Token', 'form-sms' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scrope="row"><?php esc_html_e( 'Plivo Surce Number', 'form-sms' ) ?></th>
					<td>
						<input type="text" class="regular-text" name="form_sms_settings[plivo_source_number]" id="form_sms_settings[plivo_source_number]" value="<?php echo esc_attr( $plivo_source_number ); ?>">
						<p class="description"><?php esc_html_e( 'From which name the message will be sent to the users ( Default : Plivo )', 'form-sms' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<!-- End Plivo Api Block -->
		<?php
		do_action( 'form_sms_gateway_settings_options_after' );
	}
}
