<div class="loop-meta">
	<h1 class="topic-title loop-title"><?php _e( 'Edit', 'message-board' ); ?></h1>
</div>

<?php if ( mb_is_forum_edit() ) : ?>

	<?php mb_get_template_part( 'form-forum', 'edit' ); ?>

<?php elseif ( mb_is_topic_edit() ) : ?>

	<?php mb_get_template_part( 'form-topic', 'edit' ); ?>

<?php elseif ( mb_is_reply_edit() ) : ?>

	<?php mb_get_template_part( 'form-reply', 'edit' ); ?>

<?php elseif ( mb_is_user_edit() ) : ?>

	<?php mb_get_template_part( 'form-user', 'edit' ); ?>

<?php endif; ?>
