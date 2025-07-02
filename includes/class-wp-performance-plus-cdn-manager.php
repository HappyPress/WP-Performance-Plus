<?php
/**
 * CDN Manager Class
 * 
 * Coordinates all CDN providers, handles URL rewriting,
 * cache purging, and provides a unified CDN interface.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_CDN_Manager {
    
    /**
     * Available CDN providers
     * @var array
     */
    private $providers = array();
    
    /**
     * Active CDN provider
     * @var WP_Performance_Plus_CDN_Provider|null
     */
    private $active_provider = null;
    
    /**
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('wp_performance_plus_settings', array());
        $this->init_providers();
        $this->set_active_provider();
        $this->init_hooks();
    }
    
    /**
     * Initialize CDN providers
     */
    private function init_providers() {
        // Include base class
        require_once plugin_dir_path(__FILE__) . 'abstract-class-wp-performance-plus-cdn.php';
        
        // Include provider classes
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-cloudflare.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-keycdn.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-bunnycdn.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-cloudfront.php';
        
        // Initialize providers
        $this->providers = array(
            'cloudflare' => new WP_Performance_Plus_Cloudflare(),
            'keycdn' => new WP_Performance_Plus_KeyCDN(),
            'bunnycdn' => new WP_Performance_Plus_BunnyCDN(),
            'cloudfront' => new WP_Performance_Plus_CloudFront()
        );
    }
    
    /**
     * Set active provider based on settings
     */
    private function set_active_provider() {
        $provider_name = isset($this->settings['cdn_provider']) ? $this->settings['cdn_provider'] : 'none';
        
        if ($provider_name !== 'none' && isset($this->providers[$provider_name])) {
            $this->active_provider = $this->providers[$provider_name];
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // URL rewriting hooks
        if ($this->is_cdn_enabled()) {
            add_action('init', array($this, 'start_output_buffering'), 1);
            add_action('wp_loaded', array($this, 'setup_url_rewriting'));
        }
        
        // AJAX handlers
        add_action('wp_ajax_wp_performance_plus_test_cdn', array($this, 'ajax_test_cdn_connection'));
        add_action('wp_ajax_wp_performance_plus_purge_cdn', array($this, 'ajax_purge_cdn_cache'));
        add_action('wp_ajax_wp_performance_plus_get_cdn_zones', array($this, 'ajax_get_cdn_zones'));
        add_action('wp_ajax_wp_performance_plus_get_cdn_stats', array($this, 'ajax_get_cdn_statistics'));
        
        // Automatic cache purging on content updates
        add_action('save_post', array($this, 'auto_purge_post_cache'));
        add_action('comment_post', array($this, 'auto_purge_post_cache'));
        add_action('wp_update_nav_menu', array($this, 'auto_purge_all_cache'));
        
        // Settings integration
        add_action('wp_performance_plus_settings_saved', array($this, 'handle_settings_saved'));
    }
    
    /**
     * Check if CDN is enabled
     * @return bool
     */
    public function is_cdn_enabled() {
        return $this->active_provider && $this->active_provider->is_enabled();
    }
    
    /**
     * Start output buffering for URL rewriting
     */
    public function start_output_buffering() {
        if (!$this->is_cdn_enabled() || is_admin()) {
            return;
        }
        
        ob_start(array($this, 'rewrite_urls_in_content'));
    }
    
    /**
     * Setup URL rewriting for specific content types
     */
    public function setup_url_rewriting() {
        if (!$this->is_cdn_enabled()) {
            return;
        }
        
        // Rewrite URLs in wp_get_attachment_url
        add_filter('wp_get_attachment_url', array($this, 'rewrite_attachment_url'), 10, 1);
        
        // Rewrite URLs in content
        add_filter('the_content', array($this, 'rewrite_urls_in_content'), 99);
        
        // Rewrite script and style URLs
        add_filter('script_loader_src', array($this, 'rewrite_asset_url'), 10, 1);
        add_filter('style_loader_src', array($this, 'rewrite_asset_url'), 10, 1);
        
        // Rewrite URLs in srcset attributes
        add_filter('wp_calculate_image_srcset', array($this, 'rewrite_srcset_urls'), 10, 1);
    }
    
    /**
     * Rewrite URLs in content
     * @param string $content Content to process
     * @return string Processed content
     */
    public function rewrite_urls_in_content($content) {
        if (!$this->is_cdn_enabled() || empty($content)) {
            return $content;
        }
        
        $site_url = site_url();
        $cdn_url = $this->active_provider->get_cdn_url();
        
        if (empty($cdn_url) || $cdn_url === $site_url) {
            return $content;
        }
        
        // Find and replace URLs in common attributes
        $patterns = array(
            '/src=["\'](' . preg_quote($site_url, '/') . '[^"\']*\.(jpg|jpeg|png|gif|webp|svg|css|js|woff|woff2|ttf|eot|otf))["\']/',
            '/href=["\'](' . preg_quote($site_url, '/') . '[^"\']*\.(css|js))["\']/',
            '/url\(["\']?(' . preg_quote($site_url, '/') . '[^"\']*\.(jpg|jpeg|png|gif|webp|svg|css|js|woff|woff2|ttf|eot|otf))["\']?\)/'
        );
        
        foreach ($patterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) {
                $original_url = $matches[1];
                $rewritten_url = $this->active_provider->rewrite_url($original_url);
                return str_replace($original_url, $rewritten_url, $matches[0]);
            }, $content);
        }
        
        return $content;
    }
    
    /**
     * Rewrite attachment URL
     * @param string $url Original URL
     * @return string Rewritten URL
     */
    public function rewrite_attachment_url($url) {
        if (!$this->is_cdn_enabled()) {
            return $url;
        }
        
        return $this->active_provider->rewrite_url($url);
    }
    
    /**
     * Rewrite asset URL (scripts and styles)
     * @param string $url Original URL
     * @return string Rewritten URL
     */
    public function rewrite_asset_url($url) {
        if (!$this->is_cdn_enabled()) {
            return $url;
        }
        
        return $this->active_provider->rewrite_url($url);
    }
    
    /**
     * Rewrite URLs in srcset attributes
     * @param array $sources Srcset sources
     * @return array Modified sources
     */
    public function rewrite_srcset_urls($sources) {
        if (!$this->is_cdn_enabled() || !is_array($sources)) {
            return $sources;
        }
        
        foreach ($sources as $width => $source) {
            if (isset($source['url'])) {
                $sources[$width]['url'] = $this->active_provider->rewrite_url($source['url']);
            }
        }
        
        return $sources;
    }
    
    /**
     * Get active CDN provider
     * @return WP_Performance_Plus_CDN_Provider|null
     */
    public function get_active_provider() {
        return $this->active_provider;
    }
    
    /**
     * Get all CDN providers
     * @return array
     */
    public function get_providers() {
        return $this->providers;
    }
    
    /**
     * Get provider by name
     * @param string $name Provider name
     * @return WP_Performance_Plus_CDN_Provider|null
     */
    public function get_provider($name) {
        return isset($this->providers[$name]) ? $this->providers[$name] : null;
    }
    
    /**
     * Purge cache for specific URLs
     * @param array $urls URLs to purge
     * @return bool|WP_Error
     */
    public function purge_urls($urls) {
        if (!$this->is_cdn_enabled()) {
            return new WP_Error('cdn_not_enabled', __('CDN is not enabled.', 'wp-performance-plus'));
        }
        
        return $this->active_provider->purge_urls($urls);
    }
    
    /**
     * Purge all cache
     * @return bool|WP_Error
     */
    public function purge_all_cache() {
        if (!$this->is_cdn_enabled()) {
            return new WP_Error('cdn_not_enabled', __('CDN is not enabled.', 'wp-performance-plus'));
        }
        
        return $this->active_provider->purge_all_cache();
    }
    
    /**
     * Test CDN connection
     * @param string $provider_name Provider to test (optional)
     * @return bool|WP_Error
     */
    public function test_connection($provider_name = null) {
        $provider = $provider_name ? $this->get_provider($provider_name) : $this->active_provider;
        
        if (!$provider) {
            return new WP_Error('invalid_provider', __('Invalid CDN provider.', 'wp-performance-plus'));
        }
        
        return $provider->validate_credentials();
    }
    
    /**
     * Get CDN statistics
     * @return array|WP_Error
     */
    public function get_statistics() {
        if (!$this->is_cdn_enabled()) {
            return new WP_Error('cdn_not_enabled', __('CDN is not enabled.', 'wp-performance-plus'));
        }
        
        return $this->active_provider->get_statistics();
    }
    
    /**
     * Auto-purge cache when post is updated
     * @param int $post_id Post ID
     */
    public function auto_purge_post_cache($post_id) {
        if (!$this->is_cdn_enabled()) {
            return;
        }
        
        $urls_to_purge = array(
            get_permalink($post_id),
            home_url('/'), // Homepage
            get_post_type_archive_link(get_post_type($post_id)) // Archive page
        );
        
        // Add category and tag URLs
        $categories = get_the_category($post_id);
        foreach ($categories as $category) {
            $urls_to_purge[] = get_category_link($category->term_id);
        }
        
        $tags = get_the_tags($post_id);
        if ($tags) {
            foreach ($tags as $tag) {
                $urls_to_purge[] = get_tag_link($tag->term_id);
            }
        }
        
        $urls_to_purge = array_filter(array_unique($urls_to_purge));
        
        if (!empty($urls_to_purge)) {
            $this->purge_urls($urls_to_purge);
        }
    }
    
    /**
     * Auto-purge all cache for major site changes
     */
    public function auto_purge_all_cache() {
        if (!$this->is_cdn_enabled()) {
            return;
        }
        
        $this->purge_all_cache();
    }
    
    /**
     * Handle settings saved event
     * @param array $settings New settings
     */
    public function handle_settings_saved($settings) {
        $this->settings = $settings;
        $this->set_active_provider();
        
        // Purge cache when CDN settings change
        if ($this->is_cdn_enabled()) {
            $this->purge_all_cache();
        }
    }
    
    /**
     * AJAX handler for testing CDN connection
     */
    public function ajax_test_cdn_connection() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $provider_name = isset($_POST['provider']) ? sanitize_key($_POST['provider']) : null;
        
        $result = $this->test_connection($provider_name);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(__('CDN connection successful!', 'wp-performance-plus'));
        }
    }
    
    /**
     * AJAX handler for purging CDN cache
     */
    public function ajax_purge_cdn_cache() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $urls = isset($_POST['urls']) ? $_POST['urls'] : array();
        
        if (empty($urls)) {
            $result = $this->purge_all_cache();
        } else {
            $result = $this->purge_urls($urls);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(__('CDN cache purged successfully!', 'wp-performance-plus'));
        }
    }
    
    /**
     * AJAX handler for getting CDN zones
     */
    public function ajax_get_cdn_zones() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $provider_name = isset($_POST['provider']) ? sanitize_key($_POST['provider']) : null;
        $provider = $provider_name ? $this->get_provider($provider_name) : $this->active_provider;
        
        if (!$provider) {
            wp_send_json_error(__('Invalid CDN provider.', 'wp-performance-plus'));
        }
        
        $zones = $provider->get_zones();
        
        if (is_wp_error($zones)) {
            wp_send_json_error($zones->get_error_message());
        } else {
            wp_send_json_success($zones);
        }
    }
    
    /**
     * AJAX handler for getting CDN statistics
     */
    public function ajax_get_cdn_statistics() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $stats = $this->get_statistics();
        
        if (is_wp_error($stats)) {
            wp_send_json_error($stats->get_error_message());
        } else {
            wp_send_json_success($stats);
        }
    }
    
    /**
     * Get provider-specific settings fields for a provider
     * @param string $provider_name Provider name
     * @return string HTML for settings fields
     */
    public function get_provider_settings_html($provider_name) {
        $provider = $this->get_provider($provider_name);
        
        if (!$provider) {
            return '<p>' . __('Invalid provider selected.', 'wp-performance-plus') . '</p>';
        }
        
        return $provider->render_settings_fields();
    }
} 