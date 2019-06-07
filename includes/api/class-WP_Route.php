<?php

/**
 *
 */
class WP_Route {

	use WP_Route_Shared {
		WP_Route_Shared::__construct as private __sharedConstruct;
	}

	public $id = '';
	public $route = '';
	public $callback;
	public $methods = [];
	public $options = [
		'robots'		=> false,			// If page allows indexing by robots
		'private'		=> false,			// Make route private (has to be authenticated)
		'capabilities'	=> 'manage_options'	// If private, user has to match these capabilities (see https://wordpress.org/support/article/roles-and-capabilities/)
	];

	/**
	 * Register a route with method and options
	 *
	 * @param	array|string	$method
	 * @param	string			$route
	 * @param	FBFW_Page		$options
	 * @return	WP_Error|object			On Success return route object
	 */
	function __construct( $method, string $route, $callback, array $options = [] ) {

		// Trait constructor
		$this->__sharedConstruct();

		$methods = $this->tokenise( $method, ',' );

		// Set object attributes
		$this->id		= sanitize_title( $route . '-' . implode( '.', $methods ) );
		$this->route	= $route;
		$this->callback	= $callback;
		$this->methods	= $methods;
		$this->options	= wp_parse_args( $options, $this->options );

		// Add a page title
		add_filter( 'the_title', [ $this, 'page_title' ], 1 );
		add_filter( 'wp_title', [ $this, 'page_title' ], 1 );
	}

	/**
	 * Get route related meta
	 *
	 * @param	string	$attr
	 * @return	mixed
	 */
	public function get_meta( $attr = false ) {
		$meta = get_option( $this->id, [] );

		if ( $attr ) {
			return isset( $meta[ $attr ] ) ? $meta[ $attr ] : false;
		}

		return $meta;
	}

	/**
	 * Add custom title to route
	 *
	 * @param	string	$default
	 * @return	string
	 */
	public function page_title( $default ): ?string {
		if ( $title = $this->get_meta( 'title' ) ) {
			return $title;
		}

		return null;
	}

	/**
	 * Get URL paramaters
	 *
	 * @param	string	$uri
	 * @return	array
	 */
	public function get_params( string $uri = null ): array {
		$route_parts	= $this->tokenise( $this->route );
		$request_parts	= $this->tokenise( $uri ?: $this->request_uri );

		// Find query paramaters
		preg_match_all( '/' . router_get_setting( 'regex' ) . '/', $this->route, $matches );

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
	 * Call the callback and apply filters
	 *
	 * @return void
	 */
	public function call() {

		// Handle route permissions
		if ( $this->options[ 'private' ] ) {
			if ( !is_user_logged_in() || !current_user_can( $this->options[ 'capabilities' ] ) ) {
				status_header( 401 );
				wp_die( __( 'Sorry, you are not allowed to access this page.' ) );
			}
		}

		// Get route parameters
		$params = $this->get_params( $this->request_uri );

		// Add robots
		if ( $this->options[ 'robots' ] ){
			add_filter( 'wp_head', function() {
				return '<meta name="robots" content="noindex,nofollow"/>';
			} );
		}

		// Add body classes
		$route_classes	= !empty( $this->options[ 'body_class' ] ) ? $this->options[ 'body_class' ] : '';
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

		// Modify query
		global $wp_query;
		$wp_query->is_404 = false;
		$wp_query->query_vars[ 'pagename' ] = null;
		$wp_query->route_params = $this->get_params();

		if ( is_callable( $this->callback ) ) {
			call_user_func_array( $this->callback, [ $params ] );
			exit;
		}

		return new WP_Error( 'not_callable', 'Route handler is not callable' );
	}

}
