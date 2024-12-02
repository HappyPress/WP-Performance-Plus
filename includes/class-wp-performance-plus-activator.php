<?php

/**
 * Fired during plugin activation
 */
class WP_Performance_Plus_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Create necessary database tables
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create optimization log table
        $table_name = $wpdb->prefix . 'performance_plus_logs';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $default_options = array(
            'enable_minification' => true,
            'combine_files' => true,
            'lazy_loading' => true,
            'cdn_enabled' => false,
            'cdn_provider' => '',
            'cdn_url' => '',
            'cdn_key' => '',
            'optimization_level' => 'balanced'
        );
        
        add_option('wp_performance_plus_settings', $default_options);
        
        // Create cache directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/wp-performance-plus-cache';
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        
        // Add activation flag
        add_option('wp_performance_plus_do_activation_redirect', true);
    }
}
?>
