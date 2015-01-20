<?php
/**
 * Reply template functions for theme authors.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Creates a new reply query and checks if there are any replies found.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_reply_query() {
	$mb = message_board();

	if ( !is_null( $mb->reply_query->query ) ) {

		$have_posts = $mb->reply_query->have_posts();

		if ( empty( $have_posts ) )
			wp_reset_postdata();

		return $have_posts;
	}

	if ( mb_is_reply_archive() || mb_is_single_reply() || mb_is_user_page( 'replies' ) ) {
		global $wp_query;

		$mb->reply_query = $wp_query;
	}

	else {

		$per_page = mb_get_replies_per_page();

		$defaults = array(
			'post_type'           => mb_get_reply_post_type(),
			'post_status'         => mb_get_publish_post_status(),
			'posts_per_page'      => $per_page,
			'paged'               => get_query_var( 'paged' ),
			'orderby'             => 'menu_order',
			'order'               => 'ASC',
			'hierarchical'        => false,
			'ignore_sticky_posts' => true,
		);

		if ( $mb->topic_query->in_the_loop || mb_is_single_topic() )
			$defaults['post_parent'] = mb_get_topic_id();

		$mb->reply_query = new WP_Query( $defaults );
	}

	return $mb->reply_query->have_posts();
}

/**
 * Sets up the reply data for the current reply in The Loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_reply() {
	return message_board()->reply_query->the_post();
}

/* ====== Reply ID ====== */

/**
 * Displays the reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_id( $reply_id = 0 ) {
	echo mb_get_reply_id( $reply_id );
}

/**
 * Returns the reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int
 */
function mb_get_reply_id( $reply_id = 0 ) {
	$mb = message_board();

	if ( is_numeric( $reply_id ) && 0 < $reply_id )
		$_reply_id = $reply_id;

	elseif ( $mb->reply_query->in_the_loop && isset( $mb->reply_query->post->ID ) )
		$_reply_id = $mb->reply_query->post->ID;

	elseif ( $mb->search_query->in_the_loop && isset( $mb->search_query->post->ID ) && mb_is_reply( $mb->search_query->post->ID ) )
		$_reply_id = $mb->search_query->post->ID;

	elseif ( mb_is_single_reply() )
		$_reply_id = get_queried_object_id();

	elseif ( get_query_var( 'reply_id' ) )
		$_reply_id = get_query_var( 'reply_id' );

	else
		$_reply_id = 0;

	return apply_filters( 'mb_get_reply_id', absint( $_reply_id ), $reply_id );
}

/* ====== Conditionals ====== */

/**
 * Checks if the post is a reply.  This is a wrapper for `get_post_type()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return bool
 */
function mb_is_reply( $post_id = 0 ) {
	$post_id  = mb_get_reply_id( $post_id );
	$is_reply = mb_get_reply_post_type() === get_post_type( $post_id ) ? true : false;

	return apply_filters( 'mb_is_reply', $is_reply, $post_id );
}

/**
 * Checks if viewing a single reply page.  This is a wrapper for `is_single()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int|string  $reply
 * @return bool
 */
function mb_is_single_reply( $reply = '' ) {
	$is_single_reply = is_singular( mb_get_reply_post_type() ) ? is_single( $reply ) : false;

	return apply_filters( 'mb_is_single_reply', $is_single_reply );
}

/**
 * Checks if viewing the reply archive.  Wrapper function for `is_post_type_archive()`.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_reply_archive() {
	return apply_filters( 'mb_is_reply_archive', is_post_type_archive( mb_get_reply_post_type() ) );
}

/* ====== Reply Title ====== */

/**
 * Displays the single reply title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_single_reply_title() {
	echo single_post_title();
}

/**
 * Returns the single reply title.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_single_reply_title() {
	return apply_filters( 'mb_get_single_reply_title', single_post_title( '', false ) );
}

/**
 * Displays the reply archive title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_reply_archive_title() {
	echo mb_get_reply_archive_title();
}

/**
 * Returns the reply archive title.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_reply_archive_title() {
	return apply_filters( 'mb_get_reply_archive_title', post_type_archive_title( '', false ) );
}

/* ====== Reply Position ====== */

