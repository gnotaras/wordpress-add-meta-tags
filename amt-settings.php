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
 * Module containing settings related functions.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


/**
 * Returns an array with the default options.
 */
function amt_get_default_options() {
    return array(
        "settings_version"  => 16,       // IMPORTANT: SETTINGS UPGRADE: Every time settings are added or removed this has to be incremented.
        "site_description"  => "",      // Front page description
        "site_keywords"     => "",      // Front page keywords
        "global_keywords"   => "",      // These keywords are added to the 'keywords' meta tag on all posts and pages
        "site_wide_meta"    => "",
        "auto_description"  => "1",     // Descriptions auto-generated by default
        "auto_keywords"     => "1",     // Keywords auto-generated by default
        "auto_opengraph"    => "0",     // Opengraph
        "og_omit_video_metadata" => "0",    // Omit og:video and og:video:* meta tags
        "auto_dublincore"   => "0",
        "auto_twitter"      => "0",     // Twitter Cards
        "tc_enable_player_card_local" => "0",   // Enable the player card for locally hosted audio and video attachments.
        "tc_enforce_summary_large_image" => "0",   // Set summary_large_image as the default card.
        "auto_schemaorg"    => "0",
        "schemaorg_force_jsonld"     => "0",
        "noodp_description" => "0",
        "noindex_search_results"     => "1",
        "noindex_date_archives"      => "0",
        "noindex_category_archives"  => "0",
        "noindex_tag_archives"       => "0",
        "noindex_taxonomy_archives"  => "0",
        "noindex_author_archives"    => "0",
        "enforce_custom_title_in_metadata"    => "0",
        "enable_advanced_title_management"    => "0",
        "metabox_enable_description"     => "1",
        "metabox_enable_keywords"        => "1",
        "metabox_enable_title"           => "1",
        "metabox_enable_news_keywords"   => "0",
        "metabox_enable_full_metatags"   => "0",
        "metabox_enable_image_url"   => "0",
        "metabox_enable_content_locale"  => "0",
        "metabox_enable_express_review" => "0",
        "metabox_enable_referenced_list" => "0",
        "social_main_facebook_publisher_profile_url" => "",
        //"social_main_facebook_app_id" => "",
        //"social_main_facebook_admins" => "",
        "social_main_googleplus_publisher_profile_url" => "",
        "social_main_twitter_publisher_username" => "",
        "global_locale" => "",
        "generate_hreflang_links" => "0",
        "hreflang_strip_region" => "0",
        "manage_html_lang_attribute" => "0",
        "has_https_access" => "0",
        "force_media_limit" => "0",
        "copyright_url"     => "",
        "default_image_url" => "",
        "extended_support_woocommerce"  => "0",
        "extended_support_edd"          => "0",
        "extended_support_buddypress"   => "0",
        "review_mode"       => "0",
        "i_have_donated"    => "0",
        );
}


/**
 * Performs upgrade of the plugin settings.
 */
