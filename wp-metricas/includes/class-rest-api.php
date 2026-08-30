<?php
/**
 * API REST para recibir métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_REST_API {

	/**
	 * @var WP_Metricas_REST_API|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_REST_API {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registra rutas REST.
	 */
	public function register_routes(): void {
		register_rest_route(
			'wp-metricas/v1',
			'/visit',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_visit' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'wp-metricas/v1',
			'/click',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_click' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'wp-metricas/v1',
			'/section',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_section' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'wp-metricas/v1',
			'/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'admin_permission' ),
			)
		);
	}

	/**
	 * Permiso para tracking público.
	 */
	public function tracking_permission(): bool {
		return WP_Metricas_Settings::should_track();
	}

	/**
	 * Permiso para administradores.
	 */
	public function admin_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Registra una visita.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function handle_visit( WP_REST_Request $request ) {
		$post_id = absint( $request->get_param( 'post_id' ) );
		$post    = get_post( $post_id );

		if ( ! $post || ! WP_Metricas_Settings::should_track_post( $post ) ) {
			return new WP_REST_Response( array( 'success' => false ), 400 );
		}

		$id = WP_Metricas_Database::insert_visit(
			array(
				'post_id'      => $post_id,
				'post_type'    => sanitize_text_field( $request->get_param( 'post_type' ) ?: $post->post_type ),
				'post_title'   => sanitize_text_field( $request->get_param( 'post_title' ) ?: get_the_title( $post ) ),
				'url'          => esc_url_raw( $request->get_param( 'url' ) ?: get_permalink( $post ) ),
				'referrer'     => esc_url_raw( $request->get_param( 'referrer' ) ?: '' ),
				'session_id'   => sanitize_text_field( $request->get_param( 'session_id' ) ?: '' ),
				'user_id'      => get_current_user_id(),
				'device_type'  => $this->detect_device( $request->get_param( 'device_type' ) ),
				'is_elementor' => (bool) $request->get_param( 'is_elementor' ),
				'has_acf'      => (bool) $request->get_param( 'has_acf' ),
			)
		);

		return new WP_REST_Response(
			array(
				'success' => (bool) $id,
				'id'      => $id,
			),
			$id ? 201 : 500
		);
	}

	/**
	 * Registra un clic.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function handle_click( WP_REST_Request $request ) {
		if ( ! WP_Metricas_Settings::get( 'track_buttons', true ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'disabled' ), 403 );
		}

		$post_id = absint( $request->get_param( 'post_id' ) );

		$id = WP_Metricas_Database::insert_click(
			array(
				'post_id'             => $post_id,
				'button_id'           => sanitize_text_field( $request->get_param( 'button_id' ) ?: '' ),
				'button_text'         => sanitize_text_field( $request->get_param( 'button_text' ) ?: '' ),
				'button_url'          => esc_url_raw( $request->get_param( 'button_url' ) ?: '' ),
				'selector'            => sanitize_text_field( $request->get_param( 'selector' ) ?: '' ),
				'elementor_widget_id' => sanitize_text_field( $request->get_param( 'elementor_widget_id' ) ?: '' ),
				'session_id'          => sanitize_text_field( $request->get_param( 'session_id' ) ?: '' ),
				'user_id'             => get_current_user_id(),
			)
		);

		return new WP_REST_Response(
			array(
				'success' => (bool) $id,
				'id'      => $id,
			),
			$id ? 201 : 500
		);
	}

	/**
	 * Registra tiempo en sección.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function handle_section( WP_REST_Request $request ) {
		if ( ! WP_Metricas_Settings::get( 'track_sections', true ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'disabled' ), 403 );
		}

		$post_id = absint( $request->get_param( 'post_id' ) );

		$id = WP_Metricas_Database::insert_section_time(
			array(
				'post_id'              => $post_id,
				'section_id'           => sanitize_text_field( $request->get_param( 'section_id' ) ?: '' ),
				'section_name'         => sanitize_text_field( $request->get_param( 'section_name' ) ?: '' ),
				'selector'             => sanitize_text_field( $request->get_param( 'selector' ) ?: '' ),
				'elementor_section_id' => sanitize_text_field( $request->get_param( 'elementor_section_id' ) ?: '' ),
				'duration_seconds'     => absint( $request->get_param( 'duration_seconds' ) ),
				'session_id'           => sanitize_text_field( $request->get_param( 'session_id' ) ?: '' ),
				'user_id'              => get_current_user_id(),
			)
		);

		return new WP_REST_Response(
			array(
				'success' => (bool) $id,
				'id'      => $id,
			),
			$id ? 201 : 500
		);
	}

	/**
	 * Obtiene estadísticas para el dashboard.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function get_stats( WP_REST_Request $request ) {
		global $wpdb;

		$date_from = sanitize_text_field( $request->get_param( 'date_from' ) ?: gmdate( 'Y-m-d', strtotime( '-30 days' ) ) );
		$date_to   = sanitize_text_field( $request->get_param( 'date_to' ) ?: gmdate( 'Y-m-d' ) );
		$type      = sanitize_key( $request->get_param( 'type' ) ?: 'all' );

		$visits_table   = WP_Metricas_Database::visits_table();
		$clicks_table   = WP_Metricas_Database::clicks_table();
		$sections_table = WP_Metricas_Database::sections_table();

		$date_from_sql = $date_from . ' 00:00:00';
		$date_to_sql   = $date_to . ' 23:59:59';

		$post_type_filter = '';
		$params           = array( $date_from_sql, $date_to_sql );

		if ( 'pages' === $type ) {
			$post_type_filter = " AND post_type = 'page'";
		} elseif ( 'posts' === $type ) {
			$post_type_filter = " AND post_type = 'post'";
		} elseif ( 'acf' === $type ) {
			$post_type_filter = ' AND has_acf = 1';
		} elseif ( 'elementor' === $type ) {
			$post_type_filter = ' AND is_elementor = 1';
		} elseif ( 'cpt' === $type ) {
			$post_type_filter = " AND post_type NOT IN ('page', 'post')";
		}

		// Visitas por día.
		$visits_by_day = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(visited_at) as date_label, COUNT(*) as total
				FROM {$visits_table}
				WHERE visited_at BETWEEN %s AND %s {$post_type_filter}
				GROUP BY DATE(visited_at)
				ORDER BY date_label ASC",
				...$params
			),
			ARRAY_A
		);

		// Top páginas/entradas.
		$top_content = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, post_title, post_type, COUNT(*) as visits
				FROM {$visits_table}
				WHERE visited_at BETWEEN %s AND %s {$post_type_filter}
				GROUP BY post_id, post_title, post_type
				ORDER BY visits DESC
				LIMIT 10",
				...$params
			),
			ARRAY_A
		);

		// Top botones.
		$top_buttons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT button_text, button_url, elementor_widget_id, COUNT(*) as clicks
				FROM {$clicks_table}
				WHERE clicked_at BETWEEN %s AND %s
				GROUP BY button_text, button_url, elementor_widget_id
				ORDER BY clicks DESC
				LIMIT 10",
				$date_from_sql,
				$date_to_sql
			),
			ARRAY_A
		);

		// Tiempo por sección.
		$section_times = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT section_name, section_id, elementor_section_id,
					SUM(duration_seconds) as total_seconds,
					AVG(duration_seconds) as avg_seconds,
					COUNT(*) as records
				FROM {$sections_table}
				WHERE recorded_at BETWEEN %s AND %s
				GROUP BY section_name, section_id, elementor_section_id
				ORDER BY total_seconds DESC
				LIMIT 15",
				$date_from_sql,
				$date_to_sql
			),
			ARRAY_A
		);

		// Totales.
		$total_visits = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$visits_table}
				WHERE visited_at BETWEEN %s AND %s {$post_type_filter}",
				...$params
			)
		);

		$total_clicks = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$clicks_table}
				WHERE clicked_at BETWEEN %s AND %s",
				$date_from_sql,
				$date_to_sql
			)
		);

		$unique_sessions = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT session_id) FROM {$visits_table}
				WHERE visited_at BETWEEN %s AND %s {$post_type_filter}",
				...$params
			)
		);

		// Visitas por tipo de contenido.
		$visits_by_type = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_type, COUNT(*) as total
				FROM {$visits_table}
				WHERE visited_at BETWEEN %s AND %s
				GROUP BY post_type
				ORDER BY total DESC",
				$date_from_sql,
				$date_to_sql
			),
			ARRAY_A
		);

		// Clics por día.
		$clicks_by_day = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(clicked_at) as date_label, COUNT(*) as total
				FROM {$clicks_table}
				WHERE clicked_at BETWEEN %s AND %s
				GROUP BY DATE(clicked_at)
				ORDER BY date_label ASC",
				$date_from_sql,
				$date_to_sql
			),
			ARRAY_A
		);

		return new WP_REST_Response(
			array(
				'summary'         => array(
					'total_visits'    => $total_visits,
					'total_clicks'    => $total_clicks,
					'unique_sessions' => $unique_sessions,
				),
				'visits_by_day'   => $visits_by_day ?: array(),
				'clicks_by_day'   => $clicks_by_day ?: array(),
				'top_content'     => $top_content ?: array(),
				'top_buttons'     => $top_buttons ?: array(),
				'section_times'   => $section_times ?: array(),
				'visits_by_type'  => $visits_by_type ?: array(),
				'filters'         => array(
					'date_from' => $date_from,
					'date_to'   => $date_to,
					'type'      => $type,
				),
			),
			200
		);
	}

	/**
	 * Normaliza tipo de dispositivo.
	 *
	 * @param string $device Tipo reportado.
	 */
	private function detect_device( string $device ): string {
		$device = strtolower( sanitize_text_field( $device ) );
		$allowed = array( 'desktop', 'tablet', 'mobile' );
		return in_array( $device, $allowed, true ) ? $device : 'desktop';
	}
}
