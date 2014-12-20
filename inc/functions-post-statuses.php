<?php
/**
 * Sets up custom post status functions.  Registers post statuses with WordPress.  Handles callbacks for 
 * when a post status changes.
 *
 * In many ways, this is the heart and soul behind the plugin.  If nothing else, it's probably the most 
 * important bit.  It's easy to create new posts with WordPress.  What to do when a single post's status 
 * changes is what gets interesting.  Throw the three distinct post types that it takes to build a forum 
 * into the mix, you either have something that's going to fall apart miserably or something that's truly 
 * a work of art.  Let's shoot for the latter.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register post statuses. */
add_action( 'init', 'mb_register_post_statuses' );

/* Transition post status. */
add_action( 'transition_post_status', 'mb_transition_post_status', 10, 3 );

/* Permanently-deleted post. */
add_action( 'before_delete_post', 'mb_before_delete_post' );

/**
 * Returns the slug for the "publish" post status.  Used by replies by default.  Note that this status 
 * is not registered because it's a default WordPress post status.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_publish_post_status() {
	return apply_filters( 'mb_get_publish_post_status', 'publish' );
}

/**
 * Returns the slug for the "trash" post status.  Used by forums, topics, and replies by default.  Note 
 * that this status is not registered because it's a default WordPress post status.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_trash_post_status() {
	return apply_filters( 'mb_get_trash_post_status', 'trash' );
}

/**
 * Returns the slug for the "private" post status.  Used by forums, and topics by default.  Note 
 * that this status is not registered because it's a default WordPress post status.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_private_post_status() {
	return apply_filters( 'mb_get_private_post_status', 'private' );
}

/**
 * Returns the slug for the "open" post status.  Used by forums and topics by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_open_post_status() {
	return apply_filters( 'mb_get_open_post_status', 'open' );
}

/**
 * Returns the slug for the "close" post status.  Used by forums and topics by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_close_post_status() {
	return apply_filters( 'mb_get_close_post_status', 'close' );
}

/**
 * Returns the slug for the "archive" post status.  Used by forums by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_archive_post_status() {
	return apply_filters( 'mb_get_archive_post_status', 'archive' );
}

/**
 * Returns the slug for the "hidden" post status.  Used by forums by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_hidden_post_status() {
	return apply_filters( 'mb_get_hidden_post_status', 'hidden' );
}

/**
 * Returns the slug for the "spam" post status.  Used by topics and replies by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_spam_post_status() {
	return apply_filters( 'mb_get_spam_post_status', 'spam' );
}

/**
 * Returns the slug for the "orphan" post status.  Used by topics and replies by default.
 *
 * @note Not currently in use.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_orphan_post_status() {
	return apply_filters( 'mb_get_orphan_post_status', 'orphan' );
}

/**
 * Returns an array of allowed post statuses for forums.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_forum_post_statuses() {
	$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_hidden_post_status(), mb_get_private_post_status(), mb_get_trash_post_status(), mb_get_archive_post_status() );
	return apply_filters( 'mb_get_forum_post_statuses', $statuses );
}

/**
 * Returns an array of allowed post statuses for topics.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_topic_post_statuses() {
	$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_orphan_post_status(), mb_get_spam_post_status(), mb_get_trash_post_status() );
	return apply_filters( 'mb_get_topic_post_statuses', $statuses );
}

/**
 * Returns an array of allowed post statuses for replies.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_reply_post_statuses() {
	$statuses = array( mb_get_publish_post_status(), mb_get_orphan_post_status(), mb_get_spam_post_status(), mb_get_trash_post_status() );
	return apply_filters( 'mb_get_topic_post_statuses', $statuses );
}

/**
 * Registers post statuses used by the plugin that WordPress doesn't offer out of the box.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_statuses() {

	/* Open status args. */
	$open_args = array(
		'label'                     => __( 'Open', 'message-board' ),
		'label_verb'                => __( 'Open', 'message-board' ), // custom
		'label_count'               => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => true,
		'private'                   => false,
		'protected'                 => false,
		'publicly_queryable'        => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
	);

	/* Close status args. */
	$close_args = array(
		'label'                     => __( 'Closed', 'message-board' ),
		'label_verb'                => __( 'Close',  'message-board' ), // custom
		'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => true,
		'private'                   => false,
		'protected'                 => false,
		'publicly_queryable'        => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
	);

	/* Archive status args. */
	$archive_args = array(
		'label'                     => __( 'Archived', 'message-board' ),
		'label_verb'                => __( 'Archive',  'message-board' ), // custom
		'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => true,
		'private'                   => false,
		'protected'                 => false,
		'publicly_queryable'        => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
	);

	/* Spam status args. */
	$spam_args = array(
		'label'                     => __( 'Spam', 'message-board' ),
		'label_verb'                => __( 'Spam', 'message-board' ), // custom
		'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => false,
		'private'                   => false,
		'protected'                 => false,
		'publicly_queryable'        => false,
		'exclude_from_search'       => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => false,
	);

	/* Orphan status args. */
	$orphan_args = array(
		'label'                     => __( 'Orphan', 'message-board' ),
		'label_verb'                => __( 'Orphan', 'message-board' ), // custom
		'label_count'               => _n_noop( 'Orphan <span class="count">(%s)</span>', 'Orphan <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => false,
		'private'                   => false,
		'protected'                 => true,
		'publicly_queryable'        => false,
		'exclude_from_search'       => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => false,
	);

	/* Hidden status args. */
	$hidden_args = array(
		'label'                     => __( 'Hidden', 'message-board' ),
		'label_verb'                => __( 'Hide',   'message-board' ), // custom
		'label_count'               => _n_noop( 'Orphan <span class="count">(%s)</span>', 'Orphan <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => false,
		'private'                   => false,
		'protected'                 => true,
		'publicly_queryable'        => true,
		'exclude_from_search'       => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => false,
	);

	/* Register post statuses. */
	register_post_status( mb_get_open_post_status(),    apply_filters( 'mb_open_post_status_args',    $open_args    ) );
	register_post_status( mb_get_close_post_status(),   apply_filters( 'mb_close_post_status_args',   $close_args   ) );
	register_post_status( mb_get_archive_post_status(), apply_filters( 'mb_archive_post_status_args', $archive_args ) );
	register_post_status( mb_get_hidden_post_status(),  apply_filters( 'mb_hidden_post_status_args',  $hidden_args  ) );
	register_post_status( mb_get_spam_post_status(),    apply_filters( 'mb_spam_post_status_args',    $spam_args    ) );
	register_post_status( mb_get_orphan_post_status(),  apply_filters( 'mb_orphan_post_status_args',  $orphan_args  ) );
}

/**
 * Callback function for the `transition_post_status` hook.  This function saves the previous post status 
 * as metadata.  It also adds actions for more specific status changes.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $new_status
 * @param  string  $old_status
 * @param  object  $post
 * @return void
 */
function mb_transition_post_status( $new_status, $old_status, $post ) {

	/* Get post types. */
	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	/* If not one of our post types, bail. */
	if ( !in_array( $post->post_type, array( $forum_type, $topic_type, $reply_type ) ) )
		return;

	/* Keep track of the old post status by saving it as post meta. */
	update_post_meta( $post->ID, mb_get_prev_status_meta_key(), $old_status );

	/* Get post type statuses. */
	$forum_statuses = mb_get_forum_post_statuses();
	$topic_statuses = mb_get_topic_post_statuses();
	$reply_statuses = mb_get_reply_post_statuses();

	/* Get the post statuses we need to work with. */
	$publish_status = mb_get_publish_post_status();
	$open_status    = mb_get_open_post_status();
	$close_status   = mb_get_close_post_status();
	$spam_status    = mb_get_spam_post_status();
	$trash_status   = mb_get_trash_post_status();

	/* If old status is not one of our statuses but the new is, assume we're publishing for the first time. */
	if ( $forum_type === $post->post_type && !in_array( $old_status, $forum_statuses ) && in_array( $new_status, $forum_statuses ) )
		mb_insert_forum_data( $post );

	elseif ( $topic_type === $post->post_type && !in_array( $old_status, $topic_statuses ) && in_array( $new_status, $topic_statuses ) )
		mb_insert_topic_data( $post );

	elseif ( $reply_type === $post->post_type && !in_array( $old_status, $reply_statuses ) && in_array( $new_status, $reply_statuses ) )
		mb_insert_reply_data( $post );

	/* Publish status change. */
	add_action( "{$publish_status}_to_{$spam_status}",  'mb_publish_to_spam'  );
	add_action( "{$publish_status}_to_{$trash_status}", 'mb_publish_to_trash' );

	/* Open status change. */
	add_action( "{$open_status}_to_{$spam_status}",     'mb_publish_to_spam'  );
	add_action( "{$open_status}_to_{$trash_status}",    'mb_publish_to_trash' );

	/* Close status change. */
	add_action( "{$close_status}_to_{$spam_status}",    'mb_close_to_spam'    );
	add_action( "{$close_status}_to_{$trash_status}",   'mb_close_to_trash'   );

	/* Spam status change. */
	add_action( "{$spam_status}_to_{$publish_status}",  'mb_spam_to_publish'  );
	add_action( "{$spam_status}_to_{$open_status}",     'mb_spam_to_pubish'   );
	add_action( "{$spam_status}_to_{$close_status}",    'mb_spam_to_close'    );

	/* Trash status change. */
	add_action( "{$trash_status}_to_{$publish_status}", 'mb_trash_to_publish' );
	add_action( "{$trash_status}_to_{$open_status}",    'mb_trash_to_publish' );
	add_action( "{$trash_status}_to_{$close_status}",   'mb_trash_to_close'   );
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

/**
 * Changes a forum's post status to "open" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int|WP_Error
 */
function mb_open_forum( $forum_id ) {
	return mb_update_post_status( $forum_id, mb_get_open_post_status() );
}

/**
 * Changes a forum's post status to "close" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int|WP_Error
 */
function mb_close_forum( $forum_id ) {
	return mb_update_post_status( $forum_id, mb_get_close_post_status() );
}

/**
 * Changes a forum's post status to "archive" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int|WP_Error
 */
function mb_archive_forum( $forum_id ) {
	return mb_update_post_status( $forum_id, mb_get_archive_post_status() );
}

/**
 * Changes a topic's post status to "open" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_open_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_open_post_status() );
}

/**
 * Changes a topic's post status to "close" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_close_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_close_post_status() );
}

/**
 * Changes a topic's post status to "spam" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_spam_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_spam_post_status() );
}

/**
 * Changes a topic's status from "spam" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_unspam_topic( $topic_id ) {
	return mb_restore_post_status( $topic_id );
}

/**
 * Changes a topic's post status to "orphan" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_orphan_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_orphan_post_status() );
}

/**
 * Changes a topic's status from "orphan" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_unorphan_topic( $topic_id ) {
	return mb_restore_post_status( $topic_id );
}

/**
 * Changes a reply's post status to "spam" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_spam_reply( $reply_id ) {
	return mb_update_post_status( $reply_id, mb_get_spam_post_status() );
}

/**
 * Changes a reply's status from "spam" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_unspam_reply( $reply_id ) {
	return mb_restore_post_status( $reply_id );
}

/**
 * Changes a reply's post status to "orphan" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_orphan_reply( $reply_id ) {
	return mb_update_post_status( $reply_id, mb_get_orphan_post_status() );
}

/**
 * Changes a reply's status from "orphan" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_unorphan_reply( $reply_id ) {
	return mb_restore_post_status( $reply_id );
}

/**
 * Gets a post's previous post status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return string
 */
function mb_get_prev_post_status( $post_id ) {
	$status = get_post_meta( $post_id, mb_get_prev_status_meta_key(), true );

	if ( empty( $status ) ) {
		$status = mb_get_publish_post_status();

		if ( in_array( get_post_type( $post_id ), array( mb_get_forum_post_type(), mb_get_topic_post_type() ) ) )
			$status = mb_get_open_post_status();
	}

	return $status;
}

/**
 * Helper function for quicky restoring a post's previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return int|WP_Error
 */
function mb_restore_post_status( $post_id ) {
	return mb_update_post_status( $post_id, mb_get_prev_post_status( $post_id ) );
}

/**
 * Helper function for quickly updating a post's status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  string  $status
 * @return int|WP_Error
 */
function mb_update_post_status( $post_id, $status ) {
	return wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ) );
}

