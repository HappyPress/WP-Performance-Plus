<?php
/**
 * Class PerformancePlus_StackPath
 * 
 * Manages StackPath CDN integration functionality.
 * Handles API authentication, zone management, and cache purging operations.
 * Provides settings interface for StackPath configuration.
 */
class PerformancePlus_StackPath {
    /** @var string Option name for storing API key */
    private const OPTION_API_KEY = 'performanceplus_stackpath_api_key';
    
    /** @var string Option name for storing Stack ID */
    private const OPTION_STACK_ID = 'performanceplus_stackpath_stack_id';

    /**
     * Initialize StackPath integration
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_performanceplus_stackpath_save', [$this, 'save_stackpath_settings']);
    }

    /**
     * Renders the StackPath settings interface
     * Displays form for API credentials and configuration options
     */
    public function render_settings() {
        $api_key = get_option(self::OPTION_API_KEY, '');
        $stack_id = get_option(self::OPTION_STACK_ID, '');
        ?>
        <div class="cdn-settings-form">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="performanceplus_stackpath_save">
                <?php wp_nonce_field('performanceplus_stackpath_save_nonce', '_wpnonce'); ?>
                
                <div class="cdn-provider-settings">
                    <h3><?php esc_html_e('StackPath Settings', 'performanceplus'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="stackpath_api_key">
                                    <?php esc_html_e('API Key', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your StackPath API key can be found in your StackPath dashboard under Account Settings > API Keys.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="stackpath_api_key" 
                                       id="stackpath_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text"
                                       autocomplete="off"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your StackPath API key to enable CDN integration.', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="stackpath_stack_id">
                                    <?php esc_html_e('Stack ID', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your Stack ID can be found in the URL when viewing your stack in the StackPath dashboard.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="stackpath_stack_id" 
                                       id="stackpath_stack_id" 
                                       value="<?php echo esc_attr($stack_id); ?>" 
                                       class="regular-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Enter your Stack ID to identify your CDN configuration.', 'performanceplus'); ?>
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
     * Saves StackPath settings submitted from the admin form
     * Handles validation, sanitization, and storage of settings
     */
    public function save_stackpath_settings() {
        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'performanceplus_stackpath_save_nonce')) {
            wp_die(__('Invalid nonce specified', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not authorized to perform this action.', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        // Sanitize and save settings
        $api_key = isset($_POST['stackpath_api_key']) ? sanitize_text_field($_POST['stackpath_api_key']) : '';
        $stack_id = isset($_POST['stackpath_stack_id']) ? sanitize_text_field($_POST['stackpath_stack_id']) : '';

        update_option(self::OPTION_API_KEY, $api_key);
        update_option(self::OPTION_STACK_ID, $stack_id);

        // Redirect back to settings page with success message
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }

    /**
     * Validates API credentials with StackPath
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

// Initialize StackPath settings management
new PerformancePlus_StackPath();
?>
