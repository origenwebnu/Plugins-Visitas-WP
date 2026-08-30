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
		<?php esc_html_e( 'Configuración de Métricas y Análisis OW', 'metricas-analisis-ow' ); ?>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Configura qué elementos deseas medir en tu sitio. El plugin es compatible con Elementor y Advanced Custom Fields (ACF).', 'metricas-analisis-ow' ); ?>
	</p>

	<form method="post" action="options.php" class="metricas-analisis-ow-settings-form">
		<?php
		settings_fields( 'wp_metricas_settings_group' );
		?>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Tipos de contenido a medir', 'metricas-analisis-ow' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Contenido', 'metricas-analisis-ow' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_pages]" value="1" <?php checked( $settings['track_pages'] ); ?>>
								<?php esc_html_e( 'Páginas', 'metricas-analisis-ow' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_posts]" value="1" <?php checked( $settings['track_posts'] ); ?>>
								<?php esc_html_e( 'Entradas', 'metricas-analisis-ow' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_custom_post_types]" value="1" <?php checked( $settings['track_custom_post_types'] ); ?>>
								<?php esc_html_e( 'Custom Post Types', 'metricas-analisis-ow' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_acf]" value="1" <?php checked( $settings['track_acf'] ); ?>>
								<?php esc_html_e( 'Contenido con campos ACF', 'metricas-analisis-ow' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_elementor]" value="1" <?php checked( $settings['track_elementor'] ); ?>>
								<?php esc_html_e( 'Páginas creadas con Elementor', 'metricas-analisis-ow' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>

				<?php if ( ! empty( $post_types ) ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Custom Post Types', 'metricas-analisis-ow' ); ?></th>
					<td>
						<p class="description"><?php esc_html_e( 'Selecciona los CPT específicos a rastrear. Si no seleccionas ninguno, se rastrearán todos los públicos.', 'metricas-analisis-ow' ); ?></p>
						<div class="cpt-list">
							<?php foreach ( $post_types as $maow_pt ) : ?>
								<label>
									<input type="checkbox"
										name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[custom_post_types][]"
										value="<?php echo esc_attr( $maow_pt->name ); ?>"
										<?php checked( in_array( $maow_pt->name, $settings['custom_post_types'], true ) ); ?>>
									<?php echo esc_html( $maow_pt->labels->singular_name ); ?>
									<code>(<?php echo esc_html( $maow_pt->name ); ?>)</code>
								</label>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
				<?php endif; ?>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Interacciones a medir', 'metricas-analisis-ow' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Eventos', 'metricas-analisis-ow' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_buttons]" value="1" <?php checked( $settings['track_buttons'] ); ?>>
								<?php esc_html_e( 'Clics en botones (incluye botones de Elementor)', 'metricas-analisis-ow' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[track_sections]" value="1" <?php checked( $settings['track_sections'] ); ?>>
								<?php esc_html_e( 'Tiempo en páginas, entradas y contenido', 'metricas-analisis-ow' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Selectores CSS personalizados', 'metricas-analisis-ow' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="button_selectors"><?php esc_html_e( 'Selectores de botones', 'metricas-analisis-ow' ); ?></label>
					</th>
					<td>
						<textarea name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[button_selectors]" id="button_selectors" rows="6" class="code"><?php echo esc_textarea( implode( "\n", $settings['button_selectors'] ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Un selector CSS por línea. Por defecto incluye .elementor-button y botones comunes.', 'metricas-analisis-ow' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Privacidad y retención', 'metricas-analisis-ow' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Exclusiones', 'metricas-analisis-ow' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[exclude_admins]" value="1" <?php checked( $settings['exclude_admins'] ); ?>>
								<?php esc_html_e( 'No rastrear administradores', 'metricas-analisis-ow' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[exclude_logged_in]" value="1" <?php checked( $settings['exclude_logged_in'] ); ?>>
								<?php esc_html_e( 'No rastrear usuarios registrados', 'metricas-analisis-ow' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="retention_days"><?php esc_html_e( 'Retención de datos', 'metricas-analisis-ow' ); ?></label>
					</th>
					<td>
						<input type="number" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[retention_days]" id="retention_days" value="<?php echo esc_attr( $settings['retention_days'] ); ?>" min="7" max="365" step="1">
						<?php esc_html_e( 'días', 'metricas-analisis-ow' ); ?>
						<p class="description"><?php esc_html_e( 'Los registros más antiguos se eliminan automáticamente cada día.', 'metricas-analisis-ow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Desinstalación', 'metricas-analisis-ow' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( WP_Metricas_Settings::OPTION_KEY ); ?>[delete_data_on_uninstall]" value="1" <?php checked( $settings['delete_data_on_uninstall'] ); ?>>
							<?php esc_html_e( 'Eliminar todos los datos al desinstalar el plugin', 'metricas-analisis-ow' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="settings-section">
			<h2><?php esc_html_e( 'Privacidad', 'metricas-analisis-ow' ); ?></h2>
			<p class="description">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: link to privacy settings page */
						__( 'Este plugin recopila datos de navegación de forma anónima. Puedes añadir la información de privacidad sugerida en %s.', 'metricas-analisis-ow' ),
						'<a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">' . esc_html__( 'Ajustes → Privacidad', 'metricas-analisis-ow' ) . '</a>'
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

		<?php submit_button( __( 'Guardar configuración', 'metricas-analisis-ow' ) ); ?>
	</form>
</div>
