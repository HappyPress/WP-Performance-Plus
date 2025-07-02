<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-performance-plus-settings-content">
    
    <!-- Automatic Cleanup Configuration -->
    <div class="settings-section-header">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-database"></span>
            <?php _e('Database Optimization', 'wp-performance-plus'); ?>
        </h2>
        <p class="settings-section-description">
            <?php _e('Configure advanced database cleanup and optimization settings. Basic auto cleanup can be enabled on the main dashboard.', 'wp-performance-plus'); ?>
        </p>
    </div>

    <!-- Cleanup Schedule Settings -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-clock"></span>
            <?php _e('Cleanup Schedule', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Cleanup Frequency Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="cleanup_frequency" class="settings-field-label">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Cleanup Frequency', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <select id="cleanup_frequency" name="wp_performance_plus_settings[cleanup_frequency]" class="settings-select">
                                <option value="daily" <?php selected(get_option('wp_performance_plus_settings')['cleanup_frequency'] ?? 'weekly', 'daily'); ?>>
                                    <?php _e('Daily', 'wp-performance-plus'); ?>
                                </option>
                                <option value="weekly" <?php selected(get_option('wp_performance_plus_settings')['cleanup_frequency'] ?? 'weekly', 'weekly'); ?>>
                                    <?php _e('Weekly (Recommended)', 'wp-performance-plus'); ?>
                                </option>
                                <option value="monthly" <?php selected(get_option('wp_performance_plus_settings')['cleanup_frequency'] ?? 'weekly', 'monthly'); ?>>
                                    <?php _e('Monthly', 'wp-performance-plus'); ?>
                                </option>
                            </select>
                        </div>
                        <p class="settings-field-description"><?php _e('How often should the automatic database cleanup run', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cleanup Options -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Cleanup Options', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Post Revisions Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-backup"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Post Revisions', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove old post revisions', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_revisions]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_revisions'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Auto Drafts Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-edit"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Auto Drafts', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove old auto-saved drafts', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_auto_drafts]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_auto_drafts'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Trashed Posts Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-trash"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Trashed Posts', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove posts from trash', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_trashed_posts]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_trashed_posts'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Spam Comments Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Spam Comments', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove spam comments', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_spam_comments]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_spam_comments'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Trashed Comments Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-dismiss"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Trashed Comments', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove comments from trash', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_trashed_comments]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_trashed_comments'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Expired Transients Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Expired Transients', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove expired transient options', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_expired_transients]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_expired_transients'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Optimize Tables Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-performance"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Optimize Tables', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Optimize database tables to reduce overhead', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_optimize_tables]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_optimize_tables'] ?? true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pingbacks/Trackbacks Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-admin-links"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Pingbacks & Trackbacks', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Remove pingbacks and trackbacks', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[cleanup_pingbacks]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_pingbacks'] ?? false); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Retention Settings -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-archive"></span>
            <?php _e('Retention Settings', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Revision Limit Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="revision_limit" class="settings-field-label">
                            <span class="dashicons dashicons-backup"></span>
                            <?php _e('Revision Limit', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <input type="number" id="revision_limit" name="wp_performance_plus_settings[revision_limit]" 
                                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['revision_limit'] ?? '5'); ?>" 
                                   min="0" max="100" class="settings-number-input">
                            <span class="settings-field-unit"><?php _e('revisions per post', 'wp-performance-plus'); ?></span>
                        </div>
                        <p class="settings-field-description"><?php _e('Number of revisions to keep for each post (0 = keep all, not recommended)', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Trash Retention Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="trash_days" class="settings-field-label">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Trash Retention', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <input type="number" id="trash_days" name="wp_performance_plus_settings[trash_days]" 
                                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['trash_days'] ?? '30'); ?>" 
                                   min="0" max="365" class="settings-number-input">
                            <span class="settings-field-unit"><?php _e('days', 'wp-performance-plus'); ?></span>
                        </div>
                        <p class="settings-field-description"><?php _e('Number of days to keep trashed items before permanent deletion (0 = keep forever)', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Actions -->
    <div class="settings-section-actions">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Database Tools', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-actions-grid">
            <button type="button" class="settings-action-btn primary" id="run_cleanup">
                <span class="dashicons dashicons-database"></span>
                <?php _e('Run Cleanup Now', 'wp-performance-plus'); ?>
                <small><?php _e('Manually run the database cleanup process', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="database_analysis">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e('Database Analysis', 'wp-performance-plus'); ?>
                <small><?php _e('Analyze database size and optimization opportunities', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="backup_database">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Create Backup', 'wp-performance-plus'); ?>
                <small><?php _e('Create a database backup before optimization', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn danger" id="reset_database_settings">
                <span class="dashicons dashicons-undo"></span>
                <?php _e('Reset to Defaults', 'wp-performance-plus'); ?>
                <small><?php _e('Reset all database settings to default values', 'wp-performance-plus'); ?></small>
            </button>
        </div>
    </div>

    <!-- Database Statistics -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-chart-pie"></span>
            <?php _e('Database Statistics', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="database-stats-grid">
            <div class="stats-card">
                <div class="stats-icon">
                    <span class="dashicons dashicons-database"></span>
                </div>
                <div class="stats-content">
                    <h4><?php _e('Database Size', 'wp-performance-plus'); ?></h4>
                    <span class="stats-value" id="db-size">--</span>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon">
                    <span class="dashicons dashicons-backup"></span>
                </div>
                <div class="stats-content">
                    <h4><?php _e('Post Revisions', 'wp-performance-plus'); ?></h4>
                    <span class="stats-value" id="revision-count">--</span>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon">
                    <span class="dashicons dashicons-trash"></span>
                </div>
                <div class="stats-content">
                    <h4><?php _e('Trashed Items', 'wp-performance-plus'); ?></h4>
                    <span class="stats-value" id="trash-count">--</span>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <div class="stats-content">
                    <h4><?php _e('Spam Comments', 'wp-performance-plus'); ?></h4>
                    <span class="stats-value" id="spam-count">--</span>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Load database statistics
    function loadDatabaseStats() {
        $.ajax({
            url: wp_performance_plus_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_performance_plus_database_stats',
                nonce: wp_performance_plus_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#db-size').text(response.data.size || '--');
                    $('#revision-count').text(response.data.revisions || '--');
                    $('#trash-count').text(response.data.trash || '--');
                    $('#spam-count').text(response.data.spam || '--');
                }
            }
        });
    }

    // Load stats on page load
    loadDatabaseStats();
});
</script> 