/**
 * Displays the reply position.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_position( $reply_id = 0 ) {
	echo mb_get_reply_position( $reply_id );
}

/**
 * Returns the reply position. The reply position is stored as the `menu_order` post field. The position 
 * indicates where the reply is in reference to the other replies for a topic. It's used for keeping 
 * them in the correct order.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_get_reply_position( $reply_id = 0 ) {

	$reply_id       = mb_get_reply_id( $reply_id );
	$reply_position = get_post_field( 'menu_order', $reply_id );

	/* If there's no reply position, we need to reset the positions for the topic's replies. */
	if ( 0 >= $reply_position ) {
		$topic_id = mb_get_reply_topic_id( $reply_id );
		mb_reset_reply_positions( $topic_id );
		$reply_position = get_post_field( 'menu_order', $reply_id );
	}

	return apply_filters( 'mb_get_reply_position', absint( $reply_position ), $reply_id );
}

/* ====== Reply Edit ====== */

/**
 * Displays the reply edit URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_edit_url( $reply_id = 0 ) {
	echo mb_get_reply_edit_url( $reply_id );
}

/**
 * Returns the reply edit URL.  This is a wrapper for `get_edit_post_link()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_edit_url( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	return apply_filters( 'mb_get_reply_edit_url', get_edit_post_link( $reply_id ), $reply_id );
}

/**
 * Displays the reply edit link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_edit_link( $reply_id = 0 ) {
	echo mb_get_reply_edit_link( $reply_id );
}

/**
 * Returns the reply edit link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_edit_link( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );
	$url      = mb_get_reply_edit_url( $reply_id );
	$link     = $url ? sprintf( '<a href="%s" class="mb-reply-edit-link">%s</a>', $url, __( 'Edit', 'message-board' ) ) : '';

	return apply_filters( 'mb_get_reply_edit_link', $link, $reply_id );
}

/* ====== Reply Status ====== */

/**
 * Displays the reply post status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_status( $reply_id = 0 ) {
	echo mb_get_reply_status( $reply_id );
}

/**
 * Returns the reply post status.  Wrapper for the `get_post_status()` function.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_status( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$status   = $reply_id ? get_post_status( $reply_id ) : '';

	return apply_filters( 'mb_get_reply_status', $status, $reply_id );
}

/**
 * Whether the reply's post status is a "public" post status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return bool
 */
function mb_is_reply_public( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id();
	$status   = get_post_status_object( mb_get_reply_status( $reply_id ) );

	return apply_filters( 'mb_is_reply_public', (bool) $status->public, $reply_id );
}

/**
 * Conditional check to see whether a reply has the "publish" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_reply_published( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$status   = mb_get_reply_status( $reply_id );

	return apply_filters( 'mb_is_reply_published', mb_get_publish_post_status() === $status ? true : false, $reply_id );
}

/**
 * Conditional check to see whether a reply has the "spam" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_reply_spam( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$status   = mb_get_reply_status( $reply_id );

	return apply_filters( 'mb_is_reply_spam', mb_get_spam_post_status() === $status ? true : false, $reply_id );
}

/**
 * Conditional check to see whether a reply has the "trash" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_reply_trash( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$status   = mb_get_reply_status( $reply_id );

	return apply_filters( 'mb_is_reply_trash', mb_get_trash_post_status() === $status ? true : false, $reply_id );
}

/**
 * Conditional check to see whether a reply has the "orphan" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_reply_orphan( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$status   = mb_get_reply_status( $reply_id );

	return apply_filters( 'mb_is_reply_orphan', mb_get_orphan_post_status() === $status ? true : false, $reply_id );
}

function mb_reply_toggle_spam_url( $reply_id = 0 ) {
	echo mb_get_reply_toggle_spam_url( $reply_id = 0 );
}

function mb_get_reply_toggle_spam_url( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );

	$url = add_query_arg( array( 'reply_id' => $reply_id, 'action' => 'mb_toggle_spam' ) );
	$url = wp_nonce_url( $url, "spam_reply_{$reply_id}", 'mb_nonce' );

	return $url;
}

function mb_reply_toggle_spam_link( $reply_id = 0 ) {
	echo mb_get_reply_toggle_spam_link( $reply_id );
}

function mb_get_reply_toggle_spam_link( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );

	if ( !current_user_can( 'spam_reply', $reply_id ) )
		return '';

	$text = mb_is_reply_spam( $reply_id ) ? __( 'Unspam', 'message-board' ) : get_post_status_object( mb_get_spam_post_status() )->mb_label_verb;

	$link = sprintf( '<a class="toggle-spam-link" href="%s">%s</a>', mb_get_reply_toggle_spam_url( $reply_id ), $text );

	return $link;
}

function mb_reply_toggle_trash_url( $reply_id = 0 ) {
	echo mb_get_reply_toggle_trash_url( $reply_id = 0 );
}

function mb_get_reply_toggle_trash_url( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );

	$url = add_query_arg( array( 'reply_id' => $reply_id, 'action' => 'mb_toggle_trash' ) );
	$url = wp_nonce_url( $url, "trash_reply_{$reply_id}", 'mb_nonce' );

	return $url;
}

function mb_reply_toggle_trash_link( $reply_id = 0 ) {
	echo mb_get_reply_toggle_trash_link( $reply_id );
}

function mb_get_reply_toggle_trash_link( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );

	if ( !current_user_can( 'moderate_reply', $reply_id ) )
		return '';

	$text = mb_is_reply_trash( $reply_id ) ? __( 'Restore', 'message-board' ) : get_post_status_object( mb_get_trash_post_status() )->label;

	$link = sprintf( '<a class="toggle-trash-link" href="%s">%s</a>', mb_get_reply_toggle_trash_url( $reply_id ), $text );

	return $link;
}

/* ====== Reply Labels ====== */

