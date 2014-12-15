<?php
/**
 * Handles the template hierarchy.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Override the template hierarchy when viewing the forums. */
add_filter( 'template_include', 'mb_template_include' );

/**
 * Returns the theme folder that houses the templates for the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_theme_template_folder() {
	return apply_filters( 'mb_get_theme_template_folder', 'board' );
}

/**
 * Custom template hierarchy.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $template
 * @return string
 */
function mb_template_include( $template ) {

	/* If not viewing a message board page, bail. */
	if ( !mb_is_message_board() )
		return $template;

	/* Set up some default variables. */
	$dir          = mb_get_theme_template_folder();
	$has_template = false;
	$_templates   = array();

	/* If viewing a single forum page. */
	if ( mb_is_single_forum() ) {

		$_templates[] = "{$dir}/single-forum.php";
	}

	/* If viewing the forum archive (default forum front). */
	elseif ( mb_is_forum_archive() ) {

		$_templates[] = "{$dir}/archive-forum.php";
	}

	/* If viewing a single topic. */
	elseif ( mb_is_single_topic() ) {

		$_templates[] = "{$dir}/single-topic.php";
	}

	/* If viewing the topic archive (possible forum front page). */
	elseif ( mb_is_topic_archive() ) {

		$_templates[] = "{$dir}/archive-topic.php";
	}

	/* If viewing a user sub-page. */
	elseif ( mb_is_user_page() ) {

		$page = sanitize_key( get_query_var( 'mb_user_page' ) );

		$_templates[] = "{$dir}/single-user-{$page}.php";
		$_templates[] = "{$dir}/single-user.php";
	}

	/* If viewing a user profile page. */
	elseif ( mb_is_single_user() ) {

		$_templates[] = "{$dir}/single-user.php";
	}

	/* If viewing the user archive. */
	elseif ( mb_is_user_archive() ) {

		$_templates[] = "{$dir}/archive-user.php";
	}

	/* If viewing a search results page. */
	elseif ( mb_is_forum_search() ) {

		$_templates[] = "{$dir}/search.php";
		$_templates[] = "{$dir}/archive-topic.php"; // temp
	}

	/* If viewing the forum login page. */
	elseif ( mb_is_forum_login() ) {

		$_templates[] = "{$dir}/login.php";
	}

	/* If viewing an edit page. */
	elseif ( mb_is_edit() ) {

		if ( mb_is_forum_edit() )
			$_templates[] = "{$dir}/edit-forum.php";

		elseif ( mb_is_topic_edit() )
			$_templates[] = "{$dir}/edit-topic.php";

		elseif ( mb_is_reply_edit() )
			$_templates[] = "{$dir}/edit-reply.php";

		elseif ( mb_is_user_edit() )
			$_templates[] = "{$dir}/edit-user.php";

		$_templates[] = "{$dir}/edit.php";
	}

	/* Add the fallback template. */
	$_templates[] = "{$dir}/board.php";

	/* Check to see if we can find one of our templates. */
	$has_template = locate_template( apply_filters( 'mb_template_hierarchy', $_templates, $dir ) );

	/* If we have a template, use it.  Otherwise, fall back to WP. */
	$template = !empty( $has_template ) ? $has_template : $template;

	/* Return the template and allow devs to overwrite. */
	return apply_filters( 'mb_template_include', $template, $dir );
}
