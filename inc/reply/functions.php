<?php

function mb_get_reply_post_type() {
	return apply_filters( 'mb_get_reply_post_type', 'forum_reply' );
}

function mb_get_topic_reply_ids( $topic_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_parent = %d", mb_get_reply_post_type(), absint( $topic_id ) ) );
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
