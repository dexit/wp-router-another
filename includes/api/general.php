<?php

/**
 * Return an absolute path from relative
 *
 * @param	string	$path	Path to plugin file
 * @return	string
 */
function router_get_path( string $path ): string {
	return WP_Router_PATH . ltrim( $path, '/' );
}

/**
 * Return an absolute path from relative
 *
 * @param	string	$path
 * @return	string
 */
function router_get_url( string $path ): string {
	return WP_Router_PATH . ltrim( $path, '/' );
}

/**
 * Check if a setting exists in the global instance
 *
 * alias of router_plugin()->has_setting()
 *
 * @param	string	$name	Name of setting
 * @return	boolean
 */
function router_has_setting( string $name ): bool {
	return router_plugin()->has_setting( $name );
}

/**
 * Return a setting value from the global instance
 *
 * alias of router_plugin()->get_setting()
 *
 * @param	string	$name	Name of setting
 * @return	mixed
 */
function router_get_setting( string $name ) {
	return router_plugin()->get_setting( $name );
}

/**
 * Update a setting value from the global instance
 *
 * alias of router_plugin()->update_setting()
 *
 * @param	string	$name	Name of setting
 * @param	mixed	$value
 * @return	boolean			Always returns true
 */
function router_update_setting( string $name, $value ) {
	return router_plugin()->update_setting( $name, $value );
}
/**
 * Include file from relative path
 *
 * @param	string	$path
 * @return	void
 */
function router_include( string $path ) {
	$path = router_get_path( $path );

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
function router_require( string $path ) {
	$path = router_get_path( $path );

	if ( file_exists( $path ) ) {
		require $path;
	}
}


