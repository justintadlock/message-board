<?php
if ( !current_user_can( 'edit_topic', mb_get_topic_id() ) )
	return;
?>

<form id="topic-form" method="post" action="<?php mb_topic_url(); ?>">

	<fieldset>
		<legend><?php _e( 'Edit Topic', 'message-board' ); ?></legend>

		<p>
			<label for="mb_topic_title"><?php _e( 'Topic title: (be brief and descriptive)', 'message-board' ); ?></label>
			<input type="text" id="mb_topic_title" name="mb_topic_title" value="<?php echo esc_attr( mb_get_topic_title() ); ?>" />
		</p>

		<p>
			<label for="mb_topic_forum"><?php _e( 'Select a forum:', 'message-board' ); ?></label>
			<?php mb_dropdown_forums(
				array(
					'child_type' => mb_get_topic_post_type(),
					'name'       => 'mb_topic_forum',
					'id'         => 'mb_topic_forum',
					'selected'   => mb_get_topic_forum_id()
				)
			); ?>
		</p>

		<p>
			<label for="mb_topic_content"><?php _e( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ); ?></label>
			<textarea id="mb_topic_content" name="mb_topic_content"><?php echo format_to_edit( mb_code_trick_reverse( mb_get_topic_content( mb_get_topic_id(), 'raw' ) ) ); ?></textarea>
		</p>

		<p>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<p>
			<label>
				<input type="checkbox" name="mb_topic_subscribe" value="<?php echo mb_is_user_subscribed_topic( mb_get_topic_author_id(), mb_get_topic_id() ) ? 1 : 0; ?>" /> 
				<?php _e( 'Notify me of follow-up posts via email', 'message-board' ); ?>
			</label>
		</p>

		<input type="hidden" name="mb_topic_id" value="<?php echo absint( mb_get_topic_id() ); ?>" />

		<?php wp_nonce_field( 'mb_edit_topic_action', 'mb_edit_topic_nonce', false ); ?>

	</fieldset>
</form>