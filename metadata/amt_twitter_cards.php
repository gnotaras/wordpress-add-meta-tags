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
 * Twitter Cards specification: https://dev.twitter.com/cards/overview
 *
 * Module containing functions related to Twitter Cards
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


/**
 * Add contact method for Twitter username of author and publisher.
 */
function amt_add_twitter_contactmethod( $contactmethods ) {

    // Add Twitter author username
    if ( !isset( $contactmethods['amt_twitter_author_username'] ) ) {
        $contactmethods['amt_twitter_author_username'] = __('Twitter author username', 'add-meta-tags') . ' (AMT)';
    }

    // The publisher profile box in the WordPress user profile page can be deactivated via filtering.
    if ( apply_filters( 'amt_allow_publisher_settings_in_user_profiles', false ) ) {
        // Add Twitter publisher username
        if ( !isset( $contactmethods['amt_twitter_publisher_username'] ) ) {
            $contactmethods['amt_twitter_publisher_username'] = __('Twitter publisher username', 'add-meta-tags') . ' (AMT)';
        }
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

    $metadata_arr = array();

    if ( (! is_singular() && ! amt_is_static_home() && ! amt_is_static_front_page())
                || amt_is_default_front_page() || is_category() || is_tag() || is_tax() ) {
    // Note1: is_front_page() is used for the case in which a static page is used as the front page.
    // Note2: product groups should pass the is_tax() validation, so no need for
    // amt_is_product_group(). We do not support other product groups.

        // Default front page containing latest posts
        // Add a basic Twitter Card to the default home page that contains latest posts.
        // If static pages are used as the front page or the latest-posts page,
        // then they are treated as content and are processed below.
        if ( amt_is_default_front_page() ) {
            // Generate the card only if a publisher username has been set in the publisher settings
            if ( ! empty($options['social_main_twitter_publisher_username']) ) {
                // Type
                $metadata_arr[] = '<meta name="twitter:card" content="' . amt_get_default_twitter_card_type($options) . '" />';
                // Creator
                $metadata_arr[] = '<meta name="twitter:creator" content="@' . esc_attr( $options['social_main_twitter_publisher_username'] ) . '" />';
                // Publisher
                $metadata_arr[] = '<meta name="twitter:site" content="@' . esc_attr( $options['social_main_twitter_publisher_username'] ) . '" />';
                // Title
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( amt_process_paged( get_bloginfo('name') ) ) . '" />';
                // Site description - Note: Contains multipage information through amt_process_paged()
                $site_description = amt_get_site_description($options);
                if ( empty( $site_description ) ) {
                    $site_description = get_bloginfo('description');
                }
                if ( ! empty($site_description) ) {
                    $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( amt_process_paged( $site_description ) ) . '" />';
                }
                // Image. Use the default image (if set).
                if ( ! empty( $options["default_image_url"] ) ) {
                    $image_url = apply_filters( 'amt_twitter_cards_image_url_index', $options["default_image_url"] );
                    $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $image_url ) . '" />';
                }
            }

        // Taxonomy archives
        // Note: product groups should pass the is_tax() validation, so no need for
        // amt_is_product_group(). We do not support other product groups.
        } elseif ( is_category() || is_tag() || is_tax() ) {
            // Taxonomy term object.
            // When viewing taxonomy archives, the $post object is the taxonomy term object. Check with: var_dump($post);
            $tax_term_object = $post;
            //var_dump($tax_term_object);

            // Generate the card only if a publisher username has been set in the publisher settings
            if ( ! empty($options['social_main_twitter_publisher_username']) ) {
                // Type
                $metadata_arr[] = '<meta name="twitter:card" content="' . amt_get_default_twitter_card_type($options) . '" />';
                // Creator
                $metadata_arr[] = '<meta name="twitter:creator" content="@' . esc_attr( $options['social_main_twitter_publisher_username'] ) . '" />';
                // Publisher
                $metadata_arr[] = '<meta name="twitter:site" content="@' . esc_attr( $options['social_main_twitter_publisher_username'] ) . '" />';
                // Title
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( amt_process_paged( single_term_title( $prefix = '', $display = false ) ) ) . '" />';
                // Description
                // If set, the description of the custom taxonomy term is used in the 'description' metatag.
                // Otherwise, a generic description is used.
                // Here we sanitize the provided description for safety
                $description_content = sanitize_text_field( amt_sanitize_description( term_description( $tax_term_object->term_id, $tax_term_object->taxonomy ) ) );
                // Note: Contains multipage information through amt_process_paged()
                if ( empty( $description_content ) ) {
                    // Add a filtered generic description.
                    // Filter name
                    if ( is_category() ) {
                        $generic_description = apply_filters( 'amt_generic_description_category_archive', __('Content filed under the %s category.', 'add-meta-tags') );
                    } elseif ( is_tag() ) {
                        $generic_description = apply_filters( 'amt_generic_description_tag_archive', __('Content tagged with %s.', 'add-meta-tags') );
                    } elseif ( is_tax() ) {
                        // Construct the filter name. Template: ``amt_generic_description_TAXONOMYSLUG_archive``
                        $taxonomy_description_filter_name = sprintf( 'amt_generic_description_%s_archive', $tax_term_object->taxonomy);
                        // var_dump($taxonomy_description_filter_name);
                        // Generic description
                        $generic_description = apply_filters( $taxonomy_description_filter_name, __('Content filed under the %s taxonomy.', 'add-meta-tags') );
                    }
                    // Final generic description
                    $generic_description = sprintf( $generic_description, single_term_title( $prefix='', $display=false ) );
                    $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
                } else {
                    $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
                }
                // Image
                // Use a user defined image via filter. Otherwise use default image.
                // First filter using a term/taxonomy agnostic filter name.
                $taxonomy_image_url = apply_filters( 'amt_taxonomy_force_image_url', '', $tax_term_object );
                if ( empty($taxonomy_image_url) ) {
                    // Second filter (term/taxonomy dependent).
                    // Construct the filter name. Template: ``amt_taxonomy_image_url_TAXONOMYSLUG_TERMSLUG``
                    $taxonomy_image_url_filter_name = sprintf( 'amt_taxonomy_image_url_%s_%s', $tax_term_object->taxonomy, $tax_term_object->slug);
                    //var_dump($taxonomy_image_url_filter_name);
                    // The default image, if set, is used by default.
                    $taxonomy_image_url = apply_filters( $taxonomy_image_url_filter_name, $options["default_image_url"] );
                }
                if ( ! empty( $taxonomy_image_url ) ) {
                    $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $taxonomy_image_url ) . '" />';
                }
            }
        
        }

        return $metadata_arr;
    }

    // Further check (required in some reported cases)
    // Go no further if the content type does not validate is_singular().
    if ( ! is_singular() ) {
        return array();
    }

    // Products
    // A 'product' Twitter Card is generated. See: https://dev.twitter.com/cards/types/product
    if ( amt_is_product() ) {

        // Type
        $metadata_arr[] = '<meta name="twitter:card" content="product" />';

        // Author and Publisher
        $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );

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
        // Use the featured image or the default image as a fallback.

        // Set to true if image meta tags have been added to the card, so that it does not
        // search for any more images.
        $image_metatags_added = false;

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $global_image_override_url = amt_get_post_meta_image_url($post->ID);
        if ( $image_metatags_added === false && ! empty( $global_image_override_url ) ) {
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
            // Images have been found.
            $image_metatags_added = true;
        }

        // Set the image size to use
        $image_size = apply_filters( 'amt_image_size_product', 'full' );

        // If the content has a featured image, then we use it.
        if ( $image_metatags_added === false && function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {

            $main_size_meta = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $image_size );
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
            }

            // Images have been found.
            $image_metatags_added = true;

        }

        // If an image is still missing, then use the default image (if set).
        if ( $image_metatags_added === false && ! empty( $options["default_image_url"] ) ) {
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }

        //
        // The Product Twitter Card needs to be extended with the following required
        // in order to be valid: label1, data1, label2, data2
        //
        // For instance:
        //<meta name="twitter:label1" content="Genre">
        //<meta name="twitter:data1" content="Classic Rock">
        //<meta name="twitter:label2" content="Location">
        //<meta name="twitter:data2" content="National">
        //
        // The following filter is provided.

        // Filtering of the generated Twitter Cards metadata. $post is also passed.
        $metadata_arr = apply_filters( 'amt_product_data_twitter_cards', $metadata_arr, $post );


    // Attachments
    } elseif ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        // Images
        if ( 'image' == $attachment_type ) {
            
            // $post is an image attachment

            // Image attachments
            //$image_meta = wp_get_attachment_metadata( $post->ID );   // contains info about all sizes
            // We use wp_get_attachment_image_src() since it constructs the URLs
            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
            $main_size_meta = wp_get_attachment_image_src( $post->ID , $image_size );

            // Type
            $metadata_arr[] = '<meta name="twitter:card" content="photo" />';
            // Author and Publisher
            $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );
            // Title
            $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( get_the_title($post->ID) ) . '" />';
            // Description - We use the description defined by Add-Meta-Tags
            $content_desc = amt_get_content_description( $post );
            if ( ! empty( $content_desc ) ) {
                $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( $content_desc ) . '" />';
            }
            // Image
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
            }

        // Audio & Video
        } elseif ( $options["tc_enable_player_card_local"] == "1" && in_array( $attachment_type, array( 'video', 'audio' ) ) ) {
            // Create player card for local video and audio attachments.

            // $post is an audio or video attachment

            // Type
            $metadata_arr[] = '<meta name="twitter:card" content="player" />';
            // Author and Publisher
            $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );
            // Title
            $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( get_the_title($post->ID) ) . '" />';
            // Description - We use the description defined by Add-Meta-Tags
            $content_desc = amt_get_content_description($post);
            if ( !empty($content_desc) ) {
                $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( $content_desc ) . '" />';
            }

            // twitter:player
            $metadata_arr[] = sprintf( '<meta name="twitter:player" content="%s" />', esc_url_raw( amt_make_https( amt_embed_get_container_url( $post->ID ) ) ) );

            // Player size
            if ( 'video' == $attachment_type ) {
                // Player size (this should be considered irrelevant of the video size)
                $player_size = apply_filters( 'amt_twitter_cards_video_player_size', array(640, 480) );
            } elseif ( 'audio' == $attachment_type ) {
                $player_size = apply_filters( 'amt_twitter_cards_audio_player_size', array(320, 30) );
            }
            // twitter:player:width
            $metadata_arr[] = sprintf( '<meta name="twitter:player:width" content="%d" />', esc_attr( $player_size[0] ) );
            // twitter:player:height
            $metadata_arr[] = sprintf( '<meta name="twitter:player:height" content="%d" />', esc_attr( $player_size[1] ) );
            // twitter:image
            $preview_image_url = amt_embed_get_preview_image( $post->ID );
            if ( ! empty( $preview_image_url ) ) {
                $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( amt_make_https( $preview_image_url ) ) . '" />';
            }
            // twitter:player:stream
            $metadata_arr[] = '<meta name="twitter:player:stream" content="' . esc_url_raw( amt_make_https( amt_embed_get_stream_url( $post->ID ) ) ) . '" />';
            // twitter:player:stream:content_type
            $metadata_arr[] = '<meta name="twitter:player:stream:content_type" content="' . esc_attr( $mime_type ) . '" />';
            //$metadata_arr[] = '<meta name="twitter:player:stream:content_type" content="video/mp4; codecs=&quot;avc1.42E01E1, mp4a.40.2&quot;">';
        }


    // Content
    // - standard format (post_format === false), aside, link, quote, status, chat (create summary card or summary_large_image if enforced)
    // - photo format (creates (summary_large_image card)
    } elseif ( get_post_format($post->ID) === false || in_array( get_post_format($post->ID), array('image', 'aside', 'link', 'quote', 'status', 'chat') ) ) {

        // Render a summary card if standard format (or summary_large_image if enforced).
        // Render a summary_large_image card if image format.

        // Type
        if ( get_post_format($post->ID) === false || in_array( get_post_format($post->ID), array('aside', 'link', 'quote', 'status', 'chat') ) ) {
            $metadata_arr[] = '<meta name="twitter:card" content="' . amt_get_default_twitter_card_type($options) . '" />';
            // Set the image size to use
            $image_size = apply_filters( 'amt_image_size_content', 'full' );
        } elseif ( get_post_format($post->ID) == 'image' ) {
            $metadata_arr[] = '<meta name="twitter:card" content="summary_large_image" />';
            // Set the image size to use
            // Since we need a bigger image, here we filter the image size through 'amt_image_size_attachment',
            // which typically returns a size bigger than 'amt_image_size_content'.
            $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
        }

        // Author and Publisher
        $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );
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
        // Use the FIRST image ONLY

        // Set to true if image meta tags have been added to the card, so that it does not
        // search for any more images.
        $image_metatags_added = false;

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $global_image_override_url = amt_get_post_meta_image_url($post->ID);
        if ( $image_metatags_added === false && ! empty( $global_image_override_url ) ) {
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
            // Images have been found.
            $image_metatags_added = true;
        }

        // If the content has a featured image, then we use it.
        if ( $image_metatags_added === false && function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {

            $main_size_meta = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $image_size );
            $metadata_arr[] = '<meta name="twitter:image:src" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
            if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
            }

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
                    $metadata_arr[] = '<meta name="twitter:image:src" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
                    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                        $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                        $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
                    }

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

                if ( get_post_format($post->ID) === false || in_array( get_post_format($post->ID), array('aside', 'link', 'quote', 'status', 'chat') ) ) {
                    $metadata_arr[] = '<meta name="twitter:image:src" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
                    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                        $metadata_arr[] = '<meta name="twitter:image:width" content="150" />';
                        $metadata_arr[] = '<meta name="twitter:image:height" content="150" />';
                    }
                } elseif ( get_post_format($post->ID) == 'image' ) {
                    $metadata_arr[] = '<meta name="twitter:image:src" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';
                    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                        $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                        $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
                    }
                }

                // Images have been found.
                $image_metatags_added = true;
                
                // If an image is added, break.
                break;
            }
        }

        // If an image is still missing, then use the default image (if set).
        if ( $image_metatags_added === false && ! empty( $options["default_image_url"] ) ) {
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }


    // Content
    // - gallery format (creates gallery card)
    } elseif ( get_post_format($post->ID) == 'gallery' ) {

        // Render a gallery card if gallery format.

        // Type
        $metadata_arr[] = '<meta name="twitter:card" content="gallery" />';
        // Author and Publisher
        $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );
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

        // Set to true if image meta tags have been added to the card, so that it does not
        // search for any more images.
        $image_metatags_added = false;

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $global_image_override_url = amt_get_post_meta_image_url($post->ID);
        if ( $image_metatags_added === false && ! empty( $global_image_override_url ) ) {
            // Note 'image0'
            $metadata_arr[] = '<meta name="twitter:image0" content="' . esc_url_raw( $global_image_override_url ) . '" />';
            // Images have been found.
            $image_metatags_added = true;
        }

        // Build the gallery
        if ( $image_metatags_added === false ) {

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
                    // Allow filtering of the image size.
                    $image_size = apply_filters( 'amt_image_size_content', 'full' );
                    $main_size_meta = wp_get_attachment_image_src( $attachment->ID, $image_size );
                    $metadata_arr[] = '<meta name="twitter:image' . $k . '" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';

                    // Increment the counter
                    $k++;
                }
            }

            // Embedded Media
            foreach( $embedded_media['images'] as $embedded_item ) {
                $metadata_arr[] = '<meta name="twitter:image' . $k . '" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';

                // Increment the counter
                $k++;
            }

        }


    // Content
    // - video/audio format (creates player card)
    // Note: The ``tc_enable_player_card_local`` option is checked after this initial check,
    // because 'player' twitter cards are always generated for embedded audio and video.
    } elseif ( get_post_format($post->ID) == 'video' || get_post_format($post->ID) == 'audio' ) {

        $post_format = get_post_format($post->ID);

        $audio_video_metatags_complete = false;

        // Process local media only if it is allowed by the user.
        if ( $audio_video_metatags_complete === false && $options["tc_enable_player_card_local"] == "1" ) {

            // Local media - Process all attachments and add metatags for the first video
            foreach( $attachments as $attachment ) {

                $mime_type = get_post_mime_type( $attachment->ID );
                //$attachment_type = strstr( $mime_type, '/', true );
                // See why we do not use strstr(): http://www.codetrax.org/issues/1091
                $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );
                // Get attachment metadata from WordPress
                $attachment_metadata = wp_get_attachment_metadata( $attachment->ID );

                // We create player cards for video and audio attachments.
                // The post might have attachments of other types.
                if ( ! in_array( $attachment_type, array( 'video', 'audio' ) ) ) {
                    continue;
                } elseif ( $attachment_type != $post_format ) {
                    continue;
                }

                // Render a player card for the first attached audio or video.

                // twitter:card
                $metadata_arr[] = '<meta name="twitter:card" content="player" />';
                // Author and Publisher
                $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );
                // twitter:title
                // Title - Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';
                // twitter:description
                // Description - We use the description defined by Add-Meta-Tags
                // Note: Contains multipage information through amt_process_paged()
                $content_desc = amt_get_content_description($post);
                if ( !empty($content_desc) ) {
                    $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
                }

                // twitter:player
                $metadata_arr[] = sprintf( '<meta name="twitter:player" content="%s" />', esc_url_raw( amt_make_https( amt_embed_get_container_url( $attachment->ID ) ) ) );

                // Player size
                if ( $post_format == 'video' ) {
                    // Player size (this should be considered irrelevant of the video size)
                    $player_size = apply_filters( 'amt_twitter_cards_video_player_size', array(640, 480) );
                } elseif ( $post_format == 'audio' ) {
                    $player_size = apply_filters( 'amt_twitter_cards_audio_player_size', array(320, 30) );
                }
                // twitter:player:width
                $metadata_arr[] = sprintf( '<meta name="twitter:player:width" content="%d" />', esc_attr( $player_size[0] ) );
                // twitter:player:height
                $metadata_arr[] = sprintf( '<meta name="twitter:player:height" content="%d" />', esc_attr( $player_size[1] ) );
                // twitter:player:stream
                $metadata_arr[] = '<meta name="twitter:player:stream" content="' . esc_url_raw( amt_make_https( amt_embed_get_stream_url( $attachment->ID ) ) ) . '" />';
                // twitter:player:stream:content_type
                $metadata_arr[] = '<meta name="twitter:player:stream:content_type" content="' . esc_attr( $mime_type ) . '" />';
                //$metadata_arr[] = '<meta name="twitter:player:stream:content_type" content="video/mp4; codecs=&quot;avc1.42E01E1, mp4a.40.2&quot;">';
                // twitter:image
                $global_image_override_url = amt_get_post_meta_image_url($post->ID);
                $preview_image_url = amt_embed_get_preview_image( $attachment->ID );
                // First check if a global image override URL has been set in the post's metabox.
                // If yes, use this image URL and override all other images.
                if ( ! empty( $global_image_override_url ) ) {
                    $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
                // Else use the attachment's featured image, if set.
                } elseif ( ! empty( $preview_image_url ) ) {
                    $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( amt_make_https( $preview_image_url ) ) . '" />';
                }

                $audio_video_metatags_complete = true;

                break;
            }
        }

        // Process embedded media only if a twitter player card has not been generated.
        if ( $audio_video_metatags_complete === false ) {

            // Determine the relevant array (videos or sounds)
            if ( $post_format == 'video' ) {
                $embedded_items = $embedded_media['videos'];
            } elseif ( $post_format == 'audio' ) {
                $embedded_items = $embedded_media['sounds'];
            }

            // Embedded Media
            foreach( $embedded_items as $embedded_item ) {

                // Render a player card for the first embedded video.

                // twitter:card
                $metadata_arr[] = '<meta name="twitter:card" content="player" />';
                // Author and Publisher
                $metadata_arr = array_merge( $metadata_arr, amt_get_twitter_cards_author_publisher_metatags( $options, $post ) );
                // twitter:title
                // Title - Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="twitter:title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';
                // twitter:description
                // Description - We use the description defined by Add-Meta-Tags
                // Note: Contains multipage information through amt_process_paged()
                $content_desc = amt_get_content_description($post);
                if ( !empty($content_desc) ) {
                    $metadata_arr[] = '<meta name="twitter:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
                }

                // twitter:player
                $metadata_arr[] = '<meta name="twitter:player" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
                // Player size
                // Alt Method: Size uses  $content_width
                //global $content_width;
                //$width = $content_width;
                //$height = absint(absint($content_width)*3/4);
                //$metadata_arr[] = '<meta name="twitter:width" content="' . esc_attr( $width ) . '" />';
                //$metadata_arr[] = '<meta name="twitter:height" content="' . esc_attr( $height ) . '" />';
                // twitter:player:width
                $metadata_arr[] = sprintf( '<meta name="twitter:player:width" content="%d" />', esc_attr( $embedded_item['width'] ) );
                // twitter:player:height
                $metadata_arr[] = sprintf( '<meta name="twitter:player:height" content="%d" />', esc_attr( $embedded_item['height'] ) );
                // twitter:image
                $global_image_override_url = amt_get_post_meta_image_url($post->ID);
                // First check if a global image override URL has been set in the post's metabox.
                // If yes, use this image URL and override all other images.
                if ( ! empty( $global_image_override_url ) ) {
                    $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
                // Else use the discovered preview image, if any.
                } elseif ( ! empty( $embedded_item['thumbnail'] ) ) {
                    $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
                }

                //
                $audio_video_metatags_complete = true;

                break;
            }
        }

    }

    // Filtering of the generated Twitter Card metadata
    $metadata_arr = apply_filters( 'amt_twitter_cards_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Returns author and publisher metatags for Twitter Cards
 */
function amt_get_twitter_cards_author_publisher_metatags( $options, $post ) {
    $metadata_arr = array();
    // Author and Publisher
    $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $post->post_author);
    if ( !empty($twitter_author_username) ) {
        $metadata_arr[] = '<meta name="twitter:creator" content="@' . esc_attr( $twitter_author_username ) . '" />';
    }
    if ( ! empty($options['social_main_twitter_publisher_username']) ) {
        $metadata_arr[] = '<meta name="twitter:site" content="@' . esc_attr( $options['social_main_twitter_publisher_username'] ) . '" />';
    }
    return $metadata_arr;
}


