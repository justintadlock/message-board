<li id="post-<?php mb_topic_id(); ?>" <?php post_class(); ?>>

	<article>
		<header class="mb-topic-header">
			<time class="mb-topic-date"><?php printf( __( '%s ago', 'message-board' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time> 
			<?php mb_topic_edit_link(); ?>
			<?php mb_topic_toggle_spam_link(); ?>
			<?php mb_topic_toggle_trash_link(); ?>
			<?php mb_topic_toggle_open_link(); ?>
			<?php mb_topic_toggle_close_link(); ?>
			<a class="mb-topic-permalink" href="<?php mb_post_jump_url(); ?>" rel="bookmark" itemprop="url">#<?php mb_thread_position(); ?></a>
		</header>

		<div class="mb-author-box">

			<?php echo get_avatar( mb_get_topic_author_id() ); ?>

			<div class="mb-author-info">
				<?php mb_topic_author_profile_link(); ?>
				<br />
				<?php mb_role_link( mb_get_user_role( mb_get_topic_author_id() ) ); ?>
				<?php if ( get_the_author_meta('url') ) : ?>
					<br />
					<a href="<?php echo esc_url( get_the_author_meta( 'url' ), mb_get_topic_author_id() ); ?>"><?php _e( 'Web Site', 'message-board' ); ?></a>
				<?php endif; ?>
				<br />
				<span class="mb-user-topic-count"><?php printf( __( 'Topics: %s', 'message-board' ), mb_get_user_topic_count( mb_get_topic_author_id() ) ); ?></span>
				<br />
				<span class="mb-user-reply-count"><?php printf( __( 'Replies: %s', 'message-board' ), mb_get_user_reply_count( mb_get_topic_author_id() ) ); ?></span>
			</div><!-- .mb-author-info -->

		</div><!-- .mb-author-box -->

		<div class="mb-topic-content">
			<?php mb_topic_content(); ?>
		</div><!-- .mb-topic-content -->

	</article>
</li>