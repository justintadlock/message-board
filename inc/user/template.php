<?php
/**
 * Template functions for user-related functionality.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* ====== User Query ====== */

/**
 * Creates a new `WP_User` query.  This function is modeled after the WordPress posts query so that theme 
 * authors have an easier time grasping it.  Normally, you would use a `foreach` loop and so on, but 
 * because we're modeling this after the posts query, you'd use a while loop.  This also allows us to 
 * set up the ID of the current user in the loop behind the scenes so that anything using `mb_get_user_id()`
 * will automatically work.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_user_query() {
	global $_mb_user_query_current;

	$mb = message_board();

	/* If a query has already been created, let's roll. */
	if ( !is_null( $mb->user_query ) ) {

		if ( $_mb_user_query_current + 1 < count( $mb->user_query->results ) )
			return true;

		$_mb_user_query_current = -1;

		return false;
	}

	$page = is_paged() ? absint( get_query_var( 'paged' ) ) : 1;

	$offset = 1 === $page ? '' : ( $page - 1 ) * mb_get_users_per_page();

	$mb->user_query = new WP_User_Query(
		array(
			'orderby'      => 'login',
			'order'        => 'ASC',
			'offset'       => $offset,
			'role'         => mb_is_single_role() ? sanitize_key( get_query_var( 'mb_role' ) ) : '',
			'search'       => '',
			'number'       => mb_get_users_per_page(),
			'count_total'  => true,
			'fields'       => 'all',
			'who'          => ''
		)
	);

	$_mb_user_query_current = -1;

	return !empty( $mb->user_query->results ) ? true : false;
}

/**
 * Sets up the user data.  Basically, this function bumps the user in the `mb_user_query()` loop to the 
 * next user.  It also sets the current user ID in the loop so that `mb_get_user_id()` will return the 
 * correct user.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_user() {
	global $_mb_user_query_current;

	$mb = message_board();

	$current = $_mb_user_query_current++;

	//$mb->user_query->loop_user_id = $mb->user_query->results[ $current ]->ID;
}

/* ====== User ID ====== */

/**
 * Displays the ID of the user.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_id( $user_id = 0 ) {
	echo mb_get_user_id( $user_id );
}

/**
 * Returns the ID of the user.  The function assumes that you're looking for a user ID within the context 
 * of a user loop or a specific user page.  If not, it will assume you're looking for the currently-logged 
 * in user.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return int
 */
function mb_get_user_id( $user_id = 0 ) {
	global $_mb_user_query_current;

	/* If the numeric and greater than 0, we already have an ID. */
	if ( is_numeric( $user_id ) && 0 < $user_id )
		$user_id = $user_id;

	/* If within a user loop, use the current user's ID in the loop. */
	elseif ( !is_null( message_board()->user_query ) && isset( message_board()->user_query->results[ $_mb_user_query_current ] ) )
		$user_id = message_board()->user_query->results[ $_mb_user_query_current ]->ID;

	/* If viewing an user page, use that ID. */
	elseif ( get_query_var( 'author' ) )
		$user_id = get_query_var( 'author' );

	/* If the `user_id` query var is set, use it. */
	elseif ( get_query_var( 'user_id' ) )
		$user_id = get_query_var( 'user_id' );

	/* Else, assume we're looking for the currently-logged in user. */
	else
		$user_id  = get_current_user_id();

	/* Return the user ID. */
	return apply_filters( 'mb_get_user_id', absint( $user_id ) );
}

/* ====== Conditionals ====== */

/**
 * Checks if viewing the user archive page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_user_archive() {
	$is_user_archive = get_query_var( 'mb_custom' ) && 'users' === get_query_var( 'mb_custom' ) ? true : false;

	return apply_filters( 'mb_is_user_archive', $is_user_archive && !is_author() ? true : false );
}

/**
 * Checks if viewing a single user page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_single_user() {
	$is_user_archive = get_query_var( 'mb_custom' ) && 'users' === get_query_var( 'mb_custom' ) ? true : false;

	return apply_filters( 'mb_is_single_user', $is_user_archive && is_author() ? true : false );
}

/**
 * Conditional check to see if we're viewing a user page.  User pages are sub-pages of the single user 
 * page, which show things like topics, forums, replies, etc.
 *
 * @since  1.0.0
 * @access public
 * @param  string|array  $page
 * @return bool
 */
