<?php

function mb_get_topic_post_type() {
	return apply_filters( 'mb_get_topic_post_type', 'forum_topic' );
}

function mb_get_multi_topic_reply_ids( $topic_ids ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'  AND post_parent IN ( " . implode( ',', $topic_ids ) . " )", mb_get_reply_post_type() ) );
}

function mb_get_topic_subscribers( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) )
		return;

	$users = wp_cache_get( 'mb_get_topic_subscribers_' . $topic_id, 'message-board-users' );

	if ( false === $users ) {
		$users = mb_set_topic_subscribers( $topic_id );
	}

	return apply_filters( 'mb_get_topic_subscribers', $users );
}

function mb_set_topic_subscribers( $topic_id ) {
	global $wpdb;

	$users = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_topic_subscriptions' and FIND_IN_SET( '{$topic_id}', meta_value ) > 0" );
	wp_cache_set( 'mb_get_topic_subscribers_' . $topic_id, $users, 'message-board-users' );

	return $users;
}

function mb_get_topic_favoriters( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) )
		return;

	$users = wp_cache_get( 'mb_get_topic_favoriters_' . $topic_id, 'message-board-users' );

	if ( false === $users ) {
		$users = mb_set_topic_favoriters();
	}

	return apply_filters( 'mb_get_topic_favoriters', $users );
}

function mb_set_topic_favoriters( $topic_id ) {
	global $wpdb;

	$users = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_topic_favorites' and FIND_IN_SET( '{$topic_id}', meta_value ) > 0" );
	wp_cache_set( 'mb_get_topic_favoriters_' . $topic_id, $users, 'message-board-users' );

	return $users;
}
