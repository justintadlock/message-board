<?php
/**
 * Plugin Name: Message Board
 * Plugin URI:  http://themehybrid.com
 * Description: Simple forums for us simple folks.
 * Version:     1.0.0-pre-alpha
 * Author:      Justin Tadlock
 * Author URI:  http://justintadlock.com
 * Text Domain: message-board
 * Domain Path: /languages
 */

/**
 * Sets up and initializes the Message Board plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
final class Message_Board {

	public $version = '1.0.0';

	public $db_version = 1;

	public $dir_path = '';

	public $dir_uri = '';

	public $user_views = array();

	public $forum_types = array();


	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Message_Board;
			$instance->setup();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	private function __construct() {}

	private function setup() {

		$this->forum_query     = new WP_Query();
		$this->sub_forum_query = new WP_Query();
		$this->topic_query     = new WP_Query();
		$this->reply_query     = new WP_Query();

		$this->dir_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->dir_uri  = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	private function includes() {

		require_once( $this->dir_path . 'inc/functions-post-types.php'     );
		require_once( $this->dir_path . 'inc/functions-post-statuses.php'  );
		require_once( $this->dir_path . 'inc/functions-meta.php'           );
		require_once( $this->dir_path . 'inc/functions-filters.php'        );
		require_once( $this->dir_path . 'inc/functions-formatting.php'     );
		require_once( $this->dir_path . 'inc/functions-query.php'          );
		require_once( $this->dir_path . 'inc/functions-capabilities.php'   );
		require_once( $this->dir_path . 'inc/functions-rewrite.php'        );
		require_once( $this->dir_path . 'inc/functions-view.php'           );
		require_once( $this->dir_path . 'inc/functions-handler.php'        );
		require_once( $this->dir_path . 'inc/functions-shortcodes.php'     );
		require_once( $this->dir_path . 'inc/functions-options.php'        );
		require_once( $this->dir_path . 'inc/functions-admin-bar.php'      );

		/* Load common files. */
		require_once( $this->dir_path . 'inc/common/template.php' );

		/* Load forum files. */
		require_once( $this->dir_path . 'inc/forum/functions.php' );
		require_once( $this->dir_path . 'inc/forum/template.php'  );

		/* Load topic files. */
		require_once( $this->dir_path . 'inc/topic/functions.php' );
		require_once( $this->dir_path . 'inc/topic/template.php'  );

		/* Load reply files. */
		require_once( $this->dir_path . 'inc/reply/functions.php' );
		require_once( $this->dir_path . 'inc/reply/template.php'  );

		/* Load user files. */
		require_once( $this->dir_path . 'inc/user/functions.php' );
		require_once( $this->dir_path . 'inc/user/template.php'  );

		/* Templates. */
		require_once( $this->dir_path . 'inc/template-hierarchy.php' );
		require_once( $this->dir_path . 'inc/template.php'           );

		if ( is_admin() ) {
			require_once( $this->dir_path . 'admin/admin.php' );
			require_once( $this->dir_path . 'admin/edit-forums.php' );
			require_once( $this->dir_path . 'admin/edit-topics.php' );
			require_once( $this->dir_path . 'admin/edit-replies.php' );
			require_once( $this->dir_path . 'admin/post-forum.php' );
			require_once( $this->dir_path . 'admin/meta-boxes.php' );
		}
	}

	private function setup_actions() {

		/* Provide hook for add-on plugins to execute before the plugin runs. */
		add_action( 'plugins_loaded', array( $this, 'setup_early' ), 0 );

		/* Internationalize the text strings used. */
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		/* Provide hook for add-on plugins after the plugin has been set up. */
		add_action( 'plugins_loaded', array( $this, 'setup_late' ), 15 );

		//add_action( 'init', array( $this, 'register_user_views' ) );

		/* Register activation hook. */
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
	}

	/**
	 * Pre-setup hook.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function setup_early() { do_action( 'mb_setup_early' ); }

	/**
	 * Post-setup hook.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function setup_late() { do_action( 'mb_setup_late' ); }

	/**
	 * Loads the translation files.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'message-board', false, 'message-board/languages' );
	}

	public function register_user_views() {

		mb_register_user_view(
			'topics',
			array(
				'title' => __( 'Topics', 'message-board' ),
				'query' => array()
			)
		);
	}

	/**
	 * Method that runs only when the plugin is activated.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function activation() {

		/* Get the administrator role. */
		$role = get_role( 'administrator' );

		/* If the administrator role exists, add required capabilities for the plugin. */
		if ( !empty( $role ) ) {

			$role->add_cap( 'manage_forums' );
			$role->add_cap( 'create_forums' );
			$role->add_cap( 'edit_forums' );
			$role->add_cap( 'create_forum_topics' );
			$role->add_cap( 'edit_forum_topics' );
			$role->add_cap( 'create_forum_replies' );
			$role->add_cap( 'edit_forum_replies' );
		}
	}
}

function message_board() {
	return Message_Board::get_instance();
}

message_board();
