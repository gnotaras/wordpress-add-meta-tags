//
// This file is part of the Add-Meta-Tags plugin for WordPress.
//
// Is a script for the Add-Meta-Tags Metadata Review Mode.
//
// For licensing information, please check the LICENSE file that ships with
// the Add-Meta-Tags distribution package.
//

// Detect the press of the Esc button and hide the Metadata Review panel.
jQuery(document).keyup(function(e) {
    // escape key maps to keycode 27
    if (e.keyCode == 27) {
        if (jQuery('#amt-metadata-review').css('display').toLowerCase() == 'none') {
            // If the panel is hidden, show it.
            jQuery('#amt-metadata-review').css('display', 'block');
            jQuery('#amt-metadata-review').focus();
        } else {
            // If the panel is displayed, hide it.
            jQuery('#amt-metadata-review').css('display', 'none');
        }
    }
});
