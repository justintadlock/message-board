<?php

add_action( 'load-post.php', 'mb_post_forum_screen_load' );
add_action( 'load-post-new.php', 'mb_post_forum_screen_load' );

function mb_post_forum_screen_load() {
		$screen = get_current_screen();

		$forum_type = mb_get_forum_post_type();
		$topic_type = mb_get_topic_post_type();
		$reply_type = mb_get_reply_post_type();

		if ( empty( $screen->post_type ) || !in_array( $screen->post_type, array( $forum_type, $topic_type, $reply_type ) ) )
			return;

		add_action( 'admin_enqueue_scripts', 'mb_post_screen_styles' );

		add_action( "add_meta_boxes_{$screen->post_type}", 'mb_forum_add_meta_boxes', 0 );
}

function mb_post_screen_styles() {
		wp_enqueue_style( 'message-board-admin' );
	}

/* Creates the meta box. */
function mb_forum_add_meta_boxes( $post ) {

	remove_meta_box( 'submitdiv', $post->post_type, 'side' );

	add_meta_box(
		'mb-submitdiv',
		__( 'Publish', 'message-board' ),
		'mb_forum_submit_meta_box',
		$post->post_type,
		'side',
		'core'
	);
}

function mb_forum_submit_meta_box( $post, $args = array() ) {
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
						<label>
							<?php _e( 'Status:', 'message-board' ) ?>
							<select name='post_status' id='post_status'>
							<?php foreach ( $avail_statuses as $status ) : ?>
								<?php if ( 'publish' === $status && in_array( $post_type, array( mb_get_forum_post_type(), mb_get_topic_post_type() ) ) ) : ?>
									<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e( 'Open', 'message-board' ); ?></option>
								<?php elseif ( 'trash' !== $status ) : ?>
									<?php $status_object = get_post_status_object( $status ); ?>
									<option<?php selected( $post->post_status, $status ); ?> value='<?php echo esc_attr( $status ); ?>'><?php echo esc_html( $status_object->label ); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
							</select>
						</label>
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
				<?php if ( ( 'publish' !== $post->post_status && 'close' !== $post->post_status ) || 0 == $post->ID ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'message-board' ) ?>" />
					<?php submit_button( __( 'Publish', 'message-board' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
				<?php else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'message-board' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update', 'message-board' ) ?>" />
				<?php endif; ?>
			</div><!-- #publishing-action -->

			<div class="clear"></div>

		</div><!-- #major-publishing-actions -->

	</div><!-- #submitpost -->
<?php }




