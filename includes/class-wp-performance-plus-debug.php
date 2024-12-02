<?php
/**
 * Class WP_Performance_Plus_Debug
 * 
 * Handles debugging functionality for the plugin.
 */
class WP_Performance_Plus_Debug {
    /** @var string Option name for debug mode */
    private const OPTION_DEBUG_MODE = 'wp_performance_plus_debug_mode';
    
    /** @var string Option name for debug log */
    private const OPTION_DEBUG_LOG = 'wp_performance_plus_debug_log';

    /**
     * Initialize debug functionality
     */
    public function __construct() {
        // Add debug hooks
        add_action('admin_post_wp_performance_plus_toggle_debug', [$this, 'toggle_debug_mode']);
        add_action('admin_post_wp_performance_plus_clear_log', [$this, 'clear_debug_log']);
    }

    /**
     * Initialize debug settings
     */
    public function init_debug_settings() {
        register_setting(
            'wp_performance_plus_debug_settings',
            self::OPTION_DEBUG_MODE,
            [
                'type' => 'boolean',
                'default' => false
            ]
        );

        register_setting(
            'wp_performance_plus_debug_settings',
            self::OPTION_DEBUG_LOG,
            [
                'type' => 'array',
                'default' => []
            ]
        );
    }

    /**
     * Add debug menu
     */
    public function add_debug_menu() {
        add_submenu_page(
            'wp-performance-plus',
            __('Debug', 'wp-performance-plus'),
            __('Debug', 'wp-performance-plus'),
            'manage_options',
            'wp-performance-plus-debug',
            [$this, 'render_debug_page']
        );
    }

    /**
     * Render debug page
     */
    public function render_debug_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $debug_mode = get_option(self::OPTION_DEBUG_MODE, false);
        $debug_log = get_option(self::OPTION_DEBUG_LOG, []);

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Debug Settings', 'wp-performance-plus'); ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wp_performance_plus_toggle_debug">
                <?php wp_nonce_field('wp_performance_plus_toggle_debug_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Debug Mode', 'wp-performance-plus'); ?></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" 
                                       name="debug_mode" 
                                       <?php checked($debug_mode); ?>
                                >
                                <span class="toggle-slider"></span>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Enable debug mode to log detailed information about plugin operations.', 'wp-performance-plus'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Settings', 'wp-performance-plus'); ?>
                    </button>
                </p>
            </form>

            <?php if (!empty($debug_log)): ?>
                <div class="debug-log-section">
                    <h2><?php esc_html_e('Debug Log', 'wp-performance-plus'); ?></h2>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="wp_performance_plus_clear_log">
                        <?php wp_nonce_field('wp_performance_plus_clear_log_nonce'); ?>
                        
                        <button type="submit" class="button">
                            <?php esc_html_e('Clear Log', 'wp-performance-plus'); ?>
                        </button>
                    </form>

                    <div class="debug-log">
                        <?php foreach (array_reverse($debug_log) as $entry): ?>
                            <div class="log-entry">
                                <span class="log-time">
                                    <?php echo esc_html(date('Y-m-d H:i:s', $entry['time'])); ?>
                                </span>
                                <span class="log-level <?php echo esc_attr($entry['level']); ?>">
                                    <?php echo esc_html(strtoupper($entry['level'])); ?>
                                </span>
                                <span class="log-message">
                                    <?php echo esc_html($entry['message']); ?>
                                </span>
                                <?php if (!empty($entry['data'])): ?>
                                    <pre class="log-data">
                                        <?php echo esc_html(json_encode($entry['data'], JSON_PRETTY_PRINT)); ?>
                                    </pre>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Toggle debug mode
     */
    public function toggle_debug_mode() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wp_performance_plus_toggle_debug_nonce')) {
            wp_die(__('Invalid nonce specified', 'wp-performance-plus'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'wp-performance-plus'));
        }

        $debug_mode = isset($_POST['debug_mode']) ? (bool) $_POST['debug_mode'] : false;
        update_option(self::OPTION_DEBUG_MODE, $debug_mode);

        wp_redirect(admin_url('admin.php?page=wp-performance-plus-debug&updated=true'));
        exit;
    }

    /**
     * Clear debug log
     */
    public function clear_debug_log() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wp_performance_plus_clear_log_nonce')) {
            wp_die(__('Invalid nonce specified', 'wp-performance-plus'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'wp-performance-plus'));
        }

        update_option(self::OPTION_DEBUG_LOG, []);

        wp_redirect(admin_url('admin.php?page=wp-performance-plus-debug&cleared=true'));
        exit;
    }

    /**
     * Log a debug message
     */
    public function log($message, $level = 'info', $data = []) {
        if (!get_option(self::OPTION_DEBUG_MODE, false)) {
            return;
        }

        $log = get_option(self::OPTION_DEBUG_LOG, []);
        $log[] = [
            'time' => time(),
            'level' => $level,
            'message' => $message,
            'data' => $data
        ];

        // Keep only the last 1000 entries
        if (count($log) > 1000) {
            $log = array_slice($log, -1000);
        }

        update_option(self::OPTION_DEBUG_LOG, $log);
    }

    /**
     * Get debug information
     */
    public function get_debug_info() {
        global $wp_version;

        return [
            'wordpress' => [
                'version' => $wp_version,
                'site_url' => get_site_url(),
                'home_url' => get_home_url(),
                'is_multisite' => is_multisite(),
                'memory_limit' => WP_MEMORY_LIMIT,
                'debug_mode' => WP_DEBUG,
            ],
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'post_max_size' => ini_get('post_max_size'),
                'max_input_vars' => ini_get('max_input_vars'),
                'safe_mode' => ini_get('safe_mode'),
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'],
                'os' => PHP_OS,
                'architecture' => PHP_INT_SIZE * 8 . 'bit',
            ],
            'plugin' => [
                'version' => WP_PERFORMANCE_PLUS_VERSION,
                'debug_mode' => get_option(self::OPTION_DEBUG_MODE, false),
            ]
        ];
    }
} 