function mb_is_user_page( $page = '' ) {

	if ( !mb_is_single_user() )
		return false;

	elseif ( empty( $page ) && get_query_var( 'mb_user_page' ) )
		return true;

	if ( !empty( $page ) ) {
		foreach ( (array) $page as $_p ) {

			if ( get_query_var( 'mb_user_page' ) === $_p )
				return true;
		}
	}

	return false;
}

function mb_is_user_forums() {
	$is_forums = mb_is_single_user() && 'forums' === get_query_var( 'mb_user_page' ) ? true : false;

	return apply_filters( 'mb_is_user_forums', $is_forums );
}

function mb_is_user_topics() {
	$is_topics = mb_is_single_user() && 'topics' === get_query_var( 'mb_user_page' ) ? true : false;

	return apply_filters( 'mb_is_user_topics', $is_topics );
}

function mb_is_user_replies() {
	$is_replies = mb_is_single_user() && 'replies' === get_query_var( 'mb_user_page' ) ? true : false;

	return apply_filters( 'mb_is_user_replies', $is_replies );
}

function mb_is_user_bookmarks() {
	$is_bookmarks = mb_is_single_user() && 'bookmarks' === get_query_var( 'mb_user_page' ) ? true : false;

	return apply_filters( 'mb_is_user_bookmarks', $is_bookmarks );
}

function mb_is_user_forum_subscriptions() {
	$is_forum_subscriptions = mb_is_single_user() && 'forum-subscriptions' === get_query_var( 'mb_user_page' ) ? true : false;

	return apply_filters( 'mb_is_user_forum_subscriptions', $is_forum_subscriptions );
}

function mb_is_user_topic_subscriptions() {
	$is_topic_subscriptions = mb_is_single_user() && 'topic-subscriptions' === get_query_var( 'mb_user_page' ) ? true : false;

	return apply_filters( 'mb_is_user_topic_subscriptions', $is_topic_subscriptions );
}

/**
 * Checks if viewing an edit user page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_user_edit() {
	$is_user_edit = mb_is_edit() && is_numeric( get_query_var( 'user_id' ) ) ? true : false;

	return apply_filters( 'mb_is_user_edit', $is_user_edit );
}

/**
 * Checks if viewing an edit user page that is the current user's profile.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_user_profile_edit() {
	$is_profile_edit = mb_is_user_edit() && mb_get_user_id() === get_current_user_id() ? true : false;

	return apply_filters( 'mb_is_user_profile_edit', $is_profile_edit );
}

/* ====== Titles ====== */

/**
 * Displays the user archive title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_user_archive_title() {
	echo mb_get_user_archive_title();
}

/**
 * Returns the user archive title.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_archive_title() {
	return apply_filters( 'mb_get_user_archive_title', __( 'Users', 'message-board' ) );
}

/**
 * Displays a single user title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_single_user_title() {
	echo mb_get_single_user_title();
}

/**
 * Returns a single user title.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_single_user_title() {
	return apply_filters( 'mb_get_single_user_title', get_the_author_meta( 'display_name', mb_get_user_id() ) );
}

/**
 * Displays the user page title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_user_page_title() {
	echo mb_get_user_page_title();
}

/**
 * Returns the user page title.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_get_user_page_title() {

	if ( mb_is_user_forums() )
		$title = mb_get_user_forums_title();

	elseif ( mb_is_user_topics() )
		$title = mb_get_user_topics_title();

	elseif ( mb_is_user_replies() )
		$title = mb_get_user_replies_title();

	elseif ( mb_is_user_forum_subscriptions() )
		$title = mb_get_user_forum_subscriptions_title();

	elseif ( mb_is_user_topic_subscriptions() )
		$title = mb_get_user_topic_subscriptions_title();

	elseif ( mb_is_user_bookmarks() )
		$title = mb_get_user_bookmarks_title();

	else
		$title = mb_get_single_user_title();

	return apply_filters( 'mb_get_user_page_title', $title );
}

function mb_user_forums_title() {
	echo mb_get_user_forums_title();
}

function mb_get_user_forums_title() {
	return apply_filters( 'mb_get_user_forums_title', mb_get_forum_label( 'name' ) );
}

function mb_user_topics_title() {
	echo mb_get_user_topics_title();
}

function mb_get_user_topics_title() {
	return apply_filters( 'mb_get_user_topics_title', mb_get_topic_label( 'name' ) );
}

function mb_user_replies_title() {
	echo mb_get_user_replies_title();
}

function mb_get_user_replies_title() {
	return apply_filters( 'mb_get_user_replies_title', mb_get_reply_label( 'name' ) );
}

function mb_user_bookmarks_title() {
	echo mb_get_user_bookmarks_title();
}

function mb_get_user_bookmarks_title() {
	return apply_filters( 'mb_get_user_bookmarks_title', __( 'Bookmarks', 'message-board' ) );
}

function mb_user_forum_subscriptions_title() {
	echo mb_get_user_forum_subscriptions_title();
}

function mb_get_user_forum_subscriptions_title() {
	return apply_filters( 'mb_get_user_forum_subscriptions_title', sprintf( __( '%s Subscriptions', 'message-board' ), mb_get_forum_label( 'singular_name' ) ) );
}

function mb_user_topic_subscriptions_title() {
	echo mb_get_user_topic_subscriptions_title();
}

function mb_get_user_topic_subscriptions_title() {
	return apply_filters( 'mb_get_user_topic_subscriptions_title', sprintf( __( '%s Subscriptions', 'message-board' ), mb_get_topic_label( 'singular_name' ) ) );
}

/* ====== URLs / Links ====== */

