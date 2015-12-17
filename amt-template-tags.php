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
    $post = amt_get_queried_object($options);
    if ( ! is_null( $post ) ) {
        echo amt_get_content_description( $post );
    }
}

function amt_content_keywords() {
    $options = get_option("add_meta_tags_opts");
    $post = amt_get_queried_object($options);
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
        $post = amt_get_queried_object($options);
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

