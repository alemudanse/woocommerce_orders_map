<?php
/**
 * Plugin Name: WooCommerce Orders Map
 * Description: Map view of WooCommerce orders with driver assignment and a driver dashboard.
 * Version: 1.0.0
 * Author: Your Company
 * License: GPLv2 or later
 * Text Domain: woocommerce-orders-map
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Core plugin constants

define( 'WOM_PLUGIN_FILE', __FILE__ );
define( 'WOM_PLUGIN_VERSION', '1.0.0' );
define( 'WOM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Meta keys (add more as needed)
define( 'WOM_META_ASSIGNED_DRIVER', '_wom_assigned_driver' );
define( 'WOM_META_DRIVER_STATUS', '_wom_driver_status' );
// Bootstrap feature modules
require_once WOM_PLUGIN_PATH . 'includes/driver-dashboard.php';
require_once WOM_PLUGIN_PATH . 'includes/driver-frontend-reports.php';
require_once WOM_PLUGIN_PATH . 'includes/geocoding.php';
require_once WOM_PLUGIN_PATH . 'includes/map-dashboard.php';
require_once WOM_PLUGIN_PATH . 'includes/notifications.php';
require_once WOM_PLUGIN_PATH . 'includes/roles.php';
require_once WOM_PLUGIN_PATH . 'includes/settings.php';
require_once WOM_PLUGIN_PATH . 'includes/tracking.php';
// Ensure WooCommerce is active
add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		// WooCommerce not active. Features relying on WC should guard themselves.
	}
	load_plugin_textdomain( 'woocommerce-orders-map', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );
// Enqueue scripts
add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_script( 'wom-admin-map', WOM_PLUGIN_URL . 'assets/admin-map.js', [], WOM_PLUGIN_VERSION, true );
	wp_enqueue_script( 'wom-admin-map-google', WOM_PLUGIN_URL . 'assets/admin-map-google.js', [], WOM_PLUGIN_VERSION, true );
} );
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'wom-driver-dashboard', WOM_PLUGIN_URL . 'assets/driver-dashboard.js', [], WOM_PLUGIN_VERSION, true );
} );
// Activation/Deactivation hooks for cron schedules
register_activation_hook( WOM_PLUGIN_FILE, function () {
	if ( ! wp_next_scheduled( 'wom_geocode_backfill_event' ) ) {
		wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'hourly', 'wom_geocode_backfill_event' );
	}
} );
register_deactivation_hook( WOM_PLUGIN_FILE, function () {
	wp_clear_scheduled_hook( 'wom_geocode_backfill_event' );
} );
// No closing PHP tag to avoid accidental output