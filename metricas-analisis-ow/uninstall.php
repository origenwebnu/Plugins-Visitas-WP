<?php
/**
 * Limpieza al desinstalar el plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-cron.php';

WP_Metricas_Cron::unschedule();

$maow_settings = get_option( 'wp_metricas_settings', array() );

if ( empty( $maow_settings['delete_data_on_uninstall'] ) ) {
	return;
}

WP_Metricas_Database::drop_tables_and_options();
