<?php

/* Filter to make sure we get a topic post parent. */
add_filter( 'wp_insert_post_parent', 'mb_insert_topic_post_parent', 10, 3 );

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
	return wp_insert_post( $args );
}

/**
 * Function for inserting topic data when it's first published.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_insert_topic_data( $post ) {

	/* Get the topic ID. */
	$topic_id = mb_get_topic_id( $post->ID );

	/* Get the forum ID. */
	$forum_id = mb_get_forum_id( $post->post_parent );

	/* Get the User ID. */
	$user_id = mb_get_user_id( $post->post_author );

	/* Get the post date. */
	$post_date  = $post->post_date;
	$post_epoch = mysql2date( 'U', $post_date );

	/* Update user meta. */
	$topic_count = mb_get_user_topic_count( $user_id );
	update_user_meta( $user_id, mb_get_user_topic_count_meta_key(), $topic_count + 1 );

	/* Add topic meta. */
	update_post_meta( $topic_id, mb_get_topic_activity_datetime_meta_key(),       $post_date  );
	update_post_meta( $topic_id, mb_get_topic_activity_datetime_epoch_meta_key(), $post_epoch );
	update_post_meta( $topic_id, mb_get_topic_voices_meta_key(),                  $user_id    );
	update_post_meta( $topic_id, mb_get_topic_voice_count_meta_key(),             1           );
	update_post_meta( $topic_id, mb_get_topic_reply_count_meta_key(),             0           );

	/* If we have a forum ID. */
	if ( 0 < $forum_id ) {

		/* Update forum meta. */
		update_post_meta( $forum_id, mb_get_forum_activity_datetime_meta_key(),       $post_date  );
		update_post_meta( $forum_id, mb_get_forum_activity_datetime_epoch_meta_key(), $post_epoch );
		update_post_meta( $forum_id, mb_get_forum_last_topic_id_meta_key(),           $topic_id   );

		$topic_count = get_post_meta( $forum_id, mb_get_forum_topic_count_meta_key(), true );
		update_post_meta( $forum_id, mb_get_forum_topic_count_meta_key(), absint( $topic_count ) + 1 );
	}
}

/**
 * Attempt to always make sure that topics have a post parent.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_parent
 * @param  int     $post_id
 * @param  array   $new_postarr
 * @return int
 */
function mb_insert_topic_post_parent( $post_parent, $post_id, $new_postarr ) {

	if ( mb_get_topic_post_type() === $new_postarr['post_type'] && 0 >= $post_parent )
		$post_parent = mb_get_default_forum_id();

	return $post_parent;
}

/**
 * Adds a topic to the list of super sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_add_super_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_sticky( $topic_id ) )
		mb_remove_sticky_topic( $topic_id );

	if ( !mb_is_topic_super( $topic_id ) )
		return update_option( 'mb_super_topics', array_merge( mb_get_super_topics(), array( $topic_id ) ) );

	return false;
}

/**
 * Removes a topic from the list of super sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_remove_super_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_super( $topic_id ) ) {
		$supers = mb_get_super_topics();
		$key    = array_search( $topic_id, $supers );

		if ( isset( $supers[ $key ] ) ) {
			unset( $supers[ $key ] );
			return update_option( 'mb_super_topics', $supers );
		}
	}

	return false;
}

/**
 * Adds a topic to the list of sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_add_sticky_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_super( $topic_id ) )
		mb_remove_super_topic( $topic_id );

	if ( !mb_is_topic_sticky( $topic_id ) )
		return update_option( 'mb_sticky_topics', array_merge( mb_get_sticky_topics(), array( $topic_id ) ) );

	return false;
}

/**
 * Removes a topic from the list of sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_remove_sticky_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_sticky( $topic_id ) ) {
		$stickies = mb_get_sticky_topics();
		$key      = array_search( $topic_id, $stickies );

		if ( isset( $supers[ $key ] ) ) {
			unset( $stickies[ $key ] );
			return update_option( 'mb_sticky_topics', $stickies );
		}
	}

	return false;
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

	$users = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND FIND_IN_SET( '{$topic_id}', meta_value ) > 0", mb_get_user_topic_subscriptions_meta_key() ) );
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

	$users = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s and FIND_IN_SET( '{$topic_id}', meta_value ) > 0", mb_get_user_topic_bookmarks_meta_key() ) );
	wp_cache_set( 'mb_get_topic_bookmarkers_' . $topic_id, $users, 'message-board-users' );

	return $users;
}

function mb_reset_topic_latest( $topic_id ) {

	$reply_ids = mb_get_topic_reply_ids( $topic_id );

	if ( !empty( $reply_ids ) ) {

		$reply_ids = array_reverse( $reply_ids );
		$last_reply_id = array_shift( $reply_ids );

		$post_date = get_post_field( 'post_date', $last_reply_id );

		update_post_meta( $topic_id, mb_get_topic_activity_datetime_meta_key(),       $post_date );
		update_post_meta( $topic_id, mb_get_topic_activity_datetime_epoch_meta_key(), mysql2date( 'U', $post_date ) );
		update_post_meta( $topic_id, mb_get_topic_last_reply_id_meta_key(),           $last_reply_id );

	} else {
		$post_date = get_post_field( 'post_date', $topic_id );

		update_post_meta( $topic_id, mb_get_topic_activity_datetime_meta_key(),       $post_date );
		update_post_meta( $topic_id, mb_get_topic_activity_datetime_epoch_meta_key(), mysql2date( 'U', $post_date ) );

		delete_post_meta( $topic_id, mb_get_topic_last_reply_id_meta_key() );
	}

	$postarr = array();
	$postarr['ID'] = absint( $topic_id );
	$postarr['menu_order'] = mysql2date( 'U', $post_date );
	wp_update_post( $postarr );
}

function mb_set_topic_reply_count( $topic_id ) {

	$replies = mb_get_topic_reply_ids( $topic_id );

	$count = !empty( $replies ) ? count( $replies ) : 0;

	update_post_meta( $topic_id, mb_get_topic_reply_count_meta_key(), $count );
}

function mb_set_topic_voices( $topic_id ) {
	global $wpdb;

	$voices = $wpdb->get_col( $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s AND post_status = %s", absint( $topic_id ), mb_get_reply_post_type(), mb_get_open_post_status() ) );

	$topic_author = mb_get_topic_author_id( $topic_id );

	$voices = array_merge( array( $topic_author ), (array)$voices );
	$voices = array_unique( $voices );

	$_voices = implode( ',', wp_parse_id_list( array_filter( $voices ) ) );
	update_post_meta( $topic_id, mb_get_topic_voices_meta_key(), $_voices );
	update_post_meta( $topic_id, mb_get_topic_voice_count_meta_key(), count( $_voices ) );

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
