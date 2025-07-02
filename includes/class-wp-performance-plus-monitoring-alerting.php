<?php
/**
 * Monitoring & Alerting System
 * 
 * Comprehensive monitoring and alerting with real-time monitoring,
 * configurable alerts, multiple notification channels, escalation policies,
 * and detailed incident management.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Monitoring_Alerting {
    
    /**
     * Performance monitor instance
     * @var WP_Performance_Plus_Performance_Monitor
     */
    private $performance_monitor;
    
    /**
     * Analytics dashboard instance
     * @var WP_Performance_Plus_Analytics_Dashboard
     */
    private $analytics_dashboard;
    
    /**
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Alert configurations
     * @var array
     */
    private $alert_configs = array();
    
    /**
     * Notification channels
     * @var array
     */
    private $notification_channels = array();
    
    /**
     * Active incidents
     * @var array
     */
    private $active_incidents = array();
    
    /**
     * Escalation policies
     * @var array
     */
    private $escalation_policies = array();
    
    /**
     * Alert history
     * @var array
     */
    private $alert_history = array();
    
    /**
     * Monitoring metrics cache
     * @var array
     */
    private $metrics_cache = array();
    
    /**
     * Constructor
     */
    public function __construct($performance_monitor = null, $analytics_dashboard = null) {
        $this->performance_monitor = $performance_monitor;
        $this->analytics_dashboard = $analytics_dashboard;
        $this->settings = get_option('wp_performance_plus_settings', array());
        
        $this->init_alert_configurations();
        $this->init_notification_channels();
        $this->init_escalation_policies();
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Real-time monitoring hooks
        add_action('wp_performance_plus_realtime_monitoring', array($this, 'realtime_monitoring_check'));
        add_action('wp_performance_plus_check_performance_alerts', array($this, 'check_performance_alerts'));
        add_action('wp_performance_plus_process_alert_queue', array($this, 'process_alert_queue'));
        
        // Alert management hooks
        add_action('wp_performance_plus_trigger_alert', array($this, 'trigger_alert'), 10, 3);
        add_action('wp_performance_plus_resolve_alert', array($this, 'resolve_alert'), 10, 2);
        add_action('wp_performance_plus_escalate_alert', array($this, 'escalate_alert'), 10, 2);
        add_action('wp_performance_plus_acknowledge_alert', array($this, 'acknowledge_alert'), 10, 2);
        
        // Notification hooks
        add_action('wp_performance_plus_send_notification', array($this, 'send_notification'), 10, 4);
        add_action('wp_performance_plus_send_email_alert', array($this, 'send_email_alert'), 10, 2);
        add_action('wp_performance_plus_send_slack_alert', array($this, 'send_slack_alert'), 10, 2);
        add_action('wp_performance_plus_send_webhook_alert', array($this, 'send_webhook_alert'), 10, 2);
        add_action('wp_performance_plus_send_sms_alert', array($this, 'send_sms_alert'), 10, 2);
        
        // AJAX handlers for alert management
        add_action('wp_ajax_wp_performance_plus_configure_alerts', array($this, 'ajax_configure_alerts'));
        add_action('wp_ajax_wp_performance_plus_test_notification_channel', array($this, 'ajax_test_notification_channel'));
        add_action('wp_ajax_wp_performance_plus_get_active_alerts', array($this, 'ajax_get_active_alerts'));
        add_action('wp_ajax_wp_performance_plus_acknowledge_incident', array($this, 'ajax_acknowledge_incident'));
        add_action('wp_ajax_wp_performance_plus_resolve_incident', array($this, 'ajax_resolve_incident'));
        add_action('wp_ajax_wp_performance_plus_get_alert_history', array($this, 'ajax_get_alert_history'));
        add_action('wp_ajax_wp_performance_plus_update_escalation_policy', array($this, 'ajax_update_escalation_policy'));
        
        // Scheduled monitoring tasks
        add_action('wp_performance_plus_minute_monitoring_check', array($this, 'minute_monitoring_check'));
        add_action('wp_performance_plus_hourly_monitoring_summary', array($this, 'hourly_monitoring_summary'));
        add_action('wp_performance_plus_daily_monitoring_report', array($this, 'daily_monitoring_report'));
        
        // Incident management hooks
        add_action('wp_performance_plus_create_incident', array($this, 'create_incident'), 10, 3);
        add_action('wp_performance_plus_update_incident', array($this, 'update_incident'), 10, 3);
        add_action('wp_performance_plus_close_incident', array($this, 'close_incident'), 10, 2);
        
        // Health check hooks
        add_action('wp_performance_plus_health_check', array($this, 'comprehensive_health_check'));
        add_action('wp_performance_plus_system_status_check', array($this, 'system_status_check'));
    }
    
    /**
     * Initialize alert configurations
     */
    private function init_alert_configurations() {
        $this->alert_configs = array_merge(array(
            'performance_degradation' => array(
                'name' => __('Performance Degradation', 'wp-performance-plus'),
                'enabled' => true,
                'priority' => 'high',
                'conditions' => array(
                    'page_load_time' => array('threshold' => 5.0, 'operator' => '>'),
                    'response_time' => array('threshold' => 3.0, 'operator' => '>'),
                    'error_rate' => array('threshold' => 5, 'operator' => '>')
                ),
                'evaluation_window' => 300, // 5 minutes
                'notification_channels' => array('email', 'slack'),
                'escalation_policy' => 'standard',
                'cooldown_period' => 900 // 15 minutes
            ),
            'cdn_issues' => array(
                'name' => __('CDN Issues', 'wp-performance-plus'),
                'enabled' => true,
                'priority' => 'medium',
                'conditions' => array(
                    'cache_hit_ratio' => array('threshold' => 70, 'operator' => '<'),
                    'cdn_response_time' => array('threshold' => 2.0, 'operator' => '>'),
                    'cdn_errors' => array('threshold' => 10, 'operator' => '>')
                ),
                'evaluation_window' => 600, // 10 minutes
                'notification_channels' => array('email'),
                'escalation_policy' => 'standard',
                'cooldown_period' => 1800 // 30 minutes
            ),
            'resource_exhaustion' => array(
                'name' => __('Resource Exhaustion', 'wp-performance-plus'),
                'enabled' => true,
                'priority' => 'critical',
                'conditions' => array(
                    'memory_usage' => array('threshold' => 90, 'operator' => '>'),
                    'cpu_usage' => array('threshold' => 85, 'operator' => '>'),
                    'disk_usage' => array('threshold' => 95, 'operator' => '>')
                ),
                'evaluation_window' => 180, // 3 minutes
                'notification_channels' => array('email', 'slack', 'sms'),
                'escalation_policy' => 'critical',
                'cooldown_period' => 300 // 5 minutes
            ),
            'security_threats' => array(
                'name' => __('Security Threats', 'wp-performance-plus'),
                'enabled' => true,
                'priority' => 'critical',
                'conditions' => array(
                    'suspicious_traffic' => array('threshold' => 100, 'operator' => '>'),
                    'failed_login_attempts' => array('threshold' => 50, 'operator' => '>'),
                    'malware_detected' => array('threshold' => 1, 'operator' => '>=')
                ),
                'evaluation_window' => 60, // 1 minute
                'notification_channels' => array('email', 'slack', 'sms', 'webhook'),
                'escalation_policy' => 'immediate',
                'cooldown_period' => 0 // No cooldown for security
            ),
            'uptime_issues' => array(
                'name' => __('Uptime Issues', 'wp-performance-plus'),
                'enabled' => true,
                'priority' => 'critical',
                'conditions' => array(
                    'site_availability' => array('threshold' => 99, 'operator' => '<'),
                    'http_errors' => array('threshold' => 20, 'operator' => '>'),
                    'database_errors' => array('threshold' => 5, 'operator' => '>')
                ),
                'evaluation_window' => 120, // 2 minutes
                'notification_channels' => array('email', 'slack', 'sms'),
                'escalation_policy' => 'immediate',
                'cooldown_period' => 300 // 5 minutes
            )
        ), get_option('wp_performance_plus_custom_alert_configs', array()));
    }
    
    /**
     * Initialize notification channels
     */
    private function init_notification_channels() {
        $this->notification_channels = array(
            'email' => array(
                'name' => __('Email', 'wp-performance-plus'),
                'enabled' => true,
                'config' => array(
                    'recipients' => get_option('wp_performance_plus_alert_emails', array(get_option('admin_email'))),
                    'subject_prefix' => '[WP Performance Plus]',
                    'format' => 'html'
                )
            ),
            'slack' => array(
                'name' => __('Slack', 'wp-performance-plus'),
                'enabled' => !empty(get_option('wp_performance_plus_slack_webhook_url')),
                'config' => array(
                    'webhook_url' => get_option('wp_performance_plus_slack_webhook_url', ''),
                    'channel' => get_option('wp_performance_plus_slack_channel', '#alerts'),
                    'username' => 'WP Performance Plus',
                    'icon_emoji' => ':warning:'
                )
            ),
            'webhook' => array(
                'name' => __('Webhook', 'wp-performance-plus'),
                'enabled' => !empty(get_option('wp_performance_plus_webhook_url')),
                'config' => array(
                    'url' => get_option('wp_performance_plus_webhook_url', ''),
                    'method' => 'POST',
                    'headers' => get_option('wp_performance_plus_webhook_headers', array()),
                    'authentication' => get_option('wp_performance_plus_webhook_auth', array())
                )
            ),
            'sms' => array(
                'name' => __('SMS', 'wp-performance-plus'),
                'enabled' => !empty(get_option('wp_performance_plus_sms_provider')),
                'config' => array(
                    'provider' => get_option('wp_performance_plus_sms_provider', ''), // twilio, nexmo, etc.
                    'recipients' => get_option('wp_performance_plus_sms_recipients', array()),
                    'api_key' => get_option('wp_performance_plus_sms_api_key', ''),
                    'api_secret' => get_option('wp_performance_plus_sms_api_secret', '')
                )
            ),
            'discord' => array(
                'name' => __('Discord', 'wp-performance-plus'),
                'enabled' => !empty(get_option('wp_performance_plus_discord_webhook_url')),
                'config' => array(
                    'webhook_url' => get_option('wp_performance_plus_discord_webhook_url', ''),
                    'username' => 'WP Performance Plus',
                    'avatar_url' => ''
                )
            )
        );
    }
    
    /**
     * Initialize escalation policies
     */
    private function init_escalation_policies() {
        $this->escalation_policies = array(
            'standard' => array(
                'name' => __('Standard Escalation', 'wp-performance-plus'),
                'steps' => array(
                    array(
                        'delay' => 0, // Immediate
                        'channels' => array('email'),
                        'recipients' => 'primary'
                    ),
                    array(
                        'delay' => 900, // 15 minutes
                        'channels' => array('email', 'slack'),
                        'recipients' => 'secondary'
                    ),
                    array(
                        'delay' => 1800, // 30 minutes
                        'channels' => array('email', 'slack', 'sms'),
                        'recipients' => 'management'
                    )
                )
            ),
            'critical' => array(
                'name' => __('Critical Escalation', 'wp-performance-plus'),
                'steps' => array(
                    array(
                        'delay' => 0, // Immediate
                        'channels' => array('email', 'slack', 'sms'),
                        'recipients' => 'primary'
                    ),
                    array(
                        'delay' => 300, // 5 minutes
                        'channels' => array('email', 'slack', 'sms', 'webhook'),
                        'recipients' => 'secondary'
                    ),
                    array(
                        'delay' => 600, // 10 minutes
                        'channels' => array('email', 'slack', 'sms', 'webhook'),
                        'recipients' => 'management'
                    )
                )
            ),
            'immediate' => array(
                'name' => __('Immediate Escalation', 'wp-performance-plus'),
                'steps' => array(
                    array(
                        'delay' => 0, // Immediate
                        'channels' => array('email', 'slack', 'sms', 'webhook'),
                        'recipients' => 'all'
                    )
                )
            )
        );
    }
    
    /**
     * Real-time monitoring check
     */
    public function realtime_monitoring_check() {
        $current_metrics = $this->collect_current_metrics();
        
        // Store metrics in cache
        $this->metrics_cache = $current_metrics;
        
        // Check all configured alerts
        foreach ($this->alert_configs as $alert_id => $alert_config) {
            if (!$alert_config['enabled']) {
                continue;
            }
            
            $evaluation_result = $this->evaluate_alert_conditions($alert_id, $alert_config, $current_metrics);
            
            if ($evaluation_result['triggered']) {
                $this->handle_alert_trigger($alert_id, $alert_config, $evaluation_result);
            }
        }
        
        // Update active incidents
        $this->update_active_incidents($current_metrics);
        
        WP_Performance_Plus_Logger::debug('Real-time monitoring check completed', array(
            'metrics_collected' => count($current_metrics),
            'active_incidents' => count($this->active_incidents)
        ));
    }
    
    /**
     * Collect current performance metrics
     * @return array Current metrics
     */
    private function collect_current_metrics() {
        $metrics = array(
            'timestamp' => current_time('mysql'),
            'page_load_time' => $this->get_current_page_load_time(),
            'response_time' => $this->get_current_response_time(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'database_queries' => get_num_queries(),
            'error_rate' => $this->calculate_current_error_rate(),
            'uptime' => $this->get_current_uptime(),
            'cpu_usage' => $this->get_cpu_usage(),
            'disk_usage' => $this->get_disk_usage()
        );
        
        // Add CDN metrics if available
        if ($this->performance_monitor) {
            $cdn_metrics = $this->get_cdn_metrics();
            $metrics = array_merge($metrics, $cdn_metrics);
        }
        
        // Add custom metrics
        $custom_metrics = $this->get_custom_metrics();
        $metrics = array_merge($metrics, $custom_metrics);
        
        return $metrics;
    }
    
    /**
     * Evaluate alert conditions
     * @param string $alert_id Alert identifier
     * @param array $alert_config Alert configuration
     * @param array $current_metrics Current metrics
     * @return array Evaluation result
     */
    private function evaluate_alert_conditions($alert_id, $alert_config, $current_metrics) {
        $result = array(
            'triggered' => false,
            'conditions_met' => array(),
            'severity' => $alert_config['priority'],
            'evaluation_time' => current_time('mysql')
        );
        
        $conditions_met = 0;
        $total_conditions = count($alert_config['conditions']);
        
        foreach ($alert_config['conditions'] as $metric => $condition) {
            $current_value = $current_metrics[$metric] ?? 0;
            $threshold = $condition['threshold'];
            $operator = $condition['operator'];
            
            $condition_met = $this->evaluate_condition($current_value, $threshold, $operator);
            
            if ($condition_met) {
                $conditions_met++;
                $result['conditions_met'][] = array(
                    'metric' => $metric,
                    'current_value' => $current_value,
                    'threshold' => $threshold,
                    'operator' => $operator
                );
            }
        }
        
        // Alert triggers if ALL conditions are met
        if ($conditions_met === $total_conditions) {
            $result['triggered'] = true;
        }
        
        return $result;
    }
    
    /**
     * Handle alert trigger
     * @param string $alert_id Alert identifier
     * @param array $alert_config Alert configuration
     * @param array $evaluation_result Evaluation result
     */
    private function handle_alert_trigger($alert_id, $alert_config, $evaluation_result) {
        // Check cooldown period
        if ($this->is_in_cooldown_period($alert_id, $alert_config['cooldown_period'])) {
            return;
        }
        
        // Create or update incident
        $incident_id = $this->create_or_update_incident($alert_id, $alert_config, $evaluation_result);
        
        // Send notifications
        $this->send_alert_notifications($incident_id, $alert_config, $evaluation_result);
        
        // Schedule escalation if needed
        $this->schedule_escalation($incident_id, $alert_config);
        
        // Log alert
        $this->log_alert($alert_id, $incident_id, $evaluation_result);
        
        WP_Performance_Plus_Logger::warning("Alert triggered: {$alert_config['name']}", array(
            'alert_id' => $alert_id,
            'incident_id' => $incident_id,
            'conditions_met' => $evaluation_result['conditions_met']
        ));
    }
    
    /**
     * Send alert notifications
     * @param string $incident_id Incident ID
     * @param array $alert_config Alert configuration
     * @param array $evaluation_result Evaluation result
     */
    private function send_alert_notifications($incident_id, $alert_config, $evaluation_result) {
        $notification_data = array(
            'incident_id' => $incident_id,
            'alert_name' => $alert_config['name'],
            'priority' => $alert_config['priority'],
            'conditions_met' => $evaluation_result['conditions_met'],
            'timestamp' => current_time('mysql'),
            'site_url' => home_url(),
            'dashboard_url' => admin_url('admin.php?page=wp-performance-plus')
        );
        
        foreach ($alert_config['notification_channels'] as $channel) {
            if ($this->is_notification_channel_enabled($channel)) {
                $this->send_notification_to_channel($channel, $notification_data);
            }
        }
    }
    
    /**
     * Send notification to specific channel
     * @param string $channel Channel name
     * @param array $data Notification data
     */
    private function send_notification_to_channel($channel, $data) {
        try {
            switch ($channel) {
                case 'email':
                    $this->send_email_notification($data);
                    break;
                    
                case 'slack':
                    $this->send_slack_notification($data);
                    break;
                    
                case 'webhook':
                    $this->send_webhook_notification($data);
                    break;
                    
                case 'sms':
                    $this->send_sms_notification($data);
                    break;
                    
                case 'discord':
                    $this->send_discord_notification($data);
                    break;
                    
                default:
                    WP_Performance_Plus_Logger::warning("Unknown notification channel: {$channel}");
            }
        } catch (Exception $e) {
            WP_Performance_Plus_Logger::error("Failed to send notification via {$channel}", array(
                'error' => $e->getMessage(),
                'data' => $data
            ));
        }
    }
    
    /**
     * Send email notification
     * @param array $data Notification data
     */
    private function send_email_notification($data) {
        $channel_config = $this->notification_channels['email']['config'];
        
        $subject = sprintf(
            '%s %s Alert - %s',
            $channel_config['subject_prefix'],
            ucfirst($data['priority']),
            $data['alert_name']
        );
        
        $message = $this->generate_email_message($data);
        
        $headers = array();
        if ($channel_config['format'] === 'html') {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }
        
        foreach ($channel_config['recipients'] as $recipient) {
            wp_mail($recipient, $subject, $message, $headers);
        }
    }
    
    /**
     * Send Slack notification
     * @param array $data Notification data
     */
    private function send_slack_notification($data) {
        $channel_config = $this->notification_channels['slack']['config'];
        
        if (empty($channel_config['webhook_url'])) {
            throw new Exception('Slack webhook URL not configured');
        }
        
        $payload = array(
            'channel' => $channel_config['channel'],
            'username' => $channel_config['username'],
            'icon_emoji' => $channel_config['icon_emoji'],
            'attachments' => array(
                array(
                    'color' => $this->get_alert_color($data['priority']),
                    'title' => sprintf('%s Alert - %s', ucfirst($data['priority']), $data['alert_name']),
                    'fields' => array(
                        array(
                            'title' => 'Site',
                            'value' => $data['site_url'],
                            'short' => true
                        ),
                        array(
                            'title' => 'Time',
                            'value' => $data['timestamp'],
                            'short' => true
                        ),
                        array(
                            'title' => 'Conditions Met',
                            'value' => $this->format_conditions_for_slack($data['conditions_met']),
                            'short' => false
                        )
                    ),
                    'actions' => array(
                        array(
                            'type' => 'button',
                            'text' => 'View Dashboard',
                            'url' => $data['dashboard_url']
                        )
                    )
                )
            )
        );
        
        $response = wp_remote_post($channel_config['webhook_url'], array(
            'body' => wp_json_encode($payload),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
    }
    
    /**
     * AJAX handler for configuring alerts
     */
    public function ajax_configure_alerts() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $alert_configs = isset($_POST['alert_configs']) ? $_POST['alert_configs'] : array();
        
        // Validate and sanitize alert configurations
        $validated_configs = $this->validate_alert_configurations($alert_configs);
        
        if (empty($validated_configs['errors'])) {
            update_option('wp_performance_plus_custom_alert_configs', $validated_configs['configs']);
            $this->alert_configs = array_merge($this->alert_configs, $validated_configs['configs']);
            
            wp_send_json_success(array(
                'message' => __('Alert configurations saved successfully.', 'wp-performance-plus'),
                'configs' => $validated_configs['configs']
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid alert configurations.', 'wp-performance-plus'),
                'errors' => $validated_configs['errors']
            ));
        }
    }
    
    /**
     * AJAX handler for testing notification channels
     */
    public function ajax_test_notification_channel() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $channel = isset($_POST['channel']) ? sanitize_key($_POST['channel']) : '';
        
        if (empty($channel) || !isset($this->notification_channels[$channel])) {
            wp_send_json_error(__('Invalid notification channel.', 'wp-performance-plus'));
        }
        
        try {
            $test_data = array(
                'incident_id' => 'test-' . time(),
                'alert_name' => 'Test Alert',
                'priority' => 'warning',
                'conditions_met' => array(
                    array(
                        'metric' => 'page_load_time',
                        'current_value' => 4.5,
                        'threshold' => 3.0,
                        'operator' => '>'
                    )
                ),
                'timestamp' => current_time('mysql'),
                'site_url' => home_url(),
                'dashboard_url' => admin_url('admin.php?page=wp-performance-plus')
            );
            
            $this->send_notification_to_channel($channel, $test_data);
            
            wp_send_json_success(array(
                'message' => sprintf(__('Test notification sent successfully via %s.', 'wp-performance-plus'), $channel)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(__('Failed to send test notification: %s', 'wp-performance-plus'), $e->getMessage())
            ));
        }
    }
    
    /**
     * Additional methods would be implemented here for:
     * - send_webhook_notification()
     * - send_sms_notification()
     * - send_discord_notification()
     * - generate_email_message()
     * - evaluate_condition()
     * - is_in_cooldown_period()
     * - create_or_update_incident()
     * - schedule_escalation()
     * - log_alert()
     * - get_current_page_load_time()
     * - get_current_response_time()
     * - calculate_current_error_rate()
     * - get_current_uptime()
     * - get_cpu_usage()
     * - get_disk_usage()
     * - get_cdn_metrics()
     * - get_custom_metrics()
     * - validate_alert_configurations()
     * - format_conditions_for_slack()
     * - get_alert_color()
     * - is_notification_channel_enabled()
     * - update_active_incidents()
     * - And many more monitoring and alerting methods...
     */
} 