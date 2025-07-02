<?php
/**
 * Performance Monitoring System
 * 
 * Comprehensive performance tracking including CDN analytics,
 * page load times, optimization effectiveness, and user experience metrics.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Performance_Monitor {
    
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
     * Performance metrics
     * @var array
     */
    private $metrics = array();
    
    /**
     * Monitoring start time
     * @var float
     */
    private $start_time;
    
    /**
     * Database table for metrics
     * @var string
     */
    private $metrics_table;
    
    /**
     * Constructor
     */
    public function __construct($cdn_manager = null) {
        global $wpdb;
        
        $this->cdn_manager = $cdn_manager;
        $this->settings = get_option('wp_performance_plus_settings', array());
        $this->metrics_table = $wpdb->prefix . 'wp_performance_plus_metrics';
        $this->start_time = microtime(true);
        
        $this->init_hooks();
        $this->create_metrics_table();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Performance tracking hooks
        add_action('init', array($this, 'start_performance_tracking'), 1);
        add_action('wp_footer', array($this, 'end_performance_tracking'), 999);
        add_action('wp_head', array($this, 'add_performance_monitoring_script'), 1);
        
        // CDN performance monitoring
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            add_action('wp_footer', array($this, 'track_cdn_performance'), 998);
        }
        
        // AJAX handlers for performance data
        add_action('wp_ajax_wp_performance_plus_get_performance_metrics', array($this, 'ajax_get_performance_metrics'));
        add_action('wp_ajax_wp_performance_plus_get_cdn_analytics', array($this, 'ajax_get_cdn_analytics'));
        add_action('wp_ajax_wp_performance_plus_run_speed_test', array($this, 'ajax_run_speed_test'));
        add_action('wp_ajax_wp_performance_plus_get_optimization_recommendations', array($this, 'ajax_get_optimization_recommendations'));
        
        // Real User Monitoring (RUM)
        add_action('wp_ajax_wp_performance_plus_track_rum', array($this, 'ajax_track_rum'));
        add_action('wp_ajax_nopriv_wp_performance_plus_track_rum', array($this, 'ajax_track_rum'));
        
        // Scheduled performance reports
        add_action('wp_performance_plus_daily_report', array($this, 'generate_daily_report'));
        add_action('wp_performance_plus_weekly_report', array($this, 'generate_weekly_report'));
        
        // Error tracking
        add_action('wp_footer', array($this, 'add_error_tracking_script'), 997);
        
        // Core Web Vitals tracking
        add_action('wp_footer', array($this, 'add_core_web_vitals_tracking'), 996);
        
        // Cleanup old metrics
        add_action('wp_performance_plus_cleanup_metrics', array($this, 'cleanup_old_metrics'));
    }
    
    /**
     * Create metrics database table
     */
    private function create_metrics_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->metrics_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            page_url varchar(255) NOT NULL,
            page_type varchar(50) NOT NULL,
            load_time float NOT NULL,
            dom_content_loaded float DEFAULT NULL,
            first_contentful_paint float DEFAULT NULL,
            largest_contentful_paint float DEFAULT NULL,
            cumulative_layout_shift float DEFAULT NULL,
            first_input_delay float DEFAULT NULL,
            memory_usage bigint(20) DEFAULT NULL,
            database_queries int(11) DEFAULT NULL,
            cdn_enabled tinyint(1) DEFAULT 0,
            cdn_provider varchar(50) DEFAULT NULL,
            cdn_cache_hit tinyint(1) DEFAULT NULL,
            optimization_score int(3) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            country varchar(2) DEFAULT NULL,
            device_type varchar(20) DEFAULT NULL,
            connection_type varchar(20) DEFAULT NULL,
            errors text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY timestamp (timestamp),
            KEY page_type (page_type),
            KEY cdn_enabled (cdn_enabled),
            KEY load_time (load_time)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Start performance tracking
     */
    public function start_performance_tracking() {
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        
        $this->metrics['start_time'] = microtime(true);
        $this->metrics['start_memory'] = memory_get_usage();
        $this->metrics['start_queries'] = get_num_queries();
        
        // Track page type
        $this->metrics['page_type'] = $this->get_page_type();
        $this->metrics['page_url'] = $this->get_current_url();
        
        // Track CDN status
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $this->metrics['cdn_enabled'] = true;
            $this->metrics['cdn_provider'] = get_class($this->cdn_manager->get_active_provider());
        } else {
            $this->metrics['cdn_enabled'] = false;
        }
    }
    
    /**
     * End performance tracking and save metrics
     */
    public function end_performance_tracking() {
        if (is_admin() || !isset($this->metrics['start_time'])) {
            return;
        }
        
        $end_time = microtime(true);
        $end_memory = memory_get_peak_usage();
        $end_queries = get_num_queries();
        
        $this->metrics['load_time'] = $end_time - $this->metrics['start_time'];
        $this->metrics['memory_usage'] = $end_memory - $this->metrics['start_memory'];
        $this->metrics['database_queries'] = $end_queries - $this->metrics['start_queries'];
        $this->metrics['optimization_score'] = $this->calculate_optimization_score();
        
        // Add device and connection info
        $this->metrics['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->metrics['device_type'] = $this->detect_device_type();
        $this->metrics['connection_type'] = $this->detect_connection_type();
        $this->metrics['ip_address'] = $this->get_client_ip();
        $this->metrics['country'] = $this->get_country_from_ip($this->metrics['ip_address']);
        
        // Save metrics to database
        $this->save_metrics();
        
        // Log performance data
        WP_Performance_Plus_Logger::debug('Performance metrics recorded', $this->metrics);
    }
    
    /**
     * Add performance monitoring script to frontend
     */
    public function add_performance_monitoring_script() {
        if (is_admin()) {
            return;
        }
        
        $nonce = wp_create_nonce('wp_performance_plus_rum');
        
        echo '<script type="text/javascript">';
        echo 'window.wpPerformancePlusMonitor = {';
        echo 'ajaxUrl: "' . admin_url('admin-ajax.php') . '",';
        echo 'nonce: "' . $nonce . '",';
        echo 'trackingEnabled: ' . (isset($this->settings['enable_rum']) ? 'true' : 'false') . ',';
        echo 'startTime: performance.now()';
        echo '};';
        echo '</script>';
    }
    
    /**
     * Track CDN performance
     */
    public function track_cdn_performance() {
        if (!$this->cdn_manager || !$this->cdn_manager->is_cdn_enabled()) {
            return;
        }
        
        $provider = $this->cdn_manager->get_active_provider();
        $stats = $provider->get_statistics();
        
        if (!is_wp_error($stats)) {
            // Store CDN performance data
            $cdn_metrics = array(
                'timestamp' => current_time('mysql'),
                'provider' => get_class($provider),
                'requests_total' => $stats['requests_total'],
                'bandwidth_total' => $stats['bandwidth_total'],
                'cache_hit_ratio' => $stats['cache_hit_ratio'],
                'threats_blocked' => $stats['threats_blocked']
            );
            
            $this->save_cdn_metrics($cdn_metrics);
        }
    }
    
    /**
     * Add Core Web Vitals tracking
     */
    public function add_core_web_vitals_tracking() {
        if (is_admin()) {
            return;
        }
        
        echo '<script type="text/javascript">';
        echo $this->get_core_web_vitals_script();
        echo '</script>';
    }
    
    /**
     * Add error tracking script
     */
    public function add_error_tracking_script() {
        if (is_admin()) {
            return;
        }
        
        echo '<script type="text/javascript">';
        echo $this->get_error_tracking_script();
        echo '</script>';
    }
    
    /**
     * AJAX handler for getting performance metrics
     */
    public function ajax_get_performance_metrics() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '7days';
        $metrics = $this->get_performance_metrics($period);
        
        wp_send_json_success($metrics);
    }
    
    /**
     * AJAX handler for CDN analytics
     */
    public function ajax_get_cdn_analytics() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '7days';
        $analytics = $this->get_cdn_analytics($period);
        
        wp_send_json_success($analytics);
    }
    
    /**
     * AJAX handler for speed test
     */
    public function ajax_run_speed_test() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : home_url();
        $results = $this->run_speed_test($url);
        
        if ($results) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error(__('Speed test failed.', 'wp-performance-plus'));
        }
    }
    
    /**
     * AJAX handler for optimization recommendations
     */
    public function ajax_get_optimization_recommendations() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $recommendations = $this->get_optimization_recommendations();
        wp_send_json_success($recommendations);
    }
    
    /**
     * AJAX handler for Real User Monitoring data
     */
    public function ajax_track_rum() {
        check_ajax_referer('wp_performance_plus_rum', 'nonce');
        
        $rum_data = array(
            'url' => esc_url_raw($_POST['url'] ?? ''),
            'load_time' => floatval($_POST['load_time'] ?? 0),
            'dom_content_loaded' => floatval($_POST['dom_content_loaded'] ?? 0),
            'first_contentful_paint' => floatval($_POST['first_contentful_paint'] ?? 0),
            'largest_contentful_paint' => floatval($_POST['largest_contentful_paint'] ?? 0),
            'cumulative_layout_shift' => floatval($_POST['cumulative_layout_shift'] ?? 0),
            'first_input_delay' => floatval($_POST['first_input_delay'] ?? 0),
            'connection_type' => sanitize_text_field($_POST['connection_type'] ?? ''),
            'device_memory' => intval($_POST['device_memory'] ?? 0),
            'errors' => sanitize_textarea_field($_POST['errors'] ?? '')
        );
        
        $this->save_rum_data($rum_data);
        wp_send_json_success();
    }
    
    /**
     * Get performance metrics for a given period
     * @param string $period Time period (24hours, 7days, 30days)
     * @return array Performance metrics
     */
    public function get_performance_metrics($period = '7days') {
        global $wpdb;
        
        $date_condition = $this->get_date_condition($period);
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE(timestamp) as date,
                AVG(load_time) as avg_load_time,
                AVG(first_contentful_paint) as avg_fcp,
                AVG(largest_contentful_paint) as avg_lcp,
                AVG(cumulative_layout_shift) as avg_cls,
                AVG(first_input_delay) as avg_fid,
                AVG(optimization_score) as avg_score,
                COUNT(*) as page_views,
                COUNT(DISTINCT ip_address) as unique_visitors
            FROM {$this->metrics_table}
            WHERE timestamp >= %s
            GROUP BY DATE(timestamp)
            ORDER BY date ASC
        ", $date_condition));
        
        return array(
            'timeline' => $results,
            'summary' => array(), // Temporary empty array to prevent fatal error
            'recommendations' => $this->get_optimization_recommendations()
        );
    }
    
    /**
     * Get CDN analytics
     * @param string $period Time period
     * @return array CDN analytics data
     */
    public function get_cdn_analytics($period = '7days') {
        global $wpdb;
        
        $date_condition = $this->get_date_condition($period);
        
        // Get CDN performance comparison
        $cdn_vs_no_cdn = $wpdb->get_results($wpdb->prepare("
            SELECT 
                cdn_enabled,
                AVG(load_time) as avg_load_time,
                AVG(first_contentful_paint) as avg_fcp,
                COUNT(*) as requests
            FROM {$this->metrics_table}
            WHERE timestamp >= %s
            GROUP BY cdn_enabled
        ", $date_condition));
        
        // Get CDN cache hit rates
        $cache_performance = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE(timestamp) as date,
                AVG(CASE WHEN cdn_cache_hit = 1 THEN 1 ELSE 0 END) * 100 as cache_hit_rate,
                COUNT(*) as total_requests
            FROM {$this->metrics_table}
            WHERE timestamp >= %s AND cdn_enabled = 1
            GROUP BY DATE(timestamp)
            ORDER BY date ASC
        ", $date_condition));
        
        return array(
            'comparison' => $cdn_vs_no_cdn,
            'cache_performance' => $cache_performance,
            'geographic_performance' => array(), // Temporary empty array
            'savings' => array() // Temporary empty array
        );
    }
    
    /**
     * Run speed test on given URL
     * @param string $url URL to test
     * @return array|false Speed test results
     */
    public function run_speed_test($url) {
        $start_time = microtime(true);
        
        // Make HTTP request to test page load
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'user-agent' => 'WP Performance Plus Speed Test'
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $load_time = microtime(true) - $start_time;
        $response_code = wp_remote_retrieve_response_code($response);
        $response_size = strlen(wp_remote_retrieve_body($response));
        $headers = wp_remote_retrieve_headers($response);
        
        return array(
            'load_time' => $load_time,
            'response_code' => $response_code,
            'response_size' => $response_size,
            'ttfb' => 0, // Temporary placeholder
            'compression' => false, // Temporary placeholder
            'caching' => false, // Temporary placeholder  
            'cdn_detected' => false, // Temporary placeholder
            'score' => 50, // Temporary placeholder
            'timestamp' => current_time('mysql')
        );
    }
    
    /**
     * Get optimization recommendations
     * @return array Recommendations
     */
    public function get_optimization_recommendations() {
        $recommendations = array();
        
        // Check if CDN is enabled
        if (!$this->cdn_manager || !$this->cdn_manager->is_cdn_enabled()) {
            $recommendations[] = array(
                'type' => 'cdn',
                'priority' => 'high',
                'title' => __('Enable CDN', 'wp-performance-plus'),
                'description' => __('CDN can significantly improve load times by serving content from servers closer to your visitors.', 'wp-performance-plus'),
                'impact' => 'high',
                'effort' => 'medium'
            );
        }
        
        // Check image optimization
        if (!isset($this->settings['enable_webp']) || !$this->settings['enable_webp']) {
            $recommendations[] = array(
                'type' => 'images',
                'priority' => 'medium',
                'title' => __('Enable WebP Images', 'wp-performance-plus'),
                'description' => __('WebP images are 25-35% smaller than JPEG images and can improve page load speeds.', 'wp-performance-plus'),
                'impact' => 'medium',
                'effort' => 'low'
            );
        }
        
        // Check minification
        if (!isset($this->settings['minify_css']) || !$this->settings['minify_css']) {
            $recommendations[] = array(
                'type' => 'css',
                'priority' => 'medium',
                'title' => __('Enable CSS Minification', 'wp-performance-plus'),
                'description' => __('Minifying CSS files reduces their size and improves load times.', 'wp-performance-plus'),
                'impact' => 'low',
                'effort' => 'low'
            );
        }
        
        if (!isset($this->settings['minify_js']) || !$this->settings['minify_js']) {
            $recommendations[] = array(
                'type' => 'javascript',
                'priority' => 'medium',
                'title' => __('Enable JavaScript Minification', 'wp-performance-plus'),
                'description' => __('Minifying JavaScript files reduces their size and improves load times.', 'wp-performance-plus'),
                'impact' => 'low',
                'effort' => 'low'
            );
        }
        
        // Check database optimization
        // TODO: Implement missing method
        // $this->check_database_recommendations($recommendations);
        
        // Check Core Web Vitals
        // TODO: Implement missing method
        // $this->check_core_web_vitals_recommendations($recommendations);
        
        return $recommendations;
    }
    
    /**
     * Generate daily performance report
     */
    public function generate_daily_report() {
        $metrics = $this->get_performance_metrics('24hours');
        // TODO: Implement missing methods for reporting
        // $report = $this->format_performance_report($metrics, 'daily');
        $report = array('metrics' => $metrics, 'type' => 'daily'); // Temporary placeholder
        
        // Send email if configured
        if (isset($this->settings['email_reports']) && $this->settings['email_reports']) {
            // TODO: Implement missing method
            // $this->send_performance_email($report, 'daily');
        }
        
        // Save report
        // TODO: Implement missing method
        // $this->save_performance_report($report, 'daily');
    }
    
    /**
     * Generate weekly performance report
     */
    public function generate_weekly_report() {
        $metrics = $this->get_performance_metrics('7days');
        // TODO: Implement missing methods for reporting
        // $report = $this->format_performance_report($metrics, 'weekly');
        $report = array('metrics' => $metrics, 'type' => 'weekly'); // Temporary placeholder
        
        // Send email if configured
        if (isset($this->settings['email_reports']) && $this->settings['email_reports']) {
            // TODO: Implement missing method
            // $this->send_performance_email($report, 'weekly');
        }
        
        // Save report
        // TODO: Implement missing method
        // $this->save_performance_report($report, 'weekly');
    }
    
    /**
     * Cleanup old metrics data
     */
    public function cleanup_old_metrics() {
        global $wpdb;
        
        $retention_days = isset($this->settings['metrics_retention']) ? $this->settings['metrics_retention'] : 30;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM {$this->metrics_table}
            WHERE timestamp < %s
        ", $cutoff_date));
        
        WP_Performance_Plus_Logger::info('Cleaned up old metrics', array('deleted_rows' => $deleted));
    }
    
    // Helper methods
    
    /**
     * Get current page type
     * @return string Page type
     */
    private function get_page_type() {
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
     * Get current URL
     * @return string Current URL
     */
    private function get_current_url() {
        return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Calculate optimization score
     * @return int Score from 0-100
     */
    private function calculate_optimization_score() {
        $score = 100;
        
        // Deduct points for slow load time
        if ($this->metrics['load_time'] > 3) {
            $score -= 30;
        } elseif ($this->metrics['load_time'] > 2) {
            $score -= 20;
        } elseif ($this->metrics['load_time'] > 1) {
            $score -= 10;
        }
        
        // Deduct points for high memory usage
        if ($this->metrics['memory_usage'] > 50 * 1024 * 1024) { // 50MB
            $score -= 20;
        } elseif ($this->metrics['memory_usage'] > 30 * 1024 * 1024) { // 30MB
            $score -= 10;
        }
        
        // Deduct points for many database queries
        if ($this->metrics['database_queries'] > 50) {
            $score -= 20;
        } elseif ($this->metrics['database_queries'] > 30) {
            $score -= 10;
        }
        
        // Add points for CDN usage
        if ($this->metrics['cdn_enabled']) {
            $score += 10;
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Detect device type
     * @return string Device type
     */
    private function detect_device_type() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet|iPad/', $user_agent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * Detect connection type
     * @return string Connection type
     */
    private function detect_connection_type() {
        // This would typically use Network Information API data sent from frontend
        return 'unknown';
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
     * Get country from IP address
     * @param string $ip IP address
     * @return string Country code
     */
    private function get_country_from_ip($ip) {
        // Simplified geolocation - in production you'd use a proper GeoIP service
        return 'unknown';
    }
    
    /**
     * Save metrics to database
     */
    private function save_metrics() {
        global $wpdb;
        
        $wpdb->insert(
            $this->metrics_table,
            array(
                'page_url' => $this->metrics['page_url'],
                'page_type' => $this->metrics['page_type'],
                'load_time' => $this->metrics['load_time'],
                'memory_usage' => $this->metrics['memory_usage'],
                'database_queries' => $this->metrics['database_queries'],
                'cdn_enabled' => $this->metrics['cdn_enabled'] ? 1 : 0,
                'cdn_provider' => $this->metrics['cdn_provider'] ?? null,
                'optimization_score' => $this->metrics['optimization_score'],
                'user_agent' => $this->metrics['user_agent'],
                'ip_address' => $this->metrics['ip_address'],
                'country' => $this->metrics['country'],
                'device_type' => $this->metrics['device_type'],
                'connection_type' => $this->metrics['connection_type']
            ),
            array('%s', '%s', '%f', '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Save CDN metrics
     * @param array $metrics CDN metrics data
     */
    private function save_cdn_metrics($metrics) {
        // Save to a separate CDN metrics table or option
        $existing_metrics = get_option('wp_performance_plus_cdn_metrics', array());
        $existing_metrics[] = $metrics;
        
        // Keep only last 100 entries
        if (count($existing_metrics) > 100) {
            $existing_metrics = array_slice($existing_metrics, -100);
        }
        
        update_option('wp_performance_plus_cdn_metrics', $existing_metrics);
    }
    
    /**
     * Save Real User Monitoring data
     * @param array $rum_data RUM data
     */
    private function save_rum_data($rum_data) {
        global $wpdb;
        
        $wpdb->insert(
            $this->metrics_table,
            array(
                'page_url' => $rum_data['url'],
                'page_type' => 'rum',
                'load_time' => $rum_data['load_time'],
                'dom_content_loaded' => $rum_data['dom_content_loaded'],
                'first_contentful_paint' => $rum_data['first_contentful_paint'],
                'largest_contentful_paint' => $rum_data['largest_contentful_paint'],
                'cumulative_layout_shift' => $rum_data['cumulative_layout_shift'],
                'first_input_delay' => $rum_data['first_input_delay'],
                'connection_type' => $rum_data['connection_type'],
                'errors' => $rum_data['errors']
            )
        );
    }
    
    /**
     * Get Core Web Vitals tracking script
     * @return string JavaScript code
     */
    private function get_core_web_vitals_script() {
        return '
            (function() {
                if (!window.wpPerformancePlusMonitor || !window.wpPerformancePlusMonitor.trackingEnabled) return;
                
                var vitals = {};
                var observer;
                
                // Largest Contentful Paint
                if ("LargestContentfulPaint" in window) {
                    observer = new PerformanceObserver(function(list) {
                        var entries = list.getEntries();
                        vitals.lcp = entries[entries.length - 1].startTime;
                    });
                    observer.observe({entryTypes: ["largest-contentful-paint"]});
                }
                
                // First Input Delay
                if ("FirstInputDelay" in window) {
                    observer = new PerformanceObserver(function(list) {
                        var entries = list.getEntries();
                        vitals.fid = entries[0].processingStart - entries[0].startTime;
                    });
                    observer.observe({entryTypes: ["first-input"]});
                }
                
                // Cumulative Layout Shift
                var clsValue = 0;
                var sessionValue = 0;
                var sessionEntries = [];
                
                if ("LayoutShift" in window) {
                    observer = new PerformanceObserver(function(list) {
                        for (var entry of list.getEntries()) {
                            if (!entry.hadRecentInput) {
                                sessionValue += entry.value;
                                sessionEntries.push(entry);
                            }
                        }
                        vitals.cls = sessionValue;
                    });
                    observer.observe({entryTypes: ["layout-shift"]});
                }
                
                // Send data when page unloads
                window.addEventListener("beforeunload", function() {
                    if (Object.keys(vitals).length > 0) {
                        navigator.sendBeacon(window.wpPerformancePlusMonitor.ajaxUrl, new URLSearchParams({
                            action: "wp_performance_plus_track_rum",
                            nonce: window.wpPerformancePlusMonitor.nonce,
                            url: window.location.href,
                            load_time: performance.now() - window.wpPerformancePlusMonitor.startTime,
                            dom_content_loaded: vitals.domContentLoaded || 0,
                            first_contentful_paint: vitals.fcp || 0,
                            largest_contentful_paint: vitals.lcp || 0,
                            cumulative_layout_shift: vitals.cls || 0,
                            first_input_delay: vitals.fid || 0,
                            connection_type: navigator.connection ? navigator.connection.effectiveType : "unknown",
                            device_memory: navigator.deviceMemory || 0
                        }));
                    }
                });
            })();
        ';
    }
    
    /**
     * Get error tracking script
     * @return string JavaScript code
     */
    private function get_error_tracking_script() {
        return '
            window.addEventListener("error", function(e) {
                if (window.wpPerformancePlusMonitor && window.wpPerformancePlusMonitor.trackingEnabled) {
                    var errorData = {
                        message: e.message,
                        filename: e.filename,
                        lineno: e.lineno,
                        colno: e.colno,
                        stack: e.error ? e.error.stack : "",
                        timestamp: new Date().toISOString()
                    };
                    
                    fetch(window.wpPerformancePlusMonitor.ajaxUrl, {
                        method: "POST",
                        body: new URLSearchParams({
                            action: "wp_performance_plus_track_rum",
                            nonce: window.wpPerformancePlusMonitor.nonce,
                            url: window.location.href,
                            errors: JSON.stringify([errorData])
                        })
                    });
                }
            });
        ';
    }
    
    /**
     * Get date condition for SQL queries
     * @param string $period Period (24hours, 7days, 30days)
     * @return string Date condition
     */
    private function get_date_condition($period) {
        switch ($period) {
            case '24hours':
                return date('Y-m-d H:i:s', strtotime('-24 hours'));
            case '7days':
                return date('Y-m-d H:i:s', strtotime('-7 days'));
            case '30days':
                return date('Y-m-d H:i:s', strtotime('-30 days'));
            default:
                return date('Y-m-d H:i:s', strtotime('-7 days'));
        }
    }
    
    /**
     * Additional helper methods would go here for:
     * - get_performance_summary()
     * - get_geographic_performance()
     * - calculate_cdn_savings()
     * - calculate_ttfb()
     * - check_compression()
     * - check_caching()
     * - detect_cdn_usage()
     * - calculate_speed_score()
     * - check_database_recommendations()
     * - check_core_web_vitals_recommendations()
     * - format_performance_report()
     * - send_performance_email()
     * - save_performance_report()
     */
} 