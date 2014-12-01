<?php

function mb_get_users_per_page() {
	return apply_filters( 'mb_get_users_per_page', 15 );
}

function mb_get_user_id( $user_id = 0 ) {
	global $mb_user;

	if ( is_numeric( $user_id ) && 0 < $user_id )
		$user_id = $user_id;
	elseif ( !empty( $mb_user ) && is_object( $mb_user ) )
		$user_id = $mb_user->ID;
	else
		$user_id  = get_current_user_id();

	return absint( $user_id );
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
