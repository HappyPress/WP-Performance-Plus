<?php
/**
 * Class WP_Performance_Plus_Cloudflare
 * 
 * Manages Cloudflare CDN integration functionality.
 * Handles API authentication, zone management, and cache purging.
 * Provides settings interface for Cloudflare configuration.
 */
class WP_Performance_Plus_Cloudflare {
    /** @var string Option name for storing API token */
    private const OPTION_API_TOKEN = 'wp_performance_plus_cloudflare_api_token';
    
    /** @var string Option name for storing Zone ID */
    private const OPTION_ZONE_ID = 'wp_performance_plus_cloudflare_zone_id';
    
    /** @var string Option name for development mode */
    private const OPTION_DEV_MODE = 'wp_performance_plus_cloudflare_dev_mode';
    
    /** @var string Cloudflare API base URL */
    private const API_BASE_URL = 'https://api.cloudflare.com/client/v4/';

    /**
     * Initialize Cloudflare integration
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_wp_performance_plus_cloudflare_save', [$this, 'save_cloudflare_settings']);
        
        // Add cache purge action
        add_action('wp_performance_plus_purge_cache', [$this, 'purge_cache']);
        
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
                <input type="hidden" name="action" value="wp_performance_plus_cloudflare_save">
                <?php wp_nonce_field('wp_performance_plus_cloudflare_save_nonce', '_wpnonce'); ?>
                
                <div class="cdn-provider-settings">
                    <h3><?php esc_html_e('Cloudflare Settings', 'wp-performance-plus'); ?></h3>
                    
                    <?php if (is_wp_error($connection_status)): ?>
                        <div class="notice notice-error">
                            <p><?php echo esc_html($connection_status->get_error_message()); ?></p>
                        </div>
                    <?php elseif ($api_token): ?>
                        <div class="notice notice-success">
                            <p><?php esc_html_e('Successfully connected to Cloudflare!', 'wp-performance-plus'); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cloudflare_api_token">
                                    <?php esc_html_e('API Token', 'wp-performance-plus'); ?>
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
                            </td>
                        </tr>
                        
                        <?php if ($api_token): ?>
                        <tr>
                            <th scope="row">
                                <label for="cloudflare_zone_id">
                                    <?php esc_html_e('Zone ID', 'wp-performance-plus'); ?>
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
                                        <option value=""><?php esc_html_e('Select a domain', 'wp-performance-plus'); ?></option>
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
                                <?php esc_html_e('Development Mode', 'wp-performance-plus'); ?>
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
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Settings', 'wp-performance-plus'); ?>
                        </button>
                        
                        <?php if ($api_token && $zone_id): ?>
                            <button type="button" class="button" id="purge_cloudflare_cache">
                                <?php esc_html_e('Purge Cache', 'wp-performance-plus'); ?>
                            </button>
                        <?php endif; ?>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Validates the API credentials
     */
    public function validate_api_credentials() {
        return $this->make_api_request('user/tokens/verify');
    }

    /**
     * Makes an authenticated request to the Cloudflare API
     */
    private function make_api_request($endpoint, $args = []) {
        $api_token = get_option(self::OPTION_API_TOKEN, '');
        
        if (empty($api_token)) {
            return new WP_Error('missing_token', __('API token is required.', 'wp-performance-plus'));
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
                : __('Unknown API error occurred.', 'wp-performance-plus');
            return new WP_Error('api_error', $message);
        }

        return $data['result'];
    }

    /**
     * Retrieves available zones from Cloudflare
     */
    public function get_available_zones() {
        return $this->make_api_request('zones', ['per_page' => 50]);
    }

    /**
     * Purges the Cloudflare cache
     */
    public function purge_cache() {
        $zone_id = get_option(self::OPTION_ZONE_ID);
        if (empty($zone_id)) {
            return new WP_Error('missing_zone', __('Zone ID is required.', 'wp-performance-plus'));
        }

        return $this->make_api_request("zones/{$zone_id}/purge_cache", [
            'method' => 'POST',
            'body' => ['purge_everything' => true]
        ]);
    }

    /**
     * Toggles development mode
     */
    public function toggle_development_mode() {
        check_ajax_referer('wp_performance_plus_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-performance-plus'));
        }

        $zone_id = get_option(self::OPTION_ZONE_ID);
        if (empty($zone_id)) {
            wp_send_json_error(__('Zone ID is required.', 'wp-performance-plus'));
        }

        $dev_mode = get_option(self::OPTION_DEV_MODE, false);
        $new_value = !$dev_mode;

        $result = $this->make_api_request("zones/{$zone_id}/settings/development_mode", [
            'method' => 'PATCH',
            'body' => ['value' => $new_value ? 'on' : 'off']
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        update_option(self::OPTION_DEV_MODE, $new_value);
        wp_send_json_success(['dev_mode' => $new_value]);
    }
}

// Initialize Cloudflare settings management
// new WP_Performance_Plus_Cloudflare();
?>
