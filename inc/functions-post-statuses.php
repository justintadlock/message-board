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


//do_action( "{$old_status}_to_{$new_status}", $post );
//do_action( 'transition_post_status', $new_status, $old_status, $post );
//do_action( "{$new_status}_{$post->post_type}", $post->ID, $post );

//add_action( 'publish_to_close', 'mb_publish_to_close' );
add_action( 'publish_to_spam',  'mb_publish_to_spam'  );
add_action( 'publish_to_trash', 'mb_publish_to_trash' );

//add_action( 'close_to_publish', 'mb_close_to_publish' );
add_action( 'close_to_spam',    'mb_close_to_spam'    );
add_action( 'close_to_trash',   'mb_close_to_trash'   );

add_action( 'spam_to_publish',  'mb_spam_to_publish'  );
add_action( 'spam_to_close',    'mb_spam_to_close'    );
//add_action( 'spam_to_trash',    'mb_spam_to_trash'    );

add_action( 'trash_to_publish', 'mb_trash_to_publish' );
add_action( 'trash_to_close',   'mb_trash_to_close'   );
//add_action( 'trash_to_spam',    'mb_trash_to_spam'    );

/* Actions? do nothing. */
function mb_publish_to_close( $post ) {}
/* actions? do nothing. */
function mb_close_to_publish( $post ) {}
/* actions? */
function mb_spam_to_trash( $post ) {}
/* actions? do nothing */
function mb_trash_to_spam( $post ) {}

/* Actions? 
 * topic:
 * - reset forum if topic latest
 * - change post status of replies to 'orphan'
 * reply:
 * - reset reply positions for topic
 * - reset topic if reply latest
 */
function mb_publish_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post );
}

function mb_publish_to_trash( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post );
}

/* actions?
 * - reset forum if topic latest
 * - change post status of replies to 'orphan'
 */
function mb_close_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );
}

/* actions?
 * - reset forum if topic latest
 * - change post status of replies to 'orphan'
 */
function mb_close_to_trash( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );
}

/* Actions? 
 * topic:
 * - reset forum if topic latest
 * - change post status of replies to 'publish'
 * reply:
 * - reset reply positions for topic
 * - reset topic if reply latest
 */
function mb_spam_to_publish( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}

function mb_spam_to_close( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );
}

/* Actions? 
 * topic:
 * - reset forum if topic latest
 * - change post status of replies to 'publish'
 * reply:
 * - reset reply positions for topic
 * - reset topic if reply latest
 */
function mb_trash_to_publish( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}

function mb_trash_to_close( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}












