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
 * Schema.org Metadata
 * http://schema.org
 *
 * Also Google+ author and publisher links in HEAD.
 *
 * Module containing functions related to Schema.org Metadata
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


/**
 * Add contact method for Google+ for author and publisher.
 */
function amt_add_googleplus_contactmethod( $contactmethods ) {

    // Add Google+ author profile URL
    if ( !isset( $contactmethods['amt_googleplus_author_profile_url'] ) ) {
        $contactmethods['amt_googleplus_author_profile_url'] = __('Google+ author profile URL', 'add-meta-tags') . ' (AMT)';
    }

    // The publisher profile box in the WordPress user profile page can be deactivated via filtering.
    if ( apply_filters( 'amt_allow_publisher_settings_in_user_profiles', false ) ) {
        // Add Google+ publisher profile URL
        if ( !isset( $contactmethods['amt_googleplus_publisher_profile_url'] ) ) {
            $contactmethods['amt_googleplus_publisher_profile_url'] = __('Google+ publisher page URL', 'add-meta-tags') . ' (AMT)';
        }
    }

    return $contactmethods;
}
add_filter( 'user_contactmethods', 'amt_add_googleplus_contactmethod', 10, 1 );


/**
 * Adds links with the rel 'author' and 'publisher' to the HEAD of the page for Google+.
 */
function amt_add_schemaorg_metadata_head( $post, $attachments, $embedded_media, $options ) {

    if ( apply_filters('amt_exclude_schemaorg_metadata', false) ) {
        return array();
    }

    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    $metadata_arr = array();

    // The publisher link appears everywhere

    if ( ! is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.

        // Add the publisher link only.
        if ( ! empty($options['social_main_googleplus_publisher_profile_url']) ) {
            $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( $options['social_main_googleplus_publisher_profile_url'], array('http', 'https') ) . '" />';
        } else {
            // Link to homepage
            $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
    }

        return $metadata_arr;
    }

    // On content pages and static front/latest-posts page, add both links

    // Publisher
    if ( ! empty($options['social_main_googleplus_publisher_profile_url']) ) {
        $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( $options['social_main_googleplus_publisher_profile_url'], array('http', 'https') ) . '" />';
    } else {
        // Link to homepage
        $metadata_arr[] = '<link rel="publisher" type="text/html" title="' . esc_attr( get_bloginfo('name') ) . '" href="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
    }

    // Author
    // Additional check to make sure we have a post. Eg, BuddyPress pages have a $post with ID zero.
    if ( $post->ID > 0 ) {
        $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $post->post_author);
        if ( empty( $googleplus_author_url ) ) {
            // Link to the author archive
            $metadata_arr[] = '<link rel="author" type="text/html" title="' . esc_attr( get_the_author_meta('display_name', $post->post_author) ) . '" href="' . esc_attr( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '" />';
        } else {
            // Link to Google+ author profile
            $metadata_arr[] = '<link rel="author" type="text/html" title="' . esc_attr( get_the_author_meta('display_name', $post->post_author) ) . '" href="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
        }
    }

    // Filtering of the generated Google+ metadata
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_head', $metadata_arr );

    return $metadata_arr;
}


/**
 * Add Schema.org Microdata in the footer.
 *
 * Mainly used to embed microdata to front page, posts index page and archives.
 */
function amt_add_schemaorg_metadata_footer( $post, $attachments, $embedded_media, $options ) {

    if ( apply_filters('amt_exclude_schemaorg_metadata', false) ) {
        return array();
    }

    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    // Check if the microdata or the JSON-LD schema.org generator should be used.
    if ( $options["schemaorg_force_jsonld"] == "1" ) {
        return array();
    }

    // Check for AMP page https://www.ampproject.org/
    // For AMP pages we do not generate Schema.org microdata around the post content,
    // but enforce the JSON+LD form.
    if ( $do_auto_schemaorg && function_exists('is_amp_endpoint') && is_amp_endpoint() ) {
        return array();
    }

    $metadata_arr = array();

    if ( is_paged() ) {
        //
        // Currently we do not support adding Schema.org metadata on
        // paged archives, if page number is >=2
        //
        // NOTE: This refers to an archive or the main page being split up over
        // several pages, this does not refer to a Post or Page whose content
        // has been divided into pages using the <!--nextpage--> QuickTag.
        //
        // Multipage content IS processed below.
        //
        return array();
    }


    // Custom content override
    if ( amt_is_custom($post, $options) ) {
        // Return metadata with:
        // add_filter( 'amt_custom_metadata_schemaorg_footer', 'my_function', 10, 5 );
        // Return an array of meta tags. Array item format: ['key_can_be_whatever'] = '<meta name="foo" content="bar" />'
        $metadata_arr = apply_filters( 'amt_custom_metadata_schemaorg_footer', $metadata_arr, $post, $options, $attachments, $embedded_media );
        return $metadata_arr;

    // Default fron tpage displaying the latest posts.

    } elseif ( amt_is_default_front_page() ) {

        // Organization
        //
        // NOTICE:
        // Even if the front page has been set as the source of profile, this
        // this does not work with the default front page with the latest posts.
        // This is becuase this page does not have an author, which is essential
        // for the generated metadata on a page that is supposed to be a profile.
        // Therefore, an Organization object is always generated on the default
        // front page and it is never treated as a profile page by Add-Meta-Tags.
        //
        // Scope BEGIN: Organization: http://schema.org/Organization
        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
        //$metadata_arr[] = '<span itemprop="mainEntity" itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . '>';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . amt_get_schemaorg_entity_id_as_itemid('organization') . '>';
        // Get publisher/mainEntity metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options ) );
        // mainEntityOfPage
        $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
        // Scope END: Organization
        $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        // WebSite
        // Scope BEGIN: WebSite: http://schema.org/WebSite
        $metadata_arr[] = '<!-- Scope BEGIN: WebSite -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/WebSite"' . amt_get_schemaorg_entity_id_as_itemid('website') . '>';
        // name
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // headline - contains title information
        $metadata_arr['microdata:headline'] = '<meta itemprop="headline" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // alternateName (The WordPress tag line is used.)
        // TODO: use tag line. Needs feedback!
        //$metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // url
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';

        // publisher
//        $metadata_arr[] = '<link itemprop="publisher" href="' . esc_attr( amt_get_schemaorg_entity_id('organization') ) . '" />';

        // SearchAction
        // Scope BEGIN: SearchAction: http://schema.org/SearchAction
        $metadata_arr[] = '<!-- Scope BEGIN: SearchAction -->';
        $metadata_arr[] = '<span itemprop="potentialAction" itemscope itemtype="http://schema.org/SearchAction">';
        // target
        // Scope BEGIN: EntryPoint: http://schema.org/EntryPoint
        $metadata_arr[] = '<span itemprop="target" itemscope itemtype="http://schema.org/EntryPoint">';
        // urlTemplate
        $metadata_arr[] = '<meta itemprop="urlTemplate" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '?s={search_term}" />';
        // Scope END: EntryPoint
        $metadata_arr[] = '</span> <!-- Scope END: EntryPoint -->';
        // query-input
        // Scope BEGIN: PropertyValueSpecification: http://schema.org/PropertyValueSpecification
        $metadata_arr[] = '<span itemprop="query-input" itemscope itemtype="http://schema.org/PropertyValueSpecification">';
        // valueRequired
        $metadata_arr[] = '<meta itemprop="valueRequired" content="True" />';
        // valueName
        $metadata_arr[] = '<meta itemprop="valueName" content="search_term" />';
        // Scope END: PropertyValueSpecification
        $metadata_arr[] = '</span> <!-- Scope END: PropertyValueSpecification -->';
        // Scope END: SearchAction
        $metadata_arr[] = '</span> <!-- Scope END: SearchAction -->';

        // Scope END: WebSite
        $metadata_arr[] = '</span> <!-- Scope END: WebSite -->';


    // Front page using a static page

    } elseif ( amt_is_static_front_page() ) {

        if ( $options['author_profile_source'] == 'frontpage' ) {

            // Author
            // Scope BEGIN: Person: http://schema.org/Person
            $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
            $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . amt_get_schemaorg_entity_id_as_itemid('person') . '>';
            // Get author metatags
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $post->post_author, $options ) );
            // mainEntityOfPage
            $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
            // Scope END: Person
            $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        } else {

            // Organization
            // Scope BEGIN: Organization: http://schema.org/Organization
            $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
            //$metadata_arr[] = '<span itemprop="mainEntity" itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . '>';
            $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . amt_get_schemaorg_entity_id_as_itemid('organization') . '>';
            // Get publisher/mainEntity metatags
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options ) );
            // mainEntityOfPage
            $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
            // Scope END: Organization
            $metadata_arr[] = '</span> <!-- Scope END: Organization -->';
        }

        // WebSite
        // Scope BEGIN: WebSite: http://schema.org/WebSite
        $metadata_arr[] = '<!-- Scope BEGIN: WebSite -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/WebSite"' . amt_get_schemaorg_entity_id_as_itemid('website') . '>';
        // name
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // headline - contains title information
        $metadata_arr['microdata:headline'] = '<meta itemprop="headline" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';
        // alternateName (The WordPress tag line is used.)
        // TODO: use tag line. Needs feedback!
        //$metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // url
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';

//        if ( $options['author_profile_source'] == 'frontpage' ) {
            // author
    //        $metadata_arr[] = '<link itemprop="author" href="' . esc_attr( amt_get_schemaorg_entity_id('person') ) . '" />';
//        } else {
            // publisher
    //        $metadata_arr[] = '<link itemprop="publisher" href="' . esc_attr( amt_get_schemaorg_entity_id('organization') ) . '" />';
//        }

        // SearchAction
        // Scope BEGIN: SearchAction: http://schema.org/SearchAction
        $metadata_arr[] = '<!-- Scope BEGIN: SearchAction -->';
        $metadata_arr[] = '<span itemprop="potentialAction" itemscope itemtype="http://schema.org/SearchAction">';
        // target
        // Scope BEGIN: EntryPoint: http://schema.org/EntryPoint
        $metadata_arr[] = '<span itemprop="target" itemscope itemtype="http://schema.org/EntryPoint">';
        // urlTemplate
        $metadata_arr[] = '<meta itemprop="urlTemplate" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '?s={search_term}" />';
        // Scope END: EntryPoint
        $metadata_arr[] = '</span> <!-- Scope END: EntryPoint -->';
        // query-input
        // Scope BEGIN: PropertyValueSpecification: http://schema.org/PropertyValueSpecification
        $metadata_arr[] = '<span itemprop="query-input" itemscope itemtype="http://schema.org/PropertyValueSpecification">';
        // valueRequired
        $metadata_arr[] = '<meta itemprop="valueRequired" content="True" />';
        // valueName
        $metadata_arr[] = '<meta itemprop="valueName" content="search_term" />';
        // Scope END: PropertyValueSpecification
        $metadata_arr[] = '</span> <!-- Scope END: PropertyValueSpecification -->';
        // Scope END: SearchAction
        $metadata_arr[] = '</span> <!-- Scope END: SearchAction -->';

        // Scope END: WebSite
        $metadata_arr[] = '</span> <!-- Scope END: WebSite -->';


    // Author archive

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

        // Person
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
        
        // headline - contains title information
        // NOTE: Not supported for person
        //$metadata_arr['microdata:headline'] = '<meta itemprop="headline" content="' . esc_attr( amt_get_title_for_metadata($options, $author) ) . '" />';

        // Get author metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $author->ID, $options ) );

        // mainEntityOfPage
        $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url( get_author_posts_url( $author->ID ) ) . '" />';

        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

    }

    // Filtering of the generated microdata for footer
    $metadata_arr = apply_filters( 'amt_schemaorg_metadata_footer', $metadata_arr );

    return $metadata_arr;
}


/**
 * Filter function that generates and embeds Schema.org metadata in the content.
 */
