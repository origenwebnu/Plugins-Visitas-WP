<?php
/**
 * Visitantes activos en tiempo real.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Realtime {

	const OPTION_KEY     = 'wp_metricas_active_sessions';
	const ACTIVE_TIMEOUT = 300; // 5 minutos.

	/**
	 * Registra o actualiza un heartbeat de sesión.
	 *
	 * @param string $session_id ID de sesión.
	 * @param int    $post_id    ID del post.
	 * @param string $url        URL actual.
	 * @param string $post_title Título del post.
	 */
	public static function heartbeat( string $session_id, int $post_id, string $url, string $post_title ): void {
		if ( empty( $session_id ) ) {
			return;
		}

		$sessions = self::get_sessions();
		$now      = time();

		$sessions[ $session_id ] = array(
			'last_seen'  => $now,
			'post_id'    => $post_id,
			'url'        => $url,
			'post_title' => $post_title,
		);

		$sessions = self::prune_sessions( $sessions, $now );
		update_option( self::OPTION_KEY, $sessions, false );
	}

	/**
	 * Cuenta visitantes activos.
	 */
	public static function get_active_count(): int {
		$sessions = self::prune_sessions( self::get_sessions(), time() );
		update_option( self::OPTION_KEY, $sessions, false );
		return count( $sessions );
	}

	/**
	 * Obtiene sesiones activas con detalle.
	 */
	public static function get_active_sessions(): array {
		$sessions = self::prune_sessions( self::get_sessions(), time() );
		update_option( self::OPTION_KEY, $sessions, false );

		$result = array();
		foreach ( $sessions as $session_id => $data ) {
			$result[] = array(
				'session_id' => $session_id,
				'post_id'    => $data['post_id'] ?? 0,
				'url'        => $data['url'] ?? '',
				'post_title' => $data['post_title'] ?? '',
				'last_seen'  => $data['last_seen'] ?? 0,
			);
		}

		return $result;
	}

	/**
	 * Obtiene sesiones almacenadas.
	 */
	private static function get_sessions(): array {
		$sessions = get_option( self::OPTION_KEY, array() );
		return is_array( $sessions ) ? $sessions : array();
	}

	/**
	 * Elimina sesiones inactivas.
	 *
	 * @param array $sessions Sesiones.
	 * @param int   $now      Timestamp actual.
	 */
	private static function prune_sessions( array $sessions, int $now ): array {
		$cutoff = $now - self::ACTIVE_TIMEOUT;

		foreach ( $sessions as $session_id => $data ) {
			$last_seen = isset( $data['last_seen'] ) ? (int) $data['last_seen'] : 0;
			if ( $last_seen < $cutoff ) {
				unset( $sessions[ $session_id ] );
			}
		}

		return $sessions;
	}
}
