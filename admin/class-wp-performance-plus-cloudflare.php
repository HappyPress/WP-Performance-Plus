<?php
/**
 * Cloudflare CDN Provider Implementation
 * 
 * Handles Cloudflare CDN integration including API authentication,
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

class WP_Performance_Plus_Cloudflare extends WP_Performance_Plus_CDN_Provider {
    
    /**
     * Get provider name
     * @return string
     */
    protected function get_provider_name() {
        return 'cloudflare';
    }
    
    /**
     * Get API base URL
     * @return string
     */
    protected function get_api_base_url() {
        return 'https://api.cloudflare.com/client/v4';
    }
    
    /**
     * Validate API credentials
     * @return bool|WP_Error
     */
    public function validate_credentials() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API token and zone ID are required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('user/tokens/verify');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (isset($result['status']) && $result['status'] === 'active') {
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
        return !empty($this->settings['api_token']) && !empty($this->settings['zone_id']);
    }
    
    /**
     * Get API headers for authentication
     * @return array
     */
    protected function get_api_headers() {
        return array(
            'Authorization' => 'Bearer ' . $this->settings['api_token'],
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
        
        $result = $this->make_api_request(
            'zones/' . $this->settings['zone_id'] . '/purge_cache',
            array(
                'method' => 'POST',
                'body' => array('purge_everything' => true)
            )
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
        
        if (empty($urls) || !is_array($urls)) {
            return new WP_Error('invalid_urls', __('Valid URLs array is required.', 'wp-performance-plus'));
        }
        
        // Cloudflare has a limit of 30 URLs per request
        $url_chunks = array_chunk($urls, 30);
        
        foreach ($url_chunks as $chunk) {
            $result = $this->make_api_request(
                'zones/' . $this->settings['zone_id'] . '/purge_cache',
                array(
                    'method' => 'POST',
                    'body' => array('files' => $chunk)
                )
            );
            
            if (is_wp_error($result)) {
                $this->log('URL cache purge failed', array('urls' => $chunk, 'error' => $result->get_error_message()));
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
        if (empty($this->settings['api_token'])) {
            return new WP_Error('missing_token', __('API token is required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('zones?per_page=50');
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $zones = array();
        if (isset($result['result']) && is_array($result['result'])) {
            foreach ($result['result'] as $zone) {
                $zones[] = array(
                    'id' => $zone['id'],
                    'name' => $zone['name'],
                    'status' => $zone['status'],
                    'development_mode' => isset($zone['development_mode']) ? $zone['development_mode'] : 0
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
        
        // Get analytics for the last 24 hours
        $since = date('c', strtotime('-24 hours'));
        $until = date('c');
        
        $result = $this->make_api_request(
            'zones/' . $this->settings['zone_id'] . '/analytics/dashboard',
            array(
                'method' => 'GET',
                'body' => array(
                    'since' => $since,
                    'until' => $until
                )
            )
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $stats = array(
            'requests_total' => 0,
            'bandwidth_total' => 0,
            'cache_hit_ratio' => 0,
            'threats_blocked' => 0
        );
        
        if (isset($result['result']['totals'])) {
            $totals = $result['result']['totals'];
            $stats['requests_total'] = isset($totals['requests']['all']) ? $totals['requests']['all'] : 0;
            $stats['bandwidth_total'] = isset($totals['bandwidth']['all']) ? $totals['bandwidth']['all'] : 0;
            $stats['cache_hit_ratio'] = isset($totals['requests']['cached']) && $stats['requests_total'] > 0 
                ? round(($totals['requests']['cached'] / $stats['requests_total']) * 100, 2) 
                : 0;
            $stats['threats_blocked'] = isset($totals['threats']['all']) ? $totals['threats']['all'] : 0;
        }
        
        return $stats;
    }
    
    /**
     * Get CDN URL for this provider
     * @return string
     */
    protected function get_cdn_url() {
        // For Cloudflare, the CDN URL is typically the same as the domain
        // but we can check if there's a custom hostname configured
        if (!empty($this->settings['custom_hostname'])) {
            return 'https://' . $this->settings['custom_hostname'];
        }
        
        return site_url();
    }
    
    /**
     * Toggle development mode
     * @param bool $enable Enable or disable development mode
     * @return bool|WP_Error
     */
    public function toggle_development_mode($enable = null) {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        // If enable is null, toggle current state
        if ($enable === null) {
            $zone_info = $this->get_zone_info();
            if (is_wp_error($zone_info)) {
                return $zone_info;
            }
            $enable = !$zone_info['development_mode'];
        }
        
        $result = $this->make_api_request(
            'zones/' . $this->settings['zone_id'] . '/settings/development_mode',
            array(
                'method' => 'PATCH',
                'body' => array('value' => $enable ? 'on' : 'off')
            )
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log('Development mode toggled', array('enabled' => $enable));
        return $enable;
    }
    
    /**
     * Get zone information
     * @return array|WP_Error
     */
    public function get_zone_info() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('zones/' . $this->settings['zone_id']);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (isset($result['result'])) {
            return array(
                'id' => $result['result']['id'],
                'name' => $result['result']['name'],
                'status' => $result['result']['status'],
                'development_mode' => $result['result']['development_mode']
            );
        }
        
        return new WP_Error('invalid_response', __('Invalid API response.', 'wp-performance-plus'));
    }
    
    /**
     * Get provider-specific settings for the modern UI
     * @return array
     */
    public function get_settings_fields() {
        return array(
            array(
                'id' => 'api_token',
                'name' => 'wp_performance_plus_settings[cloudflare][api_token]',
                'label' => __('API Token', 'wp-performance-plus'),
                'type' => 'password',
                'icon' => 'lock',
                'description' => __('Your Cloudflare API Token. You can create one in your Cloudflare dashboard under "My Profile" > "API Tokens".', 'wp-performance-plus')
            ),
            array(
                'id' => 'zone_id',
                'name' => 'wp_performance_plus_settings[cloudflare][zone_id]',
                'label' => __('Zone ID', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-site-alt3',
                'description' => __('Your Cloudflare Zone ID. You can find this in your Cloudflare dashboard under the "Overview" section of your domain.', 'wp-performance-plus')
            ),
            array(
                'id' => 'custom_hostname',
                'name' => 'wp_performance_plus_settings[cloudflare][custom_hostname]',
                'label' => __('Custom Hostname', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-links',
                'description' => __('Optional: Custom hostname for CDN URLs (leave empty to use your domain).', 'wp-performance-plus')
            ),
            array(
                'id' => 'cache_level',
                'name' => 'wp_performance_plus_settings[cloudflare][cache_level]',
                'label' => __('Cache Level', 'wp-performance-plus'),
                'type' => 'select',
                'icon' => 'performance',
                'options' => array(
                    'aggressive' => __('Aggressive', 'wp-performance-plus'),
                    'basic' => __('Basic', 'wp-performance-plus'),
                    'simplified' => __('Simplified', 'wp-performance-plus')
                ),
                'description' => __('Set the cache level for your Cloudflare zone.', 'wp-performance-plus')
            )
        );
    }
    
    /**
     * Set cache level
     * @param string $level Cache level (aggressive, basic, simplified)
     * @return bool|WP_Error
     */
    public function set_cache_level($level = 'aggressive') {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        $valid_levels = array('aggressive', 'basic', 'simplified');
        if (!in_array($level, $valid_levels)) {
            return new WP_Error('invalid_level', __('Invalid cache level.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request(
            'zones/' . $this->settings['zone_id'] . '/settings/cache_level',
            array(
                'method' => 'PATCH',
                'body' => array('value' => $level)
            )
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log('Cache level updated', array('level' => $level));
        return true;
    }
    
    /**
     * Auto-detect zone ID based on current domain
     * @return string|WP_Error
     */
    public function auto_detect_zone_id() {
        $zones = $this->get_zones();
        
        if (is_wp_error($zones)) {
            return $zones;
        }
        
        $current_domain = wp_parse_url(site_url(), PHP_URL_HOST);
        
        foreach ($zones as $zone) {
            if ($zone['name'] === $current_domain) {
                return $zone['id'];
            }
        }
        
        return new WP_Error('zone_not_found', __('Zone not found for current domain.', 'wp-performance-plus'));
    }
}

// Initialize Cloudflare settings management
// new WP_Performance_Plus_Cloudflare();
?>
