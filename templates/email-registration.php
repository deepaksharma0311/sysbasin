<?php
/**
 * Registration email template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$site_name = get_bloginfo('name');
$site_url = home_url();
$dashboard_url = home_url('/my-dashboard');
$temp_password = $temp_password ?? 'your_temporary_password';
$user_email = $contact_data['email'] ?? 'user@example.com';
$user_name = $contact_data['name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(sprintf(__('Welcome to %s', 'car-sales-plugin'), $site_name)); ?></title>
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
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }
        .welcome-title {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .credentials-box {
            background: #f8f9fa;
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-title {
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .credential-item {
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }
        .credential-value {
            color: #2c3e50;
            font-family: monospace;
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #2980b9, #2471a3);
            transform: translateY(-2px);
        }
        .features-list {
            background: #e8f6fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .features-list h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .features-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .features-list li {
            margin-bottom: 8px;
            color: #495057;
        }
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 0.9rem;
        }
        .security-notice strong {
            color: #6c4c00;
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
        .contact-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
        .car-info {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .car-info h3 {
            color: #155724;
            margin-bottom: 15px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .cta-button {
                display: block;
                text-align: center;
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

        <h1 class="welcome-title"><?php printf(__('Welcome, %s!', 'car-sales-plugin'), esc_html($user_name)); ?></h1>

        <div class="content">
            <p><?php _e('Thank you for submitting your car for sale on our platform. Your car listing has been received and is currently under review.', 'car-sales-plugin'); ?></p>

            <p><?php _e('We\'ve created a user account for you to manage your car listings and track inquiries. Here are your login credentials:', 'car-sales-plugin'); ?></p>

            <div class="credentials-box">
                <div class="credentials-title"><?php _e('Your Login Credentials', 'car-sales-plugin'); ?></div>
                <div class="credential-item">
                    <span class="credential-label"><?php _e('Email:', 'car-sales-plugin'); ?></span>
                    <span class="credential-value"><?php echo esc_html($user_email); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label"><?php _e('Password:', 'car-sales-plugin'); ?></span>
                    <span class="credential-value"><?php echo esc_html($temp_password); ?></span>
                </div>
            </div>

            <div class="security-notice">
                <strong><?php _e('Security Notice:', 'car-sales-plugin'); ?></strong>
                <?php _e('Please change your password after logging in for the first time. Your account security is important to us.', 'car-sales-plugin'); ?>
            </div>

            <div style="text-align: center;">
                <a href="<?php echo esc_url($dashboard_url); ?>" class="cta-button">
                    <?php _e('Access Your Dashboard', 'car-sales-plugin'); ?>
                </a>
            </div>

            <div class="features-list">
                <h3><?php _e('What you can do in your dashboard:', 'car-sales-plugin'); ?></h3>
                <ul>
                    <li><?php _e('View the status of your car listing', 'car-sales-plugin'); ?></li>
                    <li><?php _e('Upload up to 8 high-quality images of your car', 'car-sales-plugin'); ?></li>
                    <li><?php _e('Manage inquiries from potential buyers', 'car-sales-plugin'); ?></li>
                    <li><?php _e('Track views and interest in your listing', 'car-sales-plugin'); ?></li>
                    <li><?php _e('Update car information and pricing', 'car-sales-plugin'); ?></li>
                    <li><?php _e('Monitor the approval process', 'car-sales-plugin'); ?></li>
                </ul>
            </div>

            <?php if (isset($car_id) && $car_id): ?>
            <div class="car-info">
                <h3><?php _e('Your Car Listing', 'car-sales-plugin'); ?></h3>
                <?php
                $car_meta = get_post_meta($car_id);
                ?>
                <p><strong><?php _e('Car:', 'car-sales-plugin'); ?></strong> <?php echo esc_html($car_meta['_car_make'][0] ?? ''); ?> <?php echo esc_html($car_meta['_car_model'][0] ?? ''); ?> <?php echo esc_html($car_meta['_car_year'][0] ?? ''); ?></p>
                <p><strong><?php _e('License Plate:', 'car-sales-plugin'); ?></strong> <?php echo esc_html($car_meta['_car_license_plate'][0] ?? ''); ?></p>
                <p><strong><?php _e('Status:', 'car-sales-plugin'); ?></strong> <?php echo esc_html(ucfirst($car_meta['_car_status'][0] ?? 'pending')); ?></p>
            </div>
            <?php endif; ?>

            <h3><?php _e('What happens next?', 'car-sales-plugin'); ?></h3>
            <ol>
                <li><?php _e('Our team will review your car listing within 24-48 hours', 'car-sales-plugin'); ?></li>
                <li><?php _e('Once approved, your car will be published on our platform', 'car-sales-plugin'); ?></li>
                <li><?php _e('Your listing will also be synchronized with Bilinfo.dk', 'car-sales-plugin'); ?></li>
                <li><?php _e('You\'ll receive inquiries directly in your dashboard', 'car-sales-plugin'); ?></li>
                <li><?php _e('We\'ll notify you via email of any new inquiries', 'car-sales-plugin'); ?></li>
            </ol>

            <div class="contact-info">
                <h4><?php _e('Need Help?', 'car-sales-plugin'); ?></h4>
                <p><?php _e('If you have any questions or need assistance, please don\'t hesitate to contact us:', 'car-sales-plugin'); ?></p>
                <p>
                    <strong><?php _e('Email:', 'car-sales-plugin'); ?></strong> 
                    <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>"><?php echo esc_html(get_option('admin_email')); ?></a>
                </p>
                <?php if ($support_phone = get_option('car_sales_support_phone')): ?>
                <p>
                    <strong><?php _e('Phone:', 'car-sales-plugin'); ?></strong> 
                    <a href="tel:<?php echo esc_attr($support_phone); ?>"><?php echo esc_html($support_phone); ?></a>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <p><?php printf(__('This email was sent from %s', 'car-sales-plugin'), '<a href="' . esc_url($site_url) . '">' . esc_html($site_name) . '</a>'); ?></p>
            <p><?php _e('You are receiving this email because you submitted a car for sale on our platform.', 'car-sales-plugin'); ?></p>
            <p><small><?php printf(__('© %s %s. All rights reserved.', 'car-sales-plugin'), date('Y'), esc_html($site_name)); ?></small></p>
        </div>
    </div>
</body>
</html>
