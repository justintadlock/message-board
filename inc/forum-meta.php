<?php

function mb_get_forum_meta( $forum_id, $key = '', $single = false ) {
	return get_metadata( 'term', $forum_id, $key, $single );
}	

function mb_add_forum_meta( $forum_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'term', $forum_id, $meta_key, $meta_value, $unique );
}

function mb_delete_forum_meta( $forum_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'term', $forum_id, $meta_key, $meta_value );
}

function mb_update_forum_meta( $forum_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'term', $forum_id, $meta_key, $meta_value, $prev_value );
}
