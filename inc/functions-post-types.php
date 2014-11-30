<?php
/**
 * File for registering custom post types.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @since      1.0.0
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       http://themehybrid.com/plugins/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register custom post types on the 'init' hook. */
add_action( 'init', 'mb_register_post_types' );

/* Filter post updated messages for custom post types. */
add_filter( 'post_updated_messages', 'mb_post_updated_messages' );

/* Filter the "enter title here" text. */
add_filter( 'enter_title_here', 'mb_enter_title_here', 10, 2 );

/**
 * Registers post types needed by the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_types() {

	/* Set up the arguments for the 'forum' post type. */
	$forum_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-format-chat',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => true,
		'has_archive'         => 'forums' === mb_get_show_on_front() ? mb_get_root_slug() : mb_get_forum_slug(),
		'query_var'           => mb_get_forum_post_type(),
		'capability_type'     => 'forum',
		'map_meta_cap'        => true,

		'capabilities' => array(

			// meta caps (don't assign these to roles)
			'edit_post'              => 'edit_forum',
			'read_post'              => 'read_forum',
			'delete_post'            => 'delete_forum',

			// primitive/meta caps
			'create_posts'           => 'create_forums',

			// primitive caps used outside of map_meta_cap()
			'edit_posts'             => 'edit_forums',
			'edit_others_posts'      => 'manage_forums',
			'publish_posts'          => 'edit_forums',
			'read_private_posts'     => 'read',

			// primitive caps used inside of map_meta_cap()
			'read'                   => 'read',
			'delete_posts'           => 'manage_forums',
			'delete_private_posts'   => 'manage_forums',
			'delete_published_posts' => 'manage_forums',
			'delete_others_posts'    => 'manage_forums',
			'edit_private_posts'     => 'edit_forums',
			'edit_published_posts'   => 'edit_forums'
		),

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
		)
	);

	/* Set up the arguments for the 'forum_topic' post type. */
	$topic_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => null,
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         => 'topics' === mb_get_show_on_front() ? mb_get_root_slug() : mb_get_topic_slug(),
		'query_var'           => mb_get_topic_post_type(),
		'capability_type'     => 'forum_topic',
		'map_meta_cap'        => true,

		'capabilities' => array(

			// meta caps (don't assign these to roles)
			'edit_post'              => 'edit_forum_topic',
			'read_post'              => 'read_forum_topic',
			'delete_post'            => 'delete_forum_topic',

			// primitive/meta caps
			'create_posts'           => 'create_forum_topics',

			// primitive caps used outside of map_meta_cap()
			'edit_posts'             => 'edit_forum_topics',
			'edit_others_posts'      => 'manage_forums',
			'publish_posts'          => 'edit_forum_topics',
			'read_private_posts'     => 'read',

			// primitive caps used inside of map_meta_cap()
			'read'                   => 'read',
			'delete_posts'           => 'manage_forums',
			'delete_private_posts'   => 'manage_forums',
			'delete_published_posts' => 'manage_forums',
			'delete_others_posts'    => 'manage_forums',
			'edit_private_posts'     => 'edit_forum_topics',
			'edit_published_posts'   => 'edit_forum_topics'
		),

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
			'menu_name'          => __( 'Topics',                   'message-board' ),
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

			/* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'      => __( 'Topics',                   'message-board' ),
		)
	);

	/* Set up the arguments for the 'forum_reply' post type. */
	$reply_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_admin_bar'   => false,
		'menu_position'       => null,
		'menu_icon'           => null,
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         =>  false,
		'query_var'           => mb_get_reply_post_type(),
		'capability_type'     => 'forum_reply',
		'map_meta_cap'        => true,

		'capabilities' => array(

			// meta caps (don't assign these to roles)
			'edit_post'              => 'edit_forum_reply',
			'read_post'              => 'read_forum_reply',
			'delete_post'            => 'delete_forum_reply',

			// primitive/meta caps
			'create_posts'           => 'create_forum_replies',

			// primitive caps used outside of map_meta_cap()
			'edit_posts'             => 'edit_forum_replies',
			'edit_others_posts'      => 'manage_forums',
			'publish_posts'          => 'edit_forum_replies',
			'read_private_posts'     => 'read',

			// primitive caps used inside of map_meta_cap()
			'read'                   => 'read',
			'delete_posts'           => 'manage_forums',
			'delete_private_posts'   => 'manage_forums',
			'delete_published_posts' => 'manage_forums',
			'delete_others_posts'    => 'manage_forums',
			'edit_private_posts'     => 'edit_forum_replies',
			'edit_published_posts'   => 'edit_forum_replies'
		),

		'rewrite' => false,

		'supports' => array(
			'editor'
		),

		'labels' => array(
			'name'               => __( 'Replies',                   'message-board' ),
			'singular_name'      => __( 'Reply',                     'message-board' ),
			'menu_name'          => __( 'Replies',                   'message-board' ),
			'name_admin_bar'     => __( 'Reply',                     'message-board' ),
			'all_items'          => __( 'Replies',                   'message-board' ),
			'add_new'            => __( 'Add Reply',                 'message-board' ),
			'add_new_item'       => __( 'Add New Reply',             'message-board' ),
			'edit_item'          => __( 'Edit Reply',                'message-board' ),
			'new_item'           => __( 'New Reply',                 'message-board' ),
			'view_item'          => __( 'View Reply',                'message-board' ),
			'search_items'       => __( 'Search Replies',            'message-board' ),
			'not_found'          => __( 'No replies found',          'message-board' ),
			'not_found_in_trash' => __( 'No replies found in trash', 'message-board' ),

			/* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'      => __( 'Replies',                   'message-board' ),
		)
	);

	/* Register post types. */
	register_post_type( mb_get_forum_post_type(), $forum_args );
	register_post_type( mb_get_topic_post_type(), $topic_args );
	register_post_type( mb_get_reply_post_type(), $reply_args );
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

	if ( mb_get_topic_post_type() === $post->post_type )
		$title = __( 'Enter topic name', 'message-board' );

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
