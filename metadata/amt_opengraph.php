<?php
/**
 * Opengraph Protocol Metadata
 * Opengraph Specification: http://ogp.me
 *
 * Module containing functions related to Opengraph Protocol Metadata
 */


/**
 * Add contact method for Facebook author and publisher.
 */
function amt_add_facebook_contactmethod( $contactmethods ) {
    // Add Facebook Author Profile URL
    if ( !isset( $contactmethods['amt_facebook_author_profile_url'] ) ) {
        $contactmethods['amt_facebook_author_profile_url'] = __('Facebook author profile URL', 'add-meta-tags') . ' (AMT)';
    }
    // Add Facebook Publisher Profile URL
    if ( !isset( $contactmethods['amt_facebook_publisher_profile_url'] ) ) {
        $contactmethods['amt_facebook_publisher_profile_url'] = __('Facebook publisher profile URL', 'add-meta-tags') . ' (AMT)';
    }

    // Remove test
    // if ( isset( $contactmethods['test'] ) {
    //     unset( $contactmethods['test'] );
    // }

    return $contactmethods;
}
add_filter( 'user_contactmethods', 'amt_add_facebook_contactmethod', 10, 1 );


/**
 * Generates Opengraph metadata.
 *
 * Currently for:
 * - home page
 * - author archive
 * - content
 */
