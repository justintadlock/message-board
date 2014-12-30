
	<?php if ( mb_search_query() ) : // Checks if any posts were found. ?>

		<table>
			<thead>
				<tr>
					<th>Results</th>
					<th class="num">Posted</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Results</th>
					<th class="num">Posted</th>
				</tr>
			</tfoot>
			<tbody>

		<?php while ( mb_search_query() ) : // Begins the loop through found posts. ?>

			<?php mb_the_search_post(); // Loads the post data. ?>

				<tr>
					<td>
						<a class="topic-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<div class="entry-meta">

<?php the_excerpt(); ?>

						</div><!-- .entry-meta -->
					</td>
					<td class="num">
						<a style="font-size: 14px;" href="<?php the_permalink(); ?>"><?php echo human_time_diff( get_the_date( 'U' ), current_time( 'timestamp' ) ); ?> ago</a>
					</td>
				</tr>
		<?php endwhile; // End found posts loop. ?>

			</tbody>
		</table>

		<?php mb_loop_search_pagination(); ?>

	<?php endif; // End check for posts. ?>