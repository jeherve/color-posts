<?php
/*
 * Plugin Name: Color Posts
 * Plugin URI: http://wordpress.org/plugins/color-posts/
 * Description: Add a different background color to each one of your posts, based on the images you insert in each post
 * Author: Jeremy Herve
 * Version: 1.0
 * Author URI: http://jeremy.hu
 * License: GPL2+
 * Textdomain: colorposts
 */

class Jeherve_Color_Posts {
	private static $instance;

	static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new Jeherve_Color_Posts;

		return self::$instance;
	}

	private function __construct() {
		// Load translations
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		// Load plugin
		add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'colorposts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function load_plugin() {
		// Check if Jetpack is active
		if ( class_exists( 'Jetpack' ) ) {
			require_once 'functions.jeherve-get-color.php';
		} else {
			add_action( 'admin_notices',  array( $this, 'install_jetpack' ) );
		}
	}

	// Prompt to install Jetpack
	public function install_jetpack() {
		echo '<div class="error"><p>';
		printf( __( 'To use the Color Posts plugin, you\'ll need to install and activate <a href="%s">Jetpack</a> first.', 'colorposts' ),
		'plugin-install.php?tab=search&s=jetpack&plugin-search-input=Search+Plugins'
		);
		echo '</p></div>';
	}
}
// And boom.
Jeherve_Color_Posts::get_instance();
