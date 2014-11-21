<?php

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

		$forum_type = mb_get_forum_post_type();

		if ( !empty( $screen->post_type ) && $screen->post_type !== $forum_type )
			return;

		add_action( 'mb_edit_forums_handler', array( $this, 'handler' ), 0 );

		do_action( 'mb_edit_forums_handler' );

		add_filter( 'request', array( $this, 'request' ) );

		add_action( 'admin_head', array( $this, 'print_styles'  ) );

		add_filter( "manage_edit-{$forum_type}_columns",          array( $this, 'edit_columns'            )        );
		add_filter( "manage_edit-{$forum_type}_sortable_columns", array( $this, 'manage_sortable_columns' )        );
		add_action( "manage_{$forum_type}_posts_custom_column",   array( $this, 'manage_columns'          ), 10, 2 );

		add_filter( 'page_row_actions', array( $this, 'row_actions' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	public function admin_notices() {

		$allowed_notices = array( 'spammed', 'unspammed', 'opened', 'closed' );

		if ( isset( $_GET['mb_forum_notice'] ) && in_array( $_GET['mb_forum_notice'], $allowed_notices ) && isset( $_GET['forum_id'] ) ) {

			$notice   = $_GET['mb_forum_notice'];
			$forum_id = mb_get_forum_id( absint( $_GET['forum_id'] ) );

			if ( 'closed' === $notice )
				$text = sprintf( __( 'The forum "%s" was successfully closed.', 'message-board' ), mb_get_forum_title( $forum_id ) );

			elseif ( 'opened' === $notice )
				$text = sprintf( __( 'The forum "%s" was successfully opened.', 'message-board' ), mb_get_forum_title( $forum_id ) );

			if ( !empty( $text ) ) { ?>
				<div class="updated">
					<p><?php echo $text; ?>	</p>
				</div>
			<?php }
		}
	}

	/**
	 * Filter on the 'request' hook to change the 'order' and 'orderby' query variables when 
	 * viewing the "edit menu items" screen in the admin.  This is to order the menu items 
	 * alphabetically.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $vars
	 * @return array
	 */
	public function request( $vars ) {

		/* Default ordering alphabetically. */
		if ( !isset( $vars['order'] ) && !isset( $vars['orderby'] ) ) {
			$vars = array_merge(
				$vars,
				array(
					'order'   => 'ASC',
					'orderby' => 'title'
				)
			);
		}

		elseif ( isset( $vars['orderby'] ) && '_forum_topic_count' === $vars['orderby'] ) {

			$vars = array_merge(
				$vars,
				array(
					'orderby'  => 'meta_value_num',
					'meta_key' => '_forum_topic_count'
				)
			);
		}

		elseif ( isset( $vars['orderby'] ) && '_forum_reply_count' === $vars['orderby'] ) {

			$vars = array_merge(
				$vars,
				array(
					'orderby'  => 'meta_value_num',
					'meta_key' => '_forum_reply_count'
				)
			);
		}

		return $vars;
	}

	public function edit_columns( $post_columns ) {

		$screen     = get_current_screen();
		$post_type  = $screen->post_type;
		$columns    = array();
		$taxonomies = array();

		/* Adds the checkbox column. */
		$columns['cb'] = $post_columns['cb'];

		/* Add custom columns and overwrite the 'title' column. */
		$columns['title']     = __( 'Forum',      'message-board' );
		$columns['topics']    = __( 'Topics',     'message-board' );
		$columns['replies']   = __( 'Replies',    'message-board' );
		$columns['datetime']  = __( 'Created',    'message-board' );

		/* Return the columns. */
		return $columns;
	}

	public function manage_sortable_columns( $columns ) {

		$columns['topics']  = array( '_forum_topic_count', true );
		$columns['replies'] = array( '_forum_reply_count', true );

		return $columns;
	}

	public function manage_columns( $column, $post_id ) {

		switch( $column ) {

			case 'topics' :

				$topic_count = mb_get_forum_topic_count( $post_id );

				echo !empty( $topic_count ) ? absint( $topic_count ) : number_format_i18n( 0 );

				break;

			case 'replies' :

				$reply_count = mb_get_forum_reply_count( $post_id );

				echo !empty( $reply_count ) ? absint( $reply_count ) : number_format_i18n( 0 );

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

			$forum_id = mb_get_forum_id( $post->ID );

			/* Get close link text. */
			$close_text = mb_is_forum_closed( $forum_id ) ? __( 'Open', 'message-board' ) : __( 'Close', 'message-board' );

			/* Build close toggle URL. */
			$close_url = remove_query_arg( array( 'forum_id', 'mb_forum_notice' ) );
			$close_url = add_query_arg( array( 'forum_id' => $forum_id, 'action' => 'mb_toggle_close' ), $close_url );
			$close_url = wp_nonce_url( $close_url, "close_forum_{$forum_id}" );

			/* Add toggle close action link. */
			$actions['mb_toggle_close'] = sprintf( '<a href="%s">%s</a>', esc_url( $close_url ), $close_text );
		}

		/* Move view action to the end. */
		if ( isset( $actions['view'] ) ) {
			$view_action = $actions['view'];
			unset( $actions['view'] );

			if ( 'spam' !== get_query_var( 'post_status' ) )
				$actions['view'] = $view_action;
		}

		return $actions;
	}

	public function handler() {

		if ( isset( $_GET['action'] ) && 'mb_toggle_close' === $_GET['action'] && isset( $_GET['forum_id'] ) ) {

			$forum_id = absint( mb_get_forum_id( $_GET['forum_id'] ) );

			check_admin_referer( "close_forum_{$forum_id}" );

			$notice = 'failure';

			$postarr = get_post( $forum_id, ARRAY_A );

			if ( !is_null( $postarr ) ) {

				$is_closed = mb_is_forum_closed( $forum_id );

				$new_status = $is_closed ? 'publish' : 'close';

				if ( $postarr['post_status'] !== $new_status ) {

					$notice = $is_closed ? 'opened' : 'closed';

					$postarr['post_status'] = $new_status;

					wp_update_post( $postarr );
				}
			}

			$redirect = add_query_arg( array( 'forum_id' => $forum_id, 'mb_forum_notice' => $notice ), remove_query_arg( array( 'action', 'forum_id', '_wpnonce' ) ) );
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
	public function print_styles( ) { ?>
		<style type="text/css">
		.edit-php .wp-list-table .column-forum,
		.edit-php .wp-list-table .column-forum,
		.edit-php .wp-list-table .column-datetime { 
			width: 15%;
		}
		.edit-php .wp-list-table .column-forums,
		.edit-php .wp-list-table .column-replies,
		.edit-php .wp-list-table .column-voices {
			width: 10%;
		}
		</style>
	<?php }

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
