<?php if (!defined('ABSPATH')) exit; ?>

<div class="settings-section">
    <h3><?php _e('Basic Optimization Settings', 'wp-performance-plus'); ?></h3>
    
    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[minify_html]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['minify_html'] ?? false); ?>>
            <?php _e('Minify HTML', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove unnecessary whitespace and comments from HTML output.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[minify_css]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['minify_css'] ?? false); ?>>
            <?php _e('Minify CSS', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Combine and minify CSS files to reduce file size and HTTP requests.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[minify_js]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['minify_js'] ?? false); ?>>
            <?php _e('Minify JavaScript', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Combine and minify JavaScript files to reduce file size and HTTP requests.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[gzip_compression]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['gzip_compression'] ?? false); ?>>
            <?php _e('Enable GZIP Compression', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Compress website content before sending it to visitors.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[lazy_loading]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['lazy_loading'] ?? false); ?>>
            <?php _e('Enable Lazy Loading', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Load images and iframes only when they enter the viewport.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[emoji_removal]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['emoji_removal'] ?? false); ?>>
            <?php _e('Remove WordPress Emoji Scripts', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove unnecessary emoji scripts to reduce page load time.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[disable_embeds]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['disable_embeds'] ?? false); ?>>
            <?php _e('Disable WordPress Embeds', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Disable the WordPress embed feature to reduce page load time.', 'wp-performance-plus'); ?></p>
    </div>
</div> 