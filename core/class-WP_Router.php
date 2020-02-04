<?php

/**
 * WP_Router
 *
 * @author Levi Cole <hello@thelevicole.com>
 */
class WP_Router {

	/**
	 * Routes grouped by method
	 *
	 * @var array
	 */
	protected $routes = [];

	function __construct() {

		// Run handler on init
		$this->handler();

		// Run route callback on WordPress init
		add_action( 'init', [ $this, 'handler' ] );
	}

	/**
	 * Simple function for returning all methods groups
	 *
	 * @return	array
	 */
	public function get_routes(): array {
		return $this->routes;
	}

	/**
	 * Get all routes by a specific method
	 *
	 * @param	string	$method
	 * @return	array
	 */
	public function routes_by_method( string $method ): array {
		$routes = array_filter( $this->get_routes(), function( $el ) use ( $method ) {
			return in_array( $method, $el->methods ) || in_array( 'ANY', $el->methods );
		} );

		$routes = array_values( $routes );

		return $routes;
	}

	/**
	 * Get route by identifier
	 *
	 * @param	string	$id
	 * @return	?WP_Route
	 */
	public function route_by_id( string $id ): ?WP_Route {
		$found = array_filter( $this->get_routes(), function( $el ) use ( $id ) {
			return $el->id === $id;
		} );

		$found = array_values( $found );

		return !empty( $found[ 0 ] ) ? $found[ 0 ] : null;
	}

	/**
	 * Check if route is registered
	 *
	 * @param	string			$route
	 * @param	array|string	$method
	 * @return	?WP_Route
	 */
	public function exists( string $route, $method = 'ANY' ): ?WP_Route {

		$methods = array_values( array_filter( is_array( $method ) ? $method : explode( ',', $method ) ) );

		$routes = array_filter( $this->get_routes(), function( $el ) use ( $route, $methods ) {
			return $el->route === $route && !empty( array_intersect( $el->methods, $methods ) );
		} );

		$routes = array_values( $routes );

		return !empty( $routes ) ? $routes[ 0 ] : null;
	}

	/**
	 * Register a route with method and options
	 *
	 * @param	array|string	$method
	 * @param	string			$route
	 * @param	FBFW_Page		$options
	 * @return	WP_Error|WP_Route			On Success return route object
	 */
	public function register( $method, string $route, $callback, array $options = [] ) {

		$methods = array_values( array_filter( is_array( $method ) ? $method : explode( ',', $method ) ) );

		if ( $this->exists( $route, $method ) ) {
			return new WP_Error( 'route_already_exists', __( 'A route with that path and method already exists', 'wprouter' ) );
		}

		// Store route
		$this->routes[] = new WP_Route( $method, $route, $callback, $options );

		// Return route
		return $this->exists( $route, $methods );
	}

	/**
	 * Store a GET route
	 *
	 * @see  `$this->register()`
	 */
	public function get( string $route, $callback, array $options = [] ) {
		return $this->register( 'GET', $route, $callback, $options );
	}

	/**
	 * Store a POST route
	 *
	 * @see  `$this->register()`
	 */
	public function post( string $route, $callback, array $options = [] ) {
		return $this->register( 'POST', $route, $callback, $options );
	}

	/**
	 * Store a HEAD route
	 *
	 * @see  `$this->register()`
	 */
	public function head( string $route, $callback, array $options = [] ) {
		return $this->register( 'HEAD', $route, $callback, $options );
	}

	/**
	 * Store a PUT route
	 *
	 * @see  `$this->register()`
	 */
	public function put( string $route, $callback, array $options = [] ) {
		return $this->register( 'PUT', $route, $callback, $options );
	}

	/**
	 * Store a DELETE route
	 *
	 * @see  `$this->register()`
	 */
	public function delete( string $route, $callback, array $options = [] ) {
		return $this->register( 'DELETE', $route, $callback, $options );
	}

	/**
	 * Check if the current url matches a route
	 *
	 * @return	object|boolean
	 */
	public function url_is_route() {
		$group = $this->routes_by_method( $_SERVER[ 'REQUEST_METHOD' ] );

		// Basic filtering of routes
		if ( $group ) {
			foreach ( $group as $i => $route ) {
				if ( $_matched = $route->is_matched() ) {
					return $_matched;
				}
			}
		}

		return false;
	}

	/**
	 * Header status and handling route callback
	 *
	 * @return void
	 */
	public function handler() {
		$matched = $this->url_is_route();

		if ( $matched && $matched instanceof WP_Route ) {
			return $matched->call();
		}
	}
}

/**
 * Initiate class if not already created
 *
 * @return	WP_Router		Returns class instance
 */
function wp_router() {
	global $wp_router_controller;

	// If not initiated, initiate
	if ( !isset( $wp_router_controller ) ) {
		$wp_router_controller = new WP_Router;
	}

	return $wp_router_controller;
}


/**
 * Initiate on include
 */
wp_router();



