<?php
/**
 * Admin shipper form template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
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
        <a href="<?php echo esc_url(remove_query_arg(array('action', 'shipper_id'))); ?>" class="button"><?php _e('Cancel', 'carramba-woo-order-tracking'); ?></a>
    </p>
</form>
