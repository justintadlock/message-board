<?php

/**
 * Creates a new topic query and checks if there are any topics found.  Note that we ue the main 
 * WordPress query if viewing the topic archive or a single topic.  This function is a wrapper 
 * function for the standard WP `have_posts()`, but this function should be used instead because 
 * it must also create a query of its own under some circumstances.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_topic_query() {
	$mb = message_board();

	/* If a query has already been created, let's roll. */
	if ( !is_null( $mb->topic_query->query ) ) {

		$have_posts = $mb->topic_query->have_posts();

		if ( empty( $have_posts ) )
			wp_reset_postdata();

		return $have_posts;
	}

	/* Use the main WP query when viewing a single topic or topic archive. */
	if ( is_singular( mb_get_topic_post_type() ) || is_archive( mb_get_topic_post_type() ) ) {
		global $wp_the_query;
		
		$mb->topic_query = $wp_the_query;
	}

	/* Create a new query if all else fails. */
	else {

		$per_page = mb_get_topics_per_page();

		$defaults = array(
			'post_type'           => mb_get_topic_post_type(),
			'posts_per_page'      => $per_page,
			'paged'               => get_query_var( 'paged' ),
			'orderby'             => 'menu_order',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		);

		if ( is_singular( mb_get_forum_post_type() ) ) {
			$defaults['post_parent'] = get_queried_object_id();
		}

		$mb->topic_query = new WP_Query( $defaults );
	}

	return $mb->topic_query->have_posts();
}

/**
 * Sets up the topic data for the current topic in The Loop.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_topic() {
	return message_board()->topic_query->the_post();
}

/* ====== Lead Topic ====== */

/**
 * Whether to show the topic when viewing a single topic page.  By default, the topic is shown 
 * on page #1, but it's not shown on subsequent pages if the topic is paginated.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_show_lead_topic() {
	return apply_filters( 'mb_show_lead_topic', is_paged() ? false : true );
}

/* ====== Topic Status ====== */

/**
 * Whether the topic is open to new replies.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_topic_open( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$status   = get_post_status( $topic_id );

	return apply_filters( 'mb_is_topic_open', in_array( $status, array( 'publish', 'inherit' ) ) ? true : false, $topic_id );
}

/* ====== Topic Labels ====== */

/**
 * Outputs a topics labels.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_labels( $topic_id = 0 ) {
	echo mb_get_topic_labels( $topic_id );
}

/**
 * Returns a topic's labels.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_labels( $topic_id = 0 ) {
	$topic_id       = mb_get_topic_id( $topic_id );
	$labels = array();

	if ( mb_is_topic_sticky( $topic_id ) )
		$labels['sticky'] = __( '[Sticky]', 'message-board' );

	$labels = apply_filters( 'mb_topic_labels', $labels, $topic_id );

	if ( !empty( $labels ) ) {

		$formatted = '';

		foreach ( $labels as $key => $value )
			$formatted .= sprintf( '<span class="topic-label %s">%s</span>', sanitize_html_class( "topic-label-{$key}" ), $value );

		return sprintf( '<span class="topic-labels">%s</span>', $formatted );
	}

	return '';
}

/* ====== Topic Sticky ====== */

/**
 * Checks if a topic is sticky.  Sticky topics are only sticky for their specific forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return bool
 */
function mb_is_topic_sticky( $topic_id = 0 ) {
	$topic_id       = mb_get_topic_id( $topic_id );
	$super_stickies = get_option( 'mb_super_sticky_topics', array() );
	$topic_stickies = get_option( 'mb_sticky_topics',       array() );
	$stickies       = array_merge( $super_stickies, $topic_stickies );

	return in_array( $topic_id, $stickies ) ? true : false;
}

/**
 * Checks if a topic is super sticky.  Super sticky topics are sticky on all forums as well as 
 * the topic archive page.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return bool
 */
function mb_is_topic_super_sticky( $topic_id = 0 ) {
	$topic_id       = mb_get_topic_id( $topic_id );
	$super_stickies = get_option( 'mb_super_sticky_topics', array() );

	return in_array( $topic_id, $super_stickies ) ? true : false;
}

/* ====== Topic ID ====== */

/**
 * Displays the topic ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_id( $topic_id = 0 ) {
	echo mb_get_topic_id( $topic_id );
}

/**
 * Returns the topic ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int
 */
