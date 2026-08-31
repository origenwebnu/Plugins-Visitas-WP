<?php
/**
 * Menús y páginas de administración.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Admin {

	/**
	 * @var WP_Metricas_Admin|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_Admin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Registra menús en el admin de WordPress.
	 */
	public function register_menus(): void {
		add_menu_page(
			__( 'Origen Web Analytics', 'origen-web-analytics' ),
			__( 'Origen Web Analytics', 'origen-web-analytics' ),
			'manage_options',
			'origen-web-analytics-dashboard',
			array( WP_Metricas_Dashboard::instance(), 'render_page' ),
			'dashicons-chart-area',
			30
		);

		add_submenu_page(
			'origen-web-analytics-dashboard',
			__( 'Dashboard', 'origen-web-analytics' ),
			__( 'Dashboard', 'origen-web-analytics' ),
			'manage_options',
			'origen-web-analytics-dashboard',
			array( WP_Metricas_Dashboard::instance(), 'render_page' )
		);

		add_submenu_page(
			'origen-web-analytics-dashboard',
			__( 'Configuración', 'origen-web-analytics' ),
			__( 'Configuración', 'origen-web-analytics' ),
			'manage_options',
			'origen-web-analytics-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Encola assets del admin.
	 *
	 * @param string $hook Página actual.
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( strpos( $hook, 'origen-web-analytics' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wp-metricas-admin',
			WP_METRICAS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_METRICAS_VERSION
		);

		if ( 'toplevel_page_origen-web-analytics-dashboard' === $hook || strpos( $hook, 'origen-web-analytics-dashboard' ) !== false ) {
			wp_enqueue_script(
				'wp-metricas-chartjs',
				WP_METRICAS_PLUGIN_URL . 'assets/js/chart.umd.min.js',
				array(),
				'4.5.1',
				true
			);

			wp_enqueue_script(
				'origen-web-analytics-dashboard',
				WP_METRICAS_PLUGIN_URL . 'assets/js/dashboard.js',
				array( 'wp-metricas-chartjs' ),
				WP_METRICAS_VERSION,
				true
			);

			wp_localize_script(
				'origen-web-analytics-dashboard',
				'wpMetricasDashboard',
				array(
					'restUrl'          => esc_url_raw( rest_url( 'origen-web-analytics/v1/stats' ) ),
					'realtimeUrl'      => esc_url_raw( rest_url( 'origen-web-analytics/v1/realtime' ) ),
					'realtimeInterval' => 15000,
					'defaultDateFrom'  => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
					'defaultDateTo'    => gmdate( 'Y-m-d' ),
					'nonce'            => wp_create_nonce( 'wp_rest' ),
					'i18n'             => array(
						'visits'   => __( 'Visitas', 'origen-web-analytics' ),
						'clicks'   => __( 'Clics', 'origen-web-analytics' ),
						'loading'  => __( 'Cargando...', 'origen-web-analytics' ),
						'noData'   => __( 'Sin datos para el período seleccionado', 'origen-web-analytics' ),
						'seconds'  => __( 'segundos', 'origen-web-analytics' ),
						'minutes'  => __( 'minutos', 'origen-web-analytics' ),
					),
				)
			);
		}
	}

	/**
	 * Renderiza la página de configuración.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = WP_Metricas_Settings::get_all();
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		include WP_METRICAS_PLUGIN_DIR . 'templates/settings-page.php';
	}
}