function amt_plugin_upgrade() {

    // First we try to determine if this is a new installation or if the
    // current installation requires upgrade.

    // Default Add-Meta-Tags Settings
    $default_options = amt_get_default_options();

    // Try to get the current Add-Meta-Tags options from the database
    $stored_options = get_option("add_meta_tags_opts");
    if ( empty($stored_options) ) {
        // This is the first run, so set our defaults.
        update_option("add_meta_tags_opts", $default_options);
        return;
    }

    // Check the settings version

    // If the settings version of the default options matches the settings version
    // of the stored options, there is no need to upgrade.
    if (array_key_exists('settings_version', $stored_options) &&
            (intval($stored_options["settings_version"]) == intval($default_options["settings_version"])) ) {
        // Settings are up to date. No upgrade required.
        return;
    }

    // On any other case a settings upgrade is required.

    // 1) Add any missing options to the stored Add-Meta-Tags options
    foreach ($default_options as $opt => $value) {
        // Always upgrade the ``settings_version`` option
        if ($opt == 'settings_version') {
            $stored_options['settings_version'] = $value;
        }
        // Add missing options
        elseif ( !array_key_exists($opt, $stored_options) ) {
            $stored_options[$opt] = $value;
        }
        // Existing stored options are untouched here.
    }

    // 2) Migrate any current options to new ones.
    // Migration rules should go here.

    // Version 2.2.0 (settings_version 1->2)
    // Removed ``noindex_archives``
    // No migrations required. Clean-up takes place in step (3) below.

    // Version 2.2.1 (settings_version 2->3)
    // Added ``review_mode``
    // No migrations required. Addition takes place in (1).

    // Version 2.3.3 (settings_version 3->4)
    // Added ``auto_twitter``
    // Added ``auto_schemaorg``
    // No migrations required. Addition takes place in (1).

    // Version 2.5.0 (settings_version 4->5)
    // Added ``noindex_taxonomy_archives``
    // No migrations required. Addition takes place in (1).

    // Version 2.5.6 (settings_version 5->6)
    // Added ``tc_enable_player_card_local``
    // No migrations required. Addition takes place in (1).

    // Version 2.6.0 (settings_version 6->7)
    // Added "metabox_enable_description"
    // Added "metabox_enable_keywords"
    // Added "metabox_enable_title"
    // Added "metabox_enable_news_keywords"
    // Added "metabox_enable_full_metatags"
    // Added "metabox_enable_referenced_list"
    // No migrations required. Addition takes place in (1).

    // Version 2.7.2 (settings_version 7->8)
    // Added "social_main_facebook_publisher_profile_url"
    // Added "social_main_googleplus_publisher_profile_url"
    // Added "social_main_twitter_publisher_username"
    // No migrations required. Addition takes place in (1).

    // Version 2.7.3 (settings_version 8->9)
    // Added "has_https_access"
    // Added "tc_enforce_summary_large_image"
    // No migrations required. Addition takes place in (1).

    // Version 2.7.5 (settings_version 9->10)
    // Added "global_locale"
    // Added "metabox_enable_image_url"
    // No migrations required. Addition takes place in (1).

    // Version 2.8.1 (settings_version 10->11)
    // Added "extended_support_woocommerce"
    // Added "extended_support_edd"
    // Added "og_omit_video_metadata"
    // No migrations required. Addition takes place in (1).

    // Version 2.8.1 (settings_version 11->12)
    // Added "metabox_enable_express_review"
    // No migrations required. Addition takes place in (1).

    // Version 2.8.10 (settings_version 12->13)
    // Added "metabox_enable_content_locale"
    // Added "generate_hreflang_links"
    // Added "hreflang_strip_region"
    // No migrations required. Addition takes place in (1).

    // Version 2.8.12 (settings_version 13->14)
    // Added "manage_html_lang_attribute"
    // No migrations required. Addition takes place in (1).

    // Version 2.9.0 (settings_version 14->15)
    // Added "schemaorg_force_jsonld"
    // Added "force_media_limit"
    // No migrations required. Addition takes place in (1).

    // Version 2.9.2 (settings_version 15->16)
    // Added "enforce_custom_title_in_metadata"
    // Added "enable_advanced_title_management"
    // No migrations required. Addition takes place in (1).

    // Version 2.9.7 (settings_version 16->17)
    // Added "extended_support_buddypress"
    // No migrations required. Addition takes place in (1).


    // 3) Clean stored options.
    foreach ($stored_options as $opt => $value) {
        if ( !array_key_exists($opt, $default_options) ) {
            // Remove any options that do not exist in the default options.
            unset($stored_options[$opt]);
        }
    }

    // Finally save the updated options.
    update_option("add_meta_tags_opts", $stored_options);

}
add_action('plugins_loaded', 'amt_plugin_upgrade');
// No longer called in function amt_admin_init() in amt-admin-panel.php. See notes there.


/**
 * Saves the new settings in the database.
 * Accepts the POST request data.
 */
