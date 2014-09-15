<?php

/* Hook meta box to just the 'place' post type. */
add_action( 'add_meta_boxes_forum_reply', 'my_add_meta_boxes' );

/* Creates the meta box. */
function my_add_meta_boxes( $post ) {

    add_meta_box(
        'my-place-parent',
        __( 'Parent Topic', 'message-board' ),
        'my_place_parent_meta_box',
        'forum_reply',
        'side',
        'core'
    );
}

/* Displays the meta box. */
function my_place_parent_meta_box( $post ) {

    $parents = get_posts(
        array(
            'post_type'   => 'forum_topic', 
            'orderby'     => 'title', 
            'order'       => 'ASC', 
            'numberposts' => -1 
        )
    );

    if ( !empty( $parents ) ) {

        echo '<select name="parent_id" class="widefat">'; // !Important! Don't change the 'parent_id' name attribute.

        foreach ( $parents as $parent ) {
            printf( '<option value="%s"%s>%s</option>', esc_attr( $parent->ID ), selected( $parent->ID, $post->post_parent, false ), esc_html( $parent->post_title ) );
        }

        echo '</select>';
    }
}


/* Set up the admin functionality. */
add_action( 'admin_menu', 'mb_admin_setup' );

function mb_admin_setup() {

	/* Custom columns on the edit portfolio items screen. */
	//add_filter( 'manage_edit-portfolio_item_columns', 'ccp_edit_portfolio_item_columns' );
	//add_action( 'manage_portfolio_item_posts_custom_column', 'ccp_manage_portfolio_item_columns', 10, 2 );

	/* Add meta boxes an save metadata. */
	add_action( 'add_meta_boxes', 'mb_add_meta_boxes'             );
	add_action( 'save_post',      'mb_topic_meta_box_save', 10, 2 );
}

function mb_add_meta_boxes( $post_type ) {

	if ( 'forum_topic' === $post_type ) {

		add_meta_box( 'mb-topic-info', __( 'Topic Info', 'message-board' ), 'mb_meta_box_topic_info', $post_type, 'side', 'core' );
	}
}

function mb_meta_box_topic_info( $post, $metabox ) {

	wp_nonce_field( basename( __FILE__ ), 'mb-meta-box-topic-info-nonce' );

	$super_stickies = get_option( 'mb_super_sticky_topics', array() );
	$topic_stickies = get_option( 'mb_sticky_topics',       array() );
	$all_stickies   = array_merge( $super_stickies, $topic_stickies );

	?>

	<p>
		<strong><?php _e( 'Sticky Topic', 'message-board' ); ?></strong><br />
		<label>
			<input type="radio" name="mb-topic-sticky" value="" <?php checked( !in_array( $post->ID, $all_stickies ) ); ?> /> 
			<?php _e( 'Not Sticky', 'message-board' ); ?>
		</label>
		<br />
		<label>
			<input type="radio" name="mb-topic-sticky" value="sticky" <?php checked( in_array( $post->ID, $topic_stickies ) ); ?> /> 
			<?php _e( 'Forum Sticky', 'message-board' ); ?>
		</label>
		<br />
		<label>
			<input type="radio" name="mb-topic-sticky" value="super-sticky" <?php checked( in_array( $post->ID, $super_stickies ) ); ?> /> 
			<?php _e( 'Super Sticky', 'message-board' ); ?>
		</label>
	</p>
	<?php

	/* Allow devs to hook in their own stuff here. */
	do_action( 'mb_meta_box_topic_info', $post, $metabox );
}

function mb_topic_meta_box_save( $post_id, $post ) {

	if ( !isset( $_POST['mb-meta-box-topic-info-nonce'] ) || !wp_verify_nonce( $_POST['mb-meta-box-topic-info-nonce'], basename( __FILE__ ) ) )
		return;

	if ( 'forum_topic' !== $post->post_type )
		return;

	$super_stickies = get_option( 'mb_super_sticky_topics', array() );
	$topic_stickies = get_option( 'mb_sticky_topics',       array() );

	$is_sticky = $_POST['mb-topic-sticky'];

	if ( 'super-sticky' === $is_sticky && !in_array( $post_id, $super_stickies ) ) {
		$super_stickies[] = $post_id;
		update_option( 'mb_super_sticky_topics', $super_stickies );
	}

	if ( 'sticky' === $is_sticky && !in_array( $post_id, $topic_stickies ) ) {
		$topic_stickies[] = $post_id;
		update_option( 'mb_sticky_topics', $topic_stickies );
	}

	if ( 'super-sticky' !== $is_sticky && in_array( $post_id, $super_stickies ) ) {
		$key = array_search( $post_id, $super_stickies );
		unset( $super_stickies[ $key ] );
		update_option( 'mb_super_sticky_topics', $super_stickies );
	}

	if ( 'sticky' !== $is_sticky && in_array( $post_id, $topic_stickies ) ) {
		$key = array_search( $post_id, $topic_stickies );
		unset( $topic_stickies[ $key ] );
		update_option( 'mb_sticky_topics', $topic_stickies );
	}


}





