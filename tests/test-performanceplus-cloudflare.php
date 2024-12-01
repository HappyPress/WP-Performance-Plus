class Test_PerformancePlus_Cloudflare extends WP_UnitTestCase {
    private $cloudflare;
    
    public function setUp(): void {
        parent::setUp();
        $this->cloudflare = new PerformancePlus_Cloudflare();
    }
    
    public function test_validate_api_credentials() {
        // Test with valid credentials
        $result = $this->cloudflare->validate_api_credentials();
        $this->assertTrue($result);
        
        // Test with invalid credentials
        update_option('performanceplus_cloudflare_api_token', 'invalid_token');
        $result = $this->cloudflare->validate_api_credentials();
        $this->assertInstanceOf(WP_Error::class, $result);
    }
} 