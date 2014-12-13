<?php
/**
 * Default filters/actions run by the plugin.  These mostly deal with filtering WordPress functionality. 
 * See other files for more specific filters.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Topic/Reply content filters. */
$hooks = array( 'mb_get_forum_content', 'mb_get_topic_content', 'mb_get_reply_content' );

global $wp_embed;

foreach ( $hooks as $hook ) {
	add_filter( $hook,                   'mb_code_trick',        0 );
	add_filter( $hook, array( $wp_embed, 'run_shortcode' ),      5 );
	add_filter( $hook, array( $wp_embed, 'autoembed'     ),      5 );
	add_filter( $hook,                   'wptexturize',          10 );
	add_filter( $hook,                   'convert_smilies',      15 );
	add_filter( $hook,                   'convert_chars',        20 );
	add_filter( $hook,                   'wpautop',              25 );
	add_filter( $hook,                   'mb_do_shortcode',      30 );
	add_filter( $hook,                   'mb_shortcode_unautop', 35 );
	add_filter( $hook,                   'make_clickable',       40 );
}

// @todo Use a core hook instead.
$pre_content_hooks = array( 'mb_pre_insert_forum_content', 'mb_pre_insert_topic_content', 'mb_pre_insert_reply_content' );

foreach ( $pre_content_hooks as $hook ) {
	add_filter( $hook, 'mb_encode_bad'       );
	add_filter( $hook, 'mb_code_trick'       );
	add_filter( $hook, 'force_balance_tags'  );
	add_filter( $hook, 'mb_filter_post_kses' );
}

// @todo Use a core hook intead.
$pre_title_hooks = array( 'mb_pre_insert_forum_title', 'mb_pre_insert_topic_title', 'mb_pre_insert_reply_title' );

foreach ( $pre_title_hooks as $hook ) {
	add_filter( $hook, 'strip_tags' );
	add_filter( $hook, 'esc_html'   );
}


/* Reply title filters. */
add_filter( 'the_title', 'mb_forum_reply_title_filter', 5, 2 );
add_filter( 'post_title', 'mb_forum_reply_title_filter', 5, 2 );

/* Edit post link filters. */
add_filter( 'get_edit_post_link', 'mb_get_edit_post_link', 5, 2 );

/* Filter the front-end page title. */
add_filter( 'wp_title',   'mb_wp_title'   );

/* Filter the front-end `<body>` classes. */
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

/**
 * Because replies don't get titles by default, we're going to filter the titles to read 
 * "Reply to: $topic_title".
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @param  int     $post_title
 * @return string
 */
function mb_forum_reply_title_filter( $title, $post_id ) {

	if ( mb_get_reply_post_type() === get_post_type( $post_id ) ) {
		$post = get_post( $post_id );
		if ( 0 >= $post->post_parent )
			$title = get_the_ID();
		else
			$title = sprintf( __( 'Reply to: %s', 'message-board' ), get_post_field( 'post_title', $post->post_parent ) );
	}

	return $title;
}

/**
 * Filters the edit post link for front-end editing.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $url
 * @param  int     $post_id
 */
function mb_get_edit_post_link( $url, $post_id ) {

	if ( is_admin() )
		return $url;

	$post_type = get_post_type( $post_id );

	if ( mb_get_forum_post_type() === $post_type )
		$url = add_query_arg( array( 'mb_action' => 'edit', 'forum_id' => $post_id ), mb_get_board_home_url() );

	elseif ( mb_get_topic_post_type() === $post_type )
		$url = add_query_arg( array( 'mb_action' => 'edit', 'topic_id' => $post_id ), mb_get_board_home_url() );

	elseif ( mb_get_reply_post_type() === $post_type )
		$url = add_query_arg( array( 'mb_action' => 'edit', 'reply_id' => $post_id ), mb_get_board_home_url() );

	return $url;
}
