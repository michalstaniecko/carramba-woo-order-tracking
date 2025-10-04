<?php
/**
 * Email functionality for CWOT plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Email {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add tracking info to customer completed order email
        add_action('woocommerce_email_before_order_table', array($this, 'add_tracking_info_to_email'), 10, 4);
        
        // Add tracking info to order details on my account page
        add_action('woocommerce_order_details_after_order_table', array($this, 'add_tracking_info_to_order_details'));
        
        // Enqueue frontend styles for order tracking display
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    /**
     * Enqueue frontend styles for order tracking display
     */
    public function enqueue_frontend_styles() {
        // Only enqueue on order view pages
        if (is_wc_endpoint_url('view-order') || is_wc_endpoint_url('order-received')) {
            wp_enqueue_style('cwot-order-style', CWOT_PLUGIN_URL . 'assets/css/order.css', array(), CWOT_VERSION);
        }
    }
    
    /**
     * Add tracking information to customer emails
     */
    public function add_tracking_info_to_email($order, $sent_to_admin, $plain_text, $email) {
        // Check if tracking info in email is enabled
        $show_in_email = get_option('cwot_show_in_email', 1);
        if (!$show_in_email) {
            return;
        }
        
        // Only add tracking info to customer completed order email
        if ($email->id !== 'customer_completed_order' || $sent_to_admin) {
            return;
        }
        
        $order_id = $order->get_id();
        $tracking_info = CWOT_Order_Tracking::get_order_tracking_info($order_id);
        
        if (!$tracking_info) {
            return;
        }
        
        if ($plain_text) {
            $this->render_tracking_info_plain_text($tracking_info);
        } else {
            $this->render_tracking_info_html($tracking_info);
        }
    }
    
    /**
     * Add tracking information to order details on my account page
     */
    public function add_tracking_info_to_order_details($order) {
        // Check if tracking info in order details is enabled
        $show_in_order_details = get_option('cwot_show_in_order_details', 1);
        if (!$show_in_order_details) {
            return;
        }
        
        $order_id = $order->get_id();
        $tracking_info = CWOT_Order_Tracking::get_order_tracking_info($order_id);
        
        // Only show if tracking information is available
        if (!$tracking_info) {
            return;
        }
        
        ?>
        <section class="woocommerce-order-tracking">
            <h2><?php _e('Order Tracking', 'carramba-woo-order-tracking'); ?></h2>
            <table class="woocommerce-table woocommerce-table--order-tracking shop_table order_tracking">
                <tbody>
                    <tr>
                        <th><?php _e('Shipping Company:', 'carramba-woo-order-tracking'); ?></th>
                        <td><?php echo esc_html($tracking_info['shipper_name']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Tracking Number:', 'carramba-woo-order-tracking'); ?></th>
                        <td>
                            <a href="<?php echo esc_url($tracking_info['tracking_url']); ?>" target="_blank" class="woocommerce-order-tracking-link">
                                <?php echo esc_html($tracking_info['tracking_number']); ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <a href="<?php echo esc_url($tracking_info['tracking_url']); ?>" target="_blank" class="button wc-forward">
                    <?php _e('Track Your Package', 'carramba-woo-order-tracking'); ?>
                </a>
            </p>
        </section>
        <?php
    }
    
    /**
     * Render tracking info in HTML format for emails
     */
    private function render_tracking_info_html($tracking_info) {
        ?>
        <div style="margin-bottom: 40px;">
            <h2 style="color: #96588a; display: block; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;">
                <?php _e('Order Tracking Information', 'carramba-woo-order-tracking'); ?>
            </h2>
            
            <table cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #636363; border: 1px solid #e5e5e5;" border="1">
                <tbody>
                    <tr>
                        <th style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; background-color: #f8f8f8;" scope="row">
                            <?php _e('Shipping Company:', 'carramba-woo-order-tracking'); ?>
                        </th>
                        <td style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;">
                            <?php echo esc_html($tracking_info['shipper_name']); ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; background-color: #f8f8f8;" scope="row">
                            <?php _e('Tracking Number:', 'carramba-woo-order-tracking'); ?>
                        </th>
                        <td style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;">
                            <a href="<?php echo esc_url($tracking_info['tracking_url']); ?>" style="color: #96588a; font-weight: normal; text-decoration: underline;" target="_blank">
                                <?php echo esc_html($tracking_info['tracking_number']); ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p style="margin: 0 0 16px;">
                <a href="<?php echo esc_url($tracking_info['tracking_url']); ?>" style="background-color: #96588a; border-radius: 3px; color: #ffffff; display: inline-block; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 1; margin: 16px 0 0; padding: 12px 24px; text-decoration: none; text-transform: uppercase; vertical-align: middle;" target="_blank">
                    <?php _e('Track Your Package', 'carramba-woo-order-tracking'); ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render tracking info in plain text format for emails
     */
    private function render_tracking_info_plain_text($tracking_info) {
        echo "\n" . strtoupper(__('Order Tracking Information', 'carramba-woo-order-tracking')) . "\n";
        echo str_repeat('=', 50) . "\n\n";
        
        echo __('Shipping Company:', 'carramba-woo-order-tracking') . ' ' . $tracking_info['shipper_name'] . "\n";
        echo __('Tracking Number:', 'carramba-woo-order-tracking') . ' ' . $tracking_info['tracking_number'] . "\n";
        echo __('Track Your Package:', 'carramba-woo-order-tracking') . ' ' . $tracking_info['tracking_url'] . "\n\n";
    }
}