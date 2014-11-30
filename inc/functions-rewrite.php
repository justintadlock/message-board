<?php

/* Custom query vars. */
add_filter( 'query_vars', 'mb_query_vars' );

/**
 * Returns the board root/index slug.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_root_slug() {
	return apply_filters( 'mb_root_slug', 'board' );
}

/**
 * Returns the board root/index slug or an empty string, depending on whether we need to use the root slug.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_maybe_get_root_slug() {
	return true == apply_filters( 'mb_maybe_get_root_slug', true ) ? trailingslashit( mb_get_root_slug() ) : '';
}

/**
 * Returns the topics slug.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_topic_slug() {
	return apply_filters( 'mb_topic_slug', mb_maybe_get_root_slug() . 'topics' );
}

/**
 * Returns the forums slug.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_forum_slug() {
	return apply_filters( 'mb_forum_slug', mb_maybe_get_root_slug() . 'forums' );
}

/**
 * Returns the users slug.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_user_slug() {
	return apply_filters( 'mb_get_user_slug', mb_maybe_get_root_slug() . 'users' );
}

/**
 * Returns the login slug.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_login_slug() {
	return apply_filters( 'mb_get_login_slug', mb_maybe_get_root_slug() . 'login' );
}

/**
 * Adds custom query vars.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_query_vars( $vars ) {

	if ( !array_search( 'edit', $vars ) )
		$vars[] = 'edit';

	return $vars;
}


/**
 * Sets up custom rewrite rules for pages that aren't handled by the CPT and CT APIs but are needed by 
 * the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_rewrite_rules() {

	/* Slugs and query vars. */

	$root_slug  = mb_get_root_slug();
	$user_slug  = mb_get_user_slug();
	$login_slug = mb_get_login_slug();

	$profile_query_var = 'mb_profile';
	$user_query_var    = 'mb_user_view';

	/* Rewrite tags. */

	add_rewrite_tag( '%' . $profile_query_var . '%', '([^/]+)' );
	add_rewrite_tag( '%' . $user_query_var    . '%', '([^/]+)' );

	add_rewrite_rule( $user_slug . '/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&' . $user_query_var . '=$matches[2]&paged=$matches[3]', 'top' );
	add_rewrite_rule( $user_slug . '/([^/]+)/([^/]+)/feed/?$',              'index.php?author_name=$matches[1]&' . $user_query_var . '=$matches[2]&feed=$matches[3]',  'top' );
	add_rewrite_rule( $user_slug . '/([^/]+)/([^/]+)/?$',                   'index.php?author_name=$matches[1]&' . $user_query_var . '=$matches[2]',                   'top' );
	add_rewrite_rule( $user_slug . '/([^/]+)/?$',                           'index.php?author_name=$matches[1]&mb_profile=1',                                          'top' );

	/* Login page. */
	add_rewrite_rule( '^' . $root_slug . '/' . $login_slug . '$', 'index.php', 'top' );
}

function mb_forum_rewrite_rules( $rules ) {

	return $rules;

	/**
	$forum_slug = mb_get_forum_slug();

	$rules = array(
		$forum_slug . '/.+?/attachment/([^/]+)/?$' => 'index.php?attachment=$matches[1]',
		$forum_slug . '/.+?/attachment/([^/]+)/trackback/?$' => 'index.php?attachment=$matches[1]&tb=1',
		$forum_slug . '/.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?attachment=$matches[1]&feed=$matches[2]',
		$forum_slug . '/.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?attachment=$matches[1]&feed=$matches[2]',
		$forum_slug . '/.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$' => 'index.php?attachment=$matches[1]&cpage=$matches[2]',
	//	$forum_slug . '/(.+?)/trackback/?$' => 'index.php?forum=$matches[1]&tb=1',
		$forum_slug . '/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?forum=$matches[1]&feed=$matches[2]',
		$forum_slug . '/(.+?)/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?forum=$matches[1]&feed=$matches[2]',
		$forum_slug . '/(.+?)/page/?([0-9]{1,})/?$' => 'index.php?forum=$matches[1]&paged=$matches[2]',
	//	$forum_slug . '/(.+?)/comment-page-([0-9]{1,})/?$' => 'index.php?forum=$matches[1]&cpage=$matches[2]',
		$forum_slug . '/(.+?)(/[0-9]+)?/?$' => 'index.php?forum=$matches[1]&page=$matches[2]',
	);
	/**/

	return $rules;
}

/**
 * Overwrites the rewrite rules for the `forum_topic` post type.  In particular, we need to handle the 
 * pagination on singular topics because the `forum_reply` post type is paginated on this page.
 *
 * @todo See if this can be simplified where we're only taking care of the things we need.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $rules
 * @return array
 */
function mb_forum_topic_rewrite_rules( $rules ) {

	$topic_slug = mb_get_topic_slug();

	$rules = array(
		$topic_slug . '/[^/]+/attachment/([^/]+)/?$'                               => 'index.php?attachment=$matches[1]',
		$topic_slug . '/[^/]+/attachment/([^/]+)/trackback/?$'                     => 'index.php?attachment=$matches[1]&tb=1',
		$topic_slug . '/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?attachment=$matches[1]&feed=$matches[2]',
		$topic_slug . '/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$'      => 'index.php?attachment=$matches[1]&feed=$matches[2]',
		$topic_slug . '/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$'      => 'index.php?attachment=$matches[1]&cpage=$matches[2]',
		$topic_slug . '/([^/]+)/trackback/?$'                                      => 'index.php?forum_topic=$matches[1]&tb=1',
		$topic_slug . '/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'                  => 'index.php?forum_topic=$matches[1]&feed=$matches[2]',
		$topic_slug . '/([^/]+)/(feed|rdf|rss|rss2|atom)/?$'                       => 'index.php?forum_topic=$matches[1]&feed=$matches[2]',
		$topic_slug . '/page/?([0-9]{1,})/?$'                                      => 'index.php?post_type=forum_topic&paged=$matches[1]',
		$topic_slug . '/([^/]+)/page/([0-9]{1,})/?$'                               => 'index.php?forum_topic=$matches[1]&paged=$matches[2]',
		$topic_slug . '/([^/]+)(/[0-9]+)?/?$'                                      => 'index.php?forum_topic=$matches[1]&page=$matches[2]',
		$topic_slug . '/([^/]+)/edit/([0-9]+)?/?$'                                 => 'index.php?forum_topic=$matches[1]&edit=$matches[2]',
		$topic_slug . '/[^/]+/([^/]+)/?$'                                          => 'index.php?attachment=$matches[1]',
		$topic_slug . '/[^/]+/([^/]+)/trackback/?$'                                => 'index.php?attachment=$matches[1]&tb=1',
		$topic_slug . '/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'            => 'index.php?attachment=$matches[1]&feed=$matches[2]',
		$topic_slug . '/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$'                 => 'index.php?attachment=$matches[1]&feed=$matches[2]',
		$topic_slug . '/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$'                 => 'index.php?attachment=$matches[1]&cpage=$matches[2]'
	);

	return $rules;
}

/**
 * Makes sure any paged redirects are corrected.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $redirect_url
 * @param  string  $requested_url
 * @return string
 */
function mb_redirect_canonical( $redirect_url, $requested_url ) {

	$topic_slug = mb_get_topic_slug();
	$forum_slug = mb_get_forum_slug();

	if ( preg_match( "#{$topic_slug}|{$forum_slug}/([^/]+)|(.+?)/page/([0-9]{1,})/?$#i", $requested_url ) )
		return false;

	return $redirect_url;
}
