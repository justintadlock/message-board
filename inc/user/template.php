<?php

function mb_user_query() {
	$mb = message_board();

	/* If a query has already been created, let's roll. */
	if ( !is_null( $mb->user_query ) ) {

		if ( $mb->user_query->current_user + 1 <= $mb->user_query->found_users )
			return true;

		return false;
	}

	$page = is_paged() ? absint( get_query_var( 'paged' ) ) : 1;

	$offset = 1 === $page ? '' : ( $page - 1 ) * mb_get_users_per_page();

	$mb->user_query = new WP_User_Query(
		array(
			'orderby'      => 'login',
			'order'        => 'ASC',
			'offset'       => $offset,
			'role'         => get_query_var( 'mb_role' ) ? sanitize_key( get_query_var( 'mb_role' ) ) : '',
			'search'       => '',
			'number'       => mb_get_users_per_page(),
			'count_total'  => true,
			'fields'       => 'all',
			'who'          => ''
		)
	);

	$mb->user_query->found_users = count( $mb->user_query->results );
	$mb->user_query->current_user = 0;

	return !empty( $mb->user_query->results ) ? true : false;
}

function mb_the_user() {
	$mb = message_board();

	$current = $mb->user_query->current_user++;

	$mb->user_query->loop_user_id = $mb->user_query->results[ $current ]->ID;
}

