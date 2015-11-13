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
 * Module containing the Add-Meta-Tags Command Line Interface.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


/**
 * Implements the Add-Meta-Tags command line interface.
 *
 * @package wp-cli
 * @subpackage commands/community
 * @maintainer George Notaras (http://www.g-loaded.eu)
 */
class AMT_Command extends WP_CLI_Command {

    /**
     * Prints information about Add-Meta-Tags.
     * 
     * ## OPTIONS
     * 
     * <name>
     * : The name of the person to greet.
     * 
     * ## EXAMPLES
     * 
     *     wp amt info Newman
     *
     * @synopsis <name>
     */
    function info( $args, $assoc_args ) {
        list( $name ) = $args;

        // get_plugin_data( $plugin_file, $markup = true, $translate = true )
        //$plugin info = get_plugin_data( AMT_PLUGIN_DIR . 'add-meta-tags.php', $markup = true, $translate = true );
        // WP_CLI::line( ' ' );
        // WP_CLI::line( count( $field_groups ) . ' field groups found for blog_id ' . $blog['blog_id'] );

        // Print a success message
        WP_CLI::success( "Hello, $name!" );
    }

    /**
     * Prints status of Add-Meta-Tags on all blogs.
     * 
     * ## EXAMPLES
     * 
     *     wp amt status
     *
     * @synopsis
     */
    function status( $args, $assoc_args ) {

        if ( is_multisite() ) {
            $blog_list = get_blog_list( 0, 'all' );
        } else {
            $blog_list   = array();
            $blog_list[] = array( 'blog_id' => 1 );
        }

        foreach ( $blog_list as $blog ) {
            if ( is_multisite() ) {
                switch_to_blog( $blog['blog_id'] );
            }
            $plugin_info = get_plugin_data( plugin_dir_path( __FILE__ ) . 'add-meta-tags.php', $markup = true, $translate = true );
            WP_CLI::line( ' ' );
            WP_CLI::line( get_bloginfo('name') . ' - ' . $blog['blog_id'] );
            WP_CLI::line( $plugin_info['Version'] );
            WP_CLI::line( ' ' );
            if ( is_multisite() ) {
                restore_current_blog();
            }
        }


        // get_plugin_data( $plugin_file, $markup = true, $translate = true )
        //$plugin info = get_plugin_data( AMT_PLUGIN_DIR . 'add-meta-tags.php', $markup = true, $translate = true );
        // WP_CLI::line( ' ' );
        // WP_CLI::line( count( $field_groups ) . ' field groups found for blog_id ' . $blog['blog_id'] );

        // Print a success message
        WP_CLI::success( "Operation complete." );
    }

    /**
     * Upgrades the Add-Meta-Tags settings.
     * 
     * ## EXAMPLES
     * 
     *     wp amt upgrade
     *
     * @synopsis
     */
    function upgrade( $args, $assoc_args ) {

        if ( is_multisite() ) {
            $blog_list = get_blog_list( 0, 'all' );
        } else {
            $blog_list   = array();
            $blog_list[] = array( 'blog_id' => 1 );
        }

        foreach ( $blog_list as $blog ) {
            if ( is_multisite() ) {
                switch_to_blog( $blog['blog_id'] );
            }
            $plugin_info = get_plugin_data( plugin_dir_path( __FILE__ ) . 'add-meta-tags.php', $markup = true, $translate = true );
            WP_CLI::line( 'Upgrading settings of ' . get_bloginfo('name') . ' - ' . $blog['blog_id'] );
            amt_plugin_upgrade();
            if ( is_multisite() ) {
                restore_current_blog();
            }
        }


        // get_plugin_data( $plugin_file, $markup = true, $translate = true )
        //$plugin info = get_plugin_data( AMT_PLUGIN_DIR . 'add-meta-tags.php', $markup = true, $translate = true );
        // WP_CLI::line( ' ' );
        // WP_CLI::line( count( $field_groups ) . ' field groups found for blog_id ' . $blog['blog_id'] );

        // Print a success message
        WP_CLI::success( "Operation complete." );
    }


    /**
     * Exports settings and data.
     * 
     * ## OPTIONS
     * 
     * <what>
     * : The name of the person to greet.
     * 
     * [--blog-id=<blog_id>]
     * : The name of the person to greet.
     * 
     * [--blog-domain=<blog_domain>]
     * : The name of the person to greet.
     * 
     * ## EXAMPLES
     * 
     *     wp amt export all
     *     wp amt export settings
     *     wp amt export postdata
     *     wp amt export userdata
     *
     * @synopsis <what> [--blog-id=<id>]
     */
    function export( $args, $assoc_args ) {
        list( $what ) = $args;

        // options:  --what=<all|settings|postdata|userdata> --blog-id ID

        if ( ! in_array($what, array('all', 'settings', 'postdata', 'userdata')) ) {
            WP_CLI::error( 'Invalid argument: ' . $what . ' (valid: all|settings|postdata|userdata)' );
        } elseif ( ! empty($assoc_args['blog-id']) && ! empty($assoc_args['blog-id']) ) {
            WP_CLI::error( 'Mutually exclusive arguments: --blog-id, --blog-domain' );
        } elseif ( empty($assoc_args['blog-id']) && empty($assoc_args['blog-id']) ) {
            WP_CLI::error( 'At least one required: --blog-id, --blog-domain' );
        }

WP_CLI::success( "Operation complete." );

        if ( is_multisite() ) {
            $blog_list = get_blog_list( 0, 'all' );
        } else {
            $blog_list   = array();
            $blog_list[] = array( 'blog_id' => 1 );
        }

        foreach ( $blog_list as $blog ) {
            if ( is_multisite() ) {
                switch_to_blog( $blog['blog_id'] );
            }

            $options = get_option("add_meta_tags_opts");
            //var_dump( $options );
            echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            //$plugin_info = get_plugin_data( plugin_dir_path( __FILE__ ) . 'add-meta-tags.php', $markup = true, $translate = true );
            //WP_CLI::line( 'Upgrading settings of ' . get_bloginfo('name') . ' - ' . $blog['blog_id'] );
            //amt_plugin_upgrade();

            if ( is_multisite() ) {
                restore_current_blog();
            }
        }

        // get_plugin_data( $plugin_file, $markup = true, $translate = true )
        //$plugin info = get_plugin_data( AMT_PLUGIN_DIR . 'add-meta-tags.php', $markup = true, $translate = true );
        // WP_CLI::line( ' ' );
        // WP_CLI::line( count( $field_groups ) . ' field groups found for blog_id ' . $blog['blog_id'] );

        // Print a success message
        //WP_CLI::success( "Operation complete." );
    }

}

WP_CLI::add_command( 'amt', 'AMT_Command' );

