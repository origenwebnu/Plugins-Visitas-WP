<?php
/**
 * Tracking en el frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Tracker {

	/**
	 * @var WP_Metricas_Tracker|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_Tracker {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Encola el script de tracking.
	 */
	public function enqueue_scripts(): void {
		if ( ! WP_Metricas_Settings::should_track() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post || ! WP_Metricas_Settings::should_track_post( $post ) ) {
			return;
		}

		$settings = WP_Metricas_Settings::get_all();

		wp_enqueue_script(
			'wp-metricas-tracker',
			WP_METRICAS_PLUGIN_URL . 'assets/js/tracker.js',
			array(),
			WP_METRICAS_VERSION,
			true
		);

		$is_elementor = $this->is_elementor_page( $post->ID );
		$has_acf      = $this->has_acf_fields( $post->ID );

		wp_localize_script(
			'wp-metricas-tracker',
			'wpMetricas',
			array(
				'restUrl'         => esc_url_raw( rest_url( 'wp-metricas/v1' ) ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'postId'          => $post->ID,
				'postType'        => $post->post_type,
				'postTitle'       => get_the_title( $post ),
				'url'             => get_permalink( $post ),
				'isElementor'     => $is_elementor,
				'hasAcf'          => $has_acf,
				'trackButtons'    => (bool) $settings['track_buttons'],
				'trackSections'   => (bool) $settings['track_sections'],
				'trackElementor'  => (bool) $settings['track_elementor'],
				'buttonSelectors' => $settings['button_selectors'],
				'sectionSelectors'=> $settings['section_selectors'],
				'sessionId'       => $this->get_session_id(),
				'heartbeatInterval' => 5,
				'minSectionTime'  => 2,
			)
		);
	}

	/**
	 * Genera o recupera ID de sesión.
	 */
	private function get_session_id(): string {
		$cookie_name = 'wp_metricas_sid';

		if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
			$sid = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
			if ( preg_match( '/^[a-f0-9]{32}$/', $sid ) ) {
				return $sid;
			}
		}

		$sid = wp_generate_password( 32, false, false );

		if ( ! headers_sent() ) {
			setcookie(
				$cookie_name,
				$sid,
				time() + DAY_IN_SECONDS,
				COOKIEPATH ? COOKIEPATH : '/',
				COOKIE_DOMAIN,
				is_ssl(),
				true
			);
		}

		return $sid;
	}

	/**
	 * Comprueba si la página usa Elementor.
	 *
	 * @param int $post_id ID del post.
	 */
	private function is_elementor_page( int $post_id ): bool {
		if ( ! WP_Metricas_Settings::get( 'track_elementor', true ) ) {
			return false;
		}

		if ( get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder' ) {
			return true;
		}

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document && $document->is_built_with_elementor() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Comprueba si el post tiene campos ACF.
	 *
	 * @param int $post_id ID del post.
	 */
	private function has_acf_fields( int $post_id ): bool {
		if ( ! WP_Metricas_Settings::get( 'track_acf', true ) ) {
			return false;
		}

		if ( ! function_exists( 'get_fields' ) ) {
			return false;
		}

		$fields = get_fields( $post_id );
		return ! empty( $fields );
	}
}
