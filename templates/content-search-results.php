<?php
/**
 * This template part outputs the search results content.
 */
?>

<?php 
	/* Loads the `form-search.php` template part. */
	mb_get_template_part( 'form-search' ); 
?>

<?php 
	/* Loads the `loop-search.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'search' ); 
?>