function amt_add_schemaorg_metadata_content_filter( $post_body ) {

    if ( apply_filters('amt_exclude_schemaorg_metadata', false) ) {
        return $post_body;
    }

    // For AMT timings
    $t = microtime(true);

    // Get the options the DB
    $options = get_option("add_meta_tags_opts");
    // Get current post object
    $post = amt_get_queried_object();
    // Caching indicator
    $is_cached = 'no';

    // First, try to get cached metadata. Cache must be cleared if settings have changed.
    if ( absint($options['transient_cache_expiration']) > 0 && apply_filters('amt_enable_metadata_cache_in_content_filter', true) ) {
        $cached_content_metadata_arr = amt_get_transient_cache($post, $options, $where='content');
        if ( ! empty($cached_content_metadata_arr) ) {
            $is_cached = 'yes';

            // For AMT timings
            if ( $options['enable_timings'] == '1' ) {
                array_unshift( $cached_content_metadata_arr, sprintf( '<!-- Add-Meta-Tags Timings (milliseconds) - Block total time: %.3f msec - Cached: %s -->', (microtime(true) - $t) * 1000, $is_cached ) );
            }

            // Add our comment
            if ( $options["omit_vendor_html_comments"] == "0" ) {
                array_unshift( $cached_content_metadata_arr, "<!-- BEGIN Schema.org microdata added by the Add-Meta-Tags WordPress plugin -->" );
            }
            array_unshift( $cached_content_metadata_arr, "" );   // Intentionaly left empty
            if ( $options["omit_vendor_html_comments"] == "0" ) {
                array_push( $cached_content_metadata_arr, "<!-- END Schema.org microdata added by the Add-Meta-Tags WordPress plugin -->" );
            }
            array_push( $cached_content_metadata_arr, "" );   // Intentionaly left empty

            //
            // Non persistent cache for metadata review mode.
            //
            // The review mode's content filter should have bigger priority than this
            // filter (be executed after this one). This one by default has priority 9999
            // while the review mode filter has priority of 10000
            if ( amt_check_run_metadata_review_code($options) ) {
                // What happens here is this: we copy the metadata array and remove the
                // item that contains the content data, because it is not needed for
                // metadata review.
                // Then use non-persistent cache to store this new array.
                // Metadata Review mode's filtering function should be attached with a
                // priority bigger than this one, so that it is executed after this one.
                // There the data is loaded from the non-persistent cache.
                // This way, the timings are the same as in the source code and also
                // it is more efficient from a resources perspective.
                // WARNING: in order to work fine, set correct filter priorities.
                $metadata_arr_for_review = $cached_content_metadata_arr;
                if ( array_key_exists('content_data', $metadata_arr_for_review) ) {
                    $metadata_arr_for_review['content_data'] = '    <!-- The content has been removed from the metadata review. -->';
                }
                    wp_cache_add( 'amt_cache_metadata_block_content_filter', $metadata_arr_for_review, $group='add-meta-tags' );
                unset($metadata_arr_for_review);
            }

            // Return cached metadata (contains the post body)
            return implode( PHP_EOL, $cached_content_metadata_arr );
        }
    }


    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return $post_body;
    }

    // Check if the microdata or the JSON-LD schema.org generator should be used.
    if ( $options["schemaorg_force_jsonld"] == "1" ) {
        return $post_body;
    }

    // Check for AMP page https://www.ampproject.org/
    // For AMP pages we do not generate Schema.org microdata around the post content,
    // but enforce the JSON+LD form.
    if ( $do_auto_schemaorg && function_exists('is_amp_endpoint') && is_amp_endpoint() ) {
        return $post_body;
    }

    if ( is_feed() ) {
        return $post_body;
    }

    if ( ! is_singular() || is_front_page() ) {  // is_front_page() is used for the case in which a static page is used as the front page.
        // In this filter function we only deal with content and attachments.
        return $post_body;
    }

    // Additional check to make sure we have a post.
    if ( $post->ID == 0 ) {
        // Can happen with some BuddyPress pages (eg member page)
        return $post_body;
    }

    $metadata_arr = array();

    // Since this is a function that is hooked to the 'the_content' filter
    // of WordPress, the post type check has not run, so this happens here.
    // Check if metadata is supported on this content type.
    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, amt_get_supported_post_types() ) ) {
        return $post_body;
    }

    // Get an array containing the attachments
    $attachments = amt_get_ordered_attachments( $post );
    //var_dump($attachments);

    // Get an array containing the URLs of the embedded media
    $embedded_media = amt_get_embedded_media( $post );
    //var_dump($embedded_media);


    // Custom content override
    if ( amt_is_custom($post, $options) ) {
        // Return metadata with:
        // add_filter( 'amt_custom_metadata_schemaorg_content_filter', 'my_function', 10, 5 );
        // Return an array of meta tags. Array item format: ['key_can_be_whatever'] = '<meta name="foo" content="bar" />'
        $metadata_arr = apply_filters( 'amt_custom_metadata_schemaorg_content_filter', $metadata_arr, $post, $options, $attachments, $embedded_media );

    // Products
    } elseif ( amt_is_product() ) {

        // Scope BEGIN: Product: http://schema.org/Product
        $metadata_arr[] = '<!-- Scope BEGIN: Product -->';
        $metadata_arr[] = '<div itemscope itemtype="http://schema.org/Product"' . amt_get_schemaorg_entity_id_as_itemid('product') . amt_get_schemaorg_itemref('product') . '>';

        // URL - Uses amt_get_permalink_for_multipage()
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( amt_get_permalink_for_multipage($post) ) . '" />';

        // mainEntityOfPage
        $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url( amt_get_permalink_for_multipage($post) ) . '" />';

        // name
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( amt_process_paged( strip_tags( get_the_title($post->ID) ) ) ) . '" />';

        // headline - contains title information
        //$metadata_arr['microdata:headline'] = '<meta itemprop="headline" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';

        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        //if ( empty($content_desc) ) {
        //    // Use the post body as the description. Product objects do not support body text.
        //    $content_desc = sanitize_text_field( amt_sanitize_description( $post_body ) );
        //}
        if ( ! empty($content_desc) ) {
            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        // Dates
        $metadata_arr[] = '<meta itemprop="releaseDate" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';

        // Images

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_product', 'full' );
            $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            }
        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
        //    $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
        //    $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
        //    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

        // Further image processing
        } else {

            // Set to true if any image attachments are found. Use to finally add the default image
            // if no image attachments have been found.
            $has_images = false;

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            // Image - Featured image is checked first, so that it can be the first associated image.
            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Get the image attachment object
                $image = get_post( get_post_thumbnail_id( $post->ID ) );
                // metadata BEGIN
                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                // Allow filtering of the image size.
                $image_size = apply_filters( 'amt_image_size_product', 'full' );
                // Get image metatags.
                $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $options, $image, $size=$image_size ) );
                // metadata END
                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                // Images have been found.
                $has_images = true;
            }
            // Scope END: ImageObject

            // If no images have been found so far use the default image, if set.
            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            if ( $has_images === false ) {

                $image_data = amt_get_default_image_data();
                if ( ! empty($image_data) ) {
                    $image_size = apply_filters( 'amt_image_size_product', 'full' );
                    $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
                    if ( ! empty($image_meta_tags) ) {
                        $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                        $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                        $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                        $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                    }
                }

                //$metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                //$metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
                //$metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
                //$metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            }
            // Scope END: ImageObject

        }

        // Extend the current metadata with properties of the Product object.
        // See: http://schema.org/Product
        $metadata_arr = apply_filters( 'amt_product_data_schemaorg', $metadata_arr, $post );

        // Scope END: Product
        $metadata_arr[] = '</div> <!-- Scope END: Product -->';

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_schemaorg_metadata_product', $metadata_arr );

        // Add post body
        // Remove last closing '</div>' tag, add post body and re-add the closing div afterwards.
        $closing_product_tag = array_pop($metadata_arr);
        // Product objects do not support a 'text' itemprop. We just add a div for now
        // for consistency with Article objects.
        // TODO: it should allow filtering '<div>'
        $metadata_arr[] = '<div> <!-- Product text body: BEGIN -->';
        $metadata_arr['content_data'] = $post_body;
        $metadata_arr[] = '</div> <!-- Product text body: END -->';
        // Now add closing tag for Article
        $metadata_arr[] = $closing_product_tag;

    // Attachemnts
    } elseif ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        // Early metatags - Scope starts

        if ( 'image' == $attachment_type ) {

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/ImageObject"' . amt_get_schemaorg_itemref('attachment_image') . '>';

        } elseif ( 'video' == $attachment_type ) {

            // Scope BEGIN: VideoObject: http://schema.org/VideoObject
            $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/VideoObject"' . amt_get_schemaorg_itemref('attachment_video') . '>';

        } elseif ( 'audio' == $attachment_type ) {

            // Scope BEGIN: AudioObject: http://schema.org/AudioObject
            $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/AudioObject"' . amt_get_schemaorg_itemref('attachment_audio') . '>';

        } else {
            // we do not currently support other attachment types, so we stop processing here
            return $post_body;
        }

        // Metadata common to all attachments

        // Do not add a publisher on personal websites (static front page is source of author profile).
        if ( $options['author_profile_source'] != 'frontpage' || ! amt_has_page_on_front() ) {

            // Publisher
            // Scope BEGIN: Organization: http://schema.org/Organization
            $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
            $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . '>';
            // Get publisher metatags
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options, $post->post_author ) );
            // Scope END: Organization
            $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        }

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
        // Get author metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $post->post_author, $options ) );
        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        // name
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( strip_tags( get_the_title($post->ID) ) ) . '" />';

        // headline - contains title information
        $metadata_arr['microdata:headline'] = '<meta itemprop="headline" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';

        // Description - We use the description defined by Add-Meta-Tags
        $content_desc = amt_get_content_description($post);
        if ( ! empty($content_desc) ) {
            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $content_desc ) . '" />';
        }

        // Dates
        $metadata_arr[] = '<meta itemprop="datePublished" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
        $metadata_arr[] = '<meta itemprop="dateModified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
        $metadata_arr[] = '<meta itemprop="copyrightYear" content="' . esc_attr( mysql2date('Y', $post->post_date) ) . '" />';

        // Language
        $metadata_arr[] = '<meta itemprop="inLanguage" content="' . esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) ) . '" />';

        // Thumbnail
        // A featured image is supported by video and audio attachments.
        // If one is set, then it is set as the thumbnail of the video/audio object.
        if ( 'video' == $attachment_type || 'audio' == $attachment_type ) {
            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Thumbnail URL of the featured image
                $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
                $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $image_size );
                $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
                // Currently we do not add a full ImageObject for the attachment's featured image.
                // TODO: future
            }
        }

        // mainEntityOfPage
        $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url( get_permalink( $post->ID ) ) . '" />';

        // Metadata specific to each attachment type

        if ( 'image' == $attachment_type ) {

            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
            // Get image metatags. $post is an image object.
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $options, $post, $size=$image_size, $is_representative=true ) );
            // Add the post body here
            $metadata_arr['content_data'] = $post_body;
            // Scope END: ImageObject
            $metadata_arr[] = '</div> <!-- Scope END: ImageObject -->';

        } elseif ( 'video' == $attachment_type ) {

            // Video specific metatags

            // URL (links to image file)
            //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';

            // sameAs (links to attachment page)
            $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';

            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';
            $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
            // Required by Google
            $metadata_arr[] = '<meta itemprop="uploadDate" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
            // Add the post body here
            $metadata_arr['content_data'] = $post_body;
            // Scope END: VideoObject
            $metadata_arr[] = '</div> <!-- Scope END: VideoObject -->';

        } elseif ( 'audio' == $attachment_type ) {

            // Audio specific metatags

            // URL (links to image file)
            //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';
            $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';

            // sameAs (links to attachment page)
            $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( get_permalink( $post->ID ) ) . '" />';

            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($post->ID) ) . '" />';
            $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
            // Add the post body here
            $metadata_arr['content_data'] = $post_body;
            // Scope END: AudioObject
            $metadata_arr[] = '</div> <!-- Scope END: AudioObject -->';

        }

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_schemaorg_metadata_attachment', $metadata_arr );


    // Content
    } else {

        // Set main metadata entity. By default this set to Article.
        $main_content_object = 'Article';
        // Check for Page
        // Main entity is set to WebPage on pages
        // DEV NOTE: Since many themes already set the WebPage itemscope on the
        // body element of the web page, set it to WebPage automatically would
        // result in duplicate entities. So this has to be done manually via
        // a filtering function.
//        if  ( is_page() ) {
//            $main_content_object = 'WebPage';
//        }
        // Check for Review
        $review_data = amt_get_review_data($post);
        if ( ! empty($review_data) ) {
            $main_content_object = 'Review';
        }
        // Allow filtering the main metadata object for content.
        $main_content_object = apply_filters( 'amt_schemaorg_object_main', $main_content_object );

        // Scope BEGIN: Article: http://schema.org/Article
        $metadata_arr[] = '<!-- Scope BEGIN: ' . esc_attr($main_content_object) . ' -->';
        $metadata_arr[] = '<div itemscope itemtype="http://schema.org/' . esc_attr($main_content_object) . '"' . amt_get_schemaorg_itemref('content') . '>';

        // Do not add a publisher on personal websites (static front page is source of author profile).
        if ( $options['author_profile_source'] != 'frontpage' || ! amt_has_page_on_front() ) {

            // Publisher
            // Scope BEGIN: Organization: http://schema.org/Organization
            $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
            $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . '>';
            // Get publisher metatags
            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options, $post->post_author ) );
            // Scope END: Organization
            $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        }

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
        // Get publisher metatags
        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $post->post_author, $options ) );
        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        // URL - Uses amt_get_permalink_for_multipage()
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( amt_get_permalink_for_multipage($post) ) . '" />';

        // mainEntityOfPage
        $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url( amt_get_permalink_for_multipage($post) ) . '" />';

        // Dates
        $metadata_arr[] = '<meta itemprop="datePublished" content="' . esc_attr( amt_iso8601_date($post->post_date) ) . '" />';
        $metadata_arr[] = '<meta itemprop="dateModified" content="' . esc_attr( amt_iso8601_date($post->post_modified) ) . '" />';
        $metadata_arr[] = '<meta itemprop="copyrightYear" content="' . esc_attr( mysql2date('Y', $post->post_date) ) . '" />';

        // Language
        $metadata_arr[] = '<meta itemprop="inLanguage" content="' . esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) ) . '" />';

        // name
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( amt_process_paged( strip_tags( get_the_title($post->ID) ) ) ) . '" />';

        // headline - contains title information
        $metadata_arr['microdata:headline'] = '<meta itemprop="headline" content="' . esc_attr( amt_get_title_for_metadata($options, $post) ) . '" />';

        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( amt_process_paged( $content_desc ) ) . '" />';
        }

        /*
        // Section: We use the first category as the section
        $first_cat = sanitize_text_field( amt_sanitize_keywords( amt_get_first_category($post) ) );
        if (!empty($first_cat)) {
            $metadata_arr[] = '<meta itemprop="articleSection" content="' . esc_attr( $first_cat ) . '" />';
        }
        */
        // Add articleSection in Article object only.
        if ( $main_content_object == 'Article' ) {
            $categories = get_the_category($post->ID);
            $categories = apply_filters( 'amt_post_categories_for_schemaorg', $categories );
            foreach( $categories as $cat ) {
                $section = trim( $cat->cat_name );
                if ( ! empty( $section ) && $cat->slug != 'uncategorized' ) {
                    $metadata_arr[] = '<meta itemprop="articleSection" content="' . esc_attr( $section ) . '" />';
                }
            }
        }

        // Add review properties if Review
        if ( $main_content_object == 'Review' ) {
            $metadata_arr[] = '<!-- Review Information BEGIN -->';
            $metadata_arr[] = amt_get_review_info_box( $review_data );
            $metadata_arr[] = '<!-- Review Information END -->';
        }

        // Keywords - We use the keywords defined by Add-Meta-Tags
        $keywords = amt_get_content_keywords($post);
        if (!empty($keywords)) {
            $metadata_arr[] = '<meta itemprop="keywords" content="' . esc_attr( $keywords ) . '" />';
        }

        // Referenced Items
        $referenced_url_list = amt_get_referenced_items($post);
        foreach ($referenced_url_list as $referenced_url) {
            $referenced_url = trim($referenced_url);
            if ( ! empty( $referenced_url ) ) {
                $metadata_arr[] = '<meta itemprop="referencedItem" content="' . esc_url_raw( $referenced_url ) . '" />';
            }
        }

        // Images

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_content', 'full' );
            $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_tags) ) {
                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            }
        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
        //    $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
        //    $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
        //    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $global_image_override_url ) . '" />';
        //    $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

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

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            // Image - Featured image is checked first, so that it can be the first associated image.
            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Thumbnail URL
                // First add the thumbnail URL of the featured image
                $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
                $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
                // Add full image object for featured image.
                // Get the image attachment object
                $image = get_post( get_post_thumbnail_id( $post->ID ) );
                // metadata BEGIN
                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                // Allow filtering of the image size.
                $image_size = apply_filters( 'amt_image_size_content', 'full' );
                // Get image metatags.
                $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $options, $image, $size=$image_size ) );
                // metadata END
                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                // Finally, set the $featured_image_id
                $featured_image_id = get_post_thumbnail_id( $post->ID );
                // Images have been found.
                $has_images = true;
                // Increase image counter
                $ic++;
            }
            // Scope END: ImageObject


            // Process all attachments and add metatags (featured image will be excluded)
            foreach( $attachments as $attachment ) {

                // Excluded the featured image since 
                if ( $attachment->ID != $featured_image_id ) {
                    
                    $mime_type = get_post_mime_type( $attachment->ID );
                    //$attachment_type = strstr( $mime_type, '/', true );
                    // See why we do not use strstr(): http://www.codetrax.org/issues/1091
                    $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

                    if ( 'image' == $attachment_type && $ic < $image_limit ) {

                        // metadata BEGIN
                        $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                        $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                        // Allow filtering of the image size.
                        $image_size = apply_filters( 'amt_image_size_content', 'full' );
                        // Get image metatags.
                        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $options, $attachment, $size=$image_size ) );
                        // metadata END
                        $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

                        // Images have been found.
                        $has_images = true;
                        // Increase image counter
                        $ic++;
                        
                    } elseif ( 'video' == $attachment_type && $vc < $video_limit ) {

                        // Scope BEGIN: VideoObject: http://schema.org/VideoObject
                        $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
                        $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';

                        // Video specific metatags

                        // URL (links to image file)
                        //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';

                        // sameAs (links to attachment page)
                        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';

                        $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';
                        $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
                        // name
                        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( strip_tags( get_the_title($attachment->ID) ) ) . '" />';
                        // Description - We use the description defined by Add-Meta-Tags
                        $content_desc = amt_get_content_description($attachment);
                        if ( ! empty($content_desc) ) {
                            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $content_desc ) . '" />';
                        }
                        // Thumbnail
                        // A featured image is supported by video and audio attachments.
                        // If one is set, then it is set as the thumbnail of the video/audio object.
                        if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $attachment->ID ) ) {
                            // Thumbnail URL of the featured image
                            $image_size = apply_filters( 'amt_image_size_content', 'full' );
                            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($attachment->ID), $image_size );
                            $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
                            // Currently we do not add a full ImageObject for the attachment's featured image.
                            // TODO: future
                        }
                        // uploadDate
                        $metadata_arr[] = '<meta itemprop="uploadDate" content="' . esc_attr( amt_iso8601_date($attachment->post_date) ) . '" />';

                        // Scope END: VideoObject
                        $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';

                        // Increase video counter
                        $vc++;

                    } elseif ( 'audio' == $attachment_type && $ac < $audio_limit ) {

                        // Scope BEGIN: AudioObject: http://schema.org/AudioObject
                        $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
                        $metadata_arr[] = '<span itemprop="audio" itemscope itemtype="http://schema.org/AudioObject">';

                        // Audio specific metatags

                        // URL (links to image file)
                        //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';
                        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';

                        // sameAs (links to attachment page)
                        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( get_permalink( $attachment->ID ) ) . '" />';

                        $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( wp_get_attachment_url($attachment->ID) ) . '" />';
                        $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $mime_type ) . '" />';
                        // name
                        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( strip_tags( get_the_title($attachment->ID) ) ) . '" />';
                        // Description - We use the description defined by Add-Meta-Tags
                        $content_desc = amt_get_content_description($attachment);
                        if ( ! empty($content_desc) ) {
                            $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $content_desc ) . '" />';
                        }
                        // Thumbnail
                        // A featured image is supported by video and audio attachments.
                        // If one is set, then it is set as the thumbnail of the video/audio object.
                        if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $attachment->ID ) ) {
                            // Thumbnail URL of the featured image
                            $image_size = apply_filters( 'amt_image_size_content', 'full' );
                            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($attachment->ID), $image_size );
                            $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_info[0] ) . '" />';
                            // Currently we do not add a full ImageObject for the attachment's featured image.
                            // TODO: future
                        }
                        // uploadDate
                        $metadata_arr[] = '<meta itemprop="uploadDate" content="' . esc_attr( amt_iso8601_date($attachment->post_date) ) . '" />';
                        // Scope END: AudioObject
                        $metadata_arr[] = '</span> <!-- Scope END: AudioObject -->';

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

                // Scope BEGIN: ImageObject: http://schema.org/ImageObject
                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                // name (title)
                $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $embedded_item['alt'] ) . '" />';
                // caption
                $metadata_arr[] = '<meta itemprop="caption" content="' . esc_attr( $embedded_item['alt'] ) . '" />';
                // alt
                $metadata_arr[] = '<meta itemprop="text" content="' . esc_attr( $embedded_item['alt'] ) . '" />';

                // URL (links to image file)
                //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $embedded_item['page'] ) . '" />';
                $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';

                // sameAs (links to attachment page)
                $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $embedded_item['page'] ) . '" />';

                // thumbnail url
                $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
                // main image
                $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $embedded_item['image'] ) . '" />';
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                    $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
                    $metadata_arr[] = '<meta itemprop="encodingFormat" content="image/jpeg" />';
                }
                // embedURL
                $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
                // Scope END: ImageObject
                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

                // Images have been found.
                $has_images = true;
                // Increase image counter
                $ic++;

            }
            foreach( $embedded_media['videos'] as $embedded_item ) {

                if ( $vc == $video_limit ) {
                    break;
                }

                // Scope BEGIN: VideoObject: http://schema.org/VideoObject
                // See: http://googlewebmastercentral.blogspot.gr/2012/02/using-schemaorg-markup-for-videos.html
                // See: https://support.google.com/webmasters/answer/2413309?hl=en
                $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
                $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
                // Video Embed URL
                $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
                // playerType
                $metadata_arr[] = '<meta itemprop="playerType" content="application/x-shockwave-flash" />';
                // size
                $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
                $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
                // Scope END: VideoObject
                $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';

                // Increase video counter
                $vc++;

            }
            foreach( $embedded_media['sounds'] as $embedded_item ) {

                if ( $ac == $audio_limit ) {
                    break;
                }

                // Scope BEGIN: AudioObject: http://schema.org/AudioObject
                $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
                $metadata_arr[] = '<span itemprop="audio" itemscope itemtype="http://schema.org/AudioObject">';
                // Audio Embed URL
                $metadata_arr[] = '<meta itemprop="embedURL" content="' . esc_url_raw( $embedded_item['player'] ) . '" />';
                // playerType
                $metadata_arr[] = '<meta itemprop="playerType" content="application/x-shockwave-flash" />';
                // Scope END: AudioObject
                $metadata_arr[] = '</span> <!-- Scope END: AudioObject -->';

                // Increase audio counter
                $ac++;

            }

            // If no images have been found so far use the default image, if set.
            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            if ( $has_images === false ) {

                $image_data = amt_get_default_image_data();
                if ( ! empty($image_data) ) {
                    $image_size = apply_filters( 'amt_image_size_content', 'full' );
                    $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
                    if ( ! empty($image_meta_tags) ) {
                        $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                        $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                        $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                        $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                    }
                }

                //$metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                //$metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
                //$metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
                //$metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            }
            // Scope END: ImageObject

        }

        // Article Body
        // The article body is added after filtering the generated microdata below.

        // TODO: also check: comments, contributor, copyrightHolder, , creator, dateCreated, discussionUrl, editor, version (use post revision if possible)

        // Scope END: Article
        $metadata_arr[] = '</div> <!-- Scope END: ' . esc_attr($main_content_object) . ' -->';

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_schemaorg_metadata_content', $metadata_arr );

        // Add articleBody to Artice
        // Now add the article. Remove last closing '</span>' tag, add articleBody and re-add the closing span afterwards.
        $closing_article_tag = array_pop($metadata_arr);

        // Use the 'text' itemprop by default for the main text body of the CreativeWork,
        // so it can be used by more subtypes than Article.
        // But set it explicitly to 'articleBody if the main entiry is 'Article'
        // or 'reviewBody' if the main entity is a 'Review'.
        $main_text_property = 'text';
        if ( $main_content_object == 'Article' ) {
            $main_text_property = 'articleBody';
        } elseif ( $main_content_object == 'Review' ) {
            $main_text_property = 'reviewBody';
        }
        // Allow filtering of the main text property.
        $main_text_property = apply_filters( 'amt_schemaorg_property_main_text', $main_text_property );

        $metadata_arr[] = '<div itemprop="' . esc_attr($main_text_property) . '">';
        $metadata_arr['content_data'] = $post_body;
        $metadata_arr[] = '</div> <!-- Itemprop END: ' . esc_attr($main_text_property) . ' -->';
        // Now add closing tag for Article
        $metadata_arr[] = $closing_article_tag;
    }

    // Store transient cache of content metadata
    if ( absint($options['transient_cache_expiration']) > 0 && apply_filters('amt_enable_metadata_cache_in_content_filter', true) ) {
        if ( ! empty($metadata_arr) ) {
            amt_set_transient_cache($post, $options, $metadata_arr, $where='content');
        }
    }

    // Add our comment
    if ( count( $metadata_arr ) > 0 ) {
        // For AMT timings
        if ( $options['enable_timings'] == '1' ) {
            array_unshift( $metadata_arr, sprintf( '<!-- Add-Meta-Tags Timings (milliseconds) - Block total time: %.3f msec - Cached: %s -->', (microtime(true) - $t) * 1000, $is_cached ) );
        }

        if ( $options["omit_vendor_html_comments"] == "0" ) {
            array_unshift( $metadata_arr, "<!-- BEGIN Schema.org microdata added by the Add-Meta-Tags WordPress plugin -->" );
        }
        array_unshift( $metadata_arr, "" );   // Intentionaly left empty
        if ( $options["omit_vendor_html_comments"] == "0" ) {
            array_push( $metadata_arr, "<!-- END Schema.org microdata added by the Add-Meta-Tags WordPress plugin -->" );
        }
        array_push( $metadata_arr, "" );   // Intentionaly left empty
    }

    //
    // Non persistent cache for metadata review mode.
    //
    // The review mode's content filter should have bigger priority than this
    // filter (be executed after this one). This one by default has priority 9999
    // while the review mode filter has priority of 10000
    if ( amt_check_run_metadata_review_code($options) ) {
        // What happens here is this: we copy the metadata array and remove the
        // item that contains the content data, because it is not needed for
        // metadata review.
        // Then use non-persistent cache to store this new array.
        // Metadata Review mode's filtering function should be attached with a
        // priority bigger than this one, so that it is executed after this one.
        // There the data is loaded from the non-persistent cache.
        // This way, the timings are the same as in the source code and also
        // it is more efficient from a resources perspective.
        // WARNING: in order to work fine, set correct filter priorities.
        $metadata_arr_for_review = $metadata_arr;
        if ( array_key_exists('content_data', $metadata_arr_for_review) ) {
            $metadata_arr_for_review['content_data'] = '    <!-- The content has been removed from the metadata review. -->';
        }
        wp_cache_add( 'amt_cache_metadata_block_content_filter', $metadata_arr_for_review, $group='add-meta-tags' );
        unset($metadata_arr_for_review);
    }

    //return $post_body;
    return implode( PHP_EOL, $metadata_arr );
}
// add_filter('the_content', 'amt_add_schemaorg_metadata_content_filter', 500, 1);
// Changed priority to 9999
add_filter('the_content', 'amt_add_schemaorg_metadata_content_filter', 9999, 1);



