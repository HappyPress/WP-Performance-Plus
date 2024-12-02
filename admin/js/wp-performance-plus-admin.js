jQuery(document).ready(function($) {
    'use strict';

    // Initialize performance score circle
    function initPerformanceScore() {
        const circle = document.querySelector('.circular-progress');
        if (!circle) return;

        const score = parseInt(circle.dataset.score);
        const progressBar = circle.querySelector('.progress-bar');
        const circumference = 2 * Math.PI * 45;
        
        progressBar.style.strokeDasharray = circumference;
        progressBar.style.strokeDashoffset = circumference - (score / 100) * circumference;
        
        // Set color based on score
        let color = '#FF5C75'; // Danger
        if (score >= 90) color = '#00C48C'; // Success
        else if (score >= 70) color = '#4C6FFF'; // Primary
        else if (score >= 50) color = '#FFB547'; // Warning
        
        progressBar.style.stroke = color;
    }

    // Initialize performance graph
    function initPerformanceGraph() {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;

        // Sample data - replace with actual data from your backend
        const data = {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Performance Score',
                data: [65, 70, 68, 75, 80, 82, 85],
                borderColor: '#4C6FFF',
                backgroundColor: 'rgba(76, 111, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Onboarding functionality
    function initOnboarding() {
        const onboarding = $('.onboarding-overlay');
        if (!onboarding.length) return;

        let currentStep = parseInt($('.step.active').data('step')) || 1;
        const totalSteps = 3;

        function updateProgress() {
            // Update progress bar
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            $('.progress-fill').css('width', progress + '%');

            // Update step indicators
            $('.progress-steps .step').each(function() {
                const stepNum = $(this).data('step');
                if (stepNum <= currentStep) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });

            // Show/hide steps
            $('.onboarding-step').removeClass('active');
            $(`.onboarding-step[data-step="${currentStep}"]`).addClass('active');

            // Update buttons
            $('.prev-step').prop('disabled', currentStep === 1);
            if (currentStep === totalSteps) {
                $('.next-step').hide();
                $('.finish-setup').show();
            } else {
                $('.next-step').show();
                $('.finish-setup').hide();
            }

            // Save current step
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_performance_plus_save_step',
                    nonce: performancePlusAdmin.nonce,
                    step: currentStep,
                    settings: getStepSettings()
                },
                success: function(response) {
                    if (!response.success) {
                        alert(response.data || performancePlusAdmin.strings.error);
                    }
                },
                error: function() {
                    alert(performancePlusAdmin.strings.error);
                }
            });
        }

        function getStepSettings() {
            const step = $(`.onboarding-step[data-step="${currentStep}"]`);
            const settings = {};

            // Collect settings based on current step
            switch(currentStep) {
                case 1:
                    settings.enable_minification = $('#enable-minification').is(':checked');
                    settings.combine_files = $('#combine-files').is(':checked');
                    settings.lazy_loading = $('#lazy-loading').is(':checked');
                    break;
                case 2:
                    settings.cdn_enabled = $('#cdn-enabled').is(':checked');
                    settings.cdn_provider = $('#cdn-provider').val();
                    settings.cdn_url = $('#cdn-url').val();
                    settings.cdn_key = $('#cdn-key').val();
                    break;
                case 3:
                    settings.optimize_tables = $('#optimize-tables').is(':checked');
                    settings.cleanup_schedule = $('#cleanup-schedule').val();
                    break;
            }

            return settings;
        }

        function validateStep() {
            let isValid = true;
            const step = $(`.onboarding-step[data-step="${currentStep}"]`);

            switch(currentStep) {
                case 2:
                    if ($('#cdn-enabled').is(':checked')) {
                        const provider = $('#cdn-provider').val();
                        const url = $('#cdn-url').val();
                        if (!provider || !url) {
                            isValid = false;
                            alert('Please fill in all CDN settings to continue.');
                        }
                    }
                    break;
                case 3:
                    if ($('#optimize-tables').is(':checked') && !$('#cleanup-schedule').val()) {
                        isValid = false;
                        alert('Please select a cleanup schedule to continue.');
                    }
                    break;
            }

            return isValid;
        }

        $('.next-step').on('click', function() {
            if (validateStep() && currentStep < totalSteps) {
                currentStep++;
                updateProgress();
            }
        });

        $('.prev-step').on('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateProgress();
            }
        });

        $('.finish-setup').on('click', function() {
            if (!validateStep()) return;

            const settings = getStepSettings();
            const $button = $(this);

            $button.prop('disabled', true).text(performancePlusAdmin.strings.saving);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_performance_plus_save_onboarding',
                    nonce: performancePlusAdmin.nonce,
                    settings: settings
                },
                success: function(response) {
                    if (response.success) {
                        $('.onboarding-overlay').fadeOut(400, function() {
                            $('.performanceplus-dashboard')
                                .attr('data-onboarding-complete', 'true')
                                .find('.dashboard-header, .dashboard-grid')
                                .fadeIn();
                        });
                    } else {
                        alert(response.data || performancePlusAdmin.strings.error);
                        $button.prop('disabled', false).text('Finish Setup');
                    }
                },
                error: function() {
                    alert(performancePlusAdmin.strings.error);
                    $button.prop('disabled', false).text('Finish Setup');
                }
            });
        });

        // Initialize progress
        updateProgress();
    }

    // Quick actions functionality
    function initQuickActions() {
        $('.clear-cache').on('click', function() {
            if (!confirm(performancePlusAdmin.strings.confirm_clear_cache)) return;
            
            const $button = $(this);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_performance_plus_clear_cache',
                    nonce: performancePlusAdmin.nonce
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || performancePlusAdmin.strings.error);
                    }
                },
                error: function() {
                    alert(performancePlusAdmin.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        $('.optimize-images').on('click', function() {
            const $button = $(this);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_performance_plus_optimize_images',
                    nonce: performancePlusAdmin.nonce
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || performancePlusAdmin.strings.error);
                    }
                },
                error: function() {
                    alert(performancePlusAdmin.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
    }

    // Initialize all functionality
    initPerformanceScore();
    initPerformanceGraph();
    initOnboarding();
    initQuickActions();
}); 