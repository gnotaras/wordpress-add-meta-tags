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
    if ( function_exists('is_product') ) {
        if ( is_product() ) {
            return true;
        }
    }
}

// Conditional tag that is true when our product group page is displayed.
// If such a conditional tag is provided by the e-commerce solution,
// defining such a function is entirely optional.
function amt_is_woocommerce_product_group() {
    // Check if woocommerce product group page and return true;
    // WooCommerce (http://docs.woothemes.com/document/conditional-tags/)
    // Also validates with is_tax().
    if ( function_exists('is_product_category') || function_exists('is_product_tag') ) {
        if ( is_product_category() || is_product_tag() ) {
            return true;
        }
    }
}

// Twitter Cards for woocommerce products
function amt_product_data_tc_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // WC API: http://docs.woothemes.com/wc-apidocs/class-WC_Product.html
    // Twitter product card: https://dev.twitter.com/cards/types/product

    // In this generator we only add the price. So, the WC product types that are
    // supported are those having a single price: simple, external
    // Not supported: grouped (no price), variable (multiple prices)
    $product_type = $product->product_type;
    if ( ! in_array( $product_type, array('simple', 'external') ) ) {
        $metatags = apply_filters( 'amt_product_data_woocommerce_twitter_cards', $metatags );
        return $metatags;
    }

    // Price
    // get_regular_price
    // get_sale_price
    // get_price    <-- active price (if product is on sale, the sale price is retrieved)
    // is_on_sale()
    // is_purchasable()
    $active_price = $product->get_price();
    if ( ! empty($active_price) ) {
        $metatags['twitter:label1'] = '<meta name="twitter:label1" content="Price" />';
        $metatags['twitter:data1'] = '<meta name="twitter:data1" content="' . esc_attr($active_price) . '" />';
        // Currency
        $metatags['twitter:label2'] = '<meta name="twitter:label2" content="Currency" />';
        $metatags['twitter:data2'] = '<meta name="twitter:data2" content="' . esc_attr(get_woocommerce_currency()) . '" />';
    }

    $metatags = apply_filters( 'amt_product_data_woocommerce_twitter_cards', $metatags );
    return $metatags;
}

