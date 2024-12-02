<?php
abstract class PerformancePlus_CDN {
    abstract public function validate_api_credentials();
    abstract public function purge_cache();
    abstract protected function get_api_url();
    
    protected function handle_api_response($response) {
        if (is_wp_error($response)) {
            return new WP_Error('api_error', $response->get_error_message());
        }
        return $response;
    }
} 