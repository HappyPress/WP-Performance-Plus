<?php
/**
 * The admin-specific functionality of the plugin.
 */
class WP_Performance_Plus_Admin {
    private $plugin_name;
    private $version;
    private $settings;
    private $active_tab;
    private $cdn_handlers;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = get_option('wp_performance_plus_settings', array());
        $this->active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'basics';
        $this->cdn_handlers = array();

        // Initialize CDN handlers
        $this->init_cdn_handlers();

        // Add WordPress hooks
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Add AJAX handlers
        add_action('wp_ajax_wp_performance_plus_save_onboarding', array($this, 'handle_save_onboarding'));
        add_action('wp_ajax_wp_performance_plus_save_step', array($this, 'handle_save_step'));
        add_action('wp_ajax_wp_performance_plus_clear_cache', array($this, 'handle_clear_cache'));
        add_action('wp_ajax_wp_performance_plus_optimize_images', array($this, 'handle_optimize_images'));
        add_action('wp_ajax_wp_performance_plus_optimize_database', array($this, 'handle_optimize_database'));
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-performance-plus') === false) {
            return;
        }

        // Font Awesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css',
            array(),
            '5.15.3'
        );

        // Inter Font
        wp_enqueue_style(
            'inter-font',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
            array(),
            null
        );

        // Base admin styles
        wp_enqueue_style(
            'wp-performance-plus-admin',
            plugins_url('css/wp-performance-plus-admin.css', __FILE__),
            array('font-awesome', 'inter-font'),
            $this->version
        );

        // Onboarding/welcome page styles
        wp_enqueue_style(
            'wp-performance-plus-onboarding',
            plugins_url('css/wp-performance-plus-onboarding.css', __FILE__),
            array('wp-performance-plus-admin'),
            $this->version
        );

        // Add inline style for debugging
        wp_add_inline_style('wp-performance-plus-onboarding', '
            /* Debug styles */
            // .wp-performance-plus-onboarding {
            //     border: 5px solid blue !important;
            // }
        ');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-performance-plus') === false) {
            return;
        }

        wp_enqueue_script(
            'wp-performance-plus-admin',
            plugins_url('js/wp-performance-plus-admin.js', __FILE__),
            array('jquery'),
            $this->version,
            true
        );

        wp_enqueue_script(
            'wp-performance-plus-wizard',
            plugins_url('js/wp-performance-plus-wizard.js', __FILE__),
            array('jquery', 'wp-performance-plus-admin'),
            $this->version,
            true
        );

        wp_localize_script('wp-performance-plus-wizard', 'wpWPPerformancePlusWizard', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_performance_plus_wizard'),
            'strings' => array(
                'error' => __('An error occurred while saving settings. Please try again.', 'wp-performance-plus')
            )
        ));
    }

    /**
     * Check if current page is a plugin page
     */
    private function is_plugin_page($screen) {
        $plugin_pages = array(
            'toplevel_page_wp-performance-plus',
            'wp-performance-plus_page_wp-performance-plus-settings',
            'wp-performance-plus_page_wp-performance-plus-debug'
        );
        return in_array($screen->id, $plugin_pages) || strpos($screen->id, 'wp-performance-plus') !== false;
    }

    /**
     * Initialize CDN handler classes
     */
    private function init_cdn_handlers() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-cloudflare.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-keycdn.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-bunnycdn.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-performance-plus-cloudfront.php';
        
        $this->cdn_handlers['cloudflare'] = new WP_Performance_Plus_Cloudflare();
        $this->cdn_handlers['keycdn'] = new WP_Performance_Plus_KeyCDN();
        $this->cdn_handlers['bunnycdn'] = new WP_Performance_Plus_BunnyCDN();
        $this->cdn_handlers['cloudfront'] = new WP_Performance_Plus_CloudFront();
    }

    /**
     * Register the admin menu
     */
    public function add_admin_menu() {
        static $menu_added = false;

        // Prevent multiple menu additions
        if ($menu_added) {
            return;
        }

        // Add main menu item
        add_menu_page(
            __('WP Performance Plus', 'wp-performance-plus'),
            __('WP Performance Plus', 'wp-performance-plus'),
            'manage_options',
            'wp-performance-plus',
            array($this, 'render_welcome_page'),
            'dashicons-performance',
            100
        );

        // Add Settings submenu
        add_submenu_page(
            'wp-performance-plus',
            __('Settings', 'wp-performance-plus'),
            __('Settings', 'wp-performance-plus'),
            'manage_options',
            'wp-performance-plus-settings',
            array($this, 'render_settings_page')
        );

        $menu_added = true;
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'wp_performance_plus_settings',
            'wp_performance_plus_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );

        // Add settings sections and fields here
    }

    /**
     * Render the main plugin page
     */
    public function render_main_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-performance-plus-main.php';
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Check if file exists before requiring
        $settings_file = plugin_dir_path(__FILE__) . 'partials/wp-performance-plus-settings.php';
        if (file_exists($settings_file)) {
            require_once $settings_file;
        } else {
            // Fallback to main page if settings file doesn't exist
            require_once plugin_dir_path(__FILE__) . 'partials/wp-performance-plus-main.php';
        }
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        if (isset($input['enable_minification'])) {
            $sanitized['enable_minification'] = (bool) $input['enable_minification'];
        }

        if (isset($input['combine_files'])) {
            $sanitized['combine_files'] = (bool) $input['combine_files'];
        }

        if (isset($input['lazy_loading'])) {
            $sanitized['lazy_loading'] = (bool) $input['lazy_loading'];
        }

        return $sanitized;
    }

    /**
     * Handle saving onboarding settings
     */
    public function handle_save_onboarding() {
        check_ajax_referer('wp-performance-plus-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        // Sanitize and save settings
        $sanitized = array(
            'cache_enabled' => isset($settings['cache_enabled']) ? (bool) $settings['cache_enabled'] : false,
            'cdn_provider' => isset($settings['cdn_provider']) ? sanitize_text_field($settings['cdn_provider']) : '',
            'minify_code' => isset($settings['minify_code']) ? (bool) $settings['minify_code'] : false,
            'lazy_loading' => isset($settings['lazy_loading']) ? (bool) $settings['lazy_loading'] : false,
            'image_optimize' => isset($settings['image_optimize']) ? (bool) $settings['image_optimize'] : false
        );

        $updated_settings = array_merge($this->settings, $sanitized);
        
        if (update_option('wp_performance_plus_settings', $updated_settings)) {
            update_option('wp_performance_plus_onboarding_completed', true);
            wp_send_json_success(array(
                'message' => __('Settings saved successfully.', 'wp-performance-plus'),
                'settings' => $updated_settings
            ));
        } else {
            wp_send_json_error(__('Failed to save settings.', 'wp-performance-plus'));
        }
    }

    /**
     * Handle saving wizard step settings via AJAX
     */
    public function handle_save_step() {
        check_ajax_referer('wp-performance-plus-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        $step = isset($_POST['step']) ? sanitize_key($_POST['step']) : '';
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();

        if (empty($step)) {
            wp_send_json_error('Missing step parameter');
            return;
        }

        // Sanitize settings based on step
        $sanitized_settings = array();
        switch ($step) {
            case 'basics':
                $sanitized_settings['enable_minification'] = isset($settings['enable_minification']) ? (bool) $settings['enable_minification'] : false;
                $sanitized_settings['combine_files'] = isset($settings['combine_files']) ? (bool) $settings['combine_files'] : false;
                $sanitized_settings['lazy_loading'] = isset($settings['lazy_loading']) ? (bool) $settings['lazy_loading'] : false;
                break;

            case 'cdn':
                $sanitized_settings['cdn_enabled'] = isset($settings['cdn_enabled']) ? (bool) $settings['cdn_enabled'] : false;
                $sanitized_settings['cdn_provider'] = isset($settings['cdn_provider']) ? sanitize_text_field($settings['cdn_provider']) : '';
                $sanitized_settings['cdn_url'] = isset($settings['cdn_url']) ? esc_url_raw($settings['cdn_url']) : '';
                $sanitized_settings['cdn_key'] = isset($settings['cdn_key']) ? sanitize_text_field($settings['cdn_key']) : '';
                break;

            case 'database':
                $sanitized_settings['optimize_tables'] = isset($settings['optimize_tables']) ? (bool) $settings['optimize_tables'] : false;
                $sanitized_settings['cleanup_schedule'] = isset($settings['cleanup_schedule']) ? sanitize_key($settings['cleanup_schedule']) : 'weekly';
                break;

            default:
                wp_send_json_error('Invalid step');
                return;
        }

        // Get existing settings and merge with new ones
        $existing_settings = get_option('wp_performance_plus_settings', array());
        $updated_settings = array_merge($existing_settings, $sanitized_settings);

        // Update settings
        if (update_option('wp_performance_plus_settings', $updated_settings)) {
            wp_send_json_success(array(
                'message' => __('Settings saved successfully.', 'wp-performance-plus'),
                'settings' => $updated_settings
            ));
        } else {
            wp_send_json_error(__('Failed to save settings.', 'wp-performance-plus'));
        }
    }

    /**
     * Handle clear cache action
     */
    public function handle_clear_cache() {
        check_ajax_referer('wp-performance-plus-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        try {
            // Clear WordPress object cache
            wp_cache_flush();

            // Clear page cache if enabled
            if (!empty($this->settings['cache_enabled'])) {
                $cache = new WPPerformancePlus_Cache();
                $cache->clear_all();
            }

            wp_send_json_success();
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Handle optimize images action
     */
    public function handle_optimize_images() {
        check_ajax_referer('wp-performance-plus-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        try {
            $optimizer = new WPPerformancePlus_Optimizer();
            $result = $optimizer->optimize_images();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Handle optimize database action
     */
    public function handle_optimize_database() {
        check_ajax_referer('wp-performance-plus-admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        try {
            $optimizer = new WPPerformancePlus_Optimizer();
            $result = $optimizer->optimize_database();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Render the welcome/onboarding page
     */
    public function render_welcome_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wp-performance-plus-welcome.php';
    }
} 