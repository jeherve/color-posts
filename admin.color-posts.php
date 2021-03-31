<?php
/**
 * Overwrite Colors from the dashboard.
 *
 * @since 1.5
 */

/**
 * Enqueue Color Picker on Post and Page editor.
 *
 * @since 1.5
 */
function colorposts_enqueue_admin_scripts( $hook ) {
	if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}
}
add_action( 'admin_enqueue_scripts', 'colorposts_enqueue_admin_scripts' );

/**
 * Register Meta box.
 *
 * @since 1.5
 *
 * @uses add_meta_box()
 */
function colorposts_add_metabox() {
	$post_types = get_post_types( array( 'public' => true ) );

	add_meta_box(
		'colorposts-color',
		__( 'Post Color', 'color-posts' ),
		'colorposts_display_metabox',
		(array) $post_types,
		'side',
		'low'
	);
}
add_action( 'add_meta_boxes', 'colorposts_add_metabox' );

/**
 * Display the Meta box.
 *
 * @since 1.5
 *
 * @param WP_Post $post Current post object.
 */
function colorposts_display_metabox( $post ) {
	// Get our Post color
	$post_color = colorposts_get_average_color( $post->ID );

	if ( ! empty( $post_color ) ) {
		$post_color = $post_color;
	} else {
		/**
		 * Filter the default color used when no image has been selected yet.
		 *
		 * @since 1.5
		 *
		 * @param string $post_color Default Post color.
		 */
		$post_color = apply_filters( 'colorposts_default_color', 'fff' );
	}

	printf(
		'<script type="text/javascript">
			jQuery(document).ready(function($){
				var colorpostsOptions = {
					defaultColor: "#%1$s",
					change: function(event, ui) {
						document.getElementById("color-posts-input").value = "#%1$s";
					},
					palettes: true
				};
				$(".color-picker-hex").wpColorPicker(colorpostsOptions);
				$(".wp-color-result").css( "background-color", "#%1$s" );
			});
		</script>
		<input id="color-posts-input" class="color-picker-hex" type="text" maxlength="7" placeholder="%2$s" name="colorposts_custom_color" />
		<p class="hide-if-no-js howto">%3$s</p>',
		esc_attr( $post_color ),
		esc_attr__( 'Select Color', 'color-posts' ),
		esc_html__( 'Define your own custom color', 'color-posts' )
	);
}

/**
 * To do
 * Value must not be set so we don't resave post meta on each save_post
 * but when value is not set, the color picker doesn't look good, doesn't include the value, even if a default color is set.
 *
 * make sure we recalculate a contrast color
 * make sure we don't overwrite the default when nothing is done (new post and stuff)
 *
 * use Jetpack Custom CSS to create palettes of colors to add to the color picker:
 * 	palettes: ['#125', '#459', '#78b', '#ab0', '#de3', '#f0f']
 */

/**
 * Save the Meta box content.
 *
 * @since 1.5
 *
 * @param int $post_id Post ID.
 */
function colorposts_save_metabox( $post_id ) {
	if (
		( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return $post_id;
	}

	if ( isset( $_POST['colorposts_custom_color'] ) ) {
		$post_color = colorposts_get_average_color( $post_id );

		// No need to do anything if the color hasn't changed.
		if ( $post_color == $_POST['colorposts_custom_color'] ) {
			return $post_id;
		} else {
			// Build our new array of custom colors.
			$colors = array(
				'color'    => sanitize_hex_color_no_hash( $_POST['colorposts_custom_color'] ),
				'contrast' => colorposts_get_contrast( $_POST['colorposts_custom_color'] ),
				'custom'   => true,
			);

			// Update post meta.
			update_post_meta( $post_id, '_post_colors', $colors );
		}
	}

	return $post_id;
}
add_action( 'save_post', 'colorposts_save_metabox' );
