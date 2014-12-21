	<?php $mb_users = mb_get_users(); ?>

	<?php if ( $mb_users ) : // Checks if any posts were found. ?>

		<table>
			<thead>
				<tr>
					<th>User</th>
					<th class="num">Topics</th>
					<th class="num">Replies</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>User</th>
					<th class="num">Topics</th>
					<th class="num">Replies</th>
				</tr>
			</tfoot>
			<tbody>

		<?php foreach ( $mb_users as $mb_user ) : // Begins the loop through found posts. ?>

<tr>
	<td><?php echo get_avatar( mb_get_user_id() ); ?> <?php mb_user_profile_link(); ?> <?php mb_user_edit_link(); ?></td>
	<td><?php mb_user_topic_count(); ?></td>
	<td><?php mb_user_reply_count(); ?></td>
</tr>

		<?php endforeach; // End found posts loop. ?>

			</tbody>
		</table>

		<?php mb_users_pagination(); // Loads the misc/loop-nav.php template. ?>

	<?php endif; // End check for posts. ?>