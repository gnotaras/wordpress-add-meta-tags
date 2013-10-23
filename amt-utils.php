<?php
/**
 * Module containing utility functions.
 */




/**
 * Helper function that returns an array of allowable HTML elements and attributes
 * for use in wp_kses() function.
 */
function amt_get_allowed_html_kses() {
    return array(
        'meta' => array(
            'charset' => array(),
            'content' => array(),
            'http-equiv' => array(),
            'name' => array(),
            'scheme' => array(),
            'itemprop' => array(),  // schema.org
            'property' => array(),  // facebook and others
        )
    );
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
    $desc = str_replace("'", ' ', $desc);
    $desc = str_replace('&#039;', ' ', $desc);
    $desc = str_replace("&apos;", ' ', $desc);

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
    $data = str_replace('#contentkw#', '%contentkw%', $data);
    $data = str_replace('#title#', '%title%', $data);
    return $data;
}


function amt_get_content_keywords_mesh( $post ) {
    // Keywords returned in the form: keyword1;keyword2;keyword3
    $keywords = explode(', ', amt_get_content_keywords($post));
    return implode(';', $keywords);
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
            if ( strpos($global_keywords, '%contentkw%') === false ) {
                // The placeholder ``%contentkw%`` has not been used. Append the content keywords to the global keywords.
                $content_keywords = $global_keywords . ', ' . $content_keywords;
            } else {
                // The user has used the placeholder ``%contentkw%``. Replace it with the content keywords.
                $content_keywords = str_replace('%contentkw%', $content_keywords, $global_keywords);
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
 *
 * And also to ALL public custom post types which have a UI.
 *
 * NOTE ABOUT attachments:
 * The 'attachment' post type does not support saving custom fields like other post types.
 * See: http://www.codetrax.org/issues/875
 */
function amt_get_supported_post_types() {
    $supported_builtin_types = array('post', 'page');
    $public_custom_types = get_post_types( array('public'=>true, '_builtin'=>false, 'show_ui'=>true) );
    $supported_types = array_merge($supported_builtin_types, $public_custom_types);

    // Allow filtering of the supported content types.
    $supported_types = apply_filters( 'amt_supported_post_types', $supported_types );

    return $supported_types;
}


/**
 * Helper function that returns the value of the custom field that contains
 * the content description.
 * The default field name for the description has changed to ``_amt_description``.
 * For easy migration this function supports reading the description from the
 * old ``description`` custom field and also from the custom field of other plugins.
 */
function amt_get_post_meta_description($post_id) {
    $amt_description_field_name = '_amt_description';

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys($post_id);

    // Just return an empty string if no custom fields have been associated with this content.
    if ( empty($custom_fields) ) {
        return '';
    }

    // First try our default description field
    if ( in_array($amt_description_field_name, $custom_fields) ) {
        return get_post_meta($post_id, $amt_description_field_name, true);
    }
    // Try old description field: ``description``
    elseif ( in_array('description', $custom_fields) ) {
        return get_post_meta($post_id, 'description', true);
    }
    // Try other description field names here.
    // Support reading from other plugins

    //Return empty string if all fails
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
    $amt_keywords_field_name = '_amt_keywords';

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys($post_id);

    // Just return an empty string if no custom fields have been associated with this content.
    if ( empty($custom_fields) ) {
        return '';
    }

    // First try our default keywords field
    if ( in_array($amt_keywords_field_name, $custom_fields) ) {
        return get_post_meta($post_id, $amt_keywords_field_name, true);
    }
    // Try old keywords field: ``keywords``
    elseif ( in_array('keywords', $custom_fields) ) {
        return get_post_meta($post_id, 'keywords', true);
    }
    // Try other keywords field names here.
    // Support reading from other plugins

    //Return empty string if all fails
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the custom content title.
 * The default field name for the title is ``_amt_title``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_title($post_id) {
    $amt_title_field_name = '_amt_title';

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys($post_id);

    // Just return an empty string if no custom fields have been associated with this content.
    if ( empty($custom_fields) ) {
        return '';
    }

    // First try our default title field
    if ( in_array($amt_title_field_name, $custom_fields) ) {
        return get_post_meta($post_id, $amt_title_field_name, true);
    }
    
    // Try other title field names here.
    // Support reading from other plugins

    //Return empty string if all fails
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the 'news_keywords' value.
 * The default field name for the 'news_keywords' is ``_amt_news_keywords``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_newskeywords($post_id) {
    $amt_newskeywords_field_name = '_amt_news_keywords';

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys($post_id);

    // Just return an empty string if no custom fields have been associated with this content.
    if ( empty($custom_fields) ) {
        return '';
    }

    // First try our default 'news_keywords' field
    if ( in_array($amt_newskeywords_field_name, $custom_fields) ) {
        return get_post_meta($post_id, $amt_newskeywords_field_name, true);
    }
    
    // Try other 'news_keywords' field names here.
    // Support reading from other plugins

    //Return empty string if all fails
    return '';
}


/**
 * Helper function that returns the value of the custom field that contains
 * the per-post full metatags.
 * The default field name is ``_amt_full_metatags``.
 * No need to migrate from older field name.
 */
function amt_get_post_meta_full_metatags($post_id) {
    $amt_full_metatags_field_name = '_amt_full_metatags';

    // Get an array of all custom fields names of the post
    $custom_fields = get_post_custom_keys($post_id);

    // Just return an empty string if no custom fields have been associated with this content.
    if ( empty($custom_fields) ) {
        return '';
    }

    // First try our default 'full_metatags' field
    if ( in_array($amt_full_metatags_field_name, $custom_fields) ) {
        return get_post_meta($post_id, $amt_full_metatags_field_name, true);
    }
    
    // Try other 'full_metatags' field names here.
    // Support reading from other plugins

    //Return empty string if all fails
    return '';
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
 * Opengraph helper functions
 */

function amt_get_video_url() {
    global $post;

    // Youtube
    //$pattern = '#youtube.com/watch\?v=([-|~_0-9A-Za-z]+)#';
    $pattern = '#http:\/\/(?:www.)?youtube.com\/.*v=(\w*)#';
    if ( preg_match($pattern, $post->post_content, $matches) ) {
        return 'http://youtube.com/v/' . $matches[1];
    }

    // Vimeo
    //$pattern = '#vimeo.com/([-|~_0-9A-Za-z]+)#';
    $pattern = '#http:\/\/(?:www.)?vimeo.com\/(\d*)#';
    if ( preg_match($pattern, $post->post_content, $matches) ) {
        //return 'http://vimeo.com/couchmode/' . $matches[1];
        //return 'http://vimeo.com/moogaloop.swf?clip_id=' . $matches[1];
        return 'http://player.vimeo.com/video/' . $matches[1];
    }

    return '';
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

