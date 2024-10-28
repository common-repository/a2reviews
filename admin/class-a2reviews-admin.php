<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.gobliz.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    A2reviews
 * @subpackage A2reviews/admin
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	
	/**
	 * The options object
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $options;
	
	
	private $site_url = '';
	private $domain = '';
	
	protected $ajaxData;
	
	private $isAuth;
	
	
	/**
	 * Ajax events
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $ajax_events = [
		'do_authentication' => false,
		'do_open_app' 		=> false,
		'save_settings' 	=> false,
	];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option( 'a2reviews_options' );
		
		foreach ( $this->ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_a2reviews_' . $ajax_event, array( $this, esc_attr( $ajax_event ) ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_a2reviews_nopriv_' . $ajax_event, array( $this, esc_attr( $ajax_event ) ) );
			}
		}
		
		$this->site_url = get_site_url();
		$domain = str_replace(['http://', 'https://', 'http://www.', 'https://www.'], '', $this->site_url);
		
		$this->domain = $domain;
		$this->isAuth = A2reviews_API::isAuth();
		
		//Add a2reviews menu to wordpress admin
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', function(){
			if(
				isset($_GET['page']) 
				&& isset($_GET['open-a2app']) 
				&& $_GET['page'] == 'a2reviews-setting-admin'
				&& $_GET['open-a2app'] == true)
			{
				$this->loginToA2App();
			}
		} , 10 );
		
		$this->product_column();
	}
	
	/**
     * Add options page
     */
    public function add_plugin_page(){
        // This page will be under "Settings"
        add_options_page(
            'A2reviews', 
            'A2reviews', 
            'manage_options', 
            'a2reviews-setting-admin', 
            array( $this, 'create_admin_page' )
        );
        
        add_action( 'admin_bar_menu', function(WP_Admin_Bar $admin_bar) {
	        if ( ! current_user_can( 'manage_options' ) ) {
		        return;
		    }
		    
		    $admin_bar->add_menu( array(
		        'id'    => 'a2reviews-menu',
		        'parent' => null,
		        'group'  => null,
		        'title' => '✪ A2reviews', //you can use img tag with image link. it will show the image icon Instead of the title.
		        'href'  => admin_url('options-general.php?page=a2reviews-setting-admin'),
		        'meta' => [
		            'title' => __( '✪ A2Reviews', 'a2reviews' ), //This title will show on hover
		        ]
		    ) );
		    
		    if($this->isAuth){
			    $admin_bar->add_menu( array(
			        'id'    => 'a2reviews-app',
			        'parent' => 'a2reviews-menu',
			        'group'  => null,
			        'title' => '✪ A2Reviews App', //you can use img tag with image link. it will show the image icon Instead of the title.
			        'href'  => 'options-general.php?page=a2reviews-setting-admin&open-a2app=true',
			        'meta' => [
			            'title' => __( '✪ A2Reviews App', 'a2reviews' ), //This title will show on hover
			        ]
			    ) );
		    }
        }, 500 );
    }
    
    /**
	 * Product column
	 *
	 * @since    1.0.0
	 */
    public function product_column(){
	    add_filter( 'manage_product_posts_columns', function($columns){
			$columns['a2reviews'] = __( '✪ A2 Rating', 'a2reviews' );
			
			return $columns;
		});
		
		add_action( 'manage_product_posts_custom_column' , function($column, $post_id){
			switch ( $column ) {
				case 'a2reviews' :
					$avg_rating = get_post_meta($post_id, 'a2_meta_avg_rating', true);
					$avg_rating = ($avg_rating)? $avg_rating: 0;
					
					$total = get_post_meta( $post_id, 'a2_meta_total_rating', true );
					$total = ($total)? $total: 0;
					
	            	echo '<el-rate
					  value="'.$avg_rating.'"
					  disabled
					  show-score
					  text-color="#ff9900"
					  score-template="'.$avg_rating.'">
					</el-rate>';
					
					echo '<div><strong>'.$total.'</strong> reviews</div>';
	            break;
			}
		}, 10, 2 );
    }
    
    
    /**
	 * is woocommerce active
	 *
	 * @since    1.0.0
	 */
    public function is_woocommerce_active(){
	    if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
			return true;
		}
		
		return false;
    }
    
    
    /**
	 * Check security
	 *
	 * @since    1.0.0
	 */
    public function check_security(){
	    $postdata = file_get_contents("php://input");
		$custom_request = json_decode($postdata);
		
		$this->ajaxData = $custom_request;
		
		if ( ! isset( $this->ajaxData->security ) 
		    || ! wp_verify_nonce( $this->ajaxData->security, 'a2_settings_auth' ) 
		) {
			return false;
		}else{
			return true;
		}
    }
    
    /**
	 * Do authentication
	 *
	 * @since    1.0.0
	 */
    public function do_authentication(){
	    $url = $message = '';
	    
	    if($this->check_security()){
		    flush_rewrite_rules(true);
		    
		    $access_token = md5(wp_generate_password( 26, false ));
			$access_token = sanitize_text_field($access_token);
			
			update_option( 'a2reviews_access_token', $access_token );
		    
		    $app_url = A2REVIEWS_APP_URL;
		    $domain = $this->domain;
		    $code = md5(wp_generate_password( 20, false ));
		    $timestamp = time();
		    $ssl = strpos($this->site_url, 'https://') !== false? 'true': 'false';
		    $return_url = get_admin_url(). 'options-general.php?page=a2reviews-setting-admin';
		    
		    $code = sanitize_text_field($code);
		    update_option( 'a2reviews_auth_code', $code );
		    
		    $url = "$app_url/woo-auth/?shop=$domain&code=$code&timestamp=$timestamp&ssl=$ssl&return_url=$return_url";
		    
		    $status = 'success';
	    }else{
		    $status = 'error';
		    $message = 'Sorry, your nonce did not verify.';
	    }
	    
	    wp_send_json( [
		    'status' 	=> $status,
		    'message'	=> $message,
		    'url' 		=> $url
	    ], 200 );
    } 
    
    
    /**
	 * Do authentication open a2 reviews app
	 *
	 * @since    1.0.0
	 */
    public function do_open_app(){
	    $url = $message	= '';
	    
	    if($this->check_security()){
		    $app_url 	= A2REVIEWS_APP_URL;
		    $domain 	= $this->domain;
		    $ssl 		= strpos($this->site_url, 'https://') !== false? 'true': 'false';
		    $code 		= $this->ajaxData->security;
		    $timestamp 	= time();
		    $str_2_hash = "$domain-$code-$timestamp";
		    $hmac 		= hash_hmac( 'sha256', base64_encode($str_2_hash), md5(get_option( 'a2reviews_access_token' )));
		    $url 		= "$app_url/woo-auth-login/?shop=$domain&code=$code&hmac=$hmac&timestamp=$timestamp&ssl=$ssl";
		    $status 	= 'success';
		    
		    update_option( 'a2reviews_auth_code', $code );
	    }else{
		    $status = 'error';
		    $message = __('Sorry, your nonce did not verify.', 'a2reviews');
	    }
	    
	    wp_send_json( [
		    'status' 	=> $status,
		    'message' 	=> $message,
		    'url' 		=> $url,
		    'domain'	=> $domain
	    ], 200 );
    }
    
    
    /**
	 * Do authentication open a2 reviews app
	 *
	 * @since    1.0.0
	 */
    private function loginToA2App(){
	    $app_url 	= A2REVIEWS_APP_URL;
	    $domain 	= $this->domain;
	    $ssl 		= strpos($this->site_url, 'https://') !== false? 'true': 'false';
	    $code 		= wp_create_nonce( 'a2-security' );
	    $timestamp 	= time();
	    $str_2_hash = "$domain-$code-$timestamp";
	    $hmac 		= hash_hmac( 'sha256', base64_encode($str_2_hash), md5(get_option( 'a2reviews_access_token' )));
	    $url 		= "$app_url/woo-auth-login/?shop=$domain&code=$code&hmac=$hmac&timestamp=$timestamp&ssl=$ssl";
	    
	    update_option( 'a2reviews_auth_code', $code );
	    
	    wp_redirect( $url );
    }
    
    /**
	 * Save settings from ajax.
	 *
	 * @since    1.0.0
	 */
    public function save_settings(){
	    $update = 0;
	    $message = '';
	    
	    if ( !$this->check_security() ) {
		   $status = 'error';
		   $message = __('Sorry, your nonce did not verify.', 'a2reviews');
		} else {
			$settings = $this->ajaxData->settings;
			
			flush_rewrite_rules(true);
			
			if($settings && is_object($settings)){
			    $update = update_option( 'a2reviews_options', $settings);
			    $status = 'success';
		    }
		}
	    
	    wp_send_json( [
		    'status' 	=> $status,
		    'message' 	=> $message,
		    'update' 	=> $update
	    ], 200 );
    }
    
    /**
     * Options page callback
     */
    public function create_admin_page(){		
	    $settings = '{}';
	     
	    if($this->isAuth){
		    $this->options->authentication = true;
		    $settings = json_encode($this->options);
	    }
		
		echo "<script>var a2reviews_settings = JSON.parse('".$settings."');</script>";
        include( plugin_dir_path(__FILE__) . 'partials/a2reviews-admin-display.php');
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in A2reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The A2reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name. '-element-ui', plugin_dir_url( __FILE__ ) . 'css/element-ui.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/a2reviews-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in A2reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The A2reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name. '-vue-min', plugin_dir_url( __FILE__ ) . 'js/vue.min.js', array(), $this->version, true );
		wp_enqueue_script( $this->plugin_name. '-element-ui-min', plugin_dir_url( __FILE__ ) . 'js/element-ui.min.js', array(), $this->version, true );
		wp_enqueue_script( $this->plugin_name. '-axios-min', plugin_dir_url( __FILE__ ) . 'js/axios.min.js', array(), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/a2reviews-admin.js', array( 'jquery' ), $this->version, true );

	}

}
