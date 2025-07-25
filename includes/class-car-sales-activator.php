<?php

/**
 * Fired during plugin activation
 */
class Car_Sales_Activator {

    /**
     * Plugin activation tasks
     */
    public static function activate() {
        global $wpdb;

        // Create custom tables
        self::create_tables();
        
        // Create custom post types
        self::register_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Create default pages
        self::create_default_pages();
        
        // Set default options
        self::set_default_options();
    }

    /**
     * Create custom database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Car inquiries table
        $table_name = $wpdb->prefix . 'car_inquiries';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            car_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            message text,
            inquiry_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'new',
            PRIMARY KEY (id),
            KEY car_id (car_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Car analytics table
        $table_analytics = $wpdb->prefix . 'car_analytics';
        $sql2 = "CREATE TABLE $table_analytics (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            car_id bigint(20) NOT NULL,
            action_type varchar(50) NOT NULL,
            user_ip varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY car_id (car_id),
            KEY action_type (action_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }

    /**
     * Register custom post types
     */
    private static function register_post_types() {
        // Car listings post type
        register_post_type('car_listing', array(
            'labels' => array(
                'name' => 'Car Listings',
                'singular_name' => 'Car Listing',
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-car',
            'show_in_rest' => true,
        ));
    }

    /**
     * Create default pages
     */
    private static function create_default_pages() {
        // Car search page
        $search_page = array(
            'post_title'    => 'Car Search',
            'post_content'  => '[car_search_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'car-search'
        );
        
        if (!get_page_by_path('car-search')) {
            wp_insert_post($search_page);
        }

        // User dashboard page
        $dashboard_page = array(
            'post_title'    => 'My Dashboard',
            'post_content'  => '[user_car_dashboard]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'my-dashboard'
        );
        
        if (!get_page_by_path('my-dashboard')) {
            wp_insert_post($dashboard_page);
        }

        // Financing calculator page
        $financing_page = array(
            'post_title'    => 'Car Financing',
            'post_content'  => '[financing_calculator]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'car-financing'
        );
        
        if (!get_page_by_path('car-financing')) {
            wp_insert_post($financing_page);
        }
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        add_option('car_sales_danish_registry_api_key', '');
        add_option('car_sales_synsbasen_api_key', '');
        add_option('car_sales_bilinfo_api_key', '');
        add_option('car_sales_safepay_api_key', '');
        add_option('car_sales_max_images', 8);
        add_option('car_sales_auto_approve', false);
        add_option('car_sales_email_notifications', true);
    }
}
