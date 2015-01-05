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
add_filter( 'the_title',  'mb_post_title_filter', 5, 2 );
add_filter( 'post_title', 'mb_post_title_filter', 5, 2 );
add_filter( 'single_post_title', 'mb_post_title_filter', 5, 2 );

/* Edit post link filters. */
add_filter( 'get_edit_post_link', 'mb_get_edit_post_link', 5, 2 );

/* Edit user link filter. */
add_filter( 'get_edit_user_link', 'mb_get_edit_user_link_filter', 5, 2 );

/* Filter the front-end page title. */
add_filter( 'wp_title',   'mb_wp_title'   );

/* Filter the front-end `<body>` classes. */
add_filter( 'body_class', 'mb_body_class', 15 );

/**
 * Filters `wp_title` to handle the title on the forum front page since this is a non-standard WP page.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @return string
 */
function mb_wp_title( $title ) {

	if ( !mb_is_message_board() )
		return $title;

	if ( mb_is_single_forum() )
		$title = mb_get_single_forum_title();

	elseif ( mb_is_forum_archive() )
		$title = mb_get_forum_archive_title();

	elseif ( mb_is_single_topic() )
		$title = mb_get_single_topic_title();

	elseif ( mb_is_topic_archive() )
		$title = mb_get_topic_archive_title();

	elseif ( mb_is_single_reply() )
		$title = mb_get_single_reply_title();

	elseif ( mb_is_reply_archive() )
		$title = mb_get_reply_archive_title();

	elseif ( mb_is_user_page() )
		$title = mb_get_user_page_title();

	elseif ( mb_is_single_user() )
		$title = mb_get_single_user_title();

	elseif ( mb_is_user_archive() )
		$title = mb_get_user_archive_title();

	elseif ( mb_is_search() )
		$title = mb_get_search_page_title();

	else
		$title = __( 'Board', 'message-board' );

	return apply_filters( 'mb_wp_title', $title );
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
		$classes[] = 'mb';
		$classes[] = 'bbpress'; // temporary class for compat

		$forum_type = mb_get_forum_post_type();
		$topic_type = mb_get_topic_post_type();
		$reply_type = mb_get_reply_post_type();

		$_classes = $classes;
		$remove   = array(
			"single-{$forum_type}",
			"single-{$topic_type}",
			"single-{$reply_type}",
			"singular-{$forum_type}",
			"singular-{$topic_type}",
			"singular-{$reply_type}",
			"archive-{$forum_type}",
			"archive-{$topic_type}",
			"archive-{$reply_type}"
		);

		foreach ( $_classes as $class_key => $class_value ) {

			if ( in_array( $class_value, $remove ) )
				unset( $classes[ $class_key ] );
		}

		if ( mb_is_forum_front() ) {
			$classes[] = 'forum-front';

		} elseif ( mb_is_single_forum() ) {
			$classes[] = 'single-forum';

		} elseif ( mb_is_single_topic() ) {
			$classes[] = 'single-topic';

		} elseif ( mb_is_single_reply() ) {
			$classes[] = 'single-reply';

		} elseif ( mb_is_forum_archive() ) {
			$classes[] = 'archive-forum';

		} elseif ( mb_is_topic_archive() ) {
			$classes[] = 'archive-topic';

		} elseif ( mb_is_reply_archive() ) {
			$classes[] = 'archive-reply';

		} elseif ( mb_is_user_archive() ) {
			$classes[] = 'archive-user';
		}
	}

	return $classes;
}

/**
 * Handles forums, topics, and replies without titles. The titles will use the post ID. By default, 
 * replies do not have titles and will be replaced with "Reply to: Topic Title".
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @param  int     $post
 * @return string
 */
function mb_post_title_filter( $title, $post ) {

	$post_id   = is_object( $post ) ? $post->ID : $post;
	$post_type = get_post_type( $post_id );

	/* Forum post type. */
	if ( empty( $title ) && mb_get_forum_post_type() === $post_type ) {

		/* Translators: Empty forum title "%s" is the forum ID. */
		$title = sprintf( __( 'Forum #%s', 'message-board' ), $post_id );

	/* Topic post type. */
	} elseif ( empty( $title ) && mb_get_topic_post_type() === $post_type ) {

		/* Translators: Empty topic title "%s" is the topic ID. */
		$title = sprintf( __( 'Topic #%s', 'message-board' ), $post_id );

	/* Reply post type. */
	} elseif ( empty( $title ) && mb_get_reply_post_type() === $post_type ) {
		$post = get_post( $post_id );

		/* If the reply doesn't have a parent topic. */
		if ( 0 >= $post->post_parent || mb_is_reply_orphan( $post_id ) ) {

			/* Translators: Empty reply title with no topic (orphan). "%s" is the reply ID. */
			$title = sprintf( __( 'Reply #%s', 'message-board' ), $post_id );

		/* If the reply does belong to a topic. */
		} else {

			/* Translators: Empty reply title. "%s" is the topic title. */
			$title = sprintf( __( 'Reply to: %s', 'message-board' ), mb_get_topic_title( $post->post_parent ) );
		}
	}

	if ( !is_admin() && mb_get_hidden_post_status() === get_post_status( $post_id ) ) {

		/* Translators: Hidden title. */
		$title = sprintf( __( 'Hidden: %s', 'message-board' ), $title );

	} elseif ( !is_admin() && mb_get_private_post_status() === get_post_status( $post_id ) ) {

		/* Translators: Private title. */
		$title = sprintf( __( 'Private: %s', 'message-board' ), $title );
	}

	/* Return the filtered title. */
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

/**
 * Filters the edit user link for front-end editing.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $url
 * @param  int     $post_id
 */
function mb_get_edit_user_link_filter( $url, $user_id ) {

	if ( is_admin() || !mb_is_message_board() )
		return $url;

	return add_query_arg( array( 'mb_action' => 'edit', 'user_id' => $user_id ), mb_get_board_home_url() );
}
