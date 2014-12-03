<?php

final class Message_Board_Admin_Post_Reply {

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

		if ( empty( $screen->post_type ) || $screen->post_type !== mb_get_reply_post_type() )
			return;

		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles' ) );

		add_action( "add_meta_boxes_{$screen->post_type}", array( $this, 'add_meta_boxes' ) );
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

Message_Board_Admin_Post_Reply::get_instance();
