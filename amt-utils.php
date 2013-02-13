<?php
/**
 * Module containing utility functions.
 */


function amt_strtolower($text) {
    /*
    Helper function that converts $text to lowercase.
    If the mbstring php plugin exists, then the string functions provided by that
    plugin are used.
    */
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, get_bloginfo('charset'));
    } else {
        return strtolower($text);
    }
}


function amt_clean_desc($desc) {
    /*
     * This is a filter for the description metatag text.
     */

    $desc = stripslashes($desc);
    $desc = strip_tags($desc);
    $desc = htmlspecialchars($desc);
    // Clean double quotes
    $desc = str_replace('"', '', $desc);
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


/** Helper function that returns true if a page is used as the homepage
 * instead of the posts index page.
 */
function amt_has_page_on_front() {
    $front_type = get_option('show_on_front', 'posts');
    if ( $front_type == 'page' ) {
        return true;
    }
    return false;
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

