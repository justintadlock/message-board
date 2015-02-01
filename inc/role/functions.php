<?php
/**
 * Complete Roles API for extending WordPress' role system with dynamic, on-the-fly roles.  Message Board 
 * uses custom forum roles for handling permissions.  The reason for this is so that users don't have to 
 * figure out the complexities of setting capabilities for their roles.  They can essentially plug and 
 * play.  The secondary reason is that it allows us to keep the forum roles system separate from the main 
 * WordPress roles or other custom roles.
 *
 * The plugin's forum roles system still works within the bounds of WordPress roles/caps system.  We're 
 * just building on top of it.  Plugin developers can register custom roles, unregister the default roles 
 * or even overwrite the defaults.  If adding custom roles, devs must use the `mb_register_roles` hook.
 *
 * @todo Figure out why the heck dynamic roles keep getting added to the database. :(
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register dynamic user roles. */
add_action( 'plugins_loaded', 'mb_register_roles', 0 );

/* Merge dynamic roles with WP roles. */
add_action( 'setup_theme', 'mb_merge_roles', 95 );

/* Make sure the current user has a forum role. */
add_action( 'set_current_user', 'mb_set_current_user_role', 0 );

/* Filter the editable roles. */
add_filter( 'editable_roles', 'mb_editable_roles_filter' );

