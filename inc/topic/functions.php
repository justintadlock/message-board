<?php
/**
 * Plugin functions and filters for the topic post type.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Filter to make sure we get a topic post parent. */
add_filter( 'wp_insert_post_parent', 'mb_insert_topic_post_parent', 10, 3 );

/* Update topic data on the `post_updated` hook. */
add_action( 'post_updated', 'mb_topic_post_updated', 10, 3 );

/* Topic form fields. */
add_action( 'mb_topic_form_fields', 'mb_topic_form_fields' );

/* Private/hidden links. */
add_filter( 'post_type_link', 'mb_topic_post_type_link', 10, 2 );

/**
 * Inserts a new topic.  This is a wrapper for the `wp_insert_post()` function and should be used in 
 * its place where possible.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return int|WP_Error
 */
function mb_insert_topic( $args = array() ) {

	/* Convert date. */
	$post_date  = current_time( 'mysql' );
	$post_epoch = mysql2date( 'U', $post_date );

	/* Set up the defaults. */
	$defaults = array(
		'menu_order'   => $post_epoch,
		'post_date'    => $post_date,
		'post_author'  => get_current_user_id(),
		'post_status'  => mb_get_open_post_status(),
		'post_parent'  => mb_get_default_forum_id(),
	);

	/* Allow devs to filter the defaults. */
	$defaults = apply_filters( 'mb_insert_topic_defaults', $defaults );

	/* Parse the args/defaults and apply filters. */
	$args = apply_filters( 'mb_insert_topic_args', wp_parse_args( $args, $defaults ) );

	/* Always make sure it's the correct post type. */
	$args['post_type'] = mb_get_topic_post_type();

	/* Insert the topic. */
	return wp_insert_post( $args );
}

/**
 * Function for inserting topic data when it's first published.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_insert_topic_data( $post ) {

	/* Hook for before inserting topic data. */
	do_action( 'mb_before_insert_topic_data', $post );

	/* Get the topic ID. */
	$topic_id = mb_get_topic_id( $post->ID );

	/* Get the forum ID. */
	$forum_id = mb_get_forum_id( $post->post_parent );

	/* Get the User ID. */
	$user_id = mb_get_user_id( $post->post_author );

	/* Get the post date. */
	$post_date  = $post->post_date;
	$post_epoch = mysql2date( 'U', $post_date );

	/* Update user meta. */
	$topic_count = mb_get_user_topic_count( $user_id );
	update_user_meta( $user_id, mb_get_user_topic_count_meta_key(), $topic_count + 1 );

	/* Add topic meta. */
	mb_set_topic_activity_datetime( $topic_id, $post_date  );
	mb_set_topic_activity_epoch(    $topic_id, $post_epoch );
	mb_set_topic_voices(            $topic_id, $user_id    );
	mb_set_topic_voice_count(       $topic_id, 1           );
	mb_set_topic_reply_count(       $topic_id, 0           );

	/* If we have a forum ID. */
	if ( 0 < $forum_id ) {

		$topic_count = mb_get_forum_topic_count( $forum_id );

		/* Update forum meta. */
		mb_set_forum_activity_datetime( $forum_id, $post_date                 );
		mb_set_forum_activity_epoch(    $forum_id, $post_epoch                );
		mb_set_forum_last_topic_id(     $forum_id, $topic_id                  );
		mb_set_forum_topic_count(       $forum_id, absint( $topic_count ) + 1 );
	}

	/* Notify subscribers that there's a new topic. */
	mb_notify_subscribers( $post );

	/* Hook for after inserting topic data. */
	do_action( 'mb_after_insert_topic_data', $post );
}

/**
 * Attempt to always make sure that topics have a post parent.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_parent
 * @param  int     $post_id
 * @param  array   $new_postarr
 * @return int
 */
function mb_insert_topic_post_parent( $post_parent, $post_id, $new_postarr ) {

	if ( mb_get_topic_post_type() === $new_postarr['post_type'] && 0 >= $post_parent )
		$post_parent = mb_get_default_forum_id();

	return $post_parent;
}

/**
 * Resets the topic's latest data.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_reset_topic_latest( $topic_id ) {
	global $wpdb;

	$topic_id = mb_get_topic_id( $topic_id );

	$publish_status = mb_get_publish_post_status();

	$status_where = "AND post_status = '{$publish_status}'";

	$reply_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s {$status_where} AND post_parent = %d ORDER BY post_date DESC", mb_get_reply_post_type(), $topic_id ) );

	if ( !empty( $reply_id ) ) {

		$post_date  = get_post_field( 'post_date', $reply_id );
		$post_epoch = mysql2date( 'U', $post_date );

		mb_set_topic_activity_datetime( $topic_id, $post_date  );
		mb_set_topic_activity_epoch(    $topic_id, $post_epoch );
		mb_set_topic_last_reply_id(     $topic_id, $reply_id   );

	} else {
		$post_date  = get_post_field( 'post_date', $topic_id );
		$post_epoch = mysql2date( 'U', $post_date );

		mb_set_topic_activity_datetime( $topic_id, $post_date  );
		mb_set_topic_activity_epoch(    $topic_id, $post_epoch );

		delete_post_meta( $topic_id, mb_get_topic_last_reply_id_meta_key() );
	}

	mb_set_topic_position( $topic_id, $post_epoch );
}

/**
 * Resets the topic reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @global object $wpdb
 * @return array
 */
