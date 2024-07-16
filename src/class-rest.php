<?php
/**
 * REST API-related features for the plugin.
 *
 * @package jeherve/color-posts
 */

declare( strict_types=1 );

namespace Jeherve\Color_Posts;

use WP_Error;

/**
 * Surfacing the colors in the REST API.
 */
class Rest {
	/**
	 * Add Colors to REST API Post responses.
	 *
	 * @since 1.3.0
	 */
	public function register_colors() {
		// List all public post types.
		$post_type_args = array(
			'public' => true,
		);
		$post_types     = get_post_types( $post_type_args );

		register_rest_field(
			$post_types,
			'colors',
			array(
				'get_callback'    => array( $this, 'get_colors' ),
				'update_callback' => array( $this, 'update_colors' ),
				'schema'          => null,
			)
		);
	}

	/**
	 * Get the colors for the API.
	 *
	 * @since 1.3.0
	 *
	 * @param array            $object Details of current post.
	 * @param string           $field_name Name of field.
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return array $colors Array of colors stored for that Post ID.
	 */
	public function rest_get_colors( $object, $field_name, $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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
	public function rest_update_colors( $color, $object, $field_name ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$color = Utilities::sanitize_hex_color_no_hash( $color );

		if ( ! isset( $color ) || ! empty( $color ) ) {
			return new WP_Error( 'bad-post-color', __( 'The specified color is in an invalid format.', 'color-posts' ) );
		}

		$colors = array(
			'color'    => $color,
			'contrast' => Utilities::get_contrast( $color ),
			'custom'   => true,
		);

		return update_post_meta( $object->ID, '_post_colors', $colors );
	}
}
