<?php
if ( !current_user_can( 'access_forum_form' ) )
	return;
?>

<form id="forum-form" class="mb-form-forum" method="post" action="<?php mb_board_home_url(); ?>">

	<fieldset>
		<legend><?php mb_forum_label( 'add_new_item' ); ?></legend>

		<p class="mb-form-title">
			<label for="mb_forum_title"><?php mb_forum_label( 'mb_form_title' ); ?></label>
			<input type="text" id="mb_forum_title" name="mb_forum_title" value="<?php echo esc_attr( mb_get_forum_title() ); ?>" />
		</p><!-- .mb-form-title -->

		<?php if ( !mb_is_single_forum() ) : ?>
			<p class="mb-form-parent">
				<label for="mb_post_parent"><?php mb_forum_label( 'parent_item_colon' ); ?></label>
				<?php mb_dropdown_forums(
					array(
						'name'              => 'mb_post_parent',
						'id'                => 'mb_post_parent',
						'show_option_none'  => __( '(no parent)', 'message-board' ),
						'option_none_value' => 0,
						'selected'          => mb_get_forum_parent_id()
					)
				); ?>
			</p><!-- .mb-form-parent -->
		<?php endif; ?>

		<p class="mb-form-type">
			<label for="mb_forum_type"><?php mb_forum_label( 'mb_form_type' ); ?></label>
			<?php mb_dropdown_forum_type(); ?>
		</p><!-- .mb-form-type -->

		<p class="mb-form-status">
			<label for="mb_post_status"><?php mb_forum_label( 'mb_form_status' ); ?></label>
			<?php mb_dropdown_post_status(
				array(
					'post_type' => mb_get_forum_post_type(),
					'name'      => 'mb_post_status',
					'id'        => 'mb_post_status',
					'selected'  => mb_get_forum_status(),
				)
			); ?>
		</p><!-- .mb-form-status -->

		<p class="mb-form-order">
			<label for="mb_menu_order"><?php mb_forum_label( 'mb_form_order' ); ?></label>
			<input type="number" id="mb_menu_order" name="mb_menu_order" value="<?php echo esc_attr( mb_get_forum_order() ); ?>" />
		</p><!-- .mb-form-order -->

		<p class="mb-form-content">
			<label for="mb_forum_content"><?php mb_forum_label( 'mb_form_content' ); ?></label>
			<textarea id="mb_forum_content" name="mb_forum_content"><?php echo format_to_edit( mb_code_trick_reverse( mb_get_forum_content( mb_get_forum_id(), 'raw' ) ) ); ?></textarea>
		<p><!-- .mb-form-content -->

		<p class="mb-form-submit">
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p><!-- .mb-form-submit -->

		<p class="mb-form-subscribe">
			<label>
				<input type="checkbox" name="mb_forum_subscribe" value="1" /> 
				<?php mb_forum_label( 'mb_form_subscribe' ); ?>
			</label>
		</p><!-- .mb-form-subscribe -->

		<?php if ( mb_is_single_forum() ) : ?>

			<input type="hidden" name="mb_post_parent" value="<?php echo esc_attr( mb_get_forum_id() ); ?>" />

		<?php endif; ?>

		<?php wp_nonce_field( 'mb_new_forum_action', 'mb_new_forum_nonce', false ); ?>

	</fieldset>
</form>