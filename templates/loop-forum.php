<?php if ( mb_forum_query() ) : ?>
	<table>
		<thead>
			<tr>
				<th>Forum
					<?php if ( current_user_can( 'create_forums' ) ) : ?>
					<a href="<?php mb_forum_form_url(); ?>" class="genericon-edit new-topic">New Forum &rarr;</a>
					<?php endif; ?>
				</th>
				<th class="num">Topics</th>
				<th class="num">Posts</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Forums</th>
				<th class="num">Topics</th>
				<th class="num">Posts</th>
			</tr>
		</tfoot>
		<tbody>

			<?php while ( mb_forum_query() ) : ?>

				<?php mb_the_forum(); ?>

				<tr class="forum">
					<td>
						<?php mb_forum_link(); ?>

						<?php if ( mb_is_forum_archive() && mb_subforum_query() ) : ?>

							<div class="entry-meta">
								<?php while ( mb_subforum_query() ) : ?>
									<?php mb_the_subforum(); ?>
									<?php mb_forum_link(); ?> (<?php mb_forum_topic_count(); ?>)
								<?php endwhile; ?>
							</div>

						<?php endif; ?>
					</td>
					<td class="num"><?php mb_forum_topic_count(); ?></td>
					<td class="num"><?php mb_forum_post_count(); ?></td>
				</tr>
			<?php endwhile; ?>
		</tbody>
	</table>
<?php endif; ?>