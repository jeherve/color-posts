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
		} else {
			$the_image = apply_filters( 'jetpack_open_graph_image_default', "http://wordpress.com/i/blank.jpg" );
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
	if ( is_singular() ) {

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

			/**
			 * Filters the CSS inserted in the head.
			 *
			 * @since 1.1
			 *
			 * @param string $colors_css CSS code.
			 * @param string $color      HEX color code, without the hashtag.
			 * @param string $contrast   Contrast matching the post. Either black or white. Defined in RGB.
			 */
			$custom_css .= apply_filters( 'colorposts_css_output', $colors_css, $color, $contrast );
			$custom_css .= "\n</style>\n";

			/**
			 * Filters the complete CSS output, including the style tags.
			 *
			 * @since 1.2
			 *
			 * @param string $custom_css Custom CSS output.
			 * @param string $color      HEX color code, without the hashtag.
			 * @param string $contrast   Contrast matching the post. Either black or white. Defined in RGB.
			 */
			echo apply_filters( 'colorposts_css_tag', $custom_css, $color, $contrast );
		}

	} // End is_single()

}
add_action( 'wp_head', 'colorposts_build_css' );

/**
 * Add a theme-color meta tag to the head
 *
 * This meta tag allows Android to change the color of the address bar.
 * This will now match the post color.
 *
 * @since 1.2
 *
 * @return string $custom_css Custom CSS output and our extra color tag if the color is set.
 */
function colorposts_color_meta_tag( $custom_css, $color, $contrast ) {

	// Bail early if no color is set.
	if ( ! $color || empty( $color ) ) {
		return $custom_css;
	}

	// Create our tag.
	$color_tag = sprintf(
		'<meta name="theme-color" content="#%1$s" />%2$s',
		$color,
		"\n"
	);

	// Add the tag below the Custom CSS
	return $custom_css . $color_tag;
}
add_filter( 'colorposts_css_tag', 'colorposts_color_meta_tag', 10, 3 );

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
