<?php
/**
 * Class Test_WPPerformancePlus_Core
 *
 * @package Performance_Plus
 */

class Test_WPPerformancePlus_Core extends WP_UnitTestCase {
    private $plugin;
    private $debug;

    public function setUp(): void {
        parent::setUp();
        $this->plugin = new WPPerformancePlus();
        $this->debug = new WPPerformancePlus_Debug();
    }

    public function test_plugin_initialization() {
        $this->assertInstanceOf(WPPerformancePlus::class, $this->plugin);
        $this->assertInstanceOf(WPPerformancePlus_Loader::class, $this->plugin->get_loader());
        $this->assertEquals('wp_performanceplus', $this->plugin->get_plugin_name());
        $this->assertNotEmpty($this->plugin->get_version());
    }

    public function test_debug_logging() {
        // Enable debug mode
        update_option('wp_performanceplus_debug_mode', true);
        
        // Test different log levels
        $this->debug->log('Test debug message', 'debug');
        $this->debug->log('Test info message', 'info');
        $this->debug->log('Test warning message', 'warning');
        $this->debug->log('Test error message', 'error');
        
        // Get log contents
        $log_contents = file_get_contents(WP_CONTENT_DIR . '/wp_performanceplus-debug.log');
        
        // Assert log contains our messages
        $this->assertStringContainsString('DEBUG: Test debug message', $log_contents);
        $this->assertStringContainsString('INFO: Test info message', $log_contents);
        $this->assertStringContainsString('WARNING: Test warning message', $log_contents);
        $this->assertStringContainsString('ERROR: Test error message', $log_contents);
    }

    public function test_settings() {
        // Test default settings
        $settings = get_option('wp_performanceplus_settings');
        $this->assertIsArray($settings);
        
        // Test setting updates
        $new_settings = [
            'enable_minification' => true,
            'combine_files' => true,
            'lazy_loading' => true
        ];
        update_option('wp_performanceplus_settings', $new_settings);
        
        $updated_settings = get_option('wp_performanceplus_settings');
        $this->assertEquals($new_settings, $updated_settings);
    }

    public function test_admin_menu() {
        // Simulate admin user
        $admin_user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user_id);
        
        // Test menu registration
        do_action('admin_menu');
        global $menu;
        
        $found_menu = false;
        foreach ($menu as $menu_item) {
            if (isset($menu_item[2]) && $menu_item[2] === 'wp_performanceplus') {
                $found_menu = true;
                break;
            }
        }
        $this->assertTrue($found_menu);
    }

    public function tearDown(): void {
        // Clean up
        delete_option('wp_performanceplus_debug_mode');
        delete_option('wp_performanceplus_settings');
        if (file_exists(WP_CONTENT_DIR . '/wp_performanceplus-debug.log')) {
            unlink(WP_CONTENT_DIR . '/wp_performanceplus-debug.log');
        }
        parent::tearDown();
    }
} 