<?php
if (!defined('WPINC')) {
    die;
}

// Initialize variables
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
$onboarding_completed = get_option('performanceplus_onboarding_completed', false);
$current_step = get_option('performanceplus_onboarding_step', 1);
?>

<div class="wrap performanceplus-dashboard">
    <?php if (!$onboarding_completed): ?>
    <div class="onboarding-wizard">
        <div class="wizard-header">
            <h2><span class="dashicons dashicons-performance"></span> Welcome to Performance Plus</h2>
            <p class="wizard-subtitle">Let's optimize your website in a few simple steps</p>
        </div>

        <!-- Progress Steps -->
        <div class="wizard-progress">
            <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo ($current_step - 1) * 50; ?>%"></div>
            </div>
            <div class="progress-steps">
                <div class="step<?php echo $current_step >= 1 ? ' active' : ''; ?><?php echo $current_step > 1 ? ' completed' : ''; ?>" data-step="1">
                    <div class="step-icon">
                        <span class="step-number">1</span>
                        <span class="step-check dashicons dashicons-yes-alt"></span>
                    </div>
                    <span class="step-label">Cache</span>
                </div>
                <div class="step<?php echo $current_step >= 2 ? ' active' : ''; ?><?php echo $current_step > 2 ? ' completed' : ''; ?>" data-step="2">
                    <div class="step-icon">
                        <span class="step-number">2</span>
                        <span class="step-check dashicons dashicons-yes-alt"></span>
                    </div>
                    <span class="step-label">CDN</span>
                </div>
                <div class="step<?php echo $current_step >= 3 ? ' active' : ''; ?><?php echo $current_step > 3 ? ' completed' : ''; ?>" data-step="3">
                    <div class="step-icon">
                        <span class="step-number">3</span>
                        <span class="step-check dashicons dashicons-yes-alt"></span>
                    </div>
                    <span class="step-label">Optimize</span>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="wizard-content">
            <div class="step-panel<?php echo $current_step === 1 ? ' active' : ''; ?>" data-step="1">
                <div class="step-header">
                    <div class="step-icon">
                        <span class="dashicons dashicons-dashboard"></span>
                    </div>
                    <div class="step-title">
                        <h3>Cache Configuration</h3>
                        <p>Enable caching to dramatically improve your site's performance</p>
                    </div>
                </div>
                <div class="step-body">
                    <div class="setting-card">
                        <label class="toggle-switch">
                            <input type="checkbox" id="enable-cache" name="cache_enabled">
                            <span class="toggle-slider"></span>
                            <div class="toggle-content">
                                <span class="toggle-label">Enable Page Caching</span>
                                <span class="toggle-description">Page caching can improve your site's loading speed by up to 5x by storing frequently accessed content.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="step-panel<?php echo $current_step === 2 ? ' active' : ''; ?>" data-step="2">
                <div class="step-header">
                    <div class="step-icon">
                        <span class="dashicons dashicons-admin-site-alt3"></span>
                    </div>
                    <div class="step-title">
                        <h3>CDN Setup</h3>
                        <p>Connect your CDN to serve assets faster globally</p>
                    </div>
                </div>
                <div class="step-body">
                    <div class="setting-card">
                        <div class="cdn-selector">
                            <label class="setting-label">Select Your CDN Provider</label>
                            <select id="cdn-provider" name="cdn_provider" class="enhanced-select">
                                <option value="">Choose a provider...</option>
                                <option value="cloudflare">
                                    <span class="provider-icon cloudflare"></span>
                                    Cloudflare
                                </option>
                                <option value="bunnycdn">
                                    <span class="provider-icon bunnycdn"></span>
                                    BunnyCDN
                                </option>
                                <option value="stackpath">
                                    <span class="provider-icon stackpath"></span>
                                    StackPath
                                </option>
                            </select>
                        </div>
                        <p class="setting-description">
                            A CDN can reduce your site's loading time by up to 60% for global visitors by serving content from locations closer to them.
                        </p>
                        <div class="setting-benefits">
                            <div class="benefit-item">
                                <span class="dashicons dashicons-admin-site"></span>
                                <span>Global reach</span>
                            </div>
                            <div class="benefit-item">
                                <span class="dashicons dashicons-shield"></span>
                                <span>DDoS protection</span>
                            </div>
                            <div class="benefit-item">
                                <span class="dashicons dashicons-download"></span>
                                <span>Faster delivery</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-panel<?php echo $current_step === 3 ? ' active' : ''; ?>" data-step="3">
                <div class="step-header">
                    <div class="step-icon">
                        <span class="dashicons dashicons-admin-tools"></span>
                    </div>
                    <div class="step-title">
                        <h3>Optimization Settings</h3>
                        <p>Choose optimization features to enable</p>
                    </div>
                </div>
                <div class="step-body">
                    <div class="setting-card">
                        <div class="optimization-options">
                            <label class="toggle-switch">
                                <input type="checkbox" id="minify-code" name="minify_code">
                                <span class="toggle-slider"></span>
                                <div class="toggle-content">
                                    <span class="toggle-label">Minify CSS & JavaScript</span>
                                    <span class="toggle-description">Reduce file sizes by removing unnecessary characters</span>
                                </div>
                            </label>

                            <label class="toggle-switch">
                                <input type="checkbox" id="lazy-loading" name="lazy_loading">
                                <span class="toggle-slider"></span>
                                <div class="toggle-content">
                                    <span class="toggle-label">Enable Lazy Loading</span>
                                    <span class="toggle-description">Load images only when they enter the viewport</span>
                                </div>
                            </label>

                            <label class="toggle-switch">
                                <input type="checkbox" id="image-optimize" name="image_optimize">
                                <span class="toggle-slider"></span>
                                <div class="toggle-content">
                                    <span class="toggle-label">Optimize Images</span>
                                    <span class="toggle-description">Automatically compress and optimize images</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="wizard-footer">
            <?php if ($current_step > 1): ?>
            <button class="button prev-step">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                Previous
            </button>
            <?php endif; ?>

            <?php if ($current_step < 3): ?>
            <button class="button button-primary next-step">
                Next
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </button>
            <?php else: ?>
            <button class="button button-primary finish-setup">
                <span class="dashicons dashicons-yes"></span>
                Complete Setup
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Main Dashboard Content -->
    <div class="dashboard-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="header-actions">
            <button class="button button-secondary refresh-stats">
                <span class="dashicons dashicons-update"></span> Refresh Stats
            </button>
            <button class="button button-primary run-optimization">
                <span class="dashicons dashicons-performance"></span> Run Optimization
            </button>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Performance Score Card -->
        <div class="dashboard-card score-card">
            <div class="card-header">
                <h2><span class="dashicons dashicons-chart-bar"></span> Performance Score</h2>
            </div>
            <div class="card-content">
                <?php
                $performance_score = get_option('performanceplus_performance_score', 0);
                $total_optimized = get_option('performanceplus_total_optimized', 0);
                $bytes_saved = get_option('performanceplus_bytes_saved', 0);
                $load_time_improvement = get_option('performanceplus_load_time_improvement', 0);
                ?>
                <div class="performance-score-wrapper">
                    <div class="circular-progress" data-score="<?php echo esc_attr($performance_score); ?>">
                        <svg viewBox="0 0 100 100">
                            <circle class="progress-bg" cx="50" cy="50" r="45"></circle>
                            <circle class="progress-bar" cx="50" cy="50" r="45"></circle>
                        </svg>
                        <div class="score-value">
                            <span class="current-score"><?php echo esc_html($performance_score); ?></span>
                            <span class="score-label">Score</span>
                        </div>
                    </div>
                    <div class="score-metrics">
                        <div class="metric">
                            <span class="metric-value"><?php echo esc_html($total_optimized); ?></span>
                            <span class="metric-label">Assets Optimized</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo esc_html(size_format($bytes_saved)); ?></span>
                            <span class="metric-label">Total Saved</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo esc_html(number_format($load_time_improvement, 1)); ?>s</span>
                            <span class="metric-label">Faster Loading</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rest of the dashboard cards... -->
        <?php include('dashboard/status-card.php'); ?>
        <?php include('dashboard/actions-card.php'); ?>
        <?php include('dashboard/trends-card.php'); ?>
    </div>
    <?php endif; ?>
</div> 