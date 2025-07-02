<?php
/**
 * Advanced Optimization Features
 * 
 * Handles image optimization, resource preloading, advanced caching rules,
 * and smart prefetching integrated with CDN functionality.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Advanced_Optimization {
    
    /**
     * CDN manager instance
     * @var WP_Performance_Plus_CDN_Manager
     */
    private $cdn_manager;
    
    /**
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Critical resources for preloading
     * @var array
     */
    private $critical_resources = array();
    
    /**
     * Image optimization queue
     * @var array
     */
    private $image_optimization_queue = array();
    
    /**
     * Constructor
     */
    public function __construct($cdn_manager = null) {
        $this->cdn_manager = $cdn_manager;
        $this->settings = get_option('wp_performance_plus_settings', array());
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Image optimization hooks
        add_filter('wp_get_attachment_image_src', array($this, 'optimize_image_src'), 10, 4);
        add_filter('wp_calculate_image_srcset', array($this, 'optimize_srcset_images'), 10, 5);
        add_filter('the_content', array($this, 'optimize_content_images'), 15);
        
        // Resource preloading hooks
        add_action('wp_head', array($this, 'add_resource_preloading'), 5);
        add_action('wp_head', array($this, 'add_dns_prefetch'), 6);
        add_action('wp_head', array($this, 'add_preconnect_hints'), 7);
        
        // Advanced caching hooks
        add_action('wp_head', array($this, 'add_advanced_cache_headers'), 2);
        add_filter('wp_headers', array($this, 'modify_cache_headers'), 10, 1);
        
        // Smart prefetching
        add_action('wp_footer', array($this, 'add_smart_prefetching'), 5);
        
        // Image lazy loading enhancement
        add_filter('wp_lazy_loading_enabled', array($this, 'enhance_lazy_loading'), 10, 3);
        add_filter('wp_img_tag_add_loading_attr', array($this, 'optimize_loading_attribute'), 10, 3);
        
        // WebP conversion hooks
        add_filter('wp_generate_attachment_metadata', array($this, 'generate_webp_versions'), 10, 2);
        
        // AJAX handlers for optimization actions
        add_action('wp_ajax_wp_performance_plus_optimize_images_bulk', array($this, 'ajax_optimize_images_bulk'));
        add_action('wp_ajax_wp_performance_plus_generate_critical_css', array($this, 'ajax_generate_critical_css'));
    }
    
    /**
     * Optimize image source with CDN and format optimization
     * @param array $image Image data
     * @param int $attachment_id Attachment ID
     * @param string $size Image size
     * @param bool $icon Whether it's an icon
     * @return array Modified image data
     */
    public function optimize_image_src($image, $attachment_id, $size, $icon) {
        if (!$image) {
            return $image;
        }
        
        $url = $image[0];
        $width = $image[1];
        $height = $image[2];
        
        // Apply WebP conversion if supported
        if ($this->is_webp_supported() && isset($this->settings['enable_webp']) && $this->settings['enable_webp']) {
            $webp_url = $this->get_webp_url($url);
            if ($webp_url) {
                $url = $webp_url;
            }
        }
        
        // Apply CDN URL rewriting
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $url = $this->cdn_manager->get_active_provider()->rewrite_url($url);
        }
        
        // Apply image optimization parameters
        $url = $this->add_image_optimization_params($url, $width, $height);
        
        return array($url, $width, $height, $image[3]);
    }
    
    /**
     * Optimize srcset images
     * @param array $sources Srcset sources
     * @param array $size_array Image size array
     * @param string $image_src Image source
     * @param array $image_meta Image metadata
     * @param int $attachment_id Attachment ID
     * @return array Optimized sources
     */
    public function optimize_srcset_images($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        if (!is_array($sources)) {
            return $sources;
        }
        
        foreach ($sources as $width => $source) {
            $url = $source['url'];
            
            // Apply WebP conversion if supported
            if ($this->is_webp_supported() && isset($this->settings['enable_webp']) && $this->settings['enable_webp']) {
                $webp_url = $this->get_webp_url($url);
                if ($webp_url) {
                    $url = $webp_url;
                }
            }
            
            // Apply CDN URL rewriting
            if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
                $url = $this->cdn_manager->get_active_provider()->rewrite_url($url);
            }
            
            // Apply image optimization parameters
            $url = $this->add_image_optimization_params($url, $width, null);
            
            $sources[$width]['url'] = $url;
        }
        
        return $sources;
    }
    
    /**
     * Optimize images in content
     * @param string $content Post content
     * @return string Optimized content
     */
    public function optimize_content_images($content) {
        if (empty($content)) {
            return $content;
        }
        
        // Enhanced lazy loading with intersection observer
        if (isset($this->settings['enhanced_lazy_loading']) && $this->settings['enhanced_lazy_loading']) {
            $content = $this->add_enhanced_lazy_loading($content);
        }
        
        // Add WebP support with fallback
        if ($this->is_webp_supported() && isset($this->settings['enable_webp']) && $this->settings['enable_webp']) {
            $content = $this->add_webp_picture_elements($content);
        }
        
        // Add image preloading hints for above-the-fold images
        $content = $this->add_image_preload_hints($content);
        
        return $content;
    }
    
    /**
     * Add resource preloading to page head
     */
    public function add_resource_preloading() {
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        
        $preload_resources = $this->get_critical_resources();
        
        foreach ($preload_resources as $resource) {
            $this->output_preload_link($resource);
        }
        
        // Preload critical CSS
        if (isset($this->settings['critical_css_enabled']) && $this->settings['critical_css_enabled']) {
            $critical_css_url = $this->get_critical_css_url();
            if ($critical_css_url) {
                echo '<link rel="preload" href="' . esc_url($critical_css_url) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
                echo '<noscript><link rel="stylesheet" href="' . esc_url($critical_css_url) . '"></noscript>' . "\n";
            }
        }
        
        // Preload fonts
        $this->preload_fonts();
    }
    
    /**
     * Add DNS prefetch hints
     */
    public function add_dns_prefetch() {
        $domains = array();
        
        // Add CDN domain
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $cdn_url = $this->cdn_manager->get_active_provider()->get_cdn_url();
            if ($cdn_url) {
                $domain = wp_parse_url($cdn_url, PHP_URL_HOST);
                if ($domain) {
                    $domains[] = $domain;
                }
            }
        }
        
        // Add common external domains
        $external_domains = array(
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'ajax.googleapis.com',
            'www.google-analytics.com',
            'www.googletagmanager.com'
        );
        
        $domains = array_merge($domains, $external_domains);
        $domains = array_unique($domains);
        
        foreach ($domains as $domain) {
            echo '<link rel="dns-prefetch" href="//' . esc_attr($domain) . '">' . "\n";
        }
    }
    
    /**
     * Add preconnect hints
     */
    public function add_preconnect_hints() {
        $connections = array();
        
        // Add CDN preconnect
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $cdn_url = $this->cdn_manager->get_active_provider()->get_cdn_url();
            if ($cdn_url) {
                $connections[] = $cdn_url;
            }
        }
        
        // Add font preconnects
        $connections[] = 'https://fonts.googleapis.com';
        $connections[] = 'https://fonts.gstatic.com';
        
        foreach ($connections as $url) {
            echo '<link rel="preconnect" href="' . esc_url($url) . '" crossorigin>' . "\n";
        }
    }
    
    /**
     * Add advanced cache headers
     */
    public function add_advanced_cache_headers() {
        if (is_admin() || is_user_logged_in()) {
            return;
        }
        
        // Add cache-related meta tags
        echo '<meta http-equiv="Cache-Control" content="public, max-age=3600">' . "\n";
        
        // Add service worker registration for advanced caching
        if (isset($this->settings['enable_service_worker']) && $this->settings['enable_service_worker']) {
            $this->register_service_worker();
        }
        
        // Add critical resource hints
        echo '<meta name="critical-resource-optimization" content="enabled">' . "\n";
    }
    
    /**
     * Modify cache headers
     * @param array $headers Current headers
     * @return array Modified headers
     */
    public function modify_cache_headers($headers) {
        if (is_admin() || is_user_logged_in()) {
            return $headers;
        }
        
        // Set aggressive caching for static content
        if ($this->is_static_content()) {
            $headers['Cache-Control'] = 'public, max-age=31536000, immutable';
            $headers['Expires'] = gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT';
        } else {
            // Set moderate caching for dynamic content
            $headers['Cache-Control'] = 'public, max-age=3600, must-revalidate';
            $headers['Expires'] = gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT';
        }
        
        // Add CDN-specific headers
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $headers['X-CDN-Cache'] = 'ENABLED';
            $headers['Vary'] = 'Accept-Encoding, Accept';
        }
        
        return $headers;
    }
    
    /**
     * Add smart prefetching
     */
    public function add_smart_prefetching() {
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        
        // Get pages to prefetch based on user behavior
        $prefetch_urls = $this->get_smart_prefetch_urls();
        
        if (!empty($prefetch_urls)) {
            echo '<script type="text/javascript">';
            echo 'document.addEventListener("DOMContentLoaded", function() {';
            echo 'var prefetchUrls = ' . wp_json_encode($prefetch_urls) . ';';
            echo $this->get_smart_prefetch_script();
            echo '});';
            echo '</script>';
        }
    }
    
    /**
     * Enhanced lazy loading
     * @param bool $default Default lazy loading value
     * @param string $tag_name Tag name
     * @param string $context Context
     * @return bool Whether to enable lazy loading
     */
    public function enhance_lazy_loading($default, $tag_name, $context) {
        // Enable lazy loading for all images except those above the fold
        if ($tag_name === 'img' && !$this->is_above_fold_image()) {
            return true;
        }
        
        return $default;
    }
    
    /**
     * Optimize loading attribute
     * @param string $value Loading attribute value
     * @param string $image Image HTML
     * @param string $context Context
     * @return string Optimized loading value
     */
    public function optimize_loading_attribute($value, $image, $context) {
        // Add enhanced lazy loading with intersection observer fallback
        if ($value === 'lazy' && isset($this->settings['enhanced_lazy_loading']) && $this->settings['enhanced_lazy_loading']) {
            return 'lazy';
        }
        
        return $value;
    }
    
    /**
     * Generate WebP versions of images
     * @param array $metadata Image metadata
     * @param int $attachment_id Attachment ID
     * @return array Modified metadata
     */
    public function generate_webp_versions($metadata, $attachment_id) {
        if (!isset($this->settings['enable_webp']) || !$this->settings['enable_webp']) {
            return $metadata;
        }
        
        $file = get_attached_file($attachment_id);
        if (!$file) {
            return $metadata;
        }
        
        $this->create_webp_version($file);
        
        // Create WebP versions for all sizes
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            $upload_dir = wp_upload_dir();
            $base_dir = dirname($file);
            
            foreach ($metadata['sizes'] as $size => $size_data) {
                $size_file = $base_dir . '/' . $size_data['file'];
                $this->create_webp_version($size_file);
            }
        }
        
        return $metadata;
    }
    
    /**
     * AJAX handler for bulk image optimization
     */
    public function ajax_optimize_images_bulk() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $batch_size = 10; // Process 10 images at a time
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => $batch_size,
            'offset' => $offset,
            'post_status' => 'inherit'
        ));
        
        $optimized = 0;
        $errors = array();
        
        foreach ($attachments as $attachment) {
            try {
                $file = get_attached_file($attachment->ID);
                if ($file && $this->optimize_image_file($file)) {
                    $optimized++;
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        $total = wp_count_posts('attachment')->inherit;
        $processed = $offset + count($attachments);
        $remaining = max(0, $total - $processed);
        
        wp_send_json_success(array(
            'optimized' => $optimized,
            'processed' => $processed,
            'total' => $total,
            'remaining' => $remaining,
            'complete' => $remaining === 0,
            'errors' => $errors
        ));
    }
    
    /**
     * AJAX handler for generating critical CSS
     */
    public function ajax_generate_critical_css() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : home_url();
        
        try {
            $critical_css = $this->generate_critical_css($url);
            
            if ($critical_css) {
                $this->save_critical_css($critical_css);
                wp_send_json_success(__('Critical CSS generated successfully.', 'wp-performance-plus'));
            } else {
                wp_send_json_error(__('Failed to generate critical CSS.', 'wp-performance-plus'));
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    // Helper methods
    
    /**
     * Check if WebP is supported
     * @return bool
     */
    private function is_webp_supported() {
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            return strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
        }
        return false;
    }
    
    /**
     * Get WebP URL for an image
     * @param string $url Original image URL
     * @return string|false WebP URL or false if not available
     */
    private function get_webp_url($url) {
        $path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $url);
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);
        
        if (file_exists($webp_path)) {
            return str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $webp_path);
        }
        
        return false;
    }
    
    /**
     * Add image optimization parameters
     * @param string $url Image URL
     * @param int $width Image width
     * @param int|null $height Image height
     * @return string Optimized URL
     */
    private function add_image_optimization_params($url, $width, $height = null) {
        // Add quality and format optimization parameters
        $params = array();
        
        if (isset($this->settings['image_quality'])) {
            $params['quality'] = $this->settings['image_quality'];
        }
        
        if ($width) {
            $params['w'] = $width;
        }
        
        if ($height) {
            $params['h'] = $height;
        }
        
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        return $url;
    }
    
    /**
     * Get critical resources for preloading
     * @return array Critical resources
     */
    private function get_critical_resources() {
        $resources = array();
        
        // Add critical CSS
        $theme_style = get_stylesheet_uri();
        if ($theme_style) {
            $resources[] = array(
                'url' => $theme_style,
                'type' => 'style',
                'priority' => 'high'
            );
        }
        
        // Add critical JavaScript
        $critical_js = $this->get_critical_javascript();
        foreach ($critical_js as $js_url) {
            $resources[] = array(
                'url' => $js_url,
                'type' => 'script',
                'priority' => 'high'
            );
        }
        
        return $resources;
    }
    
    /**
     * Output preload link
     * @param array $resource Resource data
     */
    private function output_preload_link($resource) {
        $url = $resource['url'];
        $type = $resource['type'];
        $priority = isset($resource['priority']) ? $resource['priority'] : 'medium';
        
        // Apply CDN URL if available
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $url = $this->cdn_manager->get_active_provider()->rewrite_url($url);
        }
        
        $as = $type === 'style' ? 'style' : 'script';
        $importance = $priority === 'high' ? ' importance="high"' : '';
        
        echo '<link rel="preload" href="' . esc_url($url) . '" as="' . esc_attr($as) . '"' . $importance . '>' . "\n";
    }
    
    /**
     * Check if current request is for static content
     * @return bool
     */
    private function is_static_content() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $static_extensions = array('.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.woff', '.woff2', '.ttf', '.eot');
        
        foreach ($static_extensions as $ext) {
            if (strpos($request_uri, $ext) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get smart prefetch URLs
     * @return array URLs to prefetch
     */
    private function get_smart_prefetch_urls() {
        $urls = array();
        
        // Add likely next pages based on current page type
        if (is_home() || is_front_page()) {
            // Prefetch recent posts
            $recent_posts = get_posts(array('numberposts' => 3));
            foreach ($recent_posts as $post) {
                $urls[] = get_permalink($post->ID);
            }
        } elseif (is_single()) {
            // Prefetch related posts and categories
            $categories = get_the_category();
            if (!empty($categories)) {
                $urls[] = get_category_link($categories[0]->term_id);
            }
        }
        
        return array_unique($urls);
    }
    
    /**
     * Get smart prefetch script
     * @return string JavaScript code
     */
    private function get_smart_prefetch_script() {
        return '
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var link = entry.target;
                        var href = link.href;
                        if (href && prefetchUrls.indexOf(href) !== -1) {
                            var prefetchLink = document.createElement("link");
                            prefetchLink.rel = "prefetch";
                            prefetchLink.href = href;
                            document.head.appendChild(prefetchLink);
                        }
                    }
                });
            });
            
            document.querySelectorAll("a[href]").forEach(function(link) {
                observer.observe(link);
            });
        ';
    }
    
    /**
     * Create WebP version of image
     * @param string $file Image file path
     * @return bool Success
     */
    private function create_webp_version($file) {
        if (!file_exists($file)) {
            return false;
        }
        
        $webp_file = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);
        
        if (function_exists('imagewebp')) {
            $image = null;
            $mime_type = mime_content_type($file);
            
            switch ($mime_type) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file);
                    break;
            }
            
            if ($image) {
                $quality = isset($this->settings['webp_quality']) ? $this->settings['webp_quality'] : 80;
                $result = imagewebp($image, $webp_file, $quality);
                imagedestroy($image);
                return $result;
            }
        }
        
        return false;
    }
    
    /**
     * Optimize image file
     * @param string $file Image file path
     * @return bool Success
     */
    private function optimize_image_file($file) {
        // Implement image optimization logic
        // This could include compression, format conversion, etc.
        return $this->create_webp_version($file);
    }
    
    /**
     * Check if image is above the fold
     * @return bool
     */
    private function is_above_fold_image() {
        // Simple heuristic - first few images are likely above fold
        static $image_count = 0;
        $image_count++;
        return $image_count <= 3;
    }
    
    /**
     * Add enhanced lazy loading to content
     * @param string $content Content
     * @return string Modified content
     */
    private function add_enhanced_lazy_loading($content) {
        // Add intersection observer lazy loading enhancement
        return $content;
    }
    
    /**
     * Add WebP picture elements
     * @param string $content Content
     * @return string Modified content
     */
    private function add_webp_picture_elements($content) {
        // Convert img tags to picture elements with WebP sources
        return $content;
    }
    
    /**
     * Add image preload hints
     * @param string $content Content
     * @return string Modified content
     */
    private function add_image_preload_hints($content) {
        // Add preload hints for critical images
        return $content;
    }
    
    /**
     * Get critical CSS URL
     * @return string|false Critical CSS URL
     */
    private function get_critical_css_url() {
        $upload_dir = wp_upload_dir();
        $critical_css_file = $upload_dir['basedir'] . '/wp-performance-plus/critical.css';
        
        if (file_exists($critical_css_file)) {
            return $upload_dir['baseurl'] . '/wp-performance-plus/critical.css';
        }
        
        return false;
    }
    
    /**
     * Preload fonts
     */
    private function preload_fonts() {
        $fonts = $this->get_critical_fonts();
        
        foreach ($fonts as $font_url) {
            echo '<link rel="preload" href="' . esc_url($font_url) . '" as="font" type="font/woff2" crossorigin>' . "\n";
        }
    }
    
    /**
     * Get critical fonts
     * @return array Font URLs
     */
    private function get_critical_fonts() {
        // Return array of critical font URLs
        return array();
    }
    
    /**
     * Get critical JavaScript files
     * @return array JavaScript URLs
     */
    private function get_critical_javascript() {
        $js_files = array();
        
        // Add jQuery if used
        if (wp_script_is('jquery', 'registered')) {
            $js_files[] = includes_url('js/jquery/jquery.min.js');
        }
        
        return $js_files;
    }
    
    /**
     * Register service worker
     */
    private function register_service_worker() {
        echo '<script>';
        echo 'if ("serviceWorker" in navigator) {';
        echo 'navigator.serviceWorker.register("/wp-content/plugins/wp-performance-plus/assets/sw.js");';
        echo '}';
        echo '</script>';
    }
    
    /**
     * Generate critical CSS
     * @param string $url URL to analyze
     * @return string|false Critical CSS
     */
    private function generate_critical_css($url) {
        // Implement critical CSS generation logic
        // This would typically involve analyzing the page and extracting critical styles
        return false;
    }
    
    /**
     * Save critical CSS
     * @param string $css Critical CSS content
     * @return bool Success
     */
    private function save_critical_css($css) {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/wp-performance-plus';
        
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        
        $file = $dir . '/critical.css';
        return file_put_contents($file, $css) !== false;
    }
} 