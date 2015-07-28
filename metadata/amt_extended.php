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
        $metatags['tc:twitter:label1'] = '<meta name="twitter:label1" content="Price" />';
        $metatags['tc:twitter:data1'] = '<meta name="twitter:data1" content="' . esc_attr($active_price) . '" />';
        // Currency
        $metatags['tc:twitter:label2'] = '<meta name="twitter:label2" content="Currency" />';
        $metatags['tc:twitter:data2'] = '<meta name="twitter:data2" content="' . esc_attr(get_woocommerce_currency()) . '" />';
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

