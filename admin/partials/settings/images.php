<?php if (!defined('ABSPATH')) exit; ?>

<div class="settings-section">
    <h3><?php _e('Image Optimization Settings', 'wp-performance-plus'); ?></h3>
    
    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[auto_optimize_images]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['auto_optimize_images'] ?? false); ?>>
            <?php _e('Automatically Optimize New Images', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Optimize images automatically when uploaded to the media library.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[resize_large_images]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['resize_large_images'] ?? false); ?>>
            <?php _e('Resize Large Images', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Automatically resize images that exceed maximum dimensions.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="max_image_width"><?php _e('Maximum Image Width (pixels)', 'wp-performance-plus'); ?></label>
        <input type="number" id="max_image_width" name="wp_performance_plus_settings[max_image_width]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['max_image_width'] ?? '2560'); ?>" 
               min="800" max="4096" class="small-text">
        <p class="description"><?php _e('Maximum width for uploaded images.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label for="jpeg_quality"><?php _e('JPEG Quality (%)', 'wp-performance-plus'); ?></label>
        <input type="number" id="jpeg_quality" name="wp_performance_plus_settings[jpeg_quality]" 
               value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['jpeg_quality'] ?? '82'); ?>" 
               min="1" max="100" class="small-text">
        <p class="description"><?php _e('Quality setting for JPEG image compression (default: 82).', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[convert_to_webp]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['convert_to_webp'] ?? false); ?>>
            <?php _e('Convert Images to WebP', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Create WebP versions of images for modern browsers.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[strip_metadata]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['strip_metadata'] ?? false); ?>>
            <?php _e('Strip Image Metadata', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Remove EXIF and other metadata from images to reduce file size.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <h4><?php _e('Lazy Loading Settings', 'wp-performance-plus'); ?></h4>
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[lazy_load_images]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['lazy_load_images'] ?? false); ?>>
            <?php _e('Enable Lazy Loading for Images', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Load images only when they enter the viewport.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <label>
            <input type="checkbox" name="wp_performance_plus_settings[lazy_load_iframes]" value="1" 
                   <?php checked(get_option('wp_performance_plus_settings')['lazy_load_iframes'] ?? false); ?>>
            <?php _e('Enable Lazy Loading for iFrames', 'wp-performance-plus'); ?>
        </label>
        <p class="description"><?php _e('Load iframes only when they enter the viewport.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="settings-group">
        <button type="button" class="button button-secondary" id="bulk_optimize">
            <?php _e('Bulk Optimize Images', 'wp-performance-plus'); ?>
        </button>
        <p class="description"><?php _e('Optimize all existing images in the media library.', 'wp-performance-plus'); ?></p>
    </div>
</div> 