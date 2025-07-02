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
    <div class="settings-card settings-card-wide">
        <div class="settings-card-header">
            <div class="settings-card-icon">
                <span class="dashicons dashicons-cloud"></span>
            </div>
            <div class="settings-card-title">
                <h3><?php _e('CDN Provider', 'wp-performance-plus'); ?></h3>
                <p class="settings-card-description"><?php _e('Select your CDN provider to configure advanced settings', 'wp-performance-plus'); ?></p>
            </div>
        </div>
        <div class="settings-card-body">
            <div class="settings-field-group">
                <label for="cdn_provider" class="settings-field-label"><?php _e('Provider', 'wp-performance-plus'); ?></label>
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

    <!-- CDN Provider Specific Settings Container -->
    <div id="cdn-provider-settings" class="cdn-settings-wrapper">
        <!-- Provider settings will be loaded dynamically -->
        <div id="cdn-provider-placeholder" class="settings-card settings-card-wide" style="text-align: center; padding: 40px;">
            <div class="settings-placeholder">
                <span class="dashicons dashicons-cloud" style="font-size: 48px; color: #ccc; margin-bottom: 15px; display: block;"></span>
                <h3><?php _e('Select a CDN Provider', 'wp-performance-plus'); ?></h3>
                <p><?php _e('Choose a CDN provider above to configure advanced settings.', 'wp-performance-plus'); ?></p>
            </div>
        </div>
    </div>

    <!-- CDN Management Actions -->
    <div id="cdn-management-actions" class="settings-section-advanced" style="display: none;">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('CDN Management', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            <!-- Test Connection Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-admin-plugins"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Test Connection', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Verify your CDN configuration', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="settings-card-actions">
                        <button type="button" id="test-cdn-connection" class="settings-button settings-button-primary">
                            <span class="dashicons dashicons-admin-plugins"></span>
                            <?php _e('Test Connection', 'wp-performance-plus'); ?>
                        </button>
                    </div>
                    <div id="connection-test-result" class="settings-status-message" style="display: none;"></div>
                </div>
            </div>

            <!-- Purge Cache Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-trash"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Purge Cache', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Clear all cached content from CDN', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="settings-card-actions">
                        <button type="button" id="purge-cdn-cache" class="settings-button settings-button-destructive">
                            <span class="dashicons dashicons-trash"></span>
                            <?php _e('Purge All Cache', 'wp-performance-plus'); ?>
                        </button>
                    </div>
                    <div id="cache-purge-result" class="settings-status-message" style="display: none;"></div>
                </div>
            </div>

            <!-- CDN Statistics Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-chart-bar"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('CDN Statistics', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('View usage and performance metrics', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div id="cdn-statistics" class="cdn-stats-container">
                        <div class="cdn-stat-item">
                            <span class="cdn-stat-label"><?php _e('Requests (24h)', 'wp-performance-plus'); ?></span>
                            <span class="cdn-stat-value" id="stat-requests">-</span>
                        </div>
                        <div class="cdn-stat-item">
                            <span class="cdn-stat-label"><?php _e('Bandwidth (24h)', 'wp-performance-plus'); ?></span>
                            <span class="cdn-stat-value" id="stat-bandwidth">-</span>
                        </div>
                        <div class="cdn-stat-item">
                            <span class="cdn-stat-label"><?php _e('Cache Hit Ratio', 'wp-performance-plus'); ?></span>
                            <span class="cdn-stat-value" id="stat-cache-ratio">-</span>
                        </div>
                    </div>
                    <div class="settings-card-actions">
                        <button type="button" id="refresh-cdn-stats" class="settings-button settings-button-secondary">
                            <span class="dashicons dashicons-update"></span>
                            <?php _e('Refresh Stats', 'wp-performance-plus'); ?>
                        </button>
                    </div>
                </div>
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
            <!-- File Types Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-media-archive"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('File Types', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Choose which file types to serve via CDN', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="settings-toggle-group">
                        <div class="settings-toggle-item">
                            <label class="settings-toggle">
                                <input type="checkbox" name="wp_performance_plus_settings[cdn_images]" value="1" <?php checked(get_option('wp_performance_plus_settings')['cdn_images'] ?? false); ?>>
                                <span class="settings-toggle-switch"></span>
                            </label>
                            <div class="settings-toggle-label">
                                <span class="settings-toggle-title"><?php _e('Images', 'wp-performance-plus'); ?></span>
                                <span class="settings-toggle-description"><?php _e('JPG, PNG, GIF, WebP, SVG files', 'wp-performance-plus'); ?></span>
                            </div>
                        </div>
                        
                        <div class="settings-toggle-item">
                            <label class="settings-toggle">
                                <input type="checkbox" name="wp_performance_plus_settings[cdn_css]" value="1" <?php checked(get_option('wp_performance_plus_settings')['cdn_css'] ?? false); ?>>
                                <span class="settings-toggle-switch"></span>
                            </label>
                            <div class="settings-toggle-label">
                                <span class="settings-toggle-title"><?php _e('Stylesheets', 'wp-performance-plus'); ?></span>
                                <span class="settings-toggle-description"><?php _e('CSS files', 'wp-performance-plus'); ?></span>
                            </div>
                        </div>
                        
                        <div class="settings-toggle-item">
                            <label class="settings-toggle">
                                <input type="checkbox" name="wp_performance_plus_settings[cdn_js]" value="1" <?php checked(get_option('wp_performance_plus_settings')['cdn_js'] ?? false); ?>>
                                <span class="settings-toggle-switch"></span>
                            </label>
                            <div class="settings-toggle-label">
                                <span class="settings-toggle-title"><?php _e('JavaScript', 'wp-performance-plus'); ?></span>
                                <span class="settings-toggle-description"><?php _e('JS files', 'wp-performance-plus'); ?></span>
                            </div>
                        </div>
                        
                        <div class="settings-toggle-item">
                            <label class="settings-toggle">
                                <input type="checkbox" name="wp_performance_plus_settings[cdn_fonts]" value="1" <?php checked(get_option('wp_performance_plus_settings')['cdn_fonts'] ?? false); ?>>
                                <span class="settings-toggle-switch"></span>
                            </label>
                            <div class="settings-toggle-label">
                                <span class="settings-toggle-title"><?php _e('Fonts', 'wp-performance-plus'); ?></span>
                                <span class="settings-toggle-description"><?php _e('WOFF, WOFF2, TTF, EOT files', 'wp-performance-plus'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- URL Exclusions Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-dismiss"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('URL Exclusions', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('URLs that should not be served via CDN', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="cdn_exclusions" class="settings-field-label"><?php _e('Excluded URLs/Patterns', 'wp-performance-plus'); ?></label>
                        <div class="settings-field-input">
                            <textarea id="cdn_exclusions" name="wp_performance_plus_settings[cdn_exclusions]" class="settings-textarea" rows="4" placeholder="<?php _e('One pattern per line, e.g.:\n/wp-admin/\n/wp-login.php\n.php\nprivate-content', 'wp-performance-plus'); ?>"><?php echo esc_textarea(get_option('wp_performance_plus_settings')['cdn_exclusions'] ?? ''); ?></textarea>
                        </div>
                        <p class="settings-field-description"><?php _e('Enter URL patterns to exclude from CDN (one per line). Use partial matches.', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var nonce = '<?php echo wp_create_nonce('wp_performance_plus_wizard'); ?>';
    
    // Handle CDN provider selection
    $('#cdn_provider').on('change', function() {
        var provider = $(this).val();
        var $placeholder = $('#cdn-provider-placeholder');
        var $settingsContainer = $('#cdn-provider-settings');
        var $managementActions = $('#cdn-management-actions');
        
        if (!provider) {
            $placeholder.show();
            $managementActions.hide();
            return;
        }
        
        $placeholder.hide();
        
        // Load provider-specific settings
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_load_cdn_settings',
                provider: provider,
                nonce: nonce
            },
            beforeSend: function() {
                $settingsContainer.html('<div class="settings-loading"><span class="dashicons dashicons-update-alt"></span> <?php _e('Loading settings...', 'wp-performance-plus'); ?></div>');
            },
            success: function(response) {
                if (response.success) {
                    $settingsContainer.html(response.data.html);
                    $managementActions.show();
                } else {
                    $settingsContainer.html('<div class="settings-error"><?php _e('Error loading settings:', 'wp-performance-plus'); ?> ' + response.data + '</div>');
                }
            },
            error: function() {
                $settingsContainer.html('<div class="settings-error"><?php _e('Failed to load provider settings.', 'wp-performance-plus'); ?></div>');
            }
        });
    });
    
    // Test CDN connection
    $('#test-cdn-connection').on('click', function() {
        var provider = $('#cdn_provider').val();
        var $button = $(this);
        var $result = $('#connection-test-result');
        
        if (!provider) {
            alert('<?php _e('Please select a CDN provider first.', 'wp-performance-plus'); ?>');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_test_cdn',
                provider: provider,
                nonce: nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> <?php _e('Testing...', 'wp-performance-plus'); ?>');
                $result.hide();
            },
            success: function(response) {
                if (response.success) {
                    $result.removeClass('settings-error').addClass('settings-success').html('✓ ' + response.data).show();
                } else {
                    $result.removeClass('settings-success').addClass('settings-error').html('✗ ' + response.data).show();
                }
            },
            error: function() {
                $result.removeClass('settings-success').addClass('settings-error').html('✗ <?php _e('Connection test failed.', 'wp-performance-plus'); ?>').show();
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-plugins"></span> <?php _e('Test Connection', 'wp-performance-plus'); ?>');
            }
        });
    });
    
    // Purge CDN cache
    $('#purge-cdn-cache').on('click', function() {
        var $button = $(this);
        var $result = $('#cache-purge-result');
        
        if (!confirm('<?php _e('Are you sure you want to purge all CDN cache?', 'wp-performance-plus'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_purge_cdn',
                nonce: nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> <?php _e('Purging...', 'wp-performance-plus'); ?>');
                $result.hide();
            },
            success: function(response) {
                if (response.success) {
                    $result.removeClass('settings-error').addClass('settings-success').html('✓ ' + response.data).show();
                } else {
                    $result.removeClass('settings-success').addClass('settings-error').html('✗ ' + response.data).show();
                }
            },
            error: function() {
                $result.removeClass('settings-success').addClass('settings-error').html('✗ <?php _e('Cache purge failed.', 'wp-performance-plus'); ?>').show();
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e('Purge All Cache', 'wp-performance-plus'); ?>');
            }
        });
    });
    
    // Refresh CDN statistics
    $('#refresh-cdn-stats').on('click', function() {
        var $button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_get_cdn_stats',
                nonce: nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> <?php _e('Loading...', 'wp-performance-plus'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    var stats = response.data;
                    $('#stat-requests').text(stats.requests_total.toLocaleString());
                    $('#stat-bandwidth').text(formatBytes(stats.bandwidth_total));
                    $('#stat-cache-ratio').text(stats.cache_hit_ratio + '%');
                } else {
                    console.error('Failed to load CDN statistics:', response.data);
                }
            },
            error: function() {
                console.error('Error loading CDN statistics');
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php _e('Refresh Stats', 'wp-performance-plus'); ?>');
            }
        });
    });
    
    // Load current provider settings on page load
    if ($('#cdn_provider').val()) {
        $('#cdn_provider').trigger('change');
    }
    
    // Format bytes helper function
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script> 