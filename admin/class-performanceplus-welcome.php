<?php
/**
 * Class PerformancePlus_Welcome
 * Handles the welcome screen and onboarding process for the plugin.
 * This class manages the initial setup wizard and guides users through CDN configuration.
 */

class PerformancePlus_Welcome {
    /** @var string User capability required to access this screen */
    private $capability = 'manage_options';

    /**
     * Initialize the welcome screen functionality.
     * Sets up necessary hooks and actions.
     */
    public function __construct() {
        // Handle redirection to welcome screen on activation
        add_action('admin_init', [$this, 'redirect_to_welcome_screen']);
    }

    /**
     * Redirects users to the welcome screen upon plugin activation.
     * Only redirects if the welcome screen flag is set and user has proper permissions.
     */
    public function redirect_to_welcome_screen() {
        if (!current_user_can($this->capability)) {
            return;
        }

        if (get_option('performanceplus_welcome_screen', false)) {
            delete_option('performanceplus_welcome_screen');
            wp_safe_redirect(admin_url('admin.php?page=performanceplus-dashboard'));
            exit;
        }
    }

    /**
     * Renders the welcome wizard interface.
     * Displays a step-by-step configuration process with progress tracking.
     */
    public function render_welcome_wizard() {
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'performanceplus'));
        }

        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        ?>
        <div class="wrap performanceplus-wizard">
            <h1><?php esc_html_e('Welcome to WP Performance Plus!', 'performanceplus'); ?></h1>
            
            <div class="wizard-progress">
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo ($current_step / 4) * 100; ?>%"></div>
                </div>
                <div class="step-count">
                    <?php printf(esc_html__('Step %d of 4', 'performanceplus'), $current_step); ?>
                </div>
            </div>

            <?php $this->render_step_content($current_step); ?>
        </div>
        <?php
    }

    private function render_step_content($step) {
        switch ($step) {
            case 1:
                $this->render_step_cdn_selection();
                break;
            case 2:
                $this->render_step_cdn_setup();
                break;
            case 3:
                $this->render_step_optimization();
                break;
            case 4:
                $this->render_step_finish();
                break;
        }
    }

    private function render_step_cdn_selection() {
        $current_tab = isset($_GET['method']) ? sanitize_key($_GET['method']) : 'basics';
        ?>
        <div class="wizard-step">
            <form method="post" action="" id="optimization-method-form">
                <?php wp_nonce_field('performanceplus_method_selection', 'method_selection_nonce'); ?>
                
                <h2><?php esc_html_e('Choose Your Optimization Method', 'performanceplus'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Select how you want to optimize your website:', 'performanceplus'); ?>
                </p>
                
                <div class="optimization-methods">
                    <nav class="nav-tab-wrapper">
                        <a href="#basics" class="nav-tab <?php echo $current_tab === 'basics' ? 'nav-tab-active' : ''; ?>" data-method="basics">
                            <span class="dashicons dashicons-dashboard"></span>
                            <?php esc_html_e('Basic Optimization', 'performanceplus'); ?>
                        </a>
                        <a href="#cloudflare" class="nav-tab <?php echo $current_tab === 'cloudflare' ? 'nav-tab-active' : ''; ?>" data-method="cloudflare">
                            <span class="dashicons dashicons-cloud"></span>
                            <?php esc_html_e('Cloudflare', 'performanceplus'); ?>
                        </a>
                        <a href="#stackpath" class="nav-tab <?php echo $current_tab === 'stackpath' ? 'nav-tab-active' : ''; ?>" data-method="stackpath">
                            <span class="dashicons dashicons-networking"></span>
                            <?php esc_html_e('StackPath', 'performanceplus'); ?>
                        </a>
                        <!-- Add other CDN providers -->
                    </nav>

                    <div class="tab-content">
                        <div id="basics" class="tab-pane <?php echo $current_tab === 'basics' ? 'active' : ''; ?>">
                            <h3><?php esc_html_e('Basic Optimization', 'performanceplus'); ?></h3>
                            <p><?php esc_html_e('Optimize your site using built-in WordPress features and local optimizations.', 'performanceplus'); ?></p>
                            <ul class="features-list">
                                <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Asset Minification', 'performanceplus'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Database Optimization', 'performanceplus'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Cache Management', 'performanceplus'); ?></li>
                            </ul>
                        </div>

                        <div id="cloudflare" class="tab-pane <?php echo $current_tab === 'cloudflare' ? 'active' : ''; ?>">
                            <h3><?php esc_html_e('Cloudflare', 'performanceplus'); ?></h3>
                            <p><?php esc_html_e('Global CDN with advanced security features and optimization tools.', 'performanceplus'); ?></p>
                            <ul class="features-list">
                                <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Global CDN', 'performanceplus'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('DDoS Protection', 'performanceplus'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('SSL Management', 'performanceplus'); ?></li>
                            </ul>
                        </div>

                        <!-- Add other CDN provider tabs -->
                    </div>
                </div>

                <input type="hidden" name="selected_method" id="selected_method" value="<?php echo esc_attr($current_tab); ?>">

                <div class="wizard-buttons">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Continue', 'performanceplus'); ?>
                    </button>
                </div>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                var method = $(this).data('method');
                
                // Update tabs
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Update content
                $('.tab-pane').removeClass('active');
                $($(this).attr('href')).addClass('active');
                
                // Update hidden input
                $('#selected_method').val(method);
            });
        });
        </script>
        <?php
    }

    private function render_step_cdn_setup() {
        ?>
        <div class="wizard-step">
            <h2><?php esc_html_e('Configure Your CDN', 'performanceplus'); ?></h2>
            <div id="cdn-setup-form">
                <!-- Dynamic content loaded via AJAX -->
            </div>
            <div class="wizard-buttons">
                <a href="<?php echo esc_url(add_query_arg('step', 1)); ?>" class="button">
                    <?php esc_html_e('Previous', 'performanceplus'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('step', 3)); ?>" class="button button-primary">
                    <?php esc_html_e('Next Step', 'performanceplus'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    private function render_step_optimization() {
        ?>
        <div class="wizard-step">
            <h2><?php esc_html_e('Configure Optimizations', 'performanceplus'); ?></h2>
            <form method="post" action="">
                <div class="optimization-options">
                    <label>
                        <input type="checkbox" name="enable_minification" checked>
                        <?php esc_html_e('Enable Asset Minification', 'performanceplus'); ?>
                    </label>
                    <label>
                        <input type="checkbox" name="enable_db_cleanup" checked>
                        <?php esc_html_e('Enable Database Cleanup', 'performanceplus'); ?>
                    </label>
                </div>
                <div class="wizard-buttons">
                    <a href="<?php echo esc_url(add_query_arg('step', 2)); ?>" class="button">
                        <?php esc_html_e('Previous', 'performanceplus'); ?>
                    </a>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save & Continue', 'performanceplus'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    private function render_step_finish() {
        ?>
        <div class="wizard-step">
            <h2><?php esc_html_e('Setup Complete!', 'performanceplus'); ?></h2>
            <p><?php esc_html_e('Congratulations! Your site is now optimized with WP Performance Plus.', 'performanceplus'); ?></p>
            <div class="wizard-buttons">
                <a href="<?php echo admin_url('admin.php?page=performanceplus-dashboard'); ?>" class="button button-primary">
                    <?php esc_html_e('Go to Dashboard', 'performanceplus'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    private function render_step_indicator($current_step) {
        $steps = [
            1 => 'Select CDN',
            2 => 'Configure CDN',
            3 => 'Optimization',
            4 => 'Finish'
        ];
        ?>
        <div class="step-indicator">
            <?php foreach ($steps as $num => $label): ?>
                <div class="step <?php echo $num === $current_step ? 'active' : ''; ?>">
                    <span class="step-number"><?php echo $num; ?></span>
                    <span class="step-label"><?php echo esc_html($label); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function handle_form_submission() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'performanceplus_setup_nonce')) {
            return;
        }

        $cdn_provider = isset($_POST['cdn_provider']) ? sanitize_text_field($_POST['cdn_provider']) : '';
        if (empty($cdn_provider)) {
            add_settings_error('performanceplus', 'no-cdn', __('Please select a CDN provider.', 'performanceplus'));
            return;
        }

        update_option('performanceplus_selected_cdn', $cdn_provider);
        wp_redirect(add_query_arg('step', 2));
        exit;
    }

    private function get_wizard_state() {
        return get_option('performanceplus_wizard_state', [
            'current_step' => 1,
            'selected_cdn' => '',
            'cdn_config' => [],
            'optimization_settings' => []
        ]);
    }

    private function update_wizard_state($key, $value) {
        $state = $this->get_wizard_state();
        $state[$key] = $value;
        update_option('performanceplus_wizard_state', $state);
    }

    private function show_error($message) {
        ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }

    private function show_success($message) {
        ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }
}

// Initialize the welcome screen functionality
new PerformancePlus_Welcome();
?>
