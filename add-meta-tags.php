<?php
/*
Plugin Name: Add Meta Tags
Plugin URI: http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
Description: Add basic meta tags and also Opengraph, Schema.org Microdata, Twitter Cards and Dublin Core metadata to optimize your web site for better SEO.
Version: 2.11.3
Author: George Notaras
Author URI: http://www.g-loaded.eu/
License: Apache License v2
Text Domain: add-meta-tags
Domain Path: /languages/
*/

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

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


// Store plugin main file path
define( 'AMT_PLUGIN_FILE', __FILE__ );
// Store plugin directory
// NOTE: TODO: Consider using __DIR__ (requires PHP >=5.3) instead of dirname.
// See: http://stackoverflow.com/questions/2220443/whats-better-of-requiredirname-file-myparent-php-than-just-require#comment18170996_12129877
//define( 'AMT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMT_PLUGIN_DIR', dirname(AMT_PLUGIN_FILE) . '/' );

// Import modules
require( AMT_PLUGIN_DIR . 'amt-settings.php' );
require( AMT_PLUGIN_DIR . 'amt-admin-panel.php' );
require( AMT_PLUGIN_DIR . 'amt-utils.php' );
require( AMT_PLUGIN_DIR . 'amt-template-tags.php' );
require( AMT_PLUGIN_DIR . 'amt-embed.php' );
require( AMT_PLUGIN_DIR . 'metadata/amt_basic.php' );
require( AMT_PLUGIN_DIR . 'metadata/amt_twitter_cards.php' );
require( AMT_PLUGIN_DIR . 'metadata/amt_opengraph.php' );
require( AMT_PLUGIN_DIR . 'metadata/amt_dublin_core.php' );
require( AMT_PLUGIN_DIR . 'metadata/amt_schemaorg.php' );
require( AMT_PLUGIN_DIR . 'metadata/amt_extended.php' );
// Command Line Interface via WPCLI
require( AMT_PLUGIN_DIR . 'amt-cli.php' );


/**
 * Translation Domain
 *
 * Translation files are searched in: wp-content/plugins
 */
//load_plugin_textdomain('add-meta-tags', false, dirname( plugin_basename( AMT_PLUGIN_FILE ) ) . '/languages/');
//load_plugin_textdomain('add-meta-tags', false, AMT_PLUGIN_DIR . 'languages/');
// For language packs check:
load_plugin_textdomain('add-meta-tags');


/**
 * Settings Link in the ``Installed Plugins`` page
 */
