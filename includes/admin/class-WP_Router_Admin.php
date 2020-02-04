<?php

/**
 *
 */
class WP_Router_Admin {

	/**
	 * The admin page title
	 *
	 * @var string
	 */
	protected $title = 'WP Router';

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

		// Register settings
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_settings() {

		/**
		 * @see		https://developer.wordpress.org/reference/functions/register_setting/
		 *
		 * @param	string		$option_group	A settings group name. Should correspond to a white listed option key name. Default white listed option key names include "general," "discussion," and "reading," among others.
		 * @param	string		$option_name	The name of an option to sanitize and save.
		 * @param	array		$args			Data used to describe the setting when registered.
		 */
		register_setting( $this->slug, 'route-settings-options', function( $input ) {
			$route_id = !empty( $_POST[ 'route-id' ] ) ? sanitize_title( $_POST[ 'route-id' ] ) : false;
			if ( is_array( $input ) ) {
				$input = array_map( 'sanitize_text_field', $input );
				update_option( $route_id, $input, false );
			}

			return null;
		} );

		/**
		 * @see		https://codex.wordpress.org/Function_Reference/add_settings_section
		 *
		 * @param	string		$id			String for use in the 'id' attribute of tags.
		 * @param	string		$title		Title of the section.
		 * @param	callable	$callback	Function that fills the section with the desired content. The function should echo its output.
		 * @param	string		$page		The menu page on which to display this section. Should match $menu_slug from Function Reference/add theme page if you are adding a section to an 'Appearance' page, or Function Reference/add options page if you are adding a section to a 'Settings' page.
		 */
		add_settings_section( 'route-settings', __( 'Route settings', 'wprouter' ), function() {
			echo 'Add a page title to this route. Accessible via `the_title()` and `get_the_title()` functions.';
		}, $this->slug );

		/**
		 * @see		https://codex.wordpress.org/Settings_API#Adding_Setting_Fields
		 *
		 * @param	string		$id 		String for use in the 'id' attribute of tags.
		 * @param	string		$title		Title of the field.
		 * @param	callable	$callback	Function that fills the field with the desired inputs as part of the larger form. Name and id of the input should match the $id given to this function. The function should echo its output.
		 * @param	string		$page		The type of settings page on which to show the field (general, reading, writing, ...).
		 * @param	string		$section	The section of the settings page in which to show the box (default or a section you added with add_settings_section, look at the page in the source to see what the existing ones are.)
		 * @param	array		$args		Extra arguments passed into the callback function
		 */
		add_settings_field( 'title', __( 'Route title', 'wprouter' ), function() {
			echo '<input type="text" name="route-settings-options[title]" style="width: 100%; max-width: 300px;">';
		}, $this->slug, 'route-settings' );

	}

	/**
	 * Register admin pages
	 *
	 * @return	void
	 */
	public function admin_menu() {
		add_menu_page( $this->title, $this->title, wp_router_plugin()->get_setting( 'capability' ), $this->slug, [ $this, 'render_admin' ], 'dashicons-randomize', 99 );
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
				<h1><?= $this->title; ?></h1>
				<hr>
				<form method="post" action="options.php">
					<?php
						if ( $route_id ) {
							include 'views/route.php';
						} else {
							include 'views/dashboard.php';
						}
					?>
				</form>
			</main>
		<?php
	}

}

wp_router_plugin()->admin = new WP_Router_Admin;
