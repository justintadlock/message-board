<?php
/**
 * This template part outputs the reply archive content (latest replies).
 */
?>

<?php 
	/* Loads the `loop-reply.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'reply' );
?>

<?php 
	/* Loads the `form-reply-new.php` template part.  Falls back to the `form-reply.php` template part. */
	mb_get_template_part( 'form-reply', 'new' );
?>