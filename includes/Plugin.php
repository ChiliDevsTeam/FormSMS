<?php
/**
 * Main plugin class.
 *
 * @package ChiliDevs\FormSMS
 * @since 1.0.0
 */

declare(strict_types=1);

namespace ChiliDevs\FormSMS;

use ChiliDevs\FormSMS\Admin\Admin;
use ChiliDevs\FormSMS\Forms\GravityFormSettings;
use ChiliDevs\FormSMS\Forms\WpFormSettings;
use ChiliDevs\FormSMS\Forms\Cf7Settings;
use ChiliDevs\FormSMS\Forms\EverestFormSettings;

/**
 * Class Plugin.
 *
 * @package ChiliDevs\FormSMS
 */
class Plugin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * Plugin's url.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Assets directory path.
	 *
	 * @var string
	 */
	public $assets_dir;

	/**
	 * Fire the plugin initialization step.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->path       = dirname( __FILE__, 2 );
		$this->url        = plugin_dir_url( trailingslashit( dirname( __FILE__, 2 ) ) . 'form-sms.php' );
		$this->assets_dir = trailingslashit( $this->url ) . 'assets/';
		require_once $this->path . '/includes/Admin/functions.php';
		new Admin();
		new WpFormSettings();
		new GravityFormSettings();
		new Cf7Settings();
		new EverestFormSettings();
	}

	/**
	 * Run the activator from installer
	 *
	 * @return void
	 */
	public function activator(): void {
        // phpcs:ignore;
		//register_activation_hook( dirname( __FILE__, 2 ) . '/form-sms.php', [ Installer::class, 'activation' ] );
	}

	/**
	 * Run the deactivator from installer
	 *
	 * @return void
	 */
	public function deactivator(): void {
        // phpcs:ignore;
		// register_deactivation_hook( dirname( __FILE__, 2 ) . '/form-sms.php', [ Installer::class, 'activation' ] );
	}
}
