<?php
/**
 * Plugin Name: Carramba WooCommerce Order Tracking
 * Plugin URI: https://github.com/michalstaniecko/carramba-woo-order-tracking
 * Description: WooCommerce order tracking plugin that allows admins to manage shippers and add tracking information to customer email notifications. Compatible with WooCommerce High-Performance Order Storage (HPOS).
 * Version: 1.0.0
 * Author: Michal Staniećko
 * Text Domain: carramba-woo-order-tracking
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CWOT_VERSION', '1.0.0');
define('CWOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CWOT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CWOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Carramba_WooCommerce_Order_Tracking {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Declare HPOS compatibility
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
        
        $this->load_textdomain();
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('carramba-woo-order-tracking', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    /**
     * Include required files
     */
    public function includes() {
        require_once CWOT_PLUGIN_PATH . 'includes/class-cwot-database.php';
        require_once CWOT_PLUGIN_PATH . 'includes/class-cwot-admin.php';
        require_once CWOT_PLUGIN_PATH . 'includes/class-cwot-order-tracking.php';
        require_once CWOT_PLUGIN_PATH . 'includes/class-cwot-email.php';
    }
    
    /**
     * Initialize hooks
     */
    public function init_hooks() {
        // Initialize components
        CWOT_Database::get_instance();
        CWOT_Admin::get_instance();
        CWOT_Order_Tracking::get_instance();
        CWOT_Email::get_instance();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        CWOT_Database::create_tables();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Static plugin activation callback
     */
    public static function plugin_activate() {
        // Ensure database class is loaded
        require_once CWOT_PLUGIN_PATH . 'includes/class-cwot-database.php';
        CWOT_Database::create_tables();
        flush_rewrite_rules();
    }
    
    /**
     * Static plugin deactivation callback
     */
    public static function plugin_deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . __('Carramba WooCommerce Order Tracking', 'carramba-woo-order-tracking') . '</strong> ' . __('requires WooCommerce to be installed and active.', 'carramba-woo-order-tracking') . '</p></div>';
    }
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('Carramba_WooCommerce_Order_Tracking', 'plugin_activate'));
register_deactivation_hook(__FILE__, array('Carramba_WooCommerce_Order_Tracking', 'plugin_deactivate'));

// Initialize the plugin
Carramba_WooCommerce_Order_Tracking::get_instance();