<?php
/**
 * Handles all the functionality for the `edit.php` screen for the reply post type. 
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_Edit_Replies {

	/**
	 * Holds the instances of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Sets up needed actions/filters for the admin to initialize.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		/* Only run our customization on the 'edit.php' page in the admin. */
		add_action( 'load-edit.php', array( $this, 'load_edit' ) );

		/* Hook to the edit reply handler. */
		add_action( 'mb_load_edit_reply', array( $this, 'handler' ), 0 );
	}

	/**
	 * Adds a custom filter on 'request' when viewing the edit menu items screen in the admin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_edit() {

		/* Get the current screen object. */
		$screen = get_current_screen();

		/* Get the reply post type name. */
		$reply_type = mb_get_reply_post_type();

		/* Bail if we're not on the edit topic screen. */
		if ( !empty( $screen->post_type ) && $screen->post_type !== $reply_type )
			return;

		/* Custom action for loading the edit screen. */
		do_action( 'mb_load_edit_reply' );

		/* Filter the `request` vars. */
		add_filter( 'request', array( $this, 'request' ) );

		/* Enqueue custom styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles'  ) );

		/* Add custom admin notices. */
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		/* Filter the bulk actions. */
		add_filter( "bulk_actions-{$screen->id}", array( $this, 'bulk_actions' ) );

		/* Handle custom columns. */
		add_filter( "manage_edit-{$reply_type}_columns",          array( $this, 'edit_columns'            )        );
		add_filter( "manage_edit-{$reply_type}_sortable_columns", array( $this, 'manage_sortable_columns' )        );
		add_action( "manage_{$reply_type}_posts_custom_column",   array( $this, 'manage_columns'          ), 10, 2 );

		/* Filter the row actions. */
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );

		/* Filter post states (shown next to post title). */
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 0, 2 );
	}

	/**
	 * Filter on the `request` hook to change what posts are loaded.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $vars
	 * @return array
	 */
	public function request( $vars ) {

		$new_vars = array();

		/* Load replies of a specific topic. */
		if ( isset( $_GET['post_parent'] ) ) {

			$new_vars['post_parent'] = mb_get_topic_id( $_GET['post_parent'] );
		}

		/* Load replies of a specific forum. */
		elseif ( isset( $_GET['mb_forum'] ) ) {

			$topic_ids = mb_get_forum_topic_ids( mb_get_forum_id( $_GET['mb_forum'] ) );

			$new_vars['post_parent__in'] = (array)$topic_ids;
		}

		/* Order replies by their forums. */
		elseif ( isset( $vars['orderby'] ) && 'forum' === $vars['orderby'] ) {

			// @todo - Fix or remove
			//$new_vars['orderby']  = 'meta_value_num';
			//$new_vars['meta_key'] = mb_get_reply_forum_id_meta_key();
		}

		/* Order replies by their topics. */
		elseif ( isset( $vars['orderby'] ) && 'post_parent' === $vars['orderby'] ) {

			$new_vars['orderby'] = 'post_parent';
		}

		/* Order replies by their author. */
		elseif ( isset( $vars['orderby'] ) && 'post_author' === $vars['orderby'] ) {

			$new_vars['orderby'] = 'post_author';
		}

		/* Return the vars, merging with the new ones. */
		return array_merge( $vars, $new_vars );
	}

	/**
	 * Customize the bulk actions drop-down.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $actions
	 * @return array
	 */
	public function bulk_actions( $actions ) {

		/* If the edit action is set, remove it. */
		if ( isset( $actions['edit'] ) )
			unset( $actions['edit'] );

		return $actions;
	}

	/**
	 * Customize the columns on the edit post screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $post_columns
	 * @return array
	 */
	public function edit_columns( $post_columns ) {

		$screen     = get_current_screen();
		$post_type  = $screen->post_type;
		$columns    = array();

		/* Add custom columns. */
		$columns['cb']        = $post_columns['cb'];
		$columns['title']     = __( 'Reply',   'message-board' );
		$columns['forum']     = __( 'Forum',   'message-board' );
		$columns['topic']     = __( 'Topic',   'message-board' );
		$columns['author']    = __( 'Author',  'message-board' );
		$columns['datetime']  = __( 'Created', 'message-board' );

		/* Return the columns. */
		return $columns;
	}

	/**
	 * Customize the sortable columns.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $columns
	 * @return array
	 */
	public function manage_sortable_columns( $columns ) {

		$columns['forum']  = array( 'forum',       true );
		$columns['topic']  = array( 'post_parent', true );
		$columns['author'] = array( 'post_author', true );

		return $columns;
	}

	/**
	 * Handles the output for custom columns.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $column
	 * @param  int     $post_id
	 */
	public function manage_columns( $column, $post_id ) {

		/* Forum column. */
		if ( 'forum' === $column ) {

			$forum_id = mb_get_reply_forum_id( $post_id );

			if ( 0 === $forum_id || mb_is_reply_orphan( $post_id ) ) {

				echo '&mdash;';
			} else {

				$post_type = get_post_type( $post_id );

				$url = add_query_arg( array( 'post_type' => $post_type, 'mb_forum' => $forum_id ), admin_url( 'edit.php' ) );

				printf( '<a href="%s">%s</a>', $url, mb_get_forum_title( $forum_id ) );
			}

		/* Topic column. */
		} elseif ( 'topic' === $column ) {

			$topic_id = mb_get_reply_topic_id( $post_id );

			if ( 0 === $topic_id || mb_is_reply_orphan( $post_id ) ) {

				echo '&mdash;';
			} else {

				$post_type = get_post_type( $post_id );

				$url = add_query_arg( array( 'post_type' => $post_type, 'post_parent' => $topic_id ), admin_url( 'edit.php' ) );

				printf( '<a href="%s">%s</a>', $url, mb_get_topic_title( $topic_id ) );
			}

		/* Datetime column. */
		} elseif ( 'datetime' === $column ) {

			the_time( get_option( 'date_format' ) );
			echo '<br />';
			the_time( get_option( 'time_format' ) );
		}
	}

	/**
	 * Custom row actions below the post title.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array   $actions
	 * @param  object  $post
	 * @return array
	 */
	function row_actions( $actions, $post ) {

		$reply_id = mb_get_reply_id( $post->ID );

		/* Remove quick edit. */
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		/* Add spam toggle link if user has permission. */
		if ( current_user_can( 'moderate_reply', $reply_id ) ) {

			/* Get post status objects. */
			$spam_object  = get_post_status_object( mb_get_spam_post_status() );

			/* Get spam link text. */
			$spam_text = mb_is_reply_spam( $reply_id ) ? __( 'Not Spam', 'message-board' ) : $spam_object->label;

			/* Build spam toggle URL. */
			$spam_url = remove_query_arg( array( 'reply_id', 'mb_reply_notice' ) );
			$spam_url = add_query_arg( array( 'reply_id' => $reply_id, 'action' => 'mb_toggle_spam' ), $spam_url );
			$spam_url = wp_nonce_url( $spam_url, "spam_reply_{$reply_id}" );

			/* Add toggle spam action link. */
			$actions['mb_toggle_spam'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $spam_url ), mb_is_reply_spam( $reply_id ) ? 'restore' : 'spam', $spam_text );
		}

		/* Move view action to the end. */
		if ( isset( $actions['view'] ) ) {
			$view_action = $actions['view'];
			unset( $actions['view'] );

			if ( mb_get_spam_post_status() !== get_query_var( 'post_status' ) )
				$actions['view'] = $view_action;
		}

		return $actions;
	}

	/**
	 * Filter for the `post_states` hook.  We're going to replace any defaults and roll our own.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array   $post_states
	 * @param  object  $post
	 */
	public function display_post_states( $post_states, $post ) {

		$states   = array();
		$reply_id = mb_get_reply_id( $post->ID );

		$states['reply-id'] = "<small>(#{$reply_id})</small>";

		return $states;
	}

	/**
	 * Callback function for handling post status changes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function handler() {

		/* Checks if the spam toggle link was clicked. */
		if ( isset( $_GET['action'] ) && 'mb_toggle_spam' === $_GET['action'] && isset( $_GET['reply_id'] ) ) {

			$reply_id = absint( $_GET['reply_id'] );

			/* Verify the nonce. */
			check_admin_referer( "spam_reply_{$reply_id}" );

			/* Assume the changed failed. */
			$notice = 'failure';

			/* Check if the reply is open. */
			$is_spam = mb_is_reply_spam( $reply_id );

			/* Update the post status. */
			$updated = $is_spam ? mb_unspam_reply( $reply_id ) : mb_spam_reply( $reply_id );

			/* If the status was updated, add notice slug. */
			if ( $updated && !is_wp_error( $updated ) ) {
				$notice = $is_spam ? 'restore' : mb_get_spam_post_status();
			}

			/* Redirect to correct admin page. */
			$redirect = add_query_arg( array( 'reply_id' => $reply_id, 'mb_reply_notice' => $notice ), remove_query_arg( array( 'action', 'reply_id', '_wpnonce' ) ) );
			wp_safe_redirect( $redirect );

			/* Always exit for good measure. */
			exit();
		}
	}

	/**
	 * Displays admin notices for the edit forum screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function admin_notices() {

		$allowed_notices = array( 'restore', mb_get_spam_post_status() );

		if ( isset( $_GET['mb_reply_notice'] ) && in_array( $_GET['mb_reply_notice'], $allowed_notices ) && isset( $_GET['reply_id'] ) ) {

			$notice   = $_GET['mb_reply_notice'];
			$reply_id = mb_get_reply_id( absint( $_GET['reply_id'] ) );

			if ( mb_get_spam_post_status() === $notice )
				$text = sprintf( __( 'The reply "%s" was successfully marked as spam.', 'message-board' ), mb_get_reply_title( $reply_id ) );

			elseif ( 'restore' === $notice )
				$text = sprintf( __( 'The reply "%s" was successfully removed from spam.', 'message-board' ), mb_get_reply_title( $reply_id ) );

			if ( !empty( $text ) )
				printf( '<div class="updated"><p>%s</p></div>', $text );
		}
	}

	/**
	 * Enqueue the plugin admin CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function print_styles() {
		wp_enqueue_style( 'message-board-admin' );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

Message_Board_Admin_Edit_Replies::get_instance();
