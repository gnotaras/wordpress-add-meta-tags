<?php
/**
 *  This file is part of the Add-Meta-Tags distribution package.
 *
 *  Add-Meta-Tags is an extension for the WordPress publishing platform.
 *
 *  Homepage:
 *  - http://wordpress.org/plugins/add-meta-tags/
 *  Documentation:
 *  - http://www.codetrax.org/projects/wp-add-meta-tags/wiki
 *  Development Web Site and Bug Tracker:
 *  - http://www.codetrax.org/projects/wp-add-meta-tags
 *  Main Source Code Repository (Mercurial):
 *  - https://bitbucket.org/gnotaras/wordpress-add-meta-tags
 *  Mirror repository (Git):
 *  - https://github.com/gnotaras/wordpress-add-meta-tags
 *  Historical plugin home:
 *  - http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
 *
 *  Licensing Information
 *
 *  Copyright 2006-2013 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  The NOTICE file contains additional licensing and copyright information.
 */


/**
 * Schema.org Metadata
 * http://schema.org
 *
 * Also Google+ author and publisher links in HEAD.
 *
 * Module containing functions related to Schema.org Metadata
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


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
function amt_add_schemaorg_metadata_head( $post, $attachments, $embedded_media, $options ) {

    if ( ! is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // Add these metatags on content pages only.
        return array();
    }

    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    $metadata_arr = array();

    // Publisher
    $googleplus_publisher_url = get_the_author_meta('amt_googleplus_publisher_profile_url', $post->post_author);
    if ( empty( $googleplus_publisher_url ) ) {
        // Link to homepage
        $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
    } else {
        // Link to Google+ publisher profile
        $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( $googleplus_publisher_url, array('http', 'https') ) . '" />';
    }

    // Author
    $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $post->post_author);
    if ( empty( $googleplus_author_url ) ) {
        // Link to the author archive
        $metadata_arr[] = '<link rel="author" type="text/html" title="' . esc_attr( get_the_author_meta('display_name', $post->post_author) ) . '" href="' . esc_attr( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '" />';
    } else {
        // Link to Google+ author profile
        $metadata_arr[] = '<link rel="author" type="text/html" title="' . esc_attr( get_the_author_meta('display_name', $post->post_author) ) . '" href="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
    }

    // Filtering of the generated Google+ metadata
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Add Schema.org Microdata in the footer.
 *
 * Mainly used to embed microdata to front page, posts index page and archives.
 */
