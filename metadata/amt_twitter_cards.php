<?php
/**
 * Twitter Cards
 * Twitter Cards specification: https://dev.twitter.com/docs/cards
 *
 * Module containing functions related to Twitter Cards
 */


/**
 * Add contact method for Twitter username of author and publisher.
 */
function amt_add_twitter_contactmethod( $contactmethods ) {
    // Add Twitter author username
    if ( !isset( $contactmethods['amt_twitter_author_username'] ) ) {
        $contactmethods['amt_twitter_author_username'] = __('Twitter author username', 'add-meta-tags') . ' (AMT)';
    }
    // Add Twitter publisher username
    if ( !isset( $contactmethods['amt_twitter_publisher_username'] ) ) {
        $contactmethods['amt_twitter_publisher_username'] = __('Twitter publisher username', 'add-meta-tags') . ' (AMT)';
    }
    return $contactmethods;
}
add_filter( 'user_contactmethods', 'amt_add_twitter_contactmethod', 10, 1 );


/**
 * Generate Twitter Cards metadata for the content pages.
 */
function amt_add_twitter_cards_metadata_head( $post ) {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_twitter = (($options["auto_twitter"] == "1") ? true : false );
    if (!$do_auto_twitter) {
        return array();
    }

    if ( ! is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // Twitter Cards are added to content pages and attachments only.
        return array();
    }

    $metadata_arr = array();

    if ( is_attachment() ) {

        if ( wp_attachment_is_image( $post->ID ) ) {
            
            // $post is an image object

            // Image attachments
            $image_meta = wp_get_attachment_metadata( $post->ID );   // contains info about all sizes
            // We use wp_get_attachment_image_src() since it constructs the URLs
            $main_size_meta = wp_get_attachment_image_src( $post->ID , 'medium' );

            // Type
            $metadata_arr[] = '<meta name="twitter:card" content="photo" />';

            // Author and Publisher
            $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $post->post_author);
            if ( !empty($twitter_author_username) ) {
                $metadata_arr[] = '<meta name="twitter:creator" content="@' . esc_attr( $twitter_author_username ) . '" />';
            }
            $twitter_publisher_username = get_the_author_meta('amt_twitter_publisher_username', $post->post_author);
            if ( !empty($twitter_publisher_username) ) {
                $metadata_arr[] = '<meta name="twitter:site" content="@' . esc_attr( $twitter_publisher_username ) . '" />';
            }

            // Title
            $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( get_the_title($post->ID) ) . '" />';

            // Image
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
            $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';

        }

    } else {    // Text

        // Type
        $metadata_arr[] = '<meta name="twitter:card" content="summary" />';

        // Author and Publisher
        $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $post->post_author);
        if ( !empty($twitter_author_username) ) {
            $metadata_arr[] = '<meta name="twitter:creator" content="@' . esc_attr( $twitter_author_username ) . '" />';
        }
        $twitter_publisher_username = get_the_author_meta('amt_twitter_publisher_username', $post->post_author);
        if ( !empty($twitter_publisher_username) ) {
            $metadata_arr[] = '<meta name="twitter:site" content="@' . esc_attr( $twitter_publisher_username ) . '" />';
        }

        // Title
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';

        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        // Image
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {
            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium' );
            $metadata_arr[] = '<meta name="twitter:image:src" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
            $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $thumbnail_info[1] ) . '" />';
            $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $thumbnail_info[2] ) . '" />';
        } elseif (!empty($options["default_image_url"])) {
            // Alternatively, use default image
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }

    }

    // Filtering of the generated Opengraph metadata
    $metadata_arr = apply_filters( 'amt_twitter_cards_metadata_head', $metadata_arr );

    return $metadata_arr;
}

