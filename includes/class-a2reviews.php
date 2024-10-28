<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.gobliz.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    A2reviews
 * @subpackage A2reviews/includes
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      A2reviews_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * The memory limit need of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	private $limit_memory = 64;

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
		if ( defined( 'A2REVIEWS_VERSION' ) ) {
			$this->version = A2REVIEWS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'a2reviews';
		
		if($this->get_limit() < $this->limit_memory){
			$this->set_limit_memory();
		}
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->rewrite_url();
		
		add_action( 'wp_ajax_nopriv_a2reviews_api', [&$this, 'app_api_port'] );
	}
	
	/**
	 * Get limit memory
	 *
	 * Uses the A2reviews_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function get_limit() {
		//set vars
		$limit = null;
		//get limit?
		if(function_exists('ini_get')) {
			$limit = (int) ini_get('memory_limit');
		}
		//return
		return $limit ? intval($limit) : null;
	}
	
	/**
	 * Set limit memory
	 *
	 * Uses the A2reviews_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function set_limit_memory(){
		if(!function_exists('ini_set')) {
			return;
		}
		
		@ini_set('memory_limit', $this->limit_memory . 'M');
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - A2reviews_Loader. Orchestrates the hooks of the plugin.
	 * - A2reviews_i18n. Defines internationalization functionality.
	 * - A2reviews_Admin. Defines all hooks for the admin area.
	 * - A2reviews_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a2reviews-woo.php';
		
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a2reviews-api.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a2reviews-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a2reviews-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a2reviews-admin.php';

		/**
		 * The class responsible for defining all actions handle woocommerce order
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-a2reviews-shortcode.php';

		/**
		 * The class responsible for defining all actions handle woocommerce order
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-a2reviews-order.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-a2reviews-public.php';

		$this->loader = new A2reviews_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the A2reviews_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new A2reviews_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new A2reviews_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new A2reviews_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    A2reviews_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	
	/**
	 * A2 Reviews rewrite api url
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function rewrite_url(){
		add_action('init', function() {
		    add_rewrite_rule(
				'a2rev/api/([^/]+)/?$',
		        'index.php?vendor=a2-reviews&type=api&endpoint=$matches[1]',
		        'top'
		    );
		    
		    add_rewrite_rule(
				'feature-reviews',
		        'index.php?vendor=a2-reviews&type=feature-page',
		        'top'
		    );
		});
		
		add_filter('query_vars', function($vars) {
		    $vars[] = "vendor";
		    $vars[] = "type";
		    $vars[] = "endpoint";
		    return $vars;
		});
		
		add_action( 'template_redirect', function(){
			global $wp_query;
			
			$query = (OBJECT) $wp_query->query;

			if(
				(isset($query->vendor) && $query->vendor == 'a2-reviews')
				&& isset($query->type) && $query->type == 'api'
				&& $_SERVER['REQUEST_METHOD'] === 'POST'
			){
				$endpoint = isset($query->endpoint)? $query->endpoint: '';
				$output = (new A2reviews_API($endpoint, $_POST))->handle();
				wp_send_json($output);
				die(0);	
			}
		}, 10 );
		
		
		add_action( 'template_include', function( $template ) {
			global $wp_query;
			
			$query = (OBJECT) $wp_query->query;

			if(
				(isset($query->vendor) && $query->vendor == 'a2-reviews')
				&& isset($query->type) && $query->type == 'feature-page'
			){
		    	return A2REVIEWS_PATH_PUBLIC . '/partials/feature-reviews.php';
		    }else{
			    return $template;
		    }
		} );
	}
	
	/**
	 * App Port Communication
	 *
	 * @since    1.0.0
	 */
	public function app_api_port(){
		$endpoint = $_GET['cmd'] ?? '';
		$output = (new A2reviews_API($endpoint, $_POST))->handle();
		wp_send_json($output);
		die(0);	
	}

}
