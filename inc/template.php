<?php

function mb_search_form() {
	echo mb_get_search_form();
}

function mb_get_search_form() {

	add_filter( 'get_search_form', 'mb_search_form_filter', 5 );
	$form = apply_filters( 'mb_get_search_form', get_search_form( false ) );
	remove_filter( 'get_search_form', 'mb_search_form_filter', 5 );

	return $form;
}

function mb_search_form_filter( $form ) {

	$form = '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">
		<label>
			<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'message-board' ) . '</span>
			<input type="search" class="search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder', 'message-board' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label', 'message-board' ) . '" />
		</label>
		<input type="submit" class="search-submit" value="'. esc_attr_x( 'Search', 'submit button', 'message-board' ) .'" />
		<input type="hidden" name="post_type[]" value="forum_topic" />
		<input type="hidden" name="post_type[]" value="forum_reply" />
	</form>';

	return $form;
}

/* Note: The below is borrowed from the bbPress plugin on handling the reply permalink and related stuff.
 * Lots of cleanup needed.
 */

//  apply_filters( 'post_type_link', $post_link, $post, $leavename, $sample );

add_filter( 'post_type_link', 'mb_reply_post_type_link', 10, 2 );

function mb_reply_post_type_link( $link, $post ) {

	if ( mb_get_reply_post_type() !== $post->post_type )
		return $link;

	$url = mb_generate_reply_url( $post->ID );

	return !empty( $url ) ? $url : $link;



}

function mb_get_reply_topic_id( $reply_id = 0 ) {
	$reply_id = mb_get_reply_id( $reply_id );
	return get_post_field( 'post_parent', $reply_id );
}

function mb_generate_reply_url( $reply_id = 0 ) {

	$reply_id       = mb_get_reply_id( $reply_id );
	$per_page       = mb_get_replies_per_page();
	$reply_position = mb_get_reply_position( $reply_id );
	$reply_hash     = "#post-{$reply_id}";

	$reply_page = ceil( $reply_position / $per_page );

	$topic_id  = mb_get_reply_topic_id( $reply_id );
	$topic_url = get_permalink( $topic_id );

	if ( 1 >= $reply_page ) {

		$reply_url = user_trailingslashit( $topic_url ) . $reply_hash;
	}

	else {
		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {

			$reply_url = trailingslashit( $topic_url ) . trailingslashit( $wp_rewrite->pagination_base ) . user_trailingslashit( $reply_page ) . $reply_hash;

		} else {
			$reply_url = add_query_arg( 'paged', $reply_page, $topic_url ) . $reply_hash;
		}
	}

	return $reply_url;
}
