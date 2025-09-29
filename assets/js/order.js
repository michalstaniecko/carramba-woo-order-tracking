/* Order edit page JavaScript for CWOT plugin */

jQuery(document).ready(function($) {
    // Show/hide tracking link based on shipper and tracking number selection
    function toggleTrackingLink() {
        var shipperId = $('#_cwot_tracking_shipper_id').val();
        var trackingNumber = $('#_cwot_tracking_number').val();
        
        // This would need to be enhanced to dynamically show the tracking link
        // For now, this is a placeholder for future enhancements
    }
    
    // Trigger on shipper or tracking number change
    $('#_cwot_tracking_shipper_id, #_cwot_tracking_number').on('change keyup', function() {
        toggleTrackingLink();
    });
    
    // Initialize on page load
    toggleTrackingLink();
});