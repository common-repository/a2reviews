<?php

/**
 * A2Reviews api class
 *
 * @link       https://www.gobliz.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/includes
 */

/**
 * A2 Reviews api class
 *
 * API for interaction between client site and a2 reviews app
 *
 * @since      1.0.0
 * @package    A2reviews
 * @subpackage A2reviews/includes
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews_API {
	
	/**
	 * [$hmac_verify description]
	 * @var [type]
	 */
	private $hmac_verify;

	/**
	 * [$hmac description]
	 * @var [type]
	 */
	private $hmac;

	/**
	 * The endpoint of this api.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $endpoint
	 */
	private $endpoint;
	
	/**
	 * The data of this $_POST.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      (array | object)    $post_data.
	 */
	private $post_data;
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($endpoint, $post_data) {
		$this->endpoint = $endpoint;
		$this->post_data = $post_data;
	}
	
	/**
	 * Check is woocommerce plugin is active
	 *
	 * @since    1.0.0
	 */
    public function isWooCommerce(){
	    if(in_array( 
		    'woocommerce/woocommerce.php', 
		    apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) 
		)){
			return true;
		}else{
			return false;
		}
    }
	
	/**
	 * Check is authentication
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function isAuth(){
		$authenticated = false;
		
		$site_url 	= get_site_url();
		$app_url 	= A2REVIEWS_APP_URL;
	    $domain 	= str_replace(['http://', 'https://', 'http://www.', 'https://www.'], '', $site_url);;
	    $ssl 		= strpos($site_url, 'https://') !== false? 'true': 'false';
	    $timestamp 	= time();

	    $data 		= [
		    'domain' => $domain,
		    'site_url' => $site_url,
		    'ssl' => $ssl,
		    'timestamp' => $timestamp
	    ];
	    	    
	    $hmac 		= hash_hmac( 'sha256', base64_encode(http_build_query($data)), md5($domain));
	    
	    $data_send = array_merge($data, ['hmac' => $hmac]);
	    
	    $url = "{$app_url}/woo-check-auth";
	    $response = wp_remote_post( $url, array(
		    'body'    => $data_send,
		    'headers' => array(
		        'Authorization' => 'A2Reviews',
		    ),
		));
		
		if(!is_wp_error( $response ) && isset($response['body'])){
			$data = json_decode($response['body']);
			if($data && $data->authenticated){
				$authenticated = true;
			}
		}
		
		return $authenticated;
	}
	
	/**
	 * Verify data send from a2reviews app
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function verify_data(){
		if(!isset($_SERVER['HTTP_X_A2REVIEWS_HMAC']))
			return false;
			
		$parse = parse_url(get_option( 'siteurl' ));
		$domain = $parse['host'];
		$hmac_verify = $_SERVER['HTTP_X_A2REVIEWS_HMAC'];
		$secret_code = ($this->endpoint == 'request-access')? md5($domain): md5(get_option( 'a2reviews_access_token' ));
		$hmac = hash_hmac('sha256', base64_encode(http_build_query($this->post_data)), $secret_code);
		$this->hmac_verify = $hmac_verify;
		$this->hmac = $hmac;
		
		return $hmac_verify === $hmac? true: false;
	}
	
	/**
	 * Build method
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function method_name(){
		return str_replace('-', '_', $this->endpoint);
	}

	/**
	 * Hand action API
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function handle() {
		
		//Check valid data
		if(!$this->verify_data()){
			return ['errors' => 'Invalid data', 'post_data' => $this->post_data, 'hmac_verify' => $this->hmac_verify , 'hmac' => $this->hmac ];
		}
		
		//Handle endpoint request		
		$method = $this->method_name();
		if(method_exists($this, $method)){
			return $this->{$method}();
		}
		
		//Not found endpoint
		return [
			'errors' => 'Not found',
			'endpoint' => $this->endpoint
		];
	}

	/**
	 * Check and clear wordpress cache
	 *
	 * @return void()
	 *
	 * @since    1.0.0
	 */
	public function clearCache($post_id = 0){
		global $wpdb;
		if(function_exists('wp_cache_clear_cache')){
			wp_cache_clear_cache( $wpdb->blogid );
		}

		if(function_exists('rocket_clean_post') && $post_id){
			rocket_clean_post( $post_id );
		}
	}
	
	
	/**
	 * Request access from a2reviews app
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function request_access(){
		$verified = false;
		$code = isset($this->post_data['code'])? $this->post_data['code']: '';
		$access_token = get_option( 'a2reviews_access_token' );
		$data_info = new \stdClass;
		
		if( $code == get_option( 'a2reviews_auth_code' )){
			$verified = true;
			$option = get_option( 'a2reviews_options' );
			
			if(is_object($option)){
				$option->authentication = true;
				update_option( 'a2reviews_options', $option );
			}
			
			$admin_email = get_option( 'admin_email' );
			
			$user = get_user_by( 'email', $admin_email );
			$user_info = $user->data;
			$user_info->first_name = get_user_meta( $user_info->ID, 'first_name', true );
			$user_info->last_name = get_user_meta( $user_info->ID, 'last_name', true );
			
			unset($user_info->user_pass);
			
			$data_info->admin_email 	= $admin_email;
			$data_info->blogname 		= get_option( 'blogname' );
			$data_info->siteurl 		= get_option( 'siteurl' );
			$data_info->user 			= $user_info;
			$data_info->apiKey 			= A2WC()->createResAPIKey($code);
			
			update_option( 'a2reviews_auth_code', '' );
		}
		
		return [
			'status' => 'success',
			'verified' => $verified,
			'access_token' => $access_token,
			'info' => $data_info
		];
	}
	
	/**
	 * Update product meta api
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function update_product_meta(){
		global $post;
		$product_data = [];
		
		$product_id = isset($this->post_data['product_id'])? intval($this->post_data['product_id']): 0;
		
		$count = new stdClass();
		$count->count_r5 	=  0;
		$count->count_r4 	=  0;
		$count->count_r3 	=  0;
		$count->count_r2 	=  0;
		$count->count_r1 	=  0;
		$count->total 		=  0;
		$count->avg_rating 	=  0;
		$count->count_deactivate =  0;
		$count->count_feature =  0;
		$count->count_empty =  0;
		$count->count_photos =  0;
		
		$total_rating 	= isset($this->post_data['total_rating'])? intval($this->post_data['total_rating']): 0;
		$total_questions = isset($this->post_data['total_questions'])? intval($this->post_data['total_questions']): 0;
		$avg_rating 	= isset($this->post_data['avg_rating'])? floatval($this->post_data['avg_rating']): 0;
		$count_data 	= isset($this->post_data['count'])? (OBJECT) $this->post_data['count']: $count;
		
		$status 		= intval($this->post_data['status']);
		
		if($product_id){
			update_post_meta( $product_id, 'a2_meta_status', $status );
			update_post_meta( $product_id, 'a2_meta_total_rating', $total_rating );
			update_post_meta( $product_id, 'a2_meta_total_questions', $total_questions );
			update_post_meta( $product_id, 'a2_meta_avg_rating', $avg_rating );
			update_post_meta( $product_id, 'a2_meta_count', $count_data );
		}
		
		if($product_id && function_exists('wc_get_product')){
			$product = wc_get_product( $product_id );
			
			$product_data = [
				'id' 			=> $product->get_id(),
				'name' 			=> $product->get_name(),
				'slug' 			=> $product->get_slug(),
				'image'			=> wp_get_attachment_image_url( $product->get_image_id(), 'full' ),
				'date_created' 	=> $product->get_date_created(),
			];
		}

		$this->clearCache($product_id);
		
		return [
			'product_id' => $product_id,
			'product' => $product_data,
			'meta' => get_post_meta($product_id)
		];
	}
	
	/**
	 * Update product meta api
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function update_settings(){
		global $post;
		
		$settings = $this->post_data['settings'];
		update_option( 'a2reviews_settings', $settings );
		$this->clearCache();
		
		return [
			'status' => 'success'
		];
	}
	
	
	/**
	 * Get webhook info
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function get_webhook_info(){
		
		if(!$this->isWooCommerce()){
			return ['webhooks' => []];
		}
		
		$data_store = WC_Data_Store::load( 'webhook' );
		$webhooks   = $data_store->search_webhooks();
		
		$webhook_data = [];
		foreach($webhooks as $id){
			$webhook = new WC_Webhook($id);
			
			if(strpos($webhook->get_delivery_url(), 'webhook.a2rev.com') !== false){
				$webhook_data[] = [
					'name' 			=> $webhook->get_name(),
					'delivery_url' 	=> $webhook->get_delivery_url(),
					'topic' 		=> $webhook->get_topic(),
					'secret' 		=> $webhook->get_secret(),
					'status' 		=> $webhook->get_status()
				];
			}
		}
		
		$topic = isset($this->post_data['topic'])? $this->post_data['topic']: '';
		
		return [
			'webhooks' => $webhook_data
		];
	}
	
	/**
	 * Update product meta api
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function update_client_settings(){
		$settings = isset($this->post_data['settings'])? intval($this->post_data['settings']): [];
		$update = 0;
		
		if(!$settings && (is_object($settings) || is_array($settings))){
			$update = update_option( 'a2reviews_client_settings', $settings );
			$this->clearCache();
		}
		
		return [
			'update' => $update
		];
	}
	
	/**
	 * Update client version
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	private function update_client_version(){
		$update = 0;
		$version = isset($this->post_data['version'])? $this->post_data['version']: '1.0.7';
		$current_version = get_option('a2reviews_client_version');
		
		if($current_version != $version){
			$update = update_option( 'a2reviews_client_version', $version );
			$this->clearCache();
		}
		
		return [
			'update' => $update
		];
	}

}
