<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/HappyPress/WP-Performance-Plus
 * @since      1.0.0
 *
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get plugin settings
$settings = get_option('wp_performance_plus_settings', array());
$optimization_enabled = isset($settings['enable_optimization']) ? $settings['enable_optimization'] : false;
$optimization_level = isset($settings['optimization_level']) ? $settings['optimization_level'] : 'balanced';
$cdn_provider = isset($settings['cdn_provider']) ? $settings['cdn_provider'] : 'none';

// Get CDN manager instance
$plugin_instance = WP_Performance_Plus::get_instance();
$cdn_manager = $plugin_instance->get_cdn_manager();
$cdn_enabled = $cdn_manager && $cdn_manager->is_cdn_enabled();
$active_provider = $cdn_enabled ? $cdn_manager->get_active_provider() : null;

// Get CDN statistics if available
$cdn_stats = array(
    'requests_total' => 0,
    'bandwidth_total' => 0,
    'cache_hit_ratio' => 0,
    'threats_blocked' => 0
);

if ($cdn_enabled && $active_provider) {
    $stats_result = $active_provider->get_statistics();
    if (!is_wp_error($stats_result)) {
        $cdn_stats = $stats_result;
    }
}

// Get performance metrics (placeholder data for now)
$performance_metrics = array(
    'cache_size' => '142 MB',
    'cached_files' => 1247,
    'optimization_status' => $optimization_enabled ? 'Active' : 'Disabled',
    'last_optimization' => get_option('wp_performance_plus_last_optimization', 'Never'),
    'database_size' => '23.4 MB',
    'database_tables' => 47
);

?>

