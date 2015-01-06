<?php
/**
 * Bookmarks API.  This allows users to bookmark/favorite topics.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* ====== Topic Bookmarks ====== */

function mb_add_user_topic_bookmark( $user_id, $topic_id ) {

	$user_id  = mb_get_user_id( $user_id );
	$topic_id = mb_get_topic_id( $topic_id );

	$favs = mb_get_user_topic_bookmarks( $user_id );

	if ( !in_array( $topic_id, $favs ) ) {
		wp_cache_delete( 'mb_get_topic_bookmarkers_' . $topic_id, 'message-board-users' );

		$favs[] = $topic_id;

		$favs = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

		return update_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), $favs );
	}

	return false;
}

function mb_remove_user_topic_bookmark( $user_id, $topic_id ) {

	$user_id  = mb_get_user_id( $user_id );
	$topic_id = mb_get_topic_id( $topic_id );

	$favs = mb_get_user_topic_bookmarks( $user_id );

	if ( in_array( $topic_id, $favs ) ) {
		wp_cache_delete( 'mb_get_topic_bookmarkers_' . $topic_id, 'message-board-users' );

		$_sub = array_search( $topic_id, $favs );

		unset( $favs[ $_sub ] );

		$favs = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

		return update_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), $favs );
	}

	return false;
}

function mb_get_user_topic_bookmarks( $user_id ) {

	$user_id = mb_get_user_id( $user_id );

	$bookmarks = get_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), true );
	$bookmarks = !empty( $bookmarks ) ? explode( ',', $bookmarks ) : array();

	return apply_filters( 'mb_get_user_topic_bookmarks', $bookmarks, $user_id );
}

/**
 * Checks if the user is bookmarkd to the topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  int     $topic_id
 * @return bool
 */
function mb_is_topic_user_bookmark( $user_id = 0, $topic_id = 0 ) {

	$user_id  = mb_get_user_id( $user_id );
	$topic_id = mb_get_topic_id( $topic_id );

	$is_bookmarkd = in_array( $topic_id, mb_get_user_topic_bookmarks( $user_id ) ) ? true : false;

	return apply_filters( 'mb_is_user_bookmarkd_topic', $is_bookmarkd, $user_id, $topic_id );
}

/**
 * Get an array of user IDs for users who are bookmarkd to the topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return array
 */
function mb_get_topic_bookmarkers( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	$users = wp_cache_get( 'mb_get_topic_bookmarkers_' . $topic_id, 'message-board-users' );

	if ( false === $users ) {
		global $wpdb;

		$users = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND FIND_IN_SET( '{$topic_id}', meta_value ) > 0", mb_get_user_topic_bookmarks_meta_key() ) );
		wp_cache_set( 'mb_get_topic_bookmarkers_' . $topic_id, $users, 'message-board-users' );
	}

	return apply_filters( 'mb_get_topic_bookmarkers', $users );
}
