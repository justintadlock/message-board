<?php if ( mb_role_query() ) : // Checks if we have roles. ?>

	<table class="mb-loop-role">
		<thead>
			<tr>
				<th class="mb-col-title"><?php _e( 'Roles', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Users', 'message-board' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="mb-col-title"><?php _e( 'Roles', 'message-board' ); ?></th>
				<th class="mb-col-count"><?php _e( 'Users', 'message-board' ); ?></th>
			</tr>
		</tfoot>
		<tbody>

		<?php while ( mb_role_query() ) : // Begins the loop through found roles. ?>

			<?php mb_the_role(); // Set up role data. ?>

			<tr>
				<td class="mb-col-title">
					<?php mb_role_link(); ?>
					<div class="mb-role-description">
						<?php mb_role_description(); ?>
					</div><!-- .mb-role-description -->
				</td>
				<td class="mb-col-count"><?php mb_role_user_count(); ?></td>
			</tr>

		<?php endwhile; // End found roles loop. ?>

		</tbody>

	</table><!-- .mb-loop-role -->

	<?php mb_loop_role_pagination(); ?>

<?php endif; // End check for roles. ?>