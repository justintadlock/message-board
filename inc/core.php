<?php

add_action( 'init', 'mb_register_post_statuses' );

function mb_register_post_statuses() {

	register_post_status(
		'close',
		array(
			'label' => __( 'Closed', 'message-board' ),
			'label_count' => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'message-board' ),
			'public'      => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list' => true,
		)
	);
}