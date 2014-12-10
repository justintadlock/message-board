<?php
if ( !current_user_can( 'create_topics' ) )
	return; 

if ( mb_is_single_forum() && !mb_is_forum_open() )
	return;

if ( mb_is_single_forum() && !mb_forum_type_allows_topics( mb_get_forum_type() ) )
	return;
?>

<form id="topic-form" method="post" action="<?php mb_topic_form_action_url(); ?>">

	<fieldset>
		<legend><?php _e( 'Add New Topic', 'message-board' ); ?></legend>

		<p>
			<label for="mb_topic_title"><?php _e( 'Topic title: (be brief and descriptive)', 'message-board' ); ?></label>
			<input type="text" id="mb_topic_title" name="mb_topic_title" />
		</p>

		<?php if ( !mb_is_single_forum() ) : ?>

			<p>
				<label for="mb_topic_forum"><?php _e( 'Select a forum:', 'message-board' ); ?></label>
				<?php mb_dropdown_forums(
					array(
						'child_type' => mb_get_topic_post_type(),
						'name'       => 'mb_topic_forum',
						'id'         => 'mb_topic_forum',
					)
				); ?>
			</p>

		<?php endif; ?>

		<p>
			<label for="mb_topic_content"><?php _e( 'Please put code in between <code>`backtick`</code> characters.', 'message-board' ); ?></label>
			<textarea id="mb_topic_content" name="mb_topic_content"></textarea>
		</p>

		<p>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<p>
			<label>
				<input type="checkbox" name="mb_topic_subscribe" value="1" /> 
				<?php _e( 'Notify me of follow-up posts via email', 'message-board' ); ?>
			</label>
		</p>

		<?php if ( mb_is_single_forum() ) : ?>

			<input type="hidden" name="mb_topic_forum" value="<?php echo absint( get_queried_object_id() ); ?>" />

		<?php endif; ?>

		<?php wp_nonce_field( 'mb_new_topic_action', 'mb_new_topic_nonce', false ); ?>

	</fieldset>
</form>