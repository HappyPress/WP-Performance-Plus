<?php
/**
 * Plugin Name: WP Performance Plus
 * Plugin URI: https://example.com/performanceplus
 * Description: Advanced performance optimization toolkit for WordPress
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: performanceplus
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('PERFORMANCEPLUS_VERSION', '1.0.0');
define('PERFORMANCEPLUS_FILE', __FILE__);
define('PERFORMANCEPLUS_PATH', plugin_dir_path(__FILE__));
define('PERFORMANCEPLUS_URL', plugin_dir_url(__FILE__));

/**
 * Initialize the plugin
 */
function performanceplus_init() {
    require_once PERFORMANCEPLUS_PATH . 'includes/class-performanceplus-loader.php';
    PerformancePlus_Loader::get_instance();
}

// Hook into WordPress init
add_action('init', 'performanceplus_init');

// Activation hook
register_activation_hook(__FILE__, 'performanceplus_activate');

/**
 * Plugin activation callback
 */
function performanceplus_activate() {
    // Set flag for welcome screen
    add_option('performanceplus_welcome_screen', true);
    
    // Create necessary database tables and options
    add_option('performanceplus_debug_mode', false);
    
    // Clear any existing debug logs
    $debug_log = WP_CONTENT_DIR . '/performanceplus-debug.log';
    if (file_exists($debug_log)) {
        unlink($debug_log);
    }
    touch($debug_log);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'performanceplus_deactivate');

/**
 * Plugin deactivation callback
 */
function performanceplus_deactivate() {
    // Clean up plugin options if needed
    // delete_option('performanceplus_debug_mode');
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'performanceplus_uninstall');

/**
 * Plugin uninstall callback
 */
function performanceplus_uninstall() {
    // Remove all plugin options and data
    delete_option('performanceplus_welcome_screen');
    delete_option('performanceplus_debug_mode');
    delete_option('performanceplus_onboarding_complete');
    
    // Remove debug log
    $debug_log = WP_CONTENT_DIR . '/performanceplus-debug.log';
    if (file_exists($debug_log)) {
        unlink($debug_log);
    }
}
?>
