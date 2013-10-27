<?php
/**
 * Schema.org Metadata
 * http://schema.org
 *
 * Also Google+ author and publisher links in HEAD.
 *
 * Module containing functions related to Schema.org Metadata
 */


/**
 * Add contact method for Google+ for author and publisher.
 */
function amt_add_googleplus_contactmethod( $contactmethods ) {
    // Add Google+ author profile URL
    if ( !isset( $contactmethods['amt_googleplus_author_profile_url'] ) ) {
        $contactmethods['amt_googleplus_author_profile_url'] = __('Google+ author profile URL', 'add-meta-tags') . ' (AMT)';
    }
    // Add Google+ publisher profile URL
    if ( !isset( $contactmethods['amt_googleplus_publisher_profile_url'] ) ) {
        $contactmethods['amt_googleplus_publisher_profile_url'] = __('Google+ publisher page URL', 'add-meta-tags') . ' (AMT)';
    }
    return $contactmethods;
}
add_filter( 'user_contactmethods', 'amt_add_googleplus_contactmethod', 10, 1 );


/**
 * Adds links with the rel 'author' and 'publisher' to the HEAD of the page for Google+.
 */
function amt_add_schemaorg_metadata_head( $post ) {

    if ( !is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // Add these metatags on content pages only.
        return array();
    }

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    $metadata_arr = array();

    // Publisher
    $googleplus_publisher_url = get_the_author_meta('amt_googleplus_publisher_profile_url', $post->post_author);
    if ( !empty($googleplus_publisher_url) ) {
        $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( $googleplus_publisher_url, array('http', 'https') ) . '" />';
    }

    // Author
    $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $post->post_author);
    if ( !empty($googleplus_author_url) ) {
        $metadata_arr[] = '<link rel="author" type="text/html" title="' . esc_attr( get_the_author_meta('display_name', $post->post_author) ) . '" href="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
    }

    // Filtering of the generated Google+ metadata
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Add Schema.org Microdata in the footer
 *
 * Mainly used to embed microdata to archives.
 */
function amt_add_schemaorg_metadata_footer( $post ) {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    // Get current post object
    $post = get_queried_object();

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

    }

    elseif ( is_front_page() ) {

        // Organization
        // Scope BEGIN: Organization: http://schema.org/Organization
        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Organization">';
        // name
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // description
        // First use the site description from the Add-Meta-Tags settings
        $site_description = $options["site_description"];
        if ( empty($site_description) ) {
            // Alternatively, use the blog description
            // Here we sanitize the provided description for safety
            $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
        }
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $site_description ) . '" />';
        // logo
        if ( !empty($options["default_image_url"]) ) {
            $metadata_arr[] = '<meta itemprop="logo" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }
        // url
        // NOTE: if this is the standard latest posts front page, then directly use the web site url. No author.
        if ( amt_is_default_front_page() ) {
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';
        } else {
            // If a Google+ publisher profile URL has been provided, it has priority,
            // Otherwise fall back to the WordPress blog home url.
            $googleplus_publisher_url = get_the_author_meta('amt_googleplus_publisher_profile_url', $post->post_author);
            if ( !empty($googleplus_publisher_url) ) {
                $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $googleplus_publisher_url, array('http', 'https') ) . '" />';
            } else {
                $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';
            }
        }
        // Scope END: Organization
        $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

    }

    elseif ( is_author() ) {

        // Author object
        // NOTE: Inside the author archives `$post->post_author` does not contain the author object.
        // In this case the $post (get_queried_object()) contains the author object itself.
        // We also can get the author object with the following code. Slug is what WP uses to construct urls.
        // $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
        // Also, ``get_the_author_meta('....', $author)`` returns nothing under author archives.
        // Access user meta with:  $author->description, $author->user_email, etc
        $author = get_queried_object();

        // Person
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Person">';
        // name
        $display_name = $author->display_name;
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $display_name ) . '" />';
        // description
        // Here we sanitize the provided description for safety
        $author_description = sanitize_text_field( amt_sanitize_description( $author->description ) );
        if ( !empty($author_description) ) {
            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $author_description ) . '" />';
        }
        // image
        // Try to get the gravatar
        // Note: We do not use the get_avatar() function since it returns an img element.
        // Here wqe do not check if "Show Avatars" is unchecked in Settings > Discussion
        $author_email = sanitize_email( $author->user_email );
        if ( !empty( $author_email ) ) {
            // Contruct gravatar link
            $gravatar_url = "http://www.gravatar.com/avatar/" . md5( $author_email ) . "?s=" . 128;
            $metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $gravatar_url ) . '" />';
        }
        // url
        // If a Google+ author profile URL has been provided, it has priority,
        // Otherwise fall back to the WordPress author archive.
        $googleplus_author_url = $author->amt_googleplus_author_profile_url;
        if ( !empty($googleplus_author_url) ) {
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
        } else {
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_author_posts_url( $author->ID ) ) . '" />';
        }
        // second url as sameAs
        $user_url = $author->user_url;
        if ( !empty($user_url) ) {
            $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $user_url, array('http', 'https') ) . '" />';
        }
        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

    }

    // Filtering of the generated microdata for footer
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_footer', $metadata_arr );

    return $metadata_arr;
}


