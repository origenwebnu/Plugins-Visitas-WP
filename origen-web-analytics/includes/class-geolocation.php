<?php
/**
 * Resolución de país y ciudad del visitante (sin APIs externas).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Metricas_Geolocation {

	/**
	 * Códigos ISO de país a nombre en español/inglés.
	 *
	 * @return array<string, string>
	 */
	public static function country_names(): array {
		return array(
			'AD' => 'Andorra',
			'AE' => 'United Arab Emirates',
			'AR' => 'Argentina',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'BE' => 'Belgium',
			'BO' => 'Bolivia',
			'BR' => 'Brazil',
			'CA' => 'Canada',
			'CH' => 'Switzerland',
			'CL' => 'Chile',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CU' => 'Cuba',
			'DE' => 'Germany',
			'DK' => 'Denmark',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'ES' => 'Spain',
			'FI' => 'Finland',
			'FR' => 'France',
			'GB' => 'United Kingdom',
			'GR' => 'Greece',
			'GT' => 'Guatemala',
			'HN' => 'Honduras',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IN' => 'India',
			'IT' => 'Italy',
			'JP' => 'Japan',
			'MX' => 'Mexico',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NZ' => 'New Zealand',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PY' => 'Paraguay',
			'RU' => 'Russia',
			'SE' => 'Sweden',
			'SV' => 'El Salvador',
			'US' => 'United States',
			'UY' => 'Uruguay',
			'VE' => 'Venezuela',
			'XX' => 'Unknown',
		);
	}

	/**
	 * Obtiene país y ciudad del visitante actual.
	 *
	 * @return array{country_code: string, country_name: string, city: string}
	 */
	public static function resolve(): array {
		$location = self::from_headers();

		if ( empty( $location['country_code'] ) ) {
			$location = self::from_server_geoip();
		}

		/**
		 * Permite integrar bases de datos GeoIP locales u otros proveedores.
		 *
		 * @param array  $location Datos de ubicación.
		 * @param string $ip       IP del visitante.
		 */
		$location = apply_filters(
			'origen_web_analytics_visitor_location',
			$location,
			self::get_client_ip()
		);

		return self::normalize( $location );
	}

	/**
	 * Lee cabeceras de CDN/proxy habituales.
	 *
	 * @return array{country_code: string, country_name: string, city: string}
	 */
	private static function from_headers(): array {
		$country_code = '';
		$city         = '';

		$country_headers = array(
			'HTTP_CF_IPCOUNTRY',
			'HTTP_CLOUDFRONT_VIEWER_COUNTRY',
			'HTTP_X_COUNTRY_CODE',
			'HTTP_X_SUCURI_COUNTRY',
			'HTTP_X_APPENGINE_COUNTRY',
		);

		foreach ( $country_headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$country_code = self::sanitize_country_code( wp_unslash( $_SERVER[ $header ] ) );
				if ( $country_code ) {
					break;
				}
			}
		}

		$city_headers = array(
			'HTTP_CF_IPCITY',
			'HTTP_CLOUDFRONT_VIEWER_CITY',
			'HTTP_X_SUCURI_CITY',
		);

		foreach ( $city_headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$city = self::sanitize_city( wp_unslash( $_SERVER[ $header ] ) );
				if ( $city ) {
					break;
				}
			}
		}

		return array(
			'country_code' => $country_code,
			'country_name' => self::country_name_from_code( $country_code ),
			'city'         => $city,
		);
	}

	/**
	 * Lee variables GEOIP del servidor (mod_geoip, MaxMind en hosting, etc.).
	 *
	 * @return array{country_code: string, country_name: string, city: string}
	 */
	private static function from_server_geoip(): array {
		$country_code = '';
		$city         = '';

		$country_vars = array(
			'GEOIP_COUNTRY_CODE',
			'HTTP_GEOIP_COUNTRY_CODE',
			'MM_COUNTRY_CODE',
		);

		foreach ( $country_vars as $var ) {
			if ( ! empty( $_SERVER[ $var ] ) ) {
				$country_code = self::sanitize_country_code( wp_unslash( $_SERVER[ $var ] ) );
				if ( $country_code ) {
					break;
				}
			}
		}

		$city_vars = array(
			'GEOIP_CITY',
			'HTTP_GEOIP_CITY',
			'MM_CITY_NAME',
		);

		foreach ( $city_vars as $var ) {
			if ( ! empty( $_SERVER[ $var ] ) ) {
				$city = self::sanitize_city( wp_unslash( $_SERVER[ $var ] ) );
				if ( $city ) {
					break;
				}
			}
		}

		return array(
			'country_code' => $country_code,
			'country_name' => self::country_name_from_code( $country_code ),
			'city'         => $city,
		);
	}

	/**
	 * Normaliza la ubicación final.
	 *
	 * @param array $location Datos crudos.
	 * @return array{country_code: string, country_name: string, city: string}
	 */
	private static function normalize( array $location ): array {
		$country_code = self::sanitize_country_code( $location['country_code'] ?? '' );
		$city         = self::sanitize_city( $location['city'] ?? '' );
		$country_name = sanitize_text_field( $location['country_name'] ?? '' );

		if ( $country_code && ! $country_name ) {
			$country_name = self::country_name_from_code( $country_code );
		}

		if ( ! $country_code ) {
			$country_code = 'XX';
			$country_name = self::country_name_from_code( 'XX' );
		}

		return array(
			'country_code' => $country_code,
			'country_name' => $country_name,
			'city'         => $city,
		);
	}

	/**
	 * Obtiene la IP del visitante.
	 */
	public static function get_client_ip(): string {
		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			$raw = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
			$ips = array_map( 'trim', explode( ',', $raw ) );

			foreach ( $ips as $ip ) {
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '';
	}

	/**
	 * Sanitiza código de país ISO-3166 alpha-2.
	 *
	 * @param string $code Código recibido.
	 */
	private static function sanitize_country_code( string $code ): string {
		$code = strtoupper( sanitize_text_field( $code ) );

		if ( 'T1' === $code ) {
			return 'XX';
		}

		if ( preg_match( '/^[A-Z]{2}$/', $code ) ) {
			return $code;
		}

		return '';
	}

	/**
	 * Sanitiza nombre de ciudad.
	 *
	 * @param string $city Ciudad recibida.
	 */
	private static function sanitize_city( string $city ): string {
		$city = sanitize_text_field( $city );
		return mb_substr( $city, 0, 100 );
	}

	/**
	 * Convierte código de país a nombre legible.
	 *
	 * @param string $code Código ISO.
	 */
	public static function country_name_from_code( string $code ): string {
		$code  = self::sanitize_country_code( $code ) ?: 'XX';
		$names = self::country_names();

		if ( isset( $names[ $code ] ) ) {
			return $names[ $code ];
		}

		if ( function_exists( 'locale_get_display_region' ) ) {
			$name = locale_get_display_region( 'und-' . $code, get_locale() );
			if ( $name && $name !== 'und-' . $code ) {
				return sanitize_text_field( $name );
			}
		}

		return $code;
	}
}
