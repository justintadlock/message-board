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

		// primitive/meta caps
		'create_posts'           => 'create_replies',

		// primitive caps used outside of map_meta_cap()
		'publish_posts'          => 'create_replies',
		'spam_posts'             => 'spam_replies',          // custom
		'edit_posts'             => 'edit_replies',
		'edit_others_posts'      => 'edit_others_replies',

		// primitive caps used inside of map_meta_cap()
		'edit_published_posts'   => 'edit_replies',
		'edit_spam_replies'      => 'edit_spam_replies',     // custom
		'delete_posts'           => 'delete_replies',
		'delete_published_posts' => 'delete_replies',
		'delete_others_posts'    => 'delete_others_replies',
		'read'                   => 'read_replies',
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

	/* Meta cap for editing a single reply. */
	} elseif ( 'edit_post' === $cap && mb_get_reply_post_type() === get_post_type( $args[0] ) ) {

		$post      = get_post( $args[0] );
		$reply_obj = get_post_type_object( mb_get_reply_post_type() );

		// Spam topics
		if ( mb_is_reply_spam( $args[0] ) )
			$caps[] = $reply_obj->cap->edit_spam_replies;

	/* Meta cap for spamming a single reply. */
	} elseif ( 'spam_reply' === $cap ) {

		$caps = array();
		$caps[] = user_can( $user_id, 'edit_reply', $args[0] ) ? 'spam_replies' : 'do_not_allow';

	/* Meta cap check for accessing the reply form. */
	} elseif ( 'access_reply_form' === $cap ) {

		$caps = array( 'create_replies' );

		if ( mb_is_single_topic() ) {

			$topic_id     = mb_get_topic_id();
			$topic_status = mb_get_topic_status( $topic_id );
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
