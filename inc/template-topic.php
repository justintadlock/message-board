<?php

/* ====== Topic Labels ====== */

function mb_topic_labels( $topic_id = 0 ) {
	echo mb_get_topic_labels( $topic_id );
}

function mb_get_topic_labels( $topic_id = 0 ) {
	$topic_id       = mb_get_topic_id( $topic_id );
	$labels = array();

	if ( mb_is_topic_sticky( $topic_id ) )
		$labels['sticky'] = __( '[Sticky]', 'message-board' );

	if ( !empty( $labels ) ) {

		$formatted = '';

		foreach ( $labels as $key => $value )
			$formatted .= sprintf( '<span class="topic-label %s">%s</span>', sanitize_html_class( "topic-label-{$key}" ), $value );

		return sprintf( '<span class="topic-labels">%s</span>', $formatted );
	}

	return '';
}

/* ====== Topic Sticky ====== */

function mb_is_topic_sticky( $topic_id = 0 ) {
	$topic_id       = mb_get_topic_id( $topic_id );
	$super_stickies = get_option( 'mb_super_sticky_topics', array() );
	$topic_stickies = get_option( 'mb_sticky_topics',       array() );
	$stickies       = array_merge( $super_stickies, $topic_stickies );

	return in_array( $topic_id, $stickies ) ? true : false;
}

function mb_is_topic_super_sticky( $topic_id = 0 ) {
	$topic_id       = mb_get_topic_id( $topic_id );
	$super_stickies = get_option( 'mb_super_sticky_topics', array() );

	return in_array( $topic_id, $super_stickies ) ? true : false;
}

/* ====== Topic ID ====== */

function mb_topic_id( $topic_id = 0 ) {
	echo mb_get_topic_id( $topic_id );
}

function mb_get_topic_id( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_id', mb_get_post_id( $topic_id ), $topic_id );
}

/* ====== Topic Content ====== */

function mb_topic_content( $topic_id = 0 ) {
	echo mb_get_topic_content( $topic_id );
}

function mb_get_topic_content( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_content', mb_get_post_content( $topic_id ), $topic_id );
}

/* ====== Topic Title ====== */

function mb_single_topic_title( $prefix = '', $echo = true ) {
	return apply_filters( 'mb_single_topic_title', single_post_title( $prefix, $echo ) );
}

function mb_topic_title( $topic_id = 0 ) {
	echo mb_get_topic_title( $topic_id );
}

function mb_get_topic_title( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_title', mb_get_post_title( $topic_id ), $topic_id );
}

/* ====== Topic URL ====== */

function mb_topic_url( $topic_id = 0 ) {
	echo mb_get_topic_url( $topic_id );
}

function mb_get_topic_url( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_url', mb_get_post_url( $topic_id ), $topic_id );
}

/* ====== Topic Author ====== */

function mb_topic_author_id( $topic_id = 0 ) {
	echo mb_get_topic_author_id( $topic_id );
}

function mb_get_topic_author_id( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_id', mb_get_post_author_id( $topic_id ), $topic_id );
}

function mb_topic_author( $topic_id = 0 ) {
	echo mb_get_topic_author( $topic_id );
}

function mb_get_topic_author( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_display_name', mb_get_post_author( $topic_id ), $topic_id );
}

function mb_topic_author_profile_url( $topic_id = 0 ) {
	echo mb_get_topic_author_profile_url( $topic_id );
}

function mb_get_topic_author_profile_url( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_profile_url', mb_get_post_author_profile_url( $topic_id ), $topic_id );
}

function mb_topic_author_profile_link( $topic_id = 0 ) {
	echo mb_get_topic_author_profile_link( $topic_id );
}

function mb_get_topic_author_profile_link( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_profile_link', mb_get_post_author_profile_link( $topic_id ), $topic_id );
}

/* ====== Topic Forum ====== */

function mb_get_topic_forum_id( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$terms    = get_the_terms( $topic_id, 'forum' );

	$forum    = is_array( $terms ) ? array_shift( $terms ) : false;
	$forum_id = is_object( $forum ) ? $forum->term_id : 0;

	return apply_filters( 'mb_get_topic_forum_id', $forum_id, $terms, $topic_id );
}

function mb_topic_forum_link( $topic_id = 0 ) {
	echo mb_get_topic_forum_link( $topic_id );
}

