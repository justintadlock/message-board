
	<?php if ( mb_reply_query() ) : // Checks if any posts were found. ?>
<table>
	<thead>
		<tr>
			<th>Replies</th>
			<th>Created</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>Replies</th>
			<th>Created</th>
		</tr>
	</tfoot>
	<tbody>

		<?php while ( mb_reply_query() ) : // Begins the loop through found posts. ?>

			<?php mb_the_reply(); // Loads the post data. ?>

	<tr>
		<td>
			<a class="reply-link" href="<?php mb_reply_url(); ?>"><?php mb_reply_title(); ?></a>
		</td>
		<td class="num">
			<?php echo get_the_date(); ?>
		</td>
	</tr>
		<?php endwhile; // End found posts loop. ?>


	</tbody>
</table>
		<?php locate_template( array( 'misc/loop-nav.php' ), true ); // Loads the misc/loop-nav.php template. ?>

	<?php endif; // End check for posts. ?>
