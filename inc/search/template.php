<?php

/**
 * Checks if viewing the search (advanced) page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_search() {
	return get_query_var( 'mb_custom' ) && 'search' === get_query_var( 'mb_custom' ) ? true : false;
}

/**
 * Checks if viewing the forum search page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_search_results() {
	global $wp;

	return is_search() && mb_is_search() ? true : false;
}

function mb_get_search_mode() {
	return isset( $_GET['mb_search_mode'] ) && 'advanced' === $_GET['mb_search_mode'] ? 'advanced' : 'basic';
}

function mb_is_advanced_search() {
	return mb_is_search_results() && 'advanced' === mb_get_search_mode() ? true : false;
}

function mb_is_basic_search() {
	return mb_is_search_results() && 'basic' === mb_get_search_mode() ? true : false;
}

function mb_search_url() {
	echo mb_get_search_url();
}

function mb_get_search_url() {
	global $wp_rewrite;

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( home_url( mb_get_search_slug() ) );
	else
		$url = add_query_arg( 'mb_custom', 'search', home_url() );

	return esc_url( apply_filters( 'mb_get_search_url', $url ) );
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
	if ( mb_is_search_results() ) {
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
function mb_the_search_result() {
	return message_board()->search_query->the_post();
}

/**
 * Outputs pagination links for search results.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_loop_search_pagination( $args = array() ) {
	return mb_pagination( $args, message_board()->search_query );
}
