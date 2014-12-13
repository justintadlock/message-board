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

add_action( 'mb_template_redirect', 'mb_handler_topic_subscribe' );
add_action( 'mb_template_redirect', 'mb_handler_topic_bookmark'  );

add_action( 'mb_template_redirect', 'mb_handler_forum_toggle_open'  );
add_action( 'mb_template_redirect', 'mb_handler_forum_toggle_trash' );
add_action( 'mb_template_redirect', 'mb_handler_topic_toggle_open'  );
add_action( 'mb_template_redirect', 'mb_handler_topic_toggle_spam'  );
add_action( 'mb_template_redirect', 'mb_handler_topic_toggle_trash' );
add_action( 'mb_template_redirect', 'mb_handler_reply_toggle_spam'  );
add_action( 'mb_template_redirect', 'mb_handler_reply_toggle_trash' );

function mb_is_board_action() {
	$allowed = array( 'edit' );
	$action  = get_query_var( 'mb_action' );

	return !empty( $action ) && in_array( $action, $allowed ) ? true : false;
}

function mb_get_board_action() {
	return mb_is_board_action() ? sanitize_key( get_query_var( 'mb_action' ) ) : '';
}

function mb_check_post_nonce( $name, $action ) {

	if ( !isset( $_POST[ $name ] ) )
		return false;

	if ( !wp_verify_nonce( $_POST[ $name ], $action ) ) {
		wp_die( __( 'Whoah, partner!', 'message-board' ) );
		exit();
	}

	return true;
}

/**
 * New forum handler. This function executes when a new topic is posted on the front end.
 *
 * @todo Separate some of the functionality into its own functions.
 * @todo Use filter hooks for sanitizing rather than doing it directly in the function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_new_forum() {

	if ( !mb_check_post_nonce( 'mb_new_forum_nonce', 'mb_new_forum_action' ) )
		return;

	/* Make sure the current user can create forums. */
	if ( !current_user_can( 'create_forums' ) ) {
		wp_die( 'Sorry, you cannot create new forums.', 'message-board' );
		exit();
	}

	/* Make sure we have a user ID. */
	if ( ! $user_id = get_current_user_id() ) {
		wp_die( 'Did not recognize user.', 'message-board' );
		exit();
	}

	/* Make sure we have a forum title. */
	if ( empty( $_POST['mb_forum_title'] ) ) {
		wp_die( __( 'You did not enter a forum title!', 'message-board' ) );
		exit();
	}

	/* Post title. */
	$post_title = esc_html( strip_tags( $_POST['mb_forum_title'] ) );

	/* Post content. */
	if ( !empty( $_POST['mb_forum_content'] ) ) {
		$post_content = $_POST['mb_forum_content'];
		$post_content = mb_encode_bad( $post_content );
		$post_content = mb_code_trick( $post_content );
		$post_content = force_balance_tags( $post_content );
		$post_content = mb_filter_post_kses( $post_content );
	} else {
		$post_content = '';
	}

	/* Forum ID. */
	$post_parent = 0 >= $_POST['mb_post_parent'] ? 0 : mb_get_forum_id( $_POST['mb_post_parent'] );

	/* Menu order. */
	$menu_order = 0 >= $_POST['mb_menu_order'] ? 0 : absint( $_POST['mb_menu_order'] );

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
		if ( isset( $_POST['mb_forum_type'] ) ) {
			mb_set_forum_type( $published, sanitize_key( $_POST['mb_forum_type'] ) );
		}

		/* If the user chose to subscribe to the forum. */
		/*
		if ( isset( $_POST['mb_forum_subscribe'] ) && 1 == $_POST['mb_forum_subscribe'] ) {

			mb_add_user_forum_subscription( absint( $user_id ), $published );
		}
		*/

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
		exit();
	}
}

function mb_handler_edit_forum() {

	if ( !mb_check_post_nonce( 'mb_edit_forum_nonce', 'mb_edit_forum_action' ) )
		return;

	/* Make sure we have a forum ID. */
	if ( !isset( $_POST['mb_forum_id'] ) ) {
		wp_die( __( 'What are you editing?', 'message-board' ) );
		exit();
	}

	$forum_id = mb_get_forum_id( $_POST['mb_forum_id'] );

	/* Make sure the current user can edit this forum. */
	if ( !current_user_can( 'edit_forum', $forum_id ) ) {
		wp_die( 'You do not have permission to edit this forum.', 'message-board' );
		exit();
	}

	/* Make sure we have a forum title. */
	if ( empty( $_POST['mb_forum_title'] ) ) {
		wp_die( __( 'You did not enter a forum title!', 'message-board' ) );
		exit();
	}

	/* Post title. */
	$post_title = esc_html( strip_tags( $_POST['mb_forum_title'] ) );

	/* Post content. */
	if ( !empty( $_POST['mb_forum_content'] ) ) {
		$post_content = $_POST['mb_forum_content'];
		$post_content = mb_encode_bad( $post_content );
		$post_content = mb_code_trick( $post_content );
		$post_content = force_balance_tags( $post_content );
		$post_content = mb_filter_post_kses( $post_content );
	} else {
		$post_content = '';
	}

	/* Forum ID. */
	$post_parent = isset( $_POST['mb_post_parent'] ) ? absint( $_POST['mb_post_parent'] ) : mb_get_forum_parent_id( $forum_id );

	/* Menu order. */
	$menu_order = isset( $_POST['mb_menu_order'] ) ? intval( $_POST['mb_menu_order'] ) : mb_get_forum_order( $forum_id );

	/* Publish a new forum topic. */
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

		/* Forum type. */
		if ( isset( $_POST['mb_forum_type'] ) ) {
			mb_set_forum_type( $published, sanitize_key( $_POST['mb_forum_type'] ) );
		}

		/* If the user chose to subscribe to the forum. */
		/*
		if ( isset( $_POST['mb_forum_subscribe'] ) && 1 == $_POST['mb_forum_subscribe'] ) {

			mb_add_user_forum_subscription( absint( $user_id ), $published );
		}
		*/

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
		exit();
	}
}

