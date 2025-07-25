<?php
/**
 * Provide an admin area view for the plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get pending cars
$pending_cars = get_posts(array(
    'post_type' => 'car_listing',
    'meta_query' => array(
        array(
            'key' => '_car_status',
            'value' => 'pending',
        ),
    ),
    'posts_per_page' => -1,
));

// Get recent inquiries
$inquiry_table = $wpdb->prefix . 'car_inquiries';
$recent_inquiries = $wpdb->get_results("
    SELECT i.*, p.post_title as car_title 
    FROM $inquiry_table i 
    LEFT JOIN {$wpdb->posts} p ON i.car_id = p.ID 
    ORDER BY i.inquiry_date DESC 
    LIMIT 10
");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="car-sales-dashboard">
        <div class="dashboard-section">
            <h2><?php _e('Pending Approvals', 'car-sales-plugin'); ?></h2>
            
            <?php if (empty($pending_cars)): ?>
                <p><?php _e('No cars pending approval.', 'car-sales-plugin'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Car', 'car-sales-plugin'); ?></th>
                            <th><?php _e('Owner', 'car-sales-plugin'); ?></th>
                            <th><?php _e('License Plate', 'car-sales-plugin'); ?></th>
                            <th><?php _e('Date Submitted', 'car-sales-plugin'); ?></th>
                            <th><?php _e('Actions', 'car-sales-plugin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_cars as $car): 
                            $meta = get_post_meta($car->ID);
                            $author = get_userdata($car->post_author);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($car->post_title); ?></strong><br>
                                <small><?php echo esc_html($meta['_car_make'][0] ?? ''); ?> <?php echo esc_html($meta['_car_model'][0] ?? ''); ?></small>
                            </td>
                            <td>
                                <?php echo esc_html($meta['_owner_name'][0] ?? $author->display_name); ?><br>
                                <small><a href="mailto:<?php echo esc_attr($meta['_owner_email'][0] ?? $author->user_email); ?>"><?php echo esc_html($meta['_owner_email'][0] ?? $author->user_email); ?></a></small>
                            </td>
                            <td><?php echo esc_html($meta['_car_license_plate'][0] ?? ''); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($car->post_date))); ?></td>
                            <td>
                                <button type="button" class="button button-primary approve-car" data-car-id="<?php echo esc_attr($car->ID); ?>">
                                    <?php _e('Approve', 'car-sales-plugin'); ?>
                                </button>
                                <a href="<?php echo esc_url(get_edit_post_link($car->ID)); ?>" class="button">
                                    <?php _e('View/Edit', 'car-sales-plugin'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h2><?php _e('Recent Inquiries', 'car-sales-plugin'); ?></h2>
            
            <?php if (empty($recent_inquiries)): ?>
                <p><?php _e('No recent inquiries.', 'car-sales-plugin'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'car-sales-plugin'); ?></th>
                            <th><?php _e('Car', 'car-sales-plugin'); ?></th>
                            <th><?php _e('Customer', 'car-sales-plugin'); ?></th>
                            <th><?php _e('Contact', 'car-sales-plugin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_inquiries as $inquiry): ?>
                        <tr>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($inquiry->inquiry_date))); ?></td>
                            <td><?php echo esc_html($inquiry->car_title); ?></td>
                            <td><?php echo esc_html($inquiry->name); ?></td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($inquiry->email); ?>"><?php echo esc_html($inquiry->email); ?></a><br>
                                <a href="tel:<?php echo esc_attr($inquiry->phone); ?>"><?php echo esc_html($inquiry->phone); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h2><?php _e('Quick Actions', 'car-sales-plugin'); ?></h2>
            <p>
                <button type="button" class="button button-secondary" id="sync-bilinfo">
                    <?php _e('Sync with Bilinfo.dk', 'car-sales-plugin'); ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=car-sales-settings')); ?>" class="button">
                    <?php _e('Settings', 'car-sales-plugin'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=car-sales-analytics')); ?>" class="button">
                    <?php _e('View Analytics', 'car-sales-plugin'); ?>
                </a>
            </p>
        </div>
    </div>
</div>
