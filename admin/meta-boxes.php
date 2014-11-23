<?php

/* Hook meta box to just the 'place' post type. */
add_action( 'add_meta_boxes_forum', 'my_add_meta_boxes' );
add_action( 'add_meta_boxes_forum_reply', 'my_add_meta_boxes' );
add_action( 'add_meta_boxes_forum_topic', 'my_add_meta_boxes' );

/* Creates the meta box. */
function my_add_meta_boxes( $post ) {

	add_meta_box(
		'mb-forum-parent',
		__( 'Parent Forum', 'message-board' ),
		'mb_forum_parent_meta_box',
		'forum',
		'side',
		'core'
	);


    add_meta_box(
        'mb-reply-parent',
        __( 'Parent Topic', 'message-board' ),
        'mb_reply_parent_meta_box',
        'forum_reply',
        'side',
        'core'
    );

    add_meta_box(
        'mb-topic-parent',
        __( 'Forum', 'message-board' ),
        'mb_topic_parent_meta_box',
        'forum_topic',
        'side',
        'core'
    );
}

/* Displays the meta box. */
function mb_forum_parent_meta_box( $post ) {

	wp_nonce_field( basename( __FILE__ ), 'mb_forum_data_nonce' );

	$forum_types = mb_get_forum_type_objects();

	echo '<p><label>';
	_e( 'Forum Type:', 'message-board' );
	echo '<br />';
	echo '<select id="mb_forum_type" name="mb_forum_type">';
	foreach ( $forum_types as $type ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $type->name ), selected( $type->name, mb_get_forum_type( $post->ID ), false ), esc_html( $type->label ) );
	}
	echo '</select></label></p>';

    $parents = get_posts(
        array(
            'post_type'   => 'forum', 
            'orderby'     => 'title', 
            'order'       => 'ASC', 
            'numberposts' => -1,
		'post__not_in' => array( $post->ID ),
        )
    );

    if ( !empty( $parents ) ) {

	echo '<p><label>';
	_e( 'Parent Forum:', 'message-board' );
	echo '<br />';
        echo '<select name="parent_id" class="widefat">'; // !Important! Don't change the 'parent_id' name attribute.
	printf( '<option value="0"%s>%s</option>', selected( 0, $post->post_parent, false ), esc_html__( '--No Parent--', 'message-board' ) );

        foreach ( $parents as $parent ) {
            printf( '<option value="%s"%s>%s</option>', esc_attr( $parent->ID ), selected( $parent->ID, $post->post_parent, false ), esc_html( $parent->post_title ) );
        }

        echo '</select></label></p>';
    }
}

/* Displays the meta box. */
function mb_topic_parent_meta_box( $post ) {

    $parents = get_posts(
        array(
            'post_type'   => 'forum', 
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

// @todo - only load on edit forum screen.
add_action( 'save_post', 'mb_meta_box_save_forum_data', 10, 2 );

function mb_meta_box_save_forum_data( $post_id, $post = '' ) {

	/* Fix for attachment save issue in WordPress 3.5. @link http://core.trac.wordpress.org/ticket/21963 */
	if ( !is_object( $post ) )
		$post = get_post();

	if ( mb_get_forum_post_type() !== $post->post_type )
		return;

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['mb_forum_data_nonce'] ) || !wp_verify_nonce( $_POST['mb_forum_data_nonce'], basename( __FILE__ ) ) )
		return;

	/* Return here if the template is not set. There's a chance it won't be if the post type doesn't have any templates. */
	if ( !isset( $_POST['mb_forum_type'] ) )
		return;

	/* Get the posted meta value. */
	$new_meta_value = $_POST['mb_forum_type'];

	/* Set the $meta_key variable based off the post type name. */
	$meta_key = "_forum_type";

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

/* Displays the meta box. */
function mb_reply_parent_meta_box( $post ) {

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





