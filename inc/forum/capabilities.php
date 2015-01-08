<?php
/**
 * Forum capabilities.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Filter meta cap checks. */
add_filter( 'map_meta_cap', 'mb_forum_map_meta_cap', 10, 4 );

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
		'moderate_forum'         => 'moderate_forum',    // custom
		'close_forum'            => 'close_forum',       // custom
		'open_forum'             => 'open_forum',        // custom
		'access_forum_form'      => 'access_forum_form', // custom

		// primitive/meta caps
		'create_posts'           => 'create_forums',

		// primitive caps used outside of map_meta_cap()
		'edit_posts'             => 'edit_forums',
		'edit_others_posts'      => 'edit_others_forums',
		'publish_posts'          => 'create_forums',
		'read_private_posts'     => 'read_private_forums',
		'read_hidden_forums'     => 'read_hidden_forums', // custom

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
function mb_forum_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* Checks if a user can read a specific forum. */
	if ( 'read_post' === $cap && mb_get_forum_post_type() === get_post_type( $args[0] ) ) {
		$post       = get_post( $args[0] );

		if ( $user_id != $post->post_author ) {
			$post_type  = get_post_type_object( $post->post_type );
			$status_obj = get_post_status_object( $post->post_status );

			if ( mb_get_hidden_post_status() === $status_obj->name )
				$caps[] = $post_type->cap->read_hidden_forums;
			elseif ( mb_get_private_post_status() === $status_obj->name )
				$caps[] = $post_type->cap->read_private_posts;
			else
				$caps = array();
				//$caps[] = $post_type->cap->read;
		} else {
			$caps = array();
		}

	/* Meta cap for moderating a single forum. */
	} elseif ( 'moderate_forum' === $cap ) {

		$caps = array();

		$forum_id = mb_get_forum_id( $args[0] );

		$forum_type_object = get_post_type_object( mb_get_forum_post_type() );

		$caps[] = $forum_type_object->cap->moderate_posts;
	}

	/* Meta cap for deleting a specific forum. */
	elseif ( 'delete_post' === $cap && mb_get_forum_post_type() === get_post_type( $args[0] ) ) {

		$forum_id = mb_get_forum_id( $args[0] );

		if ( mb_get_default_forum_id() === $forum_id )
			$caps = array( 'do_not_allow' );

	/* Meta cap check for accessing the forum form. */
	} elseif ( 'access_forum_form' === $cap ) {

		$caps = array( 'create_forums' );

		/* If this is a single forum page, check if user can create sub-forums. */
		if ( mb_is_single_forum() ) {

			$forum_id     = mb_get_forum_id();
			$forum_status = get_post_status( $forum_id );
			$forum_type   = mb_get_forum_type( $forum_id );

			if ( !current_user_can( 'read_forum', $forum_id ) )
				$caps[] = 'do_not_allow';

			elseif ( in_array( $forum_status, array( mb_get_close_post_status(), mb_get_trash_post_status(), mb_get_archive_post_status() ) ) )
				$caps[] = 'do_not_allow';

			elseif ( !mb_forum_type_allows_subforums( $forum_type ) )
				$caps[] = 'do_not_allow';

		} elseif ( mb_is_forum_edit() && !user_can( $user_id, 'edit_post', mb_get_forum_id() ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}
