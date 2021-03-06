<div class="mb-page-header">
	<h1 class="mb-page-title"><?php mb_user_page_title(); ?></h1>
</div><!-- .mb-page-header -->

<ul class="mb-user-page-links">
	<li><?php mb_user_link(); ?></li>
	<li><?php mb_user_forums_link(); ?></li>
	<li><?php mb_user_topics_link(); ?></li>
	<li><?php mb_user_replies_link(); ?></li>
	<li><?php mb_user_bookmarks_link(); ?></li>
	<li><?php mb_user_topic_subscriptions_link(); ?></li>
	<li><?php mb_user_forum_subscriptions_link(); ?></li>
	<?php if ( current_user_can( 'edit_user', mb_get_user_id() ) ) printf( '<li>%s</li>', mb_get_user_edit_link() ); ?>
</ul>

<?php if ( mb_is_user_page( array( 'forums', 'forum-subscriptions' ) ) ) : ?>

	<?php mb_get_template_part( 'loop-forum', mb_show_hierarchical_forums() ? 'hierarchical' : 'flat' ); ?>

<?php elseif ( mb_is_user_page( array( 'topics', 'topic-subscriptions', 'bookmarks' ) ) ) : ?>

	<?php mb_get_template_part( 'loop-topic' ); ?>

<?php elseif ( mb_is_user_page( 'replies' ) ) : ?>

	<?php mb_get_template_part( 'loop-reply' ); ?>

<?php else : ?>

	<?php echo get_avatar( mb_get_user_id() ); ?>

	<div class="mb-user-info">

		<ul>
			<li><?php printf( __( 'Forum role: %s', 'message-board' ), mb_get_role_link( mb_get_user_role() ) ); ?></li>
			<li><?php printf( __( 'Forums created: %s', 'message-board' ), mb_get_user_forum_count() ); ?></li>
			<li><?php printf( __( 'Topics started: %s', 'message-board' ), mb_get_user_topic_count() ); ?></li>
			<li><?php printf( __( 'Replies posted: %s', 'message-board' ), mb_get_user_reply_count() ); ?></li>
			<li><?php printf( __( 'Member since: %s', 'message-board' ), date( get_option( 'date_format' ), strtotime( get_the_author_meta( 'user_registered', get_query_var( 'author' ) ) ) ) ); ?></li>
			<li><?php printf( __( 'Web site: %s', 'message-board' ), make_clickable( get_the_author_meta( 'url', get_query_var( 'author' ) ) ) ); ?></li>
		</ul>

		<?php echo wpautop( get_the_author_meta( 'description', get_query_var( 'author' ) ) ); ?>

	</div><!-- .mb-user-info -->

<?php endif; ?>