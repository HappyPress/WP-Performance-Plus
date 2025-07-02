<?php
/**
 * The core plugin class.
 */
class WP_Performance_Plus {
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $admin;
    protected $debug;
    protected $cdn_manager;
    protected $advanced_optimization;
    protected $performance_monitor;
    
    // New enterprise-grade systems
    protected $multi_cdn_orchestrator;
    protected $content_delivery_optimizer;
    protected $analytics_dashboard;
    protected $enterprise_scaler;

    public function __construct() {
        $this->plugin_name = 'wp-performance-plus';
        $this->version = defined('WP_PERFORMANCE_PLUS_VERSION') ? WP_PERFORMANCE_PLUS_VERSION : '1.0.0';
        
        $this->load_dependencies();
        
        // Initialize logger first
        WP_Performance_Plus_Logger::init();
        
        // Log plugin initialization
        WP_Performance_Plus_Logger::info('Plugin initialized', [
            'version' => $this->version,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'is_multisite' => is_multisite(),
            'memory_limit' => ini_get('memory_limit')
        ]);
        
        // Initialize core systems first
        $this->init_cdn_manager();
        $this->init_advanced_optimization();
        $this->init_performance_monitor();
        
        // Initialize enterprise systems
        $this->init_multi_cdn_orchestrator();
        // TODO: Re-enable when content delivery optimizer methods are fully implemented
        // $this->init_content_delivery_optimizer();
        // TODO: Re-enable when performance monitor methods are fully implemented  
        // $this->init_analytics_dashboard();
        // $this->init_enterprise_scaler();
        
        // Initialize WordPress hooks
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->init_debug();
        
        // Schedule enterprise tasks
        $this->schedule_enterprise_tasks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-loader.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-logger.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-cache.php';
        require_once plugin_dir_path(__FILE__) . 'abstract-class-wp-performance-plus-cdn.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-cdn-manager.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-advanced-optimization.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-performance-monitor.php';
        
        // Load enterprise systems
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-multi-cdn-orchestrator.php';
        // TODO: Re-enable when content delivery optimizer is fully implemented
        // require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-content-delivery-optimizer.php';
        // TODO: Re-enable when performance monitor methods are fully implemented
        // require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-analytics-dashboard.php';
        // require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-enterprise-scaler.php';
        
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-debug.php';

        // Get the loader instance
        $this->loader = WP_Performance_Plus_Loader::get_instance();
    }

