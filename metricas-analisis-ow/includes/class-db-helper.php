<?php
/**
 * Database query helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_DB_Helper {

	/**
	 * Allowed plugin table names.
	 *
	 * @return string[]
	 */
	public static function allowed_tables(): array {
		return array(
			WP_Metricas_Database::visits_table(),
			WP_Metricas_Database::clicks_table(),
			WP_Metricas_Database::sections_table(),
		);
	}

	/**
	 * Allowed date column names.
	 *
	 * @return string[]
	 */
	public static function allowed_date_columns(): array {
		return array( 'visited_at', 'clicked_at', 'recorded_at' );
	}

	/**
	 * Validates a plugin table name.
	 *
	 * @param string $table Table name.
	 */
	public static function is_valid_table( string $table ): bool {
		return in_array( $table, self::allowed_tables(), true );
	}

	/**
	 * Validates a date column name.
	 *
	 * @param string $column Column name.
	 */
	public static function is_valid_date_column( string $column ): bool {
		return in_array( $column, self::allowed_date_columns(), true );
	}

	/**
	 * Runs a prepared SELECT and returns rows.
	 *
	 * @param string $sql    SQL with placeholders.
	 * @param array  $params Prepare arguments.
	 * @return array
	 */
	public static function get_results( string $sql, array $params = array() ): array {
		global $wpdb;

		if ( empty( $params ) ) {
			return array();
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Runs a prepared SELECT and returns a single value.
	 *
	 * @param string $sql    SQL with placeholders.
	 * @param array  $params Prepare arguments.
	 * @return string|null
	 */
	public static function get_var( string $sql, array $params = array() ) {
		global $wpdb;

		if ( empty( $params ) ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $wpdb->prepare( $sql, ...$params ) );
	}
}
