<?php
/**
 * Class PerformancePlus_Loader
 * 
 * Core loader class that initializes all plugin components.
 * Handles file inclusion, class initialization, and dependency management.
 * Uses singleton pattern to ensure single instance throughout the application.
 */

class PerformancePlus_Loader {
    /** @var PerformancePlus_Loader|null Singleton instance */
    private static $instance = null;

    /** @var PerformancePlus_Admin|null Admin interface instance */
    private $admin = null;

    /**
     * Get singleton instance of the loader
     * 
     * @return PerformancePlus_Loader Single instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     * Initializes core functionality and loads required files
     */
    private function __construct() {
        $this->include_files();
        $this->initialize_classes();
    }

    /**
     * Include all required plugin files
     * Loads admin, CDN handlers, and core functionality files
     */
    private function include_files() {
        try {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PerformancePlus: Starting to include files');
            }

            // Core functionality
            require_once PERFORMANCEPLUS_PATH . 'includes/class-performanceplus-debug.php';
            require_once PERFORMANCEPLUS_PATH . 'includes/class-performanceplus-i18n.php';
            require_once PERFORMANCEPLUS_PATH . 'includes/class-performanceplus-compatibility.php';

            // Core admin classes
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-admin.php';
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-welcome.php';
            
            // CDN handlers - each handler manages specific CDN integration
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-cloudflare.php';
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-stackpath.php';
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-keycdn.php';
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-bunnycdn.php';
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-cloudfront.php';
            require_once PERFORMANCEPLUS_PATH . 'admin/class-performanceplus-local.php';

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PerformancePlus: All files included successfully');
            }
        } catch (Exception $e) {
            error_log('PerformancePlus Error: ' . $e->getMessage());
        }
    }

    /**
     * Initialize core plugin classes
     * Sets up admin interface and CDN handlers if in admin area
     */
    private function initialize_classes() {
        try {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PerformancePlus: Starting class initialization');
            }

            // Initialize debug first
            $debug = new PerformancePlus_Debug();
            $debug->log('Starting class initialization');

            // Initialize compatibility checker
            $compatibility = new PerformancePlus_Compatibility();
            $debug->log('Compatibility checker initialized');

            // Check on activation
            register_activation_hook(PERFORMANCEPLUS_FILE, [$compatibility, 'display_activation_notice']);
            
            // Add AJAX handler
            add_action('wp_ajax_performanceplus_deactivate_plugins', [$compatibility, 'handle_plugin_deactivation']);

            // Initialize admin interface and CDN handlers only in admin area
            if (is_admin() && !$this->admin) {
                $this->admin = new PerformancePlus_Admin();
                
                // Initialize all CDN handlers
                $handlers = [
                    'cloudflare' => new PerformancePlus_Cloudflare(),
                    'stackpath' => new PerformancePlus_StackPath(),
                    'keycdn' => new PerformancePlus_KeyCDN(),
                    'bunnycdn' => new PerformancePlus_BunnyCDN(),
                    'cloudfront' => new PerformancePlus_CloudFront(),
                    'local' => new PerformancePlus_Local()
                ];
                
                // Pass CDN handlers to admin interface
                $this->admin->set_cdn_handlers($handlers);

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('PerformancePlus: Admin and CDN handlers initialized');
                }
            }

            // Initialize internationalization
            $i18n = new PerformancePlus_I18n();
            $i18n->load_textdomain();

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PerformancePlus: All classes initialized successfully');
            }
        } catch (Exception $e) {
            error_log('PerformancePlus Error: ' . $e->getMessage());
        }
    }
}