/**
 * Returns the role ID/slug for the forum keymaster role.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_keymaster_role() {
	return apply_filters( 'mb_get_keymaster_role', 'mb_keymaster' );
}

/**
 * Returns the role ID/slug for the forum moderator role.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_moderator_role() {
	return apply_filters( 'mb_get_moderator_role', 'mb_moderator' );
}

/**
 * Returns the role ID/slug for the forum participant role.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_participant_role() {
	return apply_filters( 'mb_get_participant_role', 'mb_participant' );
}

/**
 * Returns the role ID/slug for the forum spectator role.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_spectator_role() {
	return apply_filters( 'mb_get_spectator_role', 'mb_spectator' );
}

/**
 * Returns the role ID/slug for the forum banned role.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_banned_role() {
	return apply_filters( 'mb_get_banned_role', 'mb_banned' );
}

/**
 * Returns the capabilities for the keymaster forum role.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_keymaster_role_caps() {

	$caps = array(
		// Forum caps
		'create_forums'        => true,
		'open_forums'          => true,
		'close_forums'         => true,
		'privatize_forums'     => true,
		'hide_forums'          => true,
		'archive_forums'       => true,
		'move_forums'          => true,
		'edit_forums'          => true,
		'edit_others_forums'   => true,
		'edit_open_forums'     => true,
		'edit_closed_forums'   => true,
		'edit_private_forums'  => true,
		'edit_hidden_forums'   => true,
		'edit_archived_forums' => true,
		'delete_forums'        => true,
		'delete_others_forums' => true,
		'read_forums'          => true,
		'read_private_forums'  => true,
		'read_hidden_forums'   => true,
		'read_archived_forums' => true,

		// Topic caps
		'create_topics'        => true,
		'open_topics'          => true,
		'close_topics'         => true,
		'privatize_topics'     => true,
		'hide_topics'          => true,
		'spam_topics'          => true,
		'super_topics'         => true,
		'stick_topics'         => true,
		'move_topics'          => true,
		'edit_topics'          => true,
		'edit_others_topics'   => true,
		'edit_open_topics'     => true,
		'edit_closed_topics'   => true,
		'edit_private_topics'  => true,
		'edit_hidden_topics'   => true,
		'edit_spam_topics'     => true,
		'edit_orphan_topics'   => true,
		'delete_topics'        => true,
		'delete_others_topics' => true,
		'read_topics'          => true,
		'read_private_topics'  => true,
		'read_hidden_topics'   => true,

		// Reply caps
		'create_replies'      => true,
		'spam_replies'        => true,
		'edit_replies'        => true,
		'edit_others_replies' => true,
		'edit_spam_replies'   => true,
		'read_replies'        => true,
	);

	return apply_filters( 'mb_get_keymaster_role_caps', $caps );
}

/**
 * Returns the capabilities for the moderator forum role.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_moderator_role_caps() {

	$caps = array(
		// Forum caps
		'create_forums'        => true,
		'open_forums'          => true,
		'close_forums'         => true,
		'privatize_forums'     => true,
		'hide_forums'          => true,
		'archive_forums'       => true,
		'move_forums'          => true,
		'edit_forums'          => true,
		'edit_open_forums'     => true,
		'edit_closed_forums'   => true,
		'edit_private_forums'  => true,
		'edit_hidden_forums'   => true,
		'edit_archived_forums' => true,
		'delete_forums'        => true,
		'read_forums'          => true,
		'read_private_forums'  => true,
		'read_hidden_forums'   => true,
		'read_archived_forums' => true,

		// Topic caps
		'create_topics'        => true,
		'open_topics'          => true,
		'close_topics'         => true,
		'privatize_topics'     => true,
		'hide_topics'          => true,
		'spam_topics'          => true,
		'super_topics'         => true,
		'stick_topics'         => true,
		'move_topics'          => true,
		'edit_topics'          => true,
		'edit_others_topics'   => true,
		'edit_open_topics'     => true,
		'edit_closed_topics'   => true,
		'edit_private_topics'  => true,
		'edit_hidden_topics'   => true,
		'edit_spam_topics'     => true,
		'edit_orphan_topics'   => true,
		'delete_topics'        => true,
		'delete_others_topics' => true,
		'read_topics'          => true,
		'read_private_topics'  => true,
		'read_hidden_topics'   => true,

		// Reply caps
		'create_replies'      => true,
		'spam_replies'        => true,
		'edit_replies'        => true,
		'edit_others_replies' => true,
		'edit_spam_replies'   => true,
		'read_replies'        => true,
	);

	return apply_filters( 'mb_get_moderator_role_caps', $caps );
}

/**
 * Returns the capabilities for the participant forum role.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_participant_role_caps() {

	$caps = array(
		// Forum caps
		'read_forums'          => true,
		'read_private_forums'  => true,
		'read_archived_forums' => true,

		// Topic caps
		'create_topics'        => true,
		'open_topics'          => true,
		'edit_topics'          => true,
		'edit_open_topics'     => true,
		'read_topics'          => true,
		'read_private_topics'  => true,

		// Reply caps
		'create_replies'      => true,
		'edit_replies'        => true,
		'read_replies'        => true,
	);

	return apply_filters( 'mb_get_participant_role_caps', $caps );
}

/**
 * Returns the capabilities for the spectator forum role.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_spectator_role_caps() {

	$caps = array(
		// Forum caps
		'read_forums'          => true,
		'read_private_forums'  => true,
		'read_archived_forums' => true,

		// Topic caps
		'read_topics'          => true,
		'read_private_topics'  => true,

		// Reply caps
		'read_replies'        => true,
	);

	return apply_filters( 'mb_get_spectator_role_caps', $caps );
}

/**
 * Returns the capabilities for the banned forum role. Note that we're explicitly denying all 
 * forum-related capabilities for this role.  This means that any user with this role, regardless of 
 * any other roles they have, will be denied forum permissions.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_banned_role_caps() {

	$caps = array(
		// Forum caps
		'create_forums'        => false,
		'open_forums'          => false,
		'close_forums'         => false,
		'privatize_forums'     => false,
		'hide_forums'          => false,
		'archive_forums'       => false,
		'move_forums'          => false,
		'edit_forums'          => false,
		'edit_others_forums'   => false,
		'edit_open_forums'     => false,
		'edit_closed_forums'   => false,
		'edit_private_forums'  => false,
		'edit_hidden_forums'   => false,
		'edit_archived_forums' => false,
		'delete_forums'        => false,
		'delete_others_forums' => false,
		'read_forums'          => false,
		'read_private_forums'  => false,
		'read_hidden_forums'   => false,
		'read_archived_forums' => false,

		// Topic caps
		'create_topics'        => false,
		'open_topics'          => false,
		'close_topics'         => false,
		'privatize_topics'     => false,
		'hide_topics'          => false,
		'spam_topics'          => false,
		'super_topics'         => false,
		'stick_topics'         => false,
		'move_topics'          => false,
		'edit_topics'          => false,
		'edit_others_topics'   => false,
		'edit_open_topics'     => false,
		'edit_closed_topics'   => false,
		'edit_private_topics'  => false,
		'edit_hidden_topics'   => false,
		'edit_spam_topics'     => false,
		'edit_orphan_topics'   => false,
		'delete_topics'        => false,
		'delete_others_topics' => false,
		'read_topics'          => false,
		'read_private_topics'  => false,
		'read_hidden_topics'   => false,

		// Reply caps
		'create_replies'      => false,
		'spam_replies'        => false,
		'edit_replies'        => false,
		'edit_others_replies' => false,
		'edit_spam_replies'   => false,
		'read_replies'        => false,
	);

	return apply_filters( 'mb_get_banned_role_caps', $caps );
}

/**
 * Registers the plugin's default user roles.
 *
 * @since  1.0.0
 * @access public
 * @global object $wpdb
 * @return void
 */
