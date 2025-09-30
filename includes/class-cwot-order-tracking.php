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
        // Add tracking fields to order edit page - moved to sidebar
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_tracking_fields_to_order'));
        
        // Save tracking data when order is saved
        add_action('woocommerce_process_shop_order_meta', array($this, 'save_tracking_data'));
        
        // Add tracking column to orders list - use HPOS compatible hooks
        if ($this->is_hpos_enabled()) {
            add_filter('woocommerce_shop_order_list_table_columns', array($this, 'add_tracking_column'));
            add_action('woocommerce_shop_order_list_table_custom_column', array($this, 'display_tracking_column_hpos'), 10, 2);
        } else {
            add_filter('manage_edit-shop_order_columns', array($this, 'add_tracking_column'));
            add_action('manage_shop_order_posts_custom_column', array($this, 'display_tracking_column'), 10, 2);
        }
        
        // Enqueue scripts for order edit page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_order_scripts'));
    }
    
    /**
     * Check if HPOS is enabled
     */
    private function is_hpos_enabled() {
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return false;
    }
    
    /**
     * Get order meta data - HPOS compatible
     */
    private function get_order_meta($order_id, $meta_key, $single = true) {
        if ($this->is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            return $order ? $order->get_meta($meta_key, $single) : '';
        } else {
            return get_post_meta($order_id, $meta_key, $single);
        }
    }
    
    /**
     * Update order meta data - HPOS compatible
     */
    private function update_order_meta($order_id, $meta_key, $meta_value) {
        if ($this->is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data($meta_key, $meta_value);
                $order->save();
            }
        } else {
            update_post_meta($order_id, $meta_key, $meta_value);
        }
    }
    
    /**
     * Delete order meta data - HPOS compatible
     */
    private function delete_order_meta($order_id, $meta_key) {
        if ($this->is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->delete_meta_data($meta_key);
                $order->save();
            }
        } else {
            delete_post_meta($order_id, $meta_key);
        }
    }
    
    /**
     * Enqueue scripts for order edit page
     */
    public function enqueue_order_scripts($hook) {
        global $post;
        
        // HPOS compatibility - check for new order edit screen or traditional post edit screen
        $is_order_edit = false;
        
        if ($this->is_hpos_enabled()) {
            // For HPOS, check if we're on the order edit screen
            $screen = get_current_screen();
            $is_order_edit = $screen && $screen->id === 'woocommerce_page_wc-orders' && isset($_GET['action']) && $_GET['action'] === 'edit';
        } else {
            // For legacy, check if we're editing a shop_order post
            $is_order_edit = $hook === 'post.php' && isset($post) && $post->post_type === 'shop_order';
        }
        
        if ($is_order_edit) {
            wp_enqueue_style('cwot-order-style', CWOT_PLUGIN_URL . 'assets/css/order.css', array(), CWOT_VERSION);
            wp_enqueue_script('cwot-order-script', CWOT_PLUGIN_URL . 'assets/js/order.js', array('jquery'), CWOT_VERSION, true);
        }
    }
    
    /**
     * Add tracking fields to order edit page
     */
    public function add_tracking_fields_to_order($order) {
        $order_id = $order->get_id();
        $tracking_shipper_id = $this->get_order_meta($order_id, '_cwot_tracking_shipper_id', true);
        $tracking_number = $this->get_order_meta($order_id, '_cwot_tracking_number', true);
        $shippers = CWOT_Database::get_active_shippers();
        ?>
        <div class="order_data_column" style="width: 100%;">
            <h3><?php _e('Order Tracking', 'carramba-woo-order-tracking'); ?></h3>
            
            <p class="form-field">
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
            
            <p class="form-field">
                <label for="_cwot_tracking_number"><?php _e('Tracking Number:', 'carramba-woo-order-tracking'); ?></label>
                <input type="text" id="_cwot_tracking_number" name="_cwot_tracking_number" value="<?php echo esc_attr($tracking_number); ?>" placeholder="<?php _e('Enter tracking number', 'carramba-woo-order-tracking'); ?>" />
            </p>
            
            <?php if ($tracking_shipper_id && $tracking_number): ?>
                <?php 
                $shipper = CWOT_Database::get_shipper_by_id($tracking_shipper_id);
                if ($shipper):
                    $tracking_url = str_replace('{tracking_number}', urlencode($tracking_number), $shipper->tracking_url);
                ?>
                    <p class="form-field">
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
                $this->update_order_meta($order_id, '_cwot_tracking_shipper_id', $shipper_id);
            } else {
                $this->delete_order_meta($order_id, '_cwot_tracking_shipper_id');
            }
        }
        
        // Save tracking number
        if (isset($_POST['_cwot_tracking_number'])) {
            $tracking_number = sanitize_text_field($_POST['_cwot_tracking_number']);
            if (!empty($tracking_number)) {
                $this->update_order_meta($order_id, '_cwot_tracking_number', $tracking_number);
            } else {
                $this->delete_order_meta($order_id, '_cwot_tracking_number');
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
     * Display tracking column content (Legacy)
     */
    public function display_tracking_column($column, $order_id) {
        if ($column === 'cwot_tracking') {
            $this->render_tracking_column_content($order_id);
        }
    }
    
    /**
     * Display tracking column content (HPOS)
     */
    public function display_tracking_column_hpos($column, $order) {
        if ($column === 'cwot_tracking') {
            $this->render_tracking_column_content($order->get_id());
        }
    }
    
    /**
     * Render tracking column content
     */
    private function render_tracking_column_content($order_id) {
        $tracking_shipper_id = $this->get_order_meta($order_id, '_cwot_tracking_shipper_id', true);
        $tracking_number = $this->get_order_meta($order_id, '_cwot_tracking_number', true);
        
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
    
    /**
     * Get tracking information for an order
     */
    public static function get_order_tracking_info($order_id) {
        $instance = self::get_instance();
        $tracking_shipper_id = $instance->get_order_meta($order_id, '_cwot_tracking_shipper_id', true);
        $tracking_number = $instance->get_order_meta($order_id, '_cwot_tracking_number', true);
        
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