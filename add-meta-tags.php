<?php
/*
Plugin Name: Add Meta Tags
Plugin URI: http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
Description: Adds the <em>Description</em> and <em>Keywords</em> XHTML META tags to your blog's <em>front page</em>, posts, pages, category-based archives and tag-based archives. Also adds <em>Opengraph</em> and <em>Dublin Core</em> metadata on posts and pages.
Version: 2.3.4
Author: George Notaras
Author URI: http://www.g-loaded.eu/
License: Apache License v2
*/

/**
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
*/


// Store plugin directory
define('AMT_DIR', dirname(__FILE__));

// Import modules
require_once(AMT_DIR.'/amt-settings.php');
require_once(AMT_DIR.'/amt-admin-panel.php');
require_once(AMT_DIR.'/amt-utils.php');
require_once(AMT_DIR.'/amt-template-tags.php');


/**
 * Translation Domain
 *
 * Translation files are searched in: wp-content/plugins
 */
load_plugin_textdomain('add-meta-tags', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');


/**
 * Settings Link in the ``Installed Plugins`` page
 */
function amt_plugin_actions( $links, $file ) {
    if( $file == plugin_basename(__FILE__) && function_exists( "admin_url" ) ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=add-meta-tags-options' ) . '">' . __('Settings') . '</a>';
        // Add the settings link before other links
        array_unshift( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'amt_plugin_actions', 10, 2 );



/**
 * Generates basic metadata for the head area.
 *
 */
function amt_add_basic_metadata_head( $post ) {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_description = (($options["auto_description"] == "1") ? true : false );
    $do_keywords = (($options["auto_keywords"] == "1") ? true : false );
    $do_noodp_description = (($options["noodp_description"] == "1") ? true : false );

    // Array to store metadata
    $metadata_arr = array();

    // Add NOODP on posts and pages
    if ( $do_noodp_description && (is_front_page() || is_single() || is_page()) ) {
        $metadata_arr[] = '<meta name="robots" content="NOODP,NOYDIR" />';
    }

    if ( amt_is_default_front_page() ) {
        /*
         * Add META tags to Front Page, only if the 'latest posts' are set to
         * be displayed on the front page in the 'Reading Settings'.
         *
         * Description and Keywords from the Add-Meta-Tags settings override
         * default behaviour.
         *
         * Description and Keywords are always set on the front page regardless
         * of the auto_description and auto_keywords setings.
         */

        // Description
        if ($do_description) {
            // First use the site description from the Add-Meta-Tags settings
            $site_description = $options["site_description"];
            if (empty($site_description)) {
                // Alternatively, use the blog description
                // Here we sanitize the provided description for safety
                $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
            }

            if ( !empty($site_description) ) {
                // If $site_description is not empty, then use it in the description meta-tag of the front page
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $site_description ) ) . '" />';
            }
        }

        // Keywords
        if ($do_keywords) {
            $site_keywords = $options["site_keywords"];
            if (empty($site_keywords)) {
                // Alternatively, use the blog categories
                // Here we sanitize the provided keywords for safety
                $site_keywords = sanitize_text_field( amt_sanitize_keywords( amt_get_all_categories() ) );
            }

            if ( !empty($site_keywords) ) {
                // If $site_keywords is not empty, then use it in the keywords meta-tag of the front page
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $site_keywords ) . '" />';
            }
        }

    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        // Description
        if ($do_description) {
            $description = amt_get_content_description($post, $auto=$do_description);
            if (!empty($description)) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description ) ) . '" />';
            }
        }

        // Keywords
        if ($do_keywords) {
            $keywords = amt_get_content_keywords($post, $auto=$do_keywords);
            if (!empty($keywords)) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />';
            }
        }

        // 'news_keywords'
        $newskeywords = amt_get_post_meta_newskeywords( $post->ID );
        if (!empty($newskeywords)) {
            $metadata_arr[] = '<meta name="news_keywords" content="' . esc_attr( $newskeywords ) . '" />';
        }

        // per post full meta tags
        $full_metatags_for_content = amt_get_post_meta_full_metatags( $post->ID );
        if (!empty($full_metatags_for_content)) {
            $metadata_arr[] = html_entity_decode( stripslashes( $full_metatags_for_content ) );
        }


    } elseif ( is_category() ) {
        /*
         * Write a description META tag only if a description for the current category has been set.
         */
        if ($do_description) {
            // Here we sanitize the provided description for safety
            $description_content = sanitize_text_field( amt_sanitize_description( category_description() ) );
            if (!empty($description_content)) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        /*
         * Write a keyword metatag if there is a category name (always)
         */
        if ($do_keywords) {
            // Here we sanitize the provided keywords for safety
            $cur_cat_name = sanitize_text_field( amt_sanitize_keywords( single_cat_title($prefix = '', $display = false ) ) );
            if ( !empty($cur_cat_name) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $cur_cat_name ) . '" />';
            }
        }

    } elseif ( is_tag() ) {
        /*
         * Writes a description META tag only if a description for the current tag has been set.
         */
        if ($do_description) {
            // Here we sanitize the provided description for safety
            $description_content = sanitize_text_field( amt_sanitize_description( tag_description() ) );
            if (!empty($description_content)) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        /*
         * Write a keyword metatag if there is a tag name (always)
         */
        if ($do_keywords) {
            // Here we sanitize the provided keywords for safety
            $cur_tag_name = sanitize_text_field( amt_sanitize_keywords( single_tag_title($prefix = '', $display = false ) ) );
            if ( !empty($cur_tag_name) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $cur_tag_name ) . '" />';
            }
        }

    } elseif ( is_author() ) {

        // Author object
        // NOTE: Inside the author archives `$post->post_author` does not contain the author object.
        // In this case the $post (get_queried_object()) contains the author object itself.
        // We also can get the author object with the following code. Slug is what WP uses to construct urls.
        // $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
        // Also, ``get_the_author_meta('....', $author)`` returns nothing under author archives.
        // Access user meta with:  $author->description, $author->user_email, etc
        $author = get_queried_object();

        // Write a description META tag only if a bio has been set in the user profile.
        if ($do_description) {
            // Here we sanitize the provided description for safety
            $author_description = sanitize_text_field( amt_sanitize_description( $author->description ) );
            if ( !empty($author_description) ) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( $author_description ) . '" />';
            }
        }
        
        // no keywords meta tag for author archive
        // TODO: add the categories of the posts the author has written.
        
    }

    // Add site wide meta tags
    if (!empty($options["site_wide_meta"])) {
        $metadata_arr[] = html_entity_decode( stripslashes( $options["site_wide_meta"] ) );
    }

    // On every page print the copyright head link
    if (!empty($options["copyright_url"])) {
        $metadata_arr[] = '<link rel="copyright" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . ' Copyright Information" href="' . esc_url_raw( $options["copyright_url"] ) . '" />';
    }

    // Filtering of the generated basic metadata
    $metadata_arr = apply_filters( 'amt_basic_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Twitter Cards
 * Twitter Cards specification: https://dev.twitter.com/docs/cards
 */

/**
 * Add contact method for Twitter username of author and publisher.
 */
function amt_add_twitter_contactmethod( $contactmethods ) {
    // Add Twitter author username
    if ( !isset( $contactmethods['amt_twitter_author_username'] ) ) {
        $contactmethods['amt_twitter_author_username'] = __('Twitter author username', 'add-meta-tags');
    }
    // Add Twitter publisher username
    if ( !isset( $contactmethods['amt_twitter_publisher_username'] ) ) {
        $contactmethods['amt_twitter_publisher_username'] = __('Twitter publisher username', 'add-meta-tags');
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

    $metadata_arr = array();

    // Twitter cards are only added to content
    if ( is_singular() && ! is_front_page() ) {     // is_front_page() is used for the case in which a static page is used as the front page.

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
        } elseif ( is_attachment() && wp_attachment_is_image($post->ID) ) { // is attachment page and contains an image.
            $attachment_image_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large' );
            $metadata_arr[] = '<meta name="twitter:image:src" content="' . esc_url_raw( $attachment_image_info[0] ) . '" />';
            $metadata_arr[] = '<meta name="twitter:image:width" content="' . esc_attr( $attachment_image_info[1] ) . '" />';
            $metadata_arr[] = '<meta name="twitter:image:height" content="' . esc_attr( $attachment_image_info[2] ) . '" />';
        } elseif (!empty($options["default_image_url"])) {
            // Alternatively, use default image
            $metadata_arr[] = '<meta name="twitter:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
        }

    }

    // Filtering of the generated Opengraph metadata
    $metadata_arr = apply_filters( 'amt_twitter_cards_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Opengraph metadata
 * Opengraph Specification: http://ogp.me
 */

/**
 * Add contact method for Facebook author and publisher.
 */
function amt_add_facebook_contactmethod( $contactmethods ) {
    // Add Facebook Author Profile URL
    if ( !isset( $contactmethods['amt_facebook_author_profile_url'] ) ) {
        $contactmethods['amt_facebook_author_profile_url'] = __('Facebook Author Profile URL', 'add-meta-tags');
    }
    // Add Facebook Publisher Profile URL
    if ( !isset( $contactmethods['amt_facebook_publisher_profile_url'] ) ) {
        $contactmethods['amt_facebook_publisher_profile_url'] = __('Facebook Publisher Profile URL', 'add-meta-tags');
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


/**
 * Dublin Core metadata on posts and pages
 * http://dublincore.org/documents/dcmi-terms/
 * 
 */

function amt_add_dublin_core_metadata_head( $post ) {

    if ( !is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // Dublin Core metadata has a meaning for content only.
        return array();
    }

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_dublincore = (($options["auto_dublincore"] == "1") ? true : false );
    if (!$do_auto_dublincore) {
        return array();
    }

    $metadata_arr = array();

    // Title
    // Note: Contains multipage information through amt_process_paged()
    $metadata_arr[] = '<meta name="dc.title" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';

    // Resource identifier
    // TODO: In case of paginated content, get_permalink() still returns the link to the main mage. FIX (#1025)
    $metadata_arr[] = '<meta name="dcterms.identifier" scheme="dcterms.uri" content="' . esc_url_raw( get_permalink($post->ID) ) . '" />';

    $metadata_arr[] = '<meta name="dc.creator" content="' . esc_attr( amt_get_dublin_core_author_notation($post) ) . '" />';
    $metadata_arr[] = '<meta name="dc.date" scheme="dc.w3cdtf" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';

    // Description
    // We use the same description as the ``description`` meta tag.
    // Note: Contains multipage information through amt_process_paged()
    $content_desc = amt_get_content_description($post);
    if ( !empty($content_desc) ) {
        $metadata_arr[] = '<meta name="dc.description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
    }

    // Keywords are in the form: keyword1;keyword2;keyword3
    $metadata_arr[] = '<meta name="dc.subject" content="' . esc_attr( amt_get_content_keywords_mesh($post) ) . '" />';

    $metadata_arr[] = '<meta name="dc.language" scheme="dcterms.rfc4646" content="' . esc_attr( get_bloginfo('language') ) . '" />';
    $metadata_arr[] = '<meta name="dc.publisher" scheme="dcterms.uri" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';

    // Copyright page
    if (!empty($options["copyright_url"])) {
        $metadata_arr[] = '<meta name="dcterms.rights" scheme="dcterms.uri" content="' . esc_url_raw( get_bloginfo('url') ) . '" />';
    }
    // The following requires creative commons configurator
    if (function_exists('bccl_get_license_url')) {
        $metadata_arr[] = '<meta name="dcterms.license" scheme="dcterms.uri" content="' . esc_url_raw( bccl_get_license_url() ) . '" />';
    }

    $metadata_arr[] = '<meta name="dc.coverage" content="World" />';

    /**
     * WordPress Post Formats: http://codex.wordpress.org/Post_Formats
     * Dublin Core Format: http://dublincore.org/documents/dcmi-terms/#terms-format
     * Dublin Core DCMIType: http://dublincore.org/documents/dcmi-type-vocabulary/
     */

    /**
     * TREAT ALL POST FORMATS AS TEXT (for now)
     */
    $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Text" />';
    $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="text/html" />';

    /**
    $format = get_post_format( $post->id );
    if ( empty($format) || $format=="aside" || $format=="link" || $format=="quote" || $format=="status" || $format=="chat") {
        // Default format
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Text" />';
        $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="text/html" />';
    } elseif ($format=="gallery") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Collection" />';
        // $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="image" />';
    } elseif ($format=="image") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Image" />';
        // $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="image/png" />';
    } elseif ($format=="video") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Moving Image" />';
        $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="application/x-shockwave-flash" />';
    } elseif ($format=="audio") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Sound" />';
        $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="audio/mpeg" />';
    }
    */

    // Filtering of the generated Dublin Core metadata
    $metadata_arr = apply_filters( 'amt_dublin_core_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Schema.org Metadata
 * http://schema.org
 *
 * Also Google+ author and publisher links in HEAD.
 */

/**
 * Add contact method for Google+ for author and publisher.
 */
function amt_add_googleplus_contactmethod( $contactmethods ) {
    // Add Google+ author profile URL
    if ( !isset( $contactmethods['amt_googleplus_author_profile_url'] ) ) {
        $contactmethods['amt_googleplus_author_profile_url'] = __('Google+ author profile URL', 'add-meta-tags');
    }
    // Add Google+ publisher profile URL
    if ( !isset( $contactmethods['amt_googleplus_publisher_profile_url'] ) ) {
        $contactmethods['amt_googleplus_publisher_profile_url'] = __('Google+ publisher page URL', 'add-meta-tags');
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

    // Post type check takes place here
    if ( ! is_singular() || is_front_page() ) { // is_front_page() is used for the case in which a static page is used as the front page.
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

    // Post type check has not run, so do it here.
    // Check if metadata is supported on this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        return $post_body;
    }

    // Scope BEGIN: Article: http://schema.org/Article
    $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Article">';

    // name
    // Note: Contains multipage information through amt_process_paged()
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( amt_process_paged( get_the_title($post->ID) ) ) . '" />';

    // headline
    $metadata_arr[] = '<meta itemprop="headline" content="' . esc_attr( get_the_title($post->ID) ) . '" />';

    // URL
    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink($post->ID) ) . '" />';

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

    // Language
    $metadata_arr[] = '<meta itemprop="inLanguage" content="' . esc_attr( str_replace('-', '_', get_bloginfo('language')) ) . '" />';

    // Thumbnail URL
    if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {
        $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
        $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
    }

    // Scope BEGIN: ImageObject: http://schema.org/ImageObject
    $metadata_arr[] = '<span itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
    // Image
    if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {
        $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium' );
        $metadata_arr[] = '<meta itemprop="contentURL" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
        $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $thumbnail_info[1] ) . '" />';
        $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $thumbnail_info[2] ) . '" />';
    } elseif ( is_attachment() && wp_attachment_is_image($post->ID) ) { // is attachment page and contains an image.
        $attachment_image_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large' );
        $metadata_arr[] = '<meta itemprop="contentURL" content="' . esc_url_raw( $attachment_image_info[0] ) . '" />';
        $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( get_post_mime_type($post->ID) ) . '" />';
        $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $attachment_image_info[1] ) . '" />';
        $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $attachment_image_info[2] ) . '" />';
    } elseif (!empty($options["default_image_url"])) {
        // Alternatively, use default image
        $metadata_arr[] = '<meta itemprop="contentURL" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
    }
    // TODO: caption
    // Scope END: ImageObject
    $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
    
    // Video
    $video_url = amt_get_video_url();
    if (!empty($video_url)) {
        // Scope BEGIN: VideoObject: http://schema.org/VideoObject
        // See: http://googlewebmastercentral.blogspot.gr/2012/02/using-schemaorg-markup-for-videos.html
        // See: https://support.google.com/webmasters/answer/2413309?hl=en
        $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
        // Video Embed URL
        $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $video_url ) . '" />';
        // Scope END: VideoObject
        $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';
    }

    // Dates
    $metadata_arr[] = '<meta itemprop="datePublished" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
    $metadata_arr[] = '<meta itemprop="dateModified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
    $metadata_arr[] = '<meta itemprop="copyrightYear" content="' . esc_attr( mysql2date('Y', $post->post_date) ) . '" />';

    // Publisher
    // Scope BEGIN: Organization: http://schema.org/Organization
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

    // Article Body
    // The article body is added after filtering the generated microdata below.

    // TODO: also check: comments, contributor, copyrightHolder, , creator, dateCreated, discussionUrl, editor, version (use post revision if possible)
    // Scope END: Article
    $metadata_arr[] = '</span> <!-- Scope END: Article -->';


    // Filtering of the generated Schema.org metadata
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_content', $metadata_arr );

    // Add articleBody to content
    // Now add the article. Remove last closing '</span>' tag, add articleBody and re-add the closing span afterwards.
    $closing_article_tag = array_pop($metadata_arr);
    $metadata_arr[] = '<span itemprop="articleBody">';
    $metadata_arr[] = $post_body;
    $metadata_arr[] = '</span> <!-- Itemprop END: articleBody -->';
    // Now add closing tag for Article
    $metadata_arr[] = $closing_article_tag;

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        array_unshift( $metadata_arr, "<!-- BEGIN Microdata added by Add-Meta-Tags WordPress plugin -->" );
        array_push( $metadata_arr, "<!-- END Microdata added by Add-Meta-Tags WordPress plugin -->" );
    }

    //return $post_body;
    return implode( PHP_EOL, $metadata_arr );
}
add_filter('the_content', 'amt_add_schemaorg_metadata_content_filter', 500, 1);


/**
 * Replaces the text to be used in the title element, if a replacement text has been set.
 */
function amt_custom_title_tag($title) {

    // Get current post object
    $post = get_queried_object();

    // Check if metadata is supported on this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        return $title;
    }

    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
        
        $custom_title = amt_get_post_meta_title( $post->ID );
        if ( !empty($custom_title) ) {
            $custom_title = str_replace('%title%', $title, $custom_title);
            // Note: Contains multipage information through amt_process_paged()
            return esc_attr( amt_process_paged( $custom_title ) );
        }
    }
    // WordPress adds multipage information if a custom title is not set.
    return $title;
}
add_filter('wp_title', 'amt_custom_title_tag', 1000);


