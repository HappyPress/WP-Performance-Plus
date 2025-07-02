/**
 * WP Performance Plus Admin JavaScript
 * 
 * Handles all admin interface interactions and AJAX functionality
 */

(function($) {
    'use strict';

    /**
     * Initialize admin functionality when document is ready
     */
    $(document).ready(function() {
        WPPerformancePlus.init();
    });

    /**
     * Main WP Performance Plus Admin Object
     */
    window.WPPerformancePlus = {
        
        /**
         * Initialize all functionality
         */
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initStatusCards();
            this.initFormValidation();
            this.initQuickActions();
            this.handleSettingsToggle();
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            var self = this;

            // Quick action buttons
            $('.action-btn').on('click', function(e) {
                e.preventDefault();
                var action = $(this).attr('id');
                self.performQuickAction(action);
            });

            // Settings form submission
            $('.wp-performance-plus-form').on('submit', function(e) {
                self.handleSettingsSubmit(e, this);
            });

            // Toggle switches
            $('.toggle-switch input[type="checkbox"]').on('change', function() {
                self.handleToggleChange(this);
            });

            // CDN provider change
            $('select[name="wp_performance_plus_settings[cdn_provider]"]').on('change', function() {
                self.handleCdnProviderChange($(this).val());
            });

            // Optimization level change
            $('select[name="wp_performance_plus_settings[optimization_level]"]').on('change', function() {
                self.handleOptimizationLevelChange($(this).val());
            });

            // Refresh stats button (if exists)
            $('.refresh-stats').on('click', function(e) {
                e.preventDefault();
                self.refreshPerformanceStats();
            });

            // Clear cache link in admin bar
            $('.wp-admin-bar-wp-performance-plus-clear-cache a').on('click', function(e) {
                e.preventDefault();
                self.performQuickAction('clear-cache');
            });
        },

        /**
         * Initialize tooltips for form elements
         */
        initTooltips: function() {
            // Add tooltips to form descriptions
            $('.description').each(function() {
                var $this = $(this);
                if ($this.text().length > 100) {
                    $this.addClass('has-tooltip');
                }
            });
        },

        /**
         * Initialize status card animations
         */
        initStatusCards: function() {
            $('.status-card').each(function(index) {
                var $card = $(this);
                setTimeout(function() {
                    $card.addClass('animate-in');
                }, index * 100);
            });
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            // Add validation for required fields
            $('input[required], select[required]').on('blur', function() {
                var $field = $(this);
                var value = $field.val();
                
                if (!value || value.trim() === '') {
                    $field.addClass('error');
                    this.showFieldError($field, 'This field is required.');
                } else {
                    $field.removeClass('error');
                    this.hideFieldError($field);
                }
            }.bind(this));

            // Email validation
            $('input[type="email"]').on('blur', function() {
                var $field = $(this);
                var email = $field.val();
                
                if (email && !this.isValidEmail(email)) {
                    $field.addClass('error');
                    this.showFieldError($field, 'Please enter a valid email address.');
                } else {
                    $field.removeClass('error');
                    this.hideFieldError($field);
                }
            }.bind(this));
        },

        /**
         * Initialize quick action functionality
         */
        initQuickActions: function() {
            // Add loading states to buttons
            $('.action-btn').each(function() {
                var $btn = $(this);
                var originalText = $btn.html();
                $btn.data('original-text', originalText);
            });
        },

        /**
         * Handle settings toggle functionality
         */
        handleSettingsToggle: function() {
            // Enable/disable sections based on master toggle
            var $masterToggle = $('input[name="wp_performance_plus_settings[enable_optimization]"]');
            
            if ($masterToggle.length) {
                this.toggleDependentSettings($masterToggle.is(':checked'));
                
                $masterToggle.on('change', function() {
                    this.toggleDependentSettings($(this).is(':checked'));
                }.bind(this));
            }
        },

        /**
         * Toggle dependent settings based on master switch
         */
        toggleDependentSettings: function(enabled) {
            var $dependentSections = $('.settings-section').not(':first');
            
            if (enabled) {
                $dependentSections.removeClass('disabled').find('input, select').prop('disabled', false);
            } else {
                $dependentSections.addClass('disabled').find('input, select').prop('disabled', true);
            }
        },

        /**
         * Perform quick actions via AJAX
         */
        performQuickAction: function(action) {
            var self = this;
            var actionMap = {
                'clear-cache': 'clear_cache',
                'run-optimization': 'run_optimization', 
                'test-cdn': 'test_cdn',
                'analyze-performance': 'analyze_performance'
            };

            var ajaxAction = actionMap[action];
            if (!ajaxAction) return;

            var $btn = $('#' + action);
            var loadingTexts = {
                'clear-cache': 'Clearing cache...',
                'run-optimization': 'Running optimization...',
                'test-cdn': 'Testing CDN connection...',
                'analyze-performance': 'Analyzing performance...'
            };

            this.setButtonLoading($btn, loadingTexts[action] || 'Processing...');
            this.showLoading(loadingTexts[action] || 'Processing...');

            $.ajax({
                url: wp_performance_plus_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_performance_plus_' + ajaxAction,
                    nonce: wp_performance_plus_ajax.nonce
                },
                success: function(response) {
                    self.hideLoading();
                    self.resetButtonLoading($btn);
                    
                    if (response.success) {
                        self.showNotice(response.data.message || 'Action completed successfully!', 'success');
                        
                        // Refresh stats if needed
                        if (action === 'clear-cache' || action === 'run-optimization') {
                            self.refreshPerformanceStats();
                        }
                    } else {
                        self.showNotice(response.data.message || 'An error occurred.', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.resetButtonLoading($btn);
                    self.showNotice('Request failed. Please try again.', 'error');
                }
            });
        },

        /**
         * Handle settings form submission
         */
        handleSettingsSubmit: function(e, form) {
            var $form = $(form);
            var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
            
            // Add loading state
            $submitBtn.prop('disabled', true).val('Saving...');
            
            // Form will submit normally, but we can add client-side validation here
            var isValid = this.validateForm($form);
            
            if (!isValid) {
                e.preventDefault();
                $submitBtn.prop('disabled', false).val('Save Settings');
                return false;
            }
        },

        /**
         * Handle toggle switch changes
         */
        handleToggleChange: function(toggle) {
            var $toggle = $(toggle);
            var name = $toggle.attr('name');
            
            // Add visual feedback
            $toggle.closest('.toggle-switch').addClass('changing');
            
            setTimeout(function() {
                $toggle.closest('.toggle-switch').removeClass('changing');
            }, 300);

            // Handle specific toggle logic
            if (name === 'wp_performance_plus_settings[enable_optimization]') {
                this.toggleDependentSettings($toggle.is(':checked'));
            }
        },

        /**
         * Handle CDN provider change
         */
        handleCdnProviderChange: function(provider) {
            // Show/hide provider-specific settings
            $('.cdn-settings-section').hide();
            if (provider && provider !== 'none') {
                $('.cdn-settings-' + provider).show();
            }
            
            this.showNotice('CDN provider changed. Don\'t forget to save your settings.', 'info');
        },

        /**
         * Handle optimization level change
         */
        handleOptimizationLevelChange: function(level) {
            var descriptions = {
                'safe': 'Safe mode applies minimal optimizations that are highly compatible.',
                'balanced': 'Balanced mode provides good optimization with excellent compatibility.',
                'aggressive': 'Aggressive mode maximizes optimization but may require testing.'
            };

            if (descriptions[level]) {
                this.showNotice(descriptions[level], 'info');
            }
        },

        /**
         * Refresh performance statistics
         */
        refreshPerformanceStats: function() {
            var self = this;
            
            $.ajax({
                url: wp_performance_plus_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_performance_plus_refresh_stats',
                    nonce: wp_performance_plus_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.stats) {
                        self.updateStatsDisplay(response.data.stats);
                    }
                }
            });
        },

        /**
         * Update statistics display
         */
        updateStatsDisplay: function(stats) {
            // Update status cards
            $('.status-card').each(function() {
                var $card = $(this);
                var type = $card.attr('class').match(/status-(\w+)/);
                
                if (type && type[1] && stats[type[1]]) {
                    $card.find('.status-text').text(stats[type[1]].value);
                    $card.find('.status-level').text(stats[type[1]].description);
                }
            });

            // Update performance insights
            if (stats.performance_score) {
                $('.score-circle .score').text(stats.performance_score);
            }
            
            if (stats.cache_hit_rate) {
                $('.progress-fill').css('width', stats.cache_hit_rate + '%');
            }
        },

        /**
         * Form validation
         */
        validateForm: function($form) {
            var isValid = true;
            var self = this;

            // Check required fields
            $form.find('input[required], select[required]').each(function() {
                var $field = $(this);
                var value = $field.val();
                
                if (!value || value.trim() === '') {
                    self.showFieldError($field, 'This field is required.');
                    isValid = false;
                } else {
                    self.hideFieldError($field);
                }
            });

            // Check email fields
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var email = $field.val();
                
                if (email && !self.isValidEmail(email)) {
                    self.showFieldError($field, 'Please enter a valid email address.');
                    isValid = false;
                }
            });

            return isValid;
        },

        /**
         * Show field error
         */
        showFieldError: function($field, message) {
            var $error = $field.siblings('.field-error');
            if ($error.length === 0) {
                $error = $('<span class="field-error"></span>');
                $field.after($error);
            }
            $error.text(message).show();
            $field.addClass('error');
        },

        /**
         * Hide field error
         */
        hideFieldError: function($field) {
            $field.siblings('.field-error').hide();
            $field.removeClass('error');
        },

        /**
         * Email validation
         */
        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Set button loading state
         */
        setButtonLoading: function($btn, text) {
            $btn.prop('disabled', true)
                .addClass('loading')
                .html('<span class="dashicons dashicons-update-alt"></span> ' + text);
        },

        /**
         * Reset button loading state
         */
        resetButtonLoading: function($btn) {
            var originalText = $btn.data('original-text');
            $btn.prop('disabled', false)
                .removeClass('loading')
                .html(originalText);
        },

        /**
         * Show loading overlay
         */
        showLoading: function(text) {
            $('#wp-performance-plus-loading .loading-text').text(text || 'Processing...');
            $('#wp-performance-plus-loading').show();
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            $('#wp-performance-plus-loading').hide();
        },

        /**
         * Show admin notice
         */
        showNotice: function(message, type) {
            type = type || 'info';
            var noticeClass = 'notice-' + type;
            
            var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            // Insert after the page title
            var $target = $('.wp-performance-plus-dashboard h1, .wrap h1').first();
            if ($target.length) {
                $target.after($notice);
            } else {
                $('.wrap').prepend($notice);
            }
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);

            // Handle dismiss button
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            });
        },

        /**
         * Utility function to debounce function calls
         */
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

    /**
     * Additional CSS classes for loading states and animations
     */
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .toggle-switch.changing .toggle-slider {
                box-shadow: 0 0 10px rgba(0, 115, 170, 0.5);
            }
            
            .action-btn.loading {
                opacity: 0.7;
                cursor: not-allowed;
            }
            
            .action-btn.loading .dashicons-update-alt {
                animation: spin 1s linear infinite;
            }
            
            .field-error {
                display: block;
                color: #dc3232;
                font-size: 12px;
                margin-top: 5px;
            }
            
            .form-table input.error,
            .form-table select.error {
                border-color: #dc3232;
                box-shadow: 0 0 0 1px #dc3232;
            }
            
            .settings-section.disabled {
                opacity: 0.5;
                pointer-events: none;
            }
            
            .status-card.animate-in {
                animation: slideInUp 0.5s ease forwards;
            }
            
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `)
        .appendTo('head');

})(jQuery); 