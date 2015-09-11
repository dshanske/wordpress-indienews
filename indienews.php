<?php
/*
Plugin Name: IndieNews
Plugin URI: https://github.com/pfefferle/wordpress-indienews
Description: Push your IndieWeb articles to the IndieNews page
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
Version: 1.0.0
License: GPL-2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: indienews
Domain Path: /languages
*/

add_action( 'init', array( 'IndieNewsPlugin', 'init' ) );

/**
 * Get the blogs language and check if it supported.
 *
 * @return string The blogs language with a default fallback.
 */
function get_indienews_language() {
	$locale = get_locale();
	$locale = substr( $locale, 0, 2 );

	$supported_languages = apply_filters( 'indienews_supported_languages', array( 'en', 'sv', 'de' ) );

	if ( in_array( $locale, $supported_languages ) ) {
		return $locale;
	} else {
		return apply_filters( 'indienews_default_languages', 'en' );
	}
}

/**
 * Get the link to the correct IndieNews page.
 *
 * @return string The correct URL.
 */
function get_indienews_link() {
	return 'http://news.indiewebcamp.com/' . get_indienews_language();
}

/**
 * Get the IndieNews tag that is used to check if the post
 * is about the IndieWeb or not. Default is "indie".
 *
 * @return string The tag to filter by.
 */
function get_indienews_tag() {
	$tag = apply_filters( 'indienews_tag', 'indie' );

	return preg_quote( $tag );
}

/**
 * IndieNews class.
 *
 * @author Matthias Pfefferle
 */
class IndieNewsPlugin {

	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		// for plugins
		load_plugin_textdomain(
			'indienews', // unique slug
			false, // deprecated
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' // path
		);

		add_filter( 'term_links-post_tag', array( 'IndieNewsPlugin', 'add_indienews_tag_link' ) );
		add_filter( 'webmention_links', array( 'IndieNewsPlugin', 'send_webmentions' ), 10, 2 );
	}

	/**
	 * Add the indienews category.
	 *
	 * @param  array $links The rendered HTML links.
	 *
	 * @return array        updated list of links
	 */
	public static function add_indienews_tag_link( $links ) {
		// check if a post has an indie* tag
		foreach ( $links as $link ) {
			if ( preg_match( '/' . get_indienews_tag() . '/i', $link ) ) {
				$links[] = '<a href="' . get_indienews_link() . '" rel="tag" class="u-category u-tag">#indienews</a>';

				return $links;
			}
		}

		return $links;
	}

	/**
	 * Notify the indienews page.
	 *
	 * @param  array $links   The array of tags of one specific post.
	 * @param  int   $post_ID The ID of the post.
	 *
	 * @return array          Array of updated WebMention links.
	 */
	public static function send_webmentions( $links, $post_ID ) {
		$tags = wp_get_post_tags( $post_ID );

		foreach ( $tags as $tag ) {
			if ( preg_match( '/' . get_indienews_tag() . '/i', $tag->name ) ) {
				$links[] = get_indienews_link();

				return $links;
			}
		}

		return $links;
	}
}