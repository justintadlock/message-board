<?php

/* Add admin menu items. */
add_action( 'admin_menu', 'mb_admin_menu' );

/* Admin notices. */
add_action( 'admin_notices', 'mb_admin_notices' );

/**
 * Adds admin menu items needed by the plugin.  Rather than having multiple top-level menu items 
 * like some plugins, which shall remain unnamed, we'll consolidate everything into a single 
 * item.  Yay for no clutter!
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_admin_menu() {

	/* Get post type names. */
	$forum_type   = mb_get_forum_post_type();
	$topic_type   = mb_get_topic_post_type();
	$reply_type   = mb_get_reply_post_type();

	/* Get post type objects. */
	$topic_object = get_post_type_object( $topic_type );
	$reply_object = get_post_type_object( $reply_type );

	/* Add the topic menu page. */
	add_submenu_page( 
		"edit.php?post_type={$forum_type}", 
		$topic_object->labels->all_items, 
		$topic_object->labels->all_items, 
		$topic_object->cap->edit_posts, 
		"edit.php?post_type={$topic_type}" 
	);

	/* Add the reply menu page. */
	add_submenu_page( 
		"edit.php?post_type={$forum_type}", 
		$reply_object->labels->all_items, 
		$reply_object->labels->all_items, 
		$reply_object->cap->edit_posts, 
		"edit.php?post_type={$reply_type}" 
	);
}

/**
 * Displays an admin notice if the current theme does not support the Message Board plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_admin_notices() { 

	if ( !current_theme_supports( 'message-board' ) ) { ?>
		<div class="error">
			<p>
			<?php _e( 'The theme you are currently using does not support the Message Board plugin. Please activate a theme with support to continue enjoying full use of the plugin.', 'message-board' ); ?>
			</p>
		</div>
	<?php }
}

final class Message_Board_Admin {

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

Message_Board_Admin::get_instance();
