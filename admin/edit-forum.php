<?php
/**
 * Handles all the functionality for the `edit.php` screen for the forum post type. 
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_Edit_Forums {

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

		/* Hook to the forums handler. */
		add_action( 'mb_load_edit_forum', array( $this, 'handler' ), 0 );
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

		/* Get the forum post type name. */
		$forum_type = mb_get_forum_post_type();

		/* Bail if we're not on the edit forum screen. */
		if ( !empty( $screen->post_type ) && $screen->post_type !== $forum_type )
			return;

		/* Custom action for loading the edit forum screen. */
		do_action( 'mb_load_edit_forum' );

		/* Filter the `request` vars. */
		add_filter( 'request', array( $this, 'request' ) );

		/* Enqueue custom styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles'  ) );

		/* Add custom admin notices. */
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		/* Filter the bulk actions. */
		add_filter( "bulk_actions-{$screen->id}", array( $this, 'bulk_actions' ) );

		/* Handle custom columns. */
		add_filter( "manage_edit-{$forum_type}_columns",          array( $this, 'edit_columns'            )        );
		add_filter( "manage_edit-{$forum_type}_sortable_columns", array( $this, 'manage_sortable_columns' )        );
		add_action( "manage_{$forum_type}_posts_custom_column",   array( $this, 'manage_columns'          ), 10, 2 );

		/* Filter the row actions. */
		add_filter( 'page_row_actions', array( $this, 'row_actions' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 ); // In case forum post type is made non-hierarchial.

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

		/* Default ordering alphabetically. */
		if ( !isset( $vars['order'] ) && !isset( $vars['orderby'] ) ) {
			$vars = array_merge(
				$vars,
				array(
					'order'   => 'ASC',
					'orderby' => 'menu_order title'
				)
			);
		}

		/* Load forums with a specific type. */
		elseif ( isset( $_GET['forum_type'] ) ) {

			$forum_type = mb_get_forum_type_object( sanitize_key( $_GET['forum_type'] ) );

			if ( $forum_type ) {
				$new_vars['meta_key'] = mb_get_forum_type_meta_key();
				$new_var['meta_value'] = $forum_type->name;
			}
		}

		/* Order forums by their type. */
		elseif ( isset( $vars['orderby'] ) && 'forum_type' === $vars['orderby'] ) {

			$new_vars['orderby']  = 'meta_value';
			$new_vars['meta_key'] = mb_get_forum_type_meta_key();
		}

		/* Order forums by their topic count. */
		elseif ( isset( $vars['orderby'] ) && 'topic_count' === $vars['orderby'] ) {

			$new_vars['orderby']  = 'meta_value_num';
			$new_vars['meta_key'] = mb_get_forum_topic_count_meta_key();
		}

		/* Order forums by their reply count. */
		elseif ( isset( $vars['orderby'] ) && 'reply_count' === $vars['orderby'] ) {

			$new_vars['orderby']  = 'meta_value_num';
			$new_vars['meta_key'] = mb_get_forum_reply_count_meta_key();
		}

		/* Order forums by their author. */
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
	 * Customize the columns on the edit forum screen.
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
		$taxonomies = array();

		/* Checkbox column. */
		$columns['cb'] = $post_columns['cb'];

		/* Title column. */
		$columns['title'] = __( 'Forum', 'message-board' );

		/* Status column. */
		if ( !isset( $_GET['post_status'] ) )
			$columns['status'] = __( 'Status', 'message-board' );

		/* Type column. */
		if ( !isset( $_GET['forum_type'] ) )
			$columns['type']      = __( 'Type', 'message-board' );

		/* Topics, replies, and datetime columns. */
		$columns['topics']    = __( 'Topics',  'message-board' );
		$columns['replies']   = __( 'Replies', 'message-board' );
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

		$columns['type']    = array( 'forum_type',  true );
		$columns['topics']  = array( 'topic_count', true );
		$columns['replies'] = array( 'reply_count', true );
		$columns['author']  = array( 'post_author', true );

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

		switch( $column ) {

			/* Post status column. */
			case 'status' :

				$post_type = mb_get_forum_post_type();
				$status    = get_post_status_object( mb_get_forum_status( $post_id ) );

				/* If the forum has the "publish" post status, change it to "open". */
				if ( mb_get_publish_post_status() === $status->name )
					wp_update_post( array( 'ID' => $post_id, 'post_status' => mb_get_open_post_status() ) );

				$url = add_query_arg( array( 'post_status' => $status->name, 'post_type' => $post_type ), admin_url( 'edit.php' ) );

				printf( '<a href="%s">%s</a>', $url, $status->label );

				break;

			/* Forum type column. */
			case 'type' :

				$post_type = mb_get_forum_post_type();
				$forum_type = mb_get_forum_type_object( mb_get_forum_type( $post_id ) );

				$url = add_query_arg( array( 'post_type' => $post_type, 'forum_type' => $forum_type->name ), admin_url( 'edit.php' ) );

				printf( '<a href="%s">%s</a>', $url, $forum_type->label );

				break;

			/* Topic count column. */
			case 'topics' :

				$topic_count = mb_get_forum_topic_count( $post_id );
				$topic_count = !empty( $topic_count ) ? absint( $topic_count ) : number_format_i18n( 0 );

				if ( 0 < $topic_count && current_user_can( 'edit_topics' ) )
					printf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => mb_get_topic_post_type(), 'post_parent' => $post_id ), admin_url( 'edit.php' ) ), $topic_count );
				else
					echo $topic_count;

				break;

			/* Reply count column. */
			case 'replies' :

				$reply_count = mb_get_forum_reply_count( $post_id );
				$reply_count = !empty( $reply_count ) ? absint( $reply_count ) : number_format_i18n( 0 );

				if ( 0 < $reply_count && current_user_can( 'edit_replies' ) )
					printf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => mb_get_reply_post_type(), 'mb_forum' => $post_id ), admin_url( 'edit.php' ) ), $reply_count );
				else
					echo $reply_count;

				break;

			/* Datetime column. */
			case 'datetime' :

				the_time( get_option( 'date_format' ) );
				echo '<br />';
				the_time( get_option( 'time_format' ) );

				break;

			/* Just break out of the switch statement for everything else. */
			default :
				break;
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

		$forum_id = mb_get_forum_id( $post->ID );

		/* Remove quick edit. */
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		/* Add open/close toggle link if user has permission and forum is not spam. */
		if ( current_user_can( 'open_forum', $forum_id ) && !mb_is_forum_open( $forum_id ) ) {

			/* Get post status objects. */
			$open_object  = get_post_status_object( mb_get_open_post_status()  );

			/* Build open/close toggle URL. */
			$open_url = remove_query_arg( array( 'forum_id', 'mb_forum_notice' ) );
			$open_url = add_query_arg( array( 'forum_id' => $forum_id, 'mb_toggle_status' => 'open' ), $open_url );
			$open_url = wp_nonce_url( $open_url, "open_forum_{$forum_id}" );

			/* Add toggle open/close action link. */
			$actions['mb_toggle_open'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $open_url ), 'open', $open_object->mb_label_verb );
		}

		/* Add open/close toggle link if user has permission and forum is not spam. */
		if ( current_user_can( 'close_forum', $forum_id ) && !mb_is_forum_closed( $forum_id ) ) {

			/* Get post status objects. */
			$close_object  = get_post_status_object( mb_get_close_post_status()  );

			/* Build open/close toggle URL. */
			$close_url = remove_query_arg( array( 'forum_id', 'mb_forum_notice' ) );
			$close_url = add_query_arg( array( 'forum_id' => $forum_id, 'mb_toggle_status' => 'close' ), $close_url );
			$close_url = wp_nonce_url( $close_url, "close_forum_{$forum_id}" );

			/* Add toggle open/close action link. */
			$actions['mb_toggle_close'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $close_url ), 'close', $close_object->mb_label_verb );
		}

		/* Add open/close toggle link if user has permission and forum is not spam. */
		if ( current_user_can( 'archive_forum', $forum_id ) && !mb_is_forum_archived( $forum_id ) ) {

			/* Get post status objects. */
			$archive_object  = get_post_status_object( mb_get_archive_post_status()  );

			/* Build open/close toggle URL. */
			$archive_url = remove_query_arg( array( 'forum_id', 'mb_forum_notice' ) );
			$archive_url = add_query_arg( array( 'forum_id' => $forum_id, 'mb_toggle_status' => 'archive' ), $archive_url );
			$archive_url = wp_nonce_url( $archive_url, "archive_forum_{$forum_id}" );

			/* Add toggle open/close action link. */
			$actions['mb_toggle_archive'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $archive_url ), 'open', $archive_object->mb_label_verb );
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
		$forum_id = mb_get_forum_id( $post->ID );

		if ( mb_get_default_forum_id() === $forum_id )
			$states['default-forum'] = __( 'Default Forum', 'message-board' );

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

		/* Checks if the close toggle link was clicked. */
		if ( isset( $_GET['mb_toggle_status'] ) && isset( $_GET['forum_id'] ) ) {

			$forum_id = absint( mb_get_forum_id( $_GET['forum_id'] ) );

			/* Assume the changed failed. */
			$notice = 'failure';

			if ( 'open' === $_GET['mb_toggle_status'] && !mb_is_forum_open( $forum_id ) ) {

				/* Verify the nonce. */
				check_admin_referer( "open_forum_{$forum_id}" );

				/* Update the post status. */
				$updated = mb_open_forum( $forum_id );

				/* If the status was updated, add notice slug. */
				if ( $updated && !is_wp_error( $updated ) )
					$notice = mb_get_open_post_status();
			}

			elseif ( 'close' === $_GET['mb_toggle_status'] && !mb_is_forum_closed( $forum_id ) ) {

				/* Verify the nonce. */
				check_admin_referer( "close_forum_{$forum_id}" );

				/* Update the post status. */
				$updated = mb_close_forum( $forum_id );

				/* If the status was updated, add notice slug. */
				if ( $updated && !is_wp_error( $updated ) )
					$notice = mb_get_close_post_status();
			}

			elseif ( 'archive' === $_GET['mb_toggle_status'] && !mb_is_forum_archived( $forum_id ) ) {

				/* Verify the nonce. */
				check_admin_referer( "archive_forum_{$forum_id}" );

				/* Update the post status. */
				$updated = mb_archive_forum( $forum_id );

				/* If the status was updated, add notice slug. */
				if ( $updated && !is_wp_error( $updated ) )
					$notice = mb_get_archive_post_status();
			}

			/* Redirect to correct admin page. */
			$redirect = add_query_arg( array( 'forum_id' => $forum_id, 'mb_forum_notice' => $notice ), remove_query_arg( array( 'action', 'mb_toggle_status', 'forum_id', '_wpnonce' ) ) );
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

		$allowed_notices = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_archive_post_status() );

		/* If we have an allowed notice. */
		if ( isset( $_GET['mb_forum_notice'] ) && in_array( $_GET['mb_forum_notice'], $allowed_notices ) && isset( $_GET['forum_id'] ) ) {

			$notice   = $_GET['mb_forum_notice'];
			$forum_id = mb_get_forum_id( absint( $_GET['forum_id'] ) );

			if ( mb_get_close_post_status() === $notice )
				$text = sprintf( __( 'The forum "%s" was successfully closed.', 'message-board' ), mb_get_forum_title( $forum_id ) );

			elseif ( mb_get_open_post_status() === $notice )
				$text = sprintf( __( 'The forum "%s" was successfully opened.', 'message-board' ), mb_get_forum_title( $forum_id ) );

			elseif ( mb_get_archive_post_status() === $notice )
				$text = sprintf( __( 'The forum "%s" was successfully archived.', 'message-board' ), mb_get_forum_title( $forum_id ) );

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

Message_Board_Admin_Edit_Forums::get_instance();
