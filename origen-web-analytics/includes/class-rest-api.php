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
			'origen-web-analytics/v1',
			'/visit',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_visit' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'origen-web-analytics/v1',
			'/click',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_click' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'origen-web-analytics/v1',
			'/section',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_section' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'origen-web-analytics/v1',
			'/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'admin_permission' ),
			)
		);

		register_rest_route(
			'origen-web-analytics/v1',
			'/heartbeat',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_heartbeat' ),
				'permission_callback' => array( $this, 'tracking_permission' ),
			)
		);

		register_rest_route(
			'origen-web-analytics/v1',
			'/realtime',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_realtime' ),
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
		$post_type = sanitize_text_field( $request->get_param( 'post_type' ) ?: '' );

		$id = WP_Metricas_Database::insert_section_time(
			array(
				'post_id'              => $post_id,
				'section_id'           => sanitize_text_field( $request->get_param( 'section_id' ) ?: '' ),
				'section_name'         => sanitize_text_field( $request->get_param( 'section_name' ) ?: '' ),
				'selector'             => $post_type ?: sanitize_text_field( $request->get_param( 'selector' ) ?: '' ),
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
	 * Registra heartbeat de visitante activo.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function handle_heartbeat( WP_REST_Request $request ) {
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) ?: '' );
		$post_id    = absint( $request->get_param( 'post_id' ) );

		WP_Metricas_Realtime::heartbeat(
			$session_id,
			$post_id,
			esc_url_raw( $request->get_param( 'url' ) ?: '' ),
			sanitize_text_field( $request->get_param( 'post_title' ) ?: '' )
		);

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Obtiene conteo de visitantes activos en tiempo real.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function get_realtime( WP_REST_Request $request ) {
		return new WP_REST_Response(
			array(
				'active_visitors' => WP_Metricas_Realtime::get_active_count(),
				'sessions'        => WP_Metricas_Realtime::get_active_sessions(),
				'timestamp'       => time(),
			),
			200
		);
	}

	/**
	 * Obtiene estadísticas para el dashboard.
	 *
	 * @param WP_REST_Request $request Petición.
	 */
	public function get_stats( WP_REST_Request $request ) {
		$date_from = sanitize_text_field( $request->get_param( 'date_from' ) ?: gmdate( 'Y-m-d', strtotime( '-30 days' ) ) );
		$date_to   = sanitize_text_field( $request->get_param( 'date_to' ) ?: gmdate( 'Y-m-d' ) );
		$type      = sanitize_key( $request->get_param( 'type' ) ?: 'all' );

		$visits_table   = WP_Metricas_Database::visits_table();
		$clicks_table   = WP_Metricas_Database::clicks_table();
		$sections_table = WP_Metricas_Database::sections_table();

		$date_from_sql = $date_from . ' 00:00:00';
		$date_to_sql   = $date_to . ' 23:59:59';

		$visits_by_day = $this->get_visits_by_day( $visits_table, $date_from_sql, $date_to_sql, $type );
		$top_content   = $this->get_top_content( $visits_table, $date_from_sql, $date_to_sql, $type );
		$top_buttons   = WP_Metricas_DB_Helper::get_results(
			"SELECT button_text, button_url, elementor_widget_id, COUNT(*) as clicks
			FROM %i
			WHERE clicked_at BETWEEN %s AND %s
			GROUP BY button_text, button_url, elementor_widget_id
			ORDER BY clicks DESC
			LIMIT 10",
			array( $clicks_table, $date_from_sql, $date_to_sql )
		);
		$page_times    = $this->get_page_times( $sections_table, $date_from_sql, $date_to_sql, $type );
		$total_visits  = (int) $this->get_visits_count( $visits_table, $date_from_sql, $date_to_sql, $type );
		$total_clicks  = (int) WP_Metricas_DB_Helper::get_var(
			"SELECT COUNT(*) FROM %i WHERE clicked_at BETWEEN %s AND %s",
			array( $clicks_table, $date_from_sql, $date_to_sql )
		);
		$unique_sessions = (int) $this->get_unique_sessions( $visits_table, $date_from_sql, $date_to_sql, $type );
		$visits_by_type  = WP_Metricas_DB_Helper::get_results(
			"SELECT post_type, COUNT(*) as total
			FROM %i
			WHERE visited_at BETWEEN %s AND %s
			GROUP BY post_type
			ORDER BY total DESC",
			array( $visits_table, $date_from_sql, $date_to_sql )
		);
		$clicks_by_day   = WP_Metricas_DB_Helper::get_results(
			"SELECT DATE(clicked_at) as date_label, COUNT(*) as total
			FROM %i
			WHERE clicked_at BETWEEN %s AND %s
			GROUP BY DATE(clicked_at)
			ORDER BY date_label ASC",
			array( $clicks_table, $date_from_sql, $date_to_sql )
		);

		return new WP_REST_Response(
			array(
				'summary'        => array(
					'total_visits'    => $total_visits,
					'total_clicks'    => $total_clicks,
					'unique_sessions' => $unique_sessions,
				),
				'visits_by_day'  => $visits_by_day,
				'clicks_by_day'  => $clicks_by_day,
				'top_content'    => $top_content,
				'top_buttons'    => $top_buttons,
				'page_times'     => $page_times,
				'visits_by_type' => $visits_by_type,
				'filters'        => array(
					'date_from' => $date_from,
					'date_to'   => $date_to,
					'type'      => $type,
				),
			),
			200
		);
	}

	/**
	 * Visitas por día según el filtro de tipo.
	 */
	private function get_visits_by_day( string $table, string $from, string $to, string $type ): array {
		$sql_map = array(
			'all'       => "SELECT DATE(visited_at) as date_label, COUNT(*) as total FROM %i WHERE visited_at BETWEEN %s AND %s GROUP BY DATE(visited_at) ORDER BY date_label ASC",
			'pages'     => "SELECT DATE(visited_at) as date_label, COUNT(*) as total FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'page' GROUP BY DATE(visited_at) ORDER BY date_label ASC",
			'posts'     => "SELECT DATE(visited_at) as date_label, COUNT(*) as total FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'post' GROUP BY DATE(visited_at) ORDER BY date_label ASC",
			'acf'       => "SELECT DATE(visited_at) as date_label, COUNT(*) as total FROM %i WHERE visited_at BETWEEN %s AND %s AND has_acf = 1 GROUP BY DATE(visited_at) ORDER BY date_label ASC",
			'elementor' => "SELECT DATE(visited_at) as date_label, COUNT(*) as total FROM %i WHERE visited_at BETWEEN %s AND %s AND is_elementor = 1 GROUP BY DATE(visited_at) ORDER BY date_label ASC",
			'cpt'       => "SELECT DATE(visited_at) as date_label, COUNT(*) as total FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type NOT IN ('page', 'post') GROUP BY DATE(visited_at) ORDER BY date_label ASC",
		);

		$sql = $sql_map[ $type ] ?? $sql_map['all'];
		return WP_Metricas_DB_Helper::get_results( $sql, array( $table, $from, $to ) );
	}

	/**
	 * Contenido más visitado según el filtro de tipo.
	 */
	private function get_top_content( string $table, string $from, string $to, string $type ): array {
		$sql_map = array(
			'all'       => "SELECT post_id, post_title, post_type, COUNT(*) as visits FROM %i WHERE visited_at BETWEEN %s AND %s GROUP BY post_id, post_title, post_type ORDER BY visits DESC LIMIT 10",
			'pages'     => "SELECT post_id, post_title, post_type, COUNT(*) as visits FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'page' GROUP BY post_id, post_title, post_type ORDER BY visits DESC LIMIT 10",
			'posts'     => "SELECT post_id, post_title, post_type, COUNT(*) as visits FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'post' GROUP BY post_id, post_title, post_type ORDER BY visits DESC LIMIT 10",
			'acf'       => "SELECT post_id, post_title, post_type, COUNT(*) as visits FROM %i WHERE visited_at BETWEEN %s AND %s AND has_acf = 1 GROUP BY post_id, post_title, post_type ORDER BY visits DESC LIMIT 10",
			'elementor' => "SELECT post_id, post_title, post_type, COUNT(*) as visits FROM %i WHERE visited_at BETWEEN %s AND %s AND is_elementor = 1 GROUP BY post_id, post_title, post_type ORDER BY visits DESC LIMIT 10",
			'cpt'       => "SELECT post_id, post_title, post_type, COUNT(*) as visits FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type NOT IN ('page', 'post') GROUP BY post_id, post_title, post_type ORDER BY visits DESC LIMIT 10",
		);

		$sql = $sql_map[ $type ] ?? $sql_map['all'];
		return WP_Metricas_DB_Helper::get_results( $sql, array( $table, $from, $to ) );
	}

	/**
	 * Total de visitas según el filtro de tipo.
	 */
	private function get_visits_count( string $table, string $from, string $to, string $type ) {
		$sql_map = array(
			'all'       => 'SELECT COUNT(*) FROM %i WHERE visited_at BETWEEN %s AND %s',
			'pages'     => "SELECT COUNT(*) FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'page'",
			'posts'     => "SELECT COUNT(*) FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'post'",
			'acf'       => 'SELECT COUNT(*) FROM %i WHERE visited_at BETWEEN %s AND %s AND has_acf = 1',
			'elementor' => 'SELECT COUNT(*) FROM %i WHERE visited_at BETWEEN %s AND %s AND is_elementor = 1',
			'cpt'       => "SELECT COUNT(*) FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type NOT IN ('page', 'post')",
		);

		$sql = $sql_map[ $type ] ?? $sql_map['all'];
		return WP_Metricas_DB_Helper::get_var( $sql, array( $table, $from, $to ) );
	}

	/**
	 * Sesiones únicas según el filtro de tipo.
	 */
	private function get_unique_sessions( string $table, string $from, string $to, string $type ) {
		$sql_map = array(
			'all'       => 'SELECT COUNT(DISTINCT session_id) FROM %i WHERE visited_at BETWEEN %s AND %s',
			'pages'     => "SELECT COUNT(DISTINCT session_id) FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'page'",
			'posts'     => "SELECT COUNT(DISTINCT session_id) FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type = 'post'",
			'acf'       => 'SELECT COUNT(DISTINCT session_id) FROM %i WHERE visited_at BETWEEN %s AND %s AND has_acf = 1',
			'elementor' => 'SELECT COUNT(DISTINCT session_id) FROM %i WHERE visited_at BETWEEN %s AND %s AND is_elementor = 1',
			'cpt'       => "SELECT COUNT(DISTINCT session_id) FROM %i WHERE visited_at BETWEEN %s AND %s AND post_type NOT IN ('page', 'post')",
		);

		$sql = $sql_map[ $type ] ?? $sql_map['all'];
		return WP_Metricas_DB_Helper::get_var( $sql, array( $table, $from, $to ) );
	}

	/**
	 * Tiempo por página según el filtro de tipo.
	 */
	private function get_page_times( string $table, string $from, string $to, string $type ): array {
		$sql_map = array(
			'all'   => "SELECT s.post_id, MAX(s.section_name) as post_title, MAX(s.selector) as post_type, SUM(s.duration_seconds) as total_seconds, AVG(s.duration_seconds) as avg_seconds, COUNT(DISTINCT s.session_id) as sessions FROM %i s WHERE s.recorded_at BETWEEN %s AND %s AND s.section_id = 'page-time' GROUP BY s.post_id ORDER BY total_seconds DESC LIMIT 15",
			'pages' => "SELECT s.post_id, MAX(s.section_name) as post_title, MAX(s.selector) as post_type, SUM(s.duration_seconds) as total_seconds, AVG(s.duration_seconds) as avg_seconds, COUNT(DISTINCT s.session_id) as sessions FROM %i s WHERE s.recorded_at BETWEEN %s AND %s AND s.section_id = 'page-time' AND s.selector = 'page' GROUP BY s.post_id ORDER BY total_seconds DESC LIMIT 15",
			'posts' => "SELECT s.post_id, MAX(s.section_name) as post_title, MAX(s.selector) as post_type, SUM(s.duration_seconds) as total_seconds, AVG(s.duration_seconds) as avg_seconds, COUNT(DISTINCT s.session_id) as sessions FROM %i s WHERE s.recorded_at BETWEEN %s AND %s AND s.section_id = 'page-time' AND s.selector = 'post' GROUP BY s.post_id ORDER BY total_seconds DESC LIMIT 15",
			'cpt'   => "SELECT s.post_id, MAX(s.section_name) as post_title, MAX(s.selector) as post_type, SUM(s.duration_seconds) as total_seconds, AVG(s.duration_seconds) as avg_seconds, COUNT(DISTINCT s.session_id) as sessions FROM %i s WHERE s.recorded_at BETWEEN %s AND %s AND s.section_id = 'page-time' AND s.selector NOT IN ('page', 'post') GROUP BY s.post_id ORDER BY total_seconds DESC LIMIT 15",
		);

		$sql = $sql_map[ $type ] ?? $sql_map['all'];
		return WP_Metricas_DB_Helper::get_results( $sql, array( $table, $from, $to ) );
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
