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

		/* Add custom fields. */
		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );

		/* Must use `profile_update` to change forum role. Otherwise, WP will wipe it out. */
		add_action( 'profile_update',  array( $this, 'role_update' ) );
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
			return;

		$role = mb_get_user_role( $user->ID ); ?>

		<h3><?php _e( 'Forum', 'message-board' ); ?></h3>

		<table class="form-table">

			<tr>
				<th><label for="mb_forum_role"><?php _e( 'Forum Role', 'message-board' ); ?></label></th>

				<td>
					<?php mb_dropdown_roles(
						array( 
							'selected'         => $role ? $role : mb_get_default_role(),
							'exclude'          => mb_is_user_keymaster( get_current_user_id() ) ? array() : array( mb_get_keymaster_role() )
						)
					); ?>
				</td>
			</tr>

		</table>
	<?php }

	/**
	 * Callback function for handling forum role changes.  Note that we needed to execute this function 
	 * on a different hook, `profile_update`.  Using the normal hooks on the edit user screen won't work 
	 * because WP will wipe out the role.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  int    $user_id
	 * @return void
	 */
	public function role_update( $user_id ) {

		/* If the current user can't promote users or edit this particular user, bail. */
		if ( !current_user_can( 'promote_users' ) || !current_user_can( 'edit_user', $user_id ) )
			return;

		/* Is this a forum role change? */
		if ( !isset( $_POST['mb_forum_role'] ) )
			return;

		/* Get keymaster variables. */
		$is_keymaster   = mb_is_user_keymaster( get_current_user_id() );
		$keymaster_role = mb_get_keymaster_role();

		/* Get role variables. */
		$forum_role = mb_get_user_role( $user_id );
		$new_role   = sanitize_key( $_POST['mb_forum_role'] );

		/* Only keymasters can promote/demote other keymasters. */
		if ( $is_keymaster && ( $keymaster_role === $forum_role || $keymaster_role === $new_role ) )
			return;

		/* If there's a new role and it doesn't match the old one, set it. */
		if ( $forum_role !== $new_role && !empty( $new_role ) )
			mb_set_user_role( $user_id, $new_role );

		/* If the new role is empty, remove the user's forum role. */
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
