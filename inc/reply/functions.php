<?php

function mb_get_reply_post_type() {
	return apply_filters( 'mb_get_reply_post_type', 'forum_reply' );
}

function mb_get_topic_reply_ids( $topic_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %d ORDER BY menu_order ASC", mb_get_reply_post_type(), absint( $topic_id ) ) );
}

function mb_reset_reply_data( $post ) {

	$post = is_object( $post ) ? $post : get_post( $post );

	$topic_id         = $post->post_parent;
	$forum_id         = mb_get_topic_forum_id( $topic_id );

	$topic_last_reply = mb_get_topic_last_reply_id( $topic_id );
	$forum_last_reply = mb_get_forum_last_reply_id( $forum_id );

	/* Reset topic reply count. */
	mb_set_topic_reply_count( $topic_id );

	/* Reset topic voices. */
	mb_set_topic_voices( $topic_id );

	/* Reset reply positions. */
	mb_reset_reply_positions( $topic_id );

	/* Reset forum reply count. */
	mb_set_forum_reply_count( $forum_id );

	/* If this is the last topic reply, reset topic latest data. */
	if ( $post->ID === absint( $topic_last_reply ) )
		mb_reset_topic_latest( $topic_id );

	/* If this is the last reply, reset forum latest data. */
	if ( $post->ID === absint( $forum_last_reply ) )
		mb_reset_forum_latest( $forum_id );
}

/* Update all reply positions with a single query. */
function mb_reset_reply_positions( $topic_id ) {
	global $wpdb;

	$posts = $wpdb->get_results( 
		$wpdb->prepare( 
			"SELECT ID, menu_order FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %d ORDER BY post_date ASC", 
			mb_get_reply_post_type(), 
			absint( $topic_id ) 
		) 
	);

	if ( empty( $posts ) )
		return false;

	$post_ids = array();
	$i = 0;

	$update_sql = "UPDATE {$wpdb->posts} SET menu_order = CASE ID";

	foreach ( $posts as $post ) {
		$i++;

		$post_ids[] = $post->ID;

		$update_sql .= sprintf( " WHEN %d THEN %d", $post->ID, $i );
	}

	$update_sql .= " END WHERE ID IN (" . implode( ',', $post_ids ) . ")";

	$wpdb->query( $update_sql );
}


function mb_set_reply_statuses( $topic_id, $status ) {
	global $wpdb;

	$allowed_statuses = array( 'publish', 'trash', 'spam', 'orphan' );

	if ( !in_array( $status, $allowed_statuses ) )
		return false;

	$post_ids = $wpdb->get_results( 
		$wpdb->prepare( 
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %d ORDER BY post_date ASC", 
			mb_get_reply_post_type(), 
			absint( $topic_id ) 
		) 
	);

	if ( empty( $post_ids ) )
		return false;

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID IN (" . implode( ',', $post_ids ) . ")", $status ) );
}
