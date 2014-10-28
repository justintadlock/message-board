<?php

/**
 * Creates a new forum query and checks if there are any forums found.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_forum_query() {
	$mb = message_board();

	if ( !is_null( $mb->forum_query->query ) ) {

		$have_posts = $mb->forum_query->have_posts();

		if ( empty( $have_posts ) )
			wp_reset_postdata();

		return $have_posts;
	}

	if ( is_post_type_archive( mb_get_forum_post_type() ) ) {
		global $wp_query;

		$mb->forum_query = $wp_query;
	}

	else {

		$defaults = array(
			'post_type'           => mb_get_forum_post_type(),
			'nopaging'            => true,
			'posts_per_page'      => -1,
			'orderby'             => 'title',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
		);

		if ( is_singular( mb_get_forum_post_type() ) ) {
			$defaults['post_parent'] = get_queried_object_id();
		}

		$mb->forum_query = new WP_Query( $defaults );
	}

	return $mb->forum_query->have_posts();
}

/**
 * Sets up the forum data for the current forum in The Loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_forum() {
	return message_board()->forum_query->the_post();
}

function mb_is_subforum( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$forum = get_post( $forum_id );

	return 0 < $forum->post_parent ? true : false;
}

/* ====== Forum ID ====== */

function mb_forum_id( $forum_id = 0 ) {
	echo mb_get_forum_id( $forum_id );
}

function mb_get_forum_id( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_id', mb_get_post_id( $forum_id ), $forum_id );
}

/* ====== Forum Title ====== */

function mb_single_forum_title( $prefix = '', $echo = true ) {
	return apply_filters( 'mb_single_forum_title', single_post_title( $prefix, $echo ) );
}

function mb_forum_title( $forum_id = 0 ) {
	echo mb_get_forum_title( $forum_id );
}

function mb_get_forum_title( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_title', mb_get_post_title( $forum_id ), $forum_id );
}

/* ====== Forum URL ====== */

function mb_forum_url( $forum_id = 0 ) {
	echo mb_get_forum_url( $forum_id );
}

function mb_get_forum_url( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_url', mb_get_post_url( $forum_id ), $forum_id );
}

function mb_forum_link( $forum_id = 0 ) {
	echo mb_get_forum_link( $forum_id );
}

function mb_get_forum_link( $forum_id = 0 ) {
	$url   = mb_get_forum_url(   $forum_id );
	$title = mb_get_forum_title( $forum_id );

	return sprintf( '<a class="forum-link" href="%s">%s</a>', $url, $title );
}

/* ====== Forum Counts ====== */

function mb_forum_topic_count( $forum_id = 0 ) {
	echo mb_get_forum_topic_count( $forum_id );
}

function mb_get_forum_topic_count( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$count    = get_post_meta( $forum_id, '_forum_topic_count', true );

	if ( empty( $count ) )
		$count = mb_set_forum_topic_count( $forum_id );

	return $count;
}

function mb_forum_post_count( $forum_id = 0 ) {
	echo mb_get_forum_post_count( $forum_id );
}

function mb_get_forum_post_count( $forum_id = 0 ) {

	$topic_count = mb_get_forum_topic_count( $forum_id );
	$reply_count = mb_get_forum_reply_count( $forum_id );

	return $topic_count + $reply_count;
}

function mb_forum_reply_count( $forum_id = 0 ) {
	echo mb_get_forum_reply_count( $forum_id );
}

function mb_get_forum_reply_count( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );
	$count    = get_post_meta( $forum_id, '_forum_reply_count', true );

	if ( empty( $count ) )
		$count = mb_set_forum_reply_count( $forum_id );

	return $count;
}

function mb_set_forum_topic_count( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( empty( $topic_ids ) )
		return 0;

	//$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

	$count = count( $topic_ids );

	if ( !empty( $count ) )
		update_post_meta( $forum_id, '_forum_topic_count', $count );

	return $count;
}

