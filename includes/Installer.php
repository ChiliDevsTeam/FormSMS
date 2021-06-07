<?php
/**
 * Installer class
 *
 * Manage all Installer related functionality
 *
 * @package ChiliDevs\FormSMS
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS;

/**
 * Installer class.
 *
 * @package ChiliDevs\FormSMS
 */
class Installer {
	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public static function activation() {
		global $wpdb;
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}form_sms_log` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`form_name` text,
			`form_data` longtext  NOT NULL,
			`status` varchar(25) NOT NULL,
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
