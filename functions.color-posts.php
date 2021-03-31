<?php
/**
 * Functions used to use the Post Colors in different areas of your site.
 */

/**
 * Add Theme support for Tonesque
 *
 * @uses add_theme_support()
 *
 * @since 1.0
 */
function colorposts_call_tonesque() {
	add_theme_support( 'tonesque' );
}
add_action( 'after_setup_theme', 'colorposts_call_tonesque', 9 );

/**
 * Build CSS from Tonesque
 *
 * @uses get_the_ID(), is_single(), get_post_meta(), colorposts_get_post_image(), update_post_meta(), apply_filters()
 *
 * @since 1.0
 */
function colorposts_build_css() {
	$post_id = get_the_ID();

	// Only add color if a single page is displayed
	if ( is_singular() ) {

		// Grab color from post meta
		$tonesque = get_post_meta( $post_id, '_post_colors', true );

		// No color? Let's get one
		if ( empty( $tonesque ) ) {
			$tonesque = colorposts_calculate_colors( $post_id );
		}

		// Add the CSS to our page
		extract( $tonesque );
		if ( empty( $color ) || empty( $contrast ) ) {
			return;
		} else {
			$color      = esc_attr( $color );
			$contrast   = esc_attr( $contrast );

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
		esc_attr( $color ),
		"\n"
	);

	// Add the tag below the Custom CSS
	return $custom_css . $color_tag;
}
add_filter( 'colorposts_css_tag', 'colorposts_color_meta_tag', 10, 3 );

/**
 * Change the look of the AMP default template.
 *
 * Useful if you use the AMP Plugin: https://wordpress.org/plugins/amp/
 * We'll change the title bar to match the post color.
 *
 * @since 1.4
 *
 * @return null
 */
function colorposts_color_amp_template() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	// Grab color from post meta
	$tonesque = get_post_meta( $post_id, '_post_colors', true );
	if ( ! empty( $tonesque ) ) {
		extract( $tonesque );
	}

	// If we have custom colors, use them to change the look of AMP's title bar.
	if ( ! empty( $color ) && ! empty( $contrast ) ) : ?>
		nav.amp-wp-title-bar {
			background-color:#<?php echo esc_attr( $color ); ?>;
		}
		nav.amp-wp-title-bar a {
			color: rgba(<?php echo esc_attr( $contrast ); ?>,1);
		}
	<?php
	endif;
}
add_action( 'amp_post_template_css', 'colorposts_color_amp_template' );
