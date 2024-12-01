<?php
/**
 * Class PerformancePlus_Activator
 * Handles plugin activation tasks such as setting default options.
 */

class PerformancePlus_Activator {
    /**
     * Triggered when the plugin is activated.
     * Sets up default options for the plugin.
     */
    public static function activate() {
        // Set default options for local optimizations
        if (!get_option('performanceplus_settings')) {
            update_option('performanceplus_settings', [
                'enable_cdn' => false, // CDN integration disabled by default
                'enable_local_minification' => true, // Enable local HTML/CSS/JS minification
                'enable_database_cleanup' => true, // Enable database cleanup tasks
                'cleanup_schedule' => 'weekly' // Default cleanup schedule
            ]);
        }

        // Add a welcome screen indicator for first-time activation
        if (!get_option('performanceplus_welcome_screen')) {
            update_option('performanceplus_welcome_screen', true);
        }

        // Schedule a cleanup cron job if it does not exist
        if (!wp_next_scheduled('performanceplus_database_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'performanceplus_database_cleanup');
        }
    }
}
?>