/**
 * Callback function on the `before_delete_post` hook for when a post is deleted. This sets up some 
 * specific actions based on our post types. It also saves the deleted post object for later use in 
 * those actions.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return void
 */
function mb_before_delete_post( $post_id ) {

	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();
	$post_type  = get_post_type( $post_id );

	/* WP doesn't pass the post object after a post has been deleted, so we need to save it temporarily. */
	if ( in_array( $post_type, array( $forum_type, $topic_type, $reply_type ) ) )
		message_board()->deleted_post = get_post( $post_id );

	/* If a forum is being deleted. */
	if ( $forum_type === $post_type ) {

		/* If this is the default forum, stop everything. */
		if ( mb_get_default_forum_id() === $post_id )
			wp_die( 'Whoah there! This is the default forum and cannot be deleted. Visit the settings page to change the default forum.', 'message-board' );

		add_action( 'after_delete_post', 'mb_after_delete_forum' );

	/* If a topic is being deleted. */
	} elseif ( $topic_type === $post_type ) {
		add_action( 'after_delete_post', 'mb_after_delete_topic' );

	/* If a reply is being deleted. */
	} elseif ( $reply_type === $post_type ) {
		add_action( 'after_delete_post', 'mb_after_delete_reply' );
	}
}

/**
 * Callback function on the `after_delete_post` hook for when a forum is deleted.
 *
 * @todo All forum topics need to become orphans at this point. Attempt to move topics into parent if avail.
 * @todo Reset counts for parent forums.
 * @todo `wp_die()` if this is the default forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return void
 */
