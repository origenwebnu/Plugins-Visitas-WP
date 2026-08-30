<?php
/**
 * Tareas programadas: retención de datos.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Cron {

	const HOOK = 'wp_metricas_cleanup_old_data';

	/**
	 * @var WP_Metricas_Cron|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_Cron {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( self::HOOK, array( $this, 'cleanup_old_data' ) );
	}

	/**
	 * Programa el evento de limpieza.
	 */
	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::HOOK );
		}
	}

	/**
	 * Cancela el evento programado.
	 */
	public static function unschedule(): void {
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK );
		}
	}

	/**
	 * Elimina registros más antiguos que el período de retención.
	 */
	public function cleanup_old_data(): void {
		WP_Metricas_Database::delete_old_data();
	}
}
