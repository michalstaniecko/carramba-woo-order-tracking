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
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_init', array($this, 'register_settings'));
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
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('cwot_settings', 'cwot_show_tracking_on_order_details', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));
        
        register_setting('cwot_settings', 'cwot_show_tracking_in_emails', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));
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
            case 'save_shipper':
                $this->save_shipper();
                break;
            case 'delete_shipper':
                $this->delete_shipper();
                break;
            case 'save_settings':
                $this->save_settings();
                break;
        }
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        update_option('cwot_show_tracking_on_order_details', isset($_POST['cwot_show_tracking_on_order_details']));
        update_option('cwot_show_tracking_in_emails', isset($_POST['cwot_show_tracking_in_emails']));
        
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
            'tracking_url' => esc_url_raw($_POST['tracking_url']),
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
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'shippers';
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $shipper_id = isset($_GET['shipper_id']) ? intval($_GET['shipper_id']) : null;
        
        ?>
        <div class="wrap">
            <h1><?php _e('Order Tracking Settings', 'carramba-woo-order-tracking'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo add_query_arg('tab', 'shippers', remove_query_arg(array('action', 'shipper_id'))); ?>" class="nav-tab <?php echo $tab === 'shippers' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Shippers', 'carramba-woo-order-tracking'); ?>
                </a>
                <a href="<?php echo add_query_arg('tab', 'settings', remove_query_arg(array('action', 'shipper_id'))); ?>" class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Display Settings', 'carramba-woo-order-tracking'); ?>
                </a>
            </h2>
            
            <?php
            if ($tab === 'settings') {
                $this->render_settings_page();
            } else {
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
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Render shippers list page
     */
    private function render_shippers_list_page() {
        $shippers = CWOT_Database::get_all_shippers();
        ?>
        <p>
            <a href="<?php echo add_query_arg('action', 'add'); ?>" class="button button-primary">
                <?php _e('Add New Shipper', 'carramba-woo-order-tracking'); ?>
            </a>
        </p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'carramba-woo-order-tracking'); ?></th>
                    <th><?php _e('Tracking URL', 'carramba-woo-order-tracking'); ?></th>
                    <th><?php _e('Status', 'carramba-woo-order-tracking'); ?></th>
                    <th><?php _e('Actions', 'carramba-woo-order-tracking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($shippers)): ?>
                    <?php foreach ($shippers as $shipper): ?>
                        <tr>
                            <td><?php echo esc_html($shipper->name); ?></td>
                            <td>
                                <code><?php echo esc_html($shipper->tracking_url); ?></code>
                            </td>
                            <td>
                                <span class="cwot-status-<?php echo esc_attr($shipper->status); ?>">
                                    <?php echo ucfirst(esc_html($shipper->status)); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo add_query_arg(array('action' => 'edit', 'shipper_id' => $shipper->id)); ?>" class="button button-small">
                                    <?php _e('Edit', 'carramba-woo-order-tracking'); ?>
                                </a>
                                
                                <form method="post" style="display: inline-block;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this shipper?', 'carramba-woo-order-tracking'); ?>');">
                                    <?php wp_nonce_field('cwot_admin_action', 'cwot_nonce'); ?>
                                    <input type="hidden" name="action" value="delete_shipper">
                                    <input type="hidden" name="shipper_id" value="<?php echo esc_attr($shipper->id); ?>">
                                    <input type="submit" class="button button-small button-link-delete" value="<?php _e('Delete', 'carramba-woo-order-tracking'); ?>">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><?php _e('No shippers found.', 'carramba-woo-order-tracking'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Render add shipper page
     */
    private function render_add_shipper_page() {
        ?>
        <h2><?php _e('Add New Shipper', 'carramba-woo-order-tracking'); ?></h2>
        
        <?php $this->render_shipper_form(); ?>
        <?php
    }
    
    /**
     * Render edit shipper page
     */
    private function render_edit_shipper_page($shipper_id) {
        $shipper = CWOT_Database::get_shipper_by_id($shipper_id);
        
        if (!$shipper) {
            echo '<p>' . __('Shipper not found', 'carramba-woo-order-tracking') . '</p>';
            return;
        }
        ?>
        <h2><?php _e('Edit Shipper', 'carramba-woo-order-tracking'); ?></h2>
        
        <?php $this->render_shipper_form($shipper); ?>
        <?php
    }
    
    /**
     * Render shipper form
     */
    private function render_shipper_form($shipper = null) {
        $is_edit = !empty($shipper);
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('cwot_admin_action', 'cwot_nonce'); ?>
            <input type="hidden" name="action" value="save_shipper">
            <?php if ($is_edit): ?>
                <input type="hidden" name="shipper_id" value="<?php echo esc_attr($shipper->id); ?>">
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="shipper_name"><?php _e('Shipper Name', 'carramba-woo-order-tracking'); ?> *</label>
                    </th>
                    <td>
                        <input type="text" id="shipper_name" name="shipper_name" value="<?php echo $is_edit ? esc_attr($shipper->name) : ''; ?>" class="regular-text" required>
                        <p class="description"><?php _e('Enter the name of the shipping company.', 'carramba-woo-order-tracking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="tracking_url"><?php _e('Tracking URL', 'carramba-woo-order-tracking'); ?> *</label>
                    </th>
                    <td>
                        <input type="url" id="tracking_url" name="tracking_url" value="<?php echo $is_edit ? esc_attr($shipper->tracking_url) : ''; ?>" class="large-text" required>
                        <p class="description">
                            <?php _e('Enter the tracking URL template. Use {tracking_number} as placeholder for the tracking number.', 'carramba-woo-order-tracking'); ?><br>
                            <?php _e('Example: https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}', 'carramba-woo-order-tracking'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="status"><?php _e('Status', 'carramba-woo-order-tracking'); ?></label>
                    </th>
                    <td>
                        <select id="status" name="status">
                            <option value="active" <?php selected($is_edit ? $shipper->status : 'active', 'active'); ?>><?php _e('Active', 'carramba-woo-order-tracking'); ?></option>
                            <option value="inactive" <?php selected($is_edit ? $shipper->status : '', 'inactive'); ?>><?php _e('Inactive', 'carramba-woo-order-tracking'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php echo $is_edit ? __('Update Shipper', 'carramba-woo-order-tracking') : __('Add Shipper', 'carramba-woo-order-tracking'); ?>">
                <a href="<?php echo remove_query_arg(array('action', 'shipper_id')); ?>" class="button"><?php _e('Cancel', 'carramba-woo-order-tracking'); ?></a>
            </p>
        </form>
        <?php
    }
    
    /**
     * Render settings page
     */
    private function render_settings_page() {
        $show_on_order_details = get_option('cwot_show_tracking_on_order_details', true);
        $show_in_emails = get_option('cwot_show_tracking_in_emails', true);
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('cwot_admin_action', 'cwot_nonce'); ?>
            <input type="hidden" name="action" value="save_settings">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php _e('Display Options', 'carramba-woo-order-tracking'); ?>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="cwot_show_tracking_on_order_details" value="1" <?php checked($show_on_order_details, true); ?>>
                                <?php _e('Show tracking information on customer order details page', 'carramba-woo-order-tracking'); ?>
                            </label>
                            <br><br>
                            <label>
                                <input type="checkbox" name="cwot_show_tracking_in_emails" value="1" <?php checked($show_in_emails, true); ?>>
                                <?php _e('Show tracking information in customer email notifications', 'carramba-woo-order-tracking'); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php _e('Control where tracking information is displayed to customers.', 'carramba-woo-order-tracking'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'carramba-woo-order-tracking'); ?>">
            </p>
        </form>
        <?php
    }
}