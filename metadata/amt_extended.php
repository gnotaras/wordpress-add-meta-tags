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

    // Currently, the OG WC generator supports all product types.
    // simple, external, grouped (no price), variable (multiple prices)
    // The relevant meta tags are generated only if the relevant data can be retrieved
    // from the product object.
    $product_type = $product->product_type;
    //if ( ! in_array( $product_type, array('simple', 'external') ) ) {
    //    $metatags = apply_filters( 'amt_product_data_woocommerce_opengraph', $metatags );
    //    return $metatags;
    //}

    // Opengraph property to WooCommerce attribute map
    $property_map = array(
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
    $property_map = apply_filters( 'amt_og_woocommerce_property_map', $property_map );

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
        $metatags['og:product:availability'] = '<meta property="product:availability" content="' . esc_attr($availability) . '" />';
    }

    // Price

    // Active price
    $active_price = $product->get_price();
    if ( ! empty($active_price) ) {
        $metatags['og:product:price:amount'] = '<meta property="product:price:amount" content="' . $active_price . '" />';
        // Currency
        $metatags['og:product:price:currency'] = '<meta property="product:price:currency" content="' . get_woocommerce_currency() . '" />';
    }

    // Regular Price
    // get_regular_price
    // get_sale_price
    // get_price    <-- active price
    // is_on_sale()
    // is_purchasable()
    $regular_price = $product->get_regular_price();
    if ( ! empty($regular_price) ) {
        $metatags['og:product:original_price:amount'] = '<meta property="product:original_price:amount" content="' . $regular_price . '" />';
        // Currency
        $metatags['og:product:original_price:currency'] = '<meta property="product:original_price:currency" content="' . get_woocommerce_currency() . '" />';
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
        $metatags['og:product:sale_price:amount'] = '<meta property="product:sale_price:amount" content="' . $sale_price . '" />';
        // Currency
        $metatags['og:product:sale_price:currency'] = '<meta property="product:sale_price:currency" content="' . get_woocommerce_currency() . '" />';
    }
    // Sale price from date
    $sale_price_date_from = get_post_meta( $post->ID, '_sale_price_dates_from', true );
    if ( ! empty($sale_price_date_from) ) {
        $metatags['og:product:sale_price_dates:start'] = '<meta property="product:sale_price_dates:start" content="' . esc_attr(date_i18n('Y-m-d', $sale_price_date_from)) . '" />';
    }
    // Sale price to date
    $sale_price_date_to = get_post_meta( $post->ID, '_sale_price_dates_to', true );
    if ( ! empty($sale_price_date_to) ) {
        $metatags['og:product:sale_price_dates:end'] = '<meta property="product:sale_price_dates:end" content="' . esc_attr(date_i18n('Y-m-d', $sale_price_date_to)) . '" />';
    }

    // Product Data

    // Product category
    $product_cats = wp_get_post_terms( $post->ID, 'product_cat' );
    $product_category = array_shift($product_cats);
    if ( ! empty($product_category) ) {
        $metatags['og:product:category'] = '<meta property="product:category" content="' . esc_attr($product_category->name) . '" />';
    }

    // Brand
    $brand = $product->get_attribute( $property_map['product:brand'] );
    if ( ! empty($brand ) ) {
        $metatags['og:product:brand'] = '<meta property="product:brand" content="' . esc_attr($brand) . '" />';
    }

    // Weight
    // Also see:
    //product:shipping_weight:value
    //product:shipping_weight:units
    $weight_unit = apply_filters( 'amt_woocommerce_default_weight_unit', 'kg' );
    $weight = wc_get_weight( $product->get_weight(), $weight_unit );
    if ( ! empty($weight) ) {
        $metatags['product:weight:value'] = '<meta property="product:weight:value" content="' . esc_attr($weight) . '" />';
        $metatags['product:weight:units'] = '<meta property="product:weight:units" content="' . esc_attr($weight_unit) . '" />';
    }

    // Size
    // Do not confuse this with the product size LxWxH. This is an attribute.
    $size = $product->get_attribute( $property_map['product:size'] );
    if ( ! empty($size) ) {
        $metatags['og:product:size'] = '<meta property="product:size" content="' . esc_attr($size) . '" />';
    }

    // Color
    $color = $product->get_attribute( $property_map['product:color'] );
    if ( ! empty($color) ) {
        $metatags['og:product:color'] = '<meta property="product:color" content="' . esc_attr($color) . '" />';
    }

    // Material
    $material = $product->get_attribute( $property_map['product:material'] );
    if ( ! empty($material) ) {
        $metatags['og:product:material'] = '<meta property="product:material" content="' . esc_attr($material) . '" />';
    }

    // Condition
    $condition = $product->get_attribute( $property_map['product:condition'] );
    if ( ! empty($condition) ) {
        if ( in_array($age_group, array('new', 'refurbished', 'used') ) ) {
            $metatags['og:product:condition'] = '<meta property="product:condition" content="' . esc_attr($condition) . '" />';
        }
    } else {
        $metatags['og:product:condition'] = '<meta property="product:condition" content="new" />';
    }

    // Target gender
    $target_gender = $product->get_attribute( $property_map['product:target_gender'] );
    if ( ! empty($target_gender) && in_array($target_gender, array('male', 'female', 'unisex')) ) {
        $metatags['og:product:target_gender'] = '<meta property="product:target_gender" content="' . esc_attr($target_gender) . '" />';
    }

    // Age group
    $age_group = $product->get_attribute( $property_map['product:age_group'] );
    if ( ! empty($age_group) && in_array($age_group, array('kids', 'adult')) ) {
        $metatags['og:product:age_group'] = '<meta property="product:age_group" content="' . esc_attr($age_group) . '" />';
    }

    // Codes

    // EAN
    $ean = $product->get_attribute( $property_map['product:ean'] );
    if ( ! empty($ean) ) {
        $metatags['og:product:ean'] = '<meta property="product:ean" content="' . esc_attr($ean) . '" />';
    }

    // ISBN
    $isbn = $product->get_attribute( $property_map['product:isbn'] );
    if ( ! empty($isbn) ) {
        $metatags['og:product:isbn'] = '<meta property="product:isbn" content="' . esc_attr($isbn) . '" />';
    }

    // MPN: A manufacturer's part number for the item
    $mpn = $product->get_attribute( $property_map['product:mfr_part_no'] );
    if ( ! empty($mpn) ) {
        $metatags['og:product:mfr_part_no'] = '<meta property="product:mfr_part_no" content="' . esc_attr($mpn) . '" />';
    }

    // SKU (product:retailer_part_no?)
    // By convention we use the SKU as the product:retailer_part_no. TODO: check this
    $sku = $product->get_sku();
    if ( ! empty($sku) ) {
        $metatags['og:product:retailer_part_no'] = '<meta property="product:retailer_part_no" content="' . esc_attr($sku) . '" />';
    }

    // GTIN: A Global Trade Item Number, which encompasses UPC, EAN, JAN, and ISBN
    $gtin = $product->get_attribute( $property_map['product:gtin'] );
    if ( ! empty($gtin) ) {
        $metatags['og:product:gtin'] = '<meta property="product:gtin" content="' . esc_attr($gtin) . '" />';
    }

    // UPC: A Universal Product Code (UPC) for the product
    $upc = $product->get_attribute( $property_map['product:upc'] );
    if ( ! empty($upc) ) {
        $metatags['og:product:upc'] = '<meta property="product:upc" content="' . esc_attr($upc) . '" />';
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


// Schema.org microdata for woocommerce products
function amt_product_data_schemaorg_woocommerce( $metatags, $post ) {
    // Get the product object
    $product = get_product($post->ID);

    // WC API:
    // http://docs.woothemes.com/wc-apidocs/class-WC_Product.html
    // http://docs.woothemes.com/wc-apidocs/class-WC_Product_Variable.html
    // http://docs.woothemes.com/wc-apidocs/class-WC_Product_Variation.html
    // Schema.org:
    // http://schema.org/Product
    // http://schema.org/IndividualProduct
    // http://schema.org/ProductModel
    // http://schema.org/Offer
    // http://schema.org/Review
    // http://schema.org/AggregateRating

    // Currently, the schema.org microdata WC generator supports all product types.
    // simple, external, grouped (no price), variable (multiple prices)
    // The relevant meta tags are generated only if the relevant data can be retrieved
    // from the product object.
    $product_type = $product->product_type;
    //if ( ! in_array( $product_type, array('simple', 'external') ) ) {
    //    $metatags = apply_filters( 'amt_product_data_woocommerce_opengraph', $metatags );
    //    return $metatags;
    //}

    // Variations (only in variable products)
    $variations = null;
    if ( $product_type == 'variable' ) {
        $variations = $product->get_available_variations();
    }
    //var_dump($variations);

    // Variation attributes
    $variation_attributes = null;
    if ( $product_type == 'variable' ) {
        $variation_attributes = $product->get_variation_attributes();
    }
    //var_dump($variation_attributes);

    // Schema.org property to WooCommerce attribute map
    $property_map = array(
        'brand' => 'brand',
        'color' => 'color',
        'condition' => 'condition',
        'mpn' => 'mpn',
        'gtin' => 'gtin',
    );
    $property_map = apply_filters( 'amt_schemaorg_woocommerce_property_map', $property_map );


    // Product category
    $product_cats = wp_get_post_terms( $post->ID, 'product_cat' );
    $product_category = array_shift($product_cats);
    if ( ! empty($product_category) ) {
        $metatags['microdata:product:category'] = '<meta itemprop="category" content="' . esc_attr($product_category->name) . '" />';
    }

    // Brand
    $brand = $product->get_attribute( $property_map['brand'] );
    if ( ! empty($brand ) ) {
        $metatags['microdata:product:brand'] = '<meta itemprop="brand" content="' . esc_attr($brand) . '" />';
    }

    // Weight
    $weight_unit = apply_filters( 'amt_woocommerce_default_weight_unit', 'kg' );
    $weight = wc_get_weight( $product->get_weight(), $weight_unit );
    if ( ! empty($weight) ) {
        $metatags['microdata:product:weight:start'] = '<span itemprop="weight" itemscope itemtype="http://schema.org/QuantitativeValue">';
        $metatags['microdata:product:weight:value'] = '<meta itemprop="value" content="' . esc_attr($weight) . '" />';
        $metatags['microdata:product:weight:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($weight_unit) . '" />';
        $metatags['microdata:product:weight:end'] = '</span>';
    }

    // Dimensions
    // Schema.org has: width(length), depth(width), height(height)
    $dimension_unit = get_option( 'woocommerce_dimension_unit' );
    if ( ! empty($product->length) ) {
        $metatags['microdata:product:width:start'] = '<span itemprop="width" itemscope itemtype="http://schema.org/QuantitativeValue">';
        $metatags['microdata:product:width:value'] = '<meta itemprop="value" content="' . esc_attr($product->length) . '" />';
        $metatags['microdata:product:width:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($dimension_unit) . '" />';
        $metatags['microdata:product:width:end'] = '</span>';
    }
    if ( ! empty($product->width) ) {
        $metatags['microdata:product:depth:start'] = '<span itemprop="depth" itemscope itemtype="http://schema.org/QuantitativeValue">';
        $metatags['microdata:product:depth:value'] = '<meta itemprop="value" content="' . esc_attr($product->width) . '" />';
        $metatags['microdata:product:depth:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($dimension_unit) . '" />';
        $metatags['microdata:product:depth:end'] = '</span>';
    }
    if ( ! empty($product->height) ) {
        $metatags['microdata:product:height:start'] = '<span itemprop="height" itemscope itemtype="http://schema.org/QuantitativeValue">';
        $metatags['microdata:product:height:value'] = '<meta itemprop="value" content="' . esc_attr($product->height) . '" />';
        $metatags['microdata:product:height:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($dimension_unit) . '" />';
        $metatags['microdata:product:height:end'] = '</span>';
    }

    // Color
    $color = $product->get_attribute( $property_map['color'] );
    if ( ! empty($color) ) {
        $metatags['microdata:product:color'] = '<meta itemprop="color" content="' . esc_attr($color) . '" />';
    }

    // Condition
    $condition = $product->get_attribute( $property_map['condition'] );
    if ( ! empty($condition) ) {
        if ( in_array($age_group, array('new', 'refurbished', 'used') ) ) {
            $schema_org_condition_map = array(
                'new' => 'NewCondition',
                'refurbished' => 'RefurbishedCondition',
                'used' => 'UsedCondition',
            );
            $metatags['microdata:product:itemCondition'] = '<meta itemprop="itemCondition" content="' . esc_attr($schema_org_condition_map[$condition]) . '" />';
        }
    } else {
        $metatags['microdata:product:itemCondition'] = '<meta itemprop="itemCondition" content="http://schema.org/NewCondition" />';
    }

    // Codes

    // SKU (product:retailer_part_no?)
    // By convention we use the SKU as the product:retailer_part_no. TODO: check this
    $sku = $product->get_sku();
    if ( ! empty($sku) ) {
        $metatags['microdata:product:sku'] = '<meta itemprop="sku" content="' . esc_attr($sku) . '" />';
    }

    // GTIN: A Global Trade Item Number, which encompasses UPC, EAN, JAN, and ISBN
    $gtin = $product->get_attribute( $property_map['gtin'] );
    if ( ! empty($gtin) ) {
        $metatags['microdata:product:gtin14'] = '<meta itemprop="gtin14" content="' . esc_attr($gtin) . '" />';
    }

    // MPN: A manufacturer's part number for the item
    $mpn = $product->get_attribute( $property_map['mpn'] );
    if ( ! empty($mpn) ) {
        $metatags['microdata:product:mpn'] = '<meta itemprop="mpn" content="' . esc_attr($mpn) . '" />';
    }

    // Aggregated Rating
    $avg_rating = $product->get_average_rating();
    $rating_count = $product->get_rating_count();
    $review_count = $product->get_review_count();
    if ( $rating_count > 0 ) {
        // Scope BEGIN: AggregateRating: http://schema.org/AggregateRating
        $metatags['microdata:product:AggregateRating:start:comment'] = '<!-- Scope BEGIN: AggregateRating -->';
        $metatags['microdata:product:AggregateRating:start'] = '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
        // Rating value
        if ( ! empty($avg_rating) ) {
            $metatags['microdata:product:AggregateRating:ratingValue'] = '<meta itemprop="ratingValue" content="' . esc_attr($avg_rating) . '" />';
        }
        // Rating count
        if ( ! empty($rating_count) ) {
            $metatags['microdata:product:AggregateRating:ratingCount'] = '<meta itemprop="ratingCount" content="' . $rating_count . '" />';
        }
        // Review count
        if ( ! empty($review_count) ) {
            $metatags['microdata:product:AggregateRating:reviewCount'] = '<meta itemprop="reviewCount" content="' . $review_count . '" />';
        }
        // Scope END: AggregateRating
        $metatags['microdata:product:AggregateRating:end'] = '</span> <!-- Scope END: AggregateRating -->';

        // Reviews
        // Review counter
        //$rc = 0;
        // TODO: check how default reviews are generated by WC
        //$metatags[] = '<!-- Scope BEGIN: UserComments -->';
        //$metatags[] = '<span itemprop="review" itemscope itemtype="http://schema.org/Review">';
        //$metatags[] = '</span>';
    }


    // Offers

    if ( empty($variations) ) {

        // Availability
        $availability = '';
        if ( $product->is_in_stock() ) {
            $availability = 'InStock';
        //} elseif ( $product->backorders_allowed() ) {
        //    $availability = 'pending';
        } else {
            $availability = 'OutOfStock';
        }

        // Regular Price Offer

        // Scope BEGIN: Offer: http://schema.org/Offer
        $metatags['microdata:product:Offer:regular:start:comment'] = '<!-- Scope BEGIN: Offer -->';
        $metatags['microdata:product:Offer:regular:start'] = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
        // Availability
        if ( ! empty($availability) ) {
            $metatags['microdata:product:Offer:regular:availability'] = '<meta itemprop="availability" content="http://schema.org/' . esc_attr($availability) . '" />';
        }
        // Regular Price
        $regular_price = $product->get_regular_price();
        if ( ! empty($regular_price) ) {
            $metatags['microdata:product:Offer:regular:price'] = '<meta itemprop="price" content="' . $regular_price . '" />';
            // Currency
            $metatags['microdata:product:Offer:regular:priceCurrency'] = '<meta itemprop="priceCurrency" content="' . get_woocommerce_currency() . '" />';
        }
        // Scope END: Offer
        $metatags['microdata:product:Offer:regular:end'] = '</span> <!-- Scope END: Offer -->';

        // Sale Price Offer
        if ( $product->is_on_sale() ) {
            // Scope BEGIN: Offer: http://schema.org/Offer
            $metatags['microdata:product:Offer:sale:start:comment'] = '<!-- Scope BEGIN: Offer -->';
            $metatags['microdata:product:Offer:sale:start'] = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
            // Availability
            if ( ! empty($availability) ) {
                $metatags['microdata:product:Offer:sale:availability'] = '<meta itemprop="availability" content="http://schema.org/' . esc_attr($availability) . '" />';
            }
            // Sale Price
            $sale_price = $product->get_sale_price();
            if ( ! empty($sale_price) ) {
                $metatags['microdata:product:Offer:sale:price'] = '<meta itemprop="price" content="' . $sale_price . '" />';
                // Currency
                $metatags['microdata:product:Offer:sale:priceCurrency'] = '<meta itemprop="priceCurrency" content="' . get_woocommerce_currency() . '" />';
                // Sale price to date
                $sale_price_date_to = get_post_meta( $post->ID, '_sale_price_dates_to', true );
                if ( ! empty($sale_price_date_to) ) {
                    $metatags['microdata:product:Offer:sale:priceValidUntil'] = '<meta itemprop="priceValidUntil" content="' . esc_attr(date_i18n('Y-m-d', $sale_price_date_to)) . '" />';
                }
            }
            // Scope END: Offer
            $metatags['microdata:product:Offer:sale:end'] = '</span> <!-- Scope END: Offer -->';
        }

    // Offers for variations (Variable Products)
    } else {

        // Variation offers counter
        $oc = 0;

        foreach ( $variations as $variation_info ) {

            foreach ( array('regular', 'sale') as $offer_type ) {

                // Get the variation object
                $variation = $product->get_child($variation_info['variation_id']);
                //var_dump($variation);

                if ( $offer_type == 'sale' && ! $variation->is_on_sale() ) {
                    continue;
                }

                // Increase the Offer counter
                $oc++;

                // Availability
                $availability = '';
                if ( $variation->is_in_stock() ) {
                    $availability = 'InStock';
                //} elseif ( $variation->backorders_allowed() ) {
                //    $availability = 'pending';
                } else {
                    $availability = 'OutOfStock';
                }

                // Scope BEGIN: Offer: http://schema.org/Offer
                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':start:comment'] = '<!-- Scope BEGIN: Offer -->';
                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':start'] = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">';

                // Availability
                if ( ! empty($availability) ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':availability'] = '<meta itemprop="availability" content="http://schema.org/' . esc_attr($availability) . '" />';
                }

                // Regular Price Offer

                if ( $offer_type == 'regular' ) {

                    // Regular Price
                    $regular_price = $variation->get_regular_price();
                    if ( ! empty($regular_price) ) {
                        $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':price'] = '<meta itemprop="price" content="' . $regular_price . '" />';
                        // Currency
                        $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':priceCurrency'] = '<meta itemprop="priceCurrency" content="' . get_woocommerce_currency() . '" />';
                    }

                } elseif ( $offer_type == 'sale' ) {

                    // Sale Price Offer
                    if ( $variation->is_on_sale() ) {
                        // Sale Price
                        $sale_price = $variation->get_sale_price();
                        if ( ! empty($sale_price) ) {
                            $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':price'] = '<meta itemprop="price" content="' . $sale_price . '" />';
                            // Currency
                            $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':priceCurrency'] = '<meta itemprop="priceCurrency" content="' . get_woocommerce_currency() . '" />';
                            // Sale price to date
                            $sale_price_date_to = get_post_meta( $variation->variation_id, '_sale_price_dates_to', true );
                            if ( ! empty($sale_price_date_to) ) {
                                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':priceValidUntil'] = '<meta itemprop="priceValidUntil" content="' . esc_attr(date_i18n('Y-m-d', $sale_price_date_to)) . '" />';
                            }
                        }
                    }

                }

                // Item Offered

                // Check whether you should use 'IndividualProduct)
                // Scope BEGIN: Product: http://schema.org/Product
                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:start:comment'] = '<!-- Scope BEGIN: Product -->';
                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:start'] = '<span itemprop="itemOffered" itemscope itemtype="http://schema.org/Product">';

                // Attributes
                foreach ( $variation_info['attributes'] as $variation_attribute_name => $variation_attribute_value ) {
                    $variation_attribute_name = str_replace('attribute_pa_', '', $variation_attribute_name);
                    $variation_attribute_name = str_replace('attribute_', '', $variation_attribute_name);
                    if ( ! empty($variation_attribute_value) ) {
                        $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:'.$variation_attribute_name.':start'] = '<span itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">';
                        $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:'.$variation_attribute_name.':name'] = '<meta itemprop="name" content="' . esc_attr($variation_attribute_name) . '" />';
                        $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:'.$variation_attribute_name.':value'] = '<meta itemprop="value" content="' . esc_attr($variation_attribute_value) . '" />';
                        $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:'.$variation_attribute_name.':end'] = '</span>';
                    }
                }

                // Weight
                $variation_weight = wc_get_weight( $variation->get_weight(), $weight_unit );
                if ( ! empty($variation_weight) && $variation_weight != $weight ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:weight:start'] = '<span itemprop="weight" itemscope itemtype="http://schema.org/QuantitativeValue">';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:weight:value'] = '<meta itemprop="value" content="' . esc_attr($variation_weight) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:weight:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($weight_unit) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:weight:end'] = '</span>';
                }

                // Dimensions
                // Schema.org has: width(length), depth(width), height(height)
                if ( ! empty($variation->length) && $variation->length != $product->length ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:width:start'] = '<span itemprop="width" itemscope itemtype="http://schema.org/QuantitativeValue">';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:width:value'] = '<meta itemprop="value" content="' . esc_attr($variation->length) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:width:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($dimension_unit) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:width:end'] = '</span>';
                }
                if ( ! empty($variation->width) && $variation->width != $product->width ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:depth:start'] = '<span itemprop="depth" itemscope itemtype="http://schema.org/QuantitativeValue">';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:depth:value'] = '<meta itemprop="value" content="' . esc_attr($variation->width) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:depth:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($dimension_unit) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:depth:end'] = '</span>';
                }
                if ( ! empty($variation->height) && $variation->height != $product->height ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:height:start'] = '<span itemprop="height" itemscope itemtype="http://schema.org/QuantitativeValue">';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:height:value'] = '<meta itemprop="value" content="' . esc_attr($variation->height) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:height:unitText'] = '<meta itemprop="unitText" content="' . esc_attr($dimension_unit) . '" />';
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:height:end'] = '</span>';
                }

                // Image
                $parent_image_id = $product->get_image_id();
                $variation_image_id = $variation->get_image_id();
                if ( ! empty($variation_image_id) && $variation_image_id != $parent_image_id ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:image'] = '<meta itemprop="image" content="' . esc_url_raw( wp_get_attachment_url($variation_image_id) ) . '" />';
                }

                // Codes

                // SKU
                $variation_sku = $variation->get_sku();
                if ( ! empty($variation_sku) && $variation_sku != $sku ) {
                    $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:sku'] = '<meta itemprop="sku" content="' . esc_attr($variation_sku) . '" />';
                }

                // Scope END: Product
                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':itemOffered:end'] = '</span> <!-- Scope END: Item Offered - Product -->';

                // Scope END: Offer
                $metatags['microdata:product:Offer:'.$oc.':'.$offer_type.':end'] = '</span> <!-- Scope END: Offer -->';
                
            }
        }
    }


