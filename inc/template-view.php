<?php


function mb_view_url( $handle ) {
	echo mb_get_view_url( $handle );
}

function mb_get_view_url( $handle ) {

	if ( !mb_view_exists( $handle ) )
		return '';

	return esc_url( home_url( trailingslashit( mb_get_view_slug() ) . $handle ) );
}

function mb_view_link( $handle ) {
	echo mb_get_view_link( $handle );
}

function mb_get_view_link( $handle ) {

	if ( !mb_view_exists( $handle ) )
		return '';

	return sprintf( '<a class="view-link" href="%s">%s</a>', mb_get_view_url( $handle ), mb_get_view_title( $handle ) );
}

function mb_is_view( $handle = '' ) {

	if ( empty( $handle ) && get_query_var( 'mb_view' ) )
		return true;

	if ( get_query_var( 'mb_view' ) && empty( $handle ) )
		return true;

	if ( $handle == get_query_var( 'mb_view' ) && isset( message_board()->views[ $handle ] ) )
		return true;

	return false;
}

function mb_view_exists( $handle ) {
	$views = mb_get_views();

	return isset( $views[ $handle ] ) ? true : false;
}

function mb_get_views() {
	return message_board()->views;
}

function mb_get_view( $handle ) {
	$views = mb_get_views();

	return isset( $views[ $handle ] ) ? $views[ $handle ] : false;
}

function mb_view_title( $handle = '' ) {
	echo mb_get_view_title( $handle );
}

function mb_get_view_title( $handle = '' ) {

	if ( empty( $handle ) )
		$handle = get_query_var( 'mb_view' );

	if ( empty( $handle ) )
		return '';

	$view = mb_get_view( $handle );

	return $view['title'] ? $view['title'] : '';
}

function mb_view_description( $handle = '' ) {
	echo mb_get_view_description( $handle );
}

function mb_get_view_description( $handle = '' ) {

	if ( empty( $handle ) )
		$handle = get_query_var( 'mb_view' );

	if ( empty( $handle ) )
		return '';

	$view = mb_get_view( $handle );

	return $view['description'] ? $view['description'] : '';
}

