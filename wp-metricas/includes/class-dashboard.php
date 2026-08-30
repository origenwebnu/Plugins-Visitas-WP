<?php
/**
 * Dashboard de métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Dashboard {

	/**
	 * @var WP_Metricas_Dashboard|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_Dashboard {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Constructor vacío; render vía Admin.
	}

	/**
	 * Renderiza el dashboard.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include WP_METRICAS_PLUGIN_DIR . 'templates/dashboard-page.php';
	}
}
