<?php
/**
 * Handles all the functionality for the `index.php` (dashboard) screen in the admin.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_Dashboard {

	/**
	 * Holds the instance of this class.
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
		add_action( 'load-index.php', array( $this, 'load_dashboard' ) );
	}

	/**
	 * Runs our actions only on the dashboard admin screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_dashboard() {

		/* Add dashboard widgets. */
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widgets' ) );

		/* Enqueue custom styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles'  ) );
	}

	/**
	 * Adds custom dashboard widgets.  Note that we're using `add_meta_box()` rather than the 
	 * `wp_add_dashboard_widget()` function so that we can control the positioning.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function dashboard_widgets() {

		add_meta_box(
			'mb-dashboard-activity',
			__( 'Forum Activity', 'message-board' ),
			'mb_dashboard_activity_meta_box',
			'dashboard',
			'side',
			'high'
		);
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

Message_Board_Admin_Dashboard::get_instance();
