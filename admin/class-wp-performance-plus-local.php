<?php
/**
 * Class WPPerformancePlus_Local
 * 
 * Manages local optimization functionality.
 * Handles asset minification, database cleanup, and cache management.
 * Provides settings interface for local optimization configuration.
 */
class WPPerformancePlus_Local {
    /** @var string Option name for minification setting */
    private const OPTION_MINIFICATION = 'wp_performanceplus_enable_minification';
    
    /** @var string Option name for database cleanup setting */
    private const OPTION_DB_CLEANUP = 'wp_performanceplus_enable_database_cleanup';
    
    /** @var string Option name for cleanup schedule */
    private const OPTION_CLEANUP_SCHEDULE = 'wp_performanceplus_cleanup_schedule';

    /** @var string Option name for browser caching */
    private const OPTION_BROWSER_CACHE = 'wp_performanceplus_enable_browser_cache';

    /** @var string Option name for GZIP compression */
    private const OPTION_GZIP = 'wp_performanceplus_enable_gzip';

    /** @var string Option name for lazy loading */
    private const OPTION_LAZY_LOAD = 'wp_performanceplus_enable_lazy_loading';

    /** @var string Option name for image optimization */
    private const OPTION_IMAGE_OPTIMIZE = 'wp_performanceplus_enable_image_optimization';

    /**
     * Initialize Local Optimization functionality
     * Sets up necessary hooks and actions for admin functionality
     */
    public function __construct() {
        // Handle form submissions for saving settings
        add_action('admin_post_wp_performanceplus_local_save', [$this, 'save_local_settings']);
        
        // Schedule database cleanup if enabled
        add_action('wp_performanceplus_database_cleanup', [$this, 'run_database_cleanup']);
    }

    /**
     * Renders the Local Optimization settings interface
     * Displays form for optimization and cleanup configuration
     */
    public function render_settings() {
        $enable_minification = get_option(self::OPTION_MINIFICATION, true);
        $enable_database_cleanup = get_option(self::OPTION_DB_CLEANUP, true);
        $cleanup_schedule = get_option(self::OPTION_CLEANUP_SCHEDULE, 'weekly');
        ?>
        <div class="local-settings-form">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wp_performanceplus_local_save">
                <?php wp_nonce_field('wp_performanceplus_local_save_nonce', '_wpnonce'); ?>
                
                <div class="optimization-settings">
                    <h3><?php esc_html_e('Asset Optimization', 'wp_performanceplus'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="enable_minification">
                                    <?php esc_html_e('Enable Minification', 'wp_performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Minifies HTML, CSS, and JavaScript files to reduce file size and improve load times.', 'wp_performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" 
                                           name="enable_minification" 
                                           id="enable_minification" 
                                           <?php checked($enable_minification); ?>
                                    >
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Automatically minify and optimize your website\'s assets.', 'wp_performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h3><?php esc_html_e('Database Optimization', 'wp_performanceplus'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="enable_database_cleanup">
                                    <?php esc_html_e('Enable Database Cleanup', 'wp_performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Automatically removes post revisions, spam comments, and expired transients to keep your database lean.', 'wp_performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" 
                                           name="enable_database_cleanup" 
                                           id="enable_database_cleanup" 
                                           <?php checked($enable_database_cleanup); ?>
                                    >
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Schedule automatic database cleanup to maintain optimal performance.', 'wp_performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cleanup_schedule">
                                    <?php esc_html_e('Cleanup Schedule', 'wp_performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Set how frequently the database cleanup should run.', 'wp_performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <select name="cleanup_schedule" id="cleanup_schedule">
                                    <option value="daily" <?php selected($cleanup_schedule, 'daily'); ?>>
                                        <?php esc_html_e('Daily', 'wp_performanceplus'); ?>
                                    </option>
                                    <option value="weekly" <?php selected($cleanup_schedule, 'weekly'); ?>>
                                        <?php esc_html_e('Weekly', 'wp_performanceplus'); ?>
                                    </option>
                                    <option value="monthly" <?php selected($cleanup_schedule, 'monthly'); ?>>
                                        <?php esc_html_e('Monthly', 'wp_performanceplus'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Choose how often the database cleanup should run.', 'wp_performanceplus'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php esc_html_e('Browser Caching', 'wp_performanceplus'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="enable_browser_cache">
                                    <?php esc_html_e('Enable Browser Caching', 'wp_performanceplus'); ?>
                                    <span class="dashicons dashicons-editor-help" 
                                          title="<?php esc_attr_e('Adds browser caching headers to static resources.', 'wp_performanceplus'); ?>">
                                    </span>
                                </label>
                            </th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="enable_browser_cache" id="enable_browser_cache">
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>

                    <h3><?php esc_html_e('GZIP Compression', 'wp_performanceplus'); ?></h3>
                    <!-- Similar settings for GZIP -->

                    <h3><?php esc_html_e('Image Optimization', 'wp_performanceplus'); ?></h3>
                    <!-- Image optimization settings -->
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Settings', 'wp_performanceplus'); ?>
                        </button>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Saves Local Optimization settings submitted from the admin form
     * Handles validation, sanitization, and storage of settings
     */
    public function save_local_settings() {
        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wp_performanceplus_local_save_nonce')) {
            wp_die(__('Invalid nonce specified', 'wp_performanceplus'), __('Error', 'wp_performanceplus'), ['back_link' => true]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not authorized to perform this action.', 'wp_performanceplus'), __('Error', 'wp_performanceplus'), ['back_link' => true]);
        }

        // Sanitize and save settings
        $enable_minification = isset($_POST['enable_minification']);
        $enable_database_cleanup = isset($_POST['enable_database_cleanup']);
        $cleanup_schedule = isset($_POST['cleanup_schedule']) ? 
            sanitize_text_field($_POST['cleanup_schedule']) : 'weekly';

        update_option(self::OPTION_MINIFICATION, $enable_minification);
        update_option(self::OPTION_DB_CLEANUP, $enable_database_cleanup);
        update_option(self::OPTION_CLEANUP_SCHEDULE, $cleanup_schedule);

        // Update cleanup schedule
        wp_clear_scheduled_hook('wp_performanceplus_database_cleanup');
        if ($enable_database_cleanup) {
            if (!wp_next_scheduled('wp_performanceplus_database_cleanup')) {
                wp_schedule_event(time(), $cleanup_schedule, 'wp_performanceplus_database_cleanup');
            }
        }

        // Redirect back to settings page with success message
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }

    /**
     * Runs the database cleanup task
     * Removes unnecessary data and optimizes database tables
     */
    public function run_database_cleanup() {
        global $wpdb;

        // Delete post revisions
        $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'");

        // Delete trashed posts
        $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'trash'");

        // Delete spam comments
        $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'");

        // Delete expired transients
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%' AND option_value < NOW()");

        // Optimize database tables
        $wpdb->query("OPTIMIZE TABLE $wpdb->posts, $wpdb->comments, $wpdb->options");

        do_action('wp_performanceplus_after_database_cleanup');
    }
}

// Initialize Local Optimization settings management
new WPPerformancePlus_Local();
?>
