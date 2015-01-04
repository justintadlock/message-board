<?php
/**
 * Subscriptions/Notifications API.  This allows users to subscribe to forums or topics.  Notifications 
 * should be hierarchical.  If a user is subscribed to a topic, the user should receive notifications of 
 * new replies.  If a user is subscribed to a forum, the user should receive notifications of new topics 
 * and replies to those topics.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* ====== Forum Subscriptions ====== */

function mb_add_user_forum_subscription( $user_id, $forum_id ) {

	$user_id  = mb_get_user_id( $user_id );
	$forum_id = mb_get_forum_id( $forum_id );

	$subs = mb_get_user_forum_subscriptions( $user_id );

	if ( !in_array( $forum_id, $subs ) ) {
		wp_cache_delete( 'mb_get_forum_subscribers_' . $forum_id, 'message-board-users' );

		$subs[] = $forum_id;

		$subs = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );

		return update_user_meta( $user_id, mb_get_user_forum_subscriptions_meta_key(), $subs );
	}

	return false;
}

function mb_remove_user_forum_subscription( $user_id, $forum_id ) {

	$user_id  = mb_get_user_id( $user_id );
	$forum_id = mb_get_forum_id( $forum_id );

	$subs = mb_get_user_forum_subscriptions( $user_id );

	if ( in_array( $forum_id, $subs ) ) {
		wp_cache_delete( 'mb_get_forum_subscribers_' . $forum_id, 'message-board-users' );

		$_sub = array_search( $forum_id, $subs );

		unset( $subs[ $_sub ] );

		$subs = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );

		return update_user_meta( $user_id, mb_get_user_forum_subscriptions_meta_key(), $subs );
	}

	return false;
}

function mb_get_user_forum_subscriptions( $user_id ) {

	$user_id = mb_get_user_id( $user_id );

	$subscriptions = get_user_meta( $user_id, mb_get_user_forum_subscriptions_meta_key(), true );
	$subscriptions = !empty( $subscriptions ) ? explode( ',', $subscriptions ) : array();

	return apply_filters( 'mb_get_user_forum_subscriptions', $subscriptions, $user_id );
}

/**
 * Checks if the user is subscribed to the forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  int     $forum_id
 * @return bool
 */
function mb_is_user_subscribed_forum( $user_id = 0, $forum_id = 0 ) {

	$user_id  = mb_get_user_id( $user_id );
	$forum_id = mb_get_forum_id( $forum_id );

	$is_subscribed = in_array( $forum_id, mb_get_user_forum_subscriptions( $user_id ) ) ? true : false;

	return apply_filters( 'mb_is_user_subscribed_forum', $is_subscribed, $user_id, $forum_id );
}

/**
 * Get an array of user IDs for users who are subscribed to the forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return array
 */
function mb_get_forum_subscribers( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$users = wp_cache_get( 'mb_get_forum_subscribers_' . $forum_id, 'message-board-users' );

	if ( false === $users ) {
		global $wpdb;

		$users = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND FIND_IN_SET( '{$forum_id}', meta_value ) > 0", mb_get_user_forum_subscriptions_meta_key() ) );
		wp_cache_set( 'mb_get_forum_subscribers_' . $forum_id, $users, 'message-board-users' );
	}

	return apply_filters( 'mb_get_forum_subscribers', $users );
}

/* ====== Topic Subscriptions ====== */

function mb_add_user_topic_subscription( $user_id, $topic_id ) {

	$user_id  = mb_get_user_id( $user_id );
	$topic_id = mb_get_topic_id( $topic_id );

	$subs = mb_get_user_topic_subscriptions( $user_id );

	if ( !in_array( $topic_id, $subs ) ) {
		wp_cache_delete( 'mb_get_topic_subscribers_' . $topic_id, 'message-board-users' );

		$subs[] = $topic_id;

		$subs = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );

		return update_user_meta( $user_id, mb_get_user_topic_subscriptions_meta_key(), $subs );
	}

	return false;
}

function mb_get_user_topic_subscriptions( $user_id ) {

	$user_id = mb_get_user_id( $user_id );

	$subscriptions = get_user_meta( $user_id, mb_get_user_topic_subscriptions_meta_key(), true );
	$subscriptions = !empty( $subscriptions ) ? explode( ',', $subscriptions ) : array();

	return apply_filters( 'mb_get_user_topic_subscriptions', $subscriptions, $user_id );
}

function mb_remove_user_topic_subscription( $user_id, $topic_id ) {

	$user_id  = mb_get_user_id( $user_id );
	$topic_id = mb_get_topic_id( $topic_id );

	$subs = mb_get_user_topic_subscriptions( $user_id );

	if ( in_array( $topic_id, $subs ) ) {
		wp_cache_delete( 'mb_get_topic_subscribers_' . $topic_id, 'message-board-users' );

		$_sub = array_search( $topic_id, $subs );

		unset( $subs[ $_sub ] );

		$subs = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );

		return update_user_meta( $user_id, mb_get_user_topic_subscriptions_meta_key(), $subs );
	}

	return false;
}

