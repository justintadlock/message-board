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

	/* Get plugin settings. */
	//$settings = get_option( 'restaurant_settings', mb_get_default_settings() );

	/* Set up the arguments for the post type. */
	$topic_args = array(
		'description'         => '',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-format-chat',
		'can_export'          => true,
		'delete_with_user'    => false,
		'hierarchical'        => false,
		'has_archive'         =>  mb_get_topic_slug(),
		'query_var'           => 'forum_topic',
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
			'menu_name'          => __( 'Message Board',            'message-board' ),
			'name_admin_bar'     => __( 'Topics',                   'message-board' ),
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

	/* Set up the arguments for the post type. */
	$reply_args = array(
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
		'has_archive'         =>  false,
		'query_var'           => 'forum_reply',
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
			'title',
			'editor',
		),

		'labels' => array(
			'name'               => __( 'Replies',                   'message-board' ),
			'singular_name'      => __( 'Reply',                    'message-board' ),
			'menu_name'          => __( 'Message Board',            'message-board' ),
			'name_admin_bar'     => __( 'Replies',                   'message-board' ),
			'all_items'          => __( 'Replies',                   'message-board' ),
			'add_new'            => __( 'Add Reply',                'message-board' ),
			'add_new_item'       => __( 'Add New Reply',            'message-board' ),
			'edit_item'          => __( 'Edit Reply',               'message-board' ),
			'new_item'           => __( 'New Reply',                'message-board' ),
			'view_item'          => __( 'View Reply',               'message-board' ),
			'search_items'       => __( 'Search Replies',            'message-board' ),
			'not_found'          => __( 'No replies found',          'message-board' ),
			'not_found_in_trash' => __( 'No replies found in trash', 'message-board' ),

			/* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'      => __( 'Replies',                   'message-board' ),
		)
	);

	/* Register post types. */
	register_post_type( 'forum_topic', $topic_args );

	/* Register post types. */
	register_post_type( 'forum_reply', $reply_args );
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

	if ( 'topic' === $post->post_type )
		$title = __( 'Enter topic name', 'message-board' );

	return $title;
}

/**
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_post_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['forum_topic'] = array(
		 0 => '', // Unused. Messages start at index 1.
		 1 => sprintf( __( 'Topic updated. <a href="%s">View topic</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 2 => '',
		 3 => '',
		 4 => __( 'Topic updated.', 'message-board' ),
		 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Topic restored to revision from %s', 'message-board' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		 6 => sprintf( __( 'Topic published. <a href="%s">View topic</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 7 => __( 'Topic saved.', 'message-board' ),
		 8 => sprintf( __( 'Topic submitted. <a target="_blank" href="%s">Preview topic</a>', 'message-board' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		 9 => sprintf( __( 'Topic scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview topic</a>', 'message-board' ), date_i18n( __( 'M j, Y @ G:i', 'message-board' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Topic draft updated. <a target="_blank" href="%s">Preview topic</a>', 'message-board' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	$messages['forum_reply'] = array(
		 0 => '', // Unused. Messages start at index 1.
		 1 => sprintf( __( 'Reply updated. <a href="%s">View reply</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 2 => '',
		 3 => '',
		 4 => __( 'Reply updated.', 'message-board' ),
		 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Reply restored to revision from %s', 'message-board' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		 6 => sprintf( __( 'Reply published. <a href="%s">View reply</a>', 'message-board' ), esc_url( get_permalink( $post_ID ) ) ),
		 7 => __( 'Reply saved.', 'message-board' ),
		 8 => sprintf( __( 'Reply submitted. <a target="_blank" href="%s">Preview reply</a>', 'message-board' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		 9 => sprintf( __( 'Reply scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview reply</a>', 'message-board' ), date_i18n( __( 'M j, Y @ G:i', 'message-board' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Reply draft updated. <a target="_blank" href="%s">Preview reply</a>', 'message-board' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}
