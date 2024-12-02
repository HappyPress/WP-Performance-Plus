<?php if (!defined('ABSPATH')) exit; ?>

<div class="cdn-provider-settings-content">
    <div class="settings-group">
        <label for="cloudfront_access_key"><?php _e('AWS Access Key ID', 'wp-performance-plus'); ?></label>
        <input type="text" id="cloudfront_access_key" name="wp_performance_plus_settings[cloudfront_access_key]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_access_key'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your AWS Access Key ID.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cloudfront_secret_key"><?php _e('AWS Secret Access Key', 'wp-performance-plus'); ?></label>
        <input type="password" id="cloudfront_secret_key" name="wp_performance_plus_settings[cloudfront_secret_key]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_secret_key'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your AWS Secret Access Key.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cloudfront_distribution_id"><?php _e('Distribution ID', 'wp-performance-plus'); ?></label>
        <input type="text" id="cloudfront_distribution_id" name="wp_performance_plus_settings[cloudfront_distribution_id]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_distribution_id'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your CloudFront Distribution ID.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cloudfront_domain"><?php _e('Distribution Domain Name', 'wp-performance-plus'); ?></label>
        <input type="text" id="cloudfront_domain" name="wp_performance_plus_settings[cloudfront_domain]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_domain'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your CloudFront Distribution Domain Name (e.g., d1234.cloudfront.net).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Distribution Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_ssl]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_ssl'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Use HTTPS Only', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Force HTTPS for all CloudFront requests.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Cache Settings', 'wp-performance-plus'); ?></h4>
        
        <label for="cloudfront_default_ttl"><?php _e('Default TTL (seconds)', 'wp-performance-plus'); ?></label>
        <input type="number" id="cloudfront_default_ttl" name="wp_performance_plus_settings[cloudfront_default_ttl]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_default_ttl'] ?? '86400'); ?>" 
               min="0" max="31536000" class="regular-text">
        <p class="description"><?php _e('Default Time To Live for cached content (default: 86400 seconds / 1 day).', 'wp-performance-plus'); ?></p>

        <label for="cloudfront_max_ttl"><?php _e('Maximum TTL (seconds)', 'wp-performance-plus'); ?></label>
        <input type="number" id="cloudfront_max_ttl" name="wp_performance_plus_settings[cloudfront_max_ttl]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_max_ttl'] ?? '31536000'); ?>" 
               min="0" max="31536000" class="regular-text">
        <p class="description"><?php _e('Maximum Time To Live for cached content (default: 31536000 seconds / 1 year).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Advanced Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_gzip]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_gzip'] ?? true); ?>>
            <span class="slider"></span>
            <?php _e('Enable Compression', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable compression for faster content delivery.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_query_string]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_query_string'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Forward Query Strings', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Forward query string parameters to the origin.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_cookies]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_cookies'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Forward Cookies', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Forward cookies to the origin.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Security Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_waf]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_waf'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Enable WAF', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable AWS WAF (Web Application Firewall) protection.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_signed_urls]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_signed_urls'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Use Signed URLs', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable signed URLs for secure content delivery.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Invalidation Settings', 'wp-performance-plus'); ?></h4>
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudfront_auto_invalidation]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudfront_auto_invalidation'] ?? true); ?>>
            <span class="slider"></span>
            <?php _e('Automatic Invalidation', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Automatically invalidate cached content when content is updated.', 'wp-performance-plus'); ?></p>

        <label for="cloudfront_max_invalidations"><?php _e('Maximum Monthly Invalidations', 'wp-performance-plus'); ?></label>
        <input type="number" id="cloudfront_max_invalidations" name="wp_performance_plus_settings[cloudfront_max_invalidations]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudfront_max_invalidations'] ?? '1000'); ?>" 
               min="0" max="100000" class="small-text">
        <p class="description"><?php _e('Maximum number of invalidation requests per month (AWS free tier includes 1000).', 'wp-performance-plus'); ?></p>
    </div>
</div> 