<?php if (!defined('ABSPATH')) exit; ?>

<div class="settings-section">
    <h3><?php _e('CDN Integration Settings', 'wp-performance-plus'); ?></h3>
    
    <div class="settings-group">
        <label for="cdn_provider"><?php _e('CDN Provider', 'wp-performance-plus'); ?></label>
        <select id="cdn_provider" name="wp_performance_plus_settings[cdn_provider]" class="regular-text">
            <option value=""><?php _e('None', 'wp-performance-plus'); ?></option>
            <option value="cloudflare" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'cloudflare'); ?>>
                <?php _e('Cloudflare', 'wp-performance-plus'); ?>
            </option>
            <option value="keycdn" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'keycdn'); ?>>
                <?php _e('KeyCDN', 'wp-performance-plus'); ?>
            </option>
            <option value="bunnycdn" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'bunnycdn'); ?>>
                <?php _e('BunnyCDN', 'wp-performance-plus'); ?>
            </option>
            <option value="cloudfront" <?php selected(get_option('wp_performance_plus_settings')['cdn_provider'] ?? '', 'cloudfront'); ?>>
                <?php _e('Amazon CloudFront', 'wp-performance-plus'); ?>
            </option>
        </select>
        <p class="description"><?php _e('Select your CDN provider to enable integration.', 'wp-performance-plus'); ?></p>
    </div>

    <div class="cdn-settings-wrapper">
        <!-- Cloudflare Settings -->
        <div id="cloudflare-settings" class="cdn-provider-settings" style="display: none;">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'cdn-providers/cloudflare.php'; ?>
        </div>

        <!-- KeyCDN Settings -->
        <div id="keycdn-settings" class="cdn-provider-settings" style="display: none;">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'cdn-providers/keycdn.php'; ?>
        </div>

        <!-- BunnyCDN Settings -->
        <div id="bunnycdn-settings" class="cdn-provider-settings" style="display: none;">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'cdn-providers/bunnycdn.php'; ?>
        </div>

        <!-- CloudFront Settings -->
        <div id="cloudfront-settings" class="cdn-provider-settings" style="display: none;">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'cdn-providers/cloudfront.php'; ?>
        </div>
    </div>

    <div class="settings-group">
        <h4><?php _e('General CDN Settings', 'wp-performance-plus'); ?></h4>
        
        <div class="settings-group">
            <label>
                <input type="checkbox" name="wp_performance_plus_settings[cdn_enable_ssl]" value="1" 
                       <?php checked(get_option('wp_performance_plus_settings')['cdn_enable_ssl'] ?? false); ?>>
                <?php _e('Enable SSL', 'wp-performance-plus'); ?>
            </label>
            <p class="description"><?php _e('Use HTTPS for CDN URLs.', 'wp-performance-plus'); ?></p>
        </div>

        <div class="settings-group">
            <label>
                <input type="checkbox" name="wp_performance_plus_settings[cdn_relative_path]" value="1" 
                       <?php checked(get_option('wp_performance_plus_settings')['cdn_relative_path'] ?? false); ?>>
                <?php _e('Use Relative Path', 'wp-performance-plus'); ?>
            </label>
            <p class="description"><?php _e('Use relative URLs for CDN resources.', 'wp-performance-plus'); ?></p>
        </div>

        <div class="settings-group">
            <h4><?php _e('CDN File Types', 'wp-performance-plus'); ?></h4>
            <label>
                <input type="checkbox" name="wp_performance_plus_settings[cdn_images]" value="1" 
                       <?php checked(get_option('wp_performance_plus_settings')['cdn_images'] ?? true); ?>>
                <?php _e('Images (jpg, jpeg, png, gif, webp)', 'wp-performance-plus'); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wp_performance_plus_settings[cdn_js]" value="1" 
                       <?php checked(get_option('wp_performance_plus_settings')['cdn_js'] ?? true); ?>>
                <?php _e('JavaScript Files', 'wp-performance-plus'); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wp_performance_plus_settings[cdn_css]" value="1" 
                       <?php checked(get_option('wp_performance_plus_settings')['cdn_css'] ?? true); ?>>
                <?php _e('CSS Files', 'wp-performance-plus'); ?>
            </label><br>
            <label>
                <input type="checkbox" name="wp_performance_plus_settings[cdn_fonts]" value="1" 
                       <?php checked(get_option('wp_performance_plus_settings')['cdn_fonts'] ?? true); ?>>
                <?php _e('Font Files (woff, woff2, ttf, eot, otf)', 'wp-performance-plus'); ?>
            </label>
        </div>

        <div class="settings-group">
            <h4><?php _e('Exclusions', 'wp-performance-plus'); ?></h4>
            <textarea name="wp_performance_plus_settings[cdn_exclusions]" rows="4" class="large-text code" 
                      placeholder="<?php esc_attr_e('e.g., .php, .html, admin/, wp-includes/', 'wp-performance-plus'); ?>"
            ><?php echo esc_textarea(get_option('wp_performance_plus_settings')['cdn_exclusions'] ?? ''); ?></textarea>
            <p class="description"><?php _e('Enter file types or paths to exclude from CDN (one per line).', 'wp-performance-plus'); ?></p>
        </div>
    </div>

    <div class="settings-group">
        <button type="button" class="button button-secondary" id="test_cdn">
            <?php _e('Test CDN Connection', 'wp-performance-plus'); ?>
        </button>
        <button type="button" class="button button-secondary" id="purge_cdn">
            <?php _e('Purge CDN Cache', 'wp-performance-plus'); ?>
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function toggleCDNSettings() {
        var provider = $('#cdn_provider').val();
        $('.cdn-provider-settings').hide();
        if (provider) {
            $('#' + provider + '-settings').show();
        }
    }

    $('#cdn_provider').on('change', toggleCDNSettings);
    toggleCDNSettings();
});
</script> 