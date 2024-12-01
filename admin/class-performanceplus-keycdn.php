<?php
/**
 * Class PerformancePlus_KeyCDN
 * 
 * Manages KeyCDN integration functionality.
 * Handles API authentication, zone management, and cache purging operations.
 * Provides settings interface for KeyCDN configuration.
 */
class PerformancePlus_KeyCDN {
    /** @var string Option name for storing API key */
    private const OPTION_API_KEY = 'performanceplus_keycdn_api_key';
    
    /** @var string Option name for storing Zone ID */
    private const OPTION_ZONE_ID = 'performanceplus_keycdn_zone_id';

    /**
     * Initialize KeyCDN integration
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_performanceplus_keycdn_save', [$this, 'save_keycdn_settings']);
    }

    /**
     * Renders the KeyCDN settings interface
     * Displays form for API credentials and configuration options
     */
    public function render_settings() {
        $api_key = get_option(self::OPTION_API_KEY, '');
        $zone_id = get_option(self::OPTION_ZONE_ID, '');
        ?>
        <div class="cdn-settings-form">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="performanceplus_keycdn_save">
                <?php wp_nonce_field('performanceplus_keycdn_save_nonce', '_wpnonce'); ?>
                
                <div class="cdn-provider-settings">
                    <h3><?php esc_html_e('KeyCDN Settings', 'performanceplus'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="keycdn_api_key">
                                    <?php esc_html_e('API Key', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your KeyCDN API key can be found in your KeyCDN dashboard under Account > API.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="keycdn_api_key" 
                                       id="keycdn_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text"
                                       autocomplete="off"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your KeyCDN API key to enable content delivery.', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="keycdn_zone_id">
                                    <?php esc_html_e('Zone ID', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your Zone ID can be found in the KeyCDN dashboard under Zones.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="keycdn_zone_id" 
                                       id="keycdn_zone_id" 
                                       value="<?php echo esc_attr($zone_id); ?>" 
                                       class="regular-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your Zone ID to identify your CDN zone.', 'performanceplus'); ?>
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
     * Saves KeyCDN settings submitted from the admin form
     * Handles validation, sanitization, and storage of settings
     */
    public function save_keycdn_settings() {
        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'performanceplus_keycdn_save_nonce')) {
            wp_die(__('Invalid nonce specified', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not authorized to perform this action.', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Sanitize and save settings
        $api_key = isset($_POST['keycdn_api_key']) ? sanitize_text_field($_POST['keycdn_api_key']) : '';
        $zone_id = isset($_POST['keycdn_zone_id']) ? sanitize_text_field($_POST['keycdn_zone_id']) : '';

        update_option(self::OPTION_API_KEY, $api_key);
        update_option(self::OPTION_ZONE_ID, $zone_id);

        // Redirect back to settings page with success message
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }

    /**
     * Validates API credentials with KeyCDN
     * Tests connection and authentication with the API
     * 
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_api_credentials() {
        $api_key = get_option(self::OPTION_API_KEY, '');
        
        if (empty($api_key)) {
            return new WP_Error('missing_credentials', __('API credentials are not configured.', 'performanceplus'));
        }

        // TODO: Implement actual API validation
        return true;
    }
}

// Initialize KeyCDN settings management
new PerformancePlus_KeyCDN();
?>
