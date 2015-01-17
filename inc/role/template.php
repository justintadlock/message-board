<?php
/**
 * Template functions for role-related functionality.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* ====== Role Query ====== */

/**
 * Creates a new role query.  This function is modeled after the WordPress posts query so that theme 
 * authors have an easier time grasping it.  Normally, you would use a `foreach` loop and so on, but 
 * because we're modeling this after the posts query, you'd use a while loop.  This also allows us to 
 * set up the role of the current role object in the loop behind the scenes so that anything using 
 * `mb_get_role()` will automatically work.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_role_query() {
	$mb = message_board();

	/* If a query has already been created, let's roll. */
	if ( !is_null( $mb->role_query ) ) {

		if ( $mb->role_query->current_role + 1 <= $mb->role_query->found_roles )
			return true;

		return false;
	}

	$page     = is_paged() ? absint( get_query_var( 'paged' ) ) : 1;
	$per_page = mb_get_roles_per_page();
	$offset   = ( $page - 1 ) * $per_page;

	$roles = mb_get_dynamic_roles();
	$mb->role_query = new stdClass;

	$mb->role_query->total_roles  = count( $roles );

	$roles = array_slice( $roles, $offset, $per_page );

	foreach ( $roles as $role => $args )
		$mb->role_query->results[] = array( 'slug' => $role, 'args' => $args );

	$mb->role_query->found_roles  = count( $mb->role_query->results );
	$mb->role_query->current_role = 0;

	return true;
}

/**
 * Sets up the role data.  Basically, this function bumps the role in the `mb_role_query()` loop to the 
 * next role.  It also sets the current role in the loop so that `mb_get_role()` will return the 
 * correct role.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_the_role() {
	$mb = message_board();

	$current = $mb->role_query->current_role++;

	$mb->role_query->loop_role_slug = $mb->role_query->results[ $current ]['slug'];
}

/* ====== Role (role "ID") ====== */

function mb_role( $role = '' ) {
	echo mb_get_role();
}

function mb_get_role( $role = '' ) {

	$roles = mb_get_dynamic_roles();
	$_role = '';

	if ( $role && in_array( $role, array_keys( $roles ) ) )
		$_role = $role;

	elseif ( !is_null( message_board()->role_query ) )
		$_role = message_board()->role_query->loop_role_slug;

	elseif ( get_query_var( 'mb_role' ) )
		$_role = get_query_var( 'mb_role' );

	return apply_filters( 'mb_get_role', $_role, $role );
}

/* ====== Conditionals ====== */

function mb_is_role_archive() {

	if ( 'roles' === get_query_var( 'mb_custom' ) && !mb_is_single_role() )
		return true;

	return false;
}

function mb_is_single_role( $role = '' ) {

	/* Assume we're not viewing a role archive. */
	$is_single_role = false;

	/* Get the role query var. */
	$qv_role = get_query_var( 'mb_role' );

	/* If viewing a user archive and we have a role. */
	if ( !empty( $qv_role ) ) {

		$roles   = mb_get_dynamic_roles();

		if ( empty( $role ) && isset( $roles[ $qv_role] ) )
			$is_single_role = true;

		elseif ( !empty( $role ) && isset( $roles[ $qv_role ] ) && ( $qv_role === $role || $qv_role === "mb_{$role}" ) )
			$is_single_role = true;
	}

	return apply_filters( 'mb_is_single_role', $is_single_role );
}

/* ====== Title ====== */

function mb_single_role_title() {
	echo mb_get_single_role_title();
}

function mb_get_single_role_title() {
	$role = mb_get_role();
	return apply_filters( 'mb_get_single_role_title', mb_get_role_object( $role )->labels->plural_name );
}

function mb_role_archive_title() {
	echo mb_get_role_archive_title();
}

function mb_get_role_archive_title() {
	return __( 'Roles', 'message-board' );
}

/* ====== Content ====== */

function mb_role_description( $role = '' ) {
	echo mb_get_role_description( $role );
}

function mb_get_role_description( $role = '' ) {
	$role = mb_get_role( $role );
	$desc = $role ? mb_get_role_object( $role )->description : '';

	return apply_filters( 'mb_get_role_description', $desc, $role );
}

/* ====== Counts ====== */

function mb_role_user_count( $role = '' ) {
	echo mb_get_role_user_count( $role );
}

function mb_get_role_user_count( $role = '' ) {
	$role = mb_get_role( $role );

	$users = count_users();
	$count = 0;

	if ( isset( $users['avail_roles'][ $role ] ) )
		$count = $users['avail_roles'][ $role ];

	return apply_filters( 'mb_get_role_user_count', $count, $role );
}

/**
 * Pagination for the role loop.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string|void
 */
function mb_loop_role_pagination( $args = array() ) {
	$total_roles = message_board()->role_query->total_roles;
	$max_pages   = ceil( $total_roles / mb_get_roles_per_page() );
	$query = array( 'max_num_pages' => $max_pages );
	return mb_pagination( $args, (object) $query );
}
