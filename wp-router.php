<?php

/**
 * Plugin Name: WPRouter
 * Plugin URI: https://github.com/thelevicole/wp-router/
 * Author: Levi Cole
 * Author URI: https://thelevicole.com
 * Description: Create custom WordPress routes with a easy to use API and admin panel. Made by developers, for developers.
 * Version: 1.0.0
 * Text Domain: wprouter
 * Network: true
 * Requires at least: 5.2
 * Requires PHP: 7.2
 */

class WP_Router_Plugin {

	protected $settings = [];

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
			'version'		=> '1.0.0',
			'path'			=> plugin_dir_path( __FILE__ ),
			'url'			=> plugin_dir_url( __FILE__ ),
			'basename'		=> plugin_basename( __FILE__ ),

			// WordPress
			'wp_version'	=> get_bloginfo( 'version' ),

			// Admin options
			'capability'	=> 'manage_options'
		];

		// Set global constants
		$this->define( 'VERSION', $this->settings[ 'version' ] );
		$this->define( 'PATH', $this->settings[ 'path' ] );
		$this->define( 'URL', $this->settings[ 'url' ] );

		// General helpers
		$this->require( 'includes/api/general.php' );

		// Core controllers
		$this->require( 'core/class-WP_Route.php' );
		$this->require( 'core/class-WP_Router.php' );

		// Controler helpers
		$this->require( 'includes/api/router.php' );
		$this->require( 'includes/api/route.php' );

		// Admin only requirements
		if ( is_admin() ) {
			$this->require( 'includes/admin/class-WP_Router_Admin.php' );
		}


		// Database option filters for WP_Route
		add_filter( 'wp_router/get_option', function( $value, $option, $route ) {
			$data = get_option( $route->id, [] );
			return !empty( $data[ $option ] ) ? $data[ $option ] : $value;
		}, 10, 3 );
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
	 * Return an absolute path from relative
	 *
	 * @param	string	$path	Path to plugin file
	 * @return	string
	 */
	public function get_path( string $path ): string {
		return WP_Router_PATH . ltrim( $path, '/' );
	}

	/**
	 * Include file from relative path
	 *
	 * @param	string	$path
	 * @return	void
	 */
	public function include( string $path ) {
		$path = $this->get_path( $path );

		if ( file_exists( $path ) ) {
			include $path;
		}
	}

	/**
	 * Require file from relative path
	 *
	 * @param	string	$path
	 * @return	void
	 */
	public function require( string $path ) {
		$path = $this->get_path( $path );

		if ( file_exists( $path ) ) {
			require $path;
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
function wp_router_plugin() {
	global $wp_router_plugin;

	// If not initiated, initate
	if ( !isset( $wp_router_plugin ) ) {
		$wp_router_plugin = new WP_Router_Plugin;
		$wp_router_plugin->initialise();
	}

	return $wp_router_plugin;
}


/**
 * Init plugin
 */
wp_router_plugin();

