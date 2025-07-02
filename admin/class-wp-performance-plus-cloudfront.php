<?php
/**
 * Amazon CloudFront CDN Provider Implementation
 * 
 * Handles CloudFront integration including API authentication,
 * distribution management, cache invalidation, and URL rewriting.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/admin
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_CloudFront extends WP_Performance_Plus_CDN_Provider {
    
    /**
     * AWS region for CloudFront
     * @var string
     */
    private $aws_region = 'us-east-1';
    
    /**
     * Get provider name
     * @return string
     */
    protected function get_provider_name() {
        return 'cloudfront';
    }
    
    /**
     * Get API base URL
     * @return string
     */
    protected function get_api_base_url() {
        return 'https://cloudfront.amazonaws.com/2020-05-31';
    }
    
    /**
     * Validate API credentials
     * @return bool|WP_Error
     */
    public function validate_credentials() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('Access key, secret key, and distribution ID are required.', 'wp-performance-plus'));
        }
        
        // Try to get distribution information to validate credentials
        $result = $this->get_distribution_info();
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log('API credentials validated successfully');
        return true;
    }
    
    /**
     * Check if required credentials are present
     * @return bool
     */
    protected function has_required_credentials() {
        return !empty($this->settings['access_key']) && 
               !empty($this->settings['secret_key']) && 
               !empty($this->settings['distribution_id']);
    }
    
    /**
     * Get API headers for authentication
     * @return array
     */
    protected function get_api_headers() {
        $date = gmdate('D, d M Y H:i:s T');
        $signature = $this->generate_aws_signature('GET', '', '', $date, '/2020-05-31/distribution/' . $this->settings['distribution_id']);
        
        return array(
            'Date' => $date,
            'Authorization' => 'AWS ' . $this->settings['access_key'] . ':' . $signature,
            'Content-Type' => 'application/xml'
        );
    }
    
    /**
     * Generate AWS signature for authentication
     * @param string $method HTTP method
     * @param string $content_md5 Content MD5
     * @param string $content_type Content type
     * @param string $date Date
     * @param string $resource Resource path
     * @return string Signature
     */
    private function generate_aws_signature($method, $content_md5, $content_type, $date, $resource) {
        $string_to_sign = $method . "\n" . $content_md5 . "\n" . $content_type . "\n" . $date . "\n" . $resource;
        return base64_encode(hash_hmac('sha1', $string_to_sign, $this->settings['secret_key'], true));
    }
    
    /**
     * Purge all cache (create invalidation)
     * @return bool|WP_Error
     */
    public function purge_all_cache() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required for cache invalidation.', 'wp-performance-plus'));
        }
        
        // CloudFront invalidates by paths, so we'll invalidate everything
        return $this->purge_urls(array('/*'));
    }
    
    /**
     * Purge specific URLs (create invalidation)
     * @param array $urls URLs to purge
     * @return bool|WP_Error
     */
    public function purge_urls($urls) {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required for cache invalidation.', 'wp-performance-plus'));
        }
        
        if (empty($urls) || !is_array($urls)) {
            return new WP_Error('invalid_urls', __('Valid URLs array is required.', 'wp-performance-plus'));
        }
        
        // Convert URLs to paths for CloudFront invalidation
        $paths = array();
        $site_url = site_url();
        
        foreach ($urls as $url) {
            if ($url === '/*') {
                $paths[] = '/*';
            } else {
                $path = str_replace($site_url, '', $url);
                $paths[] = empty($path) ? '/' : $path;
            }
        }
        
        // Create invalidation request
        $caller_reference = 'wp-performance-plus-' . time() . '-' . wp_rand(1000, 9999);
        $xml_body = $this->build_invalidation_xml($paths, $caller_reference);
        
        $date = gmdate('D, d M Y H:i:s T');
        $resource = '/2020-05-31/distribution/' . $this->settings['distribution_id'] . '/invalidation';
        $signature = $this->generate_aws_signature('POST', '', 'application/xml', $date, $resource);
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Date' => $date,
                'Authorization' => 'AWS ' . $this->settings['access_key'] . ':' . $signature,
                'Content-Type' => 'application/xml'
            ),
            'body' => $xml_body
        );
        
        $result = $this->make_api_request('distribution/' . $this->settings['distribution_id'] . '/invalidation', $args);
        
        if (is_wp_error($result)) {
            $this->log('Cache invalidation failed', array('paths' => $paths, 'error' => $result->get_error_message()));
            return $result;
        }
        
        $this->log('URLs invalidated successfully', array('count' => count($paths)));
        return true;
    }
    
    /**
     * Build invalidation XML for CloudFront
     * @param array $paths Paths to invalidate
     * @param string $caller_reference Unique reference
     * @return string XML string
     */
    private function build_invalidation_xml($paths, $caller_reference) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<InvalidationBatch>' . "\n";
        $xml .= '  <Paths>' . "\n";
        $xml .= '    <Quantity>' . count($paths) . '</Quantity>' . "\n";
        $xml .= '    <Items>' . "\n";
        
        foreach ($paths as $path) {
            $xml .= '      <li>' . esc_xml($path) . '</li>' . "\n";
        }
        
        $xml .= '    </Items>' . "\n";
        $xml .= '  </Paths>' . "\n";
        $xml .= '  <CallerReference>' . esc_xml($caller_reference) . '</CallerReference>' . "\n";
        $xml .= '</InvalidationBatch>';
        
        return $xml;
    }
    
    /**
     * Get zones/distributions available for this account
     * @return array|WP_Error
     */
    public function get_zones() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        // For CloudFront, we'll return the current distribution info
        $distribution = $this->get_distribution_info();
        
        if (is_wp_error($distribution)) {
            return $distribution;
        }
        
        return array($distribution);
    }
    
    /**
     * Get CDN statistics
     * @return array|WP_Error
     */
    public function get_statistics() {
        // CloudFront statistics require CloudWatch API which is more complex
        // For now, return basic placeholder stats
        return array(
            'requests_total' => 0,
            'bandwidth_total' => 0,
            'cache_hit_ratio' => 0,
            'threats_blocked' => 0
        );
    }
    
    /**
     * Get CDN URL for this provider
     * @return string
     */
    protected function get_cdn_url() {
        if (!empty($this->settings['domain_name'])) {
            return 'https://' . $this->settings['domain_name'];
        }
        
        // Try to get domain from distribution
        $distribution = $this->get_distribution_info();
        if (!is_wp_error($distribution) && !empty($distribution['domain_name'])) {
            return 'https://' . $distribution['domain_name'];
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
                'id' => 'access_key',
                'name' => 'wp_performance_plus_settings[cloudfront][access_key]',
                'label' => __('AWS Access Key ID', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-network',
                'description' => __('Your AWS Access Key ID. You can create one in your AWS IAM console.', 'wp-performance-plus')
            ),
            array(
                'id' => 'secret_key',
                'name' => 'wp_performance_plus_settings[cloudfront][secret_key]',
                'label' => __('AWS Secret Access Key', 'wp-performance-plus'),
                'type' => 'password',
                'icon' => 'lock',
                'description' => __('Your AWS Secret Access Key.', 'wp-performance-plus')
            ),
            array(
                'id' => 'distribution_id',
                'name' => 'wp_performance_plus_settings[cloudfront][distribution_id]',
                'label' => __('Distribution ID', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-site-alt3',
                'description' => __('Your CloudFront Distribution ID.', 'wp-performance-plus')
            ),
            array(
                'id' => 'domain_name',
                'name' => 'wp_performance_plus_settings[cloudfront][domain_name]',
                'label' => __('Custom Domain Name', 'wp-performance-plus'),
                'type' => 'text',
                'icon' => 'admin-links',
                'description' => __('Optional: Custom domain name for your CloudFront distribution (e.g., cdn.yourdomain.com).', 'wp-performance-plus')
            ),
            array(
                'id' => 'default_ttl',
                'name' => 'wp_performance_plus_settings[cloudfront][default_ttl]',
                'label' => __('Default TTL', 'wp-performance-plus'),
                'type' => 'select',
                'icon' => 'clock',
                'options' => array(
                    '3600' => __('1 hour', 'wp-performance-plus'),
                    '86400' => __('1 day', 'wp-performance-plus'),
                    '604800' => __('1 week', 'wp-performance-plus'),
                    '2592000' => __('1 month', 'wp-performance-plus'),
                    '31536000' => __('1 year', 'wp-performance-plus')
                ),
                'description' => __('Default Time To Live for cached content.', 'wp-performance-plus')
            )
        );
    }
    
    /**
     * Get distribution information
     * @return array|WP_Error
     */
    public function get_distribution_info() {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request('distribution/' . $this->settings['distribution_id']);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Parse XML response (simplified)
        if (is_string($result)) {
            $xml = simplexml_load_string($result);
            if ($xml) {
                return array(
                    'id' => (string)$xml->Id,
                    'domain_name' => (string)$xml->DomainName,
                    'status' => (string)$xml->Status,
                    'enabled' => (string)$xml->DistributionConfig->Enabled === 'true'
                );
            }
        }
        
        return new WP_Error('invalid_response', __('Invalid API response.', 'wp-performance-plus'));
    }
    
    /**
     * Override make_api_request to handle AWS authentication
     * @param string $endpoint API endpoint
     * @param array $args Request arguments
     * @return array|WP_Error
     */
    protected function make_api_request($endpoint, $args = array()) {
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        $default_args = array(
            'timeout' => 30,
            'user-agent' => 'WP-Performance-Plus/' . WP_PERFORMANCE_PLUS_VERSION
        );
        
        // Use custom headers if not provided
        if (!isset($args['headers'])) {
            $args['headers'] = $this->get_api_headers();
        }
        
        $args = wp_parse_args($args, $default_args);
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'api_request_failed',
                sprintf(__('API request failed: %s', 'wp-performance-plus'), $response->get_error_message())
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Handle non-2xx status codes
        if ($status_code < 200 || $status_code >= 300) {
            $error_message = sprintf(__('API request failed with status %d', 'wp-performance-plus'), $status_code);
            
            // Try to parse error from XML response
            if (!empty($body)) {
                $xml = simplexml_load_string($body);
                if ($xml && isset($xml->Error->Message)) {
                    $error_message = (string)$xml->Error->Message;
                }
            }
            
            return new WP_Error('api_error', $error_message, array('status' => $status_code, 'body' => $body));
        }
        
        return $body;
    }
    
    /**
     * Get invalidation status
     * @param string $invalidation_id Invalidation ID
     * @return array|WP_Error
     */
    public function get_invalidation_status($invalidation_id) {
        if (!$this->has_required_credentials()) {
            return new WP_Error('missing_credentials', __('API credentials required.', 'wp-performance-plus'));
        }
        
        $result = $this->make_api_request(
            'distribution/' . $this->settings['distribution_id'] . '/invalidation/' . $invalidation_id
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Parse XML response (simplified)
        if (is_string($result)) {
            $xml = simplexml_load_string($result);
            if ($xml) {
                return array(
                    'id' => (string)$xml->Id,
                    'status' => (string)$xml->Status,
                    'create_time' => (string)$xml->CreateTime
                );
            }
        }
        
        return new WP_Error('invalid_response', __('Invalid API response.', 'wp-performance-plus'));
    }
}
