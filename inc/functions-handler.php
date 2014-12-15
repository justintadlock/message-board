<?php
/**
 * Front end post/request actions handler.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action( 'template_redirect', 'mb_template_redirect', 0 );

function mb_template_redirect() {
	do_action( 'mb_template_redirect' );
}

add_action( 'mb_template_redirect', 'mb_handler_new_forum'       );
add_action( 'mb_template_redirect', 'mb_handler_new_topic'       );
add_action( 'mb_template_redirect', 'mb_handler_new_reply'       );

add_action( 'mb_template_redirect', 'mb_handler_edit_access'     );
add_action( 'mb_template_redirect', 'mb_handler_edit_forum'      );
add_action( 'mb_template_redirect', 'mb_handler_edit_topic'      );
add_action( 'mb_template_redirect', 'mb_handler_edit_reply'      );
add_action( 'mb_template_redirect', 'mb_handler_edit_user'       );

add_action( 'mb_template_redirect', 'mb_handler_topic_subscribe' );
add_action( 'mb_template_redirect', 'mb_handler_topic_bookmark'  );

add_action( 'mb_template_redirect', 'mb_handler_forum_toggle_open'  );
add_action( 'mb_template_redirect', 'mb_handler_forum_toggle_trash' );
add_action( 'mb_template_redirect', 'mb_handler_topic_toggle_open'  );
add_action( 'mb_template_redirect', 'mb_handler_topic_toggle_spam'  );
add_action( 'mb_template_redirect', 'mb_handler_topic_toggle_trash' );
add_action( 'mb_template_redirect', 'mb_handler_reply_toggle_spam'  );
add_action( 'mb_template_redirect', 'mb_handler_reply_toggle_trash' );

/**
 * Checks if we're currently viewing a board action page. Actions mean we want to perform some action, 
 * typically interacting with the database on the front end of the site.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_board_action() {
	$allowed = array( 'edit' );
	$action  = get_query_var( 'mb_action' );

	return !empty( $action ) && in_array( $action, $allowed ) ? true : false;
}

/**
 * Gets the current board action. If not viewing an action page, returns an empty string.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_board_action() {
	return mb_is_board_action() ? sanitize_key( get_query_var( 'mb_action' ) ) : '';
}

/**
 * Checks a `$_POST` nonce by name and action.  If the nonce wasn't posted, returns `FALSE`.  If the 
 * nonce was posted, verify it using `wp_verify_nonce()`.  Returns `TRUE` if things check.  Dies if 
 * it fails.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @param  string  $action
 * @return bool
 */
function mb_check_post_nonce( $name, $action ) {

	if ( !isset( $_POST[ $name ] ) )
		return false;

	if ( !wp_verify_nonce( $_POST[ $name ], $action ) )
		mb_bring_the_doom( 'nonce-failed' );

	return true;
}

/**
 * Returns an array of messages when something fails.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_messages_of_doom() {

	$doom = array(
		'no-title'      => __( "Hey, yo! It's all about the name. Give this thing a title folks will remember.", 'message-board' ),
		'no-content'    => __( "How about showing some respect for the form and adding some content?",           'message-board' ),
		'no-permission' => __( "Whoah, partner! I'm not buying your story. What's your name? Who sent you?",     'message-board' ),
		'what-edit'     => __( "I didn't credit you with an overabundance of education, but this? This?",        'message-board' ),
		'no-topic-id'   => __( "Do you think we can just stay on topic for once? Just this once? Please?",       'message-board' ),
		'nonce-failed'  => __( "Whoah there, partner! What do you think you're doing?",                          'message-board' ),
	);

	return apply_filters( 'mb_get_messages_of_doom', $doom );
}

/**
 * Gets one of the failed messages based on context.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $handle
 * @return string
 */
function mb_get_message_of_doom( $handle ) {
	$doom = mb_get_messages_of_doom();

	return isset( $doom[ $handle ] ) ? $doom[ $handle ] : '';
}

/**
 * Kills the page and prints an error message using `wp_die()`.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $handle
 * @return void
 */
function mb_bring_the_doom( $handle ) {
	wp_die( mb_get_message_of_doom( $handle ) );
	exit();
}

