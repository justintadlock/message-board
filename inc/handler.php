<?php


add_action( 'template_redirect', 'mb_new_reply_handler', 0 );
add_action( 'template_redirect', 'mb_template_redirect', 0 );
add_action( 'template_redirect', 'mb_topic_subscribe_handler', 1 );
add_action( 'template_redirect', 'mb_topic_favorite_handler', 1 );




function mb_template_redirect() {

	if ( !isset( $_GET['message-board'] ) || !in_array( $_GET['message-board'], array( 'new-topic' ) ) )
		return;

	if ( isset( $_POST['mb_new_topic_nonce'] ) ) {
 
		if ( !wp_verify_nonce( $_POST['mb_new_topic_nonce'], 'mb_new_topic_action' ) ) {
			wp_die( __( 'Ooops! Something went wrong!', 'message-board' ) );
			exit;
		} else {

			if ( empty( $_POST['mb_topic_title'] ) ) {

				wp_die( __( 'You did not enter a topic title!', 'message-board' ) );
				exit;
			} else {

				$post_title = esc_html( strip_tags( $_POST['mb_topic_title'] ) );
			}


			if ( empty( $_POST['mb_topic_content'] ) ) {

				wp_die( __( 'You did not enter any content!', 'message-board' ) );
				exit;
			} else {

				/* Post content. */
				if ( !current_user_can( 'unfiltered_html' ) ) {
					$post_content = wp_filter_post_kses( $_POST['mb_topic_content'] );
				} else {
					$post_content = $_POST['mb_topic_content'];
				}
			}

			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				wp_die( 'Did not recognize user ID.', 'message-board' );
				exit;
			}

			$post_date = current_time( 'mysql' );

			/* Publish a new forum topic. */
			$published = wp_insert_post(
				array(
					'menu_order' => mysql2date( 'U', $post_date ),
					'post_date'   => $post_date,
					'post_author' => absint( $user_id ),
					'post_title' => $post_title,
					'post_content' => $post_content,
			//		'tax_input' => $tax_input,
					'post_status' => 'publish',
					'post_type' => 'forum_topic',
				)
			);

			if ( $published ) {
				wp_set_post_terms( $published, array( absint( $_POST['mb_topic_forum'] ) ), 'forum' );
				wp_safe_redirect( get_permalink( $published ) );
			}
		}
	}
}
function mb_new_reply_handler() {

	if ( !isset( $_GET['message-board'] ) || !in_array( $_GET['message-board'], array( 'new-reply' ) ) )
		return;

	if ( isset( $_POST['mb_new_reply_nonce'] ) ) {
 
		if ( !wp_verify_nonce( $_POST['mb_new_reply_nonce'], 'mb_new_reply_action' ) ) {
			wp_die( __( 'Ooops! Something went wrong!', 'message-board' ) );
			exit;
		} else {

			if ( empty( $_POST['mb_reply_topic_id'] ) ) {

				wp_die( __( 'Topic ID could not be found.', 'message-board' ) );
				exit;
			} else {
				$topic_id = absint( $_POST['mb_reply_topic_id'] );
			}

			if ( empty( $_POST['mb_reply_content'] ) ) {

				wp_die( __( 'You did not enter any content!', 'message-board' ) );
				exit;
			} else {

				/* Post content. */
				if ( !current_user_can( 'unfiltered_html' ) ) {
					$post_content = wp_filter_post_kses( $_POST['mb_reply_content'] );
				} else {
					$post_content = $_POST['mb_reply_content'];
				}
			}

			$user_id = get_current_user_id();

			if ( empty( $user_id ) ) {
				wp_die( 'Did not recognize user ID.', 'message-board' );
				exit;
			}

			$post_date = current_time( 'mysql' );

			/* Publish a new forum topic. */
			$published = wp_insert_post(
				array(
					'post_date'   => $post_date,
					'post_author' => absint( $user_id ),
					'post_content' => $post_content,
			//		'tax_input' => $tax_input,
					'post_parent' => $topic_id,
					'post_status' => 'publish',
					'post_type' => 'forum_reply',
				)
			);

			if ( $published ) {



		//$topic_id = get_post_field( 'parent', $published );
		$reply_date = get_post_field( 'post_date', $published );

		$old_topic = get_post( $topic_id, 'ARRAY_A' );
		$old_topic['menu_order'] = mysql2date( 'U', $reply_date );
		$old_topic['ID'] = $topic_id;

		wp_insert_post( $old_topic );

		update_post_meta( $topic_id, '_topic_activity_datetime',       $reply_date );
		update_post_meta( $topic_id, '_topic_activity_datetime_epoch', mysql2date( 'U', $reply_date ) );
		update_post_meta( $topic_id, '_topic_last_reply_id',           $published );

		$voices = get_post_meta( $topic_id, '_topic_voices' );

		if ( empty( $voices ) || !in_array( $user_id, $voices ) ) {
			add_post_meta( $topic_id, '_topic_voices', $user_id );
		}

		$voices = get_post_meta( $topic_id, '_topic_voices' );

		$count = count( $voices );

		update_post_meta( $topic_id, '_topic_voice_count', $count );

			$count = get_post_meta( $topic_id, '_topic_reply_count', true );

				$i = absint( $count ) + 1;

				update_post_meta( $topic_id, '_topic_reply_count', $i );

				wp_safe_redirect( get_permalink( $published ) );
			}
		}
	}
}


