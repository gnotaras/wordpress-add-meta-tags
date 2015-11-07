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
 * Module containing functions that implement the player container page.
 */


/**
 * Support for player embed page.
 *
 * See ticket: http://www.codetrax.org/issues/1294
 */


// Register function that adds Twitter Cards (TC) related rules on plugin activation.
function amt_embed_actions_on_plugin_activation() {
    // Add rewrite rules for the page that contains the embedded player.
    amt_embed_rewrite_rules();
    // See important notes about flushing rewrite rules at: http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
    flush_rewrite_rules();
}
register_activation_hook( AMT_PLUGIN_FILE, 'amt_embed_actions_on_plugin_activation' );


// Register function that adds Twitter Cards (TC) related rules on plugin activation.
function amt_embed_actions_on_plugin_deactivation() {
    // See important notes about flushing rewrite rules at: http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
    flush_rewrite_rules();
}
register_deactivation_hook( AMT_PLUGIN_FILE, 'amt_embed_actions_on_plugin_deactivation' );


// Adds the rewrite rules for the page with the local embedded player.
function amt_embed_rewrite_rules() {
    // NOTE: if `amt_embed_enabled` is filtered to `false`, it is required
    // to re-save the permalinks (Settings->Permalinks) or deactivate/activate
    // the plugin before embeds are fully disabled.
    if ( apply_filters( 'amt_embed_enabled', true ) ) {
        add_rewrite_rule( '^amtembed/([0-9]+)/?$', 'index.php?amtembed=$matches[1]', 'top' );
    }
}
add_action( 'init', 'amt_embed_rewrite_rules' );


// Register the query vars
function amt_embed_register_query_vars( $vars ) {
    // NOTE: if `amt_embed_enabled` is filtered to `false`, it is required
    // to re-save the permalinks (Settings->Permalinks) or deactivate/activate
    // the plugin before embeds are fully disabled.
    if ( apply_filters( 'amt_embed_enabled', true ) ) {
        $vars[] = 'amtembed';
        //var_dump($vars);
    }
    return $vars;
}
add_filter( 'query_vars', 'amt_embed_register_query_vars' );



function amt_embed_template_redirect() {

    if ( is_amtembed() ) {
        // The container page with the embedded audio/video has been requested.

        if ( ! is_ssl() && apply_filters( 'amt_embed_enforce_ssl', true ) ) {
            // Here we explicitly want an HTTPS URL and that's why `amt_make_https()` is used instead of `amt_embed_make_https()`.
            $location = amt_make_https( amt_embed_get_container_url( amt_embed_get_id() ) );
            //$location = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            wp_redirect( $location, 301 );
            exit;
        } else {
            // Make it compatible with WooCommerce
            // It has been brought to my attention that WooCommerce has an option that forces
            // a redirection from the HTTPS version of the page back to the HTTP version.
            // This happens only if the `woocommerce_unforce_ssl_checkout` option is checked.
            // Fortunatelly the WooCommerce developers have added a filter for easy resolution
            // of any issues. See the `class-wc-https.php` of the WooCommerce plugin.
            add_filter( 'woocommerce_unforce_ssl_checkout', 'amt_return_false' );
        }
    }
}
add_action( 'template_redirect', 'amt_embed_template_redirect', 10 );



function amt_embed_template_include( $default_template ) {

    if ( is_amtembed() ) {
        // The container page with the embedded audio/video has been requested.

        // We only process requests for local video or audio embedded attachments.
        $embed_type = amt_embed_get_type( amt_embed_get_id() );
        if ( $embed_type === false ) {
            return amt_force_404_template();
        }

        // Construct the template name
        $embed_template = sprintf('amt-embed-%s-template.php', $embed_type);
        //var_dump($embed_template);

        // Return the proper template
        // First try to locate the template in the theme directory
        // Here ``get_template_directory()`` is not used to retrieve the theme's directory
        // because in case of child themes, it returns the directory of the parent theme.
        // Instead we use ``get_stylesheet_directory()``.
        // See info:
        //   - http://codex.wordpress.org/Function_Reference/get_template_directory
        //   - http://codex.wordpress.org/Function_Reference/get_stylesheet_directory
        $template_in_theme_dir = get_stylesheet_directory() . '/' . $embed_template;
        //var_dump($template_in_theme_dir);
        $template_in_plugin_dir = AMT_PLUGIN_DIR . 'templates/' . $embed_template;
        //var_dump($template_in_plugin_dir);
        if ( file_exists( $template_in_theme_dir ) ) {
            return $template_in_theme_dir;
        } else {
            return $template_in_plugin_dir;
        }
        // In any other case of embed request, just return a 404 error.
        return amt_force_404_template();
    }
    // If this is not an embed, just return the default template.
    return $default_template;
}
add_filter( 'template_include', 'amt_embed_template_include', 10 );


