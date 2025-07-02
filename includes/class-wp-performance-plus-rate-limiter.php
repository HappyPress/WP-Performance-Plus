class WPPerformancePlus_Rate_Limiter {
    public function check_limit($action, $limit = 60) {
        $transient_key = "wp_performanceplus_rate_limit_{$action}";
        $count = get_transient($transient_key) ?: 0;
        
        if ($count >= $limit) {
            return false;
        }
        
        set_transient($transient_key, $count + 1, HOUR_IN_SECONDS);
        return true;
    }
} 