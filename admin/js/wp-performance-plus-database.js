jQuery(document).ready(function($) {
    'use strict';

    // Database optimization
    $('.optimize-database').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(performancePlusAdmin.strings.confirm_optimize_db)) {
            return;
        }

        const $button = $(this);
        const originalText = $button.text();
        
        $button.text(performancePlusAdmin.strings.optimizing).prop('disabled', true);

        $.ajax({
            url: performancePlusAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_performanceplus_optimize_database',
                nonce: performancePlusAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    if (response.data.refresh) {
                        window.location.reload();
                    }
                } else {
                    alert(response.data.message || performancePlusAdmin.strings.error);
                }
            },
            error: function() {
                alert(performancePlusAdmin.strings.error);
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Table selection
    $('#select-all-tables').on('change', function() {
        $('.table-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('.table-checkbox').on('change', function() {
        const allChecked = $('.table-checkbox:checked').length === $('.table-checkbox').length;
        $('#select-all-tables').prop('checked', allChecked);
    });
}); 