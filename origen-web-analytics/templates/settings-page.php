<?php
/**
 * Plantilla de configuración de métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wp-metricas-wrap">
	<h1>
		<span class="dashicons dashicons-admin-settings"></span>
		<?php esc_html_e( 'Origen Web Analytics Settings', 'origen-web-analytics' ); ?>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Configura qué elementos deseas medir en tu sitio. El plugin es compatible con Elementor y Advanced Custom Fields (ACF).', 'origen-web-analytics' ); ?>
	</p>

	<form method="post" action="options.php" class="origen-web-analytics-settings-form">
		<?php
		settings_fields( 'wp_metricas_settings_group' );
		?>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Tipos de contenido a medir', 'origen-web-analytics' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Contenido', 'origen-web-analytics' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_pages]" value="1" <?php checked( $settings['track_pages'] ); ?>>
								<?php esc_html_e( 'Páginas', 'origen-web-analytics' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_posts]" value="1" <?php checked( $settings['track_posts'] ); ?>>
								<?php esc_html_e( 'Entradas', 'origen-web-analytics' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_custom_post_types]" value="1" <?php checked( $settings['track_custom_post_types'] ); ?>>
								<?php esc_html_e( 'Custom Post Types', 'origen-web-analytics' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_acf]" value="1" <?php checked( $settings['track_acf'] ); ?>>
								<?php esc_html_e( 'Contenido con campos ACF', 'origen-web-analytics' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_elementor]" value="1" <?php checked( $settings['track_elementor'] ); ?>>
								<?php esc_html_e( 'Páginas creadas con Elementor', 'origen-web-analytics' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>

				<?php if ( ! empty( $post_types ) ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Custom Post Types', 'origen-web-analytics' ); ?></th>
					<td>
						<p class="description"><?php esc_html_e( 'Selecciona los CPT específicos a rastrear. Si no seleccionas ninguno, se rastrearán todos los públicos.', 'origen-web-analytics' ); ?></p>
						<div class="cpt-list">
							<?php foreach ( $post_types as $origen_web_analytics_pt ) : ?>
								<label>
									<input type="checkbox"
										name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[custom_post_types][]"
										value="<?php echo esc_attr( $origen_web_analytics_pt->name ); ?>"
										<?php checked( in_array( $origen_web_analytics_pt->name, $settings['custom_post_types'], true ) ); ?>>
									<?php echo esc_html( $origen_web_analytics_pt->labels->singular_name ); ?>
									<code>(<?php echo esc_html( $origen_web_analytics_pt->name ); ?>)</code>
								</label>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
				<?php endif; ?>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Interacciones a medir', 'origen-web-analytics' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Eventos', 'origen-web-analytics' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_buttons]" value="1" <?php checked( $settings['track_buttons'] ); ?>>
								<?php esc_html_e( 'Clics en botones (incluye botones de Elementor)', 'origen-web-analytics' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_sections]" value="1" <?php checked( $settings['track_sections'] ); ?>>
								<?php esc_html_e( 'Tiempo en páginas, entradas y contenido', 'origen-web-analytics' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Selectores CSS personalizados', 'origen-web-analytics' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="button_selectors"><?php esc_html_e( 'Selectores de botones', 'origen-web-analytics' ); ?></label>
					</th>
					<td>
						<textarea name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[button_selectors]" id="button_selectors" rows="6" class="code"><?php echo esc_textarea( implode( "\n", $settings['button_selectors'] ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Un selector CSS por línea. Por defecto incluye .elementor-button y botones comunes.', 'origen-web-analytics' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Privacidad y retención', 'origen-web-analytics' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Exclusiones', 'origen-web-analytics' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[exclude_admins]" value="1" <?php checked( $settings['exclude_admins'] ); ?>>
								<?php esc_html_e( 'No rastrear administradores', 'origen-web-analytics' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[exclude_logged_in]" value="1" <?php checked( $settings['exclude_logged_in'] ); ?>>
								<?php esc_html_e( 'No rastrear usuarios registrados', 'origen-web-analytics' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="retention_days"><?php esc_html_e( 'Retención de datos', 'origen-web-analytics' ); ?></label>
					</th>
					<td>
						<input type="number" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[retention_days]" id="retention_days" value="<?php echo esc_attr( $settings['retention_days'] ); ?>" min="7" max="365" step="1">
						<?php esc_html_e( 'días', 'origen-web-analytics' ); ?>
						<p class="description"><?php esc_html_e( 'Los registros más antiguos se eliminan automáticamente cada día.', 'origen-web-analytics' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Desinstalación', 'origen-web-analytics' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[delete_data_on_uninstall]" value="1" <?php checked( $settings['delete_data_on_uninstall'] ); ?>>
							<?php esc_html_e( 'Eliminar todos los datos al desinstalar el plugin', 'origen-web-analytics' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Ubicación geográfica', 'origen-web-analytics' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'El plugin registra país y ciudad desde cabeceras del servidor o CDN (Cloudflare, CloudFront, etc.). No usa APIs externas. Si usas Cloudflare, activa las cabeceras de geolocalización para mejores resultados.', 'origen-web-analytics' ); ?>
			</p>

			<h2><?php esc_html_e( 'Privacidad', 'origen-web-analytics' ); ?></h2>
			<p class="description">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: link to privacy settings page */
						__( 'Este plugin recopila datos de navegación de forma anónima. Puedes añadir la información de privacidad sugerida en %s.', 'origen-web-analytics' ),
						'<a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">' . esc_html__( 'Ajustes → Privacidad', 'origen-web-analytics' ) . '</a>'
					),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				);
				?>
			</p>
		</div>

		<?php submit_button( __( 'Guardar configuración', 'origen-web-analytics' ) ); ?>
	</form>
</div>
