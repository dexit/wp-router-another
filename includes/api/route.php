<?php

/**
 * Get current query parameters of the current route
 *
 * @return	array
 */
function wp_router_params(): array {
	global $wp_router_match;

	if ( $wp_router_match ) {
		$params = $wp_router_match->get_params();
	}

	return [];
}


/**
 * Get a single query parameter of the current route
 *
 * @param	string	$name
 * @param	mixed	$fallback
 * @return	array
 */
function wp_router_param( string $name, $fallback = false ) {
	$params = wp_router_params();

	return isset( $params[ $name ] ) ? $params[ $name ] : $fallback;
}

/**
 * Helper function to check if the current page is a match route
 *
 * @return	boolean|WP_Route
 */
function is_wp_router() {
	global $wp_router_match;

	if ( !empty( $wp_router_match ) && $wp_router_match instanceof WP_Route ) {
		return $wp_router_match;
	}

	return false;
}
