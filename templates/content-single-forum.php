<header class="mb-page-header">
	<h1 class="mb-page-title"><?php mb_single_forum_title(); ?></h1>

	<div class="mb-forum-content">
		<?php mb_forum_content(); ?>
	</div><!-- .mb-forum-content -->

	<p>
		<span class="mb-topic-count"><?php printf( __( 'Topics: %s', 'message-board' ), mb_get_forum_topic_count() ); ?></span> 
		<span class="mb-reply-count"><?php printf( __( 'Replies: %s', 'message-board' ), mb_get_forum_reply_count() ); ?></span> 
		<?php mb_forum_toggle_open_link(); ?> 
		<?php mb_forum_toggle_trash_link(); ?>
	</p>

</header><!-- .mb-page-header -->

<?php if ( current_user_can( 'read_forum', mb_get_forum_id() ) ) : // Check if the current user can read the forum. ?>

	<?php if ( !mb_is_forum_paged() ) : // Only show subforums if on page 1. ?>

		<?php mb_get_template_part( 'loop', 'forum' ); ?>

	<?php endif; // End paged check. ?>

	<?php if ( mb_forum_type_allows_topics( mb_get_forum_type() ) ) : // Only show topics if they're allowed. ?>

		<?php mb_get_template_part( 'loop', 'topic' ); ?>

	<?php endif; // End show topics check. ?>

<?php endif; // End check to see if user can read forum. ?>

<?php mb_get_template_part( 'form-topic', 'new' ); ?>