/**
 * Filter function that generates and embeds Schema.org metadata in the content.
 */
function amt_add_schemaorg_metadata_content_filter( $post_body ) {

    if ( ! is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // In this filter function we only deal with content and attachments.
        return $post_body;
    }

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return $post_body;
    }

    // Get current post object
    $post = get_queried_object();

    $metadata_arr = array();

    // Since this is a function that is hooked to the 'the_content' filter
    // of WordPress, the post type check has not run, so this happens here.
    // Check if metadata is supported on this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        return $post_body;
    }


    // Metadata common in Articles and Attachments

    if ( is_attachment() ) {
        if ( wp_attachment_is_image( $post->ID ) ) {
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemscope itemtype="http://schema.org/ImageObject">';
        } else {
            // we do not currently support other attachment types, so we stop processing here
            return $post_body;
        }
    } else {
        // Scope BEGIN: Article: http://schema.org/Article
        $metadata_arr[] = '<!-- Scope BEGIN: Article -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Article">';
    }

    // Publisher
    // Scope BEGIN: Organization: http://schema.org/Organization
    $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
    $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization">';
    // name
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
    // description
    // First use the site description from the Add-Meta-Tags settings
    $site_description = $options["site_description"];
    if ( empty($site_description) ) {
        // Alternatively, use the blog description
        // Here we sanitize the provided description for safety
        $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
    }
    $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $site_description ) . '" />';
    // logo
    if ( !empty($options["default_image_url"]) ) {
        $metadata_arr[] = '<meta itemprop="logo" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
    }
    // url
    // If a Google+ publisher profile URL has been provided, it has priority,
    // Otherwise fall back to the WordPress blog home url.
    $googleplus_publisher_url = get_the_author_meta('amt_googleplus_publisher_profile_url', $post->post_author);
    if ( !empty($googleplus_publisher_url) ) {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $googleplus_publisher_url, array('http', 'https') ) . '" />';
    } else {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';
    }
    // Scope END: Organization
    $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

    // Author
    // Scope BEGIN: Person: http://schema.org/Person
    $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
    $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person">';
    // name
    $display_name = get_the_author_meta('display_name', $post->post_author);
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $display_name ) . '" />';
    // description
    // Here we sanitize the provided description for safety
    $author_description = sanitize_text_field( amt_sanitize_description( get_the_author_meta('description', $post->post_author) ) );
    if ( !empty($author_description) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $author_description ) . '" />';
    }
    // image
    // Try to get the gravatar
    // Note: We do not use the get_avatar() function since it returns an img element.
    // Here wqe do not check if "Show Avatars" is unchecked in Settings > Discussion
    // $gravatar_img = get_avatar( get_the_author_meta('ID', $post->post_author), 96, '', get_the_author_meta('display_name', $post->post_author) );
    $author_email = sanitize_email( get_the_author_meta('user_email', $post->post_author) );
    if ( !empty( $author_email ) ) {
        // Contruct gravatar link
        $gravatar_url = "http://www.gravatar.com/avatar/" . md5( $author_email ) . "?s=" . 128;
        $metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $gravatar_url ) . '" />';
    }
    // url
    // If a Google+ author profile URL has been provided, it has priority,
    // Otherwise fall back to the WordPress author archive.
    $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $post->post_author);
    if ( !empty($googleplus_author_url) ) {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
    } else {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '" />';
    }
    // Note: The get_the_author_meta('user_url') is used in the sameAs itemprop.
    $user_url = get_the_author_meta('user_url');
    if ( !empty($user_url) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $user_url, array('http', 'https') ) . '" />';
    }
    // Scope END: Person
    $metadata_arr[] = '</span> <!-- Scope END: Person -->';


    // URL (for attachments: links to attachment page)
    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';

    // Dates
    $metadata_arr[] = '<meta itemprop="datePublished" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
    $metadata_arr[] = '<meta itemprop="dateModified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
    $metadata_arr[] = '<meta itemprop="copyrightYear" content="' . esc_attr( mysql2date('Y', $post->post_date) ) . '" />';

    // Language
    $metadata_arr[] = '<meta itemprop="inLanguage" content="' . esc_attr( str_replace('-', '_', get_bloginfo('language')) ) . '" />';


    // Metadata for Attachments

    if ( is_attachment() ) {

        // Image attachments

        if ( wp_attachment_is_image( $post->ID ) ) {
            
            // $post is an image object

            // Data for image attachments
            $image_meta = wp_get_attachment_metadata( $post->ID );   // contains info about all sizes
            // We use wp_get_attachment_image_src() since it constructs the URLs
            $thumbnail_meta = wp_get_attachment_image_src( $post->ID , 'thumbnail' );
            $main_size_meta = wp_get_attachment_image_src( $post->ID , 'medium' );

        
            // name (title)
            $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_the_title( $post->ID ) ) . '" />';

            // Description (generated from $post->post_content. See: amt_get_the_excerpt()
            $image_description = amt_get_content_description($post);
            if ( ! empty( $image_description ) ) {
                $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $image_description ) . '" />';
            }

            // thumbnail url
            $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_meta[0] ) . '" />';

            // main image
            $metadata_arr[] = '<meta itemprop="contentURL" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
            $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
            $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( get_post_mime_type( $post->ID ) ) . '" />';

            // caption
            // Here we sanitize the provided description for safety
            $image_caption = sanitize_text_field( $post->post_excerpt );
            if ( ! empty( $image_caption ) ) {
                $metadata_arr[] = '<meta itemprop="caption" content="' . esc_attr( $image_caption ) . '" />';
            }

            // alt
            // Here we sanitize the provided description for safety
            $image_alt = sanitize_text_field( get_post_meta( $post->ID, '_wp_attachment_image_alt', true ) );
            if ( ! empty( $image_alt ) ) {
                $metadata_arr[] = '<meta itemprop="text" content="' . esc_attr( $image_alt ) . '" />';
            }

            // representativeOfPage - Boolean - Indicates whether this image is representative of the content of the page.
            $metadata_arr[] = '<meta itemprop="representativeOfPage" content="True" />';

        }

    } else {    // Article

        // name
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';

        // headline
        $metadata_arr[] = '<meta itemprop="headline" content="' . esc_attr( get_the_title($post->ID) ) . '" />';


        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        // Section: We use the first category as the section
        $first_cat = sanitize_text_field( amt_sanitize_keywords( amt_get_first_category($post) ) );
        if (!empty($first_cat)) {
            $metadata_arr[] = '<meta itemprop="articleSection" content="' . esc_attr( $first_cat ) . '" />';
        }

        // Keywords - We use the keywords defined by Add-Meta-Tags
        $keywords = amt_get_content_keywords($post);
        if (!empty($keywords)) {
            $metadata_arr[] = '<meta itemprop="keywords" content="' . esc_attr( $keywords ) . '" />';
        }

        // Thumbnail URL
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {
            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
            $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
        }

        // Scope BEGIN: ImageObject: http://schema.org/ImageObject
        // Image
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
            $image = get_post( get_post_thumbnail_id( $post->ID ) );
            $image_meta = wp_get_attachment_metadata( get_post_thumbnail_id( $post->ID ) );   // contains info about all sizes
            // We use wp_get_attachment_image_src() since it constructs the URLs
            $thumbnail_meta = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
            $main_size_meta = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
            // metadata BEGIN
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
            // main image
            $metadata_arr[] = '<meta itemprop="contentURL" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
            $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
            $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( get_post_mime_type( $image->ID ) ) . '" />';
            // thumbnail url
            $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_meta[0] ) . '" />';
            // url (attachment page
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $image->ID ) ) . '" />';
            // name (title)
            $image_title = sanitize_text_field( $image->post_title );
            if ( ! empty( $image_title ) ) {
                $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $image_title ) . '" />';
            }
            // caption
            // Here we sanitize the provided description for safety
            $image_caption = sanitize_text_field( $image->post_excerpt );
            if ( ! empty( $image_caption ) ) {
                $metadata_arr[] = '<meta itemprop="caption" content="' . esc_attr( $image_caption ) . '" />';
            }
            // alt
            // Here we sanitize the provided description for safety
            $image_alt = sanitize_text_field( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) );
            if ( ! empty( $image_alt ) ) {
                $metadata_arr[] = '<meta itemprop="text" content="' . esc_attr( $image_alt ) . '" />';
            }
            // description (generated from $image->post_content. See: amt_get_the_excerpt()
            $image_description = amt_get_content_description( $image );
            if ( ! empty( $image_description ) ) {
                $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $image_description ) . '" />';
            }
            // metadata END
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

        } elseif (!empty($options["default_image_url"])) {
            // Alternatively, use default image
            $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
            $metadata_arr[] = '<meta itemprop="contentURL" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
        }
        // Scope END: ImageObject
        
        // Video
        $video_url = amt_get_video_url();
        if (!empty($video_url)) {
            // Scope BEGIN: VideoObject: http://schema.org/VideoObject
            // See: http://googlewebmastercentral.blogspot.gr/2012/02/using-schemaorg-markup-for-videos.html
            // See: https://support.google.com/webmasters/answer/2413309?hl=en
            $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
            $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
            // Video Embed URL
            $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $video_url ) . '" />';
            // Scope END: VideoObject
            $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';
        }

        // Article Body
        // The article body is added after filtering the generated microdata below.

        // TODO: also check: comments, contributor, copyrightHolder, , creator, dateCreated, discussionUrl, editor, version (use post revision if possible)

    }


    // Scope END: Article, ImageObject

    if ( is_attachment() ) {
        if ( wp_attachment_is_image( $post->ID ) ) {    // ImageObject
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
        }
    } else {    // Article
        $metadata_arr[] = '</span> <!-- Scope END: Article -->';
    }


    // Filtering of the generated Schema.org metadata
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_content', $metadata_arr );


    // Add articleBody to Artice

    if ( is_attachment() ) {
        // Add the $post_body here
        $metadata_arr[] = $post_body;
        if ( wp_attachment_is_image( $post->ID ) ) {
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
        }
    } else {    // Article
        // Now add the article. Remove last closing '</span>' tag, add articleBody and re-add the closing span afterwards.
        $closing_article_tag = array_pop($metadata_arr);
        $metadata_arr[] = '<span itemprop="articleBody">';
        $metadata_arr[] = $post_body;
        $metadata_arr[] = '</span> <!-- Itemprop END: articleBody -->';
        // Now add closing tag for Article
        $metadata_arr[] = $closing_article_tag;
    }

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        array_unshift( $metadata_arr, "<!-- BEGIN Microdata added by Add-Meta-Tags WordPress plugin -->" );
        array_unshift( $metadata_arr, "" );   // Intentionaly leftempty
        array_push( $metadata_arr, "<!-- END Microdata added by Add-Meta-Tags WordPress plugin -->" );
        array_push( $metadata_arr, "" );   // Intentionaly leftempty
    }

    //return $post_body;
    return implode( PHP_EOL, $metadata_arr );
}
add_filter('the_content', 'amt_add_schemaorg_metadata_content_filter', 500, 1);

