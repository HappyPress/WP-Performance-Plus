<?php
/**
 * Class PerformancePlus_Debug
 * 
 * Handles debug logging and management for the plugin
 */
class PerformancePlus_Debug {
    /** @var string Log file path */
    private $log_file;
    
    /** @var bool Whether debug mode is enabled */
    private $debug_enabled;

    /**
     * Initialize debug functionality
     */
    public function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/performanceplus-debug.log';
        $this->debug_enabled = get_option('performanceplus_debug_mode', false);
        
        // Add AJAX handlers for log management
        add_action('wp_ajax_performanceplus_clear_log', [$this, 'ajax_clear_log']);
        add_action('wp_ajax_performanceplus_get_log', [$this, 'ajax_get_log']);
    }

    /**
     * Log a debug message
     * 
     * @param string $message Message to log
     * @param string $type Log type (debug|info|warning|error)
     * @param array $context Additional context data
     */
    public function log($message, $type = 'debug', $context = []) {
        if (!$this->debug_enabled && $type !== 'error') {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';
        
        $log_entry = sprintf(
            "[%s] %s: %s in %s %s\n",
            $timestamp,
            strtoupper($type),
            $message,
            $caller,
            !empty($context) ? '| Context: ' . json_encode($context) : ''
        );

        error_log($log_entry, 3, $this->log_file);
    }

    /**
     * Clear the debug log
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        touch($this->log_file);
        $this->log('Log cleared', 'info');
    }

    /**
     * Handle AJAX request to clear log
     */
    public function ajax_clear_log() {
        check_ajax_referer('performanceplus_debug');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $this->clear_log();
        wp_send_json_success('Log cleared');
    }

    /**
     * Handle AJAX request to get log contents
     */
    public function ajax_get_log() {
        check_ajax_referer('performanceplus_debug');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $log_contents = file_exists($this->log_file) ? 
            file_get_contents($this->log_file) : 
            'Log file is empty';

        wp_send_json_success(['log' => $log_contents]);
    }
} 