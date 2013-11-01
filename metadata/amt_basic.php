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
 * Basic Metadata
 *
 * Module containing functions related to Basic Metadata
 */


/**
 * Generates basic metadata for the head area.
 *
 */
function amt_add_basic_metadata_head( $post, $attachments, $embedded_media, $options ) {

    $do_description = (($options["auto_description"] == "1") ? true : false );
    $do_keywords = (($options["auto_keywords"] == "1") ? true : false );
    $do_noodp_description = (($options["noodp_description"] == "1") ? true : false );

    // Array to store metadata
    $metadata_arr = array();

    // Add NOODP on posts and pages
    if ( $do_noodp_description && ( is_front_page() || is_singular() ) ) {
        $metadata_arr[] = '<meta name="robots" content="NOODP,NOYDIR" />';
    }


    // Default front page displaying latest posts
    if ( amt_is_default_front_page() ) {

        // Description and Keywords from the Add-Meta-Tags settings override
        // default behaviour.

        // Description
        if ($do_description) {
            // Use the site description from the Add-Meta-Tags settings.
            // Fall back to the blog description.
            $site_description = $options["site_description"];
            if ( empty($site_description ) ) {
                // Alternatively, use the blog description
                // Here we sanitize the provided description for safety
                $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
            }
            // If we have a description, use it in the description meta-tag of the front page
            if ( ! empty( $site_description ) ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $site_description ) ) . '" />';
            }
        }

        // Keywords
        if ($do_keywords) {
            // Use the site keywords from the Add-Meta-Tags settings.
            // Fall back to the blog categories.
            $site_keywords = $options["site_keywords"];
            if ( empty( $site_keywords ) ) {
                // Alternatively, use the blog categories
                // Here we sanitize the provided keywords for safety
                $site_keywords = sanitize_text_field( amt_sanitize_keywords( amt_get_all_categories() ) );
            }
            // If we have keywords, use them in the keywords meta-tag of the front page
            if ( ! empty( $site_keywords ) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $site_keywords ) . '" />';
            }
        }


    // Attachments
    } elseif ( is_attachment() ) {  // has to be before is_singular() since is_singular() is true for attachments.

        // Description
        if ($do_description) {
            $description = amt_get_content_description($post, $auto=$do_description);
            if ( ! empty($description ) ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description ) ) . '" />';
            }
        }

        // No keywords


    // Content pages and static pages used as "front page" and "posts page"
    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        // Description
        if ($do_description) {
            $description = amt_get_content_description($post, $auto=$do_description);
            if ( ! empty( $description ) ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description ) ) . '" />';
            }
        }

        // Keywords
        if ($do_keywords) {
            $keywords = amt_get_content_keywords($post, $auto=$do_keywords);
            if ( ! empty( $keywords ) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />';

            // Static Posts Index Page
            // If no keywords have been set in the metabox and this is the static page,
            // which displayes the latest posts, use the categories of the posts in the loop.
            } elseif ( amt_is_static_home() ) {
                // Here we sanitize the provided keywords for safety
                $cats_from_loop = sanitize_text_field( amt_sanitize_keywords( implode( ', ', amt_get_categories_from_loop() ) ) );
                if ( ! empty( $cats_from_loop ) ) {
                    $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $cats_from_loop ) . '" />';
                }
            }
        }

        // 'news_keywords'
        $newskeywords = amt_get_post_meta_newskeywords( $post->ID );
        if ( ! empty( $newskeywords ) ) {
            $metadata_arr[] = '<meta name="news_keywords" content="' . esc_attr( $newskeywords ) . '" />';
        }

        // per post full meta tags
        $full_metatags_for_content = amt_get_post_meta_full_metatags( $post->ID );
        if ( ! empty( $full_metatags_for_content ) ) {
            $metadata_arr[] = html_entity_decode( stripslashes( $full_metatags_for_content ) );
        }


    // Category based archives
    } elseif ( is_category() ) {

        if ($do_description) {
            // If set, the description of the category is used in the 'description' metatag.
            // Otherwise, a generic description is used.
            // Here we sanitize the provided description for safety
            $description_content = sanitize_text_field( amt_sanitize_description( category_description() ) );
            // Note: Contains multipage information through amt_process_paged()
            if ( empty( $description_content ) ) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( 'Content filed under the ' . single_cat_title( $prefix='', $display=false ) . ' category.' ) ) . '" />';
            } else {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        if ($do_keywords) {
            // The category name alone is included in the 'keywords' metatag
            // Here we sanitize the provided keywords for safety
            $cur_cat_name = sanitize_text_field( amt_sanitize_keywords( single_cat_title($prefix = '', $display = false ) ) );
            if ( ! empty($cur_cat_name) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $cur_cat_name ) . '" />';
            }
        }

    } elseif ( is_tag() ) {

        if ($do_description) {
            // If set, the description of the tag is used in the 'description' metatag.
            // Otherwise, a generic description is used.
            // Here we sanitize the provided description for safety
            $description_content = sanitize_text_field( amt_sanitize_description( tag_description() ) );
            // Note: Contains multipage information through amt_process_paged()
            if ( empty( $description_content ) ) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( 'Content tagged with ' . single_tag_title( $prefix='', $display=false ) . '.' ) ) . '" />';
            } else {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        if ($do_keywords) {
            // The tag name alone is included in the 'keywords' metatag
            // Here we sanitize the provided keywords for safety
            $cur_tag_name = sanitize_text_field( amt_sanitize_keywords( single_tag_title($prefix = '', $display = false ) ) );
            if ( ! empty($cur_tag_name) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $cur_tag_name ) . '" />';
            }
        }

    } elseif ( is_author() ) {

        // Author object
        // NOTE: Inside the author archives `$post->post_author` does not contain the author object.
        // In this case the $post (get_queried_object()) contains the author object itself.
        // We also can get the author object with the following code. Slug is what WP uses to construct urls.
        // $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
        // Also, ``get_the_author_meta('....', $author)`` returns nothing under author archives.
        // Access user meta with:  $author->description, $author->user_email, etc
        // $author = get_queried_object();
        $author = $post;

        // If a bio has been set in the user profile, use it in the description metatag of the
        // first page of the author archive *ONLY*. The other pages of the author archive use a generic description.
        // This happens because the 1st page of the author archive is considered the profile page
        // by the other metadata modules.
        // Otherwise use a generic meta tag.
        if ($do_description) {
            // Here we sanitize the provided description for safety
            $author_description = sanitize_text_field( amt_sanitize_description( $author->description ) );
            if ( empty( $author_description ) || is_paged() ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( amt_process_paged( 'Content published by ' . $author->display_name . '.' ) ) . '" />';
            } else {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( $author_description ) . '" />';
            }
        }
        
        // For the keywords metatag use the categories of the posts the author has written and are displayed in the current page.
        if ($do_keywords) {
            // Here we sanitize the provided keywords for safety
            $cats_from_loop = sanitize_text_field( amt_sanitize_keywords( implode( ', ', amt_get_categories_from_loop() ) ) );
            if ( ! empty( $cats_from_loop ) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $cats_from_loop ) . '" />';
            }
        }
        
    }

    // Add site wide meta tags
    if ( ! empty( $options["site_wide_meta"] ) ) {
        $metadata_arr[] = html_entity_decode( stripslashes( $options["site_wide_meta"] ) );
    }

    // On every page print the copyright head link
    if ( ! empty( $options["copyright_url"] ) ) {
        $metadata_arr[] = '<link rel="copyright" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . ' Copyright Information" href="' . esc_url_raw( $options["copyright_url"] ) . '" />';
    }

    // Filtering of the generated basic metadata
    $metadata_arr = apply_filters( 'amt_basic_metadata_head', $metadata_arr );

    return $metadata_arr;
}

