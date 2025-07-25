(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin functionality
        initCarApproval();
        initBilinfoSync();
        initAnalytics();
        initInquiryManagement();
    });

    /**
     * Car approval functionality
     */
    function initCarApproval() {
        $('.approve-car').on('click', function() {
            const carId = $(this).data('car-id');
            const button = $(this);
            
            if (confirm('Are you sure you want to approve this car listing?')) {
                button.prop('disabled', true).text('Approving...');
                
                $.ajax({
                    url: car_sales_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'approve_car_listing',
                        car_id: carId,
                        nonce: car_sales_admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Car approved successfully!', 'success');
                            // Remove the row or update status
                            button.closest('tr').fadeOut();
                        } else {
                            showNotification(response.data || 'Failed to approve car', 'error');
                            button.prop('disabled', false).text('Approve');
                        }
                    },
                    error: function() {
                        showNotification('Connection error. Please try again.', 'error');
                        button.prop('disabled', false).text('Approve');
                    }
                });
            }
        });

        // Bulk approve functionality
        $('#bulk-approve').on('click', function() {
            const selectedCars = $('.car-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedCars.length === 0) {
                showNotification('Please select cars to approve', 'warning');
                return;
            }

            if (confirm(`Are you sure you want to approve ${selectedCars.length} car(s)?`)) {
                bulkApproveCars(selectedCars);
            }
        });

        // Select all checkbox
        $('#select-all-cars').on('change', function() {
            $('.car-checkbox').prop('checked', $(this).is(':checked'));
        });
    }

    /**
     * Bulk approve cars
     */
    function bulkApproveCars(carIds) {
        let completed = 0;
        const total = carIds.length;
        
        showProgressBar(0, total);

        carIds.forEach(function(carId) {
            $.ajax({
                url: car_sales_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'approve_car_listing',
                    car_id: carId,
                    nonce: car_sales_admin.nonce
                },
                success: function(response) {
                    completed++;
                    updateProgressBar(completed, total);
                    
                    if (completed === total) {
                        hideProgressBar();
                        showNotification(`Successfully approved ${total} car(s)`, 'success');
                        location.reload();
                    }
                },
                error: function() {
                    completed++;
                    updateProgressBar(completed, total);
                    
                    if (completed === total) {
                        hideProgressBar();
                        showNotification('Some cars failed to approve. Please check and try again.', 'warning');
                    }
                }
            });
        });
    }

    /**
     * Bilinfo synchronization
     */
    function initBilinfoSync() {
        $('#sync-bilinfo').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).text('Syncing...');
            
            $.ajax({
                url: car_sales_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'sync_bilinfo',
                    nonce: car_sales_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Bilinfo sync completed successfully!', 'success');
                    } else {
                        showNotification(response.data || 'Sync failed', 'error');
                    }
                    button.prop('disabled', false).text('Sync with Bilinfo.dk');
                },
                error: function() {
                    showNotification('Connection error. Please try again.', 'error');
                    button.prop('disabled', false).text('Sync with Bilinfo.dk');
                }
            });
        });

        // Auto-sync toggle
        $('#auto-sync-toggle').on('change', function() {
            const enabled = $(this).is(':checked');
            
            $.ajax({
                url: car_sales_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'toggle_auto_sync',
                    enabled: enabled ? 1 : 0,
                    nonce: car_sales_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(
                            enabled ? 'Auto-sync enabled' : 'Auto-sync disabled', 
                            'success'
                        );
                    }
                }
            });
        });
    }

    /**
     * Analytics functionality
     */
    function initAnalytics() {
        // Date range picker for analytics
        if ($('#analytics-date-range').length) {
            $('#analytics-date-range').on('change', function() {
                loadAnalyticsData($(this).val());
            });
        }

        // Export analytics data
        $('#export-analytics').on('click', function() {
            const dateRange = $('#analytics-date-range').val();
            const exportUrl = `${window.location.href}&export=csv&range=${dateRange}`;
            window.open(exportUrl, '_blank');
        });

        // Real-time stats updates
        if ($('.car-sales-stats').length) {
            setInterval(updateRealTimeStats, 30000); // Update every 30 seconds
        }
    }

    /**
     * Load analytics data for specific date range
     */
    function loadAnalyticsData(dateRange) {
        $.ajax({
            url: car_sales_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'load_analytics',
                date_range: dateRange,
                nonce: car_sales_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateAnalyticsDisplay(response.data);
                }
            }
        });
    }

    /**
     * Update analytics display
     */
    function updateAnalyticsDisplay(data) {
        $('.total-inquiries .stat-number').text(data.total_inquiries);
        $('.total-cars .stat-number').text(data.total_cars);
        $('.pending-approval .stat-number').text(data.pending_approval);
        $('.inquiries-today .stat-number').text(data.inquiries_today);

        // Update charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            updateCharts(data);
        }
    }

    /**
     * Update real-time statistics
     */
    function updateRealTimeStats() {
        $.ajax({
            url: car_sales_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'get_real_time_stats',
                nonce: car_sales_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Animate number changes
                    animateStatNumber('.inquiries-today .stat-number', response.data.inquiries_today);
                    animateStatNumber('.total-inquiries .stat-number', response.data.total_inquiries);
                }
            }
        });
    }

    /**
     * Animate number changes in statistics
     */
    function animateStatNumber(selector, newValue) {
        const element = $(selector);
        const currentValue = parseInt(element.text()) || 0;
        
        if (currentValue !== newValue) {
            element.addClass('stat-updating');
            
            $({ value: currentValue }).animate({ value: newValue }, {
                duration: 1000,
                step: function() {
                    element.text(Math.floor(this.value));
                },
                complete: function() {
                    element.text(newValue).removeClass('stat-updating');
                }
            });
        }
    }

    /**
     * Inquiry management
     */
    function initInquiryManagement() {
        // Mark inquiry as contacted
        $('.mark-contacted').on('click', function() {
            const inquiryId = $(this).data('inquiry-id');
            updateInquiryStatus(inquiryId, 'contacted');
        });

        // Mark inquiry as closed
        $('.mark-closed').on('click', function() {
            const inquiryId = $(this).data('inquiry-id');
            updateInquiryStatus(inquiryId, 'closed');
        });

        // Inquiry filters
        $('#inquiry-status-filter, #inquiry-date-filter').on('change', function() {
            filterInquiries();
        });

        // Quick response templates
        $('.quick-response').on('click', function() {
            const template = $(this).data('template');
            loadResponseTemplate(template);
        });
    }

    /**
     * Update inquiry status
     */
    function updateInquiryStatus(inquiryId, status) {
        $.ajax({
            url: car_sales_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'update_inquiry_status',
                inquiry_id: inquiryId,
                status: status,
                nonce: car_sales_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Inquiry status updated', 'success');
                    // Update the status in the table
                    $(`.inquiry-${inquiryId} .status`).text(status).removeClass().addClass(`status status-${status}`);
                }
            }
        });
    }

    /**
     * Filter inquiries based on selected criteria
     */
    function filterInquiries() {
        const status = $('#inquiry-status-filter').val();
        const dateRange = $('#inquiry-date-filter').val();
        
        $.ajax({
            url: car_sales_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_inquiries',
                status: status,
                date_range: dateRange,
                nonce: car_sales_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#inquiries-table tbody').html(response.data.html);
                }
            }
        });
    }

    /**
     * Settings management
     */
    function initSettings() {
        // Test API connections
        $('.test-api-connection').on('click', function() {
            const apiType = $(this).data('api-type');
            testApiConnection(apiType);
        });

        // Import/export settings
        $('#export-settings').on('click', function() {
            exportSettings();
        });

        $('#import-settings').on('change', function() {
            importSettings(this.files[0]);
        });
    }

    /**
     * Test API connection
     */
    function testApiConnection(apiType) {
        const button = $(`.test-api-connection[data-api-type="${apiType}"]`);
        const originalText = button.text();
        
        button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: car_sales_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'test_api_connection',
                api_type: apiType,
                nonce: car_sales_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification(`${apiType} API connection successful`, 'success');
                    button.addClass('api-success');
                } else {
                    showNotification(response.data || `${apiType} API connection failed`, 'error');
                    button.addClass('api-error');
                }
            },
            error: function() {
                showNotification('Connection test failed', 'error');
                button.addClass('api-error');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
                setTimeout(() => {
                    button.removeClass('api-success api-error');
                }, 3000);
            }
        });
    }

    /**
     * Utility functions
     */
    
    /**
     * Show notification message
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="admin-notification notification-${type}">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 5000);
        
        // Manual close
        notification.find('.notification-close').on('click', function() {
            notification.fadeOut(() => notification.remove());
        });
    }

    /**
     * Get notification icon based on type
     */
    function getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * Progress bar functions
     */
    function showProgressBar(current, total) {
        const progressHtml = `
            <div id="bulk-progress" class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${(current / total) * 100}%"></div>
                </div>
                <div class="progress-text">${current} of ${total} completed</div>
            </div>
        `;
        
        if ($('#bulk-progress').length === 0) {
            $('body').append(progressHtml);
        }
    }

    function updateProgressBar(current, total) {
        const percentage = (current / total) * 100;
        $('#bulk-progress .progress-fill').css('width', percentage + '%');
        $('#bulk-progress .progress-text').text(`${current} of ${total} completed`);
    }

    function hideProgressBar() {
        $('#bulk-progress').fadeOut(() => $('#bulk-progress').remove());
    }

    /**
     * Data table enhancements
     */
    function initDataTables() {
        if ($.fn.DataTable) {
            $('.car-sales-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']], // Order by date descending
                columnDefs: [
                    { orderable: false, targets: 'no-sort' }
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
    }

    /**
     * Initialize all admin functionality
     */
    function initializeAdmin() {
        initSettings();
        initDataTables();
        
        // Initialize tooltips if available
        if ($.fn.tooltip) {
            $('[data-tooltip]').tooltip();
        }
        
        // Initialize admin dashboard widgets
        initDashboardWidgets();
    }

    /**
     * Dashboard widgets
     */
    function initDashboardWidgets() {
        // Refresh dashboard data
        $('.refresh-widget').on('click', function() {
            const widget = $(this).closest('.dashboard-widget');
            refreshWidget(widget);
        });

        // Widget settings
        $('.widget-settings').on('click', function() {
            const widget = $(this).closest('.dashboard-widget');
            openWidgetSettings(widget);
        });
    }

    function refreshWidget(widget) {
        const widgetType = widget.data('widget-type');
        widget.addClass('widget-loading');
        
        $.ajax({
            url: car_sales_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'refresh_widget',
                widget_type: widgetType,
                nonce: car_sales_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    widget.find('.widget-content').html(response.data.html);
                }
            },
            complete: function() {
                widget.removeClass('widget-loading');
            }
        });
    }

    // Initialize everything when DOM is ready
    $(document).ready(function() {
        initializeAdmin();
    });

})(jQuery);
