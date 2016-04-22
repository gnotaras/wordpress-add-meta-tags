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
 * Module containing utility functions.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


// Returns the post object filtered.
// In addition see this: https://github.com/Automattic/amp-wp/commit/21180205487d71e595088e2cd1d1acba3f240ea5
function amt_get_queried_object() {
    // Sometimes, it is possible that a post object (static WP Page), which behaves
    // like a custom post type archive (eg the WooCommerce main shop page -- slug=shop)
    // has been set as the static front page.
    // In such cases the get_queried_object() function may not return a regular
    // WP_Post object, which is required by this plugin. So, in such cases we
    // retrieve the WP_Post object manually.
    if ( amt_is_static_front_page() && is_post_type_archive() ) {
        $post = get_post( amt_get_front_page_id() );
    } else {
        // Use the normal way to get the $post object.
        // Get current post object
        $post = get_queried_object();
    }
    // Allow filtering of the $post object.
    $post = apply_filters('amt_get_queried_object', $post);
    return $post;
}


// Returns the plugin options
function amt_get_options() {
    return get_option("add_meta_tags_opts");
}


// Returns a key for the non persistent cache
function amt_get_amtcache_key($basename, $post=null) {
    // Non persistent object cache
    if ( is_null($post) ) {     // other data
        $amtcache_key = sprintf('%s_data', $basename);
    } elseif ( is_numeric($post) ) {    // id
        $amtcache_key = sprintf('%s_%d', $basename, $post);
    } elseif ( isset($post->ID) ) {     // post, user
        $amtcache_key = sprintf('%s_%d', $basename, $post->ID);
    } elseif ( isset($post->term_id) ) {    // term
        $amtcache_key = sprintf('%s_%d', $basename, $post->term_id);
    } else {
        // Use a static key name. Very unlikely for the page to have two non post objects.
        $amtcache_key = sprintf('%s_other_post', $basename);
    }
    //var_dump($post);
    //var_dump($amtcache_key);
    return $amtcache_key;
}

/**
 * Helper function that returns an array of allowable HTML elements and attributes
 * for use in wp_kses() function.
 */
function amt_get_allowed_html_kses() {
    // Store supported global attributes to an array
    // As of http://www.w3schools.com/tags/ref_standardattributes.asp
    $global_attributes = array(
        'accesskey' => array(),
        'class' => array(),
        'contenteditable' => array(),
        'contextmenu' => array(),
        // 'data-*' => array(),
        'dir' => array(),
        'draggable' => array(),
        'dropzone' => array(),
        'hidden' => array(),
        'id' => array(),
        'lang' => array(),
        'spellcheck' => array(),
        'style' => array(),
        'tabindex' => array(),
        'title' => array(),
        'translate' => array()
    );

    // Construct an array of valid elements and attributes
    $valid_elements_attributes = array(
        // As of http://www.w3schools.com/tags/tag_meta.asp
        // plus 'itemprop' and 'property'
        'meta' => array_merge( array(
            'charset' => array(),
            'content' => array(),
            'value' => array(),
            'http-equiv' => array(),
            'name' => array(),
            'scheme' => array(),
            'itemprop' => array(),  // schema.org
            'property' => array()  // opengraph and others
            ), $global_attributes
        ),
        // As of http://www.w3schools.com/tags/tag_link.asp
        'link' => array_merge( array(
            'charset' => array(),
            'href' => array(),
            'hreflang' => array(),
            'media' => array(),
            'rel' => array(),
            'rev' => array(),
            'sizes' => array(),
            'target' => array(),
            'type' => array()
            ), $global_attributes
        )
    );

    // Allow filtering of $valid_elements_attributes
    $valid_elements_attributes = apply_filters( 'amt_valid_full_metatag_html', $valid_elements_attributes );

    return $valid_elements_attributes;
}


/**
 * Sanitizes text for use in the description and similar metatags.
 *
 * Currently:
 * - removes shortcodes
 * - removes double quotes
 * - convert single quotes to space
 */
function amt_sanitize_description($desc) {

    // Remove shortcode
    // Needs to be before cleaning double quotes as it may contain quoted settings.
//    $pattern = get_shortcode_regex();
    //var_dump($pattern);
    // TODO: Possibly this is not needed since shortcodes are stripped in amt_get_the_excerpt().
//    $desc = preg_replace('#' . $pattern . '#s', '', $desc);

    // Clean double quotes
    $desc = str_replace('"', '', $desc);
//    $desc = str_replace('&quot;', '', $desc);

    // Convert single quotes to space
    //$desc = str_replace("'", ' ', $desc);
    //$desc = str_replace('&#039;', ' ', $desc);
    //$desc = str_replace("&apos;", ' ', $desc);
    //$desc = str_replace("&#8216;", ' ', $desc);
    //$desc = str_replace("&#8217;", ' ', $desc);
    // Finally, convert double space to single space.
    //$desc = str_replace('  ', ' ', $desc);

    // Allow further filtering of description
    $desc = apply_filters( 'amt_sanitize_description_extra', $desc );

    return $desc;
}


/**
 * Sanitizes text for use in the 'keywords' or similar metatags.
 *
 * Currently:
 * - converts to lowercase
 * - removes double quotes
 * - convert single quotes to space
 */
function amt_sanitize_keywords( $text ) {

    // Convert to lowercase
    if (function_exists('mb_strtolower')) {
        $text = mb_strtolower($text, get_bloginfo('charset'));
    } else {
        $text = strtolower($text);
    }

    // Clean double quotes
    $text = str_replace('"', '', $text);
    $text = str_replace('&quot;', '', $text);

    // Convert single quotes to space
    $text = str_replace("'", ' ', $text);
    $text = str_replace('&#039;', ' ', $text);
    $text = str_replace("&apos;", ' ', $text);

    // Allow further filtering of keywords
    $text = apply_filters( 'amt_sanitize_keywords_extra', $text );

    return $text;
}


/**
 * Helper function that converts the placeholders used by Add-Meta-Tags
 * to a form, in which they remain unaffected by the sanitization functions.
 *
 * Currently the problem is the '%ca' part of '%cats%' which is removed
 * by sanitize_text_field().
 */
function amt_convert_placeholders( $data ) {
    $data = str_replace('%cats%', '#cats#', $data);
    $data = str_replace('%tags%', '#tags#', $data);
    $data = str_replace('%terms%', '#terms#', $data);
    $data = str_replace('%contentkw%', '#contentkw#', $data);
    $data = str_replace('%title%', '#title#', $data);
    return $data;
}


/**
 * Helper function that reverts the placeholders used by Add-Meta-Tags
 * back to their original form. This action should be performed after
 * after the sanitization functions have processed the data.
 */
function amt_revert_placeholders( $data ) {
    $data = str_replace('#cats#', '%cats%', $data);
    $data = str_replace('#tags#', '%tags%', $data);
    $data = str_replace('#terms#', '%terms%', $data);
    $data = str_replace('#contentkw#', '%contentkw%', $data);
    $data = str_replace('#title#', '%title%', $data);
    return $data;
}


/**
 * This function is meant to be used in order to append information about the
 * current page to the description or the title of the content.
 *
 * Works on both:
 * 1. paged archives or main blog page
 * 2. multipage content
 */
function amt_process_paged( $data ) {

    if ( !empty( $data ) ) {

        $data_to_append = ' | Page ';
        //TODO: Check if it should be translatable
        //$data_to_append = ' | ' . __('Page', 'add-meta-tags') . ' ';

        // Allowing filtering of the $data_to_append
        $data_to_append = apply_filters( 'amt_paged_append_data', $data_to_append );

        // For paginated archives or paginated main page with latest posts.
        if ( is_paged() ) {
            $paged = get_query_var( 'paged' );  // paged
            if ( $paged && $paged >= 2 ) {
                return $data . $data_to_append . $paged;
            }
        // For a Post or PAGE Page that has been divided into pages using the <!--nextpage--> QuickTag
        } else {
            $paged = get_query_var( 'page' );  // page
            if ( $paged && $paged >= 2 ) {
                return $data . $data_to_append . $paged;
            }
        }
    }
    return $data;
}


// Escapes the contents of a field that can accept an attachment ID (integer) and a URL
// Mainly used for 'Global Image Override' fields and 'Default_image_URL' field.
function amt_esc_id_or_url_notation( $data ) {
    if ( empty($data) || is_numeric($data) ) {
        return $data;
    }
    // Treat as URL. Split into pieaces (URL,WIDTHxHEIGHT), escape each and
    // then reconstruct the data.
    $parts = explode(',', $data);
    if ( count($parts) == 1 ) {
        // We have just the URL
        return esc_url($data);
    } else {
        $url = $parts[0];
        $dimensions = explode('x', $parts[1]);
        if ( count($dimensions) != 2 ) {
            return esc_url($url);
        } elseif ( ! is_numeric($dimensions[0]) || ! is_numeric($dimensions[1]) ) {
            return esc_url($url);
        }
    }
    return sprintf('%s,%dx%d', esc_url($url), absint($dimensions[0]), absint($dimensions[1]));
}


// Function that cleans the content of the post
// Removes HTML markup, expands or removes short codes etc.
function amt_get_clean_post_content( $options, $post ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_clean_post_content', $post);
    $plain_text_processed = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $plain_text_processed !== false ) {
        return $plain_text_processed;
    }

    // Early filter that lets dev define the post. This makes it possible to
    // exclude specific parts of the post for the rest of the algorithm.
    // NOTE: qtranslate-X needs to pass through __() at this point.
    $initial_content = apply_filters( 'amt_get_the_excerpt_initial_content', $post->post_content, $post );

    // First expand the shortcodes if the relevant setting is enabled.
    if ( $options['expand_shortcodes'] == '1' ) {
        $initial_content = do_shortcode( $initial_content );
        // Filter the initial content again after expanding the shortcodes.
        $initial_content = apply_filters( 'amt_get_the_excerpt_initial_content_expanded_shortcodes', $initial_content, $post );
    }

    // Second strip all HTML tags
    //$plain_text = wp_kses( $initial_content, array() );
    // Use wp_strip_all_tags() instead of wp_kses(). The latter leave the contents
    // of script/style HTML tags.
    $plain_text = wp_strip_all_tags( $initial_content, true );
    
    // Strip properly registered shortcodes
    $plain_text = strip_shortcodes( $plain_text );
    // Also strip any shortcodes (For example, required for the removal of Visual Composer shortcodes)
    $plain_text = preg_replace('#\[[^\]]+\]#', '', $plain_text);

    // Late preprocessing filter. Content has no HTML tags and no properly registered shortcodes. Other shortcodes might still exist.
    $plain_text_processed = apply_filters( 'amt_get_the_excerpt_plain_text', $plain_text, $post );

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $plain_text_processed, $group='add-meta-tags' );

    return $plain_text_processed;
}


/**
 * Returns the post's excerpt.
 * This function was written in order to get the excerpt *outside* the loop
 * because the get_the_excerpt() function does not work there any more.
 * This function makes the retrieval of the excerpt independent from the
 * WordPress function in order not to break compatibility with older WP versions.
 *
 * Also, this is even better as the algorithm tries to get text of average
 * length 250 characters, which is more SEO friendly. The algorithm is not
 * perfect, but will do for now.
 *
 * MUST return sanitized text.
 */
function amt_get_the_excerpt( $post, $excerpt_max_len=300, $desc_avg_length=250, $desc_min_length=150 ) {
    
    $options = amt_get_options();

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_the_excerpt', $post);
    $amt_excerpt = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $amt_excerpt !== false ) {
        return $amt_excerpt;
    }

    if ( empty($post->post_excerpt) || get_post_type( $post ) == 'attachment' ) {   // In attachments we always use $post->post_content to get a description

        // Here we generate an excerpt from $post->post_content

        // Get clean content data
        $plain_text_processed = amt_get_clean_post_content( $options, $post );

        // Get the initial text.
        // We use $excerpt_max_len characters of the text for the description.
        $amt_excerpt = sanitize_text_field( amt_sanitize_description( substr($plain_text_processed, 0, $excerpt_max_len) ) );

        // Remove any URLs that may exist exactly at the beginning of the description.
        // This may happen if for example you put a youtube video url first thing in
        // the post body.
        $amt_excerpt = preg_replace( '#^https?:[^\t\r\n\s]+#i', '', $amt_excerpt );
        $amt_excerpt = ltrim( $amt_excerpt );

        // If this was not enough, try to get some more clean data for the description (nasty hack)
        if ( strlen($amt_excerpt) < $desc_avg_length ) {
            $amt_excerpt = sanitize_text_field( amt_sanitize_description( substr($plain_text_processed, 0, (int) ($excerpt_max_len * 1.5)) ) );
            if ( strlen($amt_excerpt) < $desc_avg_length ) {
                $amt_excerpt = sanitize_text_field( amt_sanitize_description( substr($plain_text_processed, 0, (int) ($excerpt_max_len * 2)) ) );
            }
        }

/** ORIGINAL ALGO

        // Get the initial data for the excerpt
        $amt_excerpt = strip_tags(substr($post->post_content, 0, $excerpt_max_len));

        // If this was not enough, try to get some more clean data for the description (nasty hack)
        if ( strlen($amt_excerpt) < $desc_avg_length ) {
            $amt_excerpt = strip_tags(substr($post->post_content, 0, (int) ($excerpt_max_len * 1.5)));
            if ( strlen($amt_excerpt) < $desc_avg_length ) {
                $amt_excerpt = strip_tags(substr($post->post_content, 0, (int) ($excerpt_max_len * 2)));
            }
        }

*/
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
        $amt_excerpt = sanitize_text_field( amt_sanitize_description( $post->post_excerpt ) );

        // NOTE ABOUT ATTACHMENTS: In attachments $post->post_excerpt is the caption.
        // It is usual that attachments have both the post_excerpt and post_content set.
        // Attachments should never enter here, but be processed above, so that
        // post->post_content is always used as the source of the excerpt.

    }

    /**
     * In some cases, the algorithm might not work, depending on the content.
     * In those cases, $amt_excerpt might only contain ``...``. Here we perform
     * a check for this and return an empty $amt_excerpt.
     */
    if ( trim($amt_excerpt) == "..." ) {
        $amt_excerpt = "";
    }

    /**
     * Allow filtering of the generated excerpt.
     *
     * Filter with:
     *
     *  function customize_amt_excerpt( $amt_excerpt, $post ) {
     *      $amt_excerpt = ...
     *      return $amt_excerpt;
     *  }
     *  add_filter( 'amt_get_the_excerpt', 'customize_amt_excerpt', 10, 2 );
     */
    $amt_excerpt = apply_filters( 'amt_get_the_excerpt', $amt_excerpt, $post );

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $amt_excerpt, $group='add-meta-tags' );

    return $amt_excerpt;
}


/**
 * Returns a comma-delimited list of a post's terms that belong to custom taxonomies.
 */
function amt_get_keywords_from_custom_taxonomies( $post ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_keywords_from_custom_taxonomies', $post);
    $custom_tax_keywords = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $custom_tax_keywords !== false ) {
        return $custom_tax_keywords;
    }

    $custom_tax_keywords = '';

    // Array to hold all terms of custom taxonomies.
    $keywords_arr = array();

    // Get the custom taxonomy names.
    // Arguments in order to retrieve all public custom taxonomies
    // (excluding the builtin categories, tags and post formats.)
    $args = array(
        'public'   => true,
        '_builtin' => false
    );
    $output = 'names'; // or objects
    $operator = 'and'; // 'and' or 'or'
    $taxonomies = get_taxonomies( $args, $output, $operator );

    // Get the terms of each taxonomy and append to $keywords_arr
    foreach ( $taxonomies  as $taxonomy ) {
        $terms = get_the_terms( $post->ID, $taxonomy );
        if ( $terms && is_array($terms) ) {
            foreach ( $terms as $term ) {
                $keywords_arr[] = $term->name;
            }
        }
    }

    if ( ! empty( $keywords_arr ) ) {
        $custom_tax_keywords = implode(', ', $keywords_arr);
    } else {
        $custom_tax_keywords = '';
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $custom_tax_keywords, $group='add-meta-tags' );

    return $custom_tax_keywords;
}


/**
 * Returns a comma-delimited list of a post's categories.
 */
function amt_get_keywords_from_post_cats( $post ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_keywords_from_post_cats', $post);
    $postcats = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $postcats !== false ) {
        return $postcats;
    }

    $postcats = '';

    foreach((get_the_category($post->ID)) as $cat) {
        if ( $cat->slug != 'uncategorized' ) {
            $postcats .= $cat->cat_name . ', ';
        }
    }
    // strip final comma
    $postcats = substr($postcats, 0, -2);

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $postcats, $group='add-meta-tags' );

    return $postcats;
}


/**
 * Helper function. Returns the first category the post belongs to.
 */
function amt_get_first_category( $post ) {
    $cats = amt_get_keywords_from_post_cats( $post );
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

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_post_tags', $post);
    $posttags = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $posttags !== false ) {
        return $posttags;
    }

    $posttags = '';

    if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
        $tags = get_the_tags($post->ID);
        if ( ! empty( $tags ) ) {
            foreach ( $tags as $tag ) {
                $posttags .= $tag->name . ', ';
            }
            $posttags = rtrim($posttags, " ,");
        }
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $posttags, $group='add-meta-tags' );

    return $posttags;
}


/**
 * Returns a comma-delimited list of all the blog's categories.
 * The built-in category "Uncategorized" is excluded.
 */
