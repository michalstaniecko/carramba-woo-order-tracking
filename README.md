# Carramba WooCommerce Order Tracking

A comprehensive WooCommerce order tracking plugin that allows store administrators to manage shipping companies and add tracking information to customer orders.

## Features

- **Shipper Management**: Add, edit, and manage shipping companies with custom tracking URL templates
- **Order Integration**: Add tracking information directly to WooCommerce orders
- **Multiple Tracking Numbers**: Support for orders split across multiple packages with the same shipper
- **Customer Notifications**: Automatically include tracking information in customer completed order emails
- **Order Tracking Display**: Show tracking information on customer account order details pages
- **Admin Interface**: Easy-to-use admin interface integrated with WooCommerce

## Installation

1. Upload the plugin files to the `/wp-content/plugins/carramba-woo-order-tracking` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to WooCommerce > Order Tracking to configure shippers

## Usage

### Managing Shippers

1. Navigate to **WooCommerce > Order Tracking** in your WordPress admin
2. Click **Add New Shipper** to create a new shipping company
3. Enter the shipper name and tracking URL template
4. Use `{tracking_number}` as a placeholder in the URL for the actual tracking number
5. Set the status to Active or Inactive

### Adding Tracking to Orders

1. Edit any WooCommerce order in the admin
2. In the Order Tracking section, select a shipper from the dropdown
3. Enter the tracking number(s)
4. Click **+ Add Another Tracking Number** to add multiple tracking numbers for the same order
5. Save the order

**Note**: All tracking numbers for a single order must use the same shipping company. If you need to use multiple shippers, you may need to split the order.

### Customer Experience

When an order status is changed to "Completed":
- Customers will receive tracking information in their order completion email
- Tracking information will be displayed on their account order details page
- Customers can click the tracking link to track their package directly

## Tracking URL Examples

- **DHL**: `https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}`
- **UPS**: `https://www.ups.com/track?loc=en_US&tracknum={tracking_number}`  
- **FedEx**: `https://www.fedex.com/fedextrack/?tracknumbers={tracking_number}`
- **USPS**: `https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1={tracking_number}`

## Requirements

- WordPress 5.0 or higher
- WooCommerce 4.0 or higher  
- PHP 7.4 or higher

## Support

For support and feature requests, please visit the [GitHub repository](https://github.com/michalstaniecko/carramba-woo-order-tracking).

## License

This plugin is licensed under the GPL v2 or later.
