<?php
/**
 * Simple test API for Synsbasen integration demo
 * This simulates the WordPress AJAX functionality for testing
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

/**
 * Demo vehicle data for testing Synsbasen API format
 */
function get_demo_vehicle_data($license_plate) {
    $demo_vehicles = array(
        'AB12345' => array(
            'success' => true,
            'data' => array(
                'license_plate' => 'AB12345',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'variant' => '1.6 VVT-i',
                'year' => '2019',
                'vin' => 'JTDBR32E420123456',
                'fuel_type' => 'Benzin',
                'color' => 'Grå metallic',
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
                'demo_note' => 'Demo data from Synsbasen API format'
            )
        ),
        'CD67890' => array(
            'success' => true,
            'data' => array(
                'license_plate' => 'CD67890',
                'make' => 'Volkswagen',
                'model' => 'Golf',
                'variant' => '2.0 TDI',
                'year' => '2020',
                'vin' => 'WVWZZZ1JZ2W123456',
                'fuel_type' => 'Diesel',
                'color' => 'Sort metallic',
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
                'demo_note' => 'Demo data from Synsbasen API format'
            )
        ),
        'EF11223' => array(
            'success' => true,
            'data' => array(
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
                'demo_note' => 'Demo data from Synsbasen API format'
            )
        )
    );

    return $demo_vehicles[$license_plate] ?? null;
}

// Handle the license plate lookup request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $license_plate = strtoupper(trim($input['license_plate'] ?? ''));
    
    if (empty($license_plate)) {
        echo json_encode(array(
            'success' => false,
            'error' => 'License plate is required'
        ));
        exit;
    }
    
    // Validate Danish license plate format
    if (!preg_match('/^[A-Z]{2}\d{5,6}$/', $license_plate)) {
        echo json_encode(array(
            'success' => false,
            'error' => 'Invalid Danish license plate format. Use format: AB12345'
        ));
        exit;
    }
    
    // Simulate API call delay for realism
    usleep(500000); // 0.5 second delay
    
    $vehicle_data = get_demo_vehicle_data($license_plate);
    
    if ($vehicle_data) {
        echo json_encode($vehicle_data);
    } else {
        echo json_encode(array(
            'success' => false,
            'error' => 'No vehicle found with this license plate. Try AB12345, CD67890, or EF11223'
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'error' => 'Invalid request method'
    ));
}
?>