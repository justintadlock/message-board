<?php

add_filter( 'post_type_link', 'mb_reply_post_type_link', 10, 2 );

function mb_get_reply_post_type() {
	return apply_filters( 'mb_get_reply_post_type', 'forum_reply' );
}

function mb_reply_post_type_link( $link, $post ) {

	if ( mb_get_reply_post_type() !== $post->post_type )
		return $link;

	$url = mb_generate_reply_url( $post->ID );

	return !empty( $url ) ? $url : $link;
}

function mb_generate_reply_url( $reply_id = 0 ) {

	$reply_id       = mb_get_reply_id( $reply_id );

	if ( mb_get_publish_post_status() !== get_post_status( $reply_id ) )
		return '';

	$per_page       = mb_get_replies_per_page();
	$reply_position = mb_get_reply_position( $reply_id );
	$reply_hash     = "#post-{$reply_id}";

	$reply_page = ceil( $reply_position / $per_page );

	$topic_id  = mb_get_reply_topic_id( $reply_id );
	$topic_url = get_permalink( $topic_id );

	if ( 1 >= $reply_page ) {

		$reply_url = user_trailingslashit( $topic_url ) . $reply_hash;
	}

	else {
		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {

			$reply_url = trailingslashit( $topic_url ) . trailingslashit( $wp_rewrite->pagination_base ) . user_trailingslashit( $reply_page ) . $reply_hash;

		} else {
			$reply_url = add_query_arg( 'paged', $reply_page, $topic_url ) . $reply_hash;
		}
	}

	return $reply_url;
}

function mb_get_topic_reply_ids( $topic_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = %d ORDER BY menu_order ASC", mb_get_reply_post_type(), mb_get_publish_post_status(), absint( $topic_id ) ) );
}

function mb_reset_reply_data( $post, $reset_latest = false ) {

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
	if ( $post->ID === absint( $topic_last_reply ) || true === $reset_latest )
		mb_reset_topic_latest( $topic_id );

	/* If this is the last reply, reset forum latest data. */
	if ( $post->ID === absint( $forum_last_reply ) || true === $reset_latest )
		mb_reset_forum_latest( $forum_id );

	/* Reset user topic count. */
	mb_set_user_reply_count( $post->post_author );
}

/* Update all reply positions with a single query. */
function mb_reset_reply_positions( $topic_id ) {
	global $wpdb;

	$posts = $wpdb->get_results( 
		$wpdb->prepare( 
			"SELECT ID, menu_order FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = %d ORDER BY post_date ASC", 
			mb_get_reply_post_type(), 
			mb_get_open_post_status(),
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

	$allowed_statuses = array(
		mb_get_publish_post_status(),
		mb_get_trash_post_status(),
		mb_get_spam_post_status(),
		mb_get_orphan_post_status()
	);

	if ( !in_array( $status, $allowed_statuses ) )
		return false;

	$post_ids = $wpdb->get_results( 
		$wpdb->prepare( 
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = %d ORDER BY post_date ASC", 
			mb_get_reply_post_type(), 
			mb_get_open_post_status(),
			absint( $topic_id ) 
		) 
	);

	if ( empty( $post_ids ) )
		return false;

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID IN (" . implode( ',', $post_ids ) . ")", $status ) );
}
