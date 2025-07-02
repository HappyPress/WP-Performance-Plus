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
            'wp_version' => get_bloginfo('version')
        ]);
        
        $this->init_cdn_manager();
        $this->init_advanced_optimization();
        $this->init_performance_monitor();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->init_debug();
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

    private function define_admin_hooks() {
        // Initialize admin only once
        if (!isset($this->admin)) {
            $this->admin = new WP_Performance_Plus_Admin($this->get_plugin_name(), $this->get_version());
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
                'advanced_optimization_active' => $this->advanced_optimization !== null,
                'performance_monitoring_active' => $this->performance_monitor !== null
            ]);
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error('Plugin execution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

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
} 