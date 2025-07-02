<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
$option_names = [
    'wp_performanceplus_settings',
    'wp_performanceplus_cloudflare_settings',
    'wp_performanceplus_keycdn_settings',
    'wp_performanceplus_bunnycdn_settings',
    'wp_performanceplus_cloudfront_settings',
    'wp_performanceplus_local_settings'
];

foreach ($option_names as $option) {
    delete_option($option);
}

// Clear scheduled events
wp_clear_scheduled_hook('wp_performanceplus_database_cleanup'); 