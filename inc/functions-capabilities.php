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

/* Filter meta cap checks. */
add_filter( 'map_meta_cap', 'mb_map_meta_cap', 10, 4 );

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
 * Returns an array of capabilities for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_forum_capabilities() {

	$caps = array(
		// meta caps (don't assign these to roles)
		'edit_post'              => 'edit_forum',
		'read_post'              => 'read_forum',
		'delete_post'            => 'delete_forum',
		'moderate_post'          => 'moderate_forum', // custom
		'close_post'             => 'close_forum',
		'open_post'              => 'open_forum',

		// primitive/meta caps
		'create_posts'           => 'create_forums',

		// primitive caps used outside of map_meta_cap()
		'edit_posts'             => 'edit_forums',
		'edit_others_posts'      => 'edit_others_forums',
		'publish_posts'          => 'create_forums',

		// primitive caps used inside of map_meta_cap()
		'read'                   => 'read_forums',
		'delete_posts'           => 'delete_forums',
		'delete_published_posts' => 'delete_forums',
		'delete_others_posts'    => 'delete_others_forums',
		'edit_published_posts'   => 'edit_forums',
		'moderate_posts'         => 'moderate_forums', // custom
	);

	return apply_filters( 'mb_get_forum_capabilities', $caps );
}

/**
 * Returns an array of capabilities for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_topic_capabilities() {

	$caps = array(
		// meta caps (don't assign these to roles)
		'edit_post'              => 'edit_topic',
		'read_post'              => 'read_topic',
		'delete_post'            => 'delete_topic',
		'moderate_post'          => 'moderate_topic', // custom
		'close_post'             => 'close_topic',    // custom
		'open_post'              => 'open_topic',     // custom
		'spam_post'              => 'spam_topic',     // custom

		// primitive/meta caps
		'create_posts'           => 'create_topics',

		// primitive caps used outside of map_meta_cap()
		'edit_posts'             => 'edit_topics',
		'edit_others_posts'      => 'edit_others_topics',
		'publish_posts'          => 'create_topics',

		// primitive caps used inside of map_meta_cap()
		'read'                   => 'read_topics',
		'delete_posts'           => 'delete_topics',
		'delete_published_posts' => 'delete_topics',
		'delete_others_posts'    => 'delete_others_topics',
		'edit_published_posts'   => 'edit_topics',
		'moderate_posts'         => 'moderate_topics', // custom
	);

	return apply_filters( 'mb_get_topic_capabilities', $caps );
}

/**
 * Returns an array of capabilities for the "reply" post type.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_reply_capabilities() {

	$caps = array(
		// meta caps (don't assign these to roles)
		'edit_post'              => 'edit_reply',
		'read_post'              => 'read_reply',
		'delete_post'            => 'delete_reply',
		'moderate_post'          => 'moderate_reply', // custom
		'spam_post'              => 'spam_reply',     // custom

		// primitive/meta caps
		'create_posts'           => 'create_replies',

		// primitive caps used outside of map_meta_cap()
		'edit_posts'             => 'edit_replies',
		'edit_others_posts'      => 'edit_others_replies',
		'publish_posts'          => 'create_replies',

		// primitive caps used inside of map_meta_cap()
		'read'                   => 'read_replies',
		'delete_posts'           => 'delete_replies',
		'delete_published_posts' => 'delete_replies',
		'delete_others_posts'    => 'delete_others_replies',
		'edit_published_posts'   => 'edit_replies',
		'moderate_posts'         => 'moderate_replies', // custom
	);

	return apply_filters( 'mb_get_reply_capabilities', $caps );
}

/**
 * Overwrites capabilities in certain scenarios.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $caps
 * @param  string  $cap
 * @param  int     $user_id
 * @param  array   $args
 * @return array
 */
function mb_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* Meta cap for moderating a single forum. */
	if ( 'moderate_forum' === $cap ) {

		$caps = array();

		$forum_id = mb_get_forum_id( $args[0] );

		$forum_type_object = get_post_type_object( mb_get_forum_post_type() );

		$caps[] = $forum_type_object->cap->moderate_posts;
	}

	/* Meta cap for moderating a single topic. */
	elseif ( 'moderate_topic' === $cap ) {

		$caps = array();

		$topic_id = mb_get_topic_id( $args[0] );
		$forum_id = mb_get_topic_forum_id( $topic_id );

		/* If user can moderate the topic forum. */
		if ( user_can( $user_id, 'moderate_forum', $forum_id ) ) {
			$forum_type_object = get_post_type_object( mb_get_forum_post_type() );
			$caps[] = $forum_type_object->cap->moderate_posts;
		}

		/* Else, add cap for moderating topics. */
		else {
			$topic_type_object = get_post_type_object( mb_get_topic_post_type() );
			$caps[] = $topic_type_object->cap->moderate_posts;
		}
	}

	/* Meta cap for moderating a single reply. */
	elseif ( 'moderate_reply' === $cap ) {

		$caps = array();

		$reply_id = mb_get_reply_id( $args[0] );
		$topic_id = mb_get_reply_topic_id( $reply_id );
		$forum_id = mb_get_reply_forum_id( $reply_id );

		/* If user can moderate the reply forum. */
		if ( user_can( $user_id, 'moderate_forum', $forum_id ) ) {
			$forum_type_object = get_post_type_object( mb_get_forum_post_type() );
			$caps[] = $forum_type_object->cap->moderate_posts;
		}

		/* Else, if the user can moderate the reply topic. */
		elseif ( user_can( $user_id, 'moderate_topic', $topic_id ) ) {
			$topic_type_object = get_post_type_object( mb_get_topic_post_type() );
			$caps[] = $topic_type_object->cap->moderate_posts;
		}

		/* Else, add cap for moderating replies. */
		else {
			$reply_type_object = get_post_type_object( mb_get_reply_post_type() );
			$caps[] = $reply_type_object->cap->moderate_posts;
		}
	}

	return $caps;
}