// productID
//model

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

        // WC API:
    // http://docs.woothemes.com/wc-apidocs/class-WC_Product.html
    // http://docs.woothemes.com/wc-apidocs/class-WC_Product_Variable.html
    // http://docs.woothemes.com/wc-apidocs/class-WC_Product_Variation.html
    // Schema.org:
    // http://schema.org/Product
    // http://schema.org/IndividualProduct
    // http://schema.org/ProductModel
    // http://schema.org/Offer
    // http://schema.org/Review
    // http://schema.org/AggregateRating

    // Currently, the schema.org JSON-LD WC generator supports all product types.
    // simple, external, grouped (no price), variable (multiple prices)
    // The relevant meta tags are generated only if the relevant data can be retrieved
    // from the product object.
    $product_type = $product->product_type;
    //if ( ! in_array( $product_type, array('simple', 'external') ) ) {
    //    $metatags = apply_filters( 'amt_product_data_woocommerce_opengraph', $metatags );
    //    return $metatags;
    //}

    // Variations (only in variable products)
    $variations = null;
    if ( $product_type == 'variable' ) {
        $variations = $product->get_available_variations();
    }
    //var_dump($variations);

    // Variation attributes
    $variation_attributes = null;
    if ( $product_type == 'variable' ) {
        $variation_attributes = $product->get_variation_attributes();
    }
    //var_dump($variation_attributes);

    // Schema.org property to WooCommerce attribute map
    $property_map = array(
        'brand' => 'brand',
        'color' => 'color',
        'condition' => 'condition',
        'mpn' => 'mpn',
        'gtin' => 'gtin',
    );
    $property_map = apply_filters( 'amt_schemaorg_woocommerce_property_map', $property_map );


    // Product category
    $product_cats = wp_get_post_terms( $post->ID, 'product_cat' );
    $product_category = array_shift($product_cats);
    if ( ! empty($product_category) ) {
        $metatags['category'] = esc_attr($product_category->name);
    }

    // Brand
    $brand = $product->get_attribute( $property_map['brand'] );
    if ( ! empty($brand ) ) {
        $metatags['brand'] = esc_attr($brand);
    }

    // Weight
    $weight_unit = apply_filters( 'amt_woocommerce_default_weight_unit', 'kg' );
    $weight = wc_get_weight( $product->get_weight(), $weight_unit );
    if ( ! empty($weight) ) {
        $metatags['weight'] = array();
        $metatags['weight']['@type'] = 'QuantitativeValue';
        $metatags['weight']['value'] = esc_attr($weight);
        $metatags['weight']['unitText'] = esc_attr($weight_unit);
    }

    // Dimensions
    // Schema.org has: width(length), depth(width), height(height)
    $dimension_unit = get_option( 'woocommerce_dimension_unit' );
    if ( ! empty($product->length) ) {
        $metatags['width'] = array();
        $metatags['width']['@type'] = 'QuantitativeValue';
        $metatags['width']['value'] = esc_attr($product->length);
        $metatags['width']['unitText'] = esc_attr($dimension_unit);
    }
    if ( ! empty($product->width) ) {
        $metatags['depth'] = array();
        $metatags['depth']['@type'] = 'QuantitativeValue';
        $metatags['depth']['value'] = esc_attr($product->width);
        $metatags['depth']['unitText'] = esc_attr($dimension_unit);
    }
    if ( ! empty($product->height) ) {
        $metatags['height'] = array();
        $metatags['height']['@type'] = 'QuantitativeValue';
        $metatags['height']['value'] = esc_attr($product->height);
        $metatags['height']['unitText'] = esc_attr($dimension_unit);
    }

    // Color
    $color = $product->get_attribute( $property_map['color'] );
    if ( ! empty($color) ) {
        $metatags['color'] = esc_attr($color);
    }

    // Condition
    $condition = $product->get_attribute( $property_map['condition'] );
    if ( ! empty($condition) ) {
        if ( in_array($age_group, array('new', 'refurbished', 'used') ) ) {
            $schema_org_condition_map = array(
                'new' => 'NewCondition',
                'refurbished' => 'RefurbishedCondition',
                'used' => 'UsedCondition',
            );
            $metatags['itemCondition'] = esc_attr($schema_org_condition_map[$condition]);
        }
    } else {
        $metatags['itemCondition'] = 'NewCondition';
    }

    // Codes

    // SKU (product:retailer_part_no?)
    // By convention we use the SKU as the product:retailer_part_no. TODO: check this
    $sku = $product->get_sku();
    if ( ! empty($sku) ) {
        $metatags['sku'] = esc_attr($sku);
    }

    // GTIN: A Global Trade Item Number, which encompasses UPC, EAN, JAN, and ISBN
    $gtin = $product->get_attribute( $property_map['gtin'] );
    if ( ! empty($gtin) ) {
        $metatags['gtin14'] = esc_attr($gtin);
    }

    // MPN: A manufacturer's part number for the item
    $mpn = $product->get_attribute( $property_map['mpn'] );
    if ( ! empty($mpn) ) {
        $metatags['mpn'] = esc_attr($mpn);
    }

    // Aggregated Rating
    $avg_rating = $product->get_average_rating();
    $rating_count = $product->get_rating_count();
    $review_count = $product->get_review_count();
    if ( $rating_count > 0 ) {
        $metatags['aggregateRating'] = array();
        $metatags['aggregateRating']['@type'] = 'AggregateRating';
        // Rating value
        if ( ! empty($avg_rating) ) {
            $metatags['aggregateRating']['ratingValue'] = esc_attr($avg_rating);
        }
        // Rating count
        if ( ! empty($rating_count) ) {
            $metatags['aggregateRating']['ratingCount'] = esc_attr($rating_count);
        }
        // Review count
        if ( ! empty($review_count) ) {
            $metatags['aggregateRating']['reviewCount'] = esc_attr($review_count);
        }

        // Reviews
        // Review counter
        //$rc = 0;
        // TODO: check how default reviews are generated by WC
        //$metatags[] = '<!-- Scope BEGIN: UserComments -->';
        //$metatags[] = '<span itemprop="review" itemscope itemtype="http://schema.org/Review">';
        //$metatags[] = '</span>';
    }


    // Offers

    $metatags['offers'] = array();

    if ( empty($variations) ) {

        // Availability
        $availability = '';
        if ( $product->is_in_stock() ) {
            $availability = 'InStock';
        //} elseif ( $product->backorders_allowed() ) {
        //    $availability = 'pending';
        } else {
            $availability = 'OutOfStock';
        }

        // Regular Price Offer

        $offer = array();
        $offer['@type'] = 'Offer';

        // Availability
        if ( ! empty($availability) ) {
            $offer['availability'] = 'http://schema.org/' . esc_attr($availability);
        }
        // Regular Price
        $regular_price = $product->get_regular_price();
        if ( ! empty($regular_price) ) {
            $offer['price'] = esc_attr($regular_price);
            // Currency
            $offer['priceCurrency'] = esc_attr(get_woocommerce_currency());
        }

        $metatags['offers'][] = $offer;

        // Sale Price Offer
        if ( $product->is_on_sale() ) {

            $offer = array();
            $offer['@type'] = 'Offer';

            // Availability
            if ( ! empty($availability) ) {
                $offer['availability'] = 'http://schema.org/' . esc_attr($availability);
            }
            // Sale Price
            $sale_price = $product->get_sale_price();
            if ( ! empty($sale_price) ) {
                $offer['price'] = esc_attr($sale_price);
                // Currency
                $offer['priceCurrency'] = esc_attr(get_woocommerce_currency());
                // Sale price to date
                $sale_price_date_to = get_post_meta( $post->ID, '_sale_price_dates_to', true );
                if ( ! empty($sale_price_date_to) ) {
                    $offer['priceValidUntil'] = esc_attr(date_i18n('Y-m-d', $sale_price_date_to));
                }
            }

            $metatags['offers'][] = $offer;

        }

    // Offers for variations (Variable Products)
    } else {

        // Variation offers counter
        $oc = 0;

        foreach ( $variations as $variation_info ) {

            foreach ( array('regular', 'sale') as $offer_type ) {

                // Get the variation object
                $variation = $product->get_child($variation_info['variation_id']);
                //var_dump($variation);

                if ( $offer_type == 'sale' && ! $variation->is_on_sale() ) {
                    continue;
                }

                // Increase the Offer counter
                $oc++;

                // Availability
                $availability = '';
                if ( $variation->is_in_stock() ) {
                    $availability = 'InStock';
                //} elseif ( $variation->backorders_allowed() ) {
                //    $availability = 'pending';
                } else {
                    $availability = 'OutOfStock';
                }

                $offer = array();
                $offer['@type'] = 'Offer';

                // Availability
                if ( ! empty($availability) ) {
                    $offer['availability'] = 'http://schema.org/' . esc_attr($availability);
                }

                // Regular Price Offer

                if ( $offer_type == 'regular' ) {

                    // Regular Price
                    $regular_price = $variation->get_regular_price();
                    if ( ! empty($regular_price) ) {
                        $offer['price'] = esc_attr($regular_price);
                        // Currency
                        $offer['priceCurrency'] = esc_attr(get_woocommerce_currency());
                    }

                } elseif ( $offer_type == 'sale' ) {

                    // Sale Price Offer
                    if ( $variation->is_on_sale() ) {
                        // Sale Price
                        $sale_price = $variation->get_sale_price();
                        if ( ! empty($sale_price) ) {
                            $offer['price'] = esc_attr($sale_price);
                            // Currency
                            $offer['priceCurrency'] = esc_attr(get_woocommerce_currency());
                            // Sale price to date
                            $sale_price_date_to = get_post_meta( $variation->variation_id, '_sale_price_dates_to', true );
                            if ( ! empty($sale_price_date_to) ) {
                                $offer['priceValidUntil'] = esc_attr(date_i18n('Y-m-d', $sale_price_date_to));
                            }
                        }
                    }

                }

                // Item Offered

                $offer['itemOffered'] = array();
                $offer['itemOffered']['@type'] = 'Product';

                // Check whether you should use 'IndividualProduct)

                // Attributes
                $offer['itemOffered']['additionalProperty'] = array();
                foreach ( $variation_info['attributes'] as $variation_attribute_name => $variation_attribute_value ) {
                    $variation_attribute_name = str_replace('attribute_pa_', '', $variation_attribute_name);
                    $variation_attribute_name = str_replace('attribute_', '', $variation_attribute_name);
                    if ( ! empty($variation_attribute_value) ) {
                        $additional_property = array();
                        $additional_property['@type'] = 'PropertyValue';
                        $additional_property['name'] = esc_attr($variation_attribute_name);
                        $additional_property['value'] = esc_attr($variation_attribute_value);
                        $offer['itemOffered']['additionalProperty'][] = $additional_property;
                    }
                }

                // Weight
                $variation_weight = wc_get_weight( $variation->get_weight(), $weight_unit );
                if ( ! empty($variation_weight) && $variation_weight != $weight ) {
                    $offer['itemOffered']['weight'] = array();
                    $offer['itemOffered']['weight']['@type'] = 'QuantitativeValue';
                    $offer['itemOffered']['weight']['value'] = esc_attr($variation_weight);
                    $offer['itemOffered']['weight']['unitText'] = esc_attr($weight_unit);
                }

                // Dimensions
                // Schema.org has: width(length), depth(width), height(height)
                if ( ! empty($variation->length) && $variation->length != $product->length ) {
                    $offer['itemOffered']['width'] = array();
                    $offer['itemOffered']['width']['@type'] = 'QuantitativeValue';
                    $offer['itemOffered']['width']['value'] = esc_attr($variation->length);
                    $offer['itemOffered']['width']['unitText'] = esc_attr($dimension_unit);
                }
                if ( ! empty($variation->width) && $variation->width != $product->width ) {
                    $offer['itemOffered']['depth'] = array();
                    $offer['itemOffered']['depth']['@type'] = 'QuantitativeValue';
                    $offer['itemOffered']['depth']['value'] = esc_attr($variation->width);
                    $offer['itemOffered']['depth']['unitText'] = esc_attr($dimension_unit);
                }
                if ( ! empty($variation->height) && $variation->height != $product->height ) {
                    $offer['itemOffered']['height'] = array();
                    $offer['itemOffered']['height']['@type'] = 'QuantitativeValue';
                    $offer['itemOffered']['height']['value'] = esc_attr($variation->height);
                    $offer['itemOffered']['height']['unitText'] = esc_attr($dimension_unit);
                }

                // Image
                $parent_image_id = $product->get_image_id();
                $variation_image_id = $variation->get_image_id();
                if ( ! empty($variation_image_id) && $variation_image_id != $parent_image_id ) {
                    $offer['itemOffered']['image'] = esc_url_raw( wp_get_attachment_url($variation_image_id) );
                }

                // Codes

                // SKU
                $variation_sku = $variation->get_sku();
                if ( ! empty($variation_sku) && $variation_sku != $sku ) {
                    $offer['itemOffered']['sku'] = esc_attr($variation_sku);
                }

                $metatags['offers'][] = $offer;
            }
        }
    }

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


