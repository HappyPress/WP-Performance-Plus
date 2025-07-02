<?php
/**
 * Abstract base class for CDN provider implementations
 * 
 * Provides a standardized interface for all CDN providers including
 * API authentication, cache management, URL rewriting, and settings integration.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */
abstract class WP_Performance_Plus_CDN_Provider {
    
    /**
     * CDN provider name
     * @var string
     */
    protected $provider_name;
    
    /**
     * Provider settings
     * @var array
     */
    protected $settings;
    
    /**
     * API base URL
     * @var string
     */
    protected $api_base_url;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->provider_name = $this->get_provider_name();
        $this->settings = $this->get_provider_settings();
        $this->api_base_url = $this->get_api_base_url();
    }
    
    /**
     * Get provider name
     * @return string
     */
    abstract protected function get_provider_name();
    
    /**
     * Get API base URL
     * @return string
     */
    abstract protected function get_api_base_url();
    
    /**
     * Validate API credentials
     * @return bool|WP_Error
     */
    abstract public function validate_credentials();
    
    /**
     * Purge all cache
     * @return bool|WP_Error
     */
    abstract public function purge_all_cache();
    
    /**
     * Purge specific URLs
     * @param array $urls URLs to purge
     * @return bool|WP_Error
     */
    abstract public function purge_urls($urls);
    
    /**
     * Get zones/domains available for this account
     * @return array|WP_Error
     */
    abstract public function get_zones();
    
    /**
     * Get CDN statistics
     * @return array|WP_Error
     */
    abstract public function get_statistics();
    
    /**
     * Get provider settings from WordPress options
     * @return array
     */
    protected function get_provider_settings() {
        $all_settings = get_option('wp_performance_plus_settings', array());
        return isset($all_settings[$this->provider_name]) ? $all_settings[$this->provider_name] : array();
    }
    
    /**
     * Update provider settings
     * @param array $settings Settings to update
     * @return bool
     */
    public function update_settings($settings) {
        $all_settings = get_option('wp_performance_plus_settings', array());
        $all_settings[$this->provider_name] = array_merge($this->settings, $settings);
        $this->settings = $all_settings[$this->provider_name];
        return update_option('wp_performance_plus_settings', $all_settings);
    }
    
    /**
     * Check if provider is enabled and configured
     * @return bool
     */
    public function is_enabled() {
        return !empty($this->settings['enabled']) && $this->has_required_credentials();
    }
    
    /**
     * Check if required credentials are present
     * @return bool
     */
    abstract protected function has_required_credentials();
    
    /**
     * Make API request to CDN provider
     * @param string $endpoint API endpoint
     * @param array $args Request arguments
     * @return array|WP_Error
     */
    protected function make_api_request($endpoint, $args = array()) {
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        $default_args = array(
            'timeout' => 30,
            'headers' => $this->get_api_headers(),
            'user-agent' => 'WP-Performance-Plus/' . WP_PERFORMANCE_PLUS_VERSION
        );
        
        $args = wp_parse_args($args, $default_args);
        
        // Convert body to JSON if it's an array
        if (!empty($args['body']) && is_array($args['body'])) {
            $args['body'] = wp_json_encode($args['body']);
            $args['headers']['Content-Type'] = 'application/json';
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'api_request_failed',
                sprintf(__('API request failed: %s', 'wp-performance-plus'), $response->get_error_message())
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Try to decode JSON response
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $body;
        }
        
        // Handle non-2xx status codes
        if ($status_code < 200 || $status_code >= 300) {
            $error_message = is_array($data) && isset($data['message']) 
                ? $data['message'] 
                : sprintf(__('API request failed with status %d', 'wp-performance-plus'), $status_code);
            
            return new WP_Error('api_error', $error_message, array('status' => $status_code, 'data' => $data));
        }
        
        return $data;
    }
    
    /**
     * Get API headers for authentication
     * @return array
     */
    abstract protected function get_api_headers();
    
    /**
     * Rewrite URLs to use CDN
     * @param string $url Original URL
     * @return string CDN URL
     */
    public function rewrite_url($url) {
        if (!$this->is_enabled() || !$this->should_rewrite_url($url)) {
            return $url;
        }
        
        $cdn_url = $this->get_cdn_url();
        if (empty($cdn_url)) {
            return $url;
        }
        
        // Parse the original URL
        $parsed = wp_parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return $url;
        }
        
        // Replace the host with CDN URL
        $site_url = site_url();
        $site_parsed = wp_parse_url($site_url);
        
        if ($parsed['host'] === $site_parsed['host']) {
            $cdn_parsed = wp_parse_url($cdn_url);
            $parsed['host'] = $cdn_parsed['host'];
            
            // Rebuild URL
            $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '//';
            $host = $parsed['host'];
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            $path = isset($parsed['path']) ? $parsed['path'] : '';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
            
            return $scheme . $host . $port . $path . $query . $fragment;
        }
        
        return $url;
    }
    
    /**
     * Get CDN URL for this provider
     * @return string
     */
    abstract protected function get_cdn_url();
    
    /**
     * Check if URL should be rewritten to use CDN
     * @param string $url URL to check
     * @return bool
     */
    protected function should_rewrite_url($url) {
        // Get CDN file type settings
        $cdn_settings = get_option('wp_performance_plus_settings', array());
        
        // Check if URL matches enabled file types
        $file_types = array(
            'images' => array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'),
            'js' => array('js'),
            'css' => array('css'),
            'fonts' => array('woff', 'woff2', 'ttf', 'eot', 'otf')
        );
        
        $url_extension = strtolower(pathinfo(wp_parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        foreach ($file_types as $type => $extensions) {
            $setting_key = 'cdn_' . $type;
            if (!empty($cdn_settings[$setting_key]) && in_array($url_extension, $extensions)) {
                return !$this->is_url_excluded($url);
            }
        }
        
        return false;
    }
    
    /**
     * Check if URL is in exclusion list
     * @param string $url URL to check
     * @return bool
     */
    protected function is_url_excluded($url) {
        $cdn_settings = get_option('wp_performance_plus_settings', array());
        $exclusions = isset($cdn_settings['cdn_exclusions']) ? $cdn_settings['cdn_exclusions'] : '';
        
        if (empty($exclusions)) {
            return false;
        }
        
        $exclusion_patterns = array_filter(array_map('trim', explode("\n", $exclusions)));
        
        foreach ($exclusion_patterns as $pattern) {
            // Simple pattern matching - can be enhanced
            if (strpos($url, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log debug information
     * @param string $message Log message
     * @param array $context Additional context
     */
    protected function log($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[WP Performance Plus CDN %s] %s %s',
                $this->provider_name,
                $message,
                !empty($context) ? wp_json_encode($context) : ''
            ));
        }
    }
    
    /**
     * Get provider-specific settings for the modern UI
     * @return array
     */
    abstract public function get_settings_fields();
    
    /**
     * Render provider-specific settings in the modern UI
     * @return string HTML for settings fields
     */
    public function render_settings_fields() {
        $fields = $this->get_settings_fields();
        $html = '';
        
        foreach ($fields as $field) {
            $html .= $this->render_settings_field($field);
        }
        
        return $html;
    }
    
    /**
     * Render individual settings field
     * @param array $field Field configuration
     * @return string HTML for field
     */
    protected function render_settings_field($field) {
        $field_id = esc_attr($field['id']);
        $field_name = esc_attr($field['name']);
        $field_value = esc_attr($this->get_setting_value($field['id']));
        $field_description = esc_html($field['description']);
        
        $html = '<div class="settings-card settings-card-wide">';
        $html .= '<div class="settings-card-body">';
        $html .= '<div class="settings-field-group">';
        
        // Field label
        $html .= '<label for="' . $field_id . '" class="settings-field-label">';
        if (!empty($field['icon'])) {
            $html .= '<span class="dashicons dashicons-' . esc_attr($field['icon']) . '"></span>';
        }
        $html .= esc_html($field['label']);
        $html .= '</label>';
        
        // Field input
        $html .= '<div class="settings-field-input">';
        
        switch ($field['type']) {
            case 'text':
            case 'password':
                $html .= '<input type="' . esc_attr($field['type']) . '" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" class="settings-text-input" />';
                break;
                
            case 'select':
                $html .= '<select id="' . $field_id . '" name="' . $field_name . '" class="settings-select">';
                foreach ($field['options'] as $value => $label) {
                    $selected = selected($field_value, $value, false);
                    $html .= '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
                }
                $html .= '</select>';
                break;
                
            case 'textarea':
                $html .= '<textarea id="' . $field_id . '" name="' . $field_name . '" class="settings-textarea" rows="3">' . $field_value . '</textarea>';
                break;
        }
        
        $html .= '</div>';
        
        // Field description
        if (!empty($field_description)) {
            $html .= '<p class="settings-field-description">' . $field_description . '</p>';
        }
        
        $html .= '</div></div></div>';
        
        return $html;
    }
    
    /**
     * Get setting value for field
     * @param string $key Setting key
     * @return mixed Setting value
     */
    protected function get_setting_value($key) {
        return isset($this->settings[$key]) ? $this->settings[$key] : '';
    }
} 