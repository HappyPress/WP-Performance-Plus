class PerformancePlus_Logger {
    public static function log($message, $type = 'info') {
        if (!WP_DEBUG) {
            return;
        }
        error_log(sprintf('[PerformancePlus] [%s] %s', $type, $message));
    }
} 