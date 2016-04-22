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
 * Module containing template tags.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


function amt_content_description() {
    $options = get_option("add_meta_tags_opts");
    $post = amt_get_queried_object();
    if ( ! is_null( $post ) ) {
        echo amt_get_content_description( $post );
    }
}

function amt_content_keywords() {
    $options = get_option("add_meta_tags_opts");
    $post = amt_get_queried_object();
    if ( ! is_null( $post ) ) {
        echo amt_get_content_keywords( $post );
    }
}

function amt_metadata_head() {
    // Prints full metadata for head area.
    amt_print_head_block();
}

function amt_metadata_footer() {
    // Prints full metadata for footer area.
    amt_print_footer_block();
}

function amt_metadata_review() {
    // Prints full metadata in review mode. No user level checks here.
    echo amt_get_metadata_inspect();
}

function amt_breadcrumbs( $user_options ) {
    echo amt_get_breadcrumbs( $user_options );
}

function amt_local_author_profile_url( $author_id=null, $display=true ) {
    $options = get_option("add_meta_tags_opts");
    if ( empty($options) ) {
        return '';
    }
    if ( is_null($author_id) ) {
        $post = amt_get_queried_object();
        if ( is_null($post) || $post->ID == 0 ) {
            return '';
        }
        $author_id = get_the_author_meta( 'ID', $post->post_author );
    }
    if ( $display ) {
        echo esc_url( amt_get_local_author_profile_url( $author_id, $options ) );
    } else {
        return esc_url( amt_get_local_author_profile_url( $author_id, $options ) );
    }
}



//
// User image template tags
//
// Returns array with image info about the custom user image set in the user profile (AMT section)
//
function amt_get_user_image_info( $size='thumbnail', $user_id=null ) {
    // Initial checks
    if ( empty($user_id) ) {
        if ( is_author() ) {
            // The post object is the author object
            $post = amt_get_queried_object();
            if ( ! isset($post->ID) ) {
                return false;
            }
            $user_id = $post->ID;
        } elseif ( is_singular() ) {
            // Get the user ID of the author of the current post.
            $post = amt_get_queried_object();
            if ( ! isset($post->post_author) ) {
                return false;
            }
            $user_id = $post->post_author;
        } else {
            return false;
        }
    } elseif ( ! is_numeric($user_id) ) {
        return false;
    }
    // Get data from Custom Field
    $custom_image_url_value = amt_get_user_meta_image_url( $user_id );
    // Get image data
    $image_data = amt_get_image_data( amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) );
    // Construct image info array
    $image_info = array(
        'url'    => null,
        'width'  => null,
        'height' => null,
    );
    if ( is_numeric($image_data['id']) ) {
        $main_size_meta = wp_get_attachment_image_src( $image_data['id'], $size );
        if ( empty($main_size_meta) ) {
            return false;
        }
        $image_info['url'] = $main_size_meta[0];
        $image_info['width'] = $main_size_meta[1];
        $image_info['height'] = $main_size_meta[2];
    } elseif ( ! is_null($image_data['url']) ) {
        $image_info['url'] = $main_size_meta[0];
        $image_info['width'] = $main_size_meta[1];
        $image_info['height'] = $main_size_meta[2];
    } else {
        return false;
    }
    return $image_info;
}

// Prints img of user image
function amt_print_user_image( $size='thumbnail', $user_id=null, $force_width=null, $force_height=null ) {
    $image_info = amt_get_user_image_info( $size=$size, $user_id=$user_id );
    if ( empty($image_info) ) {
        echo '';
    } else {
        if ( is_numeric($force_width) ) {
            $image_info['width'] = sprintf('%d', $force_width);
        }
        if ( is_numeric($force_height) ) {
            $image_info['height'] = sprintf('%d', $force_height);
        }
        echo '<img class="amt-user-image amt-user-image-' . esc_attr($size) . '" src="' . esc_url($image_info['url']) . '" width="' . esc_attr($image_info['width']) . '" height="' . esc_attr($image_info['height']) . '" />';
    }
}

// Override get_avatar_url with the URL of our user image
//
// Enable with:
//
// add_filter('amt_set_user_image_as_avatar_url', '__return_true');
//
function amt_set_user_image_as_avatar( $default_url ) {
    if ( apply_filters('amt_set_user_image_as_avatar', false) ) {
        $image_info = amt_get_user_image_info( $size='thumbnail' );
        if ( ! empty($image_info) ) {
            return $image_info['url'];
        }
    }
    return $default_url;
}
add_action('get_avatar_url', 'amt_set_user_image_as_avatar');



//
// Term image template tags
//
// Returns array with image info about the custom term image set in the term edit screen (AMT section)
//
function amt_get_term_image_info( $size='thumbnail', $term_id=null ) {
    // Initial checks
    if ( empty($term_id) ) {
        if ( is_category() || is_tag() || is_tax() ) {
            // The post object is the term object
            $post = amt_get_queried_object();
            if ( ! isset($post->term_id) ) {
                return false;
            }
            $term_id = $post->term_id;
        } else {
            return false;
        }
    } elseif ( ! is_numeric($term_id) ) {
        return false;
    }
    // Get data from Custom Field
    $custom_image_url_value = amt_get_term_meta_image_url( $term_id );
    // Get image data
    $image_data = amt_get_image_data( amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) );
    // Construct image info array
    $image_info = array(
        'url'    => null,
        'width'  => null,
        'height' => null,
    );
    if ( is_numeric($image_data['id']) ) {
        $main_size_meta = wp_get_attachment_image_src( $image_data['id'], $size );
        if ( empty($main_size_meta) ) {
            return false;
        }
        $image_info['url'] = $main_size_meta[0];
        $image_info['width'] = $main_size_meta[1];
        $image_info['height'] = $main_size_meta[2];
    } elseif ( ! is_null($image_data['url']) ) {
        $image_info['url'] = $main_size_meta[0];
        $image_info['width'] = $main_size_meta[1];
        $image_info['height'] = $main_size_meta[2];
    } else {
        return false;
    }
    
    return $image_info;
}

// Prints img of term image
function amt_print_term_image( $size='thumbnail', $term_id=null, $force_width=null, $force_height=null ) {
    $image_info = amt_get_term_image_info( $size=$size, $term_id=$term_id );
    if ( empty($image_info) ) {
        echo '';
    } else {
        if ( is_numeric($force_width) ) {
            $image_info['width'] = sprintf('%d', $force_width);
        }
        if ( is_numeric($force_height) ) {
            $image_info['height'] = sprintf('%d', $force_height);
        }
        echo '<img class="amt-term-image amt-term-image-' . esc_attr($size) . '" src="' . esc_url($image_info['url']) . '" width="' . esc_attr($image_info['width']) . '" height="' . esc_attr($image_info['height']) . '" />';
    }
}