function mb_reset_topic_reply_count( $topic_id ) {
	global $wpdb;

	$topic_id = mb_get_topic_id( $topic_id );

	$publish_status = mb_get_publish_post_status();

	$where = $wpdb->prepare( "WHERE post_parent = %d AND post_type = %s", $topic_id, mb_get_reply_post_type() );

	$status_where = "AND post_status = '{$publish_status}'";

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where $status_where" );

	mb_set_topic_reply_count( $topic_id, $count );
}

/**
 * Resets the topic voices.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return array
 */
function mb_reset_topic_voices( $topic_id ) {
	global $wpdb;

	$voices = $wpdb->get_col( $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s AND post_status = %s", absint( $topic_id ), mb_get_reply_post_type(), mb_get_publish_post_status() ) );

	$topic_author = mb_get_topic_author_id( $topic_id );

	$voices = array_merge( array( $topic_author ), (array)$voices );
	$voices = array_unique( $voices );

	mb_set_topic_voices(      $topic_id, $voices          );
	mb_set_topic_voice_count( $topic_id, count( $voices ) );

	return $voices;
}

/**
 * Resets topic data.
 *
 * @since  1.0.0
 * @access public
 * @param  object|int  $post
 * @return array
 */
function mb_reset_topic_data( $post, $reset_latest = false ) {

	$post = is_object( $post ) ? $post : get_post( $post );

	$forum_id         = mb_get_topic_forum_id( $post->ID );
	$forum_last_topic = mb_get_forum_last_topic_id( $forum_id );

	/* Reset forum topic count. */
	mb_reset_forum_topic_count( $forum_id );

	/* Reset forum reply count. */
	mb_reset_forum_reply_count( $forum_id );

	/* If this is the last topic, reset forum latest data. */
	if ( $post->ID === absint( $forum_last_topic ) || true === $reset_latest )
		mb_reset_forum_latest( $forum_id );

	/* Reset user topic count. */
	mb_set_user_topic_count( $post->post_author );
}

function mb_topic_post_updated( $post_id, $post_after, $post_before ) {

	/* Bail if this is not the topic post type. */
	if ( mb_get_topic_post_type() !== $post_after->post_type )
		return;

	/* If the topic parent (forum) has changed. */
	if ( $post_after->post_parent !== $post_before->post_parent ) {

		/* Reset forum topic count. */
		mb_reset_forum_topic_count( $post_after->post_parent );
		mb_reset_forum_topic_count( $post_before->post_parent );

		/* Reset forum reply count. */
		mb_reset_forum_reply_count( $post_after->post_parent );
		mb_reset_forum_reply_count( $post_before->post_parent );

		/* Reset forum latest data. */
		mb_reset_forum_latest( $post_after->post_parent );
		mb_reset_forum_latest( $post_before->post_parent );
	}
}

/**
 * Sets the `menu_order` for a topic.  This is how we save the topic position.  The position should 
 * either be based on the datetime epoch of the topic's last reply if there is one or of the topic 
 * itself if there are no replies.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  int     $position
 * @return bool
 */
function mb_set_topic_position( $topic_id, $position ) {
	return wp_update_post( array( 'ID' => $topic_id, 'menu_order' => $position ) );
}

/**
 * Sets the topic last activity datetime.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  string  $datetime
 * @return bool
 */
function mb_set_topic_activity_datetime( $topic_id, $datetime ) {
	return update_post_meta( $topic_id, mb_get_topic_activity_datetime_meta_key(), $datetime );
}

/**
 * Sets the topic last activity datetime epoch.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  int     $epoch
 * @return bool
 */
function mb_set_topic_activity_epoch( $topic_id, $epoch ) {
	return 	update_post_meta( $topic_id, mb_get_topic_activity_datetime_epoch_meta_key(), $epoch );
}

/**
 * Sets the topic voices.
 *
 * @since  1.0.0
 * @access public
 * @param  int           $topic_id
 * @param  array|string  $voices
 * @return bool
 */
function mb_set_topic_voices( $topic_id, $voices ) {
	$voices = implode( ',', wp_parse_id_list( $voices ) );
	return update_post_meta( $topic_id, mb_get_topic_voices_meta_key(), $voices );
}

/**
 * Sets the topic voice count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  int     $count
 * @return bool
 */
function mb_set_topic_voice_count( $topic_id, $count ) {
	return update_post_meta( $topic_id, mb_get_topic_voice_count_meta_key(), $count );
}

/**
 * Sets the topic reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  int     $count
 * @return bool
 */
function mb_set_topic_reply_count( $topic_id, $count ) {
	return update_post_meta( $topic_id, mb_get_topic_reply_count_meta_key(), $count );
}

/**
 * Sets the topic last reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  int     $reply_id
 * @return bool
 */
function mb_set_topic_last_reply_id( $topic_id, $reply_id ) {
	return update_post_meta( $topic_id, mb_get_topic_last_reply_id_meta_key(), $reply_id );
}

/**
 * Adds hidden topic form fields.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_form_fields() {

	if ( mb_is_topic_edit() )
		return;
	
	if ( mb_is_single_forum() ) : ?>
		<input type="hidden" name="mb_forum_id" value="<?php echo absint( get_queried_object_id() ); ?>" />
	<?php endif;

	wp_nonce_field( 'mb_new_topic_action', 'mb_new_topic_nonce', false );
}

/**
 * Filter on the post type link for topics. If the user doesn't have permission to view the topic, 
 * return an empty string.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $link
 * @param  object  $post
 * @return string
 */
function mb_topic_post_type_link( $link, $post ) {
	return mb_is_topic( $post->ID ) && !current_user_can( 'read_topic', $post->ID ) ? '' : $link;
}
