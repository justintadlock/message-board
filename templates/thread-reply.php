<?php if ( current_user_can( 'view_club_content' ) ) : ?>

	<li id="post-<?php echo esc_attr( mb_get_topic_id( get_the_ID() ) ); ?>" class="topic topic-post">
		<article>
			<header class="entry-header">
				<time <?php hybrid_attr( 'entry-published' ); ?>><?php printf( __( '%s ago', 'th4' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time> 
				<?php mb_reply_edit_link(); ?>
				<?php mb_reply_toggle_spam_link(); ?>
				<?php mb_reply_toggle_trash_link(); ?>

				<a class="entry-permalink" href="<?php mb_post_jump_url(); ?>" rel="bookmark" itemprop="url">#<?php mb_thread_position(); ?></a>
			</header>

		<div class="wrap">

			<div class="entry-byline">
				<?php echo get_avatar( mb_get_reply_author_id() ); ?>

				<div class="entry-author-info">
				<span <?php hybrid_attr( 'entry-author' ); ?>>
					<?php mb_reply_author_profile_link(); ?> 
				</span>
				<?php if ( get_the_author_meta( 'forum_nickname' ) ) : ?>

					<span class="user-forum-nickname"><?php the_author_meta( 'forum_nickname' ); ?></span>

				<?php endif; ?>
					<?php if ( get_the_author_meta('url') ) : ?>
						<br />
						<span class="profile-link genericon-external"><a href="<?php echo esc_url( get_the_author_meta( 'url' ), mb_get_topic_author_id() ); ?>">Web Site</a></span>
					<?php endif; ?>
					<br />
					<span class="topic-count">Topics: <?php mb_user_topic_count( mb_get_reply_author_id() ); ?></span>
					<br />
					<span class="reply-count">Replies: <?php mb_user_reply_count( mb_get_reply_author_id() ); ?></span>
				</div>
			</div>

			<div <?php hybrid_attr( 'entry-content' ); ?>>
				<?php mb_reply_content(); ?>
				<?php wp_link_pages(); ?>
			</div><!-- .entry-content -->
		</div>

		</article>
	</li>

<?php endif; ?>