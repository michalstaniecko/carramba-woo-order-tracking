/* Admin JavaScript for CWOT plugin */

jQuery(document).ready(function($) {
    // Confirm delete action
    $('.button-link-delete').on('click', function(e) {
        if (!confirm(cwot_admin.confirm_delete)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Validate tracking URL on form submission
    $('form').on('submit', function(e) {
        var trackingUrl = $('#tracking_url').val();
        
        if (trackingUrl && trackingUrl.indexOf('{tracking_number}') === -1) {
            alert('Tracking URL must contain {tracking_number} placeholder.');
            e.preventDefault();
            return false;
        }
    });
});