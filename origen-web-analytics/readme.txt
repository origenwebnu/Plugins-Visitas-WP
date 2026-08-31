=== Origen Web Analytics ===
Contributors: origenweb
Tags: analytics, metrics, statistics, elementor, tracking
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight self-hosted analytics for WordPress: page visits, button clicks, and time on page. Compatible with Elementor and ACF.

== Description ==

**Origen Web Analytics** is a lightweight analytics plugin by [Origen Web](https://origenweb.co) that runs inside your WordPress site without relying on external services.

= Features =

* Page, post, and custom post type visits
* Real-time active visitors
* Button click tracking (including Elementor buttons)
* Time on page per post, page, or CPT
* Dashboard with charts and date/type filters
* Elementor and Advanced Custom Fields (ACF) compatible
* Configurable automatic data retention
* Privacy tools (export and erase personal data)

= Privacy =

* Data is stored in your site's database
* No data is sent to third-party servers
* Admins and logged-in users can be excluded
* Suggested privacy policy content included
* Compatible with WordPress personal data export and erasure tools

= Optional integrations =

* [Elementor](https://wordpress.org/plugins/elementor/) to detect Elementor pages and buttons
* [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) to filter ACF content

== Installation ==

1. Upload the `origen-web-analytics` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Origen Web Analytics → Settings** to configure tracking
4. Go to **Origen Web Analytics → Dashboard** to view statistics
5. (Recommended) Review **Settings → Privacy** and add the suggested policy content

== Frequently Asked Questions ==

= Does it work with Elementor? =

Yes. The plugin detects Elementor pages and tracks clicks on `.elementor-button` elements.

= Does it work with ACF? =

Yes. It identifies content with ACF fields and lets you filter it in the dashboard.

= Does it track administrators? =

Not by default. You can change this in **Origen Web Analytics → Settings**.

= Is data sent to external servers? =

No. All metrics are stored in your own database.

= How are old records deleted? =

Set the retention days in **Origen Web Analytics → Settings**. The plugin runs a daily cleanup task.

== Screenshots ==

1. Dashboard with metrics, charts, and real-time visitors
2. Settings page to choose what to track
3. Date and content type filters

== Changelog ==

= 2.0.0 =
* Renamed plugin to Origen Web Analytics (distinctive brand name for WordPress.org)
* Updated Chart.js to v4.5.1

= 1.1.4 =
* Requires WordPress 6.2+ (needed for safe %i SQL placeholders)
* Fixed Plugin Check variable prefix warnings in templates

= 1.1.0 =
* WordPress.org preparation release
* Local Chart.js bundle, data retention, privacy tools, uninstall cleanup

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.0.0 =
Plugin renamed to Origen Web Analytics. New folder slug: origen-web-analytics.

== Privacy Policy ==

This plugin collects browsing data for statistical purposes:

* Anonymous session identifier (cookie `wp_metricas_sid`)
* Visited pages, referrer URL, and device type
* Button clicks (text and URL)
* Time spent on each page
* WordPress user ID when the visitor is logged in

Data is stored in the site database and is not transmitted to third parties.

Site administrators can configure retention, exclude users, and delete all data on uninstall.

== Third-party libraries ==

* [Chart.js](https://www.chartjs.org/) v4.5.1 — MIT License (bundled in `assets/js/chart.umd.min.js`)
