<?php if (!defined('ABSPATH')) exit; ?>

<div class="cdn-provider-settings-content">
    <div class="settings-group">
        <label for="keycdn_api_key"><?php _e('API Key', 'wp-performance-plus'); ?></label>
        <input type="password" id="keycdn_api_key" name="wp_performance_plus_settings[keycdn_api_key]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['keycdn_api_key'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your KeyCDN API key. You can find this in your KeyCDN account settings.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="keycdn_zone_id"><?php _e('Zone ID', 'wp-performance-plus'); ?></label>
        <input type="text" id="keycdn_zone_id" name="wp_performance_plus_settings[keycdn_zone_id]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['keycdn_zone_id'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your KeyCDN Zone ID for this domain.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="keycdn_zone_url"><?php _e('Zone URL', 'wp-performance-plus'); ?></label>
        <input type="url" id="keycdn_zone_url" name="wp_performance_plus_settings[keycdn_zone_url]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['keycdn_zone_url'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your KeyCDN Zone URL (e.g., cdn-domain.kxcdn.com).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Zone Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[keycdn_force_ssl]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['keycdn_force_ssl'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Force SSL', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Force HTTPS for all CDN requests.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[keycdn_image_optimization]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['keycdn_image_optimization'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Image Optimization', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable KeyCDN\'s image optimization feature.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Cache Settings', 'wp-performance-plus'); ?></h4>
        
        <label for="keycdn_cache_ttl"><?php _e('Cache TTL (seconds)', 'wp-performance-plus'); ?></label>
        <input type="number" id="keycdn_cache_ttl" name="wp_performance_plus_settings[keycdn_cache_ttl]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['keycdn_cache_ttl'] ?? '86400'); ?>" 
               min="0" max="2592000" class="regular-text">
        <p class="description"><?php _e('Time To Live for cached content (default: 86400 seconds / 1 day).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Advanced Settings', 'wp-performance-plus'); ?></h4>
        
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[keycdn_gzip]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['keycdn_gzip'] ?? true); ?>>
            <span class="slider"></span>
            <?php _e('Enable GZIP Compression', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable GZIP compression for faster content delivery.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[keycdn_webp]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['keycdn_webp'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('WebP Support', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Serve WebP images to supported browsers.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[keycdn_cors]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['keycdn_cors'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Enable CORS', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable Cross-Origin Resource Sharing.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Custom Headers', 'wp-performance-plus'); ?></h4>
        <textarea name="wp_performance_plus_settings[keycdn_custom_headers]" rows="4" class="large-text code" 
                  placeholder="<?php esc_attr_e('X-Custom-Header: value', 'wp-performance-plus'); ?>"
        ><?php echo esc_textarea(get_option('wp_performance_plus_settings')['keycdn_custom_headers'] ?? ''); ?></textarea>
        <p class="description"><?php _e('Add custom response headers (one per line, format: Header: Value).', 'wp-performance-plus'); ?></p>
    </div>
</div> 