function amt_get_all_categories($no_uncategorized = TRUE) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_all_categories');
    $all_cats = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $all_cats !== false ) {
        return $all_cats;
    }

    $all_cats = '';

    global $wpdb;

    if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
        $cat_field = "name";
        $sql = "SELECT name FROM $wpdb->terms LEFT OUTER JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' ORDER BY name ASC";
    } else {
        $cat_field = "cat_name";
        $sql = "SELECT cat_name FROM $wpdb->categories ORDER BY cat_name ASC";
    }
    $categories = $wpdb->get_results($sql);
    if ( ! empty( $categories ) ) {
        foreach ( $categories as $cat ) {
            if ($no_uncategorized && $cat->$cat_field != "Uncategorized") {
                $all_cats .= $cat->$cat_field . ', ';
            }
        }
        $all_cats = rtrim($all_cats, " ,");
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $all_cats, $group='add-meta-tags' );

    return $all_cats;
}


/**
 * Returns an array of the category names that appear in the posts of the loop.
 * Category 'Uncategorized' is excluded.
 *
 * Accepts the $category_arr, an array containing the initial categories.
 */
function amt_get_categories_from_loop() {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_categories_from_loop');
    $category_arr = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $category_arr !== false ) {
        return $category_arr;
    }

    $category_arr = array();

    if (have_posts()) {
        while ( have_posts() ) {
            the_post(); // Iterate the post index in The Loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
            $categories = get_the_category();
            if( ! empty($categories) ) {
                foreach( $categories as $category ) {
                    if ( ! in_array( $category->name, $category_arr ) && $category->slug != 'uncategorized' ) {
                        $category_arr[] = $category->name;
                    }
                }
            }
		}
	}
    rewind_posts(); // Not sure if this is needed.

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $category_arr, $group='add-meta-tags' );

    return $category_arr;
}


/**
 * Returns an array of the tag names that appear in the posts of the loop.
 *
 * Accepts the $tag_arr, an array containing the initial tags.
 */
function amt_get_tags_from_loop() {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_tags_from_loop');
    $tag_arr = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $tag_arr !== false ) {
        return $tag_arr;
    }

    $tag_arr = array();

    if (have_posts()) {
        while ( have_posts() ) {
            the_post(); // Iterate the post index in The Loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
            $tags = get_the_tags();
            if( ! empty($tags) ) {
                foreach( $tags as $tag ) {
                    if ( ! in_array( $tag->name, $tag_arr ) ) {
                        $tag_arr[] = $tag->name;
                    }
                }
            }
		}
	}
    rewind_posts(); // Not sure if this is needed.

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $tag_arr, $group='add-meta-tags' );

    return $tag_arr;
}


/**
 * Returns an array of URLS of referenced items in the post.
 *
 * Accepts a post object.
 */
function amt_get_referenced_items( $post ) {
    if ( is_singular() ) {  // TODO: check if this check is needed at all!
        $referenced_list_content = amt_get_post_meta_referenced_list( $post->ID );
        if ( ! empty( $referenced_list_content ) ) {
            // Each line contains a single URL. Split the string and convert each line to an array item.
            $referenced_list_content = str_replace("\r", '', $referenced_list_content);     // Do not change the double quotes.
            return explode("\n", $referenced_list_content);                                 // Do not change the double quotes.
        }
    }
    return array();
}


/**
 * This is a helper function that returns the post's or page's description.
 *
 * Important: MUST return sanitized data, unless this plugin has sanitized the data before storing to db.
 *
 */
function amt_get_content_description( $post, $auto=true ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_content_description', $post);
    $content_description = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $content_description !== false ) {
        return $content_description;
    }

    // By default, if a custom description has not been entered by the user in the
    // metabox, a description is autogenerated. To stop this automatic generation
    // of a description and return only the description that has been entered manually,
    // set $auto to false via the following filter.
    $auto = apply_filters( 'amt_generate_description_if_no_manual_data', $auto );

    $content_description = '';

    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {    // TODO: check if this check is needed at all!

        $desc_fld_content = amt_get_post_meta_description( $post->ID );

        if ( !empty($desc_fld_content) ) {
            // If there is a custom field, use it
            $content_description = $desc_fld_content;
        } else {
            // Else, use the post's excerpt. Valid for Pages too.
            if ($auto) {
                // The generated excerpt should already be sanitized.
                $content_description = amt_get_the_excerpt( $post );
            }
        }
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $content_description, $group='add-meta-tags' );

    // Allow filtering of the final description
    // NOTE: qtranslate-X needs to pass through __() at this point.
    $content_description = apply_filters( 'amt_get_content_description', $content_description, $post );

    return $content_description;
}


/**
 * This is a helper function that returns the post's or page's keywords.
 *
 * Important: MUST return sanitized data, unless this plugin has sanitized the data before storing to db.
 *
 */
function amt_get_content_keywords($post, $auto=true, $exclude_categories=false) {
    
    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_content_keywords', $post);
    $content_keywords = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $content_keywords !== false ) {
        return $content_keywords;
    }

    // By default, if custom keywords have not been entered by the user in the
    // metabox, keywords are autogenerated. To stop this automatic generation
    // of keywords and return only the keywords that have been entered manually,
    // set $auto to false via the following filter.
    $auto = apply_filters( 'amt_generate_keywords_if_no_manual_data', $auto );

    $content_keywords = '';

    /*
     * Custom post field "keywords" overrides post's categories, tags (tags exist in WordPress 2.3 or newer)
     * and custom taxonomy terms (custom taxonomies exist since WP version 2.8).
     * %cats% is replaced by the post's categories.
     * %tags% is replaced by the post's tags.
     * %terms% is replaced by the post's custom taxonomy terms.
     */
    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        $keyw_fld_content = amt_get_post_meta_keywords( $post->ID );

        // If there is a custom field, use it
        if ( ! empty($keyw_fld_content) ) {
            
            // On single posts, expand the %cats%, %tags% and %terms% placeholders.
            // This should not take place in pages (no categories, no tags by default)
            // or custom post types, the support of which for categories and tags is unknown.

            if ( is_single() ) {

                // Here we sanitize the provided keywords for safety
                $keywords_from_post_cats = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_post_cats($post) ) );
                if ( ! empty($keywords_from_post_cats) ) {
                    $keyw_fld_content = str_replace("%cats%", $keywords_from_post_cats, $keyw_fld_content);
                }

                // Also, the %tags% placeholder is replaced by the post's tags (WordPress 2.3 or newer)
                if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
                    // Here we sanitize the provided keywords for safety
                    $keywords_from_post_tags = sanitize_text_field( amt_sanitize_keywords( amt_get_post_tags($post) ) );
                    if ( ! empty($keywords_from_post_tags) ) {
                        $keyw_fld_content = str_replace("%tags%", $keywords_from_post_tags, $keyw_fld_content);
                    }
                }

                // Also, the %terms% placeholder is replaced by the post's custom taxonomy terms (WordPress 2.8 or newer)
                if ( version_compare( get_bloginfo('version'), '2.8', '>=' ) ) {
                    // Here we sanitize the provided keywords for safety
                    $keywords_from_post_terms = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_custom_taxonomies($post) ) );
                    if ( ! empty($keywords_from_post_terms) ) {
                        $keyw_fld_content = str_replace("%terms%", $keywords_from_post_terms, $keyw_fld_content);
                    }
                }
            }
            $content_keywords .= $keyw_fld_content;

        // Otherwise, generate the keywords from categories, tags and custom taxonomy terms.
        // Note:
        // Here we use is_singular(), so that pages are also checked for categories and tags.
        // By default, pages do not support categories and tags, but enabling such
        // functionality is trivial. See #1206 for more details.

        } elseif ( $auto && is_singular() ) {
            /*
             * Add keywords automatically.
             * Keywords consist of the post's categories, the post's tags (tags exist in WordPress 2.3 or newer)
             * and the terms of the custom taxonomies to which the post belongs (since WordPress 2.8).
             */
            // Categories - Here we sanitize the provided keywords for safety
            if ( $exclude_categories === false ) {
                $keywords_from_post_cats = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_post_cats($post) ) );
                if (!empty($keywords_from_post_cats)) {
                    $content_keywords .= $keywords_from_post_cats;
                }
            }
            // Tags - Here we sanitize the provided keywords for safety
            $keywords_from_post_tags = sanitize_text_field( amt_sanitize_keywords( amt_get_post_tags($post) ) );
            if (!empty($keywords_from_post_tags)) {
                if ( ! empty($content_keywords) ) {
                    $content_keywords .= ", ";
                }
                $content_keywords .= $keywords_from_post_tags;
            }
            // Custom taxonomy terms - Here we sanitize the provided keywords for safety
            $keywords_from_post_custom_taxonomies = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_custom_taxonomies($post) ) );
            if (!empty($keywords_from_post_custom_taxonomies)) {
                if ( ! empty($content_keywords) ) {
                    $content_keywords .= ", ";
                }
                $content_keywords .= $keywords_from_post_custom_taxonomies;
            }
        }
    }

    // Add post format to the list of keywords
    if ( $auto && is_singular() && get_post_format($post->ID) !== false ) {
        if ( empty($content_keywords) ) {
            $content_keywords .= get_post_format($post->ID);
        } else {
            $content_keywords .= ', ' . get_post_format($post->ID);
        }
    }

    /**
     * Finally, add the global keywords, if they are set in the administration panel.
     */
    //if ( !empty($content_keywords) && ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) ) {
    if ( $auto && ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) ) {

        $options = get_option("add_meta_tags_opts");
        $global_keywords = amt_get_site_global_keywords($options);

        if ( ! empty($global_keywords) ) {

            // If we have $content_keywords so far
            if ( ! empty($content_keywords) ) {
                if ( strpos($global_keywords, '%contentkw%') === false ) {
                    // The placeholder ``%contentkw%`` has not been used. Append the content keywords to the global keywords.
                    $content_keywords = $global_keywords . ', ' . $content_keywords;
                } else {
                    // The user has used the placeholder ``%contentkw%``. Replace it with the content keywords.
                    $content_keywords = str_replace('%contentkw%', $content_keywords, $global_keywords);
                }

            // If $content_keywords have not been found.
            } else {
                if ( strpos($global_keywords, '%contentkw%') === false ) {
                    // The placeholder ``%contentkw%`` has not been used. Just use the global keywords as is.
                    $content_keywords = $global_keywords;
                } else {
                    // The user has used the placeholder ``%contentkw%``, but we do not have generated any content keywords => Delete the %contentkw% placeholder.
                    $global_keywords_new = array();
                    foreach ( explode(',', $global_keywords) as $g_keyword ) {
                        $g_keyword = trim($g_keyword);
                        if ( $g_keyword != '%contentkw%' ) {
                            $global_keywords_new[] = $g_keyword;
                        }
                    }
                    if ( ! empty($global_keywords_new) ) {
                        $content_keywords = implode(', ', $global_keywords_new);
                    }
                }
            }

        }
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $content_keywords, $group='add-meta-tags' );

    return $content_keywords;
}


/**
 * Helper function that returns an array containing the post types that are
 * supported by Add-Meta-Tags. These include:
 *
 *   - post
 *   - page
 *   - attachment
 *
 * And also to ALL public custom post types which have a UI.
 *
 */
function amt_get_supported_post_types() {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_supported_post_types');
    $supported_types = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $supported_types !== false ) {
        return $supported_types;
    }

    $supported_builtin_types = array('post', 'page', 'attachment');
    $public_custom_types = get_post_types( array('public'=>true, '_builtin'=>false, 'show_ui'=>true) );
    $supported_types = array_merge($supported_builtin_types, $public_custom_types);

    // Allow filtering of the supported content types.
    $supported_types = apply_filters( 'amt_supported_post_types', $supported_types );

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $supported_types, $group='add-meta-tags' );

    return $supported_types;
}


/**
 * Helper function that returns an array containing permissions for the
 * Metadata metabox.
 */
function amt_get_metadata_metabox_permissions() {
    //
    // Default Metadata metabox permission settings.
    // Regardless of these settings the `edit_posts` capability is _always_
    // checked when reading/writing metabox data, so the `edit_posts` capability
    // should be considered as the least restrictive capability that can be used.
    // The available Capabilities vs Roles table can be found here:
    //     http://codex.wordpress.org/Roles_and_Capabilities
    // To disable a box, simply add a very restrictive capability like `create_users`.
    //
    $metabox_permissions = array(
        // Minimum capability for the metabox to appear in the editing
        // screen of the supported post types.
        'global_metabox_capability' => 'edit_posts',
        // The following permissions have an effect only if they are stricter
        // than the permission of the `global_metabox_capability` setting.
        // Edit these, only if you want to further restrict access to
        // specific boxes, for example the `full metatags` box.
        'description_box_capability' => 'edit_posts',
        'keywords_box_capability' => 'edit_posts',
        'title_box_capability' => 'edit_posts',
        'news_keywords_box_capability' => 'edit_posts',
        'full_metatags_box_capability' => 'edit_posts',
        'image_url_box_capability' => 'edit_posts',
        'content_locale_box_capability' => 'edit_posts',
        'express_review_box_capability' => 'edit_posts',
        'referenced_list_box_capability' => 'edit_posts',
        // Term meta
        'term_full_metatags_box_capability' => 'edit_published_posts',
        'term_image_url_box_capability' => 'edit_published_posts',
        // User meta
        'user_full_metatags_box_capability' => 'edit_published_posts',
        'user_image_url_box_capability' => 'edit_published_posts',
    );
    // Allow filtering of the metabox permissions
    $metabox_permissions = apply_filters( 'amt_metadata_metabox_permissions', $metabox_permissions );

    return $metabox_permissions;
}


/**
 * Helper function that returns an array of the supported custom fields.
 */
function amt_get_post_custom_field_names() {
    return array(
        '_amt_description',
        '_amt_keywords',
        '_amt_title',
        '_amt_news_keywords',
        '_amt_full_metatags',
        '_amt_image_url',
        '_amt_content_locale',
        '_amt_express_review',
        '_amt_referenced_list',
    );
}

/**
 * Helper function that returns an array of the supported term meta
 */
function amt_get_term_custom_field_names() {
    return array(
        '_amt_term_full_metatags',
        '_amt_term_image_url',
    );
}

/**
 * Helper function that returns an array of the supported user meta fields.
 */
function amt_get_user_custom_field_names() {
    return array(
        // Contact methods
        'amt_facebook_author_profile_url',
        'amt_facebook_publisher_profile_url',
        'amt_googleplus_author_profile_url',
        'amt_googleplus_publisher_profile_url',
        'amt_twitter_author_username',
        'amt_twitter_publisher_username',
        // User Meta
        '_amt_user_full_metatags',
        '_amt_user_image_url',
    );
}

/**
 * Helper function that returns an array containing the post types
 * on which the Metadata metabox should be added.
 *
 *   - post
 *   - page
 *
 * And also to ALL public custom post types which have a UI.
 *
 * NOTE ABOUT attachments:
 * The 'attachment' post type does not support saving custom fields like other post types.
 * See: http://www.codetrax.org/issues/875
 */
function amt_get_post_types_for_metabox() {
    // Get the post types supported by Add-Meta-Tags
    $supported_builtin_types = amt_get_supported_post_types();
    // The 'attachment' post type does not support saving custom fields like
    // other post types. See: http://www.codetrax.org/issues/875
    // So, the 'attachment' type is removed (if exists) so as not to add a metabox there.
    $attachment_post_type_key = array_search( 'attachment', $supported_builtin_types );
    if ( $attachment_post_type_key !== false ) {
        // Remove this type from the array
        unset( $supported_builtin_types[ $attachment_post_type_key ] );
    }
    // Get public post types
    $public_custom_types = get_post_types( array('public'=>true, '_builtin'=>false, 'show_ui'=>true) );
    $supported_types = array_merge($supported_builtin_types, $public_custom_types);

    // Allow filtering of the supported content types.
    $supported_types = apply_filters( 'amt_metabox_post_types', $supported_types );     // Leave this filter out of the documentation for now.

    return $supported_types;
}


/**
 * Helper function that returns the value of the custom field that contains
 * the content description.
 * The default field name for the description has changed to ``_amt_description``.
 * For easy migration this function supports reading the description from the
 * old ``description`` custom field and also from the custom field of other plugins.
 */
function amt_get_post_meta_description( $post_id ) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_description', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_description'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_description', 'description' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_description_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the description.
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the content keywords.
 * The default field name for the keywords has changed to ``_amt_keywords``.
 * For easy migration this function supports reading the keywords from the
 * old ``keywords`` custom field and also from the custom field of other plugins.
 */
function amt_get_post_meta_keywords($post_id) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_keywords', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_keywords'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_keywords', 'keywords' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_keywords_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the keywords.
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the custom content title.
 * The default field name for the title is ``_amt_title``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_title($post_id) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_title', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_title'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_title' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_title_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the custom title.
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the 'news_keywords' value.
 * The default field name for the 'news_keywords' is ``_amt_news_keywords``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_newskeywords($post_id) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_news_keywords', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_news_keywords'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_news_keywords' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_news_keywords_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the news keywords.
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


//
// Helper function that returns the value of the custom field that contains
// the per-post full metatags.
// The default field name is ``_amt_full_metatags``.
// No need to migrate from older field name.
//
function amt_get_post_meta_full_metatags($post_id) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_post_meta_full_metatags', $post_id);
    $field_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $field_value !== false ) {
        return $field_value;
    }

    $field_value = '';

    $options = get_option('add_meta_tags_opts');

    if ( ! is_array($options) ) {
        $field_value = '';
    } elseif ( ! array_key_exists( 'metabox_enable_full_metatags', $options) ) {
        $field_value = '';
    } elseif ( $options['metabox_enable_full_metatags'] == '0' ) {
        $field_value = '';
    } else {
        // Internal fields - order matters
        $supported_custom_fields = array( '_amt_full_metatags' );
        // External fields - Allow filtering
        $external_fields = array();
        $external_fields = apply_filters( 'amt_external_full_metatags_fields', $external_fields, $post_id );
        // Merge external fields to our supported custom fields
        $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

        // Get an array of all custom fields names of the post
        $custom_fields = get_post_custom_keys( $post_id );
        if ( empty( $custom_fields ) ) {
            // Just return an empty string if no custom fields have been associated with this content.
            $field_value = '';
        } else {
            // Try our fields
            foreach( $supported_custom_fields as $sup_field ) {
                // If such a field exists in the db, return its content as the news keywords.
                if ( in_array( $sup_field, $custom_fields ) ) {
                    $field_value = get_post_meta( $post_id, $sup_field, true );
                    break;
                }
            }
        }
    }
    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $field_value, $group='add-meta-tags' );

    return $field_value;
}