function mb_after_delete_forum( $post_id ) {
	global $wpdb;

	$mb = message_board();

	if ( is_object( $mb->deleted_post ) && $mb->deleted_post->ID === $post_id ) {

		$forum_id = mb_get_forum_id( $post_id );
		$user_id  = mb_get_user_id( $post->deleted_post->post_author );

		$parent_forum_id = $mb->deleted_post->post_parent;

		/* Get the current forum's topic IDs. */
		$topic_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %s ORDER BY menu_order DESC", mb_get_topic_post_type(), absint( $forum_id ) ) );

		if ( !empty( $topic_ids ) ) {

			$moved_topics = false;

			while ( 0 < $parent_forum_id ) {

				$forum_type = mb_get_forum_type( $parent_forum_id );

				if ( mb_forum_type_allows_topics( $forum_type ) ) {

					/* Change all of the topics' parents to the new forum ID. */
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_parent = %d WHERE ID IN (" . implode( ',', $topic_ids ) . ")", absint( $parent_forum_id ) ) );

					/* Reset data based on new forum. */
					mb_reset_forum_data( $parent_forum_id );

					$parent_forum_id = 0;
					$moved_topics    = true;

					/* Break out of the while loop at this point. */
					break;
				} else {
					$post = get_post( $parent_forum_id );
					$parent_forum_id = $post->post_parent;
				}
			}

			/* If topics didn't get moved to a new forum, set their status to "orphan". */
			if ( false === $moved_topics ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID IN (" . implode( ',', $topic_ids ) . ")", mb_get_orphan_post_status() ) );
			}
		}

		/* Reset user forum count. */
		mb_set_user_forum_count( $user_id );
	}
}

/**
 * Callback function on the `after_delete_post` hook for when a topic is deleted.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return void
 */
function mb_after_delete_topic( $post_id ) {
	$mb = message_board();

	if ( is_object( $mb->deleted_post ) && $mb->deleted_post->ID === $post_id ) {

		/* Reset data based on topic. */
		mb_reset_topic_data( $mb->deleted_post );

		/* Make all of the topic's replies orphans. */
		mb_orphanize_replies( $post_id );

		/* Remove the topic from sticky arrays. */
		mb_remove_super_topic(  $post_id );
		mb_remove_sticky_topic( $post_id );

		/* Reset the deleted post object. */
		$mb->deleted_post = null;
	}
}

/**
 * Callback function on the `after_delete_post` hook for when a reply is deleted.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return void
 */
function mb_after_delete_reply( $post_id ) {
	$mb = message_board();

	if ( is_object( $mb->deleted_post ) && $mb->deleted_post->ID === $post_id ) {
		mb_reset_reply_data( $mb->deleted_post );
		$mb->deleted_post = null;
	}
}
