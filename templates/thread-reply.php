<li id="post-<?php mb_reply_id(); ?>" <?php post_class(); ?>>

	<article>
		<header class="mb-reply-header">
			<time class="mb-repy-date"><?php printf( __( '%s ago', 'message-board' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time> 
			<?php mb_reply_edit_link(); ?>
			<?php mb_reply_toggle_spam_link(); ?>
			<a class="mb-reply-permalink" href="<?php mb_post_jump_url(); ?>" rel="bookmark" itemprop="url">#<?php mb_thread_position(); ?></a>
		</header>

		<div class="mb-author-box">

			<?php echo get_avatar( mb_get_reply_author_id() ); ?>

			<div class="mb-author-info">
				<?php mb_reply_author_profile_link(); ?>
				<?php if ( get_the_author_meta( 'forum_nickname' ) ) : ?>
					<br />
					<span class="user-forum-nickname"><?php the_author_meta( 'forum_nickname' ); ?></span>
				<?php endif; ?>
				<?php if ( get_the_author_meta('url') ) : ?>
					<br />
					<a href="<?php echo esc_url( get_the_author_meta( 'url' ), mb_get_reply_author_id() ); ?>"><?php _e( 'Web Site', 'message-board' ); ?></a>
				<?php endif; ?>
				<br />
				<span class="mb-user-topic-count"><?php printf( __( 'Topics: %s', 'message-board' ), mb_get_user_reply_count( mb_get_reply_author_id() ) ); ?></span>
				<br />
				<span class="mb-user-reply-count"><?php printf( __( 'Replies: %s', 'message-board' ), mb_get_user_reply_count( mb_get_reply_author_id() ) ); ?></span>
			</div><!-- .mb-author-info -->

		</div><!-- .mb-author-box -->

		<div class="mb-reply-content">
			<?php mb_reply_content(); ?>
		</div><!-- .mb-reply-content -->

	</article>
</li>