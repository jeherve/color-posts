<?php
/**
 * Multiple functions used in different areas of the plugin.
 * They're used to get and update data about Post Colors.
 *
 * You can use them in your own plugins, too.
 *
 * - colorposts_get_post_image( $post_id )  --   Get an image from a post.
 * - colorposts_calculate_colors( $post_id ) --   Calculate Average and Contrast color.
 * - colorposts_get_average_color( $post_id ) -- Get the average color for a specific post.
 * - colorposts_get_contrast( $color )        -- Get a contrast color from an average color.
 */

/**
 * Get an image from a post.
 *
 * @uses Jetpack_PostImages::get_image( $post_id ) to get the source of an image in a post, apply_filters()
 *
 * @since 1.0
 *
 * @param int $post_id Post ID.
 *
 * @return string $the_image the image source
 */
function colorposts_get_post_image( $post_id ) {
	$post_id = get_the_ID();

	if ( class_exists( 'Jetpack_PostImages' ) ) {
		$the_image = Jetpack_PostImages::get_image( $post_id );
		if ( ! empty( $the_image['src'] ) ) {
			$the_image = $the_image['src'];
		} else {
			/** This filter is documented in jetpack/functions.opengraph.php */
			$the_image = apply_filters( 'jetpack_open_graph_image_default', "http://wordpress.com/i/blank.jpg" );
		}
	}

	$the_image = apply_filters( 'colorposts_image_output', $the_image );

	return esc_url( $the_image );
}

/**
 * Calculate Average and Contrast color.
 *
 * @since 1.5
 *
 * @param int $post_id Post ID.
 *
 * @return array|null $tonesque Array of post colors.
 */
function colorposts_calculate_colors( $post_id ) {
	$post_image = colorposts_get_post_image( $post_id );

	$tonesque = new Tonesque( $post_image );
	$tonesque = array(
		'color'    => $tonesque->color(),
		'contrast' => $tonesque->contrast(),
		'custom'   => false,
	);

	if ( $tonesque['color'] ) {
		update_post_meta( $post_id, '_post_colors', $tonesque );
		return $tonesque;
	} else {
		return;
	}
}

/**
 * Get the average color for a specific post.
 *
 * @since 1.5
 *
 * @param int $post_id Post ID.
 *
 * @return string|false $post_color HEX color code, without the hashtag.
 */
function colorposts_get_average_color( $post_id ) {
	$colors = get_post_meta( $post_id, '_post_colors', true );
	if ( ! empty( $colors ) ) {
		return $colors['color'];
	} else {
		return false;
	}
}

/**
 * Get a contrast color from an average color.
 *
 * @since 1.5
 *
 * @param string $color HEX color code, without the hashtag.
 *
 * @return string|false $contrast Contrast color, in RGB.
 */
function colorposts_get_contrast( $color ) {
	if ( ! defined( 'JETPACK__PLUGIN_DIR' ) ) {
		return false;
	}

	$jetpack_color_lib = JETPACK__PLUGIN_DIR . '/_inc/lib/class.color.php';
	if ( is_readable( $jetpack_color_lib ) ) {
		require_once $jetpack_color_lib;
	} else {
		return false;
	}

	$c = new Jetpack_Color( $color, 'hex' );
	if ( ! $c ) {
		return false;
	}

	$c = $c->getMaxContrastColor();
	return implode( ',', $c->toRgbInt() );
}

/**
 * Flush out the post meta used in colorposts_build_css().
 *
 * Only happens when the post meta was set by Tonesque itself.
 * If it was set manually, via the color picker, we don't want it to be deleted.
 *
 * @since 1.0
 *
 * @uses get_post_meta(), isset(), delete_post_meta()
 * @param int $post_id The ID of the saved post.
 */
function colorposts_post_meta_flusher( $post_id ) {
	$colors = get_post_meta( $post_id, '_post_colors', true );
	if ( ! isset( $colors['custom'] ) || false == $colors['custom'] ) {
		delete_post_meta( $post_id, '_post_colors' );
	}
}
add_action( 'save_post', 'colorposts_post_meta_flusher' );

/**
 * Sanitize Color Hex code
 *
 * @since 1.5.1
 */
function colorposts_sanitize_hex_color_no_hash( $color ) {
	$color = ltrim( $color, '#' );

	if ( '' === $color ) {
		return;
	}

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match('|^([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}
}
