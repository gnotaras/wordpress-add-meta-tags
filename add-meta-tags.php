<?php
/*
Plugin Name: Add Meta Tags
Plugin URI: http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
Description: Adds the <em>Description</em> and <em>Keywords</em> XHTML META tags to your blog's <em>front page</em>, posts, pages, category-based archives and tag-based archives. Also adds <em>Opengraph</em> and <em>Dublin Core</em> metadata on posts and pages.
Version: 2.3.1
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
 * Accepts any piece of metadata. Checks if current post is paged and, if yes,
 * then it adds the (page N) suffix.
 */
function amt_process_paged($metadata) {
    
    global $paged;

    if (!empty($metadata)) {
        if ( $paged ) {
            $metadata .= ' - Page ' . $paged;
        }
    }

    return $metadata;
}


/**
 * Returns the post's excerpt.
 * This was written in order to get the excerpt *outside* the loop
 * because the get_the_excerpt() function does not work there any more.
 * This function makes the retrieval of the excerpt independent from the
 * WordPress function in order not to break compatibility with older WP versions.
 *
 * Also, this is even better as the algorithm tries to get text of average
 * length 250 characters, which is more SEO friendly. The algorithm is not
 * perfect, but will do for now.
 */
function amt_get_the_excerpt( $post, $excerpt_max_len=300, $desc_avg_length=250, $desc_min_length=150 ) {
    
    if ( empty($post->post_excerpt) ) {

        // Get the initial data for the excerpt
        $amt_excerpt = strip_tags(substr($post->post_content, 0, $excerpt_max_len));

        // If this was not enough, try to get some more clean data for the description (nasty hack)
        if ( strlen($amt_excerpt) < $desc_avg_length ) {
            $amt_excerpt = strip_tags(substr($post->post_content, 0, (int) ($excerpt_max_len * 1.5)));
            if ( strlen($amt_excerpt) < $desc_avg_length ) {
                $amt_excerpt = strip_tags(substr($post->post_content, 0, (int) ($excerpt_max_len * 2)));
            }
        }

        $end_of_excerpt = strrpos($amt_excerpt, ".");

        if ($end_of_excerpt) {
            
            // if there are sentences, end the description at the end of a sentence.
            $amt_excerpt_test = substr($amt_excerpt, 0, $end_of_excerpt + 1);

            if ( strlen($amt_excerpt_test) < $desc_min_length ) {
                // don't end at the end of the sentence because the description would be too small
                $amt_excerpt .= "...";
            } else {
                // If after ending at the end of a sentence the description has an acceptable length, use this
                $amt_excerpt = $amt_excerpt_test;
            }
        } else {
            // otherwise (no end-of-sentence in the excerpt) add this stuff at the end of the description.
            $amt_excerpt .= "...";
        }

    } else {
        // When the post excerpt has been set explicitly, then it has priority.
        $amt_excerpt = $post->post_excerpt;
    }

    /**
     * In some cases, the algorithm might not work, depending on the content.
     * In those cases, $amt_excerpt might only contain ``...``. Here we perform
     * a check for this and return an empty $amt_excerpt.
     */
    if ($amt_excerpt == "...") {
        $amt_excerpt = "";
    }

    return $amt_excerpt;
}


/**
 * Returns a comma-delimited list of a post's categories.
 */
function amt_get_keywords_from_post_cats( $post ) {

    $postcats = "";
    foreach((get_the_category($post->ID)) as $cat) {
        $postcats .= $cat->cat_name . ', ';
    }
    // strip final comma
    $postcats = substr($postcats, 0, -2);

    return $postcats;
}


/**
 * Helper function. Returns the first category the post belongs to.
 */
function amt_get_first_category( $post ) {
    $cats = amt_strtolower(amt_get_keywords_from_post_cats( $post ));
    $bits = explode(',', $cats);
    if (!empty($bits)) {
        return $bits[0];
    }
    return '';
}


/**
 * Retrieves the post's user-defined tags.
 *
 * This will only work in WordPress 2.3 or newer. On older versions it will
 * return an empty string.
 */
