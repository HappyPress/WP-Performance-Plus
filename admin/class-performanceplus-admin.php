<?php
/**
 * Main admin class for PerformancePlus
 */
class PerformancePlus_Admin {
    private $welcome;
    private $cdn_handlers = [];
    private $capability = 'manage_options';
    private $menu_slug = 'performanceplus';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        
        // Initialize welcome screen after admin_menu
        add_action('admin_init', [$this, 'init_welcome']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function init_welcome() {
        $this->welcome = new PerformancePlus_Welcome();
    }

    public function set_cdn_handlers($handlers) {
        $this->cdn_handlers = $handlers;
    }

    public function register_admin_menu() {
        if (!current_user_can($this->capability)) {
            return;
        }

        // Add main menu
        add_menu_page(
            __('WP Performance Plus', 'performanceplus'),
            __('WP Performance Plus', 'performanceplus'),
            $this->capability,
            $this->menu_slug,
            [$this, 'render_dashboard'],
            'dashicons-performance',
            25
        );

        // Define submenu pages
        $submenus = [
            ['Dashboard', 'render_dashboard'],
            ['CDN Management', 'render_cdn_management'],
            ['Basics', 'render_basics'],
            ['User Guide', 'render_user_guide'],
            ['Support', 'render_support']
        ];

        // Add submenus
        foreach ($submenus as $submenu) {
            add_submenu_page(
                $this->menu_slug,
                __($submenu[0], 'performanceplus'),
                __($submenu[0], 'performanceplus'),
                $this->capability,
                $this->menu_slug . '-' . sanitize_title($submenu[0]),
                [$this, $submenu[1]]
            );
        }

        // Remove duplicate first item
        remove_submenu_page($this->menu_slug, $this->menu_slug);
    }

    public function enqueue_admin_styles($hook) {
        if (strpos($hook, 'performanceplus') !== false) {
            wp_enqueue_style(
                'performanceplus-admin',
                PERFORMANCEPLUS_URL . 'admin/css/performanceplus-admin.css',
                [],
                PERFORMANCEPLUS_VERSION
            );

            wp_enqueue_script(
                'performanceplus-admin',
                PERFORMANCEPLUS_URL . 'admin/js/performanceplus-admin.js',
                ['jquery'],
                PERFORMANCEPLUS_VERSION,
                true
            );
        }
    }

    public function render_dashboard() {
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'performanceplus'));
        }

        $has_completed_onboarding = get_option('performanceplus_onboarding_complete', false);
        ?>
        <div class="wrap performanceplus-dashboard">
            <h1><?php esc_html_e('WP Performance Plus Dashboard', 'performanceplus'); ?></h1>
            
            <?php if (!$has_completed_onboarding): ?>
                <?php $this->welcome->render_welcome_wizard(); ?>
            <?php else: ?>
                <div class="onboarding-reset">
                    <button type="button" class="button" id="redo-onboarding">
                        <?php esc_html_e('Redo Onboarding', 'performanceplus'); ?>
                    </button>
                </div>
                <div class="performance-metrics">
                    <div class="metric-grid">
                        <?php $this->render_performance_metrics(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_cdn_management() {
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'basics';
        $cdn_providers = [
            'basics' => [
                'name' => 'Basics',
                'description' => __('Basic optimization features for your WordPress site without using a CDN.', 'performanceplus')
            ],
            'cloudflare' => [
                'name' => 'Cloudflare',
                'description' => __('Cloudflare offers a global CDN, DDoS protection, and security features.', 'performanceplus')
            ],
            'stackpath' => [
                'name' => 'StackPath',
                'description' => __('StackPath provides edge computing, CDN, and security services.', 'performanceplus')
            ],
            'keycdn' => [
                'name' => 'KeyCDN',
                'description' => __('KeyCDN is a high-performance content delivery network.', 'performanceplus')
            ],
            'bunnycdn' => [
                'name' => 'BunnyCDN',
                'description' => __('BunnyCDN offers affordable and fast content delivery.', 'performanceplus')
            ],
            'cloudfront' => [
                'name' => 'CloudFront',
                'description' => __('Amazon CloudFront is a fast and secure CDN service.', 'performanceplus')
            ]
        ];
        ?>
        <div class="wrap performanceplus-cdn-management">
            <h1><?php esc_html_e('Performance Optimization', 'performanceplus'); ?></h1>
            
            <div class="cdn-description">
                <p class="description">
                    <?php esc_html_e('Choose between basic optimizations or configure a CDN provider to improve your website\'s performance.', 'performanceplus'); ?>
                </p>
            </div>

            <nav class="nav-tab-wrapper">
                <?php foreach ($cdn_providers as $slug => $provider): ?>
                    <a href="?page=<?php echo $this->menu_slug; ?>-cdn-management&tab=<?php echo esc_attr($slug); ?>" 
                       class="nav-tab <?php echo $current_tab === $slug ? 'nav-tab-active' : ''; ?>"
                       title="<?php echo esc_attr($provider['description']); ?>">
                        <?php echo esc_html($provider['name']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="cdn-settings-content">
                <?php if ($current_tab === 'basics'): ?>
                    <?php $this->render_basics_tab(); ?>
                <?php else: ?>
                    <?php if (isset($cdn_providers[$current_tab])): ?>
                        <div class="cdn-provider-description">
                            <p><?php echo esc_html($cdn_providers[$current_tab]['description']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php
                    if (isset($this->cdn_handlers[$current_tab]) && method_exists($this->cdn_handlers[$current_tab], 'render_settings')) {
                        $this->cdn_handlers[$current_tab]->render_settings();
                    } else {
                        echo '<div class="notice notice-error"><p>' . 
                             esc_html__('Selected provider settings are not available.', 'performanceplus') . 
                             '</p></div>';
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render basics tab content
     */
    private function render_basics_tab() {
        global $wpdb;
        $tables_info = $this->get_database_tables_info();
        ?>
        <div class="basics-tab-content">
            <!-- Asset Optimization Section -->
            <div class="card">
                <h2><?php esc_html_e('Asset Optimization', 'performanceplus'); ?></h2>
                <?php $this->render_asset_optimization_settings(); ?>
            </div>

            <!-- Database Management Section -->
            <div class="card database-management">
                <h2>
                    <?php esc_html_e('Database Management', 'performanceplus'); ?>
                    <span class="dashicons dashicons-info-outline" 
                          title="<?php esc_attr_e('Review and optimize your database tables', 'performanceplus'); ?>">
                    </span>
                </h2>

                <div class="database-stats">
                    <div class="stat-card">
                        <span class="stat-value"><?php echo esc_html($this->format_size($tables_info['total_size'])); ?></span>
                        <span class="stat-label"><?php esc_html_e('Total Database Size', 'performanceplus'); ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?php echo esc_html($this->format_size($tables_info['potential_savings'])); ?></span>
                        <span class="stat-label"><?php esc_html_e('Potential Space Savings', 'performanceplus'); ?></span>
                    </div>
                </div>

                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk-action">
                            <option value="-1"><?php esc_html_e('Bulk Actions', 'performanceplus'); ?></option>
                            <option value="optimize"><?php esc_html_e('Optimize', 'performanceplus'); ?></option>
                            <option value="repair"><?php esc_html_e('Repair', 'performanceplus'); ?></option>
                        </select>
                        <button class="button action" id="doaction"><?php esc_html_e('Apply', 'performanceplus'); ?></button>
                    </div>
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(
                                esc_html__('%s items', 'performanceplus'), 
                                number_format_i18n(count($tables_info['tables']))
                            ); ?>
                        </span>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped database-tables">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all-1">
                            </td>
                            <th scope="col" class="manage-column column-name">
                                <?php esc_html_e('Table Name', 'performanceplus'); ?>
                            </th>
                            <th scope="col" class="manage-column column-rows">
                                <?php esc_html_e('Rows', 'performanceplus'); ?>
                            </th>
                            <th scope="col" class="manage-column column-size">
                                <?php esc_html_e('Size', 'performanceplus'); ?>
                            </th>
                            <th scope="col" class="manage-column column-overhead">
                                <?php esc_html_e('Overhead', 'performanceplus'); ?>
                            </th>
                            <th scope="col" class="manage-column column-actions">
                                <?php esc_html_e('Actions', 'performanceplus'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables_info['tables'] as $table): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="tables[]" value="<?php echo esc_attr($table['name']); ?>">
                                </th>
                                <td class="column-name">
                                    <strong><?php echo esc_html($table['name']); ?></strong>
                                    <?php if (!empty($table['recommendation'])): ?>
                                        <div class="row-actions">
                                            <span class="recommendation">
                                                <?php echo esc_html($table['recommendation']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="column-rows"><?php echo number_format_i18n($table['rows']); ?></td>
                                <td class="column-size"><?php echo esc_html($this->format_size($table['size'])); ?></td>
                                <td class="column-overhead">
                                    <?php if ($table['overhead'] > 0): ?>
                                        <span class="overhead-warning">
                                            <?php echo esc_html($this->format_size($table['overhead'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="overhead-ok">
                                            <?php esc_html_e('Optimized', 'performanceplus'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="column-actions">
                                    <button type="button" 
                                            class="button button-small optimize-table" 
                                            data-table="<?php echo esc_attr($table['name']); ?>">
                                        <?php esc_html_e('Optimize', 'performanceplus'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cache Management Section -->
            <div class="card">
                <h2><?php esc_html_e('Cache Management', 'performanceplus'); ?></h2>
                <?php $this->render_cache_management_settings(); ?>
            </div>
        </div>
        <?php
    }

    public function render_user_guide() {
        echo '<h1>User Guide</h1><p>Documentation will go here.</p>';
    }

    public function render_support() {
        echo '<h1>Support</h1><p>Support information will go here.</p>';
    }

    private function render_performance_metrics() {
        $metrics = [
            [
                'title' => __('Page Load Time', 'performanceplus'),
                'value' => $this->get_average_page_load_time(),
                'unit' => 'seconds',
                'icon' => 'dashicons-clock'
            ],
            [
                'title' => __('Cache Hit Rate', 'performanceplus'),
                'value' => $this->get_cache_hit_rate(),
                'unit' => '%',
                'icon' => 'dashicons-database'
            ],
            // Add more metrics...
        ];

        foreach ($metrics as $metric) {
            $this->render_metric_card($metric);
        }
    }

    private function render_metric_card($metric) {
        ?>
        <div class="metric-card">
            <span class="dashicons <?php echo esc_attr($metric['icon']); ?>"></span>
            <h3><?php echo esc_html($metric['title']); ?></h3>
            <div class="metric-value">
                <?php echo esc_html($metric['value']); ?>
                <span class="metric-unit"><?php echo esc_html($metric['unit']); ?></span>
            </div>
        </div>
        <?php
    }

    // Metric collection methods
    private function get_average_page_load_time() {
        return '1.2';
    }

    private function get_cache_hit_rate() {
        return '85';
    }

    /**
     * Add debug settings to General Settings
     */
    public function register_settings() {
        add_settings_section(
            'performanceplus_debug_section',
            __('Debug Settings', 'performanceplus'),
            [$this, 'render_debug_section'],
            'general'
        );

        register_setting('general', 'performanceplus_debug_mode');
        add_settings_field(
            'performanceplus_debug_mode',
            __('PerformancePlus Debug Mode', 'performanceplus'),
            [$this, 'render_debug_field'],
            'general',
            'performanceplus_debug_section'
        );
    }

    /**
     * Render debug settings section
     */
    public function render_debug_section() {
        ?>
        <p>
            <?php esc_html_e('Configure debugging options for PerformancePlus plugin.', 'performanceplus'); ?>
        </p>
        <?php
    }

    /**
     * Render debug toggle field
     */
    public function render_debug_field() {
        $debug_mode = get_option('performanceplus_debug_mode', false);
        ?>
        <label class="toggle-switch">
            <input type="checkbox" 
                   name="performanceplus_debug_mode" 
                   value="1" 
                   <?php checked($debug_mode); ?>
            >
            <span class="toggle-slider"></span>
        </label>
        <p class="description">
            <?php esc_html_e('Enable detailed debug logging for PerformancePlus.', 'performanceplus'); ?>
        </p>
        <?php
    }

    /**
     * Get database tables information
     */
    private function get_database_tables_info() {
        global $wpdb;
        
        $tables = [];
        $total_size = 0;
        $potential_savings = 0;
        
        $results = $wpdb->get_results("SHOW TABLE STATUS", ARRAY_A);
        
        foreach ($results as $table) {
            $size = ($table['Data_length'] + $table['Index_length']);
            $overhead = $table['Data_free'];
            
            $recommendation = $this->get_table_recommendation($table);
            
            $tables[] = [
                'name' => $table['Name'],
                'rows' => $table['Rows'],
                'size' => $size,
                'overhead' => $overhead,
                'recommendation' => $recommendation
            ];
            
            $total_size += $size;
            $potential_savings += $overhead;
        }
        
        return [
            'tables' => $tables,
            'total_size' => $total_size,
            'potential_savings' => $potential_savings
        ];
    }

    /**
     * Get recommendation for table optimization
     */
    private function get_table_recommendation($table) {
        global $wpdb;
        
        $prefix = $wpdb->prefix;
        $recommendations = [
            $prefix . 'posts' => [
                'condition' => function($table) {
                    return $table['Rows'] > 1000;
                },
                'message' => __('Consider cleaning up post revisions and trash to reduce table size.', 'performanceplus')
            ],
            $prefix . 'options' => [
                'condition' => function($table) {
                    return $table['Data_length'] > 5 * 1024 * 1024;
                },
                'message' => __('Large options table. Consider cleaning up transients and unused options.', 'performanceplus')
            ],
            // Add more table-specific recommendations
        ];
        
        if (isset($recommendations[$table['Name']])) {
            $rec = $recommendations[$table['Name']];
            if ($rec['condition']($table)) {
                return $rec['message'];
            }
        }
        
        return '';
    }

    /**
     * Format size in human readable format
     */
    private function format_size($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Render basics page
     */
    public function render_basics() {
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'performanceplus'));
        }
        
        // Redirect to CDN Management page with basics tab
        wp_redirect(admin_url('admin.php?page=performanceplus-cdn-management&tab=basics'));
        exit;
    }

    /**
     * Render asset optimization settings
     */
    private function render_asset_optimization_settings() {
        $minification_enabled = get_option('performanceplus_enable_minification', false);
        $combine_files = get_option('performanceplus_combine_files', false);
        $lazy_loading = get_option('performanceplus_lazy_loading', false);
        ?>
        <div class="optimization-section">
            <div class="optimization-option">
                <label class="toggle-switch">
                    <input type="checkbox" 
                           name="enable_minification" 
                           <?php checked($minification_enabled); ?>>
                    <span class="toggle-slider"></span>
                    <?php esc_html_e('Enable Minification', 'performanceplus'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Automatically minify CSS, JavaScript, and HTML files.', 'performanceplus'); ?>
                </p>
            </div>

            <div class="optimization-option">
                <label class="toggle-switch">
                    <input type="checkbox" 
                           name="combine_files" 
                           <?php checked($combine_files); ?>>
                    <span class="toggle-slider"></span>
                    <?php esc_html_e('Combine Files', 'performanceplus'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Combine multiple CSS and JavaScript files into single files.', 'performanceplus'); ?>
                </p>
            </div>

            <div class="optimization-option">
                <label class="toggle-switch">
                    <input type="checkbox" 
                           name="lazy_loading" 
                           <?php checked($lazy_loading); ?>>
                    <span class="toggle-slider"></span>
                    <?php esc_html_e('Enable Lazy Loading', 'performanceplus'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Delay loading of images and iframes until they enter the viewport.', 'performanceplus'); ?>
                </p>
            </div>
        </div>
        <?php
    }
} 