function amt_add_opengraph_metadata_head( $post ) {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_opengraph = (($options["auto_opengraph"] == "1") ? true : false );
    if (!$do_auto_opengraph) {
        return array();
    }

    $metadata_arr = array();

    if ( is_paged() ) {
        //
        // Currently we do not support adding Opengraph metadata on
        // paged archives, if page number is >=2
        //
        // NOTE: This refers to an archive or the main page being split up over
        // several pages, this does not refer to a Post or Page whose content
        // has been divided into pages using the <!--nextpage--> QuickTag.
        //
        // Multipage content IS processed below.
        //

    } elseif ( amt_is_default_front_page() ) {

        $metadata_arr[] = '<meta property="og:title" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        $metadata_arr[] = '<meta property="og:type" content="website" />';
        // Site Image
        // Use the default image, if one has been set.
        if (!empty($options["default_image_url"])) {
            $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }
        $metadata_arr[] = '<meta property="og:url" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';
        // Site description
        if (!empty($options["site_description"])) {
            $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( $options["site_description"] ) . '" />';
        } elseif (get_bloginfo('description')) {
            $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( get_bloginfo('description') ) . '" />';
        }
        $metadata_arr[] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', get_bloginfo('language')) ) . '" />';
        $metadata_arr[] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';


    } elseif ( is_author() ) {

        // Author object
        // NOTE: Inside the author archives `$post->post_author` does not contain the author object.
        // In this case the $post (get_queried_object()) contains the author object itself.
        // We also can get the author object with the following code. Slug is what WP uses to construct urls.
        // $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
        // Also, ``get_the_author_meta('....', $author)`` returns nothing under author archives.
        // Access user meta with:  $author->description, $author->user_email, etc
        $author = get_queried_object();

        $metadata_arr[] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        $metadata_arr[] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', get_bloginfo('language')) ) . '" />';
        $metadata_arr[] = '<meta property="og:title" content="' . esc_attr( $author->display_name ) . ' profile page" />';
        $metadata_arr[] = '<meta property="og:type" content="profile" />';

        // Profile Image
        // Try to get the gravatar
        // Note: We do not use the get_avatar() function since it returns an img element.
        // Here we do not check if "Show Avatars" is unchecked in Settings > Discussion
        $author_email = sanitize_email( $author->user_email );
        if ( !empty( $author_email ) ) {
            // Contruct gravatar link
            $gravatar_size = 128;
            $gravatar_url = "http://www.gravatar.com/avatar/" . md5( $author_email ) . "?s=" . $gravatar_size;
            $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $gravatar_url ) . '" />';
            $metadata_arr[] = '<meta property="og:imagesecure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $gravatar_url ) ) . '" />';
            $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $gravatar_size ) . '" />';
            $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $gravatar_size ) . '" />';
            $metadata_arr[] = '<meta property="og:image:type" content="image/jpeg" />';
        }

        // url
        // If a Facebook author profile URL has been provided, it has priority,
        // Otherwise fall back to the WordPress author archive.
        $fb_author_url = $author->amt_facebook_author_profile_url;
        if ( !empty($fb_author_url) ) {
            $metadata_arr[] = '<meta property="og:url" content="' . esc_url_raw( $fb_author_url, array('http', 'https') ) . '" />';
        } else {
            $metadata_arr[] = '<meta property="og:url" content="' . esc_url_raw( get_author_posts_url( $author->ID ) ) . '" />';
        }

        // description
        // Here we sanitize the provided description for safety
        $author_description = sanitize_text_field( amt_sanitize_description( $author->description ) );
        if ( !empty($author_description) ) {
            $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( $author_description ) . '" />';
        }

        // Profile first and last name
        $last_name = $author->last_name;
        if ( !empty($last_name) ) {
            $metadata_arr[] = '<meta property="profile:last_name" content="' . esc_attr( $last_name ) . '" />';
        }
        $first_name = $author->first_name;
        if ( !empty($first_name) ) {
            $metadata_arr[] = '<meta property="profile:first_name" content="' . esc_attr( $first_name ) . '" />';
        }

    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        // Title
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta property="og:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';

        // URL
        // TODO: In case of paginated content, get_permalink() still returns the link to the main mage. FIX (#1025)
        $metadata_arr[] = '<meta property="og:url" content="' . esc_url_raw( get_permalink($post->ID) ) . '" />';

        // Image
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {
            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium' );
            $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
            //$metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $thumbnail_info[0]) ) . '" />';
            $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $thumbnail_info[1] ) . '" />';
            $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $thumbnail_info[2] ) . '" />';
        } elseif ( is_attachment() && wp_attachment_is_image($post->ID) ) { // is attachment page and contains an image.
            $attachment_image_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large' );
            $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $attachment_image_info[0] ) . '" />';
            //$metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $attachment_image_info[0]) ) . '" />';
            $metadata_arr[] = '<meta property="og:image:type" content="' . esc_attr( get_post_mime_type($post->ID) ) . '" />';
            $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $attachment_image_info[1] ) . '" />';
            $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $attachment_image_info[2] ) . '" />';
        } elseif (!empty($options["default_image_url"])) {
            // Alternatively, use default image
            $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }

        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        $metadata_arr[] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', get_bloginfo('language')) ) . '" />';
        $metadata_arr[] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';

        // Video
        $video_url = amt_get_video_url();
        if (!empty($video_url)) {
            $metadata_arr[] = '<meta property="og:video" content="' . esc_url_raw( $video_url ) . '" />';
        }

        // Type
        if ( amt_is_static_front_page() ) {
            // If it is the front page (could only be a static page here) set type to 'website'
            $metadata_arr[] = '<meta property="og:type" content="website" />';
        } elseif ( amt_is_static_home() ) {
            // If it is the static page containing the latest posts
            $metadata_arr[] = '<meta property="og:type" content="article" />';
        } else {
            // We treat all other resources as articles for now
            // TODO: Check whether we could use anopther type for image-attachment pages.
            $metadata_arr[] = '<meta property="og:type" content="article" />';
            $metadata_arr[] = '<meta property="article:published_time" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
            $metadata_arr[] = '<meta property="article:modified_time" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';

            // Author
            // If a Facebook author profile URL has been provided, it has priority,
            // Otherwise fall back to the WordPress author archive.
            $fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $post->post_author);
            if ( !empty($fb_author_url) ) {
                $metadata_arr[] = '<meta property="article:author" content="' . esc_url_raw( $fb_author_url, array('http', 'https', 'mailto') ) . '" />';
            } else {
                $metadata_arr[] = '<meta property="article:author" content="' . esc_url_raw( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '" />';
            }

            // Publisher
            // If a Facebook publisher profile URL has been provided, it has priority,
            // Otherwise fall back to the WordPress blog home url.
            $fb_publisher_url = get_the_author_meta('amt_facebook_publisher_profile_url', $post->post_author);
            if ( !empty($fb_publisher_url) ) {
                $metadata_arr[] = '<meta property="article:publisher" content="' . esc_url_raw( $fb_publisher_url, array('http', 'https', 'mailto') ) . '" />';
            } else {
                $metadata_arr[] = '<meta property="article:publisher" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';
            }


            // article:section: We use the first category as the section
            $first_cat = amt_get_first_category($post);
            if (!empty($first_cat)) {
                $metadata_arr[] = '<meta property="article:section" content="' . esc_attr( $first_cat ) . '" />';
            }
            
            // article:tag: Keywords are listed as post tags
            $keywords = explode(', ', amt_get_content_keywords($post));
            foreach ($keywords as $tag) {
                if (!empty($tag)) {
                    $metadata_arr[] = '<meta property="article:tag" content="' . esc_attr( $tag ) . '" />';
                }
            }
        }
    }

    // Filtering of the generated Opengraph metadata
    $metadata_arr = apply_filters( 'amt_opengraph_metadata_head', $metadata_arr );

    return $metadata_arr;
}

