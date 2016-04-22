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
 * Basic Metadata
 *
 * Module containing functions related to Basic Metadata
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


/**
 * Generates basic metadata for the head area.
 *
 */
function amt_add_basic_metadata_head( $post, $attachments, $embedded_media, $options ) {

    if ( apply_filters('amt_exclude_basic_metadata', false) ) {
        return array();
    }

    $do_description = (($options["auto_description"] == "1") ? true : false );
    $do_keywords = (($options["auto_keywords"] == "1") ? true : false );
    $do_noodp_description = (($options["noodp_description"] == "1") ? true : false );

    // Array to store metadata
    $metadata_arr = array();

    // Pre-processing

    // Store hreflang links in array
    $hreflang_links_arr = array();

    // Store base robots options
    $robots_options = array();

    if ( $do_noodp_description && ( is_front_page() || is_singular() || is_category() || is_tag() || is_tax() || is_author() ) ) {
        // Add NOODP on posts and pages
        $robots_options[] = 'noodp';
        $robots_options[] = 'noydir';
    }

    // Store full meta tags (site wide and post specific)

    // Add site wide meta tags
    $full_metatags_as_string = '';
    if ( ! empty( $options['site_wide_meta'] ) ) {
        $full_metatags_for_site = html_entity_decode( stripslashes( $options['site_wide_meta'] ) );
        $full_metatags_as_string .= apply_filters('amt_full_metatags_site', $full_metatags_for_site);
        $full_metatags_as_string .= PHP_EOL;
    }

    // Full meta tags
    if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
        // per post full meta tags
        $full_metatags_for_content = amt_get_post_meta_full_metatags( $post->ID );
        $full_metatags_for_content = html_entity_decode( stripslashes( $full_metatags_for_content ) );
        $full_metatags_as_string .= apply_filters('amt_full_metatags_post', $full_metatags_for_content);
    } elseif ( is_category() || is_tag() || is_tax() ) {
        // Term specific full meta tags ($post is a term object)
        $full_metatags_for_term = amt_get_term_meta_full_metatags( $post->term_id );
        $full_metatags_for_term = html_entity_decode( stripslashes( $full_metatags_for_term ) );
        $full_metatags_as_string .= apply_filters('amt_full_metatags_term', $full_metatags_for_term);
    } elseif ( is_author() ) {
        // User specific full meta tags ($post is a user object)
        $full_metatags_for_user = amt_get_user_meta_full_metatags( $post->ID );
        $full_metatags_for_user = html_entity_decode( stripslashes( $full_metatags_for_user ) );
        $full_metatags_as_string .= apply_filters('amt_full_metatags_term', $full_metatags_for_user);
    }

    // Sanitize
    //$full_metatags_as_string = esc_textarea( wp_kses( $full_metatags_as_string, amt_get_allowed_html_kses() ) );
    $full_metatags_as_string = wp_kses( $full_metatags_as_string, amt_get_allowed_html_kses() );

    // Make array of full meta tags
    $full_meta_tags = preg_split('#\R#', $full_metatags_as_string, NULL, PREG_SPLIT_NO_EMPTY);

    // Process

    if ( apply_filters('amt_full_metatags_processor_enable', true) ) {

        // Store processed meta tags here
        $processed_full_meta_tags = array();

        // Field substitutions currently take place only on content pages.
        // TODO: See if this can be expanded to terms, authors.

        // Store the post's custom fields
        $custom_fields = null;

        // Store the post object's custom fields.
        //
        if ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {
            // Get an array of all custom fields names of the post object.
            $custom_fields = get_post_custom_keys( $post->ID );
        }

        // Iterate over full meta tags

        foreach ($full_meta_tags as $single_meta_tag) {

            // Note: Field value substitutions take place first, outside the elseif clauses.

            // Process substitutions of special notation with data from Custom Fields
            // Supported special notation:
            //   [field=Field Name]
            // Notes:
            // - 'Field Name' is the name of custom field.
            // - If the custom field with name 'Field Name' does not exist, the meta tag
            //   that contains it is omitted.
            // - If the value of the field is an empty string, then the substitution
            //   takes place normally.
            //

            // The regex pattern fo our special notation.
            $special_notation_pattern = '#(?:\[field\=)([^\]]+)(?:\])#';

            // The following covers content pages, as $custom_fields is only set on content pages. See above.
            if ( ! empty( $custom_fields ) && isset($post->ID) ) {
                // This also assumes that we have a post object since custom fields
                // are set only on content pages, otherwise it is null.

                // Check for special notation
                if ( preg_match($special_notation_pattern, $single_meta_tag, $matches) ) {
                    //var_dump($matches);
                    // If the field name of the special notation does not match
                    // any custom field name, omit the meta tag as per the rules above.
                    if ( ! in_array($matches[1], $custom_fields) ) {
                        continue;
                    }
                    // Since there is special notation and the field name from the special
                    // notation exists in the $custom_fields array, iterate over the available
                    // custom fields and perform the substitutions.
                    foreach ( $custom_fields as $custom_field ) {
                        // Check if it matches the field name of the special notation
                        if ( $custom_field == $matches[1] ) {
                            // Fetch the custom field's value
                            $field_value = get_post_meta( $post->ID, $custom_field, true );
                            // Sanitize value
                            // TODO: this can be a problem depending on the value and the used sanitization function.
                            $field_value = esc_attr( sanitize_text_field( $field_value ) );
                            // Perform the substitution even if the the value is an empty string as per the rules above
                            $single_meta_tag = str_replace( sprintf('[field=%s]', $custom_field), $field_value, $single_meta_tag);
                        }
                    }
                }

            } else {
                // In any other case, just remove the meta tags which contain the special notation.
                if ( preg_match($special_notation_pattern, $single_meta_tag, $tmp) ) {
                    continue;
                }
            }

            // Process the PAGEINFO variable.
            // If the current page is the 1st page of any archive or of multipage content,
            // PAGEINFO is just stripped. For subsequent pages of archives or multipage
            // content, PAGEINFO is replaced with page based path (page/N/ for archives or N/ for multipage content)
            //
            // For paginated archives or paginated main page with latest posts.
            $has_paging_info = false;
            if ( is_paged() ) {
                $paged = get_query_var( 'paged' );  // paged
                if ( $paged && $paged >= 2 ) {
                    $single_meta_tag = str_replace('PAGEINFO', 'page/' . $paged . '/', $single_meta_tag);
                    $has_paging_info = true;
                }
            // For a Post or Page that has been divided into pages using the <!--nextpage--> QuickTag
            } else {
                $paged = get_query_var( 'page' );  // page
                if ( $paged && $paged >= 2 ) {
                    $single_meta_tag = str_replace('PAGEINFO', $paged . '/', $single_meta_tag);
                    $has_paging_info = true;
                }
            }
            // If this is not paged, strip PAGEINFO
            if ( $has_paging_info === false ) {
                $single_meta_tag = str_replace('PAGEINFO', '', $single_meta_tag);
            }

            // Process custom canonical link
            // If a rel="canonical" meta tags exists, we deactivate WordPress' 'rel_canonical' action,
            // Since it is assumed that a custom canonical link has been added.
            //if ( preg_match( '# rel="canonical" #', $post_full_meta_tags, $tmp ) ) {
            if ( strpos($single_meta_tag, ' rel="canonical" ') !== false ) {
                // Remove default WordPress action
                remove_action('wp_head', 'rel_canonical');
            }

            // Process robots meta tags.
            // Multiple robots meta tags may exist. Here we collect the options.
            elseif ( strpos($single_meta_tag, ' name="robots" ') !== false ) {
                if ( preg_match( '# content="([^"]+)" #', $single_meta_tag, $matches ) ) {
                    $tmp_robots_opts = explode(',', $matches[1]);
                    foreach ($tmp_robots_opts as $single_robots_option) {
                        $single_robots_option_cleaned = strtolower(trim($single_robots_option));
                        if ( ! empty($single_robots_option_cleaned) ) {
                            $robots_options[] = $single_robots_option_cleaned;
                        }
                    }
                }
                // We simply collect options. Do not add any robots meta tags to the processed meta tags array.
                continue;
            }

            // Process hreflang links.
            // Here we just collect them and let them be process later below at the special section.
            elseif ( strpos($single_meta_tag, ' hreflang="') !== false ) {
                // Simply add to the hreflang links array for later processing
                $hreflang_links_arr[] = $single_meta_tag;
                // We simply collect hreflang links for later processing. Do not add them to the processed meta tags array.
                continue;
            }


            // If we have reached here, add the meta tags to the array with processed meta tags.
            $processed_full_meta_tags[] = $single_meta_tag;

        }

    } else {
        // Full meta tags processor not enabled
        $processed_full_meta_tags = $full_meta_tags;
    }

    //var_dump($full_meta_tags);
    //var_dump($processed_full_meta_tags);

    // Add Meta Tags

    // Add a robots meta tag if robots options exist.
    // Backwards compatible filter. TODO: This is deprecated. Needs to be deleted after a while.
    $old_options_as_string = apply_filters( 'amt_robots_data', '' );
    if ( ! empty($old_options_as_string) ) {
        foreach ( explode(',', $old_options_as_string) as $single_robots_option) {
            $single_robots_option_cleaned = strtolower(trim($single_robots_option));
            if ( ! empty($single_robots_option_cleaned) ) {
                $robots_options[] = $single_robots_option_cleaned;
            }
        }
    }
    // Add robot_options filtering
    $robots_options = apply_filters( 'amt_robots_options', $robots_options );
    if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
        // The flag is not supported
        $robots_options = array_unique( $robots_options );
    } else {
        $robots_options = array_unique( $robots_options, SORT_STRING );
    }
    if ( ! empty( $robots_options ) ) {
        $metadata_arr['basic:robots'] = '<meta name="robots" content="' . esc_attr( implode(',', $robots_options) ) . '" />';
    }

    // Add full meta tags
    // Merge meta tags
    $processed_full_meta_tags = apply_filters('amt_full_metatags_processed', $processed_full_meta_tags);
    if ( ! empty($processed_full_meta_tags) ) {
        $metadata_arr = array_merge($metadata_arr, $processed_full_meta_tags);
    }

    // Add copyright link
    // On every page print the copyright head link
    $copyright_url = amt_get_site_copyright_url($options);
    //if ( empty($copyright_url)) {
    //    $copyright_url = trailingslashit( get_bloginfo('url') );
    //}
    if ( ! empty($copyright_url) ) {
        $metadata_arr['basic:copyright'] = '<link rel="copyright" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . ' '.__('copyright information', 'add-meta-tags').'" href="' . esc_url( $copyright_url ) . '" />';
    }

    // hreflang link element
    // This section also expects an array of extra hreflang links that may have
    // been collected from the full meta tags boxes.
    if ( $options['generate_hreflang_links'] == '1' ) {
        if ( is_singular() ) {
            $locale = amt_get_language_content($options, $post);
            $hreflang = amt_get_the_hreflang($locale, $options);
            $hreflang_url = amt_get_permalink_for_multipage($post);
        } else {
            $locale = amt_get_language_site($options);
            $hreflang = amt_get_the_hreflang($locale, $options);
            $hreflang_url = '';
            if ( amt_is_default_front_page() ) {
                $hreflang_url = trailingslashit( get_bloginfo('url') );
            } elseif ( is_category() || is_tag() || is_tax() ) {
                // $post is a term object
                $hreflang_url = get_term_link($post);
            } elseif ( is_author() ) {
                // $post is an author object
                $hreflang_url = get_author_posts_url( $post->ID );
            } elseif ( is_year() ) {
                $archive_year  = get_the_time('Y'); 
                $hreflang_url = get_year_link($archive_year);
            } elseif ( is_month() ) {
                $archive_year  = get_the_time('Y'); 
                $archive_month = get_the_time('m'); 
                $hreflang_url = get_month_link($archive_year, $archive_month);
            } elseif ( is_day() ) {
                $archive_year  = get_the_time('Y'); 
                $archive_month = get_the_time('m'); 
                $archive_day   = get_the_time('d'); 
                $hreflang_url = get_day_link($archive_year, $archive_month, $archive_day);
            }
            // If paged information is available
            if ( is_paged() ) {
                //$hreflang_url = trailingslashit( $hreflang_url ) . get_query_var('paged') . '/';
                $hreflang_url = get_pagenum_link( get_query_var('paged') );
            }
        }
        // hreflang links array
        $hreflang_arr = array();
        // Add link element
        if ( ! empty($hreflang) && ! empty($hreflang_url) ) {
            $hreflang_arr[] = '<link rel="alternate" hreflang="' . esc_attr( $hreflang ) . '" href="' . esc_url_raw( $hreflang_url ) . '" />';
        }
        // Add extra hreflang links that have been collected from the full meta tags boxes
        if ( ! empty($hreflang_links_arr) ) {
            $hreflang_arr = array_merge($hreflang_arr, $hreflang_links_arr);
        }
        // Allow filtering of the hreflang array
        $hreflang_arr = apply_filters( 'amt_hreflang_links', $hreflang_arr );
        // Add to to metadata array
        foreach ( $hreflang_arr as $hreflang_link ) {
            if ( preg_match('# hreflang="([^"]+)" #', $hreflang_link, $matches) ) {
                $metadata_arr['basic:hreflang:' . $matches[1]] = $hreflang_link;
            }
        }
    }


    // Basic Meta Tags

    // Custom content override
    if ( amt_is_custom($post, $options) ) {
        // Return metadata with:
        // add_filter( 'amt_custom_metadata_basic', 'my_function', 10, 5 );
        // Return an array of meta tags. Array item format: ['key_can_be_whatever'] = '<meta name="foo" content="bar" />'
        $metadata_arr = apply_filters( 'amt_custom_metadata_basic', $metadata_arr, $post, $options, $attachments, $embedded_media );

    // Default front page displaying latest posts
    } elseif ( amt_is_default_front_page() ) {

        // Description and Keywords from the Add-Meta-Tags settings override
        // default behaviour.

        // Description
        if ($do_description) {
            // Use the site description from the Add-Meta-Tags settings.
            // Fall back to the blog description.
            $site_description = amt_get_site_description($options);
            if ( empty($site_description ) ) {
                // Alternatively, use the blog description
                // Here we sanitize the provided description for safety
                $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
            }
            // If we have a description, use it in the description meta-tag of the front page
            if ( ! empty( $site_description ) ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $site_description ) ) . '" />';
            }
        }

        // Keywords
        if ($do_keywords) {
            // Use the site keywords from the Add-Meta-Tags settings.
            // Fall back to the blog categories.
            $site_keywords = amt_get_site_keywords($options);
            if ( empty( $site_keywords ) ) {
                // Alternatively, use the blog categories
                // Here we sanitize the provided keywords for safety
                $site_keywords = sanitize_text_field( amt_sanitize_keywords( amt_get_all_categories() ) );
            }
            // If we have keywords, use them in the keywords meta-tag of the front page
            if ( ! empty( $site_keywords ) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $site_keywords ) . '" />';
            }
        }


    // Attachments
    } elseif ( is_attachment() ) {  // has to be before is_singular() since is_singular() is true for attachments.

        // Description
        if ($do_description) {
            $description = amt_get_content_description($post, $auto=$do_description);
            if ( ! empty($description ) ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description ) ) . '" />';
            }
        }

        // No keywords


    // Content pages and static pages used as "front page" and "posts page"
    // This also supports products via is_singular()
    } elseif ( is_singular() || amt_is_static_front_page() || amt_is_static_home() ) {

        // Description
        if ($do_description) {
            $description = amt_get_content_description($post, $auto=$do_description);
            if ( ! empty( $description ) ) {
                // Note: Contains multipage information through amt_process_paged()
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description ) ) . '" />';
            }
        }

        // Keywords
        if ($do_keywords) {
            $keywords = amt_get_content_keywords($post, $auto=$do_keywords);
            if ( ! empty( $keywords ) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />';

            // Static Posts Index Page
            // If no keywords have been set in the metabox and this is the static page,
            // which displayes the latest posts, use the categories of the posts in the loop.
            } elseif ( amt_is_static_home() ) {
                // Here we sanitize the provided keywords for safety
                $cats_from_loop = sanitize_text_field( amt_sanitize_keywords( implode( ', ', amt_get_categories_from_loop() ) ) );
                if ( ! empty( $cats_from_loop ) ) {
                    $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $cats_from_loop ) . '" />';
                }
            }
        }

        // 'news_keywords'
        $newskeywords = amt_get_post_meta_newskeywords( $post->ID );
        if ( ! empty( $newskeywords ) ) {
            $metadata_arr['basic:news_keywords'] = '<meta name="news_keywords" content="' . esc_attr( $newskeywords ) . '" />';
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
                // Add a filtered generic description.
                $generic_description = apply_filters( 'amt_generic_description_category_archive', __('Content filed under the %s category.', 'add-meta-tags') );
                $generic_description = sprintf( $generic_description, single_cat_title( $prefix='', $display=false ) );
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
            } else {
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        if ($do_keywords) {
            // The category name alone is included in the 'keywords' metatag
            // Here we sanitize the provided keywords for safety
            $cur_cat_name = sanitize_text_field( amt_sanitize_keywords( single_cat_title($prefix = '', $display = false ) ) );
            if ( ! empty($cur_cat_name) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $cur_cat_name ) . '" />';
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
                // Add a filtered generic description.
                $generic_description = apply_filters( 'amt_generic_description_tag_archive', __('Content tagged with %s.', 'add-meta-tags') );
                $generic_description = sprintf( $generic_description, single_tag_title( $prefix='', $display=false ) );
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
            } else {
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        if ($do_keywords) {
            // The tag name alone is included in the 'keywords' metatag
            // Here we sanitize the provided keywords for safety
            $cur_tag_name = sanitize_text_field( amt_sanitize_keywords( single_tag_title($prefix = '', $display = false ) ) );
            if ( ! empty($cur_tag_name) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $cur_tag_name ) . '" />';
            }
        }

    // Custom taxonomies - Should be after is_category() and is_tag(), as it would catch those taxonomies as well.
    // This also supports product groups via is_tax(). Only product groups that are WordPress custom taxonomies are supported.
    } elseif ( is_tax() ) {

        // Taxonomy term object.
        // When viewing taxonomy archives, the $post object is the taxonomy term object. Check with: var_dump($post);
        $tax_term_object = $post;
        //var_dump($tax_term_object);

        if ($do_description) {
            // If set, the description of the custom taxonomy term is used in the 'description' metatag.
            // Otherwise, a generic description is used.
            // Here we sanitize the provided description for safety
            $description_content = sanitize_text_field( amt_sanitize_description( term_description( $tax_term_object->term_id, $tax_term_object->taxonomy ) ) );
            // Note: Contains multipage information through amt_process_paged()
            if ( empty( $description_content ) ) {
                // Add a filtered generic description.
                // Construct the filter name. Template: ``amt_generic_description_TAXONOMYSLUG_archive``
                $taxonomy_description_filter_name = sprintf( 'amt_generic_description_%s_archive', $tax_term_object->taxonomy);
                // var_dump($taxonomy_description_filter_name);
                $generic_description = apply_filters( $taxonomy_description_filter_name, __('Content filed under the %s taxonomy.', 'add-meta-tags') );
                $generic_description = sprintf( $generic_description, single_term_title( $prefix='', $display=false ) );
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
            } else {
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $description_content ) ) . '" />';
            }
        }
        
        if ($do_keywords) {
            // The taxonomy term name alone is included in the 'keywords' metatag.
            // Here we sanitize the provided keywords for safety.
            $cur_tax_term_name = sanitize_text_field( amt_sanitize_keywords( single_term_title( $prefix = '', $display = false ) ) );
            if ( ! empty($cur_tax_term_name) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $cur_tax_term_name ) . '" />';
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
                // Add a filtered generic description.
                $generic_description = apply_filters( 'amt_generic_description_author_archive', __('Content published by %s.', 'add-meta-tags') );
                $generic_description = sprintf( $generic_description, $author->display_name );
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
            } else {
                $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( $author_description ) . '" />';
            }
        }
        
        // For the keywords metatag use the categories of the posts the author has written and are displayed in the current page.
        if ($do_keywords) {
            // Here we sanitize the provided keywords for safety
            $cats_from_loop = sanitize_text_field( amt_sanitize_keywords( implode( ', ', amt_get_categories_from_loop() ) ) );
            if ( ! empty( $cats_from_loop ) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $cats_from_loop ) . '" />';
            }
        }
    
    // Custom Post Type Archive
    } elseif ( is_post_type_archive() ) {

        // Custom post type object.
        // When viewing custom post type archives, the $post object is the custom post type object. Check with: var_dump($post);
        $post_type_object = $post;
        //var_dump($post_type_object);

        if ($do_description) {
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
            $metadata_arr['basic:description'] = '<meta name="description" content="' . esc_attr( amt_process_paged( $generic_description ) ) . '" />';
        }
        
        // For the keywords metatag use the categories of the posts that are listed in the current page.
        if ($do_keywords) {
            // Here we sanitize the provided keywords for safety
            $cats_from_loop = sanitize_text_field( amt_sanitize_keywords( implode( ', ', amt_get_categories_from_loop() ) ) );
            if ( ! empty( $cats_from_loop ) ) {
                $metadata_arr['basic:keywords'] = '<meta name="keywords" content="' . esc_attr( $cats_from_loop ) . '" />';
            }
        }
        
    }


    // Filtering of the generated basic metadata
    $metadata_arr = apply_filters( 'amt_basic_metadata_head', $metadata_arr );

    return $metadata_arr;
}

