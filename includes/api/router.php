<?php

/**
 * Alias of `wp_router()->register( ... )`
 */
function wp_router_register( $method, $route, $callback, $options = [] ) {
	return wp_router()->register( $method, $route, $callback, $options );
}

/**
 * Alias of `wp_router()->register( 'ANY', ... )`
 */
function wp_router_any( $route, $callback, $options = [] ) {
	return wp_router()->register( 'ANY', $route, $callback, $options );
}

/**
 * Alias of `wp_router()->get( ... )`
 */
function wp_router_get( $route, $callback, $options = [] ) {
	return wp_router()->get( $route, $callback, $options );
}

/**
 * Alias of `wp_router()->post( ... )`
 */
function wp_router_post( $route, $callback, $options = [] ) {
	return wp_router()->post( $route, $callback, $options );
}

/**
 * Alias of `wp_router()->head( ... )`
 */
function wp_router_head( $route, $callback, $options = [] ) {
	return wp_router()->head( $route, $callback, $options );
}

/**
 * Alias of `wp_router()->put( ... )`
 */
function wp_router_put( $route, $callback, $options = [] ) {
	return wp_router()->put( $route, $callback, $options );
}

/**
 * Alias of `wp_router()->delete( ... )`
 */
function wp_router_delete( $route, $callback, $options = [] ) {
	return wp_router()->delete( $route, $callback, $options );
}
