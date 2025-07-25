<?php

/**
 * The admin-specific functionality of the plugin
 */
class Car_Sales_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, CAR_SALES_PLUGIN_URL . 'admin/css/car-sales-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, CAR_SALES_PLUGIN_URL . 'admin/js/car-sales-admin.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'car_sales_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('car_sales_admin_nonce'),
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Car Sales', 'car-sales-plugin'),
            __('Car Sales', 'car-sales-plugin'),
            'manage_options',
            'car-sales',
            array($this, 'display_admin_page'),
            'dashicons-car',
            30
        );

        add_submenu_page(
            'car-sales',
            __('Settings', 'car-sales-plugin'),
            __('Settings', 'car-sales-plugin'),
            'manage_options',
            'car-sales-settings',
            array($this, 'display_settings_page')
        );

        add_submenu_page(
            'car-sales',
            __('Inquiries', 'car-sales-plugin'),
            __('Inquiries', 'car-sales-plugin'),
            'manage_options',
            'car-sales-inquiries',
            array($this, 'display_inquiries_page')
        );

        add_submenu_page(
            'car-sales',
            __('Analytics', 'car-sales-plugin'),
            __('Analytics', 'car-sales-plugin'),
            'manage_options',
            'car-sales-analytics',
            array($this, 'display_analytics_page')
        );
    }

    /**
     * Initialize admin settings
     */
    public function admin_init() {
        register_setting('car_sales_settings', 'car_sales_danish_registry_api_key');
        register_setting('car_sales_settings', 'car_sales_synsbasen_api_key');
        register_setting('car_sales_settings', 'car_sales_bilinfo_api_key');
        register_setting('car_sales_settings', 'car_sales_safepay_api_key');
        register_setting('car_sales_settings', 'car_sales_max_images');
        register_setting('car_sales_settings', 'car_sales_auto_approve');
        register_setting('car_sales_settings', 'car_sales_email_notifications');
    }

    /**
     * Display main admin page
     */
    public function display_admin_page() {
        include_once CAR_SALES_PLUGIN_PATH . 'admin/partials/car-sales-admin-display.php';
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('car_sales_settings');
                do_settings_sections('car_sales_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Danish Motor Registry API Key', 'car-sales-plugin'); ?></th>
                        <td>
                            <input type="text" name="car_sales_danish_registry_api_key" value="<?php echo esc_attr(get_option('car_sales_danish_registry_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php _e('API key for Danish Motor Registry integration', 'car-sales-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Synsbasen API Key', 'car-sales-plugin'); ?></th>
                        <td>
                            <input type="text" name="car_sales_synsbasen_api_key" value="<?php echo esc_attr(get_option('car_sales_synsbasen_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php _e('API key for Synsbasen integration', 'car-sales-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Bilinfo.dk API Key', 'car-sales-plugin'); ?></th>
                        <td>
                            <input type="text" name="car_sales_bilinfo_api_key" value="<?php echo esc_attr(get_option('car_sales_bilinfo_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php _e('API key for Bilinfo.dk synchronization', 'car-sales-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('SafePay Nordic API Key', 'car-sales-plugin'); ?></th>
                        <td>
                            <input type="text" name="car_sales_safepay_api_key" value="<?php echo esc_attr(get_option('car_sales_safepay_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php _e('API key for SafePay Nordic integration', 'car-sales-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Maximum Images per Car', 'car-sales-plugin'); ?></th>
                        <td>
                            <input type="number" name="car_sales_max_images" value="<?php echo esc_attr(get_option('car_sales_max_images', 8)); ?>" min="1" max="20" />
                            <p class="description"><?php _e('Maximum number of images users can upload per car', 'car-sales-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Auto-approve Cars', 'car-sales-plugin'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="car_sales_auto_approve" value="1" <?php checked(1, get_option('car_sales_auto_approve'), true); ?> />
                                <?php _e('Automatically approve new car listings', 'car-sales-plugin'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email Notifications', 'car-sales-plugin'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="car_sales_email_notifications" value="1" <?php checked(1, get_option('car_sales_email_notifications'), true); ?> />
                                <?php _e('Send email notifications for new inquiries', 'car-sales-plugin'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display inquiries page
     */
    public function display_inquiries_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'car_inquiries';
        $inquiries = $wpdb->get_results("SELECT i.*, p.post_title as car_title FROM $table_name i LEFT JOIN {$wpdb->posts} p ON i.car_id = p.ID ORDER BY i.inquiry_date DESC LIMIT 50");
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'car-sales-plugin'); ?></th>
                        <th><?php _e('Car', 'car-sales-plugin'); ?></th>
                        <th><?php _e('Name', 'car-sales-plugin'); ?></th>
                        <th><?php _e('Email', 'car-sales-plugin'); ?></th>
                        <th><?php _e('Phone', 'car-sales-plugin'); ?></th>
                        <th><?php _e('Status', 'car-sales-plugin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inquiry): ?>
                    <tr>
                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($inquiry->inquiry_date))); ?></td>
                        <td><?php echo esc_html($inquiry->car_title); ?></td>
                        <td><?php echo esc_html($inquiry->name); ?></td>
                        <td><a href="mailto:<?php echo esc_attr($inquiry->email); ?>"><?php echo esc_html($inquiry->email); ?></a></td>
                        <td><a href="tel:<?php echo esc_attr($inquiry->phone); ?>"><?php echo esc_html($inquiry->phone); ?></a></td>
                        <td>
                            <span class="status-<?php echo esc_attr($inquiry->status); ?>">
                                <?php echo esc_html(ucfirst($inquiry->status)); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Display analytics page
     */
    public function display_analytics_page() {
        global $wpdb;
        
        // Get inquiry statistics
        $inquiry_table = $wpdb->prefix . 'car_inquiries';
        $analytics_table = $wpdb->prefix . 'car_analytics';
        
        $total_inquiries = $wpdb->get_var("SELECT COUNT(*) FROM $inquiry_table");
        $inquiries_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $inquiry_table WHERE DATE(inquiry_date) = %s",
            current_time('Y-m-d')
        ));
        
        $total_cars = wp_count_posts('car_listing')->publish;
        $pending_cars = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            '_car_status',
            'pending'
        ));
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="car-sales-stats">
                <div class="stat-box">
                    <h3><?php _e('Total Cars', 'car-sales-plugin'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($total_cars); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php _e('Pending Approval', 'car-sales-plugin'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($pending_cars); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php _e('Total Inquiries', 'car-sales-plugin'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($total_inquiries); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php _e('Inquiries Today', 'car-sales-plugin'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($inquiries_today); ?></p>
                </div>
            </div>

            <h2><?php _e('Top Cars by Inquiries', 'car-sales-plugin'); ?></h2>
            <?php
            $top_cars = $wpdb->get_results("
                SELECT p.post_title, COUNT(i.id) as inquiry_count 
                FROM {$wpdb->posts} p 
                LEFT JOIN $inquiry_table i ON p.ID = i.car_id 
                WHERE p.post_type = 'car_listing' 
                GROUP BY p.ID 
                ORDER BY inquiry_count DESC 
                LIMIT 10
            ");
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Car Title', 'car-sales-plugin'); ?></th>
                        <th><?php _e('Inquiries', 'car-sales-plugin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_cars as $car): ?>
                    <tr>
                        <td><?php echo esc_html($car->post_title); ?></td>
                        <td><?php echo esc_html($car->inquiry_count); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Approve car listing
     */
    public function approve_car_listing() {
        check_ajax_referer('car_sales_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'car-sales-plugin'));
        }

        $car_id = intval($_POST['car_id']);
        
        update_post_meta($car_id, '_car_status', 'approved');
        
        // Sync to Bilinfo if enabled
        $bilinfo = new Bilinfo_Integration();
        $sync_result = $bilinfo->sync_single_car($car_id);

        wp_send_json_success(__('Car approved successfully', 'car-sales-plugin'));
    }

    /**
     * Manual Bilinfo sync
     */
    public function sync_bilinfo() {
        check_ajax_referer('car_sales_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'car-sales-plugin'));
        }

        $bilinfo = new Bilinfo_Integration();
        $result = $bilinfo->sync_approved_cars();

        wp_send_json_success(__('Bilinfo sync completed', 'car-sales-plugin'));
    }
}
