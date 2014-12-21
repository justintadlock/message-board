
		<div class="loop-meta">
			<h1 class="topic-title loop-title"><?php mb_single_topic_title(); ?></h1>

			<div class="loop-description">
				<p>
					<?php mb_topic_forum_link( get_queried_object_id() ); ?>
					<span class="entry-terms topic-posts genericon-status">
						<?php $count = mb_get_topic_post_count( mb_get_topic_id() ); ?>
						<?php echo ( 1 == $count ) ? "{$count} post" : "{$count} posts"; ?>
					</span>
					<span class="entry-terms topic-voices genericon-user">
						<?php $voices = mb_get_topic_voice_count( mb_get_topic_id() ); ?>
						<?php echo ( 1 == $voices ) ? "{$voices} voice" : "{$voices} voices"; ?>
					</span> 
					<?php if ( is_user_logged_in() ) : ?>
					<span class="entry-terms topic-subscribe genericon-mail">
						<?php mb_topic_subscribe_link( mb_get_topic_id() ); ?>
					</span>
					<span class="entry-terms topic-favorite genericon-heart">
						<?php mb_topic_bookmark_link( mb_get_topic_id() ); ?>
					</span>
					<?php endif; ?>
				</p>
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php if ( current_user_can( 'read_topic', mb_get_topic_id() ) ) : ?>

		<ol id="thread" class="comment-list">

			<?php if ( mb_show_lead_topic() && mb_topic_query() ) : ?>

				<?php while ( mb_topic_query() ) : ?>

					<?php mb_the_topic(); ?>

					<?php mb_get_template_part( 'thread', 'topic' ); ?>

				<?php endwhile; ?>

			<?php endif; ?>

			<?php if ( mb_reply_query() ) : ?>

				<?php while ( mb_reply_query() ) : ?>

					<?php mb_the_reply(); ?>

					<?php mb_get_template_part( 'thread', 'reply' ); ?>

				<?php endwhile; ?>

			<?php endif; ?>

		</ol><!-- #thread -->

		<?php if ( current_user_can( 'view_club_content' ) ) : ?>
			<div class="loop-nav"><?php echo mb_topic_pagination(); ?></div><!-- .comments-nav -->
		<?php endif; ?>

		<?php if ( mb_is_topic_open() ) : ?>
			<?php mb_get_template_part( 'form-reply', 'new' ); // Loads the topic reply form. ?>
		<?php else : ?>
			<p class="alert">Sorry, this topic is now closed to new replies.</p>
		<?php endif; ?>

	<?php endif; ?>