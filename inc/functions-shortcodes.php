<?php

/**
 * Returns an array of allowed shortcodes. By default, only the WordPress-bundled shortcodes are 
 * allowed.  Note that auto-embeds are handled separately.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_allowed_shortcodes() {
	$allowed = array( 'embed', 'wp_caption', 'caption', 'gallery', 'playlist', 'audio', 'video' );

	return apply_filters( 'mb_allowed_shortcodes', $allowed );
}

/**
 * Content filter that removes all shortcodes and only allows allowed shortcodes to be run.  This is a 
 * wrapper for the `do_shortcode()` function.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $content
 * @return string
 */
function mb_do_shortcode( $content ) {
	global $shortcode_tags;

	$temp = $shortcode_tags;

	foreach ( $shortcode_tags as $tag => $func ) {

		if ( !in_array( $tag, mb_get_allowed_shortcodes() ) )
			remove_shortcode( $tag );
	}

	$content = do_shortcode( $content );

	$shortcode_tags = $temp;

	return $content;
}

/**
 * Content filter for only "un-auto-p'ing" allowed shortcodes.  This is a wrapper for `shortcode_unautop()`.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $content
 * @return string
 */
function mb_shortcode_unautop( $content ) {
	global $shortcode_tags;

	$temp = $shortcode_tags;

	foreach ( $shortcode_tags as $tag => $func ) {

		if ( !in_array( $tag, mb_get_allowed_shortcodes() ) )
			remove_shortcode( $tag );
	}

	$content = shortcode_unautop( $content );

	$shortcode_tags = $temp;

	return $content;
}
