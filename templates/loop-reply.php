<?php if ( mb_reply_query() ) : // Checks if any posts were found. ?>

	<table class="mb-loop-reply">

		<thead>
			<tr>
				<th class="mb-col-title"><?php _e( 'Replies', 'message-board' ); ?></th>
				<th class="mb-col-datetime"><?php _e( 'Created', 'message-board' ); ?></th>
				<th class="mb-col-author"><?php _e( 'Author', 'message-board' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="mb-col-title"><?php _e( 'Replies', 'message-board' ); ?></th>
				<th class="mb-col-datetime"><?php _e( 'Created', 'message-board' ); ?></th>
				<th class="mb-col-author"><?php _e( 'Author', 'message-board' ); ?></th>
			</tr>
		</tfoot>

		<tbody>

			<?php while ( mb_reply_query() ) : // Begins the loop through found posts. ?>

				<?php mb_the_reply(); // Loads the post data. ?>

				<tr>
					<td class="mb-col-title">
						<?php mb_reply_link(); ?>
						<div class="mb-reply-summary">
							<?php the_excerpt(); ?>
						</div><!-- .mb-reply-summary -->
					</td><!-- .mb-col-title -->

					<td class="mb-col-datetime">
						<?php mb_reply_date(); ?><br />
						<?php mb_reply_time(); ?>
					</td><!-- .mb-col-datetime -->

					<td class="mb-col-author">
						<?php mb_reply_author_profile_link(); ?>
					</td><!-- .mb-col-author -->
				</tr>

			<?php endwhile; // End found posts loop. ?>

		</tbody>

	</table><!-- .mb-loop-reply -->

	<?php mb_loop_reply_pagination(); ?>

<?php endif; // End check for posts. ?>
