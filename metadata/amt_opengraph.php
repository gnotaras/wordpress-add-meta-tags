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
 * Opengraph Protocol Metadata
 * Opengraph Specification: http://ogp.me
 *
 * Module containing functions related to Opengraph Protocol Metadata
 *
 * article object: https://developers.facebook.com/docs/reference/opengraph/object-type/article/
 * video.other object: https://developers.facebook.com/docs/reference/opengraph/object-type/video.other/
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


/**
 * Add contact method for Facebook author and publisher.
 */
function amt_add_facebook_contactmethod( $contactmethods ) {

    // Add Facebook Author Profile URL
    if ( !isset( $contactmethods['amt_facebook_author_profile_url'] ) ) {
        $contactmethods['amt_facebook_author_profile_url'] = __('Facebook author profile URL', 'add-meta-tags') . ' (AMT)';
    }

    // The publisher profile box in the WordPress user profile page can be deactivated via filtering.
    if ( apply_filters( 'amt_allow_publisher_settings_in_user_profiles', false ) ) {
        // Add Facebook Publisher Profile URL
        if ( !isset( $contactmethods['amt_facebook_publisher_profile_url'] ) ) {
            $contactmethods['amt_facebook_publisher_profile_url'] = __('Facebook publisher profile URL', 'add-meta-tags') . ' (AMT)';
        }
    }

    // Remove test
    // if ( isset( $contactmethods['test'] ) {
    //     unset( $contactmethods['test'] );
    // }

    return $contactmethods;
}
add_filter( 'user_contactmethods', 'amt_add_facebook_contactmethod', 10, 1 );


/**
 * Generates Opengraph metadata.
 *
 * Currently for:
 * - home page
 * - author archive
 * - content
 */
