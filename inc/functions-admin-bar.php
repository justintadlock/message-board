<?php

add_action( 'wp_before_admin_bar_render', 'mb_admin_bar' );

function mb_admin_bar() {
	global $wp_admin_bar;

	$wp_admin_bar->remove_menu( 'new-forum_topic' );
}
