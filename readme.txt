=== Color Posts ===
Contributors: jeherve
Tags: Colors, Posts, Tonesque, Jetpack, Theme
Stable tag: 1.6.2
Requires at least: 4.7
Tested up to: 6.0

Color Posts changes the background color of your post to match the image you've inserted into that post.

== Description ==

Color Posts grabs an image from your post, samples it, creates a matching color and a contrast value (black or white), and changes the post's background color to match the image.
It also changes the background color of your window in Chrome for Android, and customizes the look of the AMP default template if you use [the AMP plugin](https://wordpress.org/plugins/amp/).

If you're not happy with the automatically picked color, you can define your own custom color thanks to the "Post Color" meta box in the post editor.

If you'd like to use post colors via the REST API, you can use the `colors` field in the post response.

Important: for this plugin to work, you must activate [Jetpack](http://wordpress.org/plugins/jetpack/) first.

If you find bugs, you can report them [here](http://wordpress.org/support/plugin/color-posts), or submit a pull request [on GitHub](https://github.com/jeherve/color-posts/).

You can read more about how the plugin works here: [Color Posts plugin: how to use Jetpack libs in your plugin](http://jeremy.hu/color-posts-jetpack-plugin/)

== Installation ==

1. Install the Jetpack plugin
2. Install the Color Posts plugin via the WordPress.org plugin repository, or via your dashboard
3. Activate the plugin
4. Enjoy! :)

== Frequently Asked Questions ==

= I don't want to change the background color on my site, but I'd like to use this plugin to change other elements on my site =

No problem! You can build your own CSS from the colors grabbed by the plugin, like so:

`function jeherve_use_custom_colors( $colors_css, $color, $contrast ) {
	$post_id = get_the_ID();

	$tonesque = get_post_meta( $post_id, '_post_colors', true );
	extract( $tonesque );

	$colors_css = ".post a {
		color: #{$color};
	}";

	return $colors_css;
}
add_filter( 'colorposts_css_output', 'jeherve_use_custom_colors', 10, 3 );`

= I don't want Jetpack to choose the base image for me. I want to use my own image =

You can do so thanks to the `colorposts_image_output` filter, like so:

`function jeherve_use_custom_image( $the_image ) {
	$the_image = 'http://f.cl.ly/items/3z0o2k2F3w0D0p0k1Y35/cool.gif'; // Whatever image you want, you could grab it from a custom field
	return esc_url( $the_image );
}
add_filter( 'colorposts_image_output', 'jeherve_use_custom_image' );`

= How do I change the default color that is assigned to posts without an image =

You can define a default image that will be used to create a color for all posts that don't include an image.
To do so, you can use the `jetpack_open_graph_image_default` filter, like so:

`function jeherve_custom_colors_default_img( $the_image ) {
	$the_image = 'http://f.cl.ly/items/3z0o2k2F3w0D0p0k1Y35/cool.gif'; // Whatever image you want, you could grab it from a custom field
	return esc_url( $the_image );
}
add_filter( 'jetpack_open_graph_image_default', 'jeherve_custom_colors_default_img' );`

== Changelog ==

= 1.6.2 =
Release Date: August 26, 2022

* Compatibility: update how the plugin relies on functionality provided by the Jetpack plugin, to ensure full compatibility with upcoming versions of the Jetpacak plugin.

= 1.6.1 =
Release Date: March 31, 2021

* Internationalization: remove custom translation implementation, WordPress' language packs handle everything these days.
* Custom contrast value: avoid issues when saving new value.
* Ensure the values saved and displayed are always as expected.

= 1.6 =

* Admin interface: add the meta box to all public post types.

= 1.5.2 =

* Fix error when the WP REST API plugin wasn't installed on a site.

= 1.5.1 =
* Fix Fatal Error on post update.

= 1.5 =
* You can now set your own custom color if you don't like the one that's automatically calculated from the post image.
	* You can do so from a color picker in the post and page editor.
	* If you don't touch the color picker, the plugin keeps using the post image to calculate a post color.
	* As soon as you touch the color picker, the plugin uses that color instead.
* Refactoring to accommodate the new admin interface.
* REST API: Allow defining custom color via the API.

= 1.4 =
* Add colors to AMP's Default template if you use [the AMP plugin](https://wordpress.org/plugins/amp/).

= 1.3 =
* Add colors to REST API Post response.

= 1.2 =
* Add background color to Chrome for Android.
* Display custom colors on pages as well.

= 1.1 =
* Add default fallback image and documentation.

= 1.0 =
* Initial release.
