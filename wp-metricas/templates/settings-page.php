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
		<?php esc_html_e( 'Configuración de Métricas', 'wp-metricas' ); ?>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Configura qué elementos deseas medir en tu sitio. El plugin es compatible con Elementor y Advanced Custom Fields (ACF).', 'wp-metricas' ); ?>
	</p>

	<form method="post" action="options.php" class="wp-metricas-settings-form">
		<?php
		settings_fields( 'wp_metricas_settings_group' );
		?>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Tipos de contenido a medir', 'wp-metricas' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Contenido', 'wp-metricas' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_pages]" value="1" <?php checked( $settings['track_pages'] ); ?>>
								<?php esc_html_e( 'Páginas', 'wp-metricas' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_posts]" value="1" <?php checked( $settings['track_posts'] ); ?>>
								<?php esc_html_e( 'Entradas', 'wp-metricas' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_custom_post_types]" value="1" <?php checked( $settings['track_custom_post_types'] ); ?>>
								<?php esc_html_e( 'Custom Post Types', 'wp-metricas' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_acf]" value="1" <?php checked( $settings['track_acf'] ); ?>>
								<?php esc_html_e( 'Contenido con campos ACF', 'wp-metricas' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_elementor]" value="1" <?php checked( $settings['track_elementor'] ); ?>>
								<?php esc_html_e( 'Páginas creadas con Elementor', 'wp-metricas' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>

				<?php if ( ! empty( $post_types ) ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Custom Post Types', 'wp-metricas' ); ?></th>
					<td>
						<p class="description"><?php esc_html_e( 'Selecciona los CPT específicos a rastrear. Si no seleccionas ninguno, se rastrearán todos los públicos.', 'wp-metricas' ); ?></p>
						<div class="cpt-list">
							<?php foreach ( $post_types as $pt ) : ?>
								<label>
									<input type="checkbox"
										name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[custom_post_types][]"
										value="<?php echo esc_attr( $pt->name ); ?>"
										<?php checked( in_array( $pt->name, $settings['custom_post_types'], true ) ); ?>>
									<?php echo esc_html( $pt->labels->singular_name ); ?>
									<code>(<?php echo esc_html( $pt->name ); ?>)</code>
								</label>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
				<?php endif; ?>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Interacciones a medir', 'wp-metricas' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Eventos', 'wp-metricas' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_buttons]" value="1" <?php checked( $settings['track_buttons'] ); ?>>
								<?php esc_html_e( 'Clics en botones (incluye botones de Elementor)', 'wp-metricas' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_sections]" value="1" <?php checked( $settings['track_sections'] ); ?>>
								<?php esc_html_e( 'Tiempo en páginas, entradas y contenido', 'wp-metricas' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Selectores CSS personalizados', 'wp-metricas' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="button_selectors"><?php esc_html_e( 'Selectores de botones', 'wp-metricas' ); ?></label>
					</th>
					<td>
						<textarea name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[button_selectors]" id="button_selectors" rows="6" class="code"><?php echo esc_textarea( implode( "\n", $settings['button_selectors'] ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Un selector CSS por línea. Por defecto incluye .elementor-button y botones comunes.', 'wp-metricas' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="section_selectors"><?php esc_html_e( 'Selectores de secciones', 'wp-metricas' ); ?></label>
					</th>
					<td>
						<textarea name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[section_selectors]" id="section_selectors" rows="6" class="code"><?php echo esc_textarea( implode( "\n", $settings['section_selectors'] ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Un selector CSS por línea. Por defecto incluye .elementor-section.', 'wp-metricas' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Privacidad y retención', 'wp-metricas' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Exclusiones', 'wp-metricas' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[exclude_admins]" value="1" <?php checked( $settings['exclude_admins'] ); ?>>
								<?php esc_html_e( 'No rastrear administradores', 'wp-metricas' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[exclude_logged_in]" value="1" <?php checked( $settings['exclude_logged_in'] ); ?>>
								<?php esc_html_e( 'No rastrear usuarios registrados', 'wp-metricas' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="retention_days"><?php esc_html_e( 'Retención de datos', 'wp-metricas' ); ?></label>
					</th>
					<td>
						<input type="number" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[retention_days]" id="retention_days" value="<?php echo esc_attr( $settings['retention_days'] ); ?>" min="7" max="365" step="1">
						<?php esc_html_e( 'días', 'wp-metricas' ); ?>
						<p class="description"><?php esc_html_e( 'Los datos más antiguos se eliminarán automáticamente (próxima versión).', 'wp-metricas' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<?php submit_button( __( 'Guardar configuración', 'wp-metricas' ) ); ?>
	</form>
</div>
