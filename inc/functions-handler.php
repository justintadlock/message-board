<?php

add_action( 'template_redirect', 'mb_template_redirect', 0 );

function mb_template_redirect() {
	do_action( 'mb_template_redirect' );
}

add_action( 'mb_template_redirect', 'mb_handler_new_forum'       );
add_action( 'mb_template_redirect', 'mb_handler_new_topic'       );
add_action( 'mb_template_redirect', 'mb_handler_new_reply'       );
add_action( 'mb_template_redirect', 'mb_handler_edit_post'       );
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

	/* Check if this is a new forum. */
	if ( !isset( $_GET['mb_action'] ) || 'new-forum' !== $_GET['mb_action'] )
		return;

	/* Check if the new forum nonce was posted. */
	if ( !isset( $_POST['mb_new_forum_nonce'] ) || !wp_verify_nonce( $_POST['mb_new_forum_nonce'], 'mb_new_forum_action' ) ) {
		wp_die( __( 'Ooops! Something went wrong!', 'message-board' ) );
		exit();
	}

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
			$forum_type = sanitize_key( $_POST['mb_forum_type'] );
			$forum_type = mb_forum_type_exists( $forum_type ) ? $forum_type : 'forum';

			update_post_meta( $published, mb_get_forum_type_meta_key(), $forum_type );
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

	/* Check if this is a new topic. */
	if ( !isset( $_GET['message-board'] ) || 'new-topic' !== $_GET['message-board'] )
		return;

	/* Check if the new topic nonce was posted. */
	if ( !isset( $_POST['mb_new_topic_nonce'] ) || !wp_verify_nonce( $_POST['mb_new_topic_nonce'], 'mb_new_topic_action' ) ) {
		wp_die( __( 'Ooops! Something went wrong!', 'message-board' ) );
		exit;
	}

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

	/* Check if this is a new reply. */
	if ( !isset( $_GET['message-board'] ) || !in_array( $_GET['message-board'], array( 'new-reply' ) ) )
		return;

	/* Check if the new reply nonce was posted. */
	if ( !isset( $_POST['mb_new_reply_nonce'] ) || !wp_verify_nonce( $_POST['mb_new_reply_nonce'], 'mb_new_reply_action' ) ) {
		wp_die( __( 'Ooops! Something went wrong!', 'message-board' ) );
		exit;
	}

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

function mb_handler_edit_post() {

	if ( !isset( $_GET['message-board'] ) || !in_array( $_GET['message-board'], array( 'edit' ) ) )
		return;

	if ( isset( $_POST['mb_edit_post_nonce'] ) ) {
 
		if ( !wp_verify_nonce( $_POST['mb_edit_post_nonce'], 'mb_edit_post_action' ) ) {
			wp_die( __( 'Ooops! Something went wrong!', 'message-board' ) );
			exit;
		} else {

			if ( empty( $_POST['mb_post_id'] ) ) {
				wp_die( __( 'No post ID found!', 'message-board' ) );
			}

			$post = get_post( absint( $_POST['mb_post_id'] ) );

			if ( is_wp_error( $post ) || empty( $post ) ) {
				wp_die( __( 'Post not found!', 'message-board' ) );
			}

			if ( mb_get_topic_post_type() === $post->post_type ) {

				if ( isset( $_POST['mb_post_title'] ) ) {

					$new_title = $_POST['mb_post_title'];

					if ( !empty( $new_title ) && $new_title !== $post->post_title ) {
						$post_title = esc_html( strip_tags( $new_title ) );
					}

				}

				if ( isset( $_POST['mb_post_forum'] ) ) {
					$new_forum = $_POST['mb_post_forum'];

					if ( !empty( $new_forum ) && $new_forum !== $post->post_parent ) {

						$post_forum = absint( $new_forum );
					}
				}
			} // end check for forum topic

			if ( empty( $_POST['mb_post_content'] ) ) {

				wp_die( __( 'You did not enter any content!', 'message-board' ) );
				exit;
			} else {

				/* Post content. */
				if ( !current_user_can( 'unfiltered_html' ) ) {
					$post_content = $_POST['mb_post_content'];
					$post_content = mb_encode_bad( $post_content );
					$post_content = mb_code_trick( $post_content );
					$post_content = force_balance_tags( $post_content );
					$post_content = wp_filter_post_kses( $post_content );
				} else {
					$post_content = $_POST['mb_post_content'];
				}
			}

			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				wp_die( 'Did not recognize user ID.', 'message-board' );
				exit;
			}

			$post_date = current_time( 'mysql' );

			/* Update. */
			$post_arr = array( 'ID' => $post->ID, 'post_content' => $post_content );

			if ( !empty( $post_title ) ) {
				$post_arr['post_title'] = $post_title;
			}

			if ( !empty( $post_forum ) ) {
				$post_arr['post_parent'] = absint( $post_forum );
			}

			$updated = wp_update_post( $post_arr );

			if ( $updated ) {

				wp_safe_redirect( get_permalink( $updated ) );
			}
		}
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
