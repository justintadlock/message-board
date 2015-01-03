<?php
/**
 * Forum types API.  Forum types are a way to distinguish between different types of forums.  The default 
 * types are "forum" and "category".  Developers can add new types if they wish to do so.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register forum types. */
add_action( 'init', 'mb_register_forum_types' );

/**
 * Registers custom forum types.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_forum_types() {

	/* Forum type args. */
	$forum_args = array(
		'topics_allowed'    => true,
		'subforums_allowed' => true,
		'_builtin'          => true,
		'_internal'         => true,
		'label'             => __( 'Normal', 'message-board' ),
	);

	/* Category type args. */
	$category_args = array(
		'topics_allowed'    => false,
		'subforums_allowed' => true,
		'_builtin'          => true,
		'_internal'         => false,
		'label'             => __( 'Category', 'message-board' ),
	);

	/* Register forum types. */
	mb_register_forum_type( 'forum',    apply_filters( 'mb_forum_forum_type_args',    $forum_args    ) );
	mb_register_forum_type( 'category', apply_filters( 'mb_category_forum_type_args', $category_args ) );
}

/**
 * Registers a new forum type.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @param  array   $args
 * @return void
 */
function mb_register_forum_type( $name, $args = array() ) {

	$name = sanitize_key( $name );

	if ( !mb_forum_type_exists( $name ) ) {

		$defaults = array(
			'topics_allowed'    => true,  // Whether new topics can be posted.
			'subforums_allowed' => true,  // Whether new subforums can be created.
			'_builtin'          => false, // Internal use only! Whether the type is built in.
			'_internal'         => false, // Internal use only! Whether the type is internal (cannot be unregistered).
			'label'             => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$args['name'] = $name;

		message_board()->forum_types[ $name ] = (object) $args;
	}
}

/**
 * Unregister a forum type.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return void
 */
function mb_unregister_forum_type( $name ) {
	if ( mb_forum_type_exists( $name ) && false === mb_get_forum_type_object( $name )->_internal )
		unset( message_board()->forum_types[ $name ] );
}

/**
 * Check if a forum type is registered.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return bool
 */
function mb_forum_type_exists( $name ) {
	return isset( message_board()->forum_types[ $name ] );
}

/**
 * Returns an array of the registered forum type objects.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_forum_type_objects() {
	return message_board()->forum_types;
}

/**
 * Returns a single forum type object.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return object|bool
 */
function mb_get_forum_type_object( $name ) {
	return mb_forum_type_exists( $name ) ? message_board()->forum_types[ $name ] : false;
}

/**
 * Conditional check to see if a forum has the "category" type.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return bool
 */
function mb_is_forum_category( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	return 'category' === mb_get_forum_type( $forum_id ) ? true : false;
}

/**
 * Displays the forum type for a specific forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return void
 */
function mb_forum_type( $forum_id = 0 ) {
	echo mb_get_forum_type( $forum_id );
}

/**
 * Returns the forum type for a specific forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $forum_id
 * @return string
 */
function mb_get_forum_type( $forum_id = 0 ) {
	$forum_id = mb_get_forum_id( $forum_id );

	$forum_type = $forum_id ? get_post_meta( $forum_id, mb_get_forum_type_meta_key(), true ) : '';

	$forum_type = !empty( $forum_type ) && mb_forum_type_exists( $forum_type ) ? $forum_type : 'forum';

	return apply_filters( 'mb_get_forum_type', $forum_type, $forum_id );
}

/**
 * Sets the forum type for a specific forum.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @param  string  $forum_type
 * @return bool
 */
function mb_set_forum_type( $forum_id, $type ) {

	$type = mb_forum_type_exists( $type ) ? $type : 'forum';

	return update_post_meta( $forum_id, mb_get_forum_type_meta_key(), $type );
}

/**
 * Conditional check to see if a forum type allows new topics to be posted.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $type
 * @return bool
 */
function mb_forum_type_allows_topics( $type ) {
	return mb_get_forum_type_object( $type )->topics_allowed;
}

/**
 * Conditional check to see if a forum type allows new subforums to be created.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $type
 * @return bool
 */
function mb_forum_type_allows_subforums( $type ) {
	return mb_get_forum_type_object( $type )->subforums_allowed;
}

/**
 * Creates a dropdown `<select>` for selecting the forum type in forms.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_dropdown_forum_type( $args = array() ) {

	$defaults = array(
		'name'      => 'mb_forum_type',
		'id'        => 'mb_forum_type',
		'selected'  => mb_get_forum_type(),
		'echo'      => true
	);

	$args = wp_parse_args( $args, $defaults );

	$types = mb_get_forum_type_objects();

	$out = sprintf( '<select name="%s" id="%s">', sanitize_html_class( $args['name'] ), sanitize_html_class( $args['id'] ) );

	foreach ( $types as $type ) {
		$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $type->name ), selected( $type->name, $args['selected'], false ), $type->label );
	}

	$out .= '</select>';

	if ( !$args['echo'] )
		return $out;

	echo $out;
}
