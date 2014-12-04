<?php

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

		add_action( 'admin_enqueue_scripts', array( $this, 'print_styles' ) );

		add_action( "add_meta_boxes_{$screen->post_type}", array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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

		/* Return here if the template is not set. There's a chance it won't be if the post type doesn't have any templates. */
		if ( !isset( $_POST['mb_forum_type'] ) )
			return;

		/* Get the posted meta value. */
		$new_meta_value = $_POST['mb_forum_type'];

		/* Set the $meta_key variable based off the post type name. */
		$meta_key = mb_get_forum_type_meta_key();

		/* Get the meta value of the meta key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		/* If there is no new meta value but an old value exists, delete it. */
		if ( current_user_can( 'delete_post_meta', $post_id ) && '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );

		/* If a new meta value was added and there was no previous value, add it. */
		elseif ( current_user_can( 'add_post_meta', $post_id, $meta_key ) && $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );

		/* If the new meta value does not match the old value, update it. */
		elseif ( current_user_can( 'edit_post_meta', $post_id ) && $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );
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
