<?php if (!defined('ABSPATH')) exit; ?>

<div class="settings-section">
    <h3><?php _e('Caching Settings', 'wp-performance-plus'); ?></h3>
    
    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[page_cache]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['page_cache'] ?? false); ?>>
            <?php _e('Enable Page Cache', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Cache entire pages to serve static content to visitors.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[browser_cache]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['browser_cache'] ?? false); ?>>
            <?php _e('Enable Browser Cache', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Set browser cache headers to store static content locally.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[object_cache]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['object_cache'] ?? false); ?>>
            <?php _e('Enable Object Cache', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Cache database queries and objects to reduce server load.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cache_lifetime"><?php _e('Cache Lifetime (hours)', 'wp-performance-plus'); ?></label>
        <input type="number" id="cache_lifetime" name="wp_performance_plus_settings[cache_lifetime]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cache_lifetime'] ?? '24'); ?>" 
               min="1" max="720" class="small-text">
        <p class="description"><?php _e('How long should cached content be stored before regenerating.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cache_logged_in]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cache_logged_in'] ?? false); ?>>
            <?php _e('Cache for Logged-in Users', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Enable caching for logged-in users (not recommended for dynamic content).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Cache Exclusions', 'wp-performance-plus'); ?></h4>
        <textarea name="wp_performance_plus_settings[cache_exclusions]" rows="4" class="large-text code" 
                  placeholder="<?php esc_attr_e('/cart/*, /checkout/*, /my-account/*', 'wp-performance-plus'); ?>"
        ><?php echo esc_textarea(get_option('wp_performance_plus_settings')['cache_exclusions'] ?? ''); ?></textarea>
        <p class="description"><?php _e('Enter URLs or patterns to exclude from caching (one per line).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <button type="button" class="button button-secondary" id="clear_cache">
            <?php _e('Clear All Cache', 'wp-performance-plus'); ?>
        </button>
        <p class="description"><?php _e('Clear all cached content and force regeneration.', 'wp-performance-plus'); ?></p>
    </div>
</div> 