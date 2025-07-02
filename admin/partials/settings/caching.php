<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-performance-plus-settings-content">
    
    <!-- Caching Features Cards -->
    <div class="settings-cards-grid">
        
        <!-- Page Cache Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Page Cache', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Cache entire pages to serve static content to visitors', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[page_cache]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['page_cache'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Browser Cache Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-networking"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Browser Cache', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Set browser cache headers to store static content locally', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[browser_cache]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['browser_cache'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Object Cache Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-database"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Object Cache', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Cache database queries and objects to reduce server load', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[object_cache]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['object_cache'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Cache for Logged-in Users Card -->
        <div class="settings-card warning-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Cache for Logged-in Users', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Enable caching for logged-in users (not recommended for dynamic content)', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[cache_logged_in]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['cache_logged_in'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Cache Settings -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Advanced Cache Settings', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Cache Lifetime Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="cache_lifetime" class="settings-field-label">
                            <span class="dashicons dashicons-clock"></span>
                            <?php _e('Cache Lifetime', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <input type="number" id="cache_lifetime" name="wp_performance_plus_settings[cache_lifetime]" 
                                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['cache_lifetime'] ?? '24'); ?>" 
                                   min="1" max="720" class="settings-number-input">
                            <span class="settings-field-unit"><?php _e('hours', 'wp-performance-plus'); ?></span>
                        </div>
                        <p class="settings-field-description"><?php _e('How long should cached content be stored before regenerating', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Cache Exclusions Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="cache_exclusions" class="settings-field-label">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php _e('Cache Exclusions', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <textarea id="cache_exclusions" name="wp_performance_plus_settings[cache_exclusions]" 
                                      rows="4" class="settings-textarea" 
                                      placeholder="<?php esc_attr_e('/cart/*&#10;/checkout/*&#10;/my-account/*', 'wp-performance-plus'); ?>"
                            ><?php echo esc_textarea(get_option('wp_performance_plus_settings')['cache_exclusions'] ?? ''); ?></textarea>
                        </div>
                        <p class="settings-field-description"><?php _e('Enter URLs or patterns to exclude from caching (one per line)', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Actions -->
    <div class="settings-section-actions">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Cache Management', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-actions-grid">
            <button type="button" class="settings-action-btn primary" id="clear_cache">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Clear All Cache', 'wp-performance-plus'); ?>
                <small><?php _e('Clear all cached content and force regeneration', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="preload_cache">
                <span class="dashicons dashicons-performance"></span>
                <?php _e('Preload Cache', 'wp-performance-plus'); ?>
                <small><?php _e('Generate cache for all important pages', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="cache_statistics">
                <span class="dashicons dashicons-chart-line"></span>
                <?php _e('View Statistics', 'wp-performance-plus'); ?>
                <small><?php _e('Show cache performance statistics', 'wp-performance-plus'); ?></small>
            </button>
        </div>
    </div>

</div> 