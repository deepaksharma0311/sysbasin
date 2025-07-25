<?php
/**
 * Car inquiry notification email template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$site_name = get_bloginfo('name');
$site_url = home_url();
$admin_url = admin_url('admin.php?page=car-sales-inquiries');
$car_url = get_permalink($car_id ?? 0);

// Extract variables with defaults
$inquiry_data = $inquiry_data ?? array();
$car_data = $car_data ?? array();
$customer_name = $inquiry_data['name'] ?? 'Unknown Customer';
$customer_email = $inquiry_data['email'] ?? 'unknown@email.com';
$customer_phone = $inquiry_data['phone'] ?? 'Not provided';
$inquiry_message = $inquiry_data['message'] ?? '';
$car_title = $car_data['title'] ?? 'Unknown Car';
$car_make = $car_data['make'] ?? '';
$car_model = $car_data['model'] ?? '';
$car_year = $car_data['year'] ?? '';
$car_price = $car_data['price'] ?? '';
$license_plate = $car_data['license_plate'] ?? '';
?>

<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(sprintf(__('New Car Inquiry - %s', 'car-sales-plugin'), $site_name)); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .alert-title {
            color: #c0392b;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .alert-icon {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .inquiry-summary {
            background: #fff5f5;
            border: 2px solid #e74c3c;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .inquiry-summary h3 {
            color: #c0392b;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .car-details {
            background: #f8f9fa;
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .car-details h3 {
            color: #2980b9;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .car-icon {
            background: #3498db;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            width: 140px;
            flex-shrink: 0;
        }
        .detail-value {
            color: #2c3e50;
            flex: 1;
        }
        .customer-info {
            background: #e8f6fd;
            border: 2px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .customer-info h3 {
            color: #0c5460;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .contact-button {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 5px 10px 5px 0;
            transition: background 0.3s ease;
        }
        .contact-button:hover {
            background: #218838;
        }
        .contact-button.phone {
            background: #17a2b8;
        }
        .contact-button.phone:hover {
            background: #138496;
        }
        .message-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .message-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        .message-content {
            color: #6c4c00;
            font-style: italic;
            white-space: pre-wrap;
        }
        .cta-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
        }
        .cta-button.secondary {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        .cta-button.secondary:hover {
            background: linear-gradient(135deg, #2980b9, #2471a3);
        }
        .stats-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-item {
            background: white;
            padding: 15px 10px;
            border-radius: 6px;
            border-top: 3px solid #3498db;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .urgent-notice {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        .footer {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .footer a {
            color: #3498db;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            .detail-label {
                width: auto;
            }
            .cta-button {
                display: block;
                margin: 10px 0;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo"><?php echo esc_html($site_name); ?></div>
            <p><?php _e('Car Sales Platform', 'car-sales-plugin'); ?></p>
        </div>

        <h1 class="alert-title">
            <span class="alert-icon">!</span>
            <?php _e('New Car Inquiry Received', 'car-sales-plugin'); ?>
        </h1>

        <div class="urgent-notice">
            <?php _e('A potential buyer has shown interest in one of your listed cars. Respond quickly to increase your chances of making a sale!', 'car-sales-plugin'); ?>
        </div>

        <div class="inquiry-summary">
            <h3><?php _e('Inquiry Summary', 'car-sales-plugin'); ?></h3>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Received:', 'car-sales-plugin'); ?></span>
                <span class="detail-value"><?php echo esc_html(date('F j, Y \a\t g:i A')); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Customer:', 'car-sales-plugin'); ?></span>
                <span class="detail-value"><?php echo esc_html($customer_name); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Contact:', 'car-sales-plugin'); ?></span>
                <span class="detail-value">
                    <a href="mailto:<?php echo esc_attr($customer_email); ?>" class="contact-button">
                        <?php echo esc_html($customer_email); ?>
                    </a>
                    <?php if ($customer_phone && $customer_phone !== 'Not provided'): ?>
                    <a href="tel:<?php echo esc_attr($customer_phone); ?>" class="contact-button phone">
                        <?php echo esc_html($customer_phone); ?>
                    </a>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="car-details">
            <h3>
                <span class="car-icon">🚗</span>
                <?php _e('Car Information', 'car-sales-plugin'); ?>
            </h3>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Vehicle:', 'car-sales-plugin'); ?></span>
                <span class="detail-value"><?php echo esc_html("$car_make $car_model $car_year"); ?></span>
            </div>
            <?php if ($license_plate): ?>
            <div class="detail-row">
                <span class="detail-label"><?php _e('License Plate:', 'car-sales-plugin'); ?></span>
                <span class="detail-value"><?php echo esc_html($license_plate); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($car_price): ?>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Asking Price:', 'car-sales-plugin'); ?></span>
                <span class="detail-value"><?php echo esc_html(number_format($car_price, 0, ',', '.')); ?> DKK</span>
            </div>
            <?php endif; ?>
            <?php if ($car_url): ?>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Listing:', 'car-sales-plugin'); ?></span>
                <span class="detail-value">
                    <a href="<?php echo esc_url($car_url); ?>" target="_blank">
                        <?php _e('View Car Listing', 'car-sales-plugin'); ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($inquiry_message)): ?>
        <div class="message-box">
            <h4><?php _e('Customer Message:', 'car-sales-plugin'); ?></h4>
            <div class="message-content"><?php echo esc_html($inquiry_message); ?></div>
        </div>
        <?php endif; ?>

        <div class="customer-info">
            <h3>
                <span class="car-icon">👤</span>
                <?php _e('Customer Contact Information', 'car-sales-plugin'); ?>
            </h3>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Full Name:', 'car-sales-plugin'); ?></span>
                <span class="detail-value"><?php echo esc_html($customer_name); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Email Address:', 'car-sales-plugin'); ?></span>
                <span class="detail-value">
                    <a href="mailto:<?php echo esc_attr($customer_email); ?>?subject=<?php echo esc_attr(sprintf(__('Re: Your inquiry about %s', 'car-sales-plugin'), $car_title)); ?>">
                        <?php echo esc_html($customer_email); ?>
                    </a>
                </span>
            </div>
            <?php if ($customer_phone && $customer_phone !== 'Not provided'): ?>
            <div class="detail-row">
                <span class="detail-label"><?php _e('Phone Number:', 'car-sales-plugin'); ?></span>
                <span class="detail-value">
                    <a href="tel:<?php echo esc_attr($customer_phone); ?>"><?php echo esc_html($customer_phone); ?></a>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <div class="stats-box">
            <h3><?php _e('Response Time Statistics', 'car-sales-plugin'); ?></h3>
            <p><?php _e('Quick responses lead to better sales results:', 'car-sales-plugin'); ?></p>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">85%</div>
                    <div class="stat-label"><?php _e('of sales happen within', 'car-sales-plugin'); ?></div>
                    <div class="stat-label"><?php _e('24 hours', 'car-sales-plugin'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">60%</div>
                    <div class="stat-label"><?php _e('respond within', 'car-sales-plugin'); ?></div>
                    <div class="stat-label"><?php _e('1 hour', 'car-sales-plugin'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">3x</div>
                    <div class="stat-label"><?php _e('higher chance', 'car-sales-plugin'); ?></div>
                    <div class="stat-label"><?php _e('with quick response', 'car-sales-plugin'); ?></div>
                </div>
            </div>
        </div>

        <div class="cta-buttons">
            <h3><?php _e('Quick Actions', 'car-sales-plugin'); ?></h3>
            <a href="mailto:<?php echo esc_attr($customer_email); ?>?subject=<?php echo esc_attr(sprintf(__('Re: Your inquiry about %s %s %s', 'car-sales-plugin'), $car_make, $car_model, $car_year)); ?>&body=<?php echo esc_attr(sprintf(__('Dear %s,\n\nThank you for your interest in my %s %s %s.\n\nI would be happy to answer any questions you may have.\n\nBest regards', 'car-sales-plugin'), $customer_name, $car_make, $car_model, $car_year)); ?>" class="cta-button">
                <?php _e('Reply via Email', 'car-sales-plugin'); ?>
            </a>
            
            <?php if ($customer_phone && $customer_phone !== 'Not provided'): ?>
            <a href="tel:<?php echo esc_attr($customer_phone); ?>" class="cta-button secondary">
                <?php _e('Call Customer', 'car-sales-plugin'); ?>
            </a>
            <?php endif; ?>
        </div>

        <div style="background: #e8f6fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h4 style="color: #0c5460; margin-bottom: 15px;"><?php _e('Tips for a Successful Response:', 'car-sales-plugin'); ?></h4>
            <ul style="color: #495057; margin: 0; padding-left: 20px;">
                <li><?php _e('Respond within 1-2 hours if possible', 'car-sales-plugin'); ?></li>
                <li><?php _e('Be friendly and professional in your communication', 'car-sales-plugin'); ?></li>
                <li><?php _e('Provide additional photos or information if requested', 'car-sales-plugin'); ?></li>
                <li><?php _e('Be flexible with viewing appointments', 'car-sales-plugin'); ?></li>
                <li><?php _e('Answer all questions thoroughly and honestly', 'car-sales-plugin'); ?></li>
            </ul>
        </div>

        <div class="footer">
            <p><?php printf(__('This notification was sent from %s car sales platform.', 'car-sales-plugin'), '<a href="' . esc_url($site_url) . '">' . esc_html($site_name) . '</a>'); ?></p>
            <p><?php _e('You are receiving this email because you have a car listed on our platform.', 'car-sales-plugin'); ?></p>
            <?php if ($admin_url): ?>
            <p><a href="<?php echo esc_url($admin_url); ?>"><?php _e('Manage all inquiries in admin panel', 'car-sales-plugin'); ?></a></p>
            <?php endif; ?>
            <p><small><?php printf(__('© %s %s. All rights reserved.', 'car-sales-plugin'), date('Y'), esc_html($site_name)); ?></small></p>
        </div>
    </div>
</body>
</html>