function amt_add_schemaorg_metadata_footer( $post, $attachments, $embedded_media, $options ) {

    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    $metadata_arr = array();

    if ( is_paged() ) {
        //
        // Currently we do not support adding Schema.org metadata on
        // paged archives, if page number is >=2
        //
        // NOTE: This refers to an archive or the main page being split up over
        // several pages, this does not refer to a Post or Page whose content
        // has been divided into pages using the <!--nextpage--> QuickTag.
        //
        // Multipage content IS processed below.
        //
        return array();
    }


    // Front page (default page with latest posts or static page used as the front page)
    if ( is_front_page() ) {

        // Organization
        // Scope BEGIN: Organization: http://schema.org/Organization
        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Organization">';
        // Get publisher metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options ) );
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
        // $author = get_queried_object();
        $author = $post;

        // Person
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Person">';
        
        // Get author metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $author->ID ) );

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

    // Get an array containing the attachments
    $attachments = amt_get_ordered_attachments( $post );
    //var_dump($attachments);

    // Get an array containing the URLs of the embedded media
    $embedded_media = amt_get_embedded_media( $post );
    //var_dump($embedded_media);


    // Attachemnts
    if ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        // Early metatags - Scope starts

        if ( 'image' == $attachment_type ) {

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/ImageObject" itemref="comments">';

        } elseif ( 'video' == $attachment_type ) {

            // Scope BEGIN: VideoObject: http://schema.org/VideoObject
            $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/VideoObject" itemref="comments">';

        } elseif ( 'audio' == $attachment_type ) {

            // Scope BEGIN: AudioObject: http://schema.org/AudioObject
            $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/AudioObject" itemref="comments">';

        } else {
            // we do not currently support other attachment types, so we stop processing here
            return $post_body;
        }

        // Metadata commong to all attachments

        // Publisher
        // Scope BEGIN: Organization: http://schema.org/Organization
        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
        $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization">';
        // Get publisher metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options, $post->post_author ) );
        // Scope END: Organization
        $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person">';
        // Get author metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $post->post_author ) );
        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        // Dates
        $metadata_arr[] = '<meta itemprop="datePublished" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
        $metadata_arr[] = '<meta itemprop="dateModified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
        $metadata_arr[] = '<meta itemprop="copyrightYear" content="' . esc_attr( mysql2date('Y', $post->post_date) ) . '" />';

        // Language
        $metadata_arr[] = '<meta itemprop="inLanguage" content="' . esc_attr( str_replace('-', '_', amt_get_language_content()) ) . '" />';


        // Metadata specific to each attachment type

        if ( 'image' == $attachment_type ) {

            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_attachment', 'large' );
            // Get image metatags. $post is an image object.
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $post, $size=$image_size, $is_representative=true ) );
            // Add the post body here
            $metadata_arr[] = $post_body;
            // Scope END: ImageObject
            $metadata_arr[] = '</div> <!-- Scope END: ImageObject -->';

        } elseif ( 'video' == $attachment_type ) {

            // Video specific metatags
            // URL (for attachments: links to attachment page)
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';
            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';
            $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
            // Add the post body here
            $metadata_arr[] = $post_body;
            // Scope END: VideoObject
            $metadata_arr[] = '</div> <!-- Scope END: VideoObject -->';

        } elseif ( 'audio' == $attachment_type ) {

            // Audio specific metatags
            // URL (for attachments: links to attachment page)
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';
            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';
            $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
            // Add the post body here
            $metadata_arr[] = $post_body;
            // Scope END: AudioObject
            $metadata_arr[] = '</div> <!-- Scope END: AudioObject -->';

        }


    // Content
    } else {

        // Scope BEGIN: Article: http://schema.org/Article
        $metadata_arr[] = '<!-- Scope BEGIN: Article -->';
        $metadata_arr[] = '<div itemscope itemtype="http://schema.org/Article" itemref="comments">';

        // Publisher
        // Scope BEGIN: Organization: http://schema.org/Organization
        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
        $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization">';
        // Get publisher metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options, $post->post_author ) );
        // Scope END: Organization
        $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person">';
        // Get publisher metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $post->post_author ) );
        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        // URL - Uses amt_get_permalink_for_multipage()
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( amt_get_permalink_for_multipage($post) ) . '" />';

        // Dates
        $metadata_arr[] = '<meta itemprop="datePublished" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
        $metadata_arr[] = '<meta itemprop="dateModified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
        $metadata_arr[] = '<meta itemprop="copyrightYear" content="' . esc_attr( mysql2date('Y', $post->post_date) ) . '" />';

        // Language
        $metadata_arr[] = '<meta itemprop="inLanguage" content="' . esc_attr( str_replace('-', '_', amt_get_language_content()) ) . '" />';

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

        /*
        // Section: We use the first category as the section
        $first_cat = sanitize_text_field( amt_sanitize_keywords( amt_get_first_category($post) ) );
        if (!empty($first_cat)) {
            $metadata_arr[] = '<meta itemprop="articleSection" content="' . esc_attr( $first_cat ) . '" />';
        }
        */
        foreach( get_the_category($post->ID) as $cat ) {
            $section = trim( $cat->cat_name );
            if ( ! empty( $section ) ) {
                $metadata_arr[] = '<meta itemprop="articleSection" content="' . esc_attr( $section ) . '" />';
            }
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

        // Referenced Items
        $referenced_url_list = amt_get_referenced_items($post);
        foreach ($referenced_url_list as $referenced_url) {
            $referenced_url = trim($referenced_url);
            if ( ! empty( $referenced_url ) ) {
                $metadata_arr[] = '<meta itemprop="referencedItem" content="' . esc_url_raw( $referenced_url ) . '" />';
            }
        }


        // We store the featured image ID in this variable so that it can easily be excluded
        // when all images are parsed from the $attachments array.
        $featured_image_id = 0;
        // Set to true if any image attachments are found. Use to finally add the default image
        // if no image attachments have been found.
        $has_images = false;

        // Scope BEGIN: ImageObject: http://schema.org/ImageObject
        // Image - Featured image is checked first, so that it can be the first associated image.
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
            // Get the image attachment object
            $image = get_post( get_post_thumbnail_id( $post->ID ) );
            // metadata BEGIN
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_content', 'medium' );
            // Get image metatags.
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $image, $size=$image_size ) );
            // metadata END
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            // Finally, set the $featured_image_id
            $featured_image_id = get_post_thumbnail_id( $post->ID );
            // Images have been found.
            $has_images = true;
        }
        // Scope END: ImageObject


        // Process all attachments and add metatags (featured image will be excluded)
        foreach( $attachments as $attachment ) {

            // Excluded the featured image since 
            if ( $attachment->ID != $featured_image_id ) {
                
                $mime_type = get_post_mime_type( $attachment->ID );
                //$attachment_type = strstr( $mime_type, '/', true );
                // See why we do not use strstr(): http://www.codetrax.org/issues/1091
                $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

                if ( 'image' == $attachment_type ) {

                    // metadata BEGIN
                    $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                    $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
                    // Allow filtering of the image size.
                    $image_size = apply_filters( 'amt_image_size_content', 'medium' );
                    // Get image metatags.
                    $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $attachment, $size=$image_size ) );
                    // metadata END
                    $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

                    // Images have been found.
                    $has_images = true;
                    
                } elseif ( 'video' == $attachment_type ) {

                    // Scope BEGIN: VideoObject: http://schema.org/VideoObject
                    $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
                    $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/VideoObject">';
                    // Video specific metatags
                    // URL (for attachments: links to attachment page)
                    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                    $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';
                    $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
                    // Scope END: VideoObject
                    $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';

                } elseif ( 'audio' == $attachment_type ) {

                    // Scope BEGIN: AudioObject: http://schema.org/AudioObject
                    $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
                    $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/AudioObject">';
                    // Audio specific metatags
                    // URL (for attachments: links to attachment page)
                    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                    $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';
                    $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
                    // Scope END: AudioObject
                    $metadata_arr[] = '</span> <!-- Scope END: AudioObject -->';

                }
            }
        }

        // Embedded Media
        foreach( $embedded_media['images'] as $embedded_item ) {

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
            // name (title)
            $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $embedded_item['alt'] ) . '" />';
            // caption
            $metadata_arr[] = '<meta itemprop="caption" content="' . esc_attr( $embedded_item['alt'] ) . '" />';
            // alt
            $metadata_arr[] = '<meta itemprop="text" content="' . esc_attr( $embedded_item['alt'] ) . '" />';
            // URL (links to web page containing the image)
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $embedded_item['page'] ) . '" />';
            // thumbnail url
            $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
            // main image
            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';
            if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
                $metadata_arr[] = '<meta itemprop="encodingFormat" content="image/jpeg" />';
            }
            // embedURL
            $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
            // Scope END: ImageObject
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

            // Images have been found.
            $has_images = true;
        }
        foreach( $embedded_media['videos'] as $embedded_item ) {
            // Scope BEGIN: VideoObject: http://schema.org/VideoObject
            // See: http://googlewebmastercentral.blogspot.gr/2012/02/using-schemaorg-markup-for-videos.html
            // See: https://support.google.com/webmasters/answer/2413309?hl=en
            $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
            $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
            // Video Embed URL
            $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
            // playerType
            $metadata_arr[] = '<meta itemprop="playerType" content="application/x-shockwave-flash" />';
            // size
            $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
            $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
            // Scope END: VideoObject
            $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';
        }
        foreach( $embedded_media['sounds'] as $embedded_item ) {
            // Scope BEGIN: AudioObject: http://schema.org/AudioObject
            $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
            $metadata_arr[] = '<span itemprop="audio" itemscope itemtype="http://schema.org/AudioObject">';
            // Audio Embed URL
            $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
            // playerType
            $metadata_arr[] = '<meta itemprop="playerType" content="application/x-shockwave-flash" />';
            // Scope END: AudioObject
            $metadata_arr[] = '</span> <!-- Scope END: AudioObject -->';
        }

        // If no images have been found so far use the default image, if set.
        // Scope BEGIN: ImageObject: http://schema.org/ImageObject
        if ( $has_images === false && ! empty( $options["default_image_url"] ) ) {
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
        }
        // Scope END: ImageObject

        // Article Body
        // The article body is added after filtering the generated microdata below.

        // TODO: also check: comments, contributor, copyrightHolder, , creator, dateCreated, discussionUrl, editor, version (use post revision if possible)

        // Scope END: Article
        $metadata_arr[] = '</div> <!-- Scope END: Article -->';

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_schemaorg_metadata_content', $metadata_arr );

        // Add articleBody to Artice
        // Now add the article. Remove last closing '</span>' tag, add articleBody and re-add the closing span afterwards.
        $closing_article_tag = array_pop($metadata_arr);
        $metadata_arr[] = '<div itemprop="articleBody">';
        $metadata_arr[] = $post_body;
        $metadata_arr[] = '</div> <!-- Itemprop END: articleBody -->';
        // Now add closing tag for Article
        $metadata_arr[] = $closing_article_tag;
    }

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        array_unshift( $metadata_arr, "<!-- BEGIN Microdata added by Add-Meta-Tags WordPress plugin -->" );
        array_unshift( $metadata_arr, "" );   // Intentionaly left empty
        array_push( $metadata_arr, "<!-- END Microdata added by Add-Meta-Tags WordPress plugin -->" );
        array_push( $metadata_arr, "" );   // Intentionaly left empty
    }

    //return $post_body;
    return implode( PHP_EOL, $metadata_arr );
}
add_filter('the_content', 'amt_add_schemaorg_metadata_content_filter', 500, 1);