// Opengraph for woocommerce products
function amt_product_data_og_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // WC API: http://docs.woothemes.com/wc-apidocs/class-WC_Product.html
    // https://developers.facebook.com/docs/reference/opengraph/object-type/product/
    // Also check:
    // https://developers.facebook.com/docs/reference/opengraph/object-type/product.item/

    // In this generator we only add the price. So, the WC product types that are
    // supported are those having a single price: simple, external
    // Not supported: grouped (no price), variable (multiple prices)
    $product_type = $product->product_type;
    if ( ! in_array( $product_type, array('simple', 'external') ) ) {
        $metatags = apply_filters( 'amt_product_data_woocommerce_opengraph', $metatags );
        return $metatags;
    }

    // Opengraph property to WooCommerce attribute map
    $default_property_map = array(
        'product:brand' => 'brand',
        'product:size' => 'size',
        'product:color' => 'color',
        'product:material' => 'material',
        'product:condition' => 'condition',
        'product:target_gender' => 'target_gender',
        'product:age_group' => 'age_group',
        'product:ean' => 'ean',
        'product:isbn' => 'isbn',
        'product:mfr_part_no' => 'mpn',
        'product:gtin' => 'gtin',
        'product:upc' => 'upc',
    );
    $property_map = apply_filters( 'amt_og_woocommerce_property_map', $default_property_map );

    // Availability
    $availability = '';
    if ( $product->is_in_stock() ) {
        $availability = 'instock';
    } elseif ( $product->backorders_allowed() ) {
        $availability = 'pending';
    } else {
        $availability = 'oos';
    }
    if ( ! empty($availability) ) {
        $metatags[] = '<meta property="product:availability" content="' . esc_attr($availability) . '" />';
    }

    // Price

    // Regular Price
    // get_regular_price
    // get_sale_price
    // get_price    <-- active price
    // is_on_sale()
    // is_purchasable()
    $regular_price = $product->get_regular_price();
    if ( ! empty($regular_price) ) {
        $metatags[] = '<meta property="product:price:amount" content="' . $regular_price . '" />';
        // Currency
        $metatags[] = '<meta property="product:price:currency" content="' . get_woocommerce_currency() . '" />';
    }

    // Sale Price
    // get_regular_price
    // get_sale_price
    // get_price    <-- active price
    // is_on_sale()
    // is_purchasable()
    //var_dump( $product->is_on_sale() );
    $sale_price = $product->get_sale_price();
    if ( ! empty($sale_price) ) {
        $metatags[] = '<meta property="product:sale_price:amount" content="' . $sale_price . '" />';
        // Currency
        $metatags[] = '<meta property="product:sale_price:currency" content="' . get_woocommerce_currency() . '" />';
    }
    // Sale price from date
    $sale_price_date_from = get_post_meta( $post->ID, '_sale_price_dates_from', true );
    if ( ! empty($sale_price_date_from) ) {
        $metatags[] = '<meta property="product:sale_price_dates:start" content="' . esc_attr(date_i18n('Y-m-d', $sale_price_date_from)) . '" />';
    }
    // Sale price to date
    $sale_price_date_to = get_post_meta( $post->ID, '_sale_price_dates_to', true );
    if ( ! empty($sale_price_date_to) ) {
        $metatags[] = '<meta property="product:sale_price_dates:end" content="' . esc_attr(date_i18n('Y-m-d', $sale_price_date_to)) . '" />';
    }

    // Product Data

    // Product category
    $categories = $product->get_categories( $sep = ',', $before = '', $after = '' );
    $parts = explode(',', $categories);
    $product_category = '';
    if ( ! empty($parts) ) {
        $product_category = $parts[0];
    }
    if ( ! empty($product_category) ) {
        //$metatags[] = '<meta property="product:category" content="' . esc_attr($product_category) . '" />';
        //$metatags[] = '<meta property="product:retailer_category" content="' . esc_attr($product_category) . '" />';
    }

    // Brand
    $brand = $product->get_attribute( $property_map['product:brand'] );
    if ( ! empty($brand ) ) {
        $metatags[] = '<meta property="product:brand" content="' . esc_attr($brand) . '" />';
    }

    // Weight
    // Also see:
    //product:shipping_weight:value
    //product:shipping_weight:units
    $weight_unit = apply_filters( 'amt_woocommerce_default_weight_unit', 'kg' );
    $weight = wc_get_weight( $product->get_weight(), $weight_unit );
    if ( ! empty($weight) ) {
        $metatags[] = '<meta property="product:weight:value" content="' . esc_attr($weight) . '" />';
        $metatags[] = '<meta property="product:weight:units" content="' . esc_attr($weight_unit) . '" />';
    }

    // Size
    $size = $product->get_attribute( $property_map['product:size'] );
    if ( ! empty($size) ) {
        $metatags[] = '<meta property="product:size" content="' . esc_attr($size) . '" />';
    }

    // Color
    $color = $product->get_attribute( $property_map['product:color'] );
    if ( ! empty($color) ) {
        $metatags[] = '<meta property="product:color" content="' . esc_attr($color) . '" />';
    }

    // Material
    $material = $product->get_attribute( $property_map['product:material'] );
    if ( ! empty($material) ) {
        $metatags[] = '<meta property="product:material" content="' . esc_attr($material) . '" />';
    }

    // Condition
    $condition = $product->get_attribute( $property_map['product:condition'] );
    if ( ! empty($condition) ) {
        if ( in_array($age_group, array('new', 'refurbished', 'used') ) ) {
            $metatags[] = '<meta property="product:condition" content="' . esc_attr($condition) . '" />';
        }
    } else {
        $metatags[] = '<meta property="product:condition" content="new" />';
    }

    // Target gender
    $target_gender = $product->get_attribute( $property_map['product:target_gender'] );
    if ( ! empty($target_gender) && in_array($target_gender, array('male', 'female', 'unisex')) ) {
        $metatags[] = '<meta property="product:target_gender" content="' . esc_attr($target_gender) . '" />';
    }

    // Age group
    $age_group = $product->get_attribute( $property_map['product:age_group'] );
    if ( ! empty($age_group) && in_array($age_group, array('kids', 'adult')) ) {
        $metatags[] = '<meta property="product:age_group" content="' . esc_attr($age_group) . '" />';
    }

    // Codes

    // EAN
    $ean = $product->get_attribute( $property_map['product:ean'] );
    if ( ! empty($ean) ) {
        $metatags[] = '<meta property="product:ean" content="' . esc_attr($ean) . '" />';
    }

    // ISBN
    $isbn = $product->get_attribute( $property_map['product:isbn'] );
    if ( ! empty($isbn) ) {
        $metatags[] = '<meta property="product:isbn" content="' . esc_attr($isbn) . '" />';
    }

    // MPN: A manufacturer's part number for the item
    $mpn = $product->get_attribute( $property_map['product:mfr_part_no'] );
    if ( ! empty($mpn) ) {
        $metatags[] = '<meta property="product:mfr_part_no" content="' . esc_attr($mpn) . '" />';
    }

    // SKU (product:retailer_part_no?)
    // By convention we use the SKU as the product:retailer_part_no. TODO: check this
    $sku = $product->get_sku();
    if ( ! empty($sku) ) {
        $metatags[] = '<meta property="product:retailer_part_no" content="' . esc_attr($sku) . '" />';
    }

    // GTIN: A Global Trade Item Number, which encompasses UPC, EAN, JAN, and ISBN
    $gtin = $product->get_attribute( $property_map['product:gtin'] );
    if ( ! empty($gtin) ) {
        $metatags[] = '<meta property="product:gtin" content="' . esc_attr($gtin) . '" />';
    }

    // UPC: A Universal Product Code (UPC) for the product
    $upc = $product->get_attribute( $property_map['product:upc'] );
    if ( ! empty($upc) ) {
        $metatags[] = '<meta property="product:upc" content="' . esc_attr($upc) . '" />';
    }

    // Retailer data
    // User, consider adding these using a filtering function.
    //product:retailer
    //product:retailer_category
    //product:retailer_title
    //product:product_link

    $metatags = apply_filters( 'amt_product_data_woocommerce_opengraph', $metatags );
    return $metatags;
}