function mb_reply_label( $label ) {
	echo mb_get_reply_label( $label );
}

function mb_get_reply_label( $label ) {
	$labels = get_post_type_object( mb_get_reply_post_type() )->labels;

	return $labels->$label;
}

/* ====== Reply Content ====== */

function mb_reply_content( $reply_id = 0 ) {
	echo mb_get_reply_content( $reply_id );
}

function mb_get_reply_content( $reply_id = 0, $mode = 'display' ) {
	$reply_id = mb_get_reply_id( $reply_id );

	$content  = $reply_id ? get_post_field( 'post_content', $reply_id, 'raw' ) : '';

	if ( 'raw' === $mode )
		return $content;
	else
		return apply_filters( 'mb_get_reply_content', $content, $reply_id );
}

/* ====== Reply Title ====== */

function mb_reply_title( $reply_id = 0 ) {
	echo mb_get_reply_title( $reply_id );
}

function mb_get_reply_title( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_title', mb_get_post_title( $reply_id ), $reply_id );
}

/* ====== Reply URL ====== */

function mb_reply_url( $reply_id = 0 ) {
	echo mb_get_reply_url( $reply_id );
}

function mb_get_reply_url( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	return apply_filters( 'mb_get_reply_url', mb_get_post_url( $reply_id ), $reply_id );
}

/**
 * Displays the reply link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_link( $reply_id = 0 ) {
	echo mb_get_reply_link( $reply_id );
}

/**
 * Returns the reply link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_link( $reply_id = 0 ) {
	$url   = mb_get_reply_url(   $reply_id );
	$title = mb_get_reply_title( $reply_id );

	return apply_filters( 'mb_get_reply_link', sprintf( '<a class="mb-reply-link" href="%s">%s</a>', $url, $title ), $reply_id );
}

/**
 * Displays the reply date.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id,
 * @param  string  $format
 * @return void
 */
function mb_reply_date( $reply_id = 0, $format = '' ) {
	echo mb_get_reply_date( $reply_id, $format );
}

/**
 * Returns the reply date.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id,
 * @param  string  $format
 * @return void
 */
function mb_get_reply_date( $reply_id = 0, $format = '' ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$format   = !empty( $format ) ? $format : get_option( 'date_format' );

	return get_post_time( $format, false, $reply_id, true );
}

/**
 * Displays the reply time.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id,
 * @param  string  $format
 * @return void
 */
function mb_reply_time( $reply_id = 0, $format = '' ) {
	echo mb_get_reply_time( $reply_id, $format );
}

/**
 * Returns the reply time.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id,
 * @param  string  $format
 * @return void
 */
