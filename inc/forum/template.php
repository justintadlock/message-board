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

	if ( mb_is_forum_archive() || mb_is_user_page( array( 'forums', 'forum-subscriptions' ) ) ) {
		global $wp_query;

		$mb->forum_query = $wp_query;
	}

	else {
		$per_page = mb_get_forums_per_page();

		$defaults = array(
			'post_type'           => mb_get_forum_post_type(),
			'posts_per_page'      => $per_page,
			'paged'               => get_query_var( 'paged' ),
			'orderby'             => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'ignore_sticky_posts' => true,
		);

		if ( mb_is_single_forum() )
			$defaults['post_parent'] = get_queried_object_id();

		add_filter( 'the_posts', 'mb_posts_hierarchy_filter', 10, 2 );

		$mb->forum_query = new WP_Query( $defaults );
	}

	return $mb->forum_query->have_posts();
}

/**
 * Creates a new sub-forum query and checks if there are any forums found.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_subforum_query() {
	$mb = message_board();

	if ( !is_null( $mb->subforum_query->query ) ) {

		$have_posts = $mb->subforum_query->have_posts();

		if ( empty( $have_posts ) ) {
			wp_reset_postdata();
			$mb->subforum_query->query = null;
		}

		return $have_posts;
	}

	add_action( 'loop_end',             'mb_subforum_loop_end' );
	add_filter( 'mb_in_subforum_loop', '__return_true'         );

	$defaults = array(
		'post_type'           => mb_get_forum_post_type(),
		'nopaging'            => true,
		'posts_per_page'      => -1,
		'orderby'             => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'ignore_sticky_posts' => true,
	);

	if ( $mb->forum_query->in_the_loop )
		$defaults['post_parent'] = mb_get_forum_id();
	elseif ( mb_is_single_forum() )
		$defaults['post_parent'] = get_queried_object_id();

	//add_filter( 'the_posts', 'mb_posts_hierarchy_filter', 10, 2 );

	$mb->subforum_query = new WP_Query( $defaults );

	return $mb->subforum_query->have_posts();
}

/**
 * Remove filters/actions when the sub-forum loop ends.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_subforum_loop_end() {
	remove_action( 'loop_end',             'mb_subforum_loop_end' );
	remove_filter( 'mb_in_subforum_loop', '__return_true'         );
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

function mb_the_subforum() {
	return message_board()->subforum_query->the_post();
}

/* ====== Conditionals ====== */

function mb_is_single_forum( $forum = '' ) {

	if ( !is_singular( mb_get_forum_post_type() ) )
		return false;

	if ( !empty( $forum ) )
		return is_single( $forum );

	return true;
}

function mb_is_forum_archive() {
	return mb_is_forum_search() ? false : is_post_type_archive( mb_get_forum_post_type() );
}

/* ====== Forum Status ====== */

/**
 * Conditional check to see whether a forum has the "open" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_open( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = get_post_status( $forum_id );

	return apply_filters( 'mb_is_forum_open', mb_get_open_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "close" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_closed( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = get_post_status( $forum_id );

	return apply_filters( 'mb_is_forum_closed', mb_get_close_post_status() === $status ? true : false, $forum_id );
}

/**
 * Conditional check to see whether a forum has the "trash" post status.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_trash( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$status   = get_post_status( $forum_id );

	return apply_filters( 'mb_is_forum_trash', mb_get_trash_post_status() === $status ? true : false, $forum_id );
}

function mb_forum_toggle_open_url( $forum_id = 0 ) {
	echo mb_get_forum_toggle_open_close_url( $forum_id = 0 );
}

function mb_get_forum_toggle_open_url( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	$action = mb_is_forum_open( $forum_id ) ? 'close' : 'open';

	$url = add_query_arg( array( 'forum_id' => $forum_id, 'action' => 'mb_toggle_open' ) );
	$url = wp_nonce_url( $url, "open_forum_{$forum_id}", 'mb_nonce' );

	return $url;
}

function mb_forum_toggle_open_link( $forum_id = 0 ) {
	echo mb_get_forum_toggle_open_link( $forum_id );
}

function mb_get_forum_toggle_open_link( $forum_id = 0 ) {

	$forum_id = mb_get_topic_id( $forum_id );

	if ( !current_user_can( 'moderate_forum', $forum_id ) )
		return '';

	$status = mb_is_forum_open( $forum_id ) ? get_post_status_object( mb_get_close_post_status() ) : get_post_status_object( mb_get_open_post_status() );

	$link = sprintf( '<a class="toggle-open-link" href="%s">%s</a>', mb_get_forum_toggle_open_url( $forum_id ), $status->label_verb );

	return $link;
}

function mb_forum_toggle_trash_url( $forum_id = 0 ) {
	echo mb_get_forum_toggle_trash_url( $forum_id = 0 );
}

function mb_get_forum_toggle_trash_url( $forum_id = 0 ) {

	$forum_id = mb_get_forum_id( $forum_id );

	$url = add_query_arg( array( 'forum_id' => $forum_id, 'action' => 'mb_toggle_trash' ) );
	$url = wp_nonce_url( $url, "trash_forum_{$forum_id}", 'mb_nonce' );

	return $url;
}

function mb_forum_toggle_trash_link( $forum_id = 0 ) {
	echo mb_get_forum_toggle_trash_link( $forum_id );
}

function mb_get_forum_toggle_trash_link( $forum_id = 0 ) {

	$forum_id = mb_get_topic_id( $forum_id );

	if ( !current_user_can( 'moderate_forum', $forum_id ) )
		return '';

	$text = mb_is_forum_trash( $forum_id ) ? __( 'Restore', 'message-board' ) : get_post_status_object( mb_get_trash_post_status() )->label;

	$link = sprintf( '<a class="toggle-trash-link" href="%s">%s</a>', mb_get_forum_toggle_trash_url( $forum_id ), $text );

	return $link;
}

/* ====== Forum Labels ====== */