/**
 * Returns an array of all the generated metadata for the head area.
 */
function amt_get_metadata_head() {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_add_metadata = true;

    $metadata_arr = array();

    // Check for NOINDEX,FOLLOW on archives.
    // There is no need to further process metadata as we explicitly ask search
    // engines not to index the content.
    if ( is_archive() || is_search() ) {
        if (
            ( is_search() && ($options["noindex_search_results"] == "1") )  ||          // Search results
            ( is_date() && ($options["noindex_date_archives"] == "1") )  ||             // Date and time archives
            ( is_category() && ($options["noindex_category_archives"] == "1") )  ||     // Category archives
            ( is_tag() && ($options["noindex_tag_archives"] == "1") )  ||               // Tag archives
            ( is_author() && ($options["noindex_author_archives"] == "1") )             // Author archives
        ) {
            $metadata_arr[] = '<meta name="robots" content="NOINDEX,FOLLOW" />';
            $do_add_metadata = false;   // No need to process metadata
        }
    }

    // Get current post object
    $post = get_queried_object();

    // Check if metadata should be added to this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        $do_add_metadata = false;
    }

    // Add Metadata
    if ($do_add_metadata) {

        // Basic Meta tags
        $metadata_arr = array_merge($metadata_arr, amt_add_basic_metadata_head($post));
        //var_dump(amt_add_basic_metadata());
        // Add Opengraph
        $metadata_arr = array_merge($metadata_arr, amt_add_opengraph_metadata_head($post));
        // Add Twitter Cards
        $metadata_arr = array_merge($metadata_arr, amt_add_twitter_cards_metadata_head($post));
        // Add Dublin Core
        $metadata_arr = array_merge($metadata_arr, amt_add_dublin_core_metadata_head($post));
        // Add Google+ Author/Publisher links
        $metadata_arr = array_merge($metadata_arr, amt_add_schemaorg_metadata_head($post));
    }

    // Allow filtering of the all the generated metatags
    $metadata_arr = apply_filters( 'amt_metadata_head', $metadata_arr );

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        array_unshift( $metadata_arr, "<!-- BEGIN Metadata added by Add-Meta-Tags WordPress plugin -->" );
        array_push( $metadata_arr, "<!-- END Metadata added by Add-Meta-Tags WordPress plugin -->" );
    }

    return $metadata_arr;
}


