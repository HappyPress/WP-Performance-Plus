<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-performance-plus-settings-content">
    
    <!-- CDN Provider Selection -->
    <div class="settings-section-header">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-networking"></span>
            <?php _e('CDN Provider Configuration', 'wp-performance-plus'); ?>
        </h2>
        <p class="settings-section-description">
            <?php _e('Configure your CDN provider settings. Basic CDN selection is available on the main dashboard.', 'wp-performance-plus'); ?>
        </p>
    </div>

    <!-- Provider Selection Card -->
    <div class="settings-card settings-card-wide cdn-provider-card">
        <div class="settings-card-body">
            <div class="settings-field-group">
                <label for="cdn_provider" class="settings-field-label">
                    <span class="dashicons dashicons-cloud"></span>
                    <?php _e('Selected CDN Provider', 'wp-performance-plus'); ?>
                </label>
                <div class="settings-field-input">
                    <select id="cdn_provider" name="wp_performance_plus_settings[cdn_provider]" class="settings-select">
                        <option value=""><?php _e('None - Configure on Dashboard', 'wp-performance-plus'); ?></option>
                        <option value="cloudflare" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'cloudflare'); ?>>
                            <?php _e('Cloudflare', 'wp-performance-plus'); ?>
                        </option>
                        <option value="keycdn" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'keycdn'); ?>>
                            <?php _e('KeyCDN', 'wp-performance-plus'); ?>
                        </option>
                        <option value="bunnycdn" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'bunnycdn'); ?>>
                            <?php _e('BunnyCDN', 'wp-performance-plus'); ?>
                        </option>
                        <option value="cloudfront" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'cloudfront'); ?>>
                            <?php _e('Amazon CloudFront', 'wp-performance-plus'); ?>
                        </option>
                    </select>
                </div>
                <p class="settings-field-description"><?php _e('Select your CDN provider to show advanced configuration options below', 'wp-performance-plus'); ?></p>
            </div>
        </div>
    </div>

    <!-- CDN Provider Specific Settings -->
    <div class="cdn-settings-wrapper">
        <!-- Provider settings will be loaded dynamically -->
        <div id="cdn-provider-placeholder" class="settings-card settings-card-wide" style="text-align: center; padding: 40px;">
            <div class="settings-placeholder">
                <span class="dashicons dashicons-cloud" style="font-size: 48px; color: #ccc; margin-bottom: 15px; display: block;"></span>
                <h3><?php _e('Select a CDN Provider', 'wp-performance-plus'); ?></h3>
                <p><?php _e('Choose a CDN provider above to configure advanced settings.', 'wp-performance-plus'); ?></p>
            </div>
        </div>
    </div>

    <!-- General CDN Features -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('CDN Features', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- SSL/HTTPS Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Force HTTPS', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Use HTTPS for all CDN URLs', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cdn_enable_ssl]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cdn_enable_ssl'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Relative Path Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-admin-links"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Relative URLs', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Use relative URLs for CDN resources', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cdn_relative_path]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cdn_relative_path'] ?? false); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Types Configuration -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-media-default"></span>
            <?php _e('CDN File Types', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Images Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-format-image"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Images', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('jpg, jpeg, png, gif, webp', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cdn_images]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cdn_images'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- JavaScript Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-media-code"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('JavaScript', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('js, javascript files', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cdn_js]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cdn_js'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- CSS Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('CSS Files', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('css, stylesheet files', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cdn_css]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cdn_css'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Fonts Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-editor-textcolor"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Font Files', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('woff, woff2, ttf, eot, otf', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cdn_fonts]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cdn_fonts'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CDN Exclusions -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-dismiss"></span>
            <?php _e('CDN Exclusions', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-card settings-card-wide">
            <div class="settings-card-body">
                <div class="settings-field-group">
                    <label for="cdn_exclusions" class="settings-field-label">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php _e('Exclude Files & Paths', 'wp-performance-plus'); ?>
                    </label>
                    <div class="settings-field-input">
                        <textarea id="cdn_exclusions" name="wp_performance_plus_settings[cdn_exclusions]" 
                                  rows="4" class="settings-textarea" 
                                  placeholder="<?php esc_attr_e('.php&#10;.html&#10;admin/&#10;wp-includes/&#10;wp-content/plugins/specific-plugin/', 'wp-performance-plus'); ?>"
                        ><?php echo esc_textarea(get_option('wp_performance_plus_settings')['cdn_exclusions'] ?? ''); ?></textarea>
                    </div>
                    <p class="settings-field-description"><?php _e('Enter file extensions, paths, or patterns to exclude from CDN (one per line)', 'wp-performance-plus'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- CDN Actions -->
    <div class="settings-section-actions">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('CDN Management', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-actions-grid">
            <button type="button" class="settings-action-btn primary" id="test_cdn">
                <span class="dashicons dashicons-networking"></span>
                <?php _e('Test CDN Connection', 'wp-performance-plus'); ?>
                <small><?php _e('Verify CDN configuration and connectivity', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="purge_cdn">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Purge CDN Cache', 'wp-performance-plus'); ?>
                <small><?php _e('Clear all cached content from CDN', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="cdn_analytics">
                <span class="dashicons dashicons-chart-line"></span>
                <?php _e('CDN Analytics', 'wp-performance-plus'); ?>
                <small><?php _e('View CDN usage and performance statistics', 'wp-performance-plus'); ?></small>
            </button>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    function toggleCDNSettings() {
        var provider = $('#cdn_provider').val();
        var $placeholder = $('#cdn-provider-placeholder');
        var $wrapper = $('.cdn-settings-wrapper');
        
        if (provider && provider !== '') {
            $placeholder.hide();
            // Show provider-specific settings (these would be loaded via AJAX or included)
            // For now, just hide the placeholder
        } else {
            $placeholder.show();
        }
    }

    $('#cdn_provider').on('change', toggleCDNSettings);
    toggleCDNSettings(); // Initialize on load
});
</script> 