<?php
/**
 * Admin shippers list template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<p>
    <a href="<?php echo esc_url(add_query_arg(array('action' => 'add', 'tab' => 'shippers'))); ?>" class="button button-primary">
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
                        <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'shipper_id' => $shipper->id, 'tab' => 'shippers'))); ?>" class="button button-small">
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
