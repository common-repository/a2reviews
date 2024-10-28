<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.eosvn.com/plugin/a2reviews
 * @since             1.0.2
 * @package           A2reviews
 *
 * @wordpress-plugin
 * Plugin Name:       A2Reviews
 * Plugin URI:        https://www.a2rev.com
 * Description:       A2Reviews is the best review app for WooCommerce. Smart assessment management system.
 * Version:           1.2.3
 * Author:            A2reviews
 * Author URI:        https://www.eosvn.com/plugin/a2reviews
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       a2reviews
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'A2REVIEWS_VERSION', '1.2.3' );
define( 'A2REVIEWS_APP_URL', 'https://app.a2rev.com' );
define( 'A2REVIEWS_API_URL', 'https://api.a2rev.com' );
define( 'A2REVIEWS_SOCKET_URL', 'https://socket.a2rev.com' );
define( 'A2REVIEWS_PATH_PUBLIC', plugin_dir_path( __FILE__ ) . 'public' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-a2reviews.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-a2reviews-activator.php
 */
function activate_a2reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-a2reviews-activator.php';
	A2reviews_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-a2reviews-deactivator.php
 */
function deactivate_a2reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-a2reviews-deactivator.php';
	A2reviews_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_a2reviews' );
register_deactivation_hook( __FILE__, 'deactivate_a2reviews' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_a2reviews() {

	$plugin = new A2reviews();
	$plugin->run();

}
run_a2reviews();
