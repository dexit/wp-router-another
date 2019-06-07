<?php

trait WP_Route_Shared {

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

	function __construct() {
		// Clean URI and store for reuse
		$this->request_uri = $this->clean_url( $_SERVER[ 'REQUEST_URI' ] );
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
	private function clean_url( string $url ): ?string {
		return trim( $url, '/' );
	}

}
