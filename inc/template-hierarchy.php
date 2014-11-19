<?php
/**
 * Assume all of the below will change.  This is a rough first draft of the template hierarchy that themes 
 * can use.
 */

add_filter( 'template_include', 'mb_template_include' );

function mb_template_include( $template ) {

	$dir          = 'message-board';
	$has_template = false;
	$_templates   = array();

	if ( is_singular( mb_get_forum_post_type() ) ) {

		$_templates[] = "{$dir}/single-forum.php";
	}

	elseif ( is_post_type_archive( mb_get_forum_post_type() ) ) {

		$_templates[] = "{$dir}/archive-forum.php";
	}

	elseif ( is_singular( mb_get_topic_post_type() ) && get_query_var( 'edit' ) && current_user_can( 'edit_post', absint( get_query_var( 'edit' ) ) ) ) {

		$_templates[] = "{$dir}/edit.php";
	}

	elseif ( is_singular( mb_get_topic_post_type() ) ) {

		$_templates[] = "{$dir}/single-topic.php";
	}

	elseif ( is_post_type_archive( mb_get_topic_post_type() ) ) {

		$_templates[] = "{$dir}/archive-topic.php";
	}

	elseif ( is_author() && mb_is_user_view() ) {

		$_templates[] = "{$dir}/single-user-topics.php";
		$_templates[] = "{$dir}/single-user.php";
	}

	elseif ( is_author() && get_query_var( 'mb_subscriptions' ) ) {

		$_templates[] = "{$dir}/single-user-topics.php";
		$_templates[] = "{$dir}/single-user.php";
	}

	elseif ( is_author() && get_query_var( 'mb_topics' ) ) {

		$_templates[] = "{$dir}/single-user-topics.php";
		$_templates[] = "{$dir}/single-user.php";
	}

	elseif ( 1 == get_query_var( 'mb_profile' ) ) {

		$_templates[] = "{$dir}/single-user.php";
	}

	elseif ( mb_is_forum_search() ) {

		$_templates[] = "{$dir}/search.php";
		$_templates[] = "{$dir}/archive-topic.php"; // temp
	}

	elseif ( mb_is_forum_login() ) {

		$_templates[] = "{$dir}/login.php";
	}

	if ( !empty( $_templates ) ) {
		$_templates[] = "{$dir}/board.php";
		$has_template = locate_template( apply_filters( 'mb_template_hierarchy', $_templates ) );

		return apply_filters( 'mb_template_include', !empty( $has_template ) ? $has_template : $template );
	}

	return $template;
}
