<?php

/**
 * WP_Router
 * 
 * @author Levi Cole <hello@thelevicole.com>
 */
class WP_Router {

	/**
	 * Regex part for query paramters
	 * 
	 * @var string
	 */
	protected $regex = '\:([\-_a-zA-Z0-9]{1,})';	// :match

	/**
	 * Routes grouped by method
	 * 
	 * @var array
	 */
	public $routes = [
		'ANY'		=> [],
		'GET'		=> [],
		'POST'		=> [],
		'HEAD'		=> [],
		'PUT'		=> [],
		'DELETE'	=> [],
	];

	/**
	 * Store the current request URI
	 * 
	 * @var string
	 */
	protected $request_uri = '';

	function __construct() {
		// Clean URI and store for reuse
		$this->request_uri = $this->clean_url( $_SERVER[ 'REQUEST_URI' ] );

		// Run handler on init
		$this->handler();

		// Modify WordPress query
		add_action( 'parse_query', [ $this, 'query_modifier' ] );

		// Run route callback on WordPress init
		add_action( 'init', [ $this, 'handler' ] );
	}

	/**
	 * Get all routes by a specific method
	 *
	 * @param	string	$method
	 * @return	array
	 */
	public function routes_by_method( string $method ): array {
		return array_merge( $this->routes[ strtoupper( $method ) ], $this->routes[ 'ANY' ] );
	}

	/**
	 * Check if route is registered
	 *
	 * @param	string	$route
	 * @return	?object
	 */
	public function exists( string $route, string $method = 'ANY' ): ?object {
		$all = $this->routes_by_method( $method );
		return in_array( $route, array_keys( $all ) ) ? $all[ $route ] : null;
	}

	/**
	 * Create an array of URI parts
	 *
	 * @param	string	$uri
	 * @return	array
	 */
	private function tokenise( string $uri ): array {
		return array_filter( explode( '/', trim( $uri, '/' ) ) );
	}

	/**
	 * Globaly clean url in a specific way
	 *
	 * @param	string	$url
	 * @return	string
	 */
	private function clean_url( string $url ): ?string {
		return trim( $url, '/' );
	}

	/**
	 * Get URL paramaters
	 *
	 * @param	string	$route
	 * @return	array
	 */
	private function get_params( string $route ): array {
		$route_parts	= $this->tokenise( $route );
		$request_parts	= $this->tokenise( $this->request_uri );

		// Find query paramaters
		preg_match_all( "/{$this->regex}/", $route, $matches );

		$return = [];

		if ( !empty( $matches[ 0 ] ) && is_array( $matches[ 0 ] ) ) {
			foreach ( $matches[ 0 ] as $key => $match ){
				$search = array_search( $match, $route_parts );
				if ( $search !== false ) {
					$param_key = $matches[ 1 ][ $key ];

					$return[ $param_key ] = sanitize_text_field( $request_parts[ $search ] );
				}
			}
		}

		return $return;
	}

	/**
	 * Register a route with method and options
	 *
	 * @param	string	$route
	 * @param	FBFW_Page	$options
	 * @return	WP_Error|object			On Success return route object
	 */
	public function register( string $method, string $route, $callback, array $options = [] ) {

		if ( $this->exists( $route, $method ) ) {
			return new WP_Error( 'route_already_exists', 'A route with that path and method already exists' );
		}

		// Merge optional args with defaults
		$args = wp_parse_args( $options, [
			'robots'		=> false,			// If page allows indexing by robots
			'private'		=> false,			// Make route private (has to be authenticated)
			'capabilities'	=> 'manage_options'	// If private, user has to match these capabilities (see https://wordpress.org/support/article/roles-and-capabilities/)
		] );

		// Override static arguments
		$args[ 'route' ] = $route;
		$args[ 'callback' ] = $callback;

		// Store route
		$this->routes[ $method ][ $route ] = (object)$args;

		// Return route
		return $this->exists( $route, $method );
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
		$request_parts = $this->tokenise( $this->request_uri );

		$matched = false;

		// Basic filtering of routes
		if ( $group ) {
			foreach ( $group as $route => $options ) {
				$route_parts = $this->tokenise( $route );

				if ( count( $route_parts ) === count( $request_parts ) ) {
					$checked = 0;
					foreach ( $route_parts as $key => $value ) {
						if ( $value === $request_parts[ $key ] || preg_match( "/^{$this->regex}$/", $value ) ) {
							$checked++;
						}
					}

					if ( $checked === count( $route_parts ) ) {
						$matched = $options;
						break;
					}
				}
			}
		}

		return $matched;
	}

