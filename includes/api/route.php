<?php

/**
 * Get current query paramaters of the current route
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


/**
 * Get a single query paramater of the current route
 *
 * @param	string	$name
 * @return	array
 */
function wp_router_param( string $name ) {
	$params = wp_router_params();

	return isset( $params[ $name ] ) ? $params[ $name ] : false;
}
