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
			__( 'Métricas y Análisis OW', 'metricas-analisis-ow' ),
			__( 'Métricas y Análisis OW', 'metricas-analisis-ow' ),
			'manage_options',
			'metricas-analisis-ow-dashboard',
			array( WP_Metricas_Dashboard::instance(), 'render_page' ),
			'dashicons-chart-area',
			30
		);

		add_submenu_page(
			'metricas-analisis-ow-dashboard',
			__( 'Dashboard', 'metricas-analisis-ow' ),
			__( 'Dashboard', 'metricas-analisis-ow' ),
			'manage_options',
			'metricas-analisis-ow-dashboard',
			array( WP_Metricas_Dashboard::instance(), 'render_page' )
		);

		add_submenu_page(
			'metricas-analisis-ow-dashboard',
			__( 'Configuración', 'metricas-analisis-ow' ),
			__( 'Configuración', 'metricas-analisis-ow' ),
			'manage_options',
			'metricas-analisis-ow-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Encola assets del admin.
	 *
	 * @param string $hook Página actual.
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( strpos( $hook, 'metricas-analisis-ow' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wp-metricas-admin',
			WP_METRICAS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_METRICAS_VERSION
		);

		if ( 'toplevel_page_metricas-analisis-ow-dashboard' === $hook || strpos( $hook, 'metricas-analisis-ow-dashboard' ) !== false ) {
			wp_enqueue_script(
				'wp-metricas-chartjs',
				WP_METRICAS_PLUGIN_URL . 'assets/js/chart.umd.min.js',
				array(),
				'4.4.1',
				true
			);

			wp_enqueue_script(
				'metricas-analisis-ow-dashboard',
				WP_METRICAS_PLUGIN_URL . 'assets/js/dashboard.js',
				array( 'wp-metricas-chartjs' ),
				WP_METRICAS_VERSION,
				true
			);

			wp_localize_script(
				'metricas-analisis-ow-dashboard',
				'wpMetricasDashboard',
				array(
					'restUrl'          => esc_url_raw( rest_url( 'metricas-analisis-ow/v1/stats' ) ),
					'realtimeUrl'      => esc_url_raw( rest_url( 'metricas-analisis-ow/v1/realtime' ) ),
					'realtimeInterval' => 15000,
					'defaultDateFrom'  => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
					'defaultDateTo'    => gmdate( 'Y-m-d' ),
					'nonce'            => wp_create_nonce( 'wp_rest' ),
					'i18n'             => array(
						'visits'   => __( 'Visitas', 'metricas-analisis-ow' ),
						'clicks'   => __( 'Clics', 'metricas-analisis-ow' ),
						'loading'  => __( 'Cargando...', 'metricas-analisis-ow' ),
						'noData'   => __( 'Sin datos para el período seleccionado', 'metricas-analisis-ow' ),
						'seconds'  => __( 'segundos', 'metricas-analisis-ow' ),
						'minutes'  => __( 'minutos', 'metricas-analisis-ow' ),
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
