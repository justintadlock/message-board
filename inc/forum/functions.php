<?php
/**
 * Plugin functions and filters for the forum post type.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Update forum data on the `post_updated` hook. */
add_action( 'post_updated', 'mb_forum_post_updated', 10, 3 );

/* Private/hidden links. */
add_filter( 'post_type_link', 'mb_forum_post_type_link', 10, 2 );

/**
 * Callback function executed after a forum has been updated.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  object  $post_after
 * @param  object  $post_before
 * @return void
 */
function mb_forum_post_updated( $post_id, $post_after, $post_before ) {

	/* Bail if this is not the forum post type. */
	if ( !mb_is_forum( $post_id ) )
		return;

	/* If the forum parent has changed. */
	if ( $post_after->post_parent !== $post_before->post_parent ) {

		/* Update the forum's level. */
		mb_reset_forum_level( $post_id );

		/* Update the new forum parent's subforum count. */
		if ( 0 < $post_after->post_parent )
			mb_reset_forum_subforum_count( $post_after->post_parent );

		/* Update the old forum parent's subforum count. */
		if ( 0 < $post_before->post_parent )
			mb_reset_forum_subforum_count( $post_before->post_parent );
	}
}

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

	/* Insert the forum. */
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

	/* Hook for before inserting forum data. */
	do_action( 'mb_before_insert_forum_data', $post );

	/* Get the forum ID. */
	$forum_id = mb_get_forum_id( $post->ID );

	/* Get the User ID. */
	$user_id = mb_get_user_id( $post->post_author );

	/* Update parent's subforum count. */
	if ( 0 < $post->post_parent ) {
		$count = mb_get_forum_subforum_count( $post->post_parent );
		mb_set_forum_subforum_count( $post->post_parent, absint( $count ) + 1 );
	}

	/* Update user meta. */
	mb_set_user_forum_count( $user_id );

	/* Add forum meta. */
	mb_reset_forum_level( $forum_id );

	/* Notify subscribers that there's a new forum. */
	mb_notify_subscribers( $post );

	/* Hook for after inserting forum data. */
	do_action( 'mb_after_insert_forum_data', $post );
}

/**
 * Resets a specific forum's data.
 *
 * @since  1.0.0
 * @access public
 * @param  object|int  $post
 * @return void
 */
function mb_reset_forum_data( $post ) {

	/* Get the forum ID. */
	$forum_id = is_object( $post ) ? mb_get_forum_id( get_post( $post )->ID ) : mb_get_forum_id( $post );

	/* Reset subforum count. */
	mb_reset_forum_subforum_count( $forum_id );

	/* Reset forum topic count. */
	mb_reset_forum_topic_count( $forum_id );

	/* Reset forum reply count. */
	mb_reset_forum_reply_count( $forum_id );

	/* Reset forum latest. */
	mb_reset_forum_latest( $forum_id );
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
 * Resets a forum's subforum count.
 *
 * @todo Update the $status_where to use any published forum status rather than hardcoding them.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @global object $wpdb
 * @return int
 */
function mb_reset_forum_subforum_count( $forum_id ) {
	global $wpdb;

	$forum_id = mb_get_forum_id( $forum_id );

	$open_status    = mb_get_open_post_status();
	$close_status   = mb_get_close_post_status();
	$publish_status = mb_get_publish_post_status();
	$hidden_status  = mb_get_hidden_post_status();
	$private_status = mb_get_private_post_status();
	$archive_status = mb_get_archive_post_status();

	$where = $wpdb->prepare( "WHERE post_parent = %d AND post_type = %s", $forum_id, mb_get_forum_post_type() );

	$status_where = "AND (post_status = '{$open_status}' OR post_status = '{$close_status}' OR post_status = '{$publish_status}' OR post_status = '{$private_status}' OR post_status = '{$hidden_status}' OR post_status = '{$archive_status}')";

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where $status_where" );

	mb_set_forum_subforum_count( $forum_id, $count );

	return $count;
}

/**
 * Sets a forum's subforum count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  int     $count
 * @return bool
 */
function mb_set_forum_subforum_count( $forum_id, $count ) {
	return update_post_meta( $forum_id, mb_get_forum_subforum_count_meta_key(), absint( $count ) );
}

/**
 * Resets the forum topic count.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return int
 */
function mb_reset_forum_topic_count( $forum_id ) {

	$forum_id = mb_get_forum_id( $forum_id );

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

	$open_status    = mb_get_open_post_status();
	$close_status   = mb_get_close_post_status();
	$publish_status = mb_get_publish_post_status();
	$private_status = mb_get_private_post_status();
	$hidden_status  = mb_get_hidden_post_status();

	$status_where = "AND (post_status = '{$open_status}' OR post_status = '{$close_status}' OR post_status = '{$publish_status}' OR post_status = '{$private_status}' OR post_status = '{$hidden_status}')";

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s $status_where AND post_parent = %s ORDER BY menu_order DESC", mb_get_topic_post_type(), absint( $forum_id ) ) );
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

/**
 * Filter on the post type link for forums. If the user doesn't have permission to view the forum, 
 * return an empty string.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $link
 * @param  object  $post
 * @return string
 */
function mb_forum_post_type_link( $link, $post ) {
	return mb_is_forum( $post->ID ) && !current_user_can( 'read_forum', $post->ID ) ? '' : $link;
}
