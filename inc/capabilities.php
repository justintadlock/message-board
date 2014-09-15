<?php

/**
 * Overwrites capabilities in certain scenarios.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $caps
 * @param  string  $cap
 * @param  int     $user_id
 * @param  array   $args
 * @return array
 */
function mb_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* If in the admin, we don't want users to be able to create new topics. */
	if ( is_admin() && in_array( $cap, array( 'create_forum_topics', 'create_forum_replies' ) ) ) {
		$caps = array( 'do_not_allow' );
	}

	return $caps;
}
