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

        // Use Synsbasen vehicles endpoint with search query
        $url = $this->api_url . 'vehicles';
        
        $body = json_encode(array(
            'query' => array(
                'registration_eq' => $license_plate
            ),
            'expand' => array('emission', 'weight', 'inspections', 'dmr_data'),
            'method' => 'SELECT',
            'per_page' => 1
        ));
        
        $response = wp_remote_post($url, array(
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
            $error_message = isset($data['error']) ? $data['error'] : __('Failed to retrieve car data', 'car-sales-plugin');
            return new WP_Error('api_error', $error_message);
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
        
        // Try API first if key is configured
        if (!empty($this->api_key)) {
            $result = $this->lookup_by_license_plate($license_plate);
            
            if (!is_wp_error($result)) {
                wp_send_json_success($result);
            }
            
            // If API fails, fall back to demo data
            $demo_result = $this->get_demo_data($license_plate);
            if ($demo_result) {
                $demo_result['demo_note'] = __('Demo data - Configure Synsbasen API key for live data', 'car-sales-plugin');
                wp_send_json_success($demo_result);
            }
            
            wp_send_json_error($result->get_error_message());
        } else {
            // No API key - use demo data
            $demo_result = $this->get_demo_data($license_plate);
            if ($demo_result) {
                $demo_result['demo_note'] = __('Demo data - Configure Synsbasen API key for live data', 'car-sales-plugin');
                wp_send_json_success($demo_result);
            }
            
            wp_send_json_error(__('No vehicle found with this license plate', 'car-sales-plugin'));
        }
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
        
        // Extract emission data if available
        $emission = $data['emission'] ?? array();
        $weight = $data['weight'] ?? array();
        $inspections = $data['inspections'] ?? array();
        $dmr_data = $data['dmr_data'] ?? array();
        
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
            
            // Emission data
            'emission_class' => $emission['euronorm'] ?? '',
            'co2_emission' => $emission['co2'] ?? '',
            'energy_class' => $emission['energy_class'] ?? '',
            'euro_norm' => $emission['euronorm'] ?? '',
            'particle_filter' => $emission['particle_filter'] ?? false,
            'environmental_zones' => $emission['allowed_in_environmental_zones'] ?? false,
            
            // Weight data
            'weight' => $weight['curb_weight'] ?? '',
            'max_weight' => $weight['total_weight'] ?? '',
            'gross_weight' => $weight['gross_weight'] ?? '',
            
            // Inspection data
            'last_inspection' => $this->get_last_inspection_date($inspections),
            'inspection_valid_until' => $this->get_next_inspection_date($inspections),
            'inspection_status' => $this->get_latest_inspection_result($inspections),
            
            // Additional data
            'seats' => $data['seats'] ?? '',
            'length' => $data['length'] ?? '',
            'width' => $data['width'] ?? '',
            'height' => $data['height'] ?? '',
            'owner_changes' => $data['owner_changes'] ?? 0,
            'technical_max_mass' => $data['technical_max_mass'] ?? '',
            'axles' => $data['axles'] ?? '',
            
            // DMR specific data if available
            'insurance_company' => $dmr_data['insurance_company'] ?? '',
            'insurance_valid_from' => $dmr_data['insurance_valid_from'] ?? '',
            'registration_certificate_number' => $dmr_data['registration_certificate_number'] ?? '',
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
     * Get latest inspection result from inspections array
     */
    private function get_latest_inspection_result($inspections) {
        if (empty($inspections) || !is_array($inspections)) {
            return '';
        }
        
        $latest_date = '';
        $latest_result = '';
        
        foreach ($inspections as $inspection) {
            $date = $inspection['date'] ?? '';
            $result = $inspection['result'] ?? '';
            
            if ($date && ($latest_date === '' || strtotime($date) > strtotime($latest_date))) {
                $latest_date = $date;
                $latest_result = $result;
            }
        }
        
        return $latest_result;
    }

    /**
     * Create demo data for testing when API key is not available
     */
    public function get_demo_data($license_plate) {
        $demo_vehicles = array(
            'AB12345' => array(
                'license_plate' => 'AB12345',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'variant' => '1.6 VVT-i',
                'year' => '2019',
                'vin' => 'JTDBR32E420123456',
                'fuel_type' => 'Benzin',
                'color' => 'Grå',
                'doors' => '5',
                'mileage' => '45000',
                'engine_size' => '1598',
                'power_hp' => '132',
                'power_kw' => '97',
                'transmission' => 'Manuel',
                'body_type' => 'Personbil',
                'status' => 'Registreret',
                'registration_status' => 'Normal',
                'first_registration_date' => '2019-03-15',
                'emission_class' => 'Euro 6',
                'co2_emission' => '120',
                'energy_class' => 'B',
                'euro_norm' => 'Euro 6',
                'particle_filter' => false,
                'environmental_zones' => true,
                'weight' => '1315',
                'max_weight' => '1760',
                'gross_weight' => '1760',
                'last_inspection' => '2023-03-15',
                'inspection_valid_until' => '2025-03-15',
                'inspection_status' => 'Godkendt',
                'seats' => '5',
                'length' => '4630',
                'width' => '1780',
                'height' => '1435',
                'owner_changes' => '2',
                'technical_max_mass' => '1760',
                'axles' => '2',
                'insurance_company' => 'Tryg Forsikring',
                'insurance_valid_from' => '2024-01-01',
                'registration_certificate_number' => 'DK123456789',
            ),
            'CD67890' => array(
                'license_plate' => 'CD67890',
                'make' => 'Volkswagen',
                'model' => 'Golf',
                'variant' => '2.0 TDI',
                'year' => '2020',
                'vin' => 'WVWZZZ1JZ2W123456',
                'fuel_type' => 'Diesel',
                'color' => 'Sort',
                'doors' => '5',
                'mileage' => '32000',
                'engine_size' => '1968',
                'power_hp' => '150',
                'power_kw' => '110',
                'transmission' => 'Automatgear',
                'body_type' => 'Personbil',
                'status' => 'Registreret',
                'registration_status' => 'Normal',
                'first_registration_date' => '2020-06-22',
                'emission_class' => 'Euro 6d-TEMP',
                'co2_emission' => '108',
                'energy_class' => 'A',
                'euro_norm' => 'Euro 6d-TEMP',
                'particle_filter' => true,
                'environmental_zones' => true,
                'weight' => '1428',
                'max_weight' => '1980',
                'gross_weight' => '1980',
                'last_inspection' => '2023-06-22',
                'inspection_valid_until' => '2025-06-22',
                'inspection_status' => 'Godkendt',
                'seats' => '5',
                'length' => '4284',
                'width' => '1789',
                'height' => '1456',
                'owner_changes' => '1',
                'technical_max_mass' => '1980',
                'axles' => '2',
                'insurance_company' => 'Alka Forsikring',
                'insurance_valid_from' => '2024-02-15',
                'registration_certificate_number' => 'DK987654321',
            ),
            'EF11223' => array(
                'license_plate' => 'EF11223',
                'make' => 'BMW',
                'model' => 'X3',
                'variant' => 'xDrive20d',
                'year' => '2021',
                'vin' => 'WBAXG71090L123456',
                'fuel_type' => 'Diesel',
                'color' => 'Hvid',
                'doors' => '5',
                'mileage' => '28000',
                'engine_size' => '1995',
                'power_hp' => '190',
                'power_kw' => '140',
                'transmission' => 'Automatgear',
                'body_type' => 'SUV',
                'status' => 'Registreret',
                'registration_status' => 'Normal',
                'first_registration_date' => '2021-09-10',
                'emission_class' => 'Euro 6d',
                'co2_emission' => '142',
                'energy_class' => 'C',
                'euro_norm' => 'Euro 6d',
                'particle_filter' => true,
                'environmental_zones' => true,
                'weight' => '1865',
                'max_weight' => '2450',
                'gross_weight' => '2450',
                'last_inspection' => '2024-09-10',
                'inspection_valid_until' => '2026-09-10',
                'inspection_status' => 'Godkendt',
                'seats' => '5',
                'length' => '4708',
                'width' => '1891',
                'height' => '1676',
                'owner_changes' => '0',
                'technical_max_mass' => '2450',
                'axles' => '2',
                'insurance_company' => 'Codan Forsikring',
                'insurance_valid_from' => '2024-03-01',
                'registration_certificate_number' => 'DK456789123',
            )
        );

        return $demo_vehicles[$license_plate] ?? null;
    }
}