/**
 * Displays the user archive URL.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_user_archive_url() {
	echo mb_get_user_archive_url();
}

/**
 * Returns the user archive URL.
 *
 * @since  1.0.0
 * @access public
 * @global object  $wp_rewrite
 * @return string
 */
function mb_get_user_archive_url() {
	global $wp_rewrite;

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( home_url( mb_get_user_slug() ) );
	else
		$url = add_query_arg( 'mb_custom', 'users', home_url() );

	return apply_filters( 'mb_get_user_archive_url', $url );
}

/**
 * Displays the user archive link.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_user_archive_link() {
	echo mb_get_user_archive_link();
}

/**
 * Returns the user archive link.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_get_user_archive_link() {

	$link = sprintf( '<a class="mb-user-archive-link" href="%s">%s</a>', mb_get_user_archive_url(), __( 'Users', 'message-board' ) );

	return apply_filters( 'mb_get_user_archive_link', $link );
}

/**
 * Displays a user edit URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_edit_url( $user_id = 0 ) {
	echo mb_get_user_edit_url( $user_id );
}

/**
 * Returns a user edit URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_edit_url( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	return apply_filters( 'mb_get_user_edit_url', get_edit_user_link( $user_id ), $user_id );
}

/**
 * Displays a user edit link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_edit_link( $user_id = 0 ) {
	echo mb_get_user_edit_link( $user_id );
}

/**
 * Returns a user edit link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_edit_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = '';

	if ( current_user_can( 'edit_user', $user_id ) ) {
		$url  = mb_get_user_edit_url( $user_id );

		if ( !empty( $url ) )
			$link = sprintf( '<a href="%s" class="mb-user-edit-link">%s</a>', $url, __( 'Edit', 'message-board' ) );
	}

	return apply_filters( 'mb_get_user_edit_link', $link, $user_id );
}

/**
 * Displays a single user URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_url( $user_id = 0 ) {
	echo mb_get_user_url( $user_id );
}

/**
 * Returns a single user URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return string
 */
function mb_get_user_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id  = mb_get_user_id( $user_id );
	$nicename = get_the_author_meta( 'user_nicename', $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( home_url( mb_get_user_slug() ) ) . $nicename );
	else
		$url = add_query_arg( array( 'mb_custom' => 'users', 'author_name' => $nicename ), home_url() );

	return apply_filters( 'mb_user_url', $url, $user_id );
}

/**
 * Displays a single user link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_link( $user_id = 0 ) {
	echo mb_get_user_link( $user_id );
}

/**
 * Returns a single user link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-link" href="%s">%s</a>', mb_get_user_url( $user_id ), get_the_author_meta( 'display_name', $user_id ) );

	return apply_filters( 'mb_get_user_link', $link, $user_id );
}

/**
 * Displays a single user topics URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_topics_url( $user_id = 0 ) {
	echo mb_get_user_topics_url( $user_id );
}

/**
 * Returns a single user topics URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return string
 */
function mb_get_user_topics_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id = mb_get_user_id( $user_id );
	$url     = mb_get_user_url( $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( $url ) . 'topics' );
	else
		$url = add_query_arg( array( 'mb_user_page' => 'topics' ), $url );

	return apply_filters( 'mb_user_topics_url', $url, $user_id );
}