function mb_register_roles() {
	global $wpdb;

	/* Keymaster role args. */
	$keymaster_args = array(
		'labels' => array(
			'plural_name'   => __( 'Keymasters', 'message-board' ), 
			'singular_name' => __( 'Keymaster',  'message-board' ),
		),
		'capabilities'   => mb_get_keymaster_role_caps(),
		'description'    => __( 'Keymasters are the administrators of the board. They have the permission to perform any forum-related tasks.', 'message-board' ),
	);

	/* Moderator role args. */
	$moderator_args = array(
		'labels' => array(
			'plural_name'   => __( 'Moderators', 'message-board' ), 
			'singular_name' => __( 'Moderator',  'message-board' ),
		),
		'capabilities'   => mb_get_moderator_role_caps(),
		'description'    => __( 'Moderators are allowed to participate in the forums and moderate all topics and replies created by forum members.', 'message-board' ),
	);

	/* Participant role args. */
	$participant_args = array(
		'labels' => array(
			'plural_name'   => __( 'Participants', 'message-board' ), 
			'singular_name' => __( 'Participant',  'message-board' ),
		),
		'capabilities'   => mb_get_participant_role_caps(),
		'description'    => __( 'Participants are allowed to participate in the forums by creating topics and replies.', 'message-board' ),
	);

	/* Spectator role args. */
	$spectator_args = array(
		'labels' => array(
			'plural_name'   => __( 'Spectators', 'message-board' ), 
			'singular_name' => __( 'Spectator',  'message-board' ),
		),
		'capabilities'   => mb_get_spectator_role_caps(),
		'description'    => __( 'Spectators are allowed to read topics and replies but are not allowed to participate in the discussion.', 'message-board' ),
	);

	/* Banned role args. */
	$banned_args = array(
		'labels' => array(
			'plural_name'   => __( 'Banned', 'message-board' ), 
			'singular_name' => __( 'Banned',  'message-board' ),
		),
		'capabilities'   => mb_get_banned_role_caps(),
		'description'    => __( 'Banned users are explicitly denied access to using the forums in any way.', 'message-board' ),
	);

	/* Register the roles. */
	mb_register_role( mb_get_keymaster_role(),   apply_filters( 'mb_keymaster_role_args',   $keymaster_args   ) );
	mb_register_role( mb_get_moderator_role(),   apply_filters( 'mb_moderator_role_args',   $moderator_args   ) );
	mb_register_role( mb_get_participant_role(), apply_filters( 'mb_participant_role_args', $participant_args ) );
	mb_register_role( mb_get_spectator_role(),   apply_filters( 'mb_spectator_role_args',   $spectator_args   ) );
	mb_register_role( mb_get_banned_role(),      apply_filters( 'mb_banned_role_args',      $banned_args      ) );

	/* Action hook for registering custom forum roles. */
	do_action( 'mb_register_roles' );

	/* Filter the user roles option when WP decides to pull roles from the DB. */
	add_filter( "option_{$wpdb->prefix}user_roles", 'mb_option_user_roles_filter' );
}