    /**
     * Initialize CDN manager
     */
    private function init_cdn_manager() {
        try {
            $this->cdn_manager = new WP_Performance_Plus_CDN_Manager();
            
            WP_Performance_Plus_Logger::info('CDN Manager initialized successfully', [
                'providers_loaded' => count($this->cdn_manager->get_providers()),
                'active_provider' => $this->cdn_manager->get_active_provider() ? 
                    get_class($this->cdn_manager->get_active_provider()) : 'none'
            ]);
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('CDN Manager initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Initialize advanced optimization features
     */
    private function init_advanced_optimization() {
        try {
            $this->advanced_optimization = new WP_Performance_Plus_Advanced_Optimization($this->cdn_manager);
            
            WP_Performance_Plus_Logger::info('Advanced Optimization initialized successfully');
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Advanced Optimization initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Initialize performance monitoring
     */
    private function init_performance_monitor() {
        try {
            $this->performance_monitor = new WP_Performance_Plus_Performance_Monitor($this->cdn_manager);
            
            WP_Performance_Plus_Logger::info('Performance Monitor initialized successfully');
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Performance Monitor initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Initialize Multi-CDN Orchestrator
     */
    private function init_multi_cdn_orchestrator() {
        try {
            if ($this->cdn_manager) {
                $providers = $this->cdn_manager->get_providers();
                $this->multi_cdn_orchestrator = new WP_Performance_Plus_Multi_CDN_Orchestrator(
                    $providers,
                    $this->performance_monitor
                );
                
                WP_Performance_Plus_Logger::info('Multi-CDN Orchestrator initialized successfully', [
                    'provider_count' => count($providers)
                ]);
            }
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Multi-CDN Orchestrator initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Initialize Content Delivery Optimizer
     */
    private function init_content_delivery_optimizer() {
        try {
            $this->content_delivery_optimizer = new WP_Performance_Plus_Content_Delivery_Optimizer(
                $this->cdn_manager,
                $this->multi_cdn_orchestrator,
                $this->performance_monitor
            );
            
            WP_Performance_Plus_Logger::info('Content Delivery Optimizer initialized successfully');
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Content Delivery Optimizer initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Initialize Analytics Dashboard
     */
    private function init_analytics_dashboard() {
        try {
            $this->analytics_dashboard = new WP_Performance_Plus_Analytics_Dashboard(
                $this->performance_monitor,
                $this->cdn_manager,
                $this->multi_cdn_orchestrator
            );
            
            WP_Performance_Plus_Logger::info('Analytics Dashboard initialized successfully');
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Analytics Dashboard initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Initialize Enterprise Scaler
     */
    private function init_enterprise_scaler() {
        try {
            $this->enterprise_scaler = new WP_Performance_Plus_Enterprise_Scaler(
                $this->cdn_manager,
                $this->multi_cdn_orchestrator,
                $this->performance_monitor,
                $this->analytics_dashboard
            );
            
            WP_Performance_Plus_Logger::info('Enterprise Scaler initialized successfully', [
                'multisite_enabled' => is_multisite()
            ]);
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Enterprise Scaler initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Schedule enterprise tasks
     */
    private function schedule_enterprise_tasks() {
        // CDN health monitoring
        if (!wp_next_scheduled('wp_performance_plus_check_cdn_health')) {
            wp_schedule_event(time(), 'hourly', 'wp_performance_plus_check_cdn_health');
        }

        // Content delivery optimization
        // TODO: Re-enable when content delivery optimizer is fully implemented
        // if (!wp_next_scheduled('wp_performance_plus_optimize_content_delivery')) {
        //     wp_schedule_event(time(), 'twicedaily', 'wp_performance_plus_optimize_content_delivery');
        // }

        // Performance analytics generation
        // TODO: Re-enable when analytics dashboard is fully implemented
        // if (!wp_next_scheduled('wp_performance_plus_generate_analytics')) {
        //     wp_schedule_event(time(), 'hourly', 'wp_performance_plus_generate_analytics');
        // }

        // Resource monitoring
        // TODO: Re-enable when enterprise scaler is fully implemented
        // if (!wp_next_scheduled('wp_performance_plus_monitor_resources')) {
        //     wp_schedule_event(time(), 'wp_performance_plus_fifteen_minutes', 'wp_performance_plus_monitor_resources');
        // }

        // Auto-scaling checks
        // TODO: Re-enable when enterprise scaler is fully implemented
        // if (!wp_next_scheduled('wp_performance_plus_check_scaling_conditions')) {
        //     wp_schedule_event(time(), 'wp_performance_plus_five_minutes', 'wp_performance_plus_check_scaling_conditions');
        // }

        // Network optimization (for multisite)
        // TODO: Re-enable when enterprise scaler is fully implemented
        // if (is_multisite() && !wp_next_scheduled('wp_performance_plus_optimize_network_performance')) {
        //     wp_schedule_event(time(), 'daily', 'wp_performance_plus_optimize_network_performance');
        // }

        // Cache warming
        // TODO: Re-enable when content delivery optimizer is fully implemented
        // if (!wp_next_scheduled('wp_performance_plus_warm_cache')) {
        //     wp_schedule_event(time(), 'hourly', 'wp_performance_plus_warm_cache');
        // }

        // Performance alerts check
        if (!wp_next_scheduled('wp_performance_plus_check_alerts')) {
            wp_schedule_event(time(), 'wp_performance_plus_five_minutes', 'wp_performance_plus_check_alerts');
        }

        // Add custom cron intervals
        add_filter('cron_schedules', array($this, 'add_custom_cron_intervals'));
    }

    /**
     * Add custom cron intervals
     */
    public function add_custom_cron_intervals($schedules) {
        $schedules['wp_performance_plus_five_minutes'] = array(
            'interval' => 300,
            'display' => __('Every 5 Minutes', 'wp-performance-plus')
        );
        
        $schedules['wp_performance_plus_fifteen_minutes'] = array(
            'interval' => 900,
            'display' => __('Every 15 Minutes', 'wp-performance-plus')
        );
        
        return $schedules;
    }

    private function define_admin_hooks() {
        // Initialize admin only once
        if (!isset($this->admin)) {
            $this->admin = new WP_Performance_Plus_Admin($this->get_plugin_name(), $this->get_version());
            
            // Pass enterprise systems to admin (some are disabled due to incomplete implementation)
            if (method_exists($this->admin, 'set_enterprise_systems')) {
                $this->admin->set_enterprise_systems(
                    $this->multi_cdn_orchestrator,
                    null, // content_delivery_optimizer is disabled due to incomplete implementation
                    null, // analytics_dashboard is disabled due to incomplete implementation
                    null  // enterprise_scaler is disabled due to incomplete implementation
                );
            }
        }

        // Register admin hooks
        $this->loader->add_action('admin_menu', $this->admin, 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');
        $this->loader->add_action('admin_init', $this->admin, 'register_settings');
    }

    /**
     * Define public-facing site hooks
     */
    private function define_public_hooks() {
        // CDN URL rewriting hooks (only on frontend)
        if (!is_admin() && $this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
            // Start output buffering early for CDN URL rewriting
            $this->loader->add_action('init', $this->cdn_manager, 'start_output_buffering', 1);
            
            // Setup URL rewriting hooks
            $this->loader->add_action('wp_loaded', $this->cdn_manager, 'setup_url_rewriting');
            
            WP_Performance_Plus_Logger::debug('CDN URL rewriting hooks registered');
        }

        // Performance monitoring hooks
        $this->loader->add_action('wp_footer', $this, 'add_performance_metrics', 999);
        
        // Cache hooks
        $this->loader->add_action('wp_head', $this, 'add_cache_headers', 1);
        
        // Real-time performance tracking
        // TODO: Re-enable when analytics dashboard is fully implemented
        // if ($this->analytics_dashboard) {
        //     $this->loader->add_action('wp_footer', $this->analytics_dashboard, 'add_realtime_tracking_script', 998);
        // }
    }

    private function init_debug() {
        $this->debug = new WP_Performance_Plus_Debug();
        
        // Add debug hooks with lower priority
        $this->loader->add_action('admin_init', $this->debug, 'init_debug_settings', 20);
        $this->loader->add_action('admin_menu', $this->debug, 'add_debug_menu', 20);
    }

    /**
     * Add performance metrics to footer
     */
    public function add_performance_metrics() {
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            $memory_usage = memory_get_peak_usage(true);
            $queries = get_num_queries();
            $load_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            
            echo "<!-- WP Performance Plus Debug\n";
            echo "Memory Usage: " . size_format($memory_usage) . "\n";
            echo "Database Queries: {$queries}\n";
            echo "Page Load Time: " . number_format($load_time, 4) . "s\n";
            
            if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
                echo "CDN: Enabled (" . get_class($this->cdn_manager->get_active_provider()) . ")\n";
            }
            
            if ($this->multi_cdn_orchestrator) {
                echo "Multi-CDN: Enabled\n";
            }
            
            if ($this->enterprise_scaler && is_multisite()) {
                echo "Enterprise Scaling: Enabled\n";
            }
            
            echo "-->";
        }
    }

    /**
     * Add cache headers
     */
    public function add_cache_headers() {
        if (!is_admin() && !is_user_logged_in()) {
            $settings = get_option('wp_performance_plus_settings', array());
            
            // Set cache headers for static content
            if (!headers_sent()) {
                header('Cache-Control: public, max-age=3600');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
                
                // Add CDN-specific headers if enabled
                if ($this->cdn_manager && $this->cdn_manager->is_cdn_enabled()) {
                    header('X-CDN-Provider: ' . get_class($this->cdn_manager->get_active_provider()));
                }
                
                // Add enterprise headers
                if ($this->multi_cdn_orchestrator) {
                    header('X-CDN-Orchestration: Enabled');
                }
                
                header('X-Performance-Plus: Optimized');
            }
        }
    }

    public function run() {
        $start_time = microtime(true);
        
        try {
            $this->loader->run();
            
            $execution_time = microtime(true) - $start_time;
            WP_Performance_Plus_Logger::info('Plugin execution completed', [
                'execution_time' => $execution_time,
                'peak_memory' => memory_get_peak_usage(true),
                'cdn_enabled' => $this->cdn_manager ? $this->cdn_manager->is_cdn_enabled() : false,
                'multi_cdn_enabled' => $this->multi_cdn_orchestrator !== null,
                'advanced_optimization_active' => $this->advanced_optimization !== null,
                'performance_monitoring_active' => $this->performance_monitor !== null,
                'analytics_dashboard_active' => $this->analytics_dashboard !== null,
                'enterprise_scaler_active' => $this->enterprise_scaler !== null,
                'is_multisite' => is_multisite()
            ]);
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Plugin execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // Getter methods for all systems
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_debug() {
        return $this->debug;
    }

    /**
     * Get CDN manager instance
     * @return WP_Performance_Plus_CDN_Manager|null
     */
    public function get_cdn_manager() {
        return $this->cdn_manager;
    }

    /**
     * Get advanced optimization instance
     * @return WP_Performance_Plus_Advanced_Optimization|null
     */
    public function get_advanced_optimization() {
        return $this->advanced_optimization;
    }

    /**
     * Get performance monitor instance
     * @return WP_Performance_Plus_Performance_Monitor|null
     */
    public function get_performance_monitor() {
        return $this->performance_monitor;
    }

    /**
     * Get Multi-CDN orchestrator instance
     * @return WP_Performance_Plus_Multi_CDN_Orchestrator|null
     */
    public function get_multi_cdn_orchestrator() {
        return $this->multi_cdn_orchestrator;
    }

    /**
     * Get content delivery optimizer instance
     * @return WP_Performance_Plus_Content_Delivery_Optimizer|null
     */
    public function get_content_delivery_optimizer() {
        return $this->content_delivery_optimizer;
    }

    /**
     * Get analytics dashboard instance
     * @return WP_Performance_Plus_Analytics_Dashboard|null
     */
    public function get_analytics_dashboard() {
        return $this->analytics_dashboard;
    }

    /**
     * Get enterprise scaler instance
     * @return WP_Performance_Plus_Enterprise_Scaler|null
     */
    public function get_enterprise_scaler() {
        return $this->enterprise_scaler;
    }

    /**
     * Get plugin instance (singleton pattern)
     * @return WP_Performance_Plus
     */
    public static function get_instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Get comprehensive system status
     * @return array System status
     */
    public function get_system_status() {
        return array(
            'plugin_version' => $this->version,
            'systems' => array(
                'cdn_manager' => $this->cdn_manager !== null,
                'multi_cdn_orchestrator' => $this->multi_cdn_orchestrator !== null,
                'advanced_optimization' => $this->advanced_optimization !== null,
                'performance_monitor' => $this->performance_monitor !== null,
                'content_delivery_optimizer' => $this->content_delivery_optimizer !== null,
                'analytics_dashboard' => $this->analytics_dashboard !== null,
                'enterprise_scaler' => $this->enterprise_scaler !== null
            ),
            'features' => array(
                'multisite_support' => is_multisite(),
                'cdn_enabled' => $this->cdn_manager ? $this->cdn_manager->is_cdn_enabled() : false,
                'auto_scaling' => $this->enterprise_scaler !== null,
                'real_time_analytics' => $this->analytics_dashboard !== null,
                'intelligent_optimization' => $this->content_delivery_optimizer !== null
            ),
            'performance' => array(
                'memory_usage' => memory_get_peak_usage(true),
                'memory_limit' => ini_get('memory_limit'),
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version')
            )
        );
    }

    /**
     * Cleanup on plugin deactivation
     */
    public function deactivate() {
        // Clear all scheduled events
        $scheduled_events = array(
            'wp_performance_plus_check_cdn_health',
            // TODO: Re-enable when content delivery optimizer is fully implemented
            // 'wp_performance_plus_optimize_content_delivery',
            // TODO: Re-enable when analytics dashboard is fully implemented
            // 'wp_performance_plus_generate_analytics',
            // TODO: Re-enable when enterprise scaler is fully implemented
            // 'wp_performance_plus_monitor_resources',
            // 'wp_performance_plus_check_scaling_conditions',
            // 'wp_performance_plus_optimize_network_performance',
            // TODO: Re-enable when content delivery optimizer is fully implemented
            // 'wp_performance_plus_warm_cache',
            'wp_performance_plus_check_alerts'
        );

        foreach ($scheduled_events as $event) {
            wp_clear_scheduled_hook($event);
        }

        WP_Performance_Plus_Logger::info('Plugin deactivated - scheduled events cleared');
    }
} 