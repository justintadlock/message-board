<?php if ( !current_user_can( 'access_topic_form' ) )
	return;
?>

<form id="mb-topic-form" class="mb-form-topic" method="post" action="<?php mb_board_url(); ?>">

	<fieldset>
		<legend><?php mb_topic_label( 'add_new_item' ); ?></legend>

		<p class="mb-form-title">
			<label for="mb_topic_title"><?php mb_topic_label( 'mb_form_title' ); ?></label>
			<input type="text" id="mb_topic_title" name="mb_topic_title" value="<?php echo esc_attr( mb_get_topic_title() ); ?>" placeholder="<?php echo esc_attr( mb_get_topic_label( 'mb_form_title_placeholder' ) ); ?>" />
		</p><!-- .mb-form-title -->

		<?php if ( !mb_is_single_forum() ) : ?>

			<p class="mb-form-parent">
				<label for="mb_forum_id"><?php mb_topic_label( 'parent_item_colon' ); ?></label>
				<?php mb_dropdown_forums(
					array(
						'child_type' => mb_get_topic_post_type(),
						'name'       => 'mb_forum_id',
						'id'         => 'mb_forum_id',
						'selected'   => mb_get_topic_forum_id()
					)
				); ?>
			</p><!-- .mb-form-parent -->

		<?php endif; ?>

		<p class="mb-form-type">
			<label for="mb_topic_type"><?php mb_topic_label( 'mb_form_type' ); ?></label>
			<?php mb_dropdown_topic_type(); ?>
		</p><!-- .mb-form-type -->

		<p class="mb-form-status">
			<label for="mb_post_status"><?php mb_topic_label( 'mb_form_status' ); ?></label>
			<?php mb_dropdown_topic_status(
				array(
					'name'      => 'mb_post_status',
					'id'        => 'mb_post_status',
					'selected'  => mb_get_topic_status()
				)
			); ?>
		</p><!-- .mb-form-status -->

		<div class="mb-form-content">
			<label class="screen-reader-text" for="mb_topic_content"><?php mb_topic_label( 'mb_form_content' ); ?></label>
			<?php mb_topic_editor(); ?>
		</div><!-- .mb-form-content -->

		<p class="mb-form-submit">
			<input type="submit" value="<?php esc_attr_e( 'Create Topic', 'message-board' ); ?>" />
		</p><!-- .mb-form-submit -->

		<p class="mb-form-subscribe">
			<label>
				<input type="checkbox" name="mb_topic_subscribe" value="1" /> 
				<?php mb_topic_label( 'mb_form_subscribe' ); ?>
			</label>
		</p><!-- .mb-form-subscribe -->

		<?php do_action( 'mb_topic_form_fields' ); ?>

	</fieldset>

</form><!-- #mb-topic-form -->