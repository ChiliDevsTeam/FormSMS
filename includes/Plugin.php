<?php
/**
 * Main plugin class.
 *
 * @package ChiliDevs\TextyForms
 * @since 1.0.0
 */

declare(strict_types=1);

namespace ChiliDevs\TextyForms;

use ChiliDevs\TextyForms\Admin\Admin;
use ChiliDevs\TextyForms\Forms\GravityFormSettings;
use ChiliDevs\TextyForms\Forms\WpFormSettings;
use ChiliDevs\TextyForms\Forms\Cf7Settings;

/**
 * Class Plugin.
 *
 * @package ChiliDevs\TextyForms
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
		$this->url        = plugin_dir_url( trailingslashit( dirname( __FILE__, 2 ) ) . 'texty-forms.php' );
		$this->assets_dir = trailingslashit( $this->url ) . 'assets/';
		require_once $this->path . '/includes/Admin/functions.php';
		new Admin();
		new WpFormSettings();
		new GravityFormSettings();
		new Cf7Settings();
	}

	/**
	 * Run the activator from installer
	 *
	 * @return void
	 */
	public function activator(): void {
        // phpcs:ignore;
		// register_activation_hook( dirname( __FILE__, 2 ) . '/texty-forms.php', [ Installer::class, 'activation' ] );
	}

	/**
	 * Run the deactivator from installer
	 *
	 * @return void
	 */
	public function deactivator(): void {
        // phpcs:ignore;
		// register_deactivation_hook( dirname( __FILE__, 2 ) . '/texty-forms.php', [ Installer::class, 'activation' ] );
	}
}
