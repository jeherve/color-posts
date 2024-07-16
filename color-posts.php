<?php
/**
 * Plugin Name: Color Posts
 * Plugin URI: http://wordpress.org/plugins/color-posts/
 * Description: Color Posts changes the background color of your post to match the image you've inserted into that post.
 * Author: Jeremy Herve
 * Version: 2.0.0
 * Author URI: https://jeremy.hu
 * License: GPL2+
 * Text Domain: color-posts
 * Requires at least: 5.6
 * Requires PHP: 7.1
 *
 * @package jeherve/color-posts
 */

declare( strict_types=1 );

namespace Jeherve\Color_Posts;

$color_posts_autoloader = plugin_dir_path( __FILE__ ) . 'vendor/autoload_packages.php';
if ( is_readable( $color_posts_autoloader ) ) {
	require $color_posts_autoloader;
}

/**
 * Load the plugin.
 */
function load() {
	/**
	 * Add Theme support for Tonesque
	 *
	 * @uses add_theme_support()
	 *
	 * @since 1.0
	 */
	add_action(
		'after_setup_theme',
		function () {
			add_theme_support( 'tonesque' );
		},
		9
	);

	// Load Toneque.
	add_action( 'init', array( '\\Automattic\Jetpack\Classic_Theme_Helper\Main', 'jetpack_load_theme_tools' ) );

	// Add colors to REST API Post response.
	add_action( 'rest_api_init', array( __NAMESPACE__ . '\\Rest', 'register_colors' ) );

	// Admin interface.
	add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\\Admin', 'enqueue_admin_scripts' ) );
	add_action( 'add_meta_boxes', array( __NAMESPACE__ . '\\Admin', 'add_metabox' ) );
	add_action( 'save_post', array( __NAMESPACE__ . '\\Admin', 'save_metabox' ) );
	add_action( 'save_post', array( __NAMESPACE__ . '\\Admin', 'post_meta_flusher' ) );

	// Frontend display.
	add_action( 'wp_head', array( __NAMESPACE__ . '\\Frontend', 'build_css' ) );
	add_filter( 'colorposts_css_tag', array( __NAMESPACE__ . '\\Frontend', 'color_meta_tag' ), 10, 3 );
	add_action( 'amp_post_template_css', array( __NAMESPACE__ . '\\Frontend', 'color_amp_template' ) );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load' );
