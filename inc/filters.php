<?php

global $wp_embed;

/* Topic/Reply content filters. */
add_filter( 'mb_get_post_content',                   'mb_code_trick',        0 );
add_filter( 'mb_get_post_content', array( $wp_embed, 'run_shortcode' ),      5 );
add_filter( 'mb_get_post_content', array( $wp_embed, 'autoembed'     ),      5 );
add_filter( 'mb_get_post_content',                   'wptexturize',          10 );
add_filter( 'mb_get_post_content',                   'convert_smilies',      15 );
add_filter( 'mb_get_post_content',                   'convert_chars',        20 );
add_filter( 'mb_get_post_content',                   'wpautop',              25 );
add_filter( 'mb_get_post_content',                   'mb_do_shortcode',      30 );
add_filter( 'mb_get_post_content',                   'mb_shortcode_unautop', 35 );
add_filter( 'mb_get_post_content',                   'make_clickable',       40 );

/* Reply title filters. */
add_filter( 'the_title', 'mb_forum_reply_title_filter', 5, 2 );
add_filter( 'post_title', 'mb_forum_reply_title_filter', 5, 2 );

/* Edit post link filters. */
add_filter( 'get_edit_post_link', 'mb_get_edit_post_link', 5, 2 );

/* Capabilities. See `capabilities.php`. */
add_filter( 'map_meta_cap', 'mb_map_meta_cap', 10, 4 );

/* Meta filters. See `meta.php`. */
add_action( 'init',              'mb_register_meta'         );
add_action( 'save_post',         'mb_save_post',      10, 2 );

/* Rewrite filters. See `rewrite.php`. */
add_action( 'init',                      'mb_rewrite_rules',            5     );
add_filter( 'forum_topic_rewrite_rules', 'mb_forum_topic_rewrite_rules'       );
add_filter( 'redirect_canonical',        'mb_redirect_canonical',       10, 2 );

/* Query filters. See `query.php`. */
add_action( 'pre_get_posts',     'mb_pre_get_posts'   );
add_action( 'parse_query',       'mb_parse_query'     );
add_filter( 'template_redirect', 'mb_404_override', 0 );

/* Misc. */
add_filter( 'wp_title',   'mb_wp_title'   );
add_filter( 'body_class', 'mb_body_class' );

/**
 * Filters `wp_title` to handle the title on the forum front page since this is a non-standard WP page.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @return string
 */
function mb_wp_title( $title ) {
	if ( mb_is_forum_front() )
		$title = esc_attr__( 'Forums', 'message-board' );
	elseif ( mb_is_view() )
		$title = esc_attr( mb_get_view_title() );

	return $title;
}

/**
 * Filter on `body_class` to add custom classes for the plugin's pages on the front end.
 *
 * @todo Remove `bbpress` class.
 * @todo Decide on class naming system.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $classes
 * @return array
 */
function mb_body_class( $classes ) {
	global $wp;

	if ( mb_is_message_board() ) {
		$classes[] = 'forum';
		$classes[] = 'bbpress'; // temporary class for compat

		if ( mb_is_forum_front() ) {
			$classes[] = 'forum-front';
		}
	}

	return $classes;
}

function mb_forum_reply_title_filter( $title, $post_id ) {

	if ( 'forum_reply' === get_post_type( $post_id ) ) {
		$post = get_post( $post_id );
		if ( 0 >= $post->post_parent )
			$title = get_the_ID();
		else
			$title = sprintf( __( 'Reply to &ldquo;%s&rdquo;', 'message-board' ), get_post_field( 'post_title', $post->post_parent ) );
	}

	return $title;
}

function mb_get_edit_post_link( $url, $post_id ) {

	$post_type = get_post_type( $post_id );

	if ( 'forum_topic' === $post_type || 'forum_reply' === $post_type ) {

		if ( 'forum_topic' === $post_type ) {
			$topic_link = get_permalink( $post_id );
		} else {
			$post = get_post( $post_id );
			$topic_link = get_permalink( $post->post_parent );
		}

		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {
			$url = trailingslashit( $topic_link ) . 'edit/' . $post_id;

		} else {
			$url = add_query_arg( 'edit', $post_id, $topic_link );
		}

		$url = esc_url( $url );
	}

	return $url;
}
