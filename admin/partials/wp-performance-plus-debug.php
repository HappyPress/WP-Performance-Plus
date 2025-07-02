<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wp_performanceplus-wrap">
    <h1><?php _e('Performance Plus Debug', 'wp_performanceplus'); ?></h1>

    <div class="notice notice-info">
        <p>
            <?php _e('Debug mode is currently:', 'wp_performanceplus'); ?>
            <strong id="debug-status">
                <?php echo get_option('wp_performanceplus_debug_mode') ? 
                    __('Enabled', 'wp_performanceplus') : 
                    __('Disabled', 'wp_performanceplus'); ?>
            </strong>
            <button type="button" class="button" id="toggle-debug">
                <?php _e('Toggle Debug Mode', 'wp_performanceplus'); ?>
            </button>
        </p>
    </div>

    <div class="pp-card">
        <div class="pp-card-header">
            <h2><?php _e('Performance Metrics', 'wp_performanceplus'); ?></h2>
            <button type="button" class="button" id="refresh-metrics">
                <?php _e('Refresh', 'wp_performanceplus'); ?>
            </button>
        </div>
        <div class="pp-card-body">
            <div id="performance-metrics">
                <?php
                $metrics = $this->get_metrics();
                if (!empty($metrics)): ?>
                    <div class="metrics-grid">
                        <div class="metric-box">
                            <h3><?php _e('Memory Usage', 'wp_performanceplus'); ?></h3>
                            <p>Current: <?php echo size_format($metrics['memory']['start']); ?></p>
                            <p>Peak: <?php echo size_format($metrics['memory']['peak']); ?></p>
                        </div>
                        <div class="metric-box">
                            <h3><?php _e('Load Time', 'wp_performanceplus'); ?></h3>
                            <p><?php echo number_format($metrics['load_time'], 4); ?> seconds</p>
                        </div>
                        <div class="metric-box">
                            <h3><?php _e('Database Queries', 'wp_performanceplus'); ?></h3>
                            <p>Total: <?php echo $metrics['queries']['count']; ?></p>
                            <p>Slow Queries: <?php echo count($metrics['queries']['slow_queries']); ?></p>
                        </div>
                        <div class="metric-box">
                            <h3><?php _e('Errors', 'wp_performanceplus'); ?></h3>
                            <p>Total: <?php echo $metrics['errors']['count']; ?></p>
                        </div>
                    </div>

                    <?php if (!empty($metrics['queries']['slow_queries'])): ?>
                        <div class="slow-queries">
                            <h3><?php _e('Slow Queries', 'wp_performanceplus'); ?></h3>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Query', 'wp_performanceplus'); ?></th>
                                        <th><?php _e('Time', 'wp_performanceplus'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($metrics['queries']['slow_queries'] as $query): ?>
                                        <tr>
                                            <td><code><?php echo esc_html($query['query']); ?></code></td>
                                            <td><?php echo number_format($query['time'], 4); ?>s</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($metrics['errors']['types'])): ?>
                        <div class="error-summary">
                            <h3><?php _e('Error Summary', 'wp_performanceplus'); ?></h3>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Error Type', 'wp_performanceplus'); ?></th>
                                        <th><?php _e('Count', 'wp_performanceplus'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($metrics['errors']['types'] as $type => $count): ?>
                                        <tr>
                                            <td><?php echo esc_html($type); ?></td>
                                            <td><?php echo intval($count); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p><?php _e('No metrics available. Enable debug mode to collect metrics.', 'wp_performanceplus'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="pp-card">
        <div class="pp-card-header">
            <h2><?php _e('Debug Log', 'wp_performanceplus'); ?></h2>
            <div class="pp-card-actions">
                <button type="button" class="button" id="refresh-log">
                    <?php _e('Refresh', 'wp_performanceplus'); ?>
                </button>
                <button type="button" class="button" id="clear-log">
                    <?php _e('Clear Log', 'wp_performanceplus'); ?>
                </button>
            </div>
        </div>
        <div class="pp-card-body">
            <div id="debug-log">
                <pre><?php echo esc_html(file_get_contents($this->log_file)); ?></pre>
            </div>
        </div>
    </div>
</div>

<style>
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 15px;
    border-radius: 4px;
}

.metric-box h3 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #1d2327;
}

.metric-box p {
    margin: 5px 0;
    font-size: 13px;
    color: #50575e;
}

.slow-queries, .error-summary {
    margin-top: 30px;
}

#debug-log {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 15px;
    border-radius: 4px;
    max-height: 500px;
    overflow-y: auto;
}

#debug-log pre {
    margin: 0;
    white-space: pre-wrap;
    font-family: monospace;
    font-size: 12px;
}

.pp-card-actions {
    display: flex;
    gap: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle debug mode
    $('#toggle-debug').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performanceplus_toggle_debug',
                _ajax_nonce: '<?php echo wp_create_nonce('wp_performanceplus_debug'); ?>',
                enabled: $('#debug-status').text().trim() === 'Disabled'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Refresh metrics
    $('#refresh-metrics').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performanceplus_get_metrics',
                _ajax_nonce: '<?php echo wp_create_nonce('wp_performanceplus_debug'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Clear log
    $('#clear-log').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to clear the debug log?', 'wp_performanceplus'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performanceplus_clear_log',
                _ajax_nonce: '<?php echo wp_create_nonce('wp_performanceplus_debug'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Refresh log
    $('#refresh-log').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performanceplus_get_log',
                _ajax_nonce: '<?php echo wp_create_nonce('wp_performanceplus_debug'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#debug-log pre').text(response.data.log);
                }
            }
        });
    });
});
</script> 