/**
 * Return an array of Schema.org metatags for the provided $image object.
 * By default, returns metadata for the 'medium' sized version of the image.
 */
function amt_get_schemaorg_image_metatags( $options, $image_data, $size='medium', $is_representative=false ) {

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
    // 3. An attachment object (post object)    (Usually ths is the case in the schema.org generators
    //

    $metadata_arr = array();
    $image = null;

    if ( is_array($image_data) && ! is_null($image_data['url']) ) {
        // Here we process the image data as retrieved from the special notation of the image's URL.
        // No size information is taken into account in this case.
        // Image tags
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url( $image_data['url'] ) . '" />';
        $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url( $image_data['url'] ) . '" />';

        if ( apply_filters( 'amt_extended_image_tags', true ) ) {
            if ( ! is_null($image_data['width']) ) {
                $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $image_data['width'] ) . '" />';
            }
            if ( ! is_null($image_data['height']) ) {
                $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $image_data['height'] ) . '" />';
            }
            if ( ! is_null($image_data['type']) ) {
                $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( $image_data['type'] ) . '" />';
            }
        }

        return $metadata_arr;

    } elseif ( is_array($image_data) && is_numeric($image_data['id']) ) {
        // The attachment ID exists in the array's 'id' item.
        $image = get_post( absint( $image_data['id'] ) );
    } elseif ( is_numeric($image_data) ) {
        // Image data is the attachment ID (integer)
        $image = get_post( absint( $image_data ) );
    } elseif ( is_object($image_data) && isset($image_data->ID) ) {
        // Image data is the attachment itself.
        $image = $image_data;
    }

    if ( is_null($image) || ! is_object($image) || ! isset($image->ID) ) {
        return $metadata_arr;
    }

    // Process the image attachment and generate meta tags.

    // Get the image object <- Already have it
    //$image = get_post( $post_id );

    // Data for image attachments
    $image_meta = wp_get_attachment_metadata( $image->ID );   // contains info about all sizes
    // We use wp_get_attachment_image_src() since it constructs the URLs
    $thumbnail_meta = wp_get_attachment_image_src( $image->ID , 'thumbnail' );
    $main_size_meta = wp_get_attachment_image_src( $image->ID , $size );

    // name (title)
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( strip_tags( get_the_title( $image->ID ) ) ) . '" />';
    // OLD name (title)
    //$image_title = sanitize_text_field( $image->post_title );
    //if ( ! empty( $image_title ) ) {
    //    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $image_title ) . '" />';
    //}

    // URL (links to image file)
    //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $image->ID ) ) . '" />';
    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';

    // sameAS (links to attachment page)
    $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( get_permalink( $image->ID ) ) . '" />';

    // Description (generated from $image->post_content. See: amt_get_the_excerpt()
    $image_description = amt_get_content_description($image);
    if ( ! empty( $image_description ) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $image_description ) . '" />';
    }

    // thumbnail url
    $metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $thumbnail_meta[0] ) . '" />';

    // main image
    $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $main_size_meta[0] ) . '" />';
    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
        $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $main_size_meta[1] ) . '" />';
        $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $main_size_meta[2] ) . '" />';
        $metadata_arr[] = '<meta itemprop="encodingFormat" content="' . esc_attr( get_post_mime_type( $image->ID ) ) . '" />';
    }

    // caption
    // Here we sanitize the provided description for safety
    $image_caption = sanitize_text_field( $image->post_excerpt );
    if ( ! empty( $image_caption ) ) {
        $metadata_arr[] = '<meta itemprop="caption" content="' . esc_attr( $image_caption ) . '" />';
    }

    // alt
    // Here we sanitize the provided description for safety
    $image_alt = sanitize_text_field( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) );
    if ( ! empty( $image_alt ) ) {
        $metadata_arr[] = '<meta itemprop="text" content="' . esc_attr( $image_alt ) . '" />';
    }

    if ( $is_representative === true ) {
        // representativeOfPage - Boolean - Indicates whether this image is representative of the content of the page.
        $metadata_arr[] = '<meta itemprop="representativeOfPage" content="True" />';
    }

    return $metadata_arr;
}