function mb_get_topic_forum_link( $topic_id = 0 ) {
	$forum_id   = mb_get_topic_forum_id( $topic_id );
	$forum_link = mb_get_forum_link( $forum_id );

	return apply_filters( 'mb_get_topic_forum_link', $forum_link, $forum_id, $topic_id );
}

/* ====== Last Activity ====== */

/**
 * Prints the topic last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_last_active_time( $topic_id = 0 ) {
	echo mb_get_topic_last_active_time( $topic_id );
}

/**
 * Returns the topic last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_last_active_time( $topic_id = 0 ) {

	$topic_id   = mb_get_topic_id( $topic_id );
	$time       = get_post_meta( $topic_id, '_topic_activity_datetime', true );
	$mysql_date = mysql2date( 'U', $time );
	$now        = current_time( 'timestamp' );

	return apply_filters( 'mb_get_topic_last_active_time', human_time_diff( $mysql_date, $now ), $time, $topic_id );
}

/* ====== Last Reply ID ====== */

function mb_topic_last_reply_id( $topic_id = 0 ) {
	echo mb_get_topic_last_reply_id( $topic_id );
}

/**
 * Returns the last topic reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @retrn  int
 */
function mb_get_topic_last_reply_id( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$reply_id = get_post_meta( $topic_id, '_topic_last_reply_id', true );

	$mb_reply_id = !empty( $reply_id ) && is_numeric( $reply_id ) ? absint( $reply_id ) : 0;

	return apply_filters( 'mb_get_topic_last_reply_id', $mb_reply_id, $topic_id );
}

/* ====== Last Post Author ====== */

function mb_topic_last_poster( $topic_id = 0 ) {
	echo mb_get_topic_last_poster( $topic_id );
}

function mb_get_topic_last_poster( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$reply_id = mb_get_topic_last_reply_id( $topic_id );

	$author = !empty( $reply_id ) ? mb_get_reply_author( $reply_id ) : mb_get_topic_author( $topic_id );

	return apply_filters( 'mb_get_topic_last_poster', $author, $reply_id, $topic_id );
}

/* ====== Last Post URL ====== */

function mb_topic_last_post_url( $topic_id = 0 ) {
	echo mb_get_topic_last_post_url( $topic_id );
}

function mb_get_topic_last_post_url( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$reply_id = mb_get_topic_last_reply_id( $topic_id );

	$url = !empty( $reply_id ) ? mb_get_reply_url( $reply_id ) : mb_get_topic_url( $topic_id );

	return apply_filters( 'mb_get_topic_last_post_url', $url, $reply_id, $topic_id );
}

/* ====== Post/Reply Count ====== */

function mb_topic_reply_count( $topic_id = 0 ) {
	echo mb_get_topic_reply_count( $topic_id );
}

function mb_get_topic_reply_count( $topic_id = 0 ) {
	$topic_id    = mb_get_topic_id( $topic_id );
	$reply_count = get_post_meta( $topic_id, '_topic_reply_count', true );

	return apply_filters( 'mb_get_topic_reply_count', absint( $reply_count ), $topic_id );
}

function mb_topic_post_count( $topic_id = 0 ) {
	echo mb_get_topic_post_count( $topic_id );
}

function mb_get_topic_post_count( $topic_id = 0 ) {
	$post_count = 1 + mb_get_topic_reply_count( $topic_id );

	return apply_filters( 'mb_get_topic_post_count', $post_count, $topic_id );
}

/* ====== Topic Voices ====== */

function mb_topic_voice_count( $topic_id = 0 ) {
	echo mb_get_topic_voice_count( $topic_id );
}

function mb_get_topic_voice_count( $topic_id = 0 ) {
	$topic_id     = mb_get_topic_id( $topic_id );
	$voice_count  = get_post_meta( $topic_id, '_topic_voice_count' );

	$voice_count = $voice_count ? absint( $voice_count ) : count( mb_get_topic_voices( $topic_id ) );

	return apply_filters( 'mb_get_topic_voice_count', $voice_count, $topic_id );
}

function mb_get_topic_voices( $topic_id = 0 ) {
	$topic_id     = mb_get_topic_id( $topic_id );
	$topic_voices = get_post_meta( $topic_id, '_topic_voices' );

	$voices = !empty( $voices ) ? $voices : array( mb_get_topic_author_id( $topic_id ) );

	return apply_filters( 'mb_get_topic_voices', $voices, $topic_id );
}

/* ====== Pagination ====== */