function amt_get_post_tags( $post ) {

    if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
        $tags = get_the_tags($post->ID);
        if ( empty( $tags ) ) {
            return false;
        } else {
            $tag_list = "";
            foreach ( $tags as $tag ) {
                $tag_list .= $tag->name . ', ';
            }
            $tag_list = amt_strtolower(rtrim($tag_list, " ,"));
            return $tag_list;
        }
    } else {
        return "";
    }
}


/**
 * Returns a comma-delimited list of all the blog's categories.
 * The built-in category "Uncategorized" is excluded.
 */
function amt_get_all_categories($no_uncategorized = TRUE) {

    global $wpdb;

    if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
        $cat_field = "name";
        $sql = "SELECT name FROM $wpdb->terms LEFT OUTER JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' ORDER BY name ASC";
    } else {
        $cat_field = "cat_name";
        $sql = "SELECT cat_name FROM $wpdb->categories ORDER BY cat_name ASC";
    }
    $categories = $wpdb->get_results($sql);
    if ( empty( $categories ) ) {
        return "";
    } else {
        $all_cats = "";
        foreach ( $categories as $cat ) {
            if ($no_uncategorized && $cat->$cat_field != "Uncategorized") {
                $all_cats .= $cat->$cat_field . ', ';
            }
        }
        $all_cats = amt_strtolower(rtrim($all_cats, " ,"));
        return $all_cats;
    }
}


/**
 * This is a helper function that returns the post's or page's description.
 */
function amt_get_content_description( $post, $auto=true ) {

    $content_description = '';

    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {    // TODO: check if this check is needed at all!

        $desc_fld_content = amt_get_post_meta_description( $post->ID );

        if ( !empty($desc_fld_content) ) {
            // If there is a custom field, use it
            $content_description = amt_clean_desc($desc_fld_content);
        } else {
            // Else, use the post's excerpt. Valid for Pages too.
            if ($auto) {
                $content_description = amt_clean_desc( amt_get_the_excerpt($post) );
            }
        }
    }
    return $content_description;
}


/**
 * This is a helper function that returns the post's or page's keywords.
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

        if ( !empty($keyw_fld_content) ) {
            // If there is a custom field, use it
            if ( is_single() ) {
                // On single posts, the %cat% tag is replaced by the post's categories
                $keyw_fld_content = str_replace("%cats%", amt_get_keywords_from_post_cats($post), $keyw_fld_content);
                // Also, the %tags% tag is replaced by the post's tags (WordPress 2.3 or newer)
                if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
                    $keyw_fld_content = str_replace("%tags%", amt_get_post_tags($post), $keyw_fld_content);
                }
            }
            $content_keywords .= amt_strtolower($keyw_fld_content);
        } elseif ( is_single() ) {  // pages do not support categories and tags
            if ($auto) {
                /*
                 * Add keywords automatically.
                 * Keywords consist of the post's categories and the post's tags (tags exist in WordPress 2.3 or newer).
                 */
                $content_keywords .= amt_strtolower(amt_get_keywords_from_post_cats($post));
                $post_tags = amt_strtolower(amt_get_post_tags($post));
                if (!empty($post_tags)) {
                    $content_keywords .= ", " . $post_tags;
                }
            }
        }
    }

    /**
     * Finally, add the global keyword, if they are set in the administration panel.
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


function amt_get_content_keywords_mesh( $post ) {
    // Keywords returned in the form: keyword1;keyword2;keyword3
    $keywords = explode(', ', amt_get_content_keywords($post));
    return implode(';', $keywords);
}


/**
 * This is the main function that actually writes the meta tags to the
 * appropriate page.
 */
