<?php
/**
 * Frontend elements in the plugin.
 *
 * @package jeherve/color-posts
 */

declare( strict_types=1 );

namespace Jeherve\Color_Posts;

/**
 * Displaying the colors on the frontend.
 */
class Frontend {
	/**
	 * Build CSS from Tonesque
	 *
	 * @uses get_the_ID(), is_single(), get_post_meta(), colorposts_get_post_image(), update_post_meta(), apply_filters()
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function build_css() {
		// Only add color if a single page is displayed
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		// Grab color from post meta
		$tonesque = get_post_meta( $post_id, '_post_colors', true );

		// No color? Let's get one
		if ( empty( $tonesque ) ) {
			$tonesque = Utilities::calculate_colors( $post_id );
		}

		// Add the CSS to our page
		$color    = $tonesque['color'] ?? '';
		$contrast = $tonesque['contrast'] ?? '';

		if ( empty( $color ) || empty( $contrast ) ) {
			return;
		}

		$color    = esc_attr( $color );
		$contrast = esc_attr( $contrast );

		$custom_css = "\n<s type='text/css'>\n";
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
		echo apply_filters( 'colorposts_css_tag', $custom_css, $color, $contrast ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this is raw HTML, generated manually above.
	}

	/**
	 * Add a theme-color meta tag to the head
	 *
	 * This meta tag allows Android to change the color of the address bar.
	 * This will now match the post color.
	 *
	 * @since 1.2
	 *
	 * @param string $custom_css Custom CSS output.
	 * @param string $color      HEX color code, without the hashtag.
	 * @param string $contrast   Contrast matching the post. Either black or white. Defined in RGB.
	 *
	 * @return string $custom_css Custom CSS output and our extra color tag if the color is set.
	 */
	public function color_meta_tag( $custom_css, $color, $contrast ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

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

	/**
	 * Change the look of the AMP default template.
	 *
	 * Useful if you use the AMP Plugin: https://wordpress.org/plugins/amp/
	 * We'll change the title bar to match the post color.
	 *
	 * @since 1.4
	 *
	 * @return void
	 */
	public function color_amp_template() {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		// Grab color from post meta
		$tonesque = get_post_meta( $post_id, '_post_colors', true );
		$color    = $tonesque['color'] ?? '';
		$contrast = $tonesque['contrast'] ?? '';

		if ( empty( $color ) || empty( $contrast ) ) {
			return;
		}

		// Use the colors to change the look of AMP's title bar.
		printf(
			'nav.amp-wp-title-bar {
				background-color:#%1$s;
			}
			nav.amp-wp-title-bar a {
				color: rgba(%2$s,1);
			}
			',
			esc_attr( $color ),
			esc_attr( $contrast )
		);
	}
}
