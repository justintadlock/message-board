<?php
/**
 * Topic types API.  Topic types are a way to distinguish between different types of topics.  The default 
 * types are "topic/normal", "super", "sticky".  Developers can add new types if they wish to do so.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register topic types. */
add_action( 'init', 'mb_register_topic_types' );

/**
 * Returns the "normal" topic type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_normal_topic_type() {
	return apply_filters( 'mb_get_normal_topic_type', 'normal' );
}

/**
 * Returns the "super" topic type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_super_topic_type() {
	return apply_filters( 'mb_get_super_topic_type', 'super' );
}

/**
 * Returns the "sticky" topic type.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_sticky_topic_type() {
	return apply_filters( 'mb_get_sticky_topic_type', 'sticky' );
}

/**
 * Registers custom topic types.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_topic_types() {

	/* Topic type args. */
	$normal_args = array(
		'replies_allowed' => true,
		'_builtin'        => true,
		'_internal'       => true,
		'label'           => __( 'Normal', 'message-board' ),
		'label_count'     => _n_noop( 'Normal <span class="count">(%s)</span>', 'Normal <span class="count">(%s)</span>', 'message-board' ),
	);

	/* Super type args. */
	$super_args = array(
		'replies_allowed' => true,
		'_builtin'        => true,
		'_internal'       => false,
		'label'           => __( 'Super', 'message-board' ),
		'label_count'     => _n_noop( 'Super <span class="count">(%s)</span>', 'Super <span class="count">(%s)</span>', 'message-board' ),
	);

	/* Sticky type args. */
	$sticky_args = array(
		'replies_allowed' => true,
		'_builtin'        => true,
		'_internal'       => false,
		'label'           => __( 'Sticky', 'message-board' ),
		'label_count'     => _n_noop( 'Sticky <span class="count">(%s)</span>', 'Sticky <span class="count">(%s)</span>', 'message-board' ),
	);

	/* Register topic types. */
	mb_register_topic_type( mb_get_normal_topic_type(), apply_filters( 'mb_normal_topic_type_args', $normal_args ) );
	mb_register_topic_type( mb_get_super_topic_type(),  apply_filters( 'mb_super_topic_type_args',  $super_args  ) );
	mb_register_topic_type( mb_get_sticky_topic_type(), apply_filters( 'mb_sticky_topic_type_args', $sticky_args ) );
}

/**
 * Registers a new topic type.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @param  array   $args
 * @return void
 */
