<?php
/**
 * Post template functions.  In this plugin, both "topics" and "replies" are technically custom post types. 
 * This file exists so that we can consolidate some of these template functions into one.  For more-specific 
 * template tags that apply to topics and replies, see `template-topic.php` and `template-post.php`.
 *
 * Technically, you could use WP's built-in functions for getting the data needed because most of these 
 * functions are simply wrappers for those functions.  However, this is discouraged because there are 
 * certain hooks that will be executed when using these functions.
 */

/* ====== Post ID ====== */

function mb_post_id( $post_id = 0 ) {
	echo mb_get_post_id( $post_id );
}

function mb_get_post_id( $post_id = 0 ) {

	if ( is_numeric( $post_id ) && 0 < $post_id )
		$_post_id = $post_id;

	elseif ( in_the_loop() )
		$_post_id = get_the_ID();

	else
		$_post_id = 0;

	return apply_filters( 'mb_get_post_id', $_post_id, $post_id );
}

/* ====== Post Content ====== */

function mb_post_content( $post_id = 0 ) {
	echo mb_get_post_content( $post_id );
}

function mb_get_post_content( $post_id = 0 ) {
	$post_id      = mb_get_post_id( $post_id );
	$post_content = get_post_field( 'post_content', $post_id, 'raw' );

	return apply_filters( 'mb_get_post_content', $post_content, $post_id );
}

/* ====== Post Title ====== */

function mb_post_title( $post_id = 0 ) {
	echo mb_get_post_title( $post_id );
}

function mb_get_post_title( $post_id = 0 ) {
	$post_id    = mb_get_post_id( $post_id );
	$post_title = get_post_field( 'post_title', $post_id );

	return apply_filters( 'mb_get_post_title', $post_title, $post_id );
}

/* ====== Post URL ====== */

function mb_post_url( $post_id = 0 ) {
	echo mb_get_post_url( $post_id );
}

function mb_get_post_url( $post_id = 0 ) {
	$post_id = mb_get_post_id( $post_id );

	return apply_filters( 'mb_get_post_url', get_permalink( $post_id ), $post_id );
}

function mb_post_jump_url( $post_id = 0 ) {
	echo mb_get_post_jump_url( $post_id );
}

/* example.com/board/topics/example/#post-1000 */
function mb_get_post_jump_url( $post_id = 0 ) {
	$post_id = mb_get_post_id( $post_id );

	$url = 'forum_topic' === get_post_type( $post_id ) ? esc_url( trailingslashit( get_permalink( $post_id ) ) . '#post-' . get_the_ID() ) : get_permalink( $post_id );

	return apply_filters( 'mb_get_post_jump_url', $url, $post_id );
}

/* ====== Post Author ====== */

function mb_post_author_id( $post_id = 0 ) {
	echo mb_get_post_author_id( $post_id );
}

function mb_get_post_author_id( $post_id = 0 ) {
	$post_id   = mb_get_post_id( $post_id );
	$author_id = get_post_field( 'post_author', $post_id );

	return apply_filters( 'mb_get_post_author_id', absint( $author_id ), $post_id );
}

function mb_post_author( $post_id = 0 ) {
	echo mb_get_post_author( $post_id );
}

function mb_get_post_author( $post_id = 0 ) {

	$post_id      = mb_get_post_id( $post_id );
	$author_id    = mb_get_post_author_id( $post_id );
	$display_name = get_the_author_meta( 'display_name', $author_id );

	return apply_filters( 'mb_get_post_author_display_name', $display_name, $author_id, $post_id );
}

function mb_post_author_profile_url( $post_id = 0 ) {
	echo mb_get_post_author_profile_url( $post_id );
}

function mb_get_post_author_profile_url( $post_id = 0 ) {
	$post_id     = mb_get_post_id( $post_id );
	$author_id   = mb_get_post_author_id( $post_id );
	$profile_url = mb_get_user_profile_url( $author_id );

	return apply_filters( 'mb_get_post_author_profile_url', $profile_url, $author_id, $post_id );
}

function mb_post_author_profile_link( $post_id = 0 ) {
	echo mb_get_post_author_profile_link( $post_id );
}

function mb_get_post_author_profile_link( $post_id = 0 ) {
	$author_name = mb_get_post_author( $post_id );
	$author_url  = mb_get_post_author_profile_url( $post_id );

	$profile_link = sprintf( '<a class="user-profile-link" href="%s">%s</a>', $author_url, $author_name );

	return apply_filters( 'mb_get_post_author_profile_link', $profile_link, $post_id );
}
