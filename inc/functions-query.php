<?php

/* Filter the posts found by the main query. */
add_filter( 'the_posts', 'mb_the_posts', 10, 2 );

/**
 * Checks if viewing the forum front page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_front() {

	$is_front = false;
	$on_front = mb_get_show_on_front();

	if ( 'forums' === $on_front && is_post_type_archive( mb_get_forum_post_type() ) )
		$is_front = true;

	elseif ( 'topics' === $on_front && is_post_type_archive( mb_get_topic_post_type() ) )
		$is_front = true;

	return apply_filters( 'mb_is_forum_front', $is_front );
}

/**
 * Checks if viewing the forum login page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_login() {
	return get_query_var( 'mb_custom' ) && 'login' === get_query_var( 'mb_login' ) ? true : false;
}

/**
 * Checks if viewing one of the Message Board plugin pages.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_message_board() {

	$is_message_board = false;

	if ( 1 == get_query_var( 'mb_profile' ) || get_query_var( 'mb_topics' ) || get_query_var( 'mb_replies' ) ||
		get_query_var( 'mb_bookmarks' ) || get_query_var( 'mb_subscriptions' ) 
		|| mb_is_user_view() 
		|| mb_is_forum_search() 
		|| mb_is_forum_front() 
		|| mb_is_topic_archive()
		|| mb_is_forum_archive()
		|| mb_is_forum_login()
		|| mb_is_single_topic()
		|| mb_is_single_forum()
		|| mb_is_user_archive()
	) {
		$is_message_board = true;
	}

	return apply_filters( 'mb_is_message_board', $is_message_board );
}

/**
 * Overwrites the main query depending on the situation.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $query
 * @return void
 */
