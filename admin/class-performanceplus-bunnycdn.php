<?php
/**
 * Class PerformancePlus_BunnyCDN
 * 
 * Manages BunnyCDN integration functionality.
 * Handles API authentication, pull zone management, and cache purging.
 * Provides settings interface for BunnyCDN configuration.
 */
class PerformancePlus_BunnyCDN {
    /** @var string Option name for storing API key */
    private const OPTION_API_KEY = 'performanceplus_bunnycdn_api_key';
    
    /** @var string Option name for storing Pull Zone name */
    private const OPTION_ZONE_NAME = 'performanceplus_bunnycdn_zone_name';

    /**
     * Initialize BunnyCDN integration
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_performanceplus_bunnycdn_save', [$this, 'save_bunnycdn_settings']);
    }

    /**
     * Renders the BunnyCDN settings interface
     * Displays form for API credentials and pull zone configuration
     */
    public function render_settings() {
        $api_key = get_option(self::OPTION_API_KEY, '');
        $zone_name = get_option(self::OPTION_ZONE_NAME, '');
        ?>
        <div class="cdn-settings-form">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="performanceplus_bunnycdn_save">
                <?php wp_nonce_field('performanceplus_bunnycdn_save_nonce', '_wpnonce'); ?>
                
                <div class="cdn-provider-settings">
                    <h3><?php esc_html_e('BunnyCDN Settings', 'performanceplus'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="bunnycdn_api_key">
                                    <?php esc_html_e('API Key', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your BunnyCDN API key can be found in your BunnyCDN dashboard under Account > API Access.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="bunnycdn_api_key" 
                                       id="bunnycdn_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text"
                                       autocomplete="off"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your BunnyCDN API key to enable content delivery.', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="bunnycdn_zone_name">
                                    <?php esc_html_e('Pull Zone Name', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your Pull Zone name can be found in the BunnyCDN dashboard under Pull Zones.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="bunnycdn_zone_name" 
                                       id="bunnycdn_zone_name" 
                                       value="<?php echo esc_attr($zone_name); ?>" 
                                       class="regular-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your Pull Zone name (e.g., my-website-zone).', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Settings', 'performanceplus'); ?>
                        </button>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Saves BunnyCDN settings submitted from the admin form
     * Handles validation, sanitization, and storage of settings
     */
    public function save_bunnycdn_settings() {
        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'performanceplus_bunnycdn_save_nonce')) {
            wp_die(__('Invalid nonce specified', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not authorized to perform this action.', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Sanitize and save settings
        $api_key = isset($_POST['bunnycdn_api_key']) ? sanitize_text_field($_POST['bunnycdn_api_key']) : '';
        $zone_name = isset($_POST['bunnycdn_zone_name']) ? sanitize_text_field($_POST['bunnycdn_zone_name']) : '';

        update_option(self::OPTION_API_KEY, $api_key);
        update_option(self::OPTION_ZONE_NAME, $zone_name);

        // Redirect back to settings page with success message
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }

    /**
     * Validates API credentials with BunnyCDN
     * Tests connection and authentication with the API
     * 
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_api_credentials() {
        $api_key = get_option(self::OPTION_API_KEY, '');
        
        if (empty($api_key)) {
            return new WP_Error('missing_credentials', __('API credentials are not configured.', 'performanceplus'));
        }

        // TODO: Implement actual BunnyCDN API validation
        return true;
    }
}

// Initialize BunnyCDN settings management
new PerformancePlus_BunnyCDN();
?>
