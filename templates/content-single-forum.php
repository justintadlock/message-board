<div class="loop-meta forum">
	<h1 class="topic-title loop-title"><?php mb_single_forum_title(); ?></h1>

	<div class="loop-description">
		<?php mb_forum_content(); ?>
	</div><!-- .loop-description -->

	<p>
		<span class="mb-topic-count"><?php printf( __( 'Topics: %s', 'message-board' ), mb_get_forum_topic_count() ); ?></span> 
		<span class="mb-reply-count"><?php printf( __( 'Replies: %s', 'message-board' ), mb_get_forum_reply_count() ); ?></span> 
		<?php mb_forum_toggle_open_link(); ?> 
		<?php mb_forum_toggle_trash_link(); ?>
	</p>

</div><!-- .loop-meta -->

<?php if ( current_user_can( 'read_forum', mb_get_forum_id() ) ) : ?>

	<?php if ( !mb_is_forum_paged() ) : ?>

		<?php mb_get_template_part( 'loop', 'forum' ); ?>

	<?php endif; ?>

	<?php if ( mb_forum_type_allows_topics( mb_get_forum_type() ) ) : ?>

		<?php mb_get_template_part( 'loop', 'topic' ); ?>

	<?php endif; ?>

<?php else: ?>

	<p class="mb-error"><?php _e( 'Sorry, but you do not have permission to view this forum.', 'message-board' ); ?></p>

<?php endif; ?>

<?php mb_get_template_part( 'form-topic', 'new' ); ?>