/**
 * Figures out whether we're on an edit page and whether the current user has permission to be here.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_edit_access() {

	if ( mb_is_edit() ) {

		if ( mb_is_forum_edit() && !current_user_can( 'edit_forum', mb_get_forum_id() ) )
			mb_bring_the_doom( 'no-permission' );

		elseif ( mb_is_topic_edit() && !current_user_can( 'edit_topic', mb_get_topic_id() ) )
			mb_bring_the_doom( 'no-permission' );

		elseif ( mb_is_reply_edit() && !current_user_can( 'edit_reply', mb_get_reply_id() ) )
			mb_bring_the_doom( 'no-permission' );

		elseif ( mb_is_user_edit() && !current_user_can( 'edit_user', mb_get_user_id() ) )
			mb_bring_the_doom( 'no-permission' );

		elseif ( !mb_is_forum_edit() && !mb_is_topic_edit() && !mb_is_reply_edit() && !mb_is_user_edit() )
			mb_bring_the_doom( 'no-permission' );
	}
}

/**
 * Front end new forum handler.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_new_forum() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_new_forum_nonce', 'mb_new_forum_action' ) )
		return;

	/* Make sure the current user can create forums. */
	if ( !current_user_can( 'create_forums' ) )
		mb_bring_the_doom( 'no-permission' );

	/* Make sure we have a forum title. */
	if ( empty( $_POST['mb_forum_title'] ) )
		mb_bring_the_doom( 'no-title' );

	/* Post title. */
	$post_title = apply_filters( 'mb_pre_insert_forum_title', $_POST['mb_forum_title'] );

	/* Post content. */
	$post_content = apply_filters( 'mb_pre_insert_forum_content', $_POST['mb_forum_content'] );

	/* Forum ID. */
	$post_parent = isset( $_POST['mb_post_parent'] ) ? mb_get_forum_id( $_POST['mb_post_parent'] ) : 0;

	/* Menu order. */
	$menu_order = isset( $_POST['mb_menu_order'] ) ? absint( $_POST['mb_menu_order'] ) : 0;

	/* Publish a new forum topic. */
	$published = mb_insert_forum(
		array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_parent'  => $post_parent,
			'menu_order'   => $menu_order
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		/* Forum type. */
		if ( isset( $_POST['mb_forum_type'] ) )
			mb_set_forum_type( $published, sanitize_key( $_POST['mb_forum_type'] ) );

		/* Forum subscription. */
		if ( isset( $_POST['mb_forum_subscribe'] ) && 1 === absint( $_POST['mb_forum_subscribe'] ) )
			mb_add_user_forum_subscription( mb_get_forum_author_id( $published ), $published );

		/* Redirect to the published single post. */
		wp_safe_redirect( get_permalink( $published ) );
		exit();
	}
}

/**
 * Front end edit forum handler.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_edit_forum() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_edit_forum_nonce', 'mb_edit_forum_action' ) )
		return;

	/* Make sure we have a forum ID. */
	if ( !isset( $_POST['mb_forum_id'] ) )
		mb_bring_the_doom( 'what-edit' );

	/* Get the forum ID. */
	$forum_id = mb_get_forum_id( $_POST['mb_forum_id'] );

	/* Make sure the current user can edit this forum. */
	if ( !current_user_can( 'edit_forum', $forum_id ) )
		mb_bring_the_doom( 'no-permission' );

	/* Make sure we have a forum title. */
	if ( empty( $_POST['mb_forum_title'] ) )
		mb_bring_the_doom( 'no-title' );

	/* Post title. */
	$post_title = apply_filters( 'mb_pre_insert_forum_title', $_POST['mb_forum_title'] );

	/* Post content. */
	$post_content = apply_filters( 'mb_pre_insert_forum_content', $_POST['mb_forum_content'] );

	/* Forum ID. */
	$post_parent = isset( $_POST['mb_post_parent'] ) ? absint( $_POST['mb_post_parent'] ) : mb_get_forum_parent_id( $forum_id );

	/* Menu order. */
	$menu_order = isset( $_POST['mb_menu_order'] ) ? intval( $_POST['mb_menu_order'] ) : mb_get_forum_order( $forum_id );

	/* Update the forum. */
	$published = wp_update_post(
		array(
			'ID'           => $forum_id,
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_parent'  => $post_parent,
			'menu_order'   => $menu_order
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		$user_id = mb_get_forum_author_id( $published );

		/* Forum type. */
		if ( isset( $_POST['mb_forum_type'] ) )
			mb_set_forum_type( $published, sanitize_key( $_POST['mb_forum_type'] ) );

		/* If the user chose to subscribe to the forum. */
		if ( isset( $_POST['mb_forum_subscribe'] ) && 1 === absint( $_POST['mb_forum_subscribe'] ) )
			mb_add_user_forum_subscription( $user_id, $published );
		else
			mb_remove_user_forum_subscription( $user_id, $published );

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
		exit();
	}
}