function amt_add_meta_tags( $post ) {

    global $paged;

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_auto_description = (($options["auto_description"] == "1") ? true : false );
    $do_auto_keywords = (($options["auto_keywords"] == "1") ? true : false );
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
        // First use the site description from the Add-Meta-Tags settings
        $site_description = $options["site_description"];
        if (empty($site_description)) {
            // Alternatively, use the blog description
            $site_description = get_bloginfo('description');
        }

        if ( !empty($site_description) ) {
            // If $site_description is not empty, then use it in the description meta-tag of the front page
            $metadata_arr[] = '<meta name="description" content="' . amt_process_paged(amt_clean_desc($site_description)) . '" />';
        }

        // Keywords
        $site_keywords = $options["site_keywords"];
        if (empty($site_keywords)) {
            // Alternatively, use the blog categories
            $site_keywords = amt_get_all_categories();
        }

        if ( !empty($site_keywords) ) {
            // If $site_keywords is not empty, then use it in the keywords meta-tag of the front page
            $metadata_arr[] = '<meta name="keywords" content="' . $site_keywords . '" />';
        }

    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        // Description
        $description = amt_get_content_description($post, $auto=$do_auto_description);
        if (!empty($description)) {
            $metadata_arr[] = '<meta name="description" content="' . $description . '" />';
        }

        // Keywords
        $keywords = amt_get_content_keywords($post, $auto=$do_auto_keywords);
        if (!empty($keywords)) {
            $metadata_arr[] = '<meta name="keywords" content="' . amt_strtolower($keywords) . '" />';
        }

        // 'news_keywords'
        $newskeywords = amt_get_post_meta_newskeywords( $post->ID );
        if (!empty($newskeywords)) {
            $metadata_arr[] = '<meta name="news_keywords" content="' . $newskeywords . '" />';
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
        if ($do_auto_description) {
            $description_content = amt_clean_desc(category_description());
            if (!empty($description_content)) {
                $metadata_arr[] = '<meta name="description" content="' . amt_process_paged($description_content) . '" />';
            }
        }
        
        /*
         * Write a keyword metatag if there is a category name (always)
         */
        if ($do_auto_keywords) {
            $cur_cat_name = single_cat_title($prefix = '', $display = false );
            if ( !empty($cur_cat_name) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . amt_strtolower($cur_cat_name) . '" />';
            }
        }

    } elseif ( is_tag() ) {
        /*
         * Writes a description META tag only if a description for the current tag has been set.
         */
        if ($do_auto_description) {
            $description_content = amt_clean_desc(tag_description());
            if (!empty($description_content)) {
                $metadata_arr[] = '<meta name="description" content="' . amt_process_paged($description_content) . '" />';
            }
        }
        
        /*
         * Write a keyword metatag if there is a tag name (always)
         */
        if ($do_auto_keywords) {
            $cur_tag_name = single_tag_title($prefix = '', $display = false );
            if ( !empty($cur_tag_name) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . amt_strtolower($cur_tag_name) . '" />';
            }
        }
    }

    // Add site wide meta tags
    if (!empty($options["site_wide_meta"])) {
        $metadata_arr[] = html_entity_decode( stripslashes( $options["site_wide_meta"] ) );
    }

    // On every page print the copyright head link
    if (!empty($options["copyright_url"])) {
        $metadata_arr[] = '<link rel="copyright" type="text/html" title="' . get_bloginfo('name') . ' Copyright Information" href="' . trim($options["copyright_url"]) . '" />';
    }

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
        $contactmethods['amt_facebook_author_profile_url'] = 'Facebook Author Profile URL';
    }
    // Add Facebook Publisher Profile URL
    if ( !isset( $contactmethods['amt_facebook_publisher_profile_url'] ) ) {
        $contactmethods['amt_facebook_publisher_profile_url'] = 'Facebook Publisher Profile URL';
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

    if ( amt_is_default_front_page() ) {

        $metadata_arr[] = '<meta property="og:title" content="' . amt_process_paged(get_bloginfo('name')) . '" />';
        $metadata_arr[] = '<meta property="og:type" content="website" />';
        // Site Image
        // Use the default image, if one has been set.
        if (!empty($options["default_image_url"])) {
            $metadata_arr[] = '<meta property="og:image" content="' . trim($options["default_image_url"]) . '" />';
        }
        $metadata_arr[] = '<meta property="og:url" content="' . get_bloginfo('url') . '" />';
        // Site description
        if (!empty($options["site_description"])) {
            $metadata_arr[] = '<meta property="og:description" content="' . amt_process_paged($options["site_description"]) . '" />';
        } elseif (get_bloginfo('description')) {
            $metadata_arr[] = '<meta property="og:description" content="' . amt_process_paged(get_bloginfo('description')) . '" />';
        }
        $metadata_arr[] = '<meta property="og:locale" content="' . str_replace('-', '_', get_bloginfo('language')) . '" />';
        $metadata_arr[] = '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />';


    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        // Title
        $metadata_arr[] = '<meta property="og:title" content="' . get_the_title($post->ID) . '" />';

        // URL
        $metadata_arr[] = '<meta property="og:url" content="' . get_permalink($post->ID) . '" />';

        // Image
        if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) {
            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium' );
            $metadata_arr[] = '<meta property="og:image" content="' . $thumbnail_info[0] . '" />';
            //$metadata_arr[] = '<meta property="og:image:secure_url" content="' . str_replace('http:', 'https:', $thumbnail_info[0]) . '" />';
            $metadata_arr[] = '<meta property="og:image:width" content="' . $thumbnail_info[1] . '" />';
            $metadata_arr[] = '<meta property="og:image:height" content="' . $thumbnail_info[2] . '" />';
        } elseif ( is_attachment() && wp_attachment_is_image($post->ID) ) { // is attachment page and contains an image.
            $attachment_image_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large' );
            $metadata_arr[] = '<meta property="og:image" content="' . $attachment_image_info[0] . '" />';
            //$metadata_arr[] = '<meta property="og:image:secure_url" content="' . str_replace('http:', 'https:', $attachment_image_info[0]) . '" />';
            $metadata_arr[] = '<meta property="og:image:type" content="' . get_post_mime_type($post->ID) . '" />';
            $metadata_arr[] = '<meta property="og:image:width" content="' . $attachment_image_info[1] . '" />';
            $metadata_arr[] = '<meta property="og:image:height" content="' . $attachment_image_info[2] . '" />';
        } elseif (!empty($options["default_image_url"])) {
            // Alternatively, use default image
            $metadata_arr[] = '<meta property="og:image" content="' . trim($options["default_image_url"]) . '" />';
        }

        // We use the description defined by Add-Meta-Tags
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta property="og:description" content="' . $content_desc . '" />';
        }

        $metadata_arr[] = '<meta property="og:locale" content="' . str_replace('-', '_', get_bloginfo('language')) . '" />';
        $metadata_arr[] = '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />';

        // Video
        $video_url = amt_get_video_url();
        if (!empty($video_url)) {
            $metadata_arr[] = '<meta property="og:video" content="' . $video_url . '" />';
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
            $metadata_arr[] = '<meta property="article:published_time" content="' . amt_iso8601_date($post->post_date) . '" />';
            $metadata_arr[] = '<meta property="article:modified_time" content="' . amt_iso8601_date($post->post_modified) . '" />';

            // Author and Publisher
            $fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $post->post_author);
            if ( !empty($fb_author_url) ) {
                $metadata_arr[] = '<meta property="article:author" content="' . esc_url( $fb_author_url, array('http', 'https', 'mailto') ) . '" />';
            }
            $fb_publisher_url = get_the_author_meta('amt_facebook_publisher_profile_url', $post->post_author);
            if ( !empty($fb_publisher_url) ) {
                $metadata_arr[] = '<meta property="article:publisher" content="' . esc_url( $fb_publisher_url, array('http', 'https', 'mailto') ) . '" />';
            }

            // article:section: We use the first category as the section
            $first_cat = amt_get_first_category($post);
            if (!empty($first_cat)) {
                $metadata_arr[] = '<meta property="article:section" content="' . $first_cat . '" />';
            }
            
            // article:tag: Keywords are listed as post tags
            $keywords = explode(', ', amt_get_content_keywords($post));
            foreach ($keywords as $tag) {
                if (!empty($tag)) {
                    $metadata_arr[] = '<meta property="article:tag" content="' . $tag . '" />';
                }
            }
        }
    }

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
    $metadata_arr[] = '<meta name="dc.title" content="' . get_the_title($post->ID) . '" />';

    // Resource identifier
    $metadata_arr[] = '<meta name="dcterms.identifier" scheme="dcterms.uri" content="' . get_permalink($post->ID) . '" />';

    $metadata_arr[] = '<meta name="dc.creator" content="' . amt_get_dublin_core_author_notation($post) . '" />';
    $metadata_arr[] = '<meta name="dc.date" scheme="dc.w3cdtf" content="' . amt_iso8601_date($post->post_date) . '" />';
    // We use the same description as the ``description`` meta tag.
    $content_desc = amt_get_content_description($post);
    if ( !empty($content_desc) ) {
        $metadata_arr[] = '<meta name="dc.description" content="' . $content_desc . '" />';
    }
    // Keywords are in the form: keyword1;keyword2;keyword3
    $metadata_arr[] = '<meta name="dc.subject" content="' . amt_get_content_keywords_mesh($post) . '" />';
    $metadata_arr[] = '<meta name="dc.language" scheme="dcterms.rfc4646" content="' . get_bloginfo('language') . '" />';
    $metadata_arr[] = '<meta name="dc.publisher" scheme="dcterms.uri" content="' . get_bloginfo('url') . '" />';
    // Copyright page
    if (!empty($options["copyright_url"])) {
        $metadata_arr[] = '<meta name="dcterms.rights" scheme="dcterms.uri" content="' . get_bloginfo('url') . '" />';
    }
    // The following requires creative commons configurator
    if (function_exists('bccl_get_license_url')) {
        $metadata_arr[] = '<meta name="dcterms.license" scheme="dcterms.uri" content="' . bccl_get_license_url() . '" />';
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

    return $metadata_arr;
}