/**
 * Checks if the user is subscribed to the topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  int     $topic_id
 * @return bool
 */
function mb_is_user_subscribed_topic( $user_id = 0, $topic_id = 0 ) {

	$user_id  = mb_get_user_id( $user_id );
	$topic_id = mb_get_topic_id( $topic_id );

	$is_subscribed = in_array( $topic_id, mb_get_user_topic_subscriptions( $user_id ) ) ? true : false;

	return apply_filters( 'mb_is_user_subscribed_topic', $is_subscribed, $user_id, $topic_id );
}

/**
 * Get an array of user IDs for users who are subscribed to the topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return array
 */
function mb_get_topic_subscribers( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	$users = wp_cache_get( 'mb_get_topic_subscribers_' . $topic_id, 'message-board-users' );

	if ( false === $users ) {
		global $wpdb;

		$users = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND FIND_IN_SET( '{$topic_id}', meta_value ) > 0", mb_get_user_topic_subscriptions_meta_key() ) );
		wp_cache_set( 'mb_get_topic_subscribers_' . $topic_id, $users, 'message-board-users' );
	}

	return apply_filters( 'mb_get_topic_subscribers', $users );
}

/**
 * Notifies users by email when a new post is published.  By default, notifications are sent to users 
 * who are subscribed to a particular forum or a particular topic.
 *
 * @since  1.0.0
 * @access public
 * @param  object|int  $post
 * @return void
 */
function mb_notify_subscribers( $post ) {

	$post = !is_object( $post ) ? get_post( $post ) : $post;

	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	if ( !in_array( $post->post_type, array( $forum_type, $topic_type, $reply_type ) ) )
		return;

	do_action( 'mb_before_notify_subscribers', $post );

	if ( $topic_type === $post->post_type ) {
		//mb_notify_forum_subscribers( $post );

	} elseif ( $reply_type === $post->post_type ) {
		//mb_notify_forum_subscribers( $post );
		mb_notify_topic_subscribers( $post->post_parent, $post );
	}

	do_action( 'mb_after_notify_subscribers', $post );
}

function mb_notify_forum_subscribers( $forum_id, $post ) {
}

function mb_notify_topic_subscribers( $topic_id, $post ) {

	$reply_id = mb_get_reply_id( $post->ID );
	$topic_id = mb_get_topic_id( $topic_id );
	$forum_id = mb_get_topic_forum_id( $topic_id );

	$forum_subscribers = $forum_id ? mb_get_forum_subscribers( $forum_id ) : array();
	$topic_subscribers = $topic_id ? mb_get_topic_subscribers( $topic_id ) : array();

	/* Remove users who are already subscribed to the topic's forum or who wrote the post. */
	$subscribers = array_diff( $topic_subscribers, $forum_subscribers, array( $post->post_author ) );

	/* If there are no subscribers, bail. */
	if ( empty( $subscribers ) )
		return false;

	/* Get needed topic data. */
	$topic_title   = strip_tags( mb_get_topic_title(   $topic_id ) );

	/* Get needed reply data. */
	$reply_url        = mb_get_reply_url( $reply_id );
	$reply_author     = mb_get_reply_author( $reply_id );
	$reply_author_id  = mb_get_reply_author_id( $reply_id );
	$reply_content    = mb_get_reply_content( $reply_id, 'raw' );

	/* Filter the reply content for email. */
	$reply_content = apply_filters( 'mb_pre_email_reply_content', $reply_content, $reply_id );

	/* Build the email message. */
	$message = sprintf( 
		__( '%1$s replied: %4$s%2$s %4$sPost Link: %3$s %4$sYou are receiving this email because you subscribed to a forum topic. Log in and visit the topic to unsubscribe from these emails.', 'message-board' ),
		$reply_author,
		$reply_content,
		$reply_url,
		"\n\n"
	);

	/* Get the site name and domain. */
	$site_name   = esc_html( strip_tags( get_option( 'blogname' ) ) );
	$site_domain = untrailingslashit( str_replace( array( 'http://', 'https://' ), '', home_url() ) );

	/* Who's the message from? */
	$from = '<noreply@' . $site_domain . '>';

	/* Translators: Email subject. 1 is the blog name. 2 is the topic title. */
	$subject = sprintf( esc_attr__( '[$1%s] $2%s', 'message-board' ), $site_name, $topic_title );

	/* Build the email headers. */
	$headers = array();

	$headers[] = sprintf( 'From: %s %s', $site_name, $from );

	foreach ( $subscribers as $user_id )
		$headers[] = 'Bcc: ' . get_userdata( $user_id )->user_email;

	/* Send the email. */
	return wp_mail( $from, $subject, $message, $headers );
}