/**
 * Displays a single user topics link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_topics_link( $user_id = 0 ) {
	echo mb_get_user_topics_link( $user_id );
}

/**
 * Returns a single user topics link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_topics_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-topics-link" href="%s">%s</a>', mb_get_user_topics_url( $user_id ), __( 'Topics', 'message-board' ) );

	return apply_filters( 'mb_get_user_topics_link', $link, $user_id );
}

/**
 * Displays a single user forums URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_forums_url( $user_id = 0 ) {
	echo mb_get_user_forums_url( $user_id );
}

/**
 * Returns a single user forums URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return void
 */
function mb_get_user_forums_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id = mb_get_user_id( $user_id );
	$url     = mb_get_user_url( $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( $url ) . 'forums' );
	else
		$url = add_query_arg( array( 'mb_user_page' => 'forums' ), $url );

	return apply_filters( 'mb_user_forums_url', $url, $user_id );
}

/**
 * Displays a single user forums link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_forums_link( $user_id = 0 ) {
	echo mb_get_user_forums_link( $user_id );
}

/**
 * Returns a single user forums link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_forums_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-forums-link" href="%s">%s</a>', mb_get_user_forums_url( $user_id ), __( 'Forums', 'message-board' ) );

	return apply_filters( 'mb_get_user_forums_link', $link, $user_id );
}

/**
 * Displays a single user replies URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_replies_url( $user_id = 0 ) {
	echo mb_get_user_replies_url( $user_id );
}

/**
 * Returns a single user replies URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return void
 */
function mb_get_user_replies_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id = mb_get_user_id( $user_id );
	$url     = mb_get_user_url( $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( $url ) . 'replies' );
	else
		$url = add_query_arg( array( 'mb_user_page' => 'replies' ), $url );

	return apply_filters( 'mb_user_replies_url', $url, $user_id );
}

/**
 * Displays a single user replies link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_replies_link( $user_id = 0 ) {
	echo mb_get_user_replies_link( $user_id );
}

/**
 * Returns a single user replies link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_replies_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-replies-link" href="%s">%s</a>', mb_get_user_replies_url( $user_id ), __( 'Replies', 'message-board' ) );

	return apply_filters( 'mb_get_user_replies_link', $link, $user_id );
}

/**
 * Displays a single user bookmarks URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_bookmarks_url( $user_id = 0 ) {
	echo mb_get_user_bookmarks_url( $user_id );
}

/**
 * Returns a single user bookmarks URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return string
 */
function mb_get_user_bookmarks_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id = mb_get_user_id( $user_id );
	$url     = mb_get_user_url( $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( $url ) . 'bookmarks' );
	else
		$url = add_query_arg( array( 'mb_user_page' => 'bookmarks' ), $url );

	return apply_filters( 'mb_user_bookmarks_url', $url, $user_id );
}

/**
 * Displays a single user bookmarks link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_bookmarks_link( $user_id = 0 ) {
	echo mb_get_user_bookmarks_link( $user_id );
}

/**
 * Returns a single user bookmarks link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_bookmarks_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-bookmarks-link" href="%s">%s</a>', mb_get_user_bookmarks_url( $user_id ), __( 'Bookmarks', 'message-board' ) );

	return apply_filters( 'mb_get_user_bookmarks_link', $link, $user_id );
}

/**
 * Displays a single user topic subscriptions URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_topic_subscriptions_url( $user_id = 0 ) {
	echo mb_get_user_topic_subscriptions_url( $user_id );
}

/**
 * Returns a single user topic subscriptions URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return string
 */
function mb_get_user_topic_subscriptions_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id = mb_get_user_id( $user_id );
	$url     = mb_get_user_url( $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( $url ) . 'topic-subscriptions' );
	else
		$url = add_query_arg( array( 'mb_user_page' => 'topic-subscriptions' ), $url );

	return apply_filters( 'mb_user_topic_subscriptions_url', $url, $user_id );
}

