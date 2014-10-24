<?php

/* Add admin menu items. */
add_action( 'admin_menu', 'mb_admin_menu' );

/**
 * Adds admin menu items needed by the plugin.  Rather than having multiple top-level menu items 
 * like some plugins, which shall remain unnamed, we'll consolidate everything into a single 
 * item.  Yay for no clutter!
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_admin_menu() {

	/* Get post type names. */
	$forum_type   = mb_get_forum_post_type();
	$topic_type   = mb_get_topic_post_type();
	$reply_type   = mb_get_reply_post_type();

	/* Get post type objects. */
	$topic_object = get_post_type_object( $topic_type );
	$reply_object = get_post_type_object( $reply_type );

	/* Add the topic menu page. */
	add_submenu_page( 
		"edit.php?post_type={$forum_type}", 
		$topic_object->labels->all_items, 
		$topic_object->labels->all_items, 
		$topic_object->cap->edit_posts, 
		"edit.php?post_type={$topic_type}" 
	);

	/* Add the reply menu page. */
	add_submenu_page( 
		"edit.php?post_type={$forum_type}", 
		$reply_object->labels->all_items, 
		$reply_object->labels->all_items, 
		$reply_object->cap->edit_posts, 
		"edit.php?post_type={$reply_type}" 
	);
}
