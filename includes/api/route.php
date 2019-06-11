<?php

function wp_router_meta( WP_Route $route, ?string $attr = null ) {
	$meta = get_option( $route->id, [] );

	return $meta;
}
