# WP Métricas

Plugin de WordPress para métricas de visitas, clics en botones y tiempo por sección. Compatible con **Elementor** y **ACF**.

## Características

- **Visitas**: páginas, entradas, custom post types, contenido ACF y páginas Elementor
- **Clics en botones**: rastrea botones de Elementor y selectores personalizados
- **Tiempo por sección**: mide cuánto tiempo pasan los usuarios en cada sección (Intersection Observer)
- **Menú "Métricas"** en el admin de WordPress con:
  - **Dashboard**: gráficas, tablas y filtros por fecha y tipo
  - **Configuración**: elige qué medir y personaliza selectores CSS

## Instalación

1. Copia la carpeta `wp-metricas` en `wp-content/plugins/`
2. Activa el plugin en WordPress
3. Ve a **Métricas → Configuración** para configurar el tracking
4. Ve a **Métricas → Dashboard** para ver las estadísticas

## Requisitos

- WordPress 5.8+
- PHP 7.4+
- Opcional: Elementor, Advanced Custom Fields (ACF)

## Estructura

```
wp-metricas/
├── wp-metricas.php          # Archivo principal
├── includes/
│   ├── class-database.php   # Tablas y almacenamiento
│   ├── class-settings.php   # Configuración
│   ├── class-tracker.php    # Tracking frontend
│   ├── class-rest-api.php   # API REST
│   ├── class-admin.php      # Menús admin
│   └── class-dashboard.php  # Dashboard
├── assets/
│   ├── css/admin.css
│   └── js/
│       ├── tracker.js       # Script de tracking
│       └── dashboard.js     # Gráficas Chart.js
└── templates/
    ├── dashboard-page.php
    └── settings-page.php
```

## Licencia

GPLv2 or later
