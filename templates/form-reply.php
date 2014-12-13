<?php if ( !current_user_can( 'create_replies' ) || !mb_is_topic_open( get_queried_object_id() ) )
	return; 
?>

<form id="reply-form" method="post" action="<?php mb_topic_url(); ?>">

	<fieldset>
		<legend><?php _e( 'Leave A Reply', 'message-board' ); ?></legend>

		<p>
			<label for="mb_reply_content" name="mb_reply_content"><?php _e( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ); ?></label>
			<textarea id="mb_reply_content" name="mb_reply_content"></textarea>
		</p>

		<p>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<?php if ( !mb_is_user_subscribed_topic( mb_get_topic_id() ) ) : ?>
			<p>
				<label>
					<input type="checkbox" name="mb_reply_subscribe" value="1" />
					<?php _e( 'Notify me of follow-up posts via email', 'message-board' ); ?>
				</label>
			</p>
		<?php endif; ?>

		<input type="hidden" name="mb_reply_topic_id" value="<?php mb_topic_id(); ?>" />

		<?php wp_nonce_field( 'mb_new_reply_action', 'mb_new_reply_nonce', false ); ?>

	</fieldset>
</form>