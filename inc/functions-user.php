<?php

function mb_notify_topic_subscribers( $topic_id, $reply_id ) {

	$subscribers =  mb_get_topic_subscribers( $topic_id, true );

	if ( empty( $subscribers ) )
		return false;

	remove_all_filters( 'mb_get_reply_content' );

	$topic_title   = strip_tags( mb_get_topic_title(   $topic_id ) );

	$reply_url        = mb_get_reply_url( $reply_id );
	$reply_author     = mb_get_reply_author( $reply_id );
	$reply_author_id  = mb_get_reply_author_id( $reply_id );
	$reply_content    = strip_tags( mb_get_reply_content( $reply_id ) );

	$blog_name     = esc_html( strip_tags( get_option( 'blogname' ) ) );

	$from          = '<noreply@' . ltrim( get_home_url(), '^(http|https)://' ) . '>';

	$message = sprintf( 
		__( '%1$s replied: %4$s%2$s %4$sPost Link: %3$s %4$sYou are receiving this email because you subscribed to a forum topic. Log in and visit the topic to unsubscribe from these emails.', 'message-board' ),
		$reply_author,
		$reply_content,
		$reply_url,
		"\n\n"
	);

	$subject = '[' . $blog_name . '] ' . $topic_title;

	$headers = array();

	$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' ' . $from;

	foreach ( (array) $subscribers as $user_id ) {

		if ( absint( $reply_author_id ) === absint( $user_id ) )
			continue;

		$headers[] = 'Bcc: ' . get_userdata( $user_id )->user_email;
	}

	return wp_mail( $from, $subject, $message, $headers );
}
