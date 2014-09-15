<?php

/**
 * Registers custom meta keys with WordPress and provides callbacks for sanitizing and authorizing 
 * the metadata.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_meta() {

	/* Register post meta. */
	register_meta( 'post', '_topic_activity_datetime',       'esc_html',   '__return_true' );
	register_meta( 'post', '_topic_activity_datetime_epoch', 'absint',     '__return_true' );
	register_meta( 'post', '_topic_last_reply_id',           'absint',     '__return_true' );
	register_meta( 'post', '_topic_voices',                  'absint',     '__return_true' );
	register_meta( 'post', '_topic_voice_count',             'absint',     '__return_true' );
	register_meta( 'post', '_topic_reply_count',             'absint',     '__return_true' );

	/* Register term meta. One can dream, right? */
	//register_meta( 'term', '_forum_activity_datetime',       'strip_tags', '__return_true' );
	//register_meta( 'term', '_forum_activity_datetime_epoch', 'strip_tags', '__return_true' );
	//register_meta( 'term', '_forum_last_topic_id',           'absint',     '__return_true' );
	//register_meta( 'term', '_forum_last_reply_id',           'absint',     '__return_true' );
	//register_meta( 'term', '_forum_reply_count',             'absint',     '__return_true' );
}

/**
 * Saves forum topic metadata when a forum topic is saved.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  object  $post
 * @return void
 */
function mb_save_post( $post_id, $post = '' ) {

	if ( empty( $post ) )
		return;

	if ( 'forum_topic' === $post->post_type ) {

		$last_post_time = get_post_meta( $post_id, '_topic_activity_datetime', true );

		if ( empty( $last_post_time ) ) {
			add_post_meta( $post_id, '_topic_activity_datetime', $post->post_date );//current_time( 'mysql' ) );
		}

		$last_post_ymd = get_post_meta( $post_id, '_topic_activity_datetime_epoch', true );

		if ( empty( $last_post_ymd ) ) {
			add_post_meta( $post_id, '_topic_activity_datetime_epoch', mysql2date( 'U', $post->post_date ) );
		}

		$voices = get_post_meta( $post_id, '_topic_voices' );

		if ( empty( $voices ) || !in_array( $post->post_author, $voices ) ) {
			add_post_meta( $post_id, '_topic_voices', $post->post_author );
		}

		$count = mb_get_topic_voice_count( $post_id );

		if ( empty( $count ) ) {
			add_post_meta( $post_id, '_topic_voice_count', $count );
		}
	}

	elseif ( 'forum_reply' === $post->post_type ) {
		//$count = get_post_meta( $topic_id, '_topic_reply_count', true );

	}
}
