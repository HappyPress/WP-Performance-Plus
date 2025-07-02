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

        <div class="status-card status-info">
            <div class="status-icon">
                <span class="dashicons dashicons-cloud"></span>
            </div>
            <div class="status-content">
                <h3><?php _e('CDN Status', 'wp-performance-plus'); ?></h3>
                <p class="status-text"><?php echo ($cdn_provider !== 'none') ? ucfirst($cdn_provider) : __('Not Configured', 'wp-performance-plus'); ?></p>
                <p class="status-level"><?php echo ($cdn_provider !== 'none') ? __('Active', 'wp-performance-plus') : __('Inactive', 'wp-performance-plus'); ?></p>
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

    <!-- Main Content Area -->
    <div class="wp-performance-plus-main-content">
        
        <!-- Quick Actions Panel -->
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
                
                <button type="button" class="button button-secondary action-btn" id="test-cdn">
                    <span class="dashicons dashicons-cloud"></span>
                    <?php _e('Test CDN', 'wp-performance-plus'); ?>
                </button>
                
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

                <!-- CDN Settings Preview -->
                <div class="settings-section">
                    <h3><?php _e('CDN Configuration', 'wp-performance-plus'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('CDN Provider', 'wp-performance-plus'); ?></th>
                            <td>
                                <select name="wp_performance_plus_settings[cdn_provider]" class="regular-text">
                                    <option value="none" <?php selected($cdn_provider, 'none'); ?>><?php _e('None', 'wp-performance-plus'); ?></option>
                                    <option value="cloudflare" <?php selected($cdn_provider, 'cloudflare'); ?>><?php _e('Cloudflare', 'wp-performance-plus'); ?></option>
                                    <option value="stackpath" <?php selected($cdn_provider, 'stackpath'); ?>><?php _e('StackPath', 'wp-performance-plus'); ?></option>
                                    <option value="keycdn" <?php selected($cdn_provider, 'keycdn'); ?>><?php _e('KeyCDN', 'wp-performance-plus'); ?></option>
                                    <option value="bunnycdn" <?php selected($cdn_provider, 'bunnycdn'); ?>><?php _e('BunnyCDN', 'wp-performance-plus'); ?></option>
                                    <option value="cloudfront" <?php selected($cdn_provider, 'cloudfront'); ?>><?php _e('Amazon CloudFront', 'wp-performance-plus'); ?></option>
                                    <option value="custom" <?php selected($cdn_provider, 'custom'); ?>><?php _e('Custom CDN', 'wp-performance-plus'); ?></option>
                                </select>
                                <p class="description">
                                    <?php _e('Select your CDN provider for optimized content delivery.', 'wp-performance-plus'); ?>
                                    <a href="<?php echo admin_url('admin.php?page=wp-performance-plus-cdn'); ?>" class="button button-small"><?php _e('Configure CDN', 'wp-performance-plus'); ?></a>
                                </p>
                            </td>
                        </tr>
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

        <!-- Performance Insights -->
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
                        <div class="progress-fill" style="width: 78%"></div>
                    </div>
                    <p>78% <?php _e('cache efficiency', 'wp-performance-plus'); ?></p>
                </div>

                <div class="insight-item">
                    <h4><?php _e('Optimization Potential', 'wp-performance-plus'); ?></h4>
                    <ul class="optimization-list">
                        <li class="optimization-item">
                            <span class="status-dot green"></span>
                            <?php _e('Images optimized', 'wp-performance-plus'); ?>
                        </li>
                        <li class="optimization-item">
                            <span class="status-dot orange"></span>
                            <?php _e('CSS can be improved', 'wp-performance-plus'); ?>
                        </li>
                        <li class="optimization-item">
                            <span class="status-dot red"></span>
                            <?php _e('JS optimization needed', 'wp-performance-plus'); ?>
                        </li>
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
                <div class="activity-item">
                    <span class="activity-icon dashicons dashicons-performance"></span>
                    <div class="activity-content">
                        <strong><?php _e('Optimization completed', 'wp-performance-plus'); ?></strong>
                        <span class="activity-time"><?php _e('1 day ago', 'wp-performance-plus'); ?></span>
                    </div>
                </div>
                <div class="activity-item">
                    <span class="activity-icon dashicons dashicons-cloud"></span>
                    <div class="activity-content">
                        <strong><?php _e('CDN configuration updated', 'wp-performance-plus'); ?></strong>
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
    
    // Quick action handlers
    $('#clear-cache').on('click', function() {
        performQuickAction('clear_cache', '<?php _e('Clearing cache...', 'wp-performance-plus'); ?>');
    });
    
    $('#run-optimization').on('click', function() {
        performQuickAction('run_optimization', '<?php _e('Running optimization...', 'wp-performance-plus'); ?>');
    });
    
    $('#test-cdn').on('click', function() {
        performQuickAction('test_cdn', '<?php _e('Testing CDN connection...', 'wp-performance-plus'); ?>');
    });
    
    $('#analyze-performance').on('click', function() {
        performQuickAction('analyze_performance', '<?php _e('Analyzing performance...', 'wp-performance-plus'); ?>');
    });
    
    function performQuickAction(action, loadingText) {
        showLoading(loadingText);
        
        $.ajax({
            url: wp_performance_plus_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_' + action,
                nonce: wp_performance_plus_ajax.nonce
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message || '<?php _e('An error occurred', 'wp-performance-plus'); ?>', 'error');
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
});
</script> 