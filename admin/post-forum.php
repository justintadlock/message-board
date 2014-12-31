<?php
/**
 * Handles all the functionality for the `post.php` and `post-new.php` screen for the forum post type. 
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin_Post_Forum {

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
		add_action( 'load-post.php',     array( $this, 'load_post' ) );
		add_action( 'load-post-new.php', array( $this, 'load_post' ) );
	}

	/**
	 * Callback function for the `load-post.php` or `load-post-new.php` screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function load_post() {
		$screen = get_current_screen();

		if ( empty( $screen->post_type ) || $screen->post_type !== mb_get_forum_post_type() )
			return;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( "add_meta_boxes_{$screen->post_type}", array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Loads scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'message-board-admin' );
		wp_enqueue_style(  'message-board-admin' );
	}

	/**
	 * Adds meta boxes needed for the edit post screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $post
	 * @return void
	 */
	public function add_meta_boxes( $post ) {

		/* Remove the WordPress submit meta box. */
		remove_meta_box( 'submitdiv', $post->post_type, 'side' );

		/* Add custom submit meta box. */
		add_meta_box( 'mb-submitdiv', __( 'Publish', 'message-board' ), 'mb_submit_meta_box', $post->post_type, 'side', 'core' );

		/* Add the forum attributes meta box. */
		add_meta_box( 'mb-forum-attributes', __( 'Forum Attributes', 'message-board' ), 'mb_forum_attributes_meta_box', $post->post_type, 'side', 'core' );
	}

	/**
	 * Callback for the `save_post` hook to handle meta boxes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  int     $post_id
	 * @param  object  $post
	 * @return void
	 */
	function save_post( $post_id, $post ) {

		/* Fix for attachment save issue in WordPress 3.5. @link http://core.trac.wordpress.org/ticket/21963 */
		if ( !is_object( $post ) )
			$post = get_post();

		if ( mb_get_forum_post_type() !== $post->post_type )
			return;

		if ( !isset( $_POST['mb_forum_attr_nonce'] ) || !wp_verify_nonce( $_POST['mb_forum_attr_nonce'], '_mb_forum_attr_nonce' ) )
			return;

		/* Set the forum type. */
		if ( isset( $_POST['mb_forum_type'] ) && mb_get_forum_type( $post_id ) !== $_POST['mb_forum_type'] )
			mb_set_forum_type( $post_id, sanitize_key( $_POST['mb_forum_type'] ) );
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

Message_Board_Admin_Post_Forum::get_instance();
