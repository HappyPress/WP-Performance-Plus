/**
 * Handles server-level optimizations
 */
class WPPerformancePlus_Server {
    public function setup_htaccess_rules() {
        // Browser caching rules
        $rules = "
        <IfModule mod_expires.c>
            ExpiresActive On
            ExpiresByType image/jpg \"access plus 1 year\"
            ExpiresByType image/jpeg \"access plus 1 year\"
            ExpiresByType image/gif \"access plus 1 year\"
            ExpiresByType image/png \"access plus 1 year\"
            ExpiresByType text/css \"access plus 1 month\"
            ExpiresByType application/javascript \"access plus 1 month\"
        </IfModule>";

        // GZIP compression rules
        $rules .= "
        <IfModule mod_deflate.c>
            AddOutputFilterByType DEFLATE text/plain
            AddOutputFilterByType DEFLATE text/html
            AddOutputFilterByType DEFLATE text/css
            AddOutputFilterByType DEFLATE application/javascript
            AddOutputFilterByType DEFLATE application/x-javascript
        </IfModule>";

        return $rules;
    }
} 