<?php if (!defined('ABSPATH')) exit; ?>

<div class="cdn-provider-settings-content">
    <div class="settings-group">
        <label for="cloudflare_email"><?php _e('Email Address', 'wp-performance-plus'); ?></label>
        <input type="email" id="cloudflare_email" name="wp_performance_plus_settings[cloudflare_email]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudflare_email'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your Cloudflare account email address.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cloudflare_api_key"><?php _e('API Key', 'wp-performance-plus'); ?></label>
        <input type="password" id="cloudflare_api_key" name="wp_performance_plus_settings[cloudflare_api_key]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudflare_api_key'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your Cloudflare API key. You can find this in your Cloudflare account settings.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cloudflare_zone_id"><?php _e('Zone ID', 'wp-performance-plus'); ?></label>
        <input type="text" id="cloudflare_zone_id" name="wp_performance_plus_settings[cloudflare_zone_id]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cloudflare_zone_id'] ?? ''); ?>" 
               class="regular-text">
        <p class="description"><?php _e('Your Cloudflare Zone ID for this domain.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Development Mode', 'wp-performance-plus'); ?></h4>
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudflare_dev_mode]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudflare_dev_mode'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Enable Development Mode', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Temporarily bypass Cloudflare\'s cache. Automatically turns off after 3 hours.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Cache Level', 'wp-performance-plus'); ?></h4>
        <select name="wp_performance_plus_settings[cloudflare_cache_level]" class="regular-text">
            <option value="aggressive" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_cache_level'] ?? '', 'aggressive'); ?>>
                <?php _e('Aggressive', 'wp-performance-plus'); ?>
            </option>
            <option value="standard" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_cache_level'] ?? '', 'standard'); ?>>
                <?php _e('Standard', 'wp-performance-plus'); ?>
            </option>
            <option value="basic" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_cache_level'] ?? '', 'basic'); ?>>
                <?php _e('Basic', 'wp-performance-plus'); ?>
            </option>
        </select>
        <p class="description"><?php _e('Determine how much of your website\'s content you want Cloudflare to cache.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Browser Cache TTL', 'wp-performance-plus'); ?></h4>
        <select name="wp_performance_plus_settings[cloudflare_browser_cache_ttl]" class="regular-text">
            <option value="14400" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '14400'); ?>>
                <?php _e('4 hours', 'wp-performance-plus'); ?>
            </option>
            <option value="28800" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '28800'); ?>>
                <?php _e('8 hours', 'wp-performance-plus'); ?>
            </option>
            <option value="43200" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '43200'); ?>>
                <?php _e('12 hours', 'wp-performance-plus'); ?>
            </option>
            <option value="86400" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '86400'); ?>>
                <?php _e('1 day', 'wp-performance-plus'); ?>
            </option>
            <option value="172800" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '172800'); ?>>
                <?php _e('2 days', 'wp-performance-plus'); ?>
            </option>
            <option value="604800" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '604800'); ?>>
                <?php _e('1 week', 'wp-performance-plus'); ?>
            </option>
            <option value="2592000" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_browser_cache_ttl'] ?? '', '2592000'); ?>>
                <?php _e('1 month', 'wp-performance-plus'); ?>
            </option>
        </select>
        <p class="description"><?php _e('Set the browser cache TTL (Time To Live) for your content.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Security Level', 'wp-performance-plus'); ?></h4>
        <select name="wp_performance_plus_settings[cloudflare_security_level]" class="regular-text">
            <option value="essentially_off" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_security_level'] ?? '', 'essentially_off'); ?>>
                <?php _e('Essentially Off', 'wp-performance-plus'); ?>
            </option>
            <option value="low" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_security_level'] ?? '', 'low'); ?>>
                <?php _e('Low', 'wp-performance-plus'); ?>
            </option>
            <option value="medium" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_security_level'] ?? '', 'medium'); ?>>
                <?php _e('Medium', 'wp-performance-plus'); ?>
            </option>
            <option value="high" <?php selected(get_option('wp_performance_plus_settings')['cloudflare_security_level'] ?? '', 'high'); ?>>
                <?php _e('High', 'wp-performance-plus'); ?>
            </option>
        </select>
        <p class="description"><?php _e('Set the security level for your domain.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Additional Features', 'wp-performance-plus'); ?></h4>
        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudflare_always_online]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudflare_always_online'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Always Online', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Keep your website available during server downtime.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudflare_auto_minify]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudflare_auto_minify'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Auto Minify', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Automatically minify CSS, JavaScript, and HTML files.', 'wp-performance-plus'); ?></p>

        <label class="toggle-switch">
            <input type="checkbox" name="wp_performance_plus_settings[cloudflare_rocket_loader]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cloudflare_rocket_loader'] ?? false); ?>>
            <span class="slider"></span>
            <?php _e('Rocket Loader', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Improve the loading of JavaScript resources.', 'wp-performance-plus'); ?></p>
    </div>
</div> 