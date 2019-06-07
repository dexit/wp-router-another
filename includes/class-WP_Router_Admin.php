<?php

/**
 *
 */
class WP_Router_Admin {

	/**
	 * The admin page slug
	 *
	 * @var string
	 */
	protected $slug = 'wp-router';

	/**
	 * The full admin page url
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Class constructor
	 */
	function __construct() {

		// Set the admin url
		$this->url = add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) );

		// Add admin page to menu
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Register admin pages
	 *
	 * @return	void
	 */
	public function admin_menu() {
		add_menu_page( 'WP Router', 'WP Router', router_get_setting( 'capability' ), $this->slug, [ $this, 'render_admin' ], 'dashicons-randomize', 99 );
	}

	/**
	 * Render the admin dashboard
	 *
	 * @return	void
	 */
	public function render_admin() {
		$route_id = !empty( $_GET[ 'route' ] ) ? sanitize_text_field( $_GET[ 'route' ] ) : false;
		?>
			<main class="wrap">
				<h1>WP Router</h1>
				<hr>
				<form method="post" action="options.php">
					<?php
						if ( $route_id ) {
							include 'admin/view-route.php';
						} else {
							include 'admin/view-dashboard.php';
						}
					?>
				</form>
			</main>
		<?php
	}

}

router_plugin()->admin = new WP_Router_Admin;