/**
 * New topic handler. This function executes when a new topic is posted on the front end.
 *
 * @todo Separate some of the functionality into its own functions.
 * @todo Use filter hooks for sanitizing rather than doing it directly in the function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_new_topic() {

	if ( !mb_check_post_nonce( 'mb_new_topic_nonce', 'mb_new_topic_action' ) )
		return;

	/* Make sure the current user can create forum topics. */
	if ( !current_user_can( 'create_forum_topics' ) ) {
		wp_die( 'Sorry, you cannot create new forum topics.', 'message-board' );
		exit;
	}

	/* Make sure we have a user ID. */
	if ( ! $user_id = get_current_user_id() ) {
		wp_die( 'Did not recognize user.', 'message-board' );
		exit;
	}

	/* Make sure we have a topic title. */
	if ( empty( $_POST['mb_topic_title'] ) ) {
		wp_die( __( 'You did not enter a topic title!', 'message-board' ) );
		exit;
	}

	/* Make sure we have topic content. */
	if ( empty( $_POST['mb_topic_content'] ) ) {
		wp_die( __( 'You did not enter any content!', 'message-board' ) );
		exit;
	}

	/* Make sure we have a forum. */
	if ( empty( $_POST['mb_topic_forum'] ) ) {
		wp_die( __( 'No forum specified.', 'message-board' ) );
		exit;
	}

	/* Post title. */
	$post_title = esc_html( strip_tags( $_POST['mb_topic_title'] ) );

	/* Post content. */
	$post_content = $_POST['mb_topic_content'];
	$post_content = mb_encode_bad( $post_content );
	$post_content = mb_code_trick( $post_content );
	$post_content = force_balance_tags( $post_content );
	$post_content = mb_filter_post_kses( $post_content );

	/* Forum ID. */
	$forum_id = absint( $_POST['mb_topic_forum'] );

	/* Post Date. */
	$post_date = current_time( 'mysql' );

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
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] ) {

			mb_add_user_subscription( absint( $user_id ), $published );
		}

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

function mb_handler_edit_topic() {

	if ( !mb_check_post_nonce( 'mb_edit_topic_nonce', 'mb_edit_topic_action' ) )
		return;

	/* Make sure we have a topic ID. */
	if ( !isset( $_POST['mb_topic_id'] ) ) {
		wp_die( __( 'What are you editing?', 'message-board' ) );
		exit();
	}

	$topic_id = mb_get_topic_id( $_POST['mb_topic_id'] );

	/* Make sure the current user can create forum topics. */
	if ( !current_user_can( 'edit_topic', $topic_id ) ) {
		wp_die( 'You do not have permission to edit this topic.', 'message-board' );
		exit;
	}

	/* Make sure we have a topic title. */
	if ( empty( $_POST['mb_topic_title'] ) ) {
		wp_die( __( 'You did not enter a topic title!', 'message-board' ) );
		exit;
	}

	/* Make sure we have topic content. */
	if ( empty( $_POST['mb_topic_content'] ) ) {
		wp_die( __( 'You did not enter any content!', 'message-board' ) );
		exit;
	}

	/* Make sure we have a forum. */
	if ( empty( $_POST['mb_topic_forum'] ) ) {
		wp_die( __( 'No forum specified.', 'message-board' ) );
		exit;
	}

	/* Post title. */
	$post_title = esc_html( strip_tags( $_POST['mb_topic_title'] ) );

	/* Post content. */
	$post_content = $_POST['mb_topic_content'];
	$post_content = mb_encode_bad( $post_content );
	$post_content = mb_code_trick( $post_content );
	$post_content = force_balance_tags( $post_content );
	$post_content = mb_filter_post_kses( $post_content );

	/* Forum ID. */
	$forum_id = absint( $_POST['mb_topic_forum'] );

	/* Publish a new forum topic. */
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
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] ) {

			mb_add_user_subscription( absint( $user_id ), $published );
		} else {
			mb_remove_user_subscription( absint( $user_id ), $published );
		}

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

