<?php
/**
 * Class Test_PerformancePlus_Loader
 *
 * @package Performance_Plus
 */
class Test_PerformancePlus_Loader extends WP_UnitTestCase {
    private $loader;

    public function setUp(): void {
        parent::setUp();
        $this->loader = PerformancePlus_Loader::get_instance();
    }

    public function test_loader_singleton() {
        $loader1 = PerformancePlus_Loader::get_instance();
        $loader2 = PerformancePlus_Loader::get_instance();
        
        $this->assertSame($loader1, $loader2, 'Loader should maintain singleton instance');
    }

    public function test_action_registration() {
        $component = new stdClass();
        $this->loader->add_action('test_action', $component, 'test_callback');
        
        $debug_info = $this->loader->get_debug_info();
        $this->assertEquals(1, $debug_info['total_actions'], 'Should have one registered action');
        $this->assertEquals('test_action', $debug_info['actions'][0]['hook'], 'Action hook should match');
    }

    public function test_filter_registration() {
        $component = new stdClass();
        $this->loader->add_filter('test_filter', $component, 'test_callback');
        
        $debug_info = $this->loader->get_debug_info();
        $this->assertEquals(1, $debug_info['total_filters'], 'Should have one registered filter');
        $this->assertEquals('test_filter', $debug_info['filters'][0]['hook'], 'Filter hook should match');
    }

    public function test_hook_execution_timing() {
        $component = new stdClass();
        $this->loader->add_action('init', $component, 'test_callback');
        $this->loader->run();
        
        $debug_info = $this->loader->get_debug_info();
        $this->assertArrayHasKey('execution_times', $debug_info, 'Should track execution times');
        $this->assertNotEmpty($debug_info['execution_times'], 'Should have execution time entries');
    }

    public function test_clear_hooks() {
        $component = new stdClass();
        $this->loader->add_action('test_action', $component, 'test_callback');
        $this->loader->add_filter('test_filter', $component, 'test_callback');
        
        $this->loader->clear_hooks();
        
        $debug_info = $this->loader->get_debug_info();
        $this->assertEquals(0, $debug_info['total_actions'], 'Should have no actions after clear');
        $this->assertEquals(0, $debug_info['total_filters'], 'Should have no filters after clear');
    }

    public function test_hook_priority() {
        $component = new stdClass();
        $this->loader->add_action('test_action', $component, 'test_callback', 20);
        
        $debug_info = $this->loader->get_debug_info();
        $this->assertEquals(20, $debug_info['actions'][0]['priority'], 'Action priority should match');
    }

    public function tearDown(): void {
        $this->loader->clear_hooks();
        parent::tearDown();
    }
} 