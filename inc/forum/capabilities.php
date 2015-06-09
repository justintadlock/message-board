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

		// primitive/meta caps
		'create_posts'           => 'create_forums',

		// primitive caps used outside of map_meta_cap()
		'publish_posts'          => 'create_forums',
		'open_posts'             => 'open_forums',          // custom
		'close_posts'            => 'close_forums',         // custom
		'privatize_posts'        => 'privatize_forums',     // custom
		'hide_posts'             => 'hide_forums',          // custom
		'archive_posts'          => 'archive_forums',       // custom

		'edit_posts'             => 'edit_forums',

		'read_private_posts'     => 'read_private_forums',
		'read_hidden_forums'     => 'read_hidden_forums',   // custom
		'read_archived_forums'   => 'read_archived_forums', // custom
		'read_others_forums'     => 'read_forums',          // custom

		// primitive caps used inside of map_meta_cap()
		'edit_published_posts'   => 'edit_forums',
		'edit_others_posts'      => 'edit_others_forums',
		'edit_private_posts'     => 'edit_private_forums',
		'edit_open_forums'       => 'edit_open_forums',     // custom
		'edit_hidden_forums'     => 'edit_hidden_forums',   // custom
		'edit_closed_forums'     => 'edit_closed_forums',   // custom
		'edit_archived_forums'   => 'edit_archived_forums', // custom
		'delete_posts'           => 'delete_forums',
		'delete_published_posts' => 'delete_forums',
		'delete_others_posts'    => 'delete_others_forums',
		'read'                   => 'read_forums',
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
	if ( 'read_post' === $cap && mb_is_forum( $args[0] ) ) {
		$post       = get_post( $args[0] );

		if ( $user_id != $post->post_author ) {

			$parent_id = $post->post_parent;

			/* If we have a parent forum and the user can't read it, don't allow reading this forum. */
			if ( 0 < $parent_id && !mb_user_can( $user_id, 'read_forum', $parent_id ) ) {

				$caps = array( 'do_not_allow' );

			/* If the user can read the parent forum, check if they can read this one. */
			} else {
				$post_type   = get_post_type_object( $post->post_type );
				$post_status = mb_get_forum_status( $post->ID );
				$status_obj  = get_post_status_object( $post_status );

				if ( mb_get_hidden_post_status() === $status_obj->name )
					$caps[] = $post_type->cap->read_hidden_forums;

				elseif ( mb_get_private_post_status() === $status_obj->name )
					$caps[] = $post_type->cap->read_private_posts;

				elseif ( $post_type->cap->read !== $post_type->cap->read_others_forums )
					$caps[] = $post_type->cap->read_others_forums;

				else
					$caps = array();
			}
		} else {
			$caps = array();
		}

	/* Meta cap for editing a single forum. */
	} elseif ( 'edit_post' === $cap && mb_is_forum( $args[0] ) ) {

		$post      = get_post( $args[0] );
		$forum_obj = get_post_type_object( mb_get_forum_post_type() );

		if ( $user_id != $post->post_author ) {

			// Open forums.
			if ( mb_is_forum_open( $args[0] ) )
				$caps[] = $forum_obj->cap->edit_open_forums;

			// Closed forums.
			elseif ( mb_is_forum_closed( $args[0] ) )
				$caps[] = $forum_obj->cap->edit_closed_forums;

			// Hidden forums.
			elseif ( mb_is_forum_hidden( $args[0] ) )
				$caps[] = $forum_obj->cap->edit_hidden_forums;
		}

	/* Meta cap for opening a single forum. */
	} elseif ( 'open_forum' === $cap ) {

		$caps = array();
		$caps[] = user_can( $user_id, 'edit_forum', $args[0] ) ? 'open_forums' : 'do_not_allow';

	/* Meta cap for closing a single forum. */
	} elseif ( 'close_forum' === $cap ) {

		$caps = array();
		$caps[] = user_can( $user_id, 'edit_forum', $args[0] ) ? 'close_forums' : 'do_not_allow';

	/* Meta cap for privatizing a single forum. */
	} elseif ( 'privatize_forum' === $cap ) {

		$caps = array();
		$caps[] = user_can( $user_id, 'edit_forum', $args[0] ) ? 'privatize_forums' : 'do_not_allow';

	/* Meta cap for hiding a single forum. */
	} elseif ( 'hide_forum' === $cap ) {

		$caps = array();
		$caps[] = user_can( $user_id, 'edit_forum', $args[0] ) ? 'hide_forums' : 'do_not_allow';

	/* Meta cap for spamming a single forum. */
	} elseif ( 'archive_forum' === $cap ) {

		$caps = array();
		$caps[] = user_can( $user_id, 'edit_forum', $args[0] ) ? 'archive_forums' : 'do_not_allow';


	/* Meta cap for deleting a specific forum. */
	} elseif ( 'delete_post' === $cap && mb_is_forum( $args[0] ) ) {

		$forum_id = mb_get_forum_id( $args[0] );

		if ( mb_get_default_forum_id() === $forum_id )
			$caps = array( 'do_not_allow' );

	/* Meta cap check for accessing the forum form. */
	} elseif ( 'access_forum_form' === $cap ) {

		$caps = array( 'create_forums' );

		/* If this is a single forum page, check if user can create sub-forums. */
		if ( mb_is_single_forum() ) {

			$forum_id     = mb_get_forum_id();

			if ( !current_user_can( 'read_forum', $forum_id ) )
				$caps[] = 'do_not_allow';

			elseif ( !mb_forum_allows_subforums( $forum_id ) )
				$caps[] = 'do_not_allow';

		} elseif ( mb_is_forum_edit() && !user_can( $user_id, 'edit_post', mb_get_forum_id() ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}
