<?php
/**
 * Sets up functions dealing with custom post types.  Registers post types.  Handles callbacks for some 
 * post-type related filters hooks.
 *
 * Note that the default post type names registered are `forum`, `forum_topic`, and `forum_reply`.  If 
 * coming from a different WordPress forum plugin, you can filter these names to be something that 
 * matched your old plugin.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register custom post types on the 'init' hook. */
add_action( 'init', 'mb_register_post_types' );

/* Filter post updated messages for custom post types. */
add_filter( 'post_updated_messages', 'mb_post_updated_messages' );

/* Filter the "enter title here" text. */
add_filter( 'enter_title_here', 'mb_enter_title_here', 10, 2 );

/**
 * Displays the forum post type.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_forum_post_type() {
	echo mb_get_forum_post_type();
}

/**
 * Returns the name of the "forum" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_post_type() {
	return apply_filters( 'mb_get_forum_post_type', 'forum' );
}

/**
 * Displays the topic post type.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_topic_post_type() {
	echo mb_get_topic_post_type();
}

/**
 * Returns the name of the "topic" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_post_type() {
	return apply_filters( 'mb_get_topic_post_type', 'topic' );
}

/**
 * Displays the reply post type.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_reply_post_type() {
	echo mb_get_reply_post_type();
}

/**
 * Returns the name of the "reply" post type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_reply_post_type() {
	return apply_filters( 'mb_get_reply_post_type', 'reply' );
}

/**
 * Registers post types needed by the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_types() {

	/* Get admin menu page. */
	$menu_page = mb_get_admin_menu_page();

	/* Get post type names. */
	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	/* Set up the arguments for the "forum" post type. */
	$forum_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => true,
		'show_ui'             => true,
		'show_in_menu'        => "edit.php?post_type={$forum_type}" === $menu_page ? true : false,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-format-chat',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => true,
		'has_archive'         => 'forums' === mb_get_show_on_front() ? mb_get_root_slug() : mb_get_forum_slug(),
		'query_var'           => $forum_type,
		'capability_type'     => 'forum',
		'map_meta_cap'        => true,
		'capabilities'        => mb_get_forum_capabilities(),

		'rewrite' => array(
			'slug'       => mb_get_forum_slug(),
			'with_front' => false,
			'pages'      => false,
			'feeds'      => true,
			'ep_mask'    => EP_PERMALINK,
		),

		'supports' => array(
			'title',
			'editor',
			'thumbnail'
		),

		'labels' => array(
			'name'               => __( 'Forums',                   'message-board' ),
			'singular_name'      => __( 'Forum',                    'message-board' ),
			'menu_name'          => __( 'Message Board',            'message-board' ),
			'name_admin_bar'     => __( 'Forum',                    'message-board' ),
			'all_items'          => __( 'Forums',                   'message-board' ),
			'add_new'            => __( 'Add Forum',                'message-board' ),
			'add_new_item'       => __( 'Add New Forum',            'message-board' ),
			'edit_item'          => __( 'Edit Forum',               'message-board' ),
			'new_item'           => __( 'New Forum',                'message-board' ),
			'view_item'          => __( 'View Forum',               'message-board' ),
			'search_items'       => __( 'Search Forums',            'message-board' ),
			'not_found'          => __( 'No forums found',          'message-board' ),
			'not_found_in_trash' => __( 'No forums found in trash', 'message-board' ),
			'parent_item_colon'  => __( 'Parent Forum:',            'message-board' ),

			/* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'      => __( 'Forums',                   'message-board' ),
			'mb_dashboard_count' => _n_noop( '%s Forum', '%s Forums', 'message-board' ),
			'mb_form_title'      => __( 'Forum Title:',             'message-board' ),
			'mb_form_type'       => __( 'Forum Type:',              'message-board' ),
			'mb_form_status'     => __( 'Status:',                  'message-board' ),
			'mb_form_order'      => __( 'Order:',                   'message-board' ),
			'mb_form_content'    => __( 'Description:',             'message-board' ),
			'mb_form_edit_item'  => __( 'Edit Forum: %s',           'message-board' ),
			'mb_form_title_placeholder' => __( 'Enter forum title',  'message-board' ),
			'mb_form_content_placeholder' => __( 'Enter forum description&hellip;', 'message-board' ),
			'mb_form_subscribe'  => __( 'Notify me of topics and replies via email', 'message-board' ),
		)
	);

	/* Set up the arguments for the "topic" post type. */
	$topic_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => true,
		'show_in_menu'        => "edit.php?post_type={$topic_type}" === $menu_page ? true : $menu_page,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-format-chat',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => 'topics' === mb_get_show_on_front() ? mb_get_root_slug() : mb_get_topic_slug(),
		'query_var'           => mb_get_topic_post_type(),
		'capability_type'     => $topic_type,
		'map_meta_cap'        => true,
		'capabilities'        => mb_get_topic_capabilities(),

		'rewrite' => array(
			'slug'       => mb_get_topic_slug(),
			'with_front' => false,
			'pages'      => false,
			'feeds'      => true,
			'ep_mask'    => EP_PERMALINK,
		),

		'supports' => array(
			'title',
			'editor',
		),

		'labels' => array(
			'name'               => __( 'Topics',                   'message-board' ),
			'singular_name'      => __( 'Topic',                    'message-board' ),
			'menu_name'          => __( 'Message Board',            'message-board' ),
			'name_admin_bar'     => __( 'Topic',                    'message-board' ),
			'all_items'          => __( 'Topics',                   'message-board' ),
			'add_new'            => __( 'Add Topic',                'message-board' ),
			'add_new_item'       => __( 'Add New Topic',            'message-board' ),
			'edit_item'          => __( 'Edit Topic',               'message-board' ),
			'new_item'           => __( 'New Topic',                'message-board' ),
			'view_item'          => __( 'View Topic',               'message-board' ),
			'search_items'       => __( 'Search Topics',            'message-board' ),
			'not_found'          => __( 'No topics found',          'message-board' ),
			'not_found_in_trash' => __( 'No topics found in trash', 'message-board' ),
			'parent_item_colon'  => __( 'Forum:',                   'message-board' ),

			/* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'      => __( 'Topics',                   'message-board' ),
			'mb_dashboard_count' => _n_noop( '%s Topic', '%s Topics', 'message-board' ),
			'mb_form_title'      => __( 'Topic Title:',             'message-board' ),
			'mb_form_type'       => __( 'Topic Type:',              'message-board' ),
			'mb_form_status'     => __( 'Status:',                  'message-board' ),
			'mb_form_content'    => __( 'Message:',                 'message-board' ),
			'mb_form_edit_item'  => __( 'Edit Topic: %s',           'message-board' ),
			'mb_form_title_placeholder' => __( 'Enter topic title',  'message-board' ),
			'mb_form_content_placeholder' => __( 'Enter topic message&hellip;', 'message-board' ),
			'mb_form_subscribe'  => __( 'Notify me of follow-up posts via email', 'message-board' ),
		)
	);

	/* Set up the arguments for the "reply" post type. */
	$reply_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => true,
		'show_in_menu'        => "edit.php?post_type={$reply_type}" === $menu_page ? true : $menu_page,
		'show_in_admin_bar'   => false,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-format-chat',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => mb_get_reply_slug(),
		'query_var'           => $reply_type,
		'capability_type'     => 'forum_reply',
		'map_meta_cap'        => true,
		'capabilities'        => mb_get_reply_capabilities(),

		'rewrite' => array(
			'slug'       => mb_get_reply_slug(),
			'with_front' => false,
			'pages'      => false,
			'feeds'      => false,
			'ep_mask'    => EP_PERMALINK,
		),

		'supports' => array(
			'editor'
		),

		'labels' => array(
			'name'               => __( 'Replies',                   'message-board' ),
			'singular_name'      => __( 'Reply',                     'message-board' ),
			'menu_name'          => __( 'Message Board',             'message-board' ),
			'name_admin_bar'     => __( 'Reply',                     'message-board' ),
			'all_items'          => __( 'Replies',                   'message-board' ),
			'add_new'            => __( 'Add Reply',                 'message-board' ),
			'add_new_item'       => __( 'Leave A Reply',             'message-board' ),
			'edit_item'          => __( 'Edit Reply',                'message-board' ),
			'new_item'           => __( 'New Reply',                 'message-board' ),
			'view_item'          => __( 'View Reply',                'message-board' ),
			'search_items'       => __( 'Search Replies',            'message-board' ),
			'not_found'          => __( 'No replies found',          'message-board' ),
			'not_found_in_trash' => __( 'No replies found in trash', 'message-board' ),
			'parent_item_colon'  => __( 'Topic:',                    'message-board' ),

			/* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'      => __( 'Replies',                   'message-board' ),
			'mb_dashboard_count' => _n_noop( '%s Reply', '%s Replies', 'message-board' ),
			'mb_form_content'    => __( 'Message:',                  'message-board' ),
			'mb_form_edit_item'  => __( 'Edit Reply: %s',           'message-board' ),
			'mb_form_content_placeholder' => __( 'Enter reply message&hellip;', 'message-board' ),
			'mb_form_subscribe'  => __( 'Notify me of follow-up posts via email', 'message-board' ),
		)
	);

	/* Register post types. */
	register_post_type( $forum_type, apply_filters( 'mb_forum_post_type_args', $forum_args ) );
	register_post_type( $topic_type, apply_filters( 'mb_topic_post_type_args', $topic_args ) );
	register_post_type( $reply_type, apply_filters( 'mb_reply_post_type_args', $reply_args ) );
}

/**
 * Returns the top-level menu page.  This function is needed because the WordPress admin function 
 * `user_can_access_admin_page()` returns an incorrect result for sub-menu pages of post types 
 * when the user doesn't have permission to view the top-level page.  What this function does is 
 * change the top-level menu based on what capability the user does have.
 *
 * @link https://core.trac.wordpress.org/ticket/29714
 * @link https://core.trac.wordpress.org/ticket/22895
 * @link https://core.trac.wordpress.org/ticket/16204
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_admin_menu_page() {

	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	/* Default page goes to the forum edit screen. */
	$menu_page = "edit.php?post_type={$forum_type}";

	/* If user can edit topics, use the topic edit screen. */
	if ( !current_user_can( 'edit_forums' ) && current_user_can( 'edit_topics' ) ) {
		$menu_page = "edit.php?post_type={$topic_type}";
	}

	/* Else, if user can edit replies, use the reply edit screen. */
	elseif ( !current_user_can( 'edit_forums' ) && current_user_can( 'edit_replies' ) ) {
		$menu_page = "edit.php?post_type={$topic_type}";
	}
	// @todo settings page if needed

	return $menu_page;
}

/**
 * Changes the post type name to the "common" name used within the plugin.  Because the post type names 
 * can be filtered, we need an easy way to track the common name.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $post_type
 * @return string
 */
function mb_translate_post_type( $post_type ) {

	$type = $post_type;

	if ( mb_get_forum_post_type() === $post_type )
		$type = 'forum';
	elseif ( mb_get_topic_post_type() === $post_type )
		$type = 'topic';
	elseif ( mb_get_reply_post_type() === $post_type )
		$type = 'reply';

	return apply_filters( 'mb_translate_post_type', $type, $post_type );
}

/**
 * Custom "enter title here" text.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @param  object  $post
 * @return string
 */
function mb_enter_title_here( $title, $post ) {

	if ( mb_is_forum( $post->ID ) )
		$title = mb_get_forum_label( 'mb_form_title_placeholder' );

	elseif ( mb_is_topic( $post->ID ) )
		$title = mb_get_topic_label( 'mb_form_title_placeholder' );

	return $title;
}

/**
 * Post updated messages in the admin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_post_updated_messages( $messages ) {
	global $post, $post_ID;

	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	$messages[ $forum_type ] = array(
		 0 => '', // Unused. Messages start at index 1.
		 1 => sprintf( __( 'Forum updated. <a href="%s">View forum</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 2 => '',
		 3 => '',
		 4 => __( 'Forum updated.', 'message-board' ),
		 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Forum restored to revision from %s', 'message-board' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		 6 => sprintf( __( 'Forum published. <a href="%s">View forum</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 7 => __( 'Forum saved.', 'message-board' ),
		 8 => '',
		 9 => '',
		10 => ''
	);

	$messages[ $topic_type ] = array(
		 0 => '', // Unused. Messages start at index 1.
		 1 => sprintf( __( 'Topic updated. <a href="%s">View topic</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 2 => '',
		 3 => '',
		 4 => __( 'Topic updated.', 'message-board' ),
		 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Topic restored to revision from %s', 'message-board' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		 6 => sprintf( __( 'Topic published. <a href="%s">View topic</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 7 => __( 'Topic saved.', 'message-board' ),
		 8 => '',
		 9 => '',
		10 => ''
	);

	$messages[ $reply_type ] = array(
		 0 => '', // Unused. Messages start at index 1.
		 1 => sprintf( __( 'Reply updated. <a href="%s">View reply</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 2 => '',
		 3 => '',
		 4 => __( 'Reply updated.', 'message-board' ),
		 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Reply restored to revision from %s', 'message-board' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		 6 => sprintf( __( 'Reply published. <a href="%s">View reply</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 7 => __( 'Reply saved.', 'message-board' ),
		 8 => '',
		 9 => '',
		10 => ''
	);

	return $messages;
}
