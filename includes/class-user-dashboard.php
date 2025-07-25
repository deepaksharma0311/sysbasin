<?php

/**
 * User dashboard functionality
 */
class User_Dashboard {

    public function __construct() {
        add_action('wp_ajax_get_user_cars', array($this, 'get_user_cars'));
        add_action('wp_ajax_get_car_inquiries', array($this, 'get_car_inquiries'));
        add_action('wp_ajax_upload_car_images', array($this, 'upload_car_images'));
        add_action('wp_ajax_delete_car_image', array($this, 'delete_car_image'));
    }

    /**
     * Get cars for current user
     */
    public function get_user_cars() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'car-sales-plugin'));
        }

        $user_id = get_current_user_id();
        
        $cars = get_posts(array(
            'post_type' => 'car_listing',
            'author' => $user_id,
            'posts_per_page' => -1,
            'meta_key' => '_car_status',
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        $car_data = array();
        
        foreach ($cars as $car) {
            $meta = get_post_meta($car->ID);
            $inquiry_count = $this->get_car_inquiry_count($car->ID);
            
            $car_data[] = array(
                'id' => $car->ID,
                'title' => $car->post_title,
                'status' => $meta['_car_status'][0] ?? 'pending',
                'make' => $meta['_car_make'][0] ?? '',
                'model' => $meta['_car_model'][0] ?? '',
                'year' => $meta['_car_year'][0] ?? '',
                'price' => $meta['_car_price'][0] ?? '',
                'mileage' => $meta['_car_mileage'][0] ?? '',
                'license_plate' => $meta['_car_license_plate'][0] ?? '',
                'inquiry_count' => $inquiry_count,
                'images' => $this->get_car_images($car->ID),
                'created_date' => $car->post_date,
                'bilinfo_synced' => $meta['_bilinfo_synced'][0] ?? 'no',
            );
        }

        wp_send_json_success($car_data);
    }

    /**
     * Get inquiries for a specific car
     */
    public function get_car_inquiries() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'car-sales-plugin'));
        }

        $car_id = intval($_POST['car_id']);
        $user_id = get_current_user_id();
        
        // Verify user owns this car
        $car = get_post($car_id);
        if (!$car || $car->post_author != $user_id) {
            wp_send_json_error(__('Access denied', 'car-sales-plugin'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'car_inquiries';
        
        $inquiries = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE car_id = %d ORDER BY inquiry_date DESC",
            $car_id
        ));

        wp_send_json_success($inquiries);
    }

    /**
     * Upload car images
     */
    public function upload_car_images() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'car-sales-plugin'));
        }

        $car_id = intval($_POST['car_id']);
        $user_id = get_current_user_id();
        
        // Verify user owns this car
        $car = get_post($car_id);
        if (!$car || $car->post_author != $user_id) {
            wp_send_json_error(__('Access denied', 'car-sales-plugin'));
        }

        if (empty($_FILES['images'])) {
            wp_send_json_error(__('No images uploaded', 'car-sales-plugin'));
        }

        $max_images = get_option('car_sales_max_images', 8);
        $current_images = get_post_meta($car_id, '_car_images', true) ?: array();
        
        if (count($current_images) >= $max_images) {
            wp_send_json_error(sprintf(__('Maximum %d images allowed', 'car-sales-plugin'), $max_images));
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $uploaded_images = array();
        $files = $_FILES['images'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if (count($current_images) + count($uploaded_images) >= $max_images) {
                break;
            }

            $file = array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            );

            $attachment_id = media_handle_sideload($file, $car_id);
            
            if (!is_wp_error($attachment_id)) {
                $current_images[] = $attachment_id;
                $uploaded_images[] = array(
                    'id' => $attachment_id,
                    'url' => wp_get_attachment_url($attachment_id),
                );
            }
        }

        update_post_meta($car_id, '_car_images', $current_images);
        
        wp_send_json_success(array(
            'uploaded' => $uploaded_images,
            'total_images' => count($current_images),
        ));
    }

    /**
     * Delete car image
     */
    public function delete_car_image() {
        check_ajax_referer('car_sales_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'car-sales-plugin'));
        }

        $car_id = intval($_POST['car_id']);
        $image_id = intval($_POST['image_id']);
        $user_id = get_current_user_id();
        
        // Verify user owns this car
        $car = get_post($car_id);
        if (!$car || $car->post_author != $user_id) {
            wp_send_json_error(__('Access denied', 'car-sales-plugin'));
        }

        $current_images = get_post_meta($car_id, '_car_images', true) ?: array();
        $updated_images = array_filter($current_images, function($id) use ($image_id) {
            return $id != $image_id;
        });

        update_post_meta($car_id, '_car_images', array_values($updated_images));
        wp_delete_attachment($image_id, true);

        wp_send_json_success(array(
            'remaining_images' => count($updated_images),
        ));
    }

    /**
     * Get inquiry count for a car
     */
    private function get_car_inquiry_count($car_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'car_inquiries';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE car_id = %d",
            $car_id
        ));
    }

    /**
     * Get car images
     */
    private function get_car_images($car_id) {
        $image_ids = get_post_meta($car_id, '_car_images', true);
        $images = array();
        
        if ($image_ids) {
            foreach ($image_ids as $image_id) {
                $images[] = array(
                    'id' => $image_id,
                    'url' => wp_get_attachment_url($image_id),
                    'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                );
            }
        }
        
        return $images;
    }
}

new User_Dashboard();