//
// Helper function that returns the value of the custom field that contains
// a global image override URL.
// The default field name for the 'global image override URL' is ``_amt_image_url``.
// No need to migrate from older field name.
//
function amt_get_post_meta_image_url($post_id) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_post_meta_image_url', $post_id);
    $image_url = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $image_url !== false ) {
        return $image_url;
    }

    $image_url = '';

    $options = get_option('add_meta_tags_opts');

    if ( ! is_array($options) ) {
        $image_url = '';
    } elseif ( ! array_key_exists( 'metabox_enable_image_url', $options) ) {
        $image_url = '';
    } elseif ( $options['metabox_enable_image_url'] == '0' ) {
        $image_url = '';
    } else {
        // Internal fields - order matters
        $supported_custom_fields = array( '_amt_image_url' );
        // External fields - Allow filtering
        $external_fields = array();
        $external_fields = apply_filters( 'amt_external_image_url_fields', $external_fields, $post_id );
        // Merge external fields to our supported custom fields
        $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

        // Get an array of all custom fields names of the post
        $custom_fields = get_post_custom_keys( $post_id );
        if ( empty( $custom_fields ) ) {
            // Just return an empty string if no custom fields have been associated with this content.
            $image_url = '';
        } else {
            // Try our fields
            foreach( $supported_custom_fields as $sup_field ) {
                // If such a field exists in the db, return its content as the news keywords.
                if ( in_array( $sup_field, $custom_fields ) ) {
                    $image_url = get_post_meta( $post_id, $sup_field, true );
                    break;
                }
            }
        }
    }
    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $image_url, $group='add-meta-tags' );

    return $image_url;
}


/**
 * Helper function that returns the value of the custom field that contains
 * a locale override for the content.
 * The default field name for the 'content locale override' is ``_amt_content_locale``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_content_locale($post_id) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_content_locale', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_content_locale'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_content_locale' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_content_locale_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the news keywords.
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * express review related information.
 * The default field name for the 'express review' is ``_amt_express_review``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_express_review($post_id) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_express_review', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_express_review'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_express_review' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_express_review_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the news keywords.
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the list of URLs of items referenced in the post.
 * The default field name is ``_amt_referenced_list``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_referenced_list($post_id) {
    $options = get_option('add_meta_tags_opts');
    if ( ! is_array($options) ) {
        return '';
    } elseif ( ! array_key_exists( 'metabox_enable_referenced_list', $options) ) {
        return '';
    } elseif ( $options['metabox_enable_referenced_list'] == '0' ) {
        return '';
    }
    // Internal fields - order matters
    $supported_custom_fields = array( '_amt_referenced_list' );
    // External fields - Allow filtering
    $external_fields = array();
    $external_fields = apply_filters( 'amt_external_referenced_list_fields', $external_fields, $post_id );
    // Merge external fields to our supported custom fields
    $supported_custom_fields = array_merge( $supported_custom_fields, $external_fields );

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys( $post_id );
    if ( empty( $custom_fields ) ) {
        // Just return an empty string if no custom fields have been associated with this content.
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the URL list of referenced items (text).
        if ( in_array( $sup_field, $custom_fields ) ) {
            return get_post_meta( $post_id, $sup_field, true );
        }
    }

    //Return empty string if all fail
    return '';
}


//
// Helper functions for the retrieval of term meta
//

// Helper function that returns the value of the custom field that contains
// the per-term full metatags.
// The default field name is ``_amt_term_full_metatags``.
// No need to migrate from older field name.
function amt_get_term_meta_full_metatags($term_id) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_term_meta_full_metatags', $term_id);
    $field_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $field_value !== false ) {
        return $field_value;
    }

    $options = amt_get_options();

    $field_name = '_amt_term_full_metatags';
    $field_value = '';

    if ( $options['metabox_term_enable_full_metatags'] == '1' ) {
        $field_value = get_term_meta( $term_id, $field_name, true );
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $field_value, $group='add-meta-tags' );

    return $field_value;
}


//
// Helper function that returns the value of the custom field that contains
// a global image override URL.
// The default field name for the 'global image override URL' is ``_amt_term_image_url``.
// No need to migrate from older field name.
//
function amt_get_term_meta_image_url($term_id) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_term_meta_image_url', $term_id);
    $field_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $field_value !== false ) {
        return $field_value;
    }

    $options = amt_get_options();

    $field_name = '_amt_term_image_url';
    $field_value = '';

    if ( $options['metabox_term_enable_image_url'] == '1' ) {
        $field_value = get_term_meta( $term_id, $field_name, true );
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $field_value, $group='add-meta-tags' );

    return $field_value;
}


//
// Helper functions for the retrieval of user meta
//

// Helper function that returns the value of the custom field that contains
// the per-user full metatags.
// The default field name is ``_amt_user_full_metatags``.
// No need to migrate from older field name.
function amt_get_user_meta_full_metatags($user_id) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_user_meta_full_metatags', $user_id);
    $field_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $field_value !== false ) {
        return $field_value;
    }

    $options = amt_get_options();

    $field_name = '_amt_user_full_metatags';
    $field_value = '';

    if ( $options['metabox_user_enable_full_metatags'] == '1' ) {
        $field_value = get_user_meta( $user_id, $field_name, true );
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $field_value, $group='add-meta-tags' );

    return $field_value;
}


//
// Helper function that returns the value of the custom field that contains
// a global image override URL.
// The default field name for the 'global image override URL' is ``_amt_user_image_url``.
// No need to migrate from older field name.
//
function amt_get_user_meta_image_url($user_id) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_user_meta_image_url', $user_id);
    $field_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $field_value !== false ) {
        return $field_value;
    }

    $options = amt_get_options();

    $field_name = '_amt_user_image_url';
    $field_value = '';

    if ( $options['metabox_user_enable_image_url'] == '1' ) {
        $field_value = get_user_meta( $user_id, $field_name, true );
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $field_value, $group='add-meta-tags' );

    return $field_value;
}


/**
 * Helper function that returns an array of objects attached to the provided
 * $post object.
 */
function amt_get_ordered_attachments( $post ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_ordered_attachments', $post);
    $ordered_attachments = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $ordered_attachments !== false ) {
        return $ordered_attachments;
    }

    // to return IDs:
    // $attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
    $ordered_attachments = get_children( array(
        'numberposts' => -1,
        'post_parent' => $post->ID,
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        //'post_mime_type' => 'image',
        'order' => 'ASC',
        'orderby' => 'menu_order ID'
        )
    );

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $ordered_attachments, $group='add-meta-tags' );

    return $ordered_attachments;
}


/**
 * Helper function that returns the permalink of the provided $post object,
 * taking into account multipage content.
 *
 * ONLY for content.
 * DO NOT use with:
 *  - paged archives
 *  - static page as front page
 *  - static page as posts index page
 *
 * Uses logic from default WordPress function: _wp_link_page
 *   - http://core.trac.wordpress.org/browser/trunk/src/wp-includes/post-template.php#L705
 * Also see: wp-includes/canonical.php line: 227 (Post Paging)
 *
 */
function amt_get_permalink_for_multipage( $post ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_permalink_for_multipage', $post);
    $permalink = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $permalink !== false ) {
        return $permalink;
    }

    $permalink = '';

    $pagenum = get_query_var( 'page' );
    // Content is multipage
    if ( $pagenum && $pagenum > 1 ) {
        // Not using clean URLs -> Add query argument to the URL (eg: ?page=2)
        if ( '' == get_option('permalink_structure') || in_array( $post->post_status, array('draft', 'pending')) ) {
            $permalink = esc_url( add_query_arg( 'page', $pagenum, get_permalink($post->ID) ) );
        // Using clean URLs
        } else {
            $permalink = trailingslashit( get_permalink($post->ID) ) . user_trailingslashit( $pagenum, 'single_paged');
        }
    // Content is not paged
    } else {
        $permalink = get_permalink($post->ID);
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $permalink, $group='add-meta-tags' );

    return $permalink;
}


/**
 *  Helper function that returns true if a static page is used as the homepage
 *  instead of the default posts index page.
 */
function amt_has_page_on_front() {
    $front_type = get_option('show_on_front', 'posts');
    if ( $front_type == 'page' ) {
        return true;
    }
    return false;
}


/**
 * Helper function that returns true, if the currently displayed page is a
 * page that has been set as the 'posts' page in the 'Reading Settings'.
 * See: http://codex.wordpress.org/Conditional_Tags#The_Main_Page
 *
 * This function was written because is_page() is not true for the page that is
 * used as the 'posts' page.
 */
function amt_is_static_home() {
    if ( amt_has_page_on_front() && is_home() ) {
        return true;
    }
    return false;
}


/**
 * Helper function that returns true, if the currently displayed page is a
 * page that has been set as the 'front' page in the 'Reading Settings'.
 * See: http://codex.wordpress.org/Conditional_Tags#The_Main_Page
 *
 * This function was written because is_front_page() returns true if a static
 * page is used as the front page and also if the latest posts are displayed
 * on the front page.
 */
function amt_is_static_front_page() {
    if ( amt_has_page_on_front() && is_front_page() ) {
        return true;
    }
    return false;
}


/**
 * Helper function that returns true, if the currently displayed page is the
 * main index page of the site that displays the latest posts.
 *
 * This function was written because is_front_page() returns true if a static
 * page is used as the front page and also if the latest posts are displayed
 * on the front page.
 */
function amt_is_default_front_page() {
    if ( !amt_has_page_on_front() && is_front_page() ) {
        return true;
    }
    return false;
}


/**
 * Helper function that returns the ID of the page that is used as the 'front'
 * page. If a static page has not been set as the 'front' page in the
 * 'Reading Settings' or if the latest posts are displayed in the front page,
 * then 0 is returned.
 */
function amt_get_front_page_id() {
    return intval(get_option('page_on_front', 0));
}


/**
 * Helper function that returns the ID of the page that is used as the 'posts'
 * page. If a static page has not been set as the 'posts' page in the
 * 'Reading Settings' or if the latest posts are displayed in the front page,
 * then 0 is returned.
 */
function amt_get_posts_page_id() {
    return intval(get_option('page_for_posts', 0));
}


//
//function amt_store_oembed_response( $return, $data, $url ) {
//    /**
//     * Filter the returned oEmbed HTML.
//     *
//     * Use this filter to add support for custom data types, or to filter the result.
//     *
//     * @since 2.9.0
//     *
//     * @param string $return The returned oEmbed HTML.
//     * @param object $data   A data object result from an oEmbed provider.
//     * @param string $url    The URL of the content to be embedded.
//     */
//return apply_filters( 'oembed_dataparse', $return, $data, $url );
//}
//add_filter('oembed_dataparse', 'amt_store_oembed_response', 9999, 3);
//
// SEE:
// * http://wordpress.stackexchange.com/questions/70752/featured-image-of-video-from-oembed
// * http://wordpress.stackexchange.com/questions/19500/oembed-thumbnails-and-wordpress?lq=1
// * http://wordpress.stackexchange.com/questions/114656/detecting-embed-urls-within-post-content
// * http://wordpress.stackexchange.com/a/74026
// * http://wordpress.stackexchange.com/a/180169
// * http://wordpress.stackexchange.com/questions/78140/video-playing-from-featured-image?lq=1
// * http://wordpress.stackexchange.com/questions/73996/how-to-replace-youtube-videos-with-a-click-to-play-thumbnail?lq=1
// GOOD FOR OWN IMPLEMENTATION:
// * http://wordpress.stackexchange.com/questions/78140/video-playing-from-featured-image?lq=1
// * http://wordpress.stackexchange.com/questions/25808/setting-a-posts-featured-image-from-an-embedded-youtube-video?rq=1
// * http://wordpress.stackexchange.com/questions/70752/featured-image-of-video-from-oembed
//