function amt_save_settings($post_payload) {
    
    // Default Add-Meta-Tags Settings
    $default_options = amt_get_default_options();

    $add_meta_tags_opts = array();

    foreach ($default_options as $def_key => $def_value) {

        // **Always** use the ``settings_version`` from the defaults
        if ($def_key == 'settings_version') {
            $add_meta_tags_opts['settings_version'] = $def_value;
        }

        // Add options from the POST request (saved by the user)
        elseif ( array_key_exists($def_key, $post_payload) ) {

            // Validate and sanitize input before adding to 'add_meta_tags_opts'
            if ( $def_key == 'site_description' ) {
                $add_meta_tags_opts[$def_key] = sanitize_text_field( amt_sanitize_description( stripslashes( $post_payload[$def_key] ) ) );
            } elseif ( $def_key == 'site_keywords' ) {
                // No placeholders here
                $add_meta_tags_opts[$def_key] = sanitize_text_field( amt_sanitize_keywords( stripslashes( $post_payload[$def_key] ) ) );
            } elseif ( $def_key == 'global_keywords' ) {
                // placeholder may exist here
                $add_meta_tags_opts[$def_key] = amt_sanitize_keywords( amt_revert_placeholders( sanitize_text_field( amt_convert_placeholders( stripslashes( $post_payload[$def_key] ) ) ) ) );
            } elseif ( $def_key == 'site_wide_meta' ) {
                $add_meta_tags_opts[$def_key] = esc_textarea( wp_kses( stripslashes( $post_payload[$def_key] ), amt_get_allowed_html_kses() ) );
            } elseif ( $def_key == 'copyright_url' ) {
                $add_meta_tags_opts[$def_key] = esc_url_raw( stripslashes( $post_payload[$def_key] ), array( 'http', 'https') );
            } elseif ( $def_key == 'default_image_url' ) {
                $add_meta_tags_opts[$def_key] = esc_url_raw( stripslashes( $post_payload[$def_key] ), array( 'http', 'https') );
            } elseif ( $def_key == 'social_main_facebook_publisher_profile_url' ) {
                $add_meta_tags_opts[$def_key] = esc_url_raw( stripslashes( $post_payload[$def_key] ), array( 'http', 'https') );
            } elseif ( $def_key == 'social_main_googleplus_publisher_profile_url' ) {
                $add_meta_tags_opts[$def_key] = esc_url_raw( stripslashes( $post_payload[$def_key] ), array( 'http', 'https') );
            } else {
                $add_meta_tags_opts[$def_key] = sanitize_text_field( stripslashes( $post_payload[$def_key] ) );
            }
        }
        
        // If missing (eg checkboxes), use the default value, except for the case
        // those checkbox settings whose default value is 1.
        else {

            // The following settings have a default value of 1, so they can never be
            // deactivated, unless the following check takes place.
            if (   $def_key == 'auto_description'
                || $def_key == 'auto_keywords'
                || $def_key == 'noindex_search_results'
                || $def_key == 'metabox_enable_description'
                || $def_key == 'metabox_enable_keywords'
                || $def_key == 'metabox_enable_title'
            ) {
                if( !isset($post_payload[$def_key]) ){
                    $add_meta_tags_opts[$def_key] = "0";
                }
            } else {
                // Else save the default value in the db.
                $add_meta_tags_opts[$def_key] = $def_value;
            }

        }
    }

    // Finally update the Add-Meta-Tags options.
    update_option("add_meta_tags_opts", $add_meta_tags_opts);

    //var_dump($post_payload);
    //var_dump($add_meta_tags_opts);

    amt_show_info_msg(__('Add-Meta-Tags options saved', 'add-meta-tags'));
}


/**
 * Reset settings to the defaults.
 */
function amt_reset_settings() {
    // Default Add-Meta-Tags Settings
    $default_options = amt_get_default_options();

    delete_option("add_meta_tags_opts");
    update_option("add_meta_tags_opts", $default_options);
    amt_show_info_msg(__('Add-Meta-Tags options were reset to defaults', 'add-meta-tags'));
}

