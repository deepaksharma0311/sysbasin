<?php

/**
 * The public-facing functionality of the plugin
 */
class Car_Sales_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, CAR_SALES_PLUGIN_URL . 'public/css/car-sales-public.css', array(), $this->version, 'all');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    }

    /**
     * Register the JavaScript for the public-facing side
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, CAR_SALES_PLUGIN_URL . 'public/js/car-sales-public.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'car_sales_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('car_sales_nonce'),
            'strings' => array(
                'searching' => __('Searching...', 'car-sales-plugin'),
                'error' => __('An error occurred', 'car-sales-plugin'),
                'success' => __('Success', 'car-sales-plugin'),
                'confirm_delete' => __('Are you sure you want to delete this image?', 'car-sales-plugin'),
            ),
        ));
    }

    /**
     * Car search form shortcode
     */
    public function car_search_form_shortcode($atts) {
        ob_start();
        include CAR_SALES_PLUGIN_PATH . 'public/partials/license-plate-search.php';
        return ob_get_clean();
    }

    /**
     * User dashboard shortcode
     */
    public function user_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to access your dashboard.', 'car-sales-plugin') . '</p>';
        }
        
        ob_start();
        include CAR_SALES_PLUGIN_PATH . 'public/partials/user-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Financing calculator shortcode
     */
    public function financing_calculator_shortcode($atts) {
        ob_start();
        include CAR_SALES_PLUGIN_PATH . 'public/partials/financing-calculator.php';
        return ob_get_clean();
    }

    /**
     * License plate lookup AJAX handler
     */
    public function lookup_license_plate() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        $license_plate = sanitize_text_field($_POST['license_plate']);
        
        if (empty($license_plate)) {
            wp_send_json_error(__('Please enter a license plate', 'car-sales-plugin'));
        }

        $registry = new Danish_Motor_Registry();
        $car_data = $registry->lookup_by_license_plate($license_plate);

        if (is_wp_error($car_data)) {
            wp_send_json_error($car_data->get_error_message());
        }

        // Also get Synsbasen data
        $synsbasen_data = $registry->get_synsbasen_data($license_plate);
        if (!is_wp_error($synsbasen_data)) {
            $car_data['synsbasen'] = $synsbasen_data;
        }

        wp_send_json_success($car_data);
    }

    /**
     * Submit car inquiry AJAX handler
     */
    public function submit_car_inquiry() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        $car_data = array(
            'license_plate' => sanitize_text_field($_POST['license_plate']),
            'make' => sanitize_text_field($_POST['make']),
            'model' => sanitize_text_field($_POST['model']),
            'year' => sanitize_text_field($_POST['year']),
            'price' => floatval($_POST['price']),
            'mileage' => intval($_POST['mileage']),
        );

        $contact_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
        );

        // Validation
        if (empty($contact_data['name']) || empty($contact_data['email']) || empty($contact_data['phone'])) {
            wp_send_json_error(__('Please fill in all contact fields', 'car-sales-plugin'));
        }

        if (!is_email($contact_data['email'])) {
            wp_send_json_error(__('Please enter a valid email address', 'car-sales-plugin'));
        }

        // Create user account or get existing user
        $user_id = $this->create_or_get_user($contact_data);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        }

        // Create car listing
        $car_id = $this->create_car_listing($car_data, $contact_data, $user_id);
        
        if (is_wp_error($car_id)) {
            wp_send_json_error($car_id->get_error_message());
        }

        // Send registration email
        $this->send_registration_email($contact_data, $car_id);

        wp_send_json_success(array(
            'message' => __('Your car has been submitted for review. You will receive an email shortly.', 'car-sales-plugin'),
            'car_id' => $car_id,
        ));
    }

    /**
     * Create or get existing user
     */
    private function create_or_get_user($contact_data) {
        $existing_user = get_user_by('email', $contact_data['email']);
        
        if ($existing_user) {
            return $existing_user->ID;
        }

        // Create new user
        $username = sanitize_user($contact_data['email']);
        $password = wp_generate_password();

        $user_id = wp_create_user($username, $password, $contact_data['email']);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $contact_data['name'],
            'first_name' => $contact_data['name'],
        ));

        update_user_meta($user_id, 'phone', $contact_data['phone']);
        update_user_meta($user_id, 'car_sales_temp_password', $password);

        return $user_id;
    }

    /**
     * Create car listing
     */
    private function create_car_listing($car_data, $contact_data, $user_id) {
        $car_title = sprintf('%s %s %s', $car_data['make'], $car_data['model'], $car_data['year']);
        
        $post_data = array(
            'post_title' => $car_title,
            'post_type' => 'car_listing',
            'post_status' => 'publish',
            'post_author' => $user_id,
        );

        $car_id = wp_insert_post($post_data);
        
        if (!$car_id) {
            return new WP_Error('car_creation_failed', __('Failed to create car listing', 'car-sales-plugin'));
        }

        // Update car meta
        foreach ($car_data as $key => $value) {
            update_post_meta($car_id, '_car_' . $key, $value);
        }

        // Update owner contact info
        foreach ($contact_data as $key => $value) {
            update_post_meta($car_id, '_owner_' . $key, $value);
        }

        // Set initial status
        $auto_approve = get_option('car_sales_auto_approve', false);
        $status = $auto_approve ? 'approved' : 'pending';
        update_post_meta($car_id, '_car_status', $status);
        update_post_meta($car_id, '_bilinfo_synced', 'no');

        return $car_id;
    }

    /**
     * Send registration email
     */
    private function send_registration_email($contact_data, $car_id) {
        $user = get_user_by('email', $contact_data['email']);
        $temp_password = get_user_meta($user->ID, 'car_sales_temp_password', true);
        
        if ($temp_password) {
            $subject = sprintf(__('[%s] Complete Your Registration', 'car-sales-plugin'), get_bloginfo('name'));
            
            ob_start();
            include CAR_SALES_PLUGIN_PATH . 'templates/email-registration.php';
            $message = ob_get_clean();

            wp_mail($contact_data['email'], $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
            
            // Clear temp password
            delete_user_meta($user->ID, 'car_sales_temp_password');
        }
    }

    /**
     * Upload car images AJAX handler
     */
    public function upload_car_images() {
        $dashboard = new User_Dashboard();
        $dashboard->upload_car_images();
    }

    /**
     * Calculate financing AJAX handler
     */
    public function calculate_financing() {
        $calculator = new Financing_Calculator();
        $calculator->calculate_financing();
    }
}