// Schema.org for woocommerce products
function amt_product_data_schemaorg_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    //$variations = $product->get_available_variations();
    //var_dump($variations);
    var_dump($product->product_type);
    // variable, simple, grouped, external

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

// JSON-LD Schema.org for woocommerce products
function amt_product_data_jsonld_schemaorg_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // Price
    $metatags['price'] = $product->get_price();
    // Currency
    $metatags['priceCurrency'] = get_woocommerce_currency();

    // TODO: Check these:
    // itemCondition
    // productID
    // review (check first example)
    // offers (check first example)
    // sku

    $metatags = apply_filters( 'amt_product_data_woocommerce_jsonld_schemaorg', $metatags );
    return $metatags;
}

// Retrieves the WooCommerce product group's image URL, if any.
function amt_product_group_image_url_woocommerce( $default_image_url, $tax_term_object ) {
    $thumbnail_id = get_woocommerce_term_meta( $tax_term_object->term_id, 'thumbnail_id', true );
    if ( ! empty($thumbnail_id) ) {
        return wp_get_attachment_url( $thumbnail_id );
    }
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

// JSON-LD Schema.org for edd products
function amt_product_data_jsonld_schemaorg_edd( $metatags, $post ) {

    // Price
    $metatags['price'] = edd_get_download_price($post->ID);
    // Currency
    $metatags['priceCurrency'] = edd_get_currency();

    $metatags = apply_filters( 'amt_product_data_edd_jsonld_schemaorg', $metatags );
    return $metatags;
}

// Retrieves the EDD product group's image URL, if any.
function amt_product_group_image_url_edd( $term_id ) {
    // Not supported
    return '';
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
        if ( $options["schemaorg_force_jsonld"] == "0" ) {
            add_filter( 'amt_product_data_schemaorg', 'amt_product_data_schemaorg_woocommerce', 10, 2 );
        } else {
            add_filter( 'amt_product_data_jsonld_schemaorg', 'amt_product_data_jsonld_schemaorg_woocommerce', 10, 2 );
        }
        return true;
    // Easy-Digital-Downloads product
    } elseif ( $options["extended_support_edd"] == "1" && amt_is_edd_product() ) {
        add_filter( 'amt_product_data_twitter_cards', 'amt_product_data_tc_edd', 10, 2 );
        add_filter( 'amt_product_data_opengraph', 'amt_product_data_og_edd', 10, 2 );
        if ( $options["schemaorg_force_jsonld"] == "0" ) {
            add_filter( 'amt_product_data_schemaorg', 'amt_product_data_schemaorg_edd', 10, 2 );
        } else {
            add_filter( 'amt_product_data_jsonld_schemaorg', 'amt_product_data_jsonld_schemaorg_edd', 10, 2 );
        }
        return true;
    }
    return false;
}
add_filter( 'amt_is_product', 'amt_detect_ecommerce_product', 10, 1 );

// Product group page detection for Add-Meta-Tags
function amt_detect_ecommerce_product_group() {
    // Get the options the DB
    $options = get_option("add_meta_tags_opts");

    // Only product groups that validate as custom taxonomies are supported
    if ( ! is_tax() ) {
        return false;
    }

    // WooCommerce product group
    if ( $options["extended_support_woocommerce"] == "1" && amt_is_woocommerce_product_group() ) {
        add_filter( 'amt_taxonomy_force_image_url', 'amt_product_group_image_url_woocommerce', 10, 2 );
        return true;
    // Easy-Digital-Downloads product group
    } elseif ( $options["extended_support_edd"] == "1" && amt_is_edd_product_group() ) {
        return true;
    }
    return false;
}
add_filter( 'amt_is_product_group', 'amt_detect_ecommerce_product_group', 10, 1 );