/**
 * Outputs a forums labels.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_labels( $forum_id = 0 ) {
	echo mb_get_forum_labels( $forum_id );
}

/**
 * Returns a forum's labels.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_labels( $forum_id = 0 ) {
	$forum_id       = mb_get_forum_id( $forum_id );
	$labels = array();

	/* @todo Default labels - closed, private, etc. */

	$labels = apply_filters( 'mb_forum_labels', $labels, $forum_id );

	if ( !empty( $labels ) ) {

		$formatted = '';

		foreach ( $labels as $key => $value )
			$formatted .= sprintf( '<span class="forum-label %s">%s</span>', sanitize_html_class( "forum-label-{$key}" ), $value );

		return sprintf( '<span class="forum-labels">%s</span>', $formatted );
	}

	return '';
}

/* ====== Forum ID ====== */

/**
 * Displays the forum ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_id( $forum_id = 0 ) {
	echo mb_get_forum_id( $forum_id );
}

/**
 * Returns the forum ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int
 */
function mb_get_forum_id( $forum_id = 0 ) {
	$mb = message_board();

	if ( is_numeric( $forum_id ) && 0 < $forum_id )
		$_forum_id = $forum_id;

	elseif ( !empty( $mb->subforum_query->in_the_loop ) && isset( $mb->subforum_query->post->ID ) )
		$_forum_id = $mb->subforum_query->post->ID;

	elseif ( !empty( $mb->forum_query->in_the_loop ) && isset( $mb->forum_query->post->ID ) )
		$_forum_id = $mb->forum_query->post->ID;

	elseif ( mb_is_single_forum() )
		$_forum_id = get_queried_object_id();

	else
		$_forum_id = 0;

	return apply_filters( 'mb_get_forum_id', absint( $_forum_id ), $forum_id );
}

/* ====== Forum Content ====== */

/**
 * Displays the forum content.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_content( $forum_id = 0 ) {
	echo mb_get_forum_content( $forum_id );
}

/**
 * Returns the forum content.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_content( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_content', mb_get_post_content( $forum_id ), $forum_id );
}

/* ====== Forum Title ====== */

/**
 * Displays the single forum title.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $prefix
 * @param  bool    $echo
 * @return string
 */
function mb_single_forum_title( $prefix = '', $echo = true ) {
	$title = apply_filters( 'mb_single_forum_title', single_post_title( $prefix, false ) );

	if ( false === $echo )
		return $title;

	echo $title;
}

/**
 * Displays the forum title.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_title( $forum_id = 0 ) {
	echo mb_get_forum_title( $forum_id );
}

/**
 * Returns the forum title.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_title( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_title', mb_get_post_title( $forum_id ), $forum_id );
}

/* ====== Forum URL ====== */

