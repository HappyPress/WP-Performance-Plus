<?php if (!defined('ABSPATH')) exit; ?>

<div class="card">
    <h2 class="title"><?php _e('Asset Optimization', 'wp_performanceplus'); ?></h2>
    <div class="inside">
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_performanceplus_settings');
            $options = get_option('wp_performanceplus_settings', array());
            ?>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php _e('Enable Minification', 'wp_performanceplus'); ?>
                    </th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" 
                                   name="wp_performanceplus_settings[enable_minification]" 
                                   value="1" 
                                   <?php checked(1, $options['enable_minification'] ?? 0); ?>>
                            <span class="slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('Automatically minify CSS, JavaScript, and HTML files.', 'wp_performanceplus'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Combine Files', 'wp_performanceplus'); ?>
                    </th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" 
                                   name="wp_performanceplus_settings[combine_files]" 
                                   value="1" 
                                   <?php checked(1, $options['combine_files'] ?? 0); ?>>
                            <span class="slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('Combine multiple CSS and JavaScript files into single files.', 'wp_performanceplus'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Lazy Loading', 'wp_performanceplus'); ?>
                    </th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" 
                                   name="wp_performanceplus_settings[lazy_loading]" 
                                   value="1" 
                                   <?php checked(1, $options['lazy_loading'] ?? 0); ?>>
                            <span class="slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('Delay loading of images and iframes until they enter the viewport.', 'wp_performanceplus'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Changes', 'wp_performanceplus')); ?>
        </form>
    </div>
</div>

<div class="card">
    <h2 class="title"><?php _e('CDN Integration', 'wp_performanceplus'); ?></h2>
    <div class="inside">
        <p><?php _e('Choose a CDN provider from the tabs above to configure content delivery settings.', 'wp_performanceplus'); ?></p>
        
        <div class="cdn-features">
            <div class="feature-card">
                <h3><?php _e('Cloudflare', 'wp_performanceplus'); ?></h3>
                <p><?php _e('Global CDN with advanced security features and DDoS protection.', 'wp_performanceplus'); ?></p>
                <a href="?page=wp_performanceplus-cdn&tab=cloudflare" class="button button-secondary">
                    <?php _e('Configure Cloudflare', 'wp_performanceplus'); ?>
                </a>
            </div>

            <div class="feature-card">
                <h3><?php _e('BunnyCDN', 'wp_performanceplus'); ?></h3>
                <p><?php _e('Affordable CDN with global coverage and simple setup.', 'wp_performanceplus'); ?></p>
                <a href="?page=wp_performanceplus-cdn&tab=bunnycdn" class="button button-secondary">
                    <?php _e('Configure BunnyCDN', 'wp_performanceplus'); ?>
                </a>
            </div>

            <div class="feature-card">
                <h3><?php _e('CloudFront', 'wp_performanceplus'); ?></h3>
                <p><?php _e('Amazon\'s global content delivery network with AWS integration.', 'wp_performanceplus'); ?></p>
                <a href="?page=wp_performanceplus-cdn&tab=cloudfront" class="button button-secondary">
                    <?php _e('Configure CloudFront', 'wp_performanceplus'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.cdn-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.feature-card {
    background: #fff;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.feature-card h3 {
    margin: 0 0 10px;
    font-size: 16px;
}

.feature-card p {
    margin: 0 0 15px;
    color: #646970;
}
</style> 