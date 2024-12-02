<?php if (!defined('ABSPATH')) exit; ?>

<div class="settings-section">
    <h3><?php _e('Database Optimization Settings', 'wp-performance-plus'); ?></h3>
    
    <div class="settings-group">
        <h4><?php _e('Automatic Cleanup', 'wp-performance-plus'); ?></h4>
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[auto_cleanup]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['auto_cleanup'] ?? false); ?>>
            <?php _e('Enable Automatic Database Cleanup', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Automatically clean up database tables on a schedule.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="cleanup_frequency"><?php _e('Cleanup Frequency', 'wp-performance-plus'); ?></label>
        <select id="cleanup_frequency" name="wp_performance_plus_settings[cleanup_frequency]" class="regular-text">
            <option value="daily" <?php selected(get_option('wp_performance_plus_settings')['cleanup_frequency'] ?? '', 'daily'); ?>>
                <?php _e('Daily', 'wp-performance-plus'); ?>
            </option>
            <option value="weekly" <?php selected(get_option('wp_performance_plus_settings')['cleanup_frequency'] ?? '', 'weekly'); ?>>
                <?php _e('Weekly', 'wp-performance-plus'); ?>
            </option>
            <option value="monthly" <?php selected(get_option('wp_performance_plus_settings')['cleanup_frequency'] ?? '', 'monthly'); ?>>
                <?php _e('Monthly', 'wp-performance-plus'); ?>
            </option>
        </select>
        <p class="description"><?php _e('How often should the database be cleaned up.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Cleanup Options', 'wp-performance-plus'); ?></h4>
        
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_revisions]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_revisions'] ?? true); ?>>
            <?php _e('Post Revisions', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove old post revisions.', 'wp-performance-plus'); ?></p>

        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_auto_drafts]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_auto_drafts'] ?? true); ?>>
            <?php _e('Auto Drafts', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove old auto-saved drafts.', 'wp-performance-plus'); ?></p>

        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_trashed_posts]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_trashed_posts'] ?? true); ?>>
            <?php _e('Trashed Posts', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove posts from trash.', 'wp-performance-plus'); ?></p>

        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_spam_comments]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_spam_comments'] ?? true); ?>>
            <?php _e('Spam Comments', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove spam comments.', 'wp-performance-plus'); ?></p>

        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_trashed_comments]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_trashed_comments'] ?? true); ?>>
            <?php _e('Trashed Comments', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove comments from trash.', 'wp-performance-plus'); ?></p>

        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_expired_transients]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_expired_transients'] ?? true); ?>>
            <?php _e('Expired Transients', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove expired transient options.', 'wp-performance-plus'); ?></p>

        <label>
            <input type="checkbox" name="wp_performance_plus_settings[cleanup_optimize_tables]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['cleanup_optimize_tables'] ?? true); ?>>
            <?php _e('Optimize Tables', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Optimize database tables to reduce overhead.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Retention Settings', 'wp-performance-plus'); ?></h4>
        
        <label for="revision_limit">
            <?php _e('Keep Last N Revisions', 'wp-performance-plus'); ?>
            <input type="number" id="revision_limit" name="wp_performance_plus_settings[revision_limit]" 
                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['revision_limit'] ?? '5'); ?>" 
                   min="0" max="100" class="small-text">
        </label>
        <p class="description"><?php _e('Number of revisions to keep for each post (0 = keep all).', 'wp-performance-plus'); ?></p>

        <label for="trash_days">
            <?php _e('Empty Trash After N Days', 'wp-performance-plus'); ?>
            <input type="number" id="trash_days" name="wp_performance_plus_settings[trash_days]" 
                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['trash_days'] ?? '30'); ?>" 
                   min="0" max="365" class="small-text">
        </label>
        <p class="description"><?php _e('Number of days to keep trashed items (0 = keep forever).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <button type="button" class="button button-secondary" id="run_cleanup">
            <?php _e('Run Database Cleanup Now', 'wp-performance-plus'); ?>
        </button>
        <p class="description"><?php _e('Manually run the database cleanup process.', 'wp-performance-plus'); ?></p>
    </div>
</div> 