/*
Final
*/

/**
 * Uses the custom title, if one has been set.
 */
function amt_custom_title_tag($title) {
    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
        
        // Get current post object
        $post = amt_get_current_post_object();

        $custom_title = amt_get_post_meta_title( $post->ID );
        if ( !empty($custom_title) ) {
            $custom_title = str_replace('%title%', $title, $custom_title);
            return $custom_title;
        }
    }

    return $title;
}
add_filter('wp_title', 'amt_custom_title_tag', 1000);


function amt_get_metadata() {

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    $do_add_metadata = true;

    $metadata_arr = array();
    $metadata_arr[] = "";
    $metadata_arr[] = "<!-- BEGIN Metadata added by Add-Meta-Tags WordPress plugin -->";

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

    // Add Metadata
    if ($do_add_metadata) {

        // Get current post object
        $post = amt_get_current_post_object();

        // Basic Meta tags
        $metadata_arr = array_merge($metadata_arr, amt_add_meta_tags($post));
        //var_dump(amt_add_meta_tags());
        // Add Opengraph
        $metadata_arr = array_merge($metadata_arr, amt_add_opengraph_metadata($post));
        // Add Dublin Core
        $metadata_arr = array_merge($metadata_arr, amt_add_dublin_core_metadata($post));
    }
    $metadata_arr[] = "<!-- END Metadata added by Add-Meta-Tags WordPress plugin -->";
    $metadata_arr[] = "";
    $metadata_arr[] = "";

    return $metadata_arr;
}


function amt_add_metadata() {
    echo implode("\n", amt_get_metadata());
}

add_action('wp_head', 'amt_add_metadata', 0);



// Review mode

function amt_get_metadata_review() {
    // Returns metadata review code
    return '<pre>' . htmlentities( implode("\n", amt_get_metadata()) ) . '</pre>';
    //return '<pre lang="XML" line="1">' . implode("\n", amt_get_metadata()) . '</pre>';
}

function amt_add_metadata_review($post_body) {

    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

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