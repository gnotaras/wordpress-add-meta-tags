<?php
/*
Plugin Name: Add Meta Tags
Plugin URI: http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
Description: Adds the <em>Description</em> and <em>Keywords</em> XHTML META tags to your blog's <em>front page</em>, posts, pages, category-based archives and tag-based archives. Also adds <em>Opengraph</em> and <em>Dublin Core</em> metadata on posts and pages.
Version: 2.3.2
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


//
// Core
//


/**
 * This is a helper function that returns the post's or page's description.
 *
 * Important: MUST return sanitized data.
 *
 */
function amt_get_content_description( $post, $auto=true ) {

    $content_description = '';

    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {    // TODO: check if this check is needed at all!

        $desc_fld_content = amt_get_post_meta_description( $post->ID );

        if ( !empty($desc_fld_content) ) {
            // If there is a custom field, use it
            $content_description = $desc_fld_content;
        } else {
            // Else, use the post's excerpt. Valid for Pages too.
            if ($auto) {
                // Here we sanitize the generated excerpt for safety
                $content_description = sanitize_text_field( amt_sanitize_description( amt_get_the_excerpt($post) ) );
            }
        }
    }
    return $content_description;
}


/**
 * This is a helper function that returns the post's or page's keywords.
 *
 * Important: MUST return sanitized data.
 *
 */
function amt_get_content_keywords($post, $auto=true) {
    
    $content_keywords = '';

    /*
     * Custom post field "keywords" overrides post's categories and tags (tags exist in WordPress 2.3 or newer).
     * %cats% is replaced by the post's categories.
     * %tags% us replaced by the post's tags.
     */
    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        $keyw_fld_content = amt_get_post_meta_keywords( $post->ID );

        // If there is a custom field, use it
        if ( !empty($keyw_fld_content) ) {
            
            // On single posts, expand the %cats% and %tags% placeholders
            if ( is_single() ) {

                // Here we sanitize the provided keywords for safety
                $keywords_from_post_cats = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_post_cats($post) ) );
                $keyw_fld_content = str_replace("%cats%", $keywords_from_post_cats, $keyw_fld_content);

                // Also, the %tags% tag is replaced by the post's tags (WordPress 2.3 or newer)
                if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
                    // Here we sanitize the provided keywords for safety
                    $keywords_from_post_tags = sanitize_text_field( amt_sanitize_keywords( amt_get_post_tags($post) ) );
                    $keyw_fld_content = str_replace("%tags%", $keywords_from_post_tags, $keyw_fld_content);
                }
            }
            $content_keywords .= $keyw_fld_content;

        // Otherwise, generate the keywords from categories and tags
        } elseif ( is_single() ) {  // pages do not support categories and tags
            if ($auto) {
                /*
                 * Add keywords automatically.
                 * Keywords consist of the post's categories and the post's tags (tags exist in WordPress 2.3 or newer).
                 */
                // Here we sanitize the provided keywords for safety
                $keywords_from_post_cats = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_post_cats($post) ) );
                if (!empty($keywords_from_post_cats)) {
                    $content_keywords .= $keywords_from_post_cats;
                }
                // Here we sanitize the provided keywords for safety
                $keywords_from_post_tags = sanitize_text_field( amt_sanitize_keywords( amt_get_post_tags($post) ) );
                if (!empty($keywords_from_post_tags)) {
                    $content_keywords .= ", " . $keywords_from_post_tags;
                }
            }
        }
    }

    /**
     * Finally, add the global keywords, if they are set in the administration panel.
     * If $content_keywords is empty, then no global keyword processing takes place.
     */
    if ( !empty($content_keywords) && ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) ) {
        $options = get_option("add_meta_tags_opts");
        $global_keywords = $options["global_keywords"];
        if (!empty($global_keywords)) {
            if ( strpos($global_keywords, '%contentkw%') ) {
                // The user has used the placeholder ``%contentkw%``. Replace it with the content keywords.
                $content_keywords = str_replace('%contentkw%', $content_keywords, $global_keywords);
            } else {
                // The placeholder ``%contentkw%`` has not been used. Append the content keywords to the global keywords.
                $content_keywords = $global_keywords . ', ' . $content_keywords;
            }
        }
    }

    return $content_keywords;
}