/**
 * Return an array of Schema.org metatags for the provided $image object.
 * By default, returns metadata for the 'medium' sized version of the image.
 */
function amt_get_schemaorg_image_metatags( $image, $size='medium', $is_representative=false ) {

    $metadata_arr = array();

    // Get the image object <- Already have it
    //$image = get_post( $post_id );

    // Data for image attachments
    $image_meta = wp_get_attachment_metadata( $image->ID );   // contains info about all sizes
    // We use wp_get_attachment_image_src() since it constructs the URLs
    $thumbnail_meta = wp_get_attachment_image_src( $image->ID , 'thumbnail' );
    $main_size_meta = wp_get_attachment_image_src( $image->ID , $size );

    // name (title)
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_the_title( $image->ID ) ) . '" />';
    // OLD name (title)
    //$image_title = sanitize_text_field( $image->post_title );
    //if ( ! empty( $image_title ) ) {
    //    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $image_title ) . '" />';
    //}

    // URL (links to attachment page)
    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $image->ID ) ) . '" />';

    // Description (generated from $image->post_content. See: amt_get_the_excerpt()
    $image_description = amt_get_content_description($image);
    if ( ! empty( $image_description ) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $image_description ) . '" />';
    }

    // thumbnail url
    $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_meta[0] ) . '" />';

    // main image
    $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
        $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
        $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
        $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( get_post_mime_type( $image->ID ) ) . '" />';
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

    if ( $is_representative === true ) {
        // representativeOfPage - Boolean - Indicates whether this image is representative of the content of the page.
        $metadata_arr[] = '<meta itemprop="representativeOfPage" content="True" />';
    }

    return $metadata_arr;
}