/**
 * Prints the generated metadata for the head area.
 */
function amt_add_metadata_head() {
    echo PHP_EOL . implode(PHP_EOL, amt_get_metadata_head()) . PHP_EOL . PHP_EOL;
}
add_action('wp_head', 'amt_add_metadata_head', 0);


/**
 * Returns an array of all the generated metadata for the footer area.
 */
function amt_get_metadata_footer() {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_add_metadata = true;

    $metadata_arr = array();

    // Get current post object
    $post = get_queried_object();

    // Check if metadata should be added to this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        $do_add_metadata = false;
    }

    // Add Metadata
    if ($do_add_metadata) {

        // Add Schema.org Microdata
        $metadata_arr = array_merge($metadata_arr, amt_add_schemaorg_metadata_footer($post));
    }

    // Allow filtering of all the generated metatags
    $metadata_arr = apply_filters( 'amt_metadata_footer', $metadata_arr );

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        array_unshift( $metadata_arr, "<!-- BEGIN Metadata added by Add-Meta-Tags WordPress plugin -->" );
        array_push( $metadata_arr, "<!-- END Metadata added by Add-Meta-Tags WordPress plugin -->" );
    }

    return $metadata_arr;
}


/**
 * Prints the generated metadata for the footer area.
 */
function amt_add_metadata_footer() {
    echo PHP_EOL . implode(PHP_EOL, amt_get_metadata_footer()) . PHP_EOL . PHP_EOL;
}
add_action('wp_footer', 'amt_add_metadata_footer', 0);