//
// Returns an array with URLs to players for some embedded media.
//
function amt_get_embedded_media( $post ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_embedded_media', $post);
    $embedded_media_urls = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $embedded_media_urls !== false ) {
        return $embedded_media_urls;
    }

    // Post content pre-processing

    // At this point we give devs the opportunity to inject raw URLs of
    // supported embeddable media, so that they can be picked up by
    // the algorithms below.
    // Array of URLs of supported embeddable media.
    $external_media_urls = apply_filters( 'amt_embedded_media_external', array(), $post );

    // Store post body
    $post_body = $post->post_content;
    // Attach the external media URLs to the post content.
    //$post_body .= sprintf( '\n%s\n', implode('\n', $external_media_urls) );
    $post_body .= PHP_EOL . implode(PHP_EOL, $external_media_urls) . PHP_EOL;

    // Format of the array
    // Embeds are grouped by type images/videos/sounds
    // Embedded media are added to any group as an associative array.
    $embedded_media_urls = array(
        'images' => array(),
        'videos' => array(),
        'sounds' => array()
    );

    // Find Videos
    //
    // Keys:
    // page - URL to a HTML page that contains the object.
    // player - URL to the player that can be used in an iframe.
    // thumbnail - URL to a preview image

    // Youtube
    // Supported:
    // - http://www.youtube.com/watch?v=VIDEO_ID
    //$pattern = '#youtube.com/watch\?v=([-|~_0-9A-Za-z]+)#';
    //$pattern = '#http:\/\/(?:www.)?youtube.com\/.*v=(\w*)#i';
    $pattern = '#https?:\/\/(?:www.)?youtube.com\/.*v=([a-zA-Z0-9_-]+)#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of YT video URLS
        // $matches[1] contains a list of YT video IDs
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $youtube_video_url ) {

            // First we verify that this is an embedded Youtube video and not
            // one that is just linked. We confirm this by checking if the
            // relevant oembed custom field has been created.

            // Get cached HTML data for embedded youtube videos.
            // Do it like WordPress.
            // See source code:
            // - class-wp-embed.php: line 177 [[ $cachekey = '_oembed_' . md5( $url . serialize( $attr ) ); ]]
            // - media.php: line 1332 [[ function wp_embed_defaults ]]
            // If no attributes have been used in the [embed] shortcode, $attr is an empty string.
            $attr = '';
            $attr = wp_parse_args( $attr, wp_embed_defaults() );
            $cachekey = '_oembed_' . md5( $youtube_video_url . serialize( $attr ) );
            $cache = get_post_meta( $post->ID, $cachekey, true );
            //var_dump($cache);
            if ( empty($cache) ) {
                continue;
            }

            // Get video ID from the video URL
            preg_match( '#.*v=([a-zA-Z0-9_-]+)#', $youtube_video_url, $video_url_info );
            //var_dump($video_url_info);
            $youtube_video_id = $video_url_info[1];

            // Get data from the cached HTML
            //var_dump($cache);
            preg_match( '#.* (?:width="([\d]+)") (?:height="([\d]+)") .*#', $cache, $media_info );
            //var_dump($media_info);

            $player_width = '640';
            if ( isset($media_info[1]) ) {
                $player_width = $media_info[1];
            }
            $player_height = '480';
            if ( isset($media_info[2]) ) {
                $player_height = $media_info[2];
            }

            $item = array(
                'type' => 'youtube',
                'page' => 'https://www.youtube.com/watch?v=' . $youtube_video_id,
                'player' => 'https://youtube.com/v/' . $youtube_video_id,
                //'player' => 'https://www.youtube.com/embed/' . $youtube_video_id,
                // Since we can construct the video thumbnail from the ID, we add it
                'thumbnail' => apply_filters( 'amt_oembed_youtube_image_preview', 'https://img.youtube.com/vi/' . $youtube_video_id . '/sddefault.jpg', $youtube_video_id ),
                //'thumbnail' => apply_filters( 'amt_oembed_youtube_image_preview', '', $youtube_video_id ),
                // TODO: check http://i1.ytimg.com/vi/FTnqYIkjSjQ/maxresdefault.jpg    MAXRES
                // http://img.youtube.com/vi/rr6H-MJCNw0/hqdefault.jpg  480x360 (same as 0.jpg)
                // http://img.youtube.com/vi/rr6H-MJCNw0/sddefault.jpg  640x480
                // See more here: http://stackoverflow.com/a/2068371
                'width' => $player_width,
                'height' => $player_height,
            );
            //array_unshift( $embedded_media_urls['videos'], $item );
            array_push( $embedded_media_urls['videos'], $item );
        }
    }

    // Vimeo
    // Supported:
    // - http://vimeo.com/VIDEO_ID
    // Check output of:  http://vimeo.com/api/v2/video/VIDEO_ID.xml
    // INVALID METHOD: 'thumbnail' => 'https://i.vimeocdn.com/video/' . $vimeo_video_id . '_640.jpg'
    //$pattern = '#vimeo.com/([-|~_0-9A-Za-z]+)#';
    $pattern = '#https?:\/\/(?:www.)?vimeo.com\/(\d+)#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of Vimeo video URLS
        // $matches[1] contains a list of Vimeo video IDs
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $vimeo_video_url ) {

            // First we verify that this is an embedded Vimeo video and not
            // one that is just linked. We confirm this by checking if the
            // relevant oembed custom field has been created.

            // Get cached HTML data for embedded Vimeo videos.
            // Do it like WordPress.
            // See source code:
            // - class-wp-embed.php: line 177 [[ $cachekey = '_oembed_' . md5( $url . serialize( $attr ) ); ]]
            // - media.php: line 1332 [[ function wp_embed_defaults ]]
            // If no attributes have been used in the [embed] shortcode, $attr is an empty string.
            $attr = '';
            $attr = wp_parse_args( $attr, wp_embed_defaults() );
            $cachekey = '_oembed_' . md5( $vimeo_video_url . serialize( $attr ) );
            $cache = get_post_meta( $post->ID, $cachekey, true );
            //var_dump($cache);
            if ( empty($cache) ) {
                continue;
            }

            // Get video ID from the URL
            preg_match( '#.*vimeo.com\/(\d+)#', $vimeo_video_url, $video_url_info );
            //var_dump($video_url_info);
            $vimeo_video_id = $video_url_info[1];

            // Get data from the cached HTML
            //var_dump($cache);
            preg_match( '#.* (?:width="([\d]+)") (?:height="([\d]+)") .*#', $cache, $media_info );
            //var_dump($media_info);

            $player_width = '640';
            if ( isset($media_info[1]) ) {
                $player_width = $media_info[1];
            }
            $player_height = '480';
            if ( isset($media_info[2]) ) {
                $player_height = $media_info[2];
            }

            $item = array(
                'type' => 'vimeo',
                'page' => 'https://vimeo.com/' . $vimeo_video_id,
                'player' => 'https://player.vimeo.com/video/' . $vimeo_video_id,
                'thumbnail' => apply_filters( 'amt_oembed_vimeo_image_preview', '', $vimeo_video_id ),
                'width' => $player_width,
                'height' => $player_height,
            );
            array_push( $embedded_media_urls['videos'], $item );
        }
    }

    // Vine
    // Supported:
    // - https://vine.co/v/VIDEO_ID
    // Also check output of:  https://vine.co/v/bwBYItOUKrw/card
    $pattern = '#https?:\/\/(?:www.)?vine.co\/v\/([a-zA-Z0-9_-]+)#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of Vimeo video URLS
        // $matches[1] contains a list of Vimeo video IDs
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $vine_video_url ) {

            // First we verify that this is an embedded Vine video and not
            // one that is just linked. We confirm this by checking if the
            // relevant oembed custom field has been created.

            // Get cached HTML data for embedded Vine videos.
            // Do it like WordPress.
            // See source code:
            // - class-wp-embed.php: line 177 [[ $cachekey = '_oembed_' . md5( $url . serialize( $attr ) ); ]]
            // - media.php: line 1332 [[ function wp_embed_defaults ]]
            // If no attributes have been used in the [embed] shortcode, $attr is an empty string.
            $attr = '';
            $attr = wp_parse_args( $attr, wp_embed_defaults() );
            $cachekey = '_oembed_' . md5( $vine_video_url . serialize( $attr ) );
            $cache = get_post_meta( $post->ID, $cachekey, true );
            //var_dump($cache);
            if ( empty($cache) ) {
                continue;
            }

            // Get id info from the URL
            preg_match( '#.*vine.co\/v\/([a-zA-Z0-9_-]+)#', $vine_video_url, $video_url_info );
            //var_dump($video_url_info);
            $vine_video_id = $video_url_info[1];

            // Get data from the cached HTML
            //var_dump($cache);
            preg_match( '#.* (?:width="([\d]+)") (?:height="([\d]+)") .*#', $cache, $media_info );
            //var_dump($media_info);

            $player_width = '600';
            if ( isset($media_info[1]) ) {
                $player_width = $media_info[1];
            }
            $player_height = '600';
            if ( isset($media_info[2]) ) {
                $player_height = $media_info[2];
            }

            $item = array(
                'type' => 'vine',
                'page' => 'https://vine.co/v/' . $vine_video_id,
                'player' => 'https://vine.co/v/' . $vine_video_id . '/embed/simple',
                'thumbnail' => apply_filters( 'amt_oembed_vine_image_preview', '', $vine_video_id ),
                'width' => $player_width,
                'height' => $player_height,
            );
            array_push( $embedded_media_urls['videos'], $item );
        }
    }

    // Find Sounds
    //
    // Keys:
    // page - URL to a HTML page that contains the object.
    // player - URL to the player that can be used in an iframe.
    // thumbnail - URL to a preview image -= ALWAYS EMPTY, but needed for the player twitter card.

    // Soundcloud
    // Supported:
    // - https://soundcloud.com/USER_ID/TRACK_ID
    // player:
    // https://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/117455833
    //
    // ALSO SEE: https://developers.soundcloud.com/docs/api/reference#tracks
    //
    $pattern = '#https?:\/\/(?:www.)?soundcloud.com\/[^/]+\/[a-zA-Z0-9_-]+#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of Soundcloud URLS
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $soundcloud_url ) {

            // First we verify that this is an embedded Soundcloud audio and not
            // one that is just linked. We confirm this by checking if the
            // relevant oembed custom field has been created.

            // Get cached HTML data for embedded Soundcloud audios.
            // Do it like WordPress.
            // See source code:
            // - class-wp-embed.php: line 177 [[ $cachekey = '_oembed_' . md5( $url . serialize( $attr ) ); ]]
            // - media.php: line 1332 [[ function wp_embed_defaults ]]
            // If no attributes have been used in the [embed] shortcode, $attr is an empty string.
            $attr = '';
            $attr = wp_parse_args( $attr, wp_embed_defaults() );
            $cachekey = '_oembed_' . md5( $soundcloud_url . serialize( $attr ) );
            $cache = get_post_meta( $post->ID, $cachekey, true );
            //var_dump($cache);
            if ( empty($cache) ) {
                continue;
            }

            // Get data from the cached HTML
            preg_match( '#.* (?:width="([\d]+)") (?:height="([\d]+)") .*#', $cache, $soundcloud_clip_info );
            //var_dump($soundcloud_clip_info);

            $player_width = '640';
            if ( isset($soundcloud_clip_info[1]) ) {
                $player_width = $soundcloud_clip_info[1];
            }
            $player_height = '320';
            if ( isset($soundcloud_clip_info[2]) ) {
                $player_height = $soundcloud_clip_info[2];
            }

            $item = array(
                'type' => 'soundcloud',
                'page' => $soundcloud_url,
                'player' => 'https://w.soundcloud.com/player/?url=' . $soundcloud_url,
                'thumbnail' => apply_filters( 'amt_oembed_soundcloud_image_preview', '', $soundcloud_url ),
                'width' => $player_width,
                'height' => $player_height,
            );
            array_push( $embedded_media_urls['sounds'], $item );
        }
    }

    // Find Images
    //
    // Keys:
    // page - URL to a HTML page that contains the object.
    // player - URL to the player that can be used in an iframe.
    // thumbnail - URL to thumbnail
    // image - URL to image
    // alt - alt text
    // width - image width
    // height - image height

    // Flickr
    //
    // Supported:
    // Embedded URLs MUST be of Format: http://www.flickr.com/photos/USER_ID/IMAGE_ID/
    //
    // Sizes:
    // t - Thumbnail (100x)
    // q - Square 150 (150x150)
    // s - Small 240 (140x)
    // n - Small 320 (320x)
    // m - Medium 500 (500x)
    // z - Medium 640 (640x)
    // c - Large 800 (800x)
    // b - Large 900 (900x)
    // l - Large 1024 (1024x)   DOES NOT WORK
    // h - High 1600 (1600x) DOES NOT WORK
    //
    $pattern = '#https?:\/\/(?:www.)?flickr.com\/photos\/[^\/]+\/[^\/]+\/#i';
    //$pattern = '#https?://(?:www.)?flickr.com/photos/[^/]+/[^/]+/#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of Flickr image page URLS
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $flick_page_url ) {

            // Get cached HTML data for embedded images.
            // Do it like WordPress.
            // See source code:
            // - class-wp-embed.php: line 177 [[ $cachekey = '_oembed_' . md5( $url . serialize( $attr ) ); ]]
            // - media.php: line 1332 [[ function wp_embed_defaults ]]
            // If no attributes have been used in the [embed] shortcode, $attr is an empty string.
            $attr = '';
            $attr = wp_parse_args( $attr, wp_embed_defaults() );
            $cachekey = '_oembed_' . md5( $flick_page_url . serialize( $attr ) );
            $cache = get_post_meta( $post->ID, $cachekey, true );
            //var_dump($cache);
            if ( empty($cache) ) {
                continue;
            }

            // Get image info from the cached HTML
            preg_match( '#<img src="([^"]+)" alt="([^"]+)" width="([\d]+)" height="([\d]+)" \/>#i', $cache, $img_info );
            //var_dump($img_info);
            if ( ! empty( $img_info ) ) {
                $item = array(
                    'type' => 'flickr',
                    'page' => $flick_page_url,
                    'player' => $flick_page_url . 'lightbox/',
                    'thumbnail' => str_replace( 'z.jpg', 'q.jpg', $img_info[1] ),   // size q   BEFORE CHANGING this check if the 150x150 is hardcoded into any metadata generator. It is in Twitter cards.
                    'image' => $img_info[1],    // size z
                    'alt' => $img_info[2],
                    'width' => $img_info[3],
                    'height' => $img_info[4]
                );
                array_push( $embedded_media_urls['images'], $item );
            }
        }
    }

    /**
    // Instagram
    //
    // Supported:
    // Embedded URLs MUST be of Format: https://instagram.com/p/IMAGE_ID/
    //
    $pattern = '#https?:\/\/(?:www.)?instagram.com\/p\/[^\/]+\/#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of Flickr image page URLS
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $instagram_page_url ) {

            // Get cached HTML data for embedded images.
            // Do it like WordPress.
            // See source code:
            // - class-wp-embed.php: line 177 [[ $cachekey = '_oembed_' . md5( $url . serialize( $attr ) ); ]]
            // - media.php: line 1332 [[ function wp_embed_defaults ]]
            // If no attributes have been used in the [embed] shortcode, $attr is an empty string.
            $attr = '';
            $attr = wp_parse_args( $attr, wp_embed_defaults() );
            $cachekey = '_oembed_' . md5( $instagram_page_url . serialize( $attr ) );
            $cache = get_post_meta( $post->ID, $cachekey, true );
            var_dump($cache);

            // Get image info from the cached HTML
            preg_match( '#target="_top">(.*)<\/a>#i', $cache, $img_info );
            //var_dump($img_info);
            if ( ! empty( $img_info ) ) {
                $item = array(
                    'page' => $instagram_page_url,
                    'player' => $instagram_page_url . 'lightbox/',
                    'thumbnail' => str_replace( 'z.jpg', 'q.jpg', $img_info[1] ),   // size q   BEFORE CHANGING this check if the 150x150 is hardcoded into any metadata generator. It is in Twitter cards.
                    'image' => $img_info[1],    // size z
                    'alt' => $img_info[1],
                    'width' => '640',
                    'height' => '640',
                );
                array_unshift( $embedded_media_urls['images'], $item );
            }
        }
    }
    */

    // Allow filtering of the embedded media array
    $embedded_media_urls = apply_filters( 'amt_embedded_media', $embedded_media_urls, $post->ID );

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $embedded_media_urls, $group='add-meta-tags' );

    //var_dump($embedded_media_urls);
    return $embedded_media_urls;
}



/**
 * Dublin Core helper functions
 */
function amt_get_dublin_core_author_notation($post) {
    $last_name = get_the_author_meta('last_name', $post->post_author);
    $first_name = get_the_author_meta('first_name', $post->post_author);
    if ( empty($last_name) && empty($first_name) ) {
        return get_the_author_meta('display_name', $post->post_author);
    }
    return $last_name . ', ' . $first_name;
}


/**
 * Taken from WordPress (http://core.trac.wordpress.org/browser/tags/3.6.1/wp-includes/general-template.php#L1397)
 * Modified to accept a mysqltime object.
 */
function amt_iso8601_date( $mysqldate ) {
    return mysql2date('c', $mysqldate);
}


// Helper function that determines whether code that has to do with the
// Metadata Review mode should be enabled.
function amt_check_run_metadata_review_code($options) {
    if ( $options["review_mode"] == "1" && current_user_can('create_users') ) {
        return true;
    }
    return false;
}


/**
 * Custom meta tag highlighter.
 *
 * Expects string.
 */
function amt_metatag_highlighter( $metatags ) {

    // Convert special chars, but leave quotes.
    // Required for pre box.
    $metatags = htmlspecialchars($metatags, ENT_NOQUOTES);

    if ( ! apply_filters('amt_metadata_review_mode_enable_highlighter', true) ) {
        return $metatags;
    }

    // Find all property/value pairs
    preg_match_all('#([^\s]+="[^"]+?)"#i', $metatags, $matches);
    if ( ! $matches ) {
        return $metatags;
    }

    // Highlight properties and values.
    //var_dump($matches[0]);
    foreach ($matches[0] as $match) {
        $highlighted = preg_replace('#^([^=]+)="(.+)"$#i', '<span class="amt-ht-attribute">$1</span>="<span class="amt-ht-value">$2</span>"', $match);
        //var_dump($highlighted);
        $metatags = str_replace($match, $highlighted, $metatags);
    }

    // Highlight 'itemscope'
    $metatags = str_replace('itemscope', '<span class="amt-ht-itemscope">itemscope</span>', $metatags);

    // Highlight Schema.org object
    //$metatags = preg_replace('#: ([a-zA-Z0-9]+) --&gt;#', ': <span class="amt-ht-important">$1</span> --&gt;', $metatags);

    // Highlight HTML comments
    $metatags = str_replace('&lt;!--', '<span class="amt-ht-comment">&lt;!--', $metatags);
    $metatags = str_replace('--&gt;', '--&gt;</span>', $metatags);

    // Do some conversions
    $metatags =  wp_pre_kses_less_than( $metatags );
    // Done by wp_pre_kses_less_than()
    //$metatags = str_replace('<meta', '&lt;meta', $metatags);
    //$metatags = str_replace('/>', '/&gt;', $metatags);
//$metatags = str_replace('<br />', '___', $metatags);
//$metatags = str_replace('___', '<br />', $metatags);

    return $metatags;
}


// Accepts a URL and converts the protocol to https. Returns the processed URL.
function amt_make_https( $url ) {
    return preg_replace( '#^http://#' , 'https://', $url );
}


function amt_return_true() {
    return true;
}

function amt_return_false() {
    return false;
}


// Returns site locale
function amt_get_language_site($options) {
    $language = get_bloginfo('language');
    // If set, the 'global_locale' setting overrides WordPress.
    if ( ! empty( $options["global_locale"] ) ) {
        $language = $options["global_locale"];
    }
    // Allow filtering of the site language
    $language = apply_filters( 'amt_language_site', $language );
    return $language;
}


// Returns content locale
// NOTE: SHOULD NOT BE USED ON ARCHIVES
function amt_get_language_content($options, $post) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_language_content', $post);
    $language = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $language !== false ) {
        return $language;
    }

    $language = get_bloginfo('language');
    // If set, the 'global_locale' setting overrides WordPress.
    if ( ! empty( $options["global_locale"] ) ) {
        $language = $options["global_locale"];
    }
    // If set, the locale setting from the Metabox overrides all other local settings.
    $metabox_locale = amt_get_post_meta_content_locale($post->ID);
    if ( ! empty( $metabox_locale ) ) {
        $language = $metabox_locale;
    }
    // Allow filtering of the content language
    $language = apply_filters( 'amt_language_content', $language, $post );

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $language, $group='add-meta-tags' );

    return $language;
}


// Returns the hreflang attribute's value
function amt_get_the_hreflang($locale, $options) {
    $output = '';
    // Convert underscore to dash
    $locale = str_replace('_', '-', $locale);
    // Return locale if no further processing is needed
    if ( $options['hreflang_strip_region'] == '0' ) {
        $output = $locale;
    } else {
        // Strip region code
        $locale_parts = explode('-', $locale);
        if ( count($locale_parts) == 1 ) {
            $output = $locale;
        } elseif ( count($locale_parts) > 2 ) {
            $output = $locale_parts[0] . '-' . $locale_parts[1];
        } elseif ( count($locale_parts) == 2 ) {
            // In this case we need to understand whether locale is
            // language_TERRITORY or language_Script_TERRITORY
            // If the last part is a two letter string, we assume it's the region and strip it
            if ( strlen($locale_parts[1]) == 2 ) {
                // We assume this is a region code and strip it
                $output = $locale_parts[0];
            } else {
                // We assume that the locale consist only of language_Script
                $output = $locale_parts[0] . '-' . $locale_parts[1];
            }
        }
    }
    // Allow filtering
    $output = apply_filters( 'amt_get_the_hreflang', $output );
    return $output;
}


