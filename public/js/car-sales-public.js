(function($) {
    'use strict';

    // License plate search functionality
    $('#license-plate-search').on('submit', function(e) {
        e.preventDefault();
        
        const licensePlate = $('#license-plate').val().trim().toUpperCase();
        
        if (!licensePlate) {
            showError('Please enter a license plate');
            return;
        }

        // Validate Danish license plate format
        if (!/^[A-Z]{2}\d{5,6}$/.test(licensePlate)) {
            showError('Please enter a valid Danish license plate (e.g., AB12345)');
            return;
        }

        searchLicensePlate(licensePlate);
    });

    function searchLicensePlate(licensePlate) {
        showLoading();

        $.ajax({
            url: car_sales_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'lookup_license_plate',
                license_plate: licensePlate,
                nonce: car_sales_ajax.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    displayCarData(response.data);
                } else {
                    showError(response.data || 'Failed to lookup license plate');
                }
            },
            error: function() {
                hideLoading();
                showError('Connection error. Please try again.');
            }
        });
    }

    function displayCarData(carData) {
        const carDetailsHtml = `
            <div class="car-overview">
                <h4>${carData.make} ${carData.model} ${carData.variant || ''}</h4>
                <p class="license-plate">${carData.license_plate}</p>
            </div>
            <div class="car-specs">
                <div class="spec-grid">
                    <div class="spec-item">
                        <label>Year:</label>
                        <span>${carData.year || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>Fuel Type:</label>
                        <span>${carData.fuel_type || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>Engine Size:</label>
                        <span>${carData.engine_size || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>Power:</label>
                        <span>${carData.power_hp ? carData.power_hp + ' HP' : 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>Transmission:</label>
                        <span>${carData.transmission || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>Doors:</label>
                        <span>${carData.doors || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>Color:</label>
                        <span>${carData.color || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <label>First Registration:</label>
                        <span>${carData.first_registration ? formatDate(carData.first_registration) : 'N/A'}</span>
                    </div>
                </div>
            </div>
            ${carData.synsbasen ? `
                <div class="synsbasen-info">
                    <h5>Technical Assessment</h5>
                    <div class="assessment-score">
                        Score: ${carData.synsbasen.assessment_score || 'N/A'}
                    </div>
                    <div class="technical-condition">
                        Condition: ${carData.synsbasen.technical_condition || 'N/A'}
                    </div>
                </div>
            ` : ''}
        `;

        $('#car-details').html(carDetailsHtml);
        
        // Store car data for form submission
        $('#contact-form').data('car-data', carData);
        
        $('#search-results').show();
        $('#license-plate-search').hide();
    }

    // Contact form submission
    $('#contact-form').on('submit', function(e) {
        e.preventDefault();
        
        const carData = $(this).data('car-data');
        const formData = {
            action: 'submit_car_inquiry',
            nonce: car_sales_ajax.nonce,
            license_plate: carData.license_plate,
            make: carData.make,
            model: carData.model,
            year: carData.year,
            name: $('#contact-name').val(),
            email: $('#contact-email').val(),
            phone: $('#contact-phone').val(),
            price: $('#asking-price').val() || 0,
            mileage: $('#current-mileage').val() || 0
        };

        $.ajax({
            url: car_sales_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message);
                    setTimeout(() => {
                        window.location.href = '/my-dashboard';
                    }, 2000);
                } else {
                    showError(response.data || 'Failed to submit car');
                }
            },
            error: function() {
                showError('Connection error. Please try again.');
            }
        });
    });

    // Dashboard functionality
    if ($('.user-dashboard').length) {
        loadUserCars();
    }

    // Tab switching
    $('.tab-btn').on('click', function() {
        const tabId = $(this).data('tab');
        
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
        
        if (tabId === 'my-cars') {
            loadUserCars();
        }
    });

    function loadUserCars() {
        $.ajax({
            url: car_sales_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_user_cars',
                nonce: car_sales_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayUserCars(response.data);
                } else {
                    $('#cars-list').html('<p>Failed to load cars</p>');
                }
            },
            error: function() {
                $('#cars-list').html('<p>Error loading cars</p>');
            }
        });
    }

    function displayUserCars(cars) {
        if (cars.length === 0) {
            $('#cars-list').html('<p>You haven\'t added any cars yet. <a href="/car-search">Add your first car</a></p>');
            return;
        }

        let carsHtml = '';
        cars.forEach(car => {
            const statusClass = car.status === 'approved' ? 'status-approved' : 
                               car.status === 'pending' ? 'status-pending' : 'status-rejected';
            
            carsHtml += `
                <div class="car-card" data-car-id="${car.id}">
                    <div class="car-image">
                        ${car.images.length > 0 ? 
                            `<img src="${car.images[0].thumbnail}" alt="${car.title}">` :
                            '<div class="no-image"><i class="fas fa-car"></i></div>'
                        }
                    </div>
                    <div class="car-info">
                        <h4>${car.title}</h4>
                        <p class="car-details">${car.make} ${car.model} • ${car.year}</p>
                        <p class="car-price">${car.price ? formatPrice(car.price) + ' DKK' : 'Price not set'}</p>
                        <div class="car-stats">
                            <span class="inquiries"><i class="fas fa-envelope"></i> ${car.inquiry_count} inquiries</span>
                            <span class="status ${statusClass}">${car.status}</span>
                        </div>
                    </div>
                    <div class="car-actions">
                        <button class="view-car-btn" data-car-id="${car.id}">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            `;
        });

        $('#cars-list').html(carsHtml);
        
        // Populate car filter dropdown
        let filterOptions = '<option value="">All Cars</option>';
        cars.forEach(car => {
            filterOptions += `<option value="${car.id}">${car.title}</option>`;
        });
        $('#car-filter').html(filterOptions);
    }

    // Car modal functionality
    $(document).on('click', '.view-car-btn', function() {
        const carId = $(this).data('car-id');
        openCarModal(carId);
    });

    function openCarModal(carId) {
        // Get car details and show modal
        // Implementation for modal display
        $('#car-modal').show();
    }

    // Modal close
    $('.close-modal').on('click', function() {
        $(this).closest('.modal').hide();
    });

    // Financing calculator
    $('#loan-term').on('change', function() {
        const selectedOption = $(this).find(':selected');
        const rate = selectedOption.data('rate');
        $('#interest-rate').val(rate);
    });

    $('#financing-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'calculate_financing',
            nonce: car_sales_ajax.nonce,
            car_price: $('#car-price').val(),
            down_payment: $('#down-payment').val() || 0,
            loan_term: $('#loan-term').val(),
            interest_rate: $('#interest-rate').val()
        };

        $.ajax({
            url: car_sales_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    displayFinancingResults(response.data);
                } else {
                    showError(response.data || 'Calculation failed');
                }
            },
            error: function() {
                showError('Connection error. Please try again.');
            }
        });
    });

    function displayFinancingResults(results) {
        $('#monthly-payment').text(formatPrice(results.monthly_payment) + ' DKK');
        $('#total-payment').text(formatPrice(results.total_payment) + ' DKK');
        $('#total-interest').text(formatPrice(results.total_interest) + ' DKK');
        $('#loan-amount').text(formatPrice(results.loan_amount) + ' DKK');
        
        $('#results-section').show();
        
        // Store results for advice form
        $('#book-advice-btn').data('results', results);
    }

    $('#book-advice-btn').on('click', function() {
        const results = $(this).data('results');
        const loanTerm = $('#loan-term').val();
        const carPrice = $('#car-price').val();
        
        // Populate summary in modal
        $('#summary-price').text(formatPrice(carPrice) + ' DKK');
        $('#summary-monthly').text(formatPrice(results.monthly_payment) + ' DKK');
        $('#summary-term').text((loanTerm / 12) + ' years');
        
        $('#advice-modal').show();
    });

    $('#advice-form').on('submit', function(e) {
        e.preventDefault();
        
        const results = $('#book-advice-btn').data('results');
        const formData = {
            action: 'submit_financing_inquiry',
            nonce: car_sales_ajax.nonce,
            name: $('#advice-name').val(),
            email: $('#advice-email').val(),
            phone: $('#advice-phone').val(),
            car_price: $('#car-price').val(),
            down_payment: $('#down-payment').val() || 0,
            loan_term: $('#loan-term').val(),
            monthly_payment: results.monthly_payment,
            message: $('#advice-message').val()
        };

        $.ajax({
            url: car_sales_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data);
                    $('#advice-modal').hide();
                    $('#advice-form')[0].reset();
                } else {
                    showError(response.data || 'Failed to submit request');
                }
            },
            error: function() {
                showError('Connection error. Please try again.');
            }
        });
    });

    // Utility functions
    function showLoading() {
        $('#license-plate-search').hide();
        $('#search-results').hide();
        $('#search-error').hide();
        $('#search-loading').show();
    }

    function hideLoading() {
        $('#search-loading').hide();
    }

    function showError(message) {
        $('#search-loading').hide();
        $('#search-results').hide();
        $('#search-error .error-text').text(message);
        $('#search-error').show();
    }

    function showSuccess(message) {
        // Create and show success notification
        const notification = $(`
            <div class="success-notification">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 3000);
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('da-DK').format(price);
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('da-DK');
    }

})(jQuery);
