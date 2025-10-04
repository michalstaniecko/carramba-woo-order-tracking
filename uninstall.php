<?php
/**
 * Uninstall script for Carramba WooCommerce Order Tracking
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the shippers table
global $wpdb;
$table_name = $wpdb->prefix . 'cwot_shippers';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Clean up order meta data - HPOS compatible cleanup
if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && 
    \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
    // HPOS cleanup - remove from order meta table
    $orders_meta_table = $wpdb->prefix . 'wc_orders_meta';
    $wpdb->query($wpdb->prepare("DELETE FROM {$orders_meta_table} WHERE meta_key IN ('%s', '%s')", '_cwot_tracking_shipper_id', '_cwot_tracking_number'));
} else {
    // Legacy cleanup - remove from post meta table
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_cwot_tracking_shipper_id', '_cwot_tracking_number')");
}

// Clean up any plugin options (if we had any)
// delete_option('cwot_plugin_options');
// Clean up plugin settings
delete_option('cwot_show_in_order_details');
delete_option('cwot_show_in_email');