//
// Returns array with attributes
// or NULL
function amt_get_image_attributes_array( $notation ) {
    // Special notation about the default image:
    //      URL[,WIDTHxHEIGHT]

    if ( empty( $notation ) ) {
        return;
    }

    $data = array(
        'id'    => null,   // This function always returns a null attachment id.
        // Filled from special notation
        'url'   => null,
        'width' => null,
        'height' => null,
        'type'  => null,
    );

    $parts = explode(',', $notation);
    $parts_count = count($parts);

    // URL
    if ( $parts_count == 1 ) {

        // Retrieve URL
        if ( preg_match('#^(https?://.+)$#', $parts[0], $matches) ) {
            //var_dump($matches);
            $data['url'] = $matches[1];

            // Also try to determine the image type
            $extension = substr( $data['url'], strrpos($data['url'], '.') + 1);
            if ( $extension == 'jpg' ) {
                $extension = 'jpeg';
            }
            if ( in_array( $extension, array('jpeg', 'png', 'gif', 'bmp') ) ) {
                $data['type'] = 'image/' . $extension;
            }
        }

    // URL,WIDTHxHEIGHT
    } elseif ( $parts_count == 2 ) {

        // Retrieve URL
        if ( preg_match('#^(https?://.+)$#', $parts[0], $matches) ) {
            //var_dump($matches);
            $data['url'] = $matches[1];

            // Also try to determine the image type
            $extension = substr( $data['url'], strrpos($data['url'], '.') + 1);
            if ( $extension == 'jpg' ) {
                $extension = 'jpeg';
            }
            if ( in_array( $extension, array('jpeg', 'png', 'gif', 'bmp') ) ) {
                $data['type'] = 'image/' . $extension;
            }

            // Retrieve width and height
            if ( preg_match('#^([\d]+)x([\d]+)$#', $parts[1], $matches) ) {
                //var_dump($matches);
                $data['width'] = $matches[1];
                $data['height'] = $matches[2];
            }
        }

    }

    return $data;
}


// Function that returns an array with data about the default image.
// Returns false if no image could be found.
function amt_get_default_image_data() {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_default_image_data');
    $data = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $data !== false ) {
        return $data;
    }

    // The default_image_url option accepts:
    // 1. An attachment ID
    // 2. Special notation about the default image:
    //      URL[,WIDTHxHEIGHT]

    $data = array(
        'id'    => null,   // post ID of attachment
        // The ID should be enough information to retrieve all attachment information
        // Alternatively, if the ID is not set, at least the 'url' should be set.
        'url'   => null,
        'width' => null,
        'height' => null,
        'type'  => null,
    );

    $options = amt_get_options();

    $value = $options["default_image_url"];

    if ( ! empty($value) ) {

        // First check if we have an ID
        if ( is_numeric($value) ) {
            $data['id'] = absint($value);

        // Alternatively, check for special notation
        } else {
            $data = amt_get_image_attributes_array( $value );

        }

    }

    // Allow filtering
    $data = apply_filters('amt_default_image_data', $data);

    // Check if we have an image
    if ( is_null($data['id']) && is_null($data['url']) ) {
        $data = false;
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $data, $group='add-meta-tags' );

    return $data;
}


// Function that returns an array with data about the image.
// Returns false if no image could be found.
function amt_get_image_data( $value ) {

    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_image_data_' . md5($value) );
    $data = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $data !== false ) {
        return $data;
    }

    // The special notation option accepts:
    // 1. An attachment ID
    // 2. Special notation about the default image:
    //      URL[,WIDTHxHEIGHT]

    $data = array(
        'id'    => null,   // post ID of attachment
        // The ID should be enough information to retrieve all attachment information
        // Alternatively, if the ID is not set, at least the 'url' should be set.
        'url'   => null,
        'width' => null,
        'height' => null,
        'type'  => null,
    );

    if ( ! empty($value) ) {

        // First check if we have an ID
        if ( is_numeric($value) ) {
            $data['id'] = absint($value);

        // Alternatively, check for special notation
        } else {
            $data = amt_get_image_attributes_array( $value );

        }

    }

    // Allow filtering
    //$data = apply_filters('amt_get_image_data', $data);

    // Check if we have an image
    if ( is_null($data['id']) && is_null($data['url']) ) {
        $data = false;
    }

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $data, $group='add-meta-tags' );

    return $data;
}


// Returns the default Twitter Card type
function amt_get_default_twitter_card_type($options) {
    $default = 'summary';
    if ( $options["tc_enforce_summary_large_image"] == "1" ) {
        $default = 'summary_large_image';
    }
    // Allow filtering of the default card type
    $default = apply_filters( 'amt_twitter_cards_default_card_type', $default );
    return $default;
}


// Function that returns the content of the Site Description setting of the
// general Add-Meta-Tags settings.
// This function allows filtering of the description, so that it can be set
// programmatically, for instance in multilingual web sites.
function amt_get_site_description($options) {
    $output = '';
    if ( is_array($options) && array_key_exists('site_description', $options) ) {
        $output = $options['site_description'];
    }
    // Allow filtering
    $output = apply_filters( 'amt_settings_site_description', $output );
    return $output;
}


// Function that returns the content of the Site Keywords setting of the
// general Add-Meta-Tags settings.
// This function allows filtering of the keywords, so that it can be set
// programmatically, for instance in multilingual web sites.
function amt_get_site_keywords($options) {
    $output = '';
    if ( is_array($options) && array_key_exists('site_keywords', $options) ) {
        $output = $options['site_keywords'];
    }
    // Allow filtering
    $output = apply_filters( 'amt_settings_site_keywords', $output );
    return $output;
}


// Function that returns the content of the Global Keywords setting of the
// general Add-Meta-Tags settings.
// This function allows filtering of the 'global keywords', so that it can be set
// programmatically, for instance in multilingual web sites.
function amt_get_site_global_keywords($options) {
    $output = '';
    if ( is_array($options) && array_key_exists('global_keywords', $options) ) {
        $output = $options['global_keywords'];
    }
    // Allow filtering
    $output = apply_filters( 'amt_settings_global_keywords', $output );
    return $output;
}


// Function that returns the content of the Copyright URL setting of the
// general Add-Meta-Tags settings.
// This function allows filtering of the 'copyright URL', so that it can be set
// programmatically, for instance in multilingual web sites.
function amt_get_site_copyright_url($options) {
    $output = '';
    if ( is_array($options) && array_key_exists('copyright_url', $options) ) {
        $output = $options['copyright_url'];
    }
    // Allow filtering
    $output = apply_filters( 'amt_settings_copyright_url', $output );
    return $output;
}


// Function that returns an itemref attribute, ready to be placed in the HTML element.
function amt_get_schemaorg_itemref( $object_type ) {
    // Construct filter name, eg 'amt_schemaorg_itemref_organization'
    $filter_name = 'amt_schemaorg_itemref_' . $object_type;
    // Construct itemref attribute. Should contain a comma delimited list of IDs.
    $itemref = apply_filters( $filter_name, '' );
    if ( ! empty($itemref) ) {
        $itemref_attrib = ' itemref="' . $itemref . '"';
    } else {
        $itemref_attrib = '';
    }
    return $itemref_attrib;
}


// Returns a string suitable for a Schema.org ID
// Absolute or relative URLs are fine
function amt_get_schemaorg_entity_id( $object_type ) {
    return sprintf('#amt-%s', $object_type);
}


// Returns a string suitable for as a full 'itemid attribute together with itemscope.
// Absolute or relative URLs are fine
function amt_get_schemaorg_entity_id_as_itemid( $object_type ) {
    return sprintf(' itemid="%s"', amt_get_schemaorg_entity_id( $object_type ));
}


// Returns the URL of the page of the local author profile
function amt_get_local_author_profile_url( $author_id, $options ) {
    $url = '';
    if ( empty($author_id) ) {
        return $url;
    }
    if ( $options['author_profile_source'] == 'default' ) {
        $url = get_author_posts_url( $author_id );
    } elseif ( $options['author_profile_source'] == 'frontpage' ) {
        $url = get_bloginfo( 'url' );
    } elseif ( $options['author_profile_source'] == 'buddypress' ) {
        //return get_bloginfo( 'url' );
        if ( function_exists('bp_core_get_user_domain') ) {
            //return bp_core_get_user_domain($author_id);
            $url = trailingslashit( bp_core_get_user_domain($author_id) . amt_bp_get_profile_slug() );
        }
    } elseif ( $options['author_profile_source'] == 'url' ) {
        $custom_url = get_the_author_meta('url', $author_id);
        if ( ! empty($custom_url) ) {
            $url = $custom_url;
        }
    }
    $url = apply_filters( 'amt_get_local_author_profile_url', $url );
    if ( empty($url) ) {
        // Return the default if all else fails.
        return get_author_posts_url( $author_id );
    } else { 
        return $url;
    }
}


// Determines if custom content has been requested.
function amt_is_custom($post, $options) {
    return apply_filters( 'amt_is_custom', false, $post, $options );
}


// Determines if a Product page has been requested.
function amt_is_product() {
    return apply_filters( 'amt_is_product', false );
}


// Determines if a Product Group page has been requested.
function amt_is_product_group() {
    // Normally a product group should fall into the is_tax() validation.
    // Product groups other than WordPress custom taxonomies are not suported.
    // However, we use this function in order to distinguish a non product
    // related taxonomy from a product related one (aka product group).
    // This is useful in case we need to set the metadata object type to a
    // group type, like it happens with Opengraph og:type=product.group.
    return apply_filters( 'amt_is_product_group', false );
}


// Media Limits

function amt_metadata_get_default_media_limit($options) {
    $limit = 16;
    if ( is_array($options) && array_key_exists('force_media_limit', $options) && $options['force_media_limit'] == '1' ) {
        $limit = 1;
    }
    return $limit;
}

function amt_metadata_get_image_limit($options) {
    $limit = amt_metadata_get_default_media_limit($options);
    $limit = apply_filters( 'amt_metadata_image_limit', $limit );
    return absint($limit);
}

function amt_metadata_get_video_limit($options) {
    $limit = amt_metadata_get_default_media_limit($options);
    $limit = apply_filters( 'amt_metadata_video_limit', $limit );
    return absint($limit);
}

function amt_metadata_get_audio_limit($options) {
    $limit = amt_metadata_get_default_media_limit($options);
    $limit = apply_filters( 'amt_metadata_audio_limit', $limit );
    return absint($limit);
}


// Reviews


// Returns an array containing review related data, only when the provided data is valid.
function amt_get_review_data( $post ) {
    // Get review information from custom field
    $data = amt_get_post_meta_express_review( $post->ID );
    //
    // REVIEW_AMPERSAND_NOTE
    //
    // Note about conversion of ampersand:
    // Data is returned with & converted to &amp; by amt_get_post_meta_express_review().
    // This character mainly exists in sameAs URLs  (TODO: Find better replacement using preg_replace to spacifically target sameAs attributes)
    // The problem is that ';' is interpretted as a comment in the INI specification,
    // so parse_ini_string() strips part of the URL, which is wrong.
    // Here we convert &amp; to & and leave it as is in the generated review data.
    // To convert it back to &amp; after parse_ini_string() add something like:
    //     $value = str_replace('&', '&amp;', $value);
    // inside the foreach loop below.
    //
    //var_dump($data);
    $data = str_replace('&amp;', '&', $data);
    //var_dump($data);
    if ( empty($data) ) {
        return;
    }
    // Parse as INI
    $review_data_raw = parse_ini_string( $data, true, INI_SCANNER_RAW );
    //var_dump($review_data_raw);
    // Check for mandatory properties
    if ( ! array_key_exists('ratingValue', $review_data_raw) ) {
        return;
    } elseif ( ! array_key_exists('object', $review_data_raw) ) {
        return;
    } elseif ( ! array_key_exists('name', $review_data_raw) ) {
        return;
    } elseif ( ! array_key_exists('sameAs', $review_data_raw) ) {
        return;
    }
    // Construct final review data array.
    // Extra properties are collected into ['extraprop'] sub array.
    $review_data = array();
    $review_data['extra'] = array();
    $mandatory_arr = array( 'ratingValue', 'object', 'name', 'sameAs' );
    // Add extra properties
    foreach ( $review_data_raw as $key => $value ) {
        // Reverse the ampersand replacement. (see note above)
        $key = str_replace('&', '&amp;', $key);
        $value = str_replace('&', '&amp;', $value);
        if ( in_array( $key, $mandatory_arr ) ) {
            $review_data[$key] = $value;
        } else {
            $review_data['extra'][$key] = $value;
        }
    }
    //var_dump($review_data);

    return $review_data;
}


// Return the information text that should be attached to the post content.
function amt_get_review_info_box( $review_data ) {
    // Variables: #ratingValue#, #bestrating#, #object#, #name#, #sameAs#, #extra#
    // #extra# contains meta elements containing the extra properties of the reviewed item.
    $template = '
<div id="review-info" class="review-info">
    <p>This is a review of
    <span itemprop="itemReviewed" itemscope itemtype="http://schema.org/#object#">
        <a title="#object#: #name#" href="#sameAs#" itemprop="sameAs"><span itemprop="name">#name#</span></a>
#extra#
    </span>, which has been rated with 
    <span class="rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
        <span itemprop="ratingValue">#ratingValue#</span>/<span itemprop="bestRating">#bestrating#</span>
    </span> stars!</p>
</div>
';
    // Allow filtering of the template
    $template = apply_filters( 'amt_schemaorg_review_info_template', $template );
    // Set variables
    $bestrating = apply_filters( 'amt_schemaorg_review_bestrating', '5' );
    // Replace placeholders
    $output = $template;
    $output = str_replace('#ratingValue#', esc_attr($review_data['ratingValue']), $output);
    $output = str_replace('#bestrating#', esc_attr($bestrating), $output);
    $output = str_replace('#object#', esc_attr($review_data['object']), $output);
    $output = str_replace('#name#', esc_attr($review_data['name']), $output);
    $output = str_replace('#sameAs#', esc_url_raw($review_data['sameAs']), $output);
    // Extra properties
    $extra_arr = array();
    foreach ( $review_data['extra'] as $key => $value ) {
        if ( is_array($value) ) {
            // Add sub entity
            // If it is an array, the 'object' property is mandatory
            if ( ! array_key_exists( 'object', $value ) ) {
                continue;
            }
            $extra_arr[] = '<span itemprop="' . esc_attr($key) . '" itemscope itemtype="http://schema.org/' . esc_attr($value['object']) . '">';
            foreach ( $value as $subkey => $subvalue ) {
                if ( $subkey != 'object' ) {
                    if ( in_array( $subkey, array('url', 'sameAs') ) ) {
                        $extra_arr[] = '<meta itemprop="' . esc_attr($subkey) . '" content="' . esc_url_raw($subvalue) . '" />';
                    } else {
                        $extra_arr[] = '<meta itemprop="' . esc_attr($subkey) . '" content="' . esc_attr($subvalue) . '" />';
                    }
                }
            }
            $extra_arr[] = '</span>';
        } else {
            // Add simple meta element
            $extra_arr[] = '<meta itemprop="' . esc_attr($key) . '" content="' . esc_attr($value) . '" />';
        }
    }
    $output = str_replace('#extra#', implode(PHP_EOL, $extra_arr), $output);
    // Allow filtering of the output
    $output = apply_filters( 'amt_schemaorg_review_info_output', $output );
    return $output;
}

// Sample review sets
function amt_get_sample_review_sets() {

    // Default review sets
    $review_sets = array(
        'Book' => array(
            '; Review rating (required)',
            'ratingValue = 4.2',
            '; Mandatory reviewed item properties (required)',
            'object = Book',
            'name = On the Origin of Species',
            'sameAs = http://en.wikipedia.org/wiki/On_the_Origin_of_Species',
            '; Extra reviewed item properties (optional)',
            'isbn = 123456',
            '[author]',
            'object = Person',
            'name = Charles Darwin',
            'sameAs = https://en.wikipedia.org/wiki/Charles_Darwin',
        ),
        'Movie' => array(
            '; Review rating (required)',
            'ratingValue = 4.2',
            '; Mandatory reviewed item properties (required)',
            'object = Movie',
            'name = Reservoir Dogs',
            'sameAs = http://www.imdb.com/title/tt0105236/',
            '; Extra reviewed item properties (optional)',
            ';datePublished = 1992-01-21T00:00',
            '[director]',
            'object = Person',
            'name = Quentin Tarantino',
            'sameAs = https://en.wikipedia.org/wiki/Quentin_Tarantino',
            '[actor]',
            'object = Person',
            'name = Harvey Keitel',
            'sameAs = https://en.wikipedia.org/wiki/Harvey_Keitel',
        ),
        'Article' => array(
            '; Review rating (required)',
            'ratingValue = 4.2',
            '; Mandatory reviewed item properties (required)',
            'object = Article',
            'name = Structured Data',
            'sameAs = https://developers.google.com/structured-data/',
            '; Extra reviewed item properties (optional)',
            'datePublished = 2015-07-21T00:00',
            'headline = Promote Your Content with Structured Data Markup',
            'image = https://developers.google.com/structured-data/images/reviews-mobile.png',
        ),
    );

    // Check if we have any meta tag sets.
    $review_sets = apply_filters( 'amt_sample_review_sets', $review_sets );
    if ( empty($review_sets) ) {
        return;
    }

    $html = PHP_EOL . '<select id="sample_review_sets_selector" name="sample_review_sets_selector">' . PHP_EOL;
    $html .= PHP_EOL . '<option value="0">'.__('Select a sample review', 'add-meta-tags').'</option>' . PHP_EOL;
    foreach ( array_keys($review_sets) as $key ) {
        $key_slug = str_replace(' ', '_', strtolower($key));
        $html .= '<option value="' . esc_attr($key_slug) . '">' . esc_attr($key) . '</option>' . PHP_EOL;
    }
    $html .= PHP_EOL . '</select>' . PHP_EOL;

    $html .='
<script>
jQuery(document).ready(function(){
    jQuery("#sample_review_sets_selector").change(function(){
        var selection = jQuery(this).val();
        if (selection == "0") {
            var output = \'\';
    ';

    foreach ( $review_sets as $key => $value ) {
        $key_slug = str_replace(' ', '_', strtolower($key));
        $html .= '
        } else if (selection == "' . esc_attr($key_slug) . '") {
            var output = \''.implode('\'+"\n"+\'', $value).'\';
        ';
    }

    $html .='
        }
        jQuery("#amt_custom_express_review").val(output);
    });
});
</script>
    ';

    return '<br />' . __('Use sample review data:', 'add-meta-tags') . $html . '<br />';
}




