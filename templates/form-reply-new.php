<?php if ( !current_user_can( 'access_reply_form' ) )
	return;
?>

<form id="mb-reply-form" method="post" action="<?php mb_topic_url(); ?>">

	<fieldset>
		<legend><?php mb_reply_label( 'add_new_item' ); ?></legend>

		<div class="mb-form-content">
			<label class="screen-reader-text" for="mb_reply_content"><?php mb_reply_label( 'mb_form_content' ); ?></label>
			<?php mb_reply_editor(); ?>
		</div><!-- .mb-form-content -->

		<p>
			<input type="submit" value="<?php esc_attr_e( 'Submit Reply', 'message-board' ); ?>" />
		</p>

		<?php if ( mb_is_subscriptions_active() && !mb_is_user_subscribed_topic( mb_get_topic_id() ) ) : ?>

			<p>
				<label>
					<input type="checkbox" name="mb_reply_subscribe" value="1" />
					<?php _e( 'Notify me of follow-up posts via email', 'message-board' ); ?>
				</label>
			</p>

		<?php endif; // End check if subscriptions enabled. ?>

		<input type="hidden" name="mb_reply_topic_id" value="<?php mb_topic_id(); ?>" />

		<?php wp_nonce_field( 'mb_new_reply_action', 'mb_new_reply_nonce', false ); ?>

	</fieldset>
</form>