/**
 * Return an array of Schema.org metatags suitable for the publisher object of
 * the content. Accepts the $post object as argument.
 */
function amt_get_schemaorg_publisher_metatags( $options, $author_id=null ) {

    $metadata_arr = array();

    // name
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
    // description
    // First use the site description from the Add-Meta-Tags settings
    $site_description = amt_get_site_description($options);
    if ( empty($site_description) ) {
        // Alternatively, use the blog description
        // Here we sanitize the provided description for safety
        $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
    }
    if ( ! empty($site_description) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $site_description ) . '" />';
    }
    // logo
    $image_data = amt_get_default_image_data();
    if ( ! empty($image_data) ) {
        $image_size = apply_filters( 'amt_image_size_index', 'full' );
        $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
        if ( ! empty($image_meta_tags) ) {
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">';
            // name (title)
            $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( get_bloginfo('name') ) . ' ' . __('logo', 'add-meta-tags') . '" />';
            // caption
            //$metadata_arr[] = '<meta itemprop="caption" content="' . esc_attr( rtrim(get_bloginfo('description'), '.') ) . '." />';
            // alt
            $metadata_arr[] = '<meta itemprop="text" content="' . esc_attr( get_bloginfo('name') ) . ' ' . __('logo', 'add-meta-tags') . '" />';
            // URL (links to web page containing the image)
            //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url( $options["default_image_url"] ) . '" />';
            // thumbnail url
            //$metadata_arr[] = '<meta itemprop="thumbnailUrl" content="' . esc_url_raw( $embedded_item['thumbnail'] ) . '" />';
            //$metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
            $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
        }
    }
    ////$metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url( $options["default_image_url"] ) . '" />';
    //if ( apply_filters( 'amt_extended_image_tags', true ) ) {
    //    $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
    //    $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
    //    $metadata_arr[] = '<meta itemprop="encodingFormat" content="image/jpeg" />';
    //}
    // Scope END: ImageObject
    //$metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

    // url
    // The blog url is used by default. Google+, Facebook and Twitter profile URLs are added as sameAs.
    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '" />';
    // sameAs
    // Social Profile Links are added as sameAs properties
    // By default, those of the Publisher Settings  are used.
    // WARNING: Publisher profile URLs from the user profile page are now deprecated.
    // Google+ Publisher
    if ( ! empty($options['social_main_googleplus_publisher_profile_url']) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $options['social_main_googleplus_publisher_profile_url'], array('http', 'https') ) . '" />';
    }
    // Facebook
    if ( ! empty($options['social_main_facebook_publisher_profile_url']) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $options['social_main_facebook_publisher_profile_url'], array('http', 'https') ) . '" />';
    }
    // Twitter
    if ( ! empty($options['social_main_twitter_publisher_username']) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="https://twitter.com/' . esc_attr( $options['social_main_twitter_publisher_username'] ) . '" />';
    }

    // Allow filtering of the Publisher meta tags
    $metadata_arr = apply_filters( 'amt_schemaorg_publisher_extra', $metadata_arr );

    return $metadata_arr;
}


/**
 * Return an array of Schema.org metatags suitable for the author object of
 * the content. Accepts the $post object as argument.
 */
function amt_get_schemaorg_author_metatags( $author_id, $options ) {
//$author_obj = get_user_by( 'id', $author_id );

    $metadata_arr = array();

    // name
    $display_name = get_the_author_meta('display_name', $author_id);
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $display_name ) . '" />';
    // description
    // Here we sanitize the provided description for safety
    $author_description = sanitize_text_field( amt_sanitize_description( get_the_author_meta('description', $author_id) ) );
    if ( !empty($author_description) ) {
        $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $author_description ) . '" />';
    }

    // Profile Image
    // First use the global image override URL
    $image_data = amt_get_image_data( amt_get_user_meta_image_url( $author_id ) );
    if ( ! empty($image_data) ) {
        $image_size = apply_filters( 'amt_image_size_index', 'full' );
        $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
        if ( ! empty($image_meta_tags) ) {
            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
            $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
            $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
        }
    } else {
        $author_email = sanitize_email( get_the_author_meta('user_email', $author_id) );
        $avatar_size = apply_filters( 'amt_avatar_size', 128 );
        $avatar_url = '';
        // First try to get the avatar link by using get_avatar().
        // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
        $avatar_img = get_avatar( get_the_author_meta('ID', $author_id), $avatar_size, '', get_the_author_meta('display_name', $author_id) );
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
            //$metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $avatar_url ) . '" />';
            $image_data = amt_get_image_data( sprintf('%s,%dx%d', $avatar_url, $avatar_size, $avatar_size) );
            if ( ! empty($image_data) ) {
                $image_size = apply_filters( 'amt_image_size_index', 'full' );
                $image_meta_tags = amt_get_schemaorg_image_metatags( $options, $image_data, $size=$image_size );
                if ( ! empty($image_meta_tags) ) {
                    $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
                    $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                    $metadata_arr = array_merge( $metadata_arr, $image_meta_tags );
                    $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                }
            }
        }
    }

    // url
    // The URL to the author archive is added as the url.
    //$metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_author_posts_url( $author_id ) ) . '" />';
    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( amt_get_local_author_profile_url( $author_id, $options ) ) . '" />';
    // sameAs
    // Social Profile Links are added as sameAs properties
    // Those from the WordPress User Profile page are used.
    // Google+ Author
    $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $author_id);
    if ( !empty($googleplus_author_url) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $googleplus_author_url, array('http', 'https') ) . '" />';
    }
    // Facebook
    $facebook_author_url = get_the_author_meta('amt_facebook_author_profile_url', $author_id);
    if ( !empty($facebook_author_url) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $facebook_author_url, array('http', 'https') ) . '" />';
    }
    // Twitter
    $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $author_id);
    if ( !empty($twitter_author_username) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="https://twitter.com/' . esc_attr( $twitter_author_username ) . '" />';
    }
    // The User URL as set by the user in the WordPress User Profile page.
    $user_url = get_the_author_meta( 'user_url', $author_id );
    if ( !empty($user_url) ) {
        $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url_raw( $user_url, array('http', 'https') ) . '" />';
    }

    // Allow filtering of the Author meta tags
    $metadata_arr = apply_filters( 'amt_schemaorg_author_extra', $metadata_arr, $author_id );

    return $metadata_arr;
}


// Schema.org metadata generator for comments
// Use with:
// add_filter( 'comment_text', 'wwwmonk_comment_schemaorg_metadata', 99999, 1 );
function amt_add_schemaorg_metadata_comment_filter( $comment_text ) {

    global $post, $comment;

    $options = amt_get_options();

    if ( $options["omit_vendor_html_comments"] == "0" ) {
        $metadata_arr[] = '<!-- BEGIN Metadata added by the Add-Meta-Tags WordPress plugin -->';
    }

    $metadata_arr[] = '<!-- Scope BEGIN: UserComments -->';
    $metadata_arr[] = '<div itemprop="comment" itemscope itemtype="http://schema.org/UserComments">';

    // Comment Author
    $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
    $metadata_arr[] = '<span itemprop="creator" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_commenter') . '>';
    // name
    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $comment->comment_author ) . '" />';
    // url
    if ( ! empty( $comment->comment_author_url ) ) {
        // $metadata_arr[] = '<meta itemprop="url" content="' . esc_url( $comment->comment_author_url ) . '" />';
    }
    // gravatar
    if ( ! empty( $comment->comment_author_email ) ) {
        // Contruct gravatar link
        $gravatar_url = "http://www.gravatar.com/avatar/" . md5( $comment->comment_author_email ) . "?s=" . 44;
        $metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $gravatar_url ) . '" />';
    }
    // END
    $metadata_arr[] = '</span> <!-- Scope END: Person -->';

    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url_raw( get_permalink( $post->ID ) . '#comment-' . get_comment_ID() ) . '" />';
    $metadata_arr[] = '<meta itemprop="commentTime" content="' . esc_attr( get_comment_time( 'c' ) ) . '" />';
    $metadata_arr[] = '<meta itemprop="replyToUrl" content="' . get_permalink( $post->ID ) . '?replytocom=' . $comment->comment_ID . '#respond' . '" />';

    $metadata_arr[] = '<div itemprop="commentText">';
    $metadata_arr[] = $comment_text;
    $metadata_arr[] = '</div> <!-- itemprop.commentText -->';

    $metadata_arr[] = '</div> <!-- Scope END: UserComments -->';

    if ( $options["omit_vendor_html_comments"] == "0" ) {
        $metadata_arr[] = '<!-- END Metadata added by the Add-Meta-Tags WordPress plugin -->';
    }

    // Allow filtering of the generated metadata
    $metadata_arr = apply_filters( 'amt_schemaorg_comments_extra', $metadata_arr, $post, $comment );

    return PHP_EOL . implode(PHP_EOL, $metadata_arr) . PHP_EOL . PHP_EOL;
    //return implode( '', $metadata_arr );
}



/**
 * JSON-LD Schema.org Generator
 * ============================
 */





/**
 * Add Schema.org Microdata in the footer.
 *
 * Mainly used to embed microdata to front page, posts index page and archives.
 */
