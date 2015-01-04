<?php if ( mb_forum_query() ) : // If there are forums to show. ?>

	<?php while ( mb_forum_query() ) : // Loop through the forums. ?>

		<?php mb_the_forum(); // Set up forum data. ?>

		<table class="mb-loop-forum">

			<thead>
				<tr>
					<th class="mb-col-title"><?php mb_subforum_query() ? mb_forum_link() : _e( 'Forum', 'message-board' ); ?></th>
					<th class="mb-col-count"><?php _e( 'Topics / Replies', 'message-board' ); ?></th>
					<th class="mb-col-latest"><?php _e( 'Last Post', 'message-board' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php if ( mb_subforum_query() ) : // If the forum has subforums. ?>

					<tr <?php post_class(); ?>>
						<td class="mb-col-content" colspan="3">
							<div class="mb-forum-content">
								<?php mb_forum_content(); ?>
							</div><!-- .mb-forum-content -->
						</td><!-- .mb-col-content -->
					</tr>

					<?php while ( mb_subforum_query() ) : // Loop through the subforums. ?>

						<?php mb_the_subforum(); ?>

						<tr <?php post_class(); ?>>
							<td class="mb-col-title">
								<?php mb_forum_link(); ?>

								<div class="mb-forum-content">
									<?php mb_forum_content(); ?>
								</div><!-- .mb-forum-content -->

							</td><!-- .mb-col-title -->

							<td class="mb-col-count">
								<?php printf( __( 'Topics: %s', 'message-board' ), mb_get_forum_topic_count() ); ?><br />
								<?php printf( __( 'Replies: %s', 'message-board' ), mb_get_forum_reply_count() ); ?>
							</td><!-- .mb-col-count -->

							<td class="mb-col-latest">
								<?php mb_forum_last_topic_link(); ?><br />
								<?php printf( __( 'By %s', 'message-board' ), mb_get_forum_last_poster() ); ?><br />
								<a href="<?php mb_forum_last_post_url(); ?>"><?php mb_forum_last_active_time(); ?> ago</a> 
							</td><!-- .mb-col-latest -->
						</tr>

					<?php endwhile; // End subforums loop. ?>

				<?php else : // If the forum doesn't have subforums. ?>

					<tr <?php post_class(); ?>>

						<td class="mb-col-title">
							<?php mb_forum_link(); ?>

							<div class="mb-forum-content">
								<?php mb_forum_content(); ?>
							</div><!-- .mb-forum-content -->

						</td><!-- .mb-col-title -->

						<td class="mb-col-count">
							<?php printf( __( 'Topics: %s', 'message-board' ), mb_get_forum_topic_count() ); ?><br />
							<?php printf( __( 'Replies: %s', 'message-board' ), mb_get_forum_reply_count() ); ?>
						</td><!-- .mb-col-count -->

						<td class="mb-col-latest">
							<?php mb_forum_last_topic_link(); ?><br />
							<?php printf( __( 'By %s', 'message-board' ), mb_get_forum_last_poster() ); ?><br />
							<a href="<?php mb_forum_last_post_url(); ?>"><?php mb_forum_last_active_time(); ?> ago</a> 
						</td><!-- .mb-col-latest -->
					</tr>

				<?php endif; ?>

			</tbody>

		</table><!-- .mb-loop-forum -->

	<?php endwhile; // End forum loop. ?>

	<?php mb_loop_forum_pagination(); ?>

<?php endif; // End check for forums. ?>