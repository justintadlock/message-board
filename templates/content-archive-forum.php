<?php
/**
 * This template part outputs the forum archive content.  The forum archive is the board home page if 
 * the plugin is set to show forums on front (default).
 */
?>

<?php 
	/* Loads the `loop-forum.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'forum' );
?>

<?php 
	/* Loads the `form-forum-new.php` template part.  Falls back to the `form-forum.php` template part. */
	mb_get_template_part( 'form-forum', 'new' );
?>