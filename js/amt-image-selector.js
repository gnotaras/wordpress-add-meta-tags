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
        metaBox = $('.form-table'), // Your meta box id here
        addImgLink = metaBox.find('#amt_image_selector_button'),
        //delImgLink = metaBox.find( '.delete-custom-img'),
        //imgContainer = metaBox.find( '.amt-preview-image'),
        imgIdInput = metaBox.find( '#default_image_url' );
  
    // ADD IMAGE LINK
    addImgLink.on( 'click', function( event ){
    
        event.preventDefault();
        
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        
        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
          
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            // Send the attachment URL to our custom image input field.
            //imgContainer.append( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );
            //imgContainer.append( '<img src="'+attachment.sizes.thumbnail.url+'" alt="" style="max-width:100%;"/>' );
            //imgContainer.attr('src', attachment.sizes.thumbnail.url);

            // Send the attachment id to our hidden input
            imgIdInput.val( attachment.id );

      // Hide the add image link
      //addImgLink.addClass( 'hidden' );

      // Unhide the remove image link
      //delImgLink.removeClass( 'hidden' );

        });

        // Finally, open the modal on click
        frame.open();
  });

});
