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

	if ( 'forum_reply' !== $post->post_type )
		return $link;

	$url = mb_bbp_get_reply_url( $post->ID, $post );

	return !empty( $url ) ? $url : $link;



}

	function mb_bbp_get_reply_url( $reply_id = 0, $post, $redirect_to = '' ) {

		$topic_id = $post->post_parent;

		if ( 0 >= $topic_id )
			return '';

		$reply_page = ceil( (int) mb_bbp_get_reply_position( $reply_id, $topic_id ) / (int) mb_get_replies_per_page() );

		$reply_hash = '#post-' . $reply_id;
		$topic_link = get_permalink( $topic_id );
		$topic_url  = remove_query_arg( 'view', $topic_link );

		// Don't include pagination if on first page
		if ( 1 >= $reply_page ) {
			$url = $topic_url . $reply_hash;

		// Include pagination
		} else {
			global $wp_rewrite;

			// Pretty permalinks
			if ( $wp_rewrite->using_permalinks() ) {
				$url = trailingslashit( $topic_url ) . trailingslashit( $wp_rewrite->pagination_base ) . $reply_page . $reply_hash;

			// Yucky links
			} else {
				$url = add_query_arg( 'paged', $reply_page, $topic_url ) . $reply_hash;
			}
		}

		return apply_filters( 'bbp_get_reply_url', $url, $reply_id, $redirect_to );
	}

	function mb_bbp_get_reply_position( $reply_id = 0, $topic_id = 0 ) {

		// Get required data
		$reply_position = get_post_field( 'menu_order', $reply_id );

		// Reply doesn't have a position so get the raw value
		if ( empty( $reply_position ) ) {

			// Post is not the topic
			if ( $reply_id !== $topic_id ) {
				$reply_position = bbp_get_reply_position_raw( $reply_id, $topic_id );

				// Update the reply position in the posts table so we'll never have
				// to hit the DB again.
				if ( !empty( $reply_position ) ) {
					bbp_update_reply_position( $reply_id, $reply_position );
				}

			// Topic's position is always 0
			} else {
				$reply_position = 0;
			}
		}

		return (int) apply_filters( 'bbp_get_reply_position', $reply_position, $reply_id, $topic_id );
	}


function bbp_update_reply_position( $reply_id = 0, $reply_position = 0 ) {

	if ( empty( $reply_id ) )
		return false;

	// If no position was passed, get it from the db and update the menu_order
	if ( empty( $reply_position ) ) {
		$reply_position = bbp_get_reply_position_raw( $reply_id, bbp_get_reply_topic_id( $reply_id ) );
	}

	// Update the replies' 'menp_order' with the reply position
	wp_update_post( array(
		'ID'         => $reply_id,
		'menu_order' => $reply_position
	) );

	return (int) $reply_position;
}

/**
 * Get the position of a reply by querying the DB directly for the replies
 * of a given topic.
 *
 * @since bbPress (r3933)
 *
 * @param int $reply_id
 * @param int $topic_id
 */
function bbp_get_reply_position_raw( $reply_id = 0, $topic_id = 0 ) {

	// Get required data
	$reply_position = 0;

	// If reply is actually the first post in a topic, return 0
	if ( $reply_id !== $topic_id ) {

		// Make sure the topic has replies before running another query
		$reply_count = bbp_get_topic_reply_count( $topic_id, false );
		if ( !empty( $reply_count ) ) {

			// Get reply id's
			$topic_replies = bbp_get_all_child_ids( $topic_id, 'forum_reply' );
			if ( !empty( $topic_replies ) ) {

				// Reverse replies array and search for current reply position
				$topic_replies  = array_reverse( $topic_replies );
				$reply_position = array_search( (string) $reply_id, $topic_replies );

				// Bump the position to compensate for the lead topic post
				$reply_position++;
			}
		}
	}

	return (int) $reply_position;
}

	function bbp_get_topic_reply_count( $topic_id = 0, $integer = false ) {

		$replies  = (int) get_post_meta( $topic_id, '_topic_reply_count', true );
		$filter   = ( true === $integer ) ? 'bbp_get_topic_reply_count_int' : 'bbp_get_topic_reply_count';

		return apply_filters( $filter, $replies, $topic_id );
	}
function bbp_get_all_child_ids( $parent_id = 0, $post_type = 'post' ) {
	global $wpdb;

	// Bail if nothing passed
	if ( empty( $parent_id ) )
		return false;

	// The ID of the cached query
	$cache_id  = 'bbp_parent_all_' . $parent_id . '_type_' . $post_type . '_child_ids';

	// Check for cache and set if needed
	$child_ids = wp_cache_get( $cache_id, 'bbpress_posts' );
	if ( false === $child_ids ) {
		$child_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status = 'publish' AND post_type = '%s' ORDER BY ID DESC;", $parent_id, $post_type ) );
		wp_cache_set( $cache_id, $child_ids, 'bbpress_posts' );
	}

	// Filter and return
	return apply_filters( 'bbp_get_all_child_ids', $child_ids, (int) $parent_id, $post_type );
}