function amt_add_opengraph_metadata_head( $post, $attachments, $embedded_media, $options ) {

    if ( apply_filters('amt_exclude_opengraph_metadata', false) ) {
        return array();
    }

    $do_auto_opengraph = (($options["auto_opengraph"] == "1") ? true : false );
    if (!$do_auto_opengraph) {
        return array();
    }

    $metadata_arr = array();

    // fb:app_id & fb:admins
    // We currently let users add the full meta tags for fb:app_id and fb:admins in the site wide meta tags box.
    // fb:app_id appears everywhere
    //if ( ! empty($options['social_main_facebook_app_id']) ) {
    //    $metadata_arr[] = '<meta property="fb:app_id" content="' . esc_attr( $options['social_main_facebook_app_id'] ) . '" />';
    //}
    // fb:admins appear everywhere
    //if ( ! empty($options['social_main_facebook_admins']) ) {
    //    $fb_admins_arr = explode(',', $options['social_main_facebook_admins']);
    //    foreach ( $fb_admins_arr as $fb_admin ) {
    //        $metadata_arr[] = '<meta property="fb:admins" content="' . esc_attr( trim($fb_admin) ) . '" />';
    //    }
    //}
    // no publisher meta tag for facebook, unless it is content

    // Custom content override
    if ( amt_is_custom($post, $options) ) {

        // Return metadata with:
        // add_filter( 'amt_custom_metadata_opengraph', 'my_function', 10, 5 );
        // Return an array of meta tags. Array item format: ['key_can_be_whatever'] = '<meta name="foo" content="bar" />'
        $metadata_arr = apply_filters( 'amt_custom_metadata_opengraph', $metadata_arr, $post, $options, $attachments, $embedded_media );

    // Default front page displaying the latest posts
    } elseif ( amt_is_default_front_page() ) {

        // Type
        $metadata_arr['og:type'] = '<meta property="og:type" content="website" />';
        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title - Note: Contains multipage information
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL - Note: different method to get the permalink on paged archives
        if ( is_paged() ) {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_pagenum_link( get_query_var('paged') ) ) . '" />';
        } else {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
        }
        // Site description - Note: Contains multipage information through amt_process_paged()
        $site_description = amt_get_site_description($options);
        if ( empty( $site_description ) ) {
            $site_description = get_bloginfo('description');
        }
        if ( ! empty($site_description) ) {
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $site_description ) ) . '" />';
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_site($options)) ) . '" />';
        // Site Image
        // Use the default image, if one has been set.
        $image_data = amt_get_default_image_data();
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            }
        }


    // Front page using a static page
    // Note: might also contain a listing of posts which may be paged, so use amt_process_paged()
    } elseif ( amt_is_static_front_page() ) {

        // Type
        if ( $options['author_profile_source'] == 'frontpage' ) {
            // The front page is treated as the profile page.
            $metadata_arr['og:type'] = '<meta property="og:type" content="profile" />';
        } else {
            $metadata_arr['og:type'] = '<meta property="og:type" content="website" />';
        }

        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title - Note: Contains multipage information
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL - Note: different method to get the permalink on paged archives
        if ( is_paged() ) {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_pagenum_link( get_query_var('paged') ) ) . '" />';
        } else {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
        }
        // Site Description - Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            // Use the pages custom description
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        } elseif (get_bloginfo('description')) {
            // Alternatively use the blog's description
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( get_bloginfo('description') ) ) . '" />';
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) ) . '" />';
        // Site Image
        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            }
        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
        //    $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
        //        $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $global_image_override_url ) ) . '" />';
        //    }
        // Then try the featured image, if exists.
        } elseif ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $metadata_arr = array_merge( $metadata_arr, amt_get_opengraph_image_metatags( $options, get_post_thumbnail_id( $post->ID ), $size=$image_size ) );
        } else {
            // Use the default image, if one has been set.
            $image_data = amt_get_default_image_data();
            if ( ! empty($image_data) ) {
                $image_size = apply_filters( 'amt_image_size_index', 'full' );
                $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
                if ( ! empty($image_meta_tags) ) {
                    $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                }
            }
            // Alternatively, use default image
            //$metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
            //if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
            //    $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $options["default_image_url"] ) ) . '" />';
            //}
        }

        // Profile data (only if the front page has been set as the source of profile.)
        if ( $options['author_profile_source'] == 'frontpage' ) {
            // Profile first and last name
            $last_name = get_the_author_meta( 'last_name', $post->post_author );
            if ( !empty($last_name) ) {
                $metadata_arr[] = '<meta property="profile:last_name" content="' . esc_attr( $last_name ) . '" />';
            }
            $first_name = get_the_author_meta( 'first_name', $post->post_author );
            if ( !empty($first_name) ) {
                $metadata_arr[] = '<meta property="profile:first_name" content="' . esc_attr( $first_name ) . '" />';
            }
        }


    // The posts index page - a static page displaying the latest posts
    } elseif ( amt_is_static_home() ) {

        // Type
        $metadata_arr['og:type'] = '<meta property="og:type" content="website" />';
        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title - Note: Contains multipage information
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL - Note: different method to get the permalink on paged archives
        if ( is_paged() ) {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_pagenum_link( get_query_var('paged') ) ) . '" />';
        } else {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_permalink($post->ID) ) . '" />';
        }
        // Site Description - Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            // Use the pages custom description
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        } elseif (get_bloginfo('description')) {
            // Alternatively use a generic description
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . amt_process_paged( "An index of the latest content." ) . '" />';
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) ) . '" />';
        // Site Image
        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            }
        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
        //    $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
        //        $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $global_image_override_url ) ) . '" />';
        //    }
        // Then try the featured image, if exists.
        } elseif ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $metadata_arr = array_merge( $metadata_arr, amt_get_opengraph_image_metatags( $options, get_post_thumbnail_id( $post->ID ), $size=$image_size ) );
        } else {
            // Use the default image, if one has been set.
            $image_data = amt_get_default_image_data();
            if ( ! empty($image_data) ) {
                $image_size = apply_filters( 'amt_image_size_index', 'full' );
                $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
                if ( ! empty($image_meta_tags) ) {
                    $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                }
            }
            // Alternatively, use default image
            //$metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
            //if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
            //    $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $options["default_image_url"] ) ) . '" />';
            //}
        }


    // Category, Tag, Taxonomy archives
    // Note: product groups should pass the is_tax() validation, so no need for
    // amt_is_product_group(). We do not support other product groups.
    // amt_is_product_group() is used below to set the og:type to product.group.
    } elseif ( is_category() || is_tag() || is_tax() ) {
        // Taxonomy term object.
        // When viewing taxonomy archives, the $post object is the taxonomy term object. Check with: var_dump($post);
        $tax_term_object = $post;
        //var_dump($tax_term_object);

        // Type
        // In case of a product group taxonomy, we set the og:type to product.group
        if ( amt_is_product_group() ) {
            $metadata_arr['og:type'] = '<meta property="og:type" content="product.group" />';
        } else {
            $metadata_arr['og:type'] = '<meta property="og:type" content="website" />';
        }
        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title - Note: Contains multipage information
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL - Note: different method to get the permalink on paged archives
        $url = get_term_link($tax_term_object);
        if ( is_paged() ) {
            $url = trailingslashit( $url ) . get_query_var('paged') . '/';
        }
        $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( $url ) . '" />';
        // Description
        // If set, the description of the custom taxonomy term is used in the 'description' metatag.
        // Otherwise, a generic description is used.
        // Here we sanitize the provided description for safety
        $description_content = sanitize_text_field( amt_sanitize_description( term_description( $tax_term_object->term_id, $tax_term_object->taxonomy ) ) );
        // Note: Contains multipage information through amt_process_paged()
        if ( empty( $description_content ) ) {
            // Add a filtered generic description.
            // Filter name
            if ( is_category() ) {
                $generic_description = apply_filters( 'amt_generic_description_category_archive', __('Content filed under the %s category.', 'add-meta-tags') );
            } elseif ( is_tag() ) {
                $generic_description = apply_filters( 'amt_generic_description_tag_archive', __('Content tagged with %s.', 'add-meta-tags') );
            } elseif ( is_tax() ) {
                // Construct the filter name. Template: ``amt_generic_description_TAXONOMYSLUG_archive``
                $taxonomy_description_filter_name = sprintf( 'amt_generic_description_%s_archive', $tax_term_object->taxonomy);
                // var_dump($taxonomy_description_filter_name);
                // Generic description
                $generic_description = apply_filters( $taxonomy_description_filter_name, __('Content filed under the %s taxonomy.', 'add-meta-tags') );
            }
            // Final generic description
            $generic_description = sprintf( $generic_description, single_term_title( $prefix='', $display=false ) );
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
        } else {
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_site($options)) ) . '" />';
        // Image
        // Use an image from the 'Global image override' field.
        // Otherwise, use a user defined image via filter.
        // Otherwise use default image.
        $image_data = amt_get_image_data( amt_get_term_meta_image_url( $tax_term_object->term_id ) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            }
        } else {
            // First filter using a term/taxonomy agnostic filter name.
            $taxonomy_image_url = apply_filters( 'amt_taxonomy_force_image_url', '', $tax_term_object );
            if ( empty($taxonomy_image_url) ) {
                // Second filter (term/taxonomy dependent).
                // Construct the filter name. Template: ``amt_taxonomy_image_url_TAXONOMYSLUG_TERMSLUG``
                $taxonomy_image_url_filter_name = sprintf( 'amt_taxonomy_image_url_%s_%s', $tax_term_object->taxonomy, $tax_term_object->slug);
                //var_dump($taxonomy_image_url_filter_name);
                // The default image, if set, is used by default.
                $taxonomy_image_url = apply_filters( $taxonomy_image_url_filter_name, $options["default_image_url"] );
            }
            if ( ! empty( $taxonomy_image_url ) ) {
                $image_data = amt_get_image_data( $taxonomy_image_url );
                if ( ! empty($image_data) ) {
                    $image_size = apply_filters( 'amt_image_size_index', 'full' );
                    $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
                    if ( ! empty($image_meta_tags) ) {
                        $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                    }
                }
                //$metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $taxonomy_image_url ) . '" />';
                //if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
                //    $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $taxonomy_image_url ) ) . '" />';
                //}
            }
        }


    // Author archive. First page is considered a profile page.
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

        // Type
        if ( ! is_paged() &&  $options['author_profile_source'] == 'default' ) {
            // We treat the first page of the archive as a profile, only if
            // the profile source has been set to 'default'
            $metadata_arr['og:type'] = '<meta property="og:type" content="profile" />';
        } else {
            $metadata_arr['og:type'] = '<meta property="og:type" content="website" />';
        }
        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title - Note: Contains multipage information
        //if ( ! is_paged() ) {
        //    // We treat the first page of the archive as a profile
        //    $metadata_arr[] = '<meta property="og:title" content="' . esc_attr( $author->display_name ) . ' profile page" />';
        //} else {
        //    $metadata_arr[] = '<meta property="og:title" content="' . esc_attr( amt_process_paged( "Content published by " . $author->display_name ) ) . '" />';
        //}
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . ' profile page" />';

        // URL - Note: different method to get the permalink on paged archives
        // If a Facebook author profile URL has been provided, it has priority,
        // Otherwise fall back to the WordPress author archive.
        $fb_author_url = $author->amt_facebook_author_profile_url;
        if ( !empty($fb_author_url) ) {
            $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( $fb_author_url, array('http', 'https') ) . '" />';
        } else {
            if ( is_paged() ) {
                $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_pagenum_link( get_query_var('paged') ) ) . '" />';
            } else {
                $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_author_posts_url( $author->ID ) ) . '" />';
                // The following makes no sense here. 'get_author_posts_url( $author->ID )' will do in all cases.
                //$metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( amt_get_local_author_profile_url( $author->ID, $options ) ) . '" />';
            }
        }
        // description - Note: Contains multipage information through amt_process_paged()
        if ( is_paged() ) {
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( "Content published by " . $author->display_name ) ) . '" />';
        } else {
            // Here we sanitize the provided description for safety
            // We treat the first page of the archive as a profile
            $author_description = sanitize_text_field( amt_sanitize_description( $author->description ) );
            if ( empty($author_description) ) {
                $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( "Content published by " . $author->display_name ) . '" />';
            } else {
                $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( $author_description ) . '" />';
            }
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_site($options)) ) . '" />';

        // Profile Image
        // First use the global image override URL
        $image_data = amt_get_image_data( amt_get_user_meta_image_url( $author->ID ) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_index', 'full' );
            $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            }
        } else {
            $author_email = sanitize_email( $author->user_email );
            $avatar_size = apply_filters( 'amt_avatar_size', 128 );
            $avatar_url = '';
            // First try to get the avatar link by using get_avatar().
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_img = get_avatar( get_the_author_meta('ID', $author->ID), $avatar_size, '', get_the_author_meta('display_name', $author->ID) );
            if ( ! empty($avatar_img) ) {
                if ( preg_match("#src=['\"]([^'\"]+)['\"]#", $avatar_img, $matches) ) {
                    $avatar_url = $matches[1];
                }
            } elseif ( ! empty($author_email) ) {
                // If the user has provided an email, we use it to construct a gravatar link.
                $avatar_url = "http://www.gravatar.com/avatar/" . md5( $author_email ) . "?s=" . $avatar_size;
            }
            if ( ! empty($avatar_url) ) {
                //$avatar_url = html_entity_decode($avatar_url, ENT_NOQUOTES, 'UTF-8');
                $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $avatar_url ) . '" />';
                // Add an og:imagesecure_url if the image URL uses HTTPS
                if ( strpos($avatar_url, 'https://') !== false ) {
                    $metadata_arr[] = '<meta property="og:imagesecure_url" content="' . esc_url_raw( $avatar_url ) . '" />';
                }
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $avatar_size ) . '" />';
                    $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $avatar_size ) . '" />';
                    // Since we do not have a way to determine the image type, the following meta tag is commented out
                    // TODO: make a function that detects the image type from the file extension (if a file extension is available)
                    //$metadata_arr[] = '<meta property="og:image:type" content="image/jpeg" />';
                }
            }
        }

        // Profile data (only on the 1st page of the archive)
        if ( ! is_paged() &&  $options['author_profile_source'] == 'default' ) {
            // Profile first and last name
            $last_name = $author->last_name;
            if ( !empty($last_name) ) {
                $metadata_arr[] = '<meta property="profile:last_name" content="' . esc_attr( $last_name ) . '" />';
            }
            $first_name = $author->first_name;
            if ( !empty($first_name) ) {
                $metadata_arr[] = '<meta property="profile:first_name" content="' . esc_attr( $first_name ) . '" />';
            }
        }


    // Custom Post Type archives
    } elseif ( is_post_type_archive() ) {
        // Custom post type object.
        // When viewing custom post type archives, the $post object is the custom post type object. Check with: var_dump($post);
        $post_type_object = $post;
        //var_dump($post_type_object);

        // Type
        $metadata_arr['og:type'] = '<meta property="og:type" content="website" />';
        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title - Note: Contains multipage information
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL - Note: different method to get the permalink on paged archives
        $url = get_post_type_archive_link($post_type_object->name);
        if ( is_paged() ) {
            $url = trailingslashit( $url ) . get_query_var('paged') . '/';
        }
        $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( $url ) . '" />';
        // Description
        // Note: Contains multipage information through amt_process_paged()
        // Add a filtered generic description.
        // Construct the filter name. Template: ``amt_generic_description_posttype_POSTTYPESLUG_archive``
        $custom_post_type_description_filter_name = sprintf( 'amt_generic_description_posttype_%s_archive', $post_type_object->name);
        // var_dump($custom_post_type_description_filter_name);
        // Generic description
        $generic_description = apply_filters( $custom_post_type_description_filter_name, __('%s archive.', 'add-meta-tags') );
        // Final generic description
        $generic_description = sprintf( $generic_description, post_type_archive_title( $prefix='', $display=false ) );
        $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_site($options)) ) . '" />';
        // Image
        // Use a user defined image via filter. Otherwise use default image.
        // First filter using a term/taxonomy agnostic filter name.
        $posttype_image_url = apply_filters( 'amt_posttype_force_image_url', '', $post_type_object );
        if ( empty($posttype_image_url) ) {
            // Second filter (post type dependent).
            // Construct the filter name. Template: ``amt_posttype_image_url_POSTTYPESLUG``
            $posttype_image_url_filter_name = sprintf( 'amt_posttype_image_url_%s', $post_type_object->name);
            //var_dump($posttype_image_url_filter_name);
            // The default image, if set, is used by default.
            $posttype_image_url = apply_filters( $posttype_image_url_filter_name, $options["default_image_url"] );
        }
        if ( ! empty( $posttype_image_url ) ) {
            $image_data = amt_get_image_data( $posttype_image_url );
            if ( ! empty($image_data) ) {
                $image_size = apply_filters( 'amt_image_size_index', 'full' );
                $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
                if ( ! empty($image_meta_tags) ) {
                    $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                }
            }
            //$metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $posttype_image_url ) . '" />';
            //if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
            //    $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $posttype_image_url ) ) . '" />';
            //}
        }


    // Attachments
    } elseif ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        // First add metadata common to all attachment types.

        // Type
        // Note: there is no specific type for images/audio. We use article amd video
        // TODO: Check whether we could use another type specific to each attachment type.
        if ( 'video' == $attachment_type ) {
            // video.other og:type for video attachment pages
            $og_type = 'video.other';
        } else {
            $og_type = 'article';
        }
        $og_type = apply_filters( 'amt_opengraph_og_type_attachment', $og_type );
        $metadata_arr['og:type'] = '<meta property="og:type" content="' . esc_attr( $og_type ) . '" />';

        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL
        $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( get_permalink($post->ID) ) . '" />';
        // Description - We use the description defined by Add-Meta-Tags
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( $content_desc ) . '" />';
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) ) . '" />';

        // og:updated_time
        $metadata_arr['og:updated_time'] = '<meta property="og:updated_time" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';

        // Metadata specific to each attachment type

        if ( 'image' == $attachment_type ) {

            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
            $metadata_arr = array_merge( $metadata_arr, amt_get_opengraph_image_metatags( $options, $post->ID, $size=$image_size ) );

        } elseif ( 'video' == $attachment_type ) {
            
            if ( $options["og_omit_video_metadata"] != "1" ) {
                // Video tags
                $metadata_arr[] = '<meta property="og:video" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';
                if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
                    $metadata_arr[] = '<meta property="og:video:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', wp_get_attachment_url($post->ID)) ) . '" />';
                }
                //
                //$metadata_arr[] = '<meta property="og:video:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                //$metadata_arr[] = '<meta property="og:video:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
                $metadata_arr[] = '<meta property="og:video:type" content="' . esc_attr( $mime_type ) . '" />';
            }

        } elseif ( 'audio' == $attachment_type ) {
            
            // Audio tags
            $metadata_arr[] = '<meta property="og:audio" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';
            if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
                $metadata_arr[] = '<meta property="og:audio:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', wp_get_attachment_url($post->ID)) ) . '" />';
            }
            $metadata_arr[] = '<meta property="og:audio:type" content="' . esc_attr( $mime_type ) . '" />';
        }

        // Article: meta tags

        // Dates
        $metadata_arr['article:published_time'] = '<meta property="article:published_time" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
        $metadata_arr['article:modified_time'] = '<meta property="article:modified_time" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
        // Author
        // If a Facebook author profile URL has been provided, it has priority,
        // Otherwise fall back to the WordPress author archive.
        $fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $post->post_author);
        if ( !empty($fb_author_url) ) {
            $metadata_arr['article:author'] = '<meta property="article:author" content="' . esc_url_raw( $fb_author_url, array('http', 'https', 'mailto') ) . '" />';
        } else {
            //$metadata_arr['article:author'] = '<meta property="article:author" content="' . esc_url_raw( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '" />';
            $metadata_arr['article:author'] = '<meta property="article:author" content="' . esc_url_raw( amt_get_local_author_profile_url( get_the_author_meta( 'ID', $post->post_author ), $options ) ) . '" />';
        }
        // Publisher
        // If a Facebook publisher profile URL has been provided, it has priority.
        // Otherwise fall back to the WordPress blog home url.
        if ( ! empty($options['social_main_facebook_publisher_profile_url']) ) {
            $metadata_arr['article:publisher'] = '<meta property="article:publisher" content="' . esc_url_raw( $options['social_main_facebook_publisher_profile_url'], array('http', 'https', 'mailto') ) . '" />';
        } else {
            $metadata_arr['article:publisher'] = '<meta property="article:publisher" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
        }


    // Posts, pages, custom content types (attachments excluded, caught in previous clause)
    // Note: content might be multipage. Process with amt_process_paged() wherever needed.
    } elseif ( is_singular() ) {

        // Site Name
        $metadata_arr['og:site_name'] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';

        // Type
        // og:type set to 'video.other' for posts with post format set to video
        if ( get_post_format($post->ID) == 'video' ) {
            $og_type = 'video.other';
        // og:type set to 'product' if amt_is_product() validates
        // See:
        //  * https://developers.facebook.com/docs/reference/opengraph/object-type/product/
        //  * https://developers.facebook.com/docs/payments/product
        } elseif ( amt_is_product() ) {
            $og_type = 'product';
        // In any other case 'article' is used as the og:type
        } else {
            $og_type = 'article';
        }
        // Allow filtering of og:type
        $og_type = apply_filters( 'amt_opengraph_og_type_content', $og_type );
        // Set og:type meta tag.
        $metadata_arr['og:type'] = '<meta property="og:type" content="' . esc_attr( $og_type ) . '" />';

        // Title
        // Note: Contains multipage information
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // URL - Uses amt_get_permalink_for_multipage()
        $metadata_arr['og:url'] = '<meta property="og:url" content="' . esc_url_raw( amt_get_permalink_for_multipage($post) ) . '" />';
        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr['og:description'] = '<meta property="og:description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }
        // Locale
        $metadata_arr['og:locale'] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) ) . '" />';

        // og:updated_time
        $metadata_arr['og:updated_time'] = '<meta property="og:updated_time" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';

        // Image

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_content', 'full' );
            $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            }
        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
        //    $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
        //        $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $global_image_override_url ) ) . '" />';
        //    }

        // Further image processing
        } else {

            // Media Limits
            $image_limit = amt_metadata_get_image_limit($options);
            $video_limit = amt_metadata_get_video_limit($options);
            $audio_limit = amt_metadata_get_audio_limit($options);

            // Counters
            $ic = 0;    // image counter
            $vc = 0;    // video counter
            $ac = 0;    // audio counter

            // We store the featured image ID in this variable so that it can easily be excluded
            // when all images are parsed from the $attachments array.
            $featured_image_id = 0;
            // Set to true if any image attachments are found. Use to finally add the default image
            // if no image attachments have been found.
            $has_images = false;

            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Allow filtering of the image size.
                $image_size = apply_filters( 'amt_image_size_content', 'full' );
                $metadata_arr = array_merge( $metadata_arr, amt_get_opengraph_image_metatags( $options, get_post_thumbnail_id( $post->ID ), $size=$image_size ) );
                // Finally, set the $featured_image_id
                $featured_image_id = get_post_thumbnail_id( $post->ID );
                // Images have been found.
                $has_images = true;
                // Increase image counter
                $ic++;
            }

            // Process all attachments and add metatags (featured image will be excluded)
            foreach( $attachments as $attachment ) {

                // Excluded the featured image since 
                if ( $attachment->ID != $featured_image_id ) {
                    
                    $mime_type = get_post_mime_type( $attachment->ID );
                    //$attachment_type = strstr( $mime_type, '/', true );
                    // See why we do not use strstr(): http://www.codetrax.org/issues/1091
                    $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

                    if ( 'image' == $attachment_type && $ic < $image_limit ) {

                        // Image tags
                        // Allow filtering of the image size.
                        $image_size = apply_filters( 'amt_image_size_content', 'full' );
                        $metadata_arr = array_merge( $metadata_arr, amt_get_opengraph_image_metatags( $options, $attachment->ID, $size=$image_size ) );

                        // Images have been found.
                        $has_images = true;
                        // Increase image counter
                        $ic++;
                        
                    } elseif ( 'video' == $attachment_type && $vc < $video_limit ) {
                        
                        if ( $options["og_omit_video_metadata"] != "1" ) {
                            // Video tags
                            $metadata_arr[] = '<meta property="og:video" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';
                            if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
                                $metadata_arr[] = '<meta property="og:video:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', wp_get_attachment_url($attachment->ID)) ) . '" />';
                            }
                            //$metadata_arr[] = '<meta property="og:video:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
                            //$metadata_arr[] = '<meta property="og:video:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
                            $metadata_arr[] = '<meta property="og:video:type" content="' . esc_attr( $mime_type ) . '" />';

                            // Increase video counter
                            $vc++;
                        }

                    } elseif ( 'audio' == $attachment_type && $ac < $audio_limit ) {
                        
                        // Audio tags
                        $metadata_arr[] = '<meta property="og:audio" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';
                        if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
                            $metadata_arr[] = '<meta property="og:audio:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', wp_get_attachment_url($attachment->ID)) ) . '" />';
                        }
                        $metadata_arr[] = '<meta property="og:audio:type" content="' . esc_attr( $mime_type ) . '" />';

                        // Increase audio counter
                        $ac++;
                    }

                }
            }

            // Embedded Media
            foreach( $embedded_media['images'] as $embedded_item ) {

                if ( $ic == $image_limit ) {
                    break;
                }

                $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';
                $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $embedded_item['image']) ) . '" />';
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
                    $metadata_arr[] = '<meta property="og:image:type" content="image/jpeg" />';
                }

                // Images have been found.
                $has_images = true;
                // Increase image counter
                $ic++;

            }
            foreach( $embedded_media['videos'] as $embedded_item ) {

                if ( $options["og_omit_video_metadata"] != "1" ) {

                    if ( $vc == $video_limit ) {
                        break;
                    }

                    $metadata_arr[] = '<meta property="og:video" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
                    $metadata_arr[] = '<meta property="og:video:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $embedded_item['player']) ) . '" />';
                    $metadata_arr[] = '<meta property="og:video:type" content="application/x-shockwave-flash" />';
                    $metadata_arr[] = '<meta property="og:video:width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="og:video:height" content="' . esc_attr( $embedded_item['height'] ) . '" />';

                    // Increase video counter
                    $vc++;
                }

            }
            foreach( $embedded_media['sounds'] as $embedded_item ) {

                if ( $ac == $audio_limit ) {
                    break;
                }

                $metadata_arr[] = '<meta property="og:audio" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
                $metadata_arr[] = '<meta property="og:audio:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $embedded_item['player']) ) . '" />';
                $metadata_arr[] = '<meta property="og:audio:type" content="application/x-shockwave-flash" />';

                // Increase audio counter
                $ac++;
            }

            // If no images have been found so far use the default image, if set.
            if ( $has_images === false ) {

                // Use the default image, if one has been set.
                $image_data = amt_get_default_image_data();
                if ( ! empty($image_data) ) {
                    $image_size = apply_filters( 'amt_image_size_content', 'full' );
                    $image_meta_tags = amt_get_opengraph_image_metatags( $options, $image_data, $size=$image_size );
                    if ( ! empty($image_meta_tags) ) {
                        $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                    }
                }

                //$metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
                //if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
                //    $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url_raw( str_replace('http:', 'https:', $options["default_image_url"] ) ) . '" />';
                //}
            }

        }

        // og:referenced
        $referenced_url_list = amt_get_referenced_items($post);
        foreach ($referenced_url_list as $referenced_url) {
            $referenced_url = trim($referenced_url);
            if ( ! empty( $referenced_url ) ) {
                $metadata_arr[] = '<meta property="og:referenced" content="' . esc_url_raw( $referenced_url ) . '" />';
            }
        }

        // Article: meta tags

        if ( $og_type == 'article' ) {

            // Dates
            $metadata_arr['article:published_time'] = '<meta property="article:published_time" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
            $metadata_arr['article:modified_time'] = '<meta property="article:modified_time" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';

            // Author
            // If a Facebook author profile URL has been provided, it has priority,
            // Otherwise fall back to the WordPress author archive.
            $fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $post->post_author);
            if ( !empty($fb_author_url) ) {
                $metadata_arr['article:author'] = '<meta property="article:author" content="' . esc_url_raw( $fb_author_url, array('http', 'https', 'mailto') ) . '" />';
            } else {
                //$metadata_arr['article:author'] = '<meta property="article:author" content="' . esc_url_raw( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '" />';
                $metadata_arr['article:author'] = '<meta property="article:author" content="' . esc_url_raw( amt_get_local_author_profile_url( get_the_author_meta( 'ID', $post->post_author ), $options ) ) . '" />';
            }

            // Publisher
            // If a Facebook publisher profile URL has been provided, it has priority.
            // Otherwise fall back to the WordPress blog home url.
            if ( ! empty($options['social_main_facebook_publisher_profile_url']) ) {
                $metadata_arr['article:publisher'] = '<meta property="article:publisher" content="' . esc_url_raw( $options['social_main_facebook_publisher_profile_url'], array('http', 'https', 'mailto') ) . '" />';
            } else {
                $metadata_arr['article:publisher'] = '<meta property="article:publisher" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
            }

            /*
            // article:section: We use the first category as the section.
            $first_cat = amt_get_first_category($post);
            if ( ! empty( $first_cat ) ) {
                $metadata_arr[] = '<meta property="article:section" content="' . esc_attr( $first_cat ) . '" />';
            }
            */
            // article:section: We use print an ``article:section`` meta tag for each of the post's categories.
            $categories = get_the_category($post->ID);
            $categories = apply_filters( 'amt_post_categories_for_opengraph', $categories );
            foreach( $categories as $cat ) {
                $section = trim( $cat->cat_name );
                if ( ! empty( $section ) && $cat->slug != 'uncategorized' ) {
                    $metadata_arr[] = '<meta property="article:section" content="' . esc_attr( $section ) . '" />';
                }
            }
            
            // article:tag: Keywords are listed as post tags
            $keywords = explode(',', amt_get_content_keywords($post, $auto=true, $exclude_categories=true));
            foreach ($keywords as $tag) {
                $tag = trim( $tag );
                if (!empty($tag)) {
                    $metadata_arr[] = '<meta property="article:tag" content="' . esc_attr( $tag ) . '" />';
                }
            }

        }

        // video.other meta tags

        elseif ( $og_type == 'video.other' ) {

            // Dates
            $metadata_arr[] = '<meta property="video:release_date" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';

            // video:tag: Keywords are listed as post tags
            $keywords = explode(',', amt_get_content_keywords($post));
            foreach ($keywords as $tag) {
                $tag = trim( $tag );
                if (!empty($tag)) {
                    $metadata_arr[] = '<meta property="video:tag" content="' . esc_attr( $tag ) . '" />';
                }
            }

        }

        // product meta tags
        elseif ( $og_type == 'product' ) {

            // Extend the current metadata with properties of the Product object.
            // See:
            //  * https://developers.facebook.com/docs/reference/opengraph/object-type/product/
            //  * https://developers.facebook.com/docs/payments/product
            $metadata_arr = apply_filters( 'amt_product_data_opengraph', $metadata_arr, $post );

        }

    }

    // Filtering of the generated Opengraph metadata
    $metadata_arr = apply_filters( 'amt_opengraph_metadata_head', $metadata_arr );

    return $metadata_arr;
}


