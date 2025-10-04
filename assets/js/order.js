/* Order edit page JavaScript for CWOT plugin */

jQuery(document).ready(function($) {
    var trackingNumberTemplate = '<div class="cwot-tracking-number-row">' +
        '<input type="text" name="_cwot_tracking_numbers[]" value="" placeholder="' + 
        (typeof cwotData !== 'undefined' ? cwotData.enterTrackingNumber : 'Enter tracking number') + 
        '" class="cwot-tracking-number-input" />' +
        '<button type="button" class="button cwot-remove-tracking-number" title="' + 
        (typeof cwotData !== 'undefined' ? cwotData.remove : 'Remove') + 
        '">×</button>' +
        '</div>';
    
    // Add new tracking number field
    $(document).on('click', '.cwot-add-tracking-number', function(e) {
        e.preventDefault();
        $('.cwot-tracking-numbers-list').append(trackingNumberTemplate);
    });
    
    // Remove tracking number field
    $(document).on('click', '.cwot-remove-tracking-number', function(e) {
        e.preventDefault();
        $(this).closest('.cwot-tracking-number-row').remove();
    });
    
    // Show/hide tracking links based on shipper and tracking number selection
    function toggleTrackingLink() {
        var shipperId = $('#_cwot_tracking_shipper_id').val();
        var hasTrackingNumber = false;
        
        $('.cwot-tracking-number-input').each(function() {
            if ($(this).val().trim() !== '') {
                hasTrackingNumber = true;
                return false; // break
            }
        });
        
        // This would need to be enhanced to dynamically show the tracking link
        // For now, this is a placeholder for future enhancements
    }
    
    // Trigger on shipper or tracking number change
    $(document).on('change keyup', '#_cwot_tracking_shipper_id, .cwot-tracking-number-input', function() {
        toggleTrackingLink();
    });
    
    // Initialize on page load
    toggleTrackingLink();
});