function mb_pre_get_posts( $query ) {

	/* If viewing the forum archive page. */
	if ( !is_admin() && $query->is_main_query() && mb_is_forum_archive() ) {

		$query->set( 'post_type',      mb_get_forum_post_type()    );
		$query->set( 'post_status',    array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ) );
		$query->set( 'posts_per_page', mb_get_forums_per_page()    );
		$query->set( 'order',          'ASC'                       );
		$query->set( 'orderby',        'menu_order title'          );
		$query->set( 'meta_query',
			array(
				array(
					'key'     => '_forum_level',
					'value'   => array( 1, 2 ),
					'compare' => 'IN',
					'type'    => 'NUMERIC'
				)
			)
		);
	}

	/* Is topic archive page. */
	elseif ( !is_admin() && $query->is_main_query() && mb_is_topic_archive() ) {

		$query->set( 'post_type',      mb_get_topic_post_type()    );
		$query->set( 'post_status',    array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ) );
		$query->set( 'posts_per_page', mb_get_topics_per_page()    );
		$query->set( 'order',          'DESC'                      );
		$query->set( 'orderby',        'menu_order'                );
	}

	/* If viewing a user view. */
	elseif ( !is_admin() && $query->is_main_query() && get_query_var( 'mb_user_view' ) ) {

		if ( 'topics' === get_query_var( 'mb_user_view' ) ) {

			$query->set( 'post_type',      mb_get_topic_post_type() );
			$query->set( 'posts_per_page', mb_get_topics_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

		} elseif ( 'bookmarks' === get_query_var( 'mb_user_view' ) ) {

			$user      = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$bookmarks = get_user_meta( $user->ID, '_topic_bookmarks', true );
			$favs      = wp_parse_id_list( $bookmarks );

			$query->set( 'post__in',       $favs                    );
			$query->set( 'post_type',      mb_get_topic_post_type() );
			$query->set( 'posts_per_page', mb_get_topics_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

			add_filter( 'posts_where', 'mb_auth_posts_where', 10, 2 );

		} elseif ( 'subscriptions' === get_query_var( 'mb_user_view' ) ) {

			$user = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$subscriptions = get_user_meta( $user->ID, '_topic_subscriptions', true );
			$subs = wp_parse_id_list( $subscriptions );

			$query->set( 'post__in',       $subs                    );
			$query->set( 'post_type',      mb_get_topic_post_type() );
			$query->set( 'posts_per_page', mb_get_topics_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

			add_filter( 'posts_where', 'mb_auth_posts_where', 10, 2 );

		} elseif ( 'replies' === get_query_var( 'mb_user_view' ) ) {

			$query->set( 'post_type',      mb_get_reply_post_type()  );
			$query->set( 'posts_per_page', mb_get_replies_per_page() );
			$query->set( 'order',          'DESC'                    );
			$query->set( 'orderby',        'date'                    );

		} elseif ( 'activity' === get_query_var( 'mb_user_view' ) ) {

			$query->set( 'post_type',     array( mb_get_reply_post_type(), mb_get_topic_post_type() ) );
			$query->set( 'posts_per_page', mb_get_replies_per_page() );
			$query->set( 'order',          'DESC'                    );
			$query->set( 'orderby',        'date'                    );
		}
	}

	elseif ( !is_admin() && $query->is_main_query() && mb_is_forum_search() ) {

		$query->set( 'post_type',      array( mb_get_forum_post_type(), mb_get_topic_post_type(), mb_get_reply_post_type() ) );
		$query->set( 'post_status',    array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ) );
		$query->set( 'posts_per_page', mb_get_topics_per_page()    );
	}
}

/**
 * Filter on 'posts_where' to make sure we're not loading posts by the author.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $where
 * @param  object  $query
 * @global object  $wpdb
 * @return string
 */
function mb_auth_posts_where( $where, $query ) {
	global $wpdb;

	$author_id = absint( get_query_var( 'author' ) );

	return str_replace( " AND ({$wpdb->posts}.post_author = {$author_id})", '', $where );
}

/**
 * Filter on 'the_posts.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $posts
 * @param  object $query
 * @return array
 */
function mb_the_posts( $posts, $query ) {

	/* If viewing the topic archive, put super sticky topics at the top. */
	if ( !is_admin() && $query->is_main_query() && mb_is_topic_archive() ) {

		$super_stickies = get_option( 'mb_super_sticky_topics', array() );

		$posts = mb_the_posts_stickies( $posts, $super_stickies );
	}

	// http://wordpress.stackexchange.com/questions/63599/custom-post-type-wp-query-and-orderby
	/* If viewing the forum archive, put forums in hierarchical order. */
	elseif ( !is_admin() && $query->is_main_query() && mb_is_forum_archive() ) {

		$refs = $list = array();

		foreach( $posts as $post ) {
			$thisref = &$refs[ $post->ID ];

			$thisref['post'] = $post;

			if ( $post->post_parent == 0 ) {
				$list[ $post->ID ] = &$thisref;
			} else {
				$refs[ $post->post_parent ]['children'][ $post->ID ] = &$thisref;
			}
		}

		$result = array();
		mb_recursively_flatten_list( $list, $result );
		$posts = $result;
	}

	return $posts;
}

/**
 * Helper function for flattening a list of parent/child posts.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $list
 * @param  array  $result
 * @return void
 */
function mb_recursively_flatten_list( $list, &$result ) {

	foreach ( $list as $node ) {
		$result[] = $node['post'];

		if ( isset( $node['children'] ) ) {
			mb_recursively_flatten_list( $node['children'], $result );
		}
	}
}

/**
 * Adds sticky posts to the front of the line with any given set of posts and stickies.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $posts         Array of post objects.
 * @param  array  $sticky_posts  Array of post IDs.
 * @return array
 */
function mb_the_posts_stickies( $posts, $sticky_posts ) {

	/* Only do this if on the first page and we indeed have stickies. */
	if ( !is_paged() && !empty( $sticky_posts ) ) {

		$num_posts     = count( $posts );
		$sticky_offset = 0;

		/* Loop over posts and relocate stickies to the front. */
		for ( $i = 0; $i < $num_posts; $i++ ) {

			if ( in_array( $posts[ $i ]->ID, $sticky_posts ) ) {

				$sticky_post = $posts[ $i ];

				/* Remove sticky from current position. */
				array_splice( $posts, $i, 1);

				/* Move to front, after other stickies. */
				array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );

				/* Increment the sticky offset. The next sticky will be placed at this offset. */
				$sticky_offset++;

				/* Remove post from sticky posts array. */
				$offset = array_search( $sticky_post->ID, $sticky_posts );

				unset( $sticky_posts[ $offset ] );
			}
		}

		/* Fetch sticky posts that weren't in the query results. */
		if ( !empty( $sticky_posts ) ) {

			$stickies = get_posts(
				array(
					'post__in'    => $sticky_posts,
					'post_type'   => mb_get_topic_post_type(),
					'post_status' => array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ),
					'nopaging'    => true
				)
			);

			foreach ( $stickies as $sticky_post ) {
				array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
				$sticky_offset++;
			}
		}
	}

	return $posts;
}

/**
 * Sets `$query->is_404` to `false` right after the query has been parsed when viewing the forum front 
 * page, which WP sets to 404 by default.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $query
 * @return void
 */
function mb_parse_query( $query ) {

	if ( mb_is_forum_search() ) {
		$query->is_404        = false;
		$query->is_front_page = false;
		$query->is_home       = false;
		$query->is_post_type_archive = false;
	} elseif ( mb_is_forum_front() ) {
		$query->is_404 = false;
		$query->is_home = false;
	} elseif ( mb_is_user_view() ) {
		$query->is_home = false;
		$query->is_archive = true;
	}
}

/**
 * Overrides the 404 for the forum front page early on the `template_redirect` hook.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_404_override() {
	global $wp_query;

	if ( mb_is_forum_front() || mb_is_forum_login() || mb_is_user_archive() ) {
		status_header( 200 );
		$wp_query->is_404        = false;
		$wp_query->is_front_page = false;
		$wp_query->is_home       = false;
	}
}
