<?php
/**
 * Template functions for forum-related functionality.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* ====== Forum Query ====== */

/**
 * Creates a new forum query and checks if there are any forums found.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_forum_query() {
	$mb = message_board();

	if ( !is_null( $mb->forum_query->query ) ) {

		$have_posts = $mb->forum_query->have_posts();

		if ( empty( $have_posts ) )
			wp_reset_postdata();

		return $have_posts;
	}

	if ( mb_is_forum_archive() || mb_is_user_page( array( 'forums', 'forum-subscriptions' ) ) ) {
		global $wp_query;

		$mb->forum_query = $wp_query;
	}

	else {
		$per_page = mb_get_forums_per_page();

		$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status(), mb_get_private_post_status(), mb_get_archive_post_status() );

		if ( current_user_can( 'read_hidden_forums' ) )
			$statuses[] = mb_get_hidden_post_status();

		$defaults = array(
			'post_type'           => mb_get_forum_post_type(),
			'post_status'         => $statuses,
			'posts_per_page'      => $per_page,
			'paged'               => get_query_var( 'paged' ),
			'orderby'             => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'ignore_sticky_posts' => true,
		);

		if ( mb_is_single_forum() )
			$defaults['post_parent'] = get_queried_object_id();

		add_filter( 'the_posts', 'mb_posts_hierarchy_filter', 10, 2 );

		$mb->forum_query = new WP_Query( $defaults );
	}

	return $mb->forum_query->have_posts();
}

/**
 * Sets up the forum data for the current forum in the forum loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_forum() {
	return message_board()->forum_query->the_post();
}

/**
 * Creates a new sub-forum query and checks if there are any forums found.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_subforum_query() {
	$mb = message_board();

	if ( !is_null( $mb->subforum_query->query ) ) {

		$have_posts = $mb->subforum_query->have_posts();

		if ( empty( $have_posts ) ) {
			wp_reset_postdata();
			$mb->subforum_query->query = null;
		}

		return $have_posts;
	}

	if ( $mb->forum_query->in_the_loop ) {

		$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status(), mb_get_private_post_status(), mb_get_archive_post_status() );

		if ( current_user_can( 'read_hidden_forums' ) )
			$statuses[] = mb_get_hidden_post_status();

		$defaults = array(
			'post_type'           => mb_get_forum_post_type(),
			'post_status'         => $statuses,
			'posts_per_page'      => mb_get_forums_per_page(),
			'orderby'             => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'ignore_sticky_posts' => true,
		);

		$defaults['post_parent'] = mb_get_forum_id();

		$mb->subforum_query = new WP_Query( $defaults );

		return $mb->subforum_query->have_posts();
	}

	return false;
}

/**
 * Sets up the forum data for the current forum in the subforum loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_subforum() {
	return message_board()->subforum_query->the_post();
}

/* ====== Forum ID ====== */

/**
 * Displays the forum ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_id( $forum_id = 0 ) {
	echo mb_get_forum_id( $forum_id );
}

/**
 * Returns the forum ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int
 */
function mb_get_forum_id( $forum_id = 0 ) {
	$mb = message_board();

	if ( is_numeric( $forum_id ) && 0 < $forum_id )
		$_forum_id = $forum_id;

	elseif ( $mb->subforum_query->in_the_loop && isset( $mb->subforum_query->post->ID ) )
		$_forum_id = $mb->subforum_query->post->ID;

	elseif ( $mb->forum_query->in_the_loop && isset( $mb->forum_query->post->ID ) )
		$_forum_id = $mb->forum_query->post->ID;

	elseif ( $mb->search_query->in_the_loop && isset( $mb->search_query->post->ID ) && mb_is_forum( $mb->search_query->post->ID ) )
		$_forum_id = $mb->search_query->post->ID;

	elseif ( mb_is_single_forum() )
		$_forum_id = get_queried_object_id();

	elseif ( get_query_var( 'forum_id' ) )
		$_forum_id = get_query_var( 'forum_id' );

	else
		$_forum_id = 0;

	return apply_filters( 'mb_get_forum_id', absint( $_forum_id ), $forum_id );
}

