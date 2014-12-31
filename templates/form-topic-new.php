<?php
if ( !current_user_can( 'create_topics' ) )
	return; 

if ( mb_is_single_forum() && !mb_is_forum_open() )
	return;

if ( mb_is_single_forum() && !mb_forum_type_allows_topics( mb_get_forum_type() ) )
	return;
?>

<form id="mb-topic-form" class="mb-form-topic" method="post" action="<?php mb_board_home_url(); ?>">

	<fieldset>
		<legend><?php _e( 'Add New Topic', 'message-board' ); ?></legend>

		<p class="mb-form-title">
			<label for="mb_topic_title"><?php _e( 'Topic Title', 'message-board' ); ?></label>
			<input type="text" id="mb_topic_title" name="mb_topic_title" />
		</p>

		<?php if ( !mb_is_single_forum() ) : ?>

			<p class="mb-form-parent">
				<label for="mb_forum_id"><?php _e( 'Forum', 'message-board' ); ?></label>
				<?php mb_dropdown_forums(
					array(
						'child_type' => mb_get_topic_post_type(),
						'name'       => 'mb_forum_id',
						'id'         => 'mb_forum_id',
					)
				); ?>
			</p>

		<?php endif; ?>

		<p class="mb-form-type">
			<label for="mb_topic_type"><?php _e( 'Topic Type:', 'message-board' ); ?></label>
			<?php mb_dropdown_topic_type(); ?>
		</p>

		<p class="mb-form-status">
			<label for="mb_post_status"><?php _e( 'Status:', 'message-board' ); ?></label>
			<?php mb_dropdown_post_status(
				array(
					'post_type' => mb_get_topic_post_type(),
					'name'      => 'mb_post_status',
					'id'        => 'mb_post_status'
				)
			); ?>
		</p>

		<p class="mb-form-content">
			<label for="mb_topic_content"><?php _e( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ); ?></label>
			<textarea id="mb_topic_content" name="mb_topic_content"></textarea>
		</p>

		<p class="mb-form-submit">
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<p class="mb-form-subscribe">
			<label>
				<input type="checkbox" name="mb_topic_subscribe" value="1" /> 
				<?php _e( 'Notify me of follow-up posts via email', 'message-board' ); ?>
			</label>
		</p>

		<?php do_action( 'mb_topic_form_fields' ); ?>

	</fieldset>
</form>