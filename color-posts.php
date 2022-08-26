<?php
/*
 * Plugin Name: Color Posts
 * Plugin URI: http://wordpress.org/plugins/color-posts/
 * Description: Color Posts changes the background color of your post to match the image you've inserted into that post.
 * Author: Jeremy Herve
 * Version: 1.6.2
 * Author URI: https://jeremy.hu
 * License: GPL2+
 * Text Domain: color-posts
 * Domain Path: /languages
 */

define( 'COLORPOSTS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

class Jeherve_Color_Posts {
	private static $instance;

	static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Jeherve_Color_Posts;
		}
		return self::$instance;
	}

	private function __construct() {
		// Load plugin
		add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
	}

	public function load_plugin() {
		// Check if Jetpack is active
		if ( class_exists( 'Jetpack' ) ) {

			// Load our functions.
			require_once( COLORPOSTS__PLUGIN_DIR . 'functions.color-posts.php' );
			require_once( COLORPOSTS__PLUGIN_DIR . 'utilities.color-posts.php' );

			// Add a meta box in the post editor.
			if ( is_admin() ) {
				require_once( COLORPOSTS__PLUGIN_DIR . 'admin.color-posts.php' );
			}

			// Add colors to REST API Post response
			if ( function_exists( 'register_rest_field' ) ) {
				add_action( 'rest_api_init',  array( $this, 'rest_register_colors' ) );
			}

		} else {

			add_action( 'admin_notices',  array( $this, 'install_jetpack' ) );

		}
	}

	/**
	 * Prompt to install Jetpack.
	 *
	 * @since 1.0
	 */
	public function install_jetpack() {
		echo '<div class="error"><p>';
		printf( __( 'To use the Color Posts plugin, you\'ll need to install and activate <a href="%s">Jetpack</a> first.', 'color-posts' ),
		'plugin-install.php?tab=search&s=jetpack&plugin-search-input=Search+Plugins'
		);
		echo '</p></div>';
	}

	/**
	 * Add Colors to REST API Post responses.
	 *
	 * @since 1.3.0
	 */
	public function rest_register_colors() {
		// List all public post types.
		$post_type_args = array(
			'public'   => true,
		);
		$post_types = get_post_types( $post_type_args );

		register_rest_field( $post_types,
			'colors',
			array(
				'get_callback'    => array( $this, 'rest_get_colors' ),
				'update_callback' => array( $this, 'rest_update_colors' ),
				'schema'          => null,
			)
		);
	}

	/**
	 * Get the colors for the API.
	 *
	 * @since 1.3.0
	 *
	 * @param array $object Details of current post.
	 * @param string $field_name Name of field.
	 * @param WP_REST_Request $request Current request
	 *
	 * @return array $colors Array of colors stored for that Post ID.
	 */
	public function rest_get_colors( $object, $field_name, $request ) {
		return get_post_meta( $object['id'], '_post_colors', true );
	}

	/**
	 * Update colors from the API.
	 *
	 * Only accepts a single color hex value, without a hash.
	 * "Contrast" is calculated based on that color.
	 * "Custom" is always true, since color is defined manually.
	 *
	 * @since 1.5
	 *
	 * @param string $color New average color value. hex value, without a hash.
	 * @param object $object The object from the response.
	 * @param string $field_name Name of field.
	 *
	 * @return bool|int
	 */
	public function rest_update_colors( $color, $object, $field_name ) {
		$color = colorposts_sanitize_hex_color_no_hash( $color );

		if ( ! isset( $color ) || ! empty( $color ) ) {
			return new WP_Error( 'bad-post-color', __( 'The specified color is in an invalid format.', 'color-posts' ) );
		}

		$colors = array(
			'color'    => $color,
			'contrast' => colorposts_get_contrast( $color ),
			'custom'   => true,
		);

		return update_post_meta( $object->ID, '_post_colors', $colors );
	}
}
// And boom.
Jeherve_Color_Posts::get_instance();
