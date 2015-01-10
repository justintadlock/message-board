<?php if ( mb_user_query() ) : // Checks if we have users. ?>

	<table class="mb-loop-user">
		<thead>
			<tr>
				<th class="mb-col-title"><?php _e( 'Users', 'message-board' ); ?></th>
				<th class="mb-col-role"><?php _e( 'Role', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Topics', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Replies', 'message-board' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="mb-col-title"><?php _e( 'Users', 'message-board' ); ?></th>
				<th class="mb-col-role"><?php _e( 'Role', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Topics', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Replies', 'message-board' ); ?></th>
			</tr>
		</tfoot>
		<tbody>

		<?php while ( mb_user_query() ) : // Begins the loop through found users. ?>

			<?php mb_the_user(); // Set up user data. ?>

			<tr>
				<td class="mb-col-title">
					<?php echo get_avatar( mb_get_user_id() ); ?>
					<?php mb_user_link(); ?>
					<div class="mb-user-description">
						<?php the_author_meta( 'description', mb_get_user_id() ); ?>
					</div><!-- .mb-user-description -->
				</td>
				<td class="mb-col-role"><?php mb_role_link( mb_get_user_role() ); ?></td>
				<td class="mb-col-count"><a href="<?php mb_user_topics_url(); ?>"><?php mb_user_topic_count(); ?></a></td>
				<td class="mb-col-count"><a href="<?php mb_user_replies_url(); ?>"><?php mb_user_reply_count(); ?></a></td>
			</tr>

		<?php endwhile; // End found users loop. ?>

		</tbody>

	</table><!-- .mb-loop-user -->

	<?php mb_loop_user_pagination(); ?>

<?php endif; // End check for posts. ?>