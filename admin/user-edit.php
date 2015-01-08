<?php
/**
 * Handles all the functionality for the `user-edit.php` screen in WordPress.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_User_Edit {

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
		add_action( 'load-user-edit.php', array( $this, 'load_user_edit' ) );
		//add_action( 'load-profile.php',   array( $this, 'load_user_edit' ) );

		/* Callback for handling requests. */
		//add_action( 'load-users.php', array( $this, 'handler' ), 0 );
	}

	/**
	 * Adds actions/filters.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_user_edit() {

		/* Custom action for loading the edit screen. */
		do_action( 'mb_load_user_edit' );

		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );

		/* Must use `profile_update` to change forum role. Otherwise, WP will wipe it out. */
		add_action( 'profile_update',  array( $this, 'handler' ) );
		//add_action( 'edit_user_profile_update', array( $this, 'handler' ) );
	}

	/**
	 * Filter on the `request` hook to change what posts are loaded.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $user
	 * @return void
	 */
	public function profile_fields( $user ) {

		if ( !current_user_can( 'promote_users' ) || !current_user_can( 'edit_user', $user->ID ) )
			return; ?>

		<h3><?php _e( 'Forum', 'message-board' ); ?></h3>

		<table class="form-table">

			<tr>
				<th><label for="mb_forum_role"><?php _e( 'Forum Role', 'message-board' ); ?></label></th>

				<td>
					<?php mb_dropdown_roles( array( 'show_option_none' => __( 'No forum role', 'message-board' ), 'selected' => mb_get_user_role( $user->ID ) ) ); ?>
				</td>
			</tr>

		</table>
	<?php }

	/**
	 * Callback function for handling post status changes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  int    $user_id
	 * @return void
	 */
	public function handler( $user_id ) {

		if ( !current_user_can( 'promote_users' ) || !current_user_can( 'edit_user', $user_id ) )
			return;

		if ( !isset( $_POST['mb_forum_role'] ) )
			return;

		$forum_role = mb_get_user_role( $user_id );
		$new_role   = sanitize_key( $_POST['mb_forum_role'] );

		if ( $forum_role !== $new_role && !empty( $new_role ) )
			mb_set_user_role( $user_id, $new_role );

		elseif ( $forum_role !== $new_role && empty( $new_role ) )
			mb_remove_user_role( $user_id, $new_role );
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

Message_Board_Admin_User_Edit::get_instance();
