<?php
/**
 * Assume all of the below will change.  This is a rough first draft of the template hierarchy that themes 
 * can use.
 */

add_filter( 'template_include', 'mb_template_include' );

function mb_template_include( $template ) {

	$dir = 'message-board';

	if ( mb_is_forum_front() ) {
		$has_template = locate_template( 
			array( "{$dir}/archive-forum.php", "{$dir}/board.php" ) 
		);
	}

	elseif ( is_singular( 'forum_topic' ) && get_query_var( 'edit' ) && current_user_can( 'edit_post', absint( get_query_var( 'edit' ) ) ) ) {
		$has_template = locate_template( 
			array( "{$dir}/edit.php", "{$dir}/board.php" ) 
		);
	}

	elseif ( is_singular( 'forum_topic' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/single-topic.php", "{$dir}/board.php" ) 
		);
	}

	elseif ( is_tax( 'forum' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/single-forum.php" ) 
		);
	}

	elseif ( is_tax( 'forum_tag' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/single-tag.php" ) 
		);
	}

	elseif ( is_post_type_archive( 'forum_topic' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/archive-topic.php" ) 
		);
	}

	elseif ( is_author() && mb_is_user_view() ) {
		$has_template = locate_template( 
			array( "{$dir}/single-user-topics.php", "{$dir}/single-user.php" ) 
		);
	}

	elseif ( is_author() && get_query_var( 'mb_subscriptions' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/single-user-topics.php", "{$dir}/single-user.php" ) 
		);
	}

	elseif ( is_author() && get_query_var( 'mb_topics' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/single-user-topics.php", "{$dir}/single-user.php" ) 
		);
	}

	elseif ( 1 == get_query_var( 'mb_profile' ) ) {
		$has_template = locate_template( 
			array( "{$dir}/single-user.php" ) 
		);
	}

	elseif ( mb_is_view() ) {
		$view = sanitize_key( get_query_var( 'mb_view' ) );

		$has_template = locate_template( 
			array( "{$dir}/single-view-{$view}.php", "{$dir}/single-view.php" )
		);
	}

	elseif ( mb_is_forum_search() ) {
		$has_template = locate_template( 
			array( "{$dir}/search.php", "{$dir}/archive-topic.php" )
		);
	}

	return !empty( $has_template ) ? $has_template : $template;
}
