<?php
if ( !current_user_can( 'create_forums' ) )
	return;
?>

<form id="forum-form" class="mb-form-forum" method="post" action="<?php mb_board_home_url(); ?>">

	<fieldset>
		<legend><?php _e( 'Add New Forum', 'message-board' ); ?></legend>

		<p class="mb-form-title">
			<label for="mb_forum_title"><?php _e( 'Forum Title:', 'message-board' ); ?></label>
			<input type="text" id="mb_forum_title" name="mb_forum_title" />
		</p>

		<?php if ( !mb_is_single_forum() ) : ?>
			<p class="mb-form-parent">
				<label for="mb_post_parent"><?php _e( 'Parent Forum:', 'message-board' ); ?></label>
				<?php mb_dropdown_forums(
					array(
						'name'              => 'mb_post_parent',
						'id'                => 'mb_post_parent',
						'show_option_none'  => __( '(no parent)', 'message-board' ),
						'option_none_value' => 0,
						'selected'          => 0,
					)
				); ?>
			</p>
		<?php endif; ?>

		<p class="mb-form-type">
			<label for="mb_forum_type"><?php _e( 'Forum Type:', 'message-board' ); ?></label>
			<?php mb_dropdown_forum_type(); ?>
		</p>

		<p class="mb-form-status">
			<label for="mb_post_status"><?php _e( 'Status:', 'message-board' ); ?></label>
			<?php mb_dropdown_post_status(
				array(
					'post_type' => mb_get_forum_post_type(),
					'name'      => 'mb_post_status',
					'id'        => 'mb_post_status'
				)
			); ?>
		</p>

		<p class="mb-form-order">
			<label for="mb_menu_order"><?php _e( 'Order:', 'message-board' ); ?></label>
			<input type="number" id="mb_menu_order" name="mb_menu_order" value="0" />
		</p>

		<p class="mb-form-content">
			<label for="mb_forum_content"><?php _e( 'Description:', 'message-board' ); ?></label>
			<textarea id="mb_forum_content" name="mb_forum_content"></textarea>
		<p>

		<p class="mb-form-submit">
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<p class="mb-form-subscribe">
			<label>
				<input type="checkbox" name="mb_forum_subscribe" value="1" /> 
				<?php _e( 'Notify me of topics and posts via email', 'message-board' ); ?>
			</label>
		</p>

		<?php if ( mb_is_single_forum() ) : ?>

			<input type="hidden" name="mb_post_parent" value="<?php echo esc_attr( mb_get_forum_id() ); ?>" />

		<?php endif; ?>

		<?php wp_nonce_field( 'mb_new_forum_action', 'mb_new_forum_nonce', false ); ?>

	</fieldset>
</form>