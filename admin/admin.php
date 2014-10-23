<?php

add_action( 'admin_menu', 'mb_admin_menu' );

function mb_admin_menu() {

	remove_submenu_page( 'edit.php?post_type=forum_topic', 'edit.php?post_type=forum_topic' );
	//remove_submenu_page( 'edit.php?post_type=forum_topic', 'edit-tags.php?taxonomy=forum&post_type=forum_topic' );
	//remove_submenu_page( 'edit.php?post_type=forum_topic', 'edit-tags.php?taxonomy=forum_tag&post_type=forum_topic' );

	//add_menu_page( __( 'Message Board', 'message-board' ), __( 'Forums', 'message-board' ), 'manage_forums', 'edit-tags.php?taxonomy=forum&post_type=forum_topic', '', 'dashicons-format-chat', '42' );

	add_submenu_page( 'edit.php?post_type=forum', __( 'Topics',  'message-board' ), __( 'Topics',  'message-board' ), 'edit_forum_topics',  'edit.php?post_type=forum_topic' );
	add_submenu_page( 'edit.php?post_type=forum', __( 'Replies', 'message-board' ), __( 'Replies', 'message-board' ), 'edit_forum_replies', 'edit.php?post_type=forum_reply' );
	add_submenu_page( 'edit.php?post_type=forum', __( 'Tags',    'message-board' ), __( 'Tags',    'message-board' ), 'manage_forums',      'edit-tags.php?taxonomy=forum_tag&post_type=forum_topic' );

/*

http://localhost/wp-admin/edit.php?post_type=forum_topic
http://localhost/wp-admin/edit.php?post_type=forum_reply
http://localhost/wp-admin/edit-tags.php?taxonomy=forum&post_type=forum_topic
http://localhost/wp-admin/edit-tags.php?taxonomy=forum_tag&post_type=forum_topic
*/

}

add_action( 'admin_enqueue_scripts', 'mb_admin_enqueue_scripts' );

function mb_admin_enqueue_scripts( $hook_suffix ) {
	global $typenow;

	if ( 'edit-tags.php' === $hook_suffix && 'forum_topic' === $typenow )
		wp_enqueue_style( 'mb-admin', trailingslashit( message_board()->dir_uri ) . 'css/admin.css' );
}








