<div class="loop-meta">
	<h1 class="loop-title"><?php mb_user_page_title(); ?></h1>
</div><!-- .loop-meta -->

<ul class="mb-user-page-links">
	<li><?php mb_user_profile_link(); ?></li>
	<li><?php mb_user_page_link( 'forums' ); ?></li>
	<li><?php mb_user_page_link( 'topics' ); ?></li>
	<li><?php mb_user_page_link( 'replies' ); ?></li>
	<li><?php mb_user_page_link( 'bookmarks' ); ?></li>
	<li><?php mb_user_page_link( 'topic-subscriptions' ); ?></li>
	<li><?php mb_user_page_link( 'forum-subscriptions' ); ?></li>
	<?php if ( current_user_can( 'edit_user', mb_get_user_id() ) ) printf( '<li>%s</li>', mb_get_user_edit_link() ); ?>
</ul>

<?php if ( mb_is_user_page( array( 'forums', 'forum-subscriptions' ) ) ) : ?>

	<?php mb_get_template_part( 'loop', 'forum' ); ?>

<?php elseif ( mb_is_user_page( array( 'topics', 'topic-subscriptions', 'bookmarks' ) ) ) : ?>

	<?php mb_get_template_part( 'loop', 'topic' ); ?>

<?php elseif ( mb_is_user_page( 'replies' ) ) : ?>

	<?php mb_get_template_part( 'loop', 'reply' ); ?>

<?php else : ?>

	<?php echo get_avatar( mb_get_user_id() ); ?>

	<div class="mb-user-info">

		<ul>
			<li>Member since: <?php echo date( get_option( 'date_format' ), strtotime( get_the_author_meta( 'user_registered', get_query_var( 'author' ) ) ) ); ?></li>
			<li>Web site: <?php echo make_clickable( get_the_author_meta( 'url', get_query_var( 'author' ) ) ); ?></li>
		</ul>

		<?php echo wpautop( get_the_author_meta( 'description', get_query_var( 'author' ) ) ); ?>

	</div><!-- .mb-user-info -->

<?php endif; ?>