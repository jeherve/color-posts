=== Color Posts ===
Contributors: jeherve
Tags: Colors, Posts, Tonesque, Jetpack, Theme
Stable tag: 1.0
Requires at least: 3.8
Tested up to: 3.9.2

Color Posts changes the background color of your post to match the image you've inserted into that post.

== Description ==

Color Posts grabs an image from your post, samples it, creates a matching color and a contrast value (black or white), and changes the post's background color to match the image.

Important: for this plugin to work, you must activate [Jetpack](http://wordpress.org/plugins/jetpack/) first

This plugin is a work in progress. You can report issues [here](http://wordpress.org/support/plugin/color-posts), or submit a pull request [on GitHub](https://github.com/jeherve/color-posts/).

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

== Changelog ==

= 1.0 =

* Initial release
