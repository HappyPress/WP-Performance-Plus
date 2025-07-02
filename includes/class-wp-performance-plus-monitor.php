class WPPerformancePlus_Monitor {
    public function collect_metrics() {
        $metrics = array(
            'page_load_time' => $this->measure_page_load(),
            'cache_hit_rate' => $this->get_cache_hit_rate(),
            'memory_usage' => memory_get_peak_usage(true)
        );
        update_option('wp_performanceplus_metrics', $metrics);
    }
} 