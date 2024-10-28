<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.gobliz.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/shortcode
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    A2reviews
 * @subpackage A2reviews/shortcode
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews_Shortcode {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct(  ) {
		add_shortcode( 'a2reviews-block', [&$this, 'block'] );
		add_shortcode( 'a2reviews-widget', [&$this, 'widget'] );
		add_shortcode( 'a2reviews-widget-feature', [&$this, 'feature_reviews'] );
	}
	
	/**
     * block method
     *
     * @param Request object $request Data.
     * @return html
     */
	public function block( $atts ) {
	    $atts = shortcode_atts( array(
	        'id' => 0,
	        'name' => 'Home Block',
	        'show_loading' => true
	    ), $atts, 'a2reviews-block' );
	    
	    $loading_place = '';
	    $show_loading = '';
	    
	    if($atts['show_loading']){
		    $loading_place = '<div style="text-align: center;">Loading...</div>';
		    $show_loading = 'show-loading';
	    }
	 
	    return '<a2reviews-block '.$show_loading.' block-id="'. esc_attr($atts['id']) .'" name="'. esc_attr($atts['name']) .'">'.$loading_place.'</a2reviews-block>';
	}
	
	
	/**
     * Widget reviews
     *
     * @param Request object $request Data.
     * @return html
     */
	public function widget($atts){
		$atts = shortcode_atts( array(
	        'product-id' => 0,
	        'type' => 'main'
	    ), $atts, 'a2reviews-widget' );
	    
	    global $product;
	    
	    $product_id = isset($atts['product-id'])? $atts['product-id']: 0;

	    if(!$product_id && is_product()){
			$product_id = $product->get_id();
	    }
		
		if(!$product_id)
			return 'Invalid product id!';
			
		$product = wc_get_product( $product_id );
	    
	    ob_start();
	    
		$product_handle = $product->get_slug();
		$product_title 	= $product->get_name();
		$total_rating 	= get_post_meta( $product_id, 'a2_meta_total_rating', true );
	    $avg_rating 	= get_post_meta( $product_id, 'a2_meta_avg_rating', true );
	    
	    if(isset($atts['type']) && $atts['type'] == 'main'){
		    echo '<div class="a2reviews-wrapper">
			    <a2-reviews 
			        handle="'. esc_attr( $product_handle ) .'" 
			        lang="en">
			        <div class="a2reviews-rating" itemscope itemprop="aggregateRating" itemtype="schema/AggregateRating">
			            <meta itemprop="itemreviewed" content="'. esc_attr( $product_title ) .'">
			            Rated <span itemprop="ratingValue">'. $avg_rating .'</span>/5
			            based on <span itemprop="reviewCount">'. $total_rating .'</span> customer reviews
			        </div>
			    </a2-reviews>
			</div>';
	    }else if(isset($atts['type']) && $atts['type'] == 'total'){
		    echo '<a2-reviews-total 
			    handle="'. esc_attr( $product_handle ) .'" 
			    total="'. esc_attr( $total_rating ) .'" 
			    avg="'. esc_attr( $avg_rating ) .'" 
			    lang="en"
			    is-scroll="false">
			</a2-reviews-total>';
	    }else{
		    echo '<a2-reviews-total
			    collection
			    handle="'. esc_attr( $product_handle ) .'" 
			    total="'. esc_attr( $total_rating ) .'" 
			    avg="'. esc_attr( $avg_rating ) .'" 
			    lang="en"
			    is-scroll="false">
			</a2-reviews-total>';
	    }
	    
	    $content = ob_get_clean();
	    
	    return $content;
	}
	
	
	/**
     * Feature reviews
     *
     * @param Request object $request Data.
     * @return html
     */
	public function feature_reviews( $atts ){
		$atts = shortcode_atts( array(
	        'id' => 0,
	        'title' => 'Feature Reviews',
	        'show_loading' => true
	    ), $atts, 'a2reviews-widget-feature' );
	    
	    $loading_place = '';
	    $show_loading = 'false';
	    
	    if($atts['show_loading']){
		    $loading_place = '<div style="text-align: center;">Loading...</div>';
		    $show_loading = 'true';
	    }
	    
	    return '<a2-feature-reviews show-loading='. esc_attr($show_loading) .'lang="en">'. $loading_place .'</a2-feature-reviews>';
	}
	
}

new A2reviews_Shortcode();