/**
 * Merges user roles with WordPress.  Typically, WordPress roles are saved to the database.  We're going 
 * to bypass this and hook our roles into other roles when the page is loaded.  This allows us to keep the 
 * roles dynamic without having to save them to the DB.
 *
 * @since  1.0.0
 * @access public
 * @global array  $wp_roles
 * @return void
 */
function mb_merge_roles() {
	global $wp_roles;

	/* Make sure we have roles. */
	if ( !isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	/*
	 * Loop through each of the dynamic roles and merge them with the `$wp_roles` array. This is 
	 * kind of hacky, but it's the best we can do because there's no API for dynamic roles in WP.
	 */
	foreach ( mb_get_dynamic_roles() as $role => $args ) {

		/* Add the custom role. */
		$wp_roles->roles[ $role ]        = array( 'name' => $args->labels->singular_name, 'capabilities' => $args->capabilities );
		$wp_roles->role_objects[ $role ] = new WP_Role( $role, $args->capabilities );
		$wp_roles->role_names[ $role ]   = $args->labels->singular_name;
	}
}

/**
 * Filters the user roles when WP pulls them from the database.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $roles
 * @return array
 */
function mb_option_user_roles_filter( $roles ) {

	foreach ( mb_get_dynamic_roles() as $role => $args )
		$roles[ $role ] = array( 'name' => $args->labels->singular_name, 'capabilities' => $args->capabilities );

	return $roles;
}

/**
 * Register a custom user forum role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @param  array   $args {
 *     Array of arguments for registering a user forum role.
 *
 *     @type  array  $capabilities  Key/value pairs of capabilities.  The key should be the capability 
 *                                  and the value should `TRUE` to explicity grant a cap or `FALSE` to 
 *                                  explicitly deny a cap.
 *     @type  array  $labels        Array of internationalized labels for this role.
 *     @type  string $description   Description of the role.
 * }
 * @return void
 */
function mb_register_role( $role, $args = array() ) {
	$mb = message_board();

	/* Only allow alphanumeric characters, hyphens, and underscores. */
	$role = sanitize_key( $role );

	/* Don't register if the role is already registered. */
	if ( isset( $mb->roles[ $role ] ) )
		return false;

	/* Default arguments. */
	$defaults = array(
		'capabilities' => array(),
		'labels'       => array(),  // singular_name, plural_name
		'description'  => '',
	);

	$args = wp_parse_args( $args, $defaults );

	/* Make labels objects. */
	$args['labels'] = (object)$args['labels'];

	/* Add the role object. */
	$mb->roles[ $role ] = (object)$args;
}

/**
 * Unregister a registered forum role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function mb_unregister_role( $role ) {
	$mb = message_board();

	/* If the role is set, remove it. */
	if ( isset( $mb->roles[ $role ] ) ) {
		unset( $mb->roles[ $role ] );
		return true;
	}

	return false;
}

/**
 * Return a role object.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return object
 */
function mb_get_role_object( $role = '' ) {
	$role = mb_get_role( $role );
	return message_board()->roles[ $role ];
}

/**
 * Returns an array of the plugin's dynamic roles.  These roles are "dynamic" because they are not saved in 
 * the database.  Instead, they're added early in the page load.
 *
 * Developers can overwrite the default roles with custom ones. If doing so, it is recommended to use the 
 * API (e.g., `mb_register_role()`, `mb_unregister_role()`).
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_dynamic_roles() {
	return apply_filters( 'mb_get_dynamic_roles', message_board()->roles );
}

/**
 * Adds a user's forum role.  If no role is given, the role will be set to the default mapping.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  string  $role
 */
