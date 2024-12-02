<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wp-performance-plus-settings">
    <h1><?php _e('Performance Settings', 'wp-performance-plus'); ?></h1>
    
    <p class="description">
        <?php _e('Configure optimization settings to improve your website\'s performance.', 'wp-performance-plus'); ?>
    </p>

    <?php
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'basics';
    ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-performance-plus-settings&tab=basics" class="nav-tab <?php echo $active_tab === 'basics' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('Basics', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-settings&tab=caching" class="nav-tab <?php echo $active_tab === 'caching' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-performance"></span>
            <?php _e('Caching', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-settings&tab=images" class="nav-tab <?php echo $active_tab === 'images' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-format-image"></span>
            <?php _e('Image Optimization', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-settings&tab=cdn" class="nav-tab <?php echo $active_tab === 'cdn' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-networking"></span>
            <?php _e('CDN Integration', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-settings&tab=database" class="nav-tab <?php echo $active_tab === 'database' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-database"></span>
            <?php _e('Database', 'wp-performance-plus'); ?>
        </a>
    </h2>

    <form method="post" action="options.php" class="settings-form">
        <?php settings_fields('wp_performance_plus_settings'); ?>
        
        <div class="tab-content">
            <?php
            switch ($active_tab) {
                case 'basics':
                    include plugin_dir_path(__FILE__) . 'settings/basics.php';
                    break;
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

        <?php submit_button(); ?>
    </form>
</div> 