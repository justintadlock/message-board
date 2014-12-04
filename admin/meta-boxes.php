<?php

/**
 * Custom `submitdiv` meta box.  This replaces the WordPress default because it has too many things 
 * hardcoded that we cannot overwrite, particularly dealing with post statuses.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @param  array   $args
 * @return void
 */
function mb_submit_meta_box( $post, $args = array() ) {
	global $action;

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );

	if ( $post_type === mb_get_forum_post_type() )
		$avail_statuses = mb_get_forum_post_statuses();
	elseif ( $post_type === mb_get_topic_post_type() )
		$avail_statuses = mb_get_topic_post_statuses();
	else
		$avail_statuses = mb_get_reply_post_statuses();

	$can_publish = current_user_can( $post_type_object->cap->publish_posts ); ?>

	<div class="submitbox" id="submitpost">

		<div id="minor-publishing">

			<div style="display:none;">
				<?php submit_button( __( 'Save', 'message-board' ), 'button', 'save' ); ?>
			</div>

			<div id="minor-publishing-actions">
				<div class="clear"></div>
			</div><!-- #minor-publishing-actions -->

			<div id="misc-publishing-actions">

				<div class="misc-pub-section misc-pub-post-status">

					<div id="post-status-select">
						<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
							<?php foreach ( $avail_statuses as $status ) : ?>
								<?php if ( mb_get_trash_post_status() !== $status ) : // @todo - Better handling of next line. ?>
									<?php $post_status = in_array( $post->post_status, $avail_statuses ) ? $post->post_status : mb_get_open_post_status(); ?>
									<?php $status_object = get_post_status_object( $status ); ?>
									<label class="<?php echo esc_attr( $status ); ?>">
									<input type="radio" value="<?php echo esc_attr( $status ); ?>"<?php checked( $post_status, $status ); ?> /> <?php echo $status_object->label; ?>
									<br />
								<?php endif; ?>
							<?php endforeach; ?>
					</div><!-- #post-status-select -->

				</div><!-- .misc-pub-section -->

				<?php do_action( 'post_submitbox_misc_actions' ); ?>

			</div><!-- #misc-publishing-actions -->

			<div class="clear"></div>

		</div><!-- #minor-publishing -->

		<div id="major-publishing-actions">

			<?php do_action( 'post_submitbox_start' ); ?>

			<div id="delete-action">
				<?php if ( current_user_can( 'delete_post', $post->ID ) ) :
					if ( !EMPTY_TRASH_DAYS ) :
						$delete_text = __( 'Delete Permanently', 'message-board' );
					else :
						$delete_text = __( 'Move to Trash', 'message-board' );
					endif; ?>
					<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a>
				<?php endif; ?>
			</div><!-- #delete-action -->

			<div id="publishing-action">
				<span class="spinner"></span>
				<?php if ( ( mb_get_open_post_status() !== $post->post_status && mb_get_close_post_status() !== $post->post_status && mb_get_publish_post_status() !== $post->post_status ) || 0 == $post->ID ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'message-board' ) ?>" />
					<?php submit_button( __( 'Publish', 'message-board' ), 'primary button-large', 'mb-publish', false, array( 'accesskey' => 'p' ) ); ?>
				<?php else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'message-board' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update', 'message-board' ) ?>" />
				<?php endif; ?>
			</div><!-- #publishing-action -->

			<div class="clear"></div>

		</div><!-- #major-publishing-actions -->

	</div><!-- #submitpost -->
<?php }

/**
 * Forum attribute meta box.  This handles the forum type, parent, and menu order.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_forum_attributes_meta_box( $post ) {

	wp_nonce_field( '_mb_forum_attr_nonce', 'mb_forum_attr_nonce' );

	$forum_types = mb_get_forum_type_objects(); ?>

	<p>
		<strong><?php _e( 'Forum Type:', 'message-board' ); ?></strong>
	</p>
	<p>
		<?php foreach ( $forum_types as $type ) : ?>
			<label>
				<input type="radio" name="mb_forum_type" value="<?php echo esc_attr( $type->name ); ?>"<?php checked( $type->name, mb_get_forum_type( $post->ID ) ); ?> /> <?php echo esc_html( $type->label ); ?>
			</label>
			<br />
		<?php endforeach; ?>
	</p>

	<p>
		<label id="mb_parent_forum">
			<strong><?php _e( 'Parent Forum:', 'message-board' ); ?></strong>
		</label>
	</p>
	<p>
		<?php mb_dropdown_forums(
			array(
				'name'              => 'parent_id',
				'id'                => 'mb_parent_forum',
				'show_option_none'  => __( '(no parent)', 'message-board' ),
				'option_none_value' => 0,
				'selected'          => $post->post_parent
			)
		); ?>
	</p>

	<p>
		<label for="mb_menu_order"><strong><?php _e( 'Order:', 'message-board' ); ?></strong></label>
	</p>
	<p>
		<input type="number" name="menu_order" id="mb_menu_order" min="0" value="<?php echo esc_attr( $post->menu_order ); ?>" />
	</p><?php
}

/**
 * Topic attributes meta box.  This handles whether the topic is sticky and the parent forum.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_topic_attributes_meta_box( $post ) {

	wp_nonce_field( '_mb_topic_attr_nonce', 'mb_topic_attr_nonce' );

	$forum_type_object = get_post_type_object( mb_get_forum_post_type() );

	$super_stickies = get_option( 'mb_super_sticky_topics', array() );
	$topic_stickies = get_option( 'mb_sticky_topics',       array() );
	$all_stickies   = array_merge( $super_stickies, $topic_stickies ); ?>

	<p>
		<strong><?php _e( 'Sticky Status:', 'message-board' ); ?></strong>
	</p>
	<p>
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

	<p>
		<label id="mb_parent_forum">
			<strong><?php echo $forum_type_object->labels->singular_name; ?></strong>
		</label>
	</p>
	<p>
		<?php mb_dropdown_forums(
			array(
				'child_type'        => mb_get_topic_post_type(),
				'name'              => 'parent_id',
				'id'                => 'mb_parent_forum',
				'show_option_none'  => __( '&ndash;&ndash; No Parent &ndash;&ndash;', 'message-board' ),
				'option_none_value' => 0,
				'selected'          => $post->post_parent
			)
		); ?>
	</p><?php
}
