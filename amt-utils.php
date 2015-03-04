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
 * Module containing utility functions.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
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
    $pattern = get_shortcode_regex();
    //var_dump($pattern);
    $desc = preg_replace('#' . $pattern . '#s', '', $desc);

    // Clean double quotes
    $desc = str_replace('"', '', $desc);
    $desc = str_replace('&quot;', '', $desc);

    // Convert single quotes to space
    //$desc = str_replace("'", ' ', $desc);
    //$desc = str_replace('&#039;', ' ', $desc);
    //$desc = str_replace("&apos;", ' ', $desc);
    //$desc = str_replace("&#8216;", ' ', $desc);
    //$desc = str_replace("&#8217;", ' ', $desc);
    // Finally, convert double space to single space.
    //$desc = str_replace('  ', ' ', $desc);

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
    
    if ( empty($post->post_excerpt) || get_post_type( $post ) == 'attachment' ) {   // In attachments we always use $post->post_content to get a description

        // Here we generate an excerpt from $post->post_content

        // First strip all HTML tags
        $plain_text = wp_kses( $post->post_content, array() );

        // Get the initial text.
        // We use $excerpt_max_len characters of the text for the description.
        $amt_excerpt = sanitize_text_field( amt_sanitize_description( substr($plain_text, 0, $excerpt_max_len) ) );

        // Remove any URLs that may exist exactly at the beginning of the description.
        // This may happen if for example you put a youtube video url first thing in
        // the post body.
        $amt_excerpt = preg_replace( '#^https?:[^\t\r\n\s]+#i', '', $amt_excerpt );
        $amt_excerpt = ltrim( $amt_excerpt );

        // If this was not enough, try to get some more clean data for the description (nasty hack)
        if ( strlen($amt_excerpt) < $desc_avg_length ) {
            $amt_excerpt = sanitize_text_field( amt_sanitize_description( substr($post->post_content, 0, (int) ($excerpt_max_len * 1.5)) ) );
            if ( strlen($amt_excerpt) < $desc_avg_length ) {
                $amt_excerpt = sanitize_text_field( amt_sanitize_description( substr($post->post_content, 0, (int) ($excerpt_max_len * 2)) ) );
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
     *  function customize_amt_excerpt( $post ) {
     *      $amt_excerpt = ...
     *      return $amt_excerpt;
     *  }
     *  add_filter( 'amt_get_the_excerpt', 'customize_amt_excerpt', 10, 1 );
     */
    $amt_excerpt = apply_filters( 'amt_get_the_excerpt', $amt_excerpt, $post );

    return $amt_excerpt;
}


/**
 * Returns a comma-delimited list of a post's terms that belong to custom taxonomies.
 */
function amt_get_keywords_from_custom_taxonomies( $post ) {
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
        return implode(', ', $keywords_arr);
    } else {
        return '';
    }
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

    if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
        $tags = get_the_tags($post->ID);
        if ( empty( $tags ) ) {
            return false;
        } else {
            $tag_list = "";
            foreach ( $tags as $tag ) {
                $tag_list .= $tag->name . ', ';
            }
            $tag_list = rtrim($tag_list, " ,");
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
        $all_cats = rtrim($all_cats, " ,");
        return $all_cats;
    }
}


/**
 * Returns an array of the category names that appear in the posts of the loop.
 * Category 'Uncategorized' is excluded.
 *
 * Accepts the $category_arr, an array containing the initial categories.
 */
function amt_get_categories_from_loop( $category_arr=array() ) {
    if (have_posts()) {
        while ( have_posts() ) {
            the_post(); // Iterate the post index in The Loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
            $categories = get_the_category();
            if( $categories ) {
                foreach( $categories as $category ) {
                    if ( ! in_array( $category->name, $category_arr ) && $category->slug != 'uncategorized' ) {
                        $category_arr[] = $category->name;
                    }
                }
            }
		}
	}
    rewind_posts(); // Not sure if this is needed.
    return $category_arr;
}


/**
 * Returns an array of the tag names that appear in the posts of the loop.
 *
 * Accepts the $tag_arr, an array containing the initial tags.
 */
function amt_get_tags_from_loop( $tag_arr=array() ) {
    if (have_posts()) {
        while ( have_posts() ) {
            the_post(); // Iterate the post index in The Loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
            $tags = get_the_tags();
            if( $tags ) {
                foreach( $tags as $tag ) {
                    if ( ! in_array( $tag->name, $tag_arr ) ) {
                        $tag_arr[] = $tag->name;
                    }
                }
            }
		}
	}
    rewind_posts(); // Not sure if this is needed.
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
    return $content_description;
}


/**
 * This is a helper function that returns the post's or page's keywords.
 *
 * Important: MUST return sanitized data, unless this plugin has sanitized the data before storing to db.
 *
 */
function amt_get_content_keywords($post, $auto=true) {
    
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
        if ( !empty($keyw_fld_content) ) {
            
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

        } elseif ( is_singular() ) {
            if ($auto) {
                /*
                 * Add keywords automatically.
                 * Keywords consist of the post's categories, the post's tags (tags exist in WordPress 2.3 or newer)
                 * and the terms of the custom taxonomies to which the post belongs (since WordPress 2.8).
                 */
                // Categories - Here we sanitize the provided keywords for safety
                $keywords_from_post_cats = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_post_cats($post) ) );
                if (!empty($keywords_from_post_cats)) {
                    $content_keywords .= $keywords_from_post_cats;
                }
                // Tags - Here we sanitize the provided keywords for safety
                $keywords_from_post_tags = sanitize_text_field( amt_sanitize_keywords( amt_get_post_tags($post) ) );
                if (!empty($keywords_from_post_tags)) {
                    $content_keywords .= ", " . $keywords_from_post_tags;
                }
                // Custom taxonomy terms - Here we sanitize the provided keywords for safety
                $keywords_from_post_custom_taxonomies = sanitize_text_field( amt_sanitize_keywords( amt_get_keywords_from_custom_taxonomies($post) ) );
                if (!empty($keywords_from_post_custom_taxonomies)) {
                    $content_keywords .= ", " . $keywords_from_post_custom_taxonomies;
                }
            }
        }
    }

    // Add post format to the list of keywords
    if ( is_singular() && get_post_format($post->ID) !== false ) {
        if ( empty($content_keywords) ) {
            $content_keywords .= get_post_format($post->ID);
        } else {
            $content_keywords .= ', ' . get_post_format($post->ID);
        }
    }

    /**
     * Finally, add the global keywords, if they are set in the administration panel.
     */
    #if ( !empty($content_keywords) && ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) ) {
    if ( $auto && ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) ) {

        $options = get_option("add_meta_tags_opts");
        $global_keywords = $options["global_keywords"];

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
    $supported_builtin_types = array('post', 'page', 'attachment');
    $public_custom_types = get_post_types( array('public'=>true, '_builtin'=>false, 'show_ui'=>true) );
    $supported_types = array_merge($supported_builtin_types, $public_custom_types);

    // Allow filtering of the supported content types.
    $supported_types = apply_filters( 'amt_supported_post_types', $supported_types );

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
        'referenced_list_box_capability' => 'edit_posts'
    );
    // Allow filtering of the metabox permissions
    $metabox_permissions = apply_filters( 'amt_metadata_metabox_permissions', $metabox_permissions );

    return $metabox_permissions;
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