function mb_get_topic_id( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_id', mb_get_post_id( $topic_id ), $topic_id );
}

/* ====== Topic Content ====== */

/**
 * Displays the topic content.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_content( $topic_id = 0 ) {
	echo mb_get_topic_content( $topic_id );
}

/**
 * Returns the topic content.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_content( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_content', mb_get_post_content( $topic_id ), $topic_id );
}

/* ====== Topic Title ====== */

/**
 * Displays the single topic title.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $prefix
 * @param  bool    $echo
 * @return string
 */
function mb_single_topic_title( $prefix = '', $echo = true ) {
	$title = apply_filters( 'mb_single_topic_title', single_post_title( $prefix, false ) );

	if ( false === $echo )
		return $title;

	echo $title;
}

/**
 * Displays the topic title.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_title( $topic_id = 0 ) {
	echo mb_get_topic_title( $topic_id );
}

/**
 * Returns the topic title.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_title( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_title', mb_get_post_title( $topic_id ), $topic_id );
}

/* ====== Topic URL ====== */

/**
 * Displays the topic URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_url( $topic_id = 0 ) {
	echo mb_get_topic_url( $topic_id );
}

/**
 * Returns the topic URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_url( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_url', mb_get_post_url( $topic_id ), $topic_id );
}

/**
 * Displays the topic link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_link( $topic_id = 0 ) {
	echo mb_get_topic_link( $topic_id );
}

/**
 * Returns the topic link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_link( $topic_id = 0 ) {
	$url   = mb_get_topic_url(   $topic_id );
	$title = mb_get_topic_title( $topic_id );

	return apply_filters( 'mb_get_topic_link', sprintf( '<a href="%s">%s</a>', $url, $title ), $topic_id );
}

/* ====== Topic Author ====== */

/**
 * Displays the topic author ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_author_id( $topic_id = 0 ) {
	echo mb_get_topic_author_id( $topic_id );
}

/**
 * Returns the topic autor ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int
 */
function mb_get_topic_author_id( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_id', mb_get_post_author_id( $topic_id ), $topic_id );
}

/**
 * Displays the topic author.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_author( $topic_id = 0 ) {
	echo mb_get_topic_author( $topic_id );
}

/**
 * Returns the topic author.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_author( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author', mb_get_post_author( $topic_id ), $topic_id );
}

/**
 * Displays the topic author profile URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_author_profile_url( $topic_id = 0 ) {
	echo mb_get_topic_author_profile_url( $topic_id );
}

/**
 * Returns the topic author profile URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_author_profile_url( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_profile_url', mb_get_post_author_profile_url( $topic_id ), $topic_id );
}

/**
 * Displays the topic author profile link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_author_profile_link( $topic_id = 0 ) {
	echo mb_get_topic_author_profile_link( $topic_id );
}

/**
 * Returns the topic author profile link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_author_profile_link( $topic_id = 0 ) {
	return apply_filters( 'mb_get_topic_author_profile_link', mb_get_post_author_profile_link( $topic_id ), $topic_id );
}

/* ====== Topic Forum ====== */

function mb_get_topic_forum_id( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	$forum_id = get_post( $topic_id )->post_parent;

	return apply_filters( 'mb_get_topic_forum_id', $forum_id, $topic_id );
}

function mb_topic_forum_link( $topic_id = 0 ) {
	echo mb_get_topic_forum_link( $topic_id );
}

function mb_get_topic_forum_link( $topic_id = 0 ) {
	$forum_id   = mb_get_topic_forum_id( $topic_id );
	$forum_link = mb_get_forum_link( $forum_id );

	return apply_filters( 'mb_get_topic_forum_link', $forum_link, $forum_id, $topic_id );
}

/* ====== Last Activity ====== */

/**
 * Prints the topic last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_last_active_time( $topic_id = 0 ) {
	echo mb_get_topic_last_active_time( $topic_id );
}

/**
 * Returns the topic last activity time.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_last_active_time( $topic_id = 0 ) {

	$topic_id   = mb_get_topic_id( $topic_id );
	$time       = get_post_meta( $topic_id, '_topic_activity_datetime', true );
	$mysql_date = mysql2date( 'U', $time );
	$now        = current_time( 'timestamp' );

	return apply_filters( 'mb_get_topic_last_active_time', human_time_diff( $mysql_date, $now ), $time, $topic_id );
}

/* ====== Last Reply ID ====== */