function mb_is_topic_paged() {

	if ( !is_singular( 'forum_topic' ) )
		return false;

	return is_paged() ? true : false;
}

function mb_topic_pagination( $args = array() ) {
	global $wp_rewrite, $wp_query;

	$query = message_board()->reply_query;

	/* If there's not more than one page, return nothing. */
	if ( 1 >= $query->max_num_pages )
		return;

	/* Get the current page. */
	$current = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );

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
		//'prev_text'  => __( '&laquo; Previous' ), // This is the WordPress default.
		//'next_text'  => __( 'Next &raquo;' ), // This is the WordPress default.
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
	if ( $wp_rewrite->using_permalinks() )
		$defaults['base'] = user_trailingslashit( trailingslashit( get_pagenum_link() ) . "{$pagination_base}/%#%" );

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

/* ====== Topic Form ====== */

function mb_topic_form_url() {
	echo mb_get_topic_form_url();
}

function mb_get_topic_form_url() {
	return apply_filters( 'mb_topic_form_url', esc_url( '#topic-form' ) );
}

function mb_topic_form_link( $args = array() ) {
	echo mb_get_topic_form_link( $args );
}

function mb_get_topic_form_link( $args = array() ) {

	if ( !current_user_can( 'create_forum_topics' ) )
		return '';

	$defaults = array(
		'text' => __( 'New Topic &rarr;', 'message-board' ),
		'wrap' => '<a %s>%s</a>',
		'before' => '',
		'after' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$attr = sprintf( 'class="new-topic-link new-topic" href="%s"', mb_get_topic_form_url() );

	$link = sprintf( $args['before'] . $args['wrap'] . $args['after'], $attr, $args['text'] );

	return apply_filters( 'mb_get_topic_form_link', $link, $args );
}

function mb_topic_form() {
	echo mb_get_topic_form();
}

function mb_get_topic_form() {

	if ( !current_user_can( 'create_forum_topics' ) )
		return; 

	$form  = sprintf( '<form id="topic-form" method="post" action="%s">', mb_get_topic_form_action_url() );
	$form .= '<fieldset>';
	$form .= sprintf( '<legend>%s</legend>', __( 'Add New Topic', 'message-board' ) );

	// title field
	$default_fields['title']  = '<p>';
	$default_fields['title'] .= sprintf( '<label for="mb_topic_title">%s</label>', __( 'Title', 'message-board' ) );
	$default_fields['title'] .= '<input type="text" id="mb_topic_title" name="mb_topic_title" />';
	$default_fields['title'] .= '</p>';

	// forum field
	if ( !is_tax( 'forum' ) ) {
		$default_fields['forum'] = '<p>';
		$default_fields['forum'] .= sprintf( '<label for="mb_topic_forum">%s</label>', __( 'Forum', 'message-board' ) );
		$default_fields['forum'] .= wp_dropdown_categories(
			array(
				'name'          => 'mb_topic_forum',
				'id'            => 'mb_topic_forum',
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
	$default_fields['content'] .= sprintf( '<label for="mb_topic_content" name="mb_topic_content">%s</label>', __( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ) );
	$default_fields['content'] .= '<textarea id="mb_topic_content" name="mb_topic_content"></textarea>';
	$default_fields['content'] .= '</p>';

	$default_fields = apply_filters( 'mb_topic_form_fields', $default_fields );

	foreach ( $default_fields as $key => $field ) {

		if ( is_tax( 'forum' ) && 'forum' === $key ) {
			continue;
		}

		$form .= $field;
	}

	if ( is_tax( 'forum' ) )
		$form .= sprintf( '<input type="hidden" name="mb_topic_forum" value="%s" />', absint( get_queried_object_id() ) );

	$form .= sprintf( '<p><input type="submit" value="%s" /></p>', esc_attr__( 'Submit', 'message-board' ) );
	$form .= wp_nonce_field( 'mb_new_topic_action', 'mb_new_topic_nonce', false, false );
	$form .= '</fieldset>';
	$form .= '</form>';

	return apply_filters( 'mb_get_topic_form', $form );
}

function mb_topic_form_action_url() {
	echo mb_get_topic_form_action_url();
}

function mb_get_topic_form_action_url() {
	return esc_url( add_query_arg( 'message-board', 'new-topic', trailingslashit( home_url() ) ) );
}

/* ====== Topic Subscriptions ====== */

function mb_topic_subscribe_url( $topic_id = 0 ) {
	echo mb_get_topic_subscribe_url( $topic_id );
}

function mb_get_topic_subscribe_url( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'subscribe', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_subscribe_url', $url, $topic_id );
}

function mb_topic_unsubscribe_url( $topic_id = 0 ) {
	echo mb_get_topic_unsubscribe_url( $topic_id );
}

function mb_get_topic_unsubscribe_url( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'unsubscribe', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_unsubscribe_url', $url, $topic_id );
}

function mb_topic_subscribe_link( $topic_id = 0 ) {
	echo mb_get_topic_subscribe_link( $topic_id );
}

function mb_get_topic_subscribe_link( $topic_id = 0 ) {

	$topic_id = mb_get_topic_id( $topic_id );

	if ( !mb_is_user_subscribed_to_topic( get_current_user_id(), $topic_id ) ) {

		$link = sprintf( 
			'<a class="subscribe-link" href="%s">%s</a>', 
			mb_get_topic_subscribe_url( $topic_id ), 
			__( 'Subscribe', 'message-board' ) 
		);

	} else {
		$link = sprintf( 
			'<a class="subscribe-link" href="%s">%s</a>', 
			mb_get_topic_unsubscribe_url( $topic_id ),
			__( 'Unsubscribe', 'message-board' ) 
		);
	}

	return $link;
}

function mb_is_user_subscribed_to_topic( $user_id, $topic_id ) {

	$subscriptions = get_user_meta( $user_id, '_topic_subscriptions', true );

	$subs = explode( ',', $subscriptions );

	return in_array( $topic_id, $subs ) ? true : false;
}

/* @todo - Delete cache when user un/subscribes. */
function mb_get_topic_subscribers( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) )
		return;

	global $wpdb;

	$users = wp_cache_get( 'mb_get_topic_subscribers_' . $topic_id, 'message-board-users' );

	if ( false === $users ) {
		$users = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_topic_subscriptions' and FIND_IN_SET( '{$topic_id}', meta_value ) > 0" );
		wp_cache_set( 'mb_get_topic_subscribers_' . $topic_id, $users, 'message-board-users' );
	}

	return apply_filters( 'mb_get_topic_subscribers', $users );
}

/* ====== Topic Favorites ====== */

function mb_topic_favorite_url( $topic_id = 0 ) {
	echo mb_get_topic_favorite_url( $topic_id );
}

function mb_get_topic_favorite_url( $topic_id = 0 ) {

	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'favorite', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_favorite_url', $url, $topic_id );
}

function mb_topic_unfavorite_url( $topic_id = 0 ) {
	echo mb_get_topic_unfavorite_url( $topic_id );
}

function mb_get_topic_unfavorite_url( $topic_id = 0 ) {

	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'unfavorite', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_unfavorite_url', $url, $topic_id );
}

function mb_topic_favorite_link( $topic_id = 0 ) {
	echo mb_get_topic_favorite_link( $topic_id );
}

function mb_get_topic_favorite_link( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( !mb_is_user_favorite_topic( get_current_user_id(), $topic_id ) ) {
		$link = sprintf( 
			'<a class="favorite-link" href="%s">%s</a>', 
			mb_get_topic_favorite_url( $topic_id ), 
			__( 'Favorite', 'message-board' ) 
		);
	}
	else {
		$link = sprintf( 
			'<a class="favorite-link" href="%s">%s</a>', 
			mb_get_topic_unfavorite_url( $topic_id ), 
			__( 'Unfavorite', 'message-board' ) 
		);
	}

	return $link;
}

function mb_is_user_favorite_topic( $user_id, $topic_id ) {

	$favorites = get_user_meta( $user_id, '_topic_favorites', true );

	$favs = explode( ',', $favorites );

	return in_array( $topic_id, $favs ) ? true : false;
}

/* @todo - Delete cache when user un/favorites. */
function mb_get_topic_favoriters( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) )
		return;

	global $wpdb;

	$key   = '_topic_favorites';
	$users = wp_cache_get( 'mb_get_topic_favoriters_' . $topic_id, 'message-board-users' );
	if ( false === $users ) {
		$users = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$key}' and FIND_IN_SET('{$topic_id}', meta_value) > 0" );
		wp_cache_set( 'mb_get_topic_favoriters_' . $topic_id, $users, 'message-board-users' );
	}

	return apply_filters( 'mb_get_topic_favoriters', $users );
}
