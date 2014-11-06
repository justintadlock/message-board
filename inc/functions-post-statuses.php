<?php

/* Register post statuses. */
add_action( 'init', 'mb_register_post_statuses' );

/**
 * Registers post statuses used by the plugin that WordPress doesn't offer out of the box.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_statuses() {

	register_post_status(
		'close',
		array(
			'label'                     => __( 'Closed', 'message-board' ),
			'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
		)
	);

	register_post_status(
		'spam',
		array(
			'label'                     => __( 'Spam', 'message-board' ),
			'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => false,
		)
	);

	register_post_status(
		'orphan',
		array(
			'label'                     => __( 'Orphan', 'message-board' ),
			'label_count'               => _n_noop( 'Orphan <span class="count">(%s)</span>', 'Orphan <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => false,
		)
	);
}


//do_action( "{$old_status}_to_{$new_status}", $post );
//do_action( 'transition_post_status', $new_status, $old_status, $post );
//do_action( "{$new_status}_{$post->post_type}", $post->ID, $post );

add_action( 'publish_to_close', 'mb_publish_to_close' );
add_action( 'publish_to_spam',  'mb_publish_to_spam'  );
add_action( 'publish_to_trash', 'mb_publish_to_trash' );

add_action( 'close_to_publish', 'mb_close_to_publish' );
add_action( 'close_to_spam',    'mb_close_to_spam'    );
add_action( 'close_to_trash',   'mb_close_to_trash'   );

add_action( 'spam_to_publish',  'mb_spam_to_publish'  );
add_action( 'spam_to_close',    'mb_spam_to_close'    );
add_action( 'spam_to_trash',    'mb_spam_to_trash'    );

add_action( 'trash_to_publish', 'mb_trash_to_publish' );
add_action( 'trash_to_close',   'mb_trash_to_close'   );
add_action( 'trash_to_spam',    'mb_trash_to_spam'    );

/* Actions? do nothing. */
function mb_publish_to_close( $post ) {}

/* Actions? 
 * topic:
 * - reset forum if topic latest
 * - change post status of replies to 'orphan'
 * reply:
 * - reset reply positions for topic
 * - reset topic if reply latest
 */
function mb_publish_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type ) {

		$forum_id      = mb_get_topic_forum_id( $post->ID );
		$last_topic_id = mb_get_forum_last_topic_id( $forum_id );

		/* If this is the last topic, reset all forum data. */
		if ( $post->ID === absint( $last_topic_id ) ) {

			mb_reset_forum_latest( $forum_id );
		}

		/* Reset counts. */
		mb_set_forum_topic_count( $forum_id );
		mb_set_forum_reply_count( $forum_id );

	} elseif ( mb_get_reply_post_type() === $post->post_type ) {

		$topic_id         = $post->post_parent;
		$forum_id         = mb_get_topic_forum_id( $topic_id );

		$topic_last_reply = mb_get_topic_last_reply_id( $topic_id );
		$forum_last_reply = mb_get_forum_last_reply_id( $forum_id );

		/* If this is the last topic reply, reset topic latest data. */
		if ( $post->ID === absint( $topic_last_reply ) ) {

			mb_reset_topic_latest( $forum_id );
		}

		$topic_reply_count = mb_topic_reply_count( $topic_id );
		update_post_meta( $topic_id, '_topic_reply_count', absint( $topic_reply_count ) - 1 );

		/* Reset topic voices. */
		mb_set_topic_voices( $topic_id );

		/* If this is the last reply, reset all forum data. */
		if ( $post->ID === absint( $forum_last_reply ) ) {

			mb_reset_forum_latest( $forum_id );
		}

		/* Reset reply count. */
		mb_set_forum_reply_count( $forum_id );
	}
}

function mb_publish_to_trash( $post ) {}

/* actions? do nothing. */
function mb_close_to_publish( $post ) {}

/* actions?
 * - reset forum if topic latest
 * - change post status of replies to 'orphan'
 */
function mb_close_to_spam( $post ) {}

/* actions?
 * - reset forum if topic latest
 * - change post status of replies to 'orphan'
 */
function mb_close_to_trash( $post ) {}

/* Actions? 
 * topic:
 * - reset forum if topic latest
 * - change post status of replies to 'publish'
 * reply:
 * - reset reply positions for topic
 * - reset topic if reply latest
 */
function mb_spam_to_publish( $post ) {}
function mb_spam_to_close( $post ) {}

/* actions?
 * topic:
 * - do nothing
 * reply:
 * - do nothing.
 */
function mb_spam_to_trash( $post ) {}

/* Actions? 
 * topic:
 * - reset forum if topic latest
 * - change post status of replies to 'publish'
 * reply:
 * - reset reply positions for topic
 * - reset topic if reply latest
 */
function mb_trash_to_publish( $post ) {}
function mb_trash_to_close( $post ) {}

/* actions? do nothing */
function mb_trash_to_spam( $post ) {}












