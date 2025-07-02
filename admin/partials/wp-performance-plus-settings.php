<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wp-performance-plus-settings">
    <h1 class="wp-heading-inline">
        <?php _e('Performance Settings', 'wp-performance-plus'); ?>
        <span class="wp-performance-plus-version">Advanced Configuration</span>
    </h1>
    
    <hr class="wp-header-end">
    
    <p class="settings-description">
        <?php _e('Configure advanced optimization settings. Basic settings are available on the main dashboard.', 'wp-performance-plus'); ?>
        <a href="<?php echo admin_url('admin.php?page=wp-performance-plus'); ?>" class="button button-small">
            <?php _e('← Back to Dashboard', 'wp-performance-plus'); ?>
        </a>
    </p>

    <?php $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'caching'; ?>

    <div class="wp-performance-plus-nav-wrapper">
        <nav class="nav-tab-wrapper wp-clearfix">
            <a href="?page=wp-performance-plus-settings&tab=caching" 
               class="nav-tab <?php echo $active_tab === 'caching' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-performance"></span>
                <?php _e('Caching', 'wp-performance-plus'); ?>
            </a>
            <a href="?page=wp-performance-plus-settings&tab=images" 
               class="nav-tab <?php echo $active_tab === 'images' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-format-image"></span>
                <?php _e('Image Optimization', 'wp-performance-plus'); ?>
            </a>
            <a href="?page=wp-performance-plus-settings&tab=cdn" 
               class="nav-tab <?php echo $active_tab === 'cdn' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-networking"></span>
                <?php _e('CDN Integration', 'wp-performance-plus'); ?>
            </a>
            <a href="?page=wp-performance-plus-settings&tab=database" 
               class="nav-tab <?php echo $active_tab === 'database' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-database"></span>
                <?php _e('Database', 'wp-performance-plus'); ?>
            </a>
        </nav>
    </div>

    <form method="post" action="options.php" class="wp-performance-plus-settings-form">
        <?php settings_fields('wp_performance_plus_settings'); ?>
        
        <div class="tab-content-wrapper">
            <?php
            switch ($active_tab) {
                case 'caching':
                    include plugin_dir_path(__FILE__) . 'settings/caching.php';
                    break;
                case 'images':
                    include plugin_dir_path(__FILE__) . 'settings/images.php';
                    break;
                case 'cdn':
                    include plugin_dir_path(__FILE__) . 'settings/cdn.php';
                    break;
                case 'database':
                    include plugin_dir_path(__FILE__) . 'settings/database.php';
                    break;
            }
            ?>
        </div>

        <div class="settings-submit-wrapper">
            <?php submit_button(__('Save Advanced Settings', 'wp-performance-plus'), 'primary', 'submit', false); ?>
            <button type="button" class="button button-secondary" id="reset-to-defaults">
                <?php _e('Reset to Defaults', 'wp-performance-plus'); ?>
            </button>
        </div>
    </form>
</div>

<!-- Settings change notification -->
<div id="settings-change-notice" class="settings-notice" style="display: none;">
    <div class="settings-notice-content">
        <span class="dashicons dashicons-info"></span>
        <span class="notice-text"><?php _e('Settings changed. Don\'t forget to save!', 'wp-performance-plus'); ?></span>
    </div>
</div> 