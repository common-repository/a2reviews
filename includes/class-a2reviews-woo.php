<?php

/**
 * A2Reviews woocommer class
 *
 * @link       https://www.eosvn.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/includes
 */

/**
 * A2Reviews woo class
 *
 * API for interaction between client site and a2 reviews app
 *
 * @since      1.0.0
 * @package    A2reviews
 * @subpackage A2reviews/includes
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews_WC {
	
	/**
	 * Define the core option to create res api key.
	 *
	 * @since    1.0.0
	 */
	private $res_api_option = [
		'description' 	=> '',
		'user' 			=> null,
		'permissions' 	=> 'read_write'
	];
	
	// static $_instance
	public static $_instance;
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$today = date("F j, Y, g:i a"); 
		$createdOn = "(created on $today)";
		$this->res_api_option['description'] = "A2Reviews - API Read/Write $createdOn";
		$this->res_api_option['user'] = $this->findAdmin();
	}
	
	
    /**
     * Check instance and reset it
     *
     * @return $_instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
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
	 * Dev Mode
	 *
	 */
    public function dev(){
		/*
		    if(isset($_GET['dev']) && $_GET['dev'] == '6688'){
			    print_r($this->createResAPIKey());
			    exit;
		    }
		*/
    }
    
    /**
	 * Create RES API KEY
	 * @return response array
	 */
    public function createResAPIKey($code){
	    global $wpdb;
	    
	    if($code != get_option( 'a2reviews_auth_code' ))
	    	return ['error' => 'Invalid Code'];
	    
	    if(!$this->isWooCommerce())
	    	return ['error' => 'WC Not Active'];
	    	
	    $response = array();
	    	
	    try{
		    $user_id		 = $this->res_api_option['user'];
		    $description	 = $this->res_api_option['description'];
		    $permissions	 = $this->res_api_option['permissions'];
		    $consumer_key    = 'ck_' . wc_rand_hash();
			$consumer_secret = 'cs_' . wc_rand_hash();

			$data = array(
				'user_id'         => $user_id,
				'description'     => $description,
				'permissions'     => $permissions,
				'consumer_key'    => wc_api_hash( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 ),
			);

			$wpdb->insert(
				$wpdb->prefix . 'woocommerce_api_keys',
				$data,
				array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);

			if ( 0 === $wpdb->insert_id ) {
				throw new Exception( __( 'There was an error generating your API Key.', 'woocommerce' ) );
			}

			$key_id                      = $wpdb->insert_id;
			$response                    = $data;
			$response['consumer_key']    = $consumer_key;
			$response['consumer_secret'] = $consumer_secret;
	    }catch(Exception $e){
		    return array( 'error' => $e->getMessage() );
	    }
	    
	    return $response;
    }
    
    /**
     * Find admin user id
     *
     * @return id 
     */
    private function findAdmin(){
	    $users = get_users( array( 'role__in' => array( 'administrator' ) ) );
	    if($users){
		    return $users[0]->ID;
	    }
	    return 0;
    }
	
}

if(!function_exists('A2WC')){
	function A2WC(){
		return A2reviews_WC::instance();
	}
}