function amt_add_jsonld_schemaorg_metadata_head( $post, $attachments, $embedded_media, $options ) {

    if ( apply_filters('amt_exclude_schemaorg_metadata', false) ) {
        return array();
    }

    $do_auto_schemaorg = (($options["auto_schemaorg"] == "1") ? true : false );
    if (!$do_auto_schemaorg) {
        return array();
    }

    // Check if the microdata or the JSON-LD schema.org generator should be used.
    if ( $options["schemaorg_force_jsonld"] == "0" ) {

        // Here we check for AMP page https://www.ampproject.org/
        // For AMP pages, if the Schema.org microdata generator has been enabled,
        // we enforce the JSON+LD form instead of microdata.
        if ( $do_auto_schemaorg && function_exists('is_amp_endpoint') && is_amp_endpoint() ) {
            // Do nothing and let it proceed with forced generation of JSON+LD Schema.org metadata.
        } else {
            return array();
        }

    }

    $metadata_arr = array();

    // Context
    $metadata_arr['@context'] = 'http://schema.org';


// TODO: Check if this is_paged() check is needed. If removed, make sure that titles and descriptions are passed through multipage function.

    if ( is_paged() ) {
        //
        // Currently we do not support adding Schema.org metadata on
        // paged archives, if page number is >=2
        //
        // NOTE: This refers to an archive or the main page being split up over
        // several pages, this does not refer to a Post or Page whose content
        // has been divided into pages using the <!--nextpage--> QuickTag.
        //
        // Multipage content IS processed below.
        //
        return array();
    }


    // Custom content override
    if ( amt_is_custom($post, $options) ) {
        // Return metadata with:
        // add_filter( 'amt_custom_metadata_jsonld_schemaorg', 'my_function', 10, 5 );
        // Return an array of meta tags. Array item format: ['key_can_be_whatever'] = '<meta name="foo" content="bar" />'
        $metadata_arr = apply_filters( 'amt_custom_metadata_jsonld_schemaorg', $metadata_arr, $post, $options, $attachments, $embedded_media );

    // Default fron tpage displaying the latest posts.

    } elseif ( amt_is_default_front_page() ) {

        // On the front page we are adding two top level entities, so we remove
        // the existing context, as the entities need to be in an array and each
        // array item needs its own context.
        unset( $metadata_arr['@context'] );

        // Organization
        //
        // NOTICE:
        // Even if the front page has been set as the source of profile, this
        // this does not work with the default front page with the latest posts.
        // This is becuase this page does not have an author, which is essential
        // for the generated metadata on a page that is supposed to be a profile.
        // Therefore, an Organization object is always generated on the default
        // front page and it is never treated as a profile page by Add-Meta-Tags.
        //
        $organization_arr = array();
        // Context
        $organization_arr['@context'] = 'http://schema.org';
        // ID
        $organization_arr['@id'] = amt_get_schemaorg_entity_id('organization');

        $organization_arr = array_merge($organization_arr, amt_get_jsonld_schemaorg_publisher_array($options));

        // mainEntityOfPage
        $organization_arr['mainEntityOfPage'] = esc_url( trailingslashit( get_bloginfo('url') ) );

        // Get publisher/mainEntity metatags
//        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options ) );


        // WebSite
        $website_arr = array();
        // Context
        $website_arr['@context'] = 'http://schema.org';
        // ID
        $website_arr['@id'] = amt_get_schemaorg_entity_id('website');
        // Type
        $website_arr['@type'] = 'WebSite';
        // name
        $website_arr['name'] = esc_attr( get_bloginfo('name') );

        // headline - contains title information
        $website_arr['headline'] = esc_attr( amt_get_title_for_metadata($options, $post) );

        // alternateName (The WordPress tag line is used.)
        // TODO: use tag line. Needs feedback!
        // url
        $website_arr['url'] = esc_url_raw( trailingslashit( get_bloginfo('url') ) );

        // publisher
//        $website_arr['publisher'] = array( '@id' => esc_attr(amt_get_schemaorg_entity_id('organization') ) );

        // SearchAction
        // Scope BEGIN: SearchAction: http://schema.org/SearchAction
//        $metadata_arr[] = '<!-- Scope BEGIN: SearchAction -->';
//        $metadata_arr[] = '<span itemprop="potentialAction" itemscope itemtype="http://schema.org/SearchAction">';
        $website_arr['potentialAction'] = array();
        $website_arr['potentialAction']['@type'] = 'SearchAction';

        // target
        // Scope BEGIN: EntryPoint: http://schema.org/EntryPoint
        $website_arr['potentialAction']['target'] = array();
        $website_arr['potentialAction']['target']['@type'] = 'EntryPoint';
        // urlTemplate
        $website_arr['potentialAction']['target']['urlTemplate'] = esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '?s={search_term}';
        // Scope END: EntryPoint
//        $metadata_arr[] = '</span> <!-- Scope END: EntryPoint -->';
        // query-input
        // Scope BEGIN: PropertyValueSpecification: http://schema.org/PropertyValueSpecification
        //$metadata_arr[] = '<span itemprop="query-input" itemscope itemtype="http://schema.org/PropertyValueSpecification">';
        $website_arr['potentialAction']['query-input'] = array();
        $website_arr['potentialAction']['query-input']['@type'] = 'PropertyValueSpecification';
        // valueRequired
        $website_arr['potentialAction']['query-input']['valueRequired'] = 'True';
        // valueName
        $website_arr['potentialAction']['query-input']['valueName'] = 'search_term';
        // Scope END: PropertyValueSpecification
//        $metadata_arr[] = '</span> <!-- Scope END: PropertyValueSpecification -->';
        // Scope END: SearchAction
//        $metadata_arr[] = '</span> <!-- Scope END: SearchAction -->';

        // Scope END: WebSite
//        $metadata_arr[] = '</span> <!-- Scope END: WebSite -->';

        $metadata_arr = array( $organization_arr, $website_arr );


    // Front page using a static page

    } elseif ( amt_is_static_front_page() ) {

        // On the front page we are adding two top level entities, so we remove
        // the existing context, as the entities need to be in an array and each
        // array item needs its own context.
        unset( $metadata_arr['@context'] );

        $main_entity_arr = array();

        // Context
        $main_entity_arr['@context'] = 'http://schema.org';

        if ( $options['author_profile_source'] == 'frontpage' ) {

            // ID
            $main_entity_arr['@id'] = amt_get_schemaorg_entity_id('person');

            // Author
            // Scope BEGIN: Person: http://schema.org/Person
            //        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
            //        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
            // Get author metatags
            $main_entity_arr = array_merge($main_entity_arr, amt_get_jsonld_schemaorg_author_array( $post->post_author, $options ));

            // mainEntityOfPage
            $main_entity_arr['mainEntityOfPage'] = esc_url( trailingslashit( get_bloginfo('url') ) );

            // Scope END: Person
            //        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        } else {

            // ID
            $main_entity_arr['@id'] = amt_get_schemaorg_entity_id('organization');

            // Organization
            $main_entity_arr = array_merge($main_entity_arr, amt_get_jsonld_schemaorg_publisher_array($options));

            // mainEntityOfPage
            $main_entity_arr['mainEntityOfPage'] = esc_url( trailingslashit( get_bloginfo('url') ) );

            // Get publisher/mainEntity metatags
//            $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options ) );
        }

        // WebSite
        $website_arr = array();
        // Context
        $website_arr['@context'] = 'http://schema.org';
        // ID
        $website_arr['@id'] = amt_get_schemaorg_entity_id('website');
        // Type
        $website_arr['@type'] = 'WebSite';
        // name
        $website_arr['name'] = esc_attr( get_bloginfo('name') );

        // headline - contains title information
        $website_arr['headline'] = esc_attr( amt_get_title_for_metadata($options, $post) );

        // alternateName (The WordPress tag line is used.)
        // TODO: use tag line. Needs feedback!
        // url
        $website_arr['url'] = esc_url_raw( trailingslashit( get_bloginfo('url') ) );

//        if ( $options['author_profile_source'] == 'frontpage' ) {
            // author
            //$website_arr['author'] = array( '@id' => esc_attr(amt_get_schemaorg_entity_id('person') ) );
//        } else {
            // publisher
            //$website_arr['publisher'] = array( '@id' => esc_attr(amt_get_schemaorg_entity_id('organization') ) );
//        }

        // SearchAction
        // Scope BEGIN: SearchAction: http://schema.org/SearchAction
//        $metadata_arr[] = '<!-- Scope BEGIN: SearchAction -->';
//        $metadata_arr[] = '<span itemprop="potentialAction" itemscope itemtype="http://schema.org/SearchAction">';
        $website_arr['potentialAction'] = array();
        $website_arr['potentialAction']['@type'] = 'SearchAction';

        // target
        // Scope BEGIN: EntryPoint: http://schema.org/EntryPoint
        $website_arr['potentialAction']['target'] = array();
        $website_arr['potentialAction']['target']['@type'] = 'EntryPoint';
        // urlTemplate
        $website_arr['potentialAction']['target']['urlTemplate'] = esc_url_raw( trailingslashit( get_bloginfo('url') ) ) . '?s={search_term}';
        // Scope END: EntryPoint
//        $metadata_arr[] = '</span> <!-- Scope END: EntryPoint -->';
        // query-input
        // Scope BEGIN: PropertyValueSpecification: http://schema.org/PropertyValueSpecification
        //$metadata_arr[] = '<span itemprop="query-input" itemscope itemtype="http://schema.org/PropertyValueSpecification">';
        $website_arr['potentialAction']['query-input'] = array();
        $website_arr['potentialAction']['query-input']['@type'] = 'PropertyValueSpecification';
        // valueRequired
        $website_arr['potentialAction']['query-input']['valueRequired'] = 'True';
        // valueName
        $website_arr['potentialAction']['query-input']['valueName'] = 'search_term';
        // Scope END: PropertyValueSpecification
//        $metadata_arr[] = '</span> <!-- Scope END: PropertyValueSpecification -->';
        // Scope END: SearchAction
//        $metadata_arr[] = '</span> <!-- Scope END: SearchAction -->';

        // Scope END: WebSite
//        $metadata_arr[] = '</span> <!-- Scope END: WebSite -->';

        $metadata_arr = array( $main_entity_arr, $website_arr );


    // Author archive

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

        // Person
        // Scope BEGIN: Person: http://schema.org/Person
//        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
//        $metadata_arr[] = '<span itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
        $metadata_arr['@type'] = 'Person';

        // headline - contains title information
        // NOTE: Not supported for person
        //$metadata_arr['headline'] = esc_attr( amt_get_title_for_metadata($options, $author) );

        // Get author metatags
//        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $author->ID, $options ) );
        $metadata_arr = array_merge( $metadata_arr, amt_get_jsonld_schemaorg_author_array( $author->ID, $options ) );

        // mainEntityOfPage
        $metadata_arr['mainEntityOfPage'] = esc_url( get_author_posts_url( $author->ID ) );

        // Scope END: Person
//        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

    }


    // Products
    elseif ( amt_is_product() ) {

        // Scope BEGIN: Product: http://schema.org/Product
//        $metadata_arr[] = '<!-- Scope BEGIN: Product -->';
//        $metadata_arr[] = '<div itemscope itemtype="http://schema.org/Product"' . amt_get_schemaorg_itemref('product') . '>';
        // Schema.org type
        $metadata_arr['@type'] = 'Product';

        // ID
        $metadata_arr['@id'] = amt_get_schemaorg_entity_id('product');

        // URL - Uses amt_get_permalink_for_multipage()
        $metadata_arr['url'] = esc_url_raw( amt_get_permalink_for_multipage($post) );

        // mainEntityOfPage
        $metadata_arr['mainEntityOfPage'] = esc_url( amt_get_permalink_for_multipage($post) );

        // name
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr['name'] = esc_attr( amt_process_paged( strip_tags( get_the_title($post->ID) ) ) );

        // headline - contains title information
        //$metadata_arr['headline'] = esc_attr( amt_get_title_for_metadata($options, $post) );

        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        //if ( empty($content_desc) ) {
        //    // Use the post body as the description. Product objects do not support body text.
        //    // TODO: ERROR here $post_body var does not exist
        //    $content_desc = sanitize_text_field( amt_sanitize_description( $post_body ) );
        //}
        if ( ! empty($content_desc) ) {
            $metadata_arr['description'] = esc_attr( amt_process_paged( $content_desc ) );
        }

        // Dates
        $metadata_arr['releaseDate'] = esc_attr( amt_iso8601_date($post->post_date) );

        // Images
        $metadata_arr['image'] = array();

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_product', 'full' );
            $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_array) ) {
                $metadata_arr['image'] = array();
                $metadata_arr['image'][] = $image_meta_array;
            }

        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
//            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//            $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';

         //   $current_image_obj = array();
         //   $current_image_obj['@type'] = 'ImageObject';
         //   $current_image_obj['url'] = esc_url_raw( $global_image_override_url );
         //   $current_image_obj['contentUrl'] = esc_url_raw( $global_image_override_url );
         //   $metadata_arr['image'][] = $current_image_obj;
//            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

        // Further image processing
        } else {

            // Set to true if any image attachments are found. Use to finally add the default image
            // if no image attachments have been found.
            $has_images = false;

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            // Image - Featured image is checked first, so that it can be the first associated image.
            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Get the image attachment object
                $image = get_post( get_post_thumbnail_id( $post->ID ) );
                // metadata BEGIN
//                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                // Allow filtering of the image size.
                $image_size = apply_filters( 'amt_image_size_product', 'full' );
                // Get image metatags.
                $metadata_arr['image'][] = amt_get_jsonld_schemaorg_image_array( $options, $image, $size=$image_size );
                // metadata END
//                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                // Images have been found.
                $has_images = true;
            }
            // Scope END: ImageObject

            // If no images have been found so far use the default image, if set.
            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            if ( $has_images === false ) {

                $image_data = amt_get_default_image_data();
                if ( ! empty($image_data) ) {
                    $image_size = apply_filters( 'amt_image_size_product', 'full' );
                    $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size=$image_size );
                    if ( ! empty($image_meta_array) ) {
                        $metadata_arr['image'] = array();
                        $metadata_arr['image'][] = $image_meta_array;
                    }
                }

//                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                //$current_image_obj = array();
                //$current_image_obj['@type'] = 'ImageObject';
                //$current_image_obj['url'] = esc_url_raw( $options["default_image_url"] );
                //$current_image_obj['contentUrl'] = esc_url_raw( $options["default_image_url"] );
                //$metadata_arr['image'][] = $current_image_obj;
//                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            }
            // Scope END: ImageObject

        }

        // Extend the current metadata with properties of the Product object.
        // See: http://schema.org/Product
        $metadata_arr = apply_filters( 'amt_product_data_jsonld_schemaorg', $metadata_arr, $post );

        // Scope END: Product
//        $metadata_arr[] = '</div> <!-- Scope END: Product -->';

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_jsonld_schemaorg_metadata_product', $metadata_arr );

        // Add post body
        // Remove last closing '</div>' tag, add post body and re-add the closing div afterwards.
//        $closing_product_tag = array_pop($metadata_arr);
        // Product objects do not support a 'text' itemprop. We just add a div for now
        // for consistency with Article objects.
        // TODO: it should allow filtering '<div>'
//        $metadata_arr[] = '<div> <!-- Product text body: BEGIN -->';
//        $metadata_arr[] = $post_body;
//        $metadata_arr[] = '</div> <!-- Product text body: END -->';
        // Now add closing tag for Article
