<?php
/**
 * Handles admin bar functionality.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Hook into to change the admin bar output. */
add_action( 'wp_before_admin_bar_render', 'mb_admin_bar' );

/**
 * Removes the ability to add a new forum topic from the admin bar.
 *
 * @since  1.0.0
 * @access public
 * @global object  $wp_admin_bar
 * @return void
 */
function mb_admin_bar() {
	global $wp_admin_bar;

	$wp_admin_bar->remove_menu( 'new-' . mb_get_reply_post_type() );
}
