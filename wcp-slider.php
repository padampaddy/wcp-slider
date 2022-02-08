<?php

/**
 * @package WCP Slider
 * @version 1.0.0
 */
/*
Plugin Name: WCP Slider
Description: This is a wordpress products slider
Author: Anshu
Version: 1.0.0
*/

register_activation_hook(__FILE__, function () {
});

register_deactivation_hook(__FILE__, function () {
});

function get_wcp_categories($product_id)
{
    $ret = [];
    foreach ((get_the_terms($product_id, 'product_cat')) as $category) {
        array_push($ret, $category->name);
    }
    return implode(", ", $ret);
}
function elementor_preview_enqueue_styles()
{
    wp_register_style('wcp-slider-slick-css', plugin_dir_url(__FILE__) . '/slider/slick.css');
    wp_register_style('wcp-slider-slick-theme-css', plugin_dir_url(__FILE__) . '/slider/slick-theme.css');
    wp_register_style('wcp-slider-css', plugin_dir_url(__FILE__) . '/style.css');
}
function elementor_preview_enqueue_scripts()
{
    wp_register_script('wcp-slider-slick-js', plugin_dir_url(__FILE__) . '/slider/slick.min.js', ['jquery']);
    wp_register_script('wcp-slider-js', plugin_dir_url(__FILE__) . '/script.js', ['wcp-slider-slick-js']);
}
function wcp_slider_add_scripts()
{
    wp_register_script('wcp-slider-slick-js', plugin_dir_url(__FILE__) . '/slider/slick.min.js', ['jquery']);
    wp_register_style('wcp-slider-slick-css', plugin_dir_url(__FILE__) . '/slider/slick.css');
    wp_register_style('wcp-slider-slick-theme-css', plugin_dir_url(__FILE__) . '/slider/slick-theme.css');
    wp_register_script('wcp-slider-js', plugin_dir_url(__FILE__) . '/script.js', ['wcp-slider-slick-js']);
    wp_register_style('wcp-slider-css', plugin_dir_url(__FILE__) . '/style.css');
}
add_action('wp_enqueue_scripts', 'wcp_slider_add_scripts');
add_action('elementor/preview/enqueue_styles', 'elementor_preview_enqueue_styles');
add_action('elementor/preview/enqueue_scripts', 'elementor_preview_enqueue_scripts');
//  limit, columns,orderby,array of ids,sku,image size,featured,item status
function wcp_slider_add_shortcode($atts)
{
    $atts = shortcode_atts([
        'products_to_show' => 3,
        'limit' => 5,
        'categories' => '*',
        'columns' => '*',
        'orderby' => 'ID',
        'product_ids' => '*',
        'skus' => '*',
        'rows' => 1,
        'status' => 'instock',
        'height' => '400px'
    ], $atts, 'wcp-product-slider');
    $params = [
        'posts_per_page' => $atts['limit'],
        'post_type' => 'product',
        'orderby' => $atts['orderby'],
    ];
    $meta_query = [];
    $tax_query = [];
    if ($atts['categories'] !== '*')
        array_push($tax_query, [
            'taxonomy'      => 'product_cat',
            'terms'         =>  explode(',', $atts['categories']),
            'field'         => 'slug',
            'operator'      => 'IN'
        ]);
    if ($atts['skus'] !== '*')
        array_push($meta_query, [
            'key'      => '_sku',
            'value'         =>  $atts['skus'],
            'compare'      => 'IN'
        ]);
    array_push($meta_query, [
        'key'      => '_stock_status',
        'value'         =>  $atts['status'],
    ]);
    if ($atts["product_ids"] !== '*')
        $params['post__in'] = explode(',', $atts['product_ids']);
    $params["meta_query"] = $meta_query;
    $params['tax_query'] = $tax_query;
    $loop = new WP_Query($params);
    $slickParams = [
        "dots" => False,
        "slidesToShow" => $atts['products_to_show'],
        "autoplay" => False,
        "rows" => $atts['rows']
    ];
    $html = '<div class="wcp-slider" data-slick=\'' . json_encode($slickParams) . '\'>';
    while ($loop->have_posts()) {
        $loop->the_post();
        global $product;
        $html .= '<div class="wcp-slider-product wcp-slider-product-' . get_the_ID() . '"><a href="' . get_permalink() . '"><div class="wcp-image"  style="height: ' . $atts['height'] . '">' . woocommerce_get_product_thumbnail() . '</div><div class="title">' . get_the_title() . '</div><div class="description">' .  wp_trim_words(get_the_excerpt(), 3) . '</div><div class="price">' . $product->get_price() . get_woocommerce_currency_symbol("EUR") . '</div></a></div>';
    }
    // <div class="category">' .  get_wcp_categories(get_the_ID()) . '</div>
    $html .= '</div>';
    $html .= "<script>;jQuery(document).ready(function(){jQuery('.wcp-slider').slick();});</script>";
    wp_reset_query();
    wp_enqueue_script('jquery');
    wp_enqueue_style('wcp-slider-slick-css');
    wp_enqueue_style('wcp-slider-slick-theme-css');
    wp_enqueue_style('wcp-slider-css');
    wp_enqueue_style('dashicons');
    wp_enqueue_script('wcp-slider-slick-js');
    //     wp_enqueue_script('wcp-slider-js');
    return $html;
};
add_shortcode("wcp_product_slider", 'wcp_slider_add_shortcode');
