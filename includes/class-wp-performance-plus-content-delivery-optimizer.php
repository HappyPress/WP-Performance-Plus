<?php
/**
 * Content Delivery Optimization System
 * 
 * Intelligent content delivery optimization with cache warming,
 * smart purging, adaptive strategies, and automated optimization.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Content_Delivery_Optimizer {
    
    /**
     * CDN manager instance
     * @var WP_Performance_Plus_CDN_Manager
     */
    private $cdn_manager;
    
    /**
     * Multi-CDN orchestrator
     * @var WP_Performance_Plus_Multi_CDN_Orchestrator
     */
    private $multi_cdn_orchestrator;
    
    /**
     * Performance monitor instance
     * @var WP_Performance_Plus_Performance_Monitor
     */
    private $performance_monitor;
    
    /**
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Cache warming queue
     * @var array
     */
    private $cache_warming_queue = array();
    
    /**
     * Content optimization rules
     * @var array
     */
    private $optimization_rules = array();
    
    /**
     * User behavior patterns
     * @var array
     */
    private $user_patterns = array();
    
    /**
     * Content priority matrix
     * @var array
     */
    private $content_priority_matrix = array();
    
    /**
     * Constructor
     */
    public function __construct($cdn_manager = null, $multi_cdn_orchestrator = null, $performance_monitor = null) {
        $this->cdn_manager = $cdn_manager;
        $this->multi_cdn_orchestrator = $multi_cdn_orchestrator;
        $this->performance_monitor = $performance_monitor;
        $this->settings = get_option('wp_performance_plus_settings', array());
        
        $this->init_optimization_rules();
        $this->init_hooks();
        $this->analyze_user_patterns();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Content optimization hooks
        add_action('wp_performance_plus_optimize_content_delivery', array($this, 'optimize_content_delivery'));
        add_action('wp_performance_plus_warm_cache', array($this, 'warm_cache'));
        add_action('wp_performance_plus_intelligent_purge', array($this, 'intelligent_cache_purge'));
        
        // Content lifecycle hooks
        add_action('save_post', array($this, 'handle_content_update'), 10, 3);
        add_action('delete_post', array($this, 'handle_content_deletion'), 10, 1);
        add_action('wp_update_nav_menu', array($this, 'handle_menu_update'));
        add_action('customize_save_after', array($this, 'handle_customizer_save'));
        
        // User behavior tracking
        add_action('wp_footer', array($this, 'track_user_behavior'), 5);
        add_action('wp_ajax_wp_performance_plus_track_content_interaction', array($this, 'ajax_track_content_interaction'));
        add_action('wp_ajax_nopriv_wp_performance_plus_track_content_interaction', array($this, 'ajax_track_content_interaction'));
        
        // Scheduled optimization tasks
        add_action('wp_performance_plus_hourly_content_optimization', array($this, 'hourly_content_optimization'));
        add_action('wp_performance_plus_daily_cache_warming', array($this, 'daily_cache_warming'));
        add_action('wp_performance_plus_weekly_content_analysis', array($this, 'weekly_content_analysis'));
        
        // Real-time optimization
        add_action('wp_head', array($this, 'add_adaptive_optimization'), 2);
        add_filter('wp_get_attachment_url', array($this, 'optimize_media_delivery'), 10, 2);
        
        // AJAX handlers for manual optimization
        add_action('wp_ajax_wp_performance_plus_manual_cache_warm', array($this, 'ajax_manual_cache_warm'));
        add_action('wp_ajax_wp_performance_plus_analyze_content_performance', array($this, 'ajax_analyze_content_performance'));
        add_action('wp_ajax_wp_performance_plus_optimize_specific_content', array($this, 'ajax_optimize_specific_content'));
    }
    
    /**
     * Initialize content optimization rules
     */
    private function init_optimization_rules() {
        $this->optimization_rules = array(
            'critical_content' => array(
                'priority' => 'high',
                'cache_ttl' => 86400, // 24 hours
                'preload' => true,
                'optimization_level' => 'aggressive'
            ),
            'popular_content' => array(
                'priority' => 'medium',
                'cache_ttl' => 43200, // 12 hours
                'preload' => true,
                'optimization_level' => 'balanced'
            ),
            'standard_content' => array(
                'priority' => 'normal',
                'cache_ttl' => 21600, // 6 hours
                'preload' => false,
                'optimization_level' => 'standard'
            ),
            'low_priority_content' => array(
                'priority' => 'low',
                'cache_ttl' => 7200, // 2 hours
                'preload' => false,
                'optimization_level' => 'minimal'
            )
        );
    }
    
    /**
     * Optimize content delivery based on current conditions
     */
    public function optimize_content_delivery() {
        $start_time = microtime(true);
        
        // Analyze current performance metrics
        $current_performance = $this->analyze_current_performance();
        
        // Identify optimization opportunities
        $optimization_opportunities = $this->identify_optimization_opportunities($current_performance);
        
        // Apply optimizations
        $applied_optimizations = array();
        
        foreach ($optimization_opportunities as $opportunity) {
            $result = $this->apply_optimization($opportunity);
            if ($result) {
                $applied_optimizations[] = $opportunity;
            }
        }
        
        // Update content priority matrix
        $this->update_content_priority_matrix();
        
        // Warm critical content cache
        $this->warm_critical_content_cache();
        
        $execution_time = microtime(true) - $start_time;
        
        WP_Performance_Plus_Logger::info('Content delivery optimized', array(
            'execution_time' => $execution_time,
            'opportunities_found' => count($optimization_opportunities),
            'optimizations_applied' => count($applied_optimizations),
            'optimizations' => $applied_optimizations
        ));
    }
    
    /**
     * Intelligent cache warming
     */
    public function warm_cache() {
        $urls_to_warm = $this->get_cache_warming_queue();
        
        if (empty($urls_to_warm)) {
            $urls_to_warm = $this->generate_cache_warming_queue();
        }
        
        $warmed_count = 0;
        $batch_size = 10; // Warm 10 URLs at a time to avoid overloading
        
        foreach (array_slice($urls_to_warm, 0, $batch_size) as $url_data) {
            $result = $this->warm_single_url($url_data);
            if ($result) {
                $warmed_count++;
            }
        }
        
        // Remove processed URLs from queue
        $this->update_cache_warming_queue(array_slice($urls_to_warm, $batch_size));
        
        WP_Performance_Plus_Logger::info('Cache warming completed', array(
            'warmed_count' => $warmed_count,
            'remaining_queue' => count($urls_to_warm) - $batch_size
        ));
    }
    
    /**
     * Intelligent cache purging
     */
    public function intelligent_cache_purge($content_id = null, $content_type = null) {
        if ($content_id && $content_type) {
            // Targeted purging for specific content
            $related_urls = $this->get_content_related_urls($content_id, $content_type);
        } else {
            // General intelligent purging
            $related_urls = $this->identify_stale_content();
        }
        
        $purge_results = array();
        
        foreach ($related_urls as $url_group => $urls) {
            $purge_result = $this->purge_url_group($url_group, $urls);
            $purge_results[$url_group] = $purge_result;
        }
        
        // Schedule cache warming for purged content
        $this->schedule_cache_warming_for_purged_content($related_urls);
        
        WP_Performance_Plus_Logger::info('Intelligent cache purge completed', array(
            'content_id' => $content_id,
            'content_type' => $content_type,
            'purge_results' => $purge_results
        ));
    }
    
    /**
     * Handle content update
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     * @param bool $update Whether this is an update
     */
    public function handle_content_update($post_id, $post, $update) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Determine content priority
        $content_priority = $this->determine_content_priority($post);
        
        // Intelligent purging based on content relationships
        $this->intelligent_cache_purge($post_id, 'post');
        
        // Schedule cache warming if high priority content
        if ($content_priority === 'high' || $content_priority === 'medium') {
            $this->schedule_immediate_cache_warming($post_id, 'post');
        }
        
        // Update user behavior patterns if popular content
        if ($this->is_popular_content($post_id, 'post')) {
            $this->update_user_patterns_for_content($post_id, 'post');
        }
        
        WP_Performance_Plus_Logger::debug('Content update handled', array(
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'priority' => $content_priority
        ));
    }
    
    /**
     * Track user behavior for content optimization
     */
    public function track_user_behavior() {
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        
        $tracking_data = array(
            'page_id' => get_queried_object_id(),
            'page_type' => $this->get_current_page_type(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'timestamp' => current_time('mysql')
        );
        
        echo '<script type="text/javascript">';
        echo 'if (typeof wpPerformancePlusOptimizer === "undefined") {';
        echo 'window.wpPerformancePlusOptimizer = {';
        echo 'trackingData: ' . wp_json_encode($tracking_data) . ',';
        echo 'ajaxUrl: "' . admin_url('admin-ajax.php') . '",';
        echo 'nonce: "' . wp_create_nonce('wp_performance_plus_content_tracking') . '"';
        echo '};';
        echo '}';
        echo '</script>';
    }
    
    /**
     * Add adaptive optimization to page head
     */
    public function add_adaptive_optimization() {
        if (is_admin()) {
            return;
        }
        
        $current_page_id = get_queried_object_id();
        $page_type = $this->get_current_page_type();
        
        // Get adaptive optimization strategies for current page
        $strategies = $this->get_adaptive_strategies($current_page_id, $page_type);
        
        // Apply resource hints based on user behavior
        $this->add_behavioral_resource_hints($strategies);
        
        // Add critical content preloading
        $this->add_critical_content_preloading($strategies);
        
        // Add adaptive image optimization
        $this->add_adaptive_image_optimization($strategies);
    }
    
    /**
     * Optimize media delivery
     * @param string $url Media URL
     * @param int $attachment_id Attachment ID
     * @return string Optimized URL
     */
    public function optimize_media_delivery($url, $attachment_id) {
        // Get optimal CDN provider for this media type
        $media_type = $this->get_media_type($attachment_id);
        $optimal_provider = $this->get_optimal_provider_for_media($media_type);
        
        if ($optimal_provider) {
            $url = $optimal_provider->rewrite_url($url);
        }
        
        // Add adaptive parameters based on user context
        $url = $this->add_adaptive_media_parameters($url, $attachment_id);
        
        return $url;
    }
    
    /**
     * AJAX handler for tracking content interaction
     */
    public function ajax_track_content_interaction() {
        check_ajax_referer('wp_performance_plus_content_tracking', 'nonce');
        
        $interaction_data = array(
            'content_id' => intval($_POST['content_id'] ?? 0),
            'interaction_type' => sanitize_key($_POST['interaction_type'] ?? ''),
            'interaction_value' => sanitize_text_field($_POST['interaction_value'] ?? ''),
            'timestamp' => current_time('mysql'),
            'user_ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        
        $this->record_content_interaction($interaction_data);
        
        wp_send_json_success();
    }
    
    /**
     * Hourly content optimization
     */
    public function hourly_content_optimization() {
        // Analyze recent performance data
        $recent_performance = $this->analyze_recent_performance();
        
        // Update content priority matrix based on performance
        $this->update_content_priority_based_on_performance($recent_performance);
        
        // Optimize cache warming queue
        $this->optimize_cache_warming_queue();
        
        // Trigger intelligent purging for stale content
        $stale_content = $this->identify_stale_content();
        if (!empty($stale_content)) {
            $this->intelligent_cache_purge();
        }
        
        WP_Performance_Plus_Logger::info('Hourly content optimization completed');
    }
    
    /**
     * Daily cache warming
     */
    public function daily_cache_warming() {
        // Generate comprehensive cache warming queue
        $warming_queue = $this->generate_comprehensive_warming_queue();
        
        // Prioritize based on user behavior patterns
        $prioritized_queue = $this->prioritize_warming_queue($warming_queue);
        
        // Save for processing
        $this->save_cache_warming_queue($prioritized_queue);
        
        // Start immediate warming for critical content
        $this->warm_critical_content_immediately();
        
        WP_Performance_Plus_Logger::info('Daily cache warming initialized', array(
            'queue_size' => count($prioritized_queue)
        ));
    }
    
    /**
     * Weekly content analysis
     */
    public function weekly_content_analysis() {
        // Analyze content performance over the past week
        $weekly_performance = $this->analyze_weekly_content_performance();
        
        // Update optimization rules based on analysis
        $this->update_optimization_rules($weekly_performance);
        
        // Identify trending content
        $trending_content = $this->identify_trending_content($weekly_performance);
        
        // Update content priority matrix
        $this->update_content_priority_matrix_from_trends($trending_content);
        
        // Generate optimization recommendations
        $recommendations = $this->generate_weekly_optimization_recommendations($weekly_performance);
        
        // Save analysis results
        update_option('wp_performance_plus_weekly_content_analysis', array(
            'performance_data' => $weekly_performance,
            'trending_content' => $trending_content,
            'recommendations' => $recommendations,
            'analysis_date' => current_time('mysql')
        ));
        
        WP_Performance_Plus_Logger::info('Weekly content analysis completed', array(
            'trending_content_count' => count($trending_content),
            'recommendations_count' => count($recommendations)
        ));
    }
    
    // Helper methods
    
    /**
     * Analyze current performance
     * @return array Performance analysis
     */
    private function analyze_current_performance() {
        if (!$this->performance_monitor) {
            return array();
        }
        
        return $this->performance_monitor->get_performance_metrics('24hours');
    }
    
    /**
     * Identify optimization opportunities
     * @param array $performance_data Performance data
     * @return array Optimization opportunities
     */
    private function identify_optimization_opportunities($performance_data) {
        $opportunities = array();
        
        // Check for slow-loading content
        if (isset($performance_data['timeline'])) {
            foreach ($performance_data['timeline'] as $day_data) {
                if ($day_data->avg_load_time > 3.0) { // 3 seconds threshold
                    $opportunities[] = array(
                        'type' => 'slow_loading_content',
                        'priority' => 'high',
                        'data' => $day_data
                    );
                }
            }
        }
        
        // Check for cache hit ratio issues
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $cdn_stats = $this->cdn_manager->get_active_provider()->get_statistics();
            if (!is_wp_error($cdn_stats) && $cdn_stats['cache_hit_ratio'] < 80) {
                $opportunities[] = array(
                    'type' => 'low_cache_hit_ratio',
                    'priority' => 'medium',
                    'data' => $cdn_stats
                );
            }
        }
        
        // Check for popular uncached content
        $popular_uncached = $this->identify_popular_uncached_content();
        if (!empty($popular_uncached)) {
            $opportunities[] = array(
                'type' => 'popular_uncached_content',
                'priority' => 'high',
                'data' => $popular_uncached
            );
        }
        
        return $opportunities;
    }
    
    /**
     * Apply optimization
     * @param array $opportunity Optimization opportunity
     * @return bool Success
     */
    private function apply_optimization($opportunity) {
        switch ($opportunity['type']) {
            case 'slow_loading_content':
                return $this->optimize_slow_loading_content($opportunity['data']);
                
            case 'low_cache_hit_ratio':
                return $this->improve_cache_hit_ratio($opportunity['data']);
                
            case 'popular_uncached_content':
                return $this->cache_popular_content($opportunity['data']);
                
            default:
                return false;
        }
    }
    
    /**
     * Analyze user patterns
     */
    private function analyze_user_patterns() {
        // Get user behavior data from the last 30 days
        $behavior_data = get_option('wp_performance_plus_user_behavior', array());
        
        // Analyze patterns
        $this->user_patterns = array(
            'popular_pages' => $this->extract_popular_pages($behavior_data),
            'common_paths' => $this->extract_common_user_paths($behavior_data),
            'peak_hours' => $this->extract_peak_hours($behavior_data),
            'device_preferences' => $this->extract_device_preferences($behavior_data)
        );
        
        WP_Performance_Plus_Logger::debug('User patterns analyzed', $this->user_patterns);
    }
    
    /**
     * Get current page type
     * @return string Page type
     */
    private function get_current_page_type() {
        if (is_front_page()) return 'front_page';
        if (is_home()) return 'blog_home';
        if (is_single()) return 'single_post';
        if (is_page()) return 'page';
        if (is_category()) return 'category';
        if (is_tag()) return 'tag';
        if (is_archive()) return 'archive';
        if (is_search()) return 'search';
        if (is_404()) return '404';
        return 'other';
    }
    
    /**
     * Get client IP address
     * @return string IP address
     */
    private function get_client_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Additional methods would be implemented here for:
     * - generate_cache_warming_queue()
     * - warm_single_url()
     * - get_content_related_urls()
     * - determine_content_priority()
     * - get_adaptive_strategies()
     * - add_behavioral_resource_hints()
     * - add_critical_content_preloading()
     * - add_adaptive_image_optimization()
     * - get_optimal_provider_for_media()
     * - record_content_interaction()
     * - identify_popular_uncached_content()
     * - optimize_slow_loading_content()
     * - improve_cache_hit_ratio()
     * - cache_popular_content()
     * - And many more specialized optimization methods...
     */
} 