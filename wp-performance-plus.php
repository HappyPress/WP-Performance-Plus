<?php
/**
 * Plugin Name: WP Performance Plus
 * Plugin URI: https://example.com/wp-performance-plus
 * Description: A comprehensive WordPress performance optimization plugin.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-performance-plus
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WP_PERFORMANCE_PLUS_VERSION', '1.0.0');
define('WP_PERFORMANCE_PLUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_PERFORMANCE_PLUS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wp_performance_plus() {
    require_once WP_PERFORMANCE_PLUS_PLUGIN_DIR . 'includes/class-wp-performance-plus-activator.php';
    WP_Performance_Plus_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_performance_plus() {
    require_once WP_PERFORMANCE_PLUS_PLUGIN_DIR . 'includes/class-wp-performance-plus-deactivator.php';
    WP_Performance_Plus_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_performance_plus');
register_deactivation_hook(__FILE__, 'deactivate_wp_performance_plus');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once WP_PERFORMANCE_PLUS_PLUGIN_DIR . 'includes/class-wp-performance-plus.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_performance_plus() {
    $plugin = new WP_Performance_Plus();
    $plugin->run();
}

// Initialize the plugin
run_wp_performance_plus();
?>