function mb_topic_last_reply_id( $topic_id = 0 ) {
	echo mb_get_topic_last_reply_id( $topic_id );
}

/**
 * Returns the last topic reply ID.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @retrn  int
 */
function mb_get_topic_last_reply_id( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$reply_id = get_post_meta( $topic_id, '_topic_last_reply_id', true );

	$mb_reply_id = !empty( $reply_id ) && is_numeric( $reply_id ) ? absint( $reply_id ) : 0;

	return apply_filters( 'mb_get_topic_last_reply_id', $mb_reply_id, $topic_id );
}

/* ====== Last Post Author ====== */

/**
 * Displays the last post author for a topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_last_poster( $topic_id = 0 ) {
	echo mb_get_topic_last_poster( $topic_id );
}

/**
 * Returns the last post author for a topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_last_poster( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$reply_id = mb_get_topic_last_reply_id( $topic_id );

	$author = !empty( $reply_id ) ? mb_get_reply_author( $reply_id ) : mb_get_topic_author( $topic_id );

	return apply_filters( 'mb_get_topic_last_poster', $author, $reply_id, $topic_id );
}

/* ====== Last Post URL ====== */

/**
 * Displays the last post URL for a topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_last_post_url( $topic_id = 0 ) {
	echo mb_get_topic_last_post_url( $topic_id );
}

/**
 * Returns a topic's last post URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_last_post_url( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$reply_id = mb_get_topic_last_reply_id( $topic_id );

	$url = !empty( $reply_id ) ? mb_get_reply_url( $reply_id ) : mb_get_post_jump_url( $topic_id );

	return apply_filters( 'mb_get_topic_last_post_url', $url, $reply_id, $topic_id );
}

/* ====== Post/Reply Count ====== */

/**
 * Displays the topic reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_reply_count( $topic_id = 0 ) {
	echo mb_get_topic_reply_count( $topic_id );
}

/**
 * Returns the topic reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_reply_count( $topic_id = 0 ) {
	$topic_id    = mb_get_topic_id( $topic_id );
	$reply_count = get_post_meta( $topic_id, '_topic_reply_count', true );

	return apply_filters( 'mb_get_topic_reply_count', absint( $reply_count ), $topic_id );
}

/**
 * Displays the topic post count (topic + reply count).
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_post_count( $topic_id = 0 ) {
	echo mb_get_topic_post_count( $topic_id );
}

/**
 * Returns the topic post count (topic + reply count).
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_post_count( $topic_id = 0 ) {
	$post_count = 1 + mb_get_topic_reply_count( $topic_id );

	return apply_filters( 'mb_get_topic_post_count', $post_count, $topic_id );
}

/* ====== Topic Voices ====== */

/**
 * Displays the topic voice count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_voice_count( $topic_id = 0 ) {
	echo mb_get_topic_voice_count( $topic_id );
}

/**
 * Retuurns the topic voice count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int
 */
function mb_get_topic_voice_count( $topic_id = 0 ) {
	$topic_id     = mb_get_topic_id( $topic_id );
	$voice_count  = get_post_meta( $topic_id, '_topic_voice_count', true );

	$voice_count = $voice_count ? absint( $voice_count ) : count( mb_get_topic_voices( $topic_id ) );

	return apply_filters( 'mb_get_topic_voice_count', $voice_count, $topic_id );
}

/**
 * Returns an array of user IDs (topic voices).
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return array
 */
function mb_get_topic_voices( $topic_id = 0 ) {
	$topic_id     = mb_get_topic_id( $topic_id );
	$topic_voices = get_post_meta( $topic_id, '_topic_voices' );

	$voices = !empty( $voices ) ? $voices : array( mb_get_topic_author_id( $topic_id ) );

	return apply_filters( 'mb_get_topic_voices', $voices, $topic_id );
}

/* ====== Pagination ====== */

/**
 * Checks if viewing a paginated topic. Only for use on single topic pages.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_topic_paged() {

	if ( !is_singular( mb_get_topic_post_type() ) )
		return false;

	return is_paged() ? true : false;
}

/**
 * Outputs pagination links for single topic pages (the replies are paginated).
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @global object $wp_rewrite
 * @return string
 */
