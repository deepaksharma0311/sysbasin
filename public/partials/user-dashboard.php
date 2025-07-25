<?php
/**
 * User dashboard template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
?>

<div class="user-dashboard">
    <div class="dashboard-header">
        <h2><?php printf(__('Welcome, %s', 'car-sales-plugin'), esc_html($current_user->display_name)); ?></h2>
        <p><?php _e('Manage your car listings and track inquiries', 'car-sales-plugin'); ?></p>
    </div>

    <div class="dashboard-nav">
        <button class="tab-btn active" data-tab="my-cars">
            <i class="fas fa-car"></i>
            <?php _e('My Cars', 'car-sales-plugin'); ?>
        </button>
        <button class="tab-btn" data-tab="inquiries">
            <i class="fas fa-envelope"></i>
            <?php _e('Inquiries', 'car-sales-plugin'); ?>
        </button>
        <button class="tab-btn" data-tab="add-car">
            <i class="fas fa-plus"></i>
            <?php _e('Add New Car', 'car-sales-plugin'); ?>
        </button>
    </div>

    <div id="my-cars" class="tab-content active">
        <h3><?php _e('Your Car Listings', 'car-sales-plugin'); ?></h3>
        <div id="cars-list" class="cars-grid">
            <div class="loading-cars">
                <i class="fas fa-spinner fa-spin"></i>
                <p><?php _e('Loading your cars...', 'car-sales-plugin'); ?></p>
            </div>
        </div>
    </div>

    <div id="inquiries" class="tab-content">
        <h3><?php _e('Car Inquiries', 'car-sales-plugin'); ?></h3>
        <div class="inquiry-filters">
            <select id="car-filter">
                <option value=""><?php _e('All Cars', 'car-sales-plugin'); ?></option>
            </select>
            <select id="status-filter">
                <option value=""><?php _e('All Status', 'car-sales-plugin'); ?></option>
                <option value="new"><?php _e('New', 'car-sales-plugin'); ?></option>
                <option value="contacted"><?php _e('Contacted', 'car-sales-plugin'); ?></option>
                <option value="closed"><?php _e('Closed', 'car-sales-plugin'); ?></option>
            </select>
        </div>
        <div id="inquiries-list" class="inquiries-list">
            <p><?php _e('Select a car to view inquiries', 'car-sales-plugin'); ?></p>
        </div>
    </div>

    <div id="add-car" class="tab-content">
        <h3><?php _e('Add New Car', 'car-sales-plugin'); ?></h3>
        <p><?php _e('Use our license plate lookup to quickly add your car:', 'car-sales-plugin'); ?></p>
        <a href="<?php echo esc_url(home_url('/car-search')); ?>" class="add-car-btn">
            <i class="fas fa-search"></i>
            <?php _e('Start License Plate Search', 'car-sales-plugin'); ?>
        </a>
    </div>
</div>

<!-- Car Details Modal -->
<div id="car-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title"></h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="car-details-tabs">
                <button class="modal-tab-btn active" data-tab="details">
                    <?php _e('Details', 'car-sales-plugin'); ?>
                </button>
                <button class="modal-tab-btn" data-tab="images">
                    <?php _e('Images', 'car-sales-plugin'); ?>
                </button>
                <button class="modal-tab-btn" data-tab="inquiries-detail">
                    <?php _e('Inquiries', 'car-sales-plugin'); ?>
                </button>
            </div>
            
            <div id="details" class="modal-tab-content active">
                <div id="car-detail-info"></div>
            </div>
            
            <div id="images" class="modal-tab-content">
                <div class="image-upload-section">
                    <h4><?php _e('Car Images', 'car-sales-plugin'); ?></h4>
                    <div id="current-images" class="current-images"></div>
                    <div class="upload-area">
                        <input type="file" id="image-upload" multiple accept="image/*" style="display: none;">
                        <button type="button" onclick="document.getElementById('image-upload').click()" class="upload-btn">
                            <i class="fas fa-upload"></i>
                            <?php _e('Upload Images', 'car-sales-plugin'); ?>
                        </button>
                        <p><small><?php printf(__('Maximum %d images allowed', 'car-sales-plugin'), get_option('car_sales_max_images', 8)); ?></small></p>
                    </div>
                </div>
            </div>
            
            <div id="inquiries-detail" class="modal-tab-content">
                <div id="car-inquiries-list"></div>
            </div>
        </div>
    </div>
</div>
