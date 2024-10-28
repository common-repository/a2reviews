<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.gobliz.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    A2reviews
 * @subpackage A2reviews/public
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews_Order {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct(  ) {
		add_action( 'woocommerce_thankyou', [$this, 'checkout_completed'] );
	}
	
	
	/**
	 * Do assign customer with order
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function checkout_completed( $order_id ){
		$order = wc_get_order( $order_id );
	    $user = $order->get_user();
	    
	    $order_address = $order->get_address();
	    $email = $order_address['email'];
	    
	    if(!$user && is_email( $email )){
		    if( !email_exists( $email )){
			    $user_pass = wp_generate_password( 8, false );
			    
			    $userdata = array(
				    'first_name'	   	=>  isset($order_address['first_name'])? $order_address['first_name']: '',
				    'last_name'	   		=>  isset($order_address['last_name'])? $order_address['last_name']: '',
	            	'user_login'       	=>  $email,
					'user_pass'        	=>  $user_pass,
					'user_email'       	=>  $email,
					'user_registered'  	=>  date_i18n( 'Y-m-d H:i:s', time() ),
					'role'             	=>  'customer'
				);
	               
				$user_id = wp_insert_user( $userdata );
				update_post_meta($order->get_id(), '_customer_user', $user_id);
		    }else{
			    if( !$order->get_customer_id() ){
				    $user = get_user_by( 'email', $email );
					update_post_meta($order->get_id(), '_customer_user', $user->ID);
			    }
		    }
	    }
	    
	}
	
}

new A2reviews_Order();