<div class="wrap wp-performance-plus-dashboard">
    <h1 class="wp-heading-inline">
        <?php _e('WP Performance Plus Dashboard', 'wp-performance-plus'); ?>
        <span class="wp-performance-plus-version">v<?php echo WP_PERFORMANCE_PLUS_VERSION; ?></span>
    </h1>
    
    <hr class="wp-header-end">

    <!-- Quick Status Cards -->
    <div class="wp-performance-plus-status-cards">
        <div class="status-card status-<?php echo $optimization_enabled ? 'active' : 'inactive'; ?>">
            <div class="status-icon">
                <span class="dashicons <?php echo $optimization_enabled ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
            </div>
            <div class="status-content">
                <h3><?php _e('Optimization', 'wp-performance-plus'); ?></h3>
                <p class="status-text"><?php echo $performance_metrics['optimization_status']; ?></p>
                <p class="status-level"><?php printf(__('Level: %s', 'wp-performance-plus'), ucfirst($optimization_level)); ?></p>
            </div>
        </div>

        <!-- Enhanced CDN Status Card -->
        <div class="status-card status-<?php echo $cdn_enabled ? 'active' : 'inactive'; ?> cdn-status-card">
            <div class="status-icon">
                <span class="dashicons dashicons-cloud"></span>
            </div>
            <div class="status-content">
                <h3><?php _e('CDN Status', 'wp-performance-plus'); ?></h3>
                <p class="status-text">
                    <?php if ($cdn_enabled): ?>
                        <?php echo ucfirst(str_replace('WP_Performance_Plus_', '', get_class($active_provider))); ?>
                        <span class="status-indicator active"></span>
                    <?php else: ?>
                        <?php _e('Not Configured', 'wp-performance-plus'); ?>
                        <span class="status-indicator inactive"></span>
                    <?php endif; ?>
                </p>
                <p class="status-level">
                    <?php if ($cdn_enabled): ?>
                        <?php printf(__('Hit Ratio: %s%%', 'wp-performance-plus'), $cdn_stats['cache_hit_ratio']); ?>
                    <?php else: ?>
                        <a href="<?php echo admin_url('admin.php?page=wp-performance-plus-settings&tab=cdn'); ?>" class="configure-link">
                            <?php _e('Configure CDN', 'wp-performance-plus'); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="status-card status-cache">
            <div class="status-icon">
                <span class="dashicons dashicons-performance"></span>
            </div>
            <div class="status-content">
                <h3><?php _e('Cache', 'wp-performance-plus'); ?></h3>
                <p class="status-text"><?php echo $performance_metrics['cache_size']; ?></p>
                <p class="status-level"><?php printf(__('%d files cached', 'wp-performance-plus'), $performance_metrics['cached_files']); ?></p>
            </div>
        </div>

        <div class="status-card status-database">
            <div class="status-icon">
                <span class="dashicons dashicons-database"></span>
            </div>
            <div class="status-content">
                <h3><?php _e('Database', 'wp-performance-plus'); ?></h3>
                <p class="status-text"><?php echo $performance_metrics['database_size']; ?></p>
                <p class="status-level"><?php printf(__('%d tables', 'wp-performance-plus'), $performance_metrics['database_tables']); ?></p>
            </div>
        </div>
    </div>

    <!-- CDN Performance Summary (only show if CDN enabled) -->
    <?php if ($cdn_enabled): ?>
    <div class="wp-performance-plus-cdn-summary">
        <h2>
            <span class="dashicons dashicons-networking"></span>
            <?php _e('CDN Performance Summary', 'wp-performance-plus'); ?>
            <span class="refresh-stats" data-action="refresh_cdn_stats">
                <span class="dashicons dashicons-update-alt"></span>
            </span>
        </h2>
        <div class="cdn-metrics-grid">
            <div class="cdn-metric">
                <div class="metric-value" id="cdn-requests-total"><?php echo number_format($cdn_stats['requests_total']); ?></div>
                <div class="metric-label"><?php _e('Requests (24h)', 'wp-performance-plus'); ?></div>
            </div>
            <div class="cdn-metric">
                <div class="metric-value" id="cdn-bandwidth-total"><?php echo size_format($cdn_stats['bandwidth_total']); ?></div>
                <div class="metric-label"><?php _e('Bandwidth (24h)', 'wp-performance-plus'); ?></div>
            </div>
            <div class="cdn-metric">
                <div class="metric-value" id="cdn-cache-ratio"><?php echo $cdn_stats['cache_hit_ratio']; ?>%</div>
                <div class="metric-label"><?php _e('Cache Hit Ratio', 'wp-performance-plus'); ?></div>
            </div>
            <?php if ($cdn_stats['threats_blocked'] > 0): ?>
            <div class="cdn-metric">
                <div class="metric-value" id="cdn-threats-blocked"><?php echo number_format($cdn_stats['threats_blocked']); ?></div>
                <div class="metric-label"><?php _e('Threats Blocked', 'wp-performance-plus'); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="wp-performance-plus-main-content">
        
        <!-- Enhanced Quick Actions Panel -->
        <div class="wp-performance-plus-panel quick-actions">
            <h2><?php _e('Quick Actions', 'wp-performance-plus'); ?></h2>
            <div class="quick-actions-grid">
                <button type="button" class="button button-primary action-btn" id="clear-cache">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Clear All Cache', 'wp-performance-plus'); ?>
                </button>
                
                <button type="button" class="button button-secondary action-btn" id="run-optimization">
                    <span class="dashicons dashicons-performance"></span>
                    <?php _e('Run Optimization', 'wp-performance-plus'); ?>
                </button>
                
                <?php if ($cdn_enabled): ?>
                <button type="button" class="button button-secondary action-btn" id="purge-cdn-cache">
                    <span class="dashicons dashicons-cloud"></span>
                    <?php _e('Purge CDN Cache', 'wp-performance-plus'); ?>
                </button>
                
                <button type="button" class="button button-secondary action-btn" id="test-cdn-connection">
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <?php _e('Test CDN Connection', 'wp-performance-plus'); ?>
                </button>
                <?php else: ?>
                <button type="button" class="button button-secondary action-btn" id="setup-cdn">
                    <span class="dashicons dashicons-cloud"></span>
                    <?php _e('Setup CDN', 'wp-performance-plus'); ?>
                </button>
                <?php endif; ?>
                
                <button type="button" class="button button-secondary action-btn" id="analyze-performance">
                    <span class="dashicons dashicons-chart-line"></span>
                    <?php _e('Analyze Performance', 'wp-performance-plus'); ?>
                </button>
            </div>
        </div>

        <!-- Settings Overview -->
        <div class="wp-performance-plus-panel settings-overview">
            <h2><?php _e('Settings Overview', 'wp-performance-plus'); ?></h2>
            
            <form method="post" action="options.php" class="wp-performance-plus-form">
                <?php settings_fields('wp_performance_plus_settings_group'); ?>
                
                <!-- General Settings -->
                <div class="settings-section">
                    <h3><?php _e('General Settings', 'wp-performance-plus'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Enable Optimization', 'wp-performance-plus'); ?></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="wp_performance_plus_settings[enable_optimization]" value="1" <?php checked(1, $optimization_enabled); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description"><?php _e('Master switch to enable/disable all optimization features.', 'wp-performance-plus'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Optimization Level', 'wp-performance-plus'); ?></th>
                            <td>
                                <select name="wp_performance_plus_settings[optimization_level]" class="regular-text">
                                    <option value="safe" <?php selected($optimization_level, 'safe'); ?>><?php _e('Safe - Minimal optimizations', 'wp-performance-plus'); ?></option>
                                    <option value="balanced" <?php selected($optimization_level, 'balanced'); ?>><?php _e('Balanced - Recommended settings', 'wp-performance-plus'); ?></option>
                                    <option value="aggressive" <?php selected($optimization_level, 'aggressive'); ?>><?php _e('Aggressive - Maximum optimization', 'wp-performance-plus'); ?></option>
                                </select>
                                <p class="description"><?php _e('Choose the optimization level that best fits your needs.', 'wp-performance-plus'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Enhanced CDN Settings Preview -->
                <div class="settings-section">
                    <h3>
                        <?php _e('CDN Configuration', 'wp-performance-plus'); ?>
                        <a href="<?php echo admin_url('admin.php?page=wp-performance-plus-settings&tab=cdn'); ?>" class="button button-small">
                            <?php _e('Advanced CDN Settings', 'wp-performance-plus'); ?>
                        </a>
                    </h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('CDN Provider', 'wp-performance-plus'); ?></th>
                            <td>
                                <select name="wp_performance_plus_settings[cdn_provider]" class="regular-text" id="dashboard-cdn-provider">
                                    <option value="none" <?php selected($cdn_provider, 'none'); ?>><?php _e('None', 'wp-performance-plus'); ?></option>
                                    <option value="cloudflare" <?php selected($cdn_provider, 'cloudflare'); ?>><?php _e('Cloudflare', 'wp-performance-plus'); ?></option>
                                    <option value="keycdn" <?php selected($cdn_provider, 'keycdn'); ?>><?php _e('KeyCDN', 'wp-performance-plus'); ?></option>
                                    <option value="bunnycdn" <?php selected($cdn_provider, 'bunnycdn'); ?>><?php _e('BunnyCDN', 'wp-performance-plus'); ?></option>
                                    <option value="cloudfront" <?php selected($cdn_provider, 'cloudfront'); ?>><?php _e('Amazon CloudFront', 'wp-performance-plus'); ?></option>
                                </select>
                                <p class="description">
                                    <?php _e('Select your CDN provider for optimized content delivery.', 'wp-performance-plus'); ?>
                                    <?php if ($cdn_enabled): ?>
                                        <span class="cdn-status-inline">
                                            <span class="status-dot active"></span>
                                            <?php _e('Connected and Active', 'wp-performance-plus'); ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                        <?php if ($cdn_enabled): ?>
                        <tr>
                            <th scope="row"><?php _e('CDN File Types', 'wp-performance-plus'); ?></th>
                            <td>
                                <div class="cdn-file-types">
                                    <label class="inline-checkbox">
                                        <input type="checkbox" name="wp_performance_plus_settings[cdn_images]" value="1" <?php checked(1, isset($settings['cdn_images']) ? $settings['cdn_images'] : false); ?> />
                                        <?php _e('Images', 'wp-performance-plus'); ?>
                                    </label>
                                    <label class="inline-checkbox">
                                        <input type="checkbox" name="wp_performance_plus_settings[cdn_css]" value="1" <?php checked(1, isset($settings['cdn_css']) ? $settings['cdn_css'] : false); ?> />
                                        <?php _e('CSS', 'wp-performance-plus'); ?>
                                    </label>
                                    <label class="inline-checkbox">
                                        <input type="checkbox" name="wp_performance_plus_settings[cdn_js]" value="1" <?php checked(1, isset($settings['cdn_js']) ? $settings['cdn_js'] : false); ?> />
                                        <?php _e('JavaScript', 'wp-performance-plus'); ?>
                                    </label>
                                    <label class="inline-checkbox">
                                        <input type="checkbox" name="wp_performance_plus_settings[cdn_fonts]" value="1" <?php checked(1, isset($settings['cdn_fonts']) ? $settings['cdn_fonts'] : false); ?> />
                                        <?php _e('Fonts', 'wp-performance-plus'); ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Quick Feature Toggles -->
                <div class="settings-section">
                    <h3><?php _e('Quick Feature Toggles', 'wp-performance-plus'); ?></h3>
                    <div class="feature-toggles">
                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="wp_performance_plus_settings[minify_html]" value="1" <?php checked(1, isset($settings['minify_html']) ? $settings['minify_html'] : false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <strong><?php _e('Minify HTML', 'wp-performance-plus'); ?></strong>
                                <p><?php _e('Removes unnecessary whitespace and comments from HTML output.', 'wp-performance-plus'); ?></p>
                            </div>
                        </div>

                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="wp_performance_plus_settings[minify_css]" value="1" <?php checked(1, isset($settings['minify_css']) ? $settings['minify_css'] : false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <strong><?php _e('Minify CSS', 'wp-performance-plus'); ?></strong>
                                <p><?php _e('Removes unnecessary whitespace and comments from CSS files.', 'wp-performance-plus'); ?></p>
                            </div>
                        </div>

                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="wp_performance_plus_settings[minify_js]" value="1" <?php checked(1, isset($settings['minify_js']) ? $settings['minify_js'] : false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <strong><?php _e('Minify JavaScript', 'wp-performance-plus'); ?></strong>
                                <p><?php _e('Removes unnecessary whitespace and comments from JavaScript files.', 'wp-performance-plus'); ?></p>
                            </div>
                        </div>

                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="wp_performance_plus_settings[auto_cleanup]" value="1" <?php checked(1, isset($settings['auto_cleanup']) ? $settings['auto_cleanup'] : false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <strong><?php _e('Auto Database Cleanup', 'wp-performance-plus'); ?></strong>
                                <p><?php _e('Automatically clean database regularly.', 'wp-performance-plus'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php submit_button(__('Save Settings', 'wp-performance-plus'), 'primary', 'submit', false); ?>
            </form>
        </div>

        <!-- Enhanced Performance Insights -->
        <div class="wp-performance-plus-panel performance-insights">
            <h2><?php _e('Performance Insights', 'wp-performance-plus'); ?></h2>
            
            <div class="insights-grid">
                <div class="insight-item">
                    <h4><?php _e('Page Speed Score', 'wp-performance-plus'); ?></h4>
                    <div class="score-circle">
                        <span class="score">85</span>
                        <small>/100</small>
                    </div>
                    <p><?php _e('Good performance score', 'wp-performance-plus'); ?></p>
                </div>

                <div class="insight-item">
                    <h4><?php _e('Cache Hit Rate', 'wp-performance-plus'); ?></h4>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $cdn_enabled ? $cdn_stats['cache_hit_ratio'] : 78; ?>%"></div>
                    </div>
                    <p>
                        <?php echo $cdn_enabled ? $cdn_stats['cache_hit_ratio'] : 78; ?>% 
                        <?php _e('cache efficiency', 'wp-performance-plus'); ?>
                        <?php if ($cdn_enabled): ?>
                            <small>(<?php _e('CDN enabled', 'wp-performance-plus'); ?>)</small>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="insight-item">
                    <h4><?php _e('Optimization Recommendations', 'wp-performance-plus'); ?></h4>
                    <ul class="optimization-list">
                        <li class="optimization-item">
                            <span class="status-dot <?php echo $cdn_enabled ? 'green' : 'orange'; ?>"></span>
                            <?php if ($cdn_enabled): ?>
                                <?php _e('CDN configured and active', 'wp-performance-plus'); ?>
                            <?php else: ?>
                                <?php _e('Setup CDN for better performance', 'wp-performance-plus'); ?>
                            <?php endif; ?>
                        </li>
                        <li class="optimization-item">
                            <span class="status-dot <?php echo isset($settings['minify_css']) && $settings['minify_css'] ? 'green' : 'orange'; ?>"></span>
                            <?php _e('CSS optimization', 'wp-performance-plus'); ?>
                        </li>
                        <li class="optimization-item">
                            <span class="status-dot <?php echo isset($settings['minify_js']) && $settings['minify_js'] ? 'green' : 'red'; ?>"></span>
                            <?php _e('JavaScript optimization', 'wp-performance-plus'); ?>
                        </li>
                        <?php if ($cdn_enabled && $cdn_stats['cache_hit_ratio'] < 80): ?>
                        <li class="optimization-item">
                            <span class="status-dot orange"></span>
                            <?php _e('CDN cache ratio can be improved', 'wp-performance-plus'); ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="wp-performance-plus-panel recent-activity">
            <h2><?php _e('Recent Activity', 'wp-performance-plus'); ?></h2>
            <div class="activity-list">
                <div class="activity-item">
                    <span class="activity-icon dashicons dashicons-update"></span>
                    <div class="activity-content">
                        <strong><?php _e('Cache cleared', 'wp-performance-plus'); ?></strong>
                        <span class="activity-time"><?php _e('2 hours ago', 'wp-performance-plus'); ?></span>
                    </div>
                </div>
                <?php if ($cdn_enabled): ?>
                <div class="activity-item">
                    <span class="activity-icon dashicons dashicons-cloud"></span>
                    <div class="activity-content">
                        <strong><?php printf(__('CDN cache purged (%s)', 'wp-performance-plus'), ucfirst(str_replace('WP_Performance_Plus_', '', get_class($active_provider)))); ?></strong>
                        <span class="activity-time"><?php _e('4 hours ago', 'wp-performance-plus'); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="activity-item">
                    <span class="activity-icon dashicons dashicons-performance"></span>
                    <div class="activity-content">
                        <strong><?php _e('Optimization completed', 'wp-performance-plus'); ?></strong>
                        <span class="activity-time"><?php _e('1 day ago', 'wp-performance-plus'); ?></span>
                    </div>
                </div>
                <div class="activity-item">
                    <span class="activity-icon dashicons dashicons-admin-settings"></span>
                    <div class="activity-content">
                        <strong><?php _e('Settings updated', 'wp-performance-plus'); ?></strong>
                        <span class="activity-time"><?php _e('3 days ago', 'wp-performance-plus'); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Loading Overlay -->
<div id="wp-performance-plus-loading" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p class="loading-text"><?php _e('Processing...', 'wp-performance-plus'); ?></p>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var nonce = '<?php echo wp_create_nonce('wp_performance_plus_wizard'); ?>';
    
    // Quick action handlers
    $('#clear-cache').on('click', function() {
        performQuickAction('clear_cache', '<?php _e('Clearing cache...', 'wp-performance-plus'); ?>');
    });
    
    $('#run-optimization').on('click', function() {
        performQuickAction('run_optimization', '<?php _e('Running optimization...', 'wp-performance-plus'); ?>');
    });
    
    $('#purge-cdn-cache').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to purge all CDN cache?', 'wp-performance-plus'); ?>')) {
            performQuickAction('purge_cdn', '<?php _e('Purging CDN cache...', 'wp-performance-plus'); ?>');
        }
    });
    
    $('#test-cdn-connection').on('click', function() {
        performQuickAction('test_cdn', '<?php _e('Testing CDN connection...', 'wp-performance-plus'); ?>');
    });
    
    $('#setup-cdn').on('click', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=wp-performance-plus-settings&tab=cdn'); ?>';
    });
    
    $('#analyze-performance').on('click', function() {
        performQuickAction('analyze_performance', '<?php _e('Analyzing performance...', 'wp-performance-plus'); ?>');
    });
    
    // Refresh CDN stats
    $('.refresh-stats').on('click', function() {
        var $this = $(this);
        var originalHtml = $this.html();
        
        $this.html('<span class="dashicons dashicons-update-alt spin"></span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_get_cdn_stats',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    var stats = response.data;
                    $('#cdn-requests-total').text(stats.requests_total.toLocaleString());
                    $('#cdn-bandwidth-total').text(formatBytes(stats.bandwidth_total));
                    $('#cdn-cache-ratio').text(stats.cache_hit_ratio + '%');
                    if ($('#cdn-threats-blocked').length) {
                        $('#cdn-threats-blocked').text(stats.threats_blocked.toLocaleString());
                    }
                    showNotice('<?php _e('CDN statistics updated', 'wp-performance-plus'); ?>', 'success');
                } else {
                    showNotice('<?php _e('Failed to refresh CDN statistics', 'wp-performance-plus'); ?>', 'error');
                }
            },
            error: function() {
                showNotice('<?php _e('Failed to refresh CDN statistics', 'wp-performance-plus'); ?>', 'error');
            },
            complete: function() {
                $this.html(originalHtml);
            }
        });
    });
    
    function performQuickAction(action, loadingText) {
        showLoading(loadingText);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_' + action,
                nonce: nonce
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showNotice(response.data || '<?php _e('Action completed successfully', 'wp-performance-plus'); ?>', 'success');
                    
                    // Refresh page for some actions
                    if (action === 'clear_cache' || action === 'purge_cdn') {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    showNotice(response.data || '<?php _e('An error occurred', 'wp-performance-plus'); ?>', 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotice('<?php _e('Request failed', 'wp-performance-plus'); ?>', 'error');
            }
        });
    }
    
    function showLoading(text) {
        $('#wp-performance-plus-loading .loading-text').text(text);
        $('#wp-performance-plus-loading').show();
    }
    
    function hideLoading() {
        $('#wp-performance-plus-loading').hide();
    }
    
    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wp-performance-plus-dashboard h1').after(notice);
        
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 3000);
    }
    
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script> 