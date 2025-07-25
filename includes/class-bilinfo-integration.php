<?php

/**
 * Bilinfo.dk integration for car synchronization
 */
class Bilinfo_Integration {

    private $api_key;
    private $api_url;

    public function __construct() {
        $this->api_key = get_option('car_sales_bilinfo_api_key', '');
        $this->api_url = 'https://api.bilinfo.dk/v1/';
    }

    /**
     * Sync approved cars to Bilinfo.dk
     */
    public function sync_approved_cars() {
        $approved_cars = get_posts(array(
            'post_type' => 'car_listing',
            'meta_query' => array(
                array(
                    'key' => '_car_status',
                    'value' => 'approved',
                ),
                array(
                    'key' => '_bilinfo_synced',
                    'value' => 'no',
                ),
            ),
            'posts_per_page' => -1,
        ));

        foreach ($approved_cars as $car) {
            $this->sync_single_car($car->ID);
        }
    }

    /**
     * Sync a single car to Bilinfo.dk
     */
    public function sync_single_car($car_id) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('Bilinfo API key not configured', 'car-sales-plugin'));
        }

        $car_data = $this->prepare_car_data($car_id);
        
        if (is_wp_error($car_data)) {
            return $car_data;
        }

        $url = $this->api_url . 'listings';
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($car_data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (wp_remote_retrieve_response_code($response) !== 200 && wp_remote_retrieve_response_code($response) !== 201) {
            return new WP_Error('bilinfo_error', $data['message'] ?? __('Failed to sync car to Bilinfo', 'car-sales-plugin'));
        }

        // Update car meta to mark as synced
        update_post_meta($car_id, '_bilinfo_synced', 'yes');
        update_post_meta($car_id, '_bilinfo_listing_id', $data['id']);
        
        return $data;
    }

    /**
     * Prepare car data for Bilinfo API
     */
    private function prepare_car_data($car_id) {
        $car_meta = get_post_meta($car_id);
        $car_post = get_post($car_id);
        
        if (!$car_post) {
            return new WP_Error('car_not_found', __('Car listing not found', 'car-sales-plugin'));
        }

        // Get car images
        $images = get_post_meta($car_id, '_car_images', true);
        $image_urls = array();
        
        if ($images) {
            foreach ($images as $image_id) {
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    $image_urls[] = $image_url;
                }
            }
        }

        return array(
            'title' => $car_post->post_title,
            'description' => $car_post->post_content,
            'make' => $car_meta['_car_make'][0] ?? '',
            'model' => $car_meta['_car_model'][0] ?? '',
            'variant' => $car_meta['_car_variant'][0] ?? '',
            'year' => $car_meta['_car_year'][0] ?? '',
            'price' => intval($car_meta['_car_price'][0] ?? 0),
            'mileage' => intval($car_meta['_car_mileage'][0] ?? 0),
            'fuel_type' => $car_meta['_car_fuel_type'][0] ?? '',
            'transmission' => $car_meta['_car_transmission'][0] ?? '',
            'engine_size' => $car_meta['_car_engine_size'][0] ?? '',
            'power_hp' => $car_meta['_car_power_hp'][0] ?? '',
            'doors' => $car_meta['_car_doors'][0] ?? '',
            'seats' => $car_meta['_car_seats'][0] ?? '',
            'color' => $car_meta['_car_color'][0] ?? '',
            'license_plate' => $car_meta['_car_license_plate'][0] ?? '',
            'vin' => $car_meta['_car_vin'][0] ?? '',
            'first_registration' => $car_meta['_car_first_registration'][0] ?? '',
            'images' => $image_urls,
            'contact' => array(
                'name' => $car_meta['_owner_name'][0] ?? '',
                'email' => $car_meta['_owner_email'][0] ?? '',
                'phone' => $car_meta['_owner_phone'][0] ?? '',
            ),
        );
    }

    /**
     * Get listings from Bilinfo.dk
     */
    public function get_bilinfo_listings() {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('Bilinfo API key not configured', 'car-sales-plugin'));
        }

        $url = $this->api_url . 'listings';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('bilinfo_error', $data['message'] ?? __('Failed to retrieve Bilinfo listings', 'car-sales-plugin'));
        }

        return $data;
    }

    /**
     * Import cars from Bilinfo.dk
     */
    public function import_bilinfo_cars() {
        $listings = $this->get_bilinfo_listings();
        
        if (is_wp_error($listings)) {
            return $listings;
        }

        $imported_count = 0;
        
        foreach ($listings['data'] as $listing) {
            // Check if car already exists
            $existing = get_posts(array(
                'post_type' => 'car_listing',
                'meta_query' => array(
                    array(
                        'key' => '_bilinfo_listing_id',
                        'value' => $listing['id'],
                    ),
                ),
                'posts_per_page' => 1,
            ));

            if (empty($existing)) {
                $this->create_car_from_bilinfo($listing);
                $imported_count++;
            }
        }

        return $imported_count;
    }

    /**
     * Create car listing from Bilinfo data
     */
    private function create_car_from_bilinfo($listing) {
        $car_data = array(
            'post_title' => $listing['title'],
            'post_content' => $listing['description'],
            'post_type' => 'car_listing',
            'post_status' => 'publish',
        );

        $car_id = wp_insert_post($car_data);

        if ($car_id) {
            // Update car meta
            update_post_meta($car_id, '_car_make', $listing['make']);
            update_post_meta($car_id, '_car_model', $listing['model']);
            update_post_meta($car_id, '_car_variant', $listing['variant']);
            update_post_meta($car_id, '_car_year', $listing['year']);
            update_post_meta($car_id, '_car_price', $listing['price']);
            update_post_meta($car_id, '_car_mileage', $listing['mileage']);
            update_post_meta($car_id, '_car_fuel_type', $listing['fuel_type']);
            update_post_meta($car_id, '_car_transmission', $listing['transmission']);
            update_post_meta($car_id, '_car_status', 'approved');
            update_post_meta($car_id, '_bilinfo_synced', 'yes');
            update_post_meta($car_id, '_bilinfo_listing_id', $listing['id']);
            update_post_meta($car_id, '_imported_from_bilinfo', 'yes');
        }
    }
}