//        $metadata_arr[] = $closing_product_tag;


    // Attachemnts
    } elseif ( is_attachment() ) {

        $mime_type = get_post_mime_type( $post->ID );
        //$attachment_type = strstr( $mime_type, '/', true );
        // See why we do not use strstr(): http://www.codetrax.org/issues/1091
        $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

        // Early metatags - Scope starts

        if ( 'image' == $attachment_type ) {

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
//            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/ImageObject"' . amt_get_schemaorg_itemref('attachment_image') . '>';

        } elseif ( 'video' == $attachment_type ) {

            // Scope BEGIN: VideoObject: http://schema.org/VideoObject
//            $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
//            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/VideoObject"' . amt_get_schemaorg_itemref('attachment_video') . '>';
            // Schema.org type
            $metadata_arr['@type'] = 'VideoObject';

        } elseif ( 'audio' == $attachment_type ) {

            // Scope BEGIN: AudioObject: http://schema.org/AudioObject
//            $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
//            $metadata_arr[] = '<div itemscope itemtype="http://schema.org/AudioObject"' . amt_get_schemaorg_itemref('attachment_audio') . '>';
            // Schema.org type
            $metadata_arr['@type'] = 'AudioObject';

        } else {
            // we do not currently support other attachment types, so we stop processing here
            return array();
        }

        // Metadata common to all attachments

        // Do not add a publisher on personal websites (static front page is source of author profile).
        if ( $options['author_profile_source'] != 'frontpage' || ! amt_has_page_on_front() ) {

            // Publisher
            // Scope BEGIN: Organization: http://schema.org/Organization
    //        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
    //        $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . '>';
            // Get publisher metatags
    //        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options, $post->post_author ) );
            $metadata_arr['publisher'] = amt_get_jsonld_schemaorg_publisher_array($options, $post->post_author);
            // Scope END: Organization
    //        $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        }

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
//        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
//        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
        // Get author metatags
        $metadata_arr['author'] = amt_get_jsonld_schemaorg_author_array( $post->post_author, $options );
        // Scope END: Person
//        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        // name
        $metadata_arr['name'] = esc_attr( strip_tags( get_the_title($post->ID) ) );

        // headline - contains title information
        $metadata_arr['headline'] = esc_attr( amt_get_title_for_metadata($options, $post) );

        // Description - We use the description defined by Add-Meta-Tags
        $content_desc = amt_get_content_description($post);
        if ( ! empty($content_desc) ) {
            $metadata_arr['description'] = esc_attr( $content_desc );
        }

        // Dates
        $metadata_arr['datePublished'] = esc_attr( amt_iso8601_date($post->post_date) );
        $metadata_arr['dateModified'] = esc_attr( amt_iso8601_date($post->post_modified) );
        $metadata_arr['copyrightYear'] = esc_attr( mysql2date('Y', $post->post_date) );

        // Language
        $metadata_arr['inLanguage'] = esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) );

        // Thumbnail
        // A featured image is supported by video and audio attachments.
        // If one is set, then it is set as the thumbnail of the video/audio object.
        if ( 'video' == $attachment_type || 'audio' == $attachment_type ) {
            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Thumbnail URL of the featured image
                $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
                $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $image_size );
                $metadata_arr['thumbnailUrl'] = esc_url_raw( $thumbnail_info[0] );
                // Currently we do not add a full ImageObject for the attachment's featured image.
                // TODO: future
            }
        }

        // mainEntityOfPage
        $metadata_arr['mainEntityOfPage'] = esc_url( get_permalink( $post->ID ) );

        // Metadata specific to each attachment type

        if ( 'image' == $attachment_type ) {

            // Allow filtering of the image size.
            $image_size = apply_filters( 'amt_image_size_attachment', 'full' );
            // Get image metatags. $post is an image object.
            $metadata_arr = array_merge( $metadata_arr, amt_get_jsonld_schemaorg_image_array( $options, $post, $size=$image_size, $is_representative=true ) );

            // Add the post body here
//            $metadata_arr[] = $post_body;
            // Scope END: ImageObject
//            $metadata_arr[] = '</div> <!-- Scope END: ImageObject -->';

        } elseif ( 'video' == $attachment_type ) {

            // Video specific metatags

            // URL (links to image file)
            //$metadata_arr['url'] = esc_url_raw( get_permalink( $post->ID ) );
            $metadata_arr['url'] = esc_url_raw( wp_get_attachment_url($post->ID) );

            // sameAs (links to attachment page)
            $metadata_arr['sameAs'] = esc_url_raw( get_permalink( $post->ID ) );

            $metadata_arr['contentUrl'] = esc_url_raw( wp_get_attachment_url($post->ID) );
            $metadata_arr['encodingFormat'] = esc_attr( $mime_type );
            // Required by Google
            $metadata_arr['uploadDate'] = esc_attr( amt_iso8601_date($post->post_date) );
            // Add the post body here
//            $metadata_arr[] = $post_body;
            // Scope END: VideoObject
//            $metadata_arr[] = '</div> <!-- Scope END: VideoObject -->';

        } elseif ( 'audio' == $attachment_type ) {

            // Audio specific metatags

            // URL (links to image file)
            //$metadata_arr['url'] = esc_url_raw( get_permalink( $post->ID ) );
            $metadata_arr['url'] = esc_url_raw( wp_get_attachment_url($post->ID) );

            // sameAs (links to attachment page)
            $metadata_arr['sameAs'] = esc_url_raw( get_permalink( $post->ID ) );

            $metadata_arr['contentUrl'] = esc_url_raw( wp_get_attachment_url($post->ID) );
            $metadata_arr['encodingFormat'] = esc_attr( $mime_type );
            // Add the post body here
//            $metadata_arr[] = $post_body;
            // Scope END: AudioObject
//            $metadata_arr[] = '</div> <!-- Scope END: AudioObject -->';

        }

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_jsonld_schemaorg_metadata_attachment', $metadata_arr );


    // Content
    // Posts, pages, custom content types (attachments excluded, caught in previous clause)
    // Note: content might be multipage. Process with amt_process_paged() wherever needed.
    } elseif ( is_singular() ) {

        // Set main metadata entity. By default this set to Article.
        $main_content_object = 'Article';
        // Check for Page
        // Main entity is set to WebPage on pages
        // DEV NOTE: Since many themes already set the WebPage itemscope on the
        // body element of the web page, set it to WebPage automatically would
        // result in duplicate entities. So this has to be done manually via
        // a filtering function.
//        if  ( is_page() ) {
//            $main_content_object = 'WebPage';
//        }
        // Check for Review
        $review_data = amt_get_review_data($post);
        if ( ! empty($review_data) ) {
            $main_content_object = 'Review';
        }
        // Allow filtering the main metadata object for content.
        $main_content_object = apply_filters( 'amt_schemaorg_object_main', $main_content_object );

        // Scope BEGIN: Article: http://schema.org/Article
//        $metadata_arr[] = '<!-- Scope BEGIN: ' . esc_attr($main_content_object) . ' -->';
//        $metadata_arr[] = '<div itemscope itemtype="http://schema.org/' . esc_attr($main_content_object) . '"' . amt_get_schemaorg_itemref('content') . '>';
        // Schema.org type
        $metadata_arr['@type'] = esc_attr($main_content_object);

        // Do not add a publisher on personal websites (static front page is source of author profile).
        if ( $options['author_profile_source'] != 'frontpage' || ! amt_has_page_on_front() ) {

            // Publisher
            // Scope BEGIN: Organization: http://schema.org/Organization
    //        $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
    //        $metadata_arr[] = '<span itemprop="publisher" itemscope itemtype="http://schema.org/Organization"' . amt_get_schemaorg_itemref('organization') . '>';
            // Get publisher metatags
    //        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_publisher_metatags( $options, $post->post_author ) );
            $metadata_arr['publisher'] = amt_get_jsonld_schemaorg_publisher_array($options, $post->post_author);
            // Scope END: Organization
    //        $metadata_arr[] = '</span> <!-- Scope END: Organization -->';

        }

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
//        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
//        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';
        // Get author metatags
//        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_author_metatags( $post->post_author, $options ) );
        $metadata_arr['author'] = amt_get_jsonld_schemaorg_author_array( $post->post_author, $options );
        // Scope END: Person
//        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

        // URL - Uses amt_get_permalink_for_multipage()
        $metadata_arr['url'] = esc_url_raw( amt_get_permalink_for_multipage($post) );

        // mainEntityOfPage
        $metadata_arr['mainEntityOfPage'] = esc_url( amt_get_permalink_for_multipage($post) );

        // Dates
        $metadata_arr['datePublished'] = esc_attr( amt_iso8601_date($post->post_date) );
        $metadata_arr['dateModified'] = esc_attr( amt_iso8601_date($post->post_modified) );
        $metadata_arr['copyrightYear'] = esc_attr( mysql2date('Y', $post->post_date) );

        // Language
        $metadata_arr['inLanguage'] = esc_attr( str_replace('-', '_', amt_get_language_content($options, $post)) );

        // name
        // Note: Contains multipage information through amt_process_paged()
        $metadata_arr['name'] = esc_attr( amt_process_paged( strip_tags( get_the_title($post->ID) ) ) );

        // headline - contains title information
        $metadata_arr['headline'] = esc_attr( amt_get_title_for_metadata($options, $post) );

        // Description - We use the description defined by Add-Meta-Tags
        // Note: Contains multipage information through amt_process_paged()
        $content_desc = amt_get_content_description($post);
        if ( !empty($content_desc) ) {
            $metadata_arr['description'] = esc_attr( amt_process_paged( $content_desc ) );
        }

        /*
        // Section: We use the first category as the section
        $first_cat = sanitize_text_field( amt_sanitize_keywords( amt_get_first_category($post) ) );
        if (!empty($first_cat)) {
            $metadata_arr[] = '<meta itemprop="articleSection" content="' . esc_attr( $first_cat ) . '" />';
        }
        */
        // Add articleSection in Article object only.
        if ( $main_content_object == 'Article' ) {
            $categories = get_the_category($post->ID);
            $categories = apply_filters( 'amt_post_categories_for_schemaorg', $categories );
            foreach( $categories as $cat ) {
                $section = trim( $cat->cat_name );
                if ( ! empty( $section ) && $cat->slug != 'uncategorized' ) {
                    $metadata_arr['articleSection'] = esc_attr( $section );
                }
            }
        }

        // Add review properties if Review
        if ( $main_content_object == 'Review' ) {
//            $metadata_arr[] = '<!-- Review Information BEGIN -->';
//            $metadata_arr[] = amt_get_review_info_box( $review_data );
//            $metadata_arr[] = '<!-- Review Information END -->';
            // Reviewed Item
            $metadata_arr['itemReviewed'] = array();
            $metadata_arr['itemReviewed']['@type'] = esc_attr($review_data['object']);
            $metadata_arr['itemReviewed']['name'] = esc_attr($review_data['name']);
            $metadata_arr['itemReviewed']['sameAs'] = esc_url_raw($review_data['sameAs']);
            // Extra properties of reviewed item
            foreach ( $review_data['extra'] as $key => $value ) {
                if ( is_array($value) ) {
                    // Add sub entity
                    // If it is an array, the 'object' property is mandatory
                    if ( ! array_key_exists( 'object', $value ) ) {
                        continue;
                    }
                    $metadata_arr['itemReviewed'][esc_attr($key)] = array();
                    $metadata_arr['itemReviewed'][esc_attr($key)]['@type'] = esc_attr($value['object']);
                    foreach ( $value as $subkey => $subvalue ) {
                        if ( $subkey != 'object' ) {
                            if ( in_array( $subkey, array('url', 'sameAs') ) ) {
                                $metadata_arr['itemReviewed'][esc_attr($key)][esc_attr($subkey)] = esc_url_raw($subvalue);
                            } else {
                               $metadata_arr['itemReviewed'][esc_attr($key)][esc_attr($subkey)] = esc_attr($subvalue);
                            }
                        }
                    }
                } else {
                    // Add simple meta element
                    $metadata_arr['itemReviewed'][esc_attr($key)] = esc_attr($value);
                }
            }

            // Rating
            $metadata_arr['reviewRating'] = array();
            $metadata_arr['reviewRating']['@type'] = 'Rating';
            $metadata_arr['reviewRating']['ratingValue'] = esc_attr($review_data['ratingValue']);
            $bestrating = apply_filters( 'amt_schemaorg_review_bestrating', '5' );
            $metadata_arr['reviewRating']['bestRating'] = esc_attr($bestrating);
        }

        // Keywords - We use the keywords defined by Add-Meta-Tags
        $keywords = amt_get_content_keywords($post);
        if (!empty($keywords)) {
            $metadata_arr['keywords'] = esc_attr( $keywords );
        }

        // Referenced Items
        $referenced_url_list = amt_get_referenced_items($post);
        if ( ! empty($referenced_url_list) ) {
            $metadata_arr['referencedItem'] = array();
            foreach ($referenced_url_list as $referenced_url) {
                $referenced_url = trim($referenced_url);
                if ( ! empty( $referenced_url ) ) {
                    $metadata_arr['referencedItem'][] = esc_url_raw( $referenced_url );
                }
            }
        }

        // Images
        $metadata_arr['image'] = array();

        // First check if a global image override URL has been entered.
        // If yes, use this image URL and override all other images.
        $image_data = amt_get_image_data( amt_get_post_meta_image_url($post->ID) );
        if ( ! empty($image_data) ) {
            $image_size = apply_filters( 'amt_image_size_content', 'full' );
            $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size=$image_size );
            if ( ! empty($image_meta_array) ) {
                $metadata_arr['image'] = array();
                $metadata_arr['image'][] = $image_meta_array;
            }

        //$global_image_override_url = amt_get_post_meta_image_url($post->ID);
        //if ( ! empty( $global_image_override_url ) ) {
//            $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//            $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
        //    $current_image_obj = array();
        //    $current_image_obj['@type'] = 'ImageObject';
        //    $current_image_obj['url'] = esc_url_raw( $global_image_override_url );
        //    $current_image_obj['contentUrl'] = esc_url_raw( $global_image_override_url );
        //    $metadata_arr['image'][] = $current_image_obj;
//            $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $global_image_override_url ) . '" />';
//            $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

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

            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            // Image - Featured image is checked first, so that it can be the first associated image.
            if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $post->ID ) ) {
                // Thumbnail URL
                // First add the thumbnail URL of the featured image
                $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
                $metadata_arr['thumbnailUrl'] = esc_url_raw( $thumbnail_info[0] );
                // Add full image object for featured image.
                // Get the image attachment object
                $image = get_post( get_post_thumbnail_id( $post->ID ) );
                // metadata BEGIN
//                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                // Allow filtering of the image size.
                $image_size = apply_filters( 'amt_image_size_content', 'full' );
                // Get image metatags.
//                $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $options, $image, $size=$image_size ) );
                $metadata_arr['image'][] = amt_get_jsonld_schemaorg_image_array( $options, $image, $size=$image_size );
                // metadata END
//                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
                // Finally, set the $featured_image_id
                $featured_image_id = get_post_thumbnail_id( $post->ID );
                // Images have been found.
                $has_images = true;
                // Increase image counter
                $ic++;
            }
            // Scope END: ImageObject


            // Process all attachments and add metatags (featured image will be excluded)
            foreach( $attachments as $attachment ) {

                // Excluded the featured image since 
                if ( $attachment->ID != $featured_image_id ) {
                    
                    $mime_type = get_post_mime_type( $attachment->ID );
                    //$attachment_type = strstr( $mime_type, '/', true );
                    // See why we do not use strstr(): http://www.codetrax.org/issues/1091
                    $attachment_type = preg_replace( '#\/[^\/]*$#', '', $mime_type );

                    if ( 'image' == $attachment_type && $ic < $image_limit ) {

                        // metadata BEGIN
//                        $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//                        $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                        // Allow filtering of the image size.
                        $image_size = apply_filters( 'amt_image_size_content', 'full' );
                        // Get image metatags.
//                        $metadata_arr = array_merge( $metadata_arr, amt_get_schemaorg_image_metatags( $options, $attachment, $size=$image_size ) );
                        $metadata_arr['image'][] = amt_get_jsonld_schemaorg_image_array( $options, $attachment, $size=$image_size );
                        // metadata END
//                        $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

                        // Images have been found.
                        $has_images = true;
                        // Increase image counter
                        $ic++;
                        
                    } elseif ( 'video' == $attachment_type && $vc < $video_limit ) {

                        // Scope BEGIN: VideoObject: http://schema.org/VideoObject
//                        $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
//                        $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
                        // Video specific metatags
                        // URL (for attachments: links to attachment page)
                        $current_video_obj = array();
                        $current_video_obj['@type'] = 'VideoObject';

                        // URL (links to image file)
                        //$current_video_obj['url'] = esc_url_raw( get_permalink( $attachment->ID ) );
                        $current_video_obj['url'] = esc_url_raw( wp_get_attachment_url($attachment->ID) );

                        // sameAs (links to attachment page)
                        $current_video_obj['sameAs'] = esc_url_raw( get_permalink( $attachment->ID ) );

                        $current_video_obj['contentUrl'] = esc_url_raw( wp_get_attachment_url($attachment->ID) );
                        $current_video_obj['encodingFormat'] = esc_attr( $mime_type );
                        // name
                        $current_video_obj['name'] = esc_attr( strip_tags( get_the_title($attachment->ID) ) );
                        // Description - We use the description defined by Add-Meta-Tags
                        $content_desc = amt_get_content_description($attachment);
                        if ( ! empty($content_desc) ) {
                            $current_video_obj['description'] = esc_attr( $content_desc );
                        }
                        // Thumbnail
                        // A featured image is supported by video and audio attachments.
                        // If one is set, then it is set as the thumbnail of the video/audio object.
                        if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $attachment->ID ) ) {
                            // Thumbnail URL of the featured image
                            $image_size = apply_filters( 'amt_image_size_content', 'full' );
                            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($attachment->ID), $image_size );
                            $current_video_obj['thumbnailUrl'] = esc_url_raw( $thumbnail_info[0] );
                            // Currently we do not add a full ImageObject for the attachment's featured image.
                            // TODO: future
                        }
                        // uploadDate
                        $current_video_obj['uploadDate'] = esc_attr( amt_iso8601_date($attachment->post_date) );

                        $metadata_arr['video'][] = $current_video_obj;
                        // Scope END: VideoObject
