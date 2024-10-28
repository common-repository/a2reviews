<?php
/**
 * Loop Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! wc_review_ratings_enabled() ) {
	return;
}

$options = get_option( 'a2reviews_options' );

$replace_default =  isset($options->replace_cwt_default) && $options->replace_cwt_default == 'true'? true: false;

if($replace_default){
   $product_handle = isset($product->slug)? $product->slug: $product->id;
   $total_rating 	= get_post_meta( $product->id, 'a2_meta_total_rating', true );
   $avg_rating 	= get_post_meta( $product->id, 'a2_meta_avg_rating', true );
   
   echo '<a2-reviews-total
       collection
       handle="'. $product_handle .'" 
       total="'. $total_rating .'" 
       avg="'. $avg_rating .'" 
       lang="en"
       is-scroll="false">
   </a2-reviews-total>'; 
}else{
    echo wc_get_rating_html( $product->get_average_rating() ); 
}