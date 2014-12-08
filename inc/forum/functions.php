<?php
/**
 * Plugin functions and filters for the forum post type.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Inserts a new forum.  This is a wrapper for the `wp_insert_post()` function and should be used in its 
 * place where possible.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return int|WP_Error
 */
function mb_insert_forum( $args = array() ) {

	/* Convert date. */
	$post_date  = current_time( 'mysql' );
	$post_epoch = mysql2date( 'U', $post_date );

	/* Set up the defaults. */
	$defaults = array(
		'menu_order'   => 0,
		'post_date'    => $post_date,
		'post_author'  => get_current_user_id(),
		'post_status'  => mb_get_open_post_status(),
		'post_parent'  => 0,
	);

	/* Allow devs to filter the defaults. */
	$defaults = apply_filters( 'mb_insert_forum_defaults', $defaults );

	/* Parse the args/defaults and apply filters. */
	$args = apply_filters( 'mb_insert_forum_args', wp_parse_args( $args, $defaults ) );

	/* Always make sure it's the correct post type. */
	$args['post_type'] = mb_get_forum_post_type();

	/* Insert the topic. */
	return wp_insert_post( $args );
}

/**
 * Function for inserting forum data when it's first published.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_insert_forum_data( $post ) {

	/* Get the forum ID. */
	$forum_id = mb_get_forum_id( $post->ID );

	/* Get the User ID. */
	$user_id = mb_get_user_id( $post->post_author );

	/* Update user meta. */
	mb_set_user_forum_count( $user_id );

	/* Add forum meta. */
	mb_reset_forum_level( $forum_id );
}

/**
 * Gets a forum's level in the hierarchy.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return int
 */
function mb_get_forum_level( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	$forum_level = get_post_meta( $forum_id, mb_get_forum_level_meta_key(), true );

	if ( '' === $forum_level )
		$forum_level = mb_reset_forum_level( $forum_id );

	return apply_filters( 'mb_get_forum_level', $forum_level, $forum_id );
}

/**
 * Sets a forum's level.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int
 */
function mb_reset_forum_level( $forum_id ) {

	$level   = 0;
	$forum_id = mb_get_forum_id( $forum_id );

	while ( 0 < $forum_id ) {

		$level++;

		$forum_id = mb_get_forum_id( get_post( $forum_id )->post_parent );
	}

	mb_set_forum_level( $forum_id, absint( $level ) );

	return $level;
}

/**
 * Set the forum topic count.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return int
 */
function mb_reset_forum_topic_count( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	$count = !empty( $topic_ids ) ? count( $topic_ids ) : 0;

	mb_set_forum_topic_count( $forum_id, $count );

	return $count;
}

/**
 * Set the forum reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return int
 */
function mb_reset_forum_reply_count( $forum_id ) {

	$count     = 0;
	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( !empty( $topic_ids ) ) {
		$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

		$count = !empty( $reply_ids ) ? count( $reply_ids ) : 0;
	}

	mb_set_forum_reply_count( $forum_id, $count );

	return $count;
}

/**
 * Returns an array of topic IDs for the forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return array
 */
function mb_get_forum_topic_ids( $forum_id ) {
	global $wpdb;

	$open_status  = mb_get_open_post_status();
	$close_status = mb_get_close_post_status();

	$statuses = array();
	$statuses[] = "post_status = '{$open_status}'";
	$statuses[] = "post_status = '{$close_status}'";

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND (" . implode( ' OR ', $statuses ) . ") AND post_parent = %s ORDER BY menu_order DESC", mb_get_topic_post_type(), absint( $forum_id ) ) );
}

/**
 * Resets the forum's "latest" data.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return int
 */
function mb_reset_forum_latest( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( !empty( $topic_ids ) ) {

		$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

		if ( !empty( $reply_ids ) ) {
			$new_last_reply = array_shift( $reply_ids );
			$new_last_topic = mb_get_reply_topic_id( $new_last_reply );

			mb_set_forum_last_reply_id( $forum_id, $new_last_reply );
			mb_set_forum_last_topic_id( $forum_id, $new_last_topic );

			$new_last_date = get_post( $new_last_reply )->post_date;

			mb_set_forum_activity_datetime( $forum_id, $new_last_date );
			mb_set_forum_activity_epoch( $forum_id, mysql2date( 'U', $new_last_date ) );
		} else {

			$new_last_topic = array_shift( $topic_ids );

			delete_post_meta( $forum_id, mb_get_forum_last_reply_id_meta_key() );

			mb_set_forum_last_topic_id( $forum_id, $new_last_topic );

			$new_last_date = get_post( $new_last_topic )->post_date;

			mb_set_forum_activity_datetime( $forum_id, $new_last_date );
			mb_set_forum_activity_epoch( $forum_id, mysql2date( 'U', $new_last_date ) );
		}
	} else {
		delete_post_meta( $forum_id, mb_get_forum_last_reply_id_meta_key()           );
		delete_post_meta( $forum_id, mb_get_forum_last_topic_id_meta_key()           );
		delete_post_meta( $forum_id, mb_get_forum_activity_datetime_meta_key()       );
		delete_post_meta( $forum_id, mb_get_forum_activity_datetime_epoch_meta_key() );
	}
}

/**
 * Sets the forum level.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $level
 * @return bool
 */
function mb_set_forum_level( $forum_id, $level ) {
	return update_post_meta( $forum_id, mb_get_forum_level_meta_key(), $level );
}

/**
 * Sets the forum topic count
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $count
 * @return bool
 */
function mb_set_forum_topic_count( $forum_id, $count ) {
	return update_post_meta( $forum_id, mb_get_forum_topic_count_meta_key(), $count );
}

/**
 * Sets the forum reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $count
 * @return bool
 */
function mb_set_forum_reply_count( $forum_id, $count ) {
	return update_post_meta( $forum_id, mb_get_forum_reply_count_meta_key(), $count );
}

/**
 * Sets the forum last topic ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $topic_id
 * @return bool
 */
function mb_set_forum_last_topic_id( $forum_id, $topic_id ) {
	return update_post_meta( $forum_id, mb_get_forum_last_topic_id_meta_key(), $topic_id );
}

/**
 * Sets the forum last reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $reply_id
 * @return bool
 */
function mb_set_forum_last_reply_id( $forum_id, $reply_id ) {
	return update_post_meta( $forum_id, mb_get_forum_last_reply_id_meta_key(), $reply_id );
}

/**
 * Sets the forum last activity datetime.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  string  $datetime
 * @return bool
 */
function mb_set_forum_activity_datetime( $forum_id, $datetime ) {
	return update_post_meta( $forum_id, mb_get_forum_activity_datetime_meta_key(), $datetime );
}

/**
 * Sets the forum last activity datetime epoch.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $epoch
 * @return bool
 */
function mb_set_forum_activity_epoch( $forum_id, $epoch ) {
	return update_post_meta( $forum_id, mb_get_forum_activity_datetime_epoch_meta_key(), $epoch );
}