function mb_get_reply_time( $reply_id = 0, $format = '' ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$format   = !empty( $format ) ? $format : get_option( 'time_format' );

	return get_post_time( $format, false, $reply_id, true );
}

/**
 * Outputs the reply natural time (e.g., 1 month ago, 5 minutes ago, etc.)
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_natural_time( $reply_id = 0 ) {
	echo mb_get_reply_natural_time( $reply_id );
}

/**
 * Outputs the reply natural time (e.g., 1 month ago, 5 minutes ago, etc.)
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_natural_time( $reply_id = 0 ) {
	$reply_id   = mb_get_reply_id( $reply_id );
	$reply_time = $reply_id ? mb_natural_time( get_post_time( 'U', false, $reply_id, true ) ) : '';

	return apply_filters( 'mb_get_reply_natural_time', $reply_time, $reply_id );
}

/* ====== Reply Author ====== */

/**
 * Displays the reply author ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_author_id( $reply_id = 0 ) {
	echo mb_get_reply_author_id( $reply_id );
}

/**
 * Returns the reply author ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int
 */
function mb_get_reply_author_id( $reply_id = 0 ) {
	$reply_id  = mb_get_reply_id( $reply_id );
	$author_id = $reply_id ? get_post_field( 'post_author', $reply_id ) : 0;

	return apply_filters( 'mb_get_reply_author_id', absint( $author_id ), $reply_id );
}

/**
 * Displays the reply author display name.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_author( $reply_id = 0 ) {
	echo mb_get_reply_author( $reply_id );
}

/**
 * Returns the reply author display name.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_author( $reply_id = 0 ) {

	$reply_id    = mb_get_reply_id( $reply_id );
	$author_id   = mb_get_reply_author_id( $reply_id );
	$author_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';

	return apply_filters( 'mb_get_reply_author', $author_name, $reply_id );
}

/**
 * Displays the reply author URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_author_url( $reply_id = 0 ) {
	echo mb_get_reply_author_url( $reply_id );
}

/**
 * Returns the reply author URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_author_url( $reply_id = 0 ) {
	$reply_id   = mb_get_reply_id( $reply_id );
	$author_id  = mb_get_reply_author_id( $reply_id );
	$author_url = $author_id ? mb_get_user_url( $author_id ) : '';

	return apply_filters( 'mb_get_reply_author_url', $author_url, $reply_id );
}

/**
 * Displays the reply author link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_author_link( $reply_id = 0 ) {
	echo mb_get_reply_author_link( $reply_id );
}

/**
 * Displays the reply author link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return string
 */
function mb_get_reply_author_link( $reply_id = 0 ) {
	$reply_id    = mb_get_reply_id( $reply_id );
	$author_id   = mb_get_reply_author_id( $reply_id );
	$author_url  = mb_get_reply_author_url( $reply_id );
	$author_name = mb_get_reply_author( $reply_id );
	$author_link = $author_url ? sprintf( '<a class="mb-reply-author-link" href="%s">%s</a>', $author_url, $author_name ) : '';

	return apply_filters( 'mb_get_reply_author_link', $author_link, $reply_id );
}

/* ====== Reply Forum ====== */

/**
 * Displays the reply's forum ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_forum_id( $reply_id = 0 ) {
	echo mb_get_reply_forum_id( $reply_id );
}

/**
 * Returns the reply's forum ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int
 */
function mb_get_reply_forum_id( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );
	$topic_id = mb_get_reply_topic_id( $reply_id );
	$forum_id = $topic_id ? mb_get_topic_forum_id( $topic_id ) : 0;

	return apply_filters( 'mb_get_reply_forum_id', $forum_id, $reply_id );
}

/* ====== Reply Topic ====== */

/**
 * Displays the reply's topic ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return void
 */
function mb_reply_topic_id( $reply_id = 0 ) {
	echo mb_get_reply_topic_id( $reply_id );
}

/**
 * Returns the reply's topic ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int
 */
function mb_get_reply_topic_id( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );

	return apply_filters( 'mb_get_reply_topic_id', get_post_field( 'post_parent', $reply_id ), $reply_id );
}

/**
 * Outputs pagination links for single topic pages (the replies are paginated).
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_loop_reply_pagination( $args = array() ) {
	return mb_pagination( $args, message_board()->reply_query );
}