function mb_add_user_role( $user_id, $role = '' ) {

	/* Get the user object. */
	$user = new WP_User( $user_id );

	$dynamic_roles = array_keys( mb_get_dynamic_roles() );

	if ( $role ) {
		if ( in_array( $role, $dynamic_roles ) )
			$user->add_role( $role );
		return;
	}

	$role_map = mb_get_role_map();

	$user_role = array_shift( $user->roles );

	$new_role = isset( $role_map[ $user_role ] ) ? $role_map[ $user_role ] : mb_get_default_role();

	$user->add_role( $new_role );
}

/**
 * Sets a user's forum role.  If no role is given, the role will be set to the default mapping.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  string  $role
 */
function mb_set_user_role( $user_id, $role = '' ) {

	/* Get the user object. */
	$user = new WP_User( $user_id );

	$dynamic_roles = array_keys( mb_get_dynamic_roles() );

	foreach ( $dynamic_roles as $_d_role ) {

		if ( $_d_role !== $role && in_array( $_d_role, $user->roles ) )
			$user->remove_role( $_d_role );
	}

	if ( in_array( $role, $dynamic_roles ) )
		$user->add_role( $role );
}

/**
 * Removes a user's forum role.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  string  $role
 */
function mb_remove_user_role( $user_id, $role ) {

	/* Get the user object. */
	$user = new WP_User( $user_id );

	$dynamic_roles = array_keys( mb_get_dynamic_roles() );

	if ( in_array( $role, $dynamic_roles ) && in_array( $role, $user->roles ) )
		$user->remove_role( $role );
}

/**
 * Gets a user's forum role.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_get_user_role( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );
	$user    = new WP_User( $user_id );

	/* Get the user's forum roles. */
	$forum_roles = array_intersect( array_keys( mb_get_dynamic_roles() ), $user->roles );

	/* If the user has a forum role, use the first. Else, return an empty string. */
	$role = !empty( $forum_roles ) ? array_shift( $forum_roles ) : '';

	/* Return the forum role and allow devs to filter. */
	return apply_filters( 'mb_get_user_role', $role, $user_id );
}

/**
 * Conditional check to see if the user is a keymaster (i.e., forum admin).
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return bool
 */
function mb_is_user_keymaster( $user_id ) {
	$is_keymaster = mb_get_keymaster_role() === mb_get_user_role( $user_id ) ? true : false;

	return apply_filters( 'mb_is_user_keymaster', $is_keymaster, $user_id );
}

/**
 * Displays the translatable forum role name for a specific user.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return void
 */
function mb_user_role_name( $user_id = 0 ) {
	echo mb_get_user_role_name( $user_id );
}

/**
 * Returns the translatable forum role name for a specific user.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return string
 */
function mb_get_user_role_name( $user_id = 0 ) {
	$user_id = mb_get_user_id( $user_id );
	$role    = mb_get_user_role( $user_id );

	$name = !empty( $role ) ? mb_get_role_name( $role ) : '';

	/* Return the role name and allow devs to filter. */
	return apply_filters( 'mb_get_user_role_name', $name, $role, $user_id );
}

/**
 * Displays the name/label for a specific role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return void
 */
function mb_role_name( $role = '' ) {
	echo mb_get_role_name( $role );
}

/**
 * Returns the name/label for a specific role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function mb_get_role_name( $role = '' ) {
	$role  = mb_get_role( $role );
	$roles = mb_get_dynamic_roles();
	$name  = isset( $roles[ $role ] ) ? mb_get_role_object( $role )->labels->singular_name : '';

	return apply_filters( 'mb_get_role_name', $name, $role );
}

/**
 * Displays the URL (/board/users/roles/rolename) for a specific role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return void
 */
function mb_role_url( $role = '' ) {
	echo mb_get_role_url( $role );
}

