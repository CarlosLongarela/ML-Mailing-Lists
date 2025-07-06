<?php
/**
 * Main class for ML Mailing Lists plugin
 *
 * @package ML Mailing Lists
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main ML_Core class
 */
class ML_Core {

	/**
	 * Single instance of the class
	 *
	 * @var ML_Core
	 */
	private static $instance = null;

	/**
	 * Private constructor for singleton pattern
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get single instance of the class
	 *
	 * @return ML_Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin
	 */
	private function init() {
		// Load main classes.
		$this->load_dependencies();

		// Initialize main hooks.
		add_action( 'init', array( $this, 'init_plugin' ) );

		// Activation/deactivation hooks.
		register_activation_hook( ML_PLUGIN_FILE, array( $this, 'on_activation' ) );
		register_deactivation_hook( ML_PLUGIN_FILE, array( $this, 'on_deactivation' ) );
	}

	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies() {
		// Load helper functions.
		require_once ML_PLUGIN_PATH . 'includes/functions.php';

		// Load main classes.
		require_once ML_PLUGIN_PATH . 'includes/class-ml-security.php';
		require_once ML_PLUGIN_PATH . 'includes/class-ml-shortcode.php';
		require_once ML_PLUGIN_PATH . 'includes/class-ml-email-sender.php';
		require_once ML_PLUGIN_PATH . 'includes/class-ml-export.php';
		require_once ML_PLUGIN_PATH . 'includes/class-ml-admin.php';
	}

	/**
	 * Initialize plugin components
	 */
	public function init_plugin() {
		// Initialize security.
		ML_Security::get_instance();

		// Initialize shortcode.
		ML_Shortcode::get_instance();

		// Initialize export functionality.
		ML_Export::get_instance();

		// Initialize email sender.
		ML_Email_Sender::get_instance();

		// Initialize admin (only in admin area).
		if ( is_admin() ) {
			ML_Admin::get_instance();
		}
	}

	/**
	 * Actions to perform on plugin activation
	 */
	public function on_activation() {
		// Clear options cache.
		wp_cache_flush();

		// Create necessary pages/options if needed.
		do_action( 'ml_plugin_activated' );
	}

	/**
	 * Acciones al desactivar el plugin
	 */
	public function on_deactivation() {
		// Clear cache.
		wp_cache_flush();

		// Clean up plugin transients.
		delete_transient( 'ml_rate_limit_*' );

		do_action( 'ml_plugin_deactivated' );
	}

	/**
	 * Prevent cloning
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization
	 */
	private function __wakeup() {}
}
