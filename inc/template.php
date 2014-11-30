<?php

function mb_search_form() {
	echo mb_get_search_form();
}

function mb_get_search_form() {

	add_filter( 'get_search_form', 'mb_search_form_filter', 5 );
	$form = apply_filters( 'mb_get_search_form', get_search_form( false ) );
	remove_filter( 'get_search_form', 'mb_search_form_filter', 5 );

	return $form;
}

function mb_search_form_filter( $form ) {

	$form = '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">
		<label>
			<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'message-board' ) . '</span>
			<input type="search" class="search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder', 'message-board' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label', 'message-board' ) . '" />
		</label>
		<input type="submit" class="search-submit" value="'. esc_attr_x( 'Search', 'submit button', 'message-board' ) .'" />
		<input type="hidden" name="post_type[]" value="forum_topic" />
		<input type="hidden" name="post_type[]" value="forum_reply" />
	</form>';

	return $form;
}
