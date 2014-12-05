<?php

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
	}

	/**
	 * Adds a custom filter on 'request' when viewing the edit menu items screen in the admin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_edit() {
		$screen = get_current_screen();

		$reply_type = mb_get_reply_post_type();

		if ( !empty( $screen->post_type ) && $screen->post_type !== $reply_type )
			return;

		add_action( 'mb_edit_replies_handler', array( $this, 'handler' ), 0 );

		do_action( 'mb_edit_replies_handler' );

		add_filter( 'request', array( $this, 'request' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles'  ) );

		add_filter( "manage_edit-{$reply_type}_columns",          array( $this, 'edit_columns'            )        );
		add_filter( "manage_edit-{$reply_type}_sortable_columns", array( $this, 'manage_sortable_columns' )        );
		add_action( "manage_{$reply_type}_posts_custom_column",   array( $this, 'manage_columns'          ), 10, 2 );

		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
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

			$new_vars['meta_key']  = mb_get_reply_forum_id_meta_key();
			$new_vars['meta_value'] = mb_get_forum_id( $_GET['mb_forum'] );
		}

		/* Order replies by their forums. */
		elseif ( isset( $vars['orderby'] ) && 'forum' === $vars['orderby'] ) {

			$new_vars['orderby']  = 'meta_value_num';
			$new_vars['meta_key'] = mb_get_reply_forum_id_meta_key();
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

	public function admin_notices() {

		$allowed_notices = array( 'spammed', 'unspammed' );

		if ( isset( $_GET['mb_reply_notice'] ) && in_array( $_GET['mb_reply_notice'], $allowed_notices ) && isset( $_GET['reply_id'] ) ) {

			$notice   = $_GET['mb_reply_notice'];
			$reply_id = mb_get_reply_id( absint( $_GET['reply_id'] ) );

			if ( 'spammed' === $notice )
				$text = sprintf( __( 'The reply "%s" was successfully marked as spam.', 'message-board' ), mb_get_reply_title( $reply_id ) );

			elseif ( 'unspammed' === $notice )
				$text = sprintf( __( 'The reply "%s" was successfully removed from spam.', 'message-board' ), mb_get_reply_title( $reply_id ) );

			if ( !empty( $text ) ) { ?>
				<div class="updated">
					<p><?php echo $text; ?>	</p>
				</div>
			<?php }
		}
	}

	public function edit_columns( $post_columns ) {

		$screen     = get_current_screen();
		$post_type  = $screen->post_type;
		$columns    = array();
		$taxonomies = array();

		/* Adds the checkbox column. */
		$columns['cb'] = $post_columns['cb'];

		/* Add custom columns and overwrite the 'title' column. */
		$columns['title']     = __( 'Reply',      'message-board' );
		$columns['forum']     = __( 'Forum',      'message-board' );
		$columns['topic']     = __( 'Topic',      'message-board' );
		$columns['author']    = __( 'Author',     'message-board' );
		$columns['datetime']  = __( 'Created',    'message-board' );

		/* Return the columns. */
		return $columns;
	}

	public function manage_sortable_columns( $columns ) {

		$columns['forum']  = array( 'forum',       true );
		$columns['topic']  = array( 'post_parent', true );
		$columns['author'] = array( 'post_author', true );

		return $columns;
	}

	public function manage_columns( $column, $post_id ) {

		switch( $column ) {

			case 'forum' :

				$forum_id = mb_get_reply_forum_id( $post_id );

				$post_type = get_post_type( $post_id );

				$url = add_query_arg( array( 'post_type' => $post_type, 'mb_forum' => $forum_id ), admin_url( 'edit.php' ) );

				printf( '<a href="%s">%s</a>', $url, mb_get_forum_title( $forum_id ) );

				break;

			case 'topic' :

				$topic_id = mb_get_reply_topic_id( $post_id );

				$post_type = get_post_type( $post_id );

				$url = add_query_arg( array( 'post_type' => $post_type, 'post_parent' => $topic_id ), admin_url( 'edit.php' ) );

				printf( '<a href="%s">%s</a>', $url, mb_get_topic_title( $topic_id ) );

				break;

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

	function row_actions( $actions, $post ) {

		/* Remove quick edit. */
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		if ( current_user_can( 'manage_forums' ) ) {

			$reply_id = mb_get_reply_id( $post->ID );
			$is_spam  = mb_is_reply_spam( $reply_id );

			$text = $is_spam ? __( 'Not Spam', 'message-board' ) : __( 'Spam', 'message-board' );

			$url = remove_query_arg( array( 'reply_id', 'mb_reply_notice' ) );
			$url = add_query_arg( array( 'reply_id' => $reply_id, 'action' => 'mb_toggle_spam' ), $url );
			$url = wp_nonce_url( $url, "spam_reply_{$reply_id}" );

			$actions['mb_toggle_spam'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $text );
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

	public function handler() {

		// @todo - nonce
		if ( isset( $_GET['action'] ) && 'mb_toggle_spam' === $_GET['action'] && isset( $_GET['reply_id'] ) ) {

			$reply_id = absint( $_GET['reply_id'] );

			check_admin_referer( "spam_reply_{$reply_id}" );

			$notice = 'failure';

			$postarr = get_post( $reply_id, ARRAY_A );

			if ( !is_null( $postarr ) ) {

				$is_spam = mb_is_reply_spam( $reply_id );

				$new_status = $is_spam ? mb_get_publish_post_status() : mb_get_spam_post_status();

				if ( $postarr['post_status'] !== $new_status ) {

					$notice = $is_spam ? 'unspammed' : 'spammed';

					$postarr['post_status'] = $new_status;

					wp_update_post( $postarr );
				}
			}

			$redirect = add_query_arg( array( 'reply_id' => $reply_id, 'mb_reply_notice' => $notice ), remove_query_arg( array( 'action', 'reply_id', '_wpnonce' ) ) );
			wp_safe_redirect( $redirect );
			exit();
		}
	}

	/**
	 * Style adjustments for the manage menu items screen, particularly for adjusting the thumbnail 
	 * column in the table to make sure it doesn't take up too much space.
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
