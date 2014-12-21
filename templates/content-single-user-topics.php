<?php get_header(); // Loads the header.php template. ?>

<main <?php hybrid_attr( 'content' ); ?>>

		<?php hybrid_get_menu( 'forum-views' ); // Loads the menu/forum-views.php template. ?>

	<?php locate_template( array( 'misc/loop-meta.php' ), true ); // Loads the misc/loop-meta.php template. ?>

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

		<?php get_template_part( 'board/loop', 'topic' ); ?>


	</tbody>
</table>

		<?php locate_template( array( 'misc/loop-nav.php' ), true ); // Loads the misc/loop-nav.php template. ?>

	<?php else : // If no posts were found. ?>

		<?php locate_template( array( 'content/error.php' ), true ); // Loads the content/error.php template. ?>

	<?php endif; // End check for posts. ?>

	<?php if ( function_exists( 'mb_topic_form' ) ) mb_topic_form(); ?>

</main><!-- #content -->

<?php get_footer(); // Loads the footer.php template. ?>