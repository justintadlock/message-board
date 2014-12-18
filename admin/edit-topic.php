<?php
/**
 * Handles all the functionality for the `edit.php` screen for the topic post type. 
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_Edit_Topics {

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

		/* Hook to the topics handler. */
		add_action( 'mb_load_edit_topic', array( $this, 'handler' ), 0 );
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

		/* Get the topic post type name. */
		$topic_type = mb_get_topic_post_type();

		/* Bail if we're not on the edit topic screen. */
		if ( !empty( $screen->post_type ) && $screen->post_type !== $topic_type )
			return;

		/* Custom action for loading the edit screen. */
		do_action( 'mb_load_edit_topic' );

		/* Filter the `request` vars. */
		add_filter( 'request', array( $this, 'request' ) );

		/* Enqueue custom styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles'  ) );

		/* Add custom admin notices. */
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		/* Add custom views. */
		add_filter( "views_edit-{$topic_type}", array( $this, 'views' ) );

		/* Filter the bulk actions. */
		add_filter( "bulk_actions-{$screen->id}", array( $this, 'bulk_actions' ) );

		/* Handle custom columns. */
		add_filter( "manage_edit-{$topic_type}_columns",          array( $this, 'edit_columns'            )        );
		add_filter( "manage_edit-{$topic_type}_sortable_columns", array( $this, 'manage_sortable_columns' )        );
		add_action( "manage_{$topic_type}_posts_custom_column",   array( $this, 'manage_columns'          ), 10, 2 );

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

		/* Load topics of a specific forum. */
		if ( isset( $_GET['post_parent'] ) ) {

			$new_vars['post_parent'] = mb_get_forum_id( $_GET['post_parent'] );
		}

		elseif ( isset( $_GET['show_super'] ) && 1 === absint( $_GET['show_super'] ) ) {

			$new_vars['post__in'] = mb_get_super_topics();
		}

		elseif ( isset( $_GET['show_sticky'] ) && 1 === absint( $_GET['show_sticky'] ) ) {
			$new_vars['post__in'] = mb_get_sticky_topics();
		}

		/* Order topics by their forums. */
		elseif ( isset( $vars['orderby'] ) && 'post_parent' === $vars['orderby'] ) {

			$new_vars['orderby'] = 'post_parent';
		}

		/* Order topics by their reply count. */
		elseif ( isset( $vars['orderby'] ) && 'reply_count' === $vars['orderby'] ) {

			$new_vars['orderby']  = 'meta_value_num';
			$new_vars['meta_key'] = mb_get_topic_reply_count_meta_key();
		}

		/* Order topics by their voice count. */
		elseif ( isset( $vars['orderby'] ) && 'voice_count' === $vars['orderby'] ) {

			$new_vars['orderby']  = 'meta_value_num';
			$new_vars['meta_key'] = mb_get_topic_voice_count_meta_key();
		}

		/* Order topics by their author. */
		elseif ( isset( $vars['orderby'] ) && 'post_author' === $vars['orderby'] ) {

			$new_vars['orderby'] = 'post_author';
		}

		/* Return the vars, merging with the new ones. */
		return array_merge( $vars, $new_vars );
	}

	/**
	 * Add custom views (status list).
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $views
	 * @return array
	 */
	public function views( $views ) {

		$post_type = mb_get_topic_post_type();

		$super  = mb_get_super_topics();
		$sticky = mb_get_sticky_topics();

		$super_count  = count( $super  );
		$sticky_count = count( $sticky );

		if ( 0 < $super_count ) {
			$super_text = sprintf( _n( 'Super <span class="count">(%s)</span>', 'Super <span class="count">(%s)</span>', $super_count, 'message-board' ), number_format_i18n( $super_count ) );
			$views['super'] = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => $post_type, 'show_super' => 1 ), admin_url( 'edit.php' ) ), $super_text );
		}

		if ( 0 < $sticky_count ) {
			$sticky_text = sprintf( _n( 'Sticky <span class="count">(%s)</span>', 'Sticky <span class="count">(%s)</span>', $sticky_count, 'message-board' ), number_format_i18n( $sticky_count ) );
			$views['sticky'] = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => $post_type, 'show_sticky' => 1 ), admin_url( 'edit.php' ) ), $sticky_text );
		}

		return $views;
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
		$columns['cb']       = $post_columns['cb'];
		$columns['title']    = __( 'Topic',   'message-board' );
		$columns['status']   = __( 'Status',  'message-board' );
		$columns['forum']    = __( 'Forum',   'message-board' );
		$columns['replies']  = __( 'Replies', 'message-board' );
		$columns['voices']   = __( 'Voices',  'message-board' );
		$columns['author']   = __( 'Author',  'message-board' );
		$columns['datetime'] = __( 'Created', 'message-board' );

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

		$columns['forum']   = array( 'post_parent', true );
		$columns['replies'] = array( 'reply_count', true );
		$columns['voices']  = array( 'voice_count', true );
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

		/* Post status column. */
		if ( 'status' === $column ) {

			$post_type = mb_get_topic_post_type();
			$status    = get_post_status_object( get_post_status( $post_id ) );

			if ( mb_get_publish_post_status() === $status->name )
				wp_update_post( array( 'ID' => $post_id, 'post_status' => mb_get_open_post_status() ) );

			$url = add_query_arg( array( 'post_status' => $status->name, 'post_type' => $post_type ), admin_url( 'edit.php' ) );

			printf( '<a href="%s">%s</a>', $url, $status->label );

		/* Topic forum column. */
		} elseif ( 'forum' === $column ) {

			$post_type = mb_get_topic_post_type();
			$forum_id  = mb_get_topic_forum_id( $post_id );

			$url = add_query_arg( array( 'post_type' => $post_type, 'post_parent' => $forum_id ), admin_url( 'edit.php' ) );

			printf( '<a href="%s">%s</a>', $url, mb_get_forum_title( $forum_id ) );

		/* Replies column. */
		} elseif ( 'replies' === $column ) {

			$reply_count = mb_get_topic_reply_count( $post_id );
			$reply_count = !empty( $reply_count ) ? absint( $reply_count ) : number_format_i18n( 0 );

			if ( 0 < $reply_count && current_user_can( 'edit_replies' ) )
				printf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => mb_get_reply_post_type(), 'post_parent' => $post_id ), admin_url( 'edit.php' ) ), $reply_count );
			else
				echo $reply_count;

		/* Voices column. */
		} elseif ( 'voices' === $column ) {

			$voice_count = mb_get_topic_voice_count( $post_id );

			echo !empty( $voice_count ) ? absint( $voice_count ) : number_format_i18n( 0 );

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

		$topic_id = mb_get_topic_id( $post->ID );

		/* Remove quick edit. */
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		/* Add delete link for spam and orphan replies. */
		if ( ( mb_is_topic_spam( $topic_id ) || mb_is_topic_orphan( $topic_id ) ) && current_user_can( 'delete_post', $topic_id ) && EMPTY_TRASH_DAYS ) {

			$actions['delete'] = sprintf( '<a class="submitdelete" href="%s">%s</a>', get_delete_post_link( $topic_id, '', true ), __( 'Delete Permanently', 'message-board' ) );
		}

		/* Add spam toggle link if user has permission. */
		if ( current_user_can( 'moderate_topic', $topic_id ) ) {

			/* Get post status objects. */
			$spam_object  = get_post_status_object( mb_get_spam_post_status() );

			/* Get spam link text. */
			$spam_text = mb_is_topic_spam( $topic_id ) ? __( 'Not Spam', 'message-board' ) : $spam_object->label_verb;

			/* Build spam toggle URL. */
			$spam_url = remove_query_arg( array( 'topic_id', 'mb_topic_notice' ) );
			$spam_url = add_query_arg( array( 'topic_id' => $topic_id, 'action' => 'mb_toggle_spam' ), $spam_url );
			$spam_url = wp_nonce_url( $spam_url, "spam_topic_{$topic_id}" );

			/* Add toggle spam action link. */
			$actions['mb_toggle_spam'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $spam_url ), mb_is_topic_spam() ? 'restore' : 'spam', $spam_text );
		}

		/* Add open/close toggle link if user has permission and topic is not spam. */
		if ( current_user_can( 'moderate_topic', $topic_id ) && !mb_is_topic_spam( $topic_id ) ) {

			/* Get post status objects. */
			$open_object  = get_post_status_object( mb_get_open_post_status()  );
			$close_object = get_post_status_object( mb_get_close_post_status() );

			/* Get open/close link text. */
			$open_text = mb_is_topic_open() ? $close_object->label_verb : $open_object->label_verb;

			/* Build open/close toggle URL. */
			$open_url = remove_query_arg( array( 'topic_id', 'mb_topic_notice' ) );
			$open_url = add_query_arg( array( 'topic_id' => $topic_id, 'action' => 'mb_toggle_open' ), $open_url );
			$open_url = wp_nonce_url( $open_url, "open_topic_{$topic_id}" );

			/* Add toggle open/close action link. */
			$actions['mb_toggle_open'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $open_url ), mb_is_topic_open() ? 'close' : 'open', $open_text );
		}

		/* Add sticky toggle link if user has permission and topic is not spam. */
		if ( current_user_can( 'moderate_topic', $topic_id ) && !mb_is_topic_spam( $topic_id ) ) {

			$current_url = remove_query_arg( array( 'topic_id', 'mb_topic_notice' ) );

			/* Build sticky text. */
			$sticky_text = mb_is_topic_sticky( $topic_id ) ? __( 'Unstick',  'message-board' ) : __( 'Stick', 'message-board' );
			$super_text  = mb_is_topic_super(  $topic_id ) ? __( 'Unsuper',  'message-board' ) : __( 'Super', 'message-board' );

			/* Build sticky toggle URL. */
			$sticky_url = add_query_arg( array( 'topic_id' => $topic_id, 'action' => 'mb_toggle_sticky' ), $current_url );
			$sticky_url = wp_nonce_url( $sticky_url, "sticky_topic_{$topic_id}" );

			/* Build super toggle URL. */
			$super_url = add_query_arg( array( 'topic_id' => $topic_id, 'action' => 'mb_toggle_super' ), $current_url );
			$super_url = wp_nonce_url( $super_url, "super_topic_{$topic_id}" );

			$actions['mb_toggle_sticky'] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $sticky_url ), 'sticky', $sticky_text );
			$actions['mb_toggle_super']  = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $super_url  ), 'super',  $super_text  );
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
		$topic_id = mb_get_topic_id( $post->ID );

		if ( mb_is_topic_super( $topic_id ) )
			$states['super'] = __( 'Super Sticky', 'message-board' );
		elseif ( mb_is_topic_sticky( $topic_id ) )
			$states['sticky'] = __( 'Sticky', 'message-board' );

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
		if ( isset( $_GET['action'] ) && 'mb_toggle_spam' === $_GET['action'] && isset( $_GET['topic_id'] ) ) {

			$topic_id = absint( mb_get_topic_id( $_GET['topic_id'] ) );

			/* Verify the nonce. */
			check_admin_referer( "spam_topic_{$topic_id}" );

			/* Assume the changed failed. */
			$notice = 'failure';

			/* Check if the topic is open. */
			$is_spam = mb_is_topic_spam( $topic_id );

			/* Update the post status. */
			$updated = $is_spam ? mb_unspam_topic( $topic_id ) : mb_spam_topic( $topic_id );

			/* If the status was updated, add notice slug. */
			if ( $updated && !is_wp_error( $updated ) ) {
				$notice = $is_spam ? 'restore' : mb_get_spam_post_status();
			}

			/* Redirect to correct admin page. */
			$redirect = add_query_arg( array( 'topic_id' => $topic_id, 'mb_topic_notice' => $notice ), remove_query_arg( array( 'action', 'topic_id', '_wpnonce' ) ) );
			wp_safe_redirect( $redirect );

			/* Always exit for good measure. */
			exit();
		}

		/* Checks if the open/close toggle link was clicked. */
		elseif ( isset( $_GET['action'] ) && 'mb_toggle_open' === $_GET['action'] && isset( $_GET['topic_id'] ) ) {

			$topic_id = absint( mb_get_topic_id( $_GET['topic_id'] ) );

			/* Verify the nonce. */
			check_admin_referer( "open_topic_{$topic_id}" );

			/* Assume the changed failed. */
			$notice = 'failure';

			/* Check if the topic is open. */
			$is_open = mb_is_topic_open( $topic_id );

			/* Update the post status. */
			$updated = $is_open ? mb_close_topic( $topic_id ) : mb_open_topic( $topic_id );

			/* If the status was updated, add notice slug. */
			if ( $updated && !is_wp_error( $updated ) ) {
				$notice = $is_open ? mb_get_close_post_status() : mb_get_open_post_status();
			}

			/* Redirect to correct admin page. */
			$redirect = add_query_arg( array( 'topic_id' => $topic_id, 'mb_topic_notice' => $notice ), remove_query_arg( array( 'action', 'topic_id', '_wpnonce' ) ) );
			wp_safe_redirect( $redirect );

			/* Always exit for good measure. */
			exit();
		}

		/* Checks if the sticky toggle link was clicked. */
		elseif ( isset( $_GET['action'] ) && 'mb_toggle_sticky' === $_GET['action'] && isset( $_GET['topic_id'] ) ) {

			$topic_id = absint( mb_get_topic_id( $_GET['topic_id'] ) );

			/* Verify the nonce. */
			check_admin_referer( "sticky_topic_{$topic_id}" );

			/* Assume the changed failed. */
			$notice = 'failure';

			/* Check if the topic is sticky. */
			$is_sticky = mb_is_topic_sticky( $topic_id );

			/* Update the post status. */
			$updated = $is_sticky ? mb_remove_sticky_topic( $topic_id ) : mb_add_sticky_topic( $topic_id );

			/* If the status was updated, add notice slug. */
			if ( $updated && !is_wp_error( $updated ) ) {
				$notice = $is_sticky ? 'unsticky' : 'sticky';
			}

			/* Redirect to correct admin page. */
			$redirect = add_query_arg( array( 'topic_id' => $topic_id, 'mb_topic_notice' => $notice ), remove_query_arg( array( 'action', 'topic_id', '_wpnonce' ) ) );
			wp_safe_redirect( $redirect );

			/* Always exit for good measure. */
			exit();
		}

		/* Checks if the super toggle link was clicked. */
		elseif ( isset( $_GET['action'] ) && 'mb_toggle_super' === $_GET['action'] && isset( $_GET['topic_id'] ) ) {

			$topic_id = absint( mb_get_topic_id( $_GET['topic_id'] ) );

			/* Verify the nonce. */
			check_admin_referer( "super_topic_{$topic_id}" );

			/* Assume the changed failed. */
			$notice = 'failure';

			/* Check if the topic is sticky. */
			$is_super = mb_is_topic_super( $topic_id );

			/* Update the post status. */
			$updated = $is_super ? mb_remove_super_topic( $topic_id ) : mb_add_super_topic( $topic_id );

			/* If the status was updated, add notice slug. */
			if ( $updated && !is_wp_error( $updated ) ) {
				$notice = $is_sticky ? 'unsuper' : 'super';
			}

			/* Redirect to correct admin page. */
			$redirect = add_query_arg( array( 'topic_id' => $topic_id, 'mb_topic_notice' => $notice ), remove_query_arg( array( 'action', 'topic_id', '_wpnonce' ) ) );
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

		$allowed_notices = array( 'restore', mb_get_spam_post_status(), mb_get_open_post_status(), mb_get_close_post_status(), 'sticky', 'unsticky', 'super', 'unsuper' );

		if ( isset( $_GET['mb_topic_notice'] ) && in_array( $_GET['mb_topic_notice'], $allowed_notices ) && isset( $_GET['topic_id'] ) ) {

			$notice   = $_GET['mb_topic_notice'];
			$topic_id = mb_get_topic_id( absint( $_GET['topic_id'] ) );

			if ( mb_get_spam_post_status() === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully marked as spam.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( 'restore' === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully removed from spam.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( mb_get_close_post_status() === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully closed.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( mb_get_open_post_status() === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully opened.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( 'sticky' === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully added as a sticky topic.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( 'unsticky' === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully removed from sticky topics.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( 'super' === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully added as a super sticky topic.', 'message-board' ), mb_get_topic_title( $topic_id ) );

			elseif ( 'unsuper' === $notice )
				$text = sprintf( __( 'The topic "%s" was successfully removed from super sticky topics.', 'message-board' ), mb_get_topic_title( $topic_id ) );

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

Message_Board_Admin_Edit_Topics::get_instance();
