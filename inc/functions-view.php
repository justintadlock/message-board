<?php
/**
 * Functionality for handling built-in and custom views.
 */

function mb_register_view( $handle, $args = array() ) {

	$defaults = array(
		'name'        => '',
		'description' => '',
		'query'       => array(),
		'capability'  => ''
	);

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $handle ) || empty( $args['title'] ) )
		return false;

	if ( !empty( $args['capability'] ) && !current_user_can( $args['capability'] ) )
		return false;

	$mb            = message_board();
	$handle        = sanitize_key( $handle );

	$args = apply_filters( "mb_register_view_{$handle}", $args );

	$args['title'] = esc_html( $args['title'] );

	$query_defaults = array(
		'posts_per_page' => mb_get_topics_per_page(),
		'order'          => 'DESC',
		'orderby'        => 'date'
	);

	$args['query'] = wp_parse_args( $args['query'], $query_defaults );

	return $mb->views[ $handle ] = $args;
}

function mb_deregister_view( $handle ) {

	$mb = message_board();

	if ( isset( $mb->views[ $handle ] ) )
		return false;

	unset( $mb->views[ $handle ] );

	return true;
}

/********** USER VIEWS *********************/

/* don't use the below */



function mb_user_view_url( $handle, $user_id ) {
	echo mb_get_user_view_url( $handle, $user_id );
}

function mb_get_user_view_url( $handle, $user_id ) {

	if ( !mb_user_view_exists( $handle ) )
		return '';

	return esc_url( trailingslashit( mb_get_user_profile_url( $user_id ) ) . $handle );
}

function mb_user_view_link( $handle, $user_id ) {
	echo mb_get_user_view_link( $handle );
}

function mb_get_user_view_link( $handle, $user_id ) {

	if ( !mb_user_view_exists( $handle, $user_id ) )
		return '';

	return sprintf( '<a class="user_view-link" href="%s">%s</a>', mb_get_user_view_url( $handle ), mb_get_user_view_title( $handle ) );
}

function mb_is_user_view( $handle = '' ) {

	if ( empty( $handle ) && get_query_var( 'mb_user_view' ) )
		return true;

	if ( get_query_var( 'mb_user_view' ) && empty( $handle ) )
		return true;

	if ( $handle == get_query_var( 'mb_user_view' ) && isset( message_board()->user_views[ $handle ] ) )
		return true;

	return false;
}

function mb_user_view_exists( $handle ) {
	$user_views = mb_get_user_views();

	return isset( $user_views[ $handle ] ) ? true : false;
}

function mb_get_user_views() {
	return message_board()->user_views;
}

function mb_get_user_view( $handle ) {
	$user_views = mb_get_user_views();

	return isset( $user_views[ $handle ] ) ? $user_views[ $handle ] : false;
}

function mb_user_view_title( $handle = '' ) {
	echo mb_get_user_view_title( $handle );
}

function mb_get_user_view_title( $handle = '' ) {

	if ( empty( $handle ) )
		$handle = get_query_var( 'mb_user_view' );

	if ( empty( $handle ) )
		return '';

	$user_view = mb_get_user_view( $handle );

	return $user_view['title'] ? $user_view['title'] : '';
}

function mb_user_view_description( $handle = '' ) {
	echo mb_get_user_view_description( $handle );
}

function mb_get_user_view_description( $handle = '' ) {

	if ( empty( $handle ) )
		$handle = get_query_var( 'mb_user_view' );

	if ( empty( $handle ) )
		return '';

	$user_view = mb_get_user_view( $handle );

	return $user_view['description'] ? $user_view['description'] : '';
}

function mb_register_user_view( $handle, $args = array() ) {

	$defaults = array(
		'name'        => '',
		'description' => '',
		'query'       => array(),
		'capability'  => ''
	);

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $handle ) || empty( $args['title'] ) )
		return false;

	if ( !empty( $args['capability'] ) && !current_user_can( $args['capability'] ) )
		return false;

	$mb            = message_board();
	$handle        = sanitize_key( $handle );

	$args = apply_filters( "mb_register_user_view_{$handle}", $args );

	$args['title'] = esc_html( $args['title'] );

	$query_defaults = array(
		'posts_per_page' => mb_get_topics_per_page(),
		'order'          => 'DESC',
		'orderby'        => 'date'
	);

	$args['query'] = wp_parse_args( $args['query'], $query_defaults );

	return $mb->user_views[ $handle ] = $args;
}

function mb_deregister_user_view( $handle ) {

	$mb = message_board();

	if ( isset( $mb->user_views[ $handle ] ) )
		return false;

	unset( $mb->user_views[ $handle ] );

	return true;
}