// Breadcrumbs

// Generates a semantic (Schema.org) breadcrumb trail.
// Accepts array
function amt_get_breadcrumbs( $user_options ) {
    // Get plugin options
    $plugin_options = get_option("add_meta_tags_opts");
    // Get post object
    $post = amt_get_queried_object();

    // Default Options
    $default_options = array(
        // ID of list element.
        'list_id' => 'breadcrumbs',
        // Show breadcrumb item for the home page.
        'show_home' => true,
        // Text for the home link (requires show_home=true).
        'home_link_text' => 'Home',
        // Show breadcrumb item for the last page.
        'show_last' => true,
        // Show last breadcrumb as link (requires show_last=true).
        'show_last_as_link' => true,
        // Separator. Set to empty string for no separator.
        'separator' => '>'
    );
    // Final options.
    $options = array_merge( $default_options, $user_options );

    $bc_arr = array();
    $bc_arr[] = '<!-- BEGIN Metadata added by Add-Meta-Tags WordPress plugin -->';
    $bc_arr[] = '<!-- Scope BEGIN: BreadcrumbList -->';
    $bc_arr[] = '<ul id="' . $options['list_id'] . '" itemprop="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">';
    // Item counter
    $counter = 1;
    // Home link
    if ( $options['show_home'] ) {
        $bc_arr['bc-home'] = '<li class="list-item list-item-' . $counter . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="breadcrumb breadcrumb-' . $counter . '" itemprop="item" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '"><span itemprop="name">' . $options['home_link_text'] . '</span></a></li>';
        //$bc_arr['bc-home-pos'] = '<meta itemprop="position" content="' . $counter . '" />';
        $counter++;
    }
    // Generate breadcrumbs for parent pages, if any.
    if ( $post->post_parent ) {
        // Get the parent pages
        $ancestors = get_post_ancestors( $post->ID );
        // Set ancestors in reverse order
        $ancestors = array_reverse( $ancestors );
        // Generate items
        foreach ( $ancestors as $ancestor ) {
            // Add separator
            if ( ! empty($options['separator']) ) {
                $bc_arr['bc-sep-' . $counter] = '<span class="separator separator-' . $counter . '"> ' . esc_attr($options['separator']) . ' </span>';
            }
            $bc_arr['bc-item-' . $counter] = '<li class="list-item list-item-' . $counter . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="breadcrumb breadcrumb-' . $counter . '" itemprop="item" title="' . esc_attr( strip_tags( get_the_title($ancestor) ) ) . '" href="' . esc_url_raw( get_permalink($ancestor) ) . '"><span itemprop="name">' .esc_attr( strip_tags( get_the_title($ancestor) ) ) . '</span></a></li>';
            //$bc_arr['bc-item-' . $counter . '-pos'] = '<meta itemprop="position" content="' . $counter . '" />';
            $counter++;
        }
    }
    // Last link
    if ( $options['show_last'] ) {
        // Add separator
        if ( ! empty($options['separator']) ) {
            $bc_arr['bc-sep-' . $counter] = '<span class="separator separator-' . $counter . ' separator-current"> ' . esc_attr($options['separator']) . ' </span>';
        }
        if ( $options['show_last_as_link'] ) {
            $bc_arr['bc-item-' . $counter] = '<li class="list-item list-item-' . $counter . ' list-item-current" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="breadcrumb breadcrumb-' . $counter . ' breadcrumb-current" itemprop="item" title="' . esc_attr( strip_tags( get_the_title($post) ) ) . '" href="' . esc_url_raw( get_permalink($post) ) . '"><span itemprop="name">' .esc_attr( strip_tags( get_the_title($post) ) ) . '</span></a></li>';
        } else {
            $bc_arr['bc-item-' . $counter] = '<li class="list-item list-item-' . $counter . ' list-item-current" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="item"><span itemprop="name">' .esc_attr( strip_tags( get_the_title($post) ) ) . '</span></span></li>';
        }
        //$bc_arr['bc-item-' . $counter . '-pos'] = '<meta itemprop="position" content="' . $counter . '" />';
        $counter++;
    }

    $bc_arr[] = '<!-- END Metadata added by Add-Meta-Tags WordPress plugin -->';

    // Allow filtering of the generated
    $bc_arr = apply_filters( 'amt_breadcrumbs', $bc_arr );

    return PHP_EOL . implode(PHP_EOL, $bc_arr) . PHP_EOL . PHP_EOL;
}


//
// Full meta tag boxes helper functions
//

// Meta Tag Sets
function amt_get_full_meta_tag_sets( $default ) {

    // Default meta tag sets
    $default_meta_tag_sets = array();

    $default_meta_tag_sets['Robots - Generic'] = array(
        '<meta name="robots" content="all" />',
    );
    $default_meta_tag_sets['Robots - NoIndex, NoFollow'] = array(
        '<meta name="robots" content="noindex,nofollow" />',
    );
    $default_meta_tag_sets['Robots - NoIndex, Follow'] = array(
        '<meta name="robots" content="noindex,follow" />',
    );
    $default_meta_tag_sets['Robots - No extra services'] = array(
        '<meta name="robots" content="noodp,noarchive,notranslate,noimageindex" />',
    );
    $default_meta_tag_sets['Hreflang - Link to alternate translation'] = array(
        '<link rel="alternate" hreflang="ENTER_LOCALE_HERE" href="ENTER_PAGE_URL_HERE" />',
    );
    $default_meta_tag_sets['Canonical - Custom canonical link'] = array(
        '<link rel="canonical" href="ENTER_PAGE_URL_HERE" />',
    );

    // Check if we have any meta tag sets.
    $meta_tag_sets = apply_filters( 'amt_full_meta_tag_sets', $default_meta_tag_sets );
    if ( empty($meta_tag_sets) ) {
        return;
    }

    $html = PHP_EOL . '<select id="full_meta_tag_sets_selector" name="full_meta_tag_sets_selector">' . PHP_EOL;
    $html .= PHP_EOL . '<option value="0">'.__('Select a meta tag group', 'add-meta-tags').'</option>' . PHP_EOL;
    foreach ( array_keys($meta_tag_sets) as $key ) {
        $key_slug = str_replace(' ', '_', strtolower($key));
        $html .= '<option value="' . esc_attr($key_slug) . '">' . esc_attr($key) . '</option>' . PHP_EOL;
    }
    $html .= PHP_EOL . '</select>' . PHP_EOL;

    $html .= PHP_EOL . '<input class="button" id="full_meta_tag_sets_reset" name="full_meta_tag_sets_reset" type="submit" value="'.__('Reset', 'add-meta-tags').'" />' . PHP_EOL;

    $html .= PHP_EOL . ' &mdash; (<a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Plugin_Functionality_Customization#Create-Pre-Defined-Full-Meta-Tag-Sets">' . __('Customize these sets', 'add-meta-tags') . '</a>)' . PHP_EOL;

    $reset_msg = __('Undo your changes in the full meta tags box?', 'add-meta-tags');

    $html .='
<script>
jQuery(document).ready(function(){

    // On selector change
    jQuery("#full_meta_tag_sets_selector").change(function(){
        // Store current full meta tags box contents
        var cur_contents = jQuery("#amt_custom_full_metatags").val();
        // Get selector value
        var selection = jQuery(this).val();
        if (selection == "0") {
            var output = \'\';
    ';

    // Iterate through the meta tag sets and set the output value accordingly.
    foreach ( $meta_tag_sets as $key => $value ) {
        $key_slug = str_replace(' ', '_', strtolower($key));
        $additional_metatags = implode("\n", $value);
        $html .= '
        } else if (selection == "' . esc_attr($key_slug) . '") {
            var output = ' . json_encode( html_entity_decode( $additional_metatags ) ) . ';
        ';
    }

    $html .='
        }
        // Replace text area contents.
        if ( cur_contents == "" ) {
            // If the full meta tags box is currently empty
            jQuery("#amt_custom_full_metatags").val(output);
        } else {
            // If the full meta tags box already contains data
            jQuery("#amt_custom_full_metatags").val(cur_contents.trim() + "\n" + output);
        }
    });

    // On Reset button click
    jQuery("#full_meta_tag_sets_reset").click(function() {
        // Store current full meta tags box contents
        var cur_contents = jQuery("#amt_custom_full_metatags").val();
        if ( cur_contents != "" ) {
            // alert( "Handler for .click() called." );
            var rchoice = confirm(' . json_encode($reset_msg) . ');
            if (rchoice == true) {
                //jQuery("#amt_custom_full_metatags").val(\'\');
                jQuery("#amt_custom_full_metatags").val(' . json_encode( html_entity_decode( $default ) ) . ');
                jQuery("#full_meta_tag_sets_selector").val("0");
            }
        }
        return false;
    });

});
</script>
    ';

// Testing
// jQuery("#amt_custom_full_metatags").html(' . json_encode($default) . ').text();
// jQuery("#amt_custom_full_metatags").val("' . html_entity_decode($default, ENT_NOQUOTES) . '");
// jQuery("#amt_custom_full_metatags").html("' . html_entity_decode($default, ENT_NOQUOTES) . '").text();

    return '<br />' . __('Append a meta tag set:', 'add-meta-tags') . $html . '<br />';
}


//
// Metadata Caching
//

// AmtCache
//
// AmtCache is an array of commonly used pieces of metadata, like description,
// keywords, etc that is passed to each metadata generator.
// The AmtCache is only used for the metadata of content pages. It is not used
// for the metadata of the frontpage or archive pages.
//function amt_get_amt_cache($post, $options) {
//}

// Retrieve metadata from transient cache
// $where should be one of: head, footer, content
function amt_get_transient_cache($post, $options, $where='') {
    if ( ! in_array($where, array('head', 'footer', 'content')) ) {
        return;
    }
    if ( ! is_singular() ) {
        // TODO: if ( is_search() || is_404() || is_date() || is_paged()
        // We only cache content metadata at this time
        return;
    }
    if ( $post->ID <= 0 ) {
        return;
    }
    // Get transient name
    $transient_name = amt_get_transient_name($post->ID, $where);
    // Return the transient data
    return get_transient($transient_name);
}

// Cache metadata in transient cache
function amt_set_transient_cache($post, $options, $metadata_arr, $where='') {
    if ( ! in_array($where, array('head', 'footer', 'content')) ) {
        return;
    }
    if ( ! is_singular() ) {
        // TODO: if ( is_search() || is_404() || is_date() || is_paged()
        // We only cache content metadata at this time
        return;
    }
    if ( $post->ID <= 0 ) {
        return;
    }
    // Cache metadata only for published content
    if ( get_post_status($post->ID) != 'publish' ) {
        return;
    }
    // Transient expiration
    $transient_expiration = absint($options['transient_cache_expiration']);
    if ( $transient_expiration == "0" || ! is_numeric($transient_expiration) ) {
        return;
    }
    $transient_name = amt_get_transient_name($post->ID, $where);
    // Store the transient data
    set_transient( $transient_name, $metadata_arr, intval($transient_expiration) );
}

// Return a name of the transient. Format: amt_MD5SUM
function amt_get_transient_name($post_id, $where) {
    $to_hash = array();

    // Content: MD5( ID, $where )
    if ( absint($post_id) > 0 ) {
        // DO NOT USE is_singular() here because it may be called by 'save_post' actions etc
        $to_hash[] = absint($post_id);
        $to_hash[] = $where;

        // Check whether the ID is the same as the front/index static page's ID
        $front_page_id = intval(get_option('page_on_front', 0));
        if ( $post_id == $front_page_id ) {
            $to_hash[] = 'front_page';
        }
        // No need for this check since this is considered an archive and is not cached.
        // $index_page_id = intval(get_option('page_for_posts', 0));
        // if ( $post_id == $index_page_id ) {
        //     $to_hash[] = 'posts_page';
        // }
        
        // No query args here because we need to construct the name when editing/publishing the post/comment
        // See in add-meta-tags.php end.
    }
/*
    // TODO: for archives
    else {
        global $wp;
        $to_hash[] = serialize( $wp->query_vars );
        if ( is_front_page() ) {
            $to_hash[] = 'is_front_page';
        } elseif ( is_home() ) {
            $to_hash[] = 'is_home';
        }
        $to_hash[] = $where;
    }
*/
    $transient_name = sprintf('amt_%s', md5(serialize($to_hash)));
    //$transient_name = sprintf('amt_post_%d_%s', $post_id, $where);
    //var_dump($transient_name);
    return $transient_name;
}

// Delete transient cache
function amt_delete_transient_cache_for_post($post_id) {
    $locations = array('head', 'footer', 'content');
    foreach ( $locations as $where ) {
        $transient_name = amt_get_transient_name($post_id, $where);
        delete_transient($transient_name);
    }
}

// Delete all transients
// WORKS ONLY WHEN TRANSIENTS ARE STORED IN THE DATABASE
function amt_delete_all_transient_metadata_cache($blog_id=null) {
    if ( is_null($blog_id) ) {
        $blog_id = get_current_blog_id();
    }
    if ( $blog_id <= 0 ) {
        return;
    }

    // Clear the transient metadata cache

    global $wpdb;

    // Construct the options table name for the current blog
    $options_table = $wpdb->get_blog_prefix($blog_id) . 'options';

    // Construct SQL query that counts the AMT transient cache entries
    // Here we do not count the timeout entries: _transient_timeout_amt_* since they are not meant to be separate cached objects.
    $sql = "SELECT COUNT(*) FROM $options_table WHERE option_name LIKE '\_transient\_amt\_%'";

    // Get number of cache entries
    $nr_cache_entries = $wpdb->get_var($sql);

    // Construct SQL query that deletes the cached metadata
    // Here we also delete the timeout entries: _transient_timeout_amt_*
    $sql = "DELETE FROM $options_table WHERE option_name LIKE '\_transient\_amt\_%' OR option_name LIKE '\_transient\_timeout\_amt\_%'";

    // Execute query
    $wpdb->query($sql);

    return $nr_cache_entries;

}


// Count all AMT transients
function amt_count_transient_metadata_cache_entries($blog_id=null) {
    if ( is_null($blog_id) ) {
        $blog_id = get_current_blog_id();
    }
    if ( $blog_id <= 0 ) {
        return;
    }

    // Clear the transient metadata cache

    global $wpdb;

    // Construct the options table name for the current blog
    $options_table = $wpdb->get_blog_prefix($blog_id) . 'options';

    // Construct SQL query that counts the AMT transient cache entries
    // Here we do not count the timeout entries: _transient_timeout_amt_* since they are not meant to be separate cached objects.
    $sql = "SELECT COUNT(*) FROM $options_table WHERE option_name LIKE '\_transient\_amt\_%'";

    // Get number of cache entries
    $nr_cache_entries = $wpdb->get_var($sql);

    return $nr_cache_entries;

}


//
// Advanced Title Management
//


// Returns the title that should be used in the title HTML element
function amt_get_title_for_title_element($options, $post) {
    
    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_title_for_title_element', $post);
    $title_element_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $title_element_value !== false ) {
        return $title_element_value;
    }

    // Variables
    // #entity_title#, #page#, #page_total#, #site_name#, #site_tagline#
    
    $default_title_element_title_templates = array(
        // Default front page displaying the latest posts
        'front_page_default'        => '#site_name# | #site_tagline#',
        'front_page_default_paged'  => '#site_name# | Page #page# | #site_tagline#',
        // Front page using static page
        'front_page_static'         => '#site_name# | #site_tagline#',
        //'front_page_static'         => '#entity_title# | #site_tagline#',
        'front_page_static_paged'   => '#site_name# | Page #page# | #site_tagline#',
        // Latest posts page using static page
        'blog_index_static'         => '#entity_title# | #site_name#',
        'blog_index_static_paged'   => '#entity_title# | Page #page# | #site_name#',
        // Date Archives
        // Date::Yearly
        'archive_date_yearly'       => 'Yearly Archive: #year# | #site_name#',
        'archive_date_yearly_paged' => 'Yearly Archive: #year# | Page #page# | #site_name#',
        // Date::Monthly
        'archive_date_monthly'      => 'Monthly Archive: #month_name# #year# | #site_name#',
        'archive_date_monthly_paged'=> 'Monthly Archive: #month_name# #year# | Page #page# | #site_name#',
        // Date::Daily
        'archive_date_daily'      => 'Daily Archive: #month_name# #day#, #year# | #site_name#',
        'archive_date_daily_paged'=> 'Daily Archive: #month_name# #day#, #year# | Page #page# | #site_name#',
        // Taxonomy Archives
        // Taxonomy::Category
        'archive_taxonomy_category'        => '#Entity_title# Archive | #site_name#',
        'archive_taxonomy_category_paged'  => '#Entity_title# Archive | Page #page# | #site_name#',
        // Taxonomy::Tag
        'archive_taxonomy_post_tag'        => '#Entity_title# Archive | #site_name#',
        'archive_taxonomy_post_tag_paged'  => '#Entity_title# Archive | Page #page# | #site_name#',
        // Taxonomy::Custom
        'archive_taxonomy_CUSTOMSLUG'        => '#entity_title# Archive| #site_name#',
        'archive_taxonomy_CUSTOMSLUG_paged'  => '#entity_title# Archive | Page #page# | #site_name#',
        // Author Archives
        'archive_author'        => '#entity_title# profile | #site_name#',
        'archive_author_paged'  => 'Content published by #entity_title# | Page #page# | #site_name#',
        // Custom Post Type Archives
        'archive_posttype_POSTTYPESLUG'        => '#entity_title# Archive | #site_name#',
        'archive_posttype_POSTTYPESLUG_paged'  => '#entity_title# Archive | Page #page# | #site_name#',
        // Content
        // Content::Attachment
        'content_attachment'        => '#entity_title# | #site_name#',
        //'content_attachment_image'  => 'Image: #entity_title# | #site_name#',
        //'content_attachment_video'  => 'Video: #entity_title# | #site_name#',
        //'content_attachment_audio'  => 'Audio: #entity_title# | #site_name#',
        // Content::Page
        'content_page'        => '#entity_title# | #site_name#',
        'content_page_paged'  => '#entity_title# | Page #page# | #site_name#',
        // Content::Post
        'content_post'        => '#entity_title# | #site_name#',
        'content_post_paged'  => '#entity_title# | Page #page# | #site_name#',
        // Post with format
        //'content_post_image'  => 'Image: #entity_title# | #site_name#',
        //'content_post_video'  => 'Video: #entity_title# | #site_name#',
        //'content_post_audio'  => 'Audio: #entity_title# | #site_name#',
        //'content_post_status'  => 'Status: #entity_title# | #site_name#',
        //'content_post_gallery'  => 'Gallery: #entity_title# | #site_name#',
        //'content_post_link'  => 'Link: #entity_title# | #site_name#',
        //'content_post_quote'  => 'Quote: #entity_title# | #site_name#',
        //'content_post_chat'  => 'Chat: #entity_title# | #site_name#',
        // Content::Custom-Post-Type
        'content_POSTTYPESLUG'        => '#entity_title# | #site_name#',
        'content_POSTTYPESLUG_paged'  => '#entity_title# | Page #page# | #site_name#',
        // is_error - TODO
        // is_search - TODO
    );

    $title_element_title_templates = apply_filters('amt_titles_title_element_templates', $default_title_element_title_templates, $post);

    // Always use custom title if it is set
    $title_element_value = amt_internal_get_title($options, $post, $title_element_title_templates, $force_custom_title_if_set=true, $caller_is_metadata_generator=false);

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $title_element_value, $group='add-meta-tags' );

    return $title_element_value;
}


