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

function mb_forum_title( $forum_id ) {
	echo mb_get_forum_title( $forum_id );
}

function mb_get_forum_title( $forum_id ) {
	return get_term( absint( $forum_id ), 'forum' )->name;
}