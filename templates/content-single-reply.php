<header class="mb-page-header">
	<h1 class="mb-page-title"><?php mb_single_reply_title(); ?></h1>
</header><!-- .mb-page-header -->

<?php if ( current_user_can( 'read_reply', mb_get_reply_id() ) ) : ?>

	<ol id="mb-thread" class="mb-thread">

		<?php if ( mb_reply_query() ) : ?>

			<?php while ( mb_reply_query() ) : ?>

				<?php mb_the_reply(); ?>

				<?php mb_get_template_part( 'thread', 'reply' ); ?>

			<?php endwhile; ?>

		<?php endif; ?>

	</ol><!-- #thread -->

<?php endif; ?>