<?php

/**
 * Returns the number of topics to show per page.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_topics_per_page() {
	return intval( apply_filters( 'mb_get_topics_per_page', 15 ) );
}

/**
 * Returns the number of replies to show per page.
 *
 * @todo Plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function mb_get_replies_per_page() {
	return intval( apply_filters( 'mb_get_replies_per_page', 15 ) );
}

