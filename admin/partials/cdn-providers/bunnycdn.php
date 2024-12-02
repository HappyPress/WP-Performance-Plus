<?php if (!defined('ABSPATH')) exit; ?>

<div class="cdn-provider-settings-content">
    <div class="settings-group">
        <label for="bunnycdn_api_key"><?php _e('API Key', 'wp-performance-plus'); ?></label>
        <input type="password" id="bunnycdn_api_key" name="wp_performance_plus_settings[bunnycdn_api_key]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['bunnycdn_api_key'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your BunnyCDN API key. You can find this in your BunnyCDN account settings.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="bunnycdn_pull_zone"><?php _e('Pull Zone Name', 'wp-performance-plus'); ?></label>
        <input type="text" id="bunnycdn_pull_zone" name="wp_performance_plus_settings[bunnycdn_pull_zone]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['bunnycdn_pull_zone'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your BunnyCDN Pull Zone name.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="bunnycdn_hostname"><?php _e('Hostname', 'wp-performance-plus'); ?></label>
        <input type="text" id="bunnycdn_hostname" name="wp_performance_plus_settings[bunnycdn_hostname]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['bunnycdn_hostname'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your BunnyCDN hostname (e.g., cdn.yourdomain.com).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Pull Zone Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_force_ssl]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_force_ssl'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Force SSL', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Force HTTPS for all CDN requests.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_optimize_images]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_optimize_images'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Image Optimization', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable BunnyCDN\'s image optimization feature.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Cache Settings', 'wp-performance-plus'); ?></h4>
        
        <label for="bunnycdn_cache_ttl"><?php _e('Cache TTL (minutes)', 'wp-performance-plus'); ?></label>
        <input type="number" id="bunnycdn_cache_ttl" name="wp_performance_plus_settings[bunnycdn_cache_ttl]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['bunnycdn_cache_ttl'] ?? '1440'); ?>" 
               min="1" max="525600" class="regular-text">
        <p class="description"><?php _e('Time To Live for cached content (default: 1440 minutes / 1 day).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Advanced Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_gzip]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_gzip'] ?? true); ?>>
            <span class="slider"></span>
            <?php _e('Enable GZIP Compression', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable GZIP compression for faster content delivery.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_webp]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_webp'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('WebP Support', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Serve WebP images to supported browsers.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_error_page]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_error_page'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Custom Error Pages', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable custom error pages for CDN errors.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Security Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_block_bad_bots]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_block_bad_bots'] ?? true); ?>>
            <span class="slider"></span>
            <?php _e('Block Bad Bots', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Block known bad bots and crawlers.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[bunnycdn_token_auth]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['bunnycdn_token_auth'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Token Authentication', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable token authentication for secure content delivery.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Blocked IPs', 'wp-performance-plus'); ?></h4>
        <textarea name="wp_performance_plus_settings[bunnycdn_blocked_ips]" rows="4" class="large-text code" 
                  placeholder="<?php esc_attr_e('Enter IP addresses to block, one per line', 'wp-performance-plus'); ?>"
        ><?php echo esc_textarea(get_option('wp_performance_plus_settings')['bunnycdn_blocked_ips'] ?? ''); ?></textarea>
        <p class="description"><?php _e('Block specific IP addresses from accessing your content (one per line).', 'wp-performance-plus'); ?></p>
    </div>
</div> 