/**
 * Displays the forum URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_url( $forum_id = 0 ) {
	echo mb_get_forum_url( $forum_id );
}

/**
 * Returns the forum URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_url( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_url', mb_get_post_url( $forum_id ), $forum_id );
}

/**
 * Displays the forum link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_link( $forum_id = 0 ) {
	echo mb_get_forum_link( $forum_id );
}

/**
 * Returns the forum link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_link( $forum_id = 0 ) {
	$url   = mb_get_forum_url(   $forum_id );
	$title = mb_get_forum_title( $forum_id );

	return apply_filters( 'mb_get_forum_link', sprintf( '<a class="forum-link" href="%s">%s</a>', $url, $title ), $forum_id );
}

/* ====== Forum Author ====== */

/**
 * Displays the forum author ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_id( $forum_id = 0 ) {
	echo mb_get_forum_author_id( $forum_id );
}

/**
 * Returns the forum autor ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int
 */
function mb_get_forum_author_id( $forum_id = 0 ) {
	$forum_id  = mb_get_forum_id( $forum_id );
	$author_id = get_post_field( 'post_author', $forum_id );

	return apply_filters( 'mb_get_forum_author_id', absint( $author_id ), $forum_id );
}

/**
 * Displays the forum author.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author( $forum_id = 0 ) {
	echo mb_get_forum_author( $forum_id );
}

/**
 * Returns the forum author.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_author', mb_get_post_author( $forum_id ), $forum_id );
}

/**
 * Displays the forum author profile URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_profile_url( $forum_id = 0 ) {
	echo mb_get_forum_author_profile_url( $forum_id );
}

/**
 * Returns the forum author profile URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author_profile_url( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_author_profile_url', mb_get_post_author_profile_url( $forum_id ), $forum_id );
}

/**
 * Displays the forum author profile link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_author_profile_link( $forum_id = 0 ) {
	echo mb_get_forum_author_profile_link( $forum_id );
}

/**
 * Returns the forum author profile link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_author_profile_link( $forum_id = 0 ) {
	return apply_filters( 'mb_get_forum_author_profile_link', mb_get_post_author_profile_link( $forum_id ), $forum_id );
}

/* ====== Last Activity ====== */

/**
 * Prints the forum last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_last_active_time( $forum_id = 0 ) {
	echo mb_get_forum_last_active_time( $forum_id );
}

/**
 * Returns the forum last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_last_active_time( $forum_id = 0 ) {

	$forum_id   = mb_get_forum_id( $forum_id );
	$time       = get_post_meta( $forum_id, mb_get_forum_activity_datetime_meta_key(), true );
	$mysql_date = mysql2date( 'U', $time );
	$now        = current_time( 'timestamp' );

	return apply_filters( 'mb_get_forum_last_active_time', human_time_diff( $mysql_date, $now ), $time, $forum_id );
}

/* ====== Last Reply ID ====== */

function mb_forum_last_reply_id( $forum_id = 0 ) {
	echo mb_get_forum_last_reply_id( $forum_id );
}

/**
 * Returns the last forum reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @retrn  int
 */
function mb_get_forum_last_reply_id( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$reply_id = get_post_meta( $forum_id, mb_get_forum_last_reply_id_meta_key(), true );

	$mb_reply_id = !empty( $reply_id ) && is_numeric( $reply_id ) ? absint( $reply_id ) : 0;

	return apply_filters( 'mb_get_forum_last_reply_id', $mb_reply_id, $forum_id );
}

/* ====== Last Post Author ====== */

/**
 * Displays the last post author for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_poster( $forum_id = 0 ) {
	echo mb_get_forum_last_poster( $forum_id );
}

/**
 * Returns the last post author for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_poster( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	$author = !empty( $reply_id ) ? mb_get_reply_author( $reply_id ) : mb_get_forum_author( $forum_id );

	return apply_filters( 'mb_get_forum_last_poster', $author, $reply_id, $forum_id );
}

/* ====== Last Post ID ====== */


function mb_forum_last_post_id( $forum_id ) {
	echo mb_get_forum_last_post_id( $forum_id );
}

function mb_get_forum_last_post_id( $forum_id ) {

	$topic_id = mb_get_forum_last_topic_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	return $reply_id > $topic_id ? $reply_id : $topic_id;
}


/* ====== Last Post URL ====== */

/**
 * Displays the last post URL for a forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return void
 */
function mb_forum_last_post_url( $forum_id = 0 ) {
	echo mb_get_forum_last_post_url( $forum_id );
}

/**
 * Returns a forum's last post URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return string
 */
function mb_get_forum_last_post_url( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$reply_id = mb_get_forum_last_reply_id( $forum_id );

	$url = !empty( $reply_id ) ? mb_get_reply_url( $reply_id ) : mb_get_post_jump_url( $forum_id );

	return apply_filters( 'mb_get_forum_last_post_url', $url, $reply_id, $forum_id );
}

