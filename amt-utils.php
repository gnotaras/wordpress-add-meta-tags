<?php
/**
 * Module containing utility functions.
 */


/**
 * Helper function that converts $text to lowercase.
 * If the mbstring php plugin exists, then the string functions provided by that
 * plugin are used.
 */
function amt_strtolower($text) {
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, get_bloginfo('charset'));
    } else {
        return strtolower($text);
    }
}


/**
 * This is a filter for the description metatag text.
 */
function amt_clean_desc($desc) {
    $desc = stripslashes($desc);
    $desc = strip_tags($desc);
    // Clean double quotes
    $desc = str_replace('"', '', $desc);
    $desc = htmlspecialchars($desc);
    //$desc = preg_replace('/(\n+)/', ' ', $desc);
    $desc = preg_replace('/([\n \t\r]+)/', ' ', $desc); 
    $desc = preg_replace('/( +)/', ' ', $desc);

    // Remove shortcode
    $pattern = get_shortcode_regex();
    //var_dump($pattern);
    $desc = preg_replace('#' . $pattern . '#s', '', $desc);

    return trim($desc);
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
 * This is a helper function that returns the current post's ID
 */
function amt_get_post_id() {
    if ( amt_is_static_front_page() ) {
        return amt_get_front_page_id();
    } elseif ( amt_is_static_home() ) {
        return amt_get_posts_page_id();
    } elseif ( is_singular() ) {
        global $post;
        return $post->ID;
        // Alt
        // global $posts;
        // return $posts[0]->ID
    }
}


/**
 * Helper function that returns the current post object
 */
function amt_get_current_post_object() {
    // Determine post object.
    if ( amt_is_static_home() ) {
        // If a static page is used as the page that displays the latest posts,
        // the available $post object is NOT the object of the static page,
        // but the object of the latest retrieved post.
        // This does not happen with the static page that is used as a front page.
        $post = get_post( amt_get_posts_page_id() );
    } else {
        //global $post;
        // Get current post.
        $post = get_post();
    }
    return $post;
}


/**
 * Opengraph helper functions
 */

function amt_get_video_url() {
    global $post;

    // Youtube
    $pattern = '#youtube.com/watch\?v=([-|~_0-9A-Za-z]+)#';
    if ( preg_match($pattern, $post->post_content, $matches) ) {
        return 'http://youtube.com/v/' . $matches[1];
    }

    // Vimeo
    $pattern = '#vimeo.com/([-|~_0-9A-Za-z]+)#';
    if ( preg_match($pattern, $post->post_content, $matches) ) {
        return 'http://vimeo.com/couchmode/' . $matches[1];
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

