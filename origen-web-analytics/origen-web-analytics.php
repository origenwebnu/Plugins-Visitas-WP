<?php
/**
 * Plugin Name:       Origen Web Analytics
 * Plugin URI:        https://origenweb.co/plugins
 * Description:       Lightweight self-hosted analytics: page visits, button clicks, and time on page. Compatible with Elementor and ACF.
 * Version:           2.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Origen Web
 * Author URI:        https://origenweb.co
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       origen-web-analytics
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_METRICAS_VERSION', '2.1.0' );
define( 'WP_METRICAS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_METRICAS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_METRICAS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-database.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-db-helper.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-cron.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-realtime.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-settings.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-geolocation.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-privacy.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-tracker.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-admin.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-dashboard.php';

/**
 * Clase principal del plugin.
 */
final class WP_Metricas {

	/**
	 * Instancia singleton.
	 *
	 * @var WP_Metricas|null
	 */
	private static $instance = null;

	/**
	 * Obtiene la instancia del plugin.
	 */
	public static function instance(): WP_Metricas {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Activa el plugin.
	 */
	public function activate(): void {
		WP_Metricas_Database::activate();
		WP_Metricas_Cron::schedule();
	}

	/**
	 * Desactiva el plugin.
	 */
	public function deactivate(): void {
		WP_Metricas_Cron::unschedule();
	}

	/**
	 * Inicializa componentes del plugin.
	 */
	public function init(): void {
		WP_Metricas_Database::maybe_upgrade();
		WP_Metricas_Cron::schedule();
		WP_Metricas_Cron::instance();
		WP_Metricas_Settings::instance();
		WP_Metricas_Privacy::instance();
		WP_Metricas_Tracker::instance();
		WP_Metricas_REST_API::instance();

		if ( is_admin() ) {
			WP_Metricas_Admin::instance();
			WP_Metricas_Dashboard::instance();
		}
	}
}

WP_Metricas::instance();
