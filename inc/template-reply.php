<?php

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

	if ( !current_user_can( 'create_forum_replies' ) )
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
	$form .= wp_nonce_field( 'mb_new_reply_action', 'mb_new_reply_nonce', false, false );
	$form .= '</fieldset>';
	$form .= '</form>';

	return apply_filters( 'mb_get_reply_form', $form );
}
