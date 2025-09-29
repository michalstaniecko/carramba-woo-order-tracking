<?php
/**
 * Order tracking functionality for CWOT plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Order_Tracking {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add tracking fields to order edit page
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'add_tracking_fields_to_order'));
        
        // Save tracking data when order is saved
        add_action('woocommerce_process_shop_order_meta', array($this, 'save_tracking_data'));
        
        // Add tracking column to orders list
        add_filter('manage_edit-shop_order_columns', array($this, 'add_tracking_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_tracking_column'), 10, 2);
        
        // Enqueue scripts for order edit page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_order_scripts'));
    }
    
    /**
     * Enqueue scripts for order edit page
     */
    public function enqueue_order_scripts($hook) {
        global $post;
        
        if ($hook === 'post.php' && isset($post) && $post->post_type === 'shop_order') {
            wp_enqueue_style('cwot-order-style', CWOT_PLUGIN_URL . 'assets/css/order.css', array(), CWOT_VERSION);
            wp_enqueue_script('cwot-order-script', CWOT_PLUGIN_URL . 'assets/js/order.js', array('jquery'), CWOT_VERSION, true);
        }
    }
    
    /**
     * Add tracking fields to order edit page
     */
    public function add_tracking_fields_to_order($order) {
        $order_id = $order->get_id();
        $tracking_shipper_id = get_post_meta($order_id, '_cwot_tracking_shipper_id', true);
        $tracking_number = get_post_meta($order_id, '_cwot_tracking_number', true);
        $shippers = CWOT_Database::get_active_shippers();
        ?>
        <div class="order_data_column">
            <h3><?php _e('Order Tracking', 'carramba-woo-order-tracking'); ?></h3>
            
            <p class="form-field form-field-wide">
                <label for="_cwot_tracking_shipper_id"><?php _e('Shipper:', 'carramba-woo-order-tracking'); ?></label>
                <select id="_cwot_tracking_shipper_id" name="_cwot_tracking_shipper_id" class="wc-enhanced-select" style="width: 100%;">
                    <option value=""><?php _e('Select a shipper...', 'carramba-woo-order-tracking'); ?></option>
                    <?php foreach ($shippers as $shipper): ?>
                        <option value="<?php echo esc_attr($shipper->id); ?>" <?php selected($tracking_shipper_id, $shipper->id); ?>>
                            <?php echo esc_html($shipper->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p class="form-field form-field-wide">
                <label for="_cwot_tracking_number"><?php _e('Tracking Number:', 'carramba-woo-order-tracking'); ?></label>
                <input type="text" id="_cwot_tracking_number" name="_cwot_tracking_number" value="<?php echo esc_attr($tracking_number); ?>" placeholder="<?php _e('Enter tracking number', 'carramba-woo-order-tracking'); ?>" />
            </p>
            
            <?php if ($tracking_shipper_id && $tracking_number): ?>
                <?php 
                $shipper = CWOT_Database::get_shipper_by_id($tracking_shipper_id);
                if ($shipper):
                    $tracking_url = str_replace('{tracking_number}', urlencode($tracking_number), $shipper->tracking_url);
                ?>
                    <p class="form-field form-field-wide">
                        <label><?php _e('Tracking Link:', 'carramba-woo-order-tracking'); ?></label>
                        <a href="<?php echo esc_url($tracking_url); ?>" target="_blank" class="button button-secondary">
                            <?php _e('Track Package', 'carramba-woo-order-tracking'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Save tracking data when order is saved
     */
    public function save_tracking_data($order_id) {
        if (!current_user_can('edit_shop_orders')) {
            return;
        }
        
        // Save shipper ID
        if (isset($_POST['_cwot_tracking_shipper_id'])) {
            $shipper_id = intval($_POST['_cwot_tracking_shipper_id']);
            if ($shipper_id > 0) {
                update_post_meta($order_id, '_cwot_tracking_shipper_id', $shipper_id);
            } else {
                delete_post_meta($order_id, '_cwot_tracking_shipper_id');
            }
        }
        
        // Save tracking number
        if (isset($_POST['_cwot_tracking_number'])) {
            $tracking_number = sanitize_text_field($_POST['_cwot_tracking_number']);
            if (!empty($tracking_number)) {
                update_post_meta($order_id, '_cwot_tracking_number', $tracking_number);
            } else {
                delete_post_meta($order_id, '_cwot_tracking_number');
            }
        }
    }
    
    /**
     * Add tracking column to orders list
     */
    public function add_tracking_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Add tracking column after order status
            if ($key === 'order_status') {
                $new_columns['cwot_tracking'] = __('Tracking', 'carramba-woo-order-tracking');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display tracking column content
     */
    public function display_tracking_column($column, $order_id) {
        if ($column === 'cwot_tracking') {
            $tracking_shipper_id = get_post_meta($order_id, '_cwot_tracking_shipper_id', true);
            $tracking_number = get_post_meta($order_id, '_cwot_tracking_number', true);
            
            if ($tracking_shipper_id && $tracking_number) {
                $shipper = CWOT_Database::get_shipper_by_id($tracking_shipper_id);
                if ($shipper) {
                    $tracking_url = str_replace('{tracking_number}', urlencode($tracking_number), $shipper->tracking_url);
                    echo '<div class="cwot-tracking-info">';
                    echo '<strong>' . esc_html($shipper->name) . '</strong><br>';
                    echo '<a href="' . esc_url($tracking_url) . '" target="_blank" title="' . __('Track package', 'carramba-woo-order-tracking') . '">';
                    echo esc_html($tracking_number);
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<span class="cwot-tracking-error">' . __('Invalid shipper', 'carramba-woo-order-tracking') . '</span>';
                }
            } else {
                echo '<span class="cwot-no-tracking">' . __('No tracking', 'carramba-woo-order-tracking') . '</span>';
            }
        }
    }
    
    /**
     * Get tracking information for an order
     */
    public static function get_order_tracking_info($order_id) {
        $tracking_shipper_id = get_post_meta($order_id, '_cwot_tracking_shipper_id', true);
        $tracking_number = get_post_meta($order_id, '_cwot_tracking_number', true);
        
        if (!$tracking_shipper_id || !$tracking_number) {
            return false;
        }
        
        $shipper = CWOT_Database::get_shipper_by_id($tracking_shipper_id);
        if (!$shipper) {
            return false;
        }
        
        return array(
            'shipper_id' => $tracking_shipper_id,
            'shipper_name' => $shipper->name,
            'tracking_number' => $tracking_number,
            'tracking_url' => str_replace('{tracking_number}', urlencode($tracking_number), $shipper->tracking_url)
        );
    }
}