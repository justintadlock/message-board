<?php
//if ( !current_user_can( 'search_advanced' ) )
//	return;
?>

<form id="mb-form-search" method="get" action="<?php mb_search_url(); ?>">

	<fieldset>
		<legend><?php _e( 'Search For', 'message-board' ); ?></legend>

		<p>
			<label for="mb_search_s"><?php _e( 'Keyword(s)', 'message-board' ); ?></label>
			<input type="search" id="mb_search_s" name="s" value="<?php the_search_query(); ?>" />
		</p>

		<p>
			<label for="mb_username"><?php _e( 'Username', 'message-board' ); ?></label>
			<input type="text" id="mb_username" name="author_name" value="" /> 
		</p>

	</fieldset>

	<fieldset>
		<legend><?php _e( 'Search In', 'message-board' ); ?></legend>

		<p>
			<label>
				<input type="checkbox" name="post_type[]" value="<?php mb_forum_post_type(); ?>" /> 
				<?php _e( 'Forums', 'message-board' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" name="post_type[]" value="<?php mb_topic_post_type(); ?>" checked="checked" /> 
				<?php _e( 'Topics', 'message-board' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" name="post_type[]" value="<?php mb_reply_post_type(); ?>" checked="checked" /> 
				<?php _e( 'Replies', 'message-board' ); ?>
			</label>
		</p>

	</fieldset>

	<p>
		<input type="submit" value="<?php esc_attr_e( 'Search', 'message-board' ); ?>" />
	</p>

	<input type="hidden" name="mb_search_mode" value="advanced" />

</form>