<?php
/**
 * SmsLog class
 *
 * Manage allSmsLog related functionality
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS\Admin;

use function ChiliDevs\FormSMS\plugin;
use WP_Error;

/**
 * SmsLog class.
 *
 * @package ChiliDevs\FormSMS\Admin
 */
class SmsLog {

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'sms_sent_successfully', [ $this, 'sms_sent_successfully_log' ], 10, 3 );
		add_action( 'sms_sent_fail', [ $this, 'sms_sent_fail_log' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'sms_log_submenu' ], 14 );
	}

	/**
	 * Sms Log Submenu
	 */
	public function sms_log_submenu() {
		add_submenu_page(
			'form-sms',
			__( 'SmsLog', 'form-sms' ),
			__( 'SmsLog', 'form-sms' ),
			'manage_options',
			'sms-log',
			[ $this, 'sms_log_content' ] 
		);
	}

	/**
	 * Successful Sms Log
	 *
	 * @param array  $success_data  Sms Successful Data.
	 * @param string $form_name Form Name.
	 * @param string $form_entry Form Entry.
	 *
	 * @return void
	 */
	public function sms_sent_successfully_log( $success_data, $form_name, $form_entry ) {
		$sms_successful = sanitize_text_field( $success_data['message'] );
		$form_name      = sanitize_text_field( $form_name );
		$form_data      = maybe_serialize( $form_entry );

		$data = [
			'form_name'  => $form_name,
			'form_data'  => $form_data,
			'status'     => $sms_successful,
			'created_at' => current_time( 'mysql' ),
		];

		$result = $this->create( $data );

	}

	/**
	 * Fail Sms Log
	 *
	 * @param array  $fail_data  Sms Fail Data.
	 * @param string $form_name  Form Name.
	 *
	 * @return void
	 */
	public function sms_sent_fail_log( $fail_data, $form_name ) {
		$sms_fail  = sanitize_text_field( $fail_data['message'] );
		$form_name = sanitize_text_field( $form_name );
		$data = [
			'form_name'  => $form_name,
			'status'     => $sms_fail,
			'created_at' => current_time( 'mysql' ),
  
		];

		$result = $this->create( $data );

	}

	/**
	 * Insert Data on form_sms_log table
	 *
	 * @param array $data Table Data.
	 *
	 * @return boolean
	 */
	public function create( $data = [] ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'form_sms_log';
		$wpdb->insert( $table_name, $data, [ '%s', '%s', '%s', '%s' ] );
		
		if ( $wpdb->insert_id ) {
			return true;
		}
		return false;
	}

	/**
	 * Display Sms Logs
	 */
	public function sms_log_content() {
		$this->delete_sms_log();

		ob_start();

		require_once plugin()->path . '/includes/Views/sms-log-list-table.php';
	  
		$template = ob_get_contents();

		ob_end_clean();
		
		echo $template;
	}

	/**
	 * Delete Sms Logs
	 */
	public function delete_sms_log() {
		global $wpdb;
		$action = isset( $_GET['action'] ) ? trim( $_GET['action'] ) : '';
		if ( 'log-delete' == $action ) {
			$sms_log_id = isset( $_GET['log_id'] ) ? intval( $_GET['log_id'] ) : '';
			$table_name = $wpdb->prefix . 'form_sms_log';
			$wpdb->delete( $table_name, [ 'id' => $sms_log_id ], [ '%d' ] );
		}
		
	}

}
