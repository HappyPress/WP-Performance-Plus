<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-performance-plus-settings-content">
    
    <!-- Image Optimization Features Cards -->
    <div class="settings-cards-grid">
        
        <!-- Auto Optimize Images Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-format-image"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Auto Optimize New Images', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Automatically optimize images when uploaded to the media library', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[auto_optimize_images]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['auto_optimize_images'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Resize Large Images Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-image-crop"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Resize Large Images', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Automatically resize images that exceed maximum dimensions', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[resize_large_images]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['resize_large_images'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- WebP Conversion Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-images-alt2"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Convert to WebP', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Create WebP versions of images for modern browsers', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[convert_to_webp]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['convert_to_webp'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Strip Metadata Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <span class="dashicons dashicons-privacy"></span>
                </div>
                <div class="settings-card-title">
                    <h3><?php _e('Strip Image Metadata', 'wp-performance-plus'); ?></h3>
                    <p class="settings-card-description"><?php _e('Remove EXIF and other metadata from images to reduce file size', 'wp-performance-plus'); ?></p>
                </div>
                <div class="settings-card-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="wp_performance_plus_settings[strip_metadata]" value="1" 
                               <?php checked(get_option('wp_performance_plus_settings')['strip_metadata'] ?? false); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Image Settings -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Advanced Image Settings', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Image Dimensions Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="max_image_width" class="settings-field-label">
                            <span class="dashicons dashicons-leftright"></span>
                            <?php _e('Maximum Image Width', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <input type="number" id="max_image_width" name="wp_performance_plus_settings[max_image_width]" 
                                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['max_image_width'] ?? '2560'); ?>" 
                                   min="800" max="4096" class="settings-number-input">
                            <span class="settings-field-unit"><?php _e('pixels', 'wp-performance-plus'); ?></span>
                        </div>
                        <p class="settings-field-description"><?php _e('Maximum width for uploaded images', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>

            <!-- JPEG Quality Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="jpeg_quality" class="settings-field-label">
                            <span class="dashicons dashicons-art"></span>
                            <?php _e('JPEG Quality', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <input type="range" id="jpeg_quality" name="wp_performance_plus_settings[jpeg_quality]" 
                                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['jpeg_quality'] ?? '82'); ?>" 
                                   min="1" max="100" class="settings-range-input">
                            <span class="settings-field-value" id="jpeg_quality_value">
                                <?php echo esc_attr(get_option('wp_performance_plus_settings')['jpeg_quality'] ?? '82'); ?>%
                            </span>
                        </div>
                        <p class="settings-field-description"><?php _e('Quality setting for JPEG image compression. Higher values = better quality but larger file sizes', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Lazy Loading Settings -->
    <div class="settings-section-advanced">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-visibility"></span>
            <?php _e('Advanced Lazy Loading', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-cards-grid">
            
            <!-- Lazy Load iFrames Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span class="dashicons dashicons-admin-page"></span>
                    </div>
                    <div class="settings-card-title">
                        <h3><?php _e('Lazy Load iFrames', 'wp-performance-plus'); ?></h3>
                        <p class="settings-card-description"><?php _e('Load iframes only when they enter the viewport', 'wp-performance-plus'); ?></p>
                    </div>
                    <div class="settings-card-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" name="wp_performance_plus_settings[lazy_load_iframes]" value="1" 
                                   <?php checked(get_option('wp_performance_plus_settings')['lazy_load_iframes'] ?? false); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Lazy Load Threshold Card -->
            <div class="settings-card settings-card-wide">
                <div class="settings-card-body">
                    <div class="settings-field-group">
                        <label for="lazy_load_threshold" class="settings-field-label">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php _e('Loading Threshold', 'wp-performance-plus'); ?>
                        </label>
                        <div class="settings-field-input">
                            <input type="number" id="lazy_load_threshold" name="wp_performance_plus_settings[lazy_load_threshold]" 
                                   value="<?php echo esc_attr(get_option('wp_performance_plus_settings')['lazy_load_threshold'] ?? '200'); ?>" 
                                   min="0" max="1000" class="settings-number-input">
                            <span class="settings-field-unit"><?php _e('pixels', 'wp-performance-plus'); ?></span>
                        </div>
                        <p class="settings-field-description"><?php _e('Load images when they are this many pixels away from entering the viewport', 'wp-performance-plus'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Actions -->
    <div class="settings-section-actions">
        <h2 class="settings-section-title">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Image Optimization Tools', 'wp-performance-plus'); ?>
        </h2>
        
        <div class="settings-actions-grid">
            <button type="button" class="settings-action-btn primary" id="bulk_optimize">
                <span class="dashicons dashicons-format-image"></span>
                <?php _e('Bulk Optimize Images', 'wp-performance-plus'); ?>
                <small><?php _e('Optimize all existing images in the media library', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="generate_webp">
                <span class="dashicons dashicons-images-alt2"></span>
                <?php _e('Generate WebP Versions', 'wp-performance-plus'); ?>
                <small><?php _e('Create WebP versions of existing images', 'wp-performance-plus'); ?></small>
            </button>
            
            <button type="button" class="settings-action-btn secondary" id="image_analysis">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e('Analyze Images', 'wp-performance-plus'); ?>
                <small><?php _e('Show detailed image optimization report', 'wp-performance-plus'); ?></small>
            </button>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Update JPEG quality display
    $('#jpeg_quality').on('input', function() {
        $('#jpeg_quality_value').text($(this).val() + '%');
    });
});
</script> 