// Retrieve WooCommerce page IDs
//
//get_option( 'woocommerce_shop_page_id' ); 
//get_option( 'woocommerce_cart_page_id' ); 
//get_option( 'woocommerce_checkout_page_id' );
//get_option( 'woocommerce_pay_page_id' ); 
//get_option( 'woocommerce_thanks_page_id' ); 
//get_option( 'woocommerce_myaccount_page_id' ); 
//get_option( 'woocommerce_edit_address_page_id' ); 
//get_option( 'woocommerce_view_order_page_id' ); 
//get_option( 'woocommerce_terms_page_id' ); 



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
    if ( is_singular() && 'download' == get_post_type() ) {
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
function amt_detect_ecommerce_product( $default ) {

    // First and important check.
    // $default is a boolean variable which indicates if custom content has been
    // detected by any previous filter.
    // Check if custom content has already been detected by another filter.
    // If such content has been detected, just return $default (should be true)
    // and *do not* add any metadata filters.
    // This check is mandatory in order the detection mechanism to work correctly.
    if ( $default ) {
        return $default;
    }

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
function amt_detect_ecommerce_product_group( $default ) {

    // First and important check.
    // $default is a boolean variable which indicates if custom content has been
    // detected by any previous filter.
    // Check if custom content has already been detected by another filter.
    // If such content has been detected, just return $default (should be true)
    // and *do not* add any metadata filters.
    // This check is mandatory in order the detection mechanism to work correctly.
    if ( $default ) {
        return $default;
    }

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






//
//
//  BuddyPress Support
//
//


// BuddyPress detection
function amt_detect_buddypress( $default, $post, $options ) {
    // First and important check.
    // $default is a boolean variable which indicates if custom content has been
    // detected by any previous filter.
    // Check if custom content has already been detected by another filter.
    // If such content has been detected, just return $default (should be true)
    // and *do not* add any metadata filters.
    // This check is mandatory in order the detection mechanism to work correctly.
    if ( $default ) {
        return $default;
    }
    // Process BuddyPress metadata, only if the BuddyPress extended metadata
    // support has been enabled in the Add-Meta-Tags settings.
    if ( $options["extended_support_buddypress"] == "1" ) {
        // Perform the 'function_exists()' test in case BuddyPress is not installed/activated.
        if ( ! function_exists('is_buddypress') || ! is_buddypress() ) {
            return false;
        }
        // Insert metadata for BuddyPress pages
        // Basic (description/keywords)
        //remove_all_filters( 'amt_custom_metadata_basic' );    // CHECK IF NEEDED
        add_filter( 'amt_custom_metadata_basic', 'amt_buddypress_basic', 10, 5 );
        // Opengraph
        add_filter( 'amt_custom_metadata_opengraph', 'amt_buddypress_opengraph', 10, 5 );
        // Twitter Cards
        add_filter( 'amt_custom_metadata_twitter_cards', 'amt_buddypress_twitter_cards', 10, 5 );
        // Dublin Core
        add_filter( 'amt_custom_metadata_dublin_core', 'amt_buddypress_dublin_core', 10, 5 );
        // Schema.org
        if ( $options["schemaorg_force_jsonld"] == "0" ) {
            // Microdata
            // Non content pages via 'wp_footer' action
            add_filter( 'amt_custom_metadata_schemaorg_footer', 'amt_buddypress_schemaorg_footer', 10, 5 );
            // Content pages via 'the_content' filter
//            add_filter( 'amt_custom_metadata_schemaorg_content_filter', 'amt_buddypress_schemaorg_content_filter', 10, 5 );
        } else {
            add_filter( 'amt_custom_metadata_jsonld_schemaorg', 'amt_buddypress_jsonld_schemaorg', 10, 5 );
        }
        // Finally return true. BuddyPress detected.
        return true;
    }
    return false;
}
add_filter( 'amt_is_custom', 'amt_detect_buddypress', 10, 3 );


function amt_buddypress_basic( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // User Profiles

    // Determines if a BuddyPress user profile has been requested
    if ( bp_is_user_profile() ) {
        // https://codex.buddypress.org/developer/the-bp-global/
        global $bp;
        // $user_id = $bp->displayed_user->id;
        $user_id = bp_displayed_user_id();
        // $user_domain = $bp->displayed_user->domain;
        // bp_core_get_user_domain( bp_displayed_user_id() )
        $user_domain = bp_displayed_user_domain();
        $user_profile_url = trailingslashit( bp_displayed_user_domain() . amt_bp_get_profile_slug() );
        $user_fullname = $bp->displayed_user->fullname;
        // $user_fullname = bp_displayed_user_fullname();
        // $user_username = $bp->displayed_user->user_login;
        $user_username = bp_get_displayed_user_username();
        //$wp_user_obj = get_user_by( 'id', $user_id );
        $wp_user_obj = get_userdata( $user_id );
        //var_dump($wp_user_obj);

        // Related resources
        // Perhaps add Facebook, Twitter, Google+ profile URLs in 'og:see_also' meta tags
        // og:see_also

        // Determines if Extended Profiles component is active.
        if ( ! bp_is_active( 'xprofile' ) ) {

            // Description
            $author_description = sanitize_text_field( amt_sanitize_description( $wp_user_obj->description ) );
            if ( empty($author_description) ) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $wp_user_obj->display_name ) . '" />';
            } else {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( $author_description ) . '" />';
            }

            // No automatic keywords

        // Extended Profiles
        } else {
            // https://codex.buddypress.org/themes/guides/displaying-extended-profile-fields-on-member-profiles/

            $xprofile_field_map = amt_buddypress_get_xprofile_field_map();
            // Get list of IDs of public fields
            $xprofile_public_fields = bp_xprofile_get_fields_by_visibility_levels( $user_id, array('public') );

            // Description
            $field_value = amt_bp_get_profile_field_data( 'description', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( amt_sanitize_description( $field_value ) );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( $field_value ) . '" />';
            } else {
                $metadata_arr[] = '<meta name="description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $user_fullname ) . '" />';
            }

            // Keywords
            $field_value = amt_bp_get_profile_field_data( 'keywords', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( amt_sanitize_keywords( $field_value ) );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta name="keywords" content="' . esc_attr( $field_value ) . '" />';
            }

        }

    }

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_basic_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_basic_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_buddypress_opengraph( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // User Profiles

    // Determines if a BuddyPress user profile has been requested
    if ( bp_is_user_profile() ) {
        // https://codex.buddypress.org/developer/the-bp-global/
        global $bp;
        // $user_id = $bp->displayed_user->id;
        $user_id = bp_displayed_user_id();
        // $user_domain = $bp->displayed_user->domain;
        // bp_core_get_user_domain( bp_displayed_user_id() )
        $user_domain = bp_displayed_user_domain();
        $user_profile_url = trailingslashit( bp_displayed_user_domain() . amt_bp_get_profile_slug() );
        $user_fullname = $bp->displayed_user->fullname;
        // $user_fullname = bp_displayed_user_fullname();
        // $user_username = $bp->displayed_user->user_login;
        $user_username = bp_get_displayed_user_username();
        //$wp_user_obj = get_user_by( 'id', $user_id );
        $wp_user_obj = get_userdata( $user_id );
        //var_dump($wp_user_obj);

        // Type
        // https://developers.facebook.com/docs/reference/opengraph/object-type/profile/
        $metadata_arr[] = '<meta property="og:type" content="profile" />';
        // Site Name
        $metadata_arr[] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo('name') ) . '" />';
        // Title
        $metadata_arr['og:title'] = '<meta property="og:title" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $wp_user_obj->display_name ) . '" />';
        // URL
        $metadata_arr[] = '<meta property="og:url" content="' . esc_url( $user_profile_url, array('http', 'https') ) . '" />';
        // Locale
        $metadata_arr[] = '<meta property="og:locale" content="' . esc_attr( str_replace('-', '_', amt_get_language_site($options)) ) . '" />';
        // fb:profile_id
        // The fb:profile_id field associates the object with a Facebook user.

        // Related resources as 'og:see_also meta' tags
        // Perhaps add Facebook, Twitter, Google+ profile URLs in 'og:see_also' meta tags
        // Facebook Profile
        //$fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $user_id);
        $fb_author_url = get_user_meta($user_id, 'amt_facebook_author_profile_url', true);
        if ( ! empty($fb_author_url) ) {
            $metadata_arr[] = '<meta property="og:see_also" content="' . esc_url( $fb_author_url, array('http', 'https') ) . '" />';
        }
        // Twitter
        //$twitter_author_username = get_the_author_meta('amt_twitter_author_username', $user_id);
        $twitter_author_username = get_user_meta($user_id, 'amt_twitter_author_username', true);
        if ( ! empty($twitter_author_username) ) {
            $metadata_arr[] = '<meta property="og:see_also" content="https://twitter.com/' . esc_attr( $twitter_author_username ) . '" />';
        }
        // Google+
        //$googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $wp_user_obj);
        $googleplus_author_url = get_user_meta($user_id, 'amt_googleplus_author_profile_url', true);
        if ( ! empty( $googleplus_author_url ) ) {
            $metadata_arr[] = '<meta property="og:see_also" content="' . esc_url( $googleplus_author_url, array('http', 'https') ) . '" />';
        }

        // profile:username
        if ( ! empty($user_username) ) {
            $metadata_arr[] = '<meta property="profile:username" content="' . esc_attr( $user_username ) . '" />';
        }


        // Determines if Extended Profiles component is active.

        if ( ! bp_is_active( 'xprofile' ) ) {

            // Website
            //$website_url = get_user_meta($user_id, 'amt_googleplus_author_profile_url', true);
            $website_url = get_the_author_meta( 'user_url', $user_id );
            if ( ! empty( $website_url ) ) {
                $metadata_arr[] = '<meta property="og:see_also" content="' . esc_url( $website_url, array('http', 'https') ) . '" />';
            }

            // Description
            $author_description = sanitize_text_field( amt_sanitize_description( $wp_user_obj->description ) );
            if ( empty($author_description) ) {
                $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $wp_user_obj->display_name ) . '" />';
            } else {
                $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( $author_description ) . '" />';
            }

            // Profile Image
            $author_email = sanitize_email( $wp_user_obj->user_email );
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            // First try to get the avatar link by using get_avatar().
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_img = get_avatar( get_the_author_meta('ID', $wp_user_obj->ID), $avatar_size, '', get_the_author_meta('display_name', $wp_user_obj->ID) );
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
                    $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $avatar_size['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $avatar_size['height'] ) . '" />';
                    // Since we do not have a way to determine the image type, the following meta tag is commented out
                    // TODO: make a function that detects the image type from the file extension (if a file extension is available)
                    //$metadata_arr[] = '<meta property="og:image:type" content="image/jpeg" />';
                }
            }

            // Other Profile Data

            // profile:last_name
            $last_name = $wp_user_obj->last_name;
            if ( ! empty($last_name) ) {
                $metadata_arr[] = '<meta property="profile:last_name" content="' . esc_attr( $last_name ) . '" />';
            }

            // profile:first_name
            $first_name = $wp_user_obj->first_name;
            if ( ! empty($first_name) ) {
                $metadata_arr[] = '<meta property="profile:first_name" content="' . esc_attr( $first_name ) . '" />';
            }


        // Extended Profiles

        } else {
            // https://codex.buddypress.org/themes/guides/displaying-extended-profile-fields-on-member-profiles/

            $xprofile_field_map = amt_buddypress_get_xprofile_field_map();
            // Get list of IDs of public fields
            $xprofile_public_fields = bp_xprofile_get_fields_by_visibility_levels( $user_id, array('public') );

            // Website
            $field_value = amt_bp_get_profile_field_data( 'website', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta property="og:see_also" content="' . esc_url( $field_value, array('http', 'https') ) . '" />';
            }

            // Description
            $field_value = amt_bp_get_profile_field_data( 'description', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( amt_sanitize_description( $field_value ) );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( $field_value ) . '" />';
            } else {
                $metadata_arr[] = '<meta property="og:description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $user_fullname ) . '" />';
            }
                
            // Profile Image
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            $avatar_args = array(
                'item_id'   => $user_id,
                'width'     => $avatar_size['width'],
                'height'    => $avatar_size['height'],
            );
            $avatar_img = bp_core_fetch_avatar( $avatar_args );
            if ( ! empty($avatar_img) ) {
                if ( preg_match("#src=['\"]([^'\"]+)['\"]#", $avatar_img, $matches) ) {
                    $avatar_url = $matches[1];
                }
            }
            if ( ! empty($avatar_url) ) {
                //$avatar_url = html_entity_decode($avatar_url, ENT_NOQUOTES, 'UTF-8');
                $metadata_arr[] = '<meta property="og:image" content="' . esc_url_raw( $avatar_url ) . '" />';
                // Add an og:imagesecure_url if the image URL uses HTTPS
                if ( strpos($avatar_url, 'https://') !== false ) {
                    $metadata_arr[] = '<meta property="og:imagesecure_url" content="' . esc_url_raw( $avatar_url ) . '" />';
                }
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $metadata_arr[] = '<meta property="og:image:width" content="' . esc_attr( $avatar_size['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="og:image:height" content="' . esc_attr( $avatar_size['height'] ) . '" />';
                    // Since we do not have a way to determine the image type, the following meta tag is commented out
                    // TODO: make a function that detects the image type from the file extension (if a file extension is available)
                    //$metadata_arr[] = '<meta property="og:image:type" content="image/jpeg" />';
                }
            }

            // Other Profile Data

            // profile:last_name
            $has_last_name = false;
            $field_value = amt_bp_get_profile_field_data( 'last_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta property="profile:last_name" content="' . esc_attr( $field_value ) . '" />';
                $has_last_name = true;
            }

            // profile:first_name
            $has_first_name = false;
            $field_value = amt_bp_get_profile_field_data( 'first_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta property="profile:first_name" content="' . esc_attr( $field_value ) . '" />';
                $has_first_name = true;
            }

            // Generate first and last name from full name if needed.
            if ( ! $has_last_name && ! $has_first_name && ! empty($user_fullname) ) {
                $parts = explode(' ', $user_fullname);
                $last_name = sanitize_text_field( array_pop($parts) ); // Removes and returns the element off the end of array
                if ( ! empty($last_name) ) {
                    $metadata_arr[] = '<meta property="profile:last_name" content="' . esc_attr( $last_name ) . '" />';
                }
                $first_name = sanitize_text_field( implode(' ', $parts) );
                if ( ! empty($first_name) ) {
                    $metadata_arr[] = '<meta property="profile:first_name" content="' . esc_attr( $first_name ) . '" />';
                }
            }

            // profile:gender
            $field_value = amt_bp_get_profile_field_data( 'gender', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta property="profile:gender" content="' . esc_attr( $field_value ) . '" />';
            }

        }

    }


/*

    if ( bp_is_current_component( 'xprofile' ) ) {
        $metadata_arr[] = 'XPROFILE_IS_CURRENT_COMPONENT';
    }
    if ( bp_is_user() ) {
        $metadata_arr[] = 'IS_USER';
    }

    if ( bp_is_profile_component() ) {
        $metadata_arr[] = 'IS_PROFILE_COMPONENT';
    }

*/


    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_opengraph_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_opengraph_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_buddypress_twitter_cards( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // User Profiles

    // Determines if a BuddyPress user profile has been requested
    if ( bp_is_user_profile() ) {
        // https://codex.buddypress.org/developer/the-bp-global/
        global $bp;
        // $user_id = $bp->displayed_user->id;
        $user_id = bp_displayed_user_id();
        // $user_domain = $bp->displayed_user->domain;
        // bp_core_get_user_domain( bp_displayed_user_id() )
        $user_domain = bp_displayed_user_domain();
        $user_profile_url = trailingslashit( bp_displayed_user_domain() . amt_bp_get_profile_slug() );
        $user_fullname = $bp->displayed_user->fullname;
        // $user_fullname = bp_displayed_user_fullname();
        // $user_username = $bp->displayed_user->user_login;
        $user_username = bp_get_displayed_user_username();
        //$wp_user_obj = get_user_by( 'id', $user_id );
        $wp_user_obj = get_userdata( $user_id );
        //var_dump($wp_user_obj);

        // Generate a twitter card only if the user and the publisher have
        // filled in their Twitter usernames.
        $twitter_author_username = get_the_author_meta('amt_twitter_author_username', $user_id);
        $twitter_publisher_username = $options['social_main_twitter_publisher_username'];
        if ( empty($twitter_author_username) || empty($twitter_publisher_username) ) {
            return $metadata_arr;
        }
        // Type
        $metadata_arr[] = '<meta name="twitter:card" content="' . amt_get_default_twitter_card_type($options) . '" />';
        // Creator
        $metadata_arr[] = '<meta name="twitter:creator" content="@' . esc_attr( $twitter_author_username ) . '" />';
        // Site
        $metadata_arr[] = '<meta name="twitter:site" content="@' . esc_attr( $twitter_publisher_username ) . '" />';
        // Title
        $metadata_arr['twitter:title'] = '<meta name="twitter:title" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $user_fullname ) . '" />';

        // Determines if Extended Profiles component is active.
        if ( ! bp_is_active( 'xprofile' ) ) {

            // Description
            $author_description = sanitize_text_field( amt_sanitize_description( $wp_user_obj->description ) );
            if ( empty($author_description) ) {
                $metadata_arr['twitter:description'] = '<meta name="twitter:description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $wp_user_obj->display_name ) . '" />';
            } else {
                $metadata_arr['twitter:description'] = '<meta name="twitter:description" content="' . esc_attr( $author_description ) . '" />';
            }

            // Profile Image
            $author_email = sanitize_email( $wp_user_obj->user_email );
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            // First try to get the avatar link by using get_avatar().
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_img = get_avatar( get_the_author_meta('ID', $wp_user_obj->ID), $avatar_size, '', get_the_author_meta('display_name', $wp_user_obj->ID) );
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
                $metadata_arr[] = '<meta property="twitter:image" content="' . esc_url_raw( $avatar_url ) . '" />';
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $metadata_arr[] = '<meta property="twitter:image:width" content="' . esc_attr( $avatar_size['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:height" content="' . esc_attr( $avatar_size['height'] ) . '" />';
                    // Since we do not have a way to determine the image type, the following meta tag is commented out
                    // TODO: make a function that detects the image type from the file extension (if a file extension is available)
                    //$metadata_arr[] = '<meta property="twitter:image:type" content="image/jpeg" />';
                }
            }

        // Extended Profiles
        } else {
            // https://codex.buddypress.org/themes/guides/displaying-extended-profile-fields-on-member-profiles/

            $xprofile_field_map = amt_buddypress_get_xprofile_field_map();
            // Get list of IDs of public fields
            $xprofile_public_fields = bp_xprofile_get_fields_by_visibility_levels( $user_id, array('public') );

            // Description
            $field_value = amt_bp_get_profile_field_data( 'description', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( amt_sanitize_description( $field_value ) );
            if ( ! empty($field_value) ) {
                $metadata_arr['twitter:description'] = '<meta name="twitter:description" content="' . esc_attr( $field_value ) . '" />';
            } else {
                $metadata_arr['twitter:description'] = '<meta name="twitter:description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $user_fullname ) . '" />';
            }

            // Profile Image
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            $avatar_args = array(
                'item_id'   => $user_id,
                'width'     => $avatar_size['width'],
                'height'    => $avatar_size['height'],
            );
            $avatar_img = bp_core_fetch_avatar( $avatar_args );
            if ( ! empty($avatar_img) ) {
                if ( preg_match("#src=['\"]([^'\"]+)['\"]#", $avatar_img, $matches) ) {
                    $avatar_url = $matches[1];
                }
            }
            if ( ! empty($avatar_url) ) {
                //$avatar_url = html_entity_decode($avatar_url, ENT_NOQUOTES, 'UTF-8');
                $metadata_arr[] = '<meta property="twitter:image" content="' . esc_url_raw( $avatar_url ) . '" />';
                if ( apply_filters( 'amt_extended_image_tags', true ) ) {
                    $metadata_arr[] = '<meta property="twitter:image:width" content="' . esc_attr( $avatar_size['width'] ) . '" />';
                    $metadata_arr[] = '<meta property="twitter:image:height" content="' . esc_attr( $avatar_size['height'] ) . '" />';
                    // Since we do not have a way to determine the image type, the following meta tag is commented out
                    // TODO: make a function that detects the image type from the file extension (if a file extension is available)
                    //$metadata_arr[] = '<meta property="twitter:image:type" content="image/jpeg" />';
                }
            }

        }

    }

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_twitter_cards_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_twitter_cards_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_buddypress_dublin_core( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_dublin_core_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_dublin_core_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_buddypress_schemaorg_footer( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // User Profiles

    // Determines if a BuddyPress user profile has been requested
    if ( bp_is_user_profile() ) {
        // https://codex.buddypress.org/developer/the-bp-global/
        global $bp;
        // $user_id = $bp->displayed_user->id;
        $user_id = bp_displayed_user_id();
        // $user_domain = $bp->displayed_user->domain;
        // bp_core_get_user_domain( bp_displayed_user_id() )
        $user_domain = bp_displayed_user_domain();
        $user_profile_url = trailingslashit( bp_displayed_user_domain() . amt_bp_get_profile_slug() );
        $user_fullname = $bp->displayed_user->fullname;
        // $user_fullname = bp_displayed_user_fullname();
        // $user_username = $bp->displayed_user->user_login;
        $user_username = bp_get_displayed_user_username();
        //$wp_user_obj = get_user_by( 'id', $user_id );
        $wp_user_obj = get_userdata( $user_id );
        //var_dump($wp_user_obj);

        // Author
        // Scope BEGIN: Person: http://schema.org/Person
        $metadata_arr[] = '<!-- Scope BEGIN: Person -->';
        $metadata_arr[] = '<span itemprop="author" itemscope itemtype="http://schema.org/Person"' . amt_get_schemaorg_itemref('person_author') . '>';

        // name
        $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $user_fullname ) . '" />';

        // URL
        $metadata_arr[] = '<meta itemprop="url" content="' . esc_url( $user_profile_url, array('http', 'https') ) . '" />';

        // mainEntityOfPage
        $metadata_arr[] = '<meta itemprop="mainEntityOfPage" content="' . esc_url( $user_profile_url, array('http', 'https') ) . '" />';

        // Related resources as sameAs
        // Facebook Profile
        //$fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $user_id);
        $fb_author_url = get_user_meta($user_id, 'amt_facebook_author_profile_url', true);
        if ( ! empty($fb_author_url) ) {
            $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url( $fb_author_url, array('http', 'https') ) . '" />';
        }
        // Twitter
        //$twitter_author_username = get_the_author_meta('amt_twitter_author_username', $user_id);
        $twitter_author_username = get_user_meta($user_id, 'amt_twitter_author_username', true);
        if ( ! empty($twitter_author_username) ) {
            $metadata_arr[] = '<meta itemprop="sameAs" content="https://twitter.com/' . esc_attr( $twitter_author_username ) . '" />';
        }
        // Google+
        //$googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $wp_user_obj);
        $googleplus_author_url = get_user_meta($user_id, 'amt_googleplus_author_profile_url', true);
        if ( ! empty( $googleplus_author_url ) ) {
            $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url( $googleplus_author_url, array('http', 'https') ) . '" />';
        }


        // Determines if Extended Profiles component is active.
        if ( ! bp_is_active( 'xprofile' ) ) {

            // Website
            //$website_url = get_user_meta($user_id, 'amt_googleplus_author_profile_url', true);
            $website_url = get_the_author_meta( 'user_url', $user_id );
            if ( ! empty( $website_url ) ) {
                $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url( $website_url, array('http', 'https') ) . '" />';
            }

            // Description
            $author_description = sanitize_text_field( amt_sanitize_description( $wp_user_obj->description ) );
            if ( empty($author_description) ) {
                $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $wp_user_obj->display_name ) . '" />';
            } else {
                $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $author_description ) . '" />';
            }

            // Profile Image
            $author_email = sanitize_email( $wp_user_obj->user_email );
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            // First try to get the avatar link by using get_avatar().
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_img = get_avatar( get_the_author_meta('ID', $wp_user_obj->ID), $avatar_size, '', get_the_author_meta('display_name', $wp_user_obj->ID) );
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
                $metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $avatar_url ) . '" />';
            }

            // familyName
            $last_name = $wp_user_obj->last_name;
            if ( ! empty($last_name) ) {
                $metadata_arr[] = '<meta itemprop="familyName" content="' . esc_attr( $last_name ) . '" />';
            }

            // givenName
            $first_name = $wp_user_obj->first_name;
            if ( ! empty($first_name) ) {
                $metadata_arr[] = '<meta itemprop="givenName" content="' . esc_attr( $first_name ) . '" />';
            }


        // Extended Profiles
        } else {
            // https://codex.buddypress.org/themes/guides/displaying-extended-profile-fields-on-member-profiles/

            $xprofile_field_map = amt_buddypress_get_xprofile_field_map();
            // Get list of IDs of public fields
            $xprofile_public_fields = bp_xprofile_get_fields_by_visibility_levels( $user_id, array('public') );

            // Website
            $field_value = amt_bp_get_profile_field_data( 'website', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="sameAs" content="' . esc_url( $field_value, array('http', 'https') ) . '" />';
            }

            // Description
            $field_value = amt_bp_get_profile_field_data( 'description', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( amt_sanitize_description( $field_value ) );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( $field_value ) . '" />';
            } else {
                $metadata_arr[] = '<meta itemprop="description" content="' . esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $user_fullname ) . '" />';
            }

            // Profile Image
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            $avatar_args = array(
                'item_id'   => $user_id,
                'width'     => $avatar_size['width'],
                'height'    => $avatar_size['height'],
            );
            $avatar_img = bp_core_fetch_avatar( $avatar_args );
            if ( ! empty($avatar_img) ) {
                if ( preg_match("#src=['\"]([^'\"]+)['\"]#", $avatar_img, $matches) ) {
                    $avatar_url = $matches[1];
                }
            }
            if ( ! empty($avatar_url) ) {
                //$avatar_url = html_entity_decode($avatar_url, ENT_NOQUOTES, 'UTF-8');
                $metadata_arr[] = '<meta itemprop="image" content="' . esc_url_raw( $avatar_url ) . '" />';
            }

            // familyName
            $has_last_name = false;
            $field_value = amt_bp_get_profile_field_data( 'last_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="familyName" content="' . esc_attr( $field_value ) . '" />';
                $has_last_name = true;
            }

            // givenName
            $has_first_name = false;
            $field_value = amt_bp_get_profile_field_data( 'first_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="givenName" content="' . esc_attr( $field_value ) . '" />';
                $has_first_name = true;
            }

            // Generate first and last name from full name if needed.
            if ( ! $has_last_name && ! $has_first_name && ! empty($user_fullname) ) {
                $parts = explode(' ', $user_fullname);
                $last_name = sanitize_text_field( array_pop($parts) ); // Removes and returns the element off the end of array
                if ( ! empty($last_name) ) {
                    $metadata_arr[] = '<meta itemprop="familyName" content="' . esc_attr( $last_name ) . '" />';
                }
                $first_name = sanitize_text_field( implode(' ', $parts) );
                if ( ! empty($first_name) ) {
                    $metadata_arr[] = '<meta itemprop="givenName" content="' . esc_attr( $first_name ) . '" />';
                }
            }

            // alternateName
            $field_value = amt_bp_get_profile_field_data( 'nickname', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="alternateName" content="' . esc_attr( $field_value ) . '" />';
            }

            // additionalName
            $field_value = amt_bp_get_profile_field_data( 'additional_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="additionalName" content="' . esc_attr( $field_value ) . '" />';
            }

            // honorificPrefix
            $field_value = amt_bp_get_profile_field_data( 'honorific_prefix', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="honorificPrefix" content="' . esc_attr( $field_value ) . '" />';
            }

            // honorificSuffix
            $field_value = amt_bp_get_profile_field_data( 'honorific_suffix', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="honorificSuffix" content="' . esc_attr( $field_value ) . '" />';
            }

            // gender
            $field_value = amt_bp_get_profile_field_data( 'gender', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="gender" content="' . esc_attr( $field_value ) . '" />';
            }

            // nationality
            $field_value = amt_bp_get_profile_field_data( 'nationality', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<!-- Scope BEGIN: Country -->';
                $metadata_arr[] = '<span itemprop="nationality" itemscope itemtype="http://schema.org/Country">';
                $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $field_value ) . '" />';
                $metadata_arr[] = '</span> <!-- Scope END: Country -->';
            }

            // telephone
            $field_value = amt_bp_get_profile_field_data( 'telephone', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="telephone" content="' . esc_attr( $field_value ) . '" />';
            }

            // faxNumber
            $field_value = amt_bp_get_profile_field_data( 'fax', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="faxNumber" content="' . esc_attr( $field_value ) . '" />';
            }

            // email
            $field_value = amt_bp_get_profile_field_data( 'email', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="email" content="' . esc_attr( $field_value ) . '" />';
            }

            // jobTitle
            $field_value = amt_bp_get_profile_field_data( 'job_title', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr[] = '<meta itemprop="jobTitle" content="' . esc_attr( $field_value ) . '" />';
            }

            // worksFor
            $work_name = '';
            $field_value = amt_bp_get_profile_field_data( 'works_for', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $work_name = esc_attr( $field_value );
            }

            // worksFor URL
            $work_url = '';
            $field_value = amt_bp_get_profile_field_data( 'works_for_url', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $work_url = esc_url( $field_value );
            }

            if ( ! empty($work_name) || ! empty($work_url) ) {
                $metadata_arr[] = '<!-- Scope BEGIN: Organization -->';
                $metadata_arr[] = '<span itemprop="worksFor" itemscope itemtype="http://schema.org/Organization">';
                if ( ! empty($work_name) ) {
                    $metadata_arr[] = '<meta itemprop="name" content="' . esc_attr( $work_name ) . '" />';
                }
                if ( ! empty($work_url) ) {
                    $metadata_arr[] = '<meta itemprop="url" content="' . esc_url( $work_url ) . '" />';
                }
                $metadata_arr[] = '</span> <!-- Scope END: Organization -->';
            }

            // Home Location Geo Coordinates

            // home latitude
            $latitude = '';
            $field_value = amt_bp_get_profile_field_data( 'home_latitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $latitude = esc_attr( $field_value );
            }

            // home longitude
            $longitude = '';
            $field_value = amt_bp_get_profile_field_data( 'home_longitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $longitude = esc_attr( $field_value );
            }

            if ( ! empty($latitude) && ! empty($longitude) ) {
                $metadata_arr[] = '<!-- Scope BEGIN: Place -->';
                $metadata_arr[] = '<span itemprop="homeLocation" itemscope itemtype="http://schema.org/Place">';
                $metadata_arr[] = '<meta itemprop="latitude" content="' . esc_attr( $latitude ) . '" />';
                $metadata_arr[] = '<meta itemprop="longitude" content="' . esc_attr( $longitude ) . '" />';
                $metadata_arr[] = '</span> <!-- Scope END: Place -->';
            }

            // Work Location Geo Coordinates

            // work latitude
            $latitude = '';
            $field_value = amt_bp_get_profile_field_data( 'work_latitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $latitude = esc_attr( $field_value );
            }

            // work longitude
            $longitude = '';
            $field_value = amt_bp_get_profile_field_data( 'work_longitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $longitude = esc_attr( $field_value );
            }

            if ( ! empty($latitude) && ! empty($longitude) ) {
                $metadata_arr[] = '<!-- Scope BEGIN: Place -->';
                $metadata_arr[] = '<span itemprop="workLocation" itemscope itemtype="http://schema.org/Place">';
                $metadata_arr[] = '<meta itemprop="latitude" content="' . esc_attr( $latitude ) . '" />';
                $metadata_arr[] = '<meta itemprop="longitude" content="' . esc_attr( $longitude ) . '" />';
                $metadata_arr[] = '</span> <!-- Scope END: Place -->';
            }

        }

        // Scope END: Person
        $metadata_arr[] = '</span> <!-- Scope END: Person -->';

    }

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_schemaorg_footer_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_schemaorg_footer_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_buddypress_schemaorg_content_filter( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Currently not called. Possibly not needed.

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_schemaorg_content_filter_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_schemaorg_content_filter_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_buddypress_jsonld_schemaorg( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // User Profiles

    // Determines if a BuddyPress user profile has been requested
    if ( bp_is_user_profile() ) {
        // https://codex.buddypress.org/developer/the-bp-global/
        global $bp;
        // $user_id = $bp->displayed_user->id;
        $user_id = bp_displayed_user_id();
        // $user_domain = $bp->displayed_user->domain;
        // bp_core_get_user_domain( bp_displayed_user_id() )
        $user_domain = bp_displayed_user_domain();
        $user_profile_url = trailingslashit( bp_displayed_user_domain() . amt_bp_get_profile_slug() );
        $user_fullname = $bp->displayed_user->fullname;
        // $user_fullname = bp_displayed_user_fullname();
        // $user_username = $bp->displayed_user->user_login;
        $user_username = bp_get_displayed_user_username();
        //$wp_user_obj = get_user_by( 'id', $user_id );
        $wp_user_obj = get_userdata( $user_id );
        //var_dump($wp_user_obj);

        // Context
        $metadata_arr['@context'] = 'http://schema.org';

        // Schema.org type
        $metadata_arr['@type'] = 'Person';

        // name
        $metadata_arr['name'] = esc_attr( $user_fullname );

        // URL
        $metadata_arr['url'] = esc_url( $user_profile_url, array('http', 'https') );

        // mainEntityOfPage
        $metadata_arr['mainEntityOfPage'] = esc_url( $user_profile_url, array('http', 'https') );

        // Related resources as sameAs
        $metadata_arr['sameAs'] = array();
        // Facebook Profile
        //$fb_author_url = get_the_author_meta('amt_facebook_author_profile_url', $user_id);
        $fb_author_url = get_user_meta($user_id, 'amt_facebook_author_profile_url', true);
        if ( ! empty($fb_author_url) ) {
            $metadata_arr['sameAs'][] = esc_url( $fb_author_url, array('http', 'https') );
        }
        // Twitter
        //$twitter_author_username = get_the_author_meta('amt_twitter_author_username', $user_id);
        $twitter_author_username = get_user_meta($user_id, 'amt_twitter_author_username', true);
        if ( ! empty($twitter_author_username) ) {
            $metadata_arr['sameAs'][] = 'https://twitter.com/' . esc_attr( $twitter_author_username );
        }
        // Google+
        //$googleplus_author_url = get_the_author_meta('amt_googleplus_author_profile_url', $wp_user_obj);
        $googleplus_author_url = get_user_meta($user_id, 'amt_googleplus_author_profile_url', true);
        if ( ! empty( $googleplus_author_url ) ) {
            $metadata_arr['sameAs'][] = esc_url( $googleplus_author_url, array('http', 'https') );
        }

        // Determines if Extended Profiles component is active.
        if ( ! bp_is_active( 'xprofile' ) ) {

            // Website
            //$website_url = get_user_meta($user_id, 'amt_googleplus_author_profile_url', true);
            $website_url = get_the_author_meta( 'user_url', $user_id );
            if ( ! empty( $website_url ) ) {
                $metadata_arr['sameAs'][] = esc_url( $website_url, array('http', 'https') );
            }

            // Description
            $author_description = sanitize_text_field( amt_sanitize_description( $wp_user_obj->description ) );
            if ( empty($author_description) ) {
                $metadata_arr['description'] = esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $wp_user_obj->display_name );
            } else {
                $metadata_arr['description'] = esc_attr( $author_description );
            }

            // Profile Image
            $author_email = sanitize_email( $wp_user_obj->user_email );
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            // First try to get the avatar link by using get_avatar().
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_img = get_avatar( get_the_author_meta('ID', $wp_user_obj->ID), $avatar_size, '', get_the_author_meta('display_name', $wp_user_obj->ID) );
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
                $metadata_arr['image'] = esc_url( $avatar_url );
            }

            // familyName
            $last_name = $wp_user_obj->last_name;
            if ( ! empty($last_name) ) {
                $metadata_arr['familyName'] = esc_attr( $last_name );
            }

            // givenName
            $first_name = $wp_user_obj->first_name;
            if ( ! empty($first_name) ) {
                $metadata_arr['givenName'] = esc_attr( $first_name );
            }


        // Extended Profiles
        } else {
            // https://codex.buddypress.org/themes/guides/displaying-extended-profile-fields-on-member-profiles/

            $xprofile_field_map = amt_buddypress_get_xprofile_field_map();
            // Get list of IDs of public fields
            $xprofile_public_fields = bp_xprofile_get_fields_by_visibility_levels( $user_id, array('public') );

            // Website
            $field_value = amt_bp_get_profile_field_data( 'website', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['sameAs'][] = esc_url( $field_value, array('http', 'https') );
            }

            // Description
            $field_value = amt_bp_get_profile_field_data( 'description', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( amt_sanitize_description( $field_value ) );
            if ( ! empty($field_value) ) {
                $metadata_arr['description'] = esc_attr( $field_value );
            } else {
                $metadata_arr['description'] = esc_attr( __('Profile of', 'add-meta-tags') . ' ' . $user_fullname );
            }

            // Profile Image
            // Important: for this to work the "Show Avatars" option should be enabled in Settings > Discussion.
            $avatar_size = apply_filters( 'amt_bp_avatar_size', array('width'=>50, 'height'=>50) );
            $avatar_url = '';
            $avatar_args = array(
                'item_id'   => $user_id,
                'width'     => $avatar_size['width'],
                'height'    => $avatar_size['height'],
            );
            $avatar_img = bp_core_fetch_avatar( $avatar_args );
            if ( ! empty($avatar_img) ) {
                if ( preg_match("#src=['\"]([^'\"]+)['\"]#", $avatar_img, $matches) ) {
                    $avatar_url = $matches[1];
                }
            }
            if ( ! empty($avatar_url) ) {
                //$avatar_url = html_entity_decode($avatar_url, ENT_NOQUOTES, 'UTF-8');
                $metadata_arr['image'] = esc_url( $avatar_url );
            }

            // familyName
            $has_last_name = false;
            $field_value = amt_bp_get_profile_field_data( 'last_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['familyName'] = esc_attr( $field_value );
                $has_last_name = true;
            }

            // givenName
            $has_first_name = false;
            $field_value = amt_bp_get_profile_field_data( 'first_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['givenName'] = esc_attr( $field_value );
                $has_first_name = true;
            }

            // Generate first and last name from full name if needed.
            if ( ! $has_last_name && ! $has_first_name && ! empty($user_fullname) ) {
                $parts = explode(' ', $user_fullname);
                $last_name = sanitize_text_field( array_pop($parts) ); // Removes and returns the element off the end of array
                if ( ! empty($last_name) ) {
                    $metadata_arr['familyName'] = esc_attr( $last_name );
                }
                $first_name = sanitize_text_field( implode(' ', $parts) );
                if ( ! empty($first_name) ) {
                    $metadata_arr['givenName'] = esc_attr( $first_name );
                }
            }

            // alternateName
            $field_value = amt_bp_get_profile_field_data( 'nickname', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['alternateName'] = esc_attr( $field_value );
            }

            // additionalName
            $field_value = amt_bp_get_profile_field_data( 'additional_name', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['additionalName'] = esc_attr( $field_value );
            }

            // honorificPrefix
            $field_value = amt_bp_get_profile_field_data( 'honorific_prefix', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['honorificPrefix'] = esc_attr( $field_value );
            }

            // honorificSuffix
            $field_value = amt_bp_get_profile_field_data( 'honorific_suffix', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['honorificSuffix'] = esc_attr( $field_value );
            }

            // gender
            $field_value = amt_bp_get_profile_field_data( 'gender', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['gender'] = esc_attr( $field_value );
            }

            // nationality
            $field_value = amt_bp_get_profile_field_data( 'nationality', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['nationality'] = array();
                $metadata_arr['nationality']['@type'] = 'Country';
                $metadata_arr['nationality']['name'] = esc_attr( $field_value );
            }

            // telephone
            $field_value = amt_bp_get_profile_field_data( 'telephone', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['telephone'] = esc_attr( $field_value );
            }

            // faxNumber
            $field_value = amt_bp_get_profile_field_data( 'fax', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['faxNumber'] = esc_attr( $field_value );
            }

            // email
            $field_value = amt_bp_get_profile_field_data( 'email', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['email'] = 'mailto:' . esc_attr( $field_value );
            }

            // jobTitle
            $field_value = amt_bp_get_profile_field_data( 'job_title', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['jobTitle'] = esc_attr( $field_value );
            }

            // worksFor
            $field_value = amt_bp_get_profile_field_data( 'works_for', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $metadata_arr['worksFor'] = array();
                $metadata_arr['worksFor']['@type'] = 'Organization';
                $metadata_arr['worksFor']['name'] = esc_attr( $field_value );
            }

            // worksFor URL
            $field_value = amt_bp_get_profile_field_data( 'works_for_url', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                if ( ! array_key_exists('worksFor', $metadata_arr) || ! is_array($metadata_arr['worksFor']) ) {
                    $metadata_arr['worksFor'] = array();
                    $metadata_arr['worksFor']['@type'] = 'Organization';
                }
                $metadata_arr['worksFor']['url'] = esc_attr( $field_value );
            }

            // Home Location Geo Coordinates

            // home latitude
            $latitude = '';
            $field_value = amt_bp_get_profile_field_data( 'home_latitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $latitude = esc_attr( $field_value );
            }

            // home longitude
            $longitude = '';
            $field_value = amt_bp_get_profile_field_data( 'home_longitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $longitude = esc_attr( $field_value );
            }

            if ( ! empty($latitude) && ! empty($longitude) ) {
                $metadata_arr['homeLocation'] = array();
                $metadata_arr['homeLocation']['@type'] = 'Place';
                $metadata_arr['homeLocation']['latitude'] = esc_attr( $latitude );
                $metadata_arr['homeLocation']['longitude'] = esc_attr( $longitude );
            }

            // Work Location Geo Coordinates

            // work latitude
            $latitude = '';
            $field_value = amt_bp_get_profile_field_data( 'work_latitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $latitude = esc_attr( $field_value );
            }

            // work longitude
            $longitude = '';
            $field_value = amt_bp_get_profile_field_data( 'work_longitude', $user_id, $xprofile_field_map, $xprofile_public_fields );
            $field_value = sanitize_text_field( $field_value );
            if ( ! empty($field_value) ) {
                $longitude = esc_attr( $field_value );
            }

            if ( ! empty($latitude) && ! empty($longitude) ) {
                $metadata_arr['workLocation'] = array();
                $metadata_arr['workLocation']['@type'] = 'Place';
                $metadata_arr['workLocation']['latitude'] = esc_attr( $latitude );
                $metadata_arr['workLocation']['longitude'] = esc_attr( $longitude );
            }

        }

    }

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_buddypress_jsonld_schemaorg_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_buddypress_jsonld_schemaorg_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}






