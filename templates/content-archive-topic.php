<?php
/**
 * This template part outputs the topic archive content (latest topics).  The topic archive is the 
 * board home page if the plugin is set to show topics on front.
 */
?>

<?php 
	/* Loads the `loop-topic.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'topic' );
?>

<?php 
	/* Loads the `form-topic-new.php` template part.  Falls back to the `form-topic.php` template part. */
	mb_get_template_part( 'form-topic', 'new' );
?>