/* ====== Last Topic ID ====== */

function mb_forum_last_topic_id( $forum_id ) {
	echo mb_get_forum_last_topic_id( $forum_id );
}

function mb_get_forum_last_topic_id( $forum_id ) {
	$topic_id = get_post_meta( $forum_id, mb_get_forum_last_topic_id_meta_key(), true );

	return !empty( $topic_id ) ? absint( $topic_id ) : 0;
}

/* ====== Subforums ====== */

function mb_is_subforum( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$forum = get_post( $forum_id );

	return 0 < $forum->post_parent ? true : false;
}

/* ====== Forum Counts ====== */

function mb_forum_topic_count( $forum_id = 0 ) {
	echo mb_get_forum_topic_count( $forum_id );
}

function mb_get_forum_topic_count( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );
	$count    = get_post_meta( $forum_id, mb_get_forum_topic_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_reset_forum_topic_count( $forum_id );

	return absint( $count );
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
	$count    = get_post_meta( $forum_id, mb_get_forum_reply_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_reset_forum_reply_count( $forum_id );

	return $count;
}

/* ====== Pagination ====== */

/**
 * Checks if viewing a paginated forum. Only for use on single forum pages.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_paged() {
	return mb_is_single_forum() && is_paged() ? true : false;
}

/**
 * Outputs pagination links for single topic pages (the replies are paginated).
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_forum_pagination( $args = array() ) {
	return mb_pagination( $args, message_board()->topic_query );
}

function mb_dropdown_forums( $args = array() ) {

	$defaults = array(
		'child_type'  => mb_get_forum_post_type(),
		'post_type'   => mb_get_forum_post_type(),
		'post_status' => array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ),
		'walker'      => new MB_Walker_Forum_Dropdown,
	);

	return wp_dropdown_pages( wp_parse_args( $args, $defaults ) );
}


class MB_Walker_Forum_Dropdown extends Walker_PageDropdown {

	/**
	 * @see Walker::start_el()
	 * @since 1.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page in reference to parent pages. Used for padding.
	 * @param array $args Uses 'selected' argument for selected page to set selected HTML attribute for option element.
	 * @param int $id
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $id = 0 ) {

		$forum_type = mb_get_forum_type_object( mb_get_forum_type( $page->ID ) );

		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"$page->ID\"";
		if ( $page->ID == $args['selected'] )
			$output .= ' selected="selected"';

		if ( mb_get_forum_post_type() !== $args['child_type'] && ( !in_array( $page->post_status, array( mb_get_open_post_status(), mb_get_publish_post_status() ) ) || false === $forum_type->topics_allowed ) )
			$output .= ' disabled="disabled"';
		$output .= '>';

		$title = $page->post_title;
		if ( '' === $title ) {
			$title = sprintf( __( '#%d (no title)' ), $page->ID );
		}

		/**
		 * Filter the page title when creating an HTML drop-down list of pages.
		 *
		 * @since 3.1.0
		 *
		 * @param string $title Page title.
		 * @param object $page  Page data object.
		 */
		$title = apply_filters( 'list_pages', $title, $page );
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}

/* ====== Forum Form ====== */

/**
 * Outputs the URL to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_form_url() {
	echo mb_get_forum_form_url();
}

/**
 * Returns the URL to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_form_url() {
	return apply_filters( 'mb_forum_form_url', esc_url( '#forum-form' ) );
}

/**
 * Outputs a link to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return void
 */
function mb_forum_form_link( $args = array() ) {
	echo mb_get_forum_form_link( $args );
}

/**
 * Returns a link to the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_get_forum_form_link( $args = array() ) {

	if ( !current_user_can( 'create_forums' ) )
		return '';

	$url  = mb_get_forum_form_url();
	$link = '';

	$defaults = array(
		'text' => __( 'New Forum &rarr;', 'message-board' ),
		'wrap' => '<a %s>%s</a>',
		'before' => '',
		'after' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( !empty( $url ) ) {

		$attr = sprintf( 'class="new-forum-link new-forum" href="%s"', $url );

		$link = sprintf( $args['before'] . $args['wrap'] . $args['after'], $attr, $args['text'] );
	}

	return apply_filters( 'mb_get_forum_form_link', $link, $args );
}

/**
 * Displays the new forum form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_form() {
	require_once( trailingslashit( message_board()->dir_path ) . 'templates/form-forum.php' );
}
