<?php
/**
 * Plantilla del dashboard de métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$origen_web_analytics_default_from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
$origen_web_analytics_default_to   = gmdate( 'Y-m-d' );
?>
<div class="wrap wp-metricas-wrap">
	<h1>
		<span class="dashicons dashicons-chart-area"></span>
		<?php esc_html_e( 'Origen Web Analytics Dashboard', 'origen-web-analytics' ); ?>
	</h1>

	<div class="wp-metricas-filters-card">
		<h2 class="wp-metricas-filters-title"><?php esc_html_e( 'Visitas: fechas', 'origen-web-analytics' ); ?></h2>
		<div class="wp-metricas-filters-row">
			<label for="metricas-date-from"><?php esc_html_e( 'Desde', 'origen-web-analytics' ); ?></label>
			<input type="date" id="metricas-date-from" value="<?php echo esc_attr( $origen_web_analytics_default_from ); ?>">
			<label for="metricas-date-to"><?php esc_html_e( 'Hasta', 'origen-web-analytics' ); ?></label>
			<input type="date" id="metricas-date-to" value="<?php echo esc_attr( $origen_web_analytics_default_to ); ?>">
			<label for="metricas-type-filter"><?php esc_html_e( 'Tipo', 'origen-web-analytics' ); ?></label>
			<select id="metricas-type-filter">
				<option value="all"><?php esc_html_e( 'Todos', 'origen-web-analytics' ); ?></option>
				<option value="pages"><?php esc_html_e( 'Páginas', 'origen-web-analytics' ); ?></option>
				<option value="posts"><?php esc_html_e( 'Entradas', 'origen-web-analytics' ); ?></option>
				<option value="cpt"><?php esc_html_e( 'Custom Post Types', 'origen-web-analytics' ); ?></option>
				<option value="acf"><?php esc_html_e( 'Con campos ACF', 'origen-web-analytics' ); ?></option>
				<option value="elementor"><?php esc_html_e( 'Páginas Elementor', 'origen-web-analytics' ); ?></option>
			</select>
			<div class="wp-metricas-filters-actions">
				<button type="button" id="metricas-clear-filters" class="button">
					<?php esc_html_e( 'Limpiar filtros', 'origen-web-analytics' ); ?>
				</button>
				<button type="button" id="metricas-apply-filters" class="button button-primary">
					<?php esc_html_e( 'Aplicar filtros', 'origen-web-analytics' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div id="metricas-loading" class="wp-metricas-loading" style="display:none;">
		<span class="spinner is-active" style="float:none;"></span>
		<?php esc_html_e( 'Cargando métricas...', 'origen-web-analytics' ); ?>
	</div>

	<div class="wp-metricas-cards">
		<div class="wp-metricas-card realtime">
			<div class="card-label">
				<span class="wp-metricas-live-dot"></span>
				<?php esc_html_e( 'Visitas en tiempo real', 'origen-web-analytics' ); ?>
			</div>
			<div class="card-value" id="metricas-active-visitors">—</div>
			<div class="card-sub"><?php esc_html_e( 'Personas activas ahora', 'origen-web-analytics' ); ?></div>
		</div>
		<div class="wp-metricas-card">
			<div class="card-label"><?php esc_html_e( 'Total visitas', 'origen-web-analytics' ); ?></div>
			<div class="card-value" id="metricas-total-visits">—</div>
		</div>
		<div class="wp-metricas-card clicks">
			<div class="card-label"><?php esc_html_e( 'Total clics en botones', 'origen-web-analytics' ); ?></div>
			<div class="card-value" id="metricas-total-clicks">—</div>
		</div>
		<div class="wp-metricas-card sessions">
			<div class="card-label"><?php esc_html_e( 'Sesiones únicas', 'origen-web-analytics' ); ?></div>
			<div class="card-value" id="metricas-unique-sessions">—</div>
		</div>
	</div>

	<div class="wp-metricas-charts">
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Visitas por día', 'origen-web-analytics' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-visits-chart"></canvas>
			</div>
		</div>
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Clics en botones por día', 'origen-web-analytics' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-clicks-chart"></canvas>
			</div>
		</div>
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Visitas por tipo de contenido', 'origen-web-analytics' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-types-chart"></canvas>
			</div>
		</div>
	</div>

	<div class="wp-metricas-tables">
		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Contenido más visitado', 'origen-web-analytics' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Título', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'Visitas', 'origen-web-analytics' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-top-content">
					<tr><td colspan="4"><?php esc_html_e( 'Cargando...', 'origen-web-analytics' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Botones con más clics', 'origen-web-analytics' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Botón', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'URL', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'Clics', 'origen-web-analytics' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-top-buttons">
					<tr><td colspan="4"><?php esc_html_e( 'Cargando...', 'origen-web-analytics' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Tiempo por página', 'origen-web-analytics' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Título', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'Tiempo promedio', 'origen-web-analytics' ); ?></th>
						<th><?php esc_html_e( 'Tiempo total', 'origen-web-analytics' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-page-times">
					<tr><td colspan="5"><?php esc_html_e( 'Cargando...', 'origen-web-analytics' ); ?></td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