//
//
//  bbPress Support
//
//


// bbPress detection
function amt_detect_bbpress( $default, $post, $options ) {
    // First and important check.
    // $default is a boolean variable which indicates if custom content has been
    // detected by any previous filter.
    // Check if custom content has already been detected by another filter.
    // If such content has been detected, just return $default (should be true)
    // and *do not* add any metadata filters.
    // This check is mandatory in order the detection mechanism to work correctly.
    if ( $default ) {
        return $default;
    }
    // Process bbPress metadata, only if the bbPress extended metadata
    // support has been enabled in the Add-Meta-Tags settings.
    if ( $options["extended_support_bbpress"] == "1" ) {
        // Perform this test in case bbPress is not installed/activated.
        if ( ! function_exists('is_bbpress') || ! is_bbpress() ) {
            return false;
        }
        // Insert metadata for bbPress pages
        // Basic (description/keywords)
        add_filter( 'amt_custom_metadata_basic', 'amt_bbpress_basic', 10, 5 );
        // Opengraph
        add_filter( 'amt_custom_metadata_opengraph', 'amt_bbpress_opengraph', 10, 5 );
        // Twitter Cards
        add_filter( 'amt_custom_metadata_twitter_cards', 'amt_bbpress_twitter_cards', 10, 5 );
        // Dublin Core
        add_filter( 'amt_custom_metadata_dublin_core', 'amt_bbpress_dublin_core', 10, 5 );
        // Schema.org
        if ( $options["schemaorg_force_jsonld"] == "0" ) {
            // Microdata
            // Non content pages via 'wp_footer' action
            add_filter( 'amt_custom_metadata_schemaorg_footer', 'amt_bbpress_schemaorg_footer', 10, 5 );
            // Content pages via 'the_content' filter
            add_filter( 'amt_custom_metadata_schemaorg_content_filter', 'amt_bbpress_schemaorg_content_filter', 10, 5 );
        } else {
            add_filter( 'amt_custom_metadata_jsonld_schemaorg', 'amt_bbpress_jsonld_schemaorg', 10, 5 );
        }
        // Finally return true. bbPress detected.
        return true;
    }
    return false;
}
add_filter( 'amt_is_custom', 'amt_detect_bbpress', 10, 3 );


function amt_bbpress_basic( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_basic_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_basic_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_bbpress_opengraph( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_opengraph_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_opengraph_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_bbpress_twitter_cards( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_twitter_cards_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_twitter_cards_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_bbpress_dublin_core( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_dublin_core_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_dublin_core_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_bbpress_schemaorg_footer( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_schemaorg_footer_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_schemaorg_footer_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_bbpress_schemaorg_content_filter( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_schemaorg_content_filter_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_schemaorg_content_filter_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}


function amt_bbpress_jsonld_schemaorg( $metadata_arr, $post, $options, $attachments, $embedded_media ) {

    // Allow filtering of the generated metadata
    // Customize with: add_filter('amt_bbpress_jsonld_schemaorg_extra', 'my_function', 10, 5);
    $metadata_arr = apply_filters( 'amt_bbpress_jsonld_schemaorg_extra', $metadata_arr, $post, $options, $attachments, $embedded_media );
    return $metadata_arr;
}

