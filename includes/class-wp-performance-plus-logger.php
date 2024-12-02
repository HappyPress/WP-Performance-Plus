<?php
/**
 * Class WP_Performance_Plus_Logger
 * 
 * Handles logging functionality for the plugin.
 */
class WP_Performance_Plus_Logger {
    /** @var string Option name for log entries */
    private const OPTION_LOG = 'wp_performance_plus_log';
    
    /** @var bool Whether logger is initialized */
    private static $initialized = false;

    /**
     * Initialize the logger
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;
        self::info('Logger initialized');
    }

    /**
     * Log an info message
     */
    public static function info($message, $data = []) {
        self::log($message, 'info', $data);
    }

    /**
     * Log an error message
     */
    public static function error($message, $data = []) {
        self::log($message, 'error', $data);
    }

    /**
     * Log a warning message
     */
    public static function warning($message, $data = []) {
        self::log($message, 'warning', $data);
    }

    /**
     * Log a debug message
     */
    public static function debug($message, $data = []) {
        self::log($message, 'debug', $data);
    }

    /**
     * Add a log entry
     */
    private static function log($message, $level = 'info', $data = []) {
        if (!self::$initialized) {
            return;
        }

        $log = get_option(self::OPTION_LOG, []);
        
        $log[] = [
            'time' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'data' => $data,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ];

        // Keep only the last 1000 entries
        if (count($log) > 1000) {
            $log = array_slice($log, -1000);
        }

        update_option(self::OPTION_LOG, $log);

        // Also write to WordPress debug log if enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[WP Performance Plus] %s: %s %s',
                strtoupper($level),
                $message,
                !empty($data) ? json_encode($data) : ''
            ));
        }
    }

    /**
     * Get all log entries
     */
    public static function get_logs() {
        return get_option(self::OPTION_LOG, []);
    }

    /**
     * Clear all log entries
     */
    public static function clear_logs() {
        delete_option(self::OPTION_LOG);
        self::info('Log cleared');
    }

    /**
     * Get log entries by level
     */
    public static function get_logs_by_level($level) {
        $logs = self::get_logs();
        return array_filter($logs, function($entry) use ($level) {
            return $entry['level'] === $level;
        });
    }

    /**
     * Get log entries within a time range
     */
    public static function get_logs_by_timeframe($start_time, $end_time = null) {
        $logs = self::get_logs();
        return array_filter($logs, function($entry) use ($start_time, $end_time) {
            $entry_time = strtotime($entry['time']);
            if ($end_time === null) {
                return $entry_time >= $start_time;
            }
            return $entry_time >= $start_time && $entry_time <= $end_time;
        });
    }

    /**
     * Search log entries
     */
    public static function search_logs($query) {
        $logs = self::get_logs();
        return array_filter($logs, function($entry) use ($query) {
            return stripos($entry['message'], $query) !== false ||
                   (is_string($entry['data']) && stripos($entry['data'], $query) !== false);
        });
    }

    /**
     * Export logs as JSON
     */
    public static function export_logs() {
        return json_encode(self::get_logs(), JSON_PRETTY_PRINT);
    }

    /**
     * Get log statistics
     */
    public static function get_stats() {
        $logs = self::get_logs();
        $stats = [
            'total' => count($logs),
            'by_level' => [],
            'first_entry' => null,
            'last_entry' => null
        ];

        if (!empty($logs)) {
            $stats['first_entry'] = reset($logs)['time'];
            $stats['last_entry'] = end($logs)['time'];

            foreach ($logs as $entry) {
                if (!isset($stats['by_level'][$entry['level']])) {
                    $stats['by_level'][$entry['level']] = 0;
                }
                $stats['by_level'][$entry['level']]++;
            }
        }

        return $stats;
    }
} 