/**
 * Front end new topic handler.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_new_topic() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_new_topic_nonce', 'mb_new_topic_action' ) )
		return;

	/* Make sure the current user can create forum topics. */
	if ( !current_user_can( 'create_topics' ) )
		mb_bring_the_doom( 'no-permission' );

	/* Make sure we have a topic title. */
	if ( empty( $_POST['mb_topic_title'] ) )
		mb_bring_the_doom( 'no-title' );

	/* Make sure we have topic content. */
	if ( empty( $_POST['mb_topic_content'] ) )
		mb_bring_the_doom( 'no-content' );

	/* Post title. */
	$post_title = apply_filters( 'mb_pre_insert_topic_title', $_POST['mb_topic_title'] );

	/* Post content. */
	$post_content = apply_filters( 'mb_pre_insert_topic_content', $_POST['mb_topic_content'] );

	/* Forum ID. */
	$forum_id = isset( $_POST['mb_topic_forum'] ) ? mb_get_forum_id( $_POST['mb_topic_forum'] ) : 0;
	$forum_id = 0 < $forum_id ? $forum_id : mb_get_default_forum_id();

	/* Publish a new forum topic. */
	$published = mb_insert_topic(
		array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_parent'  => $forum_id,
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] )
			mb_add_user_subscription( get_current_user_id(), $published );

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

