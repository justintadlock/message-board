<?php
/**
 * Reply capabilities (i.e., permissions).
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Filter meta cap checks. */
add_filter( 'map_meta_cap', 'mb_reply_map_meta_cap', 10, 4 );

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
		'moderate_reply'         => 'moderate_reply',    // custom
		'spam_reply'             => 'spam_reply',        // custom
		'access_reply_form'      => 'access_reply_form', // custom

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
function mb_reply_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* Checks if a user can read a specific reply. */
	if ( 'read_post' === $cap && mb_get_reply_post_type() === get_post_type( $args[0] ) ) {

		$post = get_post( $args[0] );

		/* Only run our code if the user isn't the post author. */
		if ( $user_id != $post->post_author ) {

			$topic_id = $post->post_parent;

			/* If we have a topic and the user can't read it, don't allow reading the reply. */
			if ( 0 < $topic_id && !user_can( $user_id, 'read_post', $topic_id ) ) {

				$caps = array( 'do_not_allow' );

			/* If the user can read the topic, check if they can read the reply. */
			} else {

				$post_type  = get_post_type_object( $post->post_type );

				$caps = array();
				//$caps[] = $post_type->cap->read;
			}
		} else {
			$caps = array();
		}

	/* Meta cap for moderating a single reply. */
	} elseif ( 'moderate_reply' === $cap ) {

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

	/* Meta cap check for accessing the reply form. */
	} elseif ( 'access_reply_form' === $cap ) {

		$caps = array( 'create_replies' );

		if ( mb_is_single_topic() ) {

			$topic_id     = mb_get_topic_id();
			$topic_status = get_post_status( $topic_id );
			$topic_type   = mb_get_topic_type( $topic_id );

			if ( !current_user_can( 'read_topic', $topic_id ) )
				$caps[] = 'do_not_allow';

			elseif ( in_array( $topic_status, array( mb_get_close_post_status(), mb_get_trash_post_status() ) ) )
				$caps[] = 'do_not_allow';

			elseif ( !mb_topic_type_allows_replies( $topic_type ) )
				$caps[] = 'do_not_allow';

		} elseif ( mb_is_reply_edit() && !user_can( $user_id, 'edit_post', mb_get_reply_id() ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}
