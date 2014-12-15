<?php
if ( !current_user_can( 'edit_user', mb_get_user_id() ) )
	return;
?>

<form id="topic-form" method="post" action="<?php mb_user_profile_url(); ?>">

	<p>
		<?php printf( __( 'Required fields are marked %s', 'message-board' ), '<span class="required">*</span>' ); ?>
	</p>

	<fieldset>
		<legend><?php _e( 'Name', 'message-board' ); ?></legend>

		<p>
			<label for="mb_first_name"><?php _e( 'First Name', 'message-board' ); ?></label>
			<input type="text" id="mb_first_name" name="mb_first_name" value="<?php echo esc_attr( get_the_author_meta( 'first_name', mb_get_user_id() ) ); ?>" />
		</p>

		<p>
			<label for="mb_last_name"><?php _e( 'Last Name', 'message-board' ); ?></label>
			<input type="text" id="mb_last_name" name="mb_last_name" value="<?php echo esc_attr( get_the_author_meta( 'last_name', mb_get_user_id() ) ); ?>" />
		</p>

		<p>
			<label for="mb_nickname"><?php _e( 'Nickname', 'message-board' ); ?> <span class="required">*</span></label>
			<input type="text" id="mb_nickname" name="mb_nickname" value="<?php echo esc_attr( get_the_author_meta( 'nickname', mb_get_user_id() ) ); ?>" />
		</p>

		<p>
			<label for="mb_display_name"><?php _e( 'Display Name', 'message-board' ); ?> <span class="required">*</span></label>
			<input type="text" disabled="disabled" id="mb_display_name" name="mb_display_name" value="<?php echo esc_attr( get_the_author_meta( 'display_name', mb_get_user_id() ) ); ?>" />
		</p>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Contact Info', 'message-board' ); ?></legend>

		<p>
			<label for="mb_email"><?php _e( 'Email', 'message-board' ); ?> <span class="required">*</span></label>
			<input type="email" id="mb_email" name="mb_email" value="<?php echo esc_attr( get_the_author_meta( 'email', mb_get_user_id() ) ); ?>" />
		</p>
		<p>
			<label for="mb_url"><?php _e( 'Web Site', 'message-board' ); ?></label>
			<input type="url" id="mb_url" name="mb_url" value="<?php echo esc_url( get_the_author_meta( 'url', mb_get_user_id() ) ); ?>" />
		</p>

		<?php foreach ( mb_get_user_contact_methods() as $name => $label ) : ?>
			<p>
				<label for="<?php echo esc_attr( "mb_contact_{$name}" ); ?>"><?php echo $label; ?></label>
				<input type="text" id="<?php echo esc_attr( "mb_contact_{$name}" ); ?>" name="<?php echo esc_attr( "mb_contact_{$name}" ); ?>" value="<?php echo esc_attr( get_the_author_meta( $name, mb_get_user_id() ) ); ?>" />
			</p>
		<?php endforeach; ?>
	</fieldset>

	<fieldset>
		<legend><?php mb_is_user_profile_edit() ? _e( 'About Yourself', 'message-board' ) : _e( 'About The User', 'message-board' ); ?></legend>

		<p>
			<label for="mb_description"><?php _e( 'Biographical Info', 'message-board' ); ?></label>
			<textarea id="mb_description" name="mb_description"><?php echo esc_textarea( get_the_author_meta( 'description', mb_get_user_id() ) ); ?></textarea>
		</p>
	</fieldset>

	<p>
		<input type="submit" value="<?php esc_attr_e( 'Submit', 'message-board' ); ?>" />
	</p>

	<input type="hidden" name="mb_user_id" value="<?php mb_user_id(); ?>" />

	<?php wp_nonce_field( 'mb_edit_user_action', 'mb_edit_user_nonce', false ); ?>

</form>