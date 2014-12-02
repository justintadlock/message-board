<?php

/* Register post statuses. */
add_action( 'init', 'mb_register_post_statuses' );

/* Published status change. */
add_action( 'publish_to_spam',  'mb_publish_to_spam'  );
add_action( 'publish_to_trash', 'mb_publish_to_trash' );
add_action( 'open_to_spam',     'mb_publish_to_spam'  );
add_action( 'open_to_trash',    'mb_publish_to_trash' );

/* Closed status change. */
add_action( 'close_to_spam',    'mb_close_to_spam'    );
add_action( 'close_to_trash',   'mb_close_to_trash'   );

/* Spam status change. */
add_action( 'spam_to_publish',  'mb_spam_to_publish'  );
add_action( 'spam_to_open',     'mb_spam_to_pubish'   );
add_action( 'spam_to_close',    'mb_spam_to_close'    );

/* Trash status change. */
add_action( 'trash_to_publish', 'mb_trash_to_publish' );
add_action( 'trash_to_open',    'mb_trash_to_publish' );
add_action( 'trash_to_close',   'mb_trash_to_close'   );

/**
 * Returns an array of allowed post statuses for forums.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_forum_post_statuses() {
	return apply_filters( 'mb_get_forum_post_statuses', array( 'open', 'close', 'trash' ) );
}

/**
 * Returns an array of allowed post statuses for topics.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_topic_post_statuses() {
	return apply_filters( 'mb_get_topic_post_statuses', array( 'open', 'close', 'spam', 'trash' ) );
}

/**
 * Returns an array of allowed post statuses for replies.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_reply_post_statuses() {
	return apply_filters( 'mb_get_reply_post_statuses', array( 'publish', 'spam', 'trash' ) );
}

/**
 * Registers post statuses used by the plugin that WordPress doesn't offer out of the box.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_statuses() {

	/* forums, topics */
	register_post_status(
		'open',
		array(
			'label'                     => __( 'Open', 'message-board' ),
			'label_count'               => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
		)
	);

	/* forums, topics */
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

	/* topics, replies */
	register_post_status(
		'spam',
		array(
			'label'                     => __( 'Spam', 'message-board' ),
			'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => current_user_can( 'manage_forums' ) && !is_admin() ? true : false,
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

/**
 * Resets topic/reply data when the post status is changed from 'publish' to 'spam'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_publish_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post );
}

/**
 * Resets topic/reply data when the post status is changed from 'publish' to 'trash'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_publish_to_trash( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post );
}

/**
 * Resets topic data when the post status is changed from 'close' to 'spam'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_close_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );
}

/**
 * Resets topic data when the post status is changed from 'close' to 'trash'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_close_to_trash( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );
}

/**
 * Resets topic/reply data when the post status is changed from 'spam' to 'publish'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_spam_to_publish( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}

/**
 * Resets topic data when the post status is changed from 'spam' to 'close'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_spam_to_close( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );
}

/**
 * Resets topic/reply data when the post status is changed from 'trash' to 'publish'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_trash_to_publish( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}

/**
 * Resets topic data when the post status is changed from 'trash' to 'close'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_trash_to_close( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );
}