function amt_force_404_template() {
    global $wp_query;
    // Use set_404() so that WordPress can correctly set any `is_*()` conditional tags.
    $wp_query->set_404();
    $wp_query->max_num_pages = 0; // stop theme from showing Next/Prev links
    status_header( 404 );
    // Set Cache-Control and Expires headers so that the response is not cached.
    nocache_headers();
    // Return the theme's 404 template.
    return get_404_template();
}


function is_amtembed() {
    if ( absint( get_query_var( 'amtembed' ) ) > 0 ) {
        return true;
    }
    return false;
}


// Returns an integer
function amt_embed_get_id() {
    if ( is_amtembed() ) {
        return absint( get_query_var( 'amtembed' ) );
    }
    return;
}


function amt_embed_get_container_url( $attachment_id ) {
    if ( ! empty( $attachment_id ) ) {
        return site_url( sprintf( 'amtembed/%d/', $attachment_id ) );
    }
    return '';
}


function amt_embed_get_stream_url( $attachment_id ) {
    if ( ! empty( $attachment_id ) ) {
        return wp_get_attachment_url( $attachment_id );
    }
    return '';
}


// Returns the embed type.
// Expects post id.
// Returns: string ('audio' or 'video'), boolean false if unsupported.
function amt_embed_get_type( $attachment_id ) {
    if ( empty( $attachment_id ) ) {
        return false;
    }
    // We only process video or audio attachments
    $attachment = get_post( $attachment_id );
    if ( get_post_type($attachment) != 'attachment' ) {
        return false;
    }
    $mime_type = get_post_mime_type( $attachment->ID );
    //$attachment_type = strstr( $mime_type, '/', true );
    // See why we do not use strstr(): http://www.codetrax.org/issues/1091
    $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );
    // Return embed type
    if ( 'video' == $attachment_type ) {
        return 'video';
    } elseif ( 'audio' == $attachment_type ) {
        return 'audio';
    } else {
        return false;
    }
}


// Accepts a URL and converts the protocol to https if certain conditions are met.
// Returns the processed URL.
function amt_embed_make_https( $url ) {
    // If the web site is served over https, convert the URL.
    if ( is_ssl() ) {
        return preg_replace( '#^http://#' , 'https://', $url );
    // If https is enforced on embeds, convert the URL.
    } elseif ( apply_filters( 'amt_embed_enforce_ssl', true ) ) {
        return preg_replace( '#^http://#' , 'https://', $url );
    // In any other case return the URL as is.
    } else {
        return $url;
    }
}


// Returns the URL to an image that can be used as the preview image of video
// or audio players or be displayed in the place of media players if they
// are not supported by the client.
function amt_embed_get_preview_image( $attachment_id ) {

    // This function has been primarily developed in order to return an image
    // URL suitable for the `twitter:image` meta tags of the player Twitter Card.
    //
    // From the Player Twitter Card specs about `twitter:image`:
    // Image to be displayed in place of the player on platforms that don’t support iframes or inline players.
    // You should make this image the same dimensions as your player. Images with fewer than 68,600 pixels
    // (a 262x262 square image, or a 350x196 16:9 image) will cause the player card not to render. Image must be less than 1MB in size.
    //
    // Here, we try to retrieve an image with size 'full'. The user is expected to have uploaded a properly resized image
    // or have resized the uploaded image using the WordPress image editing tools or, finally, have filtered the used image
    // size with a user-defined size that matches that of the video player (and not of the video itself).
    // NOTE: To create an on-demand properly resized image check out:
    //     http://codex.wordpress.org/Class_Reference/WP_Image_Editor
    $image_size = apply_filters( 'amt_image_video_preview', 'full' );
    // In WordPress audio and video attachments can have a featured image. In order to generate the
    // twitter:image meta tag of the player card, we first try to use the featured image of the __attachment__ and NOT of the post.
    if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $attachment_id ) ) {   // Read above why we use $attachment->ID
        $main_size_meta = wp_get_attachment_image_src( get_post_thumbnail_id( $attachment_id ), $image_size );
        return $main_size_meta[0];
    }
    // Alternatively we try to use the post's featured image if available.
    $attachment = get_post( $attachment_id );
    $parent_post_id = $attachment->post_parent;  // $attachment->post_parent: is the ID of the attachment post's parent post
    if ( ! empty( $parent_post_id ) && function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $parent_post_id ) ) {
        // We retrieve an image with size equal to the size of the player (array $image_size) (According to the specs).
        $main_size_meta = wp_get_attachment_image_src( get_post_thumbnail_id( $parent_post_id ), $image_size );
        return $main_size_meta[0];
    }
}


function amt_embed_get_includes_url( $path ) {
    return apply_filters( 'amt_embed_includes_url', includes_url( $path ) );
}