function mb_set_forum_reply_count( $forum_id ) {

	$topic_ids = mb_get_forum_topic_ids( $forum_id );

	if ( empty( $topic_ids ) )
		return 0;

	$reply_ids = mb_get_multi_topic_reply_ids( $topic_ids );

	$count = !empty( $reply_ids ) ? count( $reply_ids ) : 0;

	if ( !empty( $count ) )
		update_post_meta( $forum_id, '_forum_reply_count', $count );

	return $count;
}

function mb_get_forum_topic_ids( $forum_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %s", mb_get_topic_post_type(), absint( $forum_id ) ) );
}

function mb_get_multi_topic_reply_ids( $topic_ids ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent IN ( " . implode( ',', $topic_ids ) . " )", mb_get_reply_post_type() ) );
}

function mb_forum_last_topic_id( $forum_id ) {
	echo mb_get_forum_last_topic_id( $forum_id );
}

function mb_get_forum_last_topic_id( $forum_id ) {
	$topic_id = get_post_meta( $forum_id, '_forum_last_topic_id', true );

	return !empty( $topic_id ) ? absint( $topic_id ) : 0;
}

function mb_forum_last_reply_id( $forum_id ) {
	echo mb_get_forum_last_reply_id( $forum_id );
}

function mb_get_forum_last_reply_id( $forum_id ) {
	$topic_id = get_post_meta( $forum_id, '_forum_last_reply_id', true );

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




function mb_forum_pagination( $args = array() ) {
	global $wp_rewrite, $wp_query;

	$query = message_board()->topic_query;

	/* If there's not more than one page, return nothing. */
	if ( 1 >= $query->max_num_pages )
		return;

	/* Get the current page. */
	$current = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );

	/* Get the max number of pages. */
	$max_num_pages = intval( $query->max_num_pages );

	/* Get the pagination base. */
	$pagination_base = $wp_rewrite->pagination_base;

	/* Set up some default arguments for the paginate_links() function. */
	$defaults = array(
		'base'         => add_query_arg( 'paged', '%#%' ),
		'format'       => '',
		'total'        => $max_num_pages,
		'current'      => $current,
		'prev_next'    => true,
		//'prev_text'  => __( '&laquo; Previous' ), // This is the WordPress default.
		//'next_text'  => __( 'Next &raquo;' ), // This is the WordPress default.
		'show_all'     => false,
		'end_size'     => 1,
		'mid_size'     => 1,
		'add_fragment' => '',
		'type'         => 'plain',

		// Begin loop_pagination() arguments.
		'before'       => '<nav class="pagination loop-pagination">',
		'after'        => '</nav>',
		'echo'         => true,
	);

	/* Add the $base argument to the array if the user is using permalinks. */
	if ( $wp_rewrite->using_permalinks() )
		$defaults['base'] = user_trailingslashit( trailingslashit( get_pagenum_link() ) . "{$pagination_base}/%#%" );

	/* Merge the arguments input with the defaults. */
	$args = wp_parse_args( $args, $defaults );

	/* Don't allow the user to set this to an array. */
	if ( 'array' == $args['type'] )
		$args['type'] = 'plain';

	/* Get the paginated links. */
	$page_links = paginate_links( $args );

	/* Remove 'page/1' from the entire output since it's not needed. */
	$page_links = preg_replace( 
		array( 
			"#(href=['\"].*?){$pagination_base}/1(['\"])#",  // 'page/1'
			"#(href=['\"].*?){$pagination_base}/1/(['\"])#", // 'page/1/'
			"#(href=['\"].*?)\?paged=1(['\"])#",             // '?paged=1'
			"#(href=['\"].*?)&\#038;paged=1(['\"])#"         // '&#038;paged=1'
		), 
		'$1$2', 
		$page_links 
	);

	/* Wrap the paginated links with the $before and $after elements. */
	$page_links = $args['before'] . $page_links . $args['after'];

	/* Return the paginated links for use in themes. */
	if ( $args['echo'] )
		echo $page_links;
	else
		return $page_links;
}



