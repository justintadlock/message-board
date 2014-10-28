<?php

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

	if ( is_post_type_archive( mb_get_forum_post_type() ) ) {
		global $wp_query;

		$mb->forum_query = $wp_query;
	}

	else {

		$defaults = array(
			'post_type'           => mb_get_forum_post_type(),
			'nopaging'            => true,
			'posts_per_page'      => -1,
			'orderby'             => 'title',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
		);

		if ( is_singular( mb_get_forum_post_type() ) ) {
			$defaults['post_parent'] = get_queried_object_id();
		}

		$mb->forum_query = new WP_Query( $defaults );
	}

	return $mb->forum_query->have_posts();
}

/**
 * Sets up the forum data for the current forum in The Loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_forum() {
	return message_board()->forum_query->the_post();
}


/* ====== Forum Status ====== */

/**
 * Whether the forum is open to new replies.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_open( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = get_post_status( $forum_id );

	return apply_filters( 'mb_is_forum_open', in_array( $status, array( 'publish', 'inherit' ) ) ? true : false, $forum_id );
}

/* ====== Forum Labels ====== */

/**
 * Outputs a forums labels.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_labels( $forum_id = 0 ) {
	echo mb_get_forum_labels( $forum_id );
}

/**
 * Returns a forum's labels.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_labels( $forum_id = 0 ) {
	$forum_id       = mb_get_forum_id( $forum_id );
	$labels = array();

	/* @todo Default labels - closed, private, etc. */

	$labels = apply_filters( 'mb_forum_labels', $labels, $forum_id );

	if ( !empty( $labels ) ) {

		$formatted = '';

		foreach ( $labels as $key => $value )
			$formatted .= sprintf( '<span class="forum-label %s">%s</span>', sanitize_html_class( "forum-label-{$key}" ), $value );

		return sprintf( '<span class="forum-labels">%s</span>', $formatted );
	}

	return '';
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
	return apply_filters( 'mb_get_forum_id', mb_get_post_id( $forum_id ), $forum_id );
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
function mb_get_forum_content( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_content', mb_get_post_content( $forum_id ), $forum_id );
}

/* ====== Forum Title ====== */

/**
 * Displays the single forum title.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $prefix
 * @param  bool    $echo
 * @return string
 */
function mb_single_forum_title( $prefix = '', $echo = true ) {
	$title = apply_filters( 'mb_single_forum_title', single_post_title( $prefix, false ) );

	if ( false === $echo )
		return $title;

	echo $title;
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
	return apply_filters( 'mb_get_forum_title', mb_get_post_title( $forum_id ), $forum_id );
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
 * Returns the forum URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_url( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_url', mb_get_post_url( $forum_id ), $forum_id );
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
	$url   = mb_get_forum_url(   $forum_id );
	$title = mb_get_forum_title( $forum_id );

	return apply_filters( 'mb_get_forum_link', sprintf( '<a class="forum-link" href="%s">%s</a>', $url, $title ), $forum_id );
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
	return apply_filters( 'mb_get_forum_author_id', mb_get_post_author_id( $forum_id ), $forum_id );
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
	return apply_filters( 'mb_get_forum_author', mb_get_post_author( $forum_id ), $forum_id );
}

/**
 * Displays the forum author profile URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_profile_url( $forum_id = 0 ) {
	echo mb_get_forum_author_profile_url( $forum_id );
}

/**
 * Returns the forum author profile URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author_profile_url( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_author_profile_url', mb_get_post_author_profile_url( $forum_id ), $forum_id );
}

/**
 * Displays the forum author profile link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_profile_link( $forum_id = 0 ) {
	echo mb_get_forum_author_profile_link( $forum_id );
}

/**
 * Returns the forum author profile link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author_profile_link( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_author_profile_link', mb_get_post_author_profile_link( $forum_id ), $forum_id );
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
	$time       = get_post_meta( $forum_id, '_forum_activity_datetime', true );
	$mysql_date = mysql2date( 'U', $time );
	$now        = current_time( 'timestamp' );

	return apply_filters( 'mb_get_forum_last_active_time', human_time_diff( $mysql_date, $now ), $time, $forum_id );
}

/* ====== Last Reply ID ====== */

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
	$reply_id = get_post_meta( $forum_id, '_forum_last_reply_id', true );

	$mb_reply_id = !empty( $reply_id ) && is_numeric( $reply_id ) ? absint( $reply_id ) : 0;

	return apply_filters( 'mb_get_forum_last_reply_id', $mb_reply_id, $forum_id );
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
function mb_forum_last_poster( $forum_id = 0 ) {
	echo mb_get_forum_last_poster( $forum_id );
}

/**
 * Returns the last post author for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_poster( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	$author = !empty( $reply_id ) ? mb_get_reply_author( $reply_id ) : mb_get_forum_author( $forum_id );

	return apply_filters( 'mb_get_forum_last_poster', $author, $reply_id, $forum_id );
}

/* ====== Last Post ID ====== */


function mb_forum_last_post_id( $forum_id ) {
	echo mb_get_forum_last_post_id( $forum_id );
}

function mb_get_forum_last_post_id( $forum_id ) {

	$topic_id = mb_get_forum_last_topic_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	return $reply_id > $topic_id ? $reply_id : $topic_id;
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
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	$url = !empty( $reply_id ) ? mb_get_reply_url( $reply_id ) : mb_get_post_jump_url( $forum_id );

	return apply_filters( 'mb_get_forum_last_post_url', $url, $reply_id, $forum_id );
}

/* ====== Last Topic ID ====== */

function mb_forum_last_topic_id( $forum_id ) {
	echo mb_get_forum_last_topic_id( $forum_id );
}

function mb_get_forum_last_topic_id( $forum_id ) {
	$topic_id = get_post_meta( $forum_id, '_forum_last_topic_id', true );

	return !empty( $topic_id ) ? absint( $topic_id ) : 0;
}

/* ====== Subforums ====== */

function mb_is_subforum( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$forum = get_post( $forum_id );

	return 0 < $forum->post_parent ? true : false;
}

/* ====== Forum Counts ====== */

function mb_forum_topic_count( $forum_id = 0 ) {
	echo mb_get_forum_topic_count( $forum_id );
}

function mb_get_forum_topic_count( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$count    = get_post_meta( $forum_id, '_forum_topic_count', true );

	if ( empty( $count ) )
		$count = mb_set_forum_topic_count( $forum_id );

	return $count;
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
	$count    = get_post_meta( $forum_id, '_forum_reply_count', true );

	if ( empty( $count ) )
		$count = mb_set_forum_reply_count( $forum_id );

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
	return is_singular( mb_get_forum_post_type() ) && is_paged() ? true : false;
}

/**
 * Outputs pagination links for single topic pages (the replies are paginated).
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_forum_pagination( $args = array() ) {
	return mb_pagination( $args, message_board()->topic_query );
}
