<?php
/**
 * KeyCDN Provider Implementation
 * 
 * Handles KeyCDN integration including API authentication,
 * zone management, cache purging, and URL rewriting.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/admin
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_KeyCDN extends WP_Performance_Plus_CDN_Provider {
    
    /**
     * Get provider name
     * @return string
     */
    protected function get_provider_name() {
        return 'keycdn';
    }
    
    /**
     * Get API base URL
     * @return string
     */
    protected function get_api_base_url() {
        return 'https://api.keycdn.com';
    }
    
    /**
     * Validate API credentials
     * @return bool|WP_Error
     */
    public function validate_credentials() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API key is required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('zones.json');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (isset($result['status']) && $result['status'] === 'success') {
            $this->log('API credentials validated successfully');
            return true;
        }
        
        return new WP_Error('invalid_credentials', __('Invalid API credentials.', 'wp-performance-plus'));
    }
    
    /**
     * Check if required credentials are present
     * @return bool
     */
    protected function has_required_credentials() {
        return !empty($this->settings['api_key']);
    }
    
    /**
     * Get API headers for authentication
     * @return array
     */
    protected function get_api_headers() {
        return array(
            'Authorization' => 'Basic ' . base64_encode($this->settings['api_key'] . ':'),
            'Content-Type' => 'application/json'
        );
    }
    
    /**
     * Purge all cache
     * @return bool|WP_Error
     */
    public function purge_all_cache() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required for cache purging.', 'wp-performance-plus'));
        }
        
        if (empty($this->settings['zone_id'])) {
            return new WP_Error('missing_zone_id', __('Zone ID is required for cache purging.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request(
            'zones/purge/' . $this->settings['zone_id'] . '.json',
            array('method' => 'GET')
        );
        
        if (is_wp_error($result)) {
            $this->log('Cache purge failed', array('error' => $result->get_error_message()));
            return $result;
        }
        
        $this->log('All cache purged successfully');
        return true;
    }
    
    /**
     * Purge specific URLs
     * @param array $urls URLs to purge
     * @return bool|WP_Error
     */
    public function purge_urls($urls) {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required for cache purging.', 'wp-performance-plus'));
        }
        
        if (empty($this->settings['zone_id'])) {
            return new WP_Error('missing_zone_id', __('Zone ID is required for cache purging.', 'wp-performance-plus'));
        }
        
        if (empty($urls) || !is_array($urls)) {
            return new WP_Error('invalid_urls', __('Valid URLs array is required.', 'wp-performance-plus'));
        }
        
        // KeyCDN requires URLs to be relative paths for purging
        $paths = array();
        $site_url = site_url();
        
        foreach ($urls as $url) {
            $path = str_replace($site_url, '', $url);
            if (!empty($path)) {
                $paths[] = $path;
            }
        }
        
        if (empty($paths)) {
            return new WP_Error('no_valid_paths', __('No valid paths found for purging.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request(
            'zones/purgeurl/' . $this->settings['zone_id'] . '.json',
            array(
                'method' => 'POST',
                'body' => array('urls' => $paths)
            )
        );
        
        if (is_wp_error($result)) {
            $this->log('URL cache purge failed', array('paths' => $paths, 'error' => $result->get_error_message()));
            return $result;
        }
        
        $this->log('URLs purged successfully', array('count' => count($paths)));
        return true;
    }
    
    /**
     * Get zones/domains available for this account
     * @return array|WP_Error
     */
    public function get_zones() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('zones.json');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $zones = array();
        if (isset($result['data']['zones']) && is_array($result['data']['zones'])) {
            foreach ($result['data']['zones'] as $zone) {
                $zones[] = array(
                    'id' => $zone['id'],
                    'name' => $zone['name'],
                    'status' => $zone['status'],
                    'type' => $zone['type']
                );
            }
        }
        
        return $zones;
    }
    
    /**
     * Get CDN statistics
     * @return array|WP_Error
     */
    public function get_statistics() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        if (empty($this->settings['zone_id'])) {
            return new WP_Error('missing_zone_id', __('Zone ID is required.', 'wp-performance-plus'));
        }
        
        // Get statistics for the last 24 hours
        $start = date('Y-m-d', strtotime('-1 day'));
        $end = date('Y-m-d');
        
        $result = $this->make_api_request(
            'reports/traffic.json?zoneId=' . $this->settings['zone_id'] . 
            '&start=' . $start . '&end=' . $end
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $stats = array(
            'requests_total' => 0,
            'bandwidth_total' => 0,
            'cache_hit_ratio' => 0,
            'threats_blocked' => 0 // KeyCDN doesn't provide threat data
        );
        
        if (isset($result['data']['traffic']) && is_array($result['data']['traffic'])) {
            foreach ($result['data']['traffic'] as $day_data) {
                $stats['requests_total'] += isset($day_data['requests']) ? $day_data['requests'] : 0;
                $stats['bandwidth_total'] += isset($day_data['bandwidth']) ? $day_data['bandwidth'] : 0;
            }
        }
        
        // Calculate cache hit ratio if available
        if (isset($result['data']['cache_hit_ratio'])) {
            $stats['cache_hit_ratio'] = $result['data']['cache_hit_ratio'];
        }
        
        return $stats;
    }
    
    /**
     * Get CDN URL for this provider
     * @return string
     */
    protected function get_cdn_url() {
        if (!empty($this->settings['zone_url'])) {
            return 'https://' . $this->settings['zone_url'];
        }
        
        // If no custom zone URL, try to construct from zone name
        if (!empty($this->settings['zone_name'])) {
            return 'https://' . $this->settings['zone_name'] . '.kxcdn.com';
        }
        
        return site_url();
    }
    
    /**
     * Get provider-specific settings for the modern UI
     * @return array
     */
    public function get_settings_fields() {
        return array(
            array(
                'id' => 'api_key',
                'name' => 'wp_performance_plus_settings[keycdn][api_key]',
                'label' => __('API Key', 'wp-performance-plus'),
                'type' => 'password',
                'icon' => 'lock',
                'description' => __('Your KeyCDN API key. You can find this in your KeyCDN account settings.', 'wp-performance-plus')
            ),
            array(
                'id' => 'zone_id',
                'name' => 'wp_performance_plus_settings[keycdn][zone_id]',
                'label' => __('Zone ID', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-site-alt3',
                'description' => __('Your KeyCDN Zone ID for this domain.', 'wp-performance-plus')
            ),
            array(
                'id' => 'zone_url',
                'name' => 'wp_performance_plus_settings[keycdn][zone_url]',
                'label' => __('Zone URL', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-links',
                'description' => __('Your KeyCDN Zone URL (e.g., your-domain.kxcdn.com).', 'wp-performance-plus')
            ),
            array(
                'id' => 'cache_control',
                'name' => 'wp_performance_plus_settings[keycdn][cache_control]',
                'label' => __('Cache Control', 'wp-performance-plus'),
                'type' => 'select',
                'icon' => 'clock',
                'options' => array(
                    '3600' => __('1 hour', 'wp-performance-plus'),
                    '86400' => __('1 day', 'wp-performance-plus'),
                    '604800' => __('1 week', 'wp-performance-plus'),
                    '2592000' => __('1 month', 'wp-performance-plus'),
                    '31536000' => __('1 year', 'wp-performance-plus')
                ),
                'description' => __('Default cache time for static content.', 'wp-performance-plus')
            )
        );
    }
    
    /**
     * Get zone information
     * @return array|WP_Error
     */
    public function get_zone_info() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        if (empty($this->settings['zone_id'])) {
            return new WP_Error('missing_zone_id', __('Zone ID is required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('zones/' . $this->settings['zone_id'] . '.json');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (isset($result['data']['zone'])) {
            return array(
                'id' => $result['data']['zone']['id'],
                'name' => $result['data']['zone']['name'],
                'status' => $result['data']['zone']['status'],
                'type' => $result['data']['zone']['type']
            );
        }
        
        return new WP_Error('invalid_response', __('Invalid API response.', 'wp-performance-plus'));
    }
    
    /**
     * Auto-detect zone for current domain
     * @return string|WP_Error
     */
    public function auto_detect_zone() {
        $zones = $this->get_zones();
        
        if (is_wp_error($zones)) {
            return $zones;
        }
        
        $current_domain = wp_parse_url(site_url(), PHP_URL_HOST);
        
        foreach ($zones as $zone) {
            // KeyCDN zones might have the origin URL in their name or settings
            if (strpos($zone['name'], $current_domain) !== false) {
                return $zone['id'];
            }
        }
        
        return new WP_Error('zone_not_found', __('Zone not found for current domain.', 'wp-performance-plus'));
    }
}
