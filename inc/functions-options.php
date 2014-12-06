<?php

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
	return apply_filters( 'mb_get_show_on_front', 'forums' );
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
	return intval( apply_filters( 'mb_get_forums_per_page', -1 ) );
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
	return intval( apply_filters( 'mb_get_topics_per_page', 15 ) );
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
	return intval( apply_filters( 'mb_get_replies_per_page', 15 ) );
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
	return absint( apply_filters( 'mb_get_default_forum_id', 0 ) );
}

/**
 * Returns an array of super sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_super_sticky_topics() {
	return apply_filters( 'mb_get_super_sticky_topics', get_option( 'mb_super_sticky_topics', array() ) );
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