/* ====== Conditionals ====== */

/**
 * Checks if the post is a forum.  This is a wrapper for `get_post_type()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return bool
 */
function mb_is_forum( $post_id = 0 ) {
	$post_id  = mb_get_forum_id( $post_id );
	$is_forum = mb_get_forum_post_type() === get_post_type( $post_id ) ? true : false;

	return apply_filters( 'mb_is_forum', $is_forum, $post_id );
}

/**
 * Checks if viewing a single forum.  Wrapper function for the WordPress `is_single()` function.
 *
 * @since  1.0.0
 * @access public
 * @param  int|string
 * @return bool
 */
function mb_is_single_forum( $forum = '' ) {
	$is_single_forum = is_singular( mb_get_forum_post_type() ) ? is_single( $forum ) : false;

	return apply_filters( 'mb_is_single_forum', $is_single_forum );
}

/**
 * Checks if viewing the forum archive.  Wrapper function for `is_post_type_archive()`.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_archive() {
	$is_forum_archive = get_query_var( 'mb_custom' ) ? false : is_post_type_archive( mb_get_forum_post_type() );

	return apply_filters( 'mb_is_forum_archive', $is_forum_archive );
}

/**
 * Checks if the forums should be shown in hierarchical (vs. flat) format.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_show_hierarchical_forums() {
	$show = 'hierarchical' === mb_get_forum_archive_display() && mb_is_forum_archive() ? true : false;
	return apply_filters( 'mb_show_hierarchical_forums', $show );
}

/**
 * Conditional check to see if a forum allows new subforums to be created.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return bool
 */
function mb_forum_allows_subforums( $forum_id = 0 ) {
	$forum_id  = mb_get_forum_id( $forum_id );
	$parent_id = mb_get_forum_parent_id( $forum_id );
	$allow     = true;

	/* Check if the forum type allows subforums. */
	if ( !mb_forum_type_allows_subforums( mb_get_forum_type( $forum_id ) ) )
		$allow = false;

	/* Check if the forum status allows subforums. */
	elseif ( !mb_forum_status_allows_subforums( mb_get_forum_status( $forum_id ) ) )
		$allow = false;

	/* If there's a parent forum, check if it allows subforums. */
	elseif ( 0 < $parent_id && !mb_forum_status_allows_subforums( mb_get_forum_status( $parent_id ) ) )
		$allow = false;

	return apply_filters( 'mb_forum_allows_subforums', $allow, $forum_id );
}

/**
 * Conditional check to see if a forum allows new topics to be created.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return bool
 */
function mb_forum_allows_topics( $forum_id = 0 ) {
	$forum_id   = mb_get_forum_id( $forum_id );
	$parent_id  = mb_get_forum_parent_id( $forum_id );
	$allow      = true;

	/* Check if the forum type allows topics. */
	if ( !mb_forum_type_allows_topics( mb_get_forum_type( $forum_id ) ) )
		$allow = false;

	/* Check if the forum status allows topics. */
	elseif ( !mb_forum_status_allows_topics( mb_get_forum_status( $forum_id ) ) )
		$allow = false;

	/* If there's a parent forum, check if it allows topics. */
	elseif ( 0 < $parent_id && !mb_forum_status_allows_topics( mb_get_forum_status( $parent_id ) ) )
		$allow = false;

	return apply_filters( 'mb_forum_allows_subforums', $allow, $forum_id );
}

/* ====== Forum Status ====== */

/**
 * Displays the forum post status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_status( $forum_id = 0 ) {
	echo mb_get_forum_status( $forum_id );
}

/**
 * Returns the forum post status.  Wrapper function for `get_post_status()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_get_forum_status( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = $forum_id ? get_post_status( $forum_id ) : '';

	return apply_filters( 'mb_get_forum_status', $status, $forum_id );
}

/**
 * Whether the forum's post status is a "public" post status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return bool
 */
