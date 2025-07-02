<?php
/**
 * Advanced Analytics Dashboard
 * 
 * Comprehensive performance analytics, reporting, and monitoring
 * with real-time insights, alerting, and enterprise features.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Analytics_Dashboard {
    
    /**
     * Performance monitor instance
     * @var WP_Performance_Plus_Performance_Monitor
     */
    private $performance_monitor;
    
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
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Analytics data
     * @var array
     */
    private $analytics_data = array();
    
    /**
     * Alert thresholds
     * @var array
     */
    private $alert_thresholds = array();
    
    /**
     * Real-time data cache
     * @var array
     */
    private $realtime_cache = array();
    
    /**
     * Constructor
     */
    public function __construct($performance_monitor = null, $cdn_manager = null, $multi_cdn_orchestrator = null) {
        $this->performance_monitor = $performance_monitor;
        $this->cdn_manager = $cdn_manager;
        $this->multi_cdn_orchestrator = $multi_cdn_orchestrator;
        $this->settings = get_option('wp_performance_plus_settings', array());
        
        $this->init_alert_thresholds();
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Analytics dashboard hooks
        add_action('wp_performance_plus_generate_analytics', array($this, 'generate_comprehensive_analytics'));
        add_action('wp_performance_plus_check_alerts', array($this, 'check_performance_alerts'));
        add_action('wp_performance_plus_update_realtime_data', array($this, 'update_realtime_data'));
        
        // AJAX handlers for dashboard data
        add_action('wp_ajax_wp_performance_plus_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_wp_performance_plus_get_performance_timeline', array($this, 'ajax_get_performance_timeline'));
        add_action('wp_ajax_wp_performance_plus_get_cdn_analytics', array($this, 'ajax_get_cdn_analytics'));
        add_action('wp_ajax_wp_performance_plus_get_geographical_data', array($this, 'ajax_get_geographical_data'));
        add_action('wp_ajax_wp_performance_plus_get_user_experience_metrics', array($this, 'ajax_get_user_experience_metrics'));
        add_action('wp_ajax_wp_performance_plus_export_analytics_report', array($this, 'ajax_export_analytics_report'));
        
        // Real-time monitoring
        add_action('wp_ajax_wp_performance_plus_get_realtime_metrics', array($this, 'ajax_get_realtime_metrics'));
        add_action('wp_ajax_wp_performance_plus_start_realtime_monitoring', array($this, 'ajax_start_realtime_monitoring'));
        add_action('wp_ajax_wp_performance_plus_stop_realtime_monitoring', array($this, 'ajax_stop_realtime_monitoring'));
        
        // Alert management
        add_action('wp_ajax_wp_performance_plus_configure_alerts', array($this, 'ajax_configure_alerts'));
        add_action('wp_ajax_wp_performance_plus_test_alert_notification', array($this, 'ajax_test_alert_notification'));
        add_action('wp_ajax_wp_performance_plus_acknowledge_alert', array($this, 'ajax_acknowledge_alert'));
        
        // Multi-site analytics
        if (is_multisite()) {
            add_action('wp_ajax_wp_performance_plus_get_multisite_analytics', array($this, 'ajax_get_multisite_analytics'));
            add_action('wp_ajax_wp_performance_plus_compare_sites_performance', array($this, 'ajax_compare_sites_performance'));
        }
        
        // Scheduled analytics tasks
        add_action('wp_performance_plus_hourly_analytics_update', array($this, 'hourly_analytics_update'));
        add_action('wp_performance_plus_daily_analytics_report', array($this, 'daily_analytics_report'));
        add_action('wp_performance_plus_weekly_analytics_summary', array($this, 'weekly_analytics_summary'));
        add_action('wp_performance_plus_monthly_analytics_archive', array($this, 'monthly_analytics_archive'));
    }
    
    /**
     * Initialize alert thresholds
     */
    private function init_alert_thresholds() {
        $this->alert_thresholds = array_merge(array(
            'page_load_time' => array(
                'warning' => 3.0,   // 3 seconds
                'critical' => 5.0   // 5 seconds
            ),
            'first_contentful_paint' => array(
                'warning' => 2.5,   // 2.5 seconds
                'critical' => 4.0   // 4 seconds
            ),
            'largest_contentful_paint' => array(
                'warning' => 2.5,   // 2.5 seconds
                'critical' => 4.0   // 4 seconds
            ),
            'cumulative_layout_shift' => array(
                'warning' => 0.1,   // 0.1 CLS score
                'critical' => 0.25  // 0.25 CLS score
            ),
            'first_input_delay' => array(
                'warning' => 100,   // 100ms
                'critical' => 300   // 300ms
            ),
            'cdn_cache_hit_ratio' => array(
                'warning' => 80,    // 80%
                'critical' => 70    // 70%
            ),
            'database_queries' => array(
                'warning' => 50,    // 50 queries
                'critical' => 100   // 100 queries
            ),
            'memory_usage' => array(
                'warning' => 128,   // 128MB
                'critical' => 256   // 256MB
            )
        ), get_option('wp_performance_plus_custom_alert_thresholds', array()));
    }
    
    /**
     * Generate comprehensive analytics
     */
    public function generate_comprehensive_analytics() {
        $start_time = microtime(true);
        
        $analytics = array(
            'overview' => $this->generate_overview_analytics(),
            'performance_metrics' => $this->generate_performance_analytics(),
            'cdn_analytics' => $this->generate_cdn_analytics(),
            'user_experience' => $this->generate_user_experience_analytics(),
            'geographical_data' => $this->generate_geographical_analytics(),
            'content_analysis' => $this->generate_content_analysis(),
            'optimization_insights' => $this->generate_optimization_insights(),
            'trends_and_predictions' => $this->generate_trends_and_predictions()
        );
        
        // Add multi-site analytics if applicable
        if (is_multisite()) {
            $analytics['multisite'] = $this->generate_multisite_analytics();
        }
        
        // Cache analytics data
        $this->analytics_data = $analytics;
        set_transient('wp_performance_plus_analytics_cache', $analytics, 15 * MINUTE_IN_SECONDS);
        
        $execution_time = microtime(true) - $start_time;
        
        WP_Performance_Plus_Logger::info('Comprehensive analytics generated', array(
            'execution_time' => $execution_time,
            'data_points' => $this->count_analytics_data_points($analytics)
        ));
        
        return $analytics;
    }
    
    /**
     * Check performance alerts
     */
    public function check_performance_alerts() {
        if (!$this->performance_monitor) {
            return;
        }
        
        $recent_metrics = $this->performance_monitor->get_performance_metrics('1hour');
        $active_alerts = array();
        
        foreach ($recent_metrics['timeline'] as $metric_data) {
            $alerts = $this->evaluate_metric_against_thresholds($metric_data);
            $active_alerts = array_merge($active_alerts, $alerts);
        }
        
        // Check CDN-specific alerts
        if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            $cdn_alerts = $this->check_cdn_alerts();
            $active_alerts = array_merge($active_alerts, $cdn_alerts);
        }
        
        // Process alerts
        foreach ($active_alerts as $alert) {
            $this->process_alert($alert);
        }
        
        // Update alert status
        update_option('wp_performance_plus_active_alerts', $active_alerts);
        
        WP_Performance_Plus_Logger::info('Performance alerts checked', array(
            'active_alerts' => count($active_alerts)
        ));
    }
    
    /**
     * Update real-time data
     */
    public function update_realtime_data() {
        $realtime_data = array(
            'current_load_time' => $this->get_current_load_time(),
            'active_users' => $this->get_active_users_count(),
            'current_memory_usage' => memory_get_usage(true),
            'current_database_queries' => get_num_queries(),
            'cdn_status' => $this->get_current_cdn_status(),
            'cache_status' => $this->get_current_cache_status(),
            'server_response_time' => $this->get_server_response_time(),
            'timestamp' => current_time('mysql')
        );
        
        // Store in transient for quick access
        set_transient('wp_performance_plus_realtime_data', $realtime_data, 2 * MINUTE_IN_SECONDS);
        
        $this->realtime_cache = $realtime_data;
        
        // Trigger real-time alerts if necessary
        $this->check_realtime_alerts($realtime_data);
    }
    
    /**
     * AJAX handler for dashboard data
     */
    public function ajax_get_dashboard_data() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $timeframe = isset($_POST['timeframe']) ? sanitize_key($_POST['timeframe']) : '24hours';
        $include_realtime = isset($_POST['include_realtime']) ? (bool)$_POST['include_realtime'] : false;
        
        $dashboard_data = array(
            'overview' => $this->get_dashboard_overview($timeframe),
            'key_metrics' => $this->get_key_metrics($timeframe),
            'performance_score' => $this->calculate_performance_score($timeframe),
            'alerts' => $this->get_active_alerts(),
            'recommendations' => $this->get_current_recommendations()
        );
        
        if ($include_realtime) {
            $dashboard_data['realtime'] = $this->get_realtime_data();
        }
        
        wp_send_json_success($dashboard_data);
    }
    
    /**
     * AJAX handler for performance timeline
     */
    public function ajax_get_performance_timeline() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $timeframe = isset($_POST['timeframe']) ? sanitize_key($_POST['timeframe']) : '7days';
        $metrics = isset($_POST['metrics']) ? array_map('sanitize_key', $_POST['metrics']) : array('load_time', 'fcp', 'lcp');
        
        $timeline_data = $this->get_performance_timeline($timeframe, $metrics);
        
        wp_send_json_success($timeline_data);
    }
    
    /**
     * AJAX handler for CDN analytics
     */
    public function ajax_get_cdn_analytics() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $timeframe = isset($_POST['timeframe']) ? sanitize_key($_POST['timeframe']) : '7days';
        
        $cdn_analytics = array(
            'usage_statistics' => $this->get_cdn_usage_statistics($timeframe),
            'performance_impact' => $this->get_cdn_performance_impact($timeframe),
            'geographical_distribution' => $this->get_cdn_geographical_data($timeframe),
            'cache_performance' => $this->get_cdn_cache_performance($timeframe),
            'cost_analysis' => $this->get_cdn_cost_analysis($timeframe)
        );
        
        wp_send_json_success($cdn_analytics);
    }
    
    /**
     * AJAX handler for geographical data
     */
    public function ajax_get_geographical_data() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $timeframe = isset($_POST['timeframe']) ? sanitize_key($_POST['timeframe']) : '7days';
        
        $geographical_data = array(
            'performance_by_country' => $this->get_performance_by_country($timeframe),
            'visitor_distribution' => $this->get_visitor_geographical_distribution($timeframe),
            'cdn_edge_performance' => $this->get_cdn_edge_performance($timeframe),
            'latency_map' => $this->generate_latency_map_data($timeframe)
        );
        
        wp_send_json_success($geographical_data);
    }
    
    /**
     * AJAX handler for user experience metrics
     */
    public function ajax_get_user_experience_metrics() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $timeframe = isset($_POST['timeframe']) ? sanitize_key($_POST['timeframe']) : '7days';
        
        $ux_metrics = array(
            'core_web_vitals' => $this->get_core_web_vitals_data($timeframe),
            'user_satisfaction' => $this->calculate_user_satisfaction_score($timeframe),
            'bounce_rate_correlation' => $this->get_bounce_rate_correlation($timeframe),
            'device_performance' => $this->get_device_performance_breakdown($timeframe),
            'page_performance_ranking' => $this->get_page_performance_ranking($timeframe)
        );
        
        wp_send_json_success($ux_metrics);
    }
    
    /**
     * AJAX handler for real-time metrics
     */
    public function ajax_get_realtime_metrics() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $realtime_data = $this->get_realtime_data();
        wp_send_json_success($realtime_data);
    }
    
    /**
     * AJAX handler for exporting analytics report
     */
    public function ajax_export_analytics_report() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $format = isset($_POST['format']) ? sanitize_key($_POST['format']) : 'pdf';
        $timeframe = isset($_POST['timeframe']) ? sanitize_key($_POST['timeframe']) : '7days';
        $sections = isset($_POST['sections']) ? array_map('sanitize_key', $_POST['sections']) : array('overview', 'performance', 'cdn');
        
        try {
            $report_file = $this->generate_analytics_report($format, $timeframe, $sections);
            
            wp_send_json_success(array(
                'download_url' => $report_file['url'],
                'file_size' => $report_file['size'],
                'generated_at' => current_time('mysql')
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * Generate overview analytics
     * @return array Overview data
     */
    private function generate_overview_analytics() {
        $overview = array(
            'total_page_views' => $this->get_total_page_views(),
            'unique_visitors' => $this->get_unique_visitors(),
            'average_load_time' => $this->get_average_load_time(),
            'performance_score' => $this->calculate_current_performance_score(),
            'optimization_savings' => $this->calculate_optimization_savings(),
            'cdn_bandwidth_saved' => $this->calculate_cdn_bandwidth_savings(),
            'uptime_percentage' => $this->calculate_uptime_percentage(),
            'error_rate' => $this->calculate_error_rate()
        );
        
        return $overview;
    }
    
    /**
     * Generate performance analytics
     * @return array Performance data
     */
    private function generate_performance_analytics() {
        if (!$this->performance_monitor) {
            return array();
        }
        
        $timeframes = array('24hours', '7days', '30days');
        $performance_data = array();
        
        foreach ($timeframes as $timeframe) {
            $metrics = $this->performance_monitor->get_performance_metrics($timeframe);
            $performance_data[$timeframe] = array(
                'timeline' => $metrics['timeline'],
                'summary' => $metrics['summary'],
                'core_web_vitals' => $this->extract_core_web_vitals($metrics),
                'performance_trends' => $this->calculate_performance_trends($metrics)
            );
        }
        
        return $performance_data;
    }
    
    /**
     * Generate CDN analytics
     * @return array CDN analytics data
     */
    private function generate_cdn_analytics() {
        if (!$this->cdn_manager || !$this->cdn_manager->is_cdn_enabled()) {
            return array('enabled' => false);
        }
        
        $provider = $this->cdn_manager->get_active_provider();
        $cdn_stats = $provider->get_statistics();
        
        if (is_wp_error($cdn_stats)) {
            return array('enabled' => true, 'error' => $cdn_stats->get_error_message());
        }
        
        return array(
            'enabled' => true,
            'provider' => get_class($provider),
            'statistics' => $cdn_stats,
            'performance_impact' => $this->calculate_cdn_performance_impact(),
            'cost_savings' => $this->calculate_cdn_cost_savings(),
            'geographical_performance' => $this->get_cdn_geographical_performance()
        );
    }
    
    /**
     * Hourly analytics update
     */
    public function hourly_analytics_update() {
        // Update real-time data
        $this->update_realtime_data();
        
        // Check for alerts
        $this->check_performance_alerts();
        
        // Update trending data
        $this->update_trending_analytics();
        
        WP_Performance_Plus_Logger::info('Hourly analytics update completed');
    }
    
    /**
     * Daily analytics report
     */
    public function daily_analytics_report() {
        $analytics = $this->generate_comprehensive_analytics();
        
        // Generate daily summary
        $daily_summary = $this->generate_daily_summary($analytics);
        
        // Save to database
        $this->save_daily_analytics($daily_summary);
        
        // Send email report if configured
        if (isset($this->settings['email_daily_reports']) && $this->settings['email_daily_reports']) {
            $this->send_daily_email_report($daily_summary);
        }
        
        WP_Performance_Plus_Logger::info('Daily analytics report generated');
    }
    
    /**
     * Get real-time data
     * @return array Real-time metrics
     */
    private function get_realtime_data() {
        // Try to get from cache first
        $cached_data = get_transient('wp_performance_plus_realtime_data');
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Generate fresh real-time data
        $this->update_realtime_data();
        return $this->realtime_cache;
    }
    
    /**
     * Calculate performance score
     * @param string $timeframe Timeframe for calculation
     * @return array Performance score data
     */
    private function calculate_performance_score($timeframe = '24hours') {
        if (!$this->performance_monitor) {
            return array('score' => 0, 'grade' => 'F');
        }
        
        $metrics = $this->performance_monitor->get_performance_metrics($timeframe);
        
        // Calculate weighted score based on various factors
        $scores = array();
        
        // Load time score (40% weight)
        if (isset($metrics['summary']['avg_load_time'])) {
            $load_time = $metrics['summary']['avg_load_time'];
            if ($load_time <= 1.0) {
                $scores['load_time'] = 100;
            } elseif ($load_time <= 2.0) {
                $scores['load_time'] = 90;
            } elseif ($load_time <= 3.0) {
                $scores['load_time'] = 75;
            } elseif ($load_time <= 4.0) {
                $scores['load_time'] = 60;
            } else {
                $scores['load_time'] = 30;
            }
        } else {
            $scores['load_time'] = 50;
        }
        
        // Core Web Vitals score (40% weight)
        $cwv_score = $this->calculate_core_web_vitals_score($metrics);
        $scores['core_web_vitals'] = $cwv_score;
        
        // CDN performance score (20% weight)
        $cdn_score = $this->calculate_cdn_score();
        $scores['cdn'] = $cdn_score;
        
        // Calculate final weighted score
        $final_score = round(
            ($scores['load_time'] * 0.4) + 
            ($scores['core_web_vitals'] * 0.4) + 
            ($scores['cdn'] * 0.2)
        );
        
        // Determine grade
        $grade = 'F';
        if ($final_score >= 90) $grade = 'A';
        elseif ($final_score >= 80) $grade = 'B';
        elseif ($final_score >= 70) $grade = 'C';
        elseif ($final_score >= 60) $grade = 'D';
        
        return array(
            'score' => $final_score,
            'grade' => $grade,
            'breakdown' => $scores,
            'recommendations' => $this->get_score_based_recommendations($scores)
        );
    }
    
    // Additional helper methods would be implemented here for:
    // - get_dashboard_overview()
    // - get_key_metrics()
    // - get_active_alerts()
    // - get_current_recommendations()
    // - generate_analytics_report()
    // - calculate_cdn_performance_impact()
    // - get_performance_timeline()
    // - check_realtime_alerts()
    // - process_alert()
    // - And many more specialized analytics methods...
} 