//                        $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';
                        // Increase video counter
                        $vc++;

                    } elseif ( 'audio' == $attachment_type && $ac < $audio_limit ) {

                        // Scope BEGIN: AudioObject: http://schema.org/AudioObject
//                        $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
//                        $metadata_arr[] = '<span itemprop="audio" itemscope itemtype="http://schema.org/AudioObject">';
                        // Audio specific metatags
                        // URL (for attachments: links to attachment page)
                        $current_audio_obj = array();
                        $current_audio_obj['@type'] = 'AudioObject';

                        // URL (links to image file)
                        //$current_audio_obj['url'] = esc_url_raw( get_permalink( $attachment->ID ) );
                        $current_audio_obj['url'] = esc_url_raw( wp_get_attachment_url($attachment->ID) );

                        // sameAs (links to attachment page)
                        $current_audio_obj['sameAs'] = esc_url_raw( get_permalink( $attachment->ID ) );

                        $current_audio_obj['contentUrl'] = esc_url_raw( wp_get_attachment_url($attachment->ID) );
                        $current_audio_obj['encodingFormat'] = esc_attr( $mime_type );
                        // name
                        $current_audio_obj['name'] = esc_attr( strip_tags( get_the_title($attachment->ID) ) );
                        // Description - We use the description defined by Add-Meta-Tags
                        $content_desc = amt_get_content_description($attachment);
                        if ( ! empty($content_desc) ) {
                            $current_audio_obj['description'] = esc_attr( $content_desc );
                        }
                        // Thumbnail
                        // A featured image is supported by video and audio attachments.
                        // If one is set, then it is set as the thumbnail of the video/audio object.
                        if ( function_exists('has_post_thumbnail') && has_post_thumbnail( $attachment->ID ) ) {
                            // Thumbnail URL of the featured image
                            $image_size = apply_filters( 'amt_image_size_content', 'full' );
                            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($attachment->ID), $image_size );
                            $current_audio_obj['thumbnailUrl'] = esc_url_raw( $thumbnail_info[0] );
                            // Currently we do not add a full ImageObject for the attachment's featured image.
                            // TODO: future
                        }
                        // uploadDate
                        $current_audio_obj['uploadDate'] = esc_attr( amt_iso8601_date($attachment->post_date) );

                        $metadata_arr['audio'][] = $current_audio_obj;
                        // Scope END: AudioObject
//                        $metadata_arr[] = '</span> <!-- Scope END: AudioObject -->';
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

                // Scope BEGIN: ImageObject: http://schema.org/ImageObject
//                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                $current_image_obj = array();
                $current_image_obj['@type'] = 'ImageObject';
                // name (title)
                $current_image_obj['name'] = esc_attr( $embedded_item['alt'] );
                // caption
                $current_image_obj['caption'] = esc_attr( $embedded_item['alt'] );
                // alt
                $current_image_obj['text'] = esc_attr( $embedded_item['alt'] );

                // URL (links to image file)
                //$current_image_obj['url'] = esc_url_raw( $embedded_item['page'] );
                $current_image_obj['url'] = esc_url_raw( $embedded_item['image'] );

                // sameAs (links to attachment page)
                $current_image_obj['sameAs'] = esc_url_raw( $embedded_item['page'] );

                // thumbnail url
                $current_image_obj['thumbnailUrl'] = esc_url_raw( $embedded_item['thumbnail'] );
                // main image
                $current_image_obj['contentUrl'] = esc_url_raw( $embedded_item['image'] );
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $current_image_obj['width'] = esc_attr( $embedded_item['width'] );
                    $current_image_obj['height'] = esc_attr( $embedded_item['height'] );
                    $current_image_obj['encodingFormat'] = 'image/jpeg';
                }
                // embedURL
                $current_image_obj['embedURL'] = esc_url_raw( $embedded_item['player'] );
                $metadata_arr['image'][] = $current_image_obj;
                // Scope END: ImageObject
//                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';

                // Images have been found.
                $has_images = true;
                // Increase image counter
                $ic++;

            }
            foreach( $embedded_media['videos'] as $embedded_item ) {

                if ( $vc == $video_limit ) {
                    break;
                }

                // Scope BEGIN: VideoObject: http://schema.org/VideoObject
                // See: http://googlewebmastercentral.blogspot.gr/2012/02/using-schemaorg-markup-for-videos.html
                // See: https://support.google.com/webmasters/answer/2413309?hl=en
//                $metadata_arr[] = '<!-- Scope BEGIN: VideoObject -->';
//                $metadata_arr[] = '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
                $current_video_obj = array();
                // Video Embed URL
                $current_video_obj['embedURL'] = esc_url_raw( $embedded_item['player'] );
                // playerType
                $current_video_obj['playerType'] = 'application/x-shockwave-flash';
                // size
                $current_video_obj['width'] = esc_attr( $embedded_item['width'] );
                $current_video_obj['height'] = esc_attr( $embedded_item['height'] );
                $metadata_arr['video'][] = $current_video_obj;
                // Scope END: VideoObject
//                $metadata_arr[] = '</span> <!-- Scope END: VideoObject -->';

                // Increase video counter
                $vc++;

            }
            foreach( $embedded_media['sounds'] as $embedded_item ) {

                if ( $ac == $audio_limit ) {
                    break;
                }

                // Scope BEGIN: AudioObject: http://schema.org/AudioObject
//                $metadata_arr[] = '<!-- Scope BEGIN: AudioObject -->';
//                $metadata_arr[] = '<span itemprop="audio" itemscope itemtype="http://schema.org/AudioObject">';
                $current_audio_obj = array();
                // Audio Embed URL
                $current_audio_obj['embedURL'] = esc_url_raw( $embedded_item['player'] );
                // playerType
                $current_audio_obj['playerType'] = 'application/x-shockwave-flash';
                $metadata_arr['audio'][] = $current_audio_obj;
                // Scope END: AudioObject
//                $metadata_arr[] = '</span> <!-- Scope END: AudioObject -->';

                // Increase audio counter
                $ac++;

            }

            // If no images have been found so far use the default image, if set.
            // Scope BEGIN: ImageObject: http://schema.org/ImageObject
            if ( $has_images === false ) {

                $image_data = amt_get_default_image_data();
                if ( ! empty($image_data) ) {
                    $image_size = apply_filters( 'amt_image_size_content', 'full' );
                    $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size=$image_size );
                    if ( ! empty($image_meta_array) ) {
                        $metadata_arr['image'] = array();
                        $metadata_arr['image'][] = $image_meta_array;
                    }
                }

//                $metadata_arr[] = '<!-- Scope BEGIN: ImageObject -->';
//                $metadata_arr[] = '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                $current_image_obj = array();
                $current_image_obj['@type'] = 'ImageObject';
                $current_image_obj['url'] = esc_url_raw( $options["default_image_url"] );
                $current_image_obj['contentUrl'] = esc_url_raw( $options["default_image_url"] );
                $metadata_arr['image'][] = $current_image_obj;
//                $metadata_arr[] = '<meta itemprop="contentUrl" content="' . esc_url_raw( $options["default_image_url"] ) . '" />';
//                $metadata_arr[] = '</span> <!-- Scope END: ImageObject -->';
            }
            // Scope END: ImageObject

        }

        // Article Body
        // The article body is added after filtering the generated microdata below.

        // TODO: also check: comments, contributor, copyrightHolder, , creator, dateCreated, discussionUrl, editor, version (use post revision if possible)

        // Scope END: Article
//        $metadata_arr[] = '</div> <!-- Scope END: ' . esc_attr($main_content_object) . ' -->';

        // Filtering of the generated Schema.org metadata
        $metadata_arr = apply_filters( 'amt_jsonld_schemaorg_metadata_content', $metadata_arr );

        // Add articleBody to Artice
        // Now add the article. Remove last closing '</span>' tag, add articleBody and re-add the closing span afterwards.
//        $closing_article_tag = array_pop($metadata_arr);

        // Use the 'text' itemprop by default for the main text body of the CreativeWork,
        // so it can be used by more subtypes than Article.
        // But set it explicitly to 'articleBody if the main entiry is 'Article'
        // or 'reviewBody' if the main entity is a 'Review'.
        $main_text_property = 'text';
        if ( $main_content_object == 'Article' ) {
            $main_text_property = 'articleBody';
        } elseif ( $main_content_object == 'Review' ) {
            $main_text_property = 'reviewBody';
        }
        // Allow filtering of the main text property.
        $main_text_property = apply_filters( 'amt_schemaorg_property_main_text', $main_text_property );

//        $metadata_arr[] = '<div itemprop="' . esc_attr($main_text_property) . '">';


        // Add main content
        //$metadata_arr[ esc_attr($main_text_property) ] = $post->post_content;


//        $metadata_arr[] = $post_body;
//        $metadata_arr[] = '</div> <!-- Itemprop END: ' . esc_attr($main_text_property) . ' -->';
        // Now add closing tag for Article
//        $metadata_arr[] = $closing_article_tag;
    }

    // Add our comment
//    if ( count( $metadata_arr ) > 0 ) {
//        array_unshift( $metadata_arr, "<!-- BEGIN Schema.org microdata added by Add-Meta-Tags WordPress plugin -->" );
//        array_unshift( $metadata_arr, "" );   // Intentionaly left empty
//        array_push( $metadata_arr, "<!-- END Schema.org microdata added by Add-Meta-Tags WordPress plugin -->" );
//        array_push( $metadata_arr, "" );   // Intentionaly left empty
//    }

    //return $post_body;
//    return implode( PHP_EOL, $metadata_arr );


    // Filtering of the generated microdata for footer
    $metadata_arr = apply_filters( 'amt_jsonld_schemaorg_metadata_head', $metadata_arr );

//    if ( count( $metadata_arr ) > 0 ) {
//        array_unshift( $metadata_arr, '<script type="application/ld+json">' );
//        array_unshift( $metadata_arr, "" );   // Intentionaly left empty
//        array_push( $metadata_arr, '</script>' );
//        array_push( $metadata_arr, "" );   // Intentionaly left empty
//    }

