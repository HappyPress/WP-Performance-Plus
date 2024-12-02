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
        
        $this->define_admin_hooks();
        $this->init_debug();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-loader.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-logger.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-cache.php';
        require_once plugin_dir_path(__FILE__) . 'abstract-class-wp-performance-plus-cdn.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-performance-plus-debug.php';

        // Get the loader instance
        $this->loader = WP_Performance_Plus_Loader::get_instance();
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

    private function init_debug() {
        $this->debug = new WP_Performance_Plus_Debug();
        
        // Add debug hooks with lower priority
        $this->loader->add_action('admin_init', $this->debug, 'init_debug_settings', 20);
        $this->loader->add_action('admin_menu', $this->debug, 'add_debug_menu', 20);
    }

    public function run() {
        $start_time = microtime(true);
        
        try {
            $this->loader->run();
            
            $execution_time = microtime(true) - $start_time;
            WP_Performance_Plus_Logger::info('Plugin execution completed', [
                'execution_time' => $execution_time,
                'peak_memory' => memory_get_peak_usage(true)
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
} 