/**
 * This is the main function that actually writes the meta tags to the
 * appropriate page.
 */
function amt_add_meta_tags( $post ) {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_description = (($options["auto_description"] == "1") ? true : false );
    $do_keywords = (($options["auto_keywords"] == "1") ? true : false );
    $do_noodp_description = (($options["noodp_description"] == "1") ? true : false );

    // Array to store metadata
    $metadata_arr = array();

    /**
     * NOODP on posts and pages
     */
    if ( $do_noodp_description && (is_front_page() || is_single() || is_page()) ) {
        $metadata_arr[] = '<meta name="robots" content="NOODP,NOYDIR" />';
    }

    /**
     * Basic Meta tags
     */

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
    $metadata_arr = apply_filters( 'amt_basic_metatags', $metadata_arr );

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
 * Add Opengraph metadata for site and content.
 */
function amt_add_opengraph_metadata( $post ) {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $auto_opengraph = $options["auto_opengraph"];
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

            // Author and Publisher
            $fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $post->post_author);
            if ( !empty($fb_author_url) ) {
                $metadata_arr[] = '<meta property="article:author" content="' . esc_url_raw( $fb_author_url, array('http', 'https', 'mailto') ) . '" />';
            }
            $fb_publisher_url = get_the_author_meta('amt_facebook_publisher_profile_url', $post->post_author);
            if ( !empty($fb_publisher_url) ) {
                $metadata_arr[] = '<meta property="article:publisher" content="' . esc_url_raw( $fb_publisher_url, array('http', 'https', 'mailto') ) . '" />';
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
    $metadata_arr = apply_filters( 'amt_opengraph_metatags', $metadata_arr );

    return $metadata_arr;
}



/**
 * Dublin Core metadata on posts and pages
 * http://dublincore.org/documents/dcmi-terms/
 * 
 */

function amt_add_dublin_core_metadata( $post ) {

    if ( !is_singular() || is_front_page() ) {
        // Dublin Core metadata has a meaning for content only.
        return array();
    }

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $auto_dublincore = $options["auto_dublincore"];
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
    $metadata_arr = apply_filters( 'amt_dublin_core_metatags', $metadata_arr );

    return $metadata_arr;
}


/*
Final
*/

/**
 * Uses the custom title, if one has been set.
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


function amt_get_metadata() {

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
        $metadata_arr = array_merge($metadata_arr, amt_add_meta_tags($post));
        //var_dump(amt_add_meta_tags());
        // Add Opengraph
        $metadata_arr = array_merge($metadata_arr, amt_add_opengraph_metadata($post));
        // Add Dublin Core
        $metadata_arr = array_merge($metadata_arr, amt_add_dublin_core_metadata($post));
    }

    // Allow filtering of the all the generated metatags
    $metadata_arr = apply_filters( 'amt_metatags', $metadata_arr );

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        array_unshift( $metadata_arr, "<!-- BEGIN Metadata added by Add-Meta-Tags WordPress plugin -->" );
        array_push( $metadata_arr, "<!-- END Metadata added by Add-Meta-Tags WordPress plugin -->" );
    }

    return $metadata_arr;
}


function amt_add_metadata() {
    echo PHP_EOL . implode(PHP_EOL, amt_get_metadata()) . PHP_EOL . PHP_EOL;
}
add_action('wp_head', 'amt_add_metadata', 0);



// Review mode

function amt_get_metadata_review() {
    // Returns metadata review code
    //return '<pre>' . htmlentities( implode(PHP_EOL, amt_get_metadata()) ) . '</pre>';
    $msg = '<span style="text-decoration: underline; color: black;"><span style="font-weight: bold;">NOTE</span>: This box is displayed because <span style="font-weight: bold;">Review Mode</span> has been enabled in' . PHP_EOL . 'the Add-Meta-Tags settings. Only logged in administrators can see this box.</span>' . PHP_EOL;
    return '<pre>' . $msg . amt_metatag_highlighter( implode(PHP_EOL, amt_get_metadata()) ) . '</pre>';
    //return '<pre lang="XML" line="1">' . implode(PHP_EOL, amt_get_metadata()) . '</pre>';
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

add_filter('the_content', 'amt_add_metadata_review', 0);

?>