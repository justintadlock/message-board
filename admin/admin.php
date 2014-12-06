<?php

final class Message_Board_Admin {

	public $forum_type;
	public $topic_type;
	public $reply_type;

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

		/* Get post type names. */
		$this->forum_type = mb_get_forum_post_type();
		$this->topic_type = mb_get_topic_post_type();
		$this->reply_type = mb_get_reply_post_type();

		/* Add admin menu items. */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		/* Correct parent file. */
		add_filter( 'parent_file', array( $this, 'parent_file' ) );

		/* Admin notices. */
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		/* Register styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );

		/* Add custom body class. */
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
	}

	/**
	 * Adds admin menu items needed by the plugin.  Rather than having multiple top-level menu items 
	 * like some plugins, which shall remain unnamed, we'll consolidate everything into a single 
	 * item.  Yay for no clutter!
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function admin_menu() {

		/* Remove `post-new.php` submenu pages for topics and replies. */
		remove_submenu_page( mb_get_admin_menu_page(), "post-new.php?post_type={$this->topic_type}" );
		remove_submenu_page( mb_get_admin_menu_page(), "post-new.php?post_type={$this->reply_type}" );
	}

	function parent_file( $parent_file ) {

		if ( "edit.php?post_type={$this->topic_type}" === $parent_file || "edit.php?post_type={$this->reply_type}" === $parent_file ) {
			$parent_file = mb_get_admin_menu_page();
		}

		return $parent_file;
	}

	/**
	 * Displays an admin notice if the current theme does not support the Message Board plugin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function admin_notices() { 

		if ( !current_theme_supports( 'message-board' ) ) { ?>
			<div class="error">
				<p>
				<?php _e( 'The theme you are currently using does not support the Message Board plugin. Please activate a theme with support to continue enjoying full use of the plugin.', 'message-board' ); ?>
				</p>
			</div>
		<?php }
	}

	/**
	 * Registers the admin stylesheet.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public function register_styles() {
		wp_register_style( 'message-board-admin', message_board()->dir_uri . 'css/admin.css' );
	}

	/**
	 * Adds a custom admin body class.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $class
	 * @return string
	 */
	public function admin_body_class( $class ) {

		$screen = get_current_screen();

		if ( $this->forum_type === $screen->post_type )
			$class .= 'mb-forum ';
		elseif ( $this->topic_type === $screen->post_type )
			$class .= 'mb-topic ';
		elseif ( $this->reply_type === $screen->post_type )
			$class .= 'mb-reply ';

		return $class;
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
