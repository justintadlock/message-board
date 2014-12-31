<?php
/**
 * This template part outputs the user archive content.
 */
?>

<div class="loop-meta">
	<h1 class="loop-title"><?php mb_user_archive_title(); ?></h1>
</div><!-- .loop-meta -->

<?php 
	/* Loads the `loop-user.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'user' );
?>