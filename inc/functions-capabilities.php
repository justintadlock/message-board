<?php
/**
 * Plugin capabilities (i.e., permissions).
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

function mb_user_can( $user_id, $cap, $post_id ) {

	// @todo Check hierarchy.
	if ( 0 >= $user_id && in_array( $cap, array( 'read_forum', 'read_topic', 'read_reply' ) ) ) {

		$status_obj = get_post_status_object( get_post_status( $post_id ) );

		if ( false === $status_obj->private && false === $status_obj->protected )
			return true;
	}

	return user_can( $user_id, $cap, $post_id );
}
