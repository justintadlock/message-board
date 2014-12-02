<?php

add_action( 'template_redirect', 'mb_template_redirect', 0 );

function mb_template_redirect() {
	do_action( 'mb_template_redirect' );
}

add_action( 'mb_template_redirect', 'mb_handler_new_topic'       );
add_action( 'mb_template_redirect', 'mb_handler_new_reply'       );
add_action( 'mb_template_redirect', 'mb_handler_edit_post'       );
add_action( 'mb_template_redirect', 'mb_handler_topic_subscribe' );
add_action( 'mb_template_redirect', 'mb_handler_topic_bookmark'  );

add_action( 'mb_template_redirect', 'mb_handler_spam'       );
add_action( 'mb_template_redirect', 'mb_handler_open_close' );
add_action( 'mb_template_redirect', 'mb_handler_trash'      );

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
	$published = wp_insert_post(
		array(
			'menu_order'   => mysql2date( 'U', $post_date ), // Saved as datetime epoch.
			'post_date'    => $post_date,
			'post_author'  => absint( $user_id ),
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_status'  => 'open',
			'post_type'    => mb_get_topic_post_type(),
			'post_parent'  => $forum_id,
		)
	);

	/* If the post was published. */
	if ( $published ) {

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] ) {

			mb_add_user_subscription( absint( $user_id ), $published );
		}

		/* Update user meta. */
		$topic_count = mb_get_user_topic_count( $user_id );
		update_user_meta( absint( $user_id ), '_topic_count', $topic_count + 1 );

		/* Update forum meta. */

		update_post_meta( $forum_id, '_forum_activity_datetime', $post_date );
		update_post_meta( $forum_id, '_forum_activity_datetime_epoch', mysql2date( 'U', $post_date ) );
		update_post_meta( $forum_id, '_forum_last_topic_id', $published );

		$topic_count = get_post_meta( $forum_id, '_forum_topic_count', true );
		update_post_meta( $forum_id, '_forum_topic_count', absint( $topic_count ) + 1 );

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

	/* Post Date. */
	$post_date = current_time( 'mysql' );

	/* Reply position. */
	$position = absint( mb_get_topic_reply_count( $topic_id ) ) + 1;

	/* Publish a new forum topic. */
	$published = wp_insert_post(
		array(
			'post_date'    => $post_date,
			'post_author'  => absint( $user_id ),
			'post_content' => $post_content,
			'post_parent'  => $topic_id,
			'post_status'  => 'publish',
			'post_type'    => mb_get_reply_post_type(),
			'menu_order'   => $position
		)
	);

	/* If the post was published. */
	if ( $published ) {

		/* If the user chose to subscribe to the topic. */
		if ( isset( $_POST['mb_topic_subscribe'] ) && 1 == $_POST['mb_topic_subscribe'] ) {

			$added = mb_add_user_subscription( absint( $user_id ), $topic_id );

			if ( $added ) {
				/* Resets topic subscribers cache. */
				mb_set_topic_subscribers( $topic_id );
			}
		}

		/* Update user meta. */
		$topic_count = mb_get_user_topic_count( $user_id );
		update_user_meta( absint( $user_id ), '_topic_count', $topic_count + 1 );

		/* Update topic. */

		$old_topic               = get_post( $topic_id, 'ARRAY_A' );
		$old_topic['menu_order'] = mysql2date( 'U', $post_date );
		$old_topic['ID']         = $topic_id;

		wp_insert_post( $old_topic );

		/* Update forum meta. */

		$forum_id = mb_get_topic_forum_id( $topic_id );

		update_post_meta( $forum_id, '_forum_activity_datetime',       $post_date );
		update_post_meta( $forum_id, '_forum_activity_datetime_epoch', mysql2date( 'U', $post_date ) );
		update_post_meta( $forum_id, '_forum_last_reply_id',           $published );
		update_post_meta( $forum_id, '_forum_last_topic_id',           $topic_id );

		$reply_count = get_post_meta( $forum_id, '_forum_reply_count', true );
		update_post_meta( $forum_id, '_forum_reply_count', absint( $reply_count ) + 1 );

		/* Update topic meta. */

		update_post_meta( $topic_id, '_topic_activity_datetime',       $post_date );
		update_post_meta( $topic_id, '_topic_activity_datetime_epoch', mysql2date( 'U', $post_date ) );
		update_post_meta( $topic_id, '_topic_last_reply_id',           $published );

		$voices = mb_get_topic_voices( $topic_id );

		if ( empty( $voices ) || !in_array( $user_id, $voices ) ) {
			add_post_meta( $topic_id, '_topic_voices', $user_id );

			$count = count( $voices ) + 1;

			update_post_meta( $topic_id, '_topic_voice_count', $count );
		}

		$count = get_post_meta( $topic_id, '_topic_reply_count', true );

		update_post_meta( $topic_id, '_topic_reply_count', absint( $count ) + 1 );

		/* Notify topic subscribers. */
		mb_notify_topic_subscribers( $topic_id, $published );

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

		$bookmarks = get_user_meta( $user_id, '_topic_bookmarks', true );
		$favs = explode( ',', $bookmarks );


		if ( !in_array( $topic_id, $favs ) ) {
			$favs[] = $topic_id;

			$new_bookmarks   = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

			update_user_meta( $user_id, '_topic_bookmarks', $new_bookmarks );

			mb_set_topic_bookmarkers( $topic_id );
		}

	} elseif ( 0 < $user_id && 0 < $topic_id && 'unbookmark' === $_GET['action'] ) {

		// mb_get_user_bookmarks( $user_id );

		$bookmarks = get_user_meta( $user_id, '_topic_bookmarks', true );
		$favs = explode( ',', $bookmarks );

		if ( in_array( $topic_id, $favs ) ) {

			$_fav = array_search( $topic_id, $favs );

			unset( $favs[ $_fav ] );

			$new_bookmarks   = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

			update_user_meta( $user_id, '_topic_bookmarks', $new_bookmarks );

			mb_set_topic_bookmarkers( $topic_id );
		}
	}

		if ( isset( $_GET['redirect'] ) ) {

			wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
		}
}

