<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap wp-performance-plus-onboarding">
    <!-- Header -->
    <div class="plugin-header">
        <div class="plugin-title">
            <i class="fas fa-bolt plugin-icon"></i>
            <h1>WP Performance Plus</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Title -->
        <div class="main-title">
            <h2><?php _e('Configure Your Performance Settings', 'wp-performance-plus'); ?></h2>
            <p><?php _e('Get the most out of your WordPress optimization.', 'wp-performance-plus'); ?></p>
        </div>

        <!-- Basics Section -->
        <div class="settings-section">
            <div class="basics-card">
                <div class="basics-header">
                    <div>
                        <h3><?php _e('Basics', 'wp-performance-plus'); ?></h3>
                        <p><?php _e("Let's get you started...", 'wp-performance-plus'); ?></p>
                    </div>
                    <span class="config-status" id="basics-config-status">0/5 Basics Configured</span>
                </div>

                <!-- HTML Minification -->
                <div class="feature-row">
                    <div class="feature-info">
                        <i class="fas fa-code feature-icon"></i>
                        <div class="feature-text">
                            <h4><?php _e('Minify HTML', 'wp-performance-plus'); ?></h4>
                            <p><?php _e('Remove unnecessary whitespace and comments from HTML output.', 'wp-performance-plus'); ?></p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="minify_html" class="basics-toggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- CSS Minification -->
                <div class="feature-row">
                    <div class="feature-info">
                        <i class="fas fa-paint-brush feature-icon"></i>
                        <div class="feature-text">
                            <h4><?php _e('Minify CSS', 'wp-performance-plus'); ?></h4>
                            <p><?php _e('Combine and minify CSS files to reduce file size and HTTP requests.', 'wp-performance-plus'); ?></p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="minify_css" class="basics-toggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- JavaScript Minification -->
                <div class="feature-row">
                    <div class="feature-info">
                        <i class="fas fa-code feature-icon"></i>
                        <div class="feature-text">
                            <h4><?php _e('Minify JavaScript', 'wp-performance-plus'); ?></h4>
                            <p><?php _e('Combine and minify JavaScript files to reduce file size and HTTP requests.', 'wp-performance-plus'); ?></p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="minify_js" class="basics-toggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- GZIP Compression -->
                <div class="feature-row">
                    <div class="feature-info">
                        <i class="fas fa-compress feature-icon"></i>
                        <div class="feature-text">
                            <h4><?php _e('Enable GZIP Compression', 'wp-performance-plus'); ?></h4>
                            <p><?php _e('Compress website content before sending it to visitors.', 'wp-performance-plus'); ?></p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="enable_gzip" class="basics-toggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- WordPress Embeds -->
                <div class="feature-row">
                    <div class="feature-info">
                        <i class="fab fa-wordpress feature-icon"></i>
                        <div class="feature-text">
                            <h4><?php _e('Disable WordPress Embeds', 'wp-performance-plus'); ?></h4>
                            <p><?php _e('Disable the WordPress embed feature to reduce page load time.', 'wp-performance-plus'); ?></p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="disable_embeds" class="basics-toggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Other Settings -->
        <div class="other-settings">
            <!-- Caching -->
            <div class="settings-card">
                <div class="settings-info">
                    <h4><?php _e('Caching', 'wp-performance-plus'); ?></h4>
                    <p><?php _e('Configure your preferred Caching strategy', 'wp-performance-plus'); ?></p>
                </div>
                <button class="configure-button"><?php _e('Configure', 'wp-performance-plus'); ?></button>
            </div>

            <!-- Asset Optimization -->
            <div class="settings-card">
                <div class="settings-info">
                    <h4><?php _e('Asset Optimization', 'wp-performance-plus'); ?></h4>
                    <p><?php _e('Configure minification and compression settings', 'wp-performance-plus'); ?></p>
                </div>
                <button class="configure-button"><?php _e('Configure', 'wp-performance-plus'); ?></button>
            </div>
        </div>

        <!-- Footer -->
        <div class="plugin-footer">
            <p><?php _e("Having trouble? Can't find what you're looking for?", 'wp-performance-plus'); ?></p>
            <button class="contact-button"><?php _e('Contact us', 'wp-performance-plus'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Function to update basics configuration counter
    function updateBasicsCounter() {
        var totalToggles = $('.basics-toggle').length;
        var activeToggles = $('.basics-toggle:checked').length;
        $('#basics-config-status').text(activeToggles + '/' + totalToggles + ' Basics Configured');
    }

    // Update counter when any toggle changes
    $('.basics-toggle').on('change', function() {
        updateBasicsCounter();
    });

    // Initial counter update
    updateBasicsCounter();
});
</script> 