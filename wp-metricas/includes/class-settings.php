<?php
/**
 * Configuración del plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Settings {

	const OPTION_KEY = 'wp_metricas_settings';

	/**
	 * @var WP_Metricas_Settings|null
	 */
	private static $instance = null;

	public static function instance(): WP_Metricas_Settings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Valores por defecto de configuración.
	 */
	public static function defaults(): array {
		return array(
			'track_pages'           => true,
			'track_posts'           => true,
			'track_custom_post_types' => true,
			'track_acf'             => true,
			'track_buttons'         => true,
			'track_sections'        => true,
			'track_elementor'       => true,
			'exclude_admins'          => true,
			'exclude_logged_in'       => false,
			'retention_days'          => 90,
			'custom_post_types'       => array(),
			'button_selectors'        => array(
				'.elementor-button',
				'a.elementor-button',
				'button',
				'.wp-block-button__link',
				'.btn',
				'[data-metricas-track]',
			),
			'section_selectors'       => array(
				'.elementor-section',
				'[data-element_type="section"]',
				'section[data-metricas-section]',
				'.wp-metricas-section',
			),
		);
	}

	/**
	 * Obtiene la configuración completa.
	 */
	public static function get_all(): array {
		$settings = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $settings, self::defaults() );
	}

	/**
	 * Obtiene un valor de configuración.
	 *
	 * @param string $key     Clave.
	 * @param mixed  $default Valor por defecto.
	 */
	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Registra opciones en WordPress.
	 */
	public function register_settings(): void {
		register_setting(
			'wp_metricas_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitiza la configuración guardada.
	 *
	 * @param array $input Datos del formulario.
	 */
	public function sanitize_settings( $input ): array {
		if ( ! is_array( $input ) ) {
			return self::defaults();
		}

		$defaults = self::defaults();
		$output   = array();

		$bool_keys = array(
			'track_pages',
			'track_posts',
			'track_custom_post_types',
			'track_acf',
			'track_buttons',
			'track_sections',
			'track_elementor',
			'exclude_admins',
			'exclude_logged_in',
		);

		foreach ( $bool_keys as $key ) {
			$output[ $key ] = ! empty( $input[ $key ] );
		}

		$output['retention_days'] = max( 7, min( 365, absint( $input['retention_days'] ?? $defaults['retention_days'] ) ) );

		$output['custom_post_types'] = array();
		if ( ! empty( $input['custom_post_types'] ) && is_array( $input['custom_post_types'] ) ) {
			$output['custom_post_types'] = array_map( 'sanitize_key', $input['custom_post_types'] );
		}

		$output['button_selectors'] = $this->sanitize_selectors( $input['button_selectors'] ?? '' );
		if ( empty( $output['button_selectors'] ) ) {
			$output['button_selectors'] = $defaults['button_selectors'];
		}

		$output['section_selectors'] = $this->sanitize_selectors( $input['section_selectors'] ?? '' );
		if ( empty( $output['section_selectors'] ) ) {
			$output['section_selectors'] = $defaults['section_selectors'];
		}

		return $output;
	}

	/**
	 * Sanitiza selectores CSS (uno por línea).
	 *
	 * @param string|array $input Selectores.
	 */
	private function sanitize_selectors( $input ): array {
		if ( is_array( $input ) ) {
			$lines = $input;
		} else {
			$lines = explode( "\n", (string) $input );
		}

		$selectors = array();
		foreach ( $lines as $line ) {
			$line = trim( sanitize_text_field( $line ) );
			if ( $line !== '' ) {
				$selectors[] = $line;
			}
		}

		return array_unique( $selectors );
	}

	/**
	 * Determina si el tracking está habilitado para el contexto actual.
	 */
	public static function should_track(): bool {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		$settings = self::get_all();

		if ( ! empty( $settings['exclude_admins'] ) && current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! empty( $settings['exclude_logged_in'] ) && is_user_logged_in() ) {
			return false;
		}

		return true;
	}

	/**
	 * Determina si se debe rastrear el post actual.
	 *
	 * @param WP_Post|null $post Post actual.
	 */
	public static function should_track_post( $post ): bool {
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		$settings  = self::get_all();
		$post_type = $post->post_type;

		if ( 'page' === $post_type && empty( $settings['track_pages'] ) ) {
			return false;
		}

		if ( 'post' === $post_type && empty( $settings['track_posts'] ) ) {
			return false;
		}

		$builtin = array( 'page', 'post', 'attachment', 'revision', 'nav_menu_item' );
		if ( ! in_array( $post_type, $builtin, true ) ) {
			if ( empty( $settings['track_custom_post_types'] ) ) {
				return false;
			}

			$allowed_cpts = $settings['custom_post_types'];
			if ( ! empty( $allowed_cpts ) && ! in_array( $post_type, $allowed_cpts, true ) ) {
				return false;
			}
		}

		return true;
	}
}