function mb_topic_subscribe_handler() {

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

		// mb_get_user_subscriptions( $user_id );

		$subscriptions = get_user_meta( $user_id, '_topic_subscriptions', true );
		$subs = explode( ',', $subscriptions );


		if ( !in_array( $topic_id, $subs ) ) {
			$subs[] = $topic_id;

			$new_subscriptions   = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );

			update_user_meta( $user_id, '_topic_subscriptions', $new_subscriptions );
		}

	} elseif ( 0 < $user_id && 0 < $topic_id && 'unsubscribe' === $_GET['action'] ) {

		// mb_get_user_subscriptions( $user_id );

		$subscriptions = get_user_meta( $user_id, '_topic_subscriptions', true );
		$subs = explode( ',', $subscriptions );

		if ( in_array( $topic_id, $subs ) ) {

			$_sub = array_search( $topic_id, $subs );

			unset( $subs[ $_sub ] );

			$new_subscriptions   = implode( ',', wp_parse_id_list( array_filter( $subs ) ) );

			update_user_meta( $user_id, '_topic_subscriptions', $new_subscriptions );
		}
	}

		if ( isset( $_GET['redirect'] ) ) {

			wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
		}
}


function mb_topic_favorite_handler() {

	if ( !is_user_logged_in() )
		return;

	// @todo nonce?

	if ( !isset( $_GET['action'] ) || !in_array( $_GET['action'], array( 'favorite', 'unfavorite' ) ) )
		return;

	if ( !isset( $_GET['topic_id'] ) )
		return;

	$topic_id = absint( $_GET['topic_id'] );

	$user_id = get_current_user_id();

	if ( 0 < $user_id && 0 < $topic_id && 'favorite' === $_GET['action'] ) {

		// mb_get_user_favorites( $user_id );

		$favorites = get_user_meta( $user_id, '_topic_favorites', true );
		$favs = explode( ',', $favorites );


		if ( !in_array( $topic_id, $favs ) ) {
			$favs[] = $topic_id;

			$new_favorites   = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

			update_user_meta( $user_id, '_topic_favorites', $new_favorites );
		}

	} elseif ( 0 < $user_id && 0 < $topic_id && 'unfavorite' === $_GET['action'] ) {

		// mb_get_user_favorites( $user_id );

		$favorites = get_user_meta( $user_id, '_topic_favorites', true );
		$favs = explode( ',', $favorites );

		if ( in_array( $topic_id, $favs ) ) {

			$_fav = array_search( $topic_id, $favs );

			unset( $favs[ $_fav ] );

			$new_favorites   = implode( ',', wp_parse_id_list( array_filter( $favs ) ) );

			update_user_meta( $user_id, '_topic_favorites', $new_favorites );
		}
	}

		if ( isset( $_GET['redirect'] ) ) {

			wp_safe_redirect( esc_url( strip_tags( $_GET['redirect'] ) ) );
		}
}