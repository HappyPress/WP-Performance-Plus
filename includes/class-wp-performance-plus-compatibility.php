<?php
/**
 * Class PerformancePlus_Compatibility
 * 
 * Handles plugin compatibility checks and conflicts.
 * Detects existing performance plugins and server configurations.
 */
class PerformancePlus_Compatibility {
    /**
     * List of known performance plugins to check
     * @var array
     */
    private $competing_plugins = [
        'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
        'wp-super-cache/wp-super-cache.php' => 'WP Super Cache',
        'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
        'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
        'wp-rocket/wp-rocket.php' => 'WP Rocket',
        'swift-performance-lite/performance.php' => 'Swift Performance',
        'cache-enabler/cache-enabler.php' => 'Cache Enabler',
        'sg-cachepress/sg-cachepress.php' => 'SG Optimizer',
        'breeze/breeze.php' => 'Breeze',
        'hummingbird-performance/wp-hummingbird.php' => 'Hummingbird',
        'autoptimize/autoptimize.php' => 'Autoptimize',
        'wp-optimize/wp-optimize.php' => 'WP-Optimize',
        'flying-press/flying-press.php' => 'FlyingPress',
        'perfmatters/perfmatters.php' => 'Perfmatters',
        'async-javascript/async-javascript.php' => 'Async JavaScript',
        'rocket-lazy-load/rocket-lazy-load.php' => 'Rocket Lazy Load',
        'imagify/imagify.php' => 'Imagify',
        'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
        'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel',
        'wp-smushit/wp-smush.php' => 'Smush',
        'cdn-enabler/cdn-enabler.php' => 'CDN Enabler',
        'bunnycdn/bunnycdn.php' => 'BunnyCDN',
        'cloudflare/cloudflare.php' => 'Cloudflare',
        'stackpath/stackpath.php' => 'StackPath',
        'fast-velocity-minify/fvm.php' => 'Fast Velocity Minify',
        'merge-minify-refresh/merge-minify-refresh.php' => 'Merge + Minify + Refresh'
    ];

    /**
     * Check for active competing plugins
     * 
     * @return array Array of active competing plugins
     */
    public function get_active_competing_plugins() {
        $active_plugins = [];
        
        foreach ($this->competing_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $active_plugins[$plugin_path] = $plugin_name;
            }
        }
        
