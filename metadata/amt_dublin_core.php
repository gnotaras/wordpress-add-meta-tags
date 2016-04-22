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
 *  Copyright 2006-2016 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org
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
 * Dublin Core metadata on posts, pages and attachments.
 *
 *  * http://dublincore.org/documents/dcmi-terms/
 *  * http://dublincore.org/documents/dces/
 *  * Examples: http://www.metatags.org/dublin_core_metadata_element_set
 *
 *  * Generic Examples: http://dublincore.org/documents/2001/04/12/usageguide/generic.shtml
 *  * XML examples: http://dublincore.org/documents/dc-xml-guidelines/
 *
 * Module containing functions related to Dublin Core
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


function amt_add_dublin_core_metadata_head( $post, $attachments, $embedded_media, $options ) {

    if ( apply_filters('amt_exclude_dublin_core_metadata', false) ) {
        return array();
    }

    $metadata_arr = array();

    $do_auto_dublincore = (($options["auto_dublincore"] == "1") ? true : false );
    if ( ! $do_auto_dublincore ) {
        return $metadata_arr;
    }

    // Custom content override
    if ( amt_is_custom($post, $options) ) {
        // Return metadata with:
        // add_filter( 'amt_custom_metadata_dublin_core', 'my_function', 10, 5 );
        // Return an array of meta tags. Array item format: ['key_can_be_whatever'] = '<meta name="foo" content="bar" />'
        $metadata_arr = apply_filters( 'amt_custom_metadata_dublin_core', $metadata_arr, $post, $options, $attachments, $embedded_media );
        return $metadata_arr;
    }

    // A front page using a static page has DC metadata.
    if ( ! is_singular() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // Dublin Core metadata has a meaning for content only.
        return array();
    }

    // The Dublin Core metadata generator does not support products or product groups.
    if ( amt_is_product() || amt_is_product_group() ) {
        return array();
    }

    // Title
    // Note: Contains multipage information through amt_process_paged()
    $metadata_arr['dcterms:title'] = '<meta name="dcterms:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';

    // Resource identifier - Uses amt_get_permalink_for_multipage()
    $metadata_arr[] = '<meta name="dcterms:identifier" content="' . esc_url_raw( amt_get_permalink_for_multipage( $post ) ) . '" />';

    $metadata_arr[] = '<meta name="dcterms:creator" content="' . esc_attr( amt_get_dublin_core_author_notation($post) ) . '" />';
    //$metadata_arr[] = '<meta name="dcterms:date" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
    $metadata_arr[] = '<meta name="dcterms:created" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
    $metadata_arr[] = '<meta name="dcterms:available" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
    //$metadata_arr[] = '<meta name="dcterms:issued" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
    $metadata_arr[] = '<meta name="dcterms:modified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
 
    // Description
    // We use the same description as the ``description`` meta tag.
    // Note: Contains multipage information through amt_process_paged()
    $content_desc = amt_get_content_description($post);
    if ( !empty($content_desc) ) {
        $metadata_arr[] = '<meta name="dcterms:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
    }

    // Keywords
    if ( ! is_attachment() ) {  // Attachments do not support keywords
        // dcterms:subject - one for each keyword.
        $keywords = explode(',', amt_get_content_keywords($post));
        foreach ( $keywords as $subject ) {
            $subject = trim( $subject );
            if ( ! empty($subject) ) {
                $metadata_arr[] = '<meta name="dcterms:subject" content="' . esc_attr( $subject ) . '" />';
            }
        }
    }

    $metadata_arr[] = '<meta name="dcterms:language" content="' . esc_attr( amt_get_language_content($options, $post) ) . '" />';
    $metadata_arr[] = '<meta name="dcterms:publisher" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';

    // Copyright page
    $copyright_url = amt_get_site_copyright_url($options);
    if ( empty($copyright_url)) {
        $copyright_url = trailingslashit( get_bloginfo('url') );
    }
    if ( ! empty($copyright_url) ) {
        $metadata_arr[] = '<meta name="dcterms:rights" content="' . esc_url_raw( $copyright_url ) . '" />';
    }

    // License
    $license_url = '';
    // The following requires creative commons configurator
    if (function_exists('bccl_get_license_url')) {
        $license_url = bccl_get_license_url();
    }
    // Allow filtering of the license URL
    $license_url = apply_filters( 'amt_dublin_core_license', $license_url, $post->ID );
    // Add metatag if $license_url is not empty.
    if ( ! empty( $license_url ) ) {
        $metadata_arr[] = '<meta name="dcterms:license" content="' . esc_url_raw( $license_url ) . '" />';
    }

    // Coverage
    $metadata_arr[] = '<meta name="dcterms:coverage" content="World" />';

    if ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        $metadata_arr[] = '<meta name="dcterms:isPartOf" content="' . esc_url_raw( get_permalink( $post->post_parent ) ) . '" />';

        if ( 'image' == $attachment_type ) {
            $metadata_arr[] = '<meta name="dcterms:type" content="Image" />';
            $metadata_arr[] = '<meta name="dcterms:format" content="' . $mime_type . '" />';
        } elseif ( 'video' == $attachment_type ) {
            $metadata_arr[] = '<meta name="dcterms:type" content="MovingImage" />';
            $metadata_arr[] = '<meta name="dcterms:format" content="' . $mime_type . '" />';
        } elseif ( 'audio' == $attachment_type ) {
            $metadata_arr[] = '<meta name="dcterms:type" content="Sound" />';
            $metadata_arr[] = '<meta name="dcterms:format" content="' . $mime_type . '" />';
        }

        // Finally add the hasFormat
        $metadata_arr[] = '<meta name="dcterms:hasFormat" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';

    } else {    // Default: Text
        $metadata_arr[] = '<meta name="dcterms:type" content="Text" />';
        $metadata_arr[] = '<meta name="dcterms:format" content="text/html" />';

        // Add media files

        // Media Limits
        $image_limit = amt_metadata_get_image_limit($options);
        $video_limit = amt_metadata_get_video_limit($options);
        $audio_limit = amt_metadata_get_audio_limit($options);

        // Counters
        $ic = 0;    // image counter
        $vc = 0;    // video counter
        $ac = 0;    // audio counter

        // List attachments
        foreach( $attachments as $attachment ) {

            $mime_type = get_post_mime_type( $attachment->ID );
            //$attachment_type = strstr( $mime_type, '/', true );
            // See why we do not use strstr(): http://www.codetrax.org/issues/1091
            $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

            if ( 'image' == $attachment_type && $ic < $image_limit ) {
                $metadata_arr[] = '<meta name="dcterms:hasPart" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                // Increase image counter
                $ic++;
            } elseif ( 'video' == $attachment_type && $vc < $video_limit ) {
                $metadata_arr[] = '<meta name="dcterms:hasPart" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                // Increase video counter
                $vc++;
            } elseif ( 'audio' == $attachment_type && $ac < $audio_limit ) {
                $metadata_arr[] = '<meta name="dcterms:hasPart" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                // Increase audio counter
                $ac++;
            }
            
        }

        // Embedded Media
        foreach( $embedded_media['images'] as $embedded_item ) {
            if ( $ic == $image_limit ) {
                break;
            }
            $metadata_arr[] = '<meta name="dcterms:hasPart" content="' . esc_url_raw( $embedded_item['page'] ) . '" />';
            // Increase image counter
            $ic++;
        }
        foreach( $embedded_media['videos'] as $embedded_item ) {
            if ( $vc == $video_limit ) {
                break;
            }
            $metadata_arr[] = '<meta name="dcterms:hasPart" content="' . esc_url_raw( $embedded_item['page'] ) . '" />';
            // Increase video counter
            $vc++;
        }
        foreach( $embedded_media['sounds'] as $embedded_item ) {
            if ( $ac == $audio_limit ) {
                break;
            }
            $metadata_arr[] = '<meta name="dcterms:hasPart" content="' . esc_url_raw( $embedded_item['page'] ) . '" />';
            // Increase audio counter
            $ac++;
        }
    }


    /**
     * WordPress Post Formats: http://codex.wordpress.org/Post_Formats
     * Dublin Core Format: http://dublincore.org/documents/dcmi-terms/#terms-format
     * Dublin Core DCMIType: http://dublincore.org/documents/dcmi-type-vocabulary/
     */
    /**
     * TREAT ALL POST FORMATS AS TEXT (for now)
     */
    /**
    $format = get_post_format( $post->id );
    if ( empty($format) || $format=="aside" || $format=="link" || $format=="quote" || $format=="status" || $format=="chat") {
        // Default format
        $metadata_arr[] = '<meta name="dcterms:type" content="Text" />';
        $metadata_arr[] = '<meta name="dcterms:format" content="text/html" />';
    } elseif ($format=="gallery") {
        $metadata_arr[] = '<meta name="dcterms:type" content="Collection" />';
        // $metadata_arr[] = '<meta name="dcterms:format" content="image" />';
    } elseif ($format=="image") {
        $metadata_arr[] = '<meta name="dcterms:type" content="Image" />';
        // $metadata_arr[] = '<meta name="dcterms:format" content="image/png" />';
    } elseif ($format=="video") {
        $metadata_arr[] = '<meta name="dcterms:type" content="Moving Image" />';
        $metadata_arr[] = '<meta name="dcterms:format" content="application/x-shockwave-flash" />';
    } elseif ($format=="audio") {
        $metadata_arr[] = '<meta name="dcterms:type" content="Sound" />';
        $metadata_arr[] = '<meta name="dcterms:format" content="audio/mpeg" />';
    }
    */

    // Filtering of the generated Dublin Core metadata
    $metadata_arr = apply_filters( 'amt_dublin_core_metadata_head', $metadata_arr );

    return $metadata_arr;
}


