<?php
/**
 * Topic capabilities (i.e., permissions).
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Filter meta cap checks. */
add_filter( 'map_meta_cap', 'mb_topic_map_meta_cap', 10, 4 );

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
		'moderate_topic'         => 'moderate_topic',    // custom
		'close_topic'            => 'close_topic',       // custom
		'open_topic'             => 'open_topic',        // custom
		'spam_topic'             => 'spam_topic',        // custom
		'access_topic_form'      => 'access_topic_form', // custom

		// primitive/meta caps
		'create_posts'           => 'create_topics',

		// primitive caps used outside of map_meta_cap()
		'edit_posts'             => 'edit_topics',
		'edit_others_posts'      => 'edit_others_topics',
		'publish_posts'          => 'create_topics',
		'read_private_posts'     => 'read_private_topics',
		'read_hidden_topics'     => 'read_hidden_topics', // custom

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
function mb_topic_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* Checks if a user can read a specific topic. */
	if ( 'read_post' === $cap && mb_get_topic_post_type() === get_post_type( $args[0] ) ) {

		$post = get_post( $args[0] );

		/* Only run our code if the user isn't the post author. */
		if ( $user_id != $post->post_author ) {

			$forum_id = $post->post_parent;

			/* If we have a forum and the user can't read it, don't allow reading the topic. */
			if ( 0 < $forum_id && !mb_user_can( $user_id, 'read_forum', $forum_id ) ) {

				$caps = array( 'do_not_allow' );

			/* If the user can read the forum, check if they can read the topic. */
			} else {

				$post_type  = get_post_type_object( $post->post_type );
				$status_obj = get_post_status_object( $post->post_status );

				if ( mb_get_hidden_post_status() === $status_obj->name )
					$caps[] = $post_type->cap->read_hidden_topics;

				elseif ( mb_get_private_post_status() === $status_obj->name )
					$caps[] = $post_type->cap->read_private_posts;

				else
					$caps = array();
					//$caps[] = $post_type->cap->read;
			}
		}

	/* Meta cap for moderating a single topic. */
	} elseif ( 'moderate_topic' === $cap ) {

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

	/* Meta cap check for accessing the topic form. */
	} elseif ( 'access_topic_form' === $cap ) {

		$caps = array( 'create_topics' );

		if ( mb_is_single_forum() ) {

			$forum_id     = mb_get_forum_id();
			$forum_status = get_post_status( $forum_id );
			$forum_type   = mb_get_forum_type( $forum_id );

			if ( !current_user_can( 'read_forum', $forum_id ) )
				$caps[] = 'do_not_allow';

			elseif ( in_array( $forum_status, array( mb_get_close_post_status(), mb_get_trash_post_status(), mb_get_archive_post_status() ) ) )
				$caps[] = 'do_not_allow';

			elseif ( !mb_forum_type_allows_topics( $forum_type ) )
				$caps[] = 'do_not_allow';

		} elseif ( mb_is_topic_edit() && !user_can( $user_id, 'edit_post', mb_get_topic_id() ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}
