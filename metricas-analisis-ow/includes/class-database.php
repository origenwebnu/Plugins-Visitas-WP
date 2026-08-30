<?php
/**
 * Gestión de base de datos para métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Database {

	const DB_VERSION = '1.0.0';

	/**
	 * Nombre de tabla de visitas.
	 */
	public static function visits_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'metricas_visits';
	}

	/**
	 * Nombre de tabla de clics.
	 */
	public static function clicks_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'metricas_clicks';
	}

	/**
	 * Nombre de tabla de tiempo por sección.
	 */
	public static function sections_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'metricas_sections';
	}

	/**
	 * Activa el plugin y crea tablas.
	 */
	public static function activate(): void {
		self::create_tables();
		update_option( 'wp_metricas_db_version', self::DB_VERSION );
	}

	/**
	 * Actualiza esquema si es necesario.
	 */
	public static function maybe_upgrade(): void {
		$installed = get_option( 'wp_metricas_db_version', '0' );
		if ( version_compare( $installed, self::DB_VERSION, '<' ) ) {
			self::create_tables();
			update_option( 'wp_metricas_db_version', self::DB_VERSION );
		}
	}

	/**
	 * Crea las tablas de métricas.
	 */
	public static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$visits          = self::visits_table();
		$clicks          = self::clicks_table();
		$sections        = self::sections_table();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql_visits = "CREATE TABLE {$visits} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			post_type varchar(50) NOT NULL DEFAULT '',
			post_title varchar(255) NOT NULL DEFAULT '',
			url varchar(500) NOT NULL DEFAULT '',
			referrer varchar(500) NOT NULL DEFAULT '',
			session_id varchar(64) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			device_type varchar(20) NOT NULL DEFAULT '',
			is_elementor tinyint(1) NOT NULL DEFAULT 0,
			has_acf tinyint(1) NOT NULL DEFAULT 0,
			visited_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY post_type (post_type),
			KEY visited_at (visited_at),
			KEY session_id (session_id)
		) {$charset_collate};";

		$sql_clicks = "CREATE TABLE {$clicks} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			button_id varchar(100) NOT NULL DEFAULT '',
			button_text varchar(255) NOT NULL DEFAULT '',
			button_url varchar(500) NOT NULL DEFAULT '',
			selector varchar(255) NOT NULL DEFAULT '',
			elementor_widget_id varchar(50) NOT NULL DEFAULT '',
			session_id varchar(64) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			clicked_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY button_id (button_id),
			KEY clicked_at (clicked_at),
			KEY session_id (session_id)
		) {$charset_collate};";

		$sql_sections = "CREATE TABLE {$sections} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			section_id varchar(100) NOT NULL DEFAULT '',
			section_name varchar(255) NOT NULL DEFAULT '',
			selector varchar(255) NOT NULL DEFAULT '',
			elementor_section_id varchar(50) NOT NULL DEFAULT '',
			duration_seconds int(11) unsigned NOT NULL DEFAULT 0,
			session_id varchar(64) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			recorded_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY section_id (section_id),
			KEY recorded_at (recorded_at),
			KEY session_id (session_id)
		) {$charset_collate};";

		dbDelta( $sql_visits );
		dbDelta( $sql_clicks );
		dbDelta( $sql_sections );
	}

	/**
	 * Inserta una visita.
	 *
	 * @param array $data Datos de la visita.
	 * @return int|false
	 */
	public static function insert_visit( array $data ) {
		global $wpdb;

		$result = $wpdb->insert(
			self::visits_table(),
			array(
				'post_id'      => absint( $data['post_id'] ?? 0 ),
				'post_type'    => sanitize_text_field( $data['post_type'] ?? '' ),
				'post_title'   => sanitize_text_field( $data['post_title'] ?? '' ),
				'url'          => esc_url_raw( $data['url'] ?? '' ),
				'referrer'     => esc_url_raw( $data['referrer'] ?? '' ),
				'session_id'   => sanitize_text_field( $data['session_id'] ?? '' ),
				'user_id'      => absint( $data['user_id'] ?? 0 ),
				'device_type'  => sanitize_text_field( $data['device_type'] ?? '' ),
				'is_elementor' => ! empty( $data['is_elementor'] ) ? 1 : 0,
				'has_acf'      => ! empty( $data['has_acf'] ) ? 1 : 0,
				'visited_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Inserta un clic.
	 *
	 * @param array $data Datos del clic.
	 * @return int|false
	 */
	public static function insert_click( array $data ) {
		global $wpdb;

		$result = $wpdb->insert(
			self::clicks_table(),
			array(
				'post_id'             => absint( $data['post_id'] ?? 0 ),
				'button_id'           => sanitize_text_field( $data['button_id'] ?? '' ),
				'button_text'         => sanitize_text_field( $data['button_text'] ?? '' ),
				'button_url'          => esc_url_raw( $data['button_url'] ?? '' ),
				'selector'            => sanitize_text_field( $data['selector'] ?? '' ),
				'elementor_widget_id' => sanitize_text_field( $data['elementor_widget_id'] ?? '' ),
				'session_id'          => sanitize_text_field( $data['session_id'] ?? '' ),
				'user_id'             => absint( $data['user_id'] ?? 0 ),
				'clicked_at'          => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Inserta tiempo en sección.
	 *
	 * @param array $data Datos de la sección.
	 * @return int|false
	 */
	public static function insert_section_time( array $data ) {
		global $wpdb;

		$duration = absint( $data['duration_seconds'] ?? 0 );
		if ( $duration < 1 ) {
			return false;
		}

		$result = $wpdb->insert(
			self::sections_table(),
			array(
				'post_id'              => absint( $data['post_id'] ?? 0 ),
				'section_id'           => sanitize_text_field( $data['section_id'] ?? '' ),
				'section_name'         => sanitize_text_field( $data['section_name'] ?? '' ),
				'selector'             => sanitize_text_field( $data['selector'] ?? '' ),
				'elementor_section_id' => sanitize_text_field( $data['elementor_section_id'] ?? '' ),
				'duration_seconds'     => $duration,
				'session_id'           => sanitize_text_field( $data['session_id'] ?? '' ),
				'user_id'              => absint( $data['user_id'] ?? 0 ),
				'recorded_at'          => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Elimina registros más antiguos que el período de retención configurado.
	 *
	 * @return int Número total de filas eliminadas.
	 */
	public static function delete_old_data(): int {
		global $wpdb;

		$days = (int) WP_Metricas_Settings::get( 'retention_days', 90 );
		$days = max( 7, min( 365, $days ) );
		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$deleted = 0;
		$tables  = array(
			self::visits_table()   => 'visited_at',
			self::clicks_table()   => 'clicked_at',
			self::sections_table() => 'recorded_at',
		);

		foreach ( $tables as $table => $column ) {
			if ( ! WP_Metricas_DB_Helper::is_valid_table( $table ) || ! WP_Metricas_DB_Helper::is_valid_date_column( $column ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$deleted += (int) $wpdb->query(
				$wpdb->prepare(
					'DELETE FROM %i WHERE %i < %s',
					$table,
					$column,
					$cutoff
				)
			);
		}

		return $deleted;
	}

	/**
	 * Elimina todas las tablas y opciones del plugin.
	 */
	public static function drop_tables_and_options(): void {
		global $wpdb;

		$tables = array(
			self::visits_table(),
			self::clicks_table(),
			self::sections_table(),
		);

		foreach ( $tables as $table ) {
			if ( ! WP_Metricas_DB_Helper::is_valid_table( $table ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
		}

		delete_option( 'wp_metricas_settings' );
		delete_option( 'wp_metricas_db_version' );
		delete_option( 'wp_metricas_active_sessions' );
	}
}