//
// Return an array of Opengraph metatags for an image attachment with the
// provided post ID.
// By default, returns metadata for the 'medium' sized version of the image.
//
function amt_get_opengraph_image_metatags( $options, $image_data, $size='medium' ) {
    //
    // $image_data can be:
    //
    // 1. An array with the following data:
    //
    //    'id'    => null,   // post ID of attachment
    //    'url'   => null,
    //    'width' => null,
    //    'height' => null,
    //    'type'  => null,
    //
    // 2. An attachment ID (integer)
    //
    $metadata_arr = array();
    $image_id = null;

    if ( is_array($image_data) && ! is_null($image_data['url']) ) {
        // Here we process the image data as retrieved from the special notation of the image's URL.
        // No size information is taken into account in this case.
        // Image tags
        $metadata_arr[] = '<meta property="og:image" content="' . esc_url( $image_data['url'] ) . '" />';
        if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
            $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url( str_replace('http:', 'https:', $image_data['url']) ) . '" />';
        }
        if ( apply_filters( 'amt_extended_image_tags', true ) ) {
            if ( ! is_null($image_data['width']) ) {
                $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $image_data['width'] ) . '" />';
            }
            if ( ! is_null($image_data['height']) ) {
                $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $image_data['height'] ) . '" />';
            }
            if ( ! is_null($image_data['type']) ) {
                $metadata_arr[] = '<meta property="og:image:type" content="' . esc_attr( $image_data['type'] ) . '" />';
            }
        }

        return $metadata_arr;

    } elseif ( is_array($image_data) && is_numeric($image_data['id']) ) {
        // The attachment ID exists in the array's 'id' item.
        $image_id = absint( $image_data['id'] );
    } elseif ( is_numeric($image_data) ) {
        // Image data is the attachment ID (integer)
        $image_id = absint( $image_data );
    }

    if ( empty($image_id) ) {
        return $metadata_arr;
    }

    // Process the image attachment and generate meta tags.

    //$image = get_post( $image_id );
    //$image_meta = wp_get_attachment_metadata( $image->ID );   // contains info about all sizes
    // We use wp_get_attachment_image_src() since it constructs the URLs
    //$thumbnail_meta = wp_get_attachment_image_src( $image->ID, 'thumbnail' );
    $main_size_meta = wp_get_attachment_image_src( $image_id, $size );
    // Check if we have image data. $main_size_meta is false on error.
    if ( $main_size_meta === false ) {
        return $metadata_arr;
    }

    // Image tags
    $metadata_arr[] = '<meta property="og:image" content="' . esc_url( $main_size_meta[0] ) . '" />';
    if ( is_ssl() || ( ! is_ssl() && $options["has_https_access"] == "1" ) ) {
        $metadata_arr[] = '<meta property="og:image:secure_url" content="' . esc_url( str_replace('http:', 'https:', $main_size_meta[0]) ) . '" />';
    }
    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
        $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
        $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
        $metadata_arr[] = '<meta property="og:image:type" content="' . esc_attr( get_post_mime_type( $image_id ) ) . '" />';
    }

    return $metadata_arr;
}


