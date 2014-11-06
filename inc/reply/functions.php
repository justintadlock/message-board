<?php

function mb_get_reply_post_type() {
	return apply_filters( 'mb_get_reply_post_type', 'forum_reply' );
}

function mb_get_topic_reply_ids( $topic_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %d ORDER BY menu_order ASC", mb_get_reply_post_type(), absint( $topic_id ) ) );
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
