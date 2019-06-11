<?php

/**
 *
 */
class WP_Route {

	/**
	 * Regex part for query paramters
	 *
	 * @var string
	 */
	protected $regex = '\:([\-_a-zA-Z0-9]{1,})';	// :match

	/**
	 * Store the current request URI
	 *
	 * @var string
	 */
	protected $request_uri = '';

	/**
	 * Cache url match
	 *
	 * @var boolean
	 */
	private $is_matched = false;

	/**
	 * Route ID
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Route path
	 *
	 * @var string
	 */
	public $route = '';

	/**
	 * Route controller
	 *
	 * @var callable
	 */
	public $callback;

	/**
	 * Methods the route should match
	 *
	 * @var array
	 */
	public $methods = [];

	/**
	 * Route options
	 *
	 * @var array
	 */
	public $options = [
		'title'			=> '',				// Add a page title `get_the_title()`
		'body_class'	=> '',				// Add classes to the body
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

		$this->request_uri = $this->clean_path( $_SERVER[ 'REQUEST_URI' ] );
		$methods = $this->tokenise( $method, ',' );

		// Set object attributes
		$this->id		= sanitize_title( $route . '-' . implode( '.', $methods ) );
		$this->route	= $route;
		$this->callback	= $callback;
		$this->methods	= $methods;
		$this->options	= wp_parse_args( $options, $this->options );
	}

	/**
	 * Add custom title to route
	 *
	 * @return	string
	 */
	public function page_title( $default, $id = 0 ): ?string {
		if ( $this->is_matched() && !$id ) {
			if ( $title = $this->get_option( 'title' ) ) {
				return $title;
			}
		}

		return $default;
	}
	/**
	 * Get route options
	 *
	 * @param	string	$attr
	 * @return	mixed
	 */
	public function get_option( string $name ) {
		$option = isset( $this->options[ $name ] ) ? $this->options[ $name ] : false;

		// If option is a callable function
		if ( is_callable( $option ) ) {
			$option = call_user_func_array( $option, [ $this ] );
		}

		/**
		 * Apply filter to options generically
		 *
		 * @param	mixed		$option	Current option value
		 * @param	string		$name	Option name
		 * @param	WP_Route	$this	The route object
		 * @var mixed
		 */
		$option = apply_filters( 'wp_router/get_option', $option, $name, $this );

		/**
		 * Apply filter to name specific options
		 *
		 * @param	mixed		$option	Current option value
		 * @param	WP_Route	$this	The route object
		 * @var mixed
		 */
		$option = apply_filters( 'wp_router/get_option=' . $name, $option, $this );

		return $option;
	}

	/**
	 * Create an array of string parts
	 *
	 * @param	array|string	$string
	 * @param	string			$separator
	 * @return	array
	 */
	private function tokenise( $item, string $separator = '/' ): array {
		return array_values( array_filter( is_array( $item ) ? $item : explode( $separator, $item ) ) );
	}

	/**
	 * Globaly clean url in a specific way
	 *
	 * @param	string	$url
	 * @return	string
	 */
	private function clean_path( string $url ): ?string {
		$url = trim( $url, '/' );
		$parts = parse_url( $url );
		return isset( $parts[ 'path' ] ) ? $parts[ 'path' ] : '';
	}

	/**
	 * Get URL paramaters
	 *
	 * @return	array
	 */
	public function get_params(): array {
		$route_parts	= $this->tokenise( $this->route );
		$request_parts	= $this->tokenise( $this->request_uri );

		// Find query paramaters
		preg_match_all( '/' . $this->regex . '/', $this->route, $matches );

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
	 * If the current URI matched this route
	 *
	 * @return WP_Route|bool
	 */
	public function is_matched() {

		// Don't match admin
		if ( is_admin() ) {
			return false;
		}

		// Get cached flag
		if ( $this->is_matched ) {
			return $this;
		}

		$route_parts = $this->tokenise( $this->route );
		$request_parts	= $this->tokenise( $this->request_uri );

		if ( count( $route_parts ) === count( $request_parts ) ) {
			$checked = 0;
			foreach ( $route_parts as $key => $value ) {
				if ( $value === $request_parts[ $key ] || preg_match( '/^' . $this->regex . '$/', $value ) ) {
					$checked++;
				}
			}

			if ( $checked === count( $route_parts ) ) {
				$this->is_matched = true;
				return $this;
			}
		}

		return false;
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
		$params = $this->get_params();

		// Add robots
		if ( $this->options[ 'robots' ] ){
			add_filter( 'wp_head', function() {
				return '<meta name="robots" content="noindex,nofollow"/>';
			} );
		}

		// Add a page title
		add_filter( 'the_title', [ $this, 'page_title' ], 10, 2 );
		add_filter( 'wp_title', [ $this, 'page_title' ], 10, 1 );

		// Add body classes
		$route_classes	= !empty( $this->options[ 'body_class' ] ) ? $this->options[ 'body_class' ] : '';
		$route_classes	= $this->tokenise( $route_classes, ' ' );
		$route_classes	= array_filter( array_merge( [ 'custom-route-page' ], $route_classes ) );

		add_filter( 'body_class', function( $classes ) use ( $route_classes ) {
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
