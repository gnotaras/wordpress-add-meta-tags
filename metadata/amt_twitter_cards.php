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
function amt_add_twitter_cards_metadata_head( $post, $attachments, $embedded_media, $options ) {

    $do_auto_twitter = (($options["auto_twitter"] == "1") ? true : false );
    if (!$do_auto_twitter) {
        return array();
    }

    if ( ! is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // Twitter Cards are added to content pages and attachments only.
        return array();
    }

    $metadata_arr = array();


    // Attachments
    if ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        if ( 'image' == $attachment_type ) {
            
            // $post is an image object

            // Image attachments
            //$image_meta = wp_get_attachment_metadata( $post->ID );   // contains info about all sizes
            // We use wp_get_attachment_image_src() since it constructs the URLs
            $main_size_meta = wp_get_attachment_image_src( $post->ID , 'large' );

            // Type
            $metadata_arr[] = '<meta property="twitter:card" content="photo" />';
            // Author and Publisher
            $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $post ) );
            // Title
            $metadata_arr[] = '<meta property="twitter:title" content="' . esc_attr( get_the_title($post->ID) ) . '" />';
            // Description - We use the description defined by Add-Meta-Tags
            $content_desc = amt_get_content_description( $post );
            if ( ! empty( $content_desc ) ) {
                $metadata_arr[] = '<meta property="twitter:description" content="' . esc_attr( $content_desc ) . '" />';
            }
            // Image
            $metadata_arr[] = '<meta property="twitter:image" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            $metadata_arr[] = '<meta property="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
            $metadata_arr[] = '<meta property="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';

        } elseif ( 'video' == $attachment_type ) {
            // No player card for local video.
            // Will require a page like http://player.vimeo.com/video/VIDEO_ID
        } elseif ( 'audio' == $attachment_type ) {
            // No player card for local audio.
            // Will require a page like http://player.vimeo.com/video/VIDEO_ID
        }


    // Content
    // - standard format (creates summary card)
    // - photo format (creates (summary_large_image card)
    } elseif ( get_post_format($post->ID) === false || get_post_format($post->ID) == 'image' ) {

        // Render a summary card if standard format.
        // Render a summary_large_image card if image format.

        // Type
        if ( get_post_format($post->ID) === false ) {
            $metadata_arr[] = '<meta property="twitter:card" content="summary" />';
            // Set the image size to use
            $image_size = 'medium';
        } else {
            $metadata_arr[] = '<meta property="twitter:card" content="summary_large_image" />';
            // Set the image size to use
            $image_size = 'large';
        }

        // Author and Publisher
        $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $post ) );
        // Title
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta property="twitter:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';
        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta property="twitter:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        // Image
        // Use the FIRST image ONLY

        // Set to true if image meta tags have been added to the card, so that it does not
        // search for any more images.
        $image_metatags_added = false;

        // If the content has a featured image, then we use it.
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {

            $main_size_meta = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $image_size );
            $metadata_arr[] = '<meta property="twitter:image:src" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            $metadata_arr[] = '<meta property="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
            $metadata_arr[] = '<meta property="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';

            // Images have been found.
            $image_metatags_added = true;

        }

        // If a featured image is not set for this content, try to find the first image
        if ( $image_metatags_added === false ) {

            // Process all attachments and add metatags for the first image.
            foreach( $attachments as $attachment ) {

                $mime_type = get_post_mime_type( $attachment->ID );
                //$attachment_type = strstr( $mime_type, '/', true );
                // See why we do not use strstr(): http://www.codetrax.org/issues/1091
                $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

                if ( 'image' == $attachment_type ) {

                    // Image tags
                    $main_size_meta = wp_get_attachment_image_src( $attachment->ID, $image_size );
                    $metadata_arr[] = '<meta property="twitter:image:src" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';

                    // Images have been found.
                    $image_metatags_added = true;

                    // If an image is added, break.
                    break;
                }
            }
        }

        // If a local image-attachment is not set, try to find any embedded images
        if ( $image_metatags_added === false ) {

            // Embedded Media
            foreach( $embedded_media['images'] as $embedded_item ) {

                if ( get_post_format($post->ID) === false ) {
                    $metadata_arr[] = '<meta property="twitter:image:src" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:width" content="150" />';
                    $metadata_arr[] = '<meta property="twitter:image:height" content="150" />';
                } else {
                    $metadata_arr[] = '<meta property="twitter:image:src" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
                }

                // Images have been found.
                $image_metatags_added = true;
                
                // If an image is added, break.
                break;
            }
        }

        // If an image is still missing, then use the default image (if set).
        if ( $image_metatags_added === false && ! empty( $options["default_image_url"] ) ) {
            $metadata_arr[] = '<meta property="twitter:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }


    // Content
    // - gallery format (creates gallery card)
    } elseif ( get_post_format($post->ID) == 'gallery' ) {

        // Render a gallery card if gallery format.

        // Type
        $metadata_arr[] = '<meta property="twitter:card" content="gallery" />';
        // Author and Publisher
        $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $post ) );
        // Title
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta property="twitter:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';
        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta property="twitter:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        // Image counter
        $k = 0;

        // Process all attachments and add metatags for the first image
        foreach( $attachments as $attachment ) {

            $mime_type = get_post_mime_type( $attachment->ID );
            //$attachment_type = strstr( $mime_type, '/', true );
            // See why we do not use strstr(): http://www.codetrax.org/issues/1091
            $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

            if ( 'image' == $attachment_type ) {
                // Image tags
                $main_size_meta = wp_get_attachment_image_src( $attachment->ID, 'medium' );
                $metadata_arr[] = '<meta property="twitter:image' . $k . '" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';

                // Increment the counter
                $k++;
            }
        }

        // Embedded Media
        foreach( $embedded_media['images'] as $embedded_item ) {
            $metadata_arr[] = '<meta property="twitter:image' . $k . '" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';

            // Increment the counter
            $k++;
        }


    // Content
    // - video/audio format (creates player card)
    } elseif ( get_post_format($post->ID) == 'video' || get_post_format($post->ID) == 'audio' ) {

        // Render a player card.

        // Type
        $metadata_arr[] = '<meta property="twitter:card" content="player" />';
        // Author and Publisher
        $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $post ) );
        // Title
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta property="twitter:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';
        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta property="twitter:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        // 
        $video_metatags_set = false;

        /** LOCAL VIDEO/AUDIO ATTACHMENTS NOT SUPPORTED AT THIS TIME
        // Process all attachments and add metatags for the first video
        foreach( $attachments as $attachment ) {

            $mime_type = get_post_mime_type( $attachment->ID );
            //$attachment_type = strstr( $mime_type, '/', true );
            // See why we do not use strstr(): http://www.codetrax.org/issues/1091
            $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

            if ( 'video' == $attachment_type ) {
                // Video tags
                $metadata_arr[] = '<meta property="twitter:player" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
                <meta property="twitter:player" content="https://example.com/embed/a">
                <meta property="twitter:player:width" content="435">
                <meta property="twitter:player:height" content="251">
                <meta property="twitter:player:stream" content="https://example.com/raw-stream/a.mp4">
                <meta property="twitter:player:stream:content_type" content="video/mp4; codecs=&quot;avc1.42E01E1, mp4a.40.2&quot;">
                //
                $video_metatags_set = true;
            }
        }
        */

        // Embedded Media
        foreach( $embedded_media['videos'] as $embedded_item ) {
            // player
            $metadata_arr[] = '<meta property="twitter:player" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
            // Player size
            // Mode 1: Size uses  $content_width
            //global $content_width;
            //$width = $content_width;
            //$height = absint(absint($content_width)*3/4);
            //$metadata_arr[] = '<meta property="twitter:width" content="' . esc_attr( $width ) . '" />';
            //$metadata_arr[] = '<meta property="twitter:height" content="' . esc_attr( $height ) . '" />';
            // Mode 2: Size hard coded
            $metadata_arr[] = '<meta property="twitter:width" content="640" />';
            $metadata_arr[] = '<meta property="twitter:height" content="480" />';
            // image
            if ( ! empty( $embedded_item['thumbnail'] ) ) {
                $metadata_arr[] = '<meta property="twitter:image" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
            }

            //
            $video_metatags_set = true;

            break;
        }

    }

    // Filtering of the generated Opengraph metadata
    $metadata_arr = apply_filters( 'amt_twitter_cards_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Returns author and publisher metatags for Twitter Cards
 */
function amt_get_twitter_cards_author_publisher_metatags( $post ) {
    $metadata_arr = array();
    // Author and Publisher
    $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $post->post_author);
    if ( !empty($twitter_author_username) ) {
        $metadata_arr[] = '<meta property="twitter:creator" content="@' . esc_attr( $twitter_author_username ) . '" />';
    }
    $twitter_publisher_username = get_the_author_meta('amt_twitter_publisher_username', $post->post_author);
    if ( !empty($twitter_publisher_username) ) {
        $metadata_arr[] = '<meta property="twitter:site" content="@' . esc_attr( $twitter_publisher_username ) . '" />';
    }
    return $metadata_arr;
}

