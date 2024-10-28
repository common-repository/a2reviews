<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.gobliz.com/plugin/a2reviews
 * @since      1.0.0
 *
 * @package    A2reviews
 * @subpackage A2reviews/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    A2reviews
 * @subpackage A2reviews/includes
 * @author     A2reviews <os.solutionvn@gmail.com>
 */
class A2reviews_Activator {

	public static $option_key = 'a2reviews_options';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static $default_settings = [ 
		'tab_active' 			=> 'Default',
		'authentication' 		=> false,
		'widget_position' 		=> 'Replace default tab',
		'widget_custom_position' => 2,
		'tab_label_mask' 		=> 'Reviews ({% total_reviews %})',
		'remove_reviews_tab' 	=> false,
		'widget_total_position' => 2,
		'cat_widget_total_position' => 2,
		'qa_position' 			=> 'In tab',
		'qa_custom_position' 	=> 2,
		'tab_qa_label_mask' 	=> 'Questions & Answers ({% total_questions %})'
	];
	 
	 
	/**
	 * activate
	 */
	public static function activate() {		
		$settings = get_option( self::$option_key );
		$default_settings = (OBJECT) self::$default_settings;
		
		if(!$settings){
			update_option( self::$option_key, $default_settings);
		}
		
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
		
		flush_rewrite_rules();
	}

}
