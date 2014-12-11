<?php
/**
 * Registers custom metadata and handles custom meta functions.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register custom meta keys. */
add_action( 'init', 'mb_register_meta' );

/**
 * Registers custom meta keys with WordPress and provides callbacks for sanitizing and authorizing 
 * the metadata.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_meta() {

	/* General post meta. */
	register_meta( 'post', mb_get_prev_status_meta_key(), 'sanitize_key', '__return_true' );

	/* Forum meta. */
	register_meta( 'post', mb_get_forum_activity_datetime_meta_key(),       'esc_html', '__return_true' );
	register_meta( 'post', mb_get_forum_activity_datetime_epoch_meta_key(), 'esc_html', '__return_true' );
	register_meta( 'post', mb_get_forum_last_topic_id_meta_key(),           'absint',   '__return_true' );
	register_meta( 'post', mb_get_forum_last_reply_id_meta_key(),           'absint',   '__return_true' );
	register_meta( 'post', mb_get_forum_topic_count_meta_key(),             'absint',   '__return_true' );
	register_meta( 'post', mb_get_forum_reply_count_meta_key(),             'absint',   '__return_true' );
	register_meta( 'post', mb_get_forum_type_meta_key(),                    'esc_html', '__return_true' );
	register_meta( 'post', mb_get_forum_level_meta_key(),                   'absint',   '__return_true' );

	/* Topic meta. */
	register_meta( 'post', mb_get_topic_activity_datetime_meta_key(),       'esc_html',   '__return_true' );
	register_meta( 'post', mb_get_topic_activity_datetime_epoch_meta_key(), 'esc_html',   '__return_true' );
	register_meta( 'post', mb_get_topic_last_reply_id_meta_key(),           'absint',     '__return_true' );
	register_meta( 'post', mb_get_topic_voices_meta_key(),                  'esc_html',   '__return_true' );
	register_meta( 'post', mb_get_topic_voice_count_meta_key(),             'absint',     '__return_true' );
	register_meta( 'post', mb_get_topic_reply_count_meta_key(),             'absint',     '__return_true' );

	/* User meta. */
	register_meta( 'user', mb_get_user_forum_subscriptions_meta_key(), 'esc_html', '__return_true' );
	register_meta( 'user', mb_get_user_topic_subscriptions_meta_key(), 'esc_html', '__return_true' );
	register_meta( 'user', mb_get_user_topic_bookmarks_meta_key(),     'esc_html', '__return_true' );
	register_meta( 'user', mb_get_user_forum_count_meta_key(),         'absint',   '__return_true' );
	register_meta( 'user', mb_get_user_topic_count_meta_key(),         'absint',   '__return_true' );
	register_meta( 'user', mb_get_user_reply_count_meta_key(),         'absint',   '__return_true' );
}

/**
 * Returns the meta key used for the "previous post status" for the any post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_prev_status_meta_key() {
	return apply_filters( 'mb_get_prev_status_meta_key', '_prev_post_status' );
}

/**
 * Returns the meta key used for the "activity datetime" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_activity_datetime_meta_key() {
	return apply_filters( 'mb_get_forum_activity_datetime_meta_key', '_forum_activity_datetime' );
}

/**
 * Returns the meta key used for the "activity epoch datetime" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_activity_datetime_epoch_meta_key() {
	return apply_filters( 'mb_get_forum_activity_datetime_epoch_meta_key', '_forum_activity_datetime_epoch' );
}

/**
 * Returns the meta key used for the "last topic ID" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_last_topic_id_meta_key() {
	return apply_filters( 'mb_get_forum_last_topic_id_meta_key', '_forum_last_topic_id' );
}

/**
 * Returns the meta key used for the "last reply ID" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_last_reply_id_meta_key() {
	return apply_filters( 'mb_get_forum_last_reply_id_meta_key', '_forum_last_reply_id' );
}

/**
 * Returns the meta key used for the "topic count" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_topic_count_meta_key() {
	return apply_filters( 'mb_get_forum_topic_count_meta_key', '_forum_topic_count' );
}

/**
 * Returns the meta key used for the "reply count" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_reply_count_meta_key() {
	return apply_filters( 'mb_get_forum_reply_count_meta_key', '_forum_reply_count' );
}

/**
 * Returns the meta key used for the "forum type" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_type_meta_key() {
	return apply_filters( 'mb_get_forum_type_meta_key', '_forum_type' );
}

/**
 * Returns the meta key used for the "forum level" for the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_level_meta_key() {
	return apply_filters( 'mb_get_forum_level_meta_key', '_forum_level' );
}

/**
 * Returns the meta key used for the "activity datetime" for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_activity_datetime_meta_key() {
	return apply_filters( 'mb_get_topic_activity_datetime_meta_key', '_topic_activity_datetime' );
}

/**
 * Returns the meta key used for the "activity epoch datetime" for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_activity_datetime_epoch_meta_key() {
	return apply_filters( 'mb_get_topic_activity_datetime_epoch_meta_key', '_topic_activity_datetime_epoch' );
}

/**
 * Returns the meta key used for the "last reply ID" for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_last_reply_id_meta_key() {
	return apply_filters( 'mb_get_topic_last_reply_id_meta_key', '_topic_last_reply_id' );
}

/**
 * Returns the meta key used for the "voices" for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_voices_meta_key() {
	return apply_filters( 'mb_get_topic_voices_meta_key', '_topic_voices' );
}

/**
 * Returns the meta key used for the "voice count" for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_voice_count_meta_key() {
	return apply_filters( 'mb_get_topic_voice_count_meta_key', '_topic_voice_count' );
}

/**
 * Returns the meta key used for the "reply count" for the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_reply_count_meta_key() {
	return apply_filters( 'mb_get_topic_reply_count_meta_key', '_topic_reply_count' );
}

/**
 * Returns the meta key used for user "forum subscriptions".
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_forum_subscriptions_meta_key() {
	return apply_filters( 'mb_get_user_forum_subscriptions_meta_key', '_forum_subscriptions' );
}

/**
 * Returns the meta key used for user "topic subscriptions".
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_topic_subscriptions_meta_key() {
	return apply_filters( 'mb_get_user_topic_subscriptions_meta_key', '_topic_subscriptions' );
}

/**
 * Returns the meta key used for user "topic bookmarks".
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_topic_bookmarks_meta_key() {
	return apply_filters( 'mb_get_user_topic_bookmarks_meta_key', '_topic_bookmarks' );
}

/**
 * Returns the meta key used for user "topic count".
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_forum_count_meta_key() {
	return apply_filters( 'mb_get_user_forum_count_meta_key', '_forum_count' );
}

/**
 * Returns the meta key used for user "topic count".
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_topic_count_meta_key() {
	return apply_filters( 'mb_get_user_topic_count_meta_key', '_topic_count' );
}

/**
 * Returns the meta key used for user "reply count".
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_reply_count_meta_key() {
	return apply_filters( 'mb_get_user_reply_count_meta_key', '_reply_count' );
}
