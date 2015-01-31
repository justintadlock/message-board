<?php
/**
 * Rather than building in our own breadcrumb system, let's just use a proper one that's already made for 
 * the job.  No need to reinvent the wheel.  Script for extending the Breadcrumb Trail plugin/script. 
 * Note that this requires version 1.0.0+ of Breadcrumb Trail to work properly.
 *
 * @package    MessageBoard
 * @subpackage Extensions
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Filter the breadcrumb object. */
add_filter( 'breadcrumb_trail_object', 'mb_breadcrumb_trail_object', 10, 2 );

/**
 * Filter on `breadcrumb_trail_object`.  This filter returns a custom object if viewing a page from 
 * the Message Board plugin.
 *
 * @since 1.0.0
 * @access public
 * @param  object|null  $breadcrumb
 * @param  array        $args
 * @return object|null
 */
function mb_breadcrumb_trail_object( $breadcrumb, $args ) {
	return mb_is_message_board() ? new MB_Breadcrumb_Trail( $args ) : $breadcrumb;
}

/**
 * Class that extends the main Breadcrumb Trail class.  We'll create our own breadcrumb trail items
 * for plugin pages only.
 *
 * @since  1.0.0
 * @access public
 */
class MB_Breadcrumb_Trail extends Breadcrumb_Trail {

	/**
	 * Overwrites the `do_trail_items()` method and creates custom trail items.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function do_trail_items() {

		/* Add the network and site home links. */
		$this->do_network_home_link();
		$this->do_site_home_link();
		$this->mb_do_board_home_link();

		/* Single forum, topic, or reply. */
		if ( mb_is_single_forum() || mb_is_single_topic() || mb_is_single_reply() ) {
			$this->do_singular_items();
		}

		/* Forum archive. */
		elseif ( mb_is_forum_archive() || mb_is_topic_archive() || mb_is_reply_archive() ) {
			$this->do_post_type_archive_items();
		}

		/* Role archive. */
		elseif ( mb_is_role_archive() ) {
			$this->mb_do_user_archive_link();

			if ( is_paged() )
				$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_role_archive_url(), mb_get_role_archive_title() );

			elseif ( $this->args['show_title'] )
				$this->items[] = mb_get_role_archive_title();
		}

		/* Single role. */
		elseif ( mb_is_single_role() ) {
			$this->mb_do_user_archive_link();
			$this->mb_do_role_archive_link();

			if ( is_paged() )
				$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_role_url(), mb_get_single_role_title() );

			elseif ( $this->args['show_title'] )
				$this->items[] = mb_get_single_role_title();
		}

		/* User archive. */
		elseif ( mb_is_user_archive() ) {

			if ( is_paged() )
				$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_archive_url(), mb_get_user_archive_title() );

			elseif ( $this->args['show_title'] )
				$this->items[] = mb_get_user_archive_title();
		}

		/* Single user. */
		elseif ( mb_is_single_user() ) {
			$this->mb_do_user_archive_link();

			/* If single user subpage. */
			if ( mb_is_user_page() ) {
				$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_url(), get_the_author_meta( 'display_name', mb_get_user_id() ) );

				if ( is_paged() ) {

					if ( mb_is_user_forums() )
						$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_forums_url(), mb_get_user_forums_title() );

					elseif ( mb_is_user_topics() )
						$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_topics_url(), mb_get_user_topics_title() );

					elseif ( mb_is_user_replies() )
						$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_replies_url(), mb_get_user_replies_title() );

					elseif ( mb_is_user_bookmarks() )
						$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_bookmarks_url(), mb_get_user_bookmarks_title() );

					elseif ( mb_is_user_forum_subscriptions() )
						$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_forum_subscriptions_url(), mb_get_user_forum_subscriptions_title() );

					elseif ( mb_is_user_topic_subscriptions() )
						$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_topic_subscriptions_url(), mb_get_user_topic_subscriptions_title() );

				} elseif ( $this->args['show_title'] ) {

					$this->items[] = mb_get_user_page_title();
				}

			/* If viewing the single user page but not a subpage. */
			} elseif ( $this->args['show_title'] ) {

				$this->items[] = mb_get_single_user_title();
			}

		/* Login page. */
		} elseif ( mb_is_forum_login() ) {

			$this->items[] = mb_get_login_page_title();
		}

		/* Add paged items. */
		$this->do_paged_items();

		/* Return the board breadcrumb trail items. */
		$this->items = apply_filters( 'mb_get_breadcrumb_trail_items', $this->items, $this->args );
	}

	/**
	 * Adds the board home link to `$items` array.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function mb_do_board_home_link() {

		if ( mb_is_forum_front() )
			return;

		$show_on_front = mb_get_show_on_front();

		if ( 'forums' === $show_on_front ) {
			$object = get_post_type_object( mb_get_forum_post_type() );
			$label  = mb_get_forum_label( 'archive_title' );

		} elseif ( 'topics' === $show_on_front ) {
			$object = get_post_type_object( mb_get_topic_post_type() );
			$label  = mb_get_topic_label( 'archive_title' );
		}

		$this->items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_post_type_archive_link( $object->name ) ), $label );
	}

	/**
	 * Adds the user archive link to `$items` array.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function mb_do_user_archive_link() {

		$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_user_archive_url(), mb_get_user_archive_title() );
	}

	/**
	 * Adds the role archive link to `$items` array.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function mb_do_role_archive_link() {

		$this->items[] = sprintf( '<a href="%s">%s</a>', mb_get_role_archive_url(), mb_get_role_archive_title() );
	}
}
