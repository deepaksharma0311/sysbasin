<?php

/**
 * Synsbasen API integration for Danish vehicle data
 */
class Synsbasen_Integration {

    private $api_key;
    private $api_url;

    public function __construct() {
        $this->api_key = get_option('car_sales_synsbasen_api_key', '');
        $this->api_url = 'https://api.synsbasen.dk/v1/';
        
        add_action('wp_ajax_lookup_license_plate', array($this, 'ajax_lookup_license_plate'));
        add_action('wp_ajax_nopriv_lookup_license_plate', array($this, 'ajax_lookup_license_plate'));
    }

    /**
     * Look up car data by license plate using Synsbasen API
     */
    public function lookup_by_license_plate($license_plate) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('Synsbasen API key not configured', 'car-sales-plugin'));
        }

        $license_plate = sanitize_text_field($license_plate);
        $license_plate = strtoupper(trim($license_plate));

        // Validate Danish license plate format
        if (!$this->validate_license_plate($license_plate)) {
            return new WP_Error('invalid_plate', __('Invalid Danish license plate format', 'car-sales-plugin'));
        }

        // Use Synsbasen search endpoint with registration filter
        $url = $this->api_url . 'vehicles';
        
        $body = json_encode(array(
            'query' => array(
                'registration_eq' => $license_plate
            ),
            'expand' => array('emission', 'equipment', 'weight', 'inspections'),
            'method' => 'SELECT',
            'per_page' => 1
        ));
        
        $response = wp_remote_request($url, array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => $body,
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('api_error', $data['message'] ?? __('Failed to retrieve car data', 'car-sales-plugin'));
        }

        if (empty($data['data']) || !is_array($data['data'])) {
            return new WP_Error('no_data', __('No vehicle found with this license plate', 'car-sales-plugin'));
        }

        return $this->format_car_data($data['data'][0]);
    }
    
    /**
     * AJAX handler for license plate lookup
     */
    public function ajax_lookup_license_plate() {
        check_ajax_referer('car_sales_nonce', 'nonce');
        
        $license_plate = sanitize_text_field($_POST['license_plate'] ?? '');
        
        if (empty($license_plate)) {
            wp_send_json_error(__('License plate is required', 'car-sales-plugin'));
        }
        
        $result = $this->lookup_by_license_plate($license_plate);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }

    /**
     * Validate Danish license plate format
     */
    private function validate_license_plate($plate) {
        // Danish license plate formats:
        // AB12345 (2 letters + 5 digits)
        // AB123456 (2 letters + 6 digits) - newer format
        return preg_match('/^[A-Z]{2}\d{5,6}$/', $plate);
    }

    /**
     * Format car data from Synsbasen API response
     */
    private function format_car_data($data) {
        $first_registration = $data['first_registration_date'] ?? '';
        $year = '';
        if ($first_registration) {
            $year = date('Y', strtotime($first_registration));
        }
        
        return array(
            'license_plate' => $data['registration'] ?? '',
            'make' => $data['brand'] ?? '',
            'model' => $data['model'] ?? '',
            'variant' => $data['variant'] ?? '',
            'year' => $year,
            'vin' => $data['vin'] ?? '',
            'fuel_type' => $data['fuel'] ?? '',
            'color' => $data['color'] ?? '',
            'doors' => $data['doors'] ?? '',
            'mileage' => $data['mileage'] ?? '',
            'engine_size' => $data['engine_volume'] ?? '',
            'power_hp' => $data['power_hp'] ?? '',
            'power_kw' => $data['power_kw'] ?? '',
            'transmission' => $data['gear'] ?? '',
            'body_type' => $data['body_type'] ?? '',
            'status' => $data['status'] ?? '',
            'registration_status' => $data['registration_status'] ?? '',
            'first_registration_date' => $first_registration,
            'emission_class' => $data['emission']['euronorm'] ?? '',
            'co2_emission' => $data['emission']['co2'] ?? '',
            'energy_class' => $data['emission']['energy_class'] ?? '',
            'weight' => $data['weight']['curb_weight'] ?? '',
            'max_weight' => $data['weight']['total_weight'] ?? '',
            'last_inspection' => $this->get_last_inspection_date($data['inspections'] ?? array()),
            'inspection_valid_until' => $this->get_next_inspection_date($data['inspections'] ?? array()),
            'seats' => $data['seats'] ?? '',
            'length' => $data['length'] ?? '',
            'width' => $data['width'] ?? '',
            'height' => $data['height'] ?? '',
            'owner_changes' => $data['owner_changes'] ?? 0,
        );
    }
    
    /**
     * Get last inspection date from inspections array
     */
    private function get_last_inspection_date($inspections) {
        if (empty($inspections) || !is_array($inspections)) {
            return '';
        }
        
        $latest_date = '';
        foreach ($inspections as $inspection) {
            $date = $inspection['date'] ?? '';
            if ($date && ($latest_date === '' || strtotime($date) > strtotime($latest_date))) {
                $latest_date = $date;
            }
        }
        
        return $latest_date;
    }
    
    /**
     * Get next inspection date from inspections array
     */
    private function get_next_inspection_date($inspections) {
        if (empty($inspections) || !is_array($inspections)) {
            return '';
        }
        
        $next_date = '';
        foreach ($inspections as $inspection) {
            $valid_until = $inspection['valid_until'] ?? '';
            if ($valid_until && ($next_date === '' || strtotime($valid_until) < strtotime($next_date))) {
                $next_date = $valid_until;
            }
        }
        
        return $next_date;
    }

    /**
     * Get Synsbasen data for additional technical information
     */
    public function get_synsbasen_data($license_plate) {
        $synsbasen_api_key = get_option('car_sales_synsbasen_api_key', '');
        
        if (empty($synsbasen_api_key)) {
            return new WP_Error('no_synsbasen_key', __('Synsbasen API key not configured', 'car-sales-plugin'));
        }

        $url = 'https://api.synsbasen.dk/v1/vehicles/' . urlencode($license_plate);
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $synsbasen_api_key,
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
            return new WP_Error('synsbasen_error', $data['message'] ?? __('Failed to retrieve inspection data', 'car-sales-plugin'));
        }

        return array(
            'assessment_score' => $data['assessmentScore'] ?? '',
            'technical_condition' => $data['technicalCondition'] ?? '',
            'inspection_history' => $data['inspectionHistory'] ?? array(),
            'defects' => $data['defects'] ?? array(),
            'recommendations' => $data['recommendations'] ?? array(),
        );
    }
}
