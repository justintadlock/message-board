<?php if ( !current_user_can( 'edit_reply', mb_get_reply_id() ) )
	return; 
?>

<form id="reply-form" method="post" action="<?php echo esc_url( add_query_arg( 'message-board', 'edit-reply', user_trailingslashit( home_url() ) ) ); ?>">

	<fieldset>
		<legend><?php _e( 'Edit Reply', 'message-board' ); ?></legend>

		<p>
			<label for="mb_reply_content" name="mb_reply_content"><?php _e( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ); ?></label>
			<textarea id="mb_reply_content" name="mb_reply_content"><?php echo format_to_edit( mb_code_trick_reverse( mb_get_reply_content( mb_get_reply_id(), 'raw' ) ) ); ?></textarea>
		</p>

		<p>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<p>
			<label>
				<input type="checkbox" name="mb_topic_subscribe" value="<?php echo mb_is_user_subscribed_topic( mb_get_reply_author_id(), mb_get_reply_topic_id() ) ? 1 : 0; ?>" /> 
				<?php _e( 'Notify me of follow-up posts via email', 'message-board' ); ?>
			</label>
		</p>

		<input type="hidden" name="mb_reply_id" value="<?php mb_reply_id(); ?>" />
		<input type="hidden" name="mb_reply_topic_id" value="<?php mb_reply_topic_id(); ?>" />

		<?php wp_nonce_field( 'mb_edit_reply_action', 'mb_edit_reply_nonce', false ); ?>

	</fieldset>
</form>