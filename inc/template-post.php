<?php
/**
 * Post template functions.  In this plugin, both "topics" and "replies" are technically custom post types. 
 * This file exists so that we can consolidate some of these template functions into one.  For more-specific 
 * template tags that apply to topics and replies, see `template-topic.php` and `template-post.php`.
 *
 * Technically, you could use WP's built-in functions for getting the data needed because most of these 
 * functions are simply wrappers for those functions.  However, this is discouraged because there are 
 * certain hooks that will be executed when using these functions.
 */

/* ====== Post ID ====== */

function mb_post_id( $post_id = 0 ) {
	echo mb_get_post_id( $post_id );
}

function mb_get_post_id( $post_id = 0 ) {

	if ( is_numeric( $post_id ) && 0 < $post_id )
		$_post_id = $post_id;

	else
		$_post_id = get_the_ID();

	return apply_filters( 'mb_get_post_id', $_post_id, $post_id );
}

/* ====== Post Content ====== */

function mb_post_content( $post_id = 0 ) {
	echo mb_get_post_content( $post_id );
}

function mb_get_post_content( $post_id = 0 ) {
	$post_id      = mb_get_post_id( $post_id );
	$post_content = get_post_field( 'post_content', $post_id, 'raw' );

	return apply_filters( 'mb_get_post_content', $post_content, $post_id );
}

/* ====== Post Title ====== */

function mb_post_title( $post_id = 0 ) {
	echo mb_get_post_title( $post_id );
}

function mb_get_post_title( $post_id = 0 ) {
	$post_id    = mb_get_post_id( $post_id );
	$post_title = get_post_field( 'post_title', $post_id );

	return apply_filters( 'mb_get_post_title', $post_title, $post_id );
}

/* ====== Post URL ====== */

function mb_post_url( $post_id = 0 ) {
	echo mb_get_post_url( $post_id );
}

function mb_get_post_url( $post_id = 0 ) {
	$post_id = mb_get_post_id( $post_id );

	return apply_filters( 'mb_get_post_url', get_permalink( $post_id ), $post_id );
}

function mb_post_jump_url( $post_id = 0 ) {
	echo mb_get_post_jump_url( $post_id );
}

/* example.com/board/topics/example/#post-1000 */
function mb_get_post_jump_url( $post_id = 0 ) {
	$post_id = mb_get_post_id( $post_id );

	$url = 'forum_topic' === get_post_type( $post_id ) ? esc_url( get_permalink( $post_id ) . '#post-' . get_the_ID() ) : get_permalink( $post_id );

	return apply_filters( 'mb_get_post_jump_url', $url, $post_id );
}

/* ====== Post Author ====== */

function mb_post_author_id( $post_id = 0 ) {
	echo mb_get_post_author_id( $post_id );
}

function mb_get_post_author_id( $post_id = 0 ) {
	$post_id   = mb_get_post_id( $post_id );
	$author_id = get_post_field( 'post_author', $post_id );

	return apply_filters( 'mb_get_post_author_id', absint( $author_id ), $post_id );
}

function mb_post_author( $post_id = 0 ) {
	echo mb_get_post_author( $post_id );
}

function mb_get_post_author( $post_id = 0 ) {

	$post_id      = mb_get_post_id( $post_id );
	$author_id    = mb_get_post_author_id( $post_id );
	$display_name = get_the_author_meta( 'display_name', $author_id );

	return apply_filters( 'mb_get_post_author_display_name', $display_name, $author_id, $post_id );
}

function mb_post_author_profile_url( $post_id = 0 ) {
	echo mb_get_post_author_profile_url( $post_id );
}

function mb_get_post_author_profile_url( $post_id = 0 ) {
	$post_id     = mb_get_post_id( $post_id );
	$author_id   = mb_get_post_author_id( $post_id );
	$profile_url = mb_get_user_profile_url( $author_id );

	return apply_filters( 'mb_get_post_author_profile_url', $profile_url, $author_id, $post_id );
}

function mb_post_author_profile_link( $post_id = 0 ) {
	echo mb_get_post_author_profile_link( $post_id );
}

function mb_get_post_author_profile_link( $post_id = 0 ) {
	$author_name = mb_get_post_author( $post_id );
	$author_url  = mb_get_post_author_profile_url( $post_id );

	$profile_link = sprintf( '<a class="user-profile-link" href="%s">%s</a>', $author_url, $author_name );

	return apply_filters( 'mb_get_post_author_profile_link', $profile_link, $post_id );
}


function mb_edit_form_action_url() {
	echo mb_get_edit_form_action_url();
}

function mb_get_edit_form_action_url() {
	return esc_url( add_query_arg( 'message-board', 'edit', trailingslashit( home_url() ) ) );
}