        return $active_plugins;
    }

    /**
     * Check for existing server configurations
     * 
     * @return array Array of existing configurations
     */
    public function check_existing_configurations() {
        $configurations = [];

        // Check for existing .htaccess rules
        if ($this->has_existing_htaccess_rules()) {
            $configurations[] = [
                'type' => 'htaccess',
                'description' => __('Existing performance rules found in .htaccess', 'performanceplus')
            ];
        }

        // Check for object caching
        if (wp_using_ext_object_cache()) {
            $configurations[] = [
                'type' => 'object_cache',
                'description' => __('External object cache is already configured', 'performanceplus')
            ];
        }

        // Check for existing CDN configuration
        if ($this->detect_cdn_configuration()) {
            $configurations[] = [
                'type' => 'cdn',
                'description' => __('Existing CDN configuration detected', 'performanceplus')
            ];
        }

        return $configurations;
    }

    /**
     * Display activation notice for competing plugins
     */
    public function display_activation_notice() {
        $active_plugins = $this->get_active_competing_plugins();
        $configurations = $this->check_existing_configurations();

        if (empty($active_plugins) && empty($configurations)) {
            return;
        }

        add_action('admin_notices', function() use ($active_plugins, $configurations) {
            ?>
            <div class="notice notice-warning is-dismissible performanceplus-compatibility-notice">
                <h3><?php esc_html_e('PerformancePlus - Compatibility Notice', 'performanceplus'); ?></h3>
                
                <?php if (!empty($active_plugins)): ?>
                    <p>
                        <?php esc_html_e('The following performance plugins are currently active and may conflict with PerformancePlus:', 'performanceplus'); ?>
                    </p>
                    <ul>
                        <?php foreach ($active_plugins as $plugin_name): ?>
                            <li><?php echo esc_html($plugin_name); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($configurations)): ?>
                    <p>
                        <?php esc_html_e('The following configurations were detected:', 'performanceplus'); ?>
                    </p>
                    <ul>
                        <?php foreach ($configurations as $config): ?>
                            <li><?php echo esc_html($config['description']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <p>
                    <button type="button" class="button button-primary deactivate-competing-plugins">
                        <?php esc_html_e('Deactivate Competing Plugins', 'performanceplus'); ?>
                    </button>
                    <button type="button" class="button button-secondary dismiss-notice">
                        <?php esc_html_e('I understand, keep both', 'performanceplus'); ?>
                    </button>
                </p>
            </div>

            <script>
            jQuery(document).ready(function($) {
                $('.deactivate-competing-plugins').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'performanceplus_deactivate_plugins',
                            nonce: '<?php echo wp_create_nonce('performanceplus_deactivate_plugins'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data.message);
                            }
                        }
                    });
                });
            });
            </script>
            <?php
        });
    }

    /**
     * Check for existing .htaccess rules
     * 
     * @return bool
     */
    private function has_existing_htaccess_rules() {
        if (!file_exists(ABSPATH . '.htaccess')) {
            return false;
        }

        $htaccess = file_get_contents(ABSPATH . '.htaccess');
        
        // Check for common performance rules
        $patterns = [
            'ExpiresActive',
            'AddOutputFilterByType DEFLATE',
            'mod_pagespeed',
            'Cache-Control',
            '<FilesMatch "\.(jpg|jpeg|png|gif|js|css)">'
        ];

        foreach ($patterns as $pattern) {
            if (strpos($htaccess, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect existing CDN configuration
     * 
     * @return bool
     */
    private function detect_cdn_configuration() {
        // Check common CDN options
        $cdn_options = [
            'cdn_url',
            'cdn_domain',
            'cloudflare_api',
            'stackpath_api',
            'bunnycdn_api'
        ];

        foreach ($cdn_options as $option) {
            if (get_option($option)) {
                return true;
            }
        }

        // Check if site is behind Cloudflare
        if (isset($_SERVER['HTTP_CF_RAY'])) {
            return true;
        }

        return false;
    }

    /**
     * AJAX handler for deactivating competing plugins
     */
    public function handle_plugin_deactivation() {
        check_ajax_referer('performanceplus_deactivate_plugins', 'nonce');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => __('You do not have permission to deactivate plugins.', 'performanceplus')]);
        }

        $active_plugins = $this->get_active_competing_plugins();
        
        foreach ($active_plugins as $plugin_path => $plugin_name) {
            deactivate_plugins($plugin_path);
        }

        wp_send_json_success([
            'message' => __('Competing plugins have been deactivated.', 'performanceplus'),
            'deactivated' => array_values($active_plugins)
        ]);
    }

    /**
     * Enhanced server configuration checks
     */
    private function check_server_configurations() {
        $configurations = [];

        // PHP Configuration Checks
        $php_config = [
            'version' => [
                'value' => PHP_VERSION,
                'recommended' => '7.4',
                'status' => version_compare(PHP_VERSION, '7.4', '>='),
                'label' => __('PHP Version', 'performanceplus'),
                'description' => sprintf(__('Running PHP %s. Recommended: 7.4 or higher.', 'performanceplus'), PHP_VERSION)
            ],
            'memory_limit' => [
                'value' => ini_get('memory_limit'),
                'recommended' => '256M',
                'status' => $this->get_memory_limit() >= 256,
                'label' => __('Memory Limit', 'performanceplus'),
                'description' => sprintf(__('Current: %s. Recommended: 256M or higher.', 'performanceplus'), ini_get('memory_limit'))
            ],
            'post_max_size' => [
                'value' => ini_get('post_max_size'),
                'recommended' => '64M',
                'status' => $this->convert_to_bytes(ini_get('post_max_size')) >= $this->convert_to_bytes('64M'),
                'label' => __('Post Max Size', 'performanceplus')
            ],
            'max_execution_time' => [
                'value' => ini_get('max_execution_time'),
                'recommended' => 300,
                'status' => ini_get('max_execution_time') >= 300,
                'label' => __('Max Execution Time', 'performanceplus')
            ],
            'opcache' => [
                'value' => function_exists('opcache_get_status') ? 'Enabled' : 'Disabled',
                'recommended' => 'Enabled',
                'status' => function_exists('opcache_get_status'),
                'label' => __('OPcache', 'performanceplus')
            ]
        ];

        // MySQL Configuration Checks
        global $wpdb;
        $mysql_config = [
            'version' => [
                'value' => $wpdb->get_var('SELECT VERSION()'),
                'recommended' => '5.7',
                'status' => version_compare($wpdb->get_var('SELECT VERSION()'), '5.7', '>='),
                'label' => __('MySQL Version', 'performanceplus')
            ],
            'max_connections' => [
                'value' => $wpdb->get_var('SELECT @@max_connections'),
                'recommended' => 150,
                'status' => $wpdb->get_var('SELECT @@max_connections') >= 150,
                'label' => __('Max Connections', 'performanceplus')
            ],
            'query_cache' => [
                'value' => $wpdb->get_var('SELECT @@query_cache_size'),
                'recommended' => '64M',
                'status' => $this->convert_to_bytes($wpdb->get_var('SELECT @@query_cache_size')) >= $this->convert_to_bytes('64M'),
                'label' => __('Query Cache Size', 'performanceplus')
            ]
        ];

        // Web Server Checks
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
        $web_server = [
            'software' => [
                'value' => $server_software,
                'label' => __('Web Server', 'performanceplus')
            ]
        ];

        // Apache-specific checks
        if (stripos($server_software, 'apache') !== false) {
            $apache_modules = $this->get_apache_modules();
            $web_server['modules'] = [
                'mod_rewrite' => [
                    'value' => in_array('mod_rewrite', $apache_modules) ? 'Enabled' : 'Disabled',
                    'recommended' => 'Enabled',
                    'status' => in_array('mod_rewrite', $apache_modules),
                    'label' => 'mod_rewrite'
                ],
                'mod_expires' => [
                    'value' => in_array('mod_expires', $apache_modules) ? 'Enabled' : 'Disabled',
                    'recommended' => 'Enabled',
                    'status' => in_array('mod_expires', $apache_modules),
                    'label' => 'mod_expires'
                ],
                'mod_deflate' => [
                    'value' => in_array('mod_deflate', $apache_modules) ? 'Enabled' : 'Disabled',
                    'recommended' => 'Enabled',
                    'status' => in_array('mod_deflate', $apache_modules),
                    'label' => 'mod_deflate'
                ]
            ];
        }

        // SSL Configuration
        $ssl_config = [
            'enabled' => [
                'value' => is_ssl() ? 'Yes' : 'No',
                'recommended' => 'Yes',
                'status' => is_ssl(),
                'label' => __('SSL Enabled', 'performanceplus')
            ],
            'hsts' => [
                'value' => $this->check_hsts_enabled() ? 'Yes' : 'No',
                'recommended' => 'Yes',
                'status' => $this->check_hsts_enabled(),
                'label' => __('HSTS Enabled', 'performanceplus')
            ]
        ];

        // Object Cache Configuration
        $object_cache = [
            'enabled' => [
                'value' => wp_using_ext_object_cache() ? 'Yes' : 'No',
                'recommended' => 'Yes',
                'status' => wp_using_ext_object_cache(),
                'label' => __('External Object Cache', 'performanceplus')
            ],
            'persistent' => [
                'value' => $this->check_persistent_object_cache() ? 'Yes' : 'No',
                'recommended' => 'Yes',
                'status' => $this->check_persistent_object_cache(),
                'label' => __('Persistent Object Cache', 'performanceplus')
            ]
        ];

        // Check NGINX configuration
        $nginx_config = $this->check_nginx_configuration();

        return [
            'php' => $php_config,
            'mysql' => $mysql_config,
            'web_server' => $web_server,
            'ssl' => $ssl_config,
            'object_cache' => $object_cache,
            'nginx' => $nginx_config
        ];
    }

    /**
     * Check NGINX configuration
     */
    private function check_nginx_configuration() {
        $nginx_checks = [
            'fastcgi_cache' => [
                'value' => $this->detect_nginx_fastcgi_cache(),
                'recommended' => 'Enabled',
                'status' => $this->detect_nginx_fastcgi_cache(),
                'label' => __('FastCGI Cache', 'performanceplus'),
                'description' => __('NGINX FastCGI Cache provides fast caching for PHP applications.', 'performanceplus')
            ],
            'gzip' => [
                'value' => $this->detect_nginx_gzip(),
                'recommended' => 'Enabled',
                'status' => $this->detect_nginx_gzip(),
                'label' => __('GZIP Compression', 'performanceplus')
            ],
            'http2' => [
                'value' => $this->detect_http2(),
                'recommended' => 'Enabled',
                'status' => $this->detect_http2(),
                'label' => __('HTTP/2', 'performanceplus')
            ],
            'microcache' => [
                'value' => $this->detect_nginx_microcache(),
                'recommended' => 'Enabled',
                'status' => $this->detect_nginx_microcache(),
                'label' => __('Microcaching', 'performanceplus')
            ]
        ];

        return $nginx_checks;
    }

    /**
     * Detect NGINX FastCGI Cache
     */
    private function detect_nginx_fastcgi_cache() {
        // Check for common FastCGI cache headers
        $headers = $this->get_site_headers();
        return isset($headers['X-FastCGI-Cache']);
    }

    /**
     * Detect NGINX GZIP
     */
    private function detect_nginx_gzip() {
        $headers = $this->get_site_headers();
        return isset($headers['Content-Encoding']) && 
               stripos($headers['Content-Encoding'], 'gzip') !== false;
    }

    /**
     * Detect HTTP/2
     */
    private function detect_http2() {
        $headers = $this->get_site_headers();
        return isset($headers[':protocol']) && 
               stripos($headers[':protocol'], 'HTTP/2') !== false;
    }

    /**
     * Get site headers
     */
    private function get_site_headers() {
        $url = home_url();
        $response = wp_remote_get($url);
        return wp_remote_retrieve_headers($response);
    }

    /**
     * Get recommendations based on configuration checks
     */
    private function get_recommendations() {
        $configs = $this->check_server_configurations();
        $recommendations = [];

        // PHP Recommendations
        if (!$configs['php']['version']['status']) {
            $recommendations[] = [
                'priority' => 'high',
                'message' => sprintf(
                    __('Upgrade PHP to version %s or higher for better performance and security.', 'performanceplus'),
                    $configs['php']['version']['recommended']
                ),
                'action' => 'contact_host'
            ];
        }

        // More recommendations based on other checks...
        // Would you like me to continue with more detailed recommendations?
    }

    /**
     * Render system compatibility report
     */
    public function render_compatibility_report() {
        $configs = $this->check_server_configurations();
        $score = $this->calculate_performance_score($configs);
        ?>
        <div class="wrap performanceplus-compatibility-report">
            <h1><?php esc_html_e('System Compatibility Report', 'performanceplus'); ?></h1>

            <!-- Overall Score Section -->
            <div class="performance-score-wrapper">
                <div class="performance-score <?php echo esc_attr($this->get_score_class($score)); ?>">
                    <div class="score-number"><?php echo esc_html($score); ?></div>
                    <div class="score-label"><?php esc_html_e('Performance Score', 'performanceplus'); ?></div>
                </div>
                <div class="score-summary">
                    <?php echo esc_html($this->get_score_summary($score)); ?>
                </div>
            </div>

            <!-- Configuration Sections -->
            <?php foreach ($configs as $section => $checks): ?>
                <div class="config-section" id="section-<?php echo esc_attr($section); ?>">
                    <h2 class="section-title">
                        <?php echo esc_html($this->get_section_title($section)); ?>
                        <span class="section-score <?php echo esc_attr($this->get_score_class($this->calculate_section_score($checks))); ?>">
                            <?php echo esc_html($this->calculate_section_score($checks)); ?>
                        </span>
                    </h2>

                    <table class="widefat config-checks">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Check', 'performanceplus'); ?></th>
                                <th><?php esc_html_e('Current', 'performanceplus'); ?></th>
                                <th><?php esc_html_e('Recommended', 'performanceplus'); ?></th>
                                <th><?php esc_html_e('Status', 'performanceplus'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($checks as $key => $check): ?>
                                <tr>
                                    <td>
                                        <?php echo esc_html($check['label']); ?>
                                        <?php if (!empty($check['description'])): ?>
                                            <span class="dashicons dashicons-info-outline" 
                                                  title="<?php echo esc_attr($check['description']); ?>">
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($check['value']); ?></td>
                                    <td><?php echo esc_html($check['recommended'] ?? '—'); ?></td>
                                    <td>
                                        <span class="status-indicator <?php echo esc_attr($this->get_status_class($check['status'])); ?>">
                                            <?php echo $this->get_status_icon($check['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($recommendations = $this->get_section_recommendations($section, $checks)): ?>
                        <div class="section-recommendations">
                            <h3><?php esc_html_e('Recommendations', 'performanceplus'); ?></h3>
                            <ul class="recommendations-list">
                                <?php foreach ($recommendations as $rec): ?>
                                    <li class="recommendation-item priority-<?php echo esc_attr($rec['priority']); ?>">
                                        <span class="recommendation-icon"></span>
                                        <div class="recommendation-content">
                                            <p><?php echo esc_html($rec['message']); ?></p>
                                            <?php if (!empty($rec['action'])): ?>
                                                <button class="button button-secondary recommendation-action" 
                                                        data-action="<?php echo esc_attr($rec['action']); ?>"
                                                        data-nonce="<?php echo wp_create_nonce('performanceplus_action'); ?>">
                                                    <?php echo esc_html($this->get_action_label($rec['action'])); ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Action Buttons -->
            <div class="compatibility-actions">
                <button type="button" class="button button-primary" id="optimize-all">
                    <?php esc_html_e('Optimize All', 'performanceplus'); ?>
                </button>
                <button type="button" class="button button-secondary" id="export-report">
                    <?php esc_html_e('Export Report', 'performanceplus'); ?>
                </button>
            </div>
        </div>

        <!-- Progress Modal -->
        <div id="optimization-progress" class="performanceplus-modal" style="display:none;">
            <div class="modal-content">
                <h2><?php esc_html_e('Optimizing Your Site', 'performanceplus'); ?></h2>
                <div class="progress-bar">
                    <div class="progress"></div>
                </div>
                <div class="progress-status"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Calculate overall performance score
     */
    private function calculate_performance_score($configs) {
        $scores = [];
        $weights = [
            'php' => 0.3,
            'mysql' => 0.2,
            'web_server' => 0.2,
            'ssl' => 0.15,
            'object_cache' => 0.15
        ];

        foreach ($configs as $section => $checks) {
            $section_score = $this->calculate_section_score($checks);
            $scores[$section] = $section_score * ($weights[$section] ?? 1);
        }

        return round(array_sum($scores));
    }

    /**
     * Calculate section score
     */
    private function calculate_section_score($checks) {
        $total = 0;
        $passed = 0;

        foreach ($checks as $check) {
            if (isset($check['status'])) {
                $total++;
                if ($check['status']) {
                    $passed++;
                }
            }
        }

        return $total ? round(($passed / $total) * 100) : 100;
    }

    /**
     * Get detailed recommendations based on system checks
     * 
     * @return array Array of recommendations with priority and actions
     */
    private function get_detailed_recommendations() {
        $configs = $this->check_server_configurations();
        $recommendations = [];

        // PHP Recommendations
        if (!$configs['php']['version']['status']) {
            $recommendations[] = [
                'section' => 'php',
                'priority' => 'high',
                'message' => sprintf(
                    __('PHP version %s detected. Upgrade to version %s or higher for better performance and security.', 'performanceplus'),
                    PHP_VERSION,
                    $configs['php']['version']['recommended']
                ),
                'action' => 'contact_host',
                'action_label' => __('Contact Host', 'performanceplus'),
                'impact' => 'critical'
            ];
        }

        if (!$configs['php']['opcache']['status']) {
            $recommendations[] = [
                'section' => 'php',
                'priority' => 'medium',
                'message' => __('Enable PHP OPcache to improve PHP performance.', 'performanceplus'),
                'action' => 'enable_opcache',
                'action_label' => __('Enable OPcache', 'performanceplus'),
                'impact' => 'significant'
            ];
        }

        // MySQL Recommendations
        if (!$configs['mysql']['query_cache']['status']) {
            $recommendations[] = [
                'section' => 'mysql',
                'priority' => 'medium',
                'message' => __('Increase MySQL query cache size for better database performance.', 'performanceplus'),
                'action' => 'optimize_mysql',
                'action_label' => __('Optimize MySQL', 'performanceplus'),
                'impact' => 'moderate'
            ];
        }

        // Web Server Recommendations
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
        if (stripos($server_software, 'apache') !== false) {
            if (!$configs['web_server']['modules']['mod_expires']['status']) {
                $recommendations[] = [
                    'section' => 'web_server',
                    'priority' => 'high',
                    'message' => __('Enable Apache mod_expires for better browser caching.', 'performanceplus'),
                    'action' => 'enable_expires',
                    'action_label' => __('Enable mod_expires', 'performanceplus'),
                    'impact' => 'significant'
                ];
            }
        }

        // SSL Recommendations
        if (!$configs['ssl']['enabled']['status']) {
            $recommendations[] = [
                'section' => 'ssl',
                'priority' => 'high',
                'message' => __('Enable SSL/HTTPS for better security and performance.', 'performanceplus'),
                'action' => 'enable_ssl',
                'action_label' => __('Enable SSL', 'performanceplus'),
                'impact' => 'critical'
            ];
        }

        // Object Cache Recommendations
        if (!$configs['object_cache']['enabled']['status']) {
            $recommendations[] = [
                'section' => 'object_cache',
                'priority' => 'medium',
                'message' => __('Enable object caching to improve dynamic page load times.', 'performanceplus'),
                'action' => 'setup_object_cache',
                'action_label' => __('Setup Object Cache', 'performanceplus'),
                'impact' => 'significant'
            ];
        }

        // NGINX-specific Recommendations
        if (isset($configs['nginx'])) {
            if (!$configs['nginx']['fastcgi_cache']['status']) {
                $recommendations[] = [
                    'section' => 'nginx',
                    'priority' => 'medium',
                    'message' => __('Enable NGINX FastCGI Cache for better PHP performance.', 'performanceplus'),
                    'action' => 'enable_fastcgi_cache',
                    'action_label' => __('Enable FastCGI Cache', 'performanceplus'),
                    'impact' => 'significant'
                ];
            }
        }

        return $this->sort_recommendations($recommendations);
    }

    /**
     * Sort recommendations by priority and impact
     */
    private function sort_recommendations($recommendations) {
        $priority_weights = [
            'high' => 3,
            'medium' => 2,
            'low' => 1
        ];

        $impact_weights = [
            'critical' => 3,
            'significant' => 2,
            'moderate' => 1
        ];

        usort($recommendations, function($a, $b) use ($priority_weights, $impact_weights) {
            $priority_diff = ($priority_weights[$b['priority']] ?? 0) - ($priority_weights[$a['priority']] ?? 0);
            if ($priority_diff !== 0) {
                return $priority_diff;
            }
            return ($impact_weights[$b['impact']] ?? 0) - ($impact_weights[$a['impact']] ?? 0);
        });

        return $recommendations;
    }

    /**
     * Get action handler for recommendation
     */
    public function get_action_handler($action) {
        $handlers = [
            'enable_opcache' => [$this, 'handle_enable_opcache'],
            'optimize_mysql' => [$this, 'handle_optimize_mysql'],
            'enable_expires' => [$this, 'handle_enable_expires'],
            'enable_ssl' => [$this, 'handle_enable_ssl'],
            'setup_object_cache' => [$this, 'handle_setup_object_cache'],
            'enable_fastcgi_cache' => [$this, 'handle_enable_fastcgi_cache']
        ];

        return $handlers[$action] ?? null;
    }
} 