/**
 * New reply handler. This function executes when a new reply is posted on the front end.
 *
 * @todo Separate some of the functionality into its own functions.
 * @todo Use filter hooks for sanitizing rather than doing it directly in the function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_handler_new_reply() {

	if ( !mb_check_post_nonce( 'mb_new_reply_nonce', 'mb_new_reply_action' ) )
		return;

	/* Make sure the current user can create forum replies. */
	if ( !current_user_can( 'create_forum_replies' ) ) {
		wp_die( 'Sorry, you cannot create new replies.', 'message-board' );
		exit;
	}

	/* Make sure we have a user ID. */
	if ( ! $user_id = get_current_user_id() ) {
		wp_die( 'Did not recognize user.', 'message-board' );
		exit;
	}

	/* Make sure we have a topic ID. */
	if ( empty( $_POST['mb_reply_topic_id'] ) ) {
		wp_die( __( 'Forum topic not specified.', 'message-board' ) );
		exit;
	}

	/* Make sure we have reply content. */
	if ( empty( $_POST['mb_reply_content'] ) ) {
		wp_die( __( 'You did not enter any content!', 'message-board' ) );
		exit;
	}

	/* Parent post ID. */
	$topic_id = absint( $_POST['mb_reply_topic_id'] );

	/* Post content. */
	$post_content = $_POST['mb_reply_content'];
	$post_content = mb_encode_bad( $post_content );
	$post_content = mb_code_trick( $post_content );
	$post_content = force_balance_tags( $post_content );
	$post_content = mb_filter_post_kses( $post_content );

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
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] ) {

			$added = mb_add_user_subscription( absint( $user_id ), $topic_id );

			if ( $added ) {
				/* Resets topic subscribers cache. */
				mb_set_topic_subscribers( $topic_id );
			}
		}

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

function mb_handler_edit_reply() {

	if ( !mb_check_post_nonce( 'mb_edit_reply_nonce', 'mb_edit_reply_action' ) )
		return;

	/* Make sure we have a reply ID. */
	if ( !isset( $_POST['mb_reply_id'] ) ) {
		wp_die( __( 'What are you editing?', 'message-board' ) );
		exit();
	}

	$reply_id = mb_get_reply_id( $_POST['mb_reply_id'] );

	/* Make sure the current user can create forum replies. */
	if ( !current_user_can( 'edit_reply', $reply_id ) ) {
		wp_die( 'You do not have permission to edit this reply.', 'message-board' );
		exit;
	}

	/* Make sure we have a topic ID. */
	if ( empty( $_POST['mb_reply_topic_id'] ) ) {
		wp_die( __( 'Forum topic not specified.', 'message-board' ) );
		exit;
	}

	/* Make sure we have reply content. */
	if ( empty( $_POST['mb_reply_content'] ) ) {
		wp_die( __( 'You did not enter any content!', 'message-board' ) );
		exit;
	}

	/* Parent post ID. */
	$topic_id = absint( $_POST['mb_reply_topic_id'] );

	/* Post content. */
	$post_content = $_POST['mb_reply_content'];
	$post_content = mb_encode_bad( $post_content );
	$post_content = mb_code_trick( $post_content );
	$post_content = force_balance_tags( $post_content );
	$post_content = mb_filter_post_kses( $post_content );

	/* Publish a new reply. */
	$published = wp_update_post(
		array(
			'ID'           => $reply_id,
			'post_content' => $post_content,
			'post_parent'  => $topic_id,
		)
	);

	/* If the post was published. */
	if ( $published && !is_wp_error( $published ) ) {

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] ) {

			$subscribed = mb_add_user_subscription( absint( $user_id ), $topic_id );
		} else {
			$subscribed = mb_remove_user_subscription( absint( $user_id ), $topic_id );
		}

			if ( !empty( $subscribed ) ) {
				/* Resets topic subscribers cache. */
				mb_set_topic_subscribers( $topic_id );
			}

		/* Redirect to the published topic page. */
		wp_safe_redirect( get_permalink( $published ) );
	}
}

function mb_handler_edit_access() {

	if ( mb_is_edit() ) {

		if ( mb_is_forum_edit() && !current_user_can( 'edit_forum', absint( get_query_var( 'forum_id' ) ) ) )
			wp_die( 'You do not have permission to edit this forum.', 'message-board' );
		elseif ( mb_is_topic_edit() && !current_user_can( 'edit_topic', absint( get_query_var( 'topic_id' ) ) ) )
			wp_die( 'You do not have permission to edit this topic.', 'message-board' );
		elseif ( mb_is_reply_edit() && !current_user_can( 'edit_reply', absint( get_query_var( 'reply_id' ) ) ) )
			wp_die( 'You do not have permission to edit this reply.', 'message-board' );
		elseif ( mb_is_user_edit() && !current_user_can( 'edit_user', absint( get_query_var( 'user_id' ) ) ) )
			wp_die( 'You do not have permission to edit this user.', 'message-board' );
		elseif ( !mb_is_forum_edit() && !mb_is_topic_edit() && !mb_is_reply_edit() && !mb_is_user_edit() )
			wp_die( 'Whoah, partner!', 'message-board' );
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
