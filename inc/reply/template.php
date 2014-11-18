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
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => get_query_var( 'paged' ),
		'orderby'             => 'menu_order',
		'order'               => 'ASC',
		'hierarchical'        => false,
		'ignore_sticky_posts' => true,
	);

	if ( is_singular( mb_get_topic_post_type() ) ) {
		$defaults['post_parent'] = get_queried_object_id();
	}

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
	$url = mb_get_reply_edit_url( $reply_id );

	if ( !empty( $url ) )
		$link = sprintf( '<a href="%s" class="reply-edit-link edit-link">%s</a>', $url, __( 'Edit', 'message-board' ) );

	return apply_filters( 'mb_get_reply_edit_link', $link );
}

/* ====== Reply Trash ====== */

function mb_reply_trash_url( $reply_id = 0 ) {
	echo mb_get_reply_trash_url( $reply_id );
}

function mb_get_reply_trash_url( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );

	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'trash', 'reply_id' => $reply_id, 'redirect' => esc_url( $redirect ) ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_reply_trash_url', $url, $reply_id );
}

function mb_reply_untrash_url( $reply_id = 0 ) {
	echo mb_get_reply_untrash_url( $reply_id );
}

function mb_get_reply_untrash_url( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );

	if ( is_singular( mb_get_reply_post_type() ) ) {
		$redirect = mb_get_forum_url( mb_get_reply_forum_id( $reply_id ) );
	} else {
		$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	$url = esc_url( add_query_arg( array( 'action' => 'untrash', 'reply_id' => $reply_id, 'redirect' => esc_url( $redirect ) ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_reply_untrash_url', $url, $reply_id );
}

function mb_reply_trash_link( $reply_id = 0 ) {
	echo mb_get_reply_trash_link( $reply_id );
}

function mb_get_reply_trash_link( $reply_id = 0 ) {

	$link = '';

	if ( 'trash' !== get_post_status( $reply_id ) ) {
		$url = mb_get_reply_trash_url( $reply_id );

		if ( !empty( $url ) )
			$link = sprintf( '<a href="%s" class="reply-trash-link trash-link">%s</a>', $url, __( 'Trash', 'message-board' ) );
	}

	else {
		$url = mb_get_reply_untrash_url( $reply_id );

		if ( !empty( $url ) )
			$link = sprintf( '<a href="%s" class="reply-trash-link trash-link">%s</a>', $url, __( 'Restore', 'message-board' ) );
	}

	return apply_filters( 'mb_get_reply_trash_link', $link );
}

/* ====== Reply Status ====== */

function mb_is_reply_spam( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	$status   = get_post_status( $reply_id );

	return apply_filters( 'mb_is_reply_spam', 'spam' === $status ? true : false, $reply_id );
}

function mb_reply_spam_url( $reply_id = 0 ) {
	echo mb_get_reply_spam_url( $reply_id );
}

function mb_get_reply_spam_url( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'spam', 'reply_id' => $reply_id, 'redirect' => esc_url( $redirect ) ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_reply_spam_url', $url, $reply_id );
}

function mb_reply_unspam_url( $reply_id = 0 ) {
	echo mb_get_reply_unspam_url( $reply_id );
}

function mb_get_reply_unspam_url( $reply_id = 0 ) {

	$reply_id = mb_get_reply_id( $reply_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'unspam', 'reply_id' => $reply_id, 'redirect' => esc_url( $redirect ) ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_reply_unspam_url', $url, $reply_id );
}

function mb_reply_spam_link( $reply_id = 0 ) {
	echo mb_get_reply_spam_link( $reply_id );
}

function mb_get_reply_spam_link( $reply_id = 0 ) {

	if ( !current_user_can( 'manage_forums' ) )
		return '';

	$reply_id = mb_get_reply_id( $reply_id );

	if ( !mb_is_reply_spam( $reply_id ) ) {
		$link = sprintf( '<a class="spam-link" href="%s">%s</a>', mb_get_reply_spam_url( $reply_id ), __( 'Spam', 'message-board' ) );
	}
	else {
		$link = sprintf( '<a class="spam-link" href="%s">%s</a>', mb_get_reply_spam_url( $reply_id ), __( 'Unspam', 'message-board' ) );
	}

	return $link;
}

/* ====== Reply ID ====== */

function mb_reply_id( $reply_id = 0 ) {
	echo mb_get_reply_id( $reply_id );
}

function mb_get_reply_id( $reply_id = 0 ) {
	return apply_filters( 'mb_get_reply_id', mb_get_post_id( $reply_id ), $reply_id );
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
	$topic_id = mb_get_reply_topic_id( $reply_id );
	$forum_id = mb_get_topic_forum_id( $topic_id );

	return $forum_id;
}




