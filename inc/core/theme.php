<?php
/**
 * Theme-related functionality.  Handles the template hierarchy.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Override the template hierarchy when viewing the forums. */
add_filter( 'template_include', 'mb_template_include', 95 );

/* Adds the theme compatibility layer. */
add_action( 'mb_theme_compat', 'mb_theme_compat' );

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
 * Returns the plugin's template folder name.  This is the fallback used when a template can't be found 
 * in the theme.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_plugin_template_folder() {
	return trailingslashit( message_board()->dir_path ) . 'templates';
}

/**
 * Function for loading template parts.  This is similar to the WordPress `get_template_part()` function 
 * with the exception that it will fall back to templates in the plugin's `/templates` folder.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $slug
 * @param  string  $name
 * @return void
 */
function mb_get_template_part( $slug, $name = '' ) {

	/* Get theme and plugin templates paths. */
	$theme_dir  = mb_get_theme_template_folder();
	$plugin_dir = mb_get_plugin_template_folder();

	/* Build the templates array for the theme. */
	$templates = array();

	if ( !empty( $name ) )
		$templates[] = "{$theme_dir}/{$slug}-{$name}.php";

	$templates[] = "{$theme_dir}/{$slug}.php";

	/* Attempt to find the template in the theme. */
	$has_template = locate_template( $templates, false, false );

	/* If no theme template found, check for name + slug template in plugin. */
	if ( !$has_template && !empty( $name ) && file_exists( "{$plugin_dir}/{$slug}-{$name}.php" ) )
		$has_template = "{$plugin_dir}/{$slug}-{$name}.php";

	/* Else, if no theme template found, check for it in the plugin. */
	elseif ( !$has_template && file_exists( "{$plugin_dir}/{$slug}.php" ) )
		$has_template = "{$plugin_dir}/{$slug}.php";

	/* If we found a template, load it. */
	if ( $has_template )
		require( $has_template );
}

/**
 * Callback function on WordPress' `template_include` filter hook.  This function looks for a template within 
 * the theme for handling the current page's output.  If no template is found, it falls back to a default 
 * `board.php` template within the plugin.
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
	$hierarchy    = mb_get_template_hierarchy();
	$theme_dir    = mb_get_theme_template_folder();
	$plugin_dir   = mb_get_plugin_template_folder();
	$_templates   = array();

	foreach ( $hierarchy as $hier )
		$_templates[] = "{$theme_dir}/{$hier}";

	/* Check to see if we can find one of our templates. */
	$has_template = locate_template( $_templates );

	/* Allow devs to overwrite template. */
	$has_template = apply_filters( 'mb_template_include', $has_template, $theme_dir );

	/* If we have a template return it. */
	if ( $has_template )
		return $has_template;

	/* Load our fallback if nothing is found at this point. */
	require_once( "{$plugin_dir}/board.php" );
	return '';
}

/**
 * Callback function on the `mb_theme_compat` hook.  This hook is used to output the plugin's content 
 * as a fallback in the case that a theme doesn't handle the templates.  This function first looks for 
 * a template within the theme for compatibility.  If it doesn't find one, it'll load its own.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_theme_compat() {

	$hierarchy       = mb_get_template_hierarchy();
	$theme_dir       = mb_get_theme_template_folder();
	$plugin_dir      = mb_get_plugin_template_folder();
	$theme_templates = array();

	/* Set up the theme templates to search for. */
	foreach ( $hierarchy as $hier )
		$theme_templates[] = "{$theme_dir}/content-{$hier}";

	/* Check to see if we can find one of our templates. */
	$has_template = locate_template( $theme_templates );

	/* If no theme template was found, check the plugin. */
	if ( !$has_template ) {

		/* Loop through the hierarchy. */
		foreach ( $hierarchy as $hier ) {

			/* If the template exists in the plugin, use it. */
			if ( file_exists( "{$plugin_dir}/content-{$hier}" ) ) {
				$has_template = "{$plugin_dir}/content-{$hier}";
				break;
			}
		}
	}

	/* If we've found a template, load it once. */
	if ( $has_template )
		require_once( $has_template );
}

/**
 * Builds the template hierarchy for the plugin.  This function figures out what the current page 
 * is and returns an array of possible templates to use.  Note that this function only returns 
 * the templates name and not a full paths.  It is meant to be used within other functions that actually 
 * locate/load the templates.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_template_hierarchy() {

	$hierarchy = array();

	/* If viewing a single forum page. */
	if ( mb_is_single_forum() ) {

		$hierarchy[] = 'single-forum.php';

	/* If viewing the forum archive (default forum front). */
	} elseif ( mb_is_forum_archive() ) {

		$hierarchy[] = 'archive-forum.php';

	/* If viewing a single topic. */
	} elseif ( mb_is_single_topic() ) {

		$hierarchy[] = "single-topic.php";

	/* If viewing the topic archive (possible forum front page). */
	} elseif ( mb_is_topic_archive() ) {

		$hierarchy[] = 'archive-topic.php';

	/* If viewing a user sub-page. */
	} elseif ( mb_is_user_page() ) {

		$page = sanitize_key( get_query_var( 'mb_user_page' ) );

		$hierarchy[] = "single-user-{$page}.php";
		$hierarchy[] = 'single-user.php';

	/* If viewing a user profile page. */
	} elseif ( mb_is_single_user() ) {

		$hierarchy[] = 'single-user.php';

	/* If viewing a user role archive. */
	} elseif ( mb_is_user_role_archive() ) {

		$hierarchy[] = 'archive-user-role.php';
		$hierarchy[] = 'archive-user.php';

	/* If viewing the user archive. */
	} elseif ( mb_is_user_archive() ) {

		$hierarchy[] = 'archive-user.php';

	/* If viewing a search results page. */
	} elseif ( mb_is_search_results() ) {

		$hierarchy[] = 'search-results.php';

	/* If viewing the advanced search page. */
	} elseif ( mb_is_search() ) {

		$hierarchy[] = 'search.php';

	/* If viewing the forum login page. */
	} elseif ( mb_is_forum_login() ) {

		$hierarchy[] = 'login.php';

	/* If viewing an edit page. */
	} elseif ( mb_is_edit() ) {

		if ( mb_is_forum_edit() )
			$hierarchy[] = 'edit-forum.php';

		elseif ( mb_is_topic_edit() )
			$hierarchy[] = 'edit-topic.php';

		elseif ( mb_is_reply_edit() )
			$hierarchy[] = 'edit-reply.php';

		elseif ( mb_is_user_edit() )
			$hierarchy[] = 'edit-user.php';

		$hierarchy[] = 'edit.php';
	}

	/* Add the fallback template. */
	$hierarchy[] = 'board.php';

	return apply_filters( 'mb_get_template_hierarchy', $hierarchy );
}
