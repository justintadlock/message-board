<?php
if ( !current_user_can( 'edit_forum', mb_get_forum_id() ) )
	return;
?>

<form id="forum-form" method="post" action="<?php echo esc_url( add_query_arg( 'message-board', 'edit-forum', user_trailingslashit( home_url() ) ) ); ?>">

	<fieldset>
		<legend><?php _e( 'Edit Forum', 'message-board' ); ?></legend>

		<p>
			<label for="mb_forum_title"><?php _e( 'Forum Title:', 'message-board' ); ?></label>
			<input type="text" id="mb_forum_title" name="mb_forum_title" value="<?php echo esc_attr( mb_get_forum_title() ); ?>" />
		</p>

		<p>
			<label for="mb_post_parent"><?php _e( 'Parent Forum:', 'message-board' ); ?></label>
			<?php mb_dropdown_forums(
				array(
					'name'              => 'mb_post_parent',
					'id'                => 'mb_post_parent',
					'show_option_none'  => __( '(no parent)', 'message-board' ),
					'option_none_value' => 0,
					'selected'          => mb_get_forum_parent_id(),
				)
			); ?>
		</p>

		<p>
			<label for="mb_forum_type"><?php _e( 'Forum Type:', 'message-board' ); ?></label>
			<select id="mb_forum_type" name="mb_forum_type">
			<?php foreach ( mb_get_forum_type_objects() as $forum_type ) : ?>
				<option value="<?php echo esc_attr( $forum_type->name ); ?>"<?php selected( mb_get_forum_type(), $forum_type->name ); ?>><?php echo esc_html( $forum_type->label ); ?></option>
			<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="mb_menu_order"><?php _e( 'Order:', 'message-board' ); ?></label>
			<input type="number" id="mb_menu_order" name="mb_menu_order" value="<?php echo esc_attr( mb_get_forum_order() ); ?>" />
		</p>

		<p>
			<label for="mb_forum_content"><?php _e( 'Description:', 'message-board' ); ?></label>
			<textarea id="mb_forum_content" name="mb_forum_content"><?php echo format_to_edit( mb_code_trick_reverse( mb_get_forum_content( mb_get_forum_id(), 'raw' ) ) ); ?></textarea>
		</p>

		<p>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
		</p>

		<?php /*
		<p>
			<label>
				<input type="checkbox" name="mb_forum_subscribe" value="1" /> 
				<?php _e( 'Notify me of topics and posts via email', 'message-board' ); ?>
			</label>
		<p>
		*/ ?>


		<input type="hidden" name="mb_forum_id" value="<?php mb_forum_id(); ?>" />

		<?php wp_nonce_field( 'mb_edit_forum_action', 'mb_edit_forum_nonce', false ); ?>

	</fieldset>
</form>