/**
 * Displays a single user topic subscriptions link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_topic_subscriptions_link( $user_id = 0 ) {
	echo mb_get_user_topic_subscriptions_link( $user_id );
}

/**
 * Returns a single user topic subscriptions link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_topic_subscriptions_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-topic-subscriptions-link" href="%s">%s</a>', mb_get_user_topic_subscriptions_url( $user_id ), __( 'Topic Subscriptions', 'message-board' ) );

	return apply_filters( 'mb_get_user_topic_subscriptions_link', $link, $user_id );
}

/**
 * Displays a single user forum subscriptions URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_forum_subscriptions_url( $user_id = 0 ) {
	echo mb_get_user_forum_subscriptions_url( $user_id );
}

/**
 * Returns a single user forum subscriptions URL.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @global object  $wp_rewrite
 * @return string
 */
function mb_get_user_forum_subscriptions_url( $user_id = 0 ) {
	global $wp_rewrite;

	$user_id = mb_get_user_id( $user_id );
	$url     = mb_get_user_url( $user_id );

	if ( $wp_rewrite->using_permalinks() )
		$url = user_trailingslashit( trailingslashit( $url ) . 'forum-subscriptions' );
	else
		$url = add_query_arg( array( 'mb_user_page' => 'forum-subscriptions' ), $url );

	return apply_filters( 'mb_user_forum_subscriptions_url', $url, $user_id );
}

/**
 * Displays a single user forum subscriptions link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_forum_subscriptions_link( $user_id = 0 ) {
	echo mb_get_user_forum_subscriptions_link( $user_id );
}

/**
 * Returns a single user forum subscriptions link.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_forum_subscriptions_link( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$link = sprintf( '<a class="mb-user-forum-subscriptions-link" href="%s">%s</a>', mb_get_user_forum_subscriptions_url( $user_id ), __( 'Forum Subscriptions', 'message-board' ) );

	return apply_filters( 'mb_get_user_forum_subscriptions_link', $link, $user_id );
}

/* ====== Counts ====== */

/**
 * Displays a user's forum count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_forum_count( $user_id = 0 ) {
	echo mb_get_user_forum_count();
}

/**
 * Returns a user's forum count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return int
 */
function mb_get_user_forum_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$count = get_user_meta( $user_id, mb_get_user_forum_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_set_user_forum_count( $user_id );

	$count = !empty( $count ) ? absint( $count ) : 0;

	return apply_filters( 'mb_get_user_forum_count', $count, $user_id );
}

/**
 * Displays a user's topic count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_topic_count( $user_id = 0 ) {
	echo mb_get_user_topic_count( $user_id );
}

/**
 * Returns a user's topic count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return int
 */
function mb_get_user_topic_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$count = get_user_meta( $user_id, mb_get_user_topic_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_set_user_topic_count( $user_id );

	$count = !empty( $count ) ? absint( $count ) : 0;

	return apply_filters( 'mb_get_user_topic_count', $count, $user_id );
}

/**
 * Displays a user's reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_reply_count( $user_id = 0 ) {
	echo mb_get_user_reply_count( $user_id );
}

/**
 * Returns a user's reply count.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return int
 */
function mb_get_user_reply_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$count = get_user_meta( $user_id, mb_get_user_reply_count_meta_key(), true );

	if ( '' === $count )
		$count = mb_set_user_reply_count( $user_id );

	$count = !empty( $count ) ? absint( $count ) : 0;

	return apply_filters( 'mb_get_user_reply_count', $count, $user_id );
}

/**
 * Displays a user's post count (topics + replies).
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_post_count( $user_id = 0 ) {
	echo mb_get_user_post_count();
}

/**
 * Returns a user's post count (topics + replies).
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return int
 */
function mb_get_user_post_count( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );

	$topic_count = mb_get_user_topic_count( $user_id );
	$reply_count = mb_get_user_reply_count( $user_id );

	$count = $topic_count + $reply_count;

	return apply_filters( 'mb_get_user_post_count', $count, $user_id );
}

/* ====== Pagination ====== */

/**
 * Pagination for the user loop.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string|void
 */
function mb_loop_user_pagination( $args = array() ) {
	$total_users = message_board()->user_query->total_users;
	$max_pages   = ceil( $total_users / mb_get_users_per_page() );
	$query = array( 'max_num_pages' => $max_pages );
	return mb_pagination( $args, (object) $query );
}

/**
 * Displays the edit user form.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_user_edit_form() {
	mb_get_template_part( 'form-user', 'edit' );
}

/**
 * Returns an array of user contact methods.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_user_contact_methods() {

	$methods = array();

	return apply_filters( 'mb_get_user_contact_methods', $methods );
}
