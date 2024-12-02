<?php
/**
 * Register all actions and filters for the plugin.
 */
class WP_Performance_Plus_Loader {
    /** @var array The actions registered with WordPress */
    protected $actions;

    /** @var array The filters registered with WordPress */
    protected $filters;

    /** @var WP_Performance_Plus_Loader Singleton instance */
    private static $instance = null;

    /** @var array Debug information about hooks */
    protected $hook_debug = [];

    /**
     * Protected constructor for singleton pattern
     */
    protected function __construct() {
        $this->actions = array();
        $this->filters = array();
        $this->hook_debug = array(
            'actions' => array(),
            'filters' => array(),
            'execution_times' => array()
        );
    }

    /**
     * Get singleton instance
     *
     * @return WP_Performance_Plus_Loader
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress action.
     * @param object $component     A reference to the instance of the object on which the action is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
        
        // Log action registration for debugging
        $this->hook_debug['actions'][] = array(
            'hook' => $hook,
            'component' => get_class($component),
            'callback' => $callback,
            'priority' => $priority,
            'time_registered' => microtime(true)
        );
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress filter.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
        
        // Log filter registration for debugging
        $this->hook_debug['filters'][] = array(
            'hook' => $hook,
            'component' => get_class($component),
            'callback' => $callback,
            'priority' => $priority,
            'time_registered' => microtime(true)
        );
    }

    /**
     * A utility function that is used to register the actions and hooks into a single collection.
     *
     * @access private
     * @param array  $hooks         The collection of hooks that is being registered.
     * @param string $hook          The name of the WordPress filter that is being registered.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      The priority at which the function should be fired.
     * @param int    $accepted_args The number of arguments that should be passed to the $callback.
     * @return array The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     */
    public function run() {
        foreach ($this->filters as $hook) {
            $start_time = microtime(true);
            
            add_filter(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
            
            $this->hook_debug['execution_times'][] = array(
                'type' => 'filter',
                'hook' => $hook['hook'],
                'time' => microtime(true) - $start_time
            );
        }

        foreach ($this->actions as $hook) {
            $start_time = microtime(true);
            
            add_action(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
            
            $this->hook_debug['execution_times'][] = array(
                'type' => 'action',
                'hook' => $hook['hook'],
                'time' => microtime(true) - $start_time
            );
        }
    }

    /**
     * Get debug information about registered hooks
     *
     * @return array Debug information
     */
    public function get_debug_info() {
        return array(
            'total_actions' => count($this->actions),
            'total_filters' => count($this->filters),
            'actions' => $this->hook_debug['actions'],
            'filters' => $this->hook_debug['filters'],
            'execution_times' => $this->hook_debug['execution_times']
        );
    }

    /**
     * Clear all registered hooks (useful for testing)
     */
    public function clear_hooks() {
        $this->actions = array();
        $this->filters = array();
        $this->hook_debug = array(
            'actions' => array(),
            'filters' => array(),
            'execution_times' => array()
        );
    }
}
