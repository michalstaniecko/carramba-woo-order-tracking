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

// Clean up order meta data
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_cwot_tracking_shipper_id', '_cwot_tracking_number')");

// Clean up any plugin options (if we had any)
// delete_option('cwot_plugin_options');