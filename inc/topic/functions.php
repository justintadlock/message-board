<?php

/**
 * Inserts a new topic and adds/updates metadata.  This is a wrapper for the `wp_insert_post()` function 
 * and should be used in its place where possible.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return int|WP_Error
 */
function mb_insert_topic( $args = array() ) {

	/* Convert date. */
	$post_date  = current_time( 'mysql' );
	$post_epoch = mysql2date( 'U', $post_date );

	/* Set up the defaults. */
	$defaults = array(
		'menu_order'   => $post_epoch,
		'post_date'    => $post_date,
		'post_author'  => get_current_user_id(),
		'post_status'  => mb_get_open_post_status(),
		'post_parent'  => mb_get_default_forum_id(),
	);

	/* Allow devs to filter the defaults. */
	$defaults = apply_filters( 'mb_insert_topic_defaults', $defaults );

	/* Parse the args/defaults and apply filters. */
	$args = apply_filters( 'mb_insert_topic_args', wp_parse_args( $args, $defaults ) );

	/* Always make sure it's the correct post type. */
	$args['post_type'] = mb_get_topic_post_type();

	/* Insert the topic. */
	$published = wp_insert_post( $args );

	/* If we have a published post, add some metadata. */
	if ( $published && !is_wp_error( $published ) ) {

		/* Get the User ID. */
		$user_id = mb_get_user_id( $args['post_author'] );

		/* Update user meta. */
		$topic_count = mb_get_user_topic_count( $user_id );
		update_user_meta( $user_id, '_topic_count', $topic_count + 1 );

		/* Add topic meta. */
		update_post_meta( $published, '_topic_activity_datetime',       $post_date  );
		update_post_meta( $published, '_topic_activity_datetime_epoch', $post_epoch );
		update_post_meta( $published, '_topic_voices',                  $user_id    );
		update_post_meta( $published, '_topic_voice_count',             1           );
		update_post_meta( $published, '_topic_reply_count',             0           );

		/* If we have a forum ID. */
		if ( 0 < $args->post_parent ) {

			/* Get the forum ID. */
			$forum_id = mb_get_forum_id( $args->post_parent );

			/* Update forum meta. */
			update_post_meta( $forum_id, '_forum_activity_datetime',       $post_date  );
			update_post_meta( $forum_id, '_forum_activity_datetime_epoch', $post_epoch );
			update_post_meta( $forum_id, '_forum_last_topic_id',           $published  );

			$topic_count = get_post_meta( $forum_id, '_forum_topic_count', true );
			update_post_meta( $forum_id, '_forum_topic_count', absint( $topic_count ) + 1 );
		}
	}

	/* Return the result of `wp_insert_post()`. */
	return $published;
}

function mb_get_multi_topic_reply_ids( $topic_ids ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent IN ( " . implode( ',', $topic_ids ) . " ) ORDER BY post_date DESC", mb_get_reply_post_type(), mb_get_publish_post_status() ) );
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

function mb_get_topic_bookmarkers( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) )
		return;

	$users = wp_cache_get( 'mb_get_topic_bookmarkers_' . $topic_id, 'message-board-users' );

	if ( false === $users ) {
		$users = mb_set_topic_bookmarkers();
	}

	return apply_filters( 'mb_get_topic_bookmarkers', $users );
}

function mb_set_topic_bookmarkers( $topic_id ) {
	global $wpdb;

	$users = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_topic_bookmarks' and FIND_IN_SET( '{$topic_id}', meta_value ) > 0" );
	wp_cache_set( 'mb_get_topic_bookmarkers_' . $topic_id, $users, 'message-board-users' );

	return $users;
}

function mb_reset_topic_latest( $topic_id ) {

	$reply_ids = mb_get_topic_reply_ids( $topic_id );

	if ( !empty( $reply_ids ) ) {

		$reply_ids = array_reverse( $reply_ids );
		$last_reply_id = array_shift( $reply_ids );

		$post_date = get_post_field( 'post_date', $last_reply_id );

		update_post_meta( $topic_id, '_topic_activity_datetime',       $post_date );
		update_post_meta( $topic_id, '_topic_activity_datetime_epoch', mysql2date( 'U', $post_date ) );
		update_post_meta( $topic_id, '_topic_last_reply_id',           $last_reply_id );

	} else {
		$post_date = get_post_field( 'post_date', $topic_id );

		update_post_meta( $topic_id, '_topic_activity_datetime',       $post_date );
		update_post_meta( $topic_id, '_topic_activity_datetime_epoch', mysql2date( 'U', $post_date ) );

		delete_post_meta( $topic_id, '_topic_last_reply_id' );
	}

	$postarr = array();
	$postarr['ID'] = absint( $topic_id );
	$postarr['menu_order'] = mysql2date( 'U', $post_date );
	wp_update_post( $postarr );
}

function mb_set_topic_reply_count( $topic_id ) {

	$replies = mb_get_topic_reply_ids( $topic_id );

	$count = !empty( $replies ) ? count( $replies ) : 0;

	update_post_meta( $topic_id, '_topic_reply_count', $count );
}

function mb_set_topic_voices( $topic_id ) {
	global $wpdb;

	$voices = $wpdb->get_col( $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s AND post_status = %s", absint( $topic_id ), mb_get_reply_post_type(), mb_get_open_post_status() ) );

	$topic_author = mb_get_topic_author_id( $topic_id );

	$voices = array_merge( array( $topic_author ), (array)$voices );
	$voices = array_unique( $voices );

	$_voices = implode( ',', wp_parse_id_list( array_filter( $voices ) ) );
	update_post_meta( $topic_id, '_topic_voices', $_voices );
	update_post_meta( $topic_id, '_topic_voice_count', count( $_voices ) );

	return $voices;
}

function mb_reset_topic_data( $post, $reset_latest = false ) {

	$post = is_object( $post ) ? $post : get_post( $post );

	$forum_id         = mb_get_topic_forum_id( $post->ID );
	$forum_last_topic = mb_get_forum_last_topic_id( $forum_id );

	/* Reset forum topic count. */
	mb_set_forum_topic_count( $forum_id );

	/* Reset forum reply count. */
	mb_set_forum_reply_count( $forum_id );

	/* If this is the last topic, reset forum latest data. */
	if ( $post->ID === absint( $forum_last_topic ) || true === $reset_latest )
		mb_reset_forum_latest( $forum_id );

	/* Reset user topic count. */
	mb_set_user_topic_count( $post->post_author );
}



