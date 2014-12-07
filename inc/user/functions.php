<?php

function mb_set_user_forum_count( $user_id ) {
	global $wpdb;

	$open_status    = mb_get_open_post_status();
	$close_status   = mb_get_close_post_status();
	$publish_status = mb_get_publish_post_status();

	$where = $wpdb->prepare( "WHERE post_author = %d AND post_type = %s", $user_id, mb_get_forum_post_type() );

	$status_where = "AND (post_status = '{$open_status}' OR post_status = '{$close_status}' OR post_status = '{$publish_status}')";

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where $status_where" );

	update_user_meta( $user_id, mb_get_user_forum_count_meta_key(), $count );

	return $count;
}

function mb_set_user_topic_count( $user_id ) {
	global $wpdb;

	$open_status    = mb_get_open_post_status();
	$close_status   = mb_get_close_post_status();
	$publish_status = mb_get_publish_post_status();

	$where = $wpdb->prepare( "WHERE post_author = %d AND post_type = %s", $user_id, mb_get_topic_post_type() );

	$status_where = "AND (post_status = '{$open_status}' OR post_status = '{$close_status}' OR post_status = '{$publish_status}')";

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where $status_where" );

	update_user_meta( $user_id, mb_get_user_topic_count_meta_key(), $count );

	return $count;
}

function mb_set_user_reply_count( $user_id ) {
	global $wpdb;

	// @todo check all public reply statuses
	$where = $wpdb->prepare( "WHERE post_author = %d AND post_type = %s AND post_status = %s", $user_id, mb_get_reply_post_type(), mb_get_publish_post_status() );

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

	update_user_meta( $user_id, mb_get_user_reply_count_meta_key(), $count );

	return $count;
}

function mb_get_user_subscriptions( $user_id ) {

	$subscriptions = get_user_meta( $user_id, mb_get_user_topic_subscriptions_meta_key(), true );

	return !empty( $subscriptions ) ? explode( ',', $subscriptions ) : array();
}

function mb_update_user_subscriptions( $user_id, $subs ) {

	if ( is_array( $subs ) ) {
		$subs = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );
	}

	return update_user_meta( $user_id, mb_get_user_topic_subscriptions_meta_key(), $subs );
}

function mb_add_user_subscription( $user_id, $topic_id ) {

	$subs = mb_get_user_subscriptions( $user_id );

	/* If ID not already in subscriptions list. */
	if ( !in_array( $topic_id, $subs ) ) {
		$subs[] = $topic_id;

		return mb_update_user_subscriptions( $user_id, $subs );
	}

	return false;
}

function mb_remove_user_subscription( $user_id, $topic_id ) {

	$subs = mb_get_user_subscriptions( $user_id );

	if ( in_array( $topic_id, $subs ) ) {

		$_sub = array_search( $topic_id, $subs );

		unset( $subs[ $_sub ] );

		return mb_update_user_subscriptions( $user_id, $subs );
	}

	return false;
}

function mb_notify_topic_subscribers( $topic_id, $reply_id ) {

	$subscribers =  mb_get_topic_subscribers( $topic_id, true );

	if ( empty( $subscribers ) )
		return false;

	remove_all_filters( 'mb_get_reply_content' );

	$topic_title   = strip_tags( mb_get_topic_title(   $topic_id ) );

	$reply_url        = mb_get_reply_url( $reply_id );
	$reply_author     = mb_get_reply_author( $reply_id );
	$reply_author_id  = mb_get_reply_author_id( $reply_id );
	$reply_content    = strip_tags( mb_get_reply_content( $reply_id ) );

	$blog_name     = esc_html( strip_tags( get_option( 'blogname' ) ) );

	$site_url      = untrailingslashit( str_replace( array( 'http://', 'https://' ), '', home_url() ) );
	$from          = '<noreply@' . $site_url . '>';

	$message = sprintf( 
		__( '%1$s replied: %4$s%2$s %4$sPost Link: %3$s %4$sYou are receiving this email because you subscribed to a forum topic. Log in and visit the topic to unsubscribe from these emails.', 'message-board' ),
		$reply_author,
		$reply_content,
		$reply_url,
		"\n\n"
	);

	$subject = '[' . $blog_name . '] ' . $topic_title;

	$headers = array();

	$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' ' . $from;

	foreach ( (array) $subscribers as $user_id ) {

		if ( absint( $reply_author_id ) === absint( $user_id ) )
			continue;

		$headers[] = 'Bcc: ' . get_userdata( $user_id )->user_email;
	}

	return wp_mail( $from, $subject, $message, $headers );
}
