<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$plugin_dir_url = plugin_dir_url(dirname(dirname(__FILE__)));
?>

<div class="wrap wp-performance-plus-onboarding">
    <div class="onboarding-header">
        <div class="brand">
            <img src="<?php echo esc_url($plugin_dir_url . 'admin/img/logo.svg'); ?>" alt="WP Performance Plus" class="logo">
        </div>
        <div class="language-selector">
            <select>
                <option value="en">🇬🇧 English</option>
            </select>
        </div>
    </div>

    <div class="onboarding-container">
        <div class="main-content">
            <h1><?php _e('Configure Your Performance Settings', 'wp-performance-plus'); ?></h1>
            <p class="subtitle"><?php _e('Get the most out of your WordPress optimization.', 'wp-performance-plus'); ?></p>

            <!-- Cache Configuration Section -->
            <div class="connection-section">
                <div class="connection-header">
                    <img src="<?php echo esc_url($plugin_dir_url . 'admin/img/cache-icon.svg'); ?>" alt="" class="connection-icon">
                    <div class="connection-info">
                        <h3><?php _e('Cache Configuration', 'wp-performance-plus'); ?></h3>
                        <p><?php _e('Stay in sync with page caching and browser optimization', 'wp-performance-plus'); ?></p>
                    </div>
                    <span class="connection-status connected"><?php _e('1 service connected', 'wp-performance-plus'); ?></span>
                    <button class="revoke-access"><?php _e('Revoke access', 'wp-performance-plus'); ?></button>
                </div>

                <div class="connected-account">
                    <img src="<?php echo esc_url($plugin_dir_url . 'admin/img/cache-icon.svg'); ?>" alt="" class="service-icon">
                    <span class="account-email">Page Cache</span>
                    <span class="connection-badge"><?php _e('Connected', 'wp-performance-plus'); ?></span>
                </div>

                <button class="connect-another">+ <?php _e('Enable another service', 'wp-performance-plus'); ?></button>
            </div>

            <!-- CDN Integration Section -->
            <div class="connection-section">
                <div class="connection-header">
                    <img src="<?php echo esc_url($plugin_dir_url . 'admin/img/cdn-icon.svg'); ?>" alt="" class="connection-icon">
                    <div class="connection-info">
                        <h3><?php _e('CDN Integration', 'wp-performance-plus'); ?></h3>
                        <p><?php _e('Connect your preferred Content Delivery Network', 'wp-performance-plus'); ?></p>
                    </div>
                    <button class="connect-service"><?php _e('Connect', 'wp-performance-plus'); ?></button>
                </div>
            </div>

            <!-- Asset Optimization Section -->
            <div class="connection-section">
                <div class="connection-header">
                    <img src="<?php echo esc_url($plugin_dir_url . 'admin/img/optimization-icon.svg'); ?>" alt="" class="connection-icon">
                    <div class="connection-info">
                        <h3><?php _e('Asset Optimization', 'wp-performance-plus'); ?></h3>
                        <p><?php _e('Configure minification and compression settings', 'wp-performance-plus'); ?></p>
                    </div>
                    <button class="connect-service"><?php _e('Configure', 'wp-performance-plus'); ?></button>
                </div>
            </div>

            <div class="help-text">
                <p><?php _e("Can't find what you're looking for? You can configure additional services at any time.", 'wp-performance-plus'); ?></p>
            </div>
        </div>

        <div class="progress-timeline">
            <div class="timeline-step completed">
                <div class="step-indicator">✓</div>
                <div class="step-content">
                    <h4><?php _e('Choose optimization level', 'wp-performance-plus'); ?></h4>
                    <p><?php _e('What level of optimization do you need', 'wp-performance-plus'); ?></p>
                </div>
            </div>

            <div class="timeline-step completed">
                <div class="step-indicator">✓</div>
                <div class="step-content">
                    <h4><?php _e('Configure caching', 'wp-performance-plus'); ?></h4>
                    <p><?php _e('Set up your caching strategy', 'wp-performance-plus'); ?></p>
                </div>
            </div>

            <div class="timeline-step active">
                <div class="step-indicator">3</div>
                <div class="step-content">
                    <h4><?php _e('Connect services', 'wp-performance-plus'); ?></h4>
                    <p><?php _e('CDN, optimization, and more', 'wp-performance-plus'); ?></p>
                </div>
            </div>

            <div class="timeline-step">
                <div class="step-indicator">4</div>
                <div class="step-content">
                    <h4><?php _e('Add site URL', 'wp-performance-plus'); ?></h4>
                    <p><?php _e('Configure domain settings', 'wp-performance-plus'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="onboarding-footer">
        <div class="help-section">
            <h4><?php _e('Having trouble?', 'wp-performance-plus'); ?></h4>
            <p><?php _e('Feel free to contact us and we will always help you through the process.', 'wp-performance-plus'); ?></p>
            <button class="contact-support"><?php _e('Contact us', 'wp-performance-plus'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.connect-service').on('click', function() {
        // Add your connection logic here
    });

    $('.revoke-access').on('click', function() {
        // Add your revoke access logic here
    });

    $('.connect-another').on('click', function() {
        // Add your logic to show additional service options
    });
});
</script> 