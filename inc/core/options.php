<?php
/**
 * Wrapper functions for plugin options saved in the database.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Returns what to show on the forum front page.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return string forums|topics
 */
function mb_get_show_on_front() {
	return apply_filters( 'mb_get_show_on_front', get_option( 'mb_show_on_front', 'forums' ) );
}

/**
 * Returns the forum archive display (hierarchical or flat).
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_archive_display() {
	return apply_filters( 'mb_get_forum_archive_display', get_option( 'mb_forum_archive_display', 'hierarchical' ) );
}

/**
 * Returns the number of forums to show per page.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_forums_per_page() {
	return intval( apply_filters( 'mb_get_forums_per_page', get_option( 'mb_forums_per_page', 15 ) ) );
}

/**
 * Returns the number of topics to show per page.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_topics_per_page() {
	return intval( apply_filters( 'mb_get_topics_per_page', get_option( 'mb_topics_per_page', 15 ) ) );
}

/**
 * Returns the number of replies to show per page.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_replies_per_page() {
	return intval( apply_filters( 'mb_get_replies_per_page', get_option( 'mb_replies_per_page', 15 ) ) );
}

/**
 * Returns the number of users to show per page on the user archive.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_users_per_page() {
	return intval( apply_filters( 'mb_get_users_per_page', get_option( 'mb_users_per_page', 15 ) ) );
}

/**
 * Returns the number of roles to show per page on the role archive.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_roles_per_page() {
	return apply_filters( 'mb_get_roles_per_page', 15 );
}

/**
 * Returns the default forum ID.  This is the first-selected forum in drop-down lists for forums.  Also, 
 * this forum should not be allowed to be trashed/deleted.  Any permanently-deleted forum's topics should 
 * be assigned to the default forum.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_default_forum_id() {
	return absint( apply_filters( 'mb_get_default_forum_id', get_option( 'mb_default_forum_id', 0 ) ) );
}

/**
 * Returns the ID/slug of the default forum role. By default, this is set to the `mb_participant` role.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_default_role() {
	return apply_filters( 'mb_get_default_role', get_option( 'mb_default_forum_role', mb_get_participant_role() ) );
}

/**
 * Returns TRUE if the bookmarks feature is enabled.  Returns FALSE if disabled.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_bookmarks_active() {
	return apply_filters( 'mb_is_bookmarks_active', get_option( 'mb_enable_bookmarks', true ) );
}

/**
 * Returns TRUE if the subscriptions feature is enabled.  Returns FALSE if disabled.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_subscriptions_active() {
	return apply_filters( 'mb_is_subscriptions_active', get_option( 'mb_enable_subscriptions', true ) );
}

/**
 * Returns an array of super sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_super_topics() {
	return apply_filters( 'mb_get_super_sticky_topics', get_option( 'mb_super_topics', array() ) );
}

/**
 * Returns an array of sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_sticky_topics() {
	return apply_filters( 'mb_get_sticky_topics', get_option( 'mb_sticky_topics', array() ) );
}
