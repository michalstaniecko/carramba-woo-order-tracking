<?php
/**
 * Admin settings page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Order Tracking Settings', 'carramba-woo-order-tracking'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg(array('tab' => 'settings', 'action' => false, 'shipper_id' => false))); ?>" class="nav-tab <?php echo ($active_tab === 'settings') ? 'nav-tab-active' : ''; ?>">
            <?php _e('Settings', 'carramba-woo-order-tracking'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(array('tab' => 'shippers', 'action' => false, 'shipper_id' => false))); ?>" class="nav-tab <?php echo ($active_tab === 'shippers') ? 'nav-tab-active' : ''; ?>">
            <?php _e('Shippers', 'carramba-woo-order-tracking'); ?>
        </a>
    </h2>
    
    <?php if ($active_tab === 'settings'): ?>
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
                                <input type="checkbox" name="cwot_show_in_order_details" value="1" <?php checked($show_in_order_details, 1); ?>>
                                <?php _e('Show tracking information on customer order details page', 'carramba-woo-order-tracking'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="cwot_show_in_email" value="1" <?php checked($show_in_email, 1); ?>>
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
    <?php elseif ($active_tab === 'shippers'): ?>
        <?php include CWOT_PLUGIN_PATH . 'templates/admin/shippers-list.php'; ?>
    <?php endif; ?>
</div>
