<?php
/**
 * Plugin capabilities.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Returns an array of common capabilities used throughout the forums.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_common_capabilities() {

	$caps = array(
		'manage'   => 'manage_forums',   // Can do anything
		'throttle' => 'bypass_throttle', // Doesn't have to wait to post new topics/replies
	);

	return apply_filters( 'mb_get_common_capabilities', $caps );
}

/**
 * Helper function for checking if a user can read forums, topics, or replies. We need this to handle 
 * users who are not logged in but should have permission to read (e.g, non-private forums).  This 
 * function is meant to be used in conjunction with a filter on `map_meta_cap`.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  string  $cap
 * @param  int     $post_id
 * @return bool
 */
function mb_user_can( $user_id, $cap, $post_id ) {

	// @todo Check hierarchy.
	if ( in_array( $cap, array( 'read_forum', 'read_topic', 'read_reply' ) ) ) {

		if ( 'read_forum' === $cap )
			$status_obj = get_post_status_object( mb_get_forum_status( $post_id ) );

		elseif ( 'read_topic' === $cap )
			$status_obj = get_post_status_object( mb_get_topic_status( $post_id ) );

		elseif ( 'read_forum' === $cap )
			$status_obj = get_post_status_object( mb_get_reply_status( $post_id ) );

		if ( false === $status_obj->private && false === $status_obj->protected )
			return true;
	}

	return user_can( $user_id, $cap, $post_id );
}
