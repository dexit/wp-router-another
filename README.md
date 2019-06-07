# Basic URL router for WordPress
Easily create custom WordPress routes with this class and helper functions.

\* Still a work in progress, and not tested with [networks/multisites]([https://wordpress.org/support/article/create-a-network/](https://wordpress.org/support/article/create-a-network/))

## Requirements

**PHP** Version 7.2+
**WordPress** Version 5.2+

## Installation
Download and place the `class-WP_Router.php` file in your theme folder. Then include in your `functions.php` file like so...

```php
<?php
// functions.php

require_once get_template_directory() . '/path-to/class-WP_Router.php';	

// ...
```

## Functions

### wp_router_register()
`wp_router_register( string $method, string $route, callable $callback, array $options  )`

#### Description
Register a route with a specific request method.

#### Parameters
`$method`
*(string) (Required) The type of method to for the route to match. Accepts GET, POST, HEAD, PUT, DELETE, ANY.*

`$route`
*(string) (Required) The url route to match. This can include catchable parameters by using the `:` operator.*
*For example `/locations/:country/:city` and `/:author/books`*

`$callback`
*(callable) (Required) The name of the function you wish to be called when the route is matched.*

`$options`
*(array) (Optional) Additional parameters to passed to the route object.*

#### Usage
```php
wp_router_register( 'POST', '/profile', function( $params ) {
	// Do something with $_POST data
} );

...

wp_router_register( 'GET', '/movies/:year', function( $params ) {
	get_template_part( 'movies', $params[ 'year' ] );
} );
```

#### Aliases

| Alias | Params | Same as |
|--|--|--|
| `wp_router_get()` | $route, $callback, $options | `wp_router_register( 'GET', ... )` |
| `wp_router_post()` | $route, $callback, $options | `wp_router_register( 'POST', ... )` |
| `wp_router_head()` | $route, $callback, $options | `wp_router_register( 'HEAD', ... )` |
| `wp_router_put()` | $route, $callback, $options | `wp_router_register( 'PUT', ... )` |
| `wp_router_delete()` | $route, $callback, $options | `wp_router_register( 'DELETE', ... )` |

### wp_router_params()
`wp_router_params()`

#### Description

Get an array of route parameters if any.

#### Usage
```php
// Register route
wp_router_get( '/path/:param1/:param2/:third', 'route_handler' );

// Call on route match
function route_handler() {
	$params = wp_router_params();
	echo $params[ 'param1' ];
	echo $params[ 'param2' ];
	echo $params[ 'third' ];
}
```

## Route options and permissions
There are additional options that can be passed in the `$options` parameter.

| Option | Description | Default |
|--|--|--|
| `robots` | *(bool) Add nofollow and noindex to the request page header.* | `false` |
| `private` | *(bool) Use this option to expose the route to authenticated users only.* | `false` |
| `capabilities` | *(string) Used in conjunction with `private` option. Only allow specific roles or capabilities to access the route.* | `'manage_options'` |

### Usage
```php
wp_router_get( '/profile', 'route_handler', [
	'robots' => true, // Stop bots
	'private' => true, // Only authed users
	'capabilities' => 'subscriber' // Subscribers only
] );

function route_handler() {
	// Do something...
}
```