/**
 * Checks if viewing the user archive page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_user_archive() {
	$is_user_archive = get_query_var( 'mb_custom' ) && 'users' === get_query_var( 'mb_custom' ) ? true : false;

	return $is_user_archive && !is_author() ? true : false;
}

/**
 * Checks if viewing a user role archive page.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function mb_is_user_role_archive( $role = '' ) {

	if ( !mb_is_user_archive() )
		return false;

	$q_role = get_query_var( 'mb_role' );

	if ( empty( $role ) && $q_role )
		return true;

	if ( $q_role === $role || $q_role === "mb_{$role}" )
		return true;

	return false;
}

/**
 * Checks if viewing a single user page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_single_user() {
	$is_user_archive = get_query_var( 'mb_custom' ) && 'users' === get_query_var( 'mb_custom' ) ? true : false;

	return $is_user_archive && is_author() ? true : false;
}

function mb_is_user_page( $page = '' ) {

	if ( !mb_is_single_user() )
		return false;

	elseif ( empty( $page ) )
		return true;

	foreach ( (array) $page as $_p ) {

		if ( get_query_var( 'mb_user_page' ) === $_p )
			return true;
	}

	return false;
}

function mb_user_id( $user_id = 0 ) {
	echo mb_get_user_id( $user_id );
}

function mb_get_user_id( $user_id = 0 ) {

	if ( is_numeric( $user_id ) && 0 < $user_id )
		$user_id = $user_id;

	elseif ( !is_null( message_board()->user_query ) )
		$user_id = message_board()->user_query->loop_user_id;

	elseif ( get_query_var( 'author' ) )
		$user_id = get_query_var( 'author' );

	elseif ( get_query_var( 'user_id' ) )
		$user_id = get_query_var( 'user_id' );

	else
		$user_id  = get_current_user_id();

	return absint( $user_id );
}

function mb_single_user_title() {
	echo mb_get_single_user_title();
}

function mb_get_single_user_title() {
	return apply_filters( 'mb_get_single_user_title', get_the_author_meta( 'display_name', mb_get_user_id() ) );
}

function mb_user_page_title() {
	echo mb_get_user_page_title();
}

function mb_get_user_page_title() {

	$name = get_the_author_meta( 'display_name', mb_get_user_id() );

	if ( mb_is_user_page( 'forums' ) )
		$title = __( '%s: Forums Created', 'message-board' );
	elseif ( mb_is_user_page( 'topics' ) )
		$title = __( '%s: Topics Created', 'message-board' );
	elseif ( mb_is_user_page( 'replies' ) )
		$title = __( '%s: Replies Created', 'message-board' );
	elseif ( mb_is_user_page( 'forum-subscriptions' ) )
		$title = __( '%s: Forum Subscriptions', 'message-board' );
	elseif ( mb_is_user_page( 'topic-subscriptions' ) )
		$title = __( '%s: Topic Subscriptions', 'message-board' );
	elseif ( mb_is_user_page( 'bookmarks' ) )
		$title = __( '%s: Topic Bookmarks', 'message-board' );
	else
		$title = mb_get_single_user_title();

	return apply_filters( 'mb_get_user_page_title', sprintf( $title, get_the_author_meta( 'display_name', mb_get_user_id() ) ) );
}

function mb_user_archive_title() {
	echo mb_get_user_archive_title();
}

function mb_get_user_archive_title() {

	$title = mb_is_user_role_archive() ? mb_get_user_role_archive_title() : __( 'Users', 'message-board' );

	return apply_filters( 'mb_get_user_archive_title', $title );
}

function mb_user_role_archive_title() {
	echo mb_get_user_role_archive_title();
}

function mb_get_user_role_archive_title() {

	$name = mb_get_role_name( get_query_var( 'mb_role' ) );

	return apply_filters( 'mb_get_user_role_archive_title', sprintf( __( 'Users: %s Role', 'message-board' ), $name ) );
}

function mb_user_archive_url() {
	echo mb_get_user_archive_url();
}

function mb_get_user_archive_url() {
	global $wp_rewrite;

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( home_url( mb_get_user_slug() ) );
	else
		$url = add_query_arg( 'mb_custom', 'users', home_url() );

	return apply_filters( 'mb_get_user_archive_url', $url );
}

function mb_user_archive_link() {
	echo mb_get_user_archive_link();
}

function mb_get_user_archive_link() {

	$link = sprintf( '<a class="mb-user-archive-link" href="%s">%s</a>', mb_get_user_archive_url(), __( 'Users', 'message-board' ) );

	return apply_filters( 'mb_get_user_archive_link', $link );
}

function mb_user_edit_url( $user_id = 0 ) {
	echo mb_get_user_edit_url( $user_id );
}

function mb_get_user_edit_url( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	return apply_filters( 'mb_get_user_edit_url', get_edit_user_link( $user_id ), $user_id );
}

function mb_user_edit_link( $user_id = 0 ) {
	echo mb_get_user_edit_link( $user_id );
}

function mb_get_user_edit_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = '';

	if ( current_user_can( 'edit_user', $user_id ) ) {
		$url  = mb_get_user_edit_url( $user_id );

		if ( !empty( $url ) )
			$link = sprintf( '<a href="%s" class="user-edit-link edit-link">%s</a>', $url, __( 'Edit', 'message-board' ) );
	}

	return apply_filters( 'mb_get_topic_edit_link', $link, $user_id );
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
	echo mb_get_user_topic_count( $user_id );
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
	echo mb_get_user_reply_count( $user_id );
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

function mb_loop_user_pagination( $args = array() ) {
	$total_users = message_board()->user_query->total_users;
	$max_pages   = ceil( $total_users / mb_get_users_per_page() );
	$query = array( 'max_num_pages' => $max_pages );
	return mb_pagination( $args, (object) $query );
}

function mb_user_profile_url( $user_id = 0 ) {
	echo mb_get_user_profile_url( $user_id );
}

function mb_get_user_profile_url( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

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

/**
 * Displays the edit user form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_user_edit_form() {
	mb_get_template_part( 'form-user', 'edit' );
}

function mb_is_user_profile_edit() {

	$user_id = mb_get_user_id();
	$your_id = get_current_user_id();

	return mb_is_user_edit() && $user_id === $your_id ? true : false;
}

function mb_get_user_contact_methods() {

	$methods = array();

	return apply_filters( 'mb_get_user_contact_methods', $methods );
}
