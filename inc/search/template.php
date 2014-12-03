<?php

/**
 * Checks if viewing the forum search page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_search() {
	global $wp;

	return is_search() && mb_get_root_slug() === $wp->request ? true : false;
}

/**
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_search_query() {
	$mb = message_board();

	/* If a query has already been created, let's roll. */
	if ( !is_null( $mb->search_query->query ) ) {

		$have_posts = $mb->search_query->have_posts();

		if ( empty( $have_posts ) )
			wp_reset_postdata();

		return $have_posts;
	}

	/* Use the main WP query when viewing a single topic or topic archive. */
	if ( mb_is_forum_search() ) {
		global $wp_the_query;
		
		$mb->search_query = $wp_the_query;
	}

	return $mb->search_query->have_posts();
}

/**
 * Sets up the topic data for the current topic in The Loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_search_post() {
	return message_board()->search_query->the_post();
}
