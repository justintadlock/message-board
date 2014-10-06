<?php

/* wrapper for wp_list_categories() */
function mb_list_forums( $args = array() ) {

	$defaults = array(
		'show_option_none' => false,
		'show_count'       => true,
		'hierarchical'     => true,
		'hide_empty'       => false,
		'echo'             => true,

		// custom args
		'wrap'             => '<ul %s>%s</ul>',
	);

	$r = $args = wp_parse_args( $args, $defaults );

	$r['echo']     = false;
	$r['title_li'] = false;
	$r['taxonomy'] = 'forum';

	unset( $r['wrap'] );

	$forums = wp_list_categories( $r );

	if ( !empty( $forums ) ) {

		$forums = sprintf( $args['wrap'], 'class="forums-list"', $forums );
	}

	$forums = apply_filters( 'mb_list_forums', $forums, $args );

	if ( !$args['echo'] )
		return $forums;

	echo $forums;
}

function mb_get_forums( $args = array() ) {

	$defaults = array(
		'hide_empty'               => false,
		'hierarchical'             => false,
		'parent'                   => 0,
		'pad_counts'               => true,
	);

	$args = wp_parse_args( $args, $defaults );

	return 	get_terms( 'forum', $args );
}

function mb_get_sub_forums( $args = array() ) {

	$defaults = array(
		'parent'       => is_tax( 'forum' ) ? get_queried_object_id() : 0,
		'hide_empty'   => false,
		'pad_counts'   => true,
		'hierarchical' => false
	);

	$args = wp_parse_args( $args, $defaults );

	return get_terms( 'forum', $args );
}

function mb_forum_url( $forum_id = 0 ) {
	echo mb_get_forum_url( $forum_id );
}

function mb_get_forum_url( $forum_id = 0 ) {
	return esc_url( get_term_link( absint( $forum_id ), 'forum' ) );
}

function mb_forum_link( $forum_id = 0 ) {
	echo mb_get_forum_link( $forum_id );
}

function mb_get_forum_link( $forum_id = 0 ) {
	$forum_link  = '';
	$forum_url   = mb_get_forum_url( $forum_id );
	$forum_title = mb_get_forum_title( $forum_id );

	if ( !empty( $forum_url ) )
		$forum_link = sprintf( '<a class="forum-link" href="%s">%s</a>', $forum_url, $forum_title );

	return apply_filters( 'mb_get_forum_link', $forum_link, $forum_id );
}



function mb_forum_topic_count( $forum_id ) {
	echo mb_get_forum_topic_count( $forum_id );
}

function mb_get_forum_topic_count( $forum_id ) {
	return get_term( absint( $forum_id ), 'forum' )->count;
}

function mb_forum_post_count( $forum_id ) {
	echo mb_get_forum_post_count( $forum_id );
}

function mb_get_forum_post_count( $forum_id ) {

	$topic_count = mb_get_forum_topic_count( $forum_id );
	$reply_count = mb_get_forum_reply_count( $forum_id );

	return $topic_count + $reply_count;
}

function mb_forum_reply_count( $forum_id ) {
	echo mb_get_forum_reply_count( $forum_id );
}

function mb_get_forum_reply_count( $forum_id ) {

	$count = mb_get_forum_meta( $forum_id, '_forum_reply_count', true );

	if ( empty( $count ) )
		$count = mb_set_forum_reply_count( $forum_id );

	return $count;
}

function mb_set_forum_reply_count( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( empty( $topic_ids ) )
		return 0;

	$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

	$count = !empty( $reply_ids ) ? count( $reply_ids ) : 0;

	if ( !empty( $count ) )
		mb_update_forum_meta( $forum_id, '_forum_reply_count', $count );

	return $count;
}

function mb_get_forum_topic_ids( $forum_id ) {
	$topic_ids = get_objects_in_term( $forum_id, 'forum' );

	return ( !is_wp_error( $topic_ids ) && !empty( $topic_ids ) ) ? $topic_ids : array();
}

function mb_get_multi_topic_reply_ids( $topic_ids ) {
	global $wpdb;

	return $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_parent IN ( " . implode( ',', $topic_ids ) . " )" );
}

function mb_forum_title( $forum_id ) {
	echo mb_get_forum_title( $forum_id );
}

function mb_get_forum_title( $forum_id ) {
	return get_term( absint( $forum_id ), 'forum' )->name;
}

function mb_forum_last_topic_id( $forum_id ) {
	echo mb_get_forum_last_topic_id( $forum_id );
}

function mb_get_forum_last_topic_id( $forum_id ) {
	$topic_id = mb_get_forum_meta( $forum_id, '_forum_last_topic_id', true );

	return !empty( $topic_id ) ? absint( $topic_id ) : 0;
}

function mb_forum_last_reply_id( $forum_id ) {
	echo mb_get_forum_last_reply_id( $forum_id );
}

function mb_get_forum_last_reply_id( $forum_id ) {
	$topic_id = mb_get_forum_meta( $forum_id, '_forum_last_reply_id', true );

	return !empty( $reply_id ) ? absint( $reply_id ) : 0;
}

function mb_forum_last_post_id( $forum_id ) {
	echo mb_get_forum_last_post_id( $forum_id );
}

function mb_get_forum_last_post_id( $forum_id ) {

	$topic_id = mb_get_forum_last_topic_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	return $reply_id > $topic_id ? $reply_id : $topic_id;
}






