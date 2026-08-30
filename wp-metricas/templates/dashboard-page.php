<?php
/**
 * Plantilla del dashboard de métricas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
$default_to   = gmdate( 'Y-m-d' );
?>
<div class="wrap wp-metricas-wrap">
	<h1>
		<span class="dashicons dashicons-chart-area"></span>
		<?php esc_html_e( 'Dashboard de Métricas', 'wp-metricas' ); ?>
	</h1>

	<div class="wp-metricas-filters-card">
		<h2 class="wp-metricas-filters-title"><?php esc_html_e( 'Visitas: fechas', 'wp-metricas' ); ?></h2>
		<div class="wp-metricas-filters-row">
			<label for="metricas-date-from"><?php esc_html_e( 'Desde', 'wp-metricas' ); ?></label>
			<input type="date" id="metricas-date-from" value="<?php echo esc_attr( $default_from ); ?>">
			<label for="metricas-date-to"><?php esc_html_e( 'Hasta', 'wp-metricas' ); ?></label>
			<input type="date" id="metricas-date-to" value="<?php echo esc_attr( $default_to ); ?>">
			<label for="metricas-type-filter"><?php esc_html_e( 'Tipo', 'wp-metricas' ); ?></label>
			<select id="metricas-type-filter">
				<option value="all"><?php esc_html_e( 'Todos', 'wp-metricas' ); ?></option>
				<option value="pages"><?php esc_html_e( 'Páginas', 'wp-metricas' ); ?></option>
				<option value="posts"><?php esc_html_e( 'Entradas', 'wp-metricas' ); ?></option>
				<option value="cpt"><?php esc_html_e( 'Custom Post Types', 'wp-metricas' ); ?></option>
				<option value="acf"><?php esc_html_e( 'Con campos ACF', 'wp-metricas' ); ?></option>
				<option value="elementor"><?php esc_html_e( 'Páginas Elementor', 'wp-metricas' ); ?></option>
			</select>
			<div class="wp-metricas-filters-actions">
				<button type="button" id="metricas-clear-filters" class="button">
					<?php esc_html_e( 'Limpiar filtros', 'wp-metricas' ); ?>
				</button>
				<button type="button" id="metricas-apply-filters" class="button button-primary">
					<?php esc_html_e( 'Aplicar filtros', 'wp-metricas' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div id="metricas-loading" class="wp-metricas-loading" style="display:none;">
		<span class="spinner is-active" style="float:none;"></span>
		<?php esc_html_e( 'Cargando métricas...', 'wp-metricas' ); ?>
	</div>

	<div class="wp-metricas-cards">
		<div class="wp-metricas-card realtime">
			<div class="card-label">
				<span class="wp-metricas-live-dot"></span>
				<?php esc_html_e( 'Visitas en tiempo real', 'wp-metricas' ); ?>
			</div>
			<div class="card-value" id="metricas-active-visitors">—</div>
			<div class="card-sub"><?php esc_html_e( 'Personas activas ahora', 'wp-metricas' ); ?></div>
		</div>
		<div class="wp-metricas-card">
			<div class="card-label"><?php esc_html_e( 'Total visitas', 'wp-metricas' ); ?></div>
			<div class="card-value" id="metricas-total-visits">—</div>
		</div>
		<div class="wp-metricas-card clicks">
			<div class="card-label"><?php esc_html_e( 'Total clics en botones', 'wp-metricas' ); ?></div>
			<div class="card-value" id="metricas-total-clicks">—</div>
		</div>
		<div class="wp-metricas-card sessions">
			<div class="card-label"><?php esc_html_e( 'Sesiones únicas', 'wp-metricas' ); ?></div>
			<div class="card-value" id="metricas-unique-sessions">—</div>
		</div>
	</div>

	<div class="wp-metricas-charts">
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Visitas por día', 'wp-metricas' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-visits-chart"></canvas>
			</div>
		</div>
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Clics en botones por día', 'wp-metricas' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-clicks-chart"></canvas>
			</div>
		</div>
		<div class="wp-metricas-chart-box">
			<h3><?php esc_html_e( 'Visitas por tipo de contenido', 'wp-metricas' ); ?></h3>
			<div class="chart-container">
				<canvas id="metricas-types-chart"></canvas>
			</div>
		</div>
	</div>

	<div class="wp-metricas-tables">
		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Contenido más visitado', 'wp-metricas' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Título', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'Visitas', 'wp-metricas' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-top-content">
					<tr><td colspan="4"><?php esc_html_e( 'Cargando...', 'wp-metricas' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Botones con más clics', 'wp-metricas' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Botón', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'URL', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'Clics', 'wp-metricas' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-top-buttons">
					<tr><td colspan="4"><?php esc_html_e( 'Cargando...', 'wp-metricas' ); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="wp-metricas-table-box">
			<h3><?php esc_html_e( 'Tiempo por página', 'wp-metricas' ); ?></h3>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Título', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'Tiempo promedio', 'wp-metricas' ); ?></th>
						<th><?php esc_html_e( 'Tiempo total', 'wp-metricas' ); ?></th>
					</tr>
				</thead>
				<tbody id="metricas-page-times">
					<tr><td colspan="5"><?php esc_html_e( 'Cargando...', 'wp-metricas' ); ?></td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
