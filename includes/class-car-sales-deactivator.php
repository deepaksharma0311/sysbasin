<?php

/**
 * Fired during plugin deactivation
 */
class Car_Sales_Deactivator {

    /**
     * Plugin deactivation tasks
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any scheduled events
        wp_clear_scheduled_hook('car_sales_sync_bilinfo');
        wp_clear_scheduled_hook('car_sales_cleanup_analytics');
    }
}