/**
 * Front end edit topic handler.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_edit_topic() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_edit_topic_nonce', 'mb_edit_topic_action' ) )
		return;

	/* Make sure we have a topic ID. */
	if ( !isset( $_POST['mb_topic_id'] ) )
		mb_bring_the_doom( 'what-edit' );

	/* Get the topic ID. */
	$topic_id = mb_get_topic_id( $_POST['mb_topic_id'] );

	/* Make sure the current user can create forum topics. */
	if ( !current_user_can( 'edit_topic', $topic_id ) )
		mb_bring_the_doom( 'no-permission' );

	/* Make sure we have a topic title. */
	if ( empty( $_POST['mb_topic_title'] ) )
		mb_bring_the_doom( 'no-title' );

	/* Make sure we have topic content. */
	if ( empty( $_POST['mb_topic_content'] ) )
		mb_bring_the_doom( 'no-content' );

	/* Post title. */
	$post_title = apply_filters( 'mb_pre_insert_topic_title', $_POST['mb_topic_title'] );

	/* Post content. */
	$post_content = apply_filters( 'mb_pre_insert_topic_content', $_POST['mb_topic_content'] );

	/* Forum ID. */
	$forum_id = isset( $_POST['mb_topic_forum'] ) ? mb_get_forum_id( $_POST['mb_topic_forum'] ) : 0;
	$forum_id = 0 < $forum_id ? $forum_id : mb_get_default_forum_id();

	/* Update the topic. */
	$published = wp_update_post(
		array(
			'ID'           => $topic_id,
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_parent'  => $forum_id,
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 === absint( $_POST['mb_topic_subscribe'] ) )
			mb_add_user_subscription( absint( $user_id ), $published );
		else
			mb_remove_user_subscription( absint( $user_id ), $published );

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

/**
 * Front end new reply handler.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_new_reply() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_new_reply_nonce', 'mb_new_reply_action' ) )
		return;

	/* Make sure the current user can create forum replies. */
	if ( !current_user_can( 'create_forum_replies' ) )
		mb_bring_the_doom( 'no-permission' );

	/* Make sure we have a topic ID. */
	if ( empty( $_POST['mb_reply_topic_id'] ) )
		mb_bring_the_doom( 'no-topic-id' );

	/* Make sure we have reply content. */
	if ( empty( $_POST['mb_reply_content'] ) )
		mb_bring_the_doom( 'no-content' );

	/* Parent post ID. */
	$topic_id = mb_get_topic_id( $_POST['mb_reply_topic_id'] );

	/* Post content. */
	$post_content = apply_filters( 'mb_pre_insert_reply_content', $_POST['mb_reply_content'] );

	/* Publish a new reply. */
	$published = mb_insert_reply(
		array(
			'post_content' => $post_content,
			'post_parent'  => $topic_id,
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 === absint( $_POST['mb_topic_subscribe'] ) )
			mb_add_user_subscription( absint( $user_id ), $topic_id );

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

/**
 * Front end edit reply handler.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_edit_reply() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_edit_reply_nonce', 'mb_edit_reply_action' ) )
		return;

	/* Make sure we have a reply ID. */
	if ( !isset( $_POST['mb_reply_id'] ) )
		mb_bring_the_doom( 'what-edit' );

	/* Get the reply ID. */
	$reply_id = mb_get_reply_id( $_POST['mb_reply_id'] );

	/* Make sure the current user can edit the reply. */
	if ( !current_user_can( 'edit_reply', $reply_id ) )
		mb_bring_the_doom( 'no-permission' );

	/* Make sure we have reply content. */
	if ( empty( $_POST['mb_reply_content'] ) )
		mb_bring_the_doom( 'no-content' );

	/* Post content. */
	$post_content = apply_filters( 'mb_pre_insert_reply_content', $_POST['mb_reply_content'] );

	/* Publish a new reply. */
	$published = wp_update_post(
		array(
			'ID'           => $reply_id,
			'post_content' => $post_content,
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		$user_id = mb_get_reply_author_id( $published );

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 === absint( $_POST['mb_topic_subscribe'] ) )
			mb_add_user_subscription( $user_id, $topic_id );
		else
			mb_remove_user_subscription( $user_id, $topic_id );

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

function mb_handler_edit_user() {

	/* Verify the nonce. */
	if ( !mb_check_post_nonce( 'mb_edit_user_nonce', 'mb_edit_user_action' ) )
		return;

	/* Get the user ID. */
	$user_id = mb_get_user_id( $_POST['mb_user_id'] );

	/* Make sure the current user can edit the user. */
	if ( !current_user_can( 'edit_user', $user_id ) )
		mb_bring_the_doom( 'no-permission' );

	/* Get the user object. */
	$user = new WP_User( $user_id );

	$first_name = !empty( $_POST['mb_first_name'] ) ? esc_html( strip_tags( $_POST['mb_first_name'] ) ) : '';
	$last_name  = !empty( $_POST['mb_last_name']  ) ? esc_html( strip_tags( $_POST['mb_last_name']  ) ) : '';
	//$nickname   = !empty( $_POST['mb_nickname']   ) ? esc_html( strip_tags( $_POST['mb_nickname']   ) ) : $user->nickname;

	/*$updated = wp_update_user(
		array(
		)
	);*/

	if ( $updated && !is_wp_error( $updated ) ) {

		/* Redirect to user profile. */
		wp_safe_redirect( mb_get_user_profile_url( $user_id ) );
	}
}

function mb_handler_topic_subscribe() {

	if ( !is_user_logged_in() )
		return;

	// @todo nonce?

	if ( !isset( $_GET['action'] ) || !in_array( $_GET['action'], array( 'subscribe', 'unsubscribe' ) ) )
		return;

	if ( !isset( $_GET['topic_id'] ) )
		return;

	$topic_id = absint( $_GET['topic_id'] );

	$user_id = get_current_user_id();

	if ( 0 < $user_id && 0 < $topic_id && 'subscribe' === $_GET['action'] ) {

		$added = mb_add_user_subscription( absint( $user_id ), $topic_id );

		if ( $added ) {
			mb_set_topic_subscribers( $topic_id );
		}

	} elseif ( 0 < $user_id && 0 < $topic_id && 'unsubscribe' === $_GET['action'] ) {

		$removed = mb_remove_user_subscription( $user_id, $topic_id );

		if ( $removed ) {
			mb_set_topic_subscribers( $topic_id );
		}
	}

		if ( isset( $_GET['redirect'] ) ) {

			wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
		}
}


function mb_handler_topic_bookmark() {

	if ( !is_user_logged_in() )
		return;

	// @todo nonce?

	if ( !isset( $_GET['action'] ) || !in_array( $_GET['action'], array( 'bookmark', 'unbookmark' ) ) )
		return;

	if ( !isset( $_GET['topic_id'] ) )
		return;

	$topic_id = absint( $_GET['topic_id'] );

	$user_id = get_current_user_id();

	if ( 0 < $user_id && 0 < $topic_id && 'bookmark' === $_GET['action'] ) {

		// mb_get_user_bookmarks( $user_id );

		$bookmarks = get_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), true );
		$favs = explode( ',', $bookmarks );


		if ( !in_array( $topic_id, $favs ) ) {
			$favs[] = $topic_id;

			$new_bookmarks   = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

			update_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), $new_bookmarks );

			mb_set_topic_bookmarkers( $topic_id );
		}

	} elseif ( 0 < $user_id && 0 < $topic_id && 'unbookmark' === $_GET['action'] ) {

		// mb_get_user_bookmarks( $user_id );

		$bookmarks = get_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), true );
		$favs = explode( ',', $bookmarks );

		if ( in_array( $topic_id, $favs ) ) {

			$_fav = array_search( $topic_id, $favs );

			unset( $favs[ $_fav ] );

			$new_bookmarks   = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

			update_user_meta( $user_id, mb_get_user_topic_bookmarks_meta_key(), $new_bookmarks );

			mb_set_topic_bookmarkers( $topic_id );
		}
	}

		if ( isset( $_GET['redirect'] ) ) {

			wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
		}
}

