<?php
/**
 * Class PerformancePlus_Cloudflare
 * 
 * Manages Cloudflare CDN integration functionality.
 * Handles API authentication, zone management, and cache purging.
 * Provides settings interface for Cloudflare configuration.
 */
class PerformancePlus_Cloudflare {
    /** @var string Option name for storing API token */
    private const OPTION_API_TOKEN = 'performanceplus_cloudflare_api_token';
    
    /** @var string Option name for storing Zone ID */
    private const OPTION_ZONE_ID = 'performanceplus_cloudflare_zone_id';
    
    /** @var string Option name for development mode */
    private const OPTION_DEV_MODE = 'performanceplus_cloudflare_dev_mode';
    
    /** @var string Cloudflare API base URL */
    private const API_BASE_URL = 'https://api.cloudflare.com/client/v4/';

    /**
     * Initialize Cloudflare integration
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_performanceplus_cloudflare_save', [$this, 'save_cloudflare_settings']);
        
        // Add cache purge action
        add_action('performanceplus_purge_cache', [$this, 'purge_cache']);
        
        // Add development mode toggle
        add_action('wp_ajax_toggle_cloudflare_dev_mode', [$this, 'toggle_development_mode']);
    }

    /**
     * Renders the Cloudflare settings interface
     * Displays form for API credentials and zone configuration
     */
    public function render_settings() {
        $api_token = get_option(self::OPTION_API_TOKEN, '');
        $zone_id = get_option(self::OPTION_ZONE_ID, '');
        $dev_mode = get_option(self::OPTION_DEV_MODE, false);
        
        // Check API connection status
        $connection_status = $this->validate_api_credentials();
        ?>
        <div class="cdn-settings-form">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="performanceplus_cloudflare_save">
                <?php wp_nonce_field('performanceplus_cloudflare_save_nonce', '_wpnonce'); ?>
                
                <div class="cdn-provider-settings">
                    <h3><?php esc_html_e('Cloudflare Settings', 'performanceplus'); ?></h3>
                    
                    <?php if (is_wp_error($connection_status)): ?>
                        <div class="notice notice-error">
                            <p><?php echo esc_html($connection_status->get_error_message()); ?></p>
                        </div>
                    <?php elseif ($api_token): ?>
                        <div class="notice notice-success">
                            <p><?php esc_html_e('Successfully connected to Cloudflare!', 'performanceplus'); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cloudflare_api_token">
                                    <?php esc_html_e('API Token', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Create an API token with Zone.Cache Purge and Zone.Settings permissions.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       name="cloudflare_api_token" 
                                       id="cloudflare_api_token" 
                                       value="<?php echo esc_attr($api_token); ?>" 
                                       class="regular-text"
                                       autocomplete="off"
                                >
                                <p class="description">
                                    <?php 
                                    printf(
                                        /* translators: %s: URL to Cloudflare API tokens page */
                                        __('Create your API token in the <a href="%s" target="_blank">Cloudflare dashboard</a>.', 'performanceplus'),
                                        'https://dash.cloudflare.com/profile/api-tokens'
                                    ); 
                                    ?>
                                </p>
                            </td>
                        </tr>
                        
                        <?php if ($api_token): ?>
                        <tr>
                            <th scope="row">
                                <label for="cloudflare_zone_id">
                                    <?php esc_html_e('Zone ID', 'performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Your Zone ID can be found in the Cloudflare dashboard Overview page.', 'performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <?php 
                                $zones = $this->get_available_zones();
                                if (is_wp_error($zones)): 
                                ?>
                                    <p class="description error">
                                        <?php echo esc_html($zones->get_error_message()); ?>
                                    </p>
                                <?php else: ?>
                                    <select name="cloudflare_zone_id" id="cloudflare_zone_id">
                                        <option value=""><?php esc_html_e('Select a domain', 'performanceplus'); ?></option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?php echo esc_attr($zone['id']); ?>" 
                                                    <?php selected($zone_id, $zone['id']); ?>>
                                                <?php echo esc_html($zone['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Development Mode', 'performanceplus'); ?>
                                <span class="dashicons dashicons-editor-help" 
                                      title="<?php esc_attr_e('Temporarily bypass Cloudflare\'s cache. Automatically turns off after 3 hours.', 'performanceplus'); ?>">
                                </span>
                            </th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" 
                                           name="cloudflare_dev_mode" 
                                           id="cloudflare_dev_mode" 
                                           <?php checked($dev_mode); ?>
                                    >
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Enable development mode to bypass cache while making site changes.', 'performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Settings', 'performanceplus'); ?>
                        </button>
                        
                        <?php if ($api_token && $zone_id): ?>
                            <button type="button" class="button" id="purge_cloudflare_cache">
                                <?php esc_html_e('Purge Cache', 'performanceplus'); ?>
                            </button>
                        <?php endif; ?>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Saves Cloudflare settings submitted from the admin form.
     */
    public function save_cloudflare_settings() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'performanceplus_cloudflare_save_nonce')) {
            wp_die(__('Invalid nonce specified', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You are not authorized to perform this action.', 'performanceplus'), __('Error', 'performanceplus'), ['back_link' => true]);
        }

        $api_token = isset($_POST['cloudflare_api_token']) ? sanitize_text_field($_POST['cloudflare_api_token']) : '';
        $zone_id = isset($_POST['cloudflare_zone_id']) ? sanitize_text_field($_POST['cloudflare_zone_id']) : '';
        $dev_mode = isset($_POST['cloudflare_dev_mode']) ? boolval($_POST['cloudflare_dev_mode']) : false;

        update_option(self::OPTION_API_TOKEN, $api_token);
        update_option(self::OPTION_ZONE_ID, $zone_id);
        update_option(self::OPTION_DEV_MODE, $dev_mode);

        wp_redirect(admin_url('admin.php?page=performanceplus-cloudflare&updated=true'));
        exit;
    }

    /**
     * Makes an authenticated request to the Cloudflare API
     * 
     * @param string $endpoint API endpoint path
     * @param array  $args     Request arguments
     * @return array|WP_Error Response data or error
     */
    private function make_api_request($endpoint, $args = []) {
        $api_token = get_option(self::OPTION_API_TOKEN, '');
        
        if (empty($api_token)) {
            return new WP_Error('missing_token', __('API token is required.', 'performanceplus'));
        }

        $defaults = [
            'method'  => 'GET',
            'headers' => [
                'Authorization' => 'Bearer ' . $api_token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];

        $args = wp_parse_args($args, $defaults);
        $url = self::API_BASE_URL . ltrim($endpoint, '/');
        
        if (!empty($args['body']) && is_array($args['body'])) {
            $args['body'] = wp_json_encode($args['body']);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['success'])) {
            $message = !empty($data['errors'][0]['message']) 
                ? $data['errors'][0]['message'] 
                : __('Unknown API error occurred.', 'performanceplus');
            return new WP_Error('api_error', $message);
        }

        return $data['result'];
    }

    /**
     * Retrieves available zones from Cloudflare
     * 
     * @return array|WP_Error List of zones or error
     */
    public function get_available_zones() {
        $response = $this->make_api_request('zones', [
            'per_page' => 50,
            'status' => 'active',
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return array_map(function($zone) {
            return [
                'id' => $zone['id'],
                'name' => $zone['name'],
            ];
        }, $response);
    }

    /**
     * Purges Cloudflare cache for the zone
     * 
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function purge_cache() {
        $zone_id = get_option(self::OPTION_ZONE_ID, '');
        
        if (empty($zone_id)) {
            return new WP_Error('missing_zone', __('Zone ID is required.', 'performanceplus'));
        }

        $response = $this->make_api_request("zones/{$zone_id}/purge_cache", [
            'method' => 'POST',
            'body' => [
                'purge_everything' => true,
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        do_action('performanceplus_after_cache_purge', 'cloudflare');
        return true;
    }

    /**
     * Toggles development mode for the zone
     * 
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function toggle_development_mode() {
        check_ajax_referer('performanceplus_cloudflare_dev_mode', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'performanceplus'));
        }

        $zone_id = get_option(self::OPTION_ZONE_ID, '');
        $dev_mode = get_option(self::OPTION_DEV_MODE, false);
        
        $response = $this->make_api_request("zones/{$zone_id}/settings/development_mode", [
            'method' => 'PATCH',
            'body' => [
                'value' => $dev_mode ? 'off' : 'on',
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        update_option(self::OPTION_DEV_MODE, !$dev_mode);
        wp_send_json_success([
            'dev_mode' => !$dev_mode,
            'message' => !$dev_mode 
                ? __('Development mode enabled. Will automatically disable in 3 hours.', 'performanceplus')
                : __('Development mode disabled.', 'performanceplus'),
        ]);
    }

    /**
     * Validates API credentials with Cloudflare
     * Tests connection and authentication with the API
     * 
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_api_credentials() {
        $api_token = get_option(self::OPTION_API_TOKEN, '');
        
        if (empty($api_token)) {
            return new WP_Error('missing_credentials', __('API token is not configured.', 'performanceplus'));
        }

        $response = $this->make_api_request('user/tokens/verify');
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'invalid_token', 
                __('Failed to validate API token. Please check your credentials.', 'performanceplus')
            );
        }

        return true;
    }
}

// Initialize Cloudflare settings management
// new PerformancePlus_Cloudflare();
?>
