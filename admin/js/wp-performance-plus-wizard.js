jQuery(document).ready(function($) {
    var currentStep = 1;
    var totalSteps = 3;

    // Handle next button click
    $('.next-step').click(function(e) {
        e.preventDefault();
        
        // Save current step settings
        var stepData = {};
        $('.step-content[data-step="' + currentStep + '"] :input').each(function() {
            var input = $(this);
            var name = input.attr('name');
            
            if (!name) return;
            
            if (input.is(':checkbox')) {
                stepData[name] = input.is(':checked');
            } else if (input.is(':radio')) {
                if (input.is(':checked')) {
                    stepData[name] = input.val();
                }
            } else {
                stepData[name] = input.val();
            }
        });

        // Save step data via AJAX
        $.ajax({
            url: wpPerformancePlusWizard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wp_performance_plus_save_step',
                security: wpPerformancePlusWizard.nonce,
                step: currentStep,
                data: JSON.stringify(stepData)
            },
            success: function(response) {
                if (response.success) {
                    // Mark current step as completed
                    $('.step-indicator[data-step="' + currentStep + '"]')
                        .removeClass('active')
                        .addClass('completed');

                    // Move to next step
                    if (currentStep < totalSteps) {
                        currentStep++;
                        updateStepVisibility();
                    }
                } else {
                    alert(response.data.message || wpPerformancePlusWizard.strings.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert(wpPerformancePlusWizard.strings.error);
            }
        });
    });

    // Handle previous button click
    $('.prev-step').click(function(e) {
        e.preventDefault();
        
        if (currentStep > 1) {
            // Remove completed status from current step
            $('.step-indicator[data-step="' + currentStep + '"]')
                .removeClass('active completed');

            // Move to previous step
            currentStep--;
            updateStepVisibility();
        }
    });

    // Handle form submission
    $('#wizard-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {};
        $(this).find(':input').each(function() {
            var input = $(this);
            var name = input.attr('name');
            
            if (!name) return;
            
            if (input.is(':checkbox')) {
                formData[name] = input.is(':checked');
            } else if (input.is(':radio')) {
                if (input.is(':checked')) {
                    formData[name] = input.val();
                }
            } else {
                formData[name] = input.val();
            }
        });
        
        // Save all settings via AJAX
        $.ajax({
            url: wpPerformancePlusWizard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wp_performance_plus_save_onboarding',
                security: wpPerformancePlusWizard.nonce,
                data: JSON.stringify(formData)
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data.message || wpPerformancePlusWizard.strings.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert(wpPerformancePlusWizard.strings.error);
            }
        });
    });

    // Update step visibility
    function updateStepVisibility() {
        // Hide all steps
        $('.step-content').hide();
        
        // Show current step
        $('.step-content[data-step="' + currentStep + '"]').show();
        
        // Update step indicators
        $('.step-indicator').removeClass('active');
        $('.step-indicator[data-step="' + currentStep + '"]').addClass('active');
        
        // Update navigation buttons
        if (currentStep === 1) {
            $('.prev-step').hide();
        } else {
            $('.prev-step').show();
        }
        
        if (currentStep === totalSteps) {
            $('.next-step').hide();
            $('.finish-setup').show();
        } else {
            $('.next-step').show();
            $('.finish-setup').hide();
        }
    }

    // Initialize first step
    updateStepVisibility();
}); 