// Returns the title that should be used in the title HTML element
function amt_get_title_for_metadata($options, $post) {
    
    // Non persistent object cache
    $amtcache_key = amt_get_amtcache_key('amt_cache_get_title_for_metadata', $post);
    $metadata_title_value = wp_cache_get( $amtcache_key, $group='add-meta-tags' );
    if ( $metadata_title_value !== false ) {
        return $metadata_title_value;
    }

    // Variables
    // #entity_title#, #page#, #page_total#, #site_name#, #site_tagline#
    
    $default_metadata_title_templates = array(
        // Default front page displaying the latest posts
        'front_page_default'        => '#site_name#',
        'front_page_default_paged'  => '#site_name# | Page #page#',
        // Front page using static page
        'front_page_static'         => '#site_name#',
        'front_page_static_paged'   => '#site_name# | Page #page#',
        // Latest posts page using static page
        'blog_index_static'         => '#entity_title#',
        'blog_index_static_paged'   => '#entity_title# | Page #page#',
        // Date Archives
        // Date::Yearly
        //'archive_date_yearly'       => 'Yearly Archive: #year#',
        //'archive_date_yearly_paged' => 'Yearly Archive: #year# | Page #page#',
        // Date::Monthly
        //'archive_date_monthly'      => 'Monthly Archive: #month_name# #year#',
        //'archive_date_monthly_paged'=> 'Monthly Archive: #month_name# #year# | Page #page#',
        // Date::Daily
        //'archive_date_daily'       => 'Daily Archive: #month_name# #day#, #year#',
        //'archive_date_daily_paged' => 'Daily Archive: #month_name# #day#, #year# | Page #page#',
        // Taxonomy Archives
        // Taxonomy::Category
        'archive_taxonomy_category'        => '#Entity_title# Archive',
        'archive_taxonomy_category_paged'  => '#Entity_title# Archive | Page #page#',
        // Taxonomy::Tag
        'archive_taxonomy_post_tag'        => '#Entity_title# Archive',
        'archive_taxonomy_post_tag_paged'  => '#Entity_title# Archive | Page #page#',
        // Taxonomy::Custom
        'archive_taxonomy_CUSTOMSLUG'        => '#entity_title# Archive',
        'archive_taxonomy_CUSTOMSLUG_paged'  => '#entity_title# Archive | Page #page#',
        // Author Archives
        'archive_author'        => '#entity_title# profile',
        'archive_author_paged'  => 'Content published by #entity_title# | Page #page#',
        // Custom Post Type Archives
        'archive_posttype_POSTTYPESLUG'        => '#entity_title# Archive',
        'archive_posttype_POSTTYPESLUG_paged'  => '#entity_title# Archive | Page #page#',
        // Content
        // Content::Attachment
        'content_attachment'        => '#entity_title#',
        //'content_attachment_image'  => '#entity_title#',
        //'content_attachment_video'  => '#entity_title#',
        //'content_attachment_audio'  => '#entity_title#',
        // Content::Page
        'content_page'        => '#entity_title#',
        'content_page_paged'  => '#entity_title# | Page #page#',
        // Content::Post
        'content_post'        => '#entity_title#',
        'content_post_paged'  => '#entity_title# | Page #page#',
        // Post with format
        //'content_post_image'  => 'Image: #entity_title#',
        //'content_post_video'  => 'Video: #entity_title#',
        //'content_post_audio'  => 'Audio: #entity_title#',
        //'content_post_status'  => 'Status: #entity_title#',
        //'content_post_gallery'  => 'Gallery: #entity_title#',
        //'content_post_link'  => 'Link: #entity_title#',
        //'content_post_quote'  => 'Quote: #entity_title#',
        //'content_post_chat'  => 'Chat: #entity_title#',
        // Content::Custom-Post-Type
        'content_POSTTYPESLUG'        => '#entity_title#',
        'content_POSTTYPESLUG_paged'  => '#entity_title# | Page #page#',
        // is_error - TODO
        // is_search - TODO
    );

    $metadata_title_templates = apply_filters('amt_titles_metadata_title_templates', $default_metadata_title_templates, $post);

    $force_custom_title = false;
    if ( is_array($options) && array_key_exists('enforce_custom_title_in_metadata', $options) && $options['enforce_custom_title_in_metadata'] == '1' ) {
        $force_custom_title = true;
    }

    $metadata_title_value = amt_internal_get_title($options, $post, $metadata_title_templates, $force_custom_title_if_set=$force_custom_title, $caller_is_metadata_generator=true);

    // Non persistent object cache
    // Cache even empty
    wp_cache_add( $amtcache_key, $metadata_title_value, $group='add-meta-tags' );

    return $metadata_title_value;
}


// Return the title after applying the proper title template, if advanced titles are turned on.
function amt_internal_get_title($options, $post, $title_templates, $force_custom_title_if_set=false, $caller_is_metadata_generator=false) {

    // EARLY PROCESSING

    // First we check for a custom title whgich may have been inserted in the
    // relevant Custom Field of the supported types.
    $custom_title = '';
    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
        if ( ! is_null( $post ) ) {
            // Check if metadata is supported on this content type.
            $post_type = get_post_type( $post );
            if ( in_array( $post_type, amt_get_supported_post_types() ) ) {
                // Store the custom title. Should be empty for post types which do not support a custom title or which have an empty custom title.
                $custom_title = amt_get_post_meta_title( $post->ID );
                //$custom_title = str_replace('%title%', $title, $custom_title);
                // Allow filtering of the custom title
                $custom_title = apply_filters( 'amt_custom_title', $custom_title );
            }
        }
    }

    // Early processing in case advanced title management is TURNED OFF
    // This early processing takes place only for calls from the 'amt_get_title_for_title_element()' function.
    // WordPress constructs its own titles for the 'title' HTML element, so we
    // do not need to do further processing and title guessing here.
    // This early processing is NOT performed for calls from the 'amt_get_title_for_metadata()' function,
    // because the metadata generators 
    if ( ! $caller_is_metadata_generator && array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '0' ) {
        if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
            if ( ! empty($custom_title) ) {
                // Contains paging information
                return amt_process_paged($custom_title);
            }
        }
        return;
    }

    // From now on Add-Meta-Tags generates the title.

    // TEMPLATE VARIABLES
    // Set template variable values

    // #entity_title#, #Entity_title#, #Entity_Title#, #page#, #page_total#, #site_name#, #site_tagline#, #year#, #month#, #month_name#, #day#

    // Date variables
    // Credit for the following here: http://wordpress.stackexchange.com/a/109674
    // https://developer.wordpress.org/reference/classes/wp_locale/
    $var_year = 0;
    $var_month = 0;
    $var_month_name = '';
    $var_day = 0;
    if ( is_date() ) {
        // On date archives the following have a value. On the default front page are zero
        $var_year = get_query_var( 'year' );
        $var_month = get_query_var( 'monthnum' );
        $var_month_name = '';
        if ( $var_month ) {
            $var_month_name = $GLOBALS['wp_locale']->get_month($var_month);
        }
        $var_day = get_query_var('day');
    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
        $var_year = mysql2date('Y', $post->post_date);
        $var_month = mysql2date('m', $post->post_date);
        $var_month_name = '';
        if ( $var_month ) {
            $var_month_name = $GLOBALS['wp_locale']->get_month($var_month);
        }
        $var_day = mysql2date('d', $post->post_date);
    }

    // #page_total#
    global $wp_query;
    $page_total = 1;
    if ( isset( $wp_query->max_num_pages ) ) {
        $page_total = $wp_query->max_num_pages;
    }

    // #page#
    $page = 1;
    // For paginated archives or paginated main page with latest posts.
    if ( is_paged() ) {
        $paged = get_query_var( 'paged' );  // paged
        if ( $paged && $paged >= 2 ) {
            $page = $paged;
        }
    // For a Post or Page that has been divided into pages using the <!--nextpage--> QuickTag
    } else {
        $paged = get_query_var( 'page' );  // page
        if ( $paged && $paged >= 2 ) {
            $page = $paged;
        }
    }

    // #site_name#
    $site_name = get_bloginfo('name');

    // #site_tagline#
    $site_tagline = get_bloginfo('description');


    // MAIN PROCESSING
    // 1) generate title, 2) determine title template

    // #entity_title# and $entity_title_template
    $entity_title = '';
    $entity_title_template = '';

    // Default front page displaying the latest posts
    if ( amt_is_default_front_page() ) {
        // Entity title
        // $post is NULL
        $entity_title = get_bloginfo('name');
        // No custom title
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('front_page_default_paged', $title_templates) ) {
                $entity_title_template = $title_templates['front_page_default_paged'];
            } elseif ( array_key_exists('front_page_default', $title_templates) ) {
                $entity_title_template = $title_templates['front_page_default'];
            }
        }

    // Front page using a static page
    // Note: might also contain a listing of posts which may be paged, so use amt_process_paged()
    } elseif ( amt_is_static_front_page() ) {
        // Entity title
        $entity_title = strip_tags( get_the_title($post->ID) );
        if ( ! empty($custom_title) && $force_custom_title_if_set ) {
            $custom_title = str_replace('%title%', $entity_title, $custom_title);
            $entity_title = $custom_title;
        }
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('front_page_static_paged', $title_templates) ) {
                $entity_title_template = $title_templates['front_page_static_paged'];
            } elseif ( array_key_exists('front_page_static', $title_templates) ) {
                $entity_title_template = $title_templates['front_page_static'];
            }
        }

    // The posts index page - a static page displaying the latest posts
    } elseif ( amt_is_static_home() ) {
        // Entity title
        $entity_title = strip_tags( get_the_title($post->ID) );
        if ( ! empty($custom_title) && $force_custom_title_if_set ) {
            $custom_title = str_replace('%title%', $entity_title, $custom_title);
            $entity_title = $custom_title;
        }
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('blog_index_static_paged', $title_templates) ) {
                $entity_title_template = $title_templates['blog_index_static_paged'];
            } elseif ( array_key_exists('blog_index_static', $title_templates) ) {
                $entity_title_template = $title_templates['blog_index_static'];
            }
        }

    // Date Archives

    // Yearly Archive
    } elseif ( is_year() ) {
        // Entity title
        $entity_title = $var_year;
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('archive_date_yearly_paged', $title_templates) ) {
                $entity_title_template = $title_templates['archive_date_yearly_paged'];
            } elseif ( array_key_exists('archive_date_yearly', $title_templates) ) {
                $entity_title_template = $title_templates['archive_date_yearly'];
            }
        }

    // Monthly Archive
    } elseif ( is_month() ) {
        // Entity title
        $entity_title = $var_month_name;
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('archive_date_monthly_paged', $title_templates) ) {
                $entity_title_template = $title_templates['archive_date_monthly_paged'];
            } elseif ( array_key_exists('archive_date_monthly', $title_templates) ) {
                $entity_title_template = $title_templates['archive_date_monthly'];
            }
        }

    // Daily Archive
    } elseif ( is_day() ) {
        // Entity title
        $entity_title = $var_day;
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('archive_date_daily_paged', $title_templates) ) {
                $entity_title_template = $title_templates['archive_date_daily_paged'];
            } elseif ( array_key_exists('archive_date_daily', $title_templates) ) {
                $entity_title_template = $title_templates['archive_date_daily'];
            }
        }

    // Taxonomy Archive
    // $post is a taxonomy term object
    } elseif ( is_category() || is_tag() || is_tax() ) {
        // Entity title
        $entity_title = single_term_title( $prefix='', $display=false );
        // Entity title template
        $template_name = 'archive_taxonomy_' . $post->taxonomy;
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists($template_name . '_paged', $title_templates) ) {
                $entity_title_template = $title_templates[$template_name . '_paged'];
            } elseif ( array_key_exists($template_name, $title_templates) ) {
                $entity_title_template = $title_templates[$template_name];
            }
        }

    // Author Archive
    // $post is an author object
    } elseif ( is_author() ) {
        // Entity title
        $entity_title = $post->display_name;
        // Entity title template
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists('archive_author_paged', $title_templates) ) {
                $entity_title_template = $title_templates['archive_author_paged'];
            } elseif ( array_key_exists('archive_author', $title_templates) ) {
                $entity_title_template = $title_templates['archive_author'];
            }
        }

    // Custom Post Type Archive
    } elseif ( is_post_type_archive() ) {
        // Entity title
        $entity_title = post_type_archive_title( $prefix='', $display=false );
        // Entity title template
        // $post is a content type object
        $template_name = 'archive_posttype_' . $post->name;
        if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
            if ( $page && $page >= 2 && array_key_exists($template_name . '_paged', $title_templates) ) {
                $entity_title_template = $title_templates[$template_name . '_paged'];
            } elseif ( array_key_exists($template_name, $title_templates) ) {
                $entity_title_template = $title_templates[$template_name];
            }
        }

    // Content

    } elseif ( is_singular() ) {

        // Entity title
        // In some cases, like EDD downloads, get_the_title() also returns escaped HTML. Use strip_tags().
        $entity_title = strip_tags( get_the_title($post->ID) );
        // Alternatively, use the_title_attribute(). See: https://codex.wordpress.org/Function_Reference/the_title_attribute
        //$entity_title = the_title_attribute( array( 'before'=>'', 'after'=>'', 'echo'=>false, $post->ID) );
        if ( ! empty($custom_title) && $force_custom_title_if_set ) {
            $custom_title = str_replace('%title%', $entity_title, $custom_title);
            $entity_title = $custom_title;
        }

        // Attachments
        if ( is_attachment() ) {
            // Attachment type
            $mime_type = get_post_mime_type( $post->ID );
            //$attachment_type = strstr( $mime_type, '/', true );
            // See why we do not use strstr(): http://www.codetrax.org/issues/1091
            $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );
            $template_name = 'content_attachment';
            if ( array_key_exists('content_attachment_' . $attachment_type, $title_templates) ) {
                $template_name = 'content_attachment_' . $attachment_type;
            }
            // Entity title template
            if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
                // No paging info
                if ( array_key_exists($template_name, $title_templates) ) {
                    $entity_title_template = $title_templates[$template_name];
                }
            }

        // Page
        } elseif ( is_page() ) {
            // Entity title template
            if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
                if ( $page && $page >= 2 && array_key_exists('content_page_paged', $title_templates) ) {
                    $entity_title_template = $title_templates['content_page_paged'];
                } elseif ( array_key_exists('content_page', $title_templates) ) {
                    $entity_title_template = $title_templates['content_page'];
                }
            }

        // Posts and custom post types (with post format checking)
        } else {
            $post_type = get_post_type($post);
            $post_format = get_post_format($post->ID);
            $template_name = 'content_' . $post_type;
            if ( $post_format !== false && array_key_exists($template_name . '_' . $post_format, $title_templates) ) {
                $template_name = $template_name . '_' . $post_format;
            }
            // Entity title template
            if ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {
                if ( $page && $page >= 2 && array_key_exists($template_name . '_paged', $title_templates) ) {
                    $entity_title_template = $title_templates[$template_name . '_paged'];
                } elseif ( array_key_exists($template_name, $title_templates) ) {
                    $entity_title_template = $title_templates[$template_name];
                }
            }

        }

    }


    // LATE PROCESSING

    $title = '';

    // Late processing in case advanced title management is TURNED OFF
    // This late processing takes place only for calls from the 'amt_get_title_for_metadata()' function.
    // The metadata generators do not construct a title, so this has to be done here (above $entity_title).
    if ( $caller_is_metadata_generator && array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '0' ) {
        if ( ! empty($entity_title) ) {
            if ( $page && $page >= 2 ) {
                $title = amt_process_paged($entity_title);
            } else {
                $title = $entity_title;
            }
        } else {
            // TODO: This appears in BuddyPress member pages. Maybe it should just set $title to an empty string.
            //$title = 'PROGRAMMING ERROR - MISSING TITLE';
            $title = '';
        }

    } elseif ( empty($entity_title_template) ) {

        // If the caller is a metadata generator, then the title cannot be determined otherwise.
        // So, if an $entity_title has been found we return it.
        if ( $caller_is_metadata_generator ) {
            // If a metadata generator requested a title, but a template was
            // not found, return an error message as the title, unless the
            // $entity_title is not empty, in which case set the title to it as is.
            if ( ! empty($entity_title) ) {
                $title = $entity_title;
            } else {
                // TODO: Maybe it should just set $title to an empty string.
                //$title = 'TITLE TEMPLATE NOT FOUND';
                $title = '';
            }
        } else {
            // If the title was requested for the 'title' HTML element, but a template
            //was not found, return an empty string, so that the default WordPress title is used.
            // TODO: Check if still required
            $title = '';
        }

    // If advanced title management is enabled
    } elseif ( array_key_exists('enable_advanced_title_management', $options) && $options['enable_advanced_title_management'] == '1' ) {

        $template_vars = array(
            '#year#' => $var_year,
            '#month#' => $var_month,
            '#month_name#' => $var_month_name,
            '#day#' => $var_day,
            '#page#' => $page,
            '#page_total#' => $page_total,
            '#site_name#' => $site_name,
            '#site_tagline#' => $site_tagline,
            '#entity_title#' => $entity_title,
            '#Entity_title#' => ucfirst(strtolower($entity_title)),
            '#Entity_Title#' => ucwords(strtolower($entity_title)),
        );
        // Replace variables in the template
        foreach ( $template_vars as $var_name=>$var_value ) {
            $entity_title_template = str_replace( $var_name, $var_value, $entity_title_template );
        }
        $title = $entity_title_template;

    }

    return $title;

}



