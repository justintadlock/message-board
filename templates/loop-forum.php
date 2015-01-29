<?php if ( mb_forum_query() ) : // If there are forums to show. ?>

	<?php do_action( 'mb_loop_forum_before', 'flat' ); ?>

	<table class="mb-loop-forum">

		<thead>
			<tr>
				<th class="mb-col-title"><?php mb_is_single_forum() && !mb_is_forum_category() ? _e( 'Sub-forum', 'message-board' ) : _e( 'Forum', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Topics / Replies', 'message-board' ); ?></th>
				<th class="mb-col-latest"><?php _e( 'Last Post', 'message-board' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="mb-col-title"><?php mb_is_single_forum() ? _e( 'Sub-forum', 'message-board' ) : _e( 'Forum', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Topics / Replies', 'message-board' ); ?></th>
				<th class="mb-col-latest"><?php _e( 'Last Post', 'message-board' ); ?></th>
			</tr>
		</tfoot>

		<tbody>
			<?php while ( mb_forum_query() ) : // Loop through the forums. ?>

				<?php mb_the_forum(); // Set up forum data. ?>

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
						<?php if ( mb_get_forum_last_post_id() ) : // Only show if the forum is not empty. ?>
							<?php mb_forum_last_topic_link(); ?><br />
							<?php mb_forum_last_post_author(); ?><br />
							<a href="<?php mb_forum_last_post_url(); ?>"><?php mb_forum_last_active_time(); ?> ago</a> 
						<?php endif; ?>
					</td><!-- .mb-col-latest -->
				</tr>

			<?php endwhile; // End forum loop. ?>

		</tbody>

	</table><!-- .mb-loop-forum -->

	<?php do_action( 'mb_loop_forum_after', 'flat' ); ?>

	<?php mb_loop_forum_pagination(); ?>

<?php endif; // End check for forums. ?>