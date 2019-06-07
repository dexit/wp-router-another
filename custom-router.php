<?php

/**
 * Plugin Name: WordPress Custom Router
 * Description: Create custom WordPress routes with a easy to use API and admin panel.
 * Version: 0.1.0
 * Plugin URI: https://github.com/thelevicole/wp-router/
 * Author: Levi Cole
 * Author URI: https://thelevicole.com
 * Text Domain: customrouter
 */

class WP_Router_Plugin {

	protected $settings = [];
	protected $debug = false;

	function __construct() {}

	/**
	 * Publugin initialiser
	 *
	 * @return	void
	 */
	public function initialise() {

		/**
		 * Register settings
		 *
		 * @var array
		 */
		$this->settings = [

			// Generic
			'version'		=> $this->debug ? date( 'U' ) : '0.1.0',
			'path'			=> plugin_dir_path( __FILE__ ),
			'url'			=> plugin_dir_url( __FILE__ ),
			'basename'		=> plugin_basename( __FILE__ ),

			// Routing
			'regex'			=> '\:([\-_a-zA-Z0-9]{1,})',

			// WordPress
			'wp_version'	=> get_bloginfo( 'version' ),

			// Admin options
			'capability'	=> 'manage_options'
		];

		// Set global constants
		$this->define( 'VERSION', $this->settings[ 'version' ] );
		$this->define( 'PATH', $this->settings[ 'path' ] );
		$this->define( 'URL', $this->settings[ 'url' ] );

		// API includes
		require_once WP_Router_PATH . 'includes/api/general.php';
		router_require( 'includes/api/trait-WP_Route_Shared.php' );
		router_require( 'includes/api/class-WP_Route.php' );
		router_require( 'includes/class-WP_Router.php' );
		router_require( 'includes/api/router.php' );

		if ( is_admin() ) {
			router_require( 'includes/class-WP_Router_Admin.php' );
		}

	}

	/**
	 * Define a constant safetly, includes predefined check and adds a prefix
	 *
	 * @param	string	$key	Name of the globally created constant
	 * @param	mixed	$value	The value that will be returned when calling the constant
	 * @return	void
	 */
	public function define( string $key, $value ) {

		$key = 'WP_Router_' . strtoupper( $key );

		if ( !defined( $key ) ) {
			define( $key, $value );
		}
	}

	/**
	 * Check if the setting with `$name` exists in this instance
	 *
	 * @param	string	$name	Name of setting
	 * @return	boolean
	 */
	public function has_setting( string $name ): bool {
		return isset( $this->settings[ $name ] );
	}

	/**
	 * Get a specific setting value from the instance
	 *
	 * @param	string	$name	Name of setting
	 * @return	mixed
	 */
	public function get_setting( string $name ) {
		return $this->has_setting( $name ) ? $this->settings[ $name ] : null;
	}

	/**
	 * Update an instance setting
	 *
	 * @param	string	$name	Name of setting
	 * @param	mixed	$value
	 * @return	boolean
	 */
	public function update_setting( string $name, $value ): bool {
		$this->settings[ $name ] = $value;
		return true;
	}

}


/**
 * Initiate class if not already created
 *
 * @return	WP_Router_Plugin		Returns class instance
 */
function router_plugin() {
	global $router_plugin_controller;

	// If not initiated, initate
	if ( !isset( $router_plugin_controller ) ) {
		$router_plugin_controller = new WP_Router_Plugin;
		$router_plugin_controller->initialise();
	}

	return $router_plugin_controller;
}


/**
 * Init plugin
 */
router_plugin();

