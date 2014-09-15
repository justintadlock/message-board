<?php

function mb_get_allowed_shortcodes() {
	$allowed = array( 'embed', 'wp_caption', 'caption', 'gallery', 'playlist', 'audio', 'video' );
	return apply_filters( 'mb_allowed_shortcodes', $allowed );
}

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
