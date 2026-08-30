<?php
/**
 * Plugin Name: WP Métricas
 * Plugin URI: https://origenweb.co/plugins
 * Description: Métricas de visitas, clics en botones y tiempo por sección. Compatible con Elementor y ACF.
 * Version: 1.0.0
 * Author: Origen Web
 * Author URI: https://origenweb.co/plugins
 * Text Domain: wp-metricas
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_METRICAS_VERSION', '1.0.0' );
define( 'WP_METRICAS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_METRICAS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_METRICAS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-database.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-realtime.php';
require_once WP_METRICAS_PLUGIN_DIR . 'includes/class-settings.php';
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
		register_activation_hook( __FILE__, array( 'WP_Metricas_Database', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'WP_Metricas_Database', 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Inicializa componentes del plugin.
	 */
	public function init(): void {
		load_plugin_textdomain( 'wp-metricas', false, dirname( WP_METRICAS_PLUGIN_BASENAME ) . '/languages' );

		WP_Metricas_Database::maybe_upgrade();
		WP_Metricas_Settings::instance();
		WP_Metricas_Tracker::instance();
		WP_Metricas_REST_API::instance();

		if ( is_admin() ) {
			WP_Metricas_Admin::instance();
			WP_Metricas_Dashboard::instance();
		}
	}
}

WP_Metricas::instance();