// http://localhost/board/?action=spam&reply_id=2860&redirect=http://localhost/board/topics/sub-11/
function mb_handler_spam() {

	if ( !is_user_logged_in() )
		return;

	// @todo nonce?
	// @todo cap check

	if ( !isset( $_GET['action'] ) || 'spam' !== $_GET['action'] )
		return;

	if ( isset( $_GET['reply_id'] ) || isset( $_GET['topic_id'] ) ) {

		$post_id = isset( $_GET['reply_id'] ) ? absint( $_GET['reply_id'] ) : absint( $_GET['topic_id'] );

		$postarr = get_post( $post_id, ARRAY_A );

		if ( 'spam' !== $postarr['post_status'] ) {

			$postarr['post_status'] = 'spam';

			wp_update_post( $postarr );
		}
	}

	if ( isset( $_GET['redirect'] ) ) {
		wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
	}
}

function mb_handler_open_close() {

	if ( !is_user_logged_in() )
		return;

	// @todo nonce?
	// @todo cap check

	if ( !isset( $_GET['action'] ) || !in_array( $_GET['action'], array( 'open', 'close' ) ) )
		return;

	if ( isset( $_GET['forum_id'] ) || isset( $_GET['topic_id'] ) ) {

		$post_id = isset( $_GET['forum_id'] ) ? absint( $_GET['forum_id'] ) : absint( $_GET['topic_id'] );

		$postarr = get_post( $post_id, ARRAY_A );

		if ( 'close' === $_GET['action'] && 'close' !== $postarr['post_status'] ) {

			$postarr['post_status'] = 'close';

			wp_update_post( $postarr );
		}

		elseif ( 'open' === $_GET['action'] && 'close' === $postarr['post_status'] ) {

			$postarr['post_status'] = 'open';

			wp_update_post( $postarr );
		}
	}

	if ( isset( $_GET['redirect'] ) ) {
		wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
	}
}

function mb_handler_trash() {

	if ( !is_user_logged_in() )
		return;

	// @todo nonce?
	// @todo cap check

	if ( !isset( $_GET['action'] ) || !in_array( $_GET['action'], array( 'trash', 'untrash' ) ) )
		return;

	if ( isset( $_GET['reply_id'] ) || isset( $_GET['topic_id'] ) ) {

		$post_id = isset( $_GET['reply_id'] ) ? absint( $_GET['reply_id'] ) : absint( $_GET['topic_id'] );

		$postarr = get_post( $post_id, ARRAY_A );

		if ( 'trash' === $_GET['action'] && 'trash' !== $postarr['post_status'] ) {
			wp_trash_post( $post_id );
		}

		elseif ( 'untrash' === $_GET['action'] && 'trash' === $postarr['post_status'] ) {

			wp_untrash_post( $post_id );
		}
	}

	if ( isset( $_GET['redirect'] ) ) {
		wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
	}
}
