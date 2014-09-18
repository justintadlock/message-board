<?php
/**
 * Plugin Name: Message Board
 * Plugin URI:  http://themehybrid.com
 * Description: Simple forums for us simple folks.
 * Version:     1.0.0-alpha-1
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

	public $views = array();

	public $user_views = array();


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

		$this->dir_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->dir_uri  = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	private function includes() {

		require_once( $this->dir_path . 'inc/core.php'           );
		require_once( $this->dir_path . 'inc/post-types.php'     );
		require_once( $this->dir_path . 'inc/taxonomies.php'     );
		require_once( $this->dir_path . 'inc/meta.php'           );
		require_once( $this->dir_path . 'inc/filters.php'        );
		require_once( $this->dir_path . 'inc/formatting.php'     );
		require_once( $this->dir_path . 'inc/query.php'          );
		require_once( $this->dir_path . 'inc/capabilities.php'   );
		require_once( $this->dir_path . 'inc/rewrite.php'        );
		require_once( $this->dir_path . 'inc/functions-view.php' );
		require_once( $this->dir_path . 'inc/functions-user.php' );
		require_once( $this->dir_path . 'inc/handler.php'        );
		require_once( $this->dir_path . 'inc/shortcodes.php'     );
		require_once( $this->dir_path . 'inc/options.php'        );
		require_once( $this->dir_path . 'inc/admin-bar.php'      );

		/* Templates. */
		require_once( $this->dir_path . 'inc/template-hierarchy.php' );
		require_once( $this->dir_path . 'inc/template.php'           );
		require_once( $this->dir_path . 'inc/template-post.php'      );
		require_once( $this->dir_path . 'inc/template-forum.php'     );
		require_once( $this->dir_path . 'inc/template-topic.php'     );
		require_once( $this->dir_path . 'inc/template-reply.php'     );
		require_once( $this->dir_path . 'inc/template-user.php'      );
		require_once( $this->dir_path . 'inc/template-view.php'      );

		if ( is_admin() ) {
			require_once( $this->dir_path . 'admin/admin.php' );
			require_once( $this->dir_path . 'admin/meta-boxes.php' );
			require_once( $this->dir_path . 'admin/single-term/class.taxonomy-single-term.php' );

			$forum_tax = new Taxonomy_Single_Term( 'forum', array( 'forum_topic' ), 'select' );
			$forum_tax->set( 'priority', 'core' );
			$forum_tax->set( 'context', 'side' );
			$forum_tax->set( 'force_selection', true );
			$forum_tax->set( 'indented', true );
			$forum_tax->set( 'allow_new_terms', true );
		}
	}

	private function setup_actions() {

		/* Provide hook for add-on plugins to execute before the plugin runs. */
		add_action( 'plugins_loaded', array( $this, 'setup_early' ), 0 );

		/* Internationalize the text strings used. */
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		/* Provide hook for add-on plugins after the plugin has been set up. */
		add_action( 'plugins_loaded', array( $this, 'setup_late' ), 15 );

		add_action( 'init', array( $this, 'register_views' ) );
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

	public function register_views() {

		mb_register_view(
			'popular',
			array(
				'title' => __( 'Popular', 'message-board' ),
				'query' => array(
					'orderby'  => 'meta_value_num',
					'meta_key' => '_topic_reply_count'
				)
			)
		);

		mb_register_view(
			'most-voices',
			array(
				'title' => __( 'Most Voices', 'message-board' ),
				'query' => array(
					'orderby'  => 'meta_value_num',
					'meta_key' => '_topic_voice_count'
				)
			)
		);
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
