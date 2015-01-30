<?php
/**
 * Sets up the plugin admin.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class Message_Board_Admin {

	/**
	 * Forum post type name.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $forum_type;

	/**
	 * Topic post type name.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $topic_type;

	/**
	 * Reply post type name.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $reply_type;

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

		/* Register scripts and styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

		/* Add custom body class. */
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		/* Overwrite the nav menu meta box object query. */
		add_filter( 'nav_menu_meta_box_object', array( $this, 'nav_menu_meta_box_object' ) );

		/* Edit screen views. */
		foreach ( mb_get_post_types() as $post_type )
			add_filter( "views_edit-{$post_type}", array( $this, 'views_edit' ), 5 );
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
		//remove_submenu_page( mb_get_admin_menu_page(), "post-new.php?post_type={$this->topic_type}" );
		remove_submenu_page( mb_get_admin_menu_page(), "post-new.php?post_type={$this->reply_type}" );
	}

	/**
	 * Corrects the parent file for post type screens.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $parent_file
	 * @return string
	 */
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

		if ( !current_theme_supports( 'message-board' ) && current_user_can( 'switch_themes' ) ) { ?>
			<div class="error">
				<p>
				<?php _e( 'The theme you are currently using does not support the Message Board plugin. Please activate a theme with support to continue enjoying full use of the plugin.', 'message-board' ); ?>
				</p>
			</div>
		<?php }
	}

	/**
	 * Registers the admin scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public function register_scripts() {
		wp_register_script( 'message-board-admin', message_board()->dir_uri . 'js/admin.js', array( 'jquery' ), false, true );
		wp_register_style(  'message-board-admin', message_board()->dir_uri . 'css/admin.css' );
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
	 * Puts the post status links in the a better order. By default, WP will list these in the order 
	 * they're registered.  Instead, we're going to put them in order from public, private, protected, 
	 * and other.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $views
	 * @return array
	 */
	public function views_edit( $views ) {

		$non_status = $public = $private = $protected = $other = array();

		foreach ( $views as $view => $link ) {

			$status_obj = get_post_status_object( $view );

			if ( is_null( $status_obj ) || !is_object( $status_obj ) )
				$non_status[ $view ] = $link;

			elseif ( true === $status_obj->public )
				$public[ $view ] = $link;

			elseif ( true === $status_obj->private )
				$private[ $view ] = $link;

			elseif ( true === $status_obj->protected )

				$protected[ $view ] = $link;
			else
				$other[ $view ] = $link;
		}

		return array_merge( $non_status, $public, $private, $protected, $other );
	}

	/**
	 * Makes sure the correct post status is used when loading forums on the nav menus screen.  By 
	 * default, WordPress will only load them if they have the "publish" post status.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $object
	 * @return object
	 */
	public function nav_menu_meta_box_object( $object ) {

		if ( isset( $object->name ) && mb_get_forum_post_type() === $object->name ) {

			$statuses = array(
				mb_get_open_post_status(),
				mb_get_close_post_status(),
				mb_get_publish_post_status(),
				mb_get_private_post_status(),
				mb_get_hidden_post_status(),
				mb_get_archive_post_status()
			);

			$object->_default_query = wp_parse_args( array( 'post_status' => $statuses ), $object->_default_query );
		}

		return $object;
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
