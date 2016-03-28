//
// This file is part of the Add-Meta-Tags plugin for WordPress.
//
// Contains the Add-Meta-Tags admin scripts.
//
// For licensing information, please check the LICENSE file that ships with
// the Add-Meta-Tags distribution package.
//

jQuery( function($) {

    // Set all variables to be used in scope
    var frame,
        //metaBox = $('.form-table'), // Your meta box id here
        metaBox = $('#add-meta-tags-settings, .add-meta-tags-setting'), // Your meta box id here
        //metaBox = $('.form-field'), // Your meta box id here
        //metaBox = $('#add-meta-tags-settings'), // Your meta box id here
        //metaBox = $('#add-meta-tags-settings, .add-meta-tags-setting'), // Your meta box id here
        // Default Image
        buttonSelectDefaultImage = metaBox.find('#amt-default-image-selector-button'),
        inputDefaultImage = metaBox.find( '#default_image_url' );
        // Global Image Override
        buttonSelectImage = metaBox.find('#amt-image-selector-button'),
        inputImage = metaBox.find( '#amt_custom_image_url' );

    // Default Image
    buttonSelectDefaultImage.on( 'click', function( event ){
    
        event.preventDefault();
        
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        
        // Create a new media frame
        frame = wp.media({
            title: 'Select or upload the default image',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
          
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            // Send the attachment id to our hidden input
            inputDefaultImage.val( attachment.id );

        });

        // Finally, open the modal on click
        frame.open();
    });


    // Global Image Override
    buttonSelectImage.on( 'click', function( event ){
    
        event.preventDefault();
        
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        
        // Create a new media frame
        frame = wp.media({
            title: 'Select or upload an image',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
          
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            // Send the attachment id to our hidden input
            inputImage.val( attachment.id );

        });

        // Finally, open the modal on click
        frame.open();
    });

});
