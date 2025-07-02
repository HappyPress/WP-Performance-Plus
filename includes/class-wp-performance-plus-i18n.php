<?php
/**
 * Class WPPerformancePlus_I18n
 * Handles internationalization for the plugin.
 */

class WPPerformancePlus_I18n {
    /**
     * Load the plugin's text domain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp_performanceplus', // Text domain
            false, // Deprecated, but must be set to false
            dirname(plugin_basename(PERFORMANCEPLUS_PATH)) . '/languages/' // Path to language files
        );
    }
}
?>
