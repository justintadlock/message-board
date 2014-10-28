<?php

function mb_user_profile_url( $user_id = 0 ) {
	echo mb_get_user_profile_url( $user_id );
}

function mb_get_user_profile_url( $user_id = 0 ) {

	$nicename = get_the_author_meta( 'user_nicename', $user_id );

	$profile_url = esc_url( home_url( trailingslashit( mb_get_user_slug() ) . $nicename ) );

	return apply_filters( 'mb_get_user_profile_url', esc_url( $profile_url ), $user_id );
}

function mb_user_topics_url( $user_id = 0 ) {
	echo mb_get_user_topics_url( $user_id );
}

function mb_get_user_topics_url( $user_id = 0 ) {

	$url = mb_get_user_profile_url( $user_id );

	return esc_url( trailingslashit( $url ) . 'topics' );
}

function mb_user_favorites_url( $user_id = 0 ) {
	echo mb_get_user_favorites_url( $user_id );
}

function mb_get_user_favorites_url( $user_id = 0 ) {

	$url = trailingslashit( mb_get_user_profile_url( $user_id ) ) . 'favorites';

	return apply_filters( 'mb_get_user_favorites_url', esc_url( $url ), $user_id );
}

function mb_user_subscriptions_url( $user_id = 0 ) {
	echo mb_get_user_subscriptions_url( $user_id );
}

function mb_get_user_subscriptions_url( $user_id = 0 ) {

	$url = trailingslashit( mb_get_user_profile_url( $user_id ) ) . 'subscriptions';

	return apply_filters( 'mb_get_user_subscriptions_url', esc_url( $url ), $user_id );
}
