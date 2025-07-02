<?php
/**
 * BunnyCDN Provider Implementation
 * 
 * Handles BunnyCDN integration including API authentication,
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

class WP_Performance_Plus_BunnyCDN extends WP_Performance_Plus_CDN_Provider {
    
    /**
     * Get provider name
     * @return string
     */
    protected function get_provider_name() {
        return 'bunnycdn';
    }
    
    /**
     * Get API base URL
     * @return string
     */
    protected function get_api_base_url() {
        return 'https://bunnycdn.com/api';
    }
    
    /**
     * Validate API credentials
     * @return bool|WP_Error
     */
    public function validate_credentials() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API key is required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('pullzone');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (is_array($result)) {
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
            'AccessKey' => $this->settings['api_key'],
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
        
        if (empty($this->settings['pull_zone_id'])) {
            return new WP_Error('missing_zone_id', __('Pull Zone ID is required for cache purging.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request(
            'pullzone/' . $this->settings['pull_zone_id'] . '/purgeCache',
            array('method' => 'POST')
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
        
        if (empty($this->settings['pull_zone_id'])) {
            return new WP_Error('missing_zone_id', __('Pull Zone ID is required for cache purging.', 'wp-performance-plus'));
        }
        
        if (empty($urls) || !is_array($urls)) {
            return new WP_Error('invalid_urls', __('Valid URLs array is required.', 'wp-performance-plus'));
        }
        
        // BunnyCDN can purge specific URLs
        foreach ($urls as $url) {
            $result = $this->make_api_request(
                'purge?url=' . urlencode($url),
                array('method' => 'POST')
            );
            
            if (is_wp_error($result)) {
                $this->log('URL cache purge failed', array('url' => $url, 'error' => $result->get_error_message()));
                return $result;
            }
        }
        
        $this->log('URLs purged successfully', array('count' => count($urls)));
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
        
        $result = $this->make_api_request('pullzone');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $zones = array();
        if (is_array($result)) {
            foreach ($result as $zone) {
                $zones[] = array(
                    'id' => $zone['Id'],
                    'name' => $zone['Name'],
                    'hostname' => $zone['Hostnames'][0]['Value'] ?? '',
                    'origin_url' => $zone['OriginUrl'],
                    'enabled' => $zone['Enabled']
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
        
        if (empty($this->settings['pull_zone_id'])) {
            return new WP_Error('missing_zone_id', __('Pull Zone ID is required.', 'wp-performance-plus'));
        }
        
        // Get statistics for the last 24 hours
        $from = date('Y-m-d', strtotime('-1 day'));
        $to = date('Y-m-d');
        
        $result = $this->make_api_request(
            'statistics?dateFrom=' . $from . '&dateTo=' . $to . '&pullZone=' . $this->settings['pull_zone_id']
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $stats = array(
            'requests_total' => 0,
            'bandwidth_total' => 0,
            'cache_hit_ratio' => 0,
            'threats_blocked' => 0 // BunnyCDN doesn't provide threat data
        );
        
        if (is_array($result) && !empty($result)) {
            foreach ($result as $day_data) {
                $stats['requests_total'] += isset($day_data['RequestsServed']) ? $day_data['RequestsServed'] : 0;
                $stats['bandwidth_total'] += isset($day_data['BandwidthUsed']) ? $day_data['BandwidthUsed'] : 0;
            }
            
            // Calculate cache hit ratio
            $cache_hits = 0;
            $total_requests = 0;
            foreach ($result as $day_data) {
                $cache_hits += isset($day_data['CacheHitRate']) ? $day_data['CacheHitRate'] : 0;
                $total_requests += isset($day_data['RequestsServed']) ? $day_data['RequestsServed'] : 0;
            }
            
            if ($total_requests > 0) {
                $stats['cache_hit_ratio'] = round(($cache_hits / count($result)), 2);
            }
        }
        
        return $stats;
    }
    
    /**
     * Get CDN URL for this provider
     * @return string
     */
    protected function get_cdn_url() {
        if (!empty($this->settings['hostname'])) {
            return 'https://' . $this->settings['hostname'];
        }
        
        // If no custom hostname, try to construct from pull zone name
        if (!empty($this->settings['pull_zone_name'])) {
            return 'https://' . $this->settings['pull_zone_name'] . '.b-cdn.net';
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
                'name' => 'wp_performance_plus_settings[bunnycdn][api_key]',
                'label' => __('API Key', 'wp-performance-plus'),
                'type' => 'password',
                'icon' => 'lock',
                'description' => __('Your BunnyCDN API key. You can find this in your BunnyCDN account settings.', 'wp-performance-plus')
            ),
            array(
                'id' => 'pull_zone_id',
                'name' => 'wp_performance_plus_settings[bunnycdn][pull_zone_id]',
                'label' => __('Pull Zone ID', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-site-alt3',
                'description' => __('Your BunnyCDN Pull Zone ID for this domain.', 'wp-performance-plus')
            ),
            array(
                'id' => 'hostname',
                'name' => 'wp_performance_plus_settings[bunnycdn][hostname]',
                'label' => __('Hostname', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-links',
                'description' => __('Your BunnyCDN hostname (e.g., cdn.yourdomain.com or your-zone.b-cdn.net).', 'wp-performance-plus')
            ),
            array(
                'id' => 'cache_control_type',
                'name' => 'wp_performance_plus_settings[bunnycdn][cache_control_type]',
                'label' => __('Cache Control Type', 'wp-performance-plus'),
                'type' => 'select',
                'icon' => 'performance',
                'options' => array(
                    '0' => __('No override', 'wp-performance-plus'),
                    '1' => __('Override', 'wp-performance-plus'),
                    '2' => __('Force cache', 'wp-performance-plus')
                ),
                'description' => __('How BunnyCDN should handle cache control headers.', 'wp-performance-plus')
            ),
            array(
                'id' => 'cache_control_max_age',
                'name' => 'wp_performance_plus_settings[bunnycdn][cache_control_max_age]',
                'label' => __('Cache Max Age', 'wp-performance-plus'),
                'type' => 'select',
                'icon' => 'clock',
                'options' => array(
                    '3600' => __('1 hour', 'wp-performance-plus'),
                    '86400' => __('1 day', 'wp-performance-plus'),
                    '604800' => __('1 week', 'wp-performance-plus'),
                    '2592000' => __('1 month', 'wp-performance-plus'),
                    '31536000' => __('1 year', 'wp-performance-plus')
                ),
                'description' => __('Maximum cache age for content.', 'wp-performance-plus')
            )
        );
    }
    
    /**
     * Get pull zone information
     * @return array|WP_Error
     */
    public function get_pull_zone_info() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        if (empty($this->settings['pull_zone_id'])) {
            return new WP_Error('missing_zone_id', __('Pull Zone ID is required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('pullzone/' . $this->settings['pull_zone_id']);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return array(
            'id' => $result['Id'],
            'name' => $result['Name'],
            'hostname' => $result['Hostnames'][0]['Value'] ?? '',
            'origin_url' => $result['OriginUrl'],
            'enabled' => $result['Enabled']
        );
    }
    
    /**
     * Auto-detect pull zone for current domain
     * @return string|WP_Error
     */
    public function auto_detect_pull_zone() {
        $zones = $this->get_zones();
        
        if (is_wp_error($zones)) {
            return $zones;
        }
        
        $current_domain = wp_parse_url(site_url(), PHP_URL_HOST);
        $site_url = site_url();
        
        foreach ($zones as $zone) {
            // Check if the origin URL matches our site
            if (strpos($zone['origin_url'], $current_domain) !== false || 
                strpos($zone['origin_url'], $site_url) !== false) {
                return $zone['id'];
            }
        }
        
        return new WP_Error('zone_not_found', __('Pull zone not found for current domain.', 'wp-performance-plus'));
    }
    
    /**
     * Create a new pull zone
     * @param array $config Pull zone configuration
     * @return array|WP_Error
     */
    public function create_pull_zone($config) {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        $default_config = array(
            'Name' => '',
            'OriginUrl' => site_url(),
            'CacheControlMaxAge' => 86400,
            'CacheControlPublicMaxAge' => 86400
        );
        
        $config = wp_parse_args($config, $default_config);
        
        $result = $this->make_api_request(
            'pullzone',
            array(
                'method' => 'POST',
                'body' => $config
            )
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log('Pull zone created successfully', array('zone_id' => $result['Id']));
        return $result;
    }
}
