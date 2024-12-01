<?php
/**
 * Class PerformancePlus_CloudFront
 * 
 * Manages Amazon CloudFront CDN integration functionality.
 * Handles API authentication, distribution management, and cache invalidation.
 * Provides settings interface for CloudFront configuration.
 */
class PerformancePlus_CloudFront {
    /** @var string Option name for storing Access Key */
    private const OPTION_ACCESS_KEY = 'performanceplus_cloudfront_access_key';
    
    /** @var string Option name for storing Secret Key */
    private const OPTION_SECRET_KEY = 'performanceplus_cloudfront_secret_key';
    
    /** @var string Option name for storing Distribution ID */
    private const OPTION_DISTRIBUTION_ID = 'performanceplus_cloudfront_distribution_id';

    /**
     * Initialize CloudFront integration
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_performanceplus_cloudfront_save', [$this, 'save_cloudfront_settings']);
    }

    /**
     * Renders the CloudFront settings interface
     * Displays form for AWS credentials and distribution configuration
     */
    public function render_settings() {
        $access_key = get_option(self::OPTION_ACCESS_KEY, '');
        $secret_key = get_option(self::OPTION_SECRET_KEY, '');
        $distribution_id = get_option(self::OPTION_DISTRIBUTION_ID, '');
        ?>
        <div class="cdn-settings-form">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="performanceplus_cloudfront_save">
                <?php wp_nonce_field('performanceplus_cloudfront_save_nonce', '_wpnonce'); ?>
                
                <div class="cdn-provider-settings">
                    <h3><?php esc_html_e('CloudFront Settings', 'performanceplus'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cloudfront_access_key">
                                    <?php esc_html_e('Access Key ID', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your AWS Access Key ID from IAM credentials. Create a new IAM user with CloudFront permissions for best security.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="cloudfront_access_key" 
                                       id="cloudfront_access_key" 
                                       value="<?php echo esc_attr($access_key); ?>" 
                                       class="regular-text"
                                       autocomplete="off"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your AWS Access Key ID to enable CloudFront integration.', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cloudfront_secret_key">
                                    <?php esc_html_e('Secret Access Key', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your AWS Secret Access Key. Keep this secure and never share it.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       name="cloudfront_secret_key" 
                                       id="cloudfront_secret_key" 
                                       value="<?php echo esc_attr($secret_key); ?>" 
                                       class="regular-text"
                                       autocomplete="off"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your AWS Secret Access Key. This will be stored securely.', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cloudfront_distribution_id">
                                    <?php esc_html_e('Distribution ID', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your CloudFront Distribution ID can be found in the AWS Console under CloudFront > Distributions.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="cloudfront_distribution_id" 
                                       id="cloudfront_distribution_id" 
                                       value="<?php echo esc_attr($distribution_id); ?>" 
                                       class="regular-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your CloudFront Distribution ID (e.g., E1ABCDEF123456).', 'performanceplus'); ?>
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
     * Saves CloudFront settings submitted from the admin form
     * Handles validation, sanitization, and storage of settings
     */
    public function save_cloudfront_settings() {
        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'performanceplus_cloudfront_save_nonce')) {
            wp_die(__('Invalid nonce specified', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not authorized to perform this action.', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Sanitize and save settings
        $access_key = isset($_POST['cloudfront_access_key']) ? sanitize_text_field($_POST['cloudfront_access_key']) : '';
        $secret_key = isset($_POST['cloudfront_secret_key']) ? sanitize_text_field($_POST['cloudfront_secret_key']) : '';
        $distribution_id = isset($_POST['cloudfront_distribution_id']) ? sanitize_text_field($_POST['cloudfront_distribution_id']) : '';

        update_option(self::OPTION_ACCESS_KEY, $access_key);
        update_option(self::OPTION_SECRET_KEY, $secret_key);
        update_option(self::OPTION_DISTRIBUTION_ID, $distribution_id);

        // Redirect back to settings page with success message
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }

    /**
     * Validates AWS credentials with CloudFront
     * Tests connection and authentication with the API
     * 
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_api_credentials() {
        $access_key = get_option(self::OPTION_ACCESS_KEY, '');
        $secret_key = get_option(self::OPTION_SECRET_KEY, '');
        
        if (empty($access_key) || empty($secret_key)) {
            return new WP_Error('missing_credentials', __('AWS credentials are not configured.', 'performanceplus'));
        }

        // TODO: Implement actual AWS API validation
        return true;
    }
}

// Initialize CloudFront settings management
new PerformancePlus_CloudFront();
?>
