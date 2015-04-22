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
 *  Copyright 2006-2015 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org
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
 * Extended  getadata generator.
 *
 * Contains code that extends the generated metadata for:
 *  - WooCommerce
 *  - Easy Digital Downloads
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}



/*
 * WooCommerce Product and Product Group metadata
 *
 */

// Conditional tag that is true when our product page is displayed.
// If such a conditional tag is provided by the e-commerce solution,
// defining such a function is entirely optional.
function amt_is_woocommerce_product() {
    // Check if woocommerce product page and return true;
    // WooCommerce (http://docs.woothemes.com/document/conditional-tags/)
    // Also validates with is_singular().
    if ( is_product() ) {
        return true;
    }
}

// Conditional tag that is true when our product group page is displayed.
// If such a conditional tag is provided by the e-commerce solution,
// defining such a function is entirely optional.
function amt_is_woocommerce_product_group() {
    // Check if woocommerce product group page and return true;
    // WooCommerce (http://docs.woothemes.com/document/conditional-tags/)
    // Also validates with is_tax().
    if ( is_product_category() || is_product_tag() ) {
        return true;
    }
}

// Twitter Cards for woocommerce products
function amt_product_data_tc_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // Price
    $metatags['twitter:label1'] = '<meta name="twitter:label1" content="Price" />';
    $metatags['twitter:data1'] = '<meta name="twitter:data1" content="' . $product->get_price() . '" />';
    // Currency
    $metatags['twitter:label2'] = '<meta name="twitter:label2" content="Currency" />';
    $metatags['twitter:data2'] = '<meta name="twitter:data2" content="' . get_woocommerce_currency() . '" />';

    $metatags = apply_filters( 'amt_product_data_woocommerce_twitter_cards', $metatags );
    return $metatags;
}

// Opengraph for woocommerce products
function amt_product_data_og_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // Price
    $metatags[] = '<meta property="product:price:amount" content="' . $product->get_price() . '" />';
    // Currency
    $metatags[] = '<meta property="product:price:currency" content="' . get_woocommerce_currency() . '" />';

    // TODO: Check these:
    // product:category
    // product:availability
    // product:condition
    // product:expiration_time
    // product:isbn
    // product:product_link
    // product:upc

    $metatags = apply_filters( 'amt_product_data_woocommerce_opengraph', $metatags );
    return $metatags;
}

// Schema.org for woocommerce products
function amt_product_data_schemaorg_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // Price
    $metatags[] = '<meta itemprop="price" content="' . $product->get_price() . '" />';
    // Currency
    $metatags[] = '<meta itemprop="priceCurrency" content="' . get_woocommerce_currency() . '" />';

    // TODO: Check these:
    // itemCondition
    // productID
    // review (check first example)
    // offers (check first example)
    // sku

    $metatags = apply_filters( 'amt_product_data_woocommerce_schemaorg', $metatags );
    return $metatags;
}



/*
 * Easy Digital Downloads Product and Product Group metadata
 *
 */

// Conditional tag that is true when our product page is displayed.
// If such a conditional tag is provided by the e-commerce solution,
// defining such a function is entirely optional.
function amt_is_edd_product() {
    // Check if edd product page and return true;
    //  * Easy Digital Downloads
    if ( 'download' == get_post_type() ) {
        return true;
    }
}

// Conditional tag that is true when our product group page is displayed.
// If such a conditional tag is provided by the e-commerce solution,
// defining such a function is entirely optional.
function amt_is_edd_product_group() {
    // Check if edd product group page and return true;
    //  * Easy Digital Downloads
    // Also validates with is_tax()
    if ( is_tax( array( 'download_category', 'download_tag' ) ) ) {
        return true;
    }
}

// Twitter Cards for edd products
function amt_product_data_tc_edd( $metatags, $post ) {

    // Price
    $metatags['twitter:label1'] = '<meta name="twitter:label1" content="Price" />';
    $metatags['twitter:data1'] = '<meta name="twitter:data1" content="' . edd_get_download_price($post->ID) . '" />';
    // Currency
    $metatags['twitter:label2'] = '<meta name="twitter:label2" content="Currency" />';
    $metatags['twitter:data2'] = '<meta name="twitter:data2" content="' . edd_get_currency() . '" />';

    $metatags = apply_filters( 'amt_product_data_edd_twitter_cards', $metatags );
    return $metatags;
}

// Opengraph for edd products
function amt_product_data_og_edd( $metatags, $post ) {

    // Price
    $metatags[] = '<meta property="product:price:amount" content="' . edd_get_download_price($post->ID) . '" />';
    // Currency
    $metatags[] = '<meta property="product:price:currency" content="' . edd_get_currency() . '" />';

    $metatags = apply_filters( 'amt_product_data_edd_opengraph', $metatags );
    return $metatags;
}

// Schema.org for edd products
function amt_product_data_schemaorg_edd( $metatags, $post ) {

    // Price
    $metatags[] = '<meta itemprop="price" content="' . edd_get_download_price($post->ID) . '" />';
    // Currency
    $metatags[] = '<meta itemprop="priceCurrency" content="' . edd_get_currency() . '" />';

    $metatags = apply_filters( 'amt_product_data_edd_schemaorg', $metatags );
    return $metatags;
}



/*
 * E-Commerce Common Detection
 *
 */

// Product page detection for Add-Meta-Tags
function amt_detect_ecommerce_product() {
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");

    // WooCommerce product
    if ( $options["extended_support_woocommerce"] == "1" && amt_is_woocommerce_product() ) {
        // Filter product data meta tags
        add_filter( 'amt_product_data_twitter_cards', 'amt_product_data_tc_woocommerce', 10, 2 );
        add_filter( 'amt_product_data_opengraph', 'amt_product_data_og_woocommerce', 10, 2 );
        add_filter( 'amt_product_data_schemaorg', 'amt_product_data_schemaorg_woocommerce', 10, 2 );
        return true;
    // Easy-Digital-Downloads product
    } elseif ( $options["extended_support_edd"] == "1" && amt_is_edd_product() ) {
        add_filter( 'amt_product_data_twitter_cards', 'amt_product_data_tc_edd', 10, 2 );
        add_filter( 'amt_product_data_opengraph', 'amt_product_data_og_edd', 10, 2 );
        add_filter( 'amt_product_data_schemaorg', 'amt_product_data_schemaorg_edd', 10, 2 );
        return true;
    }
    return false;
}
add_filter( 'amt_is_product', 'amt_detect_ecommerce_product', 10, 1 );

// Product group page detection for Add-Meta-Tags
function amt_detect_ecommerce_product_group() {
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");

    // WooCommerce product group
    if ( $options["extended_support_woocommerce"] == "1" && amt_is_woocommerce_product_group() ) {
        return true;
    // Easy-Digital-Downloads product group
    } elseif ( $options["extended_support_edd"] == "1" && amt_is_edd_product_group() ) {
        return true;
    }
    return false;
}
add_filter( 'amt_is_product_group', 'amt_detect_ecommerce_product_group', 10, 1 );

