<?php
/**
 * Database operations for CWOT plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Database {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor logic if needed
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            tracking_url text NOT NULL,
            status enum('active','inactive') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Insert default shippers if none exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count == 0) {
            self::insert_default_shippers();
        }
    }
    
    /**
     * Insert default shippers
     */
    private static function insert_default_shippers() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        $default_shippers = array(
            array(
                'name' => 'DHL',
                'tracking_url' => 'https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}'
            ),
            array(
                'name' => 'UPS',
                'tracking_url' => 'https://www.ups.com/track?loc=en_US&tracknum={tracking_number}'
            ),
            array(
                'name' => 'FedEx',
                'tracking_url' => 'https://www.fedex.com/fedextrack/?tracknumbers={tracking_number}'
            ),
            array(
                'name' => 'USPS',
                'tracking_url' => 'https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1={tracking_number}'
            )
        );
        
        foreach ($default_shippers as $shipper) {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $shipper['name'],
                    'tracking_url' => $shipper['tracking_url'],
                    'status' => 'active'
                ),
                array('%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get all active shippers
     */
    public static function get_active_shippers() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name WHERE status = 'active' ORDER BY name ASC"
        );
    }
    
    /**
     * Get all shippers
     */
    public static function get_all_shippers() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY name ASC"
        );
    }
    
    /**
     * Get shipper by ID
     */
    public static function get_shipper_by_id($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
        );
    }
    
    /**
     * Insert or update shipper
     */
    public static function save_shipper($data, $id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        $shipper_data = array(
            'name' => sanitize_text_field($data['name']),
            'tracking_url' => esc_url_raw($data['tracking_url']),
            'status' => sanitize_text_field($data['status'])
        );
        
        if ($id) {
            // Update existing shipper
            return $wpdb->update(
                $table_name,
                $shipper_data,
                array('id' => $id),
                array('%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Insert new shipper
            return $wpdb->insert(
                $table_name,
                $shipper_data,
                array('%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Delete shipper
     */
    public static function delete_shipper($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cwot_shippers';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
    }
}