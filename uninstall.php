<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
$option_names = [
    'performanceplus_settings',
    'performanceplus_cloudflare_settings',
    'performanceplus_stackpath_settings',
    'performanceplus_keycdn_settings',
    'performanceplus_bunnycdn_settings',
    'performanceplus_cloudfront_settings',
    'performanceplus_local_settings'
];

foreach ($option_names as $option) {
    delete_option($option);
}

// Clear scheduled events
wp_clear_scheduled_hook('performanceplus_database_cleanup'); 