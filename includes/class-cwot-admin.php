<?php
/**
 * Admin functionality for CWOT plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Order Tracking', 'carramba-woo-order-tracking'),
            __('Order Tracking', 'carramba-woo-order-tracking'),
            'manage_woocommerce',
            'cwot-settings',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('cwot_settings', 'cwot_show_in_order_details');
        register_setting('cwot_settings', 'cwot_show_in_email');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_cwot-settings') {
            return;
        }
        
        wp_enqueue_style('cwot-admin-style', CWOT_PLUGIN_URL . 'assets/css/admin.css', array(), CWOT_VERSION);
        wp_enqueue_script('cwot-admin-script', CWOT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CWOT_VERSION, true);
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        if (!isset($_POST['cwot_nonce']) || !wp_verify_nonce($_POST['cwot_nonce'], 'cwot_admin_action')) {
            return;
        }
        
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        switch ($action) {
            case 'save_settings':
                $this->save_settings();
                break;
            case 'save_shipper':
                $this->save_shipper();
                break;
            case 'delete_shipper':
                $this->delete_shipper();
                break;
        }
    }
    
    /**
     * Save plugin settings
     */
    private function save_settings() {
        $show_in_order_details = isset($_POST['cwot_show_in_order_details']) ? 1 : 0;
        $show_in_email = isset($_POST['cwot_show_in_email']) ? 1 : 0;
        
        update_option('cwot_show_in_order_details', $show_in_order_details);
        update_option('cwot_show_in_email', $show_in_email);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'carramba-woo-order-tracking') . '</p></div>';
        });
    }
    
    /**
     * Save shipper
     */
    private function save_shipper() {
        $shipper_data = array(
            'name' => sanitize_text_field($_POST['shipper_name']),
            'tracking_url' => sanitize_text_field($_POST['tracking_url']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Validate required fields
        if (empty($shipper_data['name']) || empty($shipper_data['tracking_url'])) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Please fill in all required fields.', 'carramba-woo-order-tracking') . '</p></div>';
            });
            return;
        }
        
        // Validate tracking URL contains placeholder
        if (strpos($shipper_data['tracking_url'], '{tracking_number}') === false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Tracking URL must contain {tracking_number} placeholder.', 'carramba-woo-order-tracking') . '</p></div>';
            });
            return;
        }
        
        $shipper_id = isset($_POST['shipper_id']) ? intval($_POST['shipper_id']) : null;
        
        if (CWOT_Database::save_shipper($shipper_data, $shipper_id)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>' . __('Shipper saved successfully.', 'carramba-woo-order-tracking') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Failed to save shipper.', 'carramba-woo-order-tracking') . '</p></div>';
            });
        }
    }
    
    /**
     * Delete shipper
     */
    private function delete_shipper() {
        $shipper_id = intval($_POST['shipper_id']);
        
        if (CWOT_Database::delete_shipper($shipper_id)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>' . __('Shipper deleted successfully.', 'carramba-woo-order-tracking') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Failed to delete shipper.', 'carramba-woo-order-tracking') . '</p></div>';
            });
        }
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'settings';
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $shipper_id = isset($_GET['shipper_id']) ? intval($_GET['shipper_id']) : null;
        
        if ($tab === 'shippers') {
            switch ($action) {
                case 'edit':
                    $this->render_edit_shipper_page($shipper_id);
                    break;
                case 'add':
                    $this->render_add_shipper_page();
                    break;
                default:
                    $this->render_shippers_list_page();
                    break;
            }
        } else {
            $this->render_settings_page();
        }
    }
    
    /**
     * Render settings page
     */
    private function render_settings_page() {
        $active_tab = 'settings';
        $show_in_order_details = get_option('cwot_show_in_order_details', 1);
        $show_in_email = get_option('cwot_show_in_email', 1);
        
        include CWOT_PLUGIN_PATH . 'templates/admin/settings-page.php';
    }
    
    /**
     * Render shippers list page
     */
    private function render_shippers_list_page() {
        $active_tab = 'shippers';
        $shippers = CWOT_Database::get_all_shippers();
        $show_in_order_details = get_option('cwot_show_in_order_details', 1);
        $show_in_email = get_option('cwot_show_in_email', 1);
        
        include CWOT_PLUGIN_PATH . 'templates/admin/settings-page.php';
    }
    
    /**
     * Render add shipper page
     */
    private function render_add_shipper_page() {
        $is_edit = false;
        $shipper = null;
        ?>
        <div class="wrap">
            <h1><?php _e('Add New Shipper', 'carramba-woo-order-tracking'); ?></h1>
            
            <?php include CWOT_PLUGIN_PATH . 'templates/admin/shipper-form.php'; ?>
        </div>
        <?php
    }
    
    /**
     * Render edit shipper page
     */
    private function render_edit_shipper_page($shipper_id) {
        $shipper = CWOT_Database::get_shipper_by_id($shipper_id);
        
        if (!$shipper) {
            echo '<div class="wrap"><h1>' . __('Shipper not found', 'carramba-woo-order-tracking') . '</h1></div>';
            return;
        }
        
        $is_edit = true;
        ?>
        <div class="wrap">
            <h1><?php _e('Edit Shipper', 'carramba-woo-order-tracking'); ?></h1>
            
            <?php include CWOT_PLUGIN_PATH . 'templates/admin/shipper-form.php'; ?>
        </div>
        <?php
    }
    
    /**
     * Render shipper form (deprecated - use template instead)
     */
    private function render_shipper_form($shipper = null) {
        $is_edit = !empty($shipper);
        include CWOT_PLUGIN_PATH . 'templates/admin/shipper-form.php';
    }
}