function mb_register_topic_type( $name, $args = array() ) {

	$name = sanitize_key( $name );

	if ( !mb_topic_type_exists( $name ) ) {

		$defaults = array(
			'replies_allowed' => true,  // Whether new replies can be posted.
			'_builtin'        => false, // Internal use only! Whether the type is built in.
			'_internal'       => false, // Internal use only! Whether the type is internal (cannot be unregistered).
			'label'           => '',
			'label_count'     => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$args['name'] = $name;

		message_board()->topic_types[ $name ] = (object) $args;
	}
}

/**
 * Unregister a topic type.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return void
 */
function mb_unregister_topic_type( $name ) {
	if ( mb_topic_type_exists( $name ) && false === mb_get_topic_type_object( $name )->_internal )
		unset( message_board()->topic_types[ $name ] );
}

/**
 * Check if a topic type is registered.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return bool
 */
function mb_topic_type_exists( $name ) {
	return isset( message_board()->topic_types[ $name ] );
}

/**
 * Returns an array of the registered topic type objects.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_topic_type_objects() {
	return message_board()->topic_types;
}

/**
 * Returns a single topic type object.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return object|bool
 */
function mb_get_topic_type_object( $name ) {
	return mb_topic_type_exists( $name ) ? message_board()->topic_types[ $name ] : false;
}

/**
 * Conditional check to see if a topic has the "normal" type.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_is_topic_normal( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	return mb_get_normal_topic_type() === mb_get_topic_type( $topic_id ) ? true : false;
}

/**
 * Conditional check to see if a topic has the "super" type.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_is_topic_super( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	return mb_get_super_topic_type() === mb_get_topic_type( $topic_id ) ? true : false;
}

/**
 * Conditional check to see if a topic has the "sticky" type.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_is_topic_sticky( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	return mb_get_sticky_topic_type() === mb_get_topic_type( $topic_id ) ? true : false;
}

/**
 * Displays the topic type for a specific topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return void
 */
function mb_topic_type( $topic_id = 0 ) {
	echo mb_get_topic_type( $topic_id );
}

/**
 * Returns the topic type for a specific topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return string
 */
function mb_get_topic_type( $topic_id = 0 ) {
	$topic_id = mb_get_topic_id( $topic_id );

	$topic_type = $topic_id ? get_post_meta( $topic_id, mb_get_topic_type_meta_key(), true ) : '';

	$topic_type = !empty( $topic_type ) && mb_topic_type_exists( $topic_type ) ? $topic_type : mb_get_normal_topic_type();

	return apply_filters( 'mb_get_topic_type', $topic_type, $topic_id );
}

/**
 * Sets the topic type for a specific topic.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @param  string  $topic_type
 * @return bool
 */
function mb_set_topic_type( $topic_id, $type ) {

	$type = mb_topic_type_exists( $type ) ? $type : mb_get_normal_topic_type();

	return update_post_meta( $topic_id, mb_get_topic_type_meta_key(), $type );
}

/**
 * Conditional check to see if a topic type allows new replies to be posted.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $type
 * @return bool
 */
function mb_topic_type_allows_replies( $type ) {
	return mb_get_topic_type_object( $type )->replies_allowed;
}

/**
 * Adds a topic to the list of super sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_add_super_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_sticky( $topic_id ) )
		mb_remove_sticky_topic( $topic_id );

	if ( !mb_is_topic_super( $topic_id ) )
		return update_option( 'mb_super_topics', array_unique( array_merge( mb_get_super_topics(), array( $topic_id ) ) ) );

	return false;
}

/**
 * Removes a topic from the list of super sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_remove_super_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_super( $topic_id ) ) {
		$supers = mb_get_super_topics();
		$key    = array_search( $topic_id, $supers );

		if ( isset( $supers[ $key ] ) ) {
			unset( $supers[ $key ] );
			return update_option( 'mb_super_topics', array_unique( $supers ) );
		}
	}

	return false;
}

/**
 * Adds a topic to the list of sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_add_sticky_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_super( $topic_id ) )
		mb_remove_super_topic( $topic_id );

	if ( !mb_is_topic_sticky( $topic_id ) )
		return update_option( 'mb_sticky_topics', array_unique( array_merge( mb_get_sticky_topics(), array( $topic_id ) ) ) );

	return false;
}

/**
 * Removes a topic from the list of sticky topics.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $topic_id
 * @return bool
 */
function mb_remove_sticky_topic( $topic_id ) {
	$topic_id = mb_get_topic_id( $topic_id );

	if ( mb_is_topic_sticky( $topic_id ) ) {
		$stickies = mb_get_sticky_topics();
		$key      = array_search( $topic_id, $stickies );

		if ( isset( $stickies[ $key ] ) ) {
			unset( $stickies[ $key ] );
			return update_option( 'mb_sticky_topics', array_unique( $stickies ) );
		}
	}

	return false;
}

/**
 * Creates a dropdown `<select>` for selecting the topic type in forms.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $args
 * @return string
 */
function mb_dropdown_topic_type( $args = array() ) {

	$defaults = array(
		'name'      => 'mb_topic_type',
		'id'        => 'mb_topic_type',
		'selected'  => mb_get_topic_type(),
		'echo'      => true
	);

	$args = wp_parse_args( $args, $defaults );

	$types = mb_get_topic_type_objects();

	$out = sprintf( '<select name="%s" id="%s">', sanitize_html_class( $args['name'] ), sanitize_html_class( $args['id'] ) );

	foreach ( $types as $type ) {
		$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $type->name ), selected( $type->name, $args['selected'], false ), $type->label );
	}

	$out .= '</select>';

	if ( !$args['echo'] )
		return $out;

	echo $out;
}