function mb_edit_form() {
	echo mb_get_edit_form();
	return;


	$edit = get_query_var( 'edit' );

	if ( empty( $edit ) )
		return '';

	$post = get_post( $edit );

	if ( empty( $post ) || ( 'forum_topic' !== $post->post_type && 'forum_reply' !== $post->post_type ) )
		return;

	$edit = $post->ID;

	if ( !current_user_can( 'edit_post', $edit ) )
		return;

	$pt_object = get_post_type_object( $post->post_type );

	printf( '<form id="edit-post-form" method="post" action="%s">', mb_get_edit_form_action_url() );
	echo '<fieldset>';
	printf( '<legend>%s</legend>', $pt_object->labels->edit_item );

	// title field
	if ( 'forum_topic' === $post->post_type ) {

		echo '<p>';
		printf( '<label for="mb_post_title">%s</label>', __( 'Topic title: (be brief and descriptive)', 'message-board' ) );
		printf( '<input type="text" id="mb_post_title" name="mb_post_title" value="%s" />', esc_attr( $post->post_title ) );
		echo '</p>';

		$terms = get_the_terms( $edit, 'forum' );
		$forum = array_shift( $terms );

		echo '<p>';
		printf( '<label for="mb_post_forum">%s</label>', __( 'Select a forum:', 'message-board' ) );
		wp_dropdown_categories(
			array(
				'name'          => 'mb_post_forum',
				'id'            => 'mb_post_forum',
				'selected'      => absint( $forum->term_id ),
				'hierarchical'  => true,
				'orderby'       => 'name',
				'hide_empty'    => false,
				'hide_if_empty' => true,
				'taxonomy'      => 'forum',
			//	'echo'          => false
			)
		);
		echo '</p>';
	}

	// content field
	echo '<p>';
	printf( '<label for="mb_post_content" name="mb_post_content">%s</label>', __( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ) );

	wp_editor(
		format_to_edit( mb_code_trick_reverse( $post->post_content ) ),
		'mb_post_content',
		array(
			'media_buttons' => false,
			'tinymce'       => false,
			'quicktags'     => false,
		)
	);

	echo '</p>';


	printf( '<p><input type="submit" value="%s" /></p>', esc_attr__( 'Submit', 'message-board' ) );
	printf( '<input type="hidden" name="mb_post_id" value="%s" />', absint( $edit ) );

	wp_nonce_field( 'mb_edit_post_action', 'mb_edit_post_nonce', false, true );
	echo '</fieldset>';
	echo '</form>';
}

function mb_get_edit_form() {

	$edit = get_query_var( 'edit' );

	if ( empty( $edit ) )
		return '';

	$post = get_post( $edit );

	if ( empty( $post ) || ( 'forum_topic' !== $post->post_type && 'forum_reply' !== $post->post_type ) )
		return;

	$edit = $post->ID;

	if ( !current_user_can( 'edit_post', $edit ) )
		return;

	$pt_object = get_post_type_object( $post->post_type );

	$form  = sprintf( '<form id="edit-post-form" method="post" action="%s">', mb_get_edit_form_action_url() );
	$form .= '<fieldset>';
	$form .= sprintf( '<legend>%s</legend>', $pt_object->labels->edit_item );

	// title field
	if ( 'forum_topic' === $post->post_type ) {

		$default_fields['title']  = '<p>';
		$default_fields['title'] .= sprintf( '<label for="mb_post_title">%s</label>', __( 'Topic title: (be brief and descriptive)', 'message-board' ) );
		$default_fields['title'] .= sprintf( '<input type="text" id="mb_post_title" name="mb_post_title" value="%s" />', esc_attr( $post->post_title ) );
		$default_fields['title'] .= '</p>';

		$terms = get_the_terms( $edit, 'forum' );
		$forum = array_shift( $terms );

		$default_fields['forum'] = '<p>';
		$default_fields['forum'] .= sprintf( '<label for="mb_post_forum">%s</label>', __( 'Select a forum:', 'message-board' ) );
		$default_fields['forum'] .= wp_dropdown_categories(
			array(
				'name'          => 'mb_post_forum',
				'id'            => 'mb_post_forum',
				'selected'      => absint( $forum->term_id ),
				'hierarchical'  => true,
				'orderby'       => 'name',
				'hide_empty'    => false,
				'hide_if_empty' => true,
				'taxonomy'      => 'forum',
				'echo'          => false
			)
		);
		$default_fields['forum'] .= '</p>';
	}

	// content field
	$default_fields['content']  = '<p>';
	$default_fields['content'] .= sprintf( '<label for="mb_post_content" name="mb_post_content">%s</label>', __( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ) );

	$default_fields['content'] .= sprintf( '<textarea id="mb_post_content" name="mb_post_content">%s</textarea>', format_to_edit( mb_code_trick_reverse( $post->post_content ) ) );
	$default_fields['content'] .= '</p>';

	$default_fields = apply_filters( 'mb_edit_form_fields', $default_fields );

	foreach ( $default_fields as $key => $field ) {

		$form .= $field;
	}

	$form .= sprintf( '<p><input type="submit" value="%s" /></p>', esc_attr__( 'Submit', 'message-board' ) );
	$form .= sprintf( '<input type="hidden" name="mb_post_id" value="%s" />', absint( $edit ) );

	$form .= wp_nonce_field( 'mb_edit_post_action', 'mb_edit_post_nonce', false, false );
	$form .= '</fieldset>';
	$form .= '</form>';

	return apply_filters( 'mb_get_edit_form', $form );
}