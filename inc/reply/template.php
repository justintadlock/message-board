<?php

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

/* ====== Conditionals ====== */

function mb_is_single_reply( $reply = '' ) {

	if ( !is_singular( mb_get_reply_post_type() ) )
		return false;

	if ( !empty( $reply ) )
		return is_single( $reply );

	return true;
}

function mb_is_reply_archive() {
	return is_post_type_archive( mb_get_reply_post_type() );
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

function mb_get_single_reply_title() {
	return apply_filters( 'mb_get_single_reply_title', single_post_title( '', false ) );
}

function mb_reply_archive_title() {
	echo mb_get_reply_archive_title();
}

function mb_get_reply_archive_title() {
	return apply_filters( 'mb_get_reply_archive_title', post_type_archive_title( '', false ) );
}

/* ====== Reply Position ====== */

function mb_reply_position( $reply_id = 0 ) {
	echo mb_get_reply_position( $reply_id );
}

function mb_get_reply_position( $reply_id = 0 ) {

	$reply_id       = mb_get_reply_id( $reply_id );
	$reply_position = get_post_field( 'menu_order', $reply_id );

	if ( empty( $reply_position ) ) {
		$topic_id = mb_get_reply_topic_id( $reply_id );
		mb_reset_reply_positions( $topic_id );
		$reply_position = get_post_field( 'menu_order', $reply_id );
	}

	return $reply_position;
}

/* ====== Reply Edit ====== */

function mb_reply_edit_url( $reply_id = 0 ) {
	echo mb_get_reply_edit_url( $reply_id );
}

function mb_get_reply_edit_url( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	return apply_filters( 'mb_get_reply_edit_url', get_edit_post_link( $reply_id ), $reply_id );
}

function mb_reply_edit_link( $reply_id = 0 ) {
	echo mb_get_reply_edit_link( $reply_id );
}

function mb_get_reply_edit_link( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );
	$link     = '';

	if ( current_user_can( 'edit_reply', $reply_id ) && $url  = mb_get_reply_edit_url( $reply_id ) )
		$link = sprintf( '<a href="%s" class="reply-edit-link edit-link">%s</a>', $url, __( 'Edit', 'message-board' ) );

	return apply_filters( 'mb_get_reply_edit_link', $link, $reply_id );
}

/* ====== Reply Status ====== */

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
	$status   = get_post_status_object( get_post_status( $reply_id ) );

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
	$status   = get_post_status( $reply_id );

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
	$status   = get_post_status( $reply_id );

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
	$status   = get_post_status( $reply_id );

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
	$status   = get_post_status( $reply_id );

	return apply_filters( 'mb_is_reply_orphan', mb_get_orphan_post_status() === $status ? true : false, $reply_id );
}

function mb_reply_toggle_spam_url( $reply_id = 0 ) {
	echo mb_get_reply_toggle_spam_spam_url( $reply_id = 0 );
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

	if ( !current_user_can( 'moderate_reply', $reply_id ) )
		return '';

	$text = mb_is_reply_spam( $reply_id ) ? __( 'Unspam', 'message-board' ) : get_post_status_object( mb_get_spam_post_status() )->label;

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

	elseif ( mb_get_reply_post_type() === get_post_type( get_the_ID() ) )
		$_reply_id = get_the_ID();

	elseif ( mb_is_single_reply() )
		$_reply_id = get_queried_object_id();

	elseif ( get_query_var( 'reply_id' ) )
		$_reply_id = get_query_var( 'reply_id' );

	else
		$_reply_id = 0;

	return apply_filters( 'mb_get_reply_id', absint( $_reply_id ), $reply_id );
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

/* ====== Reply Author ====== */

function mb_reply_author_id( $reply_id = 0 ) {
	echo mb_get_reply_author_id( $reply_id );
}

function mb_get_reply_author_id( $reply_id = 0 ) {
	$reply_id  = mb_get_reply_id( $reply_id );
	$author_id = get_post_field( 'post_author', $reply_id );

	return apply_filters( 'mb_get_reply_author_id', absint( $author_id ), $reply_id );
}

function mb_reply_author( $reply_id = 0 ) {
	echo mb_get_reply_author( $reply_id );
}

function mb_get_reply_author( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_author_display_name', mb_get_post_author( $reply_id ), $reply_id );
}

function mb_reply_author_profile_url( $reply_id = 0 ) {
	echo mb_get_reply_author_profile_url( $reply_id );
}

function mb_get_reply_author_profile_url( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_author_profile_url', mb_get_post_author_profile_url( $reply_id ), $reply_id );
}

function mb_reply_author_profile_link( $reply_id = 0 ) {
	echo mb_get_reply_author_profile_link( $reply_id );
}

function mb_get_reply_author_profile_link( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_author_profile_link', mb_get_post_author_profile_link( $reply_id ), $reply_id );
}

/* ====== Reply Form ====== */

function mb_reply_form() {
	mb_get_template_part( 'form-reply', 'new' );
}

function mb_reply_edit_form() {
	mb_get_template_part( 'form-reply', 'edit' );
}

/* ====== Reply Forum ====== */

function mb_get_reply_forum_id( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );

	return mb_get_topic_forum_id( mb_get_reply_topic_id( $reply_id ) );
}

/* ====== Reply Topic ====== */

function mb_reply_topic_id( $reply_id = 0 ) {
	echo mb_get_reply_topic_id( $reply_id );
}

function mb_get_reply_topic_id( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	return get_post_field( 'post_parent', $reply_id );
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
