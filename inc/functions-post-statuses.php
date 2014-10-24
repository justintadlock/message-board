<?php

/* Register post statuses. */
add_action( 'init', 'mb_register_post_statuses' );

/**
 * Registers post statuses used by the plugin that WordPress doesn't offer out of the box.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_statuses() {

	register_post_status(
		'close',
		array(
			'label'                     => __( 'Closed', 'message-board' ),
			'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
		)
	);

	register_post_status(
		'spam',
		array(
			'label'                     => __( 'Spam', 'message-board' ),
			'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'message-board' ),
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => false,
		)
	);
}
