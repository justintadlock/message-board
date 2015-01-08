<?php
/**
 * Plugin capabilities (i.e., permissions).
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
add_action( 'plugins_loaded', 'mb_register_user_roles', 0 );

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
		'create_forums'       => true,
		'edit_forums'         => true,
		'edit_others_forums'  => true,
		'read_forums'         => true,
		'read_private_forums' => true,
		'read_hidden_forums'  => true,

		// Topic caps
		'create_topics'       => true,
		'edit_topics'         => true,
		'edit_others_topics'  => true,
		'read_topics'         => true,
		'read_private_topics' => true,
		'read_hidden_topics'  => true,

		// Reply caps
		'create_replies'      => true,
		'edit_replies'        => true,
		'edit_others_replies' => true,
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
		'create_forums'       => true,
		'edit_forums'         => true,
		'read_forums'         => true,
		'read_private_forums' => true,
		'read_hidden_forums'  => true,

		// Topic caps
		'create_topics'       => true,
		'edit_topics'         => true,
		'edit_others_topics'  => true,
		'read_topics'         => true,
		'read_private_topics' => true,
		'read_hidden_topics'  => true,

		// Reply caps
		'create_replies'      => true,
		'edit_replies'        => true,
		'edit_others_replies' => true,
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
		'read_forums'         => true,
		'read_private_forums' => true,

		// Topic caps
		'create_topics'       => true,
		'edit_topics'         => true,
		'read_topics'         => true,
		'read_private_topics' => true,

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
		'read_forums'         => true,
		'read_private_forums' => true,

		// Topic caps
		'read_topics'         => true,
		'read_private_topics' => true,

		// Reply caps
		'read_replies'        => true,
	);

	return apply_filters( 'mb_get_spectator_role_caps', $caps );
}

/**
 * Returns the capabilities for the keymaster forum role. Note that we're explicitly denying all 
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
		'create_forums'       => false,
		'edit_forums'         => false,
		'edit_others_forums'  => false,
		'read_forums'         => false,
		'read_private_forums' => false,
		'read_hidden_forums'  => false,

		// Topic caps
		'create_topics'       => false,
		'edit_topics'         => false,
		'edit_others_topics'  => false,
		'read_topics'         => false,
		'read_private_topics' => false,
		'read_hidden_topics'  => false,

		// Reply caps
		'create_replies'      => false,
		'edit_replies'        => false,
		'edit_others_replies' => false,
		'read_replies'        => false,
	);

	return apply_filters( 'mb_get_banned_role_caps', $caps );
}

/**
 * Registers user roles with WordPress.  Typically, WordPress roles are saved to the database.  We're going 
 * to bypass this and hook our roles into other roles when the page is loaded.  This allows us to keep the 
 * roles dynamic without having to save them to the DB.
 *
 * @since  1.0.0
 * @access public
 * @global array  $wp_roles
 * @return void
 */
function mb_register_user_roles() {
	global $wp_roles;

	/* Make sure we have roles. */
	if ( !isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	/*
	 * Loop through each of the dynamic roles and merge them with the `$wp_roles` array. This is 
	 * kind of hacky, but it's the best we can do because there's no API for dynamic roles in WP.
	 */
	foreach ( mb_get_dynamic_roles() as $role => $args ) {

		/*
		 * Create a role object of our own. Typicaly, creating a `new WP_Role()` would handle 
		 * this, but that method will add it to the database.
		 */
		$role_obj               = new stdClass;
		$role_obj->name         = $role;
		$role_obj->capabilities = $args['capabilities'];

		/* Add the custom role. */
		$wp_roles->roles[ $role ]        = $args;
		$wp_roles->role_objects[ $role ] = $role_obj;
		$wp_roles->role_names[ $role ]   = $args['name'];
	}

	global $wpdb;
	$role_key = $wpdb->prefix . 'user_roles';

	add_filter( 'option_' . $role_key, 'mb_option_user_roles_filter' );
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
		$roles[ $role ] = $args;

	return $roles;
}

/**
 * Returns an array of the plugin's dynamic roles.  These roles are "dynamic" because they are not saved in 
 * the database.  Instead, they're added early in the page load.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_dynamic_roles() {

	$roles = array(
		mb_get_keymaster_role()   => array( 'name' => __( 'Keymaster',   'message-board' ), 'capabilities' => mb_get_keymaster_role_caps()   ),
		mb_get_moderator_role()   => array( 'name' => __( 'Moderator',   'message-board' ), 'capabilities' => mb_get_moderator_role_caps()   ),
		mb_get_participant_role() => array( 'name' => __( 'Participant', 'message-board' ), 'capabilities' => mb_get_participant_role_caps() ),
		mb_get_spectator_role()   => array( 'name' => __( 'Spectator',   'message-board' ), 'capabilities' => mb_get_spectator_role_caps()   ),
		mb_get_banned_role()      => array( 'name' => __( 'Banned',      'message-board' ), 'capabilities' => mb_get_banned_role_caps()      ),
	);

	return apply_filters( 'mb_get_dynamic_roles', $roles );
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

	$user = new WP_User( $user_id );

	$forum_roles = array_intersect( array_keys( mb_get_dynamic_roles() ), $user->roles );

	$role = !empty( $forum_roles ) ? array_shift( $forum_roles ) : '';

	return apply_filters( 'mb_get_user_forum_role', $role, $user_id );
}

/**
 * Maps default WordPress roles to the plugin's roles.  This is the default used when a user doesn't yet 
 * have a forum role.
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
 * Returns an array of common capabilities used throughout the forums.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_common_capabilities() {

	$caps = array(
		'manage'   => 'manage_forums',   // Can do anything
		'throttle' => 'bypass_throttle', // Doesn't have to wait to post new topics/replies
	);

	return apply_filters( 'mb_get_common_capabilities', $caps );
}

function mb_user_can( $user_id, $cap, $post_id ) {

	// @todo Check hierarchy.
	if ( in_array( $cap, array( 'read_forum', 'read_topic', 'read_reply' ) ) ) {

		$status_obj = get_post_status_object( get_post_status( $post_id ) );

		if ( false === $status_obj->private && false === $status_obj->protected )
			return true;
	}

	return user_can( $user_id, $cap, $post_id );
}

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

		$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $role ), selected( $role, $args['selected'], false ), esc_html( $role_args['name'] ) );
	}

	$out .= '</select>';

	if ( !$args['echo'] )
		return $out;

	echo $out;
}
