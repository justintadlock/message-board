<?php

function mb_get_forum_post_type() {
	return apply_filters( 'mb_get_forum_post_type', 'forum' );
}


function mb_set_forum_topic_count( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( empty( $topic_ids ) )
		return 0;

	$count = count( $topic_ids );

	if ( !empty( $count ) )
		update_post_meta( $forum_id, '_forum_topic_count', $count );

	return $count;
}

function mb_set_forum_reply_count( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( empty( $topic_ids ) )
		return 0;

	$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

	$count = !empty( $reply_ids ) ? count( $reply_ids ) : 0;

	if ( !empty( $count ) )
		update_post_meta( $forum_id, '_forum_reply_count', $count );

	return $count;
}

function mb_get_forum_topic_ids( $forum_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %s", mb_get_topic_post_type(), absint( $forum_id ) ) );
}
