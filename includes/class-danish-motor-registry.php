<?php

/**
 * Danish Motor Registry API integration
 */
class Danish_Motor_Registry {

    private $api_key;
    private $api_url;

    public function __construct() {
        $this->api_key = get_option('car_sales_danish_registry_api_key', '');
        $this->api_url = 'https://www.motorregister.skat.dk/dmr-ws/rest/';
    }

    /**
     * Look up car data by license plate
     */
    public function lookup_by_license_plate($license_plate) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('Danish Motor Registry API key not configured', 'car-sales-plugin'));
        }

        $license_plate = sanitize_text_field($license_plate);
        $license_plate = strtoupper(trim($license_plate));

        // Validate Danish license plate format
        if (!$this->validate_license_plate($license_plate)) {
            return new WP_Error('invalid_plate', __('Invalid Danish license plate format', 'car-sales-plugin'));
        }

        $url = $this->api_url . 'vehicles/' . urlencode($license_plate);
        
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
            return new WP_Error('api_error', $data['message'] ?? __('Failed to retrieve car data', 'car-sales-plugin'));
        }

        return $this->format_car_data($data);
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
     * Format car data from API response
     */
    private function format_car_data($data) {
        return array(
            'license_plate' => $data['registrationNumber'] ?? '',
            'make' => $data['make'] ?? '',
            'model' => $data['model'] ?? '',
            'variant' => $data['variant'] ?? '',
            'year' => $data['firstRegistrationDate'] ? date('Y', strtotime($data['firstRegistrationDate'])) : '',
            'fuel_type' => $data['fuelType'] ?? '',
            'engine_size' => $data['engineSize'] ?? '',
            'power_hp' => $data['powerHp'] ?? '',
            'power_kw' => $data['powerKw'] ?? '',
            'transmission' => $data['transmission'] ?? '',
            'drive_type' => $data['driveType'] ?? '',
            'doors' => $data['doors'] ?? '',
            'seats' => $data['seats'] ?? '',
            'weight' => $data['weight'] ?? '',
            'length' => $data['length'] ?? '',
            'width' => $data['width'] ?? '',
            'height' => $data['height'] ?? '',
            'color' => $data['color'] ?? '',
            'vin' => $data['vinNumber'] ?? '',
            'first_registration' => $data['firstRegistrationDate'] ?? '',
            'last_inspection' => $data['lastInspectionDate'] ?? '',
            'next_inspection' => $data['nextInspectionDate'] ?? '',
            'euro_norm' => $data['euroNorm'] ?? '',
            'co2_emission' => $data['co2Emission'] ?? '',
            'owner_changes' => $data['ownerChanges'] ?? 0,
            'mileage_at_inspection' => $data['mileageAtLastInspection'] ?? '',
        );
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
