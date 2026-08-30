=== Métricas y Análisis OW ===
Contributors: origenweb
Tags: analytics, metrics, statistics, elementor, tracking
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Métricas de visitas, clics en botones y tiempo por página para WordPress. Compatible con Elementor y ACF.

== Description ==

**Métricas y Análisis OW** es un plugin de analítica ligero que funciona dentro de tu WordPress, sin depender de servicios externos.

= Características =

* Visitas por páginas, entradas y custom post types
* Visitas en tiempo real (personas activas ahora)
* Clics en botones (incluye botones de Elementor)
* Tiempo de permanencia por página, entrada o CPT
* Dashboard con gráficas y filtros por fecha y tipo
* Compatible con Elementor y Advanced Custom Fields (ACF)
* Retención automática de datos configurable
* Herramientas de privacidad (exportar y borrar datos personales)

= Privacidad =

* Los datos se almacenan en la base de datos de tu sitio
* No se envían a servidores de terceros
* Puedes excluir administradores y usuarios registrados
* Incluye sugerencias para tu política de privacidad
* Compatible con las herramientas de exportación y borrado de datos de WordPress

= Requisitos opcionales =

* [Elementor](https://wordpress.org/plugins/elementor/) para detectar páginas y botones de Elementor
* [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) para filtrar contenido con campos ACF

== Installation ==

1. Sube la carpeta `wp-metricas` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el menú **Plugins** en WordPress
3. Ve a **Métricas y Análisis OW → Configuración** para elegir qué medir
4. Ve a **Métricas y Análisis OW → Dashboard** para ver las estadísticas
5. (Recomendado) Revisa **Ajustes → Privacidad** y añade la política sugerida por el plugin

== Frequently Asked Questions ==

= ¿Funciona con Elementor? =

Sí. El plugin detecta páginas creadas con Elementor y rastrea clics en botones `.elementor-button`.

= ¿Funciona con ACF? =

Sí. Identifica contenido con campos ACF y permite filtrarlo en el dashboard.

= ¿Rastrea a los administradores? =

Por defecto no. Puedes cambiarlo en **Métricas y Análisis OW → Configuración**.

= ¿Se envían datos a servidores externos? =

No. Todas las métricas se guardan en tu propia base de datos.

= ¿Cómo se eliminan los datos antiguos? =

Configura los días de retención en **Métricas y Análisis OW → Configuración**. El plugin ejecuta una limpieza diaria automática.

== Screenshots ==

1. Dashboard con métricas, gráficas y visitas en tiempo real
2. Configuración de qué contenido e interacciones medir
3. Filtros por fecha y tipo de contenido

== Changelog ==

= 1.1.1 =
* Nombre del plugin actualizado a Métricas y Análisis OW

= 1.1.0 =
* Preparación para WordPress.org
* Chart.js incluido localmente (sin CDN externo)
* Retención automática de datos implementada
* Política de privacidad, exportación y borrado de datos personales
* Opción para eliminar datos al desinstalar
* Tiempo medido por página en lugar de por sección

= 1.0.3 =
* Tiempo por página, subtítulo en filtros y botón limpiar filtros

= 1.0.2 =
* Dashboard moderno con cards redondeadas y filtros en una sola card

= 1.0.1 =
* Branding Origen Web y visitas en tiempo real

= 1.0.0 =
* Lanzamiento inicial

== Upgrade Notice ==

= 1.1.1 =
Actualización de nombre del plugin a Métricas y Análisis OW.

== Privacy Policy ==

Este plugin recopila datos de navegación con fines estadísticos:

* Identificador de sesión anónimo (cookie `wp_metricas_sid`)
* Páginas visitadas, URL de referencia y tipo de dispositivo
* Clics en botones (texto y URL)
* Tiempo de permanencia en cada página
* ID de usuario de WordPress si la persona está registrada

Los datos se almacenan en la base de datos del sitio. No se transmiten a terceros.

El administrador puede configurar la retención, excluir usuarios y eliminar todos los datos al desinstalar el plugin.

== Third-party libraries ==

* [Chart.js](https://www.chartjs.org/) v4.4.1 — Licencia MIT (incluido en `assets/js/chart.umd.min.js`)
