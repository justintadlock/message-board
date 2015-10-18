<?php
/**
 * Integration with the Members WordPress plugin.
 *
 * @package    MessageBoard
 * @subpackage Extensions
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action( 'members_register_role_groups', 'mb_register_role_groups' );

function mb_register_role_groups() {

	members_register_role_group(
		'message-board',
		array(
			'label'       => esc_html__( 'Forum', 'message-board' ),
			'label_count' => _n_noop( 'Forum %s', 'Forum %s', 'message-board' ),
			'roles'       => array_keys( mb_get_dynamic_roles() ),
		)
	);
}
