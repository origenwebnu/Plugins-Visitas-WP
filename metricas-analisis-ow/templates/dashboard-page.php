<?php
/**
 * Plantilla del dashboard de métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$maow_default_from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
$maow_default_to   = gmdate( 'Y-m-d' );
?>
<div class="wrap wp-metricas-wrap">
	<h1>
		<span class="dashicons dashicons-chart-area"></span>
		<?php esc_html_e( 'Dashboard de Métricas y Análisis OW', 'metricas-analisis-ow' ); ?>
	</h1>

	<div class="wp-metricas-filters-card">
		<h2 class="wp-metricas-filters-title"><?php esc_html_e( 'Visitas: fechas', 'metricas-analisis-ow' ); ?></h2>
		<div class="wp-metricas-filters-row">
			<label for="metricas-date-from"><?php esc_html_e( 'Desde', 'metricas-analisis-ow' ); ?></label>
			<input type="date" id="metricas-date-from" value="<?php echo esc_attr( $maow_default_from ); ?>">
			<label for="metricas-date-to"><?php esc_html_e( 'Hasta', 'metricas-analisis-ow' ); ?></label>
			<input type="date" id="metricas-date-to" value="<?php echo esc_attr( $maow_default_to ); ?>">
			<label for="metricas-type-filter"><?php esc_html_e( 'Tipo', 'metricas-analisis-ow' ); ?></label>
			<select id="metricas-type-filter">
				<option value="all"><?php esc_html_e( 'Todos', 'metricas-analisis-ow' ); ?></option>
				<option value="pages"><?php esc_html_e( 'Páginas', 'metricas-analisis-ow' ); ?></option>
				<option value="posts"><?php esc_html_e( 'Entradas', 'metricas-analisis-ow' ); ?></option>
				<option value="cpt"><?php esc_html_e( 'Custom Post Types', 'metricas-analisis-ow' ); ?></option>
				<option value="acf"><?php esc_html_e( 'Con campos ACF', 'metricas-analisis-ow' ); ?></option>
				<option value="elementor"><?php esc_html_e( 'Páginas Elementor', 'metricas-analisis-ow' ); ?></option>
			</select>
			<div class="wp-metricas-filters-actions">
				<button type="button" id="metricas-clear-filters" class="button">
					<?php esc_html_e( 'Limpiar filtros', 'metricas-analisis-ow' ); ?>
				</button>
				<button type="button" id="metricas-apply-filters" class="button button-primary">
					<?php esc_html_e( 'Aplicar filtros', 'metricas-analisis-ow' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div id="metricas-loading" class="wp-metricas-loading" style="display:none;">
		<span class="spinner is-active" style="float:none;"></span>
		<?php esc_html_e( 'Cargando métricas...', 'metricas-analisis-ow' ); ?>
	</div>

	<div class="wp-metricas-cards">
		<div class="wp-metricas-card realtime">
			<div class="card-label">
				<span class="wp-metricas-live-dot"></span>
				<?php esc_html_e( 'Visitas en tiempo real', 'metricas-analisis-ow' ); ?>
			</div>
			<div class="card-value" id="metricas-active-visitors">—</div>
			<div class="card-sub"><?php esc_html_e( 'Personas activas ahora', 'metricas-analisis-ow' ); ?></div>
		</div>
		<div class="wp-metricas-card">
			<div class="card-label"><?php esc_html_e( 'Total visitas', 'metricas-analisis-ow' ); ?></div>
			<div class="card-value" id="metricas-total-visits">—</div>
		</div>
		<div class="wp-metricas-card clicks">
			<div class="card-label"><?php esc_html_e( 'Total clics en botones', 'metricas-analisis-ow' ); ?></div>
			<div class="card-value" id="metricas-total-clicks">—</div>
		</div>
		<div class="wp-metricas-card sessions">
			<div class="card-label"><?php esc_html_e( 'Sesiones únicas', 'metricas-analisis-ow' ); ?></div>
			<div class="card-value" id="metricas-unique-sessions">—</div>
		</div>
	</div>

	<div class="wp-metricas-charts">
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Visitas por día', 'metricas-analisis-ow' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-visits-chart"></canvas>
			</div>
		</div>
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Clics en botones por día', 'metricas-analisis-ow' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-clicks-chart"></canvas>
			</div>
		</div>
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Visitas por tipo de contenido', 'metricas-analisis-ow' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-types-chart"></canvas>
			</div>
		</div>
	</div>

	<div class="wp-metricas-tables">
		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Contenido más visitado', 'metricas-analisis-ow' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Título', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'Visitas', 'metricas-analisis-ow' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-top-content">
					<tr><td colspan="4"><?php esc_html_e( 'Cargando...', 'metricas-analisis-ow' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Botones con más clics', 'metricas-analisis-ow' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Botón', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'URL', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'Clics', 'metricas-analisis-ow' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-top-buttons">
					<tr><td colspan="4"><?php esc_html_e( 'Cargando...', 'metricas-analisis-ow' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Tiempo por página', 'metricas-analisis-ow' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Título', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'Tiempo promedio', 'metricas-analisis-ow' ); ?></th>
						<th><?php esc_html_e( 'Tiempo total', 'metricas-analisis-ow' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-page-times">
					<tr><td colspan="5"><?php esc_html_e( 'Cargando...', 'metricas-analisis-ow' ); ?></td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
