<?php

/**
* Add Theme support for Tonesque
*
* @uses add_theme_support()
*
* @since Color Posts 1.0
*/
function colorposts_call_tonesque() {
	add_theme_support( 'tonesque' );
}
add_action( 'after_setup_theme', 'colorposts_call_tonesque', 9 );


/**
* Get an image from a post
*
* @uses Jetpack_PostImages::get_image( $post_id ) to get the source of an image in a post, apply_filters()
*
* @since Color Posts 1.0
*
* @return string the image source
*/
function colorposts_get_post_image() {
	$post_id = get_the_ID();

	if ( class_exists( 'Jetpack_PostImages' ) ) {
		$the_image = Jetpack_PostImages::get_image( $post_id );
		if ( ! empty( $the_image['src'] ) ) {
			$the_image = $the_image['src'];
		}
	}

	$the_image = apply_filters( 'colorposts_image_output', $the_image );

	return esc_url( $the_image );
}


/**
* Build CSS from Tonesque
*
* @uses get_the_ID(), is_single(), get_post_meta(), colorposts_get_post_image(), update_post_meta(), apply_filters()
*
* @since Color Posts 1.0
*/
function colorposts_build_css() {
	$post_id = get_the_ID();

	// Only add color if a single page is displayed
	if ( is_single() ) {

		// Grab color from post meta
		$tonesque = get_post_meta( $post_id, '_post_colors', true );

		// No color? Let's get one
		if ( empty( $tonesque ) ) {
			$post_image = colorposts_get_post_image( $post_id );

			$tonesque = new Tonesque( $post_image );
			$tonesque = array(
				'color'    => $tonesque->color(),
				'contrast' => $tonesque->contrast(),
			);

			if ( $tonesque['color'] ) {
				update_post_meta( $post_id, '_post_colors', $tonesque );
			} else {
				return;
			}
		}

		// Add the CSS to our page
		extract( $tonesque );
		if ( empty( $color ) || empty( $contrast ) ) {
			return;
		} else {
			$custom_css = "\n<style type='text/css'>\n";

			$colors_css = "body { background: #{$color} !important; }";

			$custom_css .= apply_filters( 'colorposts_css_output', $colors_css, $color, $contrast );
			$custom_css .= "\n</style>\n";

			echo $custom_css;
		}

	} // End is_single()

}
add_action( 'wp_head', 'colorposts_build_css' );

/**
* Flush out the post meta used in colorposts_build_css().
*
* @uses delete_post_meta()
* @param int $post_id The ID of the saved post.
*
* @since Color Posts 1.0
*/
function colorposts_post_meta_flusher( $post_id ) {
	delete_post_meta( $post_id, '_post_colors' );
}
add_action( 'save_post', 'colorposts_post_meta_flusher' );
