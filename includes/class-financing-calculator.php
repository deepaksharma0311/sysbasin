<?php

/**
 * Financing calculator functionality
 */
class Financing_Calculator {

    public function __construct() {
        add_action('wp_ajax_calculate_financing', array($this, 'calculate_financing'));
        add_action('wp_ajax_nopriv_calculate_financing', array($this, 'calculate_financing'));
        add_action('wp_ajax_submit_financing_inquiry', array($this, 'submit_financing_inquiry'));
        add_action('wp_ajax_nopriv_submit_financing_inquiry', array($this, 'submit_financing_inquiry'));
    }

    /**
     * Calculate financing options
     */
    public function calculate_financing() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        $car_price = floatval($_POST['car_price']);
        $down_payment = floatval($_POST['down_payment']);
        $loan_term = intval($_POST['loan_term']); // months
        $interest_rate = floatval($_POST['interest_rate']) / 100; // convert percentage to decimal

        if ($car_price <= 0 || $loan_term <= 0) {
            wp_send_json_error(__('Invalid calculation parameters', 'car-sales-plugin'));
        }

        $loan_amount = $car_price - $down_payment;
        
        if ($loan_amount <= 0) {
            wp_send_json_success(array(
                'monthly_payment' => 0,
                'total_payment' => $car_price,
                'total_interest' => 0,
                'loan_amount' => 0,
            ));
        }

        $monthly_rate = $interest_rate / 12;
        
        if ($monthly_rate > 0) {
            $monthly_payment = $loan_amount * ($monthly_rate * pow(1 + $monthly_rate, $loan_term)) / (pow(1 + $monthly_rate, $loan_term) - 1);
        } else {
            $monthly_payment = $loan_amount / $loan_term;
        }

        $total_payment = $monthly_payment * $loan_term + $down_payment;
        $total_interest = $total_payment - $car_price;

        wp_send_json_success(array(
            'monthly_payment' => round($monthly_payment, 2),
            'total_payment' => round($total_payment, 2),
            'total_interest' => round($total_interest, 2),
            'loan_amount' => round($loan_amount, 2),
        ));
    }

    /**
     * Submit financing inquiry
     */
    public function submit_financing_inquiry() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $car_price = floatval($_POST['car_price']);
        $down_payment = floatval($_POST['down_payment']);
        $loan_term = intval($_POST['loan_term']);
        $monthly_payment = floatval($_POST['monthly_payment']);
        $message = sanitize_textarea_field($_POST['message']);

        // Validation
        if (empty($name) || empty($email) || empty($phone)) {
            wp_send_json_error(__('Please fill in all required fields', 'car-sales-plugin'));
        }

        if (!is_email($email)) {
            wp_send_json_error(__('Please enter a valid email address', 'car-sales-plugin'));
        }

        // Save inquiry to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'financing_inquiries';
        
        // Create table if it doesn't exist
        $this->create_financing_table();

        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'car_price' => $car_price,
                'down_payment' => $down_payment,
                'loan_term' => $loan_term,
                'monthly_payment' => $monthly_payment,
                'message' => $message,
                'inquiry_date' => current_time('mysql'),
                'status' => 'new',
            ),
            array('%s', '%s', '%s', '%f', '%f', '%d', '%f', '%s', '%s', '%s')
        );

        if ($result === false) {
            wp_send_json_error(__('Failed to save inquiry', 'car-sales-plugin'));
        }

        // Send notification email
        $this->send_financing_notification($name, $email, $phone, $car_price, $monthly_payment, $message);

        wp_send_json_success(__('Your financing inquiry has been submitted successfully. We will contact you soon.', 'car-sales-plugin'));
    }

    /**
     * Create financing inquiries table
     */
    private function create_financing_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'financing_inquiries';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            car_price decimal(10,2) NOT NULL,
            down_payment decimal(10,2) NOT NULL,
            loan_term int NOT NULL,
            monthly_payment decimal(10,2) NOT NULL,
            message text,
            inquiry_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'new',
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Send financing notification email
     */
    private function send_financing_notification($name, $email, $phone, $car_price, $monthly_payment, $message) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        $subject = sprintf(__('[%s] New Financing Inquiry from %s', 'car-sales-plugin'), $site_name, $name);
        
        $body = sprintf(
            __("New financing inquiry received:\n\nName: %s\nEmail: %s\nPhone: %s\nCar Price: DKK %s\nMonthly Payment: DKK %s\n\nMessage:\n%s", 'car-sales-plugin'),
            $name,
            $email,
            $phone,
            number_format($car_price, 2),
            number_format($monthly_payment, 2),
            $message
        );

        wp_mail($admin_email, $subject, $body);

        // Send confirmation email to customer
        $customer_subject = sprintf(__('[%s] Financing Inquiry Received', 'car-sales-plugin'), $site_name);
        $customer_body = sprintf(
            __("Dear %s,\n\nThank you for your financing inquiry. We have received your request and will contact you soon.\n\nYour inquiry details:\nCar Price: DKK %s\nEstimated Monthly Payment: DKK %s\n\nBest regards,\n%s", 'car-sales-plugin'),
            $name,
            number_format($car_price, 2),
            number_format($monthly_payment, 2),
            $site_name
        );

        wp_mail($email, $customer_subject, $customer_body);
    }

    /**
     * Get default interest rates
     */
    public function get_default_rates() {
        return array(
            '12' => 3.5,  // 1 year
            '24' => 4.0,  // 2 years
            '36' => 4.5,  // 3 years
            '48' => 5.0,  // 4 years
            '60' => 5.5,  // 5 years
            '72' => 6.0,  // 6 years
            '84' => 6.5,  // 7 years
        );
    }
}

new Financing_Calculator();
