<?php
/**
 * License plate search form template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="car-search-container">
    <div class="search-header">
        <h2><?php _e('Sell Your Car', 'car-sales-plugin'); ?></h2>
        <p><?php _e('Enter your license plate to get started', 'car-sales-plugin'); ?></p>
    </div>

    <form id="license-plate-search" class="license-search-form">
        <div class="search-input-group">
            <input type="text" 
                   id="license-plate" 
                   name="license_plate" 
                   placeholder="<?php esc_attr_e('Enter license plate (e.g., AB12345)', 'car-sales-plugin'); ?>"
                   class="license-input"
                   maxlength="8"
                   required>
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
                <?php _e('Search', 'car-sales-plugin'); ?>
            </button>
        </div>
        <div class="search-help">
            <small><?php _e('Danish license plates only (format: AB12345 or AB123456)', 'car-sales-plugin'); ?></small>
        </div>
    </form>

    <div id="search-results" class="search-results" style="display: none;">
        <div class="car-info-card">
            <h3><?php _e('Car Information', 'car-sales-plugin'); ?></h3>
            <div class="car-details" id="car-details">
                <!-- Car details will be populated here -->
            </div>
            
            <div class="contact-form-section">
                <h4><?php _e('Your Contact Information', 'car-sales-plugin'); ?></h4>
                <form id="contact-form" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-name"><?php _e('Full Name', 'car-sales-plugin'); ?> *</label>
                            <input type="text" id="contact-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact-email"><?php _e('Email Address', 'car-sales-plugin'); ?> *</label>
                            <input type="email" id="contact-email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-phone"><?php _e('Phone Number', 'car-sales-plugin'); ?> *</label>
                            <input type="tel" id="contact-phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="asking-price"><?php _e('Asking Price (DKK)', 'car-sales-plugin'); ?></label>
                            <input type="number" id="asking-price" name="price" min="0" step="1000">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="current-mileage"><?php _e('Current Mileage (km)', 'car-sales-plugin'); ?></label>
                            <input type="number" id="current-mileage" name="mileage" min="0">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            <?php _e('Submit Car for Sale', 'car-sales-plugin'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="search-loading" class="search-loading" style="display: none;">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p><?php _e('Looking up your car...', 'car-sales-plugin'); ?></p>
        </div>
    </div>

    <div id="search-error" class="search-error" style="display: none;">
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <p class="error-text"></p>
            <button type="button" onclick="resetSearch()" class="retry-btn">
                <?php _e('Try Again', 'car-sales-plugin'); ?>
            </button>
        </div>
    </div>
</div>

<script>
function resetSearch() {
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('search-error').style.display = 'none';
    document.getElementById('license-plate-search').style.display = 'block';
    document.getElementById('license-plate').value = '';
    document.getElementById('license-plate').focus();
}
</script>
