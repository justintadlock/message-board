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

	update_post_meta( $forum_id, '_forum_reply_count', $count );

	return $count;
}

function mb_get_forum_topic_ids( $forum_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %s ORDER BY menu_order DESC", mb_get_topic_post_type(), absint( $forum_id ) ) );
}

function mb_reset_forum_latest( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( !empty( $topic_ids ) ) {

		$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

		if ( !empty( $reply_ids ) ) {
			$new_last_reply = array_shift( $reply_ids );
			$new_last_topic = mb_get_reply_topic_id( $new_last_reply );

			update_post_meta( $forum_id, '_forum_last_reply_id', $new_last_reply );
			update_post_meta( $forum_id, '_forum_last_topic_id', $new_last_topic );

			$new_last_date = get_post( $new_last_reply )->post_date;

			update_post_meta( $forum_id, '_forum_activity_datetime',       $new_last_date );
			update_post_meta( $forum_id, '_forum_activity_datetime_epoch', mysql2date( 'U', $new_last_date ) );
		} else {

			$new_last_topic = array_shift( $topic_ids );

			delete_post_meta( $forum_id, '_forum_last_reply_id' );

			update_post_meta( $forum_id, '_forum_last_topic_id', $new_last_topic );

			$new_last_date = get_post( $new_last_topic )->post_date;

			update_post_meta( $forum_id, '_forum_activity_datetime',       $new_last_date );
			update_post_meta( $forum_id, '_forum_activity_datetime_epoch', mysql2date( 'U', $new_last_date ) );
		}
	} else {
		delete_post_meta( $forum_id, '_forum_last_reply_id' );
		delete_post_meta( $forum_id, '_forum_last_topic_id' );
		delete_post_meta( $forum_id, '_forum_activity_datetime' );
		delete_post_meta( $forum_id, '_forum_activity_datetime_epoch' );
	}
}