function amt_plugin_actions( $links, $file ) {
    if( $file == plugin_basename( AMT_PLUGIN_FILE ) && function_exists( "admin_url" ) ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=add-meta-tags-options' ) . '">' . __('Settings') . '</a>';
        // Add the settings link before other links
        array_unshift( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'amt_plugin_actions', 10, 2 );


//
// Adds prefixes to the html element of the page
// ex xmlns
//
function amt_add_html_prefixes_and_namespaces( $content ) {
    $options = amt_get_options();
    $prefixes = array();
    if ( $options['og_add_xml_namespaces'] == '1' ) {
        $prefixes['og'] = 'http://ogp.me/ns#';
        $prefixes['fb'] = 'https://www.facebook.com/2008/fbml';
    }
    // Dublin Core
    // See: http://wiki.dublincore.org/index.php/Dublin_Core_Prefixes
    if ( $options['dc_add_xml_namespaces'] == '1' ) {
        $prefixes['dcterms'] = 'http://purl.org/dc/terms/';
    }
    // Generate the value of the prefix attribute
    $prefix_value = '';
    foreach ( $prefixes as $key => $val ) {
        $prefix_value .= sprintf(' %s: %s', $key, $val);
    }
    // return the final attributes
    $output = '';
    // Although not necessary in HTML 5, we also add the xmlns="http://www.w3.org/1999/xhtml"
    // Comment out if duplicate
    $output .= ' xmlns="http://www.w3.org/1999/xhtml"';
    // Add our prefixes
    $output .= ' prefix="' . trim($prefix_value) . '"';
    return $output . ' ' . $content;
}
add_filter('language_attributes', 'amt_add_html_prefixes_and_namespaces');


/**
 * Replaces the text to be used in the title element, if a replacement text has been set.
 */
// function amt_custom_title_tag($title, $separator) {
function amt_custom_title_tag($title) {

    // var_dump($title);

    if ( is_feed() || is_search() || is_404() ) {
        return $title;
    }

    // Get the options
    $options = get_option('add_meta_tags_opts');
    // Get current post object
    $post = amt_get_queried_object();

    $processed_title = amt_get_title_for_title_element($options, $post);
    if ( ! empty($processed_title) ) {
        if ( is_array($title) ) {
            // WP >= 4.4
            $processed_title = str_replace('%title%', $title['title'], $processed_title);
            return array('title' => esc_attr($processed_title));
        } else {
            // WP < 4.4
            $processed_title = str_replace('%title%', $title, $processed_title);
            return esc_attr($processed_title);
        }
    }

    // WordPress adds multipage information if a custom title is not set.
    return $title;
}
// Both filter hooks are used so as to support themes with the 'title-tag' feature
// and also themes that generate the title using the 'wp_title()' template function.
add_filter('document_title_parts', 'amt_custom_title_tag', 9999, 1);
if ( apply_filters('amt_enable_legacy_title_support', true) ) {
    add_filter('wp_title', 'amt_custom_title_tag', 9999, 1);
}
// if ( version_compare( get_bloginfo('version'), '4.4', '>=' ) ) {
    // Since WP 4.4
    // - https://make.wordpress.org/core/2015/10/20/document-title-in-4-4/
//    add_filter('document_title_parts', 'amt_custom_title_tag', 9999, 1);
// } else {
    // add_filter('wp_title', 'amt_custom_title_tag', 1000, 2);
    // Reverting back to the one argument version of the fitlering function.
//    add_filter('wp_title', 'amt_custom_title_tag', 9999, 1);
// }

/**
 * Sets the correct lang attribute of the html element of the page,
 * according to the content's locale.
 */
function amt_set_html_lang_attribute( $lang ) {
    //var_dump($lang);
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return $lang;
    } elseif ( ! array_key_exists( 'manage_html_lang_attribute', $options) ) {
        return $lang;
    } elseif ( $options['manage_html_lang_attribute'] == '0' ) {
        return $lang;
    }
    // Set the html lang attribute according to the locale
    $locale = '';
    if ( is_singular() ) {
        $post = amt_get_queried_object();
        // Store locale
        $locale = str_replace( '_', '-', amt_get_language_content($options, $post) );
    } else {
        $locale = str_replace( '_', '-', amt_get_language_site($options) );
    }
    // Allow filtering
    $locale = apply_filters( 'amt_wordpress_lang', $locale );
    if ( ! empty($locale) ) {
        // Replace WordPress locale with ours. (even if it's the same)
        $lang = str_replace( get_bloginfo('language'), $locale, $lang );
    }
    return $lang;
}
add_filter( 'language_attributes', 'amt_set_html_lang_attribute' );


/**
 * Returns an array of all the generated metadata for the head area.
 */
function amt_get_metadata_head($post, $options) {

    $do_add_metadata = true;

    $metadata_arr = array();

    // No metadata for password protected posts.
    if ( post_password_required() ) {
        return $metadata_arr;
    }

    // Robots Meta Tag content.
    $robots_content = '';

    // Check for NOINDEX,FOLLOW on archives.
    // There is no need to further process metadata as we explicitly ask search
    // engines not to index the content.
    if ( is_archive() || is_search() ) {
        if (
            ( is_search() && ($options["noindex_search_results"] == "1") )  ||          // Search results
            ( is_date() && ($options["noindex_date_archives"] == "1") )  ||             // Date and time archives
            ( is_category() && is_paged() && ($options["noindex_category_archives"] == "1") )  ||     // Category archives (except 1st page)
            ( is_tag() && is_paged() && ($options["noindex_tag_archives"] == "1") )  ||               // Tag archives (except 1st page)
            ( is_tax() && is_paged() && ($options["noindex_taxonomy_archives"] == "1") )  ||          // Custom taxonomy archives (except 1st page)
            ( is_author() && is_paged() && ($options["noindex_author_archives"] == "1") )             // Author archives (except 1st page)
        ) {
            $do_add_metadata = false;   // No need to process metadata
            // $robots_content is old. Should remove.
            ////$robots_content = 'NOINDEX,FOLLOW';
            // Allow filtering of the robots meta tag content.
            // Dev Note: Filtering of the robots meta tag takes place here, so as to avoid double filtering in case $do_add_metadata is true.
            ////$robots_content = apply_filters( 'amt_robots_data', $robots_content );

            $robots_options = array( 'noindex', 'follow' );
            // Allow filtering of the robots meta tag content.
            // Dev Note: Filtering of the robots meta tag takes place here, so as to avoid double filtering in case $do_add_metadata is true.
            $robots_options = apply_filters( 'amt_robots_options_noindex', $robots_options );
        }
    }
    // Add a robots meta tag if its content is not empty.
    if ( ! empty( $robots_options ) ) {
        $metadata_arr[] = '<meta name="robots" content="' . implode(',', $robots_options) . '" />';
    }


    // Check post object
    if ( is_null( $post ) ) {
        // Allow metadata on the default front page (latest posts).
        // A post object is not available on that page, but we still need to
        // generate metadata for it. A $post object exists for the "front page"
        // and the "posts page" when static pages are used. No allow rule needed.
        if ( ! amt_is_default_front_page() ) {
            $do_add_metadata = false;
        }
    } elseif ( is_singular() ) {
        // The post type check should only take place on content pages.
        // Check if metadata should be added to this content type.
        $post_type = get_post_type( $post );
        if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
            $do_add_metadata = false;
        }
    }

    // Add Metadata
    if ($do_add_metadata) {

        // Attachments and embedded media are collected only on content pages.
        if ( is_singular() ) {
            // Get an array containing the attachments
            $attachments = amt_get_ordered_attachments( $post );
            //var_dump($attachments);

            // Get an array containing the URLs of the embedded media
            $embedded_media = amt_get_embedded_media( $post );
            //var_dump($embedded_media);
        } else {
            $attachments = array();
            $embedded_media = array();
        }

        // Basic Meta tags
        $metadata_arr = array_merge( $metadata_arr, amt_add_basic_metadata_head( $post, $attachments, $embedded_media, $options ) );
        //var_dump(amt_add_basic_metadata());
        // Add Opengraph
        $metadata_arr = array_merge( $metadata_arr, amt_add_opengraph_metadata_head( $post, $attachments, $embedded_media, $options ) );
        // Add Twitter Cards
        $metadata_arr = array_merge( $metadata_arr, amt_add_twitter_cards_metadata_head( $post, $attachments, $embedded_media, $options ) );
        // Add Dublin Core
        $metadata_arr = array_merge( $metadata_arr, amt_add_dublin_core_metadata_head( $post, $attachments, $embedded_media, $options ) );
        // Add Google+ Author/Publisher links
        $metadata_arr = array_merge( $metadata_arr, amt_add_schemaorg_metadata_head( $post, $attachments, $embedded_media, $options ) );
        // Add JSON+LD Schema.org
        $metadata_arr = array_merge( $metadata_arr, amt_add_jsonld_schemaorg_metadata_head( $post, $attachments, $embedded_media, $options ) );
    }

    // Allow filtering of the all the generated metatags
    $metadata_arr = apply_filters( 'amt_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Prints the generated metadata for the head area.
 */
function amt_add_metadata_head() {
    // For AMT timings
    $t = microtime(true);
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    // Get current post object
    $post = amt_get_queried_object();
    // Caching indicator
    $is_cached = 'no';
    // Get the metadata
    if ( absint($options['transient_cache_expiration']) > 0 && apply_filters('amt_enable_metadata_cache', true) ) {
        $metadata_arr = amt_get_transient_cache($post, $options, $where='head');
        if ( empty($metadata_arr) ) {
            $metadata_arr = amt_get_metadata_head($post, $options);
            // Cache the metadata
            if ( ! empty($metadata_arr) ) {
                amt_set_transient_cache($post, $options, $metadata_arr, $where='head');
            }
        } else {
            $is_cached = 'yes';
        }
    } else {
        $metadata_arr = amt_get_metadata_head($post, $options);
    }
    // For AMT timings
    if ( ! empty($metadata_arr) && $options['enable_timings'] == '1' ) {
        array_unshift( $metadata_arr, sprintf( '<!-- Add-Meta-Tags Timings (milliseconds) - Block total time: %.3f msec - Cached: %s -->', (microtime(true) - $t) * 1000, $is_cached ) );
    }
    // Add our comment
    if ( $options["omit_vendor_html_comments"] == "0" ) {
        if ( count( $metadata_arr ) > 0 ) {
            array_unshift( $metadata_arr, "<!-- BEGIN Metadata added by the Add-Meta-Tags WordPress plugin -->" );
            array_push( $metadata_arr, "<!-- END Metadata added by the Add-Meta-Tags WordPress plugin -->" );
        }
    }
    // Return complete metadata array
    return $metadata_arr;
}

function amt_print_head_block() {
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    if ( amt_check_run_metadata_review_code($options) ) {
        // Here we use non persistent caching in order to be able to use the same output in the review mode.
        // Non persistent object cache
        $amtcache_key = amt_get_amtcache_key('amt_metadata_block_head');
        $metadata_block_head = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
        if ( $metadata_block_head === false ) {
            $metadata_block_head = amt_add_metadata_head();
            // Cache even empty
            wp_cache_add( $amtcache_key, $metadata_block_head, $group='add-meta-tags' );
        }
    } else {
        $metadata_block_head = amt_add_metadata_head();
    }
    // Print the metadata block
    echo PHP_EOL . implode( PHP_EOL, $metadata_block_head ) . PHP_EOL . PHP_EOL;

}

add_action('wp_head', 'amt_print_head_block', 0);
// AMP page
//if ( function_exists('is_amp_endpoint') && is_amp_endpoint() ) {
    add_action('amp_post_template_head', 'amt_print_head_block', 0);
//}


/**
 * Returns an array of all the generated metadata for the footer area.
 */
function amt_get_metadata_footer($post, $options) {

    $do_add_metadata = true;

    $metadata_arr = array();

    // Check post object
    if ( is_null( $post ) ) {
        // Allow metadata on the default front page (latest posts).
        // A post object is not available on that page, but we still need to
        // generate metadata for it. A $post object exists for the "front page"
        // and the "posts page" when static pages are used. No allow rule needed.
        if ( ! amt_is_default_front_page() ) {
            $do_add_metadata = false;
        }
    } elseif ( is_singular() ) {
        // The post type check should only take place on content pages.
        // Check if metadata should be added to this content type.
        $post_type = get_post_type( $post );
        if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
            $do_add_metadata = false;
        }
    }

    // Add Metadata
    if ($do_add_metadata) {

        // Attachments and embedded media are collected only on content pages.
        if ( is_singular() ) {
            // Get an array containing the attachments
            $attachments = amt_get_ordered_attachments( $post );
            //var_dump($attachments);

            // Get an array containing the URLs of the embedded media
            $embedded_media = amt_get_embedded_media( $post );
            //var_dump($embedded_media);
        } else {
            $attachments = array();
            $embedded_media = array();
        }

        // Add Schema.org Microdata
        $metadata_arr = array_merge( $metadata_arr, amt_add_schemaorg_metadata_footer( $post, $attachments, $embedded_media, $options ) );
    }

    // Allow filtering of all the generated metatags
    $metadata_arr = apply_filters( 'amt_metadata_footer', $metadata_arr );

    return $metadata_arr;
}


/**
 * Prints the generated metadata for the footer area.
 */
function amt_add_metadata_footer() {
    // For AMT timings
    $t = microtime(true);
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    // Get current post object
    $post = amt_get_queried_object();
    // Caching indicator
    $is_cached = 'no';
    // Get the metadata
    // NOTE: Currently metadata is cached for content pages only (is_singular())
    if ( absint($options['transient_cache_expiration']) > 0 && apply_filters('amt_enable_metadata_cache', true) ) {
        $metadata_arr = amt_get_transient_cache($post, $options, $where='footer');
        if ( empty($metadata_arr) ) {
            $metadata_arr = amt_get_metadata_footer($post, $options);
            // Cache the metadata
            if ( ! empty($metadata_arr) ) {
                amt_set_transient_cache($post, $options, $metadata_arr, $where='footer');
            }
        } else {
            $is_cached = 'yes';
        }
    } else {
        $metadata_arr = amt_get_metadata_footer($post, $options);
    }
    // For AMT timings
    if ( ! empty($metadata_arr) && $options['enable_timings'] == '1' ) {
        array_unshift( $metadata_arr, sprintf( '<!-- Add-Meta-Tags Timings (milliseconds) - Block total time: %.3f msec - Cached: %s -->', (microtime(true) - $t) * 1000, $is_cached ) );
    }
    // Add our comment
    if ( $options["omit_vendor_html_comments"] == "0" ) {
        if ( count( $metadata_arr ) > 0 ) {
            array_unshift( $metadata_arr, "<!-- BEGIN Metadata added by the Add-Meta-Tags WordPress plugin -->" );
            array_push( $metadata_arr, "<!-- END Metadata added by the Add-Meta-Tags WordPress plugin -->" );
        }
    }
    // Return complete metadata array
    return $metadata_arr;
}

function amt_print_footer_block() {
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    if ( amt_check_run_metadata_review_code($options) ) {
        // Here we use non persistent caching in order to be able to use the same output in the review mode.
        // Non persistent object cache
        $amtcache_key = amt_get_amtcache_key('amt_metadata_block_footer');
        $metadata_block_footer = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
        if ( $metadata_block_footer === false ) {
            $metadata_block_footer = amt_add_metadata_footer();
            // Cache even empty
            wp_cache_add( $amtcache_key, $metadata_block_footer, $group='add-meta-tags' );
        }
    } else {
        $metadata_block_footer = amt_add_metadata_footer();
    }
    // Print the metadata block
    echo PHP_EOL . implode( PHP_EOL, $metadata_block_footer ) . PHP_EOL . PHP_EOL;
}

add_action('wp_footer', 'amt_print_footer_block', 0);




//
// Metadata Review Mode
//


// Prints the AMT Metadata Review Mode styles
function amt_metadata_review_mode_enqueue_styles_scripts() {
    $options = get_option("add_meta_tags_opts");
    if ( amt_check_run_metadata_review_code($options) ) {

        // Register metadata review mode stylesheet.
        wp_register_style( 'amt_metadata_review_mode', plugins_url( 'css/amt-metadata-review-mode.css', AMT_PLUGIN_FILE ) );
        //wp_register_style( 'custom_toolbar_css', plugin_dir_url( __FILE__ ) . 'custom-toolbar.css', '', '', 'screen' );
        // Enqueue the style
        wp_enqueue_style( 'amt_metadata_review_mode' );

        $script_path = 'js/amt-metadata-review-mode.js';
        if ( apply_filters('amt_metadata_review_mode_enable_enhanced_script', false) ) {
            $script_path = 'js/amt-metadata-review-mode-enhanced.js';
        }

        // Register metadata review mode script
        wp_register_script( 'amt_metadata_review_mode', plugins_url( $script_path, AMT_PLUGIN_FILE ), array('jquery') );
        // Enqueue the style
        wp_enqueue_script( 'amt_metadata_review_mode' );

    }
}
// Add styles and scripts for Metadata Review.
//add_action( 'admin_enqueue_scripts', 'amt_metadata_review_mode_enqueue_styles_scripts' );
add_action( 'wp_enqueue_scripts', 'amt_metadata_review_mode_enqueue_styles_scripts' );


// Build the review data
function amt_get_metadata_review($options, $add_as_view=false) {

    // Collect metadata

    //
    // Metadata from head section
    //

    $metadata_block_head = null;
    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_metadata_block_head');
    $metadata_block_head = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $metadata_block_head === false ) {
        $metadata_block_head = amt_add_metadata_head();
        // Cache even empty
        wp_cache_add( $amtcache_key, $metadata_block_head, $group='add-meta-tags' );
    }

    //
    // Metadata from footer
    //

    $metadata_block_footer = null;
    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_metadata_block_footer');
    $metadata_block_footer = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $metadata_block_footer === false ) {
        $metadata_block_footer = amt_add_metadata_footer();
        // Cache even empty
        wp_cache_add( $amtcache_key, $metadata_block_footer, $group='add-meta-tags' );
    }

    //
    // Metadata from content filter (Schema.org Microdata)
    //

    $metadata_block_content_filter = null;
    if ( $options["schemaorg_force_jsonld"] == "0" ) {
        // What happens here:
        // The Metadata Review mode content filter should have a bigger priority that the Schema.org
        // Microdata filter. There the metadata has been stored in non persistent cache.
        // Here we retrieve it. See the notes there for more info.
        $metadata_block_content_filter = wp_cache_get( 'amt_cache_metadata_block_content_filter', $group='add-meta-tags' );
    }

    // Build texts

    if ( $add_as_view ) {
        // $BR = '<br />';
        $BR = PHP_EOL;
        $enclosure_start = '<div id="amt-metadata-review">' . '<pre id="amt-metadata-review-pre">' . $BR;
        $enclosure_end = '</pre>' . '</div>' . $BR . $BR;
    } else {
        $BR = PHP_EOL;
        $enclosure_start = '<pre id="amt-metadata-review-pre">' . $BR;
        $enclosure_end = '</pre>' . $BR . $BR;
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {
        $text_title = '<span class="amt-ht-title">Add-Meta-Tags &mdash; Metadata Review Mode</span>' . $BR . $BR;
    } else {
        $text_title = '';
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {
        $text_intro = '<span class="amt-ht-notice"><span class="amt-ht-important">NOTE</span>: This menu has been added because <span class="amt-ht-important">Metadata Review Mode</span> has been enabled in';
        $text_intro .= $BR . 'the Add-Meta-Tags settings. Only logged in administrators can see this menu.</span>';
    } else {
        $text_intro = '';
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {
        //$text_head_intro = $BR . $BR . '<span class="amt-ht-notice">The following metadata has been added to the head section.</span>' . $BR . $BR;
        $text_head_intro = $BR . $BR . 'Metadata at the head section' . $BR;
        $text_head_intro .=            '============================' . $BR . $BR;
    } else {
        $text_head_intro = '';
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {
        //$text_footer_intro = $BR . $BR . '<span class="amt-ht-notice">The following metadata has been embedded in the body of the page.</span>' . $BR . $BR;
        $text_footer_intro = $BR . $BR . 'Metadata within the body area' . $BR;
        $text_footer_intro .=            '=============================' . $BR . $BR;
    } else {
        $text_footer_intro = $BR . $BR;
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {
        //$text_content_filter_intro = $BR . $BR . '<span class="amt-ht-notice">The following metadata has been embedded in the body of the page.</span>' . $BR;
        $text_content_filter_intro = $BR . $BR . 'Metadata within the body area' . $BR;
        $text_content_filter_intro .=            '=============================' . $BR;
    } else {
        $text_content_filter_intro = $BR;
    }

    //
    // Build view
    //

    $data = $enclosure_start . $text_title;
    
    $data .= apply_filters('amt_metadata_review_text_before', $text_intro, $metadata_block_head, $metadata_block_footer, $metadata_block_content_filter);

    //
    // Metadata Overview
    //
    if ( $options["review_mode_metadata_report"] == '1' ) {
        $metadata_overview_default_text = '';
        $data .= amt_metadata_analysis($metadata_overview_default_text, $metadata_block_head, $metadata_block_footer, $metadata_block_content_filter);
    }

    //
    // Metadata from head section
    //

    // Add for review
    if ( ! empty($metadata_block_head) ) {
        // Pretty print JSON+LD
        if ( array_key_exists('json+ld_data', $metadata_block_head) ) {
            $jsonld_data_arr = json_decode( $metadata_block_head['json+ld_data'] );
            $metadata_block_head['json+ld_data'] = json_encode($jsonld_data_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        $data .= apply_filters('amt_metadata_review_head', $text_head_intro, $metadata_block_head);
        $data .= amt_metatag_highlighter( implode( $BR, $metadata_block_head ) );
    }

    //
    // Metadata from footer
    //

    // Add for review
    if ( ! empty($metadata_block_footer) ) {
        $data .= apply_filters('amt_metadata_review_footer', $text_footer_intro, $metadata_block_footer);
        $data .=  amt_metatag_highlighter( implode( $BR, $metadata_block_footer ) );
    }

    //
    // Metadata from content filter (Schema.org Microdata)
    //

    if ( $options["schemaorg_force_jsonld"] == "0" ) {
        if ( $metadata_block_content_filter !== false ) {
            // Add for review
            $data .= apply_filters('amt_metadata_review_content_filter', $text_content_filter_intro, $metadata_block_content_filter);
            $data .= amt_metatag_highlighter( implode( $BR, $metadata_block_content_filter ) );
        }
    }

    $data .= apply_filters('amt_metadata_review_text_after', '', $metadata_block_head, $metadata_block_footer, $metadata_block_content_filter);

    // End
    $data .= $BR . $BR . $enclosure_end;

    return $data;

}


// Old view within the content
function amt_add_metadata_review($post_body) {

    // Only works in content pages
    if ( ! is_singular() ) {
        return $post_body;
    }

    $options = get_option("add_meta_tags_opts");

    // Only administrators can see the review box if is_singular() is true.
    if ( amt_check_run_metadata_review_code($options) ) {

        if ( ! apply_filters('amt_metadata_review_mode_enable_alternative', false) ) {
            return $post_body;
        }

        // Get current post object
        $post = amt_get_queried_object();
        if ( is_null( $post ) ) {
            return $post_body;
        }

        // Check if metadata is supported on this content type.
        $post_type = get_post_type( $post );
        if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
            return $post_body;
        }

        $post_body = amt_get_metadata_review($options) . '<br /><br />' . $post_body;

    }

    return $post_body;
}
// Has to be higher (so as to be executed later) than the schema.org microdata content filter.
add_filter('the_content', 'amt_add_metadata_review', 10000);


// New view -- Adds the 'Metadata' menu to the admin bar
function amt_metadata_review_mode_admin_bar_links( $admin_bar ){

    // Do not display the menu when the user is in the WP administration panel.
    if ( is_admin() ) {
        return;
    }

    // Add 'Metadata' menu to the admin bar
    $admin_bar->add_menu( array(
        'id'    => 'amt',
        'title' => '<span class="ab-icon"></span><span class="ab-label">' . __('Metadata', 'add-meta-tags') . '</span>',
        'href'  => '#',
        'meta'  => array(
            'onclick' => 'jQuery("#amt-metadata-review").toggle(); jQuery("#amt-metadata-review").focus(); return false;',
            'class'   => 'amt-metadata',
            'title'   => __('Metadata Review Mode', 'add-meta-tags'),
        )
    ));
// 'onclick' => 'jQuery("#amt-metadata-review").toggleClass("amt-metadata-review-visible"); return false;'
}


// Prints the review mode screen
function amt_metadata_review_mode_print() {
    $options = get_option("add_meta_tags_opts");

    do_action('amt_metadata_review_mode_pretext');

    echo amt_get_metadata_review($options, $add_as_view=true) . '<br /><br />';

    do_action('amt_metadata_review_mode_posttext');
}


// Main function for metadata review mode
function amt_metadata_review_mode_as_panel() {
    $options = get_option("add_meta_tags_opts");
    // Only administrators can see the review box if is_singular() is true.
    if ( amt_check_run_metadata_review_code($options) ) {
        if ( apply_filters('amt_metadata_review_mode_enable_alternative', false) ) {
            return;
        }
        // Add Purge Links to Admin Bar
        add_action('admin_bar_menu', 'amt_metadata_review_mode_admin_bar_links', 250);
        // Print the view
        add_action('wp_footer', 'amt_metadata_review_mode_print', 99999);
    }
}
add_action('wp', 'amt_metadata_review_mode_as_panel');




//
// Automatic purging of cached metadata
//



// Purging triggered by post activities

// wrapper of amt_purge_transient_cache_post for transition_post_status
function amt_purge_transient_cache_post_status($new, $old, $post) {
    if ( $old == 'publish' || $new == 'publish' ) {
        amt_purge_transient_cache_post( $post->ID );
    }
}


// Auto purge metadata cache for a post object
function amt_purge_transient_cache_post($post_id) {
    // Verify if this is an auto save routine.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( absint($post_id) <= 0 ) {
        return;
    }
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    if ( absint($options['transient_cache_expiration']) > 0 ) {
        // Purge transient data
        amt_delete_transient_cache_for_post($post_id);
    }
}

// When an attachment is updated. Required!
//add_action('edit_attachment', 'amt_purge_transient_cache_post', 10, 2);
add_action('edit_attachment', 'amt_purge_transient_cache_post');
// Check this
//add_action( 'edit_post', 'amt_purge_transient_cache_post' );
// Also works, but purges on every save
//add_action( 'save_post', 'amt_purge_transient_cache_post' );
// When post status is changed.
add_action('transition_post_status', 'amt_purge_transient_cache_post_status', 10, 3);


// Purging triggered by comment activities

// wrapper of amt_purge_transient_cache_post_comments for transition_post_status
function amt_purge_transient_cache_post_comments_status($comment_id, $new_comment_status) {
    amt_purge_transient_cache_post_comments($comment_id);
}

// Auto purge metadata cache for a post object
function amt_purge_transient_cache_post_comments($comment_id) {
    $comment = get_comment($comment_id);
    $post_id = $comment->comment_post_ID;
    if ( absint($post_id) <= 0 ) {
        return;
    }
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    if ( absint($options['transient_cache_expiration']) > 0 ) {
        // Purge transient data
        amt_delete_transient_cache_for_post($post_id);
    }
}

if ( apply_filters('amt_purge_cached_metadata_on_comment_actions', false) ) {
    add_action('comment_post', 'amt_purge_transient_cache_post_comments', 10);
    add_action('edit_comment', 'amt_purge_transient_cache_post_comments', 10);
    add_action('deleted_comment', 'amt_purge_transient_cache_post_comments', 10);
    add_action('trashed_comment', 'amt_purge_transient_cache_post_comments', 10);
    add_action('pingback_post', 'amt_purge_transient_cache_post_comments', 10);
    add_action('trackback_post', 'amt_purge_transient_cache_post_comments', 10);
    add_action('wp_set_comment_status', 'amt_purge_transient_cache_post_comments_status', 10, 2);
}