//
//
//  BuddyPress Utility Functions
//
//


// Returns the BuddyPress profile slug
// The bp_get_profile_slug() was added in BuddyPress 2.4.
// If the function does not exist, we hard-code 'profile'.
function amt_bp_get_profile_slug() {
    if ( function_exists('bp_get_profile_slug') ) {
        return bp_get_profile_slug();
    }
    return 'profile';
}


// Returns the BuddyPress field map
function amt_buddypress_get_xprofile_field_map() {
    $xprofile_field_map = array(
        'description'       => array('excerpt', 'summary', 'description', 'bio', 'about'),
        'keywords'          => array('keywords', 'skills', 'interests'),    // TODO: Future: add group names?
        'first_name'        => array('first name', 'given name'),
        'last_name'         => array('last name', 'family name', 'surname'),
        'additional_name'   => array('additional name', 'middle name'),
        'nickname'          => array('nickname', 'alias', 'alternate name'),
        'honorific_prefix'  => array('honorific prefix'),
        'honorific_suffix'  => array('honorific suffix'),
        'gender'            => array('gender', 'sex'),
        'nationality'       => array('nationality', 'country'),
        'telephone'         => array('telephone', 'phone', 'tel', 'telephone number'),
        'fax'               => array('fax number', 'fax'),
        'email'             => array('email', 'email address'),
        'website'           => array('website', 'web site', 'url', 'homepage', 'blog', 'personal page', 'alternative profile'),
        'job_title'         => array('job', 'job title'),
        'works_for'         => array('company', 'company name', 'employer', 'works for'),
        'works_for_url'     => array('company url', 'employer url'),
        'work_latitude'     => array('work latitude'),
        'work_longitude'    => array('work longitude'),
        'home_latitude'     => array('home latitude'),
        'home_longitude'    => array('home longitude'),
    );
    return apply_filters( 'amt_buddypress_xprofile_field_map', $xprofile_field_map );
}


// Returns the field contents
function amt_bp_get_profile_field_data( $internal_profile_property, $user_id, $xprofile_field_map, $xprofile_public_fields ) {
    foreach ( $xprofile_field_map[$internal_profile_property] as $field_name ) {
        $field_value = bp_get_profile_field_data( array( 'field'=>$field_name, 'user_id'=>$user_id ) );
        // profile_group_id
        if ( ! empty($field_value) && in_array(xprofile_get_field_id_from_name($field_name), $xprofile_public_fields) ) {
            return $field_value;
        }
    }
    return '';
}



//
// Content & Metadata Overview & Analysis
//


function amt_metadata_analysis($default_text, $metadata_block_head, $metadata_block_footer, $metadata_block_content_filter) {
    // Analysis is appended only o content pages
    if ( ! is_singular() ) {
        return $default_text;
    }
    // Check the filter based switch
    if ( ! apply_filters('amt_metadata_analysis_enable', true) ) {
        return $default_text;
    }

    //
    // Collect data
    //

    $options = amt_get_options();
    $post = amt_get_queried_object();

    if ( ! isset($post->ID) || $post->ID <= 0 ) {
        return $default_text;
    }

    // Content and stats
    // Post content
    $post_content = strtolower( amt_get_clean_post_content( $options, $post ) );
    $post_content_length = strlen($post_content);
    //var_dump($post_content);
    // Total words
    if ( function_exists('wordstats_words') ) {
        $post_word_count = wordstats_words($post_content);  // provided by the word-statistics-plugin by FD
    } else {
        $post_word_count = str_word_count($post_content);
    }
    // Total sentences
    if ( function_exists('wordstats_sentences') ) {
        $post_sentence_count = wordstats_sentences($post_content);  // provided by the word-statistics-plugin by FD
    } else {
        $post_sentence_count = preg_match_all('/[.!?\r]/', $post_content, $dummy );
    }
    // Total syllables
    // TODO: Find better function
    $post_syllable_count = preg_match_all('/[aeiouy]/', $post_content, $dummy );

    // Titles
    // Original
    $post_title = strtolower( strip_tags(get_the_title($post->ID)) );
    // Title HTML element
    if ( $options['enable_advanced_title_management'] == '1' ) {
        // If Advanced Title management is enabled, use this directly:
        $post_title_html_element = strtolower( amt_get_title_for_title_element($options, $post) );
    } else {
        if ( version_compare( get_bloginfo('version'), '4.4', '>=' ) ) {
            // Since WP 4.4
            // - https://make.wordpress.org/core/2015/10/20/document-title-in-4-4/
            //$post_title_html_element = strtolower( apply_filters('document_title_parts', array('title' => $post_title) ) );
            //$post_title_html_element = wp_get_document_title();
            $post_title_html_element = strtolower( get_wp_title_rss() );
        } else {
            // Reverting back to the one argument version of the fitlering function.
            //$post_title_html_element = strtolower( apply_filters('wp_title', $post_title) );
            // Until testing is performed on old WP versions we just use post title
            $post_title_html_element = $post_title;
        }
    }
    //var_dump($post_title_html_element);
    // Title in metadata
    $post_title_metadata = strtolower( amt_get_title_for_metadata($options, $post) );
    //var_dump($post_title_metadata);

    // URL
    $post_url = str_replace( get_bloginfo('url'), '', amt_get_permalink_for_multipage($post) );
    //var_dump($post_url);

    // Description
    $description = '';
    if ( array_key_exists( 'basic:description', $metadata_block_head ) ) {
        $description = strtolower( preg_replace('#^.*content="([^"]+)".*$#', '$1', $metadata_block_head['basic:description']) );
    }
    //var_dump($description);
    // Keywords
    $keywords = array();
    if ( array_key_exists( 'basic:keywords', $metadata_block_head ) ) {
        $keywords_content = strtolower( preg_replace('#^.*content="([^"]+)".*$#', '$1', $metadata_block_head['basic:keywords']) );
        $keywords = explode( ',', str_replace(', ', ',', $keywords_content) );
    }
    //var_dump($keywords);

    // Keyword matching pattern
    //$keyword_matching_pattern = '#(?:%s)#';
    //$keyword_matching_pattern = '#(?:%s)[[:^alpha:]]#';
    //$keyword_matching_pattern = '#(?:%s)[[:^alpha:]]?#';
    $keyword_matching_pattern = '#(?:%s)(?:[[:^alpha:]]|$)#';
    $keyword_matching_pattern = apply_filters('amt_metadata_analysis_keyword_matching_pattern', $keyword_matching_pattern);

    // Whether to use topic keywords field or the keywords from the 'keywords' meta tag.
    $use_keywords = false;

    // First check for a field that contains topic keywords.
    $topic_keywords_field_name = apply_filters('amt_metadata_analysis_topic_keywords_field', 'topic_keywords');
    $topic_keywords_field_value = get_post_meta( $post->ID, $topic_keywords_field_name, true );
    if ( ! empty($topic_keywords_field_value) ) {
        $topic_keywords = explode( ',', str_replace(', ', ',', $topic_keywords_field_value) );
    } else {
        $topic_keywords = $keywords;
        $use_keywords = true;
        //var_dump($topic_keywords);
    }

    $BR = PHP_EOL;

    if ( $options['review_mode_omit_notices'] == '0' ) {
        $output = $default_text . $BR . $BR;
        //$output .= $BR . '<span class="">Text analysis</span>' . $BR;
    } else {
        $output = '';
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {

        $output .= 'Metadata Overview' . $BR;
        $output .= '================='. $BR;

        //$output .= 'This overview has been generated by the Add-Meta-Tags plugin for statistical and' . $BR;
        //$output .= 'informational purposes only. Please do not modify or base your work upon this report.' . $BR . $BR;
        $output .= 'NOTICE: Add-Meta-Tags does not provide SEO advice and does not rate your content.' . $BR;
        $output .= 'This <a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Overview">overview</a> has been generated for statistical and informational purposes only.' . $BR . $BR;
        //$output .= '<a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/FAQ#Is-Add-Meta-Tags-an-SEO-plugin">Read more</a> about the mentality upon which the development of this plugin has been based.' . $BR;
        //$output .= 'Please use this statistical information to identify keyword overstuffing' . $BR;
        //$output .= 'and stay away from following any patterns or being bound by the numbers.' . $BR . $BR;

    }

    if ( $use_keywords ) {
        $output .= sprintf('This overview has been based on post keywords, because the Custom Field \'<em>%s</em>\' could not be found.', $topic_keywords_field_name) . $BR;
    } else {
        $output .= sprintf('This overview has been based on <em>topic keywords</em> retrieved from the Custom Field \'<em>%s</em>\'.', $topic_keywords_field_name) . $BR;
    }

    if ( $options['review_mode_omit_notices'] == '0' ) {
        $output .= 'Keyword Analysis' . $BR;
        $output .= '----------------' . $BR;
    } else {
        $output .= $BR;
    }

    $output .= '<table class="amt-ht-table">';
    $output .= '<tr> <th>Topic Keyword</th> <th>Content</th> <th>Description</th> <th>Keywords</th> <th>Post Title</th> <th>HTML title</th> <th>Metadata titles</th> <th>Post URL</th> </tr>';

    foreach ($topic_keywords as $topic_keyword) {
        $output .= sprintf( '<tr> <td>%s</td>', $topic_keyword );

        $is_into = array();

        // Check content
        $is_into['content'] = '';
        $occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $post_content, $matches );
        if ( $occurrences ) {
            // Only for content
            $topic_keyword_desnity = round( (($occurrences / $post_word_count) * 100), 2);
            $output .= sprintf( ' <td>%d (%.2f%%)</td>', $occurrences, $topic_keyword_desnity );
        } else {
            $output .= '<td> </td>';
        }

        // Check description
        $occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $description, $matches );
        if ( $occurrences ) {
            $output .= sprintf( ' <td>%d</td>', $occurrences );
        } else {
            $output .= '<td> </td>';
        }

        // Check keywords
        if ( $use_keywords ) {
            $output .= '<td>N/A</td>';
            $is_into['keywords'] = 'N/A';
        } elseif ( in_array($topic_keyword, $keywords) ) {
            // Always 1
            $output .= '<td>1</td>';
        } else {
            $output .= '<td> </td>';
        }

        // Check original title
        $occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $post_title, $matches );
        if ( $occurrences ) {
            $output .= sprintf( ' <td>%d</td>', $occurrences );
        } else {
            $output .= '<td> </td>';
        }


        // Check title element
        $occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $post_title_html_element, $matches );
        if ( $occurrences ) {
            $output .= sprintf( ' <td>%d</td>', $occurrences );
        } else {
            $output .= '<td> </td>';
        }

        // Check metadata titles
        $occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $post_title_metadata, $matches );
        if ( $occurrences ) {
            $output .= sprintf( ' <td>%d</td>', $occurrences );
        } else {
            $output .= '<td> </td>';
        }

        // Check post URL
        $occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $post_url, $matches );
        if ( $occurrences ) {
            $output .= sprintf( ' <td>%d</td>', $occurrences );
        } else {
            $output .= '<td> </td>';
        }

        // Close row
        $output .= ' </tr>' . $BR;

    }

    $output .= '</table>' . $BR;


    // Topic Keywords Distribution Graph

    if ( $options['review_mode_omit_notices'] == '0' ) {
        $output .= 'Topic Keywords Distribution Graph' . $BR;
        $output .= '---------------------------------'. $BR;

        $output .= 'The following text based graph shows how the <em>topic keywords</em> are distributed within your content.'. $BR;
        $output .= 'You can use it to identify incidents of keyword overstuffing.'. $BR . $BR;
    }

    //$output .= $BR . $BR;
    $total_bars = 39;   // zero based
    $step = $post_content_length / $total_bars;

    // Debug
    //$output .= $BR . $post_content_length . '  ' . $step . $BR;

    $max_weight = null;
    $weights = array();
    // Reset weights
    for ($x = 0; $x <= $total_bars; $x++) {
        $weights[$x] = 1;
    }
    foreach ($topic_keywords as $topic_keyword) {
        // Use preg_match_all with PREG_OFFSET_CAPTURE -- http://php.net/manual/en/function.preg-match-all.php
        $topic_keyword_occurrences = preg_match_all( sprintf($keyword_matching_pattern, $topic_keyword), $post_content, $matches, PREG_OFFSET_CAPTURE );
        //var_dump($matches);
        if ( ! empty($topic_keyword_occurrences) ) {
            foreach ($matches[0] as $match) {
                $pos = $match[1];
                $step_index = absint($pos / $step);
                $weights[$step_index] = $weights[$step_index] + 1;
                if ($weights[$step_index] > $max_weight) {
                    $max_weight = $weights[$step_index];
                }
                // Debug
                //$output .= sprintf('kw: %s, pos: %s, step index: %s, step weight: %s', $topic_keyword, $pos, $step_index, $weights[$step_index]) . $BR;
            }
        }
    }

    //var_dump($weights);
    for ($x = $max_weight - 1; $x >= 1; $x--) {
        $line = '';
        for ($y = 0; $y <= $total_bars; $y++) {
            if ($weights[$y] > $x) {
                $line .= '#';
            } else {
                $line .= ' ';
            }
        }
        $output .= $line . $BR;
    }
    // Currently this text based ruler is used as base line.
    $output .= str_repeat('---------+', (($total_bars + 1) / 10)) . $BR;

    if ( $options['review_mode_omit_notices'] == '0' ) {
        $output .= $BR . '<code>#</code>: indicates a single occurrence of a <em>topic keyword</em>.' . $BR;
    } else {
        $output .= $BR;
    }


    // Stats and scores by algos provided by the word-statistics-plugin by FD
    if ( function_exists('wordstats_words') ) {

        // Readability Tests

        if ( $options['review_mode_omit_notices'] == '0' ) {
            $output .= $BR . 'Readability Scores and Text Statistics' . $BR;
            $output .=       '--------------------------------------' . $BR;

            $output .= 'These readability scores and text statistics are based on algorithms provided by the <em>FD Word Statistics Plugin</em>.' . $BR . $BR;
        }

        if ( function_exists('wordstats_words') ) {
            $output .= sprintf(' &#9679; Total words: <strong>%d</strong>', wordstats_words($post_content) ) . $BR;
        }
        if ( function_exists('wordstats_sentences') ) {
            $output .= sprintf(' &#9679; Total sentences: <strong>%d</strong>', wordstats_sentences($post_content) ) . $BR;
        }
        if ( function_exists('wordstats_flesch_kincaid') ) {
            $output .= sprintf(' &#9679; Flesch-Kincaid US grade level: <strong>%.1f</strong> <em>(For instance, a score of 9.3 means suitable for a 9th grade student in the US, <a target="_blank" href="%s">read more</a>.)</em>', wordstats_flesch_kincaid($post_content), 'https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests#Flesch.E2.80.93Kincaid_grade_level' ) . $BR;
        }
        if ( function_exists('wordstats_flesch') ) {
            $output .= sprintf(' &#9679; Flesch reading ease: <strong>%.1f%%</strong> <em>(avg 11 y.o. student: 90-100%%, 13-15 y.o. students: 60-70%%, university graduates: 0-30%%, <a target="_blank" href="%s">read more</a>.)</em>', wordstats_flesch($post_content), 'https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests#Flesch_reading_ease' ) . $BR;
        }
        if ( function_exists('wordstats_fog') ) {
            $output .= sprintf(' &#9679; Gunning fog index: <strong>%.1f</strong> <em>(wide audience: < 12, near universal understanding: < 8, <a target="_blank" href="%s">read more</a>.)</em>', wordstats_fog($post_content), 'https://en.wikipedia.org/wiki/Gunning_fog_index' ) . $BR;
        }

        $output .= $BR;

    } else {
        if ( $options['review_mode_omit_notices'] == '0' ) {
            $output .= $BR . $BR . 'Note: There is experimental support for <em>FD Word Statistics Plugin</em>.';
            $output .= $BR . 'If installed, you can get some readability scores and text statistics here.' . $BR . $BR;
        }
    }

    return $output;
}


