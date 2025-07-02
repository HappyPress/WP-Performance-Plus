<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/HappyPress/WP-Performance-Plus
 * @since      1.0.0
 *
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin-specific functionality.
 *
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/admin
 * @author     HappyPress <info@happypress.com>
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
        $this->settings = get_option('wp_performance_plus_settings', $this->get_default_settings());
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

        // Settings saved hooks
        add_action('wp_performance_plus_settings_saved', array($this, 'handle_settings_saved'));
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
            'wp_performance_plus_settings_group',
            'wp_performance_plus_settings',
            array($this, 'sanitize_settings')
        );

        // Register settings sections
        $this->register_general_settings();
        $this->register_cdn_settings();
        $this->register_optimization_settings();
        $this->register_database_settings();
    }

    /**
     * Register general settings section.
     */
    private function register_general_settings() {
        add_settings_section(
            'wp_performance_plus_general',
            __('General Settings', 'wp-performance-plus'),
            array($this, 'general_section_callback'),
            'wp-performance-plus'
        );

        add_settings_field(
            'enable_optimization',
            __('Enable Optimization', 'wp-performance-plus'),
            array($this, 'render_checkbox_field'),
            'wp-performance-plus',
            'wp_performance_plus_general',
            array(
                'name' => 'enable_optimization',
                'label' => __('Enable all optimization features', 'wp-performance-plus'),
                'description' => __('Master switch to enable/disable all optimization features.', 'wp-performance-plus')
            )
        );

        add_settings_field(
            'optimization_level',
            __('Optimization Level', 'wp-performance-plus'),
            array($this, 'render_select_field'),
            'wp-performance-plus',
            'wp_performance_plus_general',
            array(
                'name' => 'optimization_level',
                'options' => array(
                    'safe' => __('Safe - Minimal optimizations', 'wp-performance-plus'),
                    'balanced' => __('Balanced - Recommended settings', 'wp-performance-plus'),
                    'aggressive' => __('Aggressive - Maximum optimization', 'wp-performance-plus')
                ),
                'description' => __('Choose the optimization level that best fits your needs.', 'wp-performance-plus')
            )
        );
    }

    /**
     * Register CDN settings section.
     */
    private function register_cdn_settings() {
        add_settings_section(
            'wp_performance_plus_cdn',
            __('CDN Settings', 'wp-performance-plus'),
            array($this, 'cdn_section_callback'),
            'wp-performance-plus-cdn'
        );

        // CDN Provider Selection
        add_settings_field(
            'cdn_provider',
            __('CDN Provider', 'wp-performance-plus'),
            array($this, 'render_select_field'),
            'wp-performance-plus-cdn',
            'wp_performance_plus_cdn',
            array(
                'name' => 'cdn_provider',
                'options' => array(
                    'none' => __('None', 'wp-performance-plus'),
                    'cloudflare' => __('Cloudflare', 'wp-performance-plus'),
                    'stackpath' => __('StackPath', 'wp-performance-plus'),
                    'keycdn' => __('KeyCDN', 'wp-performance-plus'),
                    'bunnycdn' => __('BunnyCDN', 'wp-performance-plus'),
                    'cloudfront' => __('Amazon CloudFront', 'wp-performance-plus'),
                    'custom' => __('Custom CDN', 'wp-performance-plus')
                ),
                'description' => __('Select your CDN provider for optimized content delivery.', 'wp-performance-plus')
            )
        );
    }

    /**
     * Register optimization settings section.
     */
    private function register_optimization_settings() {
        add_settings_section(
            'wp_performance_plus_optimization',
            __('Optimization Settings', 'wp-performance-plus'),
            array($this, 'optimization_section_callback'),
            'wp-performance-plus-optimization'
        );

        // Asset Optimization
        add_settings_field(
            'minify_html',
            __('Minify HTML', 'wp-performance-plus'),
            array($this, 'render_checkbox_field'),
            'wp-performance-plus-optimization',
            'wp_performance_plus_optimization',
            array(
                'name' => 'minify_html',
                'label' => __('Enable HTML minification', 'wp-performance-plus'),
                'description' => __('Removes unnecessary whitespace and comments from HTML output.', 'wp-performance-plus')
            )
        );

        add_settings_field(
            'minify_css',
            __('Minify CSS', 'wp-performance-plus'),
            array($this, 'render_checkbox_field'),
            'wp-performance-plus-optimization',
            'wp_performance_plus_optimization',
            array(
                'name' => 'minify_css',
                'label' => __('Enable CSS minification', 'wp-performance-plus'),
                'description' => __('Removes unnecessary whitespace and comments from CSS files.', 'wp-performance-plus')
            )
        );

        add_settings_field(
            'minify_js',
            __('Minify JavaScript', 'wp-performance-plus'),
            array($this, 'render_checkbox_field'),
            'wp-performance-plus-optimization',
            'wp_performance_plus_optimization',
            array(
                'name' => 'minify_js',
                'label' => __('Enable JavaScript minification', 'wp-performance-plus'),
                'description' => __('Removes unnecessary whitespace and comments from JavaScript files.', 'wp-performance-plus')
            )
        );
    }

    /**
     * Register database settings section.
     */
    private function register_database_settings() {
        add_settings_section(
            'wp_performance_plus_database',
            __('Database Settings', 'wp-performance-plus'),
            array($this, 'database_section_callback'),
            'wp-performance-plus-database'
        );

        add_settings_field(
            'auto_cleanup',
            __('Auto Cleanup', 'wp-performance-plus'),
            array($this, 'render_checkbox_field'),
            'wp-performance-plus-database',
            'wp_performance_plus_database',
            array(
                'name' => 'auto_cleanup',
                'label' => __('Enable automatic database cleanup', 'wp-performance-plus'),
                'description' => __('Automatically clean database regularly.', 'wp-performance-plus')
            )
        );
    }

    /**
     * Get default settings.
     */
    public function get_default_settings() {
        return array(
            'enable_optimization' => true,
            'optimization_level' => 'balanced',
            'cdn_provider' => 'none',
            'minify_html' => false,
            'minify_css' => false,
            'minify_js' => false,
            'auto_cleanup' => false,
            // CDN specific settings will be added dynamically
            'cloudflare' => array(
                'email' => '',
                'api_key' => '',
                'zone_id' => '',
                'enabled' => false
            ),
            'stackpath' => array(
                'alias' => '',
                'api_key' => '',
                'enabled' => false
            ),
            'keycdn' => array(
                'api_key' => '',
                'zone_id' => '',
                'enabled' => false
            ),
            'bunnycdn' => array(
                'api_key' => '',
                'storage_zone' => '',
                'enabled' => false
            ),
            'cloudfront' => array(
                'access_key' => '',
                'secret_key' => '',
                'distribution_id' => '',
                'enabled' => false
            )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Boolean fields
        $boolean_fields = array(
            'enable_optimization', 'minify_html', 'minify_css', 
            'minify_js', 'auto_cleanup'
        );
        
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($input[$field]) && $input[$field] ? true : false;
        }
        
        // Text fields
        $text_fields = array(
            'optimization_level', 'cdn_provider'
        );
        
        foreach ($text_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? sanitize_text_field($input[$field]) : '';
        }
        
        // CDN settings sanitization
        if (isset($input['cloudflare']) && is_array($input['cloudflare'])) {
            $sanitized['cloudflare'] = array(
                'email' => sanitize_email($input['cloudflare']['email']),
                'api_key' => sanitize_text_field($input['cloudflare']['api_key']),
                'zone_id' => sanitize_text_field($input['cloudflare']['zone_id']),
                'enabled' => isset($input['cloudflare']['enabled']) && $input['cloudflare']['enabled']
            );
        }
        
        // Trigger action after settings are saved
        do_action('wp_performance_plus_settings_saved', $sanitized, $input);
        
        return $sanitized;
    }

    /**
     * Handle settings saved action.
     */
    public function handle_settings_saved($sanitized) {
        // Clear cache when settings change
        if (class_exists('WP_Performance_Plus_Cache')) {
            WP_Performance_Plus_Cache::clear_all_cache();
        }
        
        // Add admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Settings saved successfully!', 'wp-performance-plus') . '</p>';
            echo '</div>';
        });
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

    // Section callbacks
    public function general_section_callback() {
        echo '<p>' . __('Configure general optimization settings.', 'wp-performance-plus') . '</p>';
    }

    public function cdn_section_callback() {
        echo '<p>' . __('Configure your CDN provider settings for optimal content delivery.', 'wp-performance-plus') . '</p>';
    }

    public function optimization_section_callback() {
        echo '<p>' . __('Configure local optimization settings for your website.', 'wp-performance-plus') . '</p>';
    }

    public function database_section_callback() {
        echo '<p>' . __('Configure database optimization and cleanup settings.', 'wp-performance-plus') . '</p>';
    }

    // Field rendering methods
    public function render_checkbox_field($args) {
        $name = $args['name'];
        $value = isset($this->settings[$name]) ? $this->settings[$name] : false;
        $label = isset($args['label']) ? $args['label'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        
        echo '<label>';
        echo '<input type="checkbox" name="wp_performance_plus_settings[' . esc_attr($name) . ']" value="1"' . checked(1, $value, false) . ' />';
        echo ' ' . esc_html($label);
        echo '</label>';
        
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }

    public function render_select_field($args) {
        $name = $args['name'];
        $value = isset($this->settings[$name]) ? $this->settings[$name] : '';
        $options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        
        echo '<select name="wp_performance_plus_settings[' . esc_attr($name) . ']">';
        foreach ($options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>';
            echo esc_html($option_label);
            echo '</option>';
        }
        echo '</select>';
        
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }
} 