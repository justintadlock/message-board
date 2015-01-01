<?php
/**
 * This template part outputs the search results content.
 */
?>

<?php 
	/* Loads the `form-search-basic.php` template part. */
	mb_get_template_part( 'form-search', 'basic' ); 
?>

<?php 
	/* Loads the `loop-search.php` template part.  Falls back to the `loop.php` template part. */
	mb_get_template_part( 'loop', 'search' ); 
?>