//    return $metadata_arr;

    if ( count( $metadata_arr ) > 1 ) {
        // contains @context by default
        return array('<script type="application/ld+json">', 'json+ld_data' => json_encode($metadata_arr), '</script>');
    } else {
        return array();
    }
}




/**
 * Return an array of Schema.org metatags for the provided $image object.
 * By default, returns metadata for the 'medium' sized version of the image.
 */
function amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size='medium', $is_representative=false ) {

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
    // 3. An attachment object (post object)    (Usually ths is the case in the schema.org generators
    //
    //var_dump($image_data);
    $metadata_arr = array();
    $image = null;

    if ( is_array($image_data) && ! is_null($image_data['url']) ) {
        // Here we process the image data as retrieved from the special notation of the image's URL.
        // No size information is taken into account in this case.

        // Schema.org type
        $metadata_arr['@type'] = 'ImageObject';

        // Image tags
        $metadata_arr['url'] = esc_url( $image_data['url'] );
        $metadata_arr['contentUrl'] = esc_url( $image_data['url'] );

        if ( apply_filters( 'amt_extended_image_tags', true ) ) {
            if ( ! is_null($image_data['width']) ) {
                $metadata_arr['width'] = esc_attr( $image_data['width'] );
            }
            if ( ! is_null($image_data['height']) ) {
                $metadata_arr['height'] = esc_attr( $image_data['height'] );
            }
            if ( ! is_null($image_data['type']) ) {
                $metadata_arr['encodingFormat'] = esc_attr( $image_data['type'] );
            }
        }

        return $metadata_arr;

    } elseif ( is_array($image_data) && is_numeric($image_data['id']) ) {
        // The attachment ID exists in the array's 'id' item.
        $image = get_post( absint( $image_data['id'] ) );
    } elseif ( is_numeric($image_data) ) {
        // Image data is the attachment ID (integer)
        $image = get_post( absint( $image_data ) );
    } elseif ( is_object($image_data) && isset($image_data->ID) ) {
        // Image data is the attachment itself.
        $image = $image_data;
    }

    if ( is_null($image) || ! is_object($image) || ! isset($image->ID) ) {
        return $metadata_arr;
    }

    // Process the image attachment and generate meta tags.

    // Schema.org type
    $metadata_arr['@type'] = 'ImageObject';

    // Get the image object <- Already have it
    //$image = get_post( $post_id );

    // Data for image attachments
    $image_meta = wp_get_attachment_metadata( $image->ID );   // contains info about all sizes
    // We use wp_get_attachment_image_src() since it constructs the URLs
    $thumbnail_meta = wp_get_attachment_image_src( $image->ID , 'thumbnail' );
    $main_size_meta = wp_get_attachment_image_src( $image->ID , $size );

    // name (title)
    $metadata_arr['name'] = esc_attr( strip_tags( get_the_title( $image->ID ) ) );
    // OLD name (title)
    //$image_title = sanitize_text_field( $image->post_title );
    //if ( ! empty( $image_title ) ) {
    //    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $image_title ) . '" />';
    //}

    // URL (links to image file)
    //$metadata_arr['url'] = esc_url_raw( get_permalink( $image->ID ) );
    $metadata_arr['url'] = esc_url_raw( $main_size_meta[0] );

    // sameAS (links to attachment page)
    $metadata_arr['sameAs'] = esc_url_raw( get_permalink( $image->ID ) );

    // Description (generated from $image->post_content. See: amt_get_the_excerpt()
    $image_description = amt_get_content_description($image);
    if ( ! empty( $image_description ) ) {
        $metadata_arr['description'] = esc_attr( $image_description );
    }

    // thumbnail url
    $metadata_arr['thumbnailUrl'] = esc_url_raw( $thumbnail_meta[0] );

    // main image
    $metadata_arr['contentUrl'] = esc_url_raw( $main_size_meta[0] );
    if ( apply_filters( 'amt_extended_image_tags', true ) ) {
        $metadata_arr['width'] = esc_attr( $main_size_meta[1] );
        $metadata_arr['height'] = esc_attr( $main_size_meta[2] );
        $metadata_arr['encodingFormat'] = esc_attr( get_post_mime_type( $image->ID ) );
    }

    // caption
    // Here we sanitize the provided description for safety
    $image_caption = sanitize_text_field( $image->post_excerpt );
    if ( ! empty( $image_caption ) ) {
        $metadata_arr['caption'] = esc_attr( $image_caption );
    }

    // alt
    // Here we sanitize the provided description for safety
    $image_alt = sanitize_text_field( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) );
    if ( ! empty( $image_alt ) ) {
        $metadata_arr['text'] = esc_attr( $image_alt );
    }

    if ( $is_representative === true ) {
        // representativeOfPage - Boolean - Indicates whether this image is representative of the content of the page.
        $metadata_arr['representativeOfPage'] = 'True';
    }

    return $metadata_arr;
}


/**
 * Return an array of Schema.org metatags suitable for the publisher object of
 * the content. Accepts the $post object as argument.
 */
function amt_get_jsonld_schemaorg_publisher_array( $options, $author_id=null ) {

    $metadata_arr = array();
    // Schema.org type
    $metadata_arr['@type'] = 'Organization';

    // name
    $metadata_arr['name'] = esc_attr( get_bloginfo('name') );
    // description
    // First use the site description from the Add-Meta-Tags settings
    $site_description = amt_get_site_description($options);
    if ( empty($site_description) ) {
        // Alternatively, use the blog description
        // Here we sanitize the provided description for safety
        $site_description = sanitize_text_field( amt_sanitize_description( get_bloginfo('description') ) );
    }
    if ( ! empty($site_description) ) {
        $metadata_arr['description'] = esc_attr( $site_description );
    }
    // logo
    $image_data = amt_get_default_image_data();
    if ( ! empty($image_data) ) {
        $image_size = apply_filters( 'amt_image_size_content', 'full' );
        $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size=$image_size );
        if ( ! empty($image_meta_array) ) {
            $metadata_arr['logo'] = array();
            $logo_obj = array();
            // Type
            $logo_obj['@type'] = 'ImageObject';
            // name (title)
            $logo_obj['name'] = esc_attr( get_bloginfo('name') ) . ' ' . __('logo', 'add-meta-tags');
            // caption
            //$logo_obj['caption'] = esc_attr( rtrim(get_bloginfo('description'), '.') ) . '.';
            // alt
            $logo_obj['text'] = esc_attr( get_bloginfo('name') ) . ' ' . __('logo', 'add-meta-tags');
            // Image URL & attributes
            $logo_obj = array_merge( $logo_obj, $image_meta_array );
            // URL (links to web page containing the image)
            //$logo_obj['url'] = esc_url( $options["default_image_url"] );
            // thumbnail url
            //$logo_obj['thumbnailUrl'] = esc_url( ... );
            // main image
            //$logo_obj['contentUrl'] = esc_url( $options["default_image_url"] );
            //if ( apply_filters( 'amt_extended_image_tags', true ) ) {
            //    $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
            //    $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
            //    $metadata_arr[] = '<meta itemprop="encodingFormat" content="image/jpeg" />';
            //}
            // Add logo entity to metadata
            $metadata_arr['logo'][] = $logo_obj;
        }
    }

    ////if ( ! empty($options["default_image_url"]) ) {
        ////$metadata_arr['logo'] = array();
        ////$logo_obj = array();
        // Type
        ////$logo_obj['@type'] = 'ImageObject';
        // name (title)
        ////$logo_obj['name'] = esc_attr( get_bloginfo('name') ) . ' ' . __('logo', 'add-meta-tags');
        // caption
        //$logo_obj['caption'] = esc_attr( rtrim(get_bloginfo('description'), '.') ) . '.';
        // alt
        ////$logo_obj['text'] = esc_attr( get_bloginfo('name') ) . ' ' . __('logo', 'add-meta-tags');
        // URL (links to web page containing the image)
        ////$logo_obj['url'] = esc_url( $options["default_image_url"] );
        // thumbnail url
        //$logo_obj['thumbnailUrl'] = esc_url( ... );
        // main image
        ////$logo_obj['contentUrl'] = esc_url( $options["default_image_url"] );
        //if ( apply_filters( 'amt_extended_image_tags', true ) ) {
        //    $metadata_arr[] = '<meta itemprop="width" content="' . esc_attr( $embedded_item['width'] ) . '" />';
        //    $metadata_arr[] = '<meta itemprop="height" content="' . esc_attr( $embedded_item['height'] ) . '" />';
        //    $metadata_arr[] = '<meta itemprop="encodingFormat" content="image/jpeg" />';
        //}
        // Add logo entity to metadata
        ////$metadata_arr['logo'][] = $logo_obj;
    ////}

    // url
    // The blog url is used by default. Google+, Facebook and Twitter profile URLs are added as sameAs.
    $metadata_arr['url'] = esc_url_raw( trailingslashit( get_bloginfo('url') ) );
    // sameAs
    $metadata_arr['sameAs'] = array();
    // Social Profile Links are added as sameAs properties
    // By default, those of the Publisher Settings  are used.
    // WARNING: Publisher profile URLs from the user profile page are now deprecated.
    // Google+ Publisher
    if ( ! empty($options['social_main_googleplus_publisher_profile_url']) ) {
        $metadata_arr['sameAs'][] = esc_url_raw( $options['social_main_googleplus_publisher_profile_url'], array('http', 'https') );
    }
    // Facebook
    if ( ! empty($options['social_main_facebook_publisher_profile_url']) ) {
        $metadata_arr['sameAs'][] = esc_url_raw( $options['social_main_facebook_publisher_profile_url'], array('http', 'https') );
    }
    // Twitter
    if ( ! empty($options['social_main_twitter_publisher_username']) ) {
        $metadata_arr['sameAs'][] = 'https://twitter.com/' . esc_attr( $options['social_main_twitter_publisher_username'] );
    }

    // Allow filtering of the Publisher meta tags
    $metadata_arr = apply_filters( 'amt_jsonld_schemaorg_publisher_extra', $metadata_arr );

    return $metadata_arr;
}


/**
 * Return an array of Schema.org metatags suitable for the author object of
 * the content. Accepts the $post object as argument.
 */
function amt_get_jsonld_schemaorg_author_array( $author_id, $options ) {
    //$author_obj = get_user_by( 'id', $author_id );

    $metadata_arr = array();
    // Schema.org type
    $metadata_arr['@type'] = 'Person';

    // name
    $display_name = get_the_author_meta('display_name', $author_id);
    $metadata_arr['name'] = esc_attr( $display_name );
    // description
    // Here we sanitize the provided description for safety
    $author_description = sanitize_text_field( amt_sanitize_description( get_the_author_meta('description', $author_id) ) );
    if ( !empty($author_description) ) {
        $metadata_arr['description'] = esc_attr( $author_description );
    }

    // Profile Image
    // First use the global image override URL
    $image_data = amt_get_image_data( amt_get_user_meta_image_url( $author_id ) );
    if ( ! empty($image_data) ) {
        $image_size = apply_filters( 'amt_image_size_index', 'full' );
        $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data, $size=$image_size );
        if ( ! empty($image_meta_array) ) {
            $metadata_arr['image'] = array();
            $metadata_arr['image'][] = $image_meta_array;
        }
    } else {
        $author_email = sanitize_email( get_the_author_meta('user_email', $author_id) );
        $avatar_size = apply_filters( 'amt_avatar_size', 128 );
        $avatar_url = '';
        // First try to get the avatar link by using get_avatar().
        // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
        $avatar_img = get_avatar( get_the_author_meta('ID', $author_id), $avatar_size, '', get_the_author_meta('display_name', $author_id) );
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
            //$metadata_arr['image'] = esc_url_raw( $avatar_url );
            $image_data = amt_get_image_data( sprintf('%s,%dx%d', $avatar_url, $avatar_size, $avatar_size) );
            if ( ! empty($image_data) ) {
                $image_meta_array = amt_get_jsonld_schemaorg_image_array( $options, $image_data );
                if ( ! empty($image_meta_array) ) {
                    $metadata_arr['image'] = array();
                    $metadata_arr['image'][] = $image_meta_array;
                }
            }
        }
    }

    // url
    // The URL to the author archive is added as the url.
    //$metadata_arr['url'] = esc_url_raw( get_author_posts_url( $author_id ) );
    $metadata_arr['url'] = esc_url_raw( amt_get_local_author_profile_url( $author_id, $options ) );
    // sameAs
    $metadata_arr['sameAs'] = array();
    // Social Profile Links are added as sameAs properties
    // Those from the WordPress User Profile page are used.
    // Google+ Author
    $googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $author_id);
    if ( !empty($googleplus_author_url) ) {
        $metadata_arr['sameAs'][] = esc_url_raw( $googleplus_author_url, array('http', 'https') );
    }
    // Facebook
    $facebook_author_url = get_the_author_meta('amt_facebook_author_profile_url', $author_id);
    if ( !empty($facebook_author_url) ) {
        $metadata_arr['sameAs'][] = esc_url_raw( $facebook_author_url, array('http', 'https') );
    }
    // Twitter
    $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $author_id);
    if ( !empty($twitter_author_username) ) {
        $metadata_arr['sameAs'][] = 'https://twitter.com/' . esc_attr( $twitter_author_username );
    }
    // The User URL as set by the user in the WordPress User Profile page.
    $user_url = get_the_author_meta( 'user_url', $author_id );
    if ( !empty($user_url) ) {
        $metadata_arr['sameAs'][] = esc_url_raw( $user_url, array('http', 'https') );
    }
    // Check if empty
    if ( empty($metadata_arr['sameAs']) ) {
        unset($metadata_arr['sameAs']);
    }

    // Allow filtering of the Author meta tags
    $metadata_arr = apply_filters( 'amt_jsonld_schemaorg_author_extra', $metadata_arr, $author_id );

    return $metadata_arr;
}


