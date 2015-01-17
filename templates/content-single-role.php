<?php
/**
 * This template part outputs the single role content.
 */
?>

<header class="mb-page-header">
	<h1 class="mb-page-title"><?php mb_single_role_title(); ?></h1>
</header><!-- .mb-page-header -->

<?php 
	/* Loads the `loop-user.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'user' );
?>