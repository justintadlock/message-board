
	<?php if ( mb_topic_query() ) : // Checks if any posts were found. ?>
<table>
	<thead>
		<tr>
			<th>Topics <?php mb_topic_form_link(); ?></th>
			<th class="num">Posts</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>Topics</th>
			<th class="num">Posts</th>
		</tr>
	</tfoot>
	<tbody>

		<?php while ( mb_topic_query() ) : // Begins the loop through found posts. ?>

			<?php mb_the_topic(); // Loads the post data. ?>

	<tr class="<?php echo mb_is_topic_super() ? 'sticky' : ''; ?>">
		<td>
			<a class="topic-link" href="<?php mb_topic_url(); ?>"><?php mb_topic_title(); ?></a>
			<div class="entry-meta">
				<?php mb_topic_labels(); ?>
				Last post 
				<a href="<?php mb_topic_last_post_url(); ?>"><?php mb_topic_last_active_time(); ?> ago</a> 
				by <?php mb_topic_last_poster(); ?>
			</div><!-- .entry-meta -->
		</td>
		<td class="num">
			<a href="<?php mb_topic_last_post_url(); ?>"><?php mb_topic_post_count(); ?></a>
		</td>
	</tr>
		<?php endwhile; // End found posts loop. ?>


	</tbody>
</table>
		<?php locate_template( array( 'misc/loop-nav.php' ), true ); // Loads the misc/loop-nav.php template. ?>

	<?php endif; // End check for posts. ?>
