<?php
/**
 * Handles all the functionality for the `users.php` screen in WordPress.
 *
 * @link  https://core.trac.wordpress.org/ticket/27887
 * @todo  WordPress doesn't currently support ordering users by `meta_value_num`.  Once that happens, we can 
 *        go in and add sorting by topics and replies.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_Users {

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

		/* Only run our customization on the 'users.php' page in the admin. */
		add_action( 'load-users.php', array( $this, 'load_users' ) );

		/* Callback for handling requests. */
		add_action( 'load-users.php', array( $this, 'handler' ), 0 );
	}

	/**
	 * Adds actions/filters.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_users() {

		/* Custom action for loading the edit screen. */
		do_action( 'mb_load_users' );

		/* Filter the `request` vars. */
		//add_filter( 'request', array( $this, 'request' ) );

		/* Enqueue custom styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles'  ) );

		/* Add custom admin notices. */
		//add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		/* Forum role selector. */
		add_action( 'restrict_manage_users', array( $this, 'roles_dropdown' ) );

		/* Handle custom columns. */
		add_filter( 'manage_users_columns',          array( $this, 'columns'          )        );
		//add_filter( 'manage_users_sortable_columns', array( $this, 'sortable_columns' )        );
		add_action( 'manage_users_custom_column',    array( $this, 'custom_column'    ), 10, 3 );
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

		/* Return the vars, merging with the new ones. */
		return array_merge( $vars, $new_vars );
	}

	/**
	 * Adds a forum roles dropdown above the users table.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function roles_dropdown() {
		if ( current_user_can( 'promote_users' ) ) : ?>

			<label class="screen-reader-text" for="mb_new_forum_role">
				<?php _e( 'Change forum role to&hellip;', 'message-board' ) ?>
			</label>

			<?php mb_dropdown_roles(
				array(
					'show_option_none' => __( 'Change forum role&hellip;', 'message-board' ),
					'exclude'          => mb_is_user_keymaster( get_current_user_id() ) ? array() : array( mb_get_keymaster_role() )
				)
			); ?>

			<?php submit_button( __( 'Change', 'message-board' ), 'button', 'mb_change_role', false ); ?>

		<?php endif;
	}

	/**
	 * Customize the columns on the edit post screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $columns
	 * @return array
	 */
	public function columns( $columns ) {

		/* Add custom columns. */
		$columns['forum_role'] = __( 'Forum Role', 'message-board' );
		$columns['topics']     = __( 'Topics',  'message-board' );
		$columns['replies']    = __( 'Replies', 'message-board' );

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
	public function sortable_columns( $columns ) {

		$columns['topics']  = array( 'topic_count', false );
		$columns['replies'] = array( 'reply_count', true );

		return $columns;
	}

	/**
	 * Handles the output for custom columns.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $column
	 * @param  string  $column_name
	 * @param  int     $post_id
	 */
	public function custom_column( $column, $column_name, $user_id ) {

		/* Forum role column. */
		if ( 'forum_role' === $column_name ) {

			$role   = mb_get_user_role_name( $user_id );
			$column = $role ? $role : '&mdash;';

		/* Post status column. */
		} elseif ( 'topics' === $column_name ) {

			$user_id     = mb_get_user_id( $user_id );
			$topic_count = mb_get_user_topic_count( $user_id );

			/* If the current user can create topics, link the topic count back to the edit topics screen. */
			if ( !empty( $topic_count ) && current_user_can( 'create_topics' ) ) {

				$url    = add_query_arg( array( 'post_type' => mb_get_topic_post_type(), 'author' => $user_id ), admin_url( 'edit.php' ) );
				$column = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( $url ), __( 'View topics by this user', 'message-board' ), $topic_count );

			/* Else, display the count. */
			} else {
				$column = !empty( $topic_count ) ? $topic_count : number_format_i18n( 0 );
			}

		/* Replies column. */
		} elseif ( 'replies' === $column_name ) {

			$user_id     = mb_get_user_id( $user_id );
			$reply_count = mb_get_user_reply_count( $user_id );

			/* If the current user can create replies, link the topic count back to the edit replies screen. */
			if ( !empty( $reply_count ) && current_user_can( 'create_replies' ) ) {

				$url = add_query_arg( array( 'post_type' => mb_get_reply_post_type(), 'author' => $user_id ), admin_url( 'edit.php' ) );
				$column = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( $url ), __( 'View replies by this user', 'message-board' ), $reply_count );

			/* Else, display the count. */
			} else {
				$column = !empty( $reply_count ) ? $reply_count : number_format_i18n( 0 );
			}
		}

		/* Return the filtered column. */
		return $column;
	}

	/**
	 * Callback function for handling post status changes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function handler() {

		/* If the current user can't promote users or there are no users, bail. */
		if ( !current_user_can( 'promote_users' ) || empty( $_REQUEST['users'] ) )
			return;

		/* Is this a forum role change request? */
		if ( empty( $_REQUEST['mb_forum_role'] ) || empty( $_REQUEST['mb_change_role'] ) )
			return;

		/* Get the new role. */
		$new_role = sanitize_key( $_REQUEST['mb_forum_role'] );

		/* Get an array of the allowed forum roles. */
		$dynamic_roles = mb_get_dynamic_roles();

		/* If the new role isn't one of our forum roles, bail. */
		if ( !isset( $dynamic_roles[ $new_role ] ) )
			return;

		/* Get some variables we need. */
		$current_user_id = get_current_user_id();
		$keymaster_role  = mb_get_keymaster_role();
		$is_keymaster    = mb_is_user_keymaster( $current_user_id );

		/* Only keymasters can make new keymasters. */
		if ( $keymaster_role === $new_role && false === $is_keymaster )
			return;

		/* Loop through each user and change their forum role. */
		foreach ( (array)$_REQUEST['users'] as $user_id ) {

			/* You can't go changing your own role, silly! */
			if ( $current_user_id === $user_id )
				continue;

			/* Get the user's current forum role. */
			$forum_role = mb_get_user_role( $user_id );

			/* Only keymasters can demote other keymasters. */
			if ( false === $is_keymaster && $keymaster_role === $forum_role )
				continue;

			/* If the new role doesn't match the user's current role, let's set it. */
			if ( $new_role !== $forum_role )
				mb_set_user_role( $user_id, $new_role );
		}

		/* Remove some query args and build the redirect URL. */
		$url = remove_query_arg( array( 's', 'action', 'new_role', 'mb_forum_role', 'mb_change_role', 'action2', 'users' ) );

		/* Redirect back to the users screen. */
		wp_safe_redirect( $url );
	}

	/**
	 * Displays admin notices for the edit forum screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function admin_notices() {}

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

Message_Board_Admin_Users::get_instance();