/**
 * Review mode
 */

function amt_get_metadata_review() {
    //
    //  TODO: FIX THIS MESS
    //
    //return '<pre>' . amt_metatag_highlighter( htmlspecialchars( amt_add_schemaorg_metadata_content_filter('dzfgdzfdzfdszfzf'), ENT_NOQUOTES) ) . '</pre>';
    // Returns metadata review code
    //return '<pre>' . htmlentities( implode(PHP_EOL, amt_get_metadata_head()) ) . '</pre>';
    $msg = '<span style="text-decoration: underline; color: black;"><span style="font-weight: bold;">NOTE</span>: This box is displayed because <span style="font-weight: bold;">Review Mode</span> has been enabled in' . PHP_EOL . 'the Add-Meta-Tags settings. Only logged in administrators can see this box.</span>' . PHP_EOL . PHP_EOL;
    $msg_body = '<span style="text-decoration: underline; color: black;">The following metadata has been embedded in the body.</span>';
    $metadata = '<pre>';
    $metadata .= $msg . amt_metatag_highlighter( implode(PHP_EOL, amt_get_metadata_head()) ) . PHP_EOL;
    $metadata .= PHP_EOL . $msg_body . PHP_EOL . PHP_EOL . amt_metatag_highlighter( amt_add_schemaorg_metadata_content_filter('') ) . PHP_EOL;
    $metadata .= PHP_EOL . amt_metatag_highlighter( implode(PHP_EOL, amt_get_metadata_footer()) ) . PHP_EOL;
    $metadata .= '</pre>';
    return $metadata;
    //return '<pre lang="XML" line="1">' . implode(PHP_EOL, amt_get_metadata_head()) . '</pre>';
}

function amt_add_metadata_review($post_body) {

    // Get current post object
    $post = get_queried_object();

    // Check if metadata is supported on this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        return $post_body;
    }

    if ( is_singular() || amt_is_static_front_page() ) {

        // Check if Review Mode is enabled
        $options = get_option("add_meta_tags_opts");
        if ( $options["review_mode"] == "0" ) {
            return $post_body;
        }

        // Adds metadata review code only for admins
        $user_info = get_userdata(get_current_user_id());
        
        // See: http://codex.wordpress.org/User_Levels
        // Admin -> User level 10
        if ( $user_info->user_level == '10' ) {
            $post_body = amt_get_metadata_review() . '<br /><br />' . $post_body;
        }

    }

    return $post_body;
}

add_filter('the_content', 'amt_add_metadata_review', 9999);

?>