/**
 * Returns the URL (/board/users/roles/rolename) for a specific role.  Note that we remove the `mb_` prefix
 * for prettier URLs.  Only forum-specific roles get archive pages.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function mb_get_role_url( $role = '' ) {
	global $wp_rewrite;

	$role          = mb_get_role( $role );
	$dynamic_roles = mb_get_dynamic_roles();
	$url           = '';

	if ( isset( $dynamic_roles[ $role ] ) ) {

		if ( $wp_rewrite->using_permalinks() )
			$url = user_trailingslashit( trailingslashit( mb_get_role_archive_url() ) . str_replace( 'mb_', '', $role ) );
		else
			$url = add_query_arg( 'mb_role', $role, mb_get_user_archive_url() );
	}

	return apply_filters( 'mb_get_user_archive_url', $url );
}

/**
 * Outputs the user role archive link.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return void
 */
function mb_role_link( $role = '' ) {
	echo mb_get_role_link( $role );
}

/**
 * Returns the user role archive link.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return void
 */
function mb_get_role_link( $role = '' ) {

	$role = mb_get_role( $role );
	$url  = mb_get_role_url( $role );
	$text = mb_get_role_name( $role );

	$link = !empty( $role ) && !empty( $url ) ? sprintf( '<a class="mb-role-link" href="%s">%s</a>', $url, $text ) : '';

	return apply_filters( 'mb_get_role_link', $link, $role );
}

/**
 * Maps default WordPress roles to the plugin's roles.  This is the default used when a user doesn't yet 
 * have a forum role.  Developers can add custom-created roles to the map using a filter on the 
 * `mb_get_role_map` hook.  Roles are mapped in key/value pairs.  The key is the WP or custom role.  The 
 * value is the forum role to map it to.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_role_map() {

	$default_role = mb_get_default_role();

	$mapped = array(
		'administrator' => mb_get_keymaster_role(),
		'editor'        => $default_role,
		'author'        => $default_role,
		'contributor'   => $default_role,
		'subscriber'    => $default_role
	);

	return apply_filters( 'mb_get_role_map', $mapped, $default_role );
}

/**
 * Makes sure the current user has a forum role.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_set_current_user_role() {

	/* If user is not logged in, bail. */
	if ( !is_user_logged_in() )
		return;

	/* Get the current user ID. */
	$user_id = get_current_user_id();

	/* Get the user's forum role. */
	$forum_role = mb_get_user_role( $user_id );

	/* If the user already has a forum role, bail. */
	if ( $forum_role )
		return;

	/* Set the user forum role. */
	mb_add_user_role( $user_id );
}

/**
 * Removes the plugin's dynamic roles from the editable roles list.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $roles
 * @return array
 */
function mb_editable_roles_filter( $roles ) {

	foreach ( array_keys( mb_get_dynamic_roles() ) as $forum_role ) {

		if ( isset( $roles[ $forum_role ] ) )
			unset( $roles[ $forum_role ] );
	}

	return $roles;
}

/**
 * `<select>` dropdown for displaying the forum roles in a form.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string|void
 */
function mb_dropdown_roles( $args = array() ) {

	$defaults = array(
		'name'              => 'mb_forum_role',
		'id'                => 'mb_forum_role',
		'selected'          => '',
		'exclude'           => array(),
		'show_option_none'  => null,
		'option_none_value' => '',
		'echo'              => true,
	);

	$args = wp_parse_args( $args, $defaults );

	$dynamic_roles = mb_get_dynamic_roles();

	$out = sprintf( '<select id="%s" name="%s">', sanitize_html_class( $args['id'] ), sanitize_html_class( $args['name'] ) );

	if ( !is_null( $args['show_option_none'] ) )
		$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $args['option_none_value'] ), selected( $args['option_none_value'], $args['selected'], false ), esc_html( $args['show_option_none'] ) );

	foreach ( $dynamic_roles as $role => $role_args ) {

		if ( in_array( $role, (array)$args['exclude'] ) )
			continue;

		$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $role ), selected( $role, $args['selected'], false ), esc_html( $role_args->labels->singular_name ) );
	}

	$out .= '</select>';

	if ( !$args['echo'] )
		return $out;

	echo $out;
}
