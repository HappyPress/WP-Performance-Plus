<?php
/**
 * Documentation System for Enterprise Features
 * 
 * Comprehensive documentation system with user guides, API documentation,
 * troubleshooting guides, interactive tutorials, context-sensitive help,
 * and searchable knowledge base.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Documentation_System {
    
    /**
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Documentation structure
     * @var array
     */
    private $documentation_structure = array();
    
    /**
     * Knowledge base articles
     * @var array
     */
    private $knowledge_base = array();
    
    /**
     * API documentation
     * @var array
     */
    private $api_documentation = array();
    
    /**
     * Interactive tutorials
     * @var array
     */
    private $tutorials = array();
    
    /**
     * Troubleshooting guides
     * @var array
     */
    private $troubleshooting_guides = array();
    
    /**
     * Context-sensitive help
     * @var array
     */
    private $contextual_help = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('wp_performance_plus_settings', array());
        
        $this->init_documentation_structure();
        $this->init_knowledge_base();
        $this->init_api_documentation();
        $this->init_tutorials();
        $this->init_troubleshooting_guides();
        $this->init_contextual_help();
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Documentation display hooks
        add_action('wp_performance_plus_display_documentation', array($this, 'display_documentation'));
        add_action('wp_performance_plus_display_help_content', array($this, 'display_help_content'));
        
        // AJAX handlers for documentation interface
        add_action('wp_ajax_wp_performance_plus_search_documentation', array($this, 'ajax_search_documentation'));
        add_action('wp_ajax_wp_performance_plus_get_help_article', array($this, 'ajax_get_help_article'));
        add_action('wp_ajax_wp_performance_plus_get_tutorial_step', array($this, 'ajax_get_tutorial_step'));
        add_action('wp_ajax_wp_performance_plus_complete_tutorial', array($this, 'ajax_complete_tutorial'));
        add_action('wp_ajax_wp_performance_plus_get_contextual_help', array($this, 'ajax_get_contextual_help'));
        add_action('wp_ajax_wp_performance_plus_export_documentation', array($this, 'ajax_export_documentation'));
        add_action('wp_ajax_wp_performance_plus_generate_api_docs', array($this, 'ajax_generate_api_docs'));
        
        // Admin help tabs
        add_action('admin_head', array($this, 'add_help_tabs'));
        
        // Documentation generation hooks
        add_action('wp_performance_plus_generate_user_guides', array($this, 'generate_user_guides'));
        add_action('wp_performance_plus_update_documentation', array($this, 'update_documentation'));
        
        // Interactive tutorial hooks
        add_action('wp_performance_plus_start_tutorial', array($this, 'start_tutorial'), 10, 2);
        add_action('wp_performance_plus_next_tutorial_step', array($this, 'next_tutorial_step'), 10, 2);
        
        // Documentation analytics
        add_action('wp_performance_plus_track_documentation_usage', array($this, 'track_documentation_usage'), 10, 3);
    }
    
    /**
     * Initialize documentation structure
     */
    private function init_documentation_structure() {
        $this->documentation_structure = array(
            'getting_started' => array(
                'title' => __('Getting Started', 'wp-performance-plus'),
                'icon' => 'dashicons-welcome-learn-more',
                'order' => 1,
                'sections' => array(
                    'installation' => __('Installation & Setup', 'wp-performance-plus'),
                    'quick_start' => __('Quick Start Guide', 'wp-performance-plus'),
                    'configuration_wizard' => __('Configuration Wizard', 'wp-performance-plus'),
                    'first_steps' => __('First Steps', 'wp-performance-plus')
                )
            ),
            'cdn_management' => array(
                'title' => __('CDN Management', 'wp-performance-plus'),
                'icon' => 'dashicons-cloud',
                'order' => 2,
                'sections' => array(
                    'provider_setup' => __('CDN Provider Setup', 'wp-performance-plus'),
                    'multi_cdn' => __('Multi-CDN Configuration', 'wp-performance-plus'),
                    'failover' => __('Failover & Load Balancing', 'wp-performance-plus'),
                    'optimization' => __('CDN Optimization', 'wp-performance-plus'),
                    'troubleshooting' => __('CDN Troubleshooting', 'wp-performance-plus')
                )
            ),
            'performance_monitoring' => array(
                'title' => __('Performance Monitoring', 'wp-performance-plus'),
                'icon' => 'dashicons-chart-line',
                'order' => 3,
                'sections' => array(
                    'metrics_overview' => __('Performance Metrics', 'wp-performance-plus'),
                    'real_time_monitoring' => __('Real-time Monitoring', 'wp-performance-plus'),
                    'analytics_dashboard' => __('Analytics Dashboard', 'wp-performance-plus'),
                    'custom_metrics' => __('Custom Metrics', 'wp-performance-plus'),
                    'reporting' => __('Reports & Exports', 'wp-performance-plus')
                )
            ),
            'alerts_notifications' => array(
                'title' => __('Alerts & Notifications', 'wp-performance-plus'),
                'icon' => 'dashicons-warning',
                'order' => 4,
                'sections' => array(
                    'alert_configuration' => __('Alert Configuration', 'wp-performance-plus'),
                    'notification_channels' => __('Notification Channels', 'wp-performance-plus'),
                    'escalation_policies' => __('Escalation Policies', 'wp-performance-plus'),
                    'incident_management' => __('Incident Management', 'wp-performance-plus')
                )
            ),
            'enterprise_features' => array(
                'title' => __('Enterprise Features', 'wp-performance-plus'),
                'icon' => 'dashicons-building',
                'order' => 5,
                'sections' => array(
                    'multi_site_management' => __('Multi-site Management', 'wp-performance-plus'),
                    'auto_scaling' => __('Auto-scaling', 'wp-performance-plus'),
                    'advanced_optimization' => __('Advanced Optimization', 'wp-performance-plus'),
                    'api_integration' => __('API Integration', 'wp-performance-plus'),
                    'compliance' => __('Compliance & Security', 'wp-performance-plus')
                )
            ),
            'api_reference' => array(
                'title' => __('API Reference', 'wp-performance-plus'),
                'icon' => 'dashicons-rest-api',
                'order' => 6,
                'sections' => array(
                    'rest_api' => __('REST API', 'wp-performance-plus'),
                    'hooks_filters' => __('Hooks & Filters', 'wp-performance-plus'),
                    'classes_methods' => __('Classes & Methods', 'wp-performance-plus'),
                    'examples' => __('Code Examples', 'wp-performance-plus')
                )
            ),
            'troubleshooting' => array(
                'title' => __('Troubleshooting', 'wp-performance-plus'),
                'icon' => 'dashicons-sos',
                'order' => 7,
                'sections' => array(
                    'common_issues' => __('Common Issues', 'wp-performance-plus'),
                    'error_codes' => __('Error Codes', 'wp-performance-plus'),
                    'debugging' => __('Debugging Guide', 'wp-performance-plus'),
                    'support' => __('Getting Support', 'wp-performance-plus')
                )
            )
        );
    }
    
    /**
     * Initialize knowledge base articles
     */
    private function init_knowledge_base() {
        $this->knowledge_base = array(
            'cdn_setup_cloudflare' => array(
                'title' => __('Setting up Cloudflare CDN', 'wp-performance-plus'),
                'category' => 'cdn_management',
                'section' => 'provider_setup',
                'difficulty' => 'beginner',
                'estimated_time' => 10,
                'tags' => array('cloudflare', 'setup', 'cdn'),
                'content' => $this->get_cloudflare_setup_content(),
                'last_updated' => '2024-01-15',
                'author' => 'WP Performance Plus Team'
            ),
            'multi_cdn_configuration' => array(
                'title' => __('Configuring Multiple CDN Providers', 'wp-performance-plus'),
                'category' => 'cdn_management',
                'section' => 'multi_cdn',
                'difficulty' => 'advanced',
                'estimated_time' => 25,
                'tags' => array('multi-cdn', 'configuration', 'failover'),
                'content' => $this->get_multi_cdn_content(),
                'last_updated' => '2024-01-15',
                'author' => 'WP Performance Plus Team'
            ),
            'performance_alerts_setup' => array(
                'title' => __('Setting up Performance Alerts', 'wp-performance-plus'),
                'category' => 'alerts_notifications',
                'section' => 'alert_configuration',
                'difficulty' => 'intermediate',
                'estimated_time' => 15,
                'tags' => array('alerts', 'monitoring', 'notifications'),
                'content' => $this->get_alerts_setup_content(),
                'last_updated' => '2024-01-15',
                'author' => 'WP Performance Plus Team'
            ),
            'enterprise_scaling' => array(
                'title' => __('Enterprise Auto-scaling Configuration', 'wp-performance-plus'),
                'category' => 'enterprise_features',
                'section' => 'auto_scaling',
                'difficulty' => 'expert',
                'estimated_time' => 45,
                'tags' => array('enterprise', 'scaling', 'automation'),
                'content' => $this->get_enterprise_scaling_content(),
                'last_updated' => '2024-01-15',
                'author' => 'WP Performance Plus Team'
            ),
            'api_integration_guide' => array(
                'title' => __('API Integration Guide', 'wp-performance-plus'),
                'category' => 'api_reference',
                'section' => 'rest_api',
                'difficulty' => 'advanced',
                'estimated_time' => 30,
                'tags' => array('api', 'integration', 'development'),
                'content' => $this->get_api_integration_content(),
                'last_updated' => '2024-01-15',
                'author' => 'WP Performance Plus Team'
            )
        );
    }
    
    /**
     * Initialize API documentation
     */
    private function init_api_documentation() {
        $this->api_documentation = array(
            'rest_endpoints' => array(
                'title' => __('REST API Endpoints', 'wp-performance-plus'),
                'endpoints' => array(
                    '/wp-json/wp-performance-plus/v1/network/overview' => array(
                        'method' => 'GET',
                        'description' => __('Get network performance overview', 'wp-performance-plus'),
                        'parameters' => array(
                            'timeframe' => array(
                                'type' => 'string',
                                'required' => false,
                                'default' => '24hours',
                                'description' => __('Time period for data aggregation', 'wp-performance-plus')
                            )
                        ),
                        'response' => array(
                            'sites' => 'array',
                            'performance_summary' => 'object',
                            'resource_utilization' => 'object'
                        ),
                        'example' => $this->get_api_example('network_overview')
                    ),
                    '/wp-json/wp-performance-plus/v1/cdn/statistics' => array(
                        'method' => 'GET',
                        'description' => __('Get CDN usage statistics', 'wp-performance-plus'),
                        'parameters' => array(
                            'provider' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => __('Specific CDN provider', 'wp-performance-plus')
                            ),
                            'timeframe' => array(
                                'type' => 'string',
                                'required' => false,
                                'default' => '7days',
                                'description' => __('Time period for statistics', 'wp-performance-plus')
                            )
                        ),
                        'response' => array(
                            'requests_total' => 'integer',
                            'bandwidth_total' => 'integer',
                            'cache_hit_ratio' => 'float'
                        ),
                        'example' => $this->get_api_example('cdn_statistics')
                    )
                )
            ),
            'hooks_filters' => array(
                'title' => __('Hooks & Filters Reference', 'wp-performance-plus'),
                'actions' => array(
                    'wp_performance_plus_cdn_url_rewritten' => array(
                        'description' => __('Fired after a URL has been rewritten for CDN', 'wp-performance-plus'),
                        'parameters' => array(
                            '$original_url' => 'Original URL before rewriting',
                            '$cdn_url' => 'CDN URL after rewriting',
                            '$provider' => 'CDN provider instance'
                        ),
                        'example' => $this->get_hook_example('cdn_url_rewritten')
                    ),
                    'wp_performance_plus_alert_triggered' => array(
                        'description' => __('Fired when a performance alert is triggered', 'wp-performance-plus'),
                        'parameters' => array(
                            '$alert_id' => 'Alert identifier',
                            '$alert_data' => 'Alert configuration and metrics',
                            '$incident_id' => 'Generated incident ID'
                        ),
                        'example' => $this->get_hook_example('alert_triggered')
                    )
                ),
                'filters' => array(
                    'wp_performance_plus_cdn_excluded_urls' => array(
                        'description' => __('Filter URLs that should be excluded from CDN', 'wp-performance-plus'),
                        'parameters' => array(
                            '$excluded_urls' => 'Array of excluded URL patterns',
                            '$provider' => 'CDN provider instance'
                        ),
                        'example' => $this->get_filter_example('cdn_excluded_urls')
                    ),
                    'wp_performance_plus_alert_thresholds' => array(
                        'description' => __('Filter alert thresholds before evaluation', 'wp-performance-plus'),
                        'parameters' => array(
                            '$thresholds' => 'Array of alert thresholds',
                            '$alert_id' => 'Alert identifier'
                        ),
                        'example' => $this->get_filter_example('alert_thresholds')
                    )
                )
            ),
            'code_examples' => array(
                'title' => __('Code Examples', 'wp-performance-plus'),
                'examples' => array(
                    'custom_cdn_provider' => array(
                        'title' => __('Creating a Custom CDN Provider', 'wp-performance-plus'),
                        'description' => __('How to create a custom CDN provider class', 'wp-performance-plus'),
                        'code' => $this->get_code_example('custom_cdn_provider'),
                        'language' => 'php'
                    ),
                    'custom_alert_handler' => array(
                        'title' => __('Custom Alert Handler', 'wp-performance-plus'),
                        'description' => __('How to create a custom alert notification handler', 'wp-performance-plus'),
                        'code' => $this->get_code_example('custom_alert_handler'),
                        'language' => 'php'
                    ),
                    'api_integration' => array(
                        'title' => __('API Integration Example', 'wp-performance-plus'),
                        'description' => __('How to integrate with the REST API', 'wp-performance-plus'),
                        'code' => $this->get_code_example('api_integration'),
                        'language' => 'javascript'
                    )
                )
            )
        );
    }
    
    /**
     * Initialize interactive tutorials
     */
    private function init_tutorials() {
        $this->tutorials = array(
            'getting_started_tutorial' => array(
                'title' => __('Getting Started with WP Performance Plus', 'wp-performance-plus'),
                'description' => __('A step-by-step tutorial to set up your first CDN configuration', 'wp-performance-plus'),
                'difficulty' => 'beginner',
                'estimated_time' => 15,
                'steps' => array(
                    array(
                        'title' => __('Welcome to WP Performance Plus', 'wp-performance-plus'),
                        'content' => __('This tutorial will guide you through setting up your first CDN configuration.', 'wp-performance-plus'),
                        'action' => 'info',
                        'target' => null
                    ),
                    array(
                        'title' => __('Access the Settings Page', 'wp-performance-plus'),
                        'content' => __('Navigate to the WP Performance Plus settings page in your WordPress admin.', 'wp-performance-plus'),
                        'action' => 'highlight',
                        'target' => '#toplevel_page_wp-performance-plus'
                    ),
                    array(
                        'title' => __('Choose Your CDN Provider', 'wp-performance-plus'),
                        'content' => __('Select your preferred CDN provider from the dropdown menu.', 'wp-performance-plus'),
                        'action' => 'focus',
                        'target' => '#cdn_provider'
                    ),
                    array(
                        'title' => __('Configure API Credentials', 'wp-performance-plus'),
                        'content' => __('Enter your CDN provider API credentials for authentication.', 'wp-performance-plus'),
                        'action' => 'form_guide',
                        'target' => '.cdn-credentials-form'
                    ),
                    array(
                        'title' => __('Test Your Configuration', 'wp-performance-plus'),
                        'content' => __('Click the test button to verify your CDN configuration.', 'wp-performance-plus'),
                        'action' => 'button_prompt',
                        'target' => '#test-cdn-connection'
                    ),
                    array(
                        'title' => __('Congratulations!', 'wp-performance-plus'),
                        'content' => __('You have successfully configured your first CDN. Your website performance will now be optimized.', 'wp-performance-plus'),
                        'action' => 'completion',
                        'target' => null
                    )
                )
            ),
            'multi_cdn_setup_tutorial' => array(
                'title' => __('Setting up Multi-CDN Configuration', 'wp-performance-plus'),
                'description' => __('Learn how to configure multiple CDN providers for maximum reliability', 'wp-performance-plus'),
                'difficulty' => 'advanced',
                'estimated_time' => 25,
                'steps' => array(
                    array(
                        'title' => __('Multi-CDN Overview', 'wp-performance-plus'),
                        'content' => __('Multi-CDN setup provides redundancy and optimal performance by using multiple CDN providers.', 'wp-performance-plus'),
                        'action' => 'info',
                        'target' => null
                    ),
                    array(
                        'title' => __('Enable Multi-CDN Feature', 'wp-performance-plus'),
                        'content' => __('Navigate to the Advanced tab and enable the Multi-CDN feature.', 'wp-performance-plus'),
                        'action' => 'tab_switch',
                        'target' => '#tab-advanced'
                    ),
                    array(
                        'title' => __('Configure Primary Provider', 'wp-performance-plus'),
                        'content' => __('Set up your primary CDN provider with credentials and preferences.', 'wp-performance-plus'),
                        'action' => 'form_guide',
                        'target' => '.primary-cdn-config'
                    ),
                    array(
                        'title' => __('Add Secondary Providers', 'wp-performance-plus'),
                        'content' => __('Configure additional CDN providers for failover and load balancing.', 'wp-performance-plus'),
                        'action' => 'form_guide',
                        'target' => '.secondary-cdn-config'
                    ),
                    array(
                        'title' => __('Configure Failover Rules', 'wp-performance-plus'),
                        'content' => __('Set up automatic failover rules and health check intervals.', 'wp-performance-plus'),
                        'action' => 'settings_guide',
                        'target' => '.failover-settings'
                    ),
                    array(
                        'title' => __('Test Multi-CDN Setup', 'wp-performance-plus'),
                        'content' => __('Run comprehensive tests to verify your multi-CDN configuration.', 'wp-performance-plus'),
                        'action' => 'test_run',
                        'target' => '#run-multi-cdn-test'
                    )
                )
            )
        );
    }
    
    /**
     * Initialize troubleshooting guides
     */
    private function init_troubleshooting_guides() {
        $this->troubleshooting_guides = array(
            'cdn_connection_issues' => array(
                'title' => __('CDN Connection Issues', 'wp-performance-plus'),
                'category' => 'cdn_management',
                'severity' => 'high',
                'symptoms' => array(
                    __('CDN test fails with authentication errors', 'wp-performance-plus'),
                    __('Assets not loading through CDN', 'wp-performance-plus'),
                    __('Connection timeouts when testing CDN', 'wp-performance-plus')
                ),
                'solutions' => array(
                    array(
                        'step' => __('Verify API Credentials', 'wp-performance-plus'),
                        'description' => __('Double-check your CDN provider API credentials for accuracy.', 'wp-performance-plus'),
                        'code' => 'Navigate to Settings > CDN and verify all credentials are correctly entered.'
                    ),
                    array(
                        'step' => __('Check Network Connectivity', 'wp-performance-plus'),
                        'description' => __('Ensure your server can reach the CDN provider APIs.', 'wp-performance-plus'),
                        'code' => 'Test connectivity using: curl -I https://api.cloudflare.com/client/v4/user'
                    ),
                    array(
                        'step' => __('Review Firewall Settings', 'wp-performance-plus'),
                        'description' => __('Check if your server firewall is blocking CDN API requests.', 'wp-performance-plus'),
                        'code' => 'Whitelist CDN provider IP ranges in your firewall configuration.'
                    )
                ),
                'related_articles' => array('cdn_setup_cloudflare', 'multi_cdn_configuration')
            ),
            'performance_alerts_not_working' => array(
                'title' => __('Performance Alerts Not Working', 'wp-performance-plus'),
                'category' => 'alerts_notifications',
                'severity' => 'medium',
                'symptoms' => array(
                    __('No alerts received despite performance issues', 'wp-performance-plus'),
                    __('Email notifications not arriving', 'wp-performance-plus'),
                    __('Slack notifications failing to send', 'wp-performance-plus')
                ),
                'solutions' => array(
                    array(
                        'step' => __('Verify Alert Configuration', 'wp-performance-plus'),
                        'description' => __('Check that alerts are enabled and properly configured.', 'wp-performance-plus'),
                        'code' => 'Go to Monitoring > Alerts and verify alert settings.'
                    ),
                    array(
                        'step' => __('Test Notification Channels', 'wp-performance-plus'),
                        'description' => __('Send test notifications to verify channel configuration.', 'wp-performance-plus'),
                        'code' => 'Use the "Test Notification" button for each configured channel.'
                    ),
                    array(
                        'step' => __('Check Alert Thresholds', 'wp-performance-plus'),
                        'description' => __('Ensure alert thresholds are set to appropriate values.', 'wp-performance-plus'),
                        'code' => 'Review and adjust threshold values based on your site performance.'
                    )
                ),
                'related_articles' => array('performance_alerts_setup')
            )
        );
    }
    
    /**
     * Initialize contextual help
     */
    private function init_contextual_help() {
        $this->contextual_help = array(
            'wp-performance-plus' => array(
                'dashboard' => array(
                    'title' => __('Dashboard Help', 'wp-performance-plus'),
                    'content' => __('The dashboard provides an overview of your site performance and CDN status. Use the quick actions to perform common tasks.', 'wp-performance-plus'),
                    'tips' => array(
                        __('Green status indicators mean everything is working properly', 'wp-performance-plus'),
                        __('Click on any metric card to see detailed information', 'wp-performance-plus'),
                        __('Use quick actions for immediate optimization tasks', 'wp-performance-plus')
                    )
                ),
                'settings' => array(
                    'title' => __('Settings Help', 'wp-performance-plus'),
                    'content' => __('Configure your CDN providers, optimization settings, and performance thresholds here.', 'wp-performance-plus'),
                    'tips' => array(
                        __('Start with the Configuration Wizard for guided setup', 'wp-performance-plus'),
                        __('Test your configuration before saving changes', 'wp-performance-plus'),
                        __('Use the "Restore Defaults" option if something goes wrong', 'wp-performance-plus')
                    )
                )
            )
        );
    }
    
    /**
     * Display documentation page
     * @param string $section Documentation section
     * @param string $article Specific article
     */
    public function display_documentation($section = null, $article = null) {
        if ($section && $article) {
            $this->display_article($section, $article);
        } elseif ($section) {
            $this->display_section($section);
        } else {
            $this->display_documentation_home();
        }
    }
    
    /**
     * AJAX handler for searching documentation
     */
    public function ajax_search_documentation() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $category = isset($_POST['category']) ? sanitize_key($_POST['category']) : '';
        $difficulty = isset($_POST['difficulty']) ? sanitize_key($_POST['difficulty']) : '';
        
        if (empty($query)) {
            wp_send_json_error(__('Search query is required.', 'wp-performance-plus'));
        }
        
        $search_results = $this->search_documentation($query, $category, $difficulty);
        
        wp_send_json_success($search_results);
    }
    
    /**
     * Search documentation
     * @param string $query Search query
     * @param string $category Category filter
     * @param string $difficulty Difficulty filter
     * @return array Search results
     */
    private function search_documentation($query, $category = '', $difficulty = '') {
        $results = array();
        $query_lower = strtolower($query);
        
        // Search knowledge base articles
        foreach ($this->knowledge_base as $article_id => $article) {
            $score = 0;
            
            // Check title match
            if (strpos(strtolower($article['title']), $query_lower) !== false) {
                $score += 100;
            }
            
            // Check tag matches
            foreach ($article['tags'] as $tag) {
                if (strpos(strtolower($tag), $query_lower) !== false) {
                    $score += 50;
                }
            }
            
            // Check content match (first 500 characters)
            $content_excerpt = substr($article['content'], 0, 500);
            if (strpos(strtolower($content_excerpt), $query_lower) !== false) {
                $score += 25;
            }
            
            // Apply filters
            if ($category && $article['category'] !== $category) {
                continue;
            }
            
            if ($difficulty && $article['difficulty'] !== $difficulty) {
                continue;
            }
            
            if ($score > 0) {
                $results[] = array(
                    'id' => $article_id,
                    'title' => $article['title'],
                    'category' => $article['category'],
                    'section' => $article['section'],
                    'difficulty' => $article['difficulty'],
                    'estimated_time' => $article['estimated_time'],
                    'excerpt' => $this->generate_excerpt($article['content'], $query),
                    'score' => $score,
                    'type' => 'article'
                );
            }
        }
        
        // Search tutorials
        foreach ($this->tutorials as $tutorial_id => $tutorial) {
            $score = 0;
            
            if (strpos(strtolower($tutorial['title']), $query_lower) !== false) {
                $score += 80;
            }
            
            if (strpos(strtolower($tutorial['description']), $query_lower) !== false) {
                $score += 40;
            }
            
            if ($difficulty && $tutorial['difficulty'] !== $difficulty) {
                continue;
            }
            
            if ($score > 0) {
                $results[] = array(
                    'id' => $tutorial_id,
                    'title' => $tutorial['title'],
                    'description' => $tutorial['description'],
                    'difficulty' => $tutorial['difficulty'],
                    'estimated_time' => $tutorial['estimated_time'],
                    'steps_count' => count($tutorial['steps']),
                    'score' => $score,
                    'type' => 'tutorial'
                );
            }
        }
        
        // Sort by relevance score
        usort($results, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return array_slice($results, 0, 20); // Return top 20 results
    }
    
    /**
     * Generate content excerpt with highlighted search terms
     * @param string $content Full content
     * @param string $query Search query
     * @return string Excerpt with highlights
     */
    private function generate_excerpt($content, $query) {
        $query_lower = strtolower($query);
        $content_lower = strtolower($content);
        
        // Find first occurrence of search term
        $pos = strpos($content_lower, $query_lower);
        
        if ($pos === false) {
            return substr(strip_tags($content), 0, 150) . '...';
        }
        
        // Extract excerpt around the search term
        $start = max(0, $pos - 75);
        $excerpt = substr($content, $start, 150);
        
        // Highlight search terms
        $excerpt = str_ireplace($query, '<mark>' . $query . '</mark>', $excerpt);
        
        return $excerpt . '...';
    }
    
    /**
     * Get Cloudflare setup content
     * @return string Content
     */
    private function get_cloudflare_setup_content() {
        return '
        <h3>' . __('Setting up Cloudflare CDN', 'wp-performance-plus') . '</h3>
        
        <h4>' . __('Step 1: Get Your Cloudflare API Token', 'wp-performance-plus') . '</h4>
        <ol>
            <li>' . __('Log in to your Cloudflare dashboard', 'wp-performance-plus') . '</li>
            <li>' . __('Go to "My Profile" > "API Tokens"', 'wp-performance-plus') . '</li>
            <li>' . __('Click "Create Token" and use the "Zone:Read, Zone:Edit" template', 'wp-performance-plus') . '</li>
            <li>' . __('Copy the generated token', 'wp-performance-plus') . '</li>
        </ol>
        
        <h4>' . __('Step 2: Configure in WP Performance Plus', 'wp-performance-plus') . '</h4>
        <ol>
            <li>' . __('Navigate to WP Performance Plus > Settings > CDN', 'wp-performance-plus') . '</li>
            <li>' . __('Select "Cloudflare" as your CDN provider', 'wp-performance-plus') . '</li>
            <li>' . __('Enter your API token and email address', 'wp-performance-plus') . '</li>
            <li>' . __('Enter your Zone ID (found in Cloudflare dashboard)', 'wp-performance-plus') . '</li>
            <li>' . __('Click "Test Connection" to verify', 'wp-performance-plus') . '</li>
        </ol>
        
        <h4>' . __('Step 3: Optimization Settings', 'wp-performance-plus') . '</h4>
        <p>' . __('Configure which file types should be served through Cloudflare:', 'wp-performance-plus') . '</p>
        <ul>
            <li>' . __('Images: Recommended for best performance', 'wp-performance-plus') . '</li>
            <li>' . __('CSS/JS: Enable for faster loading', 'wp-performance-plus') . '</li>
            <li>' . __('Fonts: Helps with typography loading', 'wp-performance-plus') . '</li>
        </ul>
        ';
    }
    
    /**
     * Additional content generation methods would be implemented here for:
     * - get_multi_cdn_content()
     * - get_alerts_setup_content()
     * - get_enterprise_scaling_content()
     * - get_api_integration_content()
     * - get_api_example()
     * - get_hook_example()
     * - get_filter_example()
     * - get_code_example()
     * - display_article()
     * - display_section()
     * - display_documentation_home()
     * - add_help_tabs()
     * - start_tutorial()
     * - next_tutorial_step()
     * - track_documentation_usage()
     * - And many more documentation methods...
     */
} 