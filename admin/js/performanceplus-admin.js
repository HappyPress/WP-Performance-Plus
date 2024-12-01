jQuery(document).ready(function($) {
    // Handle CDN provider selection in wizard
    $('.wizard-buttons .button-primary').on('click', function(e) {
        if ($('input[name="cdn_provider"]:checked').length === 0) {
            e.preventDefault();
            alert('Please select a CDN provider to continue.');
        }
    });

    // Handle Cloudflare cache purge
    $('#purge_cloudflare_cache').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const originalText = $button.text();
        
        $button.text('Purging...').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'performanceplus_purge_cloudflare_cache',
                nonce: performanceplus_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Cache purged successfully!');
                } else {
                    showNotice('error', response.data || 'Failed to purge cache.');
                }
            },
            error: function() {
                showNotice('error', 'Failed to communicate with the server.');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Handle development mode toggle
    $('#cloudflare_dev_mode').on('change', function() {
        const $toggle = $(this);
        const $parent = $toggle.closest('td');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_cloudflare_dev_mode',
                nonce: performanceplus_admin.nonce
            },
            beforeSend: function() {
                $toggle.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    $toggle.prop('checked', response.data.dev_mode);
                } else {
                    showNotice('error', response.data || 'Failed to toggle development mode.');
                    $toggle.prop('checked', !$toggle.prop('checked'));
                }
            },
            error: function() {
                showNotice('error', 'Failed to communicate with the server.');
                $toggle.prop('checked', !$toggle.prop('checked'));
            },
            complete: function() {
                $toggle.prop('disabled', false);
            }
        });
    });

    // Helper function to show admin notices
    function showNotice(type, message) {
        const $notice = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
            </div>
        `);
        
        $('.wrap > h1').after($notice);
        
        // Initialize WordPress dismissible notices
        if (typeof wp !== 'undefined' && wp.notices) {
            wp.notices.initializeNotices();
        }
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
}); 