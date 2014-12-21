
	<div class="loop-meta">
		<h1 class="topic-title loop-title"><?php mb_single_forum_title(); ?></h1>

		<div class="loop-description">
			<p>
				<span class="entry-terms topic-posts genericon-status">
					<?php $count = mb_get_forum_topic_count( get_queried_object_id() ); ?>
					<?php echo ( 1 == $count ) ? "{$count} topic" : "{$count} topics"; ?>
				</span>
				<span class="entry-terms topic-posts genericon-status">
					<?php $count = mb_get_forum_post_count( get_queried_object_id() ); ?>
					<?php echo ( 1 == $count ) ? "{$count} post" : "{$count} posts"; ?>
				</span>
				<?php mb_forum_toggle_open_link(); ?>
				<?php mb_forum_toggle_trash_link(); ?>
			</p>
		</div><!-- .loop-description -->

	</div><!-- .loop-meta -->

	<?php if ( current_user_can( 'read_forum', mb_get_forum_id() ) ) : ?>

		<?php if ( !mb_is_forum_paged() ) : ?>

			<?php mb_get_template_part( 'loop', 'forum' ); ?>

		<?php endif; ?>

		<?php if ( mb_forum_type_allows_topics( mb_get_forum_type() ) ) : ?>

			<?php mb_get_template_part( 'loop', 'topic' ); ?>

		<?php endif; ?>

	<?php else: ?>

		<p class="mb-error">Sorry, but you do not have permission to view this forum.</p>

	<?php endif; ?>

	<div class="loop-nav">
		<?php echo mb_forum_pagination(); ?>
	</div><!-- .loop-nav -->

	<?php mb_get_template_part( 'form-topic', 'new' ); ?>
