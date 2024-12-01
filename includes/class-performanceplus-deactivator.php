<?php
/**
 * Class PerformancePlus_Deactivator
 * Handles plugin deactivation tasks such as unscheduling tasks and cleanup.
 */

class PerformancePlus_Deactivator {
    /**
     * Triggered when the plugin is deactivated.
     * Unschedules tasks and performs necessary cleanup.
     */
    public static function deactivate() {
        // Clear the scheduled database cleanup task
        if (wp_next_scheduled('performanceplus_database_cleanup')) {
            wp_clear_scheduled_hook('performanceplus_database_cleanup');
        }

        // Optional: Remove temporary or transient data if needed
        // Uncomment the following lines if you wish to clean specific options:
        // delete_option('performanceplus_settings');
        // delete_option('performanceplus_welcome_screen');
    }
}
?>
