<?php

/**
 * Checks if viewing the user archive page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_user_archive() {
	return get_query_var( 'mb_custom' ) && 'users' === get_query_var( 'mb_custom' ) ? true : false;
}

/**
 * Checks if viewing a single user page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_single_user() {
	$is_user_page = get_query_var( 'mb_custom' ) && 'users' === get_query_var( 'mb_custom' ) ? true : false;

	return $is_user_page && is_author() ? true : false;
}

function mb_get_users_per_page() {
	return apply_filters( 'mb_get_users_per_page', 15 );
}

function mb_get_user_id( $user_id = 0 ) {
	global $mb_user;

	if ( is_numeric( $user_id ) && 0 < $user_id )
		$user_id = $user_id;
	elseif ( !empty( $mb_user ) && is_object( $mb_user ) )
		$user_id = $mb_user->ID;
	elseif ( get_query_var( 'author' ) )
		$user_id = get_query_var( 'author' );
	else
		$user_id  = get_current_user_id();

	return absint( $user_id );
}

function mb_single_user_title( $prefix = '', $echo = true ) {
	$title = apply_filters( 'mb_single_user_title', $prefix . get_the_author_meta( 'display_name', get_query_var( 'author' ) ) );

	if ( false === $echo )
		return $title;

	echo $title;
}

function mb_get_users() {

	$mb = message_board();

	$page = is_paged() ? absint( get_query_var( 'paged' ) ) : 1;

	$offset = 1 === $page ? '' : ( $page - 1 ) * mb_get_users_per_page();

	$mb->users_query = new WP_User_Query(
		array(
			'orderby'      => 'login',
			'order'        => 'ASC',
			'offset'       => $offset,
			'search'       => '',
			'number'       => mb_get_users_per_page(),
			'count_total'  => true,
			'fields'       => 'all',
			'who'          => ''
		)
	);

	return $mb->users_query->results;
}

function mb_user_forum_count( $user_id = 0 ) {
	echo mb_get_user_forum_count();
}

function mb_get_user_forum_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$count = get_user_meta( $user_id, mb_get_user_forum_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_set_user_forum_count( $user_id );

	$count = !empty( $count ) ? absint( $count ) : 0;

	return apply_filters( 'mb_get_user_forum_count', $count, $user_id );
}

function mb_user_topic_count( $user_id = 0 ) {
	echo mb_get_user_topic_count();
}

function mb_get_user_topic_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$count = get_user_meta( $user_id, mb_get_user_topic_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_set_user_topic_count( $user_id );

	$count = !empty( $count ) ? absint( $count ) : 0;

	return apply_filters( 'mb_get_user_topic_count', $count, $user_id );
}

function mb_user_reply_count( $user_id = 0 ) {
	echo mb_get_user_reply_count();
}

function mb_get_user_reply_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$count = get_user_meta( $user_id, mb_get_user_reply_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_set_user_reply_count( $user_id );

	$count = !empty( $count ) ? absint( $count ) : 0;

	return apply_filters( 'mb_get_user_reply_count', $count, $user_id );
}

function mb_user_post_count( $user_id = 0 ) {
	echo mb_get_user_post_count();
}

function mb_get_user_post_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$topic_count = mb_get_user_topic_count( $user_id );
	$reply_count = mb_get_user_reply_count( $user_id );

	$count = $topic_count + $reply_count;

	return apply_filters( 'mb_get_user_post_count', $count, $user_id );
}

function mb_users_pagination( $args = array() ) {
	$total_users = message_board()->users_query->total_users;
	$max_pages   = ceil( $total_users / mb_get_users_per_page() );
	$query = array( 'max_num_pages' => $max_pages );
	return mb_pagination( $args, (object) $query );
}

function mb_user_profile_url( $user_id = 0 ) {
	echo mb_get_user_profile_url( $user_id );
}

function mb_get_user_profile_url( $user_id = 0 ) {

	$nicename = get_the_author_meta( 'user_nicename', $user_id );

	$profile_url = esc_url( home_url( trailingslashit( mb_get_user_slug() ) . $nicename ) );

	return apply_filters( 'mb_get_user_profile_url', esc_url( $profile_url ), $user_id );
}

function mb_user_profile_link( $user_id = 0 ) {
	echo mb_get_user_profile_link( $user_id );
}

function mb_get_user_profile_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );
	$url  = mb_get_user_profile_url( $user_id );
	$name = get_the_author_meta( 'display_name', $user_id );
	$link = sprintf( '<a class="user-profile-link" href="%s">%s</a>', esc_url( $url ), $name );

	return apply_filters( 'mb_get_user_profile_link', $link, $user_id );
}

function mb_user_page_url( $slug = '', $user_id = 0 ) {
	echo mb_get_user_page_url( $slug, $user_id );
}

function mb_get_user_page_url( $slug = '', $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$profile_url = mb_get_user_profile_url( $user_id );

	if ( !empty( $slug ) ) {
		$url = user_trailingslashit( trailingslashit( $profile_url ) . $slug );
	} else {
		$url = $profile_url;
	}

	return apply_filters( 'mb_get_user_page_url', $url, $user_id, $slug );
}

function mb_user_page_link( $slug = '', $user_id = 0 ) {
	echo mb_get_user_page_link( $slug, $user_id );
}

function mb_get_user_page_link( $slug = '', $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$url = mb_get_user_page_url( $slug, $user_id );

	if ( !empty( $slug ) ) {
		$class = "user-{$slug}-link";
		$title = $slug; //temp
	} else {
		$class = 'user-profile-link';
		$title = __( 'Profile', 'message-board' );
	}

	$link = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $url ), sanitize_html_class( $class ), $title );

	return apply_filters( 'mb_get_user_page_link', $link, $user_id, $slug );
}

function mb_user_topics_url( $user_id = 0 ) {
	echo mb_get_user_topics_url( $user_id );
}

function mb_get_user_topics_url( $user_id = 0 ) {

	$url = mb_get_user_profile_url( $user_id );

	return esc_url( trailingslashit( $url ) . 'topics' );
}

function mb_user_bookmarks_url( $user_id = 0 ) {
	echo mb_get_user_bookmarks_url( $user_id );
}

function mb_get_user_bookmarks_url( $user_id = 0 ) {

	$url = trailingslashit( mb_get_user_profile_url( $user_id ) ) . 'bookmarks';

	return apply_filters( 'mb_get_user_bookmarks_url', esc_url( $url ), $user_id );
}

function mb_user_subscriptions_url( $user_id = 0 ) {
	echo mb_get_user_subscriptions_url( $user_id );
}

function mb_get_user_subscriptions_url( $user_id = 0 ) {

	$url = trailingslashit( mb_get_user_profile_url( $user_id ) ) . 'subscriptions';

	return apply_filters( 'mb_get_user_subscriptions_url', esc_url( $url ), $user_id );
}
