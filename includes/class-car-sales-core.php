<?php

/**
 * The core plugin class
 */
class Car_Sales_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     */
    protected $version;

    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        if (defined('CAR_SALES_PLUGIN_VERSION')) {
            $this->version = CAR_SALES_PLUGIN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'car-sales-plugin';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies
     */
    private function load_dependencies() {
        require_once CAR_SALES_PLUGIN_PATH . 'includes/class-synsbasen-integration.php';
        require_once CAR_SALES_PLUGIN_PATH . 'includes/class-bilinfo-integration.php';
        require_once CAR_SALES_PLUGIN_PATH . 'includes/class-user-dashboard.php';
        require_once CAR_SALES_PLUGIN_PATH . 'includes/class-financing-calculator.php';
        require_once CAR_SALES_PLUGIN_PATH . 'admin/class-car-sales-admin.php';
        require_once CAR_SALES_PLUGIN_PATH . 'public/class-car-sales-public.php';
    }

    /**
     * Define the locale for this plugin for internationalization
     */
    private function set_locale() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'car-sales-plugin',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Register all hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $plugin_admin = new Car_Sales_Admin($this->get_plugin_name(), $this->get_version());

        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        add_action('admin_menu', array($plugin_admin, 'add_admin_menu'));
        add_action('admin_init', array($plugin_admin, 'admin_init'));
        
        // AJAX handlers for admin
        add_action('wp_ajax_approve_car_listing', array($plugin_admin, 'approve_car_listing'));
        add_action('wp_ajax_sync_bilinfo', array($plugin_admin, 'sync_bilinfo'));
    }

    /**
     * Register all hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new Car_Sales_Public($this->get_plugin_name(), $this->get_version());

        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        
        // Shortcodes
        add_shortcode('car_search_form', array($plugin_public, 'car_search_form_shortcode'));
        add_shortcode('user_car_dashboard', array($plugin_public, 'user_dashboard_shortcode'));
        add_shortcode('financing_calculator', array($plugin_public, 'financing_calculator_shortcode'));
        
        // AJAX handlers for public
        add_action('wp_ajax_lookup_license_plate', array($plugin_public, 'lookup_license_plate'));
        add_action('wp_ajax_nopriv_lookup_license_plate', array($plugin_public, 'lookup_license_plate'));
        add_action('wp_ajax_submit_car_inquiry', array($plugin_public, 'submit_car_inquiry'));
        add_action('wp_ajax_nopriv_submit_car_inquiry', array($plugin_public, 'submit_car_inquiry'));
        add_action('wp_ajax_upload_car_images', array($plugin_public, 'upload_car_images'));
        add_action('wp_ajax_calculate_financing', array($plugin_public, 'calculate_financing'));
        add_action('wp_ajax_nopriv_calculate_financing', array($plugin_public, 'calculate_financing'));
        
        // Custom post type registration
        add_action('init', array($this, 'register_custom_post_types'));
        
        // Scheduled events
        add_action('car_sales_sync_bilinfo', array($this, 'scheduled_bilinfo_sync'));
    }

    /**
     * Register custom post types
     */
    public function register_custom_post_types() {
        register_post_type('car_listing', array(
            'labels' => array(
                'name' => __('Car Listings', 'car-sales-plugin'),
                'singular_name' => __('Car Listing', 'car-sales-plugin'),
                'add_new' => __('Add New Car', 'car-sales-plugin'),
                'add_new_item' => __('Add New Car Listing', 'car-sales-plugin'),
                'edit_item' => __('Edit Car Listing', 'car-sales-plugin'),
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-car',
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'cars'),
        ));
    }

    /**
     * Scheduled Bilinfo synchronization
     */
    public function scheduled_bilinfo_sync() {
        $bilinfo = new Bilinfo_Integration();
        $bilinfo->sync_approved_cars();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress
     */
    public function run() {
        // Schedule recurring events
        if (!wp_next_scheduled('car_sales_sync_bilinfo')) {
            wp_schedule_event(time(), 'hourly', 'car_sales_sync_bilinfo');
        }
    }

    /**
     * The name of the plugin used to uniquely identify it
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin
     */
    public function get_version() {
        return $this->version;
    }
}
