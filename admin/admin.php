<?php

/* Add admin menu items. */
add_action( 'admin_menu', 'mb_admin_menu' );

/* Admin notices. */
add_action( 'admin_notices', 'mb_admin_notices' );

// apply_filters( 'post_row_actions', $actions, $post );
add_filter( 'post_row_actions', 'mb_post_row_actions', 10, 2 );
add_filter( 'page_row_actions', 'mb_post_row_actions', 10, 2 );

function mb_post_row_actions( $actions, $post ) {

	$types = array( mb_get_forum_post_type(), mb_get_topic_post_type(), mb_get_reply_post_type() );

	if ( in_array( $post->post_type, $types ) ) {

		/* Remove quick edit. */
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}
	}

	return $actions;
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

		/* Load post meta boxes on the post editing screen. */
		//add_action( 'load-post.php',     array( $this, 'load_post_meta_boxes' ) );
		//add_action( 'load-post-new.php', array( $this, 'load_post_meta_boxes' ) );

		/* Only run our customization on the 'edit.php' page in the admin. */
		add_action( 'load-edit.php', array( $this, 'load_edit' ) );

		$forum_type = mb_get_forum_post_type();

		/* Modify the columns on the forum screen. */
		add_filter( "manage_edit-{$forum_type}_columns",          array( $this, 'edit_forum_columns'            )        );
		add_filter( "manage_edit-{$forum_type}_sortable_columns", array( $this, 'manage_forum_sortable_columns' )        );
		add_action( "manage_{$forum_type}_posts_custom_column",   array( $this, 'manage_forum_columns'          ), 10, 2 );

		$topic_type = mb_get_topic_post_type();

		add_filter( "manage_edit-{$topic_type}_columns",          array( $this, 'edit_topic_columns'            )        );
		add_filter( "manage_edit-{$topic_type}_sortable_columns", array( $this, 'manage_topic_sortable_columns' )        );
		add_action( "manage_{$topic_type}_posts_custom_column",   array( $this, 'manage_topic_columns'          ), 10, 2 );

		$reply_type = mb_get_reply_post_type();

		add_filter( "manage_edit-{$reply_type}_columns",          array( $this, 'edit_reply_columns'            )        );
		add_filter( "manage_edit-{$reply_type}_sortable_columns", array( $this, 'manage_reply_sortable_columns' )        );
		add_action( "manage_{$reply_type}_posts_custom_column",   array( $this, 'manage_reply_columns'          ), 10, 2 );
	}

	/**
	 * Adds a custom filter on 'request' when viewing the edit menu items screen in the admin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_edit() {
		$screen = get_current_screen();

		if ( !empty( $screen->post_type ) && mb_get_forum_post_type() === $screen->post_type ) {
			add_filter( 'request',               array( $this, 'request'       ) );
		//	add_action( 'restrict_manage_posts', array( $this, 'tags_dropdown' ) );
		//	add_action( 'admin_head',            array( $this, 'print_styles'  ) );
		}
	}

	/**
	 * Filter on the 'request' hook to change the 'order' and 'orderby' query variables when 
	 * viewing the "edit menu items" screen in the admin.  This is to order the menu items 
	 * alphabetically.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $vars
	 * @return array
	 */
	public function request( $vars ) {

		/* Default ordering alphabetically. */
		if ( !isset( $vars['order'] ) && !isset( $vars['orderby'] ) ) {
			$vars = array_merge(
				$vars,
				array(
					'order'   => 'ASC',
					'orderby' => 'title'
				)
			);
		}

		elseif ( isset( $vars['orderby'] ) && '_forum_topic_count' === $vars['orderby'] ) {

			$vars = array_merge(
				$vars,
				array(
					'orderby'  => 'meta_value_num',
					'meta_key' => '_forum_topic_count'
				)
			);
		}

		elseif ( isset( $vars['orderby'] ) && '_forum_reply_count' === $vars['orderby'] ) {

			$vars = array_merge(
				$vars,
				array(
					'orderby'  => 'meta_value_num',
					'meta_key' => '_forum_reply_count'
				)
			);
		}

		return $vars;
	}

	/**
	 * Loads custom meta boxes on the "add new menu item" and "edit menu item" screens.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load_post_meta_boxes() {
		//require_once( RESTAURANT_DIR . 'admin/class-restaurant-post-meta-boxes.php' );
	}

	/**
	 * Renders a restaurant tags dropdown on the "menu items" screen table nav.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function tags_dropdown() {

		$tag   = isset( $_GET['restaurant_tag'] ) ? esc_attr( $_GET['restaurant_tag'] ) : '';
		$terms = get_terms( 'restaurant_tag' );

		if ( !empty( $terms ) ) {
			echo '<select name="restaurant_tag" class="postform">';

			echo '<option value="' . selected( '', $tag, false ) . '">' . __( 'View all tags', 'message-board' ) . '</option>';

			foreach ( $terms as $term )
				printf( '<option value="%s"%s>%s (%s)</option>', esc_attr( $term->slug ), selected( $term->slug, $tag, false ), esc_html( $term->name ), esc_html( $term->count ) );

			echo '</select>';
		}
	}

	/**
	 * Filters the columns on the "menu items" screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $post_columns
	 * @return array
	 */
	public function edit_forum_columns( $post_columns ) {

		$screen     = get_current_screen();
		$post_type  = $screen->post_type;
		$columns    = array();
		$taxonomies = array();

		/* Adds the checkbox column. */
		$columns['cb'] = $post_columns['cb'];

		/* Add custom columns and overwrite the 'title' column. */
		$columns['title']     = __( 'Forum',      'message-board' );
		$columns['topics']    = __( 'Topics',     'message-board' );
		$columns['replies']   = __( 'Replies',    'message-board' );

		/* Return the columns. */
		return $columns;
	}

	public function edit_topic_columns( $post_columns ) {

		$screen     = get_current_screen();
		$post_type  = $screen->post_type;
		$columns    = array();
		$taxonomies = array();

		/* Adds the checkbox column. */
		$columns['cb'] = $post_columns['cb'];

		/* Add custom columns and overwrite the 'title' column. */
		$columns['title']     = __( 'Topic',      'message-board' );
		$columns['forum']     = __( 'Forum',      'message-board' );
		$columns['replies']   = __( 'Replies',    'message-board' );
		$columns['voices']    = __( 'Voices',     'message-board' );
		$columns['author']    = __( 'Author',     'message-board' );
		$columns['datetime']  = __( 'Created',    'message-board' );

		/* Return the columns. */
		return $columns;
	}

	public function edit_reply_columns( $post_columns ) {

		$screen     = get_current_screen();
		$post_type  = $screen->post_type;
		$columns    = array();
		$taxonomies = array();

		/* Adds the checkbox column. */
		$columns['cb'] = $post_columns['cb'];

		/* Add custom columns and overwrite the 'title' column. */
		$columns['title']     = __( 'Reply',      'message-board' );
		$columns['topic']     = __( 'Topic',      'message-board' );
		$columns['forum']     = __( 'Forum',      'message-board' );
		$columns['author']    = __( 'Author',     'message-board' );
		$columns['datetime']  = __( 'Created',    'message-board' );

		/* Return the columns. */
		return $columns;
	}

	/**
	 * Adds the 'price' column to the array of sortable columns.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array   $columns
	 * @return array
	 */
	public function manage_forum_sortable_columns( $columns ) {

		$columns['topics']  = array( '_forum_topic_count', true );
		$columns['replies'] = array( '_forum_reply_count', true );

		return $columns;
	}

	public function manage_topic_sortable_columns( $columns ) {

		//$columns['topics']  = array( '_forum_topic_count', true );
		//$columns['replies'] = array( '_forum_reply_count', true );

		return $columns;
	}

	public function manage_reply_sortable_columns( $columns ) {

		//$columns['topics']  = array( '_forum_topic_count', true );
		//$columns['replies'] = array( '_forum_reply_count', true );

		return $columns;
	}

	/**
	 * Add output for custom columns on the "menu items" screen.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $column
	 * @param  int     $post_id
	 * @return void
	 */
	public function manage_forum_columns( $column, $post_id ) {

		switch( $column ) {

			case 'topics' :

				$topic_count = mb_get_forum_topic_count( $post_id );

				echo !empty( $topic_count ) ? absint( $topic_count ) : number_format_i18n( 0 );

				break;

			case 'replies' :

				$reply_count = mb_get_forum_reply_count( $post_id );

				echo !empty( $reply_count ) ? absint( $reply_count ) : number_format_i18n( 0 );

				break;

			/* Just break out of the switch statement for everything else. */
			default :
				break;
		}
	}

	public function manage_topic_columns( $column, $post_id ) {

		switch( $column ) {

			case 'forum' :

				mb_forum_link( mb_get_topic_forum_id( $post_id ) );

				break;

			case 'replies' :

				$reply_count = mb_get_topic_reply_count( $post_id );

				echo !empty( $reply_count ) ? absint( $reply_count ) : number_format_i18n( 0 );

				break;

			case 'voices' :

				$voice_count = mb_get_topic_voice_count( $post_id );

				echo !empty( $voice_count ) ? absint( $voice_count ) : number_format_i18n( 0 );

				break;

			case 'datetime' :

				the_time( __( 'F j, Y g:i a', 'message-board' ) );

				break;

			/* Just break out of the switch statement for everything else. */
			default :
				break;
		}
	}

	public function manage_reply_columns( $column, $post_id ) {

		switch( $column ) {

			case 'forum' :

				mb_forum_link( mb_get_reply_forum_id( $post_id ) );

				break;

			case 'topic' :

				mb_topic_link( mb_get_reply_topic_id( $post_id ) );

				break;

			case 'datetime' :

				the_time( __( 'F j, Y g:i a', 'message-board' ) );

				break;

			/* Just break out of the switch statement for everything else. */
			default :
				break;
		}
	}

	/**
	 * Style adjustments for the manage menu items screen, particularly for adjusting the thumbnail 
	 * column in the table to make sure it doesn't take up too much space.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function print_styles( ) { ?>
	<?php }

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
