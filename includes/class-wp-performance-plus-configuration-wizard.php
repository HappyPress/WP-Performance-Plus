<?php
/**
 * Configuration Wizard & Setup System
 * 
 * Comprehensive setup wizard for CDN API credentials, threshold configuration,
 * guided setup process, validation, and secure credential management.
 * 
 * @package    WP_Performance_Plus
 * @subpackage WP_Performance_Plus/includes
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Performance_Plus_Configuration_Wizard {
    
    /**
     * CDN manager instance
     * @var WP_Performance_Plus_CDN_Manager
     */
    private $cdn_manager;
    
    /**
     * Testing framework instance
     * @var WP_Performance_Plus_Testing_Framework
     */
    private $testing_framework;
    
    /**
     * Plugin settings
     * @var array
     */
    private $settings;
    
    /**
     * Configuration steps
     * @var array
     */
    private $configuration_steps = array();
    
    /**
     * Wizard progress
     * @var array
     */
    private $wizard_progress = array();
    
    /**
     * Default configurations
     * @var array
     */
    private $default_configurations = array();
    
    /**
     * Security salt for encryption
     * @var string
     */
    private $encryption_salt;
    
    /**
     * Constructor
     */
    public function __construct($cdn_manager = null, $testing_framework = null) {
        $this->cdn_manager = $cdn_manager;
        $this->testing_framework = $testing_framework;
        $this->settings = get_option('wp_performance_plus_settings', array());
        $this->encryption_salt = defined('WP_PERFORMANCE_PLUS_SALT') ? WP_PERFORMANCE_PLUS_SALT : wp_salt('auth');
        
        $this->init_configuration_steps();
        $this->init_default_configurations();
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Configuration wizard hooks
        add_action('wp_performance_plus_run_setup_wizard', array($this, 'run_setup_wizard'));
        add_action('wp_performance_plus_validate_configuration', array($this, 'validate_configuration'));
        add_action('wp_performance_plus_save_configuration', array($this, 'save_configuration'));
        
        // AJAX handlers for wizard interface
        add_action('wp_ajax_wp_performance_plus_start_wizard', array($this, 'ajax_start_wizard'));
        add_action('wp_ajax_wp_performance_plus_wizard_step', array($this, 'ajax_wizard_step'));
        add_action('wp_ajax_wp_performance_plus_validate_credentials', array($this, 'ajax_validate_credentials'));
        add_action('wp_ajax_wp_performance_plus_test_configuration', array($this, 'ajax_test_configuration'));
        add_action('wp_ajax_wp_performance_plus_save_wizard_progress', array($this, 'ajax_save_wizard_progress'));
        add_action('wp_ajax_wp_performance_plus_complete_wizard', array($this, 'ajax_complete_wizard'));
        add_action('wp_ajax_wp_performance_plus_import_configuration', array($this, 'ajax_import_configuration'));
        add_action('wp_ajax_wp_performance_plus_export_configuration', array($this, 'ajax_export_configuration'));
        
        // Configuration validation hooks
        add_action('wp_performance_plus_validate_cdn_credentials', array($this, 'validate_cdn_credentials'));
        add_action('wp_performance_plus_validate_performance_thresholds', array($this, 'validate_performance_thresholds'));
        
        // Security and encryption hooks
        add_filter('wp_performance_plus_encrypt_credential', array($this, 'encrypt_credential'), 10, 2);
        add_filter('wp_performance_plus_decrypt_credential', array($this, 'decrypt_credential'), 10, 2);
    }
    
    /**
     * Initialize configuration steps
     */
    private function init_configuration_steps() {
        $this->configuration_steps = array(
            'welcome' => array(
                'title' => __('Welcome to WP Performance Plus', 'wp-performance-plus'),
                'description' => __('This wizard will guide you through the setup process.', 'wp-performance-plus'),
                'type' => 'info',
                'required' => true,
                'estimated_time' => 1
            ),
            'environment_detection' => array(
                'title' => __('Environment Detection', 'wp-performance-plus'),
                'description' => __('Detecting your WordPress environment and configuration.', 'wp-performance-plus'),
                'type' => 'auto',
                'required' => true,
                'estimated_time' => 2
            ),
            'cdn_provider_selection' => array(
                'title' => __('CDN Provider Selection', 'wp-performance-plus'),
                'description' => __('Choose your preferred CDN providers.', 'wp-performance-plus'),
                'type' => 'selection',
                'required' => true,
                'estimated_time' => 3
            ),
            'credential_configuration' => array(
                'title' => __('API Credentials Configuration', 'wp-performance-plus'),
                'description' => __('Configure API credentials for your selected CDN providers.', 'wp-performance-plus'),
                'type' => 'credentials',
                'required' => true,
                'estimated_time' => 5
            ),
            'performance_thresholds' => array(
                'title' => __('Performance Thresholds', 'wp-performance-plus'),
                'description' => __('Set performance monitoring thresholds and alerts.', 'wp-performance-plus'),
                'type' => 'configuration',
                'required' => false,
                'estimated_time' => 3
            ),
            'optimization_settings' => array(
                'title' => __('Optimization Settings', 'wp-performance-plus'),
                'description' => __('Configure optimization features and caching rules.', 'wp-performance-plus'),
                'type' => 'configuration',
                'required' => false,
                'estimated_time' => 4
            ),
            'testing_validation' => array(
                'title' => __('Configuration Testing', 'wp-performance-plus'),
                'description' => __('Testing your configuration and validating setup.', 'wp-performance-plus'),
                'type' => 'testing',
                'required' => true,
                'estimated_time' => 5
            ),
            'completion' => array(
                'title' => __('Setup Complete', 'wp-performance-plus'),
                'description' => __('Your WP Performance Plus setup is complete!', 'wp-performance-plus'),
                'type' => 'completion',
                'required' => true,
                'estimated_time' => 1
            )
        );
    }
    
    /**
     * Initialize default configurations
     */
    private function init_default_configurations() {
        $this->default_configurations = array(
            'performance_thresholds' => array(
                'page_load_time' => array(
                    'warning' => 3.0,
                    'critical' => 5.0
                ),
                'first_contentful_paint' => array(
                    'warning' => 2.5,
                    'critical' => 4.0
                ),
                'largest_contentful_paint' => array(
                    'warning' => 2.5,
                    'critical' => 4.0
                ),
                'cumulative_layout_shift' => array(
                    'warning' => 0.1,
                    'critical' => 0.25
                ),
                'first_input_delay' => array(
                    'warning' => 100,
                    'critical' => 300
                ),
                'cdn_cache_hit_ratio' => array(
                    'warning' => 80,
                    'critical' => 70
                )
            ),
            'optimization_settings' => array(
                'image_optimization' => array(
                    'enable_webp' => true,
                    'image_quality' => 85,
                    'auto_resize' => true
                ),
                'caching' => array(
                    'enable_browser_cache' => true,
                    'cache_ttl' => 86400,
                    'enable_gzip' => true
                ),
                'minification' => array(
                    'minify_html' => true,
                    'minify_css' => true,
                    'minify_js' => true
                )
            ),
            'cdn_settings' => array(
                'file_types' => array(
                    'images' => true,
                    'css' => true,
                    'js' => true,
                    'fonts' => true
                ),
                'exclusions' => array(
                    'admin_urls' => true,
                    'login_urls' => true,
                    'dynamic_content' => true
                )
            )
        );
    }
    
    /**
     * Run setup wizard
     * @param array $config Initial configuration
     * @return array Wizard session data
     */
    public function run_setup_wizard($config = array()) {
        $session_id = uniqid('wizard_');
        
        $wizard_session = array(
            'session_id' => $session_id,
            'start_time' => current_time('mysql'),
            'current_step' => 'welcome',
            'completed_steps' => array(),
            'configuration' => array_merge($this->default_configurations, $config),
            'validation_results' => array(),
            'test_results' => array()
        );
        
        // Store wizard session
        set_transient("wp_performance_plus_wizard_{$session_id}", $wizard_session, 2 * HOUR_IN_SECONDS);
        
        WP_Performance_Plus_Logger::info('Setup wizard started', array('session_id' => $session_id));
        
        return $wizard_session;
    }
    
    /**
     * Process wizard step
     * @param string $session_id Wizard session ID
     * @param string $step Current step
     * @param array $data Step data
     * @return array Step result
     */
    public function process_wizard_step($session_id, $step, $data = array()) {
        $wizard_session = get_transient("wp_performance_plus_wizard_{$session_id}");
        
        if (!$wizard_session) {
            throw new Exception(__('Wizard session expired. Please start over.', 'wp-performance-plus'));
        }
        
        $step_result = array(
            'step' => $step,
            'status' => 'completed',
            'data' => array(),
            'errors' => array(),
            'next_step' => $this->get_next_step($step)
        );
        
        try {
            switch ($step) {
                case 'welcome':
                    $step_result = array_merge($step_result, $this->process_welcome_step($data));
                    break;
                    
                case 'environment_detection':
                    $step_result = array_merge($step_result, $this->process_environment_detection($data));
                    break;
                    
                case 'cdn_provider_selection':
                    $step_result = array_merge($step_result, $this->process_cdn_provider_selection($data));
                    break;
                    
                case 'credential_configuration':
                    $step_result = array_merge($step_result, $this->process_credential_configuration($data));
                    break;
                    
                case 'performance_thresholds':
                    $step_result = array_merge($step_result, $this->process_performance_thresholds($data));
                    break;
                    
                case 'optimization_settings':
                    $step_result = array_merge($step_result, $this->process_optimization_settings($data));
                    break;
                    
                case 'testing_validation':
                    $step_result = array_merge($step_result, $this->process_testing_validation($data));
                    break;
                    
                case 'completion':
                    $step_result = array_merge($step_result, $this->process_completion($data));
                    break;
                    
                default:
                    throw new Exception(__('Unknown wizard step.', 'wp-performance-plus'));
            }
            
        } catch (Exception $e) {
            $step_result['status'] = 'failed';
            $step_result['errors'][] = $e->getMessage();
            
            WP_Performance_Plus_Logger::error("Wizard step failed: {$step}", array(
                'error' => $e->getMessage(),
                'session_id' => $session_id
            ));
        }
        
        // Update wizard session
        $wizard_session['current_step'] = $step_result['next_step'];
        $wizard_session['completed_steps'][] = $step;
        $wizard_session['configuration'] = array_merge($wizard_session['configuration'], $step_result['data']);
        
        set_transient("wp_performance_plus_wizard_{$session_id}", $wizard_session, 2 * HOUR_IN_SECONDS);
        
        return $step_result;
    }
    
    /**
     * Process credential configuration step
     * @param array $data Step data
     * @return array Step result
     */
    private function process_credential_configuration($data) {
        $result = array(
            'data' => array(),
            'errors' => array()
        );
        
        if (!isset($data['credentials']) || !is_array($data['credentials'])) {
            $result['errors'][] = __('No credentials provided.', 'wp-performance-plus');
            return $result;
        }
        
        $validated_credentials = array();
        
        foreach ($data['credentials'] as $provider => $credentials) {
            try {
                // Validate credential format
                $validation_result = $this->validate_credential_format($provider, $credentials);
                
                if (!$validation_result['valid']) {
                    $result['errors'][] = sprintf(
                        __('Invalid credentials for %s: %s', 'wp-performance-plus'),
                        $provider,
                        implode(', ', $validation_result['errors'])
                    );
                    continue;
                }
                
                // Test credential functionality
                $test_result = $this->test_credentials($provider, $credentials);
                
                if (!$test_result['success']) {
                    $result['errors'][] = sprintf(
                        __('Credential test failed for %s: %s', 'wp-performance-plus'),
                        $provider,
                        $test_result['error']
                    );
                    continue;
                }
                
                // Encrypt and store credentials
                $encrypted_credentials = $this->encrypt_credentials($credentials);
                $validated_credentials[$provider] = $encrypted_credentials;
                
            } catch (Exception $e) {
                $result['errors'][] = sprintf(
                    __('Error validating credentials for %s: %s', 'wp-performance-plus'),
                    $provider,
                    $e->getMessage()
                );
            }
        }
        
        $result['data']['cdn_credentials'] = $validated_credentials;
        
        if (empty($validated_credentials)) {
            $result['errors'][] = __('No valid credentials were provided. At least one CDN provider must be configured.', 'wp-performance-plus');
        }
        
        return $result;
    }
    
    /**
     * Validate credential format
     * @param string $provider Provider name
     * @param array $credentials Credentials data
     * @return array Validation result
     */
    private function validate_credential_format($provider, $credentials) {
        $result = array(
            'valid' => true,
            'errors' => array()
        );
        
        $required_fields = $this->get_required_credential_fields($provider);
        
        foreach ($required_fields as $field) {
            if (empty($credentials[$field])) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(__('%s is required', 'wp-performance-plus'), $field);
            }
        }
        
        // Provider-specific validation
        switch ($provider) {
            case 'cloudflare':
                if (!empty($credentials['api_token']) && !$this->is_valid_cloudflare_token($credentials['api_token'])) {
                    $result['valid'] = false;
                    $result['errors'][] = __('Invalid Cloudflare API token format', 'wp-performance-plus');
                }
                break;
                
            case 'keycdn':
                if (!empty($credentials['api_key']) && !$this->is_valid_keycdn_key($credentials['api_key'])) {
                    $result['valid'] = false;
                    $result['errors'][] = __('Invalid KeyCDN API key format', 'wp-performance-plus');
                }
                break;
                
            case 'bunnycdn':
                if (!empty($credentials['access_key']) && !$this->is_valid_bunnycdn_key($credentials['access_key'])) {
                    $result['valid'] = false;
                    $result['errors'][] = __('Invalid BunnyCDN access key format', 'wp-performance-plus');
                }
                break;
                
            case 'cloudfront':
                if (!empty($credentials['access_key_id']) && !$this->is_valid_aws_access_key($credentials['access_key_id'])) {
                    $result['valid'] = false;
                    $result['errors'][] = __('Invalid AWS access key ID format', 'wp-performance-plus');
                }
                break;
        }
        
        return $result;
    }
    
    /**
     * Test credentials by making API call
     * @param string $provider Provider name
     * @param array $credentials Credentials data
     * @return array Test result
     */
    private function test_credentials($provider, $credentials) {
        if (!$this->cdn_manager) {
            return array(
                'success' => false,
                'error' => __('CDN Manager not available', 'wp-performance-plus')
            );
        }
        
        try {
            // Create temporary provider instance for testing
            $test_provider = $this->create_test_provider($provider, $credentials);
            
            if (!$test_provider) {
                return array(
                    'success' => false,
                    'error' => __('Could not create provider instance', 'wp-performance-plus')
                );
            }
            
            // Test API connection
            $validation_result = $test_provider->validate_credentials();
            
            if (is_wp_error($validation_result)) {
                return array(
                    'success' => false,
                    'error' => $validation_result->get_error_message()
                );
            }
            
            return array(
                'success' => true,
                'response_time' => $this->measure_api_response_time($test_provider)
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Encrypt credentials for secure storage
     * @param array $credentials Raw credentials
     * @return array Encrypted credentials
     */
    private function encrypt_credentials($credentials) {
        $encrypted = array();
        
        foreach ($credentials as $key => $value) {
            if (in_array($key, array('api_token', 'api_key', 'access_key', 'secret_key', 'password'))) {
                $encrypted[$key] = $this->encrypt_credential($value, $key);
            } else {
                $encrypted[$key] = $value;
            }
        }
        
        return $encrypted;
    }
    
    /**
     * Encrypt individual credential
     * @param string $value Value to encrypt
     * @param string $key Credential key
     * @return string Encrypted value
     */
    public function encrypt_credential($value, $key) {
        if (empty($value)) {
            return $value;
        }
        
        $cipher = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(16);
        $encryption_key = hash('sha256', $this->encryption_salt . $key);
        
        $encrypted = openssl_encrypt($value, $cipher, $encryption_key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt individual credential
     * @param string $encrypted_value Encrypted value
     * @param string $key Credential key
     * @return string Decrypted value
     */
    public function decrypt_credential($encrypted_value, $key) {
        if (empty($encrypted_value)) {
            return $encrypted_value;
        }
        
        $cipher = 'AES-256-CBC';
        $data = base64_decode($encrypted_value);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $encryption_key = hash('sha256', $this->encryption_salt . $key);
        
        return openssl_decrypt($encrypted, $cipher, $encryption_key, 0, $iv);
    }
    
    /**
     * AJAX handler for starting wizard
     */
    public function ajax_start_wizard() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        try {
            $wizard_session = $this->run_setup_wizard();
            wp_send_json_success($wizard_session);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * AJAX handler for wizard step processing
     */
    public function ajax_wizard_step() {
        check_ajax_referer('wp_performance_plus_wizard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-performance-plus'));
        }
        
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $step = isset($_POST['step']) ? sanitize_key($_POST['step']) : '';
        $data = isset($_POST['data']) ? $_POST['data'] : array(); // Sanitized in processing methods
        
        if (empty($session_id) || empty($step)) {
            wp_send_json_error(__('Missing required parameters.', 'wp-performance-plus'));
        }
        
        try {
            $step_result = $this->process_wizard_step($session_id, $step, $data);
            wp_send_json_success($step_result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * Get required credential fields for provider
     * @param string $provider Provider name
     * @return array Required fields
     */
    private function get_required_credential_fields($provider) {
        $required_fields = array(
            'cloudflare' => array('api_token', 'email', 'zone_id'),
            'keycdn' => array('api_key'),
            'bunnycdn' => array('access_key', 'storage_zone_name'),
            'cloudfront' => array('access_key_id', 'secret_access_key', 'distribution_id')
        );
        
        return $required_fields[$provider] ?? array();
    }
    
    /**
     * Additional validation and helper methods would be implemented here for:
     * - is_valid_cloudflare_token()
     * - is_valid_keycdn_key()
     * - is_valid_bunnycdn_key()
     * - is_valid_aws_access_key()
     * - create_test_provider()
     * - measure_api_response_time()
     * - process_environment_detection()
     * - process_cdn_provider_selection()
     * - process_performance_thresholds()
     * - process_optimization_settings()
     * - process_testing_validation()
     * - process_completion()
     * - get_next_step()
     * - And many more configuration methods...
     */
} 