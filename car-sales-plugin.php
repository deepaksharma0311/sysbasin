<?php
/**
 * Plugin Name: Car Sales Plugin
 * Plugin URI: https://yoursite.com/car-sales-plugin
 * Description: Comprehensive car sales plugin with Danish Motor Registry integration, user dashboards, and Bilinfo.dk synchronization.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: car-sales-plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('CAR_SALES_PLUGIN_VERSION', '1.0.0');
define('CAR_SALES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAR_SALES_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_car_sales_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-car-sales-activator.php';
    Car_Sales_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_car_sales_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-car-sales-deactivator.php';
    Car_Sales_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_car_sales_plugin');
register_deactivation_hook(__FILE__, 'deactivate_car_sales_plugin');

/**
 * The core plugin class.
 */
require plugin_dir_path(__FILE__) . 'includes/class-car-sales-core.php';

/**
 * Begins execution of the plugin.
 */
function run_car_sales_plugin() {
    $plugin = new Car_Sales_Core();
    $plugin->run();
}
run_car_sales_plugin();
