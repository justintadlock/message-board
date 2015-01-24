<?php
/**
 * This template part outputs the role archive content.
 */
?>

<header class="mb-page-header">
	<h1 class="mb-page-title"><?php the_archive_title(); ?></h1>
</header><!-- .mb-page-header -->

<?php 
	/* Loads the `loop-role.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'role' );
?>