	/**
	 * Modify the main WordPress query
	 *
	 * @param	object	$query
	 * @return	void
	 */
	public function query_modifier( $query ) {
		if ( $query->is_main_query() && !is_admin() ) {
			if ( $matched = $this->url_is_route() ) {
				$query->is_404 = false;
				$query->query_vars[ 'pagename' ] = null;
				$query->route_params = $this->get_params( $matched->route );
			}
		}
	}

	/**
	 * Header satus and handling route callback
	 *
	 * @return void
	 */
	public function handler() {
		$matched = $this->url_is_route();
		if ( $matched && is_object( $matched ) ) {

			// Handle route permissions
			if ( $matched->private ) {
				if ( !is_user_logged_in() || !current_user_can( $matched->capabilities ) ) {
					status_header( 401 );
					wp_die( __( 'Sorry, you are not allowed to access this page.' ) );
				}
			}

			// Get route parameters
			$params = $this->get_params( $matched->route );

			// Add robots
			if ( $matched->robots ){
				add_filter( 'wp_head', function() {
					return '<meta name="robots" content="noindex,nofollow"/>';
				} );
			}

			// Add body classes
			$route_classes	= !empty( $matched->body_class ) ? $matched->body_class : '';
			$route_classes	= is_string( $route_classes ) ? explode( ' ', $route_classes ) : [];
			$route_classes	= array_filter( array_merge( [ 'custom-route-page' ], $route_classes ) );

			add_filter( 'body_class', function( $classes, $class ) use ( $route_classes ) {
				$search = array_search( 'error404', $classes );
				if ( $search !== false ) {
					unset( $classes[ $search ] );
				}

				if ( $route_classes ) {
					$classes = array_merge( $classes, $route_classes );
				}

				return $classes;
			}, 10, 2 );

			status_header( 200 );

			if ( is_callable( $matched->callback ) ) {
				call_user_func_array( $matched->callback, [ $params ] );
				exit;
			}

			return new WP_Error( 'not_callable', 'Route handler is not callable' );
		}
	}

}

/**
 * Initiate class if not already created
 *
 * @return	WP_Router		Returns class instance
 */
function wp_router() {
	global $wp_router;

	// If not initiated, initate
	if ( !isset( $wp_router ) ) {
		$wp_router = new WP_Router;
	}

	return $wp_router;
}


/**
 * Initiate on inlcude
 */
wp_router();


/* Helper functions
-------------------------------------------------------- */

/**
 * Alias of `WP_Router->register( ... )`
 */
function wp_router_register( $method, $route, $callback, $options = [] ) {
	return wp_router()->register( $method, $route, $callback, $options );
}

/**
 * Alias of `WP_Router->register( 'ANY', ... )`
 */
function wp_router_any( $route, $callback, $options = [] ) {
	return wp_router()->register( 'ANY', $route, $callback, $options );
}

/**
 * Alias of `WP_Router->get( ... )`
 */
function wp_router_get( $route, $callback, $options = [] ) {
	return wp_router()->get( $route, $callback, $options );
}

/**
 * Alias of `WP_Router->post( ... )`
 */
function wp_router_post( $route, $callback, $options = [] ) {
	return wp_router()->post( $route, $callback, $options );
}

/**
 * Alias of `WP_Router->head( ... )`
 */
function wp_router_head( $route, $callback, $options = [] ) {
	return wp_router()->head( $route, $callback, $options );
}

/**
 * Alias of `WP_Router->put( ... )`
 */
function wp_router_put( $route, $callback, $options = [] ) {
	return wp_router()->put( $route, $callback, $options );
}

/**
 * Alias of `WP_Router->delete( ... )`
 */
function wp_router_delete( $route, $callback, $options = [] ) {
	return wp_router()->delete( $route, $callback, $options );
}

/**
 * Get current query paramaters
 * 
 * @return	array
 */
function wp_router_params(): array {
	global $wp_query;

	$params = [];

	if ( isset( $wp_query->route_params ) ) {
		$params = $wp_query->route_params;
	}

	return $params;
}