function mb_is_forum_public( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id();
	$status   = get_post_status_object( mb_get_forum_status( $forum_id ) );

	return apply_filters( 'mb_is_forum_public', (bool) $status->public, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "open" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_open( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = mb_get_forum_status( $forum_id );

	return apply_filters( 'mb_is_forum_open', mb_get_open_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "close" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_closed( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = mb_get_forum_status( $forum_id );

	return apply_filters( 'mb_is_forum_closed', mb_get_close_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "archive" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_archived( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = mb_get_forum_status( $forum_id );

	return apply_filters( 'mb_is_forum_archived', mb_get_archive_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "private" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_private( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = mb_get_forum_status( $forum_id );

	return apply_filters( 'mb_is_forum_private', mb_get_private_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "hidden" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_hidden( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = mb_get_forum_status( $forum_id );

	return apply_filters( 'mb_is_forum_hidden', mb_get_hidden_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "trash" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_trash( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = mb_get_forum_status( $forum_id );

	return apply_filters( 'mb_is_forum_trash', mb_get_trash_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see if a forum status allows new subforums to be created.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $status
 * @return bool
 */
function mb_forum_status_allows_subforums( $status ) {

	$statuses = array( mb_get_open_post_status(), mb_get_private_post_status(), mb_get_hidden_post_status() );
	$allowed  = in_array( $status, $statuses ) ? true : false;

	return apply_filters( 'mb_forum_status_allows_subforums', $allowed, $status );
}

/**
 * Conditional check to see if a forum status allows new topics to be created.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $status
 * @return bool
 */
function mb_forum_status_allows_topics( $status ) {

	$statuses = array( mb_get_open_post_status(), mb_get_private_post_status(), mb_get_hidden_post_status() );
	$allowed  = in_array( $status, $statuses ) ? true : false;

	return apply_filters( 'mb_forum_status_allows_topics', $allowed, $status );
}

function mb_forum_toggle_open_url( $forum_id = 0 ) {
	echo mb_get_forum_toggle_open_open_url( $forum_id = 0 );
}

function mb_get_forum_toggle_open_url( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	if ( mb_is_forum_open( $forum_id ) || !current_user_can( 'open_forum', $forum_id ) )
		return '';

	$url = add_query_arg( array( 'forum_id' => $forum_id, 'action' => 'mb_toggle_open' ) );
	$url = wp_nonce_url( $url, "open_forum_{$forum_id}", 'mb_nonce' );

	return $url;
}

function mb_forum_toggle_open_link( $forum_id = 0 ) {
	echo mb_get_forum_toggle_open_link( $forum_id );
}

function mb_get_forum_toggle_open_link( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	$url = mb_get_forum_toggle_open_url( $forum_id );

	if ( empty( $url ) )
		return '';

	$status = get_post_status_object( mb_get_open_post_status() );

	$link = sprintf( '<a class="mb-forum-open-link" href="%s">%s</a>', $url, $status->mb_label_verb );

	return $link;
}

function mb_forum_toggle_close_url( $forum_id = 0 ) {
	echo mb_get_forum_toggle_close_url( $forum_id = 0 );
}

function mb_get_forum_toggle_close_url( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	if ( mb_is_forum_closed( $forum_id ) || !current_user_can( 'close_forum', $forum_id ) )
		return '';

	$url = add_query_arg( array( 'forum_id' => $forum_id, 'action' => 'mb_toggle_close' ) );
	$url = wp_nonce_url( $url, "close_forum_{$forum_id}", 'mb_nonce' );

	return $url;
}

function mb_forum_toggle_close_link( $forum_id = 0 ) {
	echo mb_get_forum_toggle_close_link( $forum_id );
}

function mb_get_forum_toggle_close_link( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	$url = mb_get_forum_toggle_close_url( $forum_id );

	if ( empty( $url ) )
		return '';

	$status = get_post_status_object( mb_get_close_post_status() );

	$link = sprintf( '<a class="mb-forum-close-link" href="%s">%s</a>', $url, $status->mb_label_verb );

	return $link;
}

function mb_forum_toggle_trash_url( $forum_id = 0 ) {
	echo mb_get_forum_toggle_trash_url( $forum_id = 0 );
}

function mb_get_forum_toggle_trash_url( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	$url = add_query_arg( array( 'forum_id' => $forum_id, 'action' => 'mb_toggle_trash' ) );
	$url = wp_nonce_url( $url, "trash_forum_{$forum_id}", 'mb_nonce' );

	return $url;
}

function mb_forum_toggle_trash_link( $forum_id = 0 ) {
	echo mb_get_forum_toggle_trash_link( $forum_id );
}

function mb_get_forum_toggle_trash_link( $forum_id = 0 ) {

	$forum_id = mb_get_topic_id( $forum_id );

	if ( !current_user_can( 'delete_post', $forum_id ) )
		return '';

	$text = mb_is_forum_trash( $forum_id ) ? __( 'Restore', 'message-board' ) : get_post_status_object( mb_get_trash_post_status() )->label;

	$link = sprintf( '<a class="toggle-trash-link" href="%s">%s</a>', mb_get_forum_toggle_trash_url( $forum_id ), $text );

	return $link;
}

/* ====== Forum Labels ====== */

/**
 * Displays a forum post type label.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $label
 * @return void
 */
function mb_forum_label( $label ) {
	echo mb_get_forum_label( $label );
}

/**
 * Returns a forum post type label.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $label
 * @return void
 */
function mb_get_forum_label( $label ) {
	$labels = get_post_type_object( mb_get_forum_post_type() )->labels;

	return apply_filters( 'mb_get_forum_label', $labels->$label, $label );
}

/* ====== Forum States (better function names?) ====== */

/**
 * Outputs a forum's states.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_states( $forum_id = 0 ) {
	echo mb_get_forum_states( $forum_id );
}

/**
 * Returns a forum's labels.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_states( $forum_id = 0 ) {
	$forum_id       = mb_get_forum_id( $forum_id );
	$labels = array();

	/* @todo Default labels - closed, private, etc. */

	$labels = apply_filters( 'mb_get_forum_states', $labels, $forum_id );

	if ( !empty( $labels ) ) {

		$formatted = '';

		foreach ( $labels as $key => $value )
			$formatted .= sprintf( '<span class="forum-label %s">%s</span>', sanitize_html_class( "forum-label-{$key}" ), $value );

		return sprintf( '<span class="forum-labels">%s</span>', $formatted );
	}

	return '';
}

/* ====== Forum Content ====== */

/**
 * Displays the forum content.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_content( $forum_id = 0 ) {
	echo mb_get_forum_content( $forum_id );
}

/**
 * Returns the forum content.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_content( $forum_id = 0, $mode = 'display' ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$content = $forum_id ? get_post_field( 'post_content', $forum_id, 'raw' ) : '';

	if ( 'raw' === $mode )
		return $content;
	else
		return apply_filters( 'mb_get_forum_content', $content, $forum_id );
}

/* ====== Forum Title ====== */

/**
 * Displays the single forum title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_single_forum_title() {
	echo mb_get_single_forum_title();
}

/**
 * Returns the single forum title.  Wrapper function for `single_post_title()`.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_single_forum_title() {
	return apply_filters( 'mb_get_single_forum_title', single_post_title( '', false ) );
}

/**
 * Displays the the forum archive title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_archive_title() {
	echo mb_get_forum_archive_title();
}

/**
 * Returns the forum archive title.  Wrapper function for `post_type_archive_title()`.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_archive_title() {
	return apply_filters( 'mb_get_forum_archive_title', post_type_archive_title( '', false ) );
}

/**
 * Displays the forum title.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_title( $forum_id = 0 ) {
	echo mb_get_forum_title( $forum_id );
}

/**
 * Returns the forum title.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_title( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$title    = $forum_id ? get_post_field( 'post_title', $forum_id ) : '';

	return apply_filters( 'mb_get_forum_title', $title, $forum_id );
}

/* ====== Forum URL ====== */

/**
 * Displays the forum URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_url( $forum_id = 0 ) {
	echo mb_get_forum_url( $forum_id );
}

/**
 * Returns the forum URL.  Wrapper function for `get_permalink()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_url( $forum_id = 0 ) {
	$forum_id  = mb_get_forum_id( $forum_id );
	$forum_url = $forum_id ? get_permalink( $forum_id ) : '';

	return apply_filters( 'mb_get_forum_url', $forum_url, $forum_id );
}

/**
 * Displays the forum link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_link( $forum_id = 0 ) {
	echo mb_get_forum_link( $forum_id );
}

/**
 * Returns the forum link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_link( $forum_id = 0 ) {
	$forum_id    = mb_get_forum_id( $forum_id );
	$forum_title = mb_get_forum_title( $forum_id );
	$forum_url   = mb_get_forum_url( $forum_id );
	$forum_link  = $forum_url ? '<a class="mb-forum-link" href="%s">%s</a>' : '<span class="mb-forum-link">%2$s</span>';

	return apply_filters( 'mb_get_forum_link', sprintf( $forum_link, $forum_url, $forum_title ), $forum_id );
}

/**
 * Displays the forum date.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id,
 * @param  string  $format
 * @return void
 */
function mb_forum_date( $forum_id = 0, $format = '' ) {
	echo mb_get_forum_date( $forum_id, $format );
}

/**
 * Returns the forum date.  Wrapper function for `get_post_time()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id,
 * @param  string  $format
 * @return void
 */
function mb_get_forum_date( $forum_id = 0, $format = '' ) {
	$forum_id   = mb_get_forum_id( $forum_id );
	$format     = !empty( $format ) ? $format : get_option( 'date_format' );
	$forum_date = $forum_id ? get_post_time( $format, false, $forum_id, true ) : '';

	return apply_filters( 'mb_get_forum_date', $forum_date, $forum_id );
}

/**
 * Displays the forum time.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id,
 * @param  string  $format
 * @return void
 */
function mb_forum_time( $forum_id = 0, $format = '' ) {
	echo mb_get_forum_time( $forum_id, $format );
}

/**
 * Returns the forum time.  Wrapper function for `get_post_time()`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id,
 * @param  string  $format
 * @return void
 */
function mb_get_forum_time( $forum_id = 0, $format = '' ) {
	$forum_id   = mb_get_forum_id( $forum_id );
	$format     = !empty( $format ) ? $format : get_option( 'time_format' );
	$forum_time = $forum_id ? get_post_time( $format, false, $forum_id, true ) : '';

	return apply_filters( 'mb_get_forum_time', $forum_time, $forum_id );
}

/* ====== Forum Author ====== */

/**
 * Displays the forum author ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_id( $forum_id = 0 ) {
	echo mb_get_forum_author_id( $forum_id );
}

/**
 * Returns the forum autor ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int
 */
function mb_get_forum_author_id( $forum_id = 0 ) {
	$forum_id  = mb_get_forum_id( $forum_id );
	$author_id = $forum_id ? get_post_field( 'post_author', $forum_id ) : 0;

	return apply_filters( 'mb_get_forum_author_id', absint( $author_id ), $forum_id );
}

/**
 * Displays the forum author.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author( $forum_id = 0 ) {
	echo mb_get_forum_author( $forum_id );
}

/**
 * Returns the forum author.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author( $forum_id = 0 ) {
	$forum_id     = mb_get_forum_id( $forum_id );
	$author_id    = mb_get_forum_author_id( $forum_id );
	$forum_author = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';

	return apply_filters( 'mb_get_forum_author', $forum_author, $forum_id );
}

/**
 * Displays the forum author URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_url( $forum_id = 0 ) {
	echo mb_get_forum_author_url( $forum_id );
}

/**
 * Returns the forum author URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author_url( $forum_id = 0 ) {
	$forum_id   = mb_get_forum_id( $forum_id );
	$author_id  = mb_get_forum_author_id( $forum_id );
	$author_url = $author_id ? mb_get_user_url( $author_id ) : '';

	return apply_filters( 'mb_get_forum_author_url', $author_url, $forum_id );
}

/**
 * Displays the forum author link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_link( $forum_id = 0 ) {
	echo mb_get_forum_author_link( $forum_id );
}

/**
 * Returns the forum author link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author_link( $forum_id = 0 ) {
	$forum_id     = mb_get_forum_id( $forum_id );
	$forum_author = mb_get_forum_author( $forum_id );
	$author_url   = mb_get_forum_author_url( $forum_id );
	$author_link  = $author_url ? sprintf( '<a class="mb-forum-author-link" href="%s">%s</a>', $author_url, $forum_author ) : '';

	return apply_filters( 'mb_get_forum_author_link', $author_link, $forum_id );
}

/* ====== Last Activity ====== */

/**
 * Prints the forum last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_last_active_time( $forum_id = 0 ) {
	echo mb_get_forum_last_active_time( $forum_id );
}

/**
 * Returns the forum last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_last_active_time( $forum_id = 0 ) {

	$forum_id   = mb_get_forum_id( $forum_id );
	$time       = get_post_meta( $forum_id, mb_get_forum_activity_datetime_meta_key(), true );
	$human_time = '';

	if ( !empty( $time ) ) {
		$mysql_date = mysql2date( 'U', $time );
		$now        = current_time( 'timestamp' );
		$human_time = human_time_diff( $mysql_date, $now );
	}

	return apply_filters( 'mb_get_forum_last_active_time', $human_time, $time, $forum_id );
}

/* ====== Last Post Author ====== */

/**
 * Displays the last post author for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_post_author( $forum_id = 0 ) {
	echo mb_get_forum_last_post_author( $forum_id );
}

/**
 * Returns the last post author for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_post_author( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$last_id  = mb_get_forum_last_post_id( $forum_id );
	$author   = '';

	if ( $last_id )
		$author = mb_is_reply( $last_id ) ? mb_get_reply_author( $last_id ) : mb_get_topic_author( $last_id );

	return apply_filters( 'mb_get_forum_last_post_author', $author, $forum_id );
}

/* ====== Last Post ID ====== */

/**
 * Displays the forum last post (topic or reply) ID.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_last_post_id( $forum_id = 0 ) {
	echo mb_get_forum_last_post_id( $forum_id );
}

/**
 * Returns the forum last post (topic or reply) ID.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_get_forum_last_post_id( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$topic_id = mb_get_forum_last_topic_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );
	$post_id  = 0;

	if ( $topic_id && $reply_id )
		$post_id = $reply_id > $topic_id ? $reply_id : $topic_id;

	elseif ( $topic_id || $reply_id )
		$post_id = $topic_id ? $topic_id : $reply_id;

	return apply_filters( 'mb_get_forum_last_post_id', $post_id, $forum_id );
}

/* ====== Last Post URL ====== */

/**
 * Displays the last post URL for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_post_url( $forum_id = 0 ) {
	echo mb_get_forum_last_post_url( $forum_id );
}

/**
 * Returns a forum's last post URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_post_url( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$last_id  = mb_get_forum_last_post_id( $forum_id );
	$url   = '';

	if ( $last_id )
		$url = mb_is_reply( $last_id ) ? mb_get_reply_url( $last_id ) : mb_get_topic_url( $last_id );

	return apply_filters( 'mb_get_forum_last_post_url', $url, $forum_id );
}

/* ====== Last Reply ID ====== */

/**
 * Display the forum last reply ID.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_last_reply_id( $forum_id = 0 ) {
	echo mb_get_forum_last_reply_id( $forum_id );
}

/**
 * Returns the last forum reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @retrn  int
 */
function mb_get_forum_last_reply_id( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$reply_id = get_post_meta( $forum_id, mb_get_forum_last_reply_id_meta_key(), true );
	$reply_id = is_numeric( $reply_id ) ? absint( $reply_id ) : 0;

	return apply_filters( 'mb_get_forum_last_reply_id', $reply_id, $forum_id );
}

/* ====== Last Topic ID ====== */

/**
 * Displays the forum last topic ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_topic_id( $forum_id = 0 ) {
	echo mb_get_forum_last_topic_id( $forum_id );
}

/**
 * Returns the forum last topic ID.  This returns the last topic by activity, which is not 
 * necessarily the newest topic created.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int
 */
function mb_get_forum_last_topic_id( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$topic_id = get_post_meta( $forum_id, mb_get_forum_last_topic_id_meta_key(), true );
	$topic_id = !empty( $topic_id ) ? absint( $topic_id ) : 0;

	return apply_filters( 'mb_get_forum_last_topic_id', $topic_id, $forum_id );
}

/* ====== Last Topic URL ====== */

/**
 * Displays the forum last topic URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_topic_url( $forum_id = 0 ) {
	echo mb_get_forum_last_topic_url( $forum_id );
}

/**
 * Returns the forum last topic URL.  This returns the last topic by activity, which is not 
 * necessarily the newest topic created.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_topic_url( $forum_id = 0 ) {
	$forum_id  = mb_get_forum_id( $forum_id );
	$topic_id  = mb_get_forum_last_topic_id( $forum_id );
	$topic_url = $topic_id ? mb_get_topic_url( $topic_id ) : '';

	return apply_filters( 'mb_get_forum_last_topic_url', $topic_url );
}

/**
 * Displays the forum last topic link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_topic_link( $forum_id = 0 ) {
	echo mb_get_forum_last_topic_link( $forum_id );
}

/**
 * Returns the forum last topic link.  This returns the last topic by activity, which is not 
 * necessarily the newest topic created.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_topic_link( $forum_id = 0 ) {
	$forum_id   = mb_get_forum_id( $forum_id );
	$topic_id   = mb_get_forum_last_topic_id( $forum_id );
	$topic_link = $topic_id ? mb_get_topic_link( $topic_id ) : '';

	return apply_filters( 'mb_get_forum_last_topic_link', $topic_link, $forum_id );
}

/* ====== Subforums ====== */

function mb_get_forum_order( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$order = $forum_id ? get_post( $forum_id )->menu_order : 0;

	return apply_filters( 'mb_get_forum_order', $order, $forum_id );
}

function mb_get_forum_parent_id( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$parent_id = $forum_id ? get_post( $forum_id )->post_parent : 0;

	return apply_filters( 'mb_get_forum_parent_id', $parent_id, $forum_id );
}

function mb_is_subforum( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$forum = get_post( $forum_id );

	return 0 < $forum->post_parent ? true : false;
}

/* ====== Forum Counts ====== */

function mb_forum_subforum_count( $forum_id = 0 ) {
	echo mb_get_forum_subforum_count( $forum_id );
}

function mb_get_forum_subforum_count( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$count = $forum_id ? get_post_meta( $forum_id, mb_get_forum_subforum_count_meta_key(), true ) : 0;

	return apply_filters( 'mb_get_forum_subforum_count', absint( $count ), $forum_id );
}

function mb_forum_topic_count( $forum_id = 0 ) {
	echo mb_get_forum_topic_count( $forum_id );
}

function mb_get_forum_topic_count( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$count    = get_post_meta( $forum_id, mb_get_forum_topic_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_reset_forum_topic_count( $forum_id );

	return absint( $count );
}

function mb_forum_post_count( $forum_id = 0 ) {
	echo mb_get_forum_post_count( $forum_id );
}

function mb_get_forum_post_count( $forum_id = 0 ) {

	$topic_count = mb_get_forum_topic_count( $forum_id );
	$reply_count = mb_get_forum_reply_count( $forum_id );

	return $topic_count + $reply_count;
}

function mb_forum_reply_count( $forum_id = 0 ) {
	echo mb_get_forum_reply_count( $forum_id );
}

function mb_get_forum_reply_count( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );
	$count    = get_post_meta( $forum_id, mb_get_forum_reply_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_reset_forum_reply_count( $forum_id );

	return $count;
}

/* ====== Pagination ====== */

/**
 * Checks if viewing a paginated forum. Only for use on single forum pages.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_paged() {
	return mb_is_single_forum() && is_paged() ? true : false;
}

/**
 * Outputs pagination links for single topic pages (the replies are paginated).
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_loop_forum_pagination( $args = array() ) {
	return mb_pagination( $args, message_board()->forum_query );
}

function mb_dropdown_forums( $args = array() ) {

	$defaults = array(
		'child_type'  => mb_get_forum_post_type(),
		'post_type'   => mb_get_forum_post_type(),
		'post_status' => mb_get_forum_post_statuses(),
		'walker'      => new MB_Walker_Forum_Dropdown,
		'echo'        => true,
	);

	$trash = array_search( mb_get_trash_post_status(), $defaults['post_status'] );

	if ( $trash )
		unset( $defaults['post_status'][ $trash ] );

	$r = $args = wp_parse_args( $args, $defaults );
	$r['echo'] = false;

	$forums = wp_dropdown_pages( $r );

	if ( '' != $args['selected'] ) {

		if ( ( mb_get_forum_post_type() === $args['child_type'] && !current_user_can( 'move_forums' ) ) || ( mb_get_topic_post_type() === $args['child_type'] && !current_user_can( 'move_topics' ) ) )
			$forums = preg_replace( '/<select(.*?)>/i', "<select disabled='disabled'$1>", $forums );
	}

	if ( false === $args['echo'] )
		return $forums;

	echo $forums;
}


class MB_Walker_Forum_Dropdown extends Walker_PageDropdown {

	/**
	 * @see Walker::start_el()
	 * @since 1.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page in reference to parent pages. Used for padding.
	 * @param array $args Uses 'selected' argument for selected page to set selected HTML attribute for option element.
	 * @param int $id
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $id = 0 ) {

		$forum_type = mb_get_forum_type_object( mb_get_forum_type( $page->ID ) );

		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"$page->ID\"";
		if ( $page->ID == $args['selected'] )
			$output .= ' selected="selected"';

		$post_status = mb_get_forum_status( $page->ID );

		if ( mb_get_forum_post_type() === $args['child_type'] && !mb_forum_allows_subforums( $page->ID ) )
			$output .= ' disabled="disabled"';

		elseif ( mb_get_topic_post_type() === $args['child_type'] && !mb_forum_allows_topics( $page->ID ) )
			$output .= ' disabled="disabled"';

		$output .= '>';

		$title = $page->post_title;
		if ( '' === $title ) {
			$title = sprintf( __( '#%d (no title)' ), $page->ID );
		}

		/**
		 * Filter the page title when creating an HTML drop-down list of pages.
		 *
		 * @since 3.1.0
		 *
		 * @param string $title Page title.
		 * @param object $page  Page data object.
		 */
		$title = apply_filters( 'list_pages', $title, $page );
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}

/* ====== Forum Form ====== */

/**
 * Outputs the URL to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_form_url() {
	echo mb_get_forum_form_url();
}

/**
 * Returns the URL to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_form_url() {
	return apply_filters( 'mb_forum_form_url', esc_url( '#forum-form' ) );
}

/**
 * Outputs a link to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return void
 */
function mb_forum_form_link( $args = array() ) {
	echo mb_get_forum_form_link( $args );
}

/**
 * Returns a link to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_get_forum_form_link( $args = array() ) {

	if ( !current_user_can( 'create_forums' ) )
		return '';

	$url  = mb_get_forum_form_url();
	$link = '';

	$defaults = array(
		'text' => __( 'New Forum &rarr;', 'message-board' ),
		'wrap' => '<a %s>%s</a>',
		'before' => '',
		'after' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( !empty( $url ) ) {

		$attr = sprintf( 'class="new-forum-link new-forum" href="%s"', $url );

		$link = sprintf( $args['before'] . $args['wrap'] . $args['after'], $attr, $args['text'] );
	}

	return apply_filters( 'mb_get_forum_form_link', $link, $args );
}

/**
 * Displays the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_form() {
	mb_get_template_part( 'form-forum', 'new' );
}

/**
 * Displays the edit forum form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_edit_form() {
	mb_get_template_part( 'form-forum', 'edit' );
}
