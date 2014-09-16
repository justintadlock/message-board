<?php
/**
 * File for registering custom taxonomies.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @since      1.0.0
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       http://themehybrid.com/plugins/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register taxonomies on the 'init' hook. */
add_action( 'init', 'mb_register_taxonomies' );

/**
 * Register taxonomies for the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void.
 */
function mb_register_taxonomies() {

	register_taxonomy(
		'forum',
		array( 'forum_topic' ),
		array(
			'public'            => true,
			'show_ui'           => false,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'query_var'         => 'forum',

			/* Only 2 caps are needed: 'manage_restaurant' and 'edit_restaurant_items'. */
			'capabilities' => array(
				'manage_terms' => 'manage_forums',
				'edit_terms'   => 'manage_forums',
				'delete_terms' => 'manage_forums',
				'assign_terms' => 'edit_forum_topics',
			),

			/* The rewrite handles the URL structure. */
			'rewrite' => array(
				'slug'         => mb_get_forum_slug(),
				'with_front'   => false,
				'hierarchical' => false,
				'ep_mask'      => EP_NONE
			),

			/* Labels used when displaying taxonomy and terms. */
			'labels' => array(
				'name'                       => __( 'Forums',                           'message-board' ),
				'singular_name'              => __( 'Forum',                            'message-board' ),
				'menu_name'                  => __( 'Forums',                           'message-board' ),
				'name_admin_bar'             => __( 'Forums',                           'message-board' ),
				'search_items'               => __( 'Search Forums',                    'message-board' ),
				'popular_items'              => __( 'Popular Forums',                   'message-board' ),
				'all_items'                  => __( 'All Forums',                       'message-board' ),
				'edit_item'                  => __( 'Edit Forum',                       'message-board' ),
				'view_item'                  => __( 'View Forum',                       'message-board' ),
				'update_item'                => __( 'Update Forum',                     'message-board' ),
				'add_new_item'               => __( 'Add New Forum',                    'message-board' ),
				'new_item_name'              => __( 'New Forum Name',                   'message-board' ),
				'separate_items_with_commas' => __( 'Separate forums with commas',      'message-board' ),
				'add_or_remove_items'        => __( 'Add or remove forums',             'message-board' ),
				'choose_from_most_used'      => __( 'Choose from the most used forums', 'message-board' ),
			)
		)
	);

	register_taxonomy(
		'forum_tag',
		array( 'forum_topic' ),
		array(
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'query_var'         => 'forum_tag',

			/* Only 2 caps are needed: 'manage_restaurant' and 'edit_restaurant_items'. */
			'capabilities' => array(
				'manage_terms' => 'manage_forums',
				'edit_terms'   => 'manage_forums',
				'delete_terms' => 'manage_forums',
				'assign_terms' => 'edit_forum_topics',
			),

			/* The rewrite handles the URL structure. */
			'rewrite' => array(
				'slug'         => mb_get_tag_slug(),
				'with_front'   => false,
				'hierarchical' => false,
				'ep_mask'      => EP_NONE
			),

			/* Labels used when displaying taxonomy and terms. */
			'labels' => array(
				'name'                       => __( 'Forum Tags',                     'message-board' ),
				'singular_name'              => __( 'Forum Tag',                      'message-board' ),
				'menu_name'                  => __( 'Tags',                           'message-board' ),
				'name_admin_bar'             => __( 'Tags',                           'message-board' ),
				'search_items'               => __( 'Search Tags',                    'message-board' ),
				'popular_items'              => __( 'Popular Tags',                   'message-board' ),
				'all_items'                  => __( 'All Tags',                       'message-board' ),
				'edit_item'                  => __( 'Edit Tag',                       'message-board' ),
				'view_item'                  => __( 'View Tag',                       'message-board' ),
				'update_item'                => __( 'Update Tag',                     'message-board' ),
				'add_new_item'               => __( 'Add New Tag',                    'message-board' ),
				'new_item_name'              => __( 'New Tag Name',                   'message-board' ),
				'separate_items_with_commas' => __( 'Separate tags with commas',      'message-board' ),
				'add_or_remove_items'        => __( 'Add or remove tags',             'message-board' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'message-board' ),
			)
		)
	);
}
