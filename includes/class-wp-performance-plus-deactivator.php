<?php
/**
 * Fired during plugin deactivation.
 */
class WP_Performance_Plus_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Remove scheduled events
        wp_clear_scheduled_hook('wp_performance_plus_database_cleanup');
        
        // Clean up cache directory
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/wp-performance-plus-cache';
        if (file_exists($cache_dir)) {
            self::delete_directory($cache_dir);
        }
        
        // Remove transients
        delete_transient('wp_performance_plus_cache_stats');
        delete_transient('wp_performance_plus_optimization_running');
        
        // Optionally, you can keep the settings by commenting out these lines
        // delete_option('wp_performance_plus_settings');
        // delete_option('wp_performance_plus_do_activation_redirect');
    }
    
    /**
     * Helper function to recursively delete a directory
     */
    private static function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
}
?>