/**
 * Helper function that returns the value of the custom field that contains
 * the per-post full metatags.
 * The default field name is ``_amt_full_metatags``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_full_metatags($post_id) {
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
        return '';
    }

    // Try our fields
    foreach( $supported_custom_fields as $sup_field ) {
        // If such a field exists in the db, return its content as the full metatags.
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


/**
 * Helper function that returns an array of objects attached to the provided
 * $post object.
 */
function amt_get_ordered_attachments( $post ) {
    // to return IDs:
    // $attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
    return get_children( array(
        'numberposts' => -1,
        'post_parent' => $post->ID,
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        //'post_mime_type' => 'image',
        'order' => 'ASC',
        'orderby' => 'menu_order ID'
        )
    );
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
    $pagenum = get_query_var( 'page' );
    // Content is multipage
    if ( $pagenum && $pagenum > 1 ) {
        // Not using clean URLs -> Add query argument to the URL (eg: ?page=2)
        if ( '' == get_option('permalink_structure') || in_array( $post->post_status, array('draft', 'pending')) ) {
            return add_query_arg( 'page', $pagenum, get_permalink($post->ID) );
        // Using clean URLs
        } else {
            return trailingslashit( get_permalink($post->ID) ) . user_trailingslashit( $pagenum, 'single_paged');
        }
    // Content is not paged
    } else {
        return get_permalink($post->ID);
    }
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


/**
 * Returns an array with URLs to players for some embedded media.
 */
function amt_get_embedded_media( $post ) {

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
        foreach( $matches[1] as $youtube_video_id ) {
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
                'width' => apply_filters( 'amt_oembed_youtube_player_width', '640' ),
                'height' => apply_filters( 'amt_oembed_youtube_player_height', '480' ),
            );
            array_unshift( $embedded_media_urls['videos'], $item );
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
        foreach( $matches[1] as $vimeo_video_id ) {
            $item = array(
                'type' => 'vimeo',
                'page' => 'https://vimeo.com/' . $vimeo_video_id,
                'player' => 'https://player.vimeo.com/video/' . $vimeo_video_id,
                'thumbnail' => apply_filters( 'amt_oembed_vimeo_image_preview', '', $vimeo_video_id ),
                'width' => apply_filters( 'amt_oembed_vimeo_player_width', '640' ),
                'height' => apply_filters( 'amt_oembed_vimeo_player_height', '480' ),
            );
            array_unshift( $embedded_media_urls['videos'], $item );
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
        foreach( $matches[1] as $vine_video_id ) {
            $item = array(
                'type' => 'vine',
                'page' => 'https://vine.co/v/' . $vine_video_id,
                'player' => 'https://vine.co/v/' . $vine_video_id . '/embed/simple',
                'thumbnail' => apply_filters( 'amt_oembed_vine_image_preview', '', $vine_video_id ),
                'width' => apply_filters( 'amt_oembed_vine_player_width', '600' ),
                'height' => apply_filters( 'amt_oembed_vine_player_height', '600' ),
            );
            array_unshift( $embedded_media_urls['videos'], $item );
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
    $pattern = '#https?:\/\/(?:www.)?soundcloud.com\/[^/]+\/[a-zA-Z0-9_-]+#i';
    preg_match_all( $pattern, $post_body, $matches );
    //var_dump($matches);
    if ($matches) {
        // $matches[0] contains a list of Soundcloud URLS
        // Add matches to $embedded_media_urls
        foreach( $matches[0] as $soundcloud_url ) {
            $item = array(
                'type' => 'soundcloud',
                'page' => $soundcloud_url,
                'player' => 'https://w.soundcloud.com/player/?url=' . $soundcloud_url,
                'thumbnail' => apply_filters( 'amt_oembed_soundcloud_image_preview', '', $soundcloud_url ),
                'width' => apply_filters( 'amt_oembed_soundcloud_player_width', '640' ),
                'height' => apply_filters( 'amt_oembed_soundcloud_player_height', '164' ),
            );
            array_unshift( $embedded_media_urls['sounds'], $item );
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
                array_unshift( $embedded_media_urls['images'], $item );
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


/**
 * Custom meta tag highlighter.
 *
 * Expects string.
 */
function amt_metatag_highlighter( $metatags ) {

    // Convert special chars, but leave quotes.
    $metatags = htmlspecialchars($metatags, ENT_NOQUOTES);

    preg_match_all('#([^\s]+="[^"]+?)"#i', $metatags, $matches);
    if ( !$matches ) {
        return $metatags;
    }

    //var_dump($matches[0]);
    foreach ($matches[0] as $match) {
        $highlighted = preg_replace('#^([^=]+)="(.+)"$#i', '<span style="font-weight:bold;color:black;">$1</span>="<span style="color:blue;">$2</span>"', $match);
        //var_dump($highlighted);
        $metatags = str_replace($match, $highlighted, $metatags);
    }

    // Highlight 'itemscope'
    $metatags = str_replace('itemscope', '<span style="font-weight: bold; color: #B90746;">itemscope</span>', $metatags);

    // Do some conversions
    $metatags =  wp_pre_kses_less_than( $metatags );
    // Done by wp_pre_kses_less_than()
    //$metatags = str_replace('<meta', '&lt;meta', $metatags);
    //$metatags = str_replace('/>', '/&gt;', $metatags);

    return $metatags;
}


// Accepts a URL and converts the protocol to https. Returns the processed URL.
function amt_make_https( $url ) {
    return preg_replace( '#^http://#' , 'https://', $url );
}


function amt_return_false() {
    return false;
}


// Returns site language
function amt_get_language_site() {
    $language = get_bloginfo('language');
    // Allow filtering of the site language
    $language = apply_filters( 'amt_language_site', $language );
    return $language;
}


// Returns content language
function amt_get_language_content() {
    $language = get_bloginfo('language');
    // Allow filtering of the content language
    $language = apply_filters( 'amt_language_content', $language );
    return $language;
}


