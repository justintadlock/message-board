<?php if ( mb_search_query() ) : // If there are any posts found in the search results. ?>

	<table class="mb-loop-search">

		<thead>
			<tr>
				<th class="mb-col-title"><?php _e( 'Results', 'message-board' ); ?></th>
				<th class="mb-col-datetime"><?php _e( 'Created', 'message-board' ); ?></th>
				<th class="mb-col-author"><?php _e( 'Author', 'message-board' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="mb-col-title"><?php _e( 'Results', 'message-board' ); ?></th>
				<th class="mb-col-datetime"><?php _e( 'Created', 'message-board' ); ?></th>
				<th class="mb-col-author"><?php _e( 'Author', 'message-board' ); ?></th>
			</tr>
		</tfoot>

		<tbody>
			<?php while ( mb_search_query() ) : // Begins the loop through found posts. ?>

				<?php mb_the_search_result(); // Loads the post data. ?>

				<?php if ( 'forum' === mb_get_content_type() ) : ?>

					<tr <?php post_class(); ?>>
						<td class="mb-col-title">
							<?php mb_forum_link(); ?>
							<div class="mb-forum-summary">
								<?php the_excerpt(); ?>
							</div><!-- .entry-meta -->
						</td><!-- .mb-col-title -->

						<td class="mb-col-datetime">
							<?php mb_forum_date(); ?><br />
							<?php mb_forum_time(); ?>
						</td><!-- .mb-col-datetime -->

						<td class="mb-col-author">
							<?php mb_forum_author_link(); ?>
						</td><!-- .mb-col-author -->
					</tr>

				<?php elseif ( 'topic' === mb_get_content_type() ) : ?>

					<tr <?php post_class(); ?>>
						<td class="mb-col-title">
							<?php mb_topic_link(); ?>
							<div class="mb-topic-summary">
								<?php the_excerpt(); ?>
							</div><!-- .entry-meta -->
						</td><!-- .mb-col-title -->

						<td class="mb-col-datetime">
							<?php mb_topic_date(); ?><br />
							<?php mb_topic_time(); ?>
						</td><!-- .mb-col-datetime -->

						<td class="mb-col-author">
							<?php mb_topic_author_profile_link(); ?>
						</td><!-- .mb-col-author -->
					</tr>

				<?php elseif ( 'reply' === mb_get_content_type() ) : ?>

					<tr <?php post_class(); ?>>
						<td class="mb-col-title">
							<?php mb_reply_link(); ?>
							<div class="mb-reply-summary">
								<?php the_excerpt(); ?>
							</div><!-- .entry-meta -->
						</td><!-- .mb-col-title -->

						<td class="mb-col-datetime">
							<?php mb_reply_date(); ?><br />
							<?php mb_reply_time(); ?>
						</td><!-- .mb-col-datetime -->

						<td class="mb-col-author">
							<?php mb_reply_author_profile_link(); ?>
						</td><!-- .mb-col-author -->
					</tr>

				<?php endif; // End content type check. ?>

			<?php endwhile; // End found posts loop. ?>

		</tbody>

	</table><!-- .mb-loop-reply -->

	<?php mb_loop_search_pagination(); ?>

<?php endif; // End check for search results. ?>