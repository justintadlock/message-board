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

function mb_get_content_type( $post_id = 0 ) {

	$post_type = get_post_type( $post_id );

	if ( mb_get_forum_post_type() === $post_type )
		$type = 'forum';
	elseif ( mb_get_topic_post_type() === $post_type )
		$type = 'topic';
	elseif ( mb_get_reply_post_type() === $post_type )
		$type = 'reply';
	else
		$type = $post_type;

	return $type;
}

/* ====== Login ====== */

function mb_login_url() {
	echo mb_get_login_url();
}

function mb_get_login_url() {
	return esc_url( home_url( mb_get_login_slug() ) );
}

function mb_login_link() {
	echo mb_get_login_link();
}

function mb_get_login_link() {
	return sprintf( '<a href="%s" class="login-link">%s</a>', mb_get_login_url(), __( 'Log In', 'message-board' ) );
}

/* ====== Thread Position ====== */

function mb_thread_position( $post_id = 0 ) {
	echo mb_get_thread_position( $post_id );
}

function mb_get_thread_position( $post_id = 0 ) {
	$post_id   = mb_get_post_id( $post_id );
	$post_type = get_post_type( $post_id );

	$position = mb_get_reply_post_type() === $post_type ? mb_get_reply_position( $post_id ) + 1 : 1;

	return apply_filters( 'mb_get_thread_position', $position );
}

/* ====== Forum Front Page ====== */

function mb_board_url() {
	echo mb_get_board_url();
}

function mb_get_board_url() {
	echo mb_get_board_home_url();
}

function mb_board_home_url() {
	echo mb_get_board_home_url();
}

function mb_get_board_home_url() {

	if ( 'forums' === mb_get_show_on_front() )
		$url = get_post_type_archive_link( mb_get_forum_post_type() );
	elseif ( 'topics' === mb_get_show_on_front() )
		$url = get_post_type_archive_link( mb_get_topic_post_type() );
	else
		$url = home_url( mb_get_root_slug() );

	return apply_filters( 'mb_get_board_home_url', $url );
}

/* ====== Post Status ====== */

function mb_dropdown_post_status( $args = array() ) {

	$defaults = array(
		'post_type' => mb_get_forum_post_type(),
		'exclude'   => '',
		'name'      => 'post_status',
		'id'        => 'post_status',
		'selected'  => '',
		'echo'      => true
	);

	$args = wp_parse_args( $args, $defaults );

	if ( mb_get_forum_post_type() === $args['post_type'] )
		$stati = mb_get_forum_post_statuses();

	elseif ( mb_get_topic_post_type() === $args['post_type'] )
		$stati = mb_get_topic_post_statuses();

	elseif ( mb_get_reply_post_type() === $args['post_type'] )
		$stati = mb_get_reply_post_statuses();

	if ( is_array( $args['exclude'] ) )
		$stati = array_diff( $stati, $args['exclude'] );

	$out = sprintf( '<select name="%s" id="%s">', sanitize_html_class( $args['name'] ), sanitize_html_class( $args['id'] ) );

	foreach ( $stati as $status ) {
		$status_obj = get_post_status_object( $status );

		$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $status_obj->name ), selected( $status_obj->name, $args['selected'], false ), $status_obj->label );
	}

	$out .= '</select>';

	if ( !$args['echo'] )
		return $out;

	echo $out;
}

/* ====== Post ID ====== */

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

	$url = mb_get_topic_post_type() === get_post_type( $post_id ) ? esc_url( get_permalink( $post_id ) . '#post-' . get_the_ID() ) : get_permalink( $post_id );

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

	if ( empty( $post ) || ( mb_get_topic_post_type() !== $post->post_type && mb_get_reply_post_type() !== $post->post_type ) )
		return;

	$edit = $post->ID;

	if ( !current_user_can( 'edit_post', $edit ) )
		return;

	$pt_object = get_post_type_object( $post->post_type );

	printf( '<form id="edit-post-form" method="post" action="%s">', mb_get_edit_form_action_url() );
	echo '<fieldset>';
	printf( '<legend>%s</legend>', $pt_object->labels->edit_item );

	// title field
	if ( mb_get_topic_post_type() === $post->post_type ) {

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

	if ( empty( $post ) || ( mb_get_topic_post_type() !== $post->post_type && mb_get_reply_post_type() !== $post->post_type ) )
		return;

	$edit = $post->ID;

	if ( !current_user_can( 'edit_post', $edit ) )
		return;

	$pt_object = get_post_type_object( $post->post_type );

	$form  = sprintf( '<form id="edit-post-form" method="post" action="%s">', mb_get_edit_form_action_url() );
	$form .= '<fieldset>';
	$form .= sprintf( '<legend>%s</legend>', $pt_object->labels->edit_item );

	// title field
	if ( mb_get_topic_post_type() === $post->post_type ) {

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

/**
 * Outputs pagination links for single topic pages (the replies are paginated).
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @param  object $query
 * @return string
 */
function mb_pagination( $args = array(), $query = null ) {
	global $wp_rewrite;

	if ( is_null( $query ) ) {
		global $wp_query;
		$query = $wp_query;
	}

	/* If there's not more than one page, return nothing. */
	if ( 1 >= $query->max_num_pages )
		return;

	/* Get the current page. */
	$current = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

	/* Get the max number of pages. */
	$max_num_pages = intval( $query->max_num_pages );

	/* Get the pagination base. */
	$pagination_base = $wp_rewrite->pagination_base;

	/* Set up some default arguments for the paginate_links() function. */
	$defaults = array(
		'base'         => add_query_arg( 'paged', '%#%' ),
		'format'       => '',
		'total'        => $max_num_pages,
		'current'      => $current,
		'prev_next'    => true,
		'show_all'     => false,
		'end_size'     => 1,
		'mid_size'     => 1,
		'add_fragment' => '',
		'type'         => 'plain',

		// Begin loop_pagination() arguments.
		'before'       => '<nav class="pagination loop-pagination">',
		'after'        => '</nav>',
		'echo'         => true,
	);

	/* Add the $base argument to the array if the user is using permalinks. */
	if ( $wp_rewrite->using_permalinks() && !is_search() ) {
		$big = 999999999;
		$defaults['base'] = str_replace( $big, '%#%', get_pagenum_link( $big ) );
	}

	/* Merge the arguments input with the defaults. */
	$args = wp_parse_args( $args, $defaults );

	/* Don't allow the user to set this to an array. */
	if ( 'array' == $args['type'] )
		$args['type'] = 'plain';

	/* Get the paginated links. */
	$page_links = paginate_links( $args );

	/* Remove 'page/1' from the entire output since it's not needed. */
	$page_links = preg_replace( 
		array( 
			"#(href=['\"].*?){$pagination_base}/1(['\"])#",  // 'page/1'
			"#(href=['\"].*?){$pagination_base}/1/(['\"])#", // 'page/1/'
			"#(href=['\"].*?)\?paged=1(['\"])#",             // '?paged=1'
			"#(href=['\"].*?)&\#038;paged=1(['\"])#"         // '&#038;paged=1'
		), 
		'$1$2', 
		$page_links 
	);

	/* Wrap the paginated links with the $before and $after elements. */
	$page_links = $args['before'] . $page_links . $args['after'];

	/* Return the paginated links for use in themes. */
	if ( $args['echo'] )
		echo $page_links;
	else
		return $page_links;
}
