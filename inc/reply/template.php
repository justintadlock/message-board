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

	elseif ( !empty( $mb->reply_query->in_the_loop ) && isset( $mb->reply_query->post->ID ) )
		$_reply_id = $mb->reply_query->post->ID;

	elseif ( mb_is_single_reply() )
		$_reply_id = get_queried_object_id();

	else
		$_reply_id = 0;

	return apply_filters( 'mb_get_reply_id', absint( $_reply_id ), $reply_id );
}

/* ====== Reply Content ====== */

function mb_reply_content( $reply_id = 0 ) {
	echo mb_get_reply_content( $reply_id );
}

function mb_get_reply_content( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_content', mb_get_post_content( $reply_id ), $reply_id );
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
	return apply_filters( 'mb_get_reply_url', mb_get_post_url( $reply_id ), $reply_id );
}

/* ====== Reply Author ====== */

function mb_reply_author_id( $reply_id = 0 ) {
	echo mb_get_reply_author_id( $reply_id );
}

function mb_get_reply_author_id( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_author_id', mb_get_post_author_id( $reply_id ), $reply_id );
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

function mb_reply_form_action_url() {
	echo mb_get_topic_form_action_url();
}

function mb_get_reply_form_action_url() {
	return esc_url( add_query_arg( 'message-board', 'new-reply', trailingslashit( home_url() ) ) );
}

function mb_reply_form() {
	echo mb_get_reply_form();
}

function mb_get_reply_form() {

	if ( !current_user_can( 'create_forum_replies' ) || !mb_is_topic_open( get_queried_object_id() ) )
		return; 

	$form  = sprintf( '<form id="reply-form" method="post" action="%s">', mb_get_reply_form_action_url() );
	$form .= '<fieldset>';
	$form .= sprintf( '<legend>%s</legend>', __( 'Leave A Reply', 'message-board' ) );

	// content field
	$default_fields['content']  = '<p>';
	$default_fields['content'] .= sprintf( '<label for="mb_reply_content" name="mb_reply_content">%s</label>', __( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ) );
	$default_fields['content'] .= '<textarea id="mb_reply_content" name="mb_reply_content"></textarea>';
	$default_fields['content'] .= '</p>';

	$default_fields = apply_filters( 'mb_reply_form_fields', $default_fields );

	foreach ( $default_fields as $key => $field ) {

		$form .= $field;
	}

	$form .= sprintf( '<p><input type="submit" value="%s" /></p>', esc_attr__( 'Submit', 'message-board' ) );
	$form .= sprintf( '<input type="hidden" name="mb_reply_topic_id" value="%s" />', absint( get_queried_object_id() ) );

	if ( !mb_is_user_subscribed_topic( get_current_user_id(), get_queried_object_id() ) ) {
		$form .= sprintf( '<p><label><input type="checkbox" name="mb_topic_subscribe" value="1" /> %s</label></p>', __( 'Notify me of follow-up posts via email', 'message-board' ) );
	}

	$form .= wp_nonce_field( 'mb_new_reply_action', 'mb_new_reply_nonce', false, false );
	$form .= '</fieldset>';
	$form .= '</form>';

	return apply_filters( 'mb_get_reply_form', $form );
}

/* ====== Reply Forum ====== */

function mb_get_reply_forum_id( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );

	return mb_get_topic_forum_id( mb_get_reply_topic_id( $reply_id ) );
}

/* ====== Reply Topic ====== */

function mb_get_reply_topic_id( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	return get_post_field( 'post_parent', $reply_id );
}