function mb_topic_pagination( $args = array() ) {
	global $wp_rewrite;

	$query = message_board()->reply_query;

	/* If there's not more than one page, return nothing. */
	if ( 1 >= $query->max_num_pages )
		return;

	/* Get the current page. */
	$current = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

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

/* ====== Topic Form ====== */

/**
 * Outputs the URL to the new topic form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_form_url() {
	echo mb_get_topic_form_url();
}

/**
 * Returns the URL to the new topic form.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_form_url() {
	return apply_filters( 'mb_topic_form_url', esc_url( '#topic-form' ) );
}

/**
 * Outputs a link to the new topic form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return void
 */
function mb_topic_form_link( $args = array() ) {
	echo mb_get_topic_form_link( $args );
}

/**
 * Returns a link to the new topic form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_get_topic_form_link( $args = array() ) {

	if ( !current_user_can( 'create_forum_topics' ) )
		return '';

	$defaults = array(
		'text' => __( 'New Topic &rarr;', 'message-board' ),
		'wrap' => '<a %s>%s</a>',
		'before' => '',
		'after' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$attr = sprintf( 'class="new-topic-link new-topic" href="%s"', mb_get_topic_form_url() );

	$link = sprintf( $args['before'] . $args['wrap'] . $args['after'], $attr, $args['text'] );

	return apply_filters( 'mb_get_topic_form_link', $link, $args );
}

/**
 * Displays the new topic form.
 *
 * @todo Set up system of hooks.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_form() {

	if ( !current_user_can( 'create_forum_topics' ) )
		return; 

	$form  = sprintf( '<form id="topic-form" method="post" action="%s">', mb_get_topic_form_action_url() );
	$form .= '<fieldset>';
	$form .= sprintf( '<legend>%s</legend>', __( 'Add New Topic', 'message-board' ) );

	// title field
	$default_fields['title']  = '<p>';
	$default_fields['title'] .= sprintf( '<label for="mb_topic_title">%s</label>', __( 'Topic title: (be brief and descriptive)', 'message-board' ) );
	$default_fields['title'] .= '<input type="text" id="mb_topic_title" name="mb_topic_title" />';
	$default_fields['title'] .= '</p>';

	// forum field
	if ( !is_singular( mb_get_forum_post_type() ) ) {
		$default_fields['forum'] = '<p>';
		$default_fields['forum'] .= sprintf( '<label for="mb_topic_forum">%s</label>', __( 'Select a forum:', 'message-board' ) );

		$default_fields['forum'] .= wp_dropdown_pages(
			array(
				'post_type' => mb_get_forum_post_type(),
				'name'      => 'mb_topic_forum',
				'id'        => 'mb_topic_forum',
				'echo'      => false,
			)
		);
		$default_fields['forum'] .= '</select>';
		$default_fields['forum'] .= '</p>';
	}

	// content field
	$default_fields['content']  = '<p>';
	$default_fields['content'] .= sprintf( '<label for="mb_topic_content" name="mb_topic_content">%s</label>', __( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ) );
	$default_fields['content'] .= '<textarea id="mb_topic_content" name="mb_topic_content"></textarea>';
	$default_fields['content'] .= '</p>';

	$default_fields = apply_filters( 'mb_topic_form_fields', $default_fields );

	foreach ( $default_fields as $key => $field ) {
		$form .= $field;
	}

	if ( is_singular( mb_get_forum_post_type() ) ) {
		$form .= sprintf( '<input type="hidden" name="mb_topic_forum" value="%s" />', absint( get_queried_object_id() ) );
	}

	$form .= sprintf( '<p><input type="submit" value="%s" /></p>', esc_attr__( 'Submit', 'message-board' ) );

	$form .= sprintf( '<p><label><input type="checkbox" name="mb_topic_subscribe" value="1" /> %s</label></p>', __( 'Notify me of follow-up posts via email', 'message-board' ) );

	$form .= wp_nonce_field( 'mb_new_topic_action', 'mb_new_topic_nonce', false, false );
	$form .= '</fieldset>';
	$form .= '</form>';

	echo $form;
}

/**
 * Displays the topic form action URL
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_form_action_url() {
	echo mb_get_topic_form_action_url();
}

/**
 * Returns the topic form action URL.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_form_action_url() {
	return esc_url( add_query_arg( 'message-board', 'new-topic', trailingslashit( home_url() ) ) );
}

/* ====== Topic Subscriptions ====== */

/**
 * Displays the topic subscribe URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_topic_subscribe_url( $topic_id = 0 ) {
	echo mb_get_topic_subscribe_url( $topic_id );
}

/**
 * Returns the topic subscribe URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_subscribe_url( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'subscribe', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_subscribe_url', $url, $topic_id );
}

/**
 * Displays the topic unsubscribe URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_unsubscribe_url( $topic_id = 0 ) {
	echo mb_get_topic_unsubscribe_url( $topic_id );
}

/**
 * Returns the topic unsubscribe URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_unsubscribe_url( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'unsubscribe', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_unsubscribe_url', $url, $topic_id );
}

/**
 * Displays the topic un/subscribe link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_subscribe_link( $topic_id = 0 ) {
	echo mb_get_topic_subscribe_link( $topic_id );
}

/**
 * Returns the topic un/subscribe link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_subscribe_link( $topic_id = 0 ) {

	$topic_id = mb_get_topic_id( $topic_id );

	if ( !mb_is_user_subscribed_topic( get_current_user_id(), $topic_id ) ) {

		$link = sprintf( 
			'<a class="subscribe-link" href="%s">%s</a>', 
			mb_get_topic_subscribe_url( $topic_id ), 
			__( 'Subscribe', 'message-board' ) 
		);

	} else {
		$link = sprintf( 
			'<a class="subscribe-link" href="%s">%s</a>', 
			mb_get_topic_unsubscribe_url( $topic_id ),
			__( 'Unsubscribe', 'message-board' ) 
		);
	}

	return $link;
}

/**
 * Checks if the user is subscribed to the topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  int     $topic_id
 * @return bool
 */
function mb_is_user_subscribed_topic( $user_id = 0, $topic_id = 0 ) {

	$user_id  = 0 < $user_id ? $user_id : get_current_user_id();
	$topic_id = mb_get_topic_id( $topic_id );

	$subs = mb_get_user_subscriptions( $user_id );

	return in_array( $topic_id, $subs ) ? true : false;
}

/* ====== Topic Favorites ====== */

/**
 * Displays the topic favorite URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_favorite_url( $topic_id = 0 ) {
	echo mb_get_topic_favorite_url( $topic_id );
}

/**
 * Returns the topic favorite URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_favorite_url( $topic_id = 0 ) {

	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'favorite', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_favorite_url', $url, $topic_id );
}

/**
 * Displays the topic unfavorite URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_unfavorite_url( $topic_id = 0 ) {
	echo mb_get_topic_unfavorite_url( $topic_id );
}

/**
 * Returns the topic unfavorite URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_unfavorite_url( $topic_id = 0 ) {

	$topic_id = mb_get_topic_id( $topic_id );
	$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$url = esc_url( add_query_arg( array( 'action' => 'unfavorite', 'topic_id' => $topic_id, 'redirect' => $redirect ), trailingslashit( home_url( 'board' ) ) ) );

	return apply_filters( 'mb_get_topic_unfavorite_url', $url, $topic_id );
}

/**
 * Displays the topic un/favorite link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return void
 */
function mb_topic_favorite_link( $topic_id = 0 ) {
	echo mb_get_topic_favorite_link( $topic_id );
}

/**
 * Returns the topic un/favorite link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return string
 */
function mb_get_topic_favorite_link( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( !mb_is_user_favorite_topic( get_current_user_id(), $topic_id ) ) {
		$link = sprintf( 
			'<a class="favorite-link" href="%s">%s</a>', 
			mb_get_topic_favorite_url( $topic_id ), 
			__( 'Favorite', 'message-board' ) 
		);
	}
	else {
		$link = sprintf( 
			'<a class="favorite-link" href="%s">%s</a>', 
			mb_get_topic_unfavorite_url( $topic_id ), 
			__( 'Unfavorite', 'message-board' ) 
		);
	}

	return $link;
}

/**
 * Checks if the topic is one of the user's favorites.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  int     $topic_id
 * @return bool
 */
function mb_is_user_favorite_topic( $user_id = 0, $topic_id = 0 ) {

	$user_id  = 0 < $user_id ? $user_id : get_current_user_id();
	$topic_id = mb_get_topic_id( $topic_id );

	$favorites = get_user_meta( $user_id, '_topic_favorites', true );

	$favs = explode( ',', $favorites );

	return in_array( $topic_id, $favs ) ? true : false;
}
