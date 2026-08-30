<?php
/**
 * Privacidad y cumplimiento GDPR.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Privacy {

	const EXPORTER_KEY = 'wp-metricas';
	const ERASER_KEY   = 'wp-metricas';

	/**
	 * @var WP_Metricas_Privacy|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_Privacy {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'register_privacy_policy' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	/**
	 * Añade contenido sugerido a la política de privacidad del sitio.
	 */
	public function register_privacy_policy(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = sprintf(
			/* translators: %s: plugin name */
			__( 'Este sitio utiliza el plugin %s para medir visitas, clics en botones y tiempo de permanencia en páginas con fines estadísticos.', 'wp-metricas' ),
			'<strong>WP Métricas</strong>'
		);

		$content .= '<h2>' . esc_html__( 'Qué datos se recopilan', 'wp-metricas' ) . '</h2>';
		$content .= '<ul>';
		$content .= '<li>' . esc_html__( 'Identificador de sesión anónimo (cookie).', 'wp-metricas' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Páginas visitadas, URL de referencia y tipo de dispositivo.', 'wp-metricas' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Clics en botones (texto y URL del botón).', 'wp-metricas' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Tiempo de permanencia en cada página.', 'wp-metricas' ) . '</li>';
		$content .= '<li>' . esc_html__( 'ID de usuario de WordPress si la persona está registrada.', 'wp-metricas' ) . '</li>';
		$content .= '</ul>';

		$content .= '<h2>' . esc_html__( 'Dónde se almacenan', 'wp-metricas' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'Los datos se guardan en la base de datos de este sitio web. No se envían a servidores externos.', 'wp-metricas' ) . '</p>';

		$content .= '<h2>' . esc_html__( 'Retención', 'wp-metricas' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'Los registros se eliminan automáticamente después del período configurado en Métricas → Configuración.', 'wp-metricas' ) . '</p>';

		wp_add_privacy_policy_content(
			'WP Métricas',
			wp_kses_post( $content )
		);
	}

	/**
	 * Registra exportador de datos personales.
	 *
	 * @param array $exporters Exportadores.
	 */
	public function register_exporter( array $exporters ): array {
		$exporters[ self::EXPORTER_KEY ] = array(
			'exporter_friendly_name' => __( 'WP Métricas', 'wp-metricas' ),
			'callback'               => array( $this, 'export_personal_data' ),
		);
		return $exporters;
	}

	/**
	 * Registra borrador de datos personales.
	 *
	 * @param array $erasers Borradores.
	 */
	public function register_eraser( array $erasers ): array {
		$erasers[ self::ERASER_KEY ] = array(
			'eraser_friendly_name' => __( 'WP Métricas', 'wp-metricas' ),
			'callback'             => array( $this, 'erase_personal_data' ),
		);
		return $erasers;
	}

	/**
	 * Exporta datos asociados a un usuario.
	 *
	 * @param string $email     Email del usuario.
	 * @param int    $page      Página.
	 */
	public function export_personal_data( string $email, int $page = 1 ): array {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return array(
				'data' => array(),
				'done' => true,
			);
		}

		global $wpdb;

		$visits_table   = WP_Metricas_Database::visits_table();
		$clicks_table   = WP_Metricas_Database::clicks_table();
		$sections_table = WP_Metricas_Database::sections_table();
		$user_id        = (int) $user->ID;
		$export_data    = array();

		$visits = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_title, url, referrer, device_type, visited_at
				FROM {$visits_table}
				WHERE user_id = %d
				ORDER BY visited_at DESC
				LIMIT 100",
				$user_id
			),
			ARRAY_A
		);

		foreach ( $visits as $row ) {
			$export_data[] = array(
				'group_id'          => 'wp-metricas-visits',
				'group_label'       => __( 'Visitas (WP Métricas)', 'wp-metricas' ),
				'item_id'           => 'visit-' . md5( wp_json_encode( $row ) ),
				'data'              => array(
					array( 'name' => __( 'Página', 'wp-metricas' ), 'value' => $row['post_title'] ),
					array( 'name' => __( 'URL', 'wp-metricas' ), 'value' => $row['url'] ),
					array( 'name' => __( 'Referencia', 'wp-metricas' ), 'value' => $row['referrer'] ),
					array( 'name' => __( 'Dispositivo', 'wp-metricas' ), 'value' => $row['device_type'] ),
					array( 'name' => __( 'Fecha', 'wp-metricas' ), 'value' => $row['visited_at'] ),
				),
			);
		}

		$clicks = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT button_text, button_url, clicked_at
				FROM {$clicks_table}
				WHERE user_id = %d
				ORDER BY clicked_at DESC
				LIMIT 100",
				$user_id
			),
			ARRAY_A
		);

		foreach ( $clicks as $row ) {
			$export_data[] = array(
				'group_id'    => 'wp-metricas-clicks',
				'group_label' => __( 'Clics (WP Métricas)', 'wp-metricas' ),
				'item_id'     => 'click-' . md5( wp_json_encode( $row ) ),
				'data'        => array(
					array( 'name' => __( 'Botón', 'wp-metricas' ), 'value' => $row['button_text'] ),
					array( 'name' => __( 'URL', 'wp-metricas' ), 'value' => $row['button_url'] ),
					array( 'name' => __( 'Fecha', 'wp-metricas' ), 'value' => $row['clicked_at'] ),
				),
			);
		}

		$times = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT section_name, duration_seconds, recorded_at
				FROM {$sections_table}
				WHERE user_id = %d
				ORDER BY recorded_at DESC
				LIMIT 100",
				$user_id
			),
			ARRAY_A
		);

		foreach ( $times as $row ) {
			$export_data[] = array(
				'group_id'    => 'wp-metricas-time',
				'group_label' => __( 'Tiempo en página (WP Métricas)', 'wp-metricas' ),
				'item_id'     => 'time-' . md5( wp_json_encode( $row ) ),
				'data'        => array(
					array( 'name' => __( 'Página', 'wp-metricas' ), 'value' => $row['section_name'] ),
					array( 'name' => __( 'Segundos', 'wp-metricas' ), 'value' => $row['duration_seconds'] ),
					array( 'name' => __( 'Fecha', 'wp-metricas' ), 'value' => $row['recorded_at'] ),
				),
			);
		}

		return array(
			'data' => $export_data,
			'done' => true,
		);
	}

	/**
	 * Elimina datos asociados a un usuario.
	 *
	 * @param string $email     Email del usuario.
	 * @param int    $page      Página.
	 */
	public function erase_personal_data( string $email, int $page = 1 ): array {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);
		}

		global $wpdb;

		$user_id        = (int) $user->ID;
		$visits_table   = WP_Metricas_Database::visits_table();
		$clicks_table   = WP_Metricas_Database::clicks_table();
		$sections_table = WP_Metricas_Database::sections_table();

		$wpdb->delete( $visits_table, array( 'user_id' => $user_id ), array( '%d' ) );
		$wpdb->delete( $clicks_table, array( 'user_id' => $user_id ), array( '%d' ) );
		$wpdb->delete( $sections_table, array( 'user_id' => $user_id ), array( '%d' ) );

		return array(
			'items_removed'  => true,
			'items_retained' => false,
			'messages'       => array( __( 'Datos de WP Métricas eliminados.', 'wp-metricas' ) ),
			'done'           => true,
		);
	}
}
