<?php if ( mb_topic_query() ) : // If there are any topics to show. ?>

	<table class="mb-loop-topic">

		<thead>
			<tr>
				<th class="mb-col-title"><?php _e( 'Topic', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Replies / Voices', 'message-board' ); ?></th>
				<th class="mb-col-latest"><?php _e( 'Last Post', 'message-board' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="mb-col-title"><?php _e( 'Topic', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Replies / Voices', 'message-board' ); ?></th>
				<th class="mb-col-latest"><?php _e( 'Last Post', 'message-board' ); ?></th>
			</tr>
		</tfoot>

		<tbody>

			<?php while ( mb_topic_query() ) : // Loop through the topics. ?>

				<?php mb_the_topic(); // Sets up the topic data. ?>

				<tr <?php post_class(); ?>>

					<td class="mb-col-title">
						<?php if ( !mb_is_user_page() ) echo get_avatar( mb_get_topic_author_id() ); ?>
						<?php mb_topic_link(); ?>
						<div class="mb-topic-meta">
							<?php mb_topic_states(); ?>
							<?php printf( __( 'Started by %s', 'message-board' ), mb_get_topic_author_profile_link() ); ?> 
							<?php mb_topic_date(); ?> 
							<?php mb_topic_time(); ?>
						</div><!-- .mb-topic-meta -->
					</td><!-- .mb-col-title -->

					<td class="mb-col-count">
						<?php printf( __( 'Replies: %s', 'message-board' ), mb_get_topic_reply_count() ); ?><br />
						<?php printf( __( 'Voices: %s', 'message-board' ), mb_get_topic_voice_count() ); ?>
					</td><!-- .mb-col-count -->

					<td class="mb-col-latest">
						<?php mb_topic_last_poster(); ?><br />
						<a href="<?php mb_topic_last_post_url(); ?>"><?php mb_topic_last_active_time(); ?></a> 
					</td><!-- .mb-col-latest -->
				</tr>

			<?php endwhile; // End topics loop. ?>

		</tbody>

	</table><!-- .mb-loop-topic -->

	<?php mb_loop_topic_pagination(); ?>

<?php endif; // End check for topics. ?>