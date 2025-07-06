<?php
/**
 * Plugin Name: ML Mailing Lists
 * Description: Plugin para gestionar listas de correo e subscricións.
 * Version: 1.0.2
 * Author: Carlos Longarela
 * Author URI: https://tabernawp.com/
 * License: GPL2
 * Text Domain: ml-mailing-lists
 * Domain Path: /languages
 *
 * @package ML_Mailing_Lists
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'ML_PLUGIN_FILE', __FILE__ );
define( 'ML_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ML_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ML_PLUGIN_VERSION', '1.0.2' );
define( 'ML_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Initialize the plugin.
 *
 * This function loads the core class and initializes the plugin.
 * All plugin logic is handled by the modular class structure.
 */
function ml_init_plugin() {
	// Load the core class that handles all plugin initialization.
	require_once ML_PLUGIN_PATH . 'includes/class-ml-core.php';

	// Initialize the plugin using the singleton pattern with namespace.
	\ML_Mailing_Lists\Core::get_instance();
}

// Initialize plugin after WordPress is fully loaded.
add_action( 'plugins_loaded', 'ml_init_plugin' );