function mb_handler_forum_toggle_open() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_open' !== $_GET['action'] || !isset( $_GET['forum_id'] ) )
		return;

	$forum_id = mb_get_forum_id( $_GET['forum_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "open_forum_{$forum_id}" ) )
		return;

	if ( !current_user_can( 'moderate_forum', $forum_id ) )
		return;

	$updated = mb_is_forum_open( $forum_id ) ? mb_close_forum( $forum_id ) : mb_open_forum( $forum_id );

	$redirect = remove_query_arg( array( 'action', 'forum_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}

function mb_handler_topic_toggle_open() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_open' !== $_GET['action'] || !isset( $_GET['topic_id'] ) )
		return;

	$topic_id = mb_get_topic_id( $_GET['topic_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "open_topic_{$topic_id}" ) )
		return;

	if ( !current_user_can( 'moderate_topic', $topic_id ) )
		return;

	$updated = mb_is_topic_open( $topic_id ) ? mb_close_topic( $topic_id ) : mb_open_topic( $topic_id );

	$redirect = remove_query_arg( array( 'action', 'topic_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}

function mb_handler_topic_toggle_spam() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_spam' !== $_GET['action'] || !isset( $_GET['topic_id'] ) )
		return;

	$topic_id = mb_get_topic_id( $_GET['topic_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "spam_topic_{$topic_id}" ) )
		return;

	if ( !current_user_can( 'moderate_topic', $topic_id ) )
		return;

	$updated = mb_is_topic_spam( $topic_id ) ? mb_unspam_topic( $topic_id ) : mb_spam_topic( $topic_id );

	$redirect = remove_query_arg( array( 'action', 'topic_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}

function mb_handler_reply_toggle_spam() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_spam' !== $_GET['action'] || !isset( $_GET['reply_id'] ) )
		return;

	$reply_id = mb_get_reply_id( $_GET['reply_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "spam_reply_{$reply_id}" ) )
		return;

	// @todo - moderate cap for this specific reply
	if ( !current_user_can( 'manage_forums' ) )
		return;

	$updated = mb_is_reply_spam( $reply_id ) ? mb_unspam_reply( $reply_id ) : mb_spam_reply( $reply_id );

	$redirect = remove_query_arg( array( 'action', 'reply_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}

function mb_handler_forum_toggle_trash() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_trash' !== $_GET['action'] || !isset( $_GET['forum_id'] ) )
		return;

	$forum_id = mb_get_forum_id( $_GET['forum_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "trash_forum_{$forum_id}" ) )
		return;

	if ( !current_user_can( 'moderate_forum', $forum_id ) )
		return;

	$updated = mb_is_forum_trash( $forum_id ) ? wp_untrash_post( $forum_id ) : wp_trash_post( $forum_id );

	$redirect = remove_query_arg( array( 'action', 'forum_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}

function mb_handler_topic_toggle_trash() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_trash' !== $_GET['action'] || !isset( $_GET['topic_id'] ) )
		return;

	$topic_id = mb_get_topic_id( $_GET['topic_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "trash_topic_{$topic_id}" ) )
		return;

	if ( !current_user_can( 'moderate_topic', $topic_id ) )
		return;

	$updated = mb_is_topic_trash( $topic_id ) ? wp_untrash_post( $topic_id ) : wp_trash_post( $topic_id );

	$redirect = remove_query_arg( array( 'action', 'topic_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}

function mb_handler_reply_toggle_trash() {

	if ( !isset( $_GET['action'] ) || 'mb_toggle_trash' !== $_GET['action'] || !isset( $_GET['reply_id'] ) )
		return;

	$reply_id = mb_get_reply_id( $_GET['reply_id'] );

	/* Verify nonce. */
	if ( !isset( $_GET['mb_nonce'] ) || !wp_verify_nonce( $_GET['mb_nonce'], "trash_reply_{$reply_id}" ) )
		return;

	if ( !current_user_can( 'moderate_reply', $reply_id ) )
		return;

	$updated = mb_is_reply_trash( $reply_id ) ? wp_untrash_post( $reply_id ) : wp_trash_post( $reply_id );

	$redirect = remove_query_arg( array( 'action', 'reply_id', 'mb_nonce' ) );

	wp_safe_redirect( esc_url( $redirect ) );
}