/**
 * Return an array of Schema.org metatags suitable for the publisher object of
 * the content. Accepts the $post object as argument.
 */
function amt_get_schemaorg_publisher_metatags( $options, $author_id=null ) {

    $metadata_arr = array();

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
    if ( ! empty($site_description) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $site_description ) . '" />';
    }
    // logo
    if ( !empty($options["default_image_url"]) ) {
        $metadata_arr[] = '<meta itemprop="logo" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
    }
    // url
    // NOTE: if no author id has been provided, use the blog url.
    if ( $author_id === null ) {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
    } else {
        // If a Google+ publisher profile URL has been provided, it has priority,
        // Otherwise fall back to the WordPress blog home url.
        $googleplus_publisher_url = get_the_author_meta('amt_googleplus_publisher_profile_url', $author_id);
        if ( ! empty( $googleplus_publisher_url ) ) {
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $googleplus_publisher_url, array('http', 'https') ) . '" />';
        } else {
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
        }
    }

    return $metadata_arr;
}


/**
 * Return an array of Schema.org metatags suitable for the author object of
 * the content. Accepts the $post object as argument.
 */
function amt_get_schemaorg_author_metatags( $author_id ) {
//$author_obj = get_user_by( 'id', $author_id );

    $metadata_arr = array();

    // name
    $display_name = get_the_author_meta('display_name', $author_id);
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $display_name ) . '" />';
    // description
    // Here we sanitize the provided description for safety
    $author_description = sanitize_text_field( amt_sanitize_description( get_the_author_meta('description', $author_id) ) );
    if ( !empty($author_description) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $author_description ) . '" />';
    }

    // Profile Image
    $author_email = sanitize_email( get_the_author_meta('user_email', $author_id) );
    $avatar_size = apply_filters( 'amt_avatar_size', 128 );
    $avatar_url = '';
    // First try to get the avatar link by using get_avatar().
    // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
    $avatar_img = get_avatar( get_the_author_meta('ID', $author_id), $avatar_size, '', get_the_author_meta('display_name', $author_id) );
    if ( ! empty($avatar_img) ) {
        if ( preg_match("#src=['\"]([^'\"]+)['\"]#", $avatar_img, $matches) ) {
            $avatar_url = $matches[1];
        }
    } elseif ( ! empty($author_email) ) {
        // If the user has provided an email, we use it to construct a gravatar link.
        $avatar_url = "http://www.gravatar.com/avatar/" . md5( $author_email ) . "?s=" . $avatar_size;
    }
    if ( ! empty($avatar_url) ) {
        //$avatar_url = html_entity_decode($avatar_url, ENT_NOQUOTES, 'UTF-8');
        $metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $avatar_url ) . '" />';
    }

    // url
    // If a Google+ author profile URL has been provided, it has priority,
    // Otherwise fall back to the WordPress author archive.
    $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $author_id);
    if ( !empty($googleplus_author_url) ) {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
    } else {
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_author_posts_url( $author_id ) ) . '" />';
    }
    // second url as sameAs - Note: The get_the_author_meta('user_url', $author_id) is used in the sameAs itemprop.
    $user_url = get_the_author_meta( 'user_url', $author_id );
    if ( !empty($user_url) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $user_url, array('http', 'https') ) . '" />';
    }

    return $metadata_arr;
}

