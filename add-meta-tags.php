<?php
/*
Plugin Name: Add Meta Tags
Plugin URI: http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
Description: Adds the <em>Description</em> and <em>Keywords</em> XHTML META tags to your blog's <em>front page</em>, posts, pages, category-based archives and tag-based archives. Also adds <em>Opengraph</em> and <em>Dublin Core</em> metadata on posts and pages.
Version: 2.3.5
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
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'amt-settings.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'amt-admin-panel.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'amt-utils.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'amt-template-tags.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'metadata', 'amt_basic.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'metadata', 'amt_twitter_cards.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'metadata', 'amt_opengraph.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'metadata', 'amt_dublin_core.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( AMT_DIR, 'metadata', 'amt_schemaorg.php' ) ) );

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

        // Only administrators can see the review box.
        if ( current_user_can( 'create_users' ) ) {
            $post_body = amt_get_metadata_review() . '<br /><br />' . $post_body;
        }

    }

    return $post_body;
}

add_filter